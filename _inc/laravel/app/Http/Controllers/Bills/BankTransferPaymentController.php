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
  RedirectResponse,
  Request,
  Response,
};
use Illuminate\Support\Facades\{
  Crypt,
  DB,
  Log,
  Route,
  Validator,
  View as ViewFacade
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

  public const PL_PAY_BNK = 'planPayWithBank';
  public function planPayWithBank(Request $request): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::info("[{$base}::{$action}] redirecting to login", ['method' => $method]);
        return $userOrRedirect;
      }
      $user = $userOrRedirect;
      $valStart = microtime(true);
      if ($resp = self::validateReceipt($req)) {
        $this->logExecutionTime($valStart, $action, 'validateReceipt');
        return $resp;
      }
      $this->logExecutionTime($valStart, $action, 'validateReceipt');
      Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'plan_id_enc' => $req->input(UsersConstants::COL_PLAN_ID), 'coupon' => $req->input('coupon'), 'method' => $method]);
      try {
        $txnStart = microtime(true);
        $resp = DB::transaction(function () use ($req, $user, $action, $base) {
          $decStart = microtime(true);
          $planId = Crypt::decryptString($req->input(UsersConstants::COL_PLAN_ID));
          $this->logExecutionTime($decStart, $action, 'decryptPlanId');
          $plan = Plan::find($planId);
          if (!$plan) {
            Log::warning("[{$base}::{$action}] plan not found", [UsersConstants::COL_PLAN_ID => $planId]);
            return redirect()->route(ViewsConstants::PLN . '.index')->with('error', __('Plan is deleted.'));
          }
          $price = $plan->price;
          $couponId = null;
          if ($code = strtoupper($req->input('coupon', ''))) {
            Log::info("[{$base}::{$action}] applying coupon", ['code' => $code]);
            $cStart = microtime(true);
            $coupon = Coupon::where('is_active', 1)->where('code', $code)->first();
            $this->logExecutionTime($cStart, $action, 'fetchCoupon');
            if (!$coupon) {
              Log::warning("[{$base}::{$action}] invalid coupon", ['code' => $code]);
              return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
            }
            if ($coupon->limit <= $coupon->used_coupon()) {
              Log::warning("[{$base}::{$action}] coupon expired", ['coupon_id' => $coupon->id]);
              return redirect()->back()->with('error', __('This coupon code has expired.'));
            }
            $applyStart = microtime(true);
            $discountAmount = $plan->price * ($coupon->discount / 100);
            $price -= $discountAmount;
            $couponId = $coupon->id;
            $this->logExecutionTime($applyStart, $action, 'applyCoupon');
            Log::info("[{$base}::{$action}] coupon applied", ['coupon_id' => $couponId, 'discount_amount' => $discountAmount, 'new_price' => $price]);
          }
          $uploadStart = microtime(true);
          $upload = self::uploadReceipt($req);
          $this->logExecutionTime($uploadStart, $action, 'uploadReceipt');
          $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
          $createStart = microtime(true);
          Order::create([
            'order_id' => $orderId,
            UsersConstants::COL_PLAN_NM => $plan->name,
            UsersConstants::COL_PLAN_ID => $plan->id,
            'price' => $price,
            'price_currency' => $req->input('currency', 'USD'),
            'payment_type' => 'Bank Transfer',
            'payment_status' => 'Pending',
            'receipt' => $upload['file'] ?? null,
            UsersConstants::COL_USER_ID => $user?->id,
          ]);
          $this->logExecutionTime($createStart, $action, 'createOrder');
          Log::info("[{$base}::{$action}] order created", ['order_id' => $orderId, UsersConstants::COL_USER_ID => $user?->id, UsersConstants::COL_PLAN_ID => $plan->id]);
          if (isset($coupon)) {
            $ucStart = microtime(true);
            UserCoupon::create(['user' => $user?->id, 'coupon' => $coupon->id, 'order' => $orderId]);
            $this->logExecutionTime($ucStart, $action, 'recordCouponUsage');
            Log::info("[{$base}::{$action}] user coupon recorded", ['coupon_id' => $coupon->id, 'order_id' => $orderId]);
            if ($coupon->limit <= $coupon->used_coupon()) {
              $deactStart = microtime(true);
              $coupon->is_active = 0;
              $coupon->save();
              $this->logExecutionTime($deactStart, $action, 'deactivateCoupon');
              Log::info("[{$base}::{$action}] coupon deactivated", ['coupon_id' => $coupon->id]);
            }
          }
          Log::info("[{$base}::{$action}] completed successfully", ['order_id' => $orderId]);
          return redirect()->route(ViewsConstants::PLN . '.index')->with('success', __('Plan payment request sent successfully.'));
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), UsersConstants::COL_USER_ID => $user?->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public const OD_DST = 'orderDestroy';
  public function orderDestroy(int|string $id): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($id, $action, $method, $class, $base) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::info("[{$base}::{$action}] redirecting to login", ['method' => $method]);
        return $userOrRedirect;
      }
      Log::info("[{$base}::{$action}] start", ['order_id' => $id, 'method' => $method]);
      try {
        $delStart = microtime(true);
        Order::whereKey($id)->delete();
        $this->logExecutionTime($delStart, $action, 'deleteOrder');
        Log::info("[{$base}::{$action}] deleted", ['order_id' => $id]);
        return redirect()->back()->with('success', __('Order successfully deleted.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'order_id' => $id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException(request(), $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'order_id' => $id]);
  }

  public function action(int|string $id): Response|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $viewPath = ViewsConstants::OD . '.action';
    return $this->measureProfile($action, function () use ($id, $action, $method, $class, $base, $viewPath) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      Log::info("[{$base}::{$action}] rendering payment action form", ['order_id' => $id, 'method' => $method]);
      try {
        $fetchStart = microtime(true);
        $order = Order::findOrFail($id);
        $this->logExecutionTime($fetchStart, $action, 'fetchOrder');
        $cfgStart = microtime(true);
        $adminPaymentSetting = Utility::getAdminPaymentSetting();
        $this->logExecutionTime($cfgStart, $action, 'getAdminPaymentSetting');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'order_id' => $id]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['order', 'adminPaymentSetting']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, compact('order', 'adminPaymentSetting'));
        $this->logExecutionTime($renderStart, $action, 'renderAction');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'order_id' => $id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException(request(), $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'order_id' => $id]);
  }

  public const CHG_STT = 'changeStatus';
  public function changeStatus(Request $request, int|string $orderId): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    $status = $request->input('status');
    return $this->measureProfile($action, function () use ($req, $orderId, $status, $action, $method, $class, $base) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      Log::info("[{$base}::{$action}] start", ['order_id_param' => $orderId, 'order_id_input' => $req->input('order_id'), 'new_status' => $status, 'method' => $method]);
      try {
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $action, $base) {
          $findStart = microtime(true);
          $order = Order::findOrFail($req->input('order_id'));
          $this->logExecutionTime($findStart, $action, 'findOrder');
          Log::info("[{$base}::{$action}] found order", ['order_id' => $order->order_id]);
          if ($req->input('status') === 'Approval') {
            $planStart = microtime(true);
            $plan = Plan::findOrFail($order->plan_id);
            $user = User::findOrFail($order->user_id);
            $this->logExecutionTime($planStart, $action, 'fetchPlanUser');
            if ($user instanceof User) {
              $applyStart = microtime(true);
              $user->plan = $plan->id;
              $user?->assignPlan($plan->id, $user?->id);
              $order->payment_status = 'Approved';
              $this->logExecutionTime($applyStart, $action, 'applyApproval');
              Log::info("[{$base}::{$action}] approved", ['order_id' => $order->order_id, 'plan_id' => $plan->id, 'user_id' => $user->id]);
            }
          } else {
            $rejStart = microtime(true);
            $order->payment_status = 'Rejected';
            $this->logExecutionTime($rejStart, $action, 'applyRejection');
            Log::info("[{$base}::{$action}] rejected", ['order_id' => $order->order_id]);
          }
          $saveStart = microtime(true);
          $order->save();
          $this->logExecutionTime($saveStart, $action, 'saveOrder');
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return redirect()->route(ViewsConstants::OD . '.index')->with('success', __('Plan payment status updated successfully.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'order_id' => $orderId, 'status' => $status]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::changeStatus');
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'order_id' => $orderId, 'status' => $status]);
  }

  public const CST_PAY_BNK = 'customerPayWithBank';
  public function customerPayWithBank(Request $request): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $valStart = microtime(true);
      if ($resp = self::validateReceipt($req)) {
        $this->logExecutionTime($valStart, $action, 'validateReceipt');
        return $resp;
      }
      $this->logExecutionTime($valStart, $action, 'validateReceipt');
      Log::info("[{$base}::{$action}] start", ['invoice_id_enc' => $req->input('invoice_id'), 'amount' => $req->input('amount'), UsersConstants::COL_USER_ID => $req->user()->id, 'method' => $method]);
      try {
        $txnStart = microtime(true);
        $resp = DB::transaction(function () use ($req, $action, $base) {
          $decStart = microtime(true);
          $invoiceId = Crypt::decryptString($req->input('invoice_id'));
          $this->logExecutionTime($decStart, $action, 'decryptInvoiceId');
          $findStart = microtime(true);
          $invoice = Invoice::find($invoiceId);
          $this->logExecutionTime($findStart, $action, 'findInvoice');
          if (!$invoice) {
            Log::warning("[{$base}::{$action}] invoice not found", ['invoice_id' => $invoiceId]);
            return back()->with('error', __('Invoice not found.'));
          }
          $uploadStart = microtime(true);
          $upload = self::uploadReceipt($req);
          $this->logExecutionTime($uploadStart, $action, 'uploadReceipt');
          $orderId = strtoupper(str_replace('.', '', uniqid('', true)));
          $createStart = microtime(true);
          InvoiceBankTransfer::create([
            'invoice_id' => $invoice->id,
            'order_id' => $orderId,
            'amount' => $req->input('amount'),
            'status' => 'Pending',
            'date' => now()->toDateString(),
            'receipt' => $upload['file'] ?? null,
            'created_by' => $invoice->created_by,
          ]);
          $this->logExecutionTime($createStart, $action, 'createBankTransfer');
          Log::info("[{$base}::{$action}] created bank transfer record", ['order_id' => $orderId, 'invoice_id' => $invoice->id]);
          return back()->with('success', __('Invoice payment request sent successfully.'));
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), UsersConstants::COL_USER_ID => $req->user()->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public const INV_ACT = 'invoiceAction';
  public function invoiceAction(int|string $id): Response|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $viewPath = ViewsConstants::INV . '.action';
    return $this->measureProfile($action, function () use ($id, $action, $method, $class, $base, $viewPath) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      Log::info("[{$base}::{$action}] rendering invoice bank-transfer action", ['transfer_id' => $id, 'method' => $method]);
      try {
        $trStart = microtime(true);
        $transfer = InvoiceBankTransfer::findOrFail($id);
        $this->logExecutionTime($trStart, $action, 'findTransfer');
        $invStart = microtime(true);
        $invoice = Invoice::findOrFail($transfer->invoice_id);
        $this->logExecutionTime($invStart, $action, 'findInvoice');
        $cfgStart = microtime(true);
        $companyPaymentSetting = Utility::getCompanyPaymentSetting($transfer->created_by);
        $this->logExecutionTime($cfgStart, $action, 'getCompanyPaymentSetting');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'transfer_id' => $id]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfer', 'companyPaymentSetting', 'invoice']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, compact('transfer', 'companyPaymentSetting', 'invoice'));
        $this->logExecutionTime($renderStart, $action, 'renderInvoiceAction');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'transfer_id' => $id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException(request(), $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $id]);
  }

  public const INV_CG_STT = 'invoiceChangeStatus';
  public function invoiceChangeStatus(Request $request, int|string $invoiceId): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    return $this->measureProfile($action, function () use ($req, $invoiceId, $action, $method, $class, $base) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $orderId = $req->input('order_id');
      $status  = $req->input('status');
      Log::info("[{$base}::{$action}] start", ['invoice_id' => $invoiceId, 'order_id' => $orderId, 'status' => $status, UsersConstants::COL_USER_ID => $req->user()->id, 'method' => $method]);
      try {
        $txnStart = microtime(true);
        $resp = DB::transaction(function () use ($req, $invoiceId, $orderId, $status, $action, $base) {
          $findStart = microtime(true);
          $transfer = InvoiceBankTransfer::where('invoice_id', $invoiceId)->where('order_id', $orderId)->firstOrFail();
          $this->logExecutionTime($findStart, $action, 'findTransfer');
          Log::info("[{$base}::{$action}] transfer found", ['transfer_id' => $transfer->id, 'invoice_id' => $transfer->invoice_id, 'order_id' => $transfer->order_id]);
          if ($status === 'Approval') {
            $prepStart = microtime(true);
            $settings = DB::table('settings')->where('created_by', $transfer->created_by)->pluck('value', 'name')->toArray();
            $invoice = Invoice::findOrFail($transfer->invoice_id);
            $desc = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
            $this->logExecutionTime($prepStart, $action, 'preparePayment');
            $createStart = microtime(true);
            InvoicePayment::create([
              'invoice_id' => $transfer->invoice_id,
              'date' => now()->toDateString(),
              'amount' => $transfer->amount,
              'payment_method' => 1,
              'order_id' => $transfer->order_id,
              'payment_type' => __('Bank Transfer'),
              'receipt' => $transfer->receipt,
              'description' => $desc,
            ]);
            $this->logExecutionTime($createStart, $action, 'createPayment');
            $delStart = microtime(true);
            $transfer->delete();
            $this->logExecutionTime($delStart, $action, 'deleteTransfer');
            Log::info("[{$base}::{$action}] approved and transfer deleted", ['transfer_id' => $transfer->id]);
          } else {
            $rejStart = microtime(true);
            $transfer->status = 'Rejected';
            $transfer->save();
            $this->logExecutionTime($rejStart, $action, 'markRejected');
            Log::info("[{$base}::{$action}] rejected", ['transfer_id' => $transfer->id]);
          }
          return back()->with('success', __('Invoice payment request status updated successfully.'));
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'invoice_id' => $invoiceId, 'order_id' => $req->input('order_id')]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::invoiceChangeStatus');
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId, 'order_id' => $request->input('order_id'), 'status' => $request->input('status')]);
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
    $file = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $request->file('payment_receipt')->getClientOriginalName());
    $path = Utility::uploadFile($request, 'payment_receipt', $file, self::DIR, []);
    Log::info('Receipt uploaded', ['file' => $file, 'path' => $path]);
    return ['file' => $file, 'path' => $path];
  }
}
