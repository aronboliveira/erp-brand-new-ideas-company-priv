<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OperationQuarantine;
use App\Models\OperationQuarantineAudit;
use Illuminate\Support\Facades\DB;

class QuarantineService
{
    private QuarantineRemediationJudge $judge;

    private OperationalEventService $events;

    public function __construct(
        ?QuarantineRemediationJudge $judge = null,
        ?OperationalEventService $events = null,
    ) {
        $this->judge = $judge ?? new QuarantineRemediationJudge();
        $this->events = $events ?? new OperationalEventService();
    }

    public function route(PostWriteValidationResult $validation, ?OperationLedger $ledger = null): OperationQuarantine
    {
        $decision = $this->judge->decide($validation);

        $quarantine = DB::transaction(function () use ($validation, $ledger, $decision): OperationQuarantine {
            $record = OperationQuarantine::create([
                'operation_ledger_id' => $ledger?->id,
                'source_table' => $validation->sourceTable,
                'source_type' => $validation->sourceType,
                'source_record_id' => $validation->sourceRecordId,
                'domain' => $validation->domain,
                'severity' => $validation->severity,
                'status' => $decision->status,
                'remediation_decision' => $decision->decision,
                'failed_criteria' => $validation->failedCriteria,
                'validation_errors' => $validation->validationErrors,
                'snapshot_payload' => $validation->snapshotPayload,
                'origin_event' => $validation->originEvent,
                'resolution_notes' => $decision->details,
                'actor_id' => $ledger?->actor_id,
                'quarantined_at' => now(),
                'resolved_at' => $decision->status === ReliabilityPolicy::QUARANTINE_ROLLED_BACK ? now() : null,
                'expires_at' => now()->addDays(ReliabilityPolicy::retentionDays('operation_quarantine', $validation->severity)),
            ]);

            $this->audit($record, ReliabilityPolicy::QUARANTINE_ACTION_QUARANTINED, 'Post-write validation routed the record to quarantine.', [
                'failed_criteria' => $validation->failedCriteria,
                'validation_errors' => $validation->validationErrors,
            ]);
            $this->audit($record, ReliabilityPolicy::QUARANTINE_ACTION_JUDGE_DECISION, $decision->details, [
                'decision' => $decision->decision,
                'status' => $decision->status,
            ]);
            $this->audit($record, $decision->action, $decision->details, [
                'source_table' => $validation->sourceTable,
                'source_record_id' => $validation->sourceRecordId,
            ]);

            return $record;
        });

        $this->events->record('finance.quarantine.' . $quarantine->status, 'Finance quarantine triggered', [
            'quarantine_id' => $quarantine->id,
            'source_table' => $quarantine->source_table,
            'source_record_id' => $quarantine->source_record_id,
            'failed_criteria' => $quarantine->failed_criteria,
            'validation_errors' => $quarantine->validation_errors,
        ], [
            'severity' => $validation->severity === ReliabilityPolicy::CRITICALITY_CRITICAL ? 'critical' : 'error',
            'criticality' => $validation->severity,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => 'finance.quarantine',
            'source' => static::class,
            'operation_ledger_id' => $ledger?->id,
            'subject_type' => $validation->sourceType,
            'subject_id' => $validation->sourceRecordId,
            'actor_id' => $ledger?->actor_id,
        ]);

        return $quarantine;
    }

    public function hasBlockingQuarantineForLedger(?OperationLedger $ledger): bool
    {
        if (!$ledger) {
            return false;
        }

        return OperationQuarantine::where('operation_ledger_id', $ledger->id)
            ->whereNotIn('status', [
                ReliabilityPolicy::QUARANTINE_RECOVERED,
                ReliabilityPolicy::QUARANTINE_DISMISSED,
            ])
            ->exists();
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function audit(OperationQuarantine $quarantine, string $action, string $details, array $metadata = []): OperationQuarantineAudit
    {
        return OperationQuarantineAudit::create([
            'operation_quarantine_id' => $quarantine->id,
            'operation_ledger_id' => $quarantine->operation_ledger_id,
            'source_record_id' => $quarantine->source_record_id,
            'domain' => $quarantine->domain,
            'action' => $action,
            'actor_type' => 'system',
            'actor_id' => $quarantine->actor_id,
            'details' => $details,
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }
}
