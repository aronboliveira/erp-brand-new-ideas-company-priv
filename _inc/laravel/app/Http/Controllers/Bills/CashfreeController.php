<?php

namespace App\Http\Controllers\Bills;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{MiddlewaresConstants as MWC, ViewsConstants as VW};
use App\Models\{
    Coupon,
    Customer,
    Invoice,
    InvoicePayment,
    Order,
    Plan,
    User,
    UserCoupon,
    Utility
};
use App\Traits\ChecksLogin;
use GuzzleHttp\Client;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Log,
    Route,
    View as ViewFacade
};
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

final class CashfreeController extends Controller
{

    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MWC::AUTH]);
    }

    public function paymentConfig(?array $settings = null): void
    {
        $s = $settings ?? Utility::getAdminPaymentSetting();
        config([
            'services.cashfree.currency' => $s['currency'] ?? 'USD',
            'services.cashfree.key'      => $s['cashfree_api_key'] ?? '',
            'services.cashfree.secret'   => $s['cashfree_secret_key'] ?? '',
            'services.cashfree.url'      => $s['cashfree_url']
                ?? 'https://api.cashfree.com/pg/orders',
        ]);
        Log::info(__METHOD__ . ' loaded config', ['settings' => $s]);
    }

    public const CF_PAY_STR = 'cashfreePaymentStore';
    public function cashfreePaymentStore(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['user_id' => Auth::id(), 'plan_id' => $req->plan_id, 'coupon' => $req->coupon, 'method' => $method]);
            try {
                $decStart = microtime(true);
                $planId = Crypt::decrypt($req->plan_id);
                $this->logExecutionTime($decStart, $action, 'decryptPlanId');
                $planFindStart = microtime(true);
                $plan = Plan::find($planId);
                $this->logExecutionTime($planFindStart, $action, 'findPlan');
                if ($r = self::guardPlan($plan, $req)) return $r;
                $usr = $req->user();
                $cfgStart = microtime(true);
                self::paymentConfig();
                $this->logExecutionTime($cfgStart, $action, 'paymentConfig');
                $amount = $plan->price;
                $couponFetchStart = microtime(true);
                $coupon = Coupon::where('code', strtoupper($req->coupon ?? ''))->where('is_active', 1)->first();
                $this->logExecutionTime($couponFetchStart, $action, 'fetchCoupon');
                if ($coupon) {
                    if ($coupon->limit <= $coupon->used_coupon()) {
                        Log::warning("[{$base}::{$action}] coupon expired", ['code' => $coupon->code]);
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $applyStart = microtime(true);
                    $discount = ($plan->price / 100) * $coupon->discount;
                    $amount -= $discount;
                    $this->logExecutionTime($applyStart, $action, 'applyCoupon');
                    Log::info("[{$base}::{$action}] coupon applied", ['code' => $coupon->code, 'discount' => $discount, 'amount' => $amount]);
                    if ($amount <= 0) return self::activateFreePlan($usr, $plan, $coupon);
                }
                $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
                $httpStart = microtime(true);
                $resp = self::curlPost(
                    config('services.cashfree.url'),
                    [
                        'Content-Type: application/json',
                        'x-api-version: 2022-01-01',
                        'x-client-id: ' . config('services.cashfree.key'),
                        'x-client-secret: ' . config('services.cashfree.secret'),
                    ],
                    [
                        'order_id' => $orderId,
                        'order_amount' => $amount,
                        'order_currency' => config('services.cashfree.currency'),
                        'order_name' => $plan->name,
                        'customer_details' => [
                            'customer_id' => "customer_{$usr->id}",
                            'customer_name' => $usr->name,
                            'customer_email' => $usr->email,
                            'customer_phone' => '1234567890',
                        ],
                        'order_meta' => [
                            'return_url' => route('cashfree.payment.success') . "?order_id={order_id}&plan_id={$plan->id}&amount={$amount}&coupon=" . ($coupon?->code ?? 0),
                        ],
                    ]
                );
                $this->logExecutionTime($httpStart, $action, 'createCharge');
                if ($resp) {
                    Log::info("[{$base}::{$action}] redirecting to payment", ['link' => $resp->payment_link]);
                    return redirect()->to($resp->payment_link);
                }
                Log::error("[{$base}::{$action}] curl failed");
                return defaultUndefinedException($req, 'curl failed', $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] exception", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'plan_id' => $req->plan_id, 'coupon' => $req->coupon]);
    }

    public const CF_PAY_SCS = 'cashfreePaymentSuccess';
    public function cashfreePaymentSuccess(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['order_id' => $req->order_id, 'coupon' => $req->coupon, 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $usr = $req->user();
            $planFindStart = microtime(true);
            $plan = Plan::find($req->plan_id);
            $this->logExecutionTime($planFindStart, $action, 'findPlan');
            if ($r = self::guardPlan($plan, $req)) return $r;
            $cfgStart = microtime(true);
            self::paymentConfig();
            $this->logExecutionTime($cfgStart, $action, 'paymentConfig');
            $infoStart = microtime(true);
            $info = self::getPaymentInfo($req->order_id);
            $this->logExecutionTime($infoStart, $action, 'getPaymentInfo');
            if (!$info || $info->payment_status !== 'SUCCESS') {
                Log::warning("[{$base}::{$action}] payment failed", ['status' => $info?->payment_status]);
                return redirect()->route('plans.index')->with('error', __('Transaction failed.'));
            }
            DB::beginTransaction();
            try {
                $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
                $recStart = microtime(true);
                self::recordOrder($orderId, $usr, $plan, $req->amount, config('services.cashfree.currency'));
                $this->logExecutionTime($recStart, $action, 'recordOrder');
                if ($cid = $req->coupon) {
                    $coupStart = microtime(true);
                    self::attachCoupon($usr, $cid, $orderId);
                    $this->logExecutionTime($coupStart, $action, 'attachCoupon');
                }
                $assignStart = microtime(true);
                $assign = $usr->assignPlan($plan->id);
                $this->logExecutionTime($assignStart, $action, 'assignPlan');
                if (!$assign['is_success']) throw new \RuntimeException($assign['error']); // !
                $commitStart = microtime(true);
                DB::commit();
                $this->logExecutionTime($commitStart, $action, 'commitTransaction');
                Log::info("[{$base}::{$action}] plan activated", ['user_id' => $usr->id, 'plan_id' => $plan->id, 'order_id' => $orderId]);
                return redirect()->route('plans.index')->with('success', __('Plan successfully activated.'));
            } catch (\Throwable $e) {
                $rbStart = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($rbStart, $action, 'rollbackTransaction');
                Log::error("[{$base}::{$action}] transaction failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'order_id' => $req->order_id, 'coupon' => $req->coupon]);
    }

    public const INV_PAY_CF = 'invoicePayWithCashfree';
    public function invoicePayWithCashfree(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['invoice_id' => $req->invoice_id, 'amount' => $req->amount, 'method' => $method]);
            try {
                $decStart = microtime(true);
                $invId = Crypt::decrypt($req->invoice_id);
                $this->logExecutionTime($decStart, $action, 'decryptInvoiceId');
                $findStart = microtime(true);
                $invoice = Invoice::find($invId);
                $this->logExecutionTime($findStart, $action, 'findInvoice');
                if (!$invoice) {
                    Log::error("[{$base}::{$action}] invoice missing", ['invoice_id' => $invId]);
                    throw new \RuntimeException('invoice missing');
                }
                $usr = Auth::check() ? $req->user() : User::find($invoice->created_by);
                $cfgStart = microtime(true);
                self::paymentConfig(Utility::getCompanyPaymentSetting($usr->id));
                $this->logExecutionTime($cfgStart, $action, 'paymentConfig');
                $amount = $req->amount;
                if ($amount <= 0 || $amount > $invoice->getDue()) {
                    Log::warning("[{$base}::{$action}] invalid amount", ['amount' => $amount, 'due' => $invoice->getDue()]);
                    return redirect()->back()->with('error', __('Invalid amount.'));
                }
                $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
                $httpStart = microtime(true);
                $resp = self::curlPost(
                    config('services.cashfree.url'),
                    [
                        'Content-Type: application/json',
                        'x-api-version: 2022-01-01',
                        'x-client-id: ' . config('services.cashfree.key'),
                        'x-client-secret: ' . config('services.cashfree.secret'),
                    ],
                    [
                        'order_id' => $orderId,
                        'order_amount' => $amount,
                        'order_currency' => 'INR',
                        'order_name' => $invoice->name,
                        'customer_details' => [
                            'customer_id' => "customer_{$usr->id}",
                            'customer_name' => $usr->name,
                            'customer_email' => $usr->email,
                            'customer_phone' => '1234567890',
                        ],
                        'order_meta' => [
                            'return_url' => route(VW::INV . '.cashfree.payment.success') . "?order_id={order_id}&invoice_id={$invoice->id}&amount={$amount}",
                        ],
                    ]
                );
                $this->logExecutionTime($httpStart, $action, 'createCharge');
                if ($resp) {
                    Log::info("[{$base}::{$action}] redirecting to payment", ['link' => $resp->payment_link]);
                    return redirect()->to($resp->payment_link);
                }
                Log::error("[{$base}::{$action}] curl failed");
                return defaultUndefinedException($req, 'curl failed', $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] exception", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $req->invoice_id, 'amount' => $req->amount]);
    }

    public const GET_INV_PAY_STT = 'getInvoicePaymentStatus';
    public function getInvoicePaymentStatus(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['order_id' => $req->order_id, 'method' => $method]);
            $invId = $req->invoice_id;
            try {
                $findStart = microtime(true);
                $invoice = Invoice::find($invId);
                $this->logExecutionTime($findStart, $action, 'findInvoice');
                if (!$invoice) {
                    Log::error("[{$base}::{$action}] invoice missing", ['invoice_id' => $invId]);
                    throw new \RuntimeException('invoice missing');
                }
                $usr = User::find($invoice->created_by);
                $cfgStart = microtime(true);
                self::paymentConfig(Utility::getCompanyPaymentSetting($usr->id));
                $this->logExecutionTime($cfgStart, $action, 'paymentConfig');
                $infoStart = microtime(true);
                $info = self::getPaymentInfo($req->order_id);
                $this->logExecutionTime($infoStart, $action, 'getPaymentInfo');
                if (!$info || $info->payment_status !== 'SUCCESS') {
                    Log::warning("[{$base}::{$action}] payment failed", ['status' => $info?->payment_status]);
                    return redirect()->route(VW::INV . '.link.copy', Crypt::encrypt($invoice->id))->with('error', __('Transaction failed.'));
                }
                DB::beginTransaction();
                try {
                    $recStart = microtime(true);
                    self::recordInvoicePayment($invoice, $req->amount);
                    $this->logExecutionTime($recStart, $action, 'recordInvoicePayment');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('customer', $invoice->customer_id, $req->amount, 'debit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    $sessStart = microtime(true);
                    $req->session()->forget('invoice_data');
                    $this->logExecutionTime($sessStart, $action, 'forgetSession');
                    $commitStart = microtime(true);
                    DB::commit();
                    $this->logExecutionTime($commitStart, $action, 'commitTransaction');
                    Log::info("[{$base}::{$action}] invoice paid", ['invoice_id' => $invoice->id, 'amount' => $req->amount]);
                    return redirect()->route(VW::INV . '.link.copy', Crypt::encrypt($invoice->id))->with('success', __('Invoice paid successfully!'));
                } catch (\Throwable $e) {
                    $rbStart = microtime(true);
                    DB::rollBack();
                    $this->logExecutionTime($rbStart, $action, 'rollbackTransaction');
                    Log::error("[{$base}::{$action}] transaction failed", ['error' => $e->getMessage()]);
                    Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                    return defaultUndefinedException($req, $e, $class . '::' . $action);
                }
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] exception", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                $safeId = isset($invoice) ? $invoice->id : $invId;
                return redirect()->route(VW::INV . '.link.copy', Crypt::encrypt($safeId ?? ''))->with('error', $e->getMessage());
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'order_id' => $req->order_id, 'invoice_id' => $req->invoice_id]);
    }

    private static function curlPost(string $url, array $headers, array $body): ?object
    {
        Log::info(__METHOD__ . ' request', ['url' => $url, 'body' => $body]);
        try {
            $c = curl_init($url);
            curl_setopt_array($c, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => json_encode($body),
            ]);
            $r = curl_exec($c);
            curl_close($c);
            Log::info(__METHOD__ . ' response', ['response' => $r]);
            return json_decode($r);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private static function httpGet(string $url, array $headers): ?object
    {
        Log::info(__METHOD__ . ' request', ['url' => $url]);
        try {
            $c = new Client();
            $r = $c->request('GET', $url, ['headers' => $headers]);
            $b = $r->getBody();
            Log::info(__METHOD__ . ' response', ['status' => $r->getStatusCode()]);
            return json_decode($b);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private static function guardPlan(?Plan $plan, Request $req): ?RedirectResponse
    {
        if ($plan) {
            Log::info(__METHOD__ . ' plan found', ['plan_id' => $plan->id]);
            return null;
        }
        Log::warning(__METHOD__ . ' plan not found');
        return defaultPermissionDenial(
            $req,
            new AuthorizationException('plan not found'),
            __CLASS__ . '::' . __FUNCTION__,
            route('plans.index')
        );
    }


    private static function activateFreePlan(User $usr, Plan $p, Coupon $c): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', ['user_id' => $usr->id, 'plan_id' => $p->id, 'coupon' => $c->code]);
        DB::beginTransaction();
        try {
            $assign = $usr->assignPlan($p->id);
            if (!$assign['is_success']) {
                Log::error(__METHOD__ . ' assignPlan failed', ['error' => $assign['error']]);
                throw new \RuntimeException($assign['error']);
            }
            Log::info(__METHOD__ . ' plan assigned', ['user_id' => $usr->id, 'plan_id' => $p->id]);

            $id = strtoupper(str_replace('.', '', uniqid('', true)));
            UserCoupon::create([
                'user'   => $usr->id,
                'coupon' => $c->id,
                'order'  => $id
            ]);
            Log::info(__METHOD__ . ' coupon redeemed', ['coupon_id' => $c->id, 'order_id' => $id]);

            Order::create([
                'order_id'       => $id,
                'plan_name'      => $p->name,
                'plan_id'        => $p->id,
                'price'          => 0,
                'price_currency' => config('services.cashfree.currency'),
                'payment_type'   => 'Cashfree',
                'payment_status' => 'success',
                'user_id'        => $usr->id,
            ]);
            Log::info(__METHOD__ . ' free Order created', ['order_id' => $id]);

            DB::commit();
            Log::info(__METHOD__ . ' transaction committed', ['order_id' => $id]);

            return redirect()->route('plans.index')
                ->with('success', __('Plan successfully activated'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' transaction rolled back', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __($e->getMessage()));
        }
    }

    private static function getPaymentInfo(string $orderId): ?object
    {
        Log::info(__METHOD__ . ' fetching settlement', ['order_id' => $orderId]);
        $settle = self::httpGet(
            config('services.cashfree.url') . "/{$orderId}/settlements",
            [
                'accept: application/json',
                'x-api-version: 2022-09-01',
                'x-client-id: ' . config('services.cashfree.key'),
                'x-client-secret: ' . config('services.cashfree.secret'),
            ]
        );
        if (!$settle?->cf_payment_id) {
            Log::warning(__METHOD__ . ' no settlement payment_id', ['order_id' => $orderId]);
            return null;
        }

        Log::info(__METHOD__ . ' fetching payment', ['payment_id' => $settle->cf_payment_id]);
        $payment = self::httpGet(
            config('services.cashfree.url') . "/{$settle->order_id}/payments/{$settle->cf_payment_id}",
            [
                'accept: application/json',
                'x-api-version: 2022-09-01',
                'x-client-id: ' . config('services.cashfree.key'),
                'x-client-secret: ' . config('services.cashfree.secret'),
            ]
        );
        Log::info(__METHOD__ . ' payment info retrieved', ['status' => $payment?->payment_status]);
        return $payment;
    }

    private static function recordOrder(
        string $id,
        User $usr,
        Plan $plan,
        float $amount,
        string $curr
    ): void {
        Log::info(__METHOD__ . ' recording Order', [
            'order_id' => $id,
            'user_id'  => $usr->id,
            'plan_id'  => $plan->id,
            'amount'   => $amount,
            'currency' => $curr
        ]);
        Order::create([
            'order_id'       => $id,
            'name'           => $usr->name,
            'plan_name'      => $plan->name,
            'plan_id'        => $plan->id,
            'price'          => $amount,
            'price_currency' => $curr,
            'payment_type'   => 'Cashfree',
            'payment_status' => 'success',
            'user_id'        => $usr->id,
        ]);
        Log::info(__METHOD__ . ' Order recorded', ['order_id' => $id]);
    }

    private static function attachCoupon(User $usr, string $code, string $oid): void
    {
        Log::info(__METHOD__ . ' attaching coupon', ['user_id' => $usr->id, 'code' => $code]);
        $c = Coupon::where('code', strtoupper($code))
            ->where('is_active', 1)
            ->first();
        if (!$c) {
            Log::warning(__METHOD__ . ' coupon not found or inactive', ['code' => $code]);
            return;
        }

        UserCoupon::create([
            'user'   => $usr->id,
            'coupon' => $c->id,
            'order'  => $oid
        ]);
        Log::info(__METHOD__ . ' coupon attached', ['coupon_id' => $c->id, 'order_id' => $oid]);

        if ($c->limit <= $c->used_coupon()) {
            $c->is_active = 0;
            $c->save();
            Log::info(__METHOD__ . ' coupon deactivated', ['coupon_id' => $c->id]);
        }
    }

    private static function recordInvoicePayment(Invoice $inv, float $amt): void
    {
        Log::info(__METHOD__ . ' recording invoice payment', [
            'invoice_id' => $inv->id,
            'amount'     => $amt
        ]);
        $ip = new InvoicePayment();
        foreach (
            [
                'invoice_id'     => $inv->id,
                'date'           => date('Y-m-d'),
                'amount'         => $amt,
                'account_id'     => 0,
                'payment_method' => 0,
                'order_id'       => uniqid(),
                'payment_type'   => 'Cashfree',
                'receipt'        => '',
                'reference'      => '',
                'description'    => 'Invoice ' . Utility::invoiceNumberFormat(
                    Utility::settingsById($inv->created_by),
                    $inv->invoice_id
                ),
            ] as $k => $v
        ) {
            $ip->$k = $v;
        }
        $ip->save();
        Log::info(__METHOD__ . ' InvoicePayment saved', ['payment_id' => $ip->id]);

        $newStatus = $inv->getDue() - $amt === 0 ? 3 : 2;
        Invoice::changeStatus($inv->id, $newStatus);
        Log::info(__METHOD__ . ' invoice status changed', [
            'invoice_id' => $inv->id,
            'new_status' => $newStatus
        ]);
    }
}
