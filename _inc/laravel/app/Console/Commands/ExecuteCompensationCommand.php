<?php

namespace App\Console\Commands;

use App\Models\OperationLedger;
use App\Services\Reliability\CompensationExecutorService;
use Illuminate\Console\Command;

class ExecuteCompensationCommand extends Command
{
    protected $signature = 'reliability:execute-compensation
        {--limit=50 : Maximum compensating ledgers to process}
        {--domain= : Optional domain filter: finance, warehouse, hrm, crm, planning, heavy_io}
        {--ledger-id= : Execute one exact operation ledger id}';

    protected $description = 'Execute domain compensation workflows for dead-lettered reliability operations.';

    public function handle(CompensationExecutorService $executor): int
    {
        $ledgerId = (string) ($this->option('ledger-id') ?? '');

        if ($ledgerId !== '') {
            $ledger = OperationLedger::find($ledgerId);
            if (!$ledger) {
                $this->error('Operation ledger not found.');

                return self::FAILURE;
            }

            $report = $executor->executeLedger($ledger);
        } else {
            $domain = (string) ($this->option('domain') ?? '');
            $report = $executor->executePending((int) $this->option('limit'), $domain !== '' ? $domain : null);
        }

        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return (($report['failed'] ?? 0) > 0 || ($report['status'] ?? null) === 'failed')
            ? self::FAILURE
            : self::SUCCESS;
    }
}
