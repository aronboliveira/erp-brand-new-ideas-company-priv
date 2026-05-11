<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{OperationLedger, ProductService, Utility};
use App\Services\Reliability\{
    CriticalOperationService,
    ReliabilityClientPayloadService,
    WarehouseOperationResult,
    WarehouseOperationService,
    WarehouseOutboxDispatcher
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
final class ProductStockController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::PRD_STK . '.index';

    public function index(Request $r): RedirectResponse|View|bool
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;
            $productServices = ProductService::query()
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->where('type', 'product')
                ->get();
            return view(ViewsConstants::PRD_STK . '.index', compact('productServices'));
        });
    }

    public function create(Request $r): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = $this->guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            $products = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->where('type', 'product')
                ->pluck('name', 'id');
            return view(ViewsConstants::PRD_STK . '.create', compact('products'));
        });
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($r, [
                'product_id' => 'required|exists:product_services,id',
                'quantity'   => 'required|integer|min:1',
            ])) return $c;
            try {
                $operation = (new WarehouseOperationService())->run('warehouse.stock.adjust', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($r, $u): array {
                    $p = ProductService::whereKey($r->product_id)
                        ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                        ->lockForUpdate()
                        ->firstOrFail();
                    $p->increment('quantity', $r->quantity);
                    $p->refresh();
                    Utility::addProductStock(
                        $p->id,
                        $r->quantity,
                        'manually',
                        "{$r->quantity} quantity added manually",
                        0
                    );
                    $payload = [
                        'product_id' => (string) $p->id,
                        'quantity' => (int) $r->quantity,
                        'expected_product_quantity' => (float) $p->quantity,
                        'stock_report_type' => 'manually',
                        'stock_report_type_id' => '0',
                    ];
                    $operations->recordStep($ledger, 'warehouse.stock.persisted', 'Persist manual product stock adjustment', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => \App\Services\Reliability\ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Adjust product stock manually',
                    'subject_type' => ProductService::class,
                    'subject_id' => (string) $r->product_id,
                    'actor_id' => $r->user()?->id,
                    'context' => ['product_id' => (string) $r->product_id, 'quantity' => (int) $r->quantity],
                    'post_write_validation' => true,
                    'event_type' => 'warehouse.stock.adjusted',
                    'message_key' => fn(array $result, OperationLedger $ledger): string => 'warehouse.stock.adjusted:' . ($result['product_id'] ?? $ledger->operation_key),
                    'payload' => fn(array $result): array => $result,
                    'replica_sync_sensitive' => true,
                ]);
                $dispatchReport = $this->dispatchWarehouseOutbox($operation);

                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Product quantity updated manually.'))
                    ->with('reliability_operation', $this->warehouseReliabilityPayload($operation, $dispatchReport));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $action);
            }
        });
    }

    public function edit(Request $r, string|int $id): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r, $id) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            $productService = ProductService::whereKey($id)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->firstOrFail();
            return view(ViewsConstants::PRD_STK . '.edit', compact('productService'));
        });
    }

    public function update(Request $r, string|int $id): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r, $id, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($r, ['quantity' => 'required|integer|min:1'])) return $c;
            try {
                $operation = (new WarehouseOperationService())->run('warehouse.stock.adjust', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($r, $id, $u): array {
                    $p = ProductService::whereKey($id)
                        ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                        ->lockForUpdate()
                        ->firstOrFail();
                    $p->increment('quantity', $r->quantity);
                    $p->refresh();
                    Utility::addProductStock(
                        $p->id,
                        $r->quantity,
                        'manually',
                        "{$r->quantity} quantity added manually",
                        0
                    );
                    $payload = [
                        'product_id' => (string) $p->id,
                        'quantity' => (int) $r->quantity,
                        'expected_product_quantity' => (float) $p->quantity,
                        'stock_report_type' => 'manually',
                        'stock_report_type_id' => '0',
                    ];
                    $operations->recordStep($ledger, 'warehouse.stock.persisted', 'Persist manual product stock adjustment', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => \App\Services\Reliability\ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Adjust product stock manually',
                    'subject_type' => ProductService::class,
                    'subject_id' => (string) $id,
                    'actor_id' => $r->user()?->id,
                    'context' => ['product_id' => (string) $id, 'quantity' => (int) $r->quantity],
                    'post_write_validation' => true,
                    'event_type' => 'warehouse.stock.adjusted',
                    'message_key' => fn(array $result, OperationLedger $ledger): string => 'warehouse.stock.adjusted:' . ($result['product_id'] ?? $ledger->operation_key),
                    'payload' => fn(array $result): array => $result,
                    'replica_sync_sensitive' => true,
                ]);
                $dispatchReport = $this->dispatchWarehouseOutbox($operation);

                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Product quantity updated manually.'))
                    ->with('reliability_operation', $this->warehouseReliabilityPayload($operation, $dispatchReport));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $action);
            }
        });
    }

    public function destroy(Request $r, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r, $id, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'delete product & service', self::REDIRECT_INDEX)) !== true) return $c;
            try {
                $operation = (new WarehouseOperationService())->run('warehouse.product.delete', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($id, $u): array {
                    $product = ProductService::whereKey($id)
                        ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                        ->lockForUpdate()
                        ->firstOrFail();
                    $payload = [
                        'product_id' => (string) $product->id,
                        'expect_no_warehouse_stock' => true,
                    ];
                    $product->delete();
                    $operations->recordStep($ledger, 'warehouse.product.deleted', 'Delete product/service from stock control', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => \App\Services\Reliability\ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Delete product/service from stock control',
                    'subject_type' => ProductService::class,
                    'subject_id' => (string) $id,
                    'actor_id' => $r->user()?->id,
                    'context' => ['product_id' => (string) $id, 'closed_state_recovery' => true],
                    'post_write_validation' => true,
                    'event_type' => 'warehouse.product.deleted',
                    'message_key' => fn(array $result, OperationLedger $ledger): string => 'warehouse.product.deleted:' . ($result['product_id'] ?? $ledger->operation_key),
                    'payload' => fn(array $result): array => $result,
                    'closed_state_recovery' => true,
                    'replica_sync_sensitive' => true,
                ]);
                $dispatchReport = $this->dispatchWarehouseOutbox($operation);

                return back()
                    ->with('success', __('Product deleted.'))
                    ->with('reliability_operation', $this->warehouseReliabilityPayload($operation, $dispatchReport));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $action);
            }
        });
    }

    public function show(): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, fn() => redirect()->route(self::REDIRECT_INDEX));
    }

    private static function v(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        return $v->fails()
            ? back()->with('error', $v->getMessageBag()->first())
            : null;
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
