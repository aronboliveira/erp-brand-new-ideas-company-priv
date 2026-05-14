<?php

namespace App\Services\Reliability;

use App\Exceptions\Reliability\CircuitBreakerOpenException;
use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Illuminate\Support\Collection;
use Throwable;

class HrmOutboxDispatcher
{
    private OutboxService $outbox;

    private CriticalOperationService $operations;

    private OperationalEventService $events;

    private HrmCompensationService $compensation;

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
        ?HrmCompensationService $compensation = null,
        ?QuarantineService $quarantine = null,
        array $handlers = [],
        ?DomainSignalHandlerService $domainSignals = null,
    ) {
        $this->outbox = $outbox ?? new OutboxService();
        $this->operations = $operations ?? new CriticalOperationService();
        $this->events = $events ?? new OperationalEventService();
        $this->compensation = $compensation ?? new HrmCompensationService($this->operations, $this->events);
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

        if ($message->stream !== 'hrm.operations') {
            return $this->report($message, 'skipped', ['reason' => 'not_hrm_stream']);
        }

        $ledger = $message->operationLedger;
        if ($this->quarantine->hasBlockingQuarantineForLedger($ledger)) {
            $this->events->record('hrm.outbox.quarantine_blocked', 'HRM outbox blocked by quarantine', [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
            ], $this->eventOptions($message, $ledger, 'critical', 'hrm.quarantine'));

            return $this->report($message, 'skipped', ['reason' => 'quarantined_operation']);
        }

        $step = $this->operations->recordStep($ledger, 'outbox.dispatch:' . $message->id, 'Dispatch HRM outbox signal', [
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
                throw new \RuntimeException('No HRM outbox handler signal was produced.');
            }

            foreach ($signals as $index => $signal) {
                $signals[$index] = $signal = $this->domainSignals->handle($message, $ledger, $signal, 'hrm');
                $this->recordSignalAccepted($message, $ledger, $signal);
            }

            $this->operations->succeedStep($step, ['signals' => $signals]);
            $this->outbox->markDispatched($message);
            $this->closeLedger($ledger);

            $this->events->record('hrm.outbox.dispatched', 'HRM outbox dispatched', [
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

            $this->events->record('hrm.outbox.dispatch_failed', 'HRM outbox dispatch failed', [
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
            ->where('stream', 'hrm.operations')
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

            $circuitBreaker = CircuitBreaker::builder('hrm.outbox.' . $this->breakerKeySegment($message->event_type))
                ->name('HRM outbox ' . $message->event_type)
                ->criticality($criticality)
                ->channel('hrm.outbox')
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

        return Retry::builder('hrm.outbox.dispatch.' . $message->event_type)
            ->criticality($criticality)
            ->channel('hrm.outbox')
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
            $this->signal('employee-record', 'hrm.employee_record', 'employee_record_projection', 'Employee record projection can refresh after commit.'),
        ];

        if (str_contains($eventType, 'salary') || str_contains($eventType, 'payroll')) {
            $signals[] = $this->signal('payroll-control', 'hrm.payroll', 'payroll_recalculation_shell', 'Payroll control can recalculate employee compensation after commit.');
            $signals[] = $this->signal('finance-payroll-bridge', 'finance.payroll_bridge', 'finance_payroll_shell', 'Finance payroll bridge can consume the salary mutation.');
        }

        if (str_contains($eventType, 'termination') || str_contains($eventType, 'employee.deleted')) {
            $signals[] = $this->signal('access-control', 'hrm.access', 'rbac_reconciliation_shell', 'Access control can reconcile user role and session state.');
            $signals[] = $this->signal('payroll-offboarding', 'hrm.payroll', 'payroll_offboarding_shell', 'Payroll can stop future compensation cycles.');
        }

        if (str_contains($eventType, 'leave')) {
            $signals[] = $this->signal('calendar-control', 'hrm.calendar', 'calendar_availability_shell', 'Calendar availability can absorb the leave decision.');
            $signals[] = $this->signal('payroll-leave', 'hrm.payroll', 'leave_payroll_adjustment_shell', 'Payroll can absorb leave discount/paid-leave effects.');
        }

        $signals[] = $this->signal('communication', 'hrm.communication', 'communication_api_shell', 'HR communication callbacks can run after commit.');
        $signals[] = $this->signal('webhook', 'hrm.webhook', 'webhook_shell', 'Webhook subscribers can be notified after HRM commit.');

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
        $this->events->record('hrm.signal.accepted', $signal['summary'] ?? 'HRM signal accepted', [
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'signal' => $signal,
        ], $this->eventOptions($message, $ledger, 'notice', (string) ($signal['channel'] ?? 'hrm')));
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
    private function eventOptions(OutboxMessage $message, ?OperationLedger $ledger, string $severity = 'info', string $channel = 'hrm'): array
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
