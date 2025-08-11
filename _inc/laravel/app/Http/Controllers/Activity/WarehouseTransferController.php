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
use Illuminate\Support\Facades\{DB, Log, Validator};
use Symfony\Component\HttpFoundation\Response;

class WarehouseTransferController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::WRH_TRF . '.index';
    private const PERM_MANAGE    = PermissionsConstants::MNG_WRH;
    private const PERM_CREATE    = 'create warehouse';
    private const PERM_DELETE    = 'delete warehouse';

    public function index(Request $request): RedirectResponse|\Illuminate\View\View
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard($request, self::PERM_MANAGE, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        try {
            $transfers = WarehouseTransfer::with(['product', 'fromWarehouse'])
                ->where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())
                ->get();
            Log::info("$action success", ['count' => $transfers->count()]);
            return view(ViewsConstants::WRH_TRF . '.' . __FUNCTION__, compact('transfers'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function create(Request $request): RedirectResponse|\Illuminate\View\View
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        try {
            $fromWarehouses = Warehouse::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())->get();
            $toWarehouses  = Warehouse::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())
                ->pluck('name', 'id')->prepend('Select Warehouse', '');
            $products      = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')
                ->pluck('name', 'product_id')->prepend('Select products', '');
            Log::info("$action view", [
                'fromCount' => $fromWarehouses->count(),
                'toCount'   => $toWarehouses->count(),
                'prodCount' => $products->count(),
            ]);
            return view(
                ViewsConstants::WRH_TRF . '.' . __FUNCTION__,
                compact('fromWarehouses', 'toWarehouses', 'products')
            );
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function show(Request $request, WarehouseTransfer $transfer): RedirectResponse|\Illuminate\View\View|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $redirect = self::guard(
                $request,
                self::PERM_MANAGE,
                self::REDIRECT_INDEX
            )
        ) return $redirect;
        if ($transfer[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                $action,
                route(self::REDIRECT_INDEX)
            );
        try {
            Log::info("$action called", ['transferId' => $transfer->id, 'user_id' => $user?->id]);
            return view(ViewsConstants::WRH_TRF . '.show', compact('transfer'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $validator = Validator::make($request->all(), [
            'from_warehouse' => 'required',
            'to_warehouse'   => 'required',
            'product_id'     => 'required',
            'quantity'      => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            Log::warning("$action validation failed", ['errors' => $validator->errors()->all()]);
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            $from = WarehouseProduct::where('warehouse_id', $request->input('fromWarehouse'))
                ->where('product_id', $request->input('productId'))
                ->firstOrFail();
            if ($request->input('quantity') > $from->quantity) {
                Log::warning("$action insufficient stock", [
                    'available' => $from->quantity,
                    'requested' => $request->input('quantity'),
                ]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('error', __('Product out of stock!'));
            }
            $transfer = WarehouseTransfer::create([
                'from_warehouse' => $request->input('fromWarehouse'),
                'to_warehouse'   => $request->input('toWarehouse'),
                'product_id'     => $request->input('productId'),
                'quantity'      => $request->input('quantity'),
                'date'          => $request->input('date'),
                DatabaseConstants::TABLE_CREATOR    => $userOrRedirect->creatorId(),
            ]);

            Utility::warehouseTransferQty(
                $request->input('fromWarehouse'),
                $request->input('toWarehouse'),
                $request->input('productId'),
                $request->input('quantity')
            );

            DB::commit();
            Log::info("$action success", ['transferId' => $transfer->id]);
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Warehouse Transfer successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, WarehouseTransfer $transfer): RedirectResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard($request, self::PERM_DELETE, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        DB::beginTransaction();
        try {
            if ($transfer[DatabaseConstants::TABLE_CREATOR] !== $userOrRedirect->creatorId()) {
                Log::warning("$action denied", ['transferId' => $transfer->id]);
                return defaultPermissionDenial(
                    $request,
                    new AuthorizationException(),
                    $action,
                    route(self::REDIRECT_INDEX)
                );
            }

            Utility::warehouseTransferQty(
                $transfer->toWarehouse,
                $transfer->fromWarehouse,
                $transfer->productId,
                $transfer->quantity,
                'delete'
            );

            $transfer->delete();
            DB::commit();
            Log::info("$action deleted", ['id' => $transfer->id]);
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Warehouse Transfer successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function getProduct(Request $request): JsonResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return response()->json([], Response::HTTP_UNAUTHORIZED);

        try {
            $warehouseId = $request->input('warehouseId');
            $products   = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')
                ->when($warehouseId != 0, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->pluck('name', 'product_id');
            $toWarehouses = Warehouse::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())
                ->when($warehouseId != 0, fn ($q) => $q->where('id', '!=', $warehouseId))
                ->pluck('name', 'id');

            Log::info("$action success", [
                'warehouseId'    => $warehouseId,
                'productsCount'  => $products->count(),
                'warehousesCount' => $toWarehouses->count(),
            ]);

            return response()->json([
                'wareProducts' => $products,
                'toWarehouses' => $toWarehouses,
            ]);
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return response()->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getQuantity(Request $request): JsonResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return response()->json([], Response::HTTP_UNAUTHORIZED);

        try {
            $productId = $request->input('productId');
            $quantities = WarehouseProduct::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())
                ->when($productId != 0, fn ($q) => $q->where('product_id', $productId))
                ->pluck('quantity', 'product_id');

            Log::info("$action success", [
                'productId' => $productId,
                'count'     => $quantities->count(),
            ]);

            return response()->json($quantities);
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return response()->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
