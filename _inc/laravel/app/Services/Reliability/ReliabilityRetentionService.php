<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\OperationalEvent;
use Illuminate\Support\Facades\DB;

class ReliabilityRetentionService
{
    /**
     * @return array<string, int>
     */
    public function pruneExpired(): array
    {
        $now = now();

        $deleted = [
            'operational_events' => OperationalEvent::whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->whereIn('status', ['recorded', 'compressed', 'ignored'])
                ->delete(),
            'outbox_messages' => DB::table(DC::TABLE_OUTBOX_MESSAGES)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->whereIn('status', ['dispatched', 'dead_letter', 'cancelled'])
                ->delete(),
            'inbox_messages' => DB::table(DC::TABLE_INBOX_MESSAGES)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->whereIn('status', ['processed', 'failed', 'ignored'])
                ->delete(),
            'circuit_breaker_calls' => DB::table(DC::TABLE_CIRCUIT_BREAKER_CALLS)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->delete(),
            'circuit_breaker_states' => DB::table(DC::TABLE_CIRCUIT_BREAKER_STATES)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->whereIn('state', [ReliabilityPolicy::CIRCUIT_CLOSED, ReliabilityPolicy::CIRCUIT_DISABLED])
                ->delete(),
            'operation_quarantines' => DB::table(DC::TABLE_OPERATION_QUARANTINES)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->whereIn('status', [
                    ReliabilityPolicy::QUARANTINE_RECOVERED,
                    ReliabilityPolicy::QUARANTINE_ROLLED_BACK,
                    ReliabilityPolicy::QUARANTINE_DISMISSED,
                ])
                ->delete(),
            'operation_steps' => DB::table(DC::TABLE_OPERATION_STEPS)
                ->whereIn('operation_ledger_id', function ($query) use ($now): void {
                    $query->select('id')
                        ->from(DC::TABLE_OPERATION_LEDGERS)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<', $now)
                        ->whereIn('status', ['failed', 'closed', 'posted_to_ledger', 'committed', 'compensated']);
                })
                ->delete(),
            'operation_ledgers' => DB::table(DC::TABLE_OPERATION_LEDGERS)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->whereIn('status', ['failed', 'closed', 'posted_to_ledger', 'committed', 'compensated'])
                ->delete(),
        ];

        return array_map('intval', $deleted);
    }

    public function compressOperationalEventsForOperation(string $operationLedgerId): ?OperationalEvent
    {
        $events = OperationalEvent::where('operation_ledger_id', $operationLedgerId)
            ->where('status', 'recorded')
            ->orderBy('occurred_at')
            ->get();

        if ($events->count() <= 1) {
            return null;
        }

        $summary = [
            'count' => $events->count(),
            'event_types' => $events->pluck('event_type')->countBy()->all(),
            'severities' => $events->pluck('severity')->countBy()->all(),
            'first_occurred_at' => optional($events->first()->occurred_at)->toISOString(),
            'last_occurred_at' => optional($events->last()->occurred_at)->toISOString(),
        ];

        $compressed = OperationalEvent::create([
            'event_type' => 'operation.events.compressed',
            'severity' => $events->contains('severity', 'error') || $events->contains('severity', 'critical') ? 'warning' : 'info',
            'criticality' => ReliabilityPolicy::highestCriticality($events->pluck('criticality')->all()),
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => $events->first()->channel,
            'source' => static::class,
            'operation_ledger_id' => $operationLedgerId,
            'status' => 'recorded',
            'summary' => 'Compressed operational event timeline',
            'context' => $summary,
            'occurred_at' => now(),
            'expires_at' => now()->addDays(ReliabilityPolicy::retentionDays('operational_event', ReliabilityPolicy::CRITICALITY_MEDIUM)),
        ]);

        OperationalEvent::whereIn('id', $events->pluck('id')->all())
            ->update([
                'status' => 'compressed',
                'compressed_at' => now(),
            ]);

        return $compressed;
    }
}
