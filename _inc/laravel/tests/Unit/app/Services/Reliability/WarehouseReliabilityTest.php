<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\{BillsConstants as BLC, DatabaseConstants as DC};
use App\Exceptions\Reliability\QuarantineRollbackRequiredException;
use App\Models\{
    CircuitBreakerCall,
    CircuitBreakerState,
    OperationLedger,
    OperationQuarantine,
    OutboxMessage,
    ProductService,
    Purchase,
    PurchaseProduct,
    WarehouseProduct
};
use App\Services\Reliability\{
    CriticalOperationService,
    PostWriteValidationResult,
    QuarantineRemediationJudge,
    QuarantineService,
    ReliabilityPolicy,
    WarehouseCompensationService,
    WarehouseOperationService,
    WarehouseOutboxDispatcher,
    WarehousePostWriteValidator,
    WarehouseReliabilityAssessment,
    WarehouseReliabilityPolicy
};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use RuntimeException;
use Tests\TestCase;

#[CoversClass(WarehouseCompensationService::class)]
#[CoversClass(WarehouseOperationService::class)]
#[CoversClass(WarehouseOutboxDispatcher::class)]
#[CoversClass(WarehousePostWriteValidator::class)]
#[CoversClass(WarehouseReliabilityAssessment::class)]
#[CoversClass(WarehouseReliabilityPolicy::class)]
#[CoversClass(PostWriteValidationResult::class)]
#[CoversClass(QuarantineRemediationJudge::class)]
#[CoversClass(QuarantineService::class)]
#[Group('services')]
#[Group('reliability')]
class WarehouseReliabilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    #[Test]
    public function stock_adjustment_creates_warehouse_ledger_outbox_and_validation_step(): void
    {
        $product = $this->product(['quantity' => 10]);

        $result = (new WarehouseOperationService())->run(
            'warehouse.stock.adjust',
            function () use ($product): array {
                $product->increment('quantity', 15);
                $product->refresh();

                return [
                    'product_id' => (string) $product->id,
                    'quantity' => 15,
                    'expected_product_quantity' => (float) $product->quantity,
                    'replica_sync_sensitive' => true,
                ];
            },
            [
                'summary' => 'Adjust product stock',
                'subject_type' => ProductService::class,
                'subject_id' => (string) $product->id,
                'event_type' => 'warehouse.stock.adjusted',
                'post_write_validation' => true,
                'payload' => fn(array $payload): array => $payload,
                'replica_sync_sensitive' => true,
            ],
        );

        $ledger = $result->ledger();
        $outbox = $result->outboxMessage();

        $this->assertInstanceOf(OperationLedger::class, $ledger);
        $this->assertInstanceOf(OutboxMessage::class, $outbox);
        $this->assertSame('warehouse', $ledger?->domain);
        $this->assertSame('warehouse.operations', $outbox?->stream);
        $this->assertSame('stock_mutation', data_get($outbox?->metadata, 'warehouse_reliability.cluster'));
        $this->assertGreaterThanOrEqual(4, (int) data_get($outbox?->metadata, 'retry.max_attempts'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger?->id,
            'step_key' => 'warehouse.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
    }

    #[Test]
    public function purchase_product_delete_reverses_stock_inside_warehouse_operation(): void
    {
        $product = $this->product(['quantity' => 35]);
        $warehouseId = (string) Str::uuid();
        $purchase = Purchase::create([
            BLC::COL_PRC_ID => 'PRCH-' . Str::uuid(),
            BLC::COL_PRC_NB => 10,
            BLC::COL_WRH_ID => $warehouseId,
            BLC::COL_PRC_DT => now(),
            BLC::COL_CAT_ID => (string) Str::uuid(),
            'vendor_id' => (string) Str::uuid(),
            'status' => 0,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
        $purchaseProduct = PurchaseProduct::create([
            BLC::COL_PRC_ID => $purchase->id,
            BLC::COL_PRD_ID => $product->id,
            'quantity' => 5,
            'tax' => null,
            'discount' => 0,
            'total' => 50,
        ]);
        WarehouseProduct::create([
            BLC::COL_WRH_ID => $warehouseId,
            BLC::COL_PRD_ID => $product->id,
            'quantity' => 20,
        ]);

        $result = (new WarehouseOperationService())->run(
            'warehouse.purchase.product.delete',
            function (?OperationLedger $ledger, CriticalOperationService $operations) use ($purchase, $purchaseProduct, $product, $warehouseId): array {
                $purchaseProduct->delete();
                $product->decrement('quantity', 5);
                WarehouseProduct::where(BLC::COL_WRH_ID, $warehouseId)
                    ->where(BLC::COL_PRD_ID, $product->id)
                    ->decrement('quantity', 5);

                $payload = [
                    'purchase_product_id' => (string) $purchaseProduct->id,
                    'purchase_id' => (string) $purchase->id,
                    'warehouse_id' => $warehouseId,
                    'product_id' => (string) $product->id,
                    'quantity' => 5,
                    'items_count' => 1,
                    'total_quantity' => 5,
                    'items' => [[
                        'purchase_product_id' => (string) $purchaseProduct->id,
                        'product_id' => (string) $product->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => 5,
                        'expected_product_quantity' => 30,
                        'expected_warehouse_quantity' => 15,
                    ]],
                    'eventual_consistency_sensitive' => true,
                    'replica_sync_sensitive' => true,
                ];
                $operations->recordStep($ledger, 'warehouse.purchase.product.deleted', 'Delete purchase product and reverse stock effects', [
                    'step_type' => 'db_write',
                    'sequence' => 50,
                    'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                    'payload' => $payload,
                    'started_at' => now(),
                    'finished_at' => now(),
                ]);

                return $payload;
            },
            [
                'summary' => 'Delete purchase product and reverse stock effects',
                'subject_type' => PurchaseProduct::class,
                'subject_id' => (string) $purchaseProduct->id,
                'event_type' => 'warehouse.purchase.product.deleted',
                'post_write_validation' => true,
                'payload' => fn(array $payload): array => $payload,
                'replica_sync_sensitive' => true,
                'eventual_consistency_sensitive' => true,
            ],
        );

        $this->assertNull(PurchaseProduct::query()->find($purchaseProduct->id));
        $this->assertSame(30.0, (float) ProductService::query()->whereKey($product->id)->value('quantity'));
        $this->assertSame(15, (int) WarehouseProduct::query()
            ->where(BLC::COL_WRH_ID, $warehouseId)
            ->where(BLC::COL_PRD_ID, $product->id)
            ->value('quantity'));
        $this->assertSame('purchase_commit', data_get($result->outboxMessage()?->metadata, 'warehouse_reliability.cluster'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $result->ledger()?->id,
            'step_key' => 'warehouse.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
    }

    #[Test]
    public function persistent_stock_mismatch_routes_to_warehouse_quarantine_manual_review(): void
    {
        $product = $this->product(['quantity' => 10]);
        $operationKey = 'warehouse-quarantine-' . Str::uuid();

        for ($i = 0; $i < 4; $i++) {
            OperationLedger::create([
                'operation_key' => 'warehouse-prior-failure-' . $i . '-' . Str::uuid(),
                'operation_type' => 'warehouse.stock.adjust',
                'domain' => 'warehouse',
                'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'subject_type' => ProductService::class,
                'subject_id' => (string) $product->id,
                'summary' => 'Prior failed stock reconciliation attempt',
                'started_at' => now()->subMinutes(20),
                'failed_at' => now()->subMinutes(10),
                'expires_at' => now()->addDay(),
            ]);
        }

        try {
            (new WarehouseOperationService())->run(
                'warehouse.stock.adjust',
                fn(): array => [
                    'product_id' => (string) $product->id,
                    'quantity' => 5,
                    'expected_product_quantity' => 999,
                    'replica_sync_sensitive' => true,
                ],
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Adjust stock with persistent mismatch',
                    'subject_type' => ProductService::class,
                    'subject_id' => (string) $product->id,
                    'event_type' => 'warehouse.stock.adjusted',
                    'post_write_validation' => true,
                    'payload' => fn(array $payload): array => $payload,
                    'replica_sync_sensitive' => true,
                ],
            );

            $this->fail('Persistent warehouse stock mismatch should route to quarantine.');
        } catch (QuarantineRollbackRequiredException $exception) {
            $this->assertNotNull($exception->quarantine());
            $this->assertSame('warehouse', $exception->quarantine()?->domain);
            $this->assertSame(ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW, $exception->quarantine()?->status);
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINES, [
            'operation_ledger_id' => $ledger->id,
            'source_table' => DC::TABLE_PROD_SERVS,
            'source_record_id' => (string) $product->id,
            'domain' => 'warehouse',
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
        ]);
        $this->assertTrue(OperationQuarantine::where('operation_ledger_id', $ledger->id)->exists());
    }

    #[Test]
    public function warehouse_outbox_dispatcher_accepts_replica_and_reconciliation_signals(): void
    {
        [$ledger, $message] = $this->warehouseOutbox('warehouse.transfer.created');

        $report = (new WarehouseOutboxDispatcher())->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dispatched', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DISPATCHED, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_CLOSED, $ledger->status);
        $this->assertGreaterThanOrEqual(5, count($report['signals']));
        $this->assertTrue(CircuitBreakerState::where('breaker_key', 'warehouse.outbox.warehouse.transfer.created')->exists());
        $this->assertTrue(CircuitBreakerCall::where('breaker_key', 'warehouse.outbox.warehouse.transfer.created')
            ->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)
            ->exists());
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'warehouse.outbox.dispatched',
        ]);
    }

    #[Test]
    public function warehouse_outbox_dispatcher_dead_letters_and_marks_compensation_required(): void
    {
        [$ledger, $message] = $this->warehouseOutbox('warehouse.pos.created', ['max_attempts' => 1]);

        $report = (new WarehouseOutboxDispatcher(handlers: [
            'warehouse.pos.created' => fn(): array => throw new RuntimeException('replica sync unavailable'),
        ]))->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dead_letter', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DEAD_LETTER, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_COMPENSATING, $ledger->status);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.required:' . $message->id,
            'status' => ReliabilityPolicy::STEP_PENDING,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'warehouse.compensation.required',
            'severity' => 'error',
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function product(array $overrides = []): ProductService
    {
        return ProductService::create(array_merge([
            'name' => 'Warehouse Product ' . Str::uuid(),
            'sku' => 'WRH-' . Str::upper((string) Str::random(12)),
            'type' => 'product',
            'quantity' => 0,
            'sale_price' => 25,
            'purchase_price' => 10,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides));
    }

    /**
     * @return array{0: OperationLedger, 1: OutboxMessage}
     */
    private function warehouseOutbox(string $eventType, array $messageOverrides = []): array
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'warehouse-test-' . Str::uuid(),
            'operation_type' => 'warehouse.test.operation',
            'domain' => 'warehouse',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create(array_merge([
            'message_key' => $eventType . ':' . Str::uuid(),
            'stream' => 'warehouse.operations',
            'event_type' => $eventType,
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => ProductService::class,
            'aggregate_id' => (string) Str::uuid(),
            'operation_ledger_id' => $ledger->id,
            'payload' => ['product_id' => (string) Str::uuid()],
            'metadata' => [
                'retry' => ['eligible' => true, 'max_attempts' => 3],
                'circuit_breaker' => ['eligible' => true],
            ],
            'max_attempts' => 3,
            'available_at' => now(),
            'expires_at' => now()->addDay(),
        ], $messageOverrides));

        return [$ledger, $message];
    }
}
