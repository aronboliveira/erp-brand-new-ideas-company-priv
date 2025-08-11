<?php

namespace App\Http\Controllers;

use App\Config\Constants\{MiddlewaresConstants, ViewsConstants};
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
use GuzzleHttp\Client;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Log
};

final class CashfreeController extends Controller
{

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
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

    public function cashfreePaymentStore(Request $req): RedirectResponse
    {

        Log::info(__METHOD__ . ' start', [
            'user_id' => Auth::id(),
            'plan_id' => $req->plan_id,
            'coupon'  => $req->coupon
        ]);
        try {
            $plan = Plan::find(Crypt::decrypt($req->plan_id));
            if ($r = self::guardPlan($plan, $req)) return $r;
            $usr = $req->user();
            self::paymentConfig();
            $amount = $plan->price;
            $coupon = Coupon::where('code', strtoupper($req->coupon ?? ''))
                ->where('is_active', 1)->first();
            if ($coupon) {
                if ($coupon->limit <= $coupon->used_coupon()) {
                    Log::warning(__METHOD__ . ' coupon expired', ['code' => $coupon->code]);
                    return redirect()->back()
                        ->with('error', __('This coupon code has expired.'));
                }
                $discount = ($plan->price / 100) * $coupon->discount;
                $amount -= $discount;
                Log::info(__METHOD__ . ' coupon applied', [
                    'code'     => $coupon->code,
                    'discount' => $discount,
                    'amount'   => $amount
                ]);
                if ($amount <= 0) {
                    return self::activateFreePlan($usr, $plan, $coupon);
                }
            }
            $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
            $resp = self::curlPost(
                config('services.cashfree.url'),
                [
                    'Content-Type: application/json',
                    'x-api-version: 2022-01-01',
                    'x-client-id: ' . config('services.cashfree.key'),
                    'x-client-secret: ' . config('services.cashfree.secret'),
                ],
                [
                    'order_id'        => $orderId,
                    'order_amount'    => $amount,
                    'order_currency'  => config('services.cashfree.currency'),
                    'order_name'      => $plan->name,
                    'customer_details' => [
                        'customer_id'    => "customer_{$usr->id}",
                        'customer_name'  => $usr->name,
                        'customer_email' => $usr->email,
                        'customer_phone' => '1234567890',
                    ],
                    'order_meta'      => [
                        'return_url' => route('cashfree.payment.success')
                            . "?order_id={order_id}&plan_id={$plan->id}&amount={$amount}"
                            . "&coupon=" . ($coupon?->code ?? 0),
                    ],
                ]
            );
            if ($resp) {
                Log::info(__METHOD__ . ' redirecting to payment', [
                    'link' => $resp->payment_link
                ]);
                return redirect()->to($resp->payment_link);
            }
            Log::error(__METHOD__ . ' curl failed');
            return defaultUndefinedException($req, 'curl failed', __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function cashfreePaymentSuccess(Request $req): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [
            'user_id' => Auth::id(),
            'order_id' => $req->order_id,
            'coupon'  => $req->coupon
        ]);
        try {
            $usr = $req->user();
            $plan = Plan::find($req->plan_id);
            if ($r = self::guardPlan($plan, $req)) return $r;
            self::paymentConfig();
            $info = self::getPaymentInfo($req->order_id);
            if (!$info || $info->payment_status !== 'SUCCESS') {
                Log::warning(__METHOD__ . ' payment failed', ['status' => $info?->payment_status]);
                return redirect()->route('plans.index')
                    ->with('error', __('Transaction failed.'));
            }
            DB::beginTransaction();
            try {
                $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
                self::recordOrder(
                    $orderId,
                    $usr,
                    $plan,
                    $req->amount,
                    config('services.cashfree.currency')
                );
                if ($cid = $req->coupon) {
                    self::attachCoupon($usr, $cid, $orderId);
                }
                $assign = $usr->assignPlan($plan->id);
                if (!$assign['is_success']) {
                    throw new \RuntimeException($assign['error']);
                }
                DB::commit();
                Log::info(__METHOD__ . ' plan activated', [
                    'user_id' => $usr->id,
                    'plan_id' => $plan->id
                ]);
                return redirect()->route('plans.index')
                    ->with('success', __('Plan successfully activated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error(__METHOD__ . ' transaction failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
            }
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const INV_PAY_CF = 'invoicePayWithCashfree';
    public function invoicePayWithCashfree(Request $req): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [
            'invoice_id' => $req->invoice_id,
            'amount' => $req->amount
        ]);
        try {
            $invoice = Invoice::find(Crypt::decrypt($req->invoice_id));
            if (!$invoice) {
                Log::error(__METHOD__ . ' invoice missing');
                throw new \RuntimeException('invoice missing');
            }
            $usr = Auth::check() ? $req->user() : User::find($invoice->created_by);
            self::paymentConfig(Utility::getCompanyPaymentSetting($usr->id));
            $amount = $req->amount;
            if ($amount <= 0 || $amount > $invoice->getDue()) {
                Log::warning(__METHOD__ . ' invalid amount', ['amount' => $amount]);
                return redirect()->back()->with('error', __('Invalid amount.'));
            }
            $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
            $resp = self::curlPost(
                config('services.cashfree.url'),
                [
                    'Content-Type: application/json',
                    'x-api-version: 2022-01-01',
                    'x-client-id: ' . config('services.cashfree.key'),
                    'x-client-secret: ' . config('services.cashfree.secret'),
                ],
                [
                    'order_id'        => $orderId,
                    'order_amount'    => $amount,
                    'order_currency'  => 'INR',
                    'order_name'      => $invoice->name,
                    'customer_details' => [
                        'customer_id'    => "customer_{$usr->id}",
                        'customer_name'  => $usr->name,
                        'customer_email' => $usr->email,
                        'customer_phone' => '1234567890',
                    ],
                    'order_meta'      => [
                        'return_url' => route(ViewsConstants::INV . '.cashfree.payment.success')
                            . "?order_id={order_id}&invoice_id={$invoice->id}"
                            . "&amount={$amount}",
                    ],
                ]
            );
            if ($resp) {
                Log::info(__METHOD__ . ' redirecting to payment', [
                    'link' => $resp->payment_link
                ]);
                return redirect()->to($resp->payment_link);
            }
            Log::error(__METHOD__ . ' curl failed');
            return defaultUndefinedException($req, 'curl failed', __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const GET_INV_PAY_STT = 'getInvoicePaymentStatus';
    public function getInvoicePaymentStatus(Request $req): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', ['order_id' => $req->order_id]);
        try {
            $invoice = Invoice::find($req->invoice_id);
            if (!$invoice) {
                Log::error(__METHOD__ . ' invoice missing');
                throw new \RuntimeException('invoice missing');
            }
            $usr = User::find($invoice->created_by);
            self::paymentConfig(Utility::getCompanyPaymentSetting($usr->id));
            $info = self::getPaymentInfo($req->order_id);
            if (!$info || $info->payment_status !== 'SUCCESS') {
                Log::warning(__METHOD__ . ' payment failed', ['status' => $info?->payment_status]);
                return redirect()->route(ViewsConstants::INV . '.link.copy', Crypt::encrypt($invoice->id))
                    ->with('error', __('Transaction failed.'));
            }
            DB::beginTransaction();
            try {
                self::recordInvoicePayment($invoice, $req->amount);
                Utility::updateUserBalance('customer', $invoice->customer_id, $req->amount, 'debit');
                $req->session()->forget('invoice_data');
                DB::commit();
                Log::info(__METHOD__ . ' invoice paid', [
                    'invoice_id' => $invoice->id,
                    'amount' => $req->amount
                ]);
                return redirect()->route(ViewsConstants::INV . '.link.copy', Crypt::encrypt($invoice->id))
                    ->with('success', __('Invoice paid successfully!'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error(__METHOD__ . ' transaction failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
            }
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return redirect()->route(ViewsConstants::INV . '.link.copy', Crypt::encrypt($invoice->id ?? ''))
                ->with('error', $e->getMessage());
        }
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
