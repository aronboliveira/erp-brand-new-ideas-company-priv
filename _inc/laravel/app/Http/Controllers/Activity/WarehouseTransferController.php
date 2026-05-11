<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    ViewsConstants,
};
use App\Http\Controllers\Controller;
use App\Models\{OperationLedger, ProductService, Purchase, Utility, Warehouse, WarehouseProduct, WarehouseTransfer};
use App\Services\Reliability\{
    CriticalOperationService,
    ReliabilityClientPayloadService,
    ReliabilityPolicy,
    WarehouseOperationResult,
    WarehouseOperationService,
    WarehouseOutboxDispatcher
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class WarehouseTransferController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

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
        return $this->measureProfile($action, function () use ($req, $action, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $redirect;
            $valStart = microtime(true);
            $req->merge([
                'from_warehouse' => $req->input('from_warehouse', $req->input('fromWarehouse')),
                'to_warehouse' => $req->input('to_warehouse', $req->input('toWarehouse')),
                'product_id' => $req->input('product_id', $req->input('productId')),
            ]);
            $validator = Validator::make($req->all(), ['from_warehouse' => 'required', 'to_warehouse' => 'required', 'product_id' => 'required', 'quantity' => 'required|integer|min:1']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($validator->fails()) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $validator->errors()->all()]);
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            try {
                $fromWarehouse = (string) ($req->input('fromWarehouse') ?? $req->input('from_warehouse'));
                $toWarehouse = (string) ($req->input('toWarehouse') ?? $req->input('to_warehouse'));
                $productId = (string) ($req->input('productId') ?? $req->input('product_id'));
                $quantity = (int) $req->input('quantity');
                $operation = (new WarehouseOperationService())->run('warehouse.transfer.create', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($req, $user, $action, $base, $fromWarehouse, $toWarehouse, $productId, $quantity): array {
                    $fetchStart = microtime(true);
                    $from = WarehouseProduct::where('warehouse_id', $fromWarehouse)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $this->logExecutionTime($fetchStart, $action, 'fetchFromStock');
                    if ($quantity > $from->quantity) {
                        Log::warning("[{$base}::{$action}] insufficient stock", ['available' => $from->quantity, 'requested' => $quantity]);
                        throw new \RuntimeException(__('Product out of stock!'));
                    }
                    $createStart = microtime(true);
                    $transfer = WarehouseTransfer::create([
                        'from_warehouse' => $fromWarehouse,
                        'to_warehouse' => $toWarehouse,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'date' => $req->input('date'),
                        DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createTransfer');
                    $invStart = microtime(true);
                    Utility::warehouseTransferQty($fromWarehouse, $toWarehouse, $productId, $quantity);
                    $this->logExecutionTime($invStart, $action, 'updateInventory');
                    Log::info("[{$base}::{$action}] success", ['transferId' => $transfer->id]);
                    $sourceQuantity = (int) (WarehouseProduct::where('warehouse_id', $fromWarehouse)->where('product_id', $productId)->value('quantity') ?? 0);
                    $destinationQuantity = (int) (WarehouseProduct::where('warehouse_id', $toWarehouse)->where('product_id', $productId)->value('quantity') ?? 0);
                    $payload = [
                        'transfer_id' => (string) $transfer->id,
                        'product_id' => $productId,
                        'from_warehouse_id' => $fromWarehouse,
                        'to_warehouse_id' => $toWarehouse,
                        'quantity' => $quantity,
                        'expected_source_quantity' => $sourceQuantity,
                        'expected_destination_quantity' => $destinationQuantity,
                        'bulk' => $quantity >= 250,
                        'eventual_consistency_sensitive' => true,
                        'replica_sync_sensitive' => true,
                    ];
                    $operations->recordStep($ledger, 'warehouse.transfer.persisted', 'Persist warehouse transfer and stock movement', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Create warehouse transfer',
                    'subject_type' => WarehouseTransfer::class,
                    'subject_id' => $productId,
                    'actor_id' => $req->user()?->id,
                    'context' => ['product_id' => $productId, 'quantity' => $quantity, 'from_warehouse_id' => $fromWarehouse, 'to_warehouse_id' => $toWarehouse],
                    'post_write_validation' => true,
                    'event_type' => 'warehouse.transfer.created',
                    'message_key' => fn(array $result, OperationLedger $ledger): string => 'warehouse.transfer.created:' . ($result['transfer_id'] ?? $ledger->operation_key),
                    'payload' => fn(array $result): array => $result,
                    'replica_sync_sensitive' => true,
                    'eventual_consistency_sensitive' => true,
                ]);
                $dispatchReport = $this->dispatchWarehouseOutbox($operation);

                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Warehouse Transfer successfully created.'))
                    ->with('reliability_operation', $this->warehouseReliabilityPayload($operation, $dispatchReport));
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
                $operation = (new WarehouseOperationService())->run('warehouse.transfer.delete', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($transfer, $action, $user): array {
                    $transferId = (string) $transfer->id;
                    $fromWarehouse = (string) $transfer->getAttribute('from_warehouse');
                    $toWarehouse = (string) $transfer->getAttribute('to_warehouse');
                    $productId = (string) $transfer->getAttribute('product_id');
                    $quantity = (int) $transfer->getAttribute('quantity');
                    $invStart = microtime(true);
                    $destination = WarehouseProduct::where('warehouse_id', $toWarehouse)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();
                    $source = WarehouseProduct::firstOrNew([
                        'warehouse_id' => $fromWarehouse,
                        'product_id' => $productId,
                    ]);
                    if (!$source->exists) {
                        $source->quantity = 0;
                        $source[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
                    }
                    $source->quantity = (int) $source->quantity + $quantity;
                    $source->save();

                    if ($destination) {
                        $newDestinationQuantity = (int) $destination->quantity - $quantity;
                        if ($newDestinationQuantity <= 0) {
                            $destination->delete();
                        } else {
                            $destination->quantity = $newDestinationQuantity;
                            $destination->save();
                        }
                    }
                    $this->logExecutionTime($invStart, $action, 'revertInventory');
                    $delStart = microtime(true);
                    $transfer->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteTransfer');
                    $sourceQuantity = (int) (WarehouseProduct::where('warehouse_id', $fromWarehouse)->where('product_id', $productId)->value('quantity') ?? 0);
                    $destinationQuantity = (int) (WarehouseProduct::where('warehouse_id', $toWarehouse)->where('product_id', $productId)->value('quantity') ?? 0);
                    $payload = [
                        'transfer_id' => $transferId,
                        'product_id' => $productId,
                        'from_warehouse_id' => $fromWarehouse,
                        'to_warehouse_id' => $toWarehouse,
                        'quantity' => $quantity,
                        'expected_source_quantity' => $sourceQuantity,
                        'expected_destination_quantity' => $destinationQuantity,
                        'closed_state_recovery' => true,
                        'eventual_consistency_sensitive' => true,
                        'replica_sync_sensitive' => true,
                    ];
                    $operations->recordStep($ledger, 'warehouse.transfer.reversed', 'Reverse warehouse transfer and stock movement', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Delete warehouse transfer and reverse stock',
                    'subject_type' => WarehouseTransfer::class,
                    'subject_id' => (string) $transfer->id,
                    'actor_id' => $req->user()?->id,
                    'context' => [
                        'transfer_id' => (string) $transfer->id,
                        'quantity' => (int) $transfer->getAttribute('quantity'),
                        'closed_state_recovery' => true,
                    ],
                    'post_write_validation' => true,
                    'event_type' => 'warehouse.transfer.deleted',
                    'message_key' => fn(array $result, OperationLedger $ledger): string => 'warehouse.transfer.deleted:' . ($result['transfer_id'] ?? $ledger->operation_key),
                    'payload' => fn(array $result): array => $result,
                    'closed_state_recovery' => true,
                    'replica_sync_sensitive' => true,
                    'eventual_consistency_sensitive' => true,
                ]);
                $dispatchReport = $this->dispatchWarehouseOutbox($operation);
                Log::info("[{$base}::{$action}] deleted", ['id' => $transfer->id]);

                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Warehouse Transfer successfully deleted.'))
                    ->with('reliability_operation', $this->warehouseReliabilityPayload($operation, $dispatchReport));
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
        return $this->measureProfile($action, function () use ($req, $action, $method, $base) {
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

    /**
     * Exibe o formulário de edição de uma transferência de armazém.
     */
    public function edit(Request $request, int|string $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::WRH_TRF . '.edit';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                $fetchStart = microtime(true);
                $transfer = WarehouseTransfer::findOrFail($id);
                $this->logExecutionTime($fetchStart, $action, 'fetchTransfer');
                if ($transfer[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                    return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::REDIRECT_INDEX));
                }
                Log::info("[{$base}::{$action}] view", ['transfer_id' => $id, 'user_id' => $user?->id, 'method' => $method]);
                $listsStart = microtime(true);
                $fromWarehouses = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $toWarehouses = Warehouse::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->prepend('Select Warehouse', '');
                $products = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')->pluck('name', 'product_id')->prepend('Select products', '');
                $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('transfer', 'fromWarehouses', 'toWarehouses', 'products'));
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $id]);
    }

    public function getQuantity(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $base) {
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

    public function update(Request $request, \App\Models\WarehouseTransfer $transfer): \Illuminate\Http\RedirectResponse
    {
        $action = __FUNCTION__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $transfer, $action, $class, $base) {
            if (($u = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $u;
            if (($r = self::guard($request, 'edit warehouse transfer')) !== true) return $r;
            if ($transfer[DatabaseConstants::COL_TABLE_CREATOR] !== $u?->creatorId()) return defaultPermissionDenial($request, new AuthorizationException(), $class . '::' . $action, route(self::REDIRECT_INDEX));
            try {
                $blockedFields = array_intersect(array_keys($request->all()), [
                    'from_warehouse',
                    'fromWarehouse',
                    'to_warehouse',
                    'toWarehouse',
                    'product_id',
                    'productId',
                    'quantity',
                ]);
                if ($blockedFields !== []) {
                    return redirect()->back()->with('error', __('Stock-defining transfer fields require a reversal/recreate operation.'));
                }
                \Illuminate\Support\Facades\Log::info("[$base::$action] updating transfer", ['id' => $transfer->id]);
                $operation = (new WarehouseOperationService())->run('warehouse.transfer.update', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($request, $transfer): array {
                    $transfer = WarehouseTransfer::whereKey($transfer->id)->lockForUpdate()->firstOrFail();
                    $transfer->update($request->except(['fromWarehouse', 'toWarehouse', 'productId']));
                    $transfer->refresh();
                    $payload = [
                        'transfer_id' => (string) $transfer->id,
                        'product_id' => (string) $transfer->getAttribute('product_id'),
                        'from_warehouse_id' => (string) $transfer->getAttribute('from_warehouse'),
                        'to_warehouse_id' => (string) $transfer->getAttribute('to_warehouse'),
                        'quantity' => (int) $transfer->getAttribute('quantity'),
                        'status' => (string) $transfer->getAttribute('status'),
                        'closed_state_recovery' => in_array((string) $transfer->getAttribute('status'), ['completed', 'approved', 'closed'], true),
                    ];
                    $operations->recordStep($ledger, 'warehouse.transfer.metadata_updated', 'Update warehouse transfer metadata/status', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Update warehouse transfer metadata/status',
                    'subject_type' => WarehouseTransfer::class,
                    'subject_id' => (string) $transfer->id,
                    'actor_id' => $request->user()?->id,
                    'context' => ['transfer_id' => (string) $transfer->id],
                    'post_write_validation' => true,
                    'event_type' => 'warehouse.transfer.updated',
                    'message_key' => fn(array $result, OperationLedger $ledger): string => 'warehouse.transfer.updated:' . ($result['transfer_id'] ?? $ledger->operation_key),
                    'payload' => fn(array $result): array => $result,
                    'replica_sync_sensitive' => true,
                ]);
                $dispatchReport = $this->dispatchWarehouseOutbox($operation);

                return redirect()->route('warehouse_transfers.index')
                    ->with('success', __('Warehouse transfer updated successfully.'))
                    ->with('reliability_operation', $this->warehouseReliabilityPayload($operation, $dispatchReport));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("[$base::$action] failed", ['err' => $e->getMessage()]);
                return redirect()->back()->with('error', $e->getMessage());
            }
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function dispatchWarehouseOutbox(WarehouseOperationResult $operation): ?array
    {
        $message = $operation->outboxMessage();

        return $message ? (new WarehouseOutboxDispatcher())->dispatchMessage($message) : null;
    }

    /**
     * @param array<string, mixed>|null $dispatchReport
     * @return array<string, mixed>
     */
    private function warehouseReliabilityPayload(WarehouseOperationResult $operation, ?array $dispatchReport): array
    {
        return (new ReliabilityClientPayloadService())->fromWarehouseResult($operation, $dispatchReport);
    }

}
