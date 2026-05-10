<?php

namespace App\Http\Controllers\Reliability;

use App\Http\Controllers\Controller;
use App\Models\OperationLedger;
use App\Services\Reliability\ReliabilityPolicy;
use Illuminate\Http\JsonResponse;

class OperationStatusController extends Controller
{
    public function show(string $operation): JsonResponse
    {
        $ledger = OperationLedger::with([
            'steps' => fn($query) => $query->orderBy('sequence')->orderBy('created_at'),
            'outboxMessages' => fn($query) => $query->orderBy('created_at'),
            'operationalEvents' => fn($query) => $query->latest('occurred_at')->limit(20),
        ])
            ->where('operation_key', $operation)
            ->orWhere('id', $operation)
            ->firstOrFail();

        return response()->json([
            'operation_key' => $ledger->operation_key,
            'operation_type' => $ledger->operation_type,
            'summary' => $ledger->summary,
            'ledger_status' => $ledger->status,
            'criticality' => $ledger->criticality,
            'progress' => $this->progress($ledger),
            'started_at' => $ledger->started_at?->toIso8601String(),
            'committed_at' => $ledger->committed_at?->toIso8601String(),
            'closed_at' => $ledger->closed_at?->toIso8601String(),
            'failed_at' => $ledger->failed_at?->toIso8601String(),
            'steps' => $ledger->steps->map(fn($step): array => [
                'key' => $step->step_key,
                'name' => $step->step_name,
                'type' => $step->step_type,
                'status' => $step->status,
                'sequence' => $step->sequence,
                'error' => $step->error_message,
            ])->values(),
            'outbox' => $ledger->outboxMessages->map(fn($message): array => [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
                'stream' => $message->stream,
                'status' => $message->status,
                'retry_count' => $message->retry_count,
                'last_error' => $message->last_error,
                'next_retry_at' => $message->next_retry_at?->toIso8601String(),
                'dispatched_at' => $message->dispatched_at?->toIso8601String(),
            ])->values(),
            'events' => $ledger->operationalEvents->map(fn($event): array => [
                'event_type' => $event->event_type,
                'severity' => $event->severity,
                'channel' => $event->channel,
                'summary' => $event->summary,
                'occurred_at' => $event->occurred_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    private function progress(OperationLedger $ledger): int
    {
        $outboxStatus = $ledger->outboxMessages->first()?->status;

        if (in_array($ledger->status, [ReliabilityPolicy::STATUS_FAILED, ReliabilityPolicy::STATUS_COMPENSATING], true)) {
            return 100;
        }

        if (in_array($outboxStatus, [ReliabilityPolicy::OUTBOX_DEAD_LETTER, ReliabilityPolicy::OUTBOX_CANCELLED], true)) {
            return 100;
        }

        if ($ledger->status === ReliabilityPolicy::STATUS_CLOSED || $outboxStatus === ReliabilityPolicy::OUTBOX_DISPATCHED) {
            return 100;
        }

        if (in_array($outboxStatus, [ReliabilityPolicy::OUTBOX_PENDING, ReliabilityPolicy::OUTBOX_READY, ReliabilityPolicy::OUTBOX_FAILED], true)) {
            return 65;
        }

        if (in_array($ledger->status, [ReliabilityPolicy::STATUS_COMMITTED, ReliabilityPolicy::STATUS_POSTED_TO_LEDGER], true)) {
            return 50;
        }

        return 25;
    }
}
