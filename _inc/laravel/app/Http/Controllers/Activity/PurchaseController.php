<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BillsConstants,
    DatabaseConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Models\{
    BankAccount,
    Bill,
    CustomField,
    ProductService,
    ProductServiceCategory,
    Purchase,
    PurchasePayment,
    PurchaseProduct,
    StockReport,
    Transaction,
    User,
    Utility,
    Vendor,
    Warehouse,
    WarehouseProduct,
    WarehouseTransfer
};
use App\Traits\{
    ChecksLogin,
    ChecksPermissions
};
use Google\Service\FirebaseAppHosting\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Crypt,
    DB,
    Log,
    Route,
    Storage,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;

class PurchaseController extends Controller
{
    private const ROUTE_INDEX = ViewsConstants::PRC . '.index';
    private const ROUTE_SHOW  = ViewsConstants::PRC . '.show';

    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::PRC . '.' . $action;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'view purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            try {
                $fetchStart = microtime(true);
                Log::debug("[{$class}::{$action}] fetching vendors and purchases", ['creator_id' => $user?->creatorId()]);
                $vendors = Vendor::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Vendor', '');
                $status = Purchase::$statuses;
                $purchases = Purchase::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->with(['vendor', 'category'])->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchIndexData');
                Log::info("[{$class}::{$action}] dataset ready", ['purchase_count' => $purchases->count(), 'vendor_options' => $vendors->count()]);
                $renderStart = microtime(true);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'compact_vars' => ['purchases', 'status', 'vendors']]);
                $resp = view($viewPath, compact('purchases', 'status', 'vendors'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['message' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function create(Request $request, string|int $vendorId): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::PRC . '.' . $action;
        return $this->measureProfile($action, function () use ($request, $vendorId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'create purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'vendor_id' => $vendorId, 'method' => $method]);
            try {
                $loadStart = microtime(true);
                Log::debug("[{$class}::{$action}] loading form data", ['creator_id' => $user?->creatorId()]);
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->where('module', 'purchase')->get();
                $category = ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->where('type', 'expense')->pluck('name', 'id')->prepend('Select Category', '');
                $purchaseNumber = $user?->purchaseNumberFormat($this->purchaseNumber());
                $vendors = Vendor::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Vendor', '');
                $warehouse = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->prepend('Select Warehouse', '');
                $productServices = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->where('type', '!=', 'service')->pluck('name', 'id')->prepend('--', '');
                $this->logExecutionTime($loadStart, $action, 'loadCreateFormData');
                Log::info("[{$class}::{$action}] form data ready", ['custom_fields' => $customFields->count(), 'product_services' => $productServices->count()]);
                $renderStart = microtime(true);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'compact_vars' => ['vendors', 'purchaseNumber', 'productServices', 'category', 'customFields', 'vendorId', 'warehouse']]);
                $resp = view($viewPath, compact('vendors', 'purchaseNumber', 'productServices', 'category', 'customFields', 'vendorId', 'warehouse'));
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['message' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'vendor_id' => $vendorId]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'create purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            try {
                Log::info("[{$class}::{$action}] starting DB transaction", [UsersConstants::COL_USER_ID => $user?->id]);
                $txnStart = microtime(true);
                $purchase = DB::transaction(function () use ($request, $user, $action, $class) {
                    $validator = Validator::make($request->all(), ['vendor_id' => 'required', 'warehouse_id' => 'required', 'purchase_date' => 'required', 'category_id' => 'required', 'items' => 'required']);
                    if ($validator->fails()) {
                        Log::warning("[{$class}::{$action}] validation failed", ['errors' => $validator->errors()->all()]);
                        throw new \InvalidArgumentException($validator->errors()->first());
                    }
                    $purchase = new Purchase();
                    $purchase->purchase_id = $this->purchaseNumber();
                    $purchase->vendor_id = $request->vendor_id;
                    $purchase->warehouse_id = $request->warehouse_id;
                    $purchase->purchase_date = $request->purchase_date;
                    $purchase->purchase_number = $request->purchase_number ?? 0;
                    $purchase->status = 0;
                    $purchase->category_id = $request->category_id;
                    $purchase[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
                    $purchase->save();
                    Log::info("[{$class}::{$action}] purchase created", ['purchase_id' => $purchase->id]);
                    $loopStart = microtime(true);
                    foreach ($request->items as $item) {
                        $pp = new PurchaseProduct();
                        $pp->purchase_id = $purchase->id;
                        $pp->product_id = $item['item'];
                        $pp->quantity = $item['quantity'];
                        $pp->tax = $item['tax'];
                        $pp->discount = $item['discount'];
                        $pp->price = $item['price'];
                        $pp->description = $item['description'];
                        $pp->save();
                        Log::debug("[{$class}::{$action}] item stored", ['pp_id' => $pp->id, 'product_id' => $pp->product_id, 'quantity' => $pp->quantity]);
                        Utility::totalQuantity('plus', $pp->quantity, $pp->product_id);
                        $desc = $item['quantity'] . '  quantity add in purchase ' . $user?->purchaseNumberFormat($purchase->purchase_id);
                        Utility::addProductStock($item['item'], $item['quantity'], 'purchase', $desc, $purchase->id);
                        Utility::addWarehouseStock($item['item'], $item['quantity'], $request->warehouse_id);
                    }
                    $this->logExecutionTime($loopStart, $action, 'storeItemsLoop');
                    return $purchase;
                });
                $this->logExecutionTime($txnStart, $action, 'storeTransaction');
                Log::info("[{$class}::{$action}] DB transaction committed", ['purchase_id' => $purchase->id, UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->route(self::ROUTE_SHOW, ['purchase' => $purchase->id])->with('success', __('Purchase successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function show(Request $request, string $ids): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::PRC . '.' . $action;
        return $this->measureProfile($action, function () use ($request, $ids, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'show purchase', self::ROUTE_SHOW)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            try {
                $decryptStart = microtime(true);
                $id = Crypt::decrypt($ids);
                $this->logExecutionTime($decryptStart, $action, 'decryptPurchaseId');
                Log::debug("[{$class}::{$action}] id decrypted", ['id' => $id]);
                $loadStart = microtime(true);
                $purchase = Purchase::findOrFail($id);
                if ($purchase[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new AuthorizationException();
                $purchasePayment = PurchasePayment::where('purchase_id', $id)->first();
                $vendor = $purchase->vendor;
                $items = $purchase->items;
                $this->logExecutionTime($loadStart, $action, 'loadPurchaseAndRelations');
                Log::info("[{$class}::{$action}] purchase ready", ['purchase_id' => $id, 'items_count' => (is_countable($items) ? count($items) : 0)]);
                $renderStart = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$class}::{$action}] view missing", ['view_path' => $viewPath]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'compact_vars' => ['purchase', 'vendor', 'items', 'purchasePayment']]);
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                return view($viewPath, compact('purchase', 'vendor', 'items', 'purchasePayment'));
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] authorization failed", [UsersConstants::COL_USER_ID => $user?->id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['message' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'enc_id' => $ids]);
    }

    public function edit(Request $request, string $ids): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::PRC . '.' . $action;
        return $this->measureProfile($action, function () use ($request, $ids, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($r = self::guard($request, 'edit purchase', ViewsConstants::PRC . '.' . $action)) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            try {
                $decryptStart = microtime(true);
                $id = Crypt::decrypt($ids);
                $this->logExecutionTime($decryptStart, $action, 'decryptPurchaseId');
                $loadStart = microtime(true);
                $purchase = Purchase::findOrFail($id);
                if ($purchase[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new AuthorizationException();
                $category = ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->where('type', 'expense')->pluck('name', 'id')->prepend('Select Category', '');
                $vendors = Vendor::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->prepend('Select Vendor', '');
                $warehouse = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->prepend('Select Warehouse', '');
                $productServices = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->where('type', '!=', 'service')->pluck('name', 'id')->prepend('--', '');
                $purchaseNumber = $user?->purchaseNumberFormat($purchase->purchase_id);
                $this->logExecutionTime($loadStart, $action, 'loadEditData');
                Log::info("[{$class}::{$action}] data ready", ['purchase_id' => $id]);
                $renderStart = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$class}::{$action}] view missing", ['view_path' => $viewPath]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'compact_vars' => ['purchase', 'vendors', 'productServices', 'warehouse', 'category', 'purchaseNumber']]);
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return view($viewPath, compact('purchase', 'vendors', 'productServices', 'warehouse', 'category', 'purchaseNumber'));
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] authorization failed", [UsersConstants::COL_USER_ID => $user?->id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['message' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'enc_id' => $ids]);
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $purchase, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'edit purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method, 'purchase_id' => $purchase->id]);
            try {
                if ($purchase[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new AuthorizationException();
                $valStart = microtime(true);
                $validator = Validator::make($request->all(), ['vendor_id' => 'required', 'purchase_date' => 'required', 'items' => 'required']);
                if ($validator->fails()) {
                    Log::warning("[{$class}::{$action}] validation failed", ['errors' => $validator->errors()->all(), 'purchase_id' => $purchase->id]);
                    return redirect()->route(self::ROUTE_INDEX)->with('error', $validator->errors()->first());
                }
                $this->logExecutionTime($valStart, $action, 'validateUpdate');
                Log::info("[{$class}::{$action}] starting DB transaction", ['purchase_id' => $purchase->id]);
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $purchase, $user, $action, $class) {
                    $updStart = microtime(true);
                    $purchase->update(['vendor_id' => $request->vendor_id, 'purchase_date' => $request->purchase_date, 'category_id' => $request->category_id]);
                    $this->logExecutionTime($updStart, $action, 'updatePurchaseRow');
                    Log::debug("[{$class}::{$action}] purchase updated", ['purchase_id' => $purchase->id]);
                    $delStart = microtime(true);
                    StockReport::where('type', 'purchase')->where('type_id', $purchase->id)->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteOldStockReports');
                    $loopStart = microtime(true);
                    foreach ($request->items as $item) {
                        $pp = PurchaseProduct::find($item['id'] ?? null) ?? new PurchaseProduct(['purchase_id' => $purchase->id]);
                        $oldQty = $pp->exists ? $pp->quantity : 0;
                        if ($pp->exists) Utility::totalQuantity('minus', $oldQty, $pp->product_id);
                        $pp->product_id = $item['item'] ?? $pp->product_id;
                        $pp->quantity = $item['quantity'];
                        $pp->tax = $item['tax'];
                        $pp->discount = $item['discount'];
                        $pp->price = $item['price'];
                        $pp->description = $item['description'];
                        $pp->save();
                        Log::debug("[{$class}::{$action}] item upserted", ['pp_id' => $pp->id, 'product_id' => $pp->product_id, 'old_qty' => $oldQty, 'new_qty' => $pp->quantity]);
                        Utility::totalQuantity('plus', $pp->quantity, $pp->product_id);
                        $desc = $pp->quantity . '  quantity add in purchase ' . $user?->purchaseNumberFormat($purchase->purchase_id);
                        Utility::addProductStock($pp->product_id, $pp->quantity, 'purchase', $desc, $purchase->id);
                        $diff = $pp->quantity - $oldQty;
                        Utility::addWarehouseStock($pp->product_id, $diff, $request->warehouse_id);
                    }
                    $this->logExecutionTime($loopStart, $action, 'upsertItemsLoop');
                });
                $this->logExecutionTime($txnStart, $action, 'updateTransaction');
                Log::info("[{$class}::{$action}] DB transaction committed", ['purchase_id' => $purchase->id]);
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Purchase successfully updated.'));
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] authorization failed", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $purchase->id]);
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id' => $purchase->id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id' => $purchase->id]);
    }

    public function destroy(Request $request, Purchase $purchase): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $purchase, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'delete purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $purchase->id, 'method' => $method]);
            try {
                Log::info("[{$class}::{$action}] starting DB transaction", ['purchase_id' => $purchase->id]);
                $txnStart = microtime(true);
                DB::transaction(function () use ($purchase, $action, $class) {
                    $payLoopStart = microtime(true);
                    foreach ($purchase->payments as $pay) {
                        Log::debug("[{$class}::{$action}] deleting payment", ['pay_id' => $pay->id]);
                        $pay->delete();
                    }
                    $this->logExecutionTime($payLoopStart, $action, 'deletePaymentsLoop');
                    $itemLoopStart = microtime(true);
                    foreach ($purchase->items as $item) {
                        foreach (WarehouseTransfer::where('product_id', $item->product_id)->where('from_warehouse', $purchase->warehouse_id)->get() as $t) {
                            Log::debug("[{$class}::{$action}] reversing warehouse transfer", ['transfer_id' => $t->id]);
                            $to = WarehouseProduct::where('warehouse_id', $t->to_warehouse)->first();
                            if ($to) {
                                $to->decrement('quantity', $t->quantity);
                                if ($to->quantity <= 0) $to->delete();
                            }
                        }
                        WarehouseProduct::where('warehouse_id', $purchase->warehouse_id)->where('product_id', $item->product_id)->decrement('quantity', $item->quantity);
                        WarehouseProduct::where('warehouse_id', $purchase->warehouse_id)->where('product_id', $item->product_id)->where('quantity', '<=', 0)->delete();
                        ProductService::where('id', $item->product_id)->decrement('quantity', $item->quantity);
                        Log::debug("[{$class}::{$action}] deleting purchase item", ['item_id' => $item->id, 'product_id' => $item->product_id]);
                        $item->delete();
                    }
                    $this->logExecutionTime($itemLoopStart, $action, 'deleteItemsLoop');
                    $rowDelStart = microtime(true);
                    $purchase->delete();
                    $this->logExecutionTime($rowDelStart, $action, 'deletePurchaseRow');
                    Log::info("[{$class}::{$action}] purchase deleted", ['purchase_id' => $purchase->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'destroyTransaction');
                Log::info("[{$class}::{$action}] DB transaction committed", ['purchase_id' => $purchase->id]);
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Purchase successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id' => $purchase->id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id' => $purchase->id]);
    }

    public function sent(Request $request, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'send purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $id, 'method' => $method]);
            try {
                $updateStart = microtime(true);
                $purchase = Purchase::findOrFail($id);
                $purchase->send_date = now()->toDateString();
                $purchase->status = 1;
                $purchase->save();
                $this->logExecutionTime($updateStart, $action, 'updatePurchaseSent');
                Log::debug("[{$class}::{$action}] marked as sent", ['purchase_id' => $purchase->id, 'status' => $purchase->status]);
                $prepStart = microtime(true);
                $vendor = Vendor::find($purchase->vendor_id);
                $name = $vendor->name ?? '';
                $purchase->name = $name;
                $purchase->purchase = $user?->purchaseNumberFormat($purchase->purchase_id);
                $purchaseId = Crypt::encrypt($purchase->id);
                $purchase->url = route(ViewsConstants::PRC . '.pdf', $purchaseId);
                $this->logExecutionTime($prepStart, $action, 'prepareVendorAndLinks');
                $creditStart = microtime(true);
                Utility::userBalance('vendor', $vendor->id, $purchase->getTotal(), 'credit');
                $this->logExecutionTime($creditStart, $action, 'creditVendorBalance');
                Log::info("[{$class}::{$action}] vendor balance credited", ['vendor_id' => $vendor->id, 'amount' => $purchase->getTotal()]);
                $emailStart = microtime(true);
                $vendorArr = ['vendor_bill_name' => $name, 'vendor_bill_id' => $purchase->purchase, 'vendor_bill_url' => $purchase->url];
                $resp = Utility::sendEmailTemplate('vendor_bill_sent', [$vendor->id => $vendor->email], $vendorArr);
                $this->logExecutionTime($emailStart, $action, 'sendEmailTemplate');
                Log::info("[{$class}::{$action}] email template sent", ['template' => 'vendor_bill_sent', 'is_success' => $resp['is_success'] ?? false]);
                return redirect()->back()->with('success', __('Purchase successfully sent.') . (($resp['is_success'] === false && !empty($resp['error'])) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id' => $id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id' => $id]);
    }

    public function resent(Request $request, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'send purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $id, 'method' => $method]);
            try {
                $prepStart = microtime(true);
                $purchase = Purchase::findOrFail($id);
                $vendor = Vendor::find($purchase->vendor_id);
                $name = $vendor->name ?? '';
                $purchase->name = $name;
                $purchase->purchase = $user?->purchaseNumberFormat($purchase->purchase_id);
                $purchaseId = Crypt::encrypt($purchase->id);
                $purchase->url = route(ViewsConstants::PRC . '.pdf', $purchaseId);
                $this->logExecutionTime($prepStart, $action, 'prepareResendData');
                Log::debug("[{$class}::{$action}] data prepared for resend", ['purchase_id' => $purchase->id]);
                $emailStart = microtime(true);
                $vendorArr = ['vendor_bill_name' => $name, 'vendor_bill_id' => $purchase->purchase, 'vendor_bill_url' => $purchase->url];
                $resp = Utility::sendEmailTemplate('vendor_bill_sent', [$vendor->id => $vendor->email], $vendorArr);
                $this->logExecutionTime($emailStart, $action, 'sendEmailTemplateResent');
                Log::info("[{$class}::{$action}] email template resent", ['template' => 'vendor_bill_sent', 'is_success' => $resp['is_success'] ?? false]);
                return redirect()->back()->with('success', __('Purchase successfully sent.') . (($resp['is_success'] === false && !empty($resp['error'])) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] authorization failed", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $id]);
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id' => $id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id' => $id]);
    }

    public function purchase(Request $request, string $purchaseId): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $purchaseId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'show purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id_enc' => $purchaseId, 'method' => $method]);
            try {
                $decryptStart = microtime(true);
                $id = Crypt::decrypt($purchaseId);
                $this->logExecutionTime($decryptStart, $action, 'decryptPurchaseId');
                Log::debug("[{$class}::{$action}] decrypted id", ['id' => $id]);
                $loadStart = microtime(true);
                $purchase = Purchase::findOrFail($id);
                $settings = Utility::settings();
                $rows = DB::table('settings')->where(DatabaseConstants::COL_TABLE_CREATOR, $purchase[DatabaseConstants::COL_TABLE_CREATOR])->get();
                foreach ($rows as $row) $settings[$row->name] = $row->value;
                $this->logExecutionTime($loadStart, $action, 'loadPurchaseAndSettings');
                Log::debug("[{$class}::{$action}] settings loaded", ['count' => (is_countable($settings) ? count($settings) : 0), 'template' => $settings[BillsConstants::COL_PRC_TMP] ?? null]);
                $computeStart = microtime(true);
                $vendor = $purchase->vendor;
                $totalTaxPrice = 0;
                $totalQuantity = 0;
                $totalRate = 0;
                $totalDiscount = 0;
                $taxesData = [];
                $items = [];
                foreach ($purchase->items as $product) {
                    $item = new \stdClass();
                    $item->name = $product->product()?->name ?? '';
                    $item->quantity = $product->quantity;
                    $item->tax = $product->tax;
                    $item->discount = $product->discount;
                    $item->price = $product->price;
                    $item->description = $product->description;
                    $totalQuantity += $item->quantity;
                    $totalRate += $item->price;
                    $totalDiscount += $item->discount;
                    $taxes = Utility::tax($product->tax);
                    $itemTaxes = [];
                    foreach ($taxes as $tax) {
                        $taxPrice = Utility::taxRate($tax->rate, $item->price, $item->quantity, $item->discount);
                        $totalTaxPrice += $taxPrice;
                        $itemTax = ['name' => $tax->name, 'rate' => $tax->rate . '%', 'price' => Utility::priceFormat($settings, $taxPrice), 'tax_price' => $taxPrice];
                        $itemTaxes[] = $itemTax;
                        $taxesData[$tax->name] = ($taxesData[$tax->name] ?? 0) + $taxPrice;
                    }
                    $item->itemTax = $itemTaxes;
                    $items[] = $item;
                }
                $this->logExecutionTime($computeStart, $action, 'computeItemsTaxes');
                $prepStart = microtime(true);
                $color = '#' . $settings['purchase_color'];
                $font_color = Utility::getFontColor($color);
                $logo = asset(Storage::url('uploads/logo/'));
                $purchase_logo = Utility::getValByName('purchase_logo');
                $img = $purchase_logo ? Utility::getFile('purchase_logo/') . $purchase_logo : asset($logo . '/' . ($settings[SettingsConstants::CPN_LG_DK] ?? SettingsConstants::CPN_LG_DK_DEF));
                $this->logExecutionTime($prepStart, $action, 'prepareBranding');
                $viewPath = ViewsConstants::PRC_TMP . ($settings[BillsConstants::COL_PRC_TMP] ?? '');
                Log::info("[{$class}::{$action}] rendering template", ['template' => $settings[BillsConstants::COL_PRC_TMP] ?? null, 'view_path' => $viewPath]);
                $renderStart = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$class}::{$action}] view missing", ['view_path' => $viewPath]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($renderStart, $action, 'renderPurchaseTemplate');
                return view($viewPath, compact('purchase', 'color', 'settings', 'vendor', 'img', 'font_color'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id_enc' => $purchaseId]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id_enc' => $purchaseId]);
    }

    public const PV_PRC = 'previewPurchase';
    public function previewPurchase(Request $request, string $template, string $color): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $template, $color, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'template' => $template, 'color_hex' => '#' . $color, 'method' => $method]);
            $settings = Utility::settings();
            $purchase = new Purchase();
            $vendor = (object)['email' => '<Email>', 'shipping_name' => '<Vendor Name>', 'shipping_country' => '<Country>', 'shipping_state' => '<State>', 'shipping_city' => '<City>', 'shipping_phone' => '<Vendor Phone Number>', 'shipping_zip' => '<Zip>', 'shipping_address' => '<Address>', 'billing_name' => '<Vendor Name>', 'billing_country' => '<Country>', 'billing_state' => '<State>', 'billing_city' => '<City>', 'billing_phone' => '<Vendor Phone Number>', 'billing_zip' => '<Zip>', 'billing_address' => '<Address>'];
            $totalTaxPrice = 0;
            $taxesData = [];
            $items = [];
            $itemsStart = microtime(true);
            for ($i = 1; $i <= 3; $i++) {
                $item = new \stdClass();
                $item->name = 'Item ' . $i;
                $item->quantity = 1;
                $item->tax = 5;
                $item->discount = 50;
                $item->price = 100;
                $itemTaxes = [];
                $taxes = ['Tax 1', 'Tax 2'];
                foreach ($taxes as $tax) {
                    $taxPrice = 10;
                    $totalTaxPrice += $taxPrice;
                    $itemTax = ['name' => $tax, 'rate' => '10%', 'price' => '$10', 'tax_price' => 10];
                    $itemTaxes[] = $itemTax;
                    $taxesData[$tax] = ($taxesData[$tax] ?? 0) + $taxPrice;
                }
                $item->itemTax = $itemTaxes;
                $items[] = $item;
            }
            $this->logExecutionTime($itemsStart, $action, 'buildPreviewItems');
            $purchase->purchase_id = 1;
            $purchase->issue_date = now()->toDateTimeString();
            $purchase->itemData = $items;
            $purchase->totalTaxPrice = $totalTaxPrice;
            $purchase->totalQuantity = 3;
            $purchase->totalRate = 300;
            $purchase->totalDiscount = 10;
            $purchase->taxesData = $taxesData;
            $purchase[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
            $brandStart = microtime(true);
            $logo = asset(Storage::url('uploads/logo/'));
            $company_logo = Utility::getValByName(SettingsConstants::CPN_LG_DK);
            $settingsData = Utility::settingsById($purchase[DatabaseConstants::COL_TABLE_CREATOR]);
            $purchase_logo = $settingsData['purchase_logo'] ?? null;
            $img = $purchase_logo ? Utility::getFile('purchase_logo/') . $purchase_logo : asset($logo . '/' . ($company_logo ?? SettingsConstants::CPN_LG_DK_DEF));
            $this->logExecutionTime($brandStart, $action, 'prepareBranding');
            $viewPath = ViewsConstants::PRC_TMP . $template;
            Log::info("[{$class}::{$action}] rendering preview", ['view_path' => $viewPath]);
            if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            return view($viewPath, compact('purchase', 'preview', 'color', 'img', 'settings', 'vendor', 'font_color'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'template' => $template]);
    }

    public const SV_PCR_TMP_STG = 'savePurchaseTemplateSettings';
    public function savePurchaseTemplateSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            try {
                $prepStart = microtime(true);
                $post = $request->except('_token');
                if (isset($post[BillsConstants::COL_PRC_TMP]) && empty($post['purchase_color'])) $post['purchase_color'] = 'ffffff';
                if ($request->hasFile('purchase_logo')) {
                    $uploadStart = microtime(true);
                    $dir = 'purchase_logo/';
                    $filename = $user?->id . '_purchase_logo.png';
                    $validation = ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF];
                    $path = Utility::uploadFile($request, 'purchase_logo', $filename, $dir, $validation);
                    $this->logExecutionTime($uploadStart, $action, 'uploadPurchaseLogo');
                    if ($path['flag'] == 0) throw new \InvalidArgumentException($path['msg']);
                    $post['purchase_logo'] = $filename;
                }
                $this->logExecutionTime($prepStart, $action, 'prepareSettingsPayload');
                $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
                $dbStart = microtime(true);
                foreach ($post as $key => $value)
                    DB::insert('insert into settings (`value`,`name`,`' . $creatorCol . '`) values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)', [$value, $key, $user?->creatorId()]);
                $this->logExecutionTime($dbStart, $action, 'persistSettings');
                Log::info("[{$class}::{$action}] settings saved", ['keys' => array_keys($post)]);
                return redirect()->back()->with('success', __('Purchase Setting updated successfully'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, url()->previous());
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function items(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            Log::info("[{$class}::{$action}] start", ['purchase_id' => $request->input('purchase_id'), 'product_id' => $request->input('product_id'), 'method' => $method]);
            try {
                $valStart = microtime(true);
                $data = $request->validate(['purchase_id' => ['required', 'integer', 'min:1'], 'product_id' => ['required', 'integer', 'min:1']]);
                $this->logExecutionTime($valStart, $action, 'validateItemsRequest');
            } catch (ValidationException $e) {
                Log::warning("[{$class}::{$action}] validation_failed", ['errors' => $e->errors()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return response()->json(new \stdClass(), 422);
            }
            try {
                $queryStart = microtime(true);
                $item = PurchaseProduct::where('purchase_id', $data['purchase_id'])->where('product_id', $data['product_id'])->first();
                $this->logExecutionTime($queryStart, $action, 'queryPurchaseItem');
                if (!$item) {
                    Log::notice("[{$class}::{$action}] not_found", $data);
                    return response()->json(new \stdClass());
                }
                return response()->json($item);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] exception", ['error' => $e->getMessage()] + $data);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return response()->json(new \stdClass(), 500);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public const PRC_LK = 'purchaseLink';
    public function purchaseLink(string $encryptedId): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::PRC . '.customer_bill';
        return $this->measureProfile($action, function () use ($encryptedId, $action, $method, $class, $viewPath) {
            Log::info("[{$class}::{$action}] start", ['encrypted_id' => $encryptedId, 'method' => $method]);
            try {
                $decryptStart = microtime(true);
                $id = Crypt::decrypt($encryptedId);
                $this->logExecutionTime($decryptStart, $action, 'decryptPurchaseId');
                $loadStart = microtime(true);
                $purchase = Purchase::findOrFail($id);
                $user = User::findOrFail($purchase[DatabaseConstants::COL_TABLE_CREATOR]);
                $purchasePayment = PurchasePayment::where('purchase_id', $purchase->id)->first();
                $vendor = $purchase->vendor;
                $items = $purchase->items;
                $this->logExecutionTime($loadStart, $action, 'loadPurchaseLinkData');
                Log::info("[{$class}::{$action}] rendering customer purchase link", ['purchase_id' => $id]);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $response = view($viewPath, compact('purchase', 'vendor', 'items', 'purchasePayment', 'user'));
                $this->logExecutionTime($renderStart, $action, 'renderCustomerBill');
                return $response;
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'encrypted_id' => $encryptedId]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }, ['method' => $method, 'class' => $class, 'encrypted_id' => $encryptedId]);
    }

    public function payment(Request $request, int $purchaseId): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::PRC . '.payment';
        return $this->measureProfile($action, function () use ($request, $purchaseId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'create payment purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $purchaseId, 'method' => $method]);
            try {
                $loadStart = microtime(true);
                $purchase = Purchase::findOrFail($purchaseId);
                Log::debug("[{$class}::{$action}] purchase loaded", ['purchase_id' => $purchaseId]);
                $vendors = Vendor::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
                $categories = ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
                $accounts = BankAccount::select('*', DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
                $this->logExecutionTime($loadStart, $action, 'loadPaymentData');
                Log::info("[{$class}::{$action}] dataset ready", ['vendors_count' => $vendors->count(), 'categories_count' => $categories->count(), 'accounts_count' => $accounts->count()]);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $response = view($viewPath, compact('vendors', 'categories', 'accounts', 'purchase'));
                $this->logExecutionTime($renderStart, $action, 'renderPayment');
                return $response;
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id' => $purchaseId]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id' => $purchaseId]);
    }

    public const CRT_PAY = 'createPayment';
    public function createPayment(Request $request, int $purchaseId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $purchaseId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'create payment purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $purchaseId, 'method' => $method]);
            try {
                Log::info("[{$class}::{$action}] starting DB transaction", ['purchase_id' => $purchaseId]);
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $purchaseId, $user, $action, $class) {
                    $valStart = microtime(true);
                    $validator = Validator::make($request->all(), ['date' => 'required', 'amount' => 'required', 'account_id' => 'required']);
                    if ($validator->fails()) {
                        Log::warning("[{$class}::{$action}] validation failed", ['errors' => $validator->errors()->all()]);
                        throw new \InvalidArgumentException($validator->errors()->first());
                    }
                    $this->logExecutionTime($valStart, $action, 'validateCreatePayment');
                    $loadStart = microtime(true);
                    $purchase = Purchase::findOrFail($purchaseId);
                    $this->logExecutionTime($loadStart, $action, 'loadPurchaseForPayment');
                    Log::debug("[{$class}::{$action}] purchase loaded for payment", ['purchase_id' => $purchaseId]);
                    $saveStart = microtime(true);
                    $pp = new PurchasePayment();
                    $pp->purchase_id = $purchaseId;
                    $pp->date = $request->date;
                    $pp->amount = $request->amount;
                    $pp->account_id = $request->account_id;
                    $pp->payment_method = 0;
                    $pp->reference = $request->reference;
                    $pp->description = $request->description;
                    if ($request->hasFile('add_receipt')) {
                        $receiptStart = microtime(true);
                        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $request->add_receipt->getClientOriginalName());
                        $request->add_receipt->storeAs('uploads/payment', $fileName);
                        $this->logExecutionTime($receiptStart, $action, 'storeReceipt');
                        $pp->add_receipt = $fileName;
                        Log::debug("[{$class}::{$action}] receipt stored", ['file' => $fileName]);
                    }
                    $pp->save();
                    $this->logExecutionTime($saveStart, $action, 'savePurchasePayment');
                    Log::info("[{$class}::{$action}] payment created", ['payment_id' => $pp->id]);
                    $statusStart = microtime(true);
                    $due = $purchase->getDue();
                    $total = $purchase->getTotal();
                    if ($purchase->status === 0) $purchase->send_date = now()->toDateString();
                    $purchase->status = $due <= 0 ? 4 : 3;
                    $purchase->save();
                    $this->logExecutionTime($statusStart, $action, 'updatePurchaseStatus');
                    Log::info("[{$class}::{$action}] purchase status updated", ['purchase_id' => $purchaseId, 'status' => $purchase->status]);
                    $txnAddStart = microtime(true);
                    $pp->user_id = $purchase->vendor_id;
                    $pp->user_type = 'Vendor';
                    $pp->type = 'Partial';
                    $pp[DatabaseConstants::COL_TABLE_CREATOR] = $user?->id;
                    $pp->payment_id = $pp->id;
                    $pp->category = 'Bill';
                    $pp->account = $request->account_id;
                    Transaction::addTransaction($pp);
                    $this->logExecutionTime($txnAddStart, $action, 'addTransaction');
                    Log::info("[{$class}::{$action}] transaction added", ['payment_id' => $pp->id]);
                    $balanceStart = microtime(true);
                    Utility::userBalance('vendor', $purchase->vendor_id, $request->amount, 'debit');
                    Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');
                    $this->logExecutionTime($balanceStart, $action, 'updateBalances');
                    Log::info("[{$class}::{$action}] balances updated", ['vendor_id' => $purchase->vendor_id, 'account_id' => $request->account_id, 'amount' => $request->amount]);
                    $settings = Utility::settings();
                    if (!empty($settings['new_bill_payment'])) {
                        $emailStart = microtime(true);
                        $vendor = Vendor::findOrFail($purchase->vendor_id);
                        $billPaymentArr = ['vendor_name' => $vendor->name, 'vendor_email' => $vendor->email, 'payment_name' => $vendor->name, 'payment_amount' => $user?->priceFormat($request->amount), 'payment_bill' => 'bill ' . $user?->purchaseNumberFormat($pp->purchase_id), 'payment_date' => $user?->dateFormat($request->date), 'payment_method' => '-', 'company_name' => '-'];
                        $resp = Utility::sendEmailTemplate('new_bill_payment', [$vendor->id => $vendor->email], $billPaymentArr);
                        $this->logExecutionTime($emailStart, $action, 'sendNewBillPaymentEmail');
                        Log::info("[{$class}::{$action}] email template new_bill_payment sent", ['is_success' => $resp['is_success'] ?? false]);
                    }
                });
                $this->logExecutionTime($txnStart, $action, 'createPaymentTransaction');
                Log::info("[{$class}::{$action}] DB transaction committed", ['purchase_id' => $purchaseId]);
                return redirect()->back()->with('success', __('Payment successfully added.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id' => $purchaseId]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id' => $purchaseId]);
    }

    public const DST_PAY = 'paymentDestroy';
    public function paymentDestroy(Request $request, int $purchaseId, int $paymentId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $purchaseId, $paymentId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'delete payment purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'purchase_id' => $purchaseId, 'payment_id' => $paymentId, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($purchaseId, $paymentId, $action, $class) {
                    $loadStart = microtime(true);
                    $pp = PurchasePayment::findOrFail($paymentId);
                    $amount = $pp->amount;
                    $accountId = $pp->account_id;
                    $this->logExecutionTime($loadStart, $action, 'loadPaymentRow');
                    $delStart = microtime(true);
                    $pp->delete();
                    $this->logExecutionTime($delStart, $action, 'deletePaymentRow');
                    Log::info("[{$class}::{$action}] payment deleted", ['payment_id' => $paymentId]);
                    $purchaseStart = microtime(true);
                    $purchase = Purchase::findOrFail($purchaseId);
                    $due = $purchase->getDue();
                    $total = $purchase->getTotal();
                    $purchase->status = $due > 0 && $total !== $due ? 3 : 2;
                    $purchase->save();
                    $this->logExecutionTime($purchaseStart, $action, 'updatePurchaseStatus');
                    Log::info("[{$class}::{$action}] purchase status updated", ['purchase_id' => $purchaseId, 'status' => $purchase->status]);
                    $balanceStart = microtime(true);
                    Utility::userBalance('vendor', $purchase->vendor_id, $amount, 'credit');
                    Utility::bankAccountBalance($accountId, $amount, 'credit');
                    $this->logExecutionTime($balanceStart, $action, 'creditBalances');
                    Log::info("[{$class}::{$action}] balances credited", ['vendor_id' => $purchase->vendor_id, 'account_id' => $accountId, 'amount' => $amount]);
                    $txDelStart = microtime(true);
                    Transaction::destroyTransaction($paymentId, 'Partial', 'Vendor');
                    $this->logExecutionTime($txDelStart, $action, 'destroyTransaction');
                    Log::info("[{$class}::{$action}] transaction destroyed", ['payment_id' => $paymentId]);
                });
                $this->logExecutionTime($txnStart, $action, 'paymentDestroyTransaction');
                return redirect()->back()->with('success', __('Payment successfully deleted.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'purchase_id' => $purchaseId, 'payment_id' => $paymentId]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'purchase_id' => $purchaseId, 'payment_id' => $paymentId]);
    }

    public function vendor(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::PRC . '.vendor_detail';
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'view purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'vendor_id' => $request->id, 'method' => $method]);
            try {
                $loadStart = microtime(true);
                $vendor = Vendor::findOrFail($request->id);
                $this->logExecutionTime($loadStart, $action, 'loadVendor');
                Log::debug("[{$class}::{$action}] vendor loaded", ['vendor_id' => $vendor->id]);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $response = view($viewPath, compact('vendor'));
                $this->logExecutionTime($renderStart, $action, 'renderVendorDetail');
                return $response;
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'vendor_id' => $request->id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'vendor_id' => $request->id]);
    }

    public function product(Request $request): string
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'view purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'product_id' => $request->product_id, 'method' => $method]);
            try {
                $loadStart = microtime(true);
                $product = ProductService::findOrFail($request->product_id);
                $this->logExecutionTime($loadStart, $action, 'loadProduct');
                Log::debug("[{$class}::{$action}] product loaded", ['product_id' => $product->id]);
                $calcStart = microtime(true);
                $unit = $product->unit?->name ?? '';
                $taxRate = $product->tax_id ? $product->taxRate($product->tax_id) : 0;
                $taxes = $product->tax_id ? Utility::tax($product->tax_id) : [];
                $salePrice = $product->purchase_price;
                $quantity = 1;
                $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
                $totalAmount = $salePrice * $quantity;
                $data = ['product' => $product, 'unit' => $unit, 'taxRate' => $taxRate, 'taxes' => $taxes, 'totalAmount' => $totalAmount];
                $this->logExecutionTime($calcStart, $action, 'computeProductPricing');
                Log::info("[{$class}::{$action}] returning product JSON", ['product_id' => $product->id]);
                return json_encode($data);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'product_id' => $request->product_id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return '{}';
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'product_id' => $request->product_id]);
    }

    public const DST_PRD = 'productDestroy';
    public function productDestroy(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($r = self::guard($request, 'delete purchase', self::ROUTE_INDEX)) !== true) return $r;
            Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'item_id' => $request->id, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $user, $action, $class) {
                    $ppLoadStart = microtime(true);
                    $res = PurchaseProduct::findOrFail($request->id);
                    $this->logExecutionTime($ppLoadStart, $action, 'loadPurchaseProduct');
                    Log::debug("[{$class}::{$action}] purchase product loaded", ['id' => $res->id, 'product_id' => $res->product_id, 'qty' => $res->quantity]);
                    $purchaseLoadStart = microtime(true);
                    $purchase = Purchase::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                    $this->logExecutionTime($purchaseLoadStart, $action, 'loadPurchase');
                    $warehouseId = $purchase->warehouse_id;
                    $whpLoadStart = microtime(true);
                    $warePro = WarehouseProduct::where(['warehouse_id' => $warehouseId, 'product_id' => $res->product_id])->firstOrFail();
                    $this->logExecutionTime($whpLoadStart, $action, 'loadWarehouseProduct');
                    if ($res->quantity >= $warePro->quantity) {
                        $whpDelStart = microtime(true);
                        $warePro->delete();
                        $this->logExecutionTime($whpDelStart, $action, 'deleteWarehouseProduct');
                        Log::info("[{$class}::{$action}] warehouse product deleted", ['warehouse_id' => $warehouseId, 'product_id' => $res->product_id]);
                    } else {
                        $whpDecStart = microtime(true);
                        $warePro->decrement('quantity', $res->quantity);
                        $this->logExecutionTime($whpDecStart, $action, 'decrementWarehouseQuantity');
                        Log::info("[{$class}::{$action}] warehouse product decremented", ['warehouse_id' => $warehouseId, 'product_id' => $res->product_id, 'new_qty' => $warePro->quantity]);
                    }
                    $ppDelStart = microtime(true);
                    $res->delete();
                    $this->logExecutionTime($ppDelStart, $action, 'deletePurchaseProduct');
                    Log::info("[{$class}::{$action}] purchase product deleted", ['id' => $res->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'productDestroyTransaction');
                return redirect()->back()->with('success', __('Purchase product successfully deleted.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'item_id' => $request->id]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'item_id' => $request->id]);
    }

    private function purchaseNumber(): int
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return -1;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' generating purchase number', [
                'creator_id' => $user?->creatorId()
            ]);
            $latest = Purchase::where(
                DatabaseConstants::COL_TABLE_CREATOR,
                $user?->creatorId()
            )->latest()->first();
            return $latest
                ? $latest->purchase_id + 1
                : 1;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return 1;
        }
    }
}
