<?php

namespace App\Console\Commands;

use App\Services\Reliability\QuarantineRetentionSweepService;
use Illuminate\Console\Command;

class SweepExpiredQuarantinesCommand extends Command
{
    protected $signature = 'reliability:sweep-expired-quarantines
        {--limit=200 : Maximum quarantine rows to dismiss per run}';

    protected $description = 'Auto-dismiss quarantine overlay rows past their retention TTL.';

    public function handle(QuarantineRetentionSweepService $sweeper): int
    {
        $report = $sweeper->sweep((int) $this->option('limit'));

        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return self::SUCCESS;
    }
}
