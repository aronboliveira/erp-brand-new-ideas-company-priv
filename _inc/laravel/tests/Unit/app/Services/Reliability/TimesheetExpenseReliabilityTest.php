<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BanksConstants as BKC,
    BillsConstants as BLC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\EvaluationStatus;
use App\Models\{
    Bill,
    OperationLedger,
    OutboxMessage,
    Project,
    ProjectTask,
    Timesheet
};
use App\Services\Reliability\{
    FinanceOutboxDispatcher,
    FinancePostWriteValidator,
    PlanningOperationService,
    PlanningOutboxDispatcher,
    PlanningPostWriteValidator,
    PlanningReliabilityPolicy,
    ReliabilityPolicy
};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use Tests\TestCase;

#[CoversClass(FinanceOutboxDispatcher::class)]
#[CoversClass(FinancePostWriteValidator::class)]
#[CoversClass(PlanningOperationService::class)]
#[CoversClass(PlanningOutboxDispatcher::class)]
#[CoversClass(PlanningPostWriteValidator::class)]
#[CoversClass(PlanningReliabilityPolicy::class)]
#[Group('services')]
#[Group('reliability')]
class TimesheetExpenseReliabilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    #[Test]
    public function timesheet_approval_creates_planning_ledger_outbox_and_validation_step(): void
    {
        $project = $this->project();
        $task = $this->projectTask((string) $project->id);
        $timesheet = $this->timesheet((string) $project->id, (string) $task->id, [
            'status' => EvaluationStatus::Pending->value,
        ]);

        $result = (new PlanningOperationService())->run(
            'planning.timesheet.approval',
            function () use ($timesheet): array {
                $timesheet->forceFill([
                    'status' => EvaluationStatus::Accept->value,
                    PJC::COL_APV_BY => DC::DEFAULT_UUID,
                    PJC::COL_APV_AT => now(),
                    PJC::COL_REJ_BY => null,
                    PJC::COL_REJ_AT => null,
                ])->save();

                return [
                    'timesheet_id' => (string) $timesheet->id,
                    'project_id' => (string) $timesheet->project_id,
                    'project_task_id' => (string) $timesheet->project_task_id,
                    'expected_status' => EvaluationStatus::Accept->value,
                    'time_minutes' => 90,
                    'approval_action' => true,
                    'payroll_handoff' => true,
                    'finance_handoff' => true,
                ];
            },
            [
                'summary' => 'Approve timesheet',
                'subject_type' => Timesheet::class,
                'subject_id' => (string) $timesheet->id,
                'actor_id' => DC::DEFAULT_UUID,
                'event_type' => 'planning.timesheet.approved',
                'post_write_validation' => true,
                'force_post_write_validation' => true,
                'requires_approval' => true,
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $this->assertInstanceOf(OperationLedger::class, $result->ledger());
        $this->assertInstanceOf(OutboxMessage::class, $result->outboxMessage());
        $this->assertSame('planning.operations', $result->outboxMessage()?->stream);
        $this->assertSame(
            PlanningReliabilityPolicy::CLUSTER_TIMESHEET_APPROVAL,
            data_get($result->outboxMessage()?->metadata, 'planning_reliability.cluster'),
        );
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $result->ledger()?->id,
            'step_key' => 'planning.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
    }

    #[Test]
    public function timesheet_delete_validator_accepts_hard_deleted_row(): void
    {
        $project = $this->project();
        $task = $this->projectTask((string) $project->id);
        $timesheet = $this->timesheet((string) $project->id, (string) $task->id);
        $timesheetId = (string) $timesheet->id;

        $timesheet->delete();

        $validation = (new PlanningPostWriteValidator())->validate('planning.timesheet.deleted', [
            'timesheet_id' => $timesheetId,
            'project_id' => (string) $project->id,
            'project_task_id' => (string) $task->id,
            'irreversible_delete' => true,
        ]);

        $this->assertTrue($validation->passed, json_encode($validation->validationErrors));
        $this->assertSame(DC::TABLE_TMS, $validation->sourceTable);
    }

    #[Test]
    public function expense_validator_accepts_finalized_expense_bill_and_payment(): void
    {
        $accountId = $this->bankAccount();
        [$billId, $paymentId] = $this->expenseBillWithPayment($accountId, 4200.00);

        $validation = (new FinancePostWriteValidator())->validate('finance.expense.created', [
            'expense_id' => $billId,
            'bill_id' => $billId,
            'payment_id' => $paymentId,
            'amount' => 4200.00,
            'total_amount' => 4200.00,
            'account_id' => $accountId,
            'requires_approval' => true,
        ]);

        $this->assertTrue($validation->passed, json_encode($validation->validationErrors));
        $this->assertSame(DC::TABLE_BILLS, $validation->sourceTable);
    }

    #[Test]
    public function finance_outbox_dispatcher_emits_expense_context_signals(): void
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'expense-outbox-' . Str::uuid(),
            'operation_type' => 'finance.expense.create',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
        $message = OutboxMessage::create([
            'message_key' => 'finance.expense.created:' . Str::uuid(),
            'stream' => 'finance.ledger',
            'event_type' => 'finance.expense.created',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => Bill::class,
            'aggregate_id' => (string) Str::uuid(),
            'operation_ledger_id' => $ledger->id,
            'payload' => ['expense_id' => (string) Str::uuid(), 'amount' => 4200],
            'metadata' => [
                'retry' => ['eligible' => true, 'max_attempts' => 3],
                'circuit_breaker' => ['eligible' => true],
            ],
            'max_attempts' => 3,
            'available_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $report = (new FinanceOutboxDispatcher())->dispatchMessage($message);

        $this->assertSame('dispatched', $report['status']);
        $this->assertContains('expense-approval-reconciliation', array_column($report['signals'], 'name'));
        $this->assertContains('planning-expense-context', array_column($report['signals'], 'name'));
    }

    private function project(): Project
    {
        return Project::forceCreate([
            'id' => (string) Str::uuid(),
            PJC::COL_NM => 'Timesheet Project ' . Str::uuid(),
            PJC::COL_S_DT => now()->toDateString(),
            PJC::COL_E_DT => now()->addMonth()->toDateString(),
            PJC::COL_CLIENT_ID => (string) Str::uuid(),
            PJC::COL_BUDGET => 35000,
            PJC::COL_STATUS => 'in_progress',
            PJC::COL_E_HRS => 120,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    private function projectTask(string $projectId): ProjectTask
    {
        return ProjectTask::forceCreate([
            'id' => (string) Str::uuid(),
            PJC::COL_PJ_ID => $projectId,
            PJC::COL_NM => 'Timesheet Task ' . Str::uuid(),
            PJC::COL_E_HRS => 8,
            'priority' => 'high',
            PJC::COL_PGR => 30,
            'status' => 'in_progress',
            PJC::COL_IS_CP => false,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function timesheet(string $projectId, string $projectTaskId, array $overrides = []): Timesheet
    {
        return Timesheet::forceCreate(array_merge([
            'id' => (string) Str::uuid(),
            PJC::COL_PJ_ID => $projectId,
            PJC::COL_PJ_TSK_ID => $projectTaskId,
            AC::COL_TSK_ID => null,
            'date' => now()->toDateString(),
            'time' => '01:30',
            'status' => EvaluationStatus::Pending->value,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides));
    }

    private function bankAccount(): string
    {
        $accountId = (string) Str::uuid();
        DB::table(DC::TABLE_BANK_ACC)->insert([
            'id' => $accountId,
            BKC::COL_ACC_N => 'ACC-' . Str::uuid(),
            BKC::COL_HNM => 'Reliability Holder',
            BKC::COL_CT => '+15555550100',
            BKC::COL_NM => 'Reliability Bank',
            BKC::COL_ADR => 'Reliability Address',
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $accountId;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function expenseBillWithPayment(string $accountId, float $amount): array
    {
        $billId = (string) Str::uuid();
        $paymentId = (string) Str::uuid();
        DB::table(DC::TABLE_BILLS)->insert([
            'id' => $billId,
            BLC::COL_BL_ID => (string) Str::uuid(),
            BLC::COL_BL_DT => now()->toDateString(),
            PJC::COL_D_DATE => now()->toDateString(),
            'type' => 'expense',
            'status' => 4,
            'items' => json_encode([['price' => $amount, 'quantity' => 1, 'discount' => 0]]),
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table(DC::TABLE_BL_PRD)->insert([
            'id' => (string) Str::uuid(),
            BLC::COL_BL_ID => $billId,
            'quantity' => 1,
            'discount' => 0,
            'total' => $amount,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table(DC::TABLE_BL_PAY)->insert([
            'id' => $paymentId,
            BLC::COL_BL_ID => $billId,
            'date' => now()->toDateString(),
            'amount' => $amount,
            BLC::COL_BACC_ID => $accountId,
            BLC::COL_PAY_MTD => 0,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$billId, $paymentId];
    }
}
