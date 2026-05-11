<?php

namespace App\Console\Commands;

use App\Services\Reliability\CrmOutboxDispatcher;
use Illuminate\Console\Command;

class DispatchCrmOutboxCommand extends Command
{
    protected $signature = 'reliability:dispatch-crm-outbox
        {--limit=50 : Maximum pending CRM outbox rows to process}
        {--message-key= : Dispatch one exact outbox message key}';

    protected $description = 'Dispatch monolith-local CRM outbox signals after commit.';

    public function handle(CrmOutboxDispatcher $dispatcher): int
    {
        $messageKey = (string) ($this->option('message-key') ?? '');
        $report = $messageKey !== ''
            ? $dispatcher->dispatchByMessageKey($messageKey)
            : $dispatcher->dispatchPending((int) $this->option('limit'));

        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return (($report['failed'] ?? 0) > 0 || ($report['dead_letter'] ?? 0) > 0 || ($report['status'] ?? null) === 'dead_letter')
            ? self::FAILURE
            : self::SUCCESS;
    }
}
