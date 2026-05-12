<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationStep, OutboxMessage};
use Illuminate\Support\Facades\{Cache, DB, Schema};
use RuntimeException;
use Throwable;

class CompensationExecutorService
{
    private CriticalOperationService $operations;

    private OperationalEventService $events;

    private QuarantineService $quarantine;

    public function __construct(
        ?CriticalOperationService $operations = null,
        ?OperationalEventService $events = null,
        ?QuarantineService $quarantine = null,
    ) {
        $this->operations = $operations ?? new CriticalOperationService();
        $this->events = $events ?? new OperationalEventService();
        $this->quarantine = $quarantine ?? new QuarantineService(events: $this->events);
    }

    /**
     * @return array<string, mixed>
     */
    public function executePending(int $limit = 50, ?string $domain = null): array
    {
        $query = OperationLedger::query()
            ->where('status', ReliabilityPolicy::STATUS_COMPENSATING)
            ->whereHas('steps', function ($steps): void {
                $steps->where('step_key', 'like', 'compensation.required:%')
                    ->whereIn('status', [
                        ReliabilityPolicy::STEP_PENDING,
                        ReliabilityPolicy::STEP_RUNNING,
                        ReliabilityPolicy::STEP_FAILED,
                    ]);
            })
            ->orderBy('failed_at')
            ->orderBy('created_at');

        $domain = $domain ? $this->normalizeDomain($domain) : null;
        if ($domain) {
            $query->where('domain', $domain);
        }

        $reports = $query->limit(max(1, $limit))->get()
            ->map(fn(OperationLedger $ledger): array => $this->executeLedger($ledger));

        return [
            'processed' => $reports->count(),
            'compensated' => $reports->where('status', 'compensated')->count(),
            'failed' => $reports->where('status', 'failed')->count(),
            'skipped' => $reports->where('status', 'skipped')->count(),
            'reports' => $reports->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function executeLedger(OperationLedger $ledger): array
    {
        $ledger->refresh();
        $domain = $this->normalizeDomain((string) $ledger->domain);

        if ($ledger->status !== ReliabilityPolicy::STATUS_COMPENSATING) {
            return $this->skipped($ledger, $domain, 'not_compensating');
        }

        if ($this->quarantine->hasBlockingQuarantineForLedger($ledger)) {
            $this->events->record($domain . '.compensation.blocked', 'Compensation blocked by unresolved quarantine', [
                'operation_key' => $ledger->operation_key,
            ], $this->eventOptions($ledger, null, $domain, 'critical'));

            return $this->skipped($ledger, $domain, 'quarantined_operation');
        }

        $requiredStep = $this->requiredStep($ledger);
        if (!$requiredStep) {
            return $this->skipped($ledger, $domain, 'missing_required_step');
        }

        $message = $this->messageForRequiredStep($requiredStep, $ledger);
        $stepKey = 'compensation.execute:' . ($message?->id ?? $requiredStep->id);
        $executionStep = $this->operations->recordStep($ledger, $stepKey, $this->stepName($domain), [
            'step_type' => 'cleanup',
            'sequence' => 920,
            'status' => ReliabilityPolicy::STEP_RUNNING,
            'payload' => [
                'required_step_id' => $requiredStep->id,
                'outbox_message_id' => $message?->id,
                'message_key' => $message?->message_key,
                'domain' => $domain,
            ],
            'started_at' => now(),
        ]);

        try {
            $result = DB::transaction(function () use ($ledger, $requiredStep, $message, $executionStep, $domain): array {
                if (!$message) {
                    throw new RuntimeException('Cannot execute compensation without the dead-letter outbox message.');
                }

                $this->assertMessageIsCompensable($ledger, $message);
                $result = $this->domainResult($ledger, $message, $domain);
                $result['cache_key'] = $this->cacheResult($ledger, $message, $domain, $result);

                $requiredStep->forceFill([
                    'status' => ReliabilityPolicy::STEP_COMPENSATED,
                    'result' => [
                        'executed_by' => static::class,
                        'outbox_message_id' => $message->id,
                    ],
                    'finished_at' => now(),
                ])->save();

                $executionStep?->forceFill([
                    'status' => ReliabilityPolicy::STEP_COMPENSATED,
                    'result' => $result,
                    'error_message' => null,
                    'finished_at' => now(),
                ])->save();

                $ledger->forceFill([
                    'status' => ReliabilityPolicy::STATUS_COMPENSATED,
                    'result' => array_merge($ledger->result ?? [], ['compensation' => $result]),
                    'error_message' => null,
                    'closed_at' => now(),
                ])->save();

                return $result;
            });

            $this->events->record($domain . '.compensation.completed', ucfirst($domain) . ' compensation completed', $result, $this->eventOptions($ledger, $message, $domain));

            return [
                'status' => 'compensated',
                'ledger_id' => $ledger->id,
                'operation_key' => $ledger->operation_key,
                'domain' => $domain,
                'message_key' => $message?->message_key,
                'result' => $result,
            ];
        } catch (Throwable $throwable) {
            $this->operations->failStep($executionStep, $throwable->getMessage());
            $ledger->forceFill([
                'status' => ReliabilityPolicy::STATUS_COMPENSATING,
                'error_message' => $throwable->getMessage(),
                'failed_at' => now(),
            ])->save();

            $this->events->record($domain . '.compensation.failed', ucfirst($domain) . ' compensation failed', [
                'operation_key' => $ledger->operation_key,
                'message_key' => $message?->message_key,
                'message' => $throwable->getMessage(),
            ], $this->eventOptions($ledger, $message, $domain, 'error'));

            return [
                'status' => 'failed',
                'ledger_id' => $ledger->id,
                'operation_key' => $ledger->operation_key,
                'domain' => $domain,
                'message_key' => $message?->message_key,
                'error' => $throwable->getMessage(),
            ];
        }
    }

    private function requiredStep(OperationLedger $ledger): ?OperationStep
    {
        return OperationStep::where('operation_ledger_id', $ledger->id)
            ->where('step_key', 'like', 'compensation.required:%')
            ->orderByDesc('created_at')
            ->first();
    }

    private function messageForRequiredStep(OperationStep $step, OperationLedger $ledger): ?OutboxMessage
    {
        $payload = is_array($step->payload) ? $step->payload : [];
        $messageId = $payload['outbox_message_id'] ?? null;

        if (!$messageId && str_contains($step->step_key, ':')) {
            $messageId = substr($step->step_key, strrpos($step->step_key, ':') + 1);
        }

        if (!$messageId) {
            return null;
        }

        return OutboxMessage::where('operation_ledger_id', $ledger->id)
            ->where('id', (string) $messageId)
            ->first();
    }

    private function assertMessageIsCompensable(OperationLedger $ledger, OutboxMessage $message): void
    {
        if ($message->operation_ledger_id !== $ledger->id) {
            throw new RuntimeException('Dead-letter message does not belong to the compensating ledger.');
        }

        if ($message->status !== ReliabilityPolicy::OUTBOX_DEAD_LETTER) {
            throw new RuntimeException('Compensation requires a dead-letter outbox message.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function domainResult(OperationLedger $ledger, OutboxMessage $message, string $domain): array
    {
        $payload = is_array($message->payload) ? $message->payload : [];
        $eventType = $message->event_type;

        return [
            'domain' => $domain,
            'operation_key' => $ledger->operation_key,
            'message_key' => $message->message_key,
            'event_type' => $eventType,
            'remediation_mode' => 'monolith_local_compensation',
            'checks' => $this->domainChecks($domain, $payload, $eventType),
            'actions' => $this->domainActions($domain, $eventType, $payload),
            'operator_notes' => $this->operatorNotes($domain, $eventType),
            'completed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function domainChecks(string $domain, array $payload, string $eventType): array
    {
        return match ($domain) {
            'finance' => $this->financeChecks($payload, $eventType),
            'warehouse' => $this->warehouseChecks($payload, $eventType),
            'hrm' => $this->hrmChecks($payload, $eventType),
            'crm' => $this->crmChecks($payload, $eventType),
            'planning' => $this->planningChecks($payload, $eventType),
            'heavy_io' => $this->heavyIoChecks($payload),
            default => ['payload_keys' => array_keys($payload), 'event_type' => $eventType],
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
     * @return array<string, mixed>
     */
    private function heavyIoChecks(array $payload): array
    {
        return [
            'payload_keys' => array_values(array_unique(array_keys($payload))),
            'row_count' => $payload['row_count'] ?? $payload['processed_rows'] ?? $payload['rows'] ?? null,
            'artifact' => $payload['artifact'] ?? $payload['file'] ?? $payload['path'] ?? null,
            'result_status' => $payload['status'] ?? $payload['result_status'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, string>
     */
    private function domainActions(string $domain, string $eventType, array $payload): array
    {
        $actions = ['dead_letter_acknowledged'];

        if ($domain === 'finance') {
            if (str_contains($eventType, 'deleted')) {
                $actions[] = 'ledger_reversal_review_registered';
            }
            if (str_contains($eventType, 'payment') || str_contains($eventType, 'revenue') || str_contains($eventType, 'expense') || str_contains($eventType, 'bank_transfer')) {
                $actions[] = 'cash_reconciliation_registered';
            }
            if (str_contains($eventType, 'journal')) {
                $actions[] = 'journal_balance_reconciliation_registered';
            }
            $actions[] = 'finance_reporting_projection_flagged';
        } elseif ($domain === 'warehouse') {
            $actions[] = 'stock_reconciliation_registered';
            if (str_contains($eventType, 'transfer')) {
                $actions[] = 'transfer_projection_rebuild_registered';
            }
            if (str_contains($eventType, 'product') || str_contains($eventType, 'stock') || str_contains($eventType, 'import')) {
                $actions[] = 'catalog_and_valuation_refresh_registered';
            }
        } elseif ($domain === 'hrm') {
            $actions[] = 'employee_record_projection_rebuild_registered';
            if (str_contains($eventType, 'salary') || str_contains($eventType, 'payroll') || str_contains($eventType, 'leave')) {
                $actions[] = 'payroll_recalculation_registered';
            }
            if (str_contains($eventType, 'termination') || str_contains($eventType, 'employee.deleted')) {
                $actions[] = 'access_reconciliation_registered';
            }
        } elseif ($domain === 'crm') {
            $actions[] = 'crm_projection_rebuild_registered';
            if (str_contains($eventType, 'permission') || str_contains($eventType, 'user') || str_contains($eventType, 'client')) {
                $actions[] = 'crm_access_reconciliation_registered';
            }
            if (str_contains($eventType, 'customer') || str_contains($eventType, 'vendor') || str_contains($eventType, 'deal') || str_contains($eventType, 'lead')) {
                $actions[] = 'relationship_pipeline_reconciliation_registered';
            }
        } elseif ($domain === 'planning') {
            $actions[] = 'planning_projection_rebuild_registered';
            if (str_contains($eventType, 'project.deleted')) {
                $actions[] = 'project_archive_and_access_cleanup_registered';
            }
            if (str_contains($eventType, 'timesheet')) {
                $actions[] = 'timesheet_rollup_payroll_finance_reconciliation_registered';
            }
        } elseif ($domain === 'heavy_io') {
            $actions[] = 'integration_health_recheck_registered';
            if (array_key_exists('artifact', $payload) || array_key_exists('file', $payload) || array_key_exists('path', $payload)) {
                $actions[] = 'artifact_audit_registered';
            }
            if (str_contains($eventType, 'webhook')) {
                $actions[] = 'webhook_dead_letter_audit_registered';
            }
        }

        return array_values(array_unique($actions));
    }

    private function operatorNotes(string $domain, string $eventType): string
    {
        return match ($domain) {
            'finance' => str_contains($eventType, 'deleted')
                ? 'Finance compensation registered a reversal review and reconciliation checkpoint; provider callbacks still require the external callback cluster.'
                : 'Finance compensation registered local reconciliation checkpoints without reversing source rows blindly.',
            'warehouse' => 'Warehouse compensation registered stock and projection reconciliation checkpoints without mutating inventory quantities blindly.',
            'hrm' => 'HRM compensation registered payroll/access reconciliation checkpoints; employee status remains governed by HRM validators and manual review when quarantined.',
            'crm' => 'CRM compensation registered projection/access/relationship reconciliation checkpoints without changing customer, vendor, client, lead, or deal rows blindly.',
            'planning' => 'Planning compensation registered project/timesheet/progress reconciliation checkpoints without changing finalized planning rows blindly.',
            'heavy_io' => 'Heavy I/O compensation registered integration health and artifact/dead-letter audit checkpoints.',
            default => 'Compensation registered a local remediation checkpoint.',
        };
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

    private function cacheResult(OperationLedger $ledger, OutboxMessage $message, string $domain, array $result): string
    {
        $cacheKey = 'reliability:compensation:' . $domain . ':' . $ledger->id;

        Cache::put($cacheKey, [
            'ledger_id' => $ledger->id,
            'operation_key' => $ledger->operation_key,
            'message_key' => $message->message_key,
            'event_type' => $message->event_type,
            'actions' => $result['actions'] ?? [],
            'completed_at' => now()->toIso8601String(),
        ], now()->addSeconds(7200));

        return $cacheKey;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));

        return match ($domain) {
            'finance', 'financial' => 'finance',
            'warehouse', 'stock', 'products', 'product' => 'warehouse',
            'hrm', 'hr', 'human_resources' => 'hrm',
            'crm' => 'crm',
            'planning', 'project', 'projects' => 'planning',
            'heavy-io', 'heavy_io', 'integrations', 'integration' => 'heavy_io',
            default => $domain ?: 'system',
        };
    }

    private function stepName(string $domain): string
    {
        return match ($domain) {
            'finance' => 'Execute finance compensation',
            'warehouse' => 'Execute warehouse compensation',
            'hrm' => 'Execute HRM compensation',
            'crm' => 'Execute CRM compensation',
            'planning' => 'Execute planning compensation',
            'heavy_io' => 'Execute heavy I/O compensation',
            default => 'Execute compensation',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function skipped(OperationLedger $ledger, string $domain, string $reason): array
    {
        return [
            'status' => 'skipped',
            'ledger_id' => $ledger->id,
            'operation_key' => $ledger->operation_key,
            'domain' => $domain,
            'reason' => $reason,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventOptions(OperationLedger $ledger, ?OutboxMessage $message, string $domain, string $severity = 'info'): array
    {
        return [
            'severity' => $severity,
            'criticality' => $ledger->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => $domain . '.compensation',
            'source' => static::class,
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message?->id,
            'subject_type' => $ledger->subject_type ?: $message?->aggregate_type,
            'subject_id' => $ledger->subject_id ?: $message?->aggregate_id,
            'actor_id' => $ledger->actor_id,
        ];
    }
}
