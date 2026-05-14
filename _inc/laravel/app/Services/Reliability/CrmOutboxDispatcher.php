<?php

namespace App\Services\Reliability;

use App\Exceptions\Reliability\CircuitBreakerOpenException;
use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Illuminate\Support\Collection;
use Throwable;

class CrmOutboxDispatcher
{
    private OutboxService $outbox;

    private CriticalOperationService $operations;

    private OperationalEventService $events;

    private CrmCompensationService $compensation;

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
        ?CrmCompensationService $compensation = null,
        ?QuarantineService $quarantine = null,
        array $handlers = [],
        ?DomainSignalHandlerService $domainSignals = null,
    ) {
        $this->outbox = $outbox ?? new OutboxService();
        $this->operations = $operations ?? new CriticalOperationService();
        $this->events = $events ?? new OperationalEventService();
        $this->compensation = $compensation ?? new CrmCompensationService($this->operations, $this->events);
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

        if ($message->stream !== 'crm.operations') {
            return $this->report($message, 'skipped', ['reason' => 'not_crm_stream']);
        }

        $ledger = $message->operationLedger;
        if ($this->quarantine->hasBlockingQuarantineForLedger($ledger)) {
            $this->events->record('crm.outbox.quarantine_blocked', 'CRM outbox blocked by quarantine', [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
            ], $this->eventOptions($message, $ledger, 'critical', 'crm.quarantine'));

            return $this->report($message, 'skipped', ['reason' => 'quarantined_operation']);
        }

        $step = $this->operations->recordStep($ledger, 'outbox.dispatch:' . $message->id, 'Dispatch CRM outbox signal', [
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
                throw new \RuntimeException('No CRM outbox handler signal was produced.');
            }

            foreach ($signals as $index => $signal) {
                $signals[$index] = $signal = $this->domainSignals->handle($message, $ledger, $signal, 'crm');
                $this->recordSignalAccepted($message, $ledger, $signal);
            }

            $this->operations->succeedStep($step, ['signals' => $signals]);
            $this->outbox->markDispatched($message);
            $this->closeLedger($ledger);

            $this->events->record('crm.outbox.dispatched', 'CRM outbox dispatched', [
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

            $this->events->record('crm.outbox.dispatch_failed', 'CRM outbox dispatch failed', [
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
            ->where('stream', 'crm.operations')
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

            $circuitBreaker = CircuitBreaker::builder('crm.outbox.' . $this->breakerKeySegment($message->event_type))
                ->name('CRM outbox ' . $message->event_type)
                ->criticality($criticality)
                ->channel('crm.outbox')
                ->operationLedger($ledger)
                ->outboxMessage($message)
                ->slidingWindowSize(20)
                ->slidingWindowSeconds(300)
                ->failureRateThreshold(50.0)
                ->minimumCalls(5)
                ->openStateDurationSeconds(60)
                ->halfOpenAllowedCalls($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL ? 2 : 3)
                ->halfOpenConservative(in_array($criticality, [ReliabilityPolicy::CRITICALITY_CRITICAL, ReliabilityPolicy::CRITICALITY_HIGH], true))
                ->build();

            return $circuitBreaker->call(fn(): array => $this->resolveSignals($message), $context);
        };

        return Retry::builder('crm.outbox.dispatch.' . $message->event_type)
            ->criticality($criticality)
            ->channel('crm.outbox')
            ->operationLedger($ledger)
            ->outboxMessage($message)
            ->maxAttempts((int) data_get($message->metadata, 'retry.max_attempts', max(1, (int) $message->max_attempts)))
            ->intervalUsing(fn(int $attempt): int => ReliabilityPolicy::retryDelaySeconds($attempt))
            ->withSleep(false)
            ->retryOnAny()
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
            $this->signal('crm-projection', 'crm.projection', 'crm_record_projection', 'CRM projections can refresh after commit.'),
        ];

        if (str_contains($eventType, 'lead.convert')) {
            $signals[] = $this->signal('client-projection', 'crm.client_projection', 'converted_client_projection_shell', 'Converted client projections can absorb the lead conversion.');
            $signals[] = $this->signal('deal-pipeline', 'crm.pipeline', 'deal_pipeline_projection_shell', 'Deal pipeline projections can absorb the converted opportunity.');
            $signals[] = $this->signal('finance-opportunity-bridge', 'finance.crm_bridge', 'finance_opportunity_shell', 'Finance can observe the converted opportunity for later billing context.');
        }

        if (str_contains($eventType, 'deal.status') || str_contains($eventType, 'deal.stage')) {
            $signals[] = $this->signal('pipeline-reconciliation', 'crm.pipeline', 'pipeline_reconciliation_shell', 'Pipeline state can reconcile after the deal movement.');
            $signals[] = $this->signal('forecasting', 'crm.forecast', 'forecast_projection_shell', 'Forecast projections can absorb material deal status changes.');
        }

        if (str_contains($eventType, 'client') || str_contains($eventType, 'permission') || str_contains($eventType, 'user')) {
            $signals[] = $this->signal('access-projection', 'crm.access', 'crm_access_projection_shell', 'CRM access projections can reconcile user/client permissions.');
        }

        if (str_contains($eventType, 'customer') || str_contains($eventType, 'vendor') || str_contains($eventType, 'crm.client.')) {
            $signals[] = $this->signal('relationship-projection', 'crm.relationships', 'crm_relationship_projection_shell', 'CRM relationship projections can refresh durable customer/vendor/client records.');
            $signals[] = $this->signal('finance-relationship-bridge', 'finance.crm_relationship_bridge', 'finance_relationship_context_shell', 'Finance can reconcile billing and payable context after CRM relationship changes.');
            $signals[] = $this->signal('project-relationship-bridge', 'project.crm_relationship_bridge', 'project_relationship_context_shell', 'Project planning can reconcile client context after CRM relationship changes.');
        }

        if (str_contains($eventType, 'deal.') || str_contains($eventType, 'lead.')) {
            $signals[] = $this->signal('project-crm-bridge', 'project.crm_bridge', 'project_crm_signal_shell', 'Project planning can observe accepted CRM lifecycle decisions.');
        }

        if (str_contains($eventType, 'product') || str_contains($eventType, 'source')) {
            $signals[] = $this->signal('catalog-context', 'crm.catalog', 'crm_catalog_context_shell', 'CRM catalog/source context projections can refresh after commit.');
        }

        $signals[] = $this->signal('communication', 'crm.communication', 'communication_api_shell', 'CRM communication callbacks can run after commit.');
        $signals[] = $this->signal('webhook', 'crm.webhook', 'webhook_shell', 'Webhook subscribers can be notified after CRM commit.');

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
        $this->events->record('crm.signal.accepted', $signal['summary'] ?? 'CRM signal accepted', [
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'signal' => $signal,
        ], $this->eventOptions($message, $ledger, 'notice', (string) ($signal['channel'] ?? 'crm')));
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
    private function eventOptions(OutboxMessage $message, ?OperationLedger $ledger, string $severity = 'info', string $channel = 'crm'): array
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
