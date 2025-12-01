<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    ViewsConstants,
};
use App\Http\Controllers\Controller;
use App\Models\{ProductService, Purchase, Utility, Warehouse, WarehouseProduct, WarehouseTransfer};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class WarehouseTransferController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::WRH_TRF . '.index';
    private const PERM_MANAGE    = PermissionsConstants::MNG_WRH;
    private const PERM_CREATE    = 'create warehouse';
    private const PERM_DELETE    = 'delete warehouse';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::WRH_TRF . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                $buildStart = microtime(true);
                $query = WarehouseTransfer::with(['product', 'fromWarehouse'])->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $transfers = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchTransfers');
                Log::info("[{$base}::{$action}] success", ['count' => $transfers->count(), 'method' => $method, 'user_id' => $user?->id]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfers']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('transfers'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::WRH_TRF . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                Log::info("[{$base}::{$action}] view", ['user_id' => $user?->id, 'method' => $method]);
                $listsStart = microtime(true);
                $fromWarehouses = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $toWarehouses = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->prepend('Select Warehouse', '');
                $products = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')->pluck('name', 'product_id')->prepend('Select products', '');
                $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
                Log::info("[{$base}::{$action}] view data", ['fromCount' => $fromWarehouses->count(), 'toCount' => $toWarehouses->count(), 'prodCount' => $products->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['fromWarehouses', 'toWarehouses', 'products']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('fromWarehouses', 'toWarehouses', 'products'));
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $request, WarehouseTransfer $transfer): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::WRH_TRF . '.show';
        return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($transfer[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::REDIRECT_INDEX));
            try {
                Log::info("[{$base}::{$action}] called", ['transferId' => $transfer->id, 'user_id' => $user?->id, 'method' => $method]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'transfer_id' => $transfer->id]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfer']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('transfer'));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'transfer_id' => $transfer->id]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $redirect;
            $valStart = microtime(true);
            $validator = Validator::make($req->all(), ['from_warehouse' => 'required', 'to_warehouse' => 'required', 'product_id' => 'required', 'quantity' => 'required|integer|min:1']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($validator->fails()) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $validator->errors()->all()]);
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            try {
                $fetchStart = microtime(true);
                $from = WarehouseProduct::where('warehouse_id', $req->input('fromWarehouse'))->where('product_id', $req->input('productId'))->firstOrFail();
                $this->logExecutionTime($fetchStart, $action, 'fetchFromStock');
                if ($req->input('quantity') > $from->quantity) {
                    Log::warning("[{$base}::{$action}] insufficient stock", ['available' => $from->quantity, 'requested' => $req->input('quantity')]);
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', __('Product out of stock!'));
                }
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $user, $action, $base, &$transfer) {
                    $createStart = microtime(true);
                    $transfer = WarehouseTransfer::create([
                        'from_warehouse' => $req->input('fromWarehouse'),
                        'to_warehouse' => $req->input('toWarehouse'),
                        'product_id' => $req->input('productId'),
                        'quantity' => $req->input('quantity'),
                        'date' => $req->input('date'),
                        DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createTransfer');
                    $invStart = microtime(true);
                    Utility::warehouseTransferQty($req->input('fromWarehouse'), $req->input('toWarehouse'), $req->input('productId'), $req->input('quantity'));
                    $this->logExecutionTime($invStart, $action, 'updateInventory');
                    Log::info("[{$base}::{$action}] success", ['transferId' => $transfer->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Warehouse Transfer successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function destroy(Request $request, WarehouseTransfer $transfer): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, self::PERM_DELETE, self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($transfer[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::REDIRECT_INDEX));
            Log::info("[{$base}::{$action}] start", ['transfer_id' => $transfer->id, 'user_id' => $user?->id, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($transfer, $action) {
                    $invStart = microtime(true);
                    Utility::warehouseTransferQty($transfer->toWarehouse, $transfer->fromWarehouse, $transfer->productId, $transfer->quantity, 'delete');
                    $this->logExecutionTime($invStart, $action, 'revertInventory');
                    $delStart = microtime(true);
                    $transfer->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteTransfer');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] deleted", ['id' => $transfer->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Warehouse Transfer successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'transfer_id' => $transfer->id]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
    }

    public const GET_PRD = 'getProduct';
    public function getProduct(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json([], Response::HTTP_UNAUTHORIZED);
            $user = $userOrRedirect;
            try {
                $warehouseId = $req->input('warehouseId');
                $prodStart = microtime(true);
                $products = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')->when($warehouseId != 0, fn($q) => $q->where('warehouse_id', $warehouseId))->pluck('name', 'product_id');
                $this->logExecutionTime($prodStart, $action, 'fetchProducts');
                $whStart = microtime(true);
                $toWarehouses = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->when($warehouseId != 0, fn($q) => $q->where('id', '!=', $warehouseId))->pluck('name', 'id');
                $this->logExecutionTime($whStart, $action, 'fetchWarehouses');
                Log::info("[{$base}::{$action}] success", ['warehouseId' => $warehouseId, 'productsCount' => $products->count(), 'warehousesCount' => $toWarehouses->count(), 'method' => $method]);
                return response()->json(['wareProducts' => $products, 'toWarehouses' => $toWarehouses]);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return response()->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const GET_QT = 'getQuantity';
    public function getQuantity(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json([], Response::HTTP_UNAUTHORIZED);
            $user = $userOrRedirect;
            try {
                $productId = $req->input('productId');
                $fetchStart = microtime(true);
                $quantities = WarehouseProduct::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->when($productId != 0, fn($q) => $q->where('product_id', $productId))->pluck('quantity', 'product_id');
                $this->logExecutionTime($fetchStart, $action, 'fetchQuantities');
                Log::info("[{$base}::{$action}] success", ['productId' => $productId, 'count' => $quantities->count(), 'method' => $method]);
                return response()->json($quantities);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return response()->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }
}
