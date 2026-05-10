<?php

namespace App\Services\Reliability\Concerns;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use App\Services\Reliability\OperationalEventService;
use App\Services\Reliability\ReliabilityPolicy;

trait EmitsReliabilityEvents
{
    protected OperationalEventService $events;

    protected string $criticality = ReliabilityPolicy::CRITICALITY_MEDIUM;

    protected string $channel = 'system';

    protected ?OperationLedger $operationLedger = null;

    protected ?OutboxMessage $outboxMessage = null;

    protected string $source = self::class;

    /**
     * @param array<string, mixed> $context
     */
    protected function emitReliabilityEvent(string $eventType, string $summary, array $context = [], string $severity = 'info'): void
    {
        $this->events->record($eventType, $summary, $context, [
            'severity' => $severity,
            'criticality' => $this->criticality,
            'storage_mode' => ReliabilityPolicy::storageMode($this->criticality),
            'channel' => $this->channel,
            'source' => $this->source,
            'operation_ledger_id' => $this->operationLedger?->id,
            'outbox_message_id' => $this->outboxMessage?->id,
            'subject_type' => $this->operationLedger?->subject_type ?? $this->outboxMessage?->aggregate_type,
            'subject_id' => $this->operationLedger?->subject_id ?? $this->outboxMessage?->aggregate_id,
            'actor_id' => $this->operationLedger?->actor_id,
        ]);
    }
}
