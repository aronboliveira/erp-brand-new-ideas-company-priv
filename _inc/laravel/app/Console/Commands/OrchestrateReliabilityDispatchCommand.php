<?php

namespace App\Console\Commands;

use App\Services\Reliability\DispatchOrchestrationService;
use Illuminate\Console\Command;

class OrchestrateReliabilityDispatchCommand extends Command
{
    protected $signature = 'reliability:orchestrate-dispatch
        {--domain=* : Optional domain filter: finance, hrm, warehouse, crm, planning, heavy_io}
        {--limit=50 : Maximum pending outbox rows to process per domain}
        {--compensation-limit=25 : Maximum compensating ledgers to process}
        {--skip-compensation : Do not execute compensation after outbox drains}
        {--stop-on-failure : Stop later domain drains after the first failed/dead-letter report}
        {--fail-on-attention : Return failure when any drain/dead-letter/compensation failure is reported}';

    protected $description = 'Orchestrate all monolith-local reliability outbox dispatchers and compensation execution.';

    public function handle(DispatchOrchestrationService $orchestrator): int
    {
        $domains = $this->option('domain');
        $report = $orchestrator->run([
            'domains' => is_array($domains) ? $domains : [],
            'limit' => (int) $this->option('limit'),
            'compensation_limit' => (int) $this->option('compensation-limit'),
            'include_compensation' => !(bool) $this->option('skip-compensation'),
            'stop_on_failure' => (bool) $this->option('stop-on-failure'),
        ]);

        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return (bool) $this->option('fail-on-attention') && ($report['status'] ?? null) === 'completed_with_attention'
            ? self::FAILURE
            : self::SUCCESS;
    }
}
