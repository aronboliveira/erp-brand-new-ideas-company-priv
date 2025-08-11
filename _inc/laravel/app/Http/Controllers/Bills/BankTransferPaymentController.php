<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  MiddlewaresConstants,
  UsersConstants,
  ViewsConstants
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
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request,
  Response,
};
use Illuminate\Support\Facades\{
  Crypt,
  DB,
  Log,
  Validator
};
use Illuminate\View\View;

final class BankTransferPaymentController extends Controller
{
  use ChecksLogin;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH]);
  }

  private const DIR = 'uploads/order';

  public function planPayWithBank(Request $request): RedirectResponse|null
  {
    // login / user resolution
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::info(__FUNCTION__ . ': redirecting to login');
      return $userOrRedirect;
    }
    $user = $userOrRedirect;
    if ($resp = self::validateReceipt($request))
      return $resp;
    Log::info(__FUNCTION__ . ': start', [
      UsersConstants::COL_USER_ID    => $user?->id,
      'plan_id_enc' => $request->input(UsersConstants::COL_PLAN_ID),
      'coupon'     => $request->input('coupon'),
    ]);
    try {
      return DB::transaction(function () use ($request, $user) {
        // decrypt plan
        $planId = Crypt::decryptString($request->input(UsersConstants::COL_PLAN_ID));
        $plan  = Plan::find($planId);
        if (!$plan) {
          Log::warning(__FUNCTION__ . ': plan not found', [UsersConstants::COL_PLAN_ID => $planId]);
          return redirect()->route(ViewsConstants::PLN . '.index')
            ->with('error', __('Plan is deleted.'));
        }
        // coupon logic
        $price   = $plan->price;
        $couponId = null;
        if ($code = strtoupper($request->input('coupon', ''))) {
          Log::info(__FUNCTION__ . ': applying coupon', ['code' => $code]);
          $coupon = Coupon::where('is_active', 1)
            ->where('code', $code)
            ->first();
          if (!$coupon) {
            Log::warning(__FUNCTION__ . ': invalid coupon', ['code' => $code]);
            return redirect()->back()
              ->with('error', __('This coupon code is invalid or has expired.'));
          }
          if ($coupon->limit <= $coupon->used_coupon()) {
            Log::warning(__FUNCTION__ . ': coupon expired', ['coupon_id' => $coupon->id]);
            return redirect()->back()
              ->with('error', __('This coupon code has expired.'));
          }
          $discountAmount = $plan->price * ($coupon->discount / 100);
          $price -= $discountAmount;
          $couponId = $coupon->id;
          Log::info(__FUNCTION__ . ': coupon applied', [
            'coupon_id'       => $couponId,
            'discount_amount' => $discountAmount,
            'new_price'       => $price,
          ]);
        }
        // upload receipt
        $upload = self::uploadReceipt($request);
        // create order
        $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
        Order::create([
          'order_id'       => $orderId,
          UsersConstants::COL_PLAN_NM      => $plan->name,
          UsersConstants::COL_PLAN_ID        => $plan->id,
          'price'          => $price,
          'price_currency' => $request->input('currency', 'USD'),
          'payment_type'   => 'Bank Transfer',
          'payment_status' => 'Pending',
          'receipt'        => $upload['file'] ?? null,
          UsersConstants::COL_USER_ID        => $user?->id,
        ]);
        Log::info(__FUNCTION__ . ': order created', [
          'order_id' => $orderId,
          UsersConstants::COL_USER_ID  => $user?->id,
          UsersConstants::COL_PLAN_ID  => $plan->id,
        ]);
        // record coupon usage
        if (isset($coupon)) {
          UserCoupon::create([
            'user'   => $user?->id,
            'coupon' => $coupon->id,
            'order'  => $orderId,
          ]);
          Log::info(__FUNCTION__ . ': user coupon recorded', [
            'coupon_id' => $coupon->id,
            'order_id'  => $orderId,
          ]);
          if ($coupon->limit <= $coupon->used_coupon()) {
            $coupon->is_active = 0;
            $coupon->save();
            Log::info(__FUNCTION__ . ': coupon deactivated', ['coupon_id' => $coupon->id]);
          }
        }
        Log::info(__FUNCTION__ . ': completed successfully', ['order_id' => $orderId]);
        return redirect()->route(ViewsConstants::PLN . '.index')
          ->with('success', __('Plan payment request sent successfully.'));
      });
    } catch (\Throwable $e) {
      Log::error(__FUNCTION__ . ' failed', [
        'exception' => $e,
        UsersConstants::COL_USER_ID   => $user?->id,
      ]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function orderDestroy(int|string $id): RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::info(__FUNCTION__ . ': redirecting to login');
      return $userOrRedirect;
    }
    Log::info(__FUNCTION__ . ': start', ['order_id' => $id]);
    try {
      Order::whereKey($id)->delete();
      Log::info(__FUNCTION__ . ': deleted', ['order_id' => $id]);
      return redirect()->back()
        ->with('success', __('Order successfully deleted.'));
    } catch (\Throwable $e) {
      Log::error(__FUNCTION__ . ' failed', [
        'exception' => $e,
        'order_id'  => $id,
      ]);
      return defaultUndefinedException(request(), $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function action(int|string $id): Response|RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
      return $userOrRedirect;
    Log::info('action: rendering payment action form', ['order_id' => $id]);
    $order              = Order::findOrFail($id);
    $adminPaymentSetting = Utility::getAdminPaymentSetting();
    return response()->view(ViewsConstants::OD . '.' . __FUNCTION__, compact('order', 'adminPaymentSetting'));
  }

  public function changeStatus(Request $request, int|string $orderId): RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
      return $userOrRedirect;
    $status = $request->input('status');
    Log::info(__FUNCTION__ . ': start', ['order_id' => $orderId, 'new_status' => $status]);
    try {
      DB::transaction(function () use ($request) {
        $order = Order::findOrFail($request->input('order_id'));
        Log::info(__FUNCTION__ . ': found order', ['order_id' => $order->order_id]);
        if ($request->input('status') === 'Approval') {
          $plan = Plan::findOrFail($order->plan_id);
          $user = User::findOrFail($order->user_id);
          if ($user instanceof User) {
            $user->plan = $plan->id;
            $user?->assignPlan($plan->id, $user?->id);
            $order->payment_status = 'Approved';
            Log::info(__FUNCTION__ . ': approved', ['order_id' => $order->order_id]);
          }
        } else {
          $order->payment_status = 'Rejected';
          Log::info(__FUNCTION__ . ': rejected', ['order_id' => $order->order_id]);
        }
        $order->save();
      });
      return redirect()->route(ViewsConstants::OD . '.index')
        ->with('success', __('Plan payment status updated successfully.'));
    } catch (\Throwable $e) {
      Log::error(__FUNCTION__ . ' failed', [
        'exception' => $e,
        'order_id'  => $orderId,
      ]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::changeStatus');
    }
  }

  public const CST_PAY_BNK = 'customerPayWithBank';
  public function customerPayWithBank(Request $request): RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
      return $userOrRedirect;
    if ($resp = self::validateReceipt($request))
      return $resp;
    Log::info('customerPayWithBank: start', [
      'invoice_id_enc' => $request->input('invoice_id'),
      'amount'         => $request->input('amount'),
      UsersConstants::COL_USER_ID        => $request->user()->id,
    ]);
    try {
      return DB::transaction(function () use ($request) {
        $invoiceId = Crypt::decryptString($request->input('invoice_id'));
        $invoice  = Invoice::find($invoiceId);
        if (!$invoice) {
          Log::warning('customerPayWithBank: invoice not found', ['invoice_id' => $invoiceId]);
          return redirect()->back()
            ->with('error', __('Invoice not found.'));
        }
        $upload = self::uploadReceipt($request);
        $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
        InvoiceBankTransfer::create([
          'invoice_id' => $invoice->id,
          'order_id'   => $orderId,
          'amount'     => $request->input('amount'),
          'status'     => 'Pending',
          'date'       => now()->toDateString(),
          'receipt'    => $upload['file'] ?? null,
          'created_by' => $invoice->created_by,
        ]);
        Log::info('customerPayWithBank: created bank transfer record', [
          'order_id'   => $orderId,
          'invoice_id' => $invoice->id,
        ]);
        return redirect()->back()
          ->with('success', __('Invoice payment request sent successfully.'));
      });
    } catch (\Throwable $e) {
      Log::error('customerPayWithBank failed', [
        'exception' => $e,
        UsersConstants::COL_USER_ID   => $request->user()->id,
      ]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::customerPayWithBank');
    }
  }

  public const INV_ACT = 'invoiceAction';
  public function invoiceAction(int|string $id): Response|RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
      return $userOrRedirect;
    Log::info('invoiceAction: rendering invoice bank-transfer action', ['transfer_id' => $id]);
    $transfer             = InvoiceBankTransfer::findOrFail($id);
    $invoice              = Invoice::findOrFail($transfer->invoice_id);
    $companyPaymentSetting = Utility::getCompanyPaymentSetting($transfer->created_by);
    return response()->view(
      ViewsConstants::INV . '.action',
      compact('transfer', 'companyPaymentSetting', 'invoice')
    );
  }

  public const INV_CG_STT = 'invoiceChangeStatus';
  public function invoiceChangeStatus(Request $request, string $invoiceId): RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
      return $userOrRedirect;
    $orderId = $request->input('order_id');
    $status = $request->input('status');
    Log::info('invoiceChangeStatus: start', [
      'invoice_id' => $invoiceId,
      'order_id'   => $orderId,
      'status'     => $status,
      UsersConstants::COL_USER_ID    => $request->user()->id,
    ]);

    try {
      return DB::transaction(function () use ($request, $invoiceId, $orderId, $status) {
        // only pick the transfer that matches both invoice_id and order_id
        $transfer = InvoiceBankTransfer::where('invoice_id', $invoiceId)
          ->where('order_id', $orderId)
          ->firstOrFail();

        Log::info('invoiceChangeStatus: transfer found', [
          'transfer_id' => $transfer->id,
          'invoice_id'  => $transfer->invoice_id,
          'order_id'    => $transfer->order_id,
        ]);

        if ($status === 'Approval') {
          // record payment
          InvoicePayment::create([
            'invoice_id'     => $transfer->invoice_id,
            'date'           => now()->toDateString(),
            'amount'         => $transfer->amount,
            'payment_method' => 1,
            'order_id'       => $transfer->order_id,
            'payment_type'   => __('Bank Transfer'),
            'receipt'        => $transfer->receipt,
            'description'    => __('Invoice') . ' ' . Utility::invoiceNumberFormat(
              DB::table('settings')
                ->where('created_by', $transfer->created_by)
                ->pluck('value', 'name'),
              Invoice::findOrFail($transfer->invoice_id)->invoice_id
            ),
          ]);

          // then delete the pending transfer
          $transfer->delete();
          Log::info('invoiceChangeStatus: approved and transfer deleted', [
            'transfer_id' => $transfer->id,
          ]);
        } else {
          // simply mark as rejected
          $transfer->status = 'Rejected';
          $transfer->save();
          Log::info('invoiceChangeStatus: rejected', [
            'transfer_id' => $transfer->id,
          ]);
        }

        return redirect()->back()
          ->with('success', __('Invoice payment request status updated successfully.'));
      });
    } catch (\Throwable $e) {
      Log::error('invoiceChangeStatus failed', [
        'exception'  => $e,
        'invoice_id' => $invoiceId,
        'order_id'   => $orderId,
        UsersConstants::COL_USER_ID    => $request->user()->id,
      ]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::invoiceChangeStatus'
      );
    }
  }

  private static function validateReceipt(Request $request): ?RedirectResponse
  {
    $v = Validator::make($request->all(), ['payment_receipt' => 'required']);
    if ($v->fails()) {
      Log::warning('Receipt validation failed', [
        'errors'  => $v->errors()->all(),
        'payload' => $request->all(),
        'user'    => $request->user()?->id,
      ]);
      return redirect()->back()->with('error', $v->errors()->first());
    }
    return null;
  }

  /** @return array{file:string,path:string}|null */
  private static function uploadReceipt(Request $request): ?array
  {
    if (!$request->hasFile('payment_receipt')) {
      Log::info('No receipt file uploaded', ['user' => $request->user()?->id]);
      return null;
    }
    $file = time() . '_' . $request->file('payment_receipt')->getClientOriginalName();
    $path = Utility::uploadFile($request, 'payment_receipt', $file, self::DIR, []);
    Log::info('Receipt uploaded', ['file' => $file, 'path' => $path]);
    return ['file' => $file, 'path' => $path];
  }
}
