<?php

namespace App\Services\Reliability;

use App\Exceptions\Reliability\CircuitBreakerOpenException;
use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Illuminate\Support\Collection;
use Throwable;

class WarehouseOutboxDispatcher
{
    private OutboxService $outbox;

    private CriticalOperationService $operations;

    private OperationalEventService $events;

    private WarehouseCompensationService $compensation;

    private QuarantineService $quarantine;

    private DomainSignalHandlerService $domainSignals;

    /**
     * @var array<string, callable(OutboxMessage): array<int, array<string, mixed>>>
     */
    private array $handlers;

    /**
     * @param array<string, callable(OutboxMessage): array<int, array<string, mixed>>> $handlers
     */
    public function __construct(
        ?OutboxService $outbox = null,
        ?CriticalOperationService $operations = null,
        ?OperationalEventService $events = null,
        ?WarehouseCompensationService $compensation = null,
        ?QuarantineService $quarantine = null,
        array $handlers = [],
        ?DomainSignalHandlerService $domainSignals = null,
    ) {
        $this->outbox = $outbox ?? new OutboxService();
        $this->operations = $operations ?? new CriticalOperationService();
        $this->events = $events ?? new OperationalEventService();
        $this->compensation = $compensation ?? new WarehouseCompensationService($this->operations, $this->events);
        $this->quarantine = $quarantine ?? new QuarantineService();
        $this->handlers = $handlers;
        $this->domainSignals = $domainSignals ?? new DomainSignalHandlerService(events: $this->events);
    }

    /**
     * @return array<string, mixed>
     */
    public function dispatchPending(int $limit = 50): array
    {
        $reports = $this->dispatchableQuery()
            ->limit(max(1, $limit))
            ->get()
            ->map(fn(OutboxMessage $message): array => $this->dispatchMessage($message));

        return $this->summarize($reports);
    }

    /**
     * @return array<string, mixed>
     */
    public function dispatchByMessageKey(string $messageKey): array
    {
        $message = OutboxMessage::where('message_key', $messageKey)->first();
        if (!$message) {
            return ['status' => 'missing', 'message_key' => $messageKey];
        }

        return $this->dispatchMessage($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function dispatchMessage(OutboxMessage $message): array
    {
        if ($message->status === ReliabilityPolicy::OUTBOX_DISPATCHED) {
            return $this->report($message, 'skipped', ['reason' => 'already_dispatched']);
        }

        if ($message->stream !== 'warehouse.operations') {
            return $this->report($message, 'skipped', ['reason' => 'not_warehouse_stream']);
        }

        $ledger = $message->operationLedger;
        if ($this->quarantine->hasBlockingQuarantineForLedger($ledger)) {
            $this->events->record('warehouse.outbox.quarantine_blocked', 'Warehouse outbox blocked by quarantine', [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
            ], $this->eventOptions($message, $ledger, 'critical', 'warehouse.quarantine'));

            return $this->report($message, 'skipped', ['reason' => 'quarantined_operation']);
        }

        $step = $this->operations->recordStep($ledger, 'outbox.dispatch:' . $message->id, 'Dispatch warehouse outbox signal', [
            'step_type' => 'outbox',
            'sequence' => 700,
            'status' => ReliabilityPolicy::STEP_RUNNING,
            'payload' => [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
                'stream' => $message->stream,
            ],
            'started_at' => now(),
        ]);

        try {
            $signals = $this->resolveSignalsWithGuards($message, $ledger);
            if ($signals === []) {
                throw new \RuntimeException('No warehouse outbox handler signal was produced.');
            }

            foreach ($signals as $index => $signal) {
                $signals[$index] = $signal = $this->domainSignals->handle($message, $ledger, $signal, 'warehouse');
                $this->recordSignalAccepted($message, $ledger, $signal);
            }

            $this->operations->succeedStep($step, ['signals' => $signals]);
            $this->outbox->markDispatched($message);
            $this->closeLedger($ledger);

            $this->events->record('warehouse.outbox.dispatched', 'Warehouse outbox dispatched', [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
                'signals' => $signals,
            ], $this->eventOptions($message, $ledger));

            return $this->report($message->fresh() ?? $message, 'dispatched', ['signals' => $signals]);
        } catch (Throwable $throwable) {
            $this->operations->failStep($step, $throwable->getMessage());

            $willDeadLetter = ((int) $message->retry_count + 1) >= max(1, (int) $message->max_attempts);
            $this->outbox->markFailed($message, $throwable->getMessage(), $willDeadLetter);
            $message = $message->fresh() ?? $message;

            $this->events->record('warehouse.outbox.dispatch_failed', 'Warehouse outbox dispatch failed', [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
                'dead_letter' => $willDeadLetter,
                'message' => $throwable->getMessage(),
            ], $this->eventOptions($message, $ledger, $willDeadLetter ? 'error' : 'warning'));

            if ($willDeadLetter) {
                $this->compensation->markRequired($message, $throwable->getMessage(), [
                    'dispatch_status' => ReliabilityPolicy::OUTBOX_DEAD_LETTER,
                ]);
            }

            return $this->report($message, $willDeadLetter ? 'dead_letter' : 'failed', [
                'error' => $throwable->getMessage(),
                'next_retry_at' => $message->next_retry_at?->toIso8601String(),
            ]);
        }
    }

    private function dispatchableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return OutboxMessage::query()
            ->where('stream', 'warehouse.operations')
            ->where(function ($query): void {
                $query->whereIn('status', [ReliabilityPolicy::OUTBOX_PENDING, ReliabilityPolicy::OUTBOX_READY])
                    ->orWhere(function ($failed): void {
                        $failed->where('status', ReliabilityPolicy::OUTBOX_FAILED)
                            ->where(function ($retry): void {
                                $retry->whereNull('next_retry_at')
                                    ->orWhere('next_retry_at', '<=', now());
                            });
                    });
            })
            ->where(function ($query): void {
                $query->whereNull('available_at')
                    ->orWhere('available_at', '<=', now());
            })
            ->orderBy('available_at')
            ->orderBy('created_at');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveSignalsWithGuards(OutboxMessage $message, ?OperationLedger $ledger): array
    {
        $context = [
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'stream' => $message->stream,
        ];
        $criticality = ReliabilityPolicy::normalizeCriticality($message->criticality);
        $circuitBreakerRequired = (bool) data_get($message->metadata, 'circuit_breaker.eligible', true);

        $runner = function () use ($message, $ledger, $context, $criticality, $circuitBreakerRequired): array {
            if (!$circuitBreakerRequired) {
                return $this->resolveSignals($message);
            }

            $circuitBreaker = CircuitBreaker::builder('warehouse.outbox.' . $this->breakerKeySegment($message->event_type))
                ->name('Warehouse outbox ' . $message->event_type)
                ->criticality($criticality)
                ->channel('warehouse.outbox')
                ->operationLedger($ledger)
                ->outboxMessage($message)
                ->slidingWindowSize($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL ? 12 : 20)
                ->slidingWindowSeconds(300)
                ->failureRateThreshold($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL ? 35.0 : 50.0)
                ->minimumCalls($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL ? 3 : 5)
                ->openStateDurationSeconds($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL ? 120 : 60)
                ->halfOpenAllowedCalls($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL ? 2 : 3)
                ->halfOpenConservative(in_array($criticality, [ReliabilityPolicy::CRITICALITY_CRITICAL, ReliabilityPolicy::CRITICALITY_HIGH], true))
                ->build();

            return $circuitBreaker->call(fn(): array => $this->resolveSignals($message), $context);
        };

        return Retry::builder('warehouse.outbox.dispatch.' . $message->event_type)
            ->criticality($criticality)
            ->channel('warehouse.outbox')
            ->operationLedger($ledger)
            ->outboxMessage($message)
            ->maxAttempts((int) data_get($message->metadata, 'retry.max_attempts', max(1, (int) $message->max_attempts)))
            ->intervalUsing(fn(int $attempt): int => ReliabilityPolicy::retryDelaySeconds($attempt))
            ->abortOn(CircuitBreakerOpenException::class)
            ->build()
            ->run(fn(): array => $runner(), $context);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveSignals(OutboxMessage $message): array
    {
        if (isset($this->handlers[$message->event_type])) {
            $signals = ($this->handlers[$message->event_type])($message);

            return is_array($signals) ? $signals : [];
        }

        return $this->defaultSignals($message);
    }

    private function breakerKeySegment(string $eventType): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $eventType) ?: 'unknown';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultSignals(OutboxMessage $message): array
    {
        $eventType = $message->event_type;
        $signals = [
            $this->signal('stock-projection', 'warehouse.stock_projection', 'stock_projection_refresh_shell', 'Stock projections can refresh after warehouse commit.'),
            $this->signal('stock-reconciliation', 'warehouse.reconciliation', 'stock_reconciliation_shell', 'Inventory reconciliation can verify product, warehouse, and stock-report quantities.'),
            $this->signal('replica-sync', 'warehouse.replica_sync', 'inventory_replica_sync_shell', 'Inventory replicas can synchronize from the committed warehouse ledger.'),
        ];

        if (str_contains($eventType, 'transfer')) {
            $signals[] = $this->signal('transfer-ledger', 'warehouse.transfer', 'warehouse_transfer_projection_shell', 'Warehouse transfer projections can refresh source and destination stock.');
            $signals[] = $this->signal('logistics-callback', 'warehouse.logistics', 'logistics_callback_shell', 'Logistics callbacks can consume transfer dispatch/receipt state.');
        }

        if (str_contains($eventType, 'stock') || str_contains($eventType, 'product') || str_contains($eventType, 'import')) {
            $signals[] = $this->signal('valuation-refresh', 'warehouse.valuation', 'stock_valuation_refresh_shell', 'Stock valuation reports can refresh quantity and pricing facts.');
            $signals[] = $this->signal('catalog-replica', 'warehouse.catalog_replica', 'catalog_replica_sync_shell', 'Product catalog replicas can absorb decisive product/service changes.');
        }

        if (str_contains($eventType, 'purchase')) {
            $signals[] = $this->signal('finance-purchase-bridge', 'finance.purchase_bridge', 'finance_purchase_inventory_shell', 'Finance/accounting can consume purchase inventory effects after commit.');
            $signals[] = $this->signal('supplier-stock', 'warehouse.supplier_stock', 'supplier_stock_projection_shell', 'Supplier-facing stock projections can refresh after purchase commit.');
        }

        if (str_contains($eventType, 'pos')) {
            $signals[] = $this->signal('finance-pos-bridge', 'finance.pos_bridge', 'finance_pos_inventory_shell', 'Finance/accounting can consume POS inventory effects after commit.');
            $signals[] = $this->signal('customer-stock', 'warehouse.customer_stock', 'customer_stock_projection_shell', 'Customer-facing availability can refresh after POS stock consumption.');
        }

        $signals[] = $this->signal('webhook', 'warehouse.webhook', 'webhook_shell', 'Webhook subscribers can be notified after warehouse/product commit.');

        return $signals;
    }

    /**
     * @return array<string, string>
     */
    private function signal(string $name, string $channel, string $target, string $summary): array
    {
        return [
            'name' => $name,
            'channel' => $channel,
            'target' => $target,
            'mode' => 'monolith_callback',
            'status' => 'accepted',
            'summary' => $summary,
        ];
    }

    /**
     * @param array<string, mixed> $signal
     */
    private function recordSignalAccepted(OutboxMessage $message, ?OperationLedger $ledger, array $signal): void
    {
        $this->events->record('warehouse.signal.accepted', $signal['summary'] ?? 'Warehouse signal accepted', [
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'signal' => $signal,
        ], $this->eventOptions($message, $ledger, 'notice', (string) ($signal['channel'] ?? 'warehouse')));
    }

    private function closeLedger(?OperationLedger $ledger): void
    {
        if (!$ledger || in_array($ledger->status, [ReliabilityPolicy::STATUS_COMPENSATING, ReliabilityPolicy::STATUS_COMPENSATED], true)) {
            return;
        }

        $ledger->forceFill([
            'status' => ReliabilityPolicy::STATUS_CLOSED,
            'closed_at' => now(),
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function report(OutboxMessage $message, string $status, array $extra = []): array
    {
        return array_merge([
            'status' => $status,
            'message_id' => $message->id,
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'outbox_status' => $message->status,
            'retry_count' => (int) $message->retry_count,
        ], $extra);
    }

    /**
     * @param Collection<int, array<string, mixed>> $reports
     * @return array<string, mixed>
     */
    private function summarize(Collection $reports): array
    {
        return [
            'processed' => $reports->count(),
            'dispatched' => $reports->where('status', 'dispatched')->count(),
            'failed' => $reports->where('status', 'failed')->count(),
            'dead_letter' => $reports->where('status', 'dead_letter')->count(),
            'skipped' => $reports->where('status', 'skipped')->count(),
            'reports' => $reports->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventOptions(OutboxMessage $message, ?OperationLedger $ledger, string $severity = 'info', string $channel = 'warehouse'): array
    {
        return [
            'severity' => $severity,
            'criticality' => $message->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => $channel,
            'source' => static::class,
            'operation_ledger_id' => $ledger?->id,
            'outbox_message_id' => $message->id,
            'subject_type' => $message->aggregate_type,
            'subject_id' => $message->aggregate_id,
            'actor_id' => $ledger?->actor_id,
        ];
    }
}
