<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Illuminate\Support\Facades\Route;

class ReliabilityClientPayloadService
{
    /**
     * @param array<string, mixed>|null $dispatchReport
     * @return array<string, mixed>
     */
    public function fromFinanceResult(FinanceOperationResult $result, ?array $dispatchReport = null): array
    {
        $ledger = $result->ledger()?->fresh();
        $outbox = $result->outboxMessage()?->fresh();

        return [
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'summary' => $ledger?->summary ?? 'Finance operation',
            'ledger_status' => $ledger?->status,
            'outbox_status' => $outbox?->status,
            'dispatch_status' => $dispatchReport['status'] ?? null,
            'message_key' => $outbox?->message_key,
            'progress' => $this->progress($ledger, $outbox),
            'status_url' => $ledger && Route::has('reliability.operations.show')
                ? route('reliability.operations.show', ['operation' => $ledger->operation_key])
                : null,
        ];
    }

    /**
     * @param array<string, mixed>|null $dispatchReport
     * @return array<string, mixed>
     */
    public function fromWarehouseResult(WarehouseOperationResult $result, ?array $dispatchReport = null): array
    {
        $ledger = $result->ledger()?->fresh();
        $outbox = $result->outboxMessage()?->fresh();

        return [
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'summary' => $ledger?->summary ?? 'Warehouse operation',
            'ledger_status' => $ledger?->status,
            'outbox_status' => $outbox?->status,
            'dispatch_status' => $dispatchReport['status'] ?? null,
            'message_key' => $outbox?->message_key,
            'progress' => $this->progress($ledger, $outbox),
            'status_url' => $ledger && Route::has('reliability.operations.show')
                ? route('reliability.operations.show', ['operation' => $ledger->operation_key])
                : null,
        ];
    }

    /**
     * @param array<string, mixed>|null $dispatchReport
     * @return array<string, mixed>
     */
    public function fromCrmResult(CrmOperationResult $result, ?array $dispatchReport = null): array
    {
        $ledger = $result->ledger()?->fresh();
        $outbox = $result->outboxMessage()?->fresh();

        return [
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'summary' => $ledger?->summary ?? 'CRM operation',
            'ledger_status' => $ledger?->status,
            'outbox_status' => $outbox?->status,
            'dispatch_status' => $dispatchReport['status'] ?? null,
            'message_key' => $outbox?->message_key,
            'progress' => $this->progress($ledger, $outbox),
            'status_url' => $ledger && Route::has('reliability.operations.show')
                ? route('reliability.operations.show', ['operation' => $ledger->operation_key])
                : null,
        ];
    }

    /**
     * @param array<string, mixed>|null $dispatchReport
     * @return array<string, mixed>
     */
    public function fromPlanningResult(PlanningOperationResult $result, ?array $dispatchReport = null): array
    {
        $ledger = $result->ledger()?->fresh();
        $outbox = $result->outboxMessage()?->fresh();

        return [
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'summary' => $ledger?->summary ?? 'Planning operation',
            'ledger_status' => $ledger?->status,
            'outbox_status' => $outbox?->status,
            'dispatch_status' => $dispatchReport['status'] ?? null,
            'message_key' => $outbox?->message_key,
            'progress' => $this->progress($ledger, $outbox),
            'status_url' => $ledger && Route::has('reliability.operations.show')
                ? route('reliability.operations.show', ['operation' => $ledger->operation_key])
                : null,
        ];
    }

    private function progress(?OperationLedger $ledger, ?OutboxMessage $outbox): int
    {
        if (!$ledger) {
            return 0;
        }

        if (in_array($ledger->status, [ReliabilityPolicy::STATUS_FAILED, ReliabilityPolicy::STATUS_COMPENSATING], true)) {
            return 100;
        }

        if ($outbox && in_array($outbox->status, [ReliabilityPolicy::OUTBOX_DEAD_LETTER, ReliabilityPolicy::OUTBOX_CANCELLED], true)) {
            return 100;
        }

        if ($ledger->status === ReliabilityPolicy::STATUS_CLOSED || $outbox?->status === ReliabilityPolicy::OUTBOX_DISPATCHED) {
            return 100;
        }

        if ($outbox && in_array($outbox->status, [ReliabilityPolicy::OUTBOX_PENDING, ReliabilityPolicy::OUTBOX_READY, ReliabilityPolicy::OUTBOX_FAILED], true)) {
            return 65;
        }

        if (in_array($ledger->status, [ReliabilityPolicy::STATUS_COMMITTED, ReliabilityPolicy::STATUS_POSTED_TO_LEDGER], true)) {
            return 50;
        }

        return 25;
    }
}
