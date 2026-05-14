<?php

namespace App\Console\Commands;

use App\Services\Reliability\ReliabilityLedgerSweepService;
use Illuminate\Console\Command;

class SweepOrphanedLedgersCommand extends Command
{
    protected $signature = 'reliability:sweep-orphaned-ledgers
        {--limit=200 : Maximum ledgers per criticality to flip per run}';

    protected $description = 'Flip ledger rows stuck in started past their criticality-tiered TTL to failed.';

    public function handle(ReliabilityLedgerSweepService $sweeper): int
    {
        $report = $sweeper->sweep((int) $this->option('limit'));

        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return self::SUCCESS;
    }
}
