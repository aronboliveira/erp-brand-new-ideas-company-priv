<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OperationStep;
use Illuminate\Support\Carbon;

/**
 * Sweeps ledger rows stuck in 'started' past their criticality-tiered TTL and flips them to 'failed'.
 *
 * After F1+F2 the only remaining way to land here is a PHP process kill between the
 * `recordStep(RUNNING)` write and `DB::transaction()` entry — a sub-millisecond window.
 * In that case the data did NOT commit (the transaction never began), but the ledger row exists
 * in 'started' forever without this sweeper. Run on a schedule (every 5–10 min in prod).
 */
class ReliabilityLedgerSweepService
{
    private OperationalEventService $events;

    public function __construct(?OperationalEventService $events = null)
    {
        $this->events = $events ?? new OperationalEventService();
    }

    /**
     * Scan all eligible criticalities and flip orphaned ledgers to 'failed'.
     *
     * @return array{scanned: int, orphaned: int, per_criticality: array<string, int>}
     */
    public function sweep(int $limit = 200): array
    {
        $now = Carbon::now();
        $perCriticality = [];
        $orphaned = 0;
        $scanned = 0;
        $reason = 'Orphaned ledger swept by reliability GC.';

        foreach ([ReliabilityPolicy::CRITICALITY_CRITICAL, ReliabilityPolicy::CRITICALITY_HIGH, ReliabilityPolicy::CRITICALITY_MEDIUM] as $criticality) {
            $ttl = ReliabilityPolicy::orphanedLedgerTtlSeconds($criticality);
            if ($ttl === null) {
                continue;
            }

            $cutoff = $now->copy()->subSeconds($ttl);

            $rows = OperationLedger::query()
                ->where('status', ReliabilityPolicy::STATUS_STARTED)
                ->where('criticality', $criticality)
                ->where('started_at', '<=', $cutoff)
                ->orderBy('started_at')
                ->limit(max(1, $limit))
                ->get();

            $perCriticality[$criticality] = $rows->count();
            $scanned += $rows->count();

            foreach ($rows as $ledger) {
                $this->markOrphaned($ledger, $reason, $now);
                $orphaned++;
            }
        }

        return [
            'scanned' => $scanned,
            'orphaned' => $orphaned,
            'per_criticality' => $perCriticality,
        ];
    }

    private function markOrphaned(OperationLedger $ledger, string $reason, Carbon $now): void
    {
        $ledger->forceFill([
            'status' => ReliabilityPolicy::STATUS_FAILED,
            'error_message' => $reason,
            'failed_at' => $now,
        ])->save();

        // Flip the db_transaction step (if it's still in RUNNING) so observability matches.
        OperationStep::query()
            ->where('operation_ledger_id', $ledger->id)
            ->where('step_key', 'operation.db_transaction')
            ->where('status', ReliabilityPolicy::STEP_RUNNING)
            ->update([
                'status' => ReliabilityPolicy::STEP_FAILED,
                'error_message' => $reason,
                'finished_at' => $now,
                'updated_at' => $now,
            ]);

        $this->events->record('reliability.operation.orphaned', 'Orphaned operation ledger swept to failed', [
            'operation_key' => $ledger->operation_key,
            'operation_type' => $ledger->operation_type,
            'domain' => $ledger->domain,
            'criticality' => $ledger->criticality,
            'started_at' => $ledger->started_at?->toIso8601String(),
            'age_seconds' => $ledger->started_at ? $now->diffInSeconds($ledger->started_at) : null,
        ], [
            'severity' => 'warning',
            'criticality' => $ledger->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => ($ledger->domain ?: 'system') . '.gc',
            'source' => static::class,
            'operation_ledger_id' => $ledger->id,
            'subject_type' => $ledger->subject_type,
            'subject_id' => $ledger->subject_id,
            'actor_id' => $ledger->actor_id,
        ]);
    }
}
