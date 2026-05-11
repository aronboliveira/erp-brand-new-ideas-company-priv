<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;

class HrmCompensationService
{
    private CriticalOperationService $operations;

    private OperationalEventService $events;

    public function __construct(
        ?CriticalOperationService $operations = null,
        ?OperationalEventService $events = null,
    ) {
        $this->operations = $operations ?? new CriticalOperationService();
        $this->events = $events ?? new OperationalEventService();
    }

    public function markRequired(OutboxMessage $message, string $reason, array $context = []): ?OperationLedger
    {
        $ledger = $message->operationLedger;
        if (!$ledger) {
            return null;
        }

        $this->operations->recordStep($ledger, 'compensation.required:' . $message->id, 'HRM compensation required', [
            'step_type' => 'cleanup',
            'sequence' => 900,
            'status' => ReliabilityPolicy::STEP_PENDING,
            'payload' => array_merge([
                'outbox_message_id' => $message->id,
                'event_type' => $message->event_type,
                'message_key' => $message->message_key,
            ], $context),
            'error_message' => $reason,
            'started_at' => now(),
        ]);

        $ledger->forceFill([
            'status' => ReliabilityPolicy::STATUS_COMPENSATING,
            'error_message' => $reason,
            'failed_at' => now(),
        ])->save();

        $this->events->record('hrm.compensation.required', 'HRM operation needs compensation', [
            'reason' => $reason,
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
        ], $this->eventOptions($ledger, $message, 'error'));

        return $ledger;
    }

    /**
     * @return array<string, mixed>
     */
    private function eventOptions(OperationLedger $ledger, OutboxMessage $message, string $severity = 'info'): array
    {
        return [
            'severity' => $severity,
            'criticality' => $ledger->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => 'hrm',
            'source' => static::class,
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'subject_type' => $ledger->subject_type,
            'subject_id' => $ledger->subject_id,
            'actor_id' => $ledger->actor_id,
        ];
    }
}
