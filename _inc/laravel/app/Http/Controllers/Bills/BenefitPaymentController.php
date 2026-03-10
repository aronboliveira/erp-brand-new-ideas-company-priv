<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Models\{
    Coupon,
    Invoice,
    InvoiceBankTransfer,
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
use Illuminate\Http\{
    Request,
    RedirectResponse,
    JsonResponse
};
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Route,
    Log,
    View as ViewFacade
};
use Throwable;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class BenefitPaymentController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public const INI_PAY = 'initiatePayment';
    public function initiatePayment(Request $req): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id(), 'plan_id' => $req->input('plan_id'), 'coupon_raw' => $req->input('coupon'), 'method' => $method]);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            if ($r = self::authorizePerm($req, 'buy plan')) return $r;
            try {
                $cfgStart = microtime(true);
                $settings = Utility::getAdminPaymentSetting();
                $secret   = $settings['benefit_secret_key'] ?? '';
                $this->logExecutionTime($cfgStart, $action, 'getAdminPaymentSetting');
                $user   = $req->user();
                $decStart = microtime(true);
                $planId = Crypt::decryptString($req->input('plan_id'));
                $this->logExecutionTime($decStart, $action, 'decryptPlanId');
                $planStart = microtime(true);
                $plan   = Plan::findOrFail($planId);
                $this->logExecutionTime($planStart, $action, 'findPlan');
                $amount = $plan->price;
                $couponCode = strtoupper($req->input('coupon', ''));
                Log::info("[{$base}::{$action}] plan lookup", ['plan_id' => $planId, 'price' => $amount]);
                if ($couponCode) {
                    $cpFetchStart = microtime(true);
                    $coupon = Coupon::where('code', $couponCode)->where('is_active', 1)->first();
                    $this->logExecutionTime($cpFetchStart, $action, 'fetchCoupon');
                    if (!$coupon) {
                        Log::warning("[{$base}::{$action}] invalid coupon", ['code' => $couponCode]);
                        return back()->with('error', __('This coupon code is invalid or expired.'));
                    }
                    if ($coupon->limit <= $coupon->used_coupon) {
                        Log::warning("[{$base}::{$action}] expired coupon", ['code' => $couponCode]);
                        return back()->with('error', __('This coupon code has expired.'));
                    }
                    $applyStart = microtime(true);
                    $discount = ($plan->price * ($coupon->discount / 100));
                    $amount -= $discount;
                    $this->logExecutionTime($applyStart, $action, 'applyCoupon');
                    Log::info("[{$base}::{$action}] coupon applied", ['code' => $couponCode, 'discount' => $discount, 'new_amount' => $amount]);
                    if ($amount <= 0) {
                        DB::beginTransaction();
                        try {
                            $freeStart = microtime(true);
                            $user?->assignPlan($plan->id);
                            UserCoupon::create(['user' => $user?->id, 'coupon' => $coupon->id, 'order' => self::txnId()]);
                            $coupon->update(['is_active' => 0]);
                            $this->logExecutionTime($freeStart, $action, 'activateFreePlan');
                            DB::commit();
                            Log::info("[{$base}::{$action}] free plan activated via coupon", [UsersConstants::COL_USER_ID => $user?->id, 'plan_id' => $plan->id]);
                            return redirect()->route(ViewsConstants::PLN . '.index')->with('success', __('Plan successfully activated.'));
                        } catch (Throwable $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }
                }
                $payload = [
                    'amount' => $amount,
                    'currency' => $settings['currency'] ?? 'BHD',
                    'customer_initiated' => true,
                    'threeDSecure' => true,
                    'save_card' => false,
                    'description' => 'Plan - ' . $plan->name,
                    'metadata' => ['udf1' => 'Metadata 1'],
                    'reference' => ['transaction' => 'txn_01', 'order' => 'ord_01'],
                    'receipt' => ['email' => true, 'sms' => true],
                    'customer' => ['first_name' => $user?->name, 'email' => $user?->email, 'phone' => ['country_code' => 965, 'number' => 51234567]],
                    'source' => ['id' => 'src_bh.benefit'],
                    'redirect' => ['url' => route('benefit.callback', ['plan' => $plan->id, 'amount' => $amount, 'coupon' => $couponCode ?: '0'])],
                ];
                Log::info("[{$base}::{$action}] sending charge request", ['payload' => $payload]);
                $httpStart = microtime(true);
                $response = self::client()->post('https://api.tap.company/v2/charges', ['json' => $payload, 'headers' => ['Authorization' => 'Bearer ' . $secret, 'Accept' => 'application/json']]);
                $this->logExecutionTime($httpStart, $action, 'initiateCharge');
                $data = json_decode($response->getBody()->getContents());
                Log::info("[{$base}::{$action}] charge initiated", ['transaction_url' => $data->transaction->url]);
                return redirect($data->transaction->url);
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return self::handleException($req, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'plan_id' => $req->input('plan_id'), 'coupon' => $req->input('coupon')]);
    }

    public const CB = 'callBack';
    public function callBack(Request $req): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $req;
        return $this->measureProfile($action, function () use ($request, $action, $method, $base) {
            Log::info("[{$base}::{$action}] callback start", ['tap_id' => $request->input('tap_id'), 'plan' => $request->input('plan'), 'coupon' => $request->input('coupon'), 'method' => $method]);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            try {
                $cfgStart = microtime(true);
                $settings = Utility::getAdminPaymentSetting();
                $secret = $settings['benefit_secret_key'] ?? '';
                $plan = Plan::findOrFail($request->input('plan'));
                $user = $request->user();
                $couponCode = $request->input('coupon') !== '0' ? $request->input('coupon') : null;
                $this->logExecutionTime($cfgStart, $action, 'prepareCallback');
                $httpStart = microtime(true);
                $resp = self::client()->get('https://api.tap.company/v2/charges/' . $request->input('tap_id'), ['headers' => ['Authorization' => 'Bearer ' . $secret, 'Accept' => 'application/json']]);
                $this->logExecutionTime($httpStart, $action, 'tapChargeGet');
                $parseStart = microtime(true);
                $status = json_decode($resp->getBody()->getContents());
                $this->logExecutionTime($parseStart, $action, 'parseTapStatus');
                Log::info("[{$base}::{$action}] charge status", ['code' => $status->gateway->response->code]);
                if ($status->gateway->response->code !== '00') {
                    Log::warning("[{$base}::{$action}] transaction failed", ['code' => $status->gateway->response->code]);
                    Log::debug("[{$base}::{$action}] failure context", ['tap_id' => $request->input('tap_id'), 'plan_id' => $plan->id, 'user_id' => $user?->id]);
                    return redirect()->route(ViewsConstants::PLN . '.index')->with('error', __('Transaction failed, please try again.'));
                }
                $txnStart = microtime(true);
                DB::beginTransaction();
                try {
                    $orderId = self::txnId();
                    $createStart = microtime(true);
                    Order::create([
                        'order_id' => $orderId,
                        'plan_name' => $plan->name,
                        'plan_id' => $plan->id,
                        'price' => $request->input('amount'),
                        'price_currency' => Utility::getValByName('currency'),
                        'payment_type' => 'Benefit',
                        'payment_status' => 'success',
                        UsersConstants::COL_USER_ID => $user?->id,
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createOrder');
                    $assignStart = microtime(true);
                    $user?->assignPlan($plan->id);
                    $this->logExecutionTime($assignStart, $action, 'assignPlan');
                    Log::info("[{$base}::{$action}] plan assigned", [UsersConstants::COL_USER_ID => $user?->id, 'plan_id' => $plan->id]);
                    if ($couponCode) {
                        $cStart = microtime(true);
                        $coupon = Coupon::where('code', $couponCode)->first();
                        $this->logExecutionTime($cStart, $action, 'fetchCoupon');
                        if ($coupon) {
                            $ucStart = microtime(true);
                            UserCoupon::create(['user' => $user?->id, 'coupon' => $coupon->id, 'order' => $orderId]);
                            $this->logExecutionTime($ucStart, $action, 'createUserCoupon');
                            if ($coupon->limit <= $coupon->used_coupon) {
                                $deactStart = microtime(true);
                                $coupon->update(['is_active' => 0]);
                                $this->logExecutionTime($deactStart, $action, 'deactivateCoupon');
                            }
                            Log::info("[{$base}::{$action}] coupon attached", ['coupon' => $couponCode, 'order' => $orderId]);
                        }
                    }
                    DB::commit();
                    $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                    return redirect()->route(ViewsConstants::PLN . '.index')->with('success', __('Plan activated successfully.'));
                } catch (Throwable $e) {
                    DB::rollBack();
                    $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                    Log::error("[{$base}::{$action}] callback failed", ['error' => $e->getMessage()]);
                    Log::debug("[{$base}::{$action}] callback failure context", ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'user_id' => $user?->id, 'plan_id' => $plan->id]);
                    throw $e;
                }
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] exception", ['message' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return self::handleException($request, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const INV_PAY_BF = 'invoicePayWithBenefit';
    public function invoicePayWithBenefit(Request $req): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $req;
        return $this->measureProfile($action, function () use ($request, $action, $method, $base) {
            Log::info("[{$base}::{$action}] start", ['invoice_id_enc' => $request->input('invoice_id'), 'amount' => $request->input('amount'), 'method' => $method]);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            try {
                $decStart = microtime(true);
                $invoiceId = Crypt::decryptString($request->input('invoice_id'));
                $this->logExecutionTime($decStart, $action, 'decryptInvoiceId');
                $invStart = microtime(true);
                $invoice = Invoice::findOrFail($invoiceId);
                $this->logExecutionTime($invStart, $action, 'findInvoice');
                $cfgStart = microtime(true);
                $settings = Utility::getCompanyPaymentSetting($invoice[DatabaseConstants::COL_TABLE_CREATOR]);
                $secret = $settings['benefit_secret_key'] ?? '';
                $this->logExecutionTime($cfgStart, $action, 'getCompanyPaymentSetting');
                $amount = (float) $request->input('amount');
                Log::info("[{$base}::{$action}] invoice lookup", ['invoice_id' => $invoiceId, 'due' => $invoice->getDue()]);
                if ($amount <= 0 || $amount > $invoice->getDue()) {
                    Log::warning("[{$base}::{$action}] invalid amount", ['amount' => $amount]);
                    Log::debug("[{$base}::{$action}] invalid amount context", ['invoice_id' => $invoiceId, 'due' => $invoice->getDue(), 'user_id' => $request->user()?->id]);
                    return back()->with('error', __('Invalid amount.'));
                }
                $payer = Auth::check() ? Auth::user() : User::find($invoice[DatabaseConstants::COL_TABLE_CREATOR]);
                $charge = [
                    'amount' => $amount,
                    'currency' => Utility::settingsById($invoice[DatabaseConstants::COL_TABLE_CREATOR])['site_currency'] ?? 'BHD',
                    'customer_initiated' => true,
                    'threeDSecure' => true,
                    'save_card' => false,
                    'description' => $invoice->invoice_id,
                    'metadata' => ['udf1' => 'Metadata 1'],
                    'reference' => ['transaction' => 'txn_01', 'order' => 'ord_01'],
                    'receipt' => ['email' => true, 'sms' => true],
                    'customer' => ['first_name' => $payer->name, 'email' => $payer->email, 'phone' => ['country_code' => 965, 'number' => 51234567]],
                    'source' => ['id' => 'src_bh.benefit'],
                    'redirect' => ['url' => route(ViewsConstants::INV . 'benefit.status', ['invoice' => $request->input('invoice_id'), 'amount' => $amount])],
                ];
                Log::info("[{$base}::{$action}] sending invoice charge", ['payload' => $charge]);
                $httpStart = microtime(true);
                $response = self::client()->post('https://api.tap.company/v2/charges', ['json' => $charge, 'headers' => ['Authorization' => 'Bearer ' . $secret, 'Accept' => 'application/json']]);
                $this->logExecutionTime($httpStart, $action, 'tapChargePost');
                $parseStart = microtime(true);
                $data = json_decode($response->getBody()->getContents());
                $this->logExecutionTime($parseStart, $action, 'parseTapResponse');
                Log::info("[{$base}::{$action}] invoice charge initiated", ['transaction_url' => $data->transaction->url]);
                return redirect($data->transaction->url);
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] exception", ['message' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'invoice_id_enc' => $request->input('invoice_id')]);
                return self::handleException($request, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const GET_INV_PAY_STT = 'getInvoicePaymentStatus';
    public function getInvoicePaymentStatus(Request $req, string $invoiceEncrypted, string $amount): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $req;
        return $this->measureProfile($action, function () use ($request, $invoiceEncrypted, $amount, $action, $method, $base) {
            Log::info("[{$base}::{$action}] start", ['invoice_enc' => $invoiceEncrypted, 'tap_id' => $request->input('tap_id'), 'amount' => $amount, 'method' => $method]);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            try {
                $decStart = microtime(true);
                $invoiceId = Crypt::decryptString($invoiceEncrypted);
                $this->logExecutionTime($decStart, $action, 'decryptInvoiceId');
                $invStart = microtime(true);
                $invoice = Invoice::findOrFail($invoiceId);
                $this->logExecutionTime($invStart, $action, 'findInvoice');
                $usrStart = microtime(true);
                $user = User::findOrFail($invoice[DatabaseConstants::COL_TABLE_CREATOR]);
                $this->logExecutionTime($usrStart, $action, 'findUser');
                $cfgStart = microtime(true);
                $secret = Utility::getCompanyPaymentSetting($user?->id)['benefit_secret_key'] ?? '';
                $this->logExecutionTime($cfgStart, $action, 'getCompanyPaymentSetting');
                $httpStart = microtime(true);
                $resp = self::client()->get('https://api.tap.company/v2/charges/' . $request->input('tap_id'), ['headers' => ['Authorization' => 'Bearer ' . $secret, 'Accept' => 'application/json']]);
                $this->logExecutionTime($httpStart, $action, 'tapChargeGet');
                $parseStart = microtime(true);
                $status = json_decode($resp->getBody()->getContents());
                $this->logExecutionTime($parseStart, $action, 'parseTapStatus');
                Log::info("[{$base}::{$action}] payment status", ['code' => $status->gateway->response->code]);
                if ($status->gateway->response->code !== '00') {
                    Log::warning("[{$base}::{$action}] transaction failed", ['invoice_id' => $invoiceId]);
                    Log::debug("[{$base}::{$action}] failure context", ['tap_id' => $request->input('tap_id'), 'user_id' => $user?->id, 'secret_set' => !empty($secret)]);
                    return redirect()->route(ViewsConstants::INV . 'link.copy', $invoiceEncrypted)->with('error', __('Transaction failed!'));
                }
                $txnStart = microtime(true);
                DB::beginTransaction();
                try {
                    $orderId = self::txnId();
                    $ipStart = microtime(true);
                    InvoicePayment::create([
                        'invoice_id' => $invoice->id,
                        'date' => now()->toDateString(),
                        'amount' => $amount,
                        'account_id' => 0,
                        'payment_method' => 0,
                        'order_id' => $orderId,
                        'payment_type' => 'Benefit',
                        'description' => 'Invoice ' . Utility::invoiceNumberFormat(Utility::settingsById($invoice[DatabaseConstants::COL_TABLE_CREATOR]), $invoice->invoice_id),
                    ]);
                    $this->logExecutionTime($ipStart, $action, 'createInvoicePayment');
                    $stStart = microtime(true);
                    $newDue = $invoice->getDue() - (float) $amount;
                    Invoice::changeStatus($invoice->id, $newDue ? 2 : 3);
                    $this->logExecutionTime($stStart, $action, 'updateInvoiceStatus');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('customer', $invoice->customer_id, $amount, 'debit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    $delStart = microtime(true);
                    InvoiceBankTransfer::where('invoice_id', $invoice->id)->where('order_id', $orderId)->delete();
                    $this->logExecutionTime($delStart, $action, 'cleanupTransfer');
                    DB::commit();
                    $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                    Log::info("[{$base}::{$action}] invoice payment recorded", ['invoice_id' => $invoiceId, 'order_id' => $orderId]);
                    return redirect()->route(ViewsConstants::INV . 'link.copy', $invoiceEncrypted)->with('success', __('Invoice paid successfully!'));
                } catch (Throwable $e) {
                    DB::rollBack();
                    $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                    Log::error("[{$base}::{$action}] recording failed", ['error' => $e->getMessage(), 'invoice_id' => $invoiceId]);
                    Log::debug("[{$base}::{$action}] recording failure context", ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'amount' => $amount, 'user_id' => $user?->id]);
                    throw $e;
                }
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['err' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'invoice_enc' => $invoiceEncrypted, 'tap_id' => $request->input('tap_id')]);
                return redirect()->route(ViewsConstants::INV . 'link.copy', $invoiceEncrypted)->with('error', $e->getMessage());
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_enc' => $invoiceEncrypted, 'amount' => $amount]);
    }

    private static function authorizePerm(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        $user = $req->user();
        if ($user?->can($perm)) {
            Log::info(__METHOD__ . " permission granted", [
                UsersConstants::COL_USER_ID => $user?->id,
                'perm' => $perm
            ]);
            return null;
        }
        Log::warning(__METHOD__ . " permission denied", [
            UsersConstants::COL_USER_ID => $user?->id,
            'perm' => $perm
        ]);
        return defaultPermissionDenial(
            $req,
            new AuthorizationException($perm),
            __CLASS__ . '::' . __FUNCTION__
        );
    }

    private static function handleException(Request $req, Throwable $e): RedirectResponse|JsonResponse|null
    {
        Log::error(__METHOD__ . " exception", [
            'message' => $e->getMessage(),
        ]);
        Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . " exception", [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);
        return defaultUndefinedException(
            $req,
            $e,
            __CLASS__ . '::' . __FUNCTION__
        );
    }

    private static function client(): Client
    {
        return new Client(['timeout' => 15]);
    }

    private static function txnId(): string
    {
        return strtoupper(str_replace('.', '', uniqid('', true)));
    }
}

// ! ALERT secret_key is stored in plain‑text settings and sent back in exceptions; avoid exposing it and consider moving to env‑encrypted vault.