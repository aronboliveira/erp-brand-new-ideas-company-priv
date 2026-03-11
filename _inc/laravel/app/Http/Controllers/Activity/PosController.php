<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  BillsConstants,
  DatabaseConstants,
  PermissionsConstants,
  SettingsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Mail\SelledInvoice;
use App\Models\{
  Customer,
  Pos,
  PosPayment,
  PosProduct,
  ProductService,
  StockReport,
  User,
  Utility,
  Warehouse,
  WarehouseProduct
};
use App\Traits\ChecksLogin;
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request,
  Response
};
use Illuminate\Support\Facades\{
  Auth,
  Crypt,
  DB,
  Log,
  Mail,
  Route,
  Storage,
  Validator,
  View as ViewFacade
};
use Illuminate\View\View;
use Throwable;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class PosController extends Controller
{

  use ChecksLogin;

  public function index(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] requested", [UsersConstants::COL_USER_ID => $req->user()?->id]);
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$class}::{$action}] unauthenticated");
        return $r;
      }
      if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null) return $r;
      try {
        $user = $req->user();
        $creatorId = $user?->creatorId();
        $custStart = microtime(true);
        $customers = Customer::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(UsersConstants::COL_USER_ID, 'name')->prepend('Walk-in-customer', '');
        $this->logExecutionTime($custStart, $action, 'fetchCustomers');
        $whStart = microtime(true);
        $warehouses = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
        $this->logExecutionTime($whStart, $action, 'fetchWarehouses');
        $detStart = microtime(true);
        $details = ['pos_id' => $user?->posNumberFormat(self::invoiceNumber()), 'customer' => $customers->toArray(), 'user' => $user?->toArray(), 'date' => now()->toDateString(), 'pay' => 'show'];
        $this->logExecutionTime($detStart, $action, 'prepareDetails');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] data prepared", ['customers_count' => is_countable($customers) ? count($customers) : null, 'warehouses_count' => is_countable($warehouses) ? count($warehouses) : null, 'method' => $method]);
        return response()->view($viewPath, compact('customers', 'warehouses', 'details'));
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.show';
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] requested", [UsersConstants::COL_USER_ID => $req->user()?->id]);
      if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null) return $r;
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$class}::{$action}] unauthenticated");
        return $u;
      }
      try {
        $cart = session('pos', []);
        if (empty($cart)) {
          Log::warning("[{$class}::{$action}] empty cart", ['method' => $method]);
          return response()->json(['error' => 'Add some products to cart!'], 404);
        }
        $user = $u;
        $creatorId = $user?->creatorId();
        $custStart = microtime(true);
        $customer = Customer::where('name', $req->vc_name)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
        $this->logExecutionTime($custStart, $action, 'findCustomer');
        $whStart = microtime(true);
        $warehouse = Warehouse::where('id', $req->warehouse_name)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
        $this->logExecutionTime($whStart, $action, 'findWarehouse');
        $details = ['posId' => $user?->posNumberFormat(self::invoiceNumber()), 'customer' => $customer->toArray(), 'warehouse' => $warehouse->toArray(), 'user' => $user?->toArray(), 'date' => now()->toDateString(), 'pay' => 'show'];
        $settingsStart = microtime(true);
        $settings = Utility::settings();
        $this->logExecutionTime($settingsStart, $action, 'loadSettings');
        $composeStart = microtime(true);
        $this->_composeDetails($details, $settings);
        $this->logExecutionTime($composeStart, $action, 'composeDetails');
        $sumStart = microtime(true);
        [$sales, $subtotal] = $this->_summarizeCart($cart);
        $this->logExecutionTime($sumStart, $action, 'summarizeCart');
        $discount = $req->discount ?? 0;
        $sales['discount'] = $user?->priceFormat($discount);
        $sales['subTotal'] = $user?->priceFormat($subtotal);
        $sales['total'] = $user?->priceFormat($subtotal - $discount);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] data prepared", ['method' => $method]);
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, compact('sales', 'details'));
        $this->logExecutionTime($renderStart, $action, 'renderPosShow');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $req): JsonResponse|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      Log::info("[{$class}::{$action}] requested", [UsersConstants::COL_USER_ID => $req->user()?->id]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$class}::{$action}] unauthenticated");
        return $u;
      }
      if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null) return $r;
      try {
        $txnStart = microtime(true);
        $result = DB::transaction(function () use ($req, $u, $action, $class) {
          $creatorId = $u->creatorId();
          $cart = session('pos', []);
          if (empty($cart)) {
            Log::warning("[{$class}::{$action}] empty cart during transaction");
            return response()->json(['code' => 404, 'success' => 'Items not found!']);
          }
          $pid = self::invoiceNumber();
          $dupStart = microtime(true);
          $already = Pos::where('pos_id', $pid)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->exists();
          $this->logExecutionTime($dupStart, $action, 'checkDuplicatePayment');
          if ($already) {
            Log::info("[{$class}::{$action}] duplicate payment", ['pos_id' => $pid]);
            return response()->json(['code' => 200, 'success' => 'Payment is already completed!']);
          }
          $custIdStart = microtime(true);
          $customer_id = Customer::customerId($req->vc_name);
          $this->logExecutionTime($custIdStart, $action, 'resolveCustomerId');
          $whIdStart = microtime(true);
          $warehouseId = Warehouse::warehouseId($req->warehouse_name);
          $this->logExecutionTime($whIdStart, $action, 'resolveWarehouseId');
          $createPosStart = microtime(true);
          $pos = Pos::create(['pos_id' => $pid, 'customer_id' => $customer_id, 'warehouse_id' => $warehouseId, 'pos_date' => now()->toDateString(), DatabaseConstants::COL_TABLE_CREATOR => $creatorId]);
          $this->logExecutionTime($createPosStart, $action, 'createPos');
          Log::info("[{$class}::{$action}] Pos record created", ['pos_id' => $pos->id]);
          foreach ($cart as $item) {
            $lockStart = microtime(true);
            $prod = ProductService::where('id', $item['id'])->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->lockForUpdate()->firstOrFail();
            $this->logExecutionTime($lockStart, $action, 'lockProduct');
            if ($prod->quantity < $item['quantity']) {
              Log::error("[{$class}::{$action}] insufficient stock", ['product_id' => $prod->id, 'required' => $item['quantity'], 'available' => $prod->quantity]);
              throw new \Exception("Insufficient stock for product {$prod->id}");
            }
            $decStart = microtime(true);
            $prod->decrement('quantity', $item['quantity']);
            $this->logExecutionTime($decStart, $action, 'decrementProduct');
            $taxStart = microtime(true);
            $taxId = ProductService::taxId($item['id']);
            $this->logExecutionTime($taxStart, $action, 'resolveTaxId');
            $ppStart = microtime(true);
            PosProduct::create(['pos_id' => $pos->id, 'product_id' => $item['id'], 'price' => $item['price'], 'quantity' => $item['quantity'], 'tax' => $taxId, 'discount' => $req->discount]);
            $this->logExecutionTime($ppStart, $action, 'createPosProduct');
            $whQtyStart = microtime(true);
            Utility::warehouseQuantity('minus', $item['quantity'], $item['id'], $warehouseId);
            $this->logExecutionTime($whQtyStart, $action, 'warehouseQuantityUpdate');
            $srDelStart = microtime(true);
            StockReport::where('type', 'pos')->where('type_id', $pos->id)->delete();
            $this->logExecutionTime($srDelStart, $action, 'deleteStockReportDuplicates');
            Log::info("[{$class}::{$action}] PosProduct created and stock updated", ['pos_id' => $pos->id, 'product_id' => $item['id']]);
          }
          $sumStart = microtime(true);
          $subtotal = array_sum(array_column($cart, 'subtotal'));
          $this->logExecutionTime($sumStart, $action, 'calculateSubtotal');
          $payStart = microtime(true);
          PosPayment::create(['pos_id' => $pos->id, 'date' => $req->date, 'amount' => $subtotal, 'discount' => $req->discount, 'discount_amount' => $subtotal - $req->discount]);
          $this->logExecutionTime($payStart, $action, 'createPosPayment');
          session()->forget('pos');
          Log::info("[{$class}::{$action}] PosPayment created and cart cleared", ['pos_id' => $pos->id]);
          return response()->json(['code' => 200, 'success' => 'Payment completed successfully!']);
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return $result;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $req, string $encId): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.view';
    return $this->measureProfile($action, function () use ($req, $encId, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] requested", ['encrypted_id' => $encId]);
      if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null) return $r;
      try {
        $decStart = microtime(true);
        $id = Crypt::decrypt($encId);
        $this->logExecutionTime($decStart, $action, 'decryptId');
        $findStart = microtime(true);
        $pos = Pos::findOrFail($id);
        $this->logExecutionTime($findStart, $action, 'findPos');
        if ($pos[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) {
          Log::warning("[{$class}::{$action}] unauthorized access", ['pos_id' => $id]);
          return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
        }
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $payStart = microtime(true);
        $payment = PosPayment::where('pos_id', $pos->id)->first();
        $this->logExecutionTime($payStart, $action, 'fetchPosPayment');
        Log::info("[{$class}::{$action}] data prepared", ['pos_id' => $id]);
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, ['pos' => $pos, 'customer' => $pos->customer, 'items' => $pos->items, 'posPayment' => $payment]);
        $this->logExecutionTime($renderStart, $action, 'renderPosView');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'encrypted_id' => $encId, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'encrypted_id' => $encId]);
  }

  public function report(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] requested", ['method' => $method, UsersConstants::COL_USER_ID => $req->user()?->id]);
      if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
        Log::warning("[{$class}::{$action}] permission denied", ['method' => $method, UsersConstants::COL_USER_ID => $req->user()?->id]);
        return $r;
      }
      try {
        $loadStart = microtime(true);
        $pps = Pos::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->with(['customer', 'warehouse'])->get();
        $this->logExecutionTime($loadStart, $action, 'loadPosRecords');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] records loaded", ['method' => $method, 'count' => is_countable($pps) ? count($pps) : null]);
        return response()->view($viewPath, ['posPayments' => $pps]);
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function barcode(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] page requested", ['method' => $method, UsersConstants::COL_USER_ID => $req->user()?->id]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$class}::{$action}] unauthenticated");
        return $u;
      }
      if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
        Log::warning("[{$class}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $u->id]);
        return $r;
      }
      try {
        $prodStart = microtime(true);
        $prods = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
        $this->logExecutionTime($prodStart, $action, 'fetchProducts');
        $bcStart = microtime(true);
        $barcode = ['barcodeType' => $u->barcodeType(), 'barcodeFormat' => $u->barcodeFormat()];
        $this->logExecutionTime($bcStart, $action, 'prepareBarcodeConfig');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] data ready", ['method' => $method, 'products_count' => is_countable($prods) ? count($prods) : null]);
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, ['productServices' => $prods, 'barcode' => $barcode]);
        $this->logExecutionTime($renderStart, $action, 'renderBarcodeView');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function setting(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] page requested", ['method' => $method, UsersConstants::COL_USER_ID => $req->user()?->id]);
      if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
        Log::warning("[{$class}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $req->user()->id]);
        return $r;
      }
      try {
        $setStart = microtime(true);
        $settings = Utility::settings();
        $this->logExecutionTime($setStart, $action, 'loadSettings');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] settings loaded", ['method' => $method, 'settings' => $settings]);
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, ['settings' => $settings]);
        $this->logExecutionTime($renderStart, $action, 'renderSettingsView');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const BC_ST_STR = 'barcodeSettingStore';
  public function barcodeSettingStore(Request $req): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      Log::info("[{$class}::{$action}] called", ['method' => $method]);
      if ($r = self::_validate($req->all(), ['barcode_type' => 'required', 'barcode_format' => 'required'])) {
        Log::warning("[{$class}::{$action}] validation failed", ['errors' => $req->all(), 'method' => $method]);
        return $r;
      }
      Log::info("[{$class}::{$action}] validation passed", ['method' => $method]);
      try {
        $rawUserId = Auth::id();
        $userId = (\App\Models\User::where('id', $rawUserId)->exists()) ? $rawUserId : null;
        $data = $req->only(['barcode_type', 'barcode_format']);
        $txnStart = microtime(true);
        DB::transaction(function () use ($data, $userId, $action) {
          $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
          $insStart = microtime(true);
          foreach ($data as $key => $value) DB::insert('INSERT INTO settings (`value`,`name`,`' . $creatorCol . '`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)', [$value, $key, $userId]);
          $this->logExecutionTime($insStart, $action, 'upsertSettings');
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        Log::info("[{$class}::{$action}] settings updated", ['method' => $method, UsersConstants::COL_USER_ID => $userId, 'data' => $data]);
        return redirect()->back()->with('success', __('Barcode setting successfully updated.'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const BC_PRT = 'printBarcode';
  public function printBarcode(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.print';
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] requested", ['method' => $method, UsersConstants::COL_USER_ID => $req->user()?->id]);
      if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
        Log::warning("[{$class}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $req->user()->id]);
        return $r;
      }
      try {
        $whStart = microtime(true);
        $wh = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->pluck('name', 'id');
        $this->logExecutionTime($whStart, $action, 'fetchWarehouses');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] warehouses loaded", ['method' => $method, 'warehouses' => is_countable($wh) ? count($wh) : null]);
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, ['warehouses' => $wh]);
        $this->logExecutionTime($renderStart, $action, 'renderPrintBarcode');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const GET_PRD = 'getProduct';
  public function getProduct(Request $req): JsonResponse|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      Log::info("[{$class}::{$action}] called", ['method' => $method, 'warehouse_id' => $req->warehouse_id]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$class}::{$action}] unauthenticated");
        return $u;
      }
      try {
        $creatorId = $u->creatorId();
        $wpStart = microtime(true);
        $prodIds = WarehouseProduct::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->where('warehouse_id', $req->warehouse_id)->pluck('product_id');
        $this->logExecutionTime($wpStart, $action, 'pluckWarehouseProductIds');
        $psStart = microtime(true);
        $prods = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->whereIn('id', $prodIds)->pluck('name', 'id');
        $this->logExecutionTime($psStart, $action, 'pluckProductServices');
        Log::info("[{$class}::{$action}] products fetched", ['method' => $method, 'product_count' => is_countable($prods) ? count($prods) : null]);
        return response()->json($prods->toArray());
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'warehouse_id' => $req->warehouse_id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'warehouse_id' => $req->warehouse_id]);
  }

  public const CRT_DSC = 'cartDiscount';
  public function cartDiscount(Request $req): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      Log::info("[{$class}::{$action}] called", ['method' => $method, 'discount' => $req->discount]);
      try {
        $cartStart = microtime(true);
        $cart = session('pos', []);
        $this->logExecutionTime($cartStart, $action, 'loadCart');
        $subStart = microtime(true);
        $sub = array_sum(array_column($cart, 'subtotal'));
        $this->logExecutionTime($subStart, $action, 'computeSubtotal');
        $totStart = microtime(true);
        $tot = User::priceFormats($sub - ($req->discount ?? 0));
        $this->logExecutionTime($totStart, $action, 'computeTotal');
        Log::info("[{$class}::{$action}] total calculated", ['method' => $method, 'total' => $tot]);
        return response()->json(['total' => $tot]);
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'discount' => $req->discount, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'discount' => $req->discount]);
  }

  public const POS_VW = 'posView';
  public function posView(Request $req, string $encId): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewBase = ViewsConstants::POS_TMP;
    return $this->measureProfile($action, function () use ($req, $encId, $action, $method, $class, $viewBase) {
      Log::info("[{$class}::{$action}] requested", ['method' => $method, 'encId' => $encId, UsersConstants::COL_USER_ID => $req->user()?->id]);
      if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
        Log::warning("[{$class}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $req->user()->id]);
        return $r;
      }
      try {
        $decStart = microtime(true);
        $id = Crypt::decrypt($encId);
        $this->logExecutionTime($decStart, $action, 'decryptId');
        $findStart = microtime(true);
        $pos = Pos::findOrFail($id);
        $this->logExecutionTime($findStart, $action, 'findPos');
        if ($pos[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) {
          Log::warning("[{$class}::{$action}] unauthorized access", ['pos_id' => $id]);
          return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
        }
        $tplStart = microtime(true);
        $tpl = $this->_template($pos[DatabaseConstants::COL_TABLE_CREATOR]);
        $this->logExecutionTime($tplStart, $action, 'resolveTemplate');
        $viewPath = $viewBase . $tpl;
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $dataStart = microtime(true);
        $data = $this->_templateData($pos);
        $this->logExecutionTime($dataStart, $action, 'prepareTemplateData');
        Log::info("[{$class}::{$action}] rendering template", ['pos_id' => $id, 'view' => $viewPath]);
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, $data);
        $this->logExecutionTime($renderStart, $action, 'renderPosTemplate');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'encId' => $encId, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'encId' => $encId]);
  }

  public const PV_POS = 'previewPos';
  public function previewPos(Request $req, string $tpl, string $col): Response
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewBase = ViewsConstants::PRC_TMP;
    return $this->measureProfile($action, function () use ($req, $tpl, $col, $action, $class, $viewBase) {
      Log::info("[{$class}::{$action}] requested", ['tpl' => $tpl, 'col' => $col]);
      try {
        $dataStart = microtime(true);
        $view = $this->_previewData($tpl, $col);
        $this->logExecutionTime($dataStart, $action, 'composePreviewData');
        $viewPath = $viewBase . 'settings' . $tpl;
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, $view);
        $this->logExecutionTime($renderStart, $action, 'renderPreviewPos');
        Log::info("[{$class}::{$action}] complete", ['view' => $viewPath]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'tpl' => $tpl, 'col' => $col, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'tpl' => $tpl, 'col' => $col]);
  }

  public const SV_POS_TMP = 'savePosTemplateSettings';
  public function savePosTemplateSettings(Request $req): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      Log::info("[{$class}::{$action}] called", ['method' => $method]);
      $userOrRedirect = self::_checkLogin();
      if ($userOrRedirect instanceof RedirectResponse) {
        Log::warning("[{$class}::{$action}] unauthenticated");
        return $userOrRedirect;
      }
      try {
        $userId = $userOrRedirect->creatorId();
        $data = $req->except('_token');
        $data['pos_color'] ??= 'ffffff';
        if ($req->hasFile('pos_logo')) {
          $uploadStart = microtime(true);
          $path = Utility::uploadFile($req, 'pos_logo', $userId . '_logo.png', 'pos_logo/', ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]);
          $this->logExecutionTime($uploadStart, $action, 'uploadLogo');
          if ($path['flag'] === 0) {
            Log::debug("[{$class}::{$action}] logo upload failed context", ['msg' => $path['msg'], UsersConstants::COL_USER_ID => $userId]);
            Log::error("[{$class}::{$action}] logo upload failed", ['msg' => $path['msg']]);
            return redirect()->back()->with('error', __($path['msg']));
          }
          $data['pos_logo'] = $userId . '_logo.png';
          Log::info("[{$class}::{$action}] logo uploaded", ['filename' => $data['pos_logo']]);
        }
        $safeUserId = (\App\Models\User::where('id', $userId)->exists()) ? $userId : null;
        $txnStart = microtime(true);
        DB::transaction(function () use ($data, $safeUserId, $action) {
          $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
          $insStart = microtime(true);
          foreach ($data as $k => $v) DB::insert('INSERT INTO settings (`value`,`name`,`' . $creatorCol . '`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)', [$v, $k, $safeUserId]);
          $this->logExecutionTime($insStart, $action, 'upsertSettings');
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        Log::info("[{$class}::{$action}] settings saved", [UsersConstants::COL_USER_ID => $userId, 'data' => $data]);
        return redirect()->back()->with('success', __('POS Setting updated successfully'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const PRT_VW = 'printView';
  public function printView(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.printview';
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] requested", ['method' => $method, UsersConstants::COL_USER_ID => $req->user()?->id]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$class}::{$action}] unauthenticated");
        return $u;
      }
      if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
        Log::warning("[{$class}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $u->id]);
        return $r;
      }
      try {
        $cart = session('pos', []);
        if (empty($cart)) {
          Log::warning("[{$class}::{$action}] empty cart");
          return redirect()->back()->with('error', 'Cart is empty.');
        }
        $creatorId = $u->creatorId();
        $custStart = microtime(true);
        $customer = Customer::where('name', $req->vc_name)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
        $this->logExecutionTime($custStart, $action, 'findCustomer');
        $whStart = microtime(true);
        $warehouse = Warehouse::where('id', $req->warehouse_name)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
        $this->logExecutionTime($whStart, $action, 'findWarehouse');
        $details = ['pos_id' => $u->posNumberFormat(self::invoiceNumber()), 'customer' => $customer->toArray(), 'warehouse' => $warehouse->toArray(), 'user' => $u->toArray(), 'date' => now()->toDateString(), 'pay' => 'show'];
        $settingsStart = microtime(true);
        $settings = Utility::settings();
        $this->logExecutionTime($settingsStart, $action, 'loadSettings');
        $composeStart = microtime(true);
        $this->_composeDetails($details, $settings);
        $this->logExecutionTime($composeStart, $action, 'composeDetails');
        $sumStart = microtime(true);
        [$sales, $subtotal] = $this->_summarizeCart($cart);
        $this->logExecutionTime($sumStart, $action, 'summarizeCart');
        $disc = $req->discount ?? 0;
        $sales['discount'] = $u->priceFormat($disc);
        $sales['sub_total'] = $u->priceFormat($subtotal);
        $sales['total'] = $u->priceFormat($subtotal - $disc);
        $barcode = ['barcodeType' => $u->barcodeType(), 'barcodeFormat' => $u->barcodeFormat()];
        $prodStart = microtime(true);
        $prodList = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($prodStart, $action, 'fetchProducts');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] view data ready", ['method' => $method, 'cart_count' => count($cart), 'products_count' => is_countable($prodList) ? count($prodList) : null]);
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, ['details' => $details, 'sales' => $sales, 'customer' => $customer, 'productServices' => $prodList, 'barcode' => $barcode]);
        $this->logExecutionTime($renderStart, $action, 'renderPrintView');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const INV_POS_N = 'invoicePosNumber';
  public function invoicePosNumber(Request $request): int|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
      $user = $r;
      if ($deny = self::_authorize($req, PermissionsConstants::MNG_POS)) return $deny;
      Log::info("[{$class}::{$action}] calculating next POS number", [UsersConstants::COL_USER_ID => $user?->id]);
      try {
        $latestStart = microtime(true);
        $latest = Pos::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->latest()->first();
        $this->logExecutionTime($latestStart, $action, 'fetchLatestPos');
        $calcStart = microtime(true);
        $next = $latest ? $latest->pos_id + 1 : 1;
        $this->logExecutionTime($calcStart, $action, 'computeNextPosNumber');
        Log::info("[{$class}::{$action}] next POS number", ['next' => $next]);
        return $next;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['user_id' => $user?->id, 'creator_id' => $user?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::error("[{$class}::{$action}] failed", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function receipt(Request $req, ?string $encId = null): View|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::POS . '.receipt';
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
      $user = $r;
      if ($deny = self::_authorize($req, PermissionsConstants::MNG_POS)) return $deny;
      Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id]);
      try {
        $ids = (array) $req->input('product_id', []);
        if (empty($ids)) return redirect()->back()->with('error', __('Product is required.'));
        $fetchStart = microtime(true);
        $productServices = ProductService::whereIn('id', $ids)->get();
        $this->logExecutionTime($fetchStart, $action, 'fetchProducts');
        $quantity = (int) $req->input('quantity', 1);
        $barcodeType = $user?->barcodeType() ?: 'code128';
        $barcodeFormat = $user?->barcodeFormat() ?: 'css';
        $barcode = ['barcodeType' => $barcodeType, 'barcodeFormat' => $barcodeFormat];
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] prepared data", ['count' => $productServices->count()]);
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('productServices', 'barcode', 'quantity'));
        $this->logExecutionTime($renderStart, $action, 'renderReceipt');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => $user?->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::error("[{$class}::{$action}] failed", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function pos(Request $request, string $encId): View|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewBase = ViewsConstants::POS_TMP;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $encId, $action, $class, $viewBase) {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
      $user = $r;
      if ($deny = self::_authorize($req, PermissionsConstants::MNG_POS)) return $deny;
      Log::info("[{$class}::{$action}] start", ['encId' => $encId, UsersConstants::COL_USER_ID => $user?->id]);
      try {
        $decStart = microtime(true);
        $id = Crypt::decrypt($encId);
        $this->logExecutionTime($decStart, $action, 'decryptId');
        $findStart = microtime(true);
        $pos = Pos::with('items.product', 'customer')->findOrFail($id);
        $this->logExecutionTime($findStart, $action, 'findPosWithRelations');
        if ($pos[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
        $payStart = microtime(true);
        $posPayment = PosPayment::where('pos_id', $pos->id)->first();
        $this->logExecutionTime($payStart, $action, 'findPosPayment');
        $setStart = microtime(true);
        $settings = Utility::settingsById($pos[DatabaseConstants::COL_TABLE_CREATOR]);
        $this->logExecutionTime($setStart, $action, 'loadSettingsById');
        $customer = $pos->customer;
        $aggStart = microtime(true);
        $totalTaxPrice = $totalQuantity = $totalRate = $totalDiscount = 0;
        $taxesData = [];
        $items = [];
        foreach ($pos->items as $it) {
          $name = $it->product?->name ?? '';
          $qty = $it->quantity;
          $price = $it->price;
          $discount = $it->discount;
          $taxRate = $it->tax;
          $totalQuantity += $qty;
          $totalRate += $price;
          $totalDiscount += $discount;
          $itemTaxes = [];
          if ($taxRate) {
            foreach (Utility::tax($taxRate) as $tax) {
              $taxPrice = Utility::taxRate($tax->rate, $price, $qty, $discount);
              $totalTaxPrice += $taxPrice;
              $itemTaxes[] = ['name' => $tax->name, 'rate' => $tax->rate . '%', 'price' => Utility::priceFormat($settings, $taxPrice)];
              $taxesData[$tax->name] = ($taxesData[$tax->name] ?? 0) + $taxPrice;
            }
          }
          $items[] = (object) ['name' => $name, 'quantity' => $qty, 'tax' => $taxRate, 'discount' => $discount, 'price' => $price, 'description' => $it->description, 'itemTax' => $itemTaxes];
        }
        $pos->itemData = $items;
        $pos->totalTaxPrice = $totalTaxPrice;
        $pos->totalQuantity = $totalQuantity;
        $pos->totalRate = $totalRate;
        $pos->totalDiscount = $totalDiscount;
        $pos->taxesData = $taxesData;
        $this->logExecutionTime($aggStart, $action, 'aggregateItems');
        $logoPath = asset(Storage::url('uploads/logo/'));
        $companyLogo = Utility::getValByName(SettingsConstants::CPN_LG_DK);
        $posLogo = $settings['pos_logo'] ?? '';
        $img = $posLogo ? Utility::getFile('pos_logo/') . $posLogo : asset($logoPath . '/' . ($companyLogo ?: SettingsConstants::CPN_LG_DK_DEF));
        $color = '#' . ($settings['pos_color'] ?? 'ffffff');
        $fontColor = Utility::getFontColor($color);
        $tpl = $settings[BillsConstants::COL_POS_TMP] ?? 'default';
        $viewPath = $viewBase . $tpl;
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] succeeded", ['pos_id' => $pos->id, 'template' => $tpl]);
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('pos', 'posPayment', 'color', 'settings', 'customer', 'img', 'fontColor'));
        $this->logExecutionTime($renderStart, $action, 'renderPosView');
        return $resp;
      } catch (Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'encId' => $encId, 'user_id' => $user?->id, 'creator_id' => $user?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::error("[{$class}::{$action}] failed", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'encId' => $encId]);
  }

  private function _composeDetails(array &$d, array $s): void
  {
    Log::info('_composeDetails start', [
      'warehouse' => $d['warehouse']['name'] ?? null,
      'customer'  => $d['customer']['name']  ?? null,
      'method'    => __METHOD__,
    ]);
    // warehouse details
    $ws = '<h7 class="text-dark">' . ucfirst($d['warehouse']['name']) . '</h7>';
    $d['warehouse']['details'] = $ws;

    if ($d['customer']) {
      $this->_addrCompose($d['customer'], 'billing');
      $this->_addrCompose($d['customer'], 'shipping');
      $cust = $d['customer'];
      $d['customer']['details'] =
        '<h6 class="text-dark">' . ucfirst($cust['name']) .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_phone'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_address'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_city'] . $cust['billing_state'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_country'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_zip'] . '</p></h6>';
      $d['customer']['shippdetails'] =
        '<h6 class="text-dark"><b>' . ucfirst($cust['name']) .
        '</b><p class="m-0 h6 font-weight-normal">' . $cust['shipping_phone'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_address'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_city'] . $cust['shipping_state'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_country'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_zip'] . '</p></h6>';
    } else {
      $d['customer']['details'] = '<h2 class="h6"><b>Walk-in Customer</b><h2>';
      $d['customer']['shippdetails'] = '-';
    }

    // company details fallbacks
    $s['company_telephone'] = $s['company_telephone'] ? ', ' . $s['company_telephone'] : '';
    $s['company_state']    = $s['company_state']     ? ', ' . $s['company_state']     : '';

    // user (company) details
    $usr = $d['user'];
    $d['user']['details'] =
      '<h6 class="text-dark"><b>' . ucfirst($usr['name']) . '</b>' .
      '<h2 class="font-weight-normal">' .
      '<p class="m-0 font-weight-normal">' . $s['company_name'] . $s['company_telephone'] . '</p>' .
      '<p class="m-0 font-weight-normal">' . $s['company_address'] . '</p>' .
      '<p class="m-0 h6 font-weight-normal">' . $s['company_city'] . $s['company_state'] . '</p>' .
      '<p class="m-0 font-weight-normal">' . $s['company_country'] . '</p>' .
      '<p class="m-0 font-weight-normal">' . $s['company_zipcode'] . '</p></h2>';
    Log::info('_composeDetails complete', ['method' => __METHOD__]);
  }

  private function _addrCompose(array &$a, string $type): void
  {
    Log::info('_addrCompose', [
      'type'   => $type,
      'state'  => $a["{$type}_state"] ?? null,
      'method' => __METHOD__,
    ]);
    $k = "{$type}_state";
    $a[$k] = $a[$k] ? ', ' . $a[$k] : '';
  }

  private function _summarizeCart(array $cart): array|RedirectResponse
  {
    Log::info('_summarizeCart start', [
      'count'  => count($cart),
      'method' => __METHOD__,
    ]);
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::warning('_summarizeCart unauthenticated', ['method' => __METHOD__]);
      return $userOrRedirect;
    }
    $user = $userOrRedirect;
    $sum = 0;
    $out = ['data' => []];

    foreach ($cart as $k => $v) {
      $sub = $v['price'] * $v['quantity'];
      $tax = ($sub * $v['tax']) / 100;
      $out['data'][$k] = [
        'name'        => $v['name'],
        'quantity'    => $v['quantity'],
        'price'       => $user?->priceFormat($v['price']),
        'tax'         => $v['tax'] . '%',
        'product_tax' => $v['product_tax'],
        'tax_amount'  => $user?->priceFormat($tax),
        'subtotal'    => $user?->priceFormat($v['subtotal']),
      ];
      $sum += $v['subtotal'];
    }

    Log::info('_summarizeCart complete', [
      'sum'    => $sum,
      'method' => __METHOD__,
    ]);
    return [$out, $sum];
  }

  private function _template(int $uid): string
  {
    $template = Utility::settingsById($uid)[BillsConstants::COL_POS_TMP] ?? 'template1';
    Log::info('_template resolved', [
      UsersConstants::COL_USER_ID  => $uid,
      'template' => $template,
      'method'   => __METHOD__,
    ]);
    return $template;
  }

  private function _templateData(Pos $pos): array
  {
    Log::info('_templateData start', [
      'pos_id' => $pos->id,
      'method' => __METHOD__,
    ]);
    $settings = Utility::settingsById($pos[DatabaseConstants::COL_TABLE_CREATOR]);
    $logoPath = Utility::getFile('pos_logo/') . ($settings['pos_logo'] ?? '');

    $data = [
      'pos'        => $pos,
      'posPayment' => PosPayment::where('pos_id', $pos->id)->first(),
      'color'      => '#' . $settings['pos_color'],
      'settings'   => $settings,
      'customer'   => $pos->customer,
      'img'        => $logoPath
        ?: asset(Storage::url('uploads/logo/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF))),
      'font_color' => Utility::getFontColor('#' . $settings['pos_color']),
    ];

    Log::info('_templateData complete', [
      'pos_id' => $pos->id,
      'method' => __METHOD__,
    ]);
    return $data;
  }

  private function _previewData(string $tpl, string $col): array|RedirectResponse
  {
    Log::info('_previewData start', [
      'template' => $tpl,
      'color'    => $col,
      'method'   => __METHOD__,
    ]);
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::warning('_previewData unauthenticated', ['method' => __METHOD__]);
      return $userOrRedirect;
    }

    $settings = Utility::settings();
    $pos = new Pos([
      'pos_id'         => 1,
      'issue_date'     => now(),
      'itemData'       => [],
      'totalTaxPrice'  => 0,
      'totalQuantity'  => 0,
      'totalRate'      => 0,
      'totalDiscount'  => 0,
      'taxesData'      => [],
      DatabaseConstants::COL_TABLE_CREATOR     => $userOrRedirect->creatorId(),
    ]);

    $view = [
      'pos'        => $pos,
      'preview'    => 1,
      'color'      => '#' . $col,
      'img'        => asset(Storage::url('uploads/logo/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF))),
      'settings'   => $settings,
      'customer'   => new \stdClass(),
      'font_color' => Utility::getFontColor('#' . $col),
      'posPayment' => new PosPayment(['amount' => 360, 'discount' => 100]),
    ];

    Log::info('_previewData complete', [
      'template' => $tpl,
      'method'   => __METHOD__,
    ]);
    return $view;
  }

  private static function _authorize(Request $req, string $perm): RedirectResponse|JsonResponse|null
  {
    if (!$req->user()->can($perm)) {
      Log::warning('Permission denied', [
        UsersConstants::COL_USER_ID    => $req->user()->id,
        'permission' => $perm,
        'method'     => __METHOD__,
      ]);
      return defaultPermissionDenial(
        $req,
        new \Exception('permission denied'),
        __CLASS__ . '::_authorize'
      );
    }
    Log::info('Permission granted', [
      UsersConstants::COL_USER_ID    => $req->user()->id,
      'permission' => $perm,
      'method'     => __METHOD__,
    ]);
    return null;
  }

  private static function _validate(
    array $d,
    array $r
  ): RedirectResponse|JsonResponse|null {
    $v = Validator::make($d, $r);
    return $v->fails()
      ? redirect()->back()->with('error', $v->getMessageBag()->first())
      : null;
  }

  private static function validateData(array $data, array $rules): ?RedirectResponse
  {
    $v = Validator::make($data, $rules);
    if ($v->fails()) {
      $msg = $v->getMessageBag()->first();
      Log::warning('Validation failed', [
        'errors' => $v->errors()->all(),
        'method' => __METHOD__,
      ]);
      return redirect()->back()->with('error', $msg);
    }
    Log::info('Validation passed', ['method' => __METHOD__]);
    return null;
  }

  private static function handleException(Request $req, Throwable $e): RedirectResponse|JsonResponse|null
  {
    Log::error('Unhandled exception', [
      'error'  => $e->getMessage(),
      'method' => __METHOD__,
    ]);
    return defaultUndefinedException($req, $e, __CLASS__ . '::handleException');
  }

  private static function invoiceNumber(): int
  {
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::error('invoiceNumber: unauthenticated', ['method' => __METHOD__]);
      throw new \Exception('unauthenticated');
    }
    $uid   = $userOrRedirect->creatorId();
    $latest = Pos::where(DatabaseConstants::COL_TABLE_CREATOR, $uid)->latest()->first();
    $next  = $latest ? $latest->pos_id + 1 : 1;
    Log::info('Next POS invoice number', [
      UsersConstants::COL_USER_ID => $userOrRedirect->id,
      'next'    => $next,
      'method'  => __METHOD__,
    ]);
    return $next;
  }
}

// ! ALERT getProduct joins by product_id == warehouse_id; confirm logic to avoid empty/incorrect datasets.