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
use Illuminate\Http\{
    Request,
    RedirectResponse,
    JsonResponse
};
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Log
};
use Throwable;

final class BenefitPaymentController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function initiatePayment(Request $req): RedirectResponse|JsonResponse|null
    {
        Log::info(__METHOD__ . " start", [
            UsersConstants::COL_USER_ID    => Auth::id(),
            'plan_id'    => $req->input('plan_id'),
            'coupon_raw' => $req->input('coupon')
        ]);
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if ($r = self::authorizePerm($req, 'buy plan')) return $r;

        try {
            $settings  = Utility::getAdminPaymentSetting();
            $secret    = $settings['benefit_secret_key'] ?? '';
            $user      = $req->user();
            $planId    = Crypt::decryptString($req->input('plan_id'));
            $plan      = Plan::findOrFail($planId);
            $amount    = $plan->price;
            $couponCode = strtoupper($req->input('coupon', ''));
            Log::info(__METHOD__ . " plan lookup", [
                'plan_id' => $planId,
                'price' => $amount
            ]);
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)
                    ->where('is_active', 1)
                    ->first();
                if (!$coupon) {
                    Log::warning(__METHOD__ . " invalid coupon", ['code' => $couponCode]);
                    return back()->with('error', __('This coupon code is invalid or expired.'));
                }
                if ($coupon->limit <= $coupon->used_coupon) {
                    Log::warning(__METHOD__ . " expired coupon", ['code' => $couponCode]);
                    return back()->with('error', __('This coupon code has expired.'));
                }
                $discount = ($plan->price * ($coupon->discount / 100));
                $amount -= $discount;
                Log::info(__METHOD__ . " coupon applied", [
                    'code' => $couponCode,
                    'discount' => $discount,
                    'new_amount' => $amount
                ]);
                if ($amount <= 0) {
                    DB::beginTransaction();
                    try {
                        $user?->assignPlan($plan->id);
                        UserCoupon::create([
                            'user'   => $user?->id,
                            'coupon' => $coupon->id,
                            'order'  => self::txnId(),
                        ]);
                        $coupon->update(['is_active' => 0]);
                        DB::commit();
                        Log::info(__METHOD__ . " free plan activated via coupon", [
                            UsersConstants::COL_USER_ID => $user?->id,
                            'plan_id' => $plan->id
                        ]);
                        return redirect()->route(ViewsConstants::PLN . '.index')
                            ->with('success', __('Plan successfully activated.'));
                    } catch (Throwable $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }
            }

            $payload = [
                'amount'            => $amount,
                'currency'          => $settings['currency'] ?? 'BHD',
                'customer_initiated' => true,
                'threeDSecure'      => true,
                'save_card'         => false,
                'description'       => 'Plan - ' . $plan->name,
                'metadata'          => ['udf1' => 'Metadata 1'],
                'reference'         => ['transaction' => 'txn_01', 'order' => 'ord_01'],
                'receipt'           => ['email' => true, 'sms' => true],
                'customer'          => [
                    'first_name' => $user?->name,
                    'email'      => $user?->email,
                    'phone'      => ['country_code' => 965, 'number' => 51234567]
                ],
                'source'            => ['id' => 'src_bh.benefit'],
                'redirect'          => [
                    'url' => route('benefit.callback', [
                        'plan'   => $plan->id,
                        'amount' => $amount,
                        'coupon' => $couponCode ?: '0'
                    ]),
                ],
            ];
            Log::info(__METHOD__ . " sending charge request", ['payload' => $payload]);
            $response = self::client()->post(
                'https://api.tap.company/v2/charges',
                ['json' => $payload, 'headers' => [
                    'Authorization' => 'Bearer ' . $secret,
                    'Accept'        => 'application/json'
                ]]
            );
            $data = json_decode($response->getBody()->getContents());
            Log::info(__METHOD__ . " charge initiated", [
                'transaction_url' => $data->transaction->url
            ]);
            return redirect($data->transaction->url);
        } catch (Throwable $e) {
            return self::handleException($req, $e);
        }
    }

    public function callBack(Request $req): RedirectResponse|JsonResponse|null
    {
        Log::info(__METHOD__ . " callback start", [
            'tap_id'     => $req->input('tap_id'),
            'plan'       => $req->input('plan'),
            'coupon'     => $req->input('coupon')
        ]);
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;

        try {
            $settings  = Utility::getAdminPaymentSetting();
            $secret    = $settings['benefit_secret_key'] ?? '';
            $plan      = Plan::findOrFail($req->input('plan'));
            $user      = $req->user();
            $couponCode = $req->input('coupon') !== '0' ? $req->input('coupon') : null;

            $resp = self::client()->get(
                'https://api.tap.company/v2/charges/' . $req->input('tap_id'),
                ['headers' => [
                    'Authorization' => 'Bearer ' . $secret,
                    'Accept'        => 'application/json'
                ]]
            );
            $status = json_decode($resp->getBody()->getContents());
            Log::info(__METHOD__ . " charge status", [
                'code' => $status->gateway->response->code
            ]);

            if ($status->gateway->response->code !== '00') {
                Log::warning(__METHOD__ . " transaction failed", [
                    'code' => $status->gateway->response->code
                ]);
                return redirect()->route(ViewsConstants::PLN . '.index')
                    ->with('error', __('Transaction failed, please try again.'));
            }

            DB::beginTransaction();
            try {
                $orderId = self::txnId();
                Order::create([
                    'order_id'       => $orderId,
                    'plan_name'      => $plan->name,
                    'plan_id'        => $plan->id,
                    'price'          => $req->input('amount'),
                    'price_currency' => Utility::getValByName('currency'),
                    'payment_type'   => 'Benefit',
                    'payment_status' => 'success',
                    UsersConstants::COL_USER_ID        => $user?->id,
                ]);
                $user?->assignPlan($plan->id);
                Log::info(__METHOD__ . " plan assigned", [
                    UsersConstants::COL_USER_ID => $user?->id,
                    'plan_id' => $plan->id
                ]);

                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)->first();
                    if ($coupon) {
                        UserCoupon::create([
                            'user'   => $user?->id,
                            'coupon' => $coupon->id,
                            'order'  => $orderId,
                        ]);
                        if ($coupon->limit <= $coupon->used_coupon) {
                            $coupon->update(['is_active' => 0]);
                        }
                        Log::info(__METHOD__ . " coupon attached", [
                            'coupon' => $couponCode,
                            'order' => $orderId
                        ]);
                    }
                }

                DB::commit();
                return redirect()->route(ViewsConstants::PLN . '.index')
                    ->with('success', __('Plan activated successfully.'));
            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Throwable $e) {
            return self::handleException($req, $e);
        }
    }

    public const INV_PAY_BF = 'invoicePayWithBenefit';
    public function invoicePayWithBenefit(Request $req): RedirectResponse|JsonResponse|null
    {
        Log::info(__METHOD__ . " start", [
            'invoice_id_enc' => $req->input('invoice_id'),
            'amount'         => $req->input('amount')
        ]);
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;

        try {
            $invoiceId = Crypt::decryptString($req->input('invoice_id'));
            $invoice  = Invoice::findOrFail($invoiceId);
            $settings = Utility::getCompanyPaymentSetting($invoice[DatabaseConstants::TABLE_CREATOR]);
            $secret   = $settings['benefit_secret_key'] ?? '';
            $amount   = (float) $req->input('amount');

            Log::info(__METHOD__ . " invoice lookup", [
                'invoice_id' => $invoiceId,
                'due'        => $invoice->getDue()
            ]);

            if ($amount <= 0 || $amount > $invoice->getDue()) {
                Log::warning(__METHOD__ . " invalid amount", ['amount' => $amount]);
                return back()->with('error', __('Invalid amount.'));
            }

            $payer = Auth::check() ? Auth::user() : User::find($invoice[DatabaseConstants::TABLE_CREATOR]);
            $charge = [
                'amount'            => $amount,
                'currency'          => Utility::settingsById($invoice[DatabaseConstants::TABLE_CREATOR])['site_currency'] ?? 'BHD',
                'customer_initiated' => true,
                'threeDSecure'      => true,
                'save_card'         => false,
                'description'       => $invoice->invoice_id,
                'metadata'          => ['udf1' => 'Metadata 1'],
                'reference'         => ['transaction' => 'txn_01', 'order' => 'ord_01'],
                'receipt'           => ['email' => true, 'sms' => true],
                'customer'          => [
                    'first_name' => $payer->name,
                    'email'      => $payer->email,
                    'phone'      => ['country_code' => 965, 'number' => 51234567]
                ],
                'source'            => ['id' => 'src_bh.benefit'],
                'redirect'          => [
                    'url' => route(ViewsConstants::INV . 'benefit.status', [
                        'invoice' => $req->input('invoice_id'),
                        'amount'  => $amount
                    ]),
                ],
            ];

            Log::info(__METHOD__ . " sending invoice charge", ['payload' => $charge]);
            $response = self::client()->post(
                'https://api.tap.company/v2/charges',
                ['json' => $charge, 'headers' => [
                    'Authorization' => 'Bearer ' . $secret,
                    'Accept'        => 'application/json'
                ]]
            );

            $data = json_decode($response->getBody()->getContents());
            Log::info(__METHOD__ . " invoice charge initiated", [
                'transaction_url' => $data->transaction->url
            ]);
            return redirect($data->transaction->url);
        } catch (Throwable $e) {
            return self::handleException($req, $e);
        }
    }

    public const GET_INV_PAY_STT = 'getInvoicePaymentStatus';
    public function getInvoicePaymentStatus(Request $req, string $invoiceEncrypted, string $amount): RedirectResponse
    {
        Log::info(__METHOD__ . " start", [
            'invoice_enc' => $invoiceEncrypted,
            'tap_id'      => $req->input('tap_id'),
            'amount'      => $amount
        ]);
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;

        try {
            $invoiceId = Crypt::decryptString($invoiceEncrypted);
            $invoice  = Invoice::findOrFail($invoiceId);
            $user     = User::findOrFail($invoice[DatabaseConstants::TABLE_CREATOR]);
            $secret   = Utility::getCompanyPaymentSetting($user?->id)['benefit_secret_key'] ?? '';

            $resp = self::client()->get(
                'https://api.tap.company/v2/charges/' . $req->input('tap_id'),
                ['headers' => [
                    'Authorization' => 'Bearer ' . $secret,
                    'Accept'        => 'application/json'
                ]]
            );
            $status = json_decode($resp->getBody()->getContents());
            Log::info(__METHOD__ . " payment status", [
                'code' => $status->gateway->response->code
            ]);

            if ($status->gateway->response->code !== '00') {
                Log::warning(__METHOD__ . " transaction failed", [
                    'invoice_id' => $invoiceId
                ]);
                return redirect()->route(ViewsConstants::INV . 'link.copy', $invoiceEncrypted)
                    ->with('error', __('Transaction failed!'));
            }

            DB::beginTransaction();
            try {
                $orderId = self::txnId();
                InvoicePayment::create([
                    'invoice_id'     => $invoice->id,
                    'date'           => now()->toDateString(),
                    'amount'         => $amount,
                    'account_id'     => 0,
                    'payment_method' => 0,
                    'order_id'       => $orderId,
                    'payment_type'   => 'Benefit',
                    'description'    => 'Invoice ' . Utility::invoiceNumberFormat(
                        Utility::settingsById($invoice[DatabaseConstants::TABLE_CREATOR]),
                        $invoice->invoice_id
                    ),
                ]);
                $newDue = $invoice->getDue() - $amount;
                Invoice::changeStatus($invoice->id, $newDue ? 2 : 3);
                Utility::updateUserBalance('customer', $invoice->customer_id, $amount, 'debit');
                InvoiceBankTransfer::where('invoice_id', $invoice->id)
                    ->where('order_id', $orderId)
                    ->delete();

                DB::commit();
                Log::info(__METHOD__ . " invoice payment recorded", [
                    'invoice_id' => $invoiceId,
                    'order_id' => $orderId
                ]);
                return redirect()->route(ViewsConstants::INV . 'link.copy', $invoiceEncrypted)
                    ->with('success', __('Invoice paid successfully!'));
            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Throwable $e) {
            Log::error(__METHOD__ . " failed", ['err' => $e->getMessage()]);
            return redirect()->route(ViewsConstants::INV . 'link.copy', $invoiceEncrypted)
                ->with('error', $e->getMessage());
        }
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
            new \Illuminate\Auth\Access\AuthorizationException($perm),
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