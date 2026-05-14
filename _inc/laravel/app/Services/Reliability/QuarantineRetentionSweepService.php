<?php

namespace App\Services\Reliability;

use App\Models\OperationQuarantine;
use App\Models\OperationQuarantineAudit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sweeps quarantine overlay rows past their retention TTL into 'dismissed' state with a
 * 'scheduler' actor on the audit row. Without this, manual-review/pending-review rows that
 * ops never act on accumulate indefinitely. Run on a schedule (every 6–12 hours in prod).
 *
 * Companion to F3 (orphaned-ledger sweep) — same shape, different table.
 */
class QuarantineRetentionSweepService
{
    private OperationalEventService $events;

    public function __construct(?OperationalEventService $events = null)
    {
        $this->events = $events ?? new OperationalEventService();
    }

    /**
     * @return array{scanned: int, dismissed: int, per_domain: array<string, int>}
     */
    public function sweep(int $limit = 200): array
    {
        $now = Carbon::now();
        $rows = OperationQuarantine::query()
            ->whereIn('status', [
                ReliabilityPolicy::QUARANTINE_PENDING_REVIEW,
                ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            ])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->orderBy('expires_at')
            ->limit(max(1, $limit))
            ->get();

        $perDomain = [];
        $dismissed = 0;
        $reason = 'Auto-dismissed past retention TTL.';

        foreach ($rows as $quarantine) {
            $this->expire($quarantine, $reason, $now);
            $dismissed++;
            $domain = (string) ($quarantine->domain ?: 'unknown');
            $perDomain[$domain] = ($perDomain[$domain] ?? 0) + 1;
        }

        return [
            'scanned' => $rows->count(),
            'dismissed' => $dismissed,
            'per_domain' => $perDomain,
        ];
    }

    private function expire(OperationQuarantine $quarantine, string $reason, Carbon $now): void
    {
        DB::transaction(function () use ($quarantine, $reason, $now): void {
            $quarantine->forceFill([
                'status' => ReliabilityPolicy::QUARANTINE_DISMISSED,
                'resolution_notes' => $reason,
                'resolved_at' => $now,
            ])->save();

            OperationQuarantineAudit::create([
                'operation_quarantine_id' => $quarantine->id,
                'operation_ledger_id' => $quarantine->operation_ledger_id,
                'source_record_id' => $quarantine->source_record_id,
                'domain' => $quarantine->domain,
                'action' => ReliabilityPolicy::QUARANTINE_ACTION_DISMISSED,
                'actor_type' => 'scheduler',
                'actor_id' => null,
                'details' => $reason,
                'metadata' => [
                    'quarantined_at' => $quarantine->quarantined_at?->toIso8601String(),
                    'expires_at' => $quarantine->expires_at?->toIso8601String(),
                ],
                'occurred_at' => $now,
            ]);
        });

        $this->events->record('reliability.quarantine.expired', 'Quarantine overlay auto-dismissed past retention TTL', [
            'quarantine_id' => $quarantine->id,
            'domain' => $quarantine->domain,
            'source_table' => $quarantine->source_table,
            'source_record_id' => $quarantine->source_record_id,
            'severity' => $quarantine->severity,
        ], [
            'severity' => 'notice',
            'criticality' => (string) ($quarantine->severity ?: ReliabilityPolicy::CRITICALITY_LOW),
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => ($quarantine->domain ?: 'system') . '.quarantine.gc',
            'source' => static::class,
            'operation_ledger_id' => $quarantine->operation_ledger_id,
            'subject_type' => $quarantine->source_type,
            'subject_id' => $quarantine->source_record_id,
        ]);
    }
}
