<?php

namespace App\Console\Commands;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\OperationLedger;
use App\Models\OperationQuarantine;
use App\Models\OperationQuarantineAudit;
use App\Services\Reliability\ReliabilityPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off backfill for production deployments that ran with the pre-Q1 enum
 * (`operation_quarantines.domain` was `enum('finance','warehouse','crm','general')`).
 *
 * Any HRM, Planning, or HeavyIO quarantine row inserted before the
 * 2026_05_14_130000_extend_operation_quarantine_enums migration would have had
 * its `domain` silently truncated to `''` by MySQL (Laravel's `strict=false` config
 * disables STRICT_TRANS_TABLES). After the enum is extended, those rows still hold ``
 * — this command recovers the lost attribution by reading `operation_ledgers.domain`
 * (a VARCHAR that was always stored correctly) and copying it onto the overlay row.
 *
 * Idempotent: a second run finds 0 candidates because the WHERE clause matches only
 * rows that still hold `''`. Safe to run multiple times.
 *
 * Run order in production:
 *   1. php artisan migrate                                  (applies 2026_05_14_130000)
 *   2. php artisan reliability:backfill-quarantine-domains  (recovers historical rows)
 */
class BackfillQuarantineDomainsCommand extends Command
{
    protected $signature = 'reliability:backfill-quarantine-domains
        {--dry-run : Report what would change without writing}
        {--limit=1000 : Maximum rows to process}';

    protected $description = 'Recover empty operation_quarantines.domain values from linked operation_ledgers.domain (one-off post-Q1 backfill).';

    private const KNOWN_DOMAINS = ['finance', 'warehouse', 'crm', 'hrm', 'planning', 'heavy_io', 'general'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, (int) $this->option('limit'));

        $candidates = OperationQuarantine::query()
            ->where('domain', '')
            ->orderBy('quarantined_at')
            ->limit($limit)
            ->get();

        $report = [
            'dry_run' => $dryRun,
            'scanned' => $candidates->count(),
            'recovered' => 0,
            'unrecoverable_no_ledger' => 0,
            'unrecoverable_invalid_domain' => 0,
            'per_domain' => [],
        ];

        foreach ($candidates as $quarantine) {
            $recovered = $this->recoverOne($quarantine, $dryRun, $report);
            if ($recovered !== null) {
                $report['per_domain'][$recovered] = ($report['per_domain'][$recovered] ?? 0) + 1;
            }
        }

        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $report
     */
    private function recoverOne(OperationQuarantine $quarantine, bool $dryRun, array &$report): ?string
    {
        if (!$quarantine->operation_ledger_id) {
            $report['unrecoverable_no_ledger']++;
            return null;
        }

        $ledger = OperationLedger::find($quarantine->operation_ledger_id);
        if (!$ledger) {
            $report['unrecoverable_no_ledger']++;
            return null;
        }

        $domain = strtolower(trim((string) $ledger->domain));
        if (!in_array($domain, self::KNOWN_DOMAINS, true)) {
            $report['unrecoverable_invalid_domain']++;
            return null;
        }

        if ($dryRun) {
            $report['recovered']++;
            return $domain;
        }

        DB::transaction(function () use ($quarantine, $domain): void {
            $quarantine->forceFill(['domain' => $domain])->save();

            OperationQuarantineAudit::create([
                'operation_quarantine_id' => $quarantine->id,
                'operation_ledger_id' => $quarantine->operation_ledger_id,
                'source_record_id' => $quarantine->source_record_id,
                'domain' => $domain,
                'action' => ReliabilityPolicy::QUARANTINE_ACTION_JUDGE_DECISION,
                'actor_type' => 'system',
                'actor_id' => null,
                'details' => "Domain attribution backfilled from operation_ledgers.domain after pre-Q1 enum truncation. Restored value: {$domain}.",
                'metadata' => [
                    'backfill_command' => 'reliability:backfill-quarantine-domains',
                    'recovered_domain' => $domain,
                    'ledger_id' => $quarantine->operation_ledger_id,
                ],
                'occurred_at' => now(),
            ]);
        });

        $report['recovered']++;
        return $domain;
    }
}
