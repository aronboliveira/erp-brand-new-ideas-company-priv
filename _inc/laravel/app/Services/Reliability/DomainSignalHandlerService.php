<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{InboxMessage, OperationLedger, OutboxMessage};
use Illuminate\Support\Facades\{Cache, DB, Schema};
use Throwable;

class DomainSignalHandlerService
{
    private InboxService $inbox;

    private OperationalEventService $events;

    public function __construct(
        ?InboxService $inbox = null,
        ?OperationalEventService $events = null,
    ) {
        $this->inbox = $inbox ?? new InboxService();
        $this->events = $events ?? new OperationalEventService();
    }

    /**
     * @param array<string, mixed> $signal
     * @return array<string, mixed>
     */
    public function handle(OutboxMessage $message, ?OperationLedger $ledger, array $signal, string $domain): array
    {
        $signalName = (string) ($signal['name'] ?? 'unknown');
        $inboxKey = $this->inboxKey($message, $signalName, (string) ($signal['target'] ?? 'unknown'));
        $payload = $this->handlerPayload($message, $signal, $domain);
        $inbox = $this->inbox->recordReceived($inboxKey, $this->inboxEventType($message, $signalName), $payload, [
            'source' => $message->stream,
            'criticality' => $message->criticality,
            'operation_ledger_id' => $ledger?->id,
            'metadata' => [
                'domain' => $domain,
                'signal_name' => $signalName,
                'signal_channel' => $signal['channel'] ?? null,
                'signal_target' => $signal['target'] ?? null,
                'outbox_message_id' => $message->id,
            ],
        ]);

        if ($inbox->status === 'processed') {
            return $this->handledSignal($signal, [
                'status' => 'skipped',
                'reason' => 'already_processed',
                'inbox_message_id' => $inbox->id,
                'consumer' => $this->consumerName($domain, $signal),
            ]);
        }

        try {
            $result = $this->executeLocalHandler($message, $ledger, $signal, $domain, $inbox);
            $this->inbox->markProcessed($inbox);

            $this->events->record($domain . '.signal.handled', 'Domain signal handled', [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
                'signal_name' => $signalName,
                'handler' => $result,
            ], $this->eventOptions($message, $ledger, $domain, (string) ($signal['channel'] ?? $domain)));

            return $this->handledSignal($signal, array_merge($result, [
                'status' => 'processed',
                'inbox_message_id' => $inbox->id,
            ]));
        } catch (Throwable $throwable) {
            $this->inbox->markFailed($inbox, $throwable->getMessage());

            $this->events->record($domain . '.signal.failed', 'Domain signal handler failed', [
                'message_key' => $message->message_key,
                'event_type' => $message->event_type,
                'signal_name' => $signalName,
                'message' => $throwable->getMessage(),
            ], $this->eventOptions($message, $ledger, $domain, (string) ($signal['channel'] ?? $domain), 'error'));

            throw $throwable;
        }
    }

    /**
     * @param array<string, mixed> $signal
     * @return array<string, mixed>
     */
    private function executeLocalHandler(OutboxMessage $message, ?OperationLedger $ledger, array $signal, string $domain, InboxMessage $inbox): array
    {
        $payload = is_array($message->payload) ? $message->payload : [];
        $result = [
            'consumer' => $this->consumerName($domain, $signal),
            'mode' => 'local_inbox_handler',
            'checks' => $this->domainChecks($domain, $payload, $message->event_type, $signal),
            'side_effects' => [
                'inbox_message_key' => $inbox->message_key,
            ],
        ];

        if ($this->shouldCacheProjection($signal)) {
            $cacheKey = $this->cacheProjection($domain, $message, $ledger, $signal, $payload);
            $result['side_effects']['projection_cache_key'] = $cacheKey;
            $result['side_effects']['projection_cache_ttl_seconds'] = 7200;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $signal
     * @param array<string, mixed> $handler
     * @return array<string, mixed>
     */
    private function handledSignal(array $signal, array $handler): array
    {
        $target = (string) ($signal['target'] ?? 'domain_signal');
        $signal['target'] = str_ends_with($target, '_shell') ? substr($target, 0, -6) . '_handler' : $target;
        $signal['mode'] = 'local_inbox_handler';
        $signal['status'] = $handler['status'] === 'skipped' ? 'already_handled' : 'handled';
        $signal['handler'] = $handler;

        return $signal;
    }

    /**
     * @param array<string, mixed> $signal
     * @return array<string, mixed>
     */
    private function handlerPayload(OutboxMessage $message, array $signal, string $domain): array
    {
        return [
            'domain' => $domain,
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'stream' => $message->stream,
            'aggregate_type' => $message->aggregate_type,
            'aggregate_id' => $message->aggregate_id,
            'payload' => $message->payload ?? [],
            'signal' => $signal,
        ];
    }

    private function inboxEventType(OutboxMessage $message, string $signalName): string
    {
        return $message->event_type . '.signal.' . $this->slug($signalName);
    }

    private function inboxKey(OutboxMessage $message, string $signalName, string $target): string
    {
        return substr($message->message_key . ':signal:' . $this->slug($signalName . ':' . $target), 0, 191);
    }

    /**
     * @param array<string, mixed> $signal
     */
    private function consumerName(string $domain, array $signal): string
    {
        $channel = (string) ($signal['channel'] ?? $domain);
        $target = (string) ($signal['target'] ?? $signal['name'] ?? 'domain_signal');

        return $this->slug($channel . ':' . $target);
    }

    /**
     * @param array<string, mixed> $signal
     */
    private function shouldCacheProjection(array $signal): bool
    {
        $needle = strtolower(implode(' ', [
            (string) ($signal['name'] ?? ''),
            (string) ($signal['channel'] ?? ''),
            (string) ($signal['target'] ?? ''),
        ]));

        foreach (['projection', 'report', 'forecast', 'replica', 'archive', 'progress', 'availability', 'health', 'frontend'] as $token) {
            if (str_contains($needle, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $signal
     * @param array<string, mixed> $payload
     */
    private function cacheProjection(string $domain, OutboxMessage $message, ?OperationLedger $ledger, array $signal, array $payload): string
    {
        $subjectId = $message->aggregate_id
            ?: $ledger?->subject_id
            ?: ($payload['id'] ?? $payload['subject_id'] ?? $message->id);
        $subjectId = (string) $subjectId;
        $cacheKey = 'reliability:domain-signal:' . $domain . ':' . $this->slug((string) ($signal['name'] ?? 'signal')) . ':' . $this->slug($subjectId);

        Cache::put($cacheKey, [
            'domain' => $domain,
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'signal' => [
                'name' => $signal['name'] ?? null,
                'channel' => $signal['channel'] ?? null,
                'target' => $signal['target'] ?? null,
            ],
            'subject_type' => $message->aggregate_type ?: $ledger?->subject_type,
            'subject_id' => $subjectId,
            'handled_at' => now()->toIso8601String(),
            'payload_fingerprint' => hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''),
        ], now()->addSeconds(7200));

        return $cacheKey;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signal
     * @return array<string, mixed>
     */
    private function domainChecks(string $domain, array $payload, string $eventType, array $signal): array
    {
        return match ($domain) {
            'finance' => $this->financeChecks($payload, $eventType),
            'warehouse' => $this->warehouseChecks($payload, $eventType),
            'hrm' => $this->hrmChecks($payload, $eventType),
            'crm' => $this->crmChecks($payload, $eventType),
            'planning' => $this->planningChecks($payload, $eventType),
            'heavy_io' => $this->heavyIoChecks($payload, $signal),
            default => ['payload_keys' => array_keys($payload)],
        };
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function financeChecks(array $payload, string $eventType): array
    {
        $journalId = $this->firstString($payload, ['journal_entry_id', 'journal_id']);

        return array_filter([
            'bill' => $this->recordCheck(DC::TABLE_BILLS, $this->firstString($payload, ['bill_id', 'expense_id'])),
            'bank_account' => $this->recordCheck(DC::TABLE_BANK_ACC, $this->firstString($payload, ['account_id', 'from_account', 'to_account'])),
            'invoice_payment' => $this->recordCheck(DC::TABLE_INV_PAY, $this->firstString($payload, ['payment_id', 'invoice_payment_id'])),
            'bill_payment' => $this->recordCheck(DC::TABLE_BL_PAY, $this->firstString($payload, ['payment_id', 'bill_payment_id'])),
            'generic_payment' => $this->recordCheck(DC::TABLE_PAY, $this->firstString($payload, ['payment_id', 'generic_payment_id'])),
            'journal_entry' => $this->recordCheck(DC::TABLE_JOURNAL_ENTRIES, $journalId),
            'journal_balance' => $journalId ? $this->journalBalanceCheck($journalId) : null,
            'event_type' => ['status' => 'observed', 'value' => $eventType],
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function warehouseChecks(array $payload, string $eventType): array
    {
        $productId = $this->firstString($payload, ['product_id', 'item_id']);
        $warehouseId = $this->firstString($payload, ['warehouse_id', 'from_warehouse_id', 'to_warehouse_id']);

        return array_filter([
            'product' => $this->recordCheck(DC::TABLE_PROD_SERVS, $productId),
            'warehouse' => $this->recordCheck(DC::TABLE_WRH, $warehouseId),
            'warehouse_product_rows' => $productId ? $this->countCheck(DC::TABLE_WRH_PRD, 'product_id', $productId) : null,
            'event_type' => ['status' => 'observed', 'value' => $eventType],
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function hrmChecks(array $payload, string $eventType): array
    {
        return array_filter([
            'employee' => $this->recordCheck(DC::TABLE_EMPLOYEES, $this->firstString($payload, ['employee_id', 'id'])),
            'user' => $this->recordCheck(DC::TABLE_USERS, $this->firstString($payload, ['user_id'])),
            'leave' => $this->recordCheck(DC::TABLE_LV, $this->firstString($payload, ['leave_id'])),
            'termination' => $this->recordCheck(DC::TABLE_TERMINATIONS, $this->firstString($payload, ['termination_id'])),
            'event_type' => ['status' => 'observed', 'value' => $eventType],
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function crmChecks(array $payload, string $eventType): array
    {
        $dealId = $this->firstString($payload, ['deal_id']);

        return array_filter([
            'lead' => $this->recordCheck(DC::TABLE_LEADS, $this->firstString($payload, ['lead_id'])),
            'deal' => $this->recordCheck(DC::TABLE_DEALS, $dealId),
            'customer' => $this->recordCheck(DC::TABLE_CUSTOMERS, $this->firstString($payload, ['customer_id'])),
            'vendor' => $this->recordCheck(DC::TABLE_VENDORS, $this->firstString($payload, ['vendor_id'])),
            'client_user' => $this->recordCheck(DC::TABLE_USERS, $this->firstString($payload, ['client_id', 'user_id'])),
            'deal_users' => $dealId ? $this->countCheck(DC::TABLE_USR_DLS, 'deal_id', $dealId) : null,
            'deal_clients' => $dealId ? $this->countCheck(DC::TABLE_CLT_DLS, 'deal_id', $dealId) : null,
            'event_type' => ['status' => 'observed', 'value' => $eventType],
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function planningChecks(array $payload, string $eventType): array
    {
        $projectId = $this->firstString($payload, ['project_id']);

        return array_filter([
            'project' => $this->recordCheck(DC::TABLE_PROJECTS, $projectId),
            'milestone' => $this->recordCheck(DC::TABLE_MSS, $this->firstString($payload, ['milestone_id'])),
            'project_task' => $this->recordCheck(DC::TABLE_PROJ_TSKS, $this->firstString($payload, ['project_task_id', 'task_id'])),
            'timesheet' => $this->recordCheck(DC::TABLE_TMS, $this->firstString($payload, ['timesheet_id'])),
            'project_timesheets' => $projectId ? $this->countCheck(DC::TABLE_TMS, 'project_id', $projectId) : null,
            'event_type' => ['status' => 'observed', 'value' => $eventType],
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signal
     * @return array<string, mixed>
     */
    private function heavyIoChecks(array $payload, array $signal): array
    {
        return [
            'payload_keys' => array_values(array_unique(array_keys($payload))),
            'signal_target' => $signal['target'] ?? null,
            'row_count' => $payload['row_count'] ?? $payload['processed_rows'] ?? $payload['rows'] ?? null,
            'artifact' => $payload['artifact'] ?? $payload['file'] ?? $payload['path'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function recordCheck(string $table, ?string $id): ?array
    {
        if (!$id) {
            return null;
        }

        if (!Schema::hasTable($table)) {
            return ['status' => 'table_missing', 'table' => $table, 'id' => $id];
        }

        return [
            'status' => DB::table($table)->where('id', $id)->exists() ? 'present' : 'missing',
            'table' => $table,
            'id' => $id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function countCheck(string $table, string $column, string $value): array
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return ['status' => 'not_available', 'table' => $table, 'column' => $column];
        }

        return [
            'status' => 'counted',
            'table' => $table,
            'column' => $column,
            'value' => $value,
            'count' => DB::table($table)->where($column, $value)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function journalBalanceCheck(string $journalId): array
    {
        if (!Schema::hasTable(DC::TABLE_JRN_IT)) {
            return ['status' => 'table_missing', 'table' => DC::TABLE_JRN_IT];
        }

        $debit = (float) DB::table(DC::TABLE_JRN_IT)->where('journal', $journalId)->sum('debit');
        $credit = (float) DB::table(DC::TABLE_JRN_IT)->where('journal', $journalId)->sum('credit');

        return [
            'status' => abs($debit - $credit) < 0.000001 ? 'balanced' : 'imbalanced',
            'journal_id' => $journalId,
            'debit' => $debit,
            'credit' => $credit,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function slug(string $value): string
    {
        return trim(preg_replace('/[^a-zA-Z0-9_.:-]+/', '-', $value) ?: 'unknown', '-');
    }

    /**
     * @return array<string, mixed>
     */
    private function eventOptions(OutboxMessage $message, ?OperationLedger $ledger, string $domain, string $channel, string $severity = 'notice'): array
    {
        return [
            'severity' => $severity,
            'criticality' => $message->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => $channel,
            'source' => static::class,
            'operation_ledger_id' => $ledger?->id,
            'outbox_message_id' => $message->id,
            'subject_type' => $message->aggregate_type ?: $ledger?->subject_type,
            'subject_id' => $message->aggregate_id ?: $ledger?->subject_id,
            'actor_id' => $ledger?->actor_id,
            'metadata' => ['domain' => $domain],
        ];
    }
}
