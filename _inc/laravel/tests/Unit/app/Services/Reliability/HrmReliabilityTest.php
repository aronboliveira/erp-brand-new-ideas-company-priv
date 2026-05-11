<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Exceptions\Reliability\{HrmPostWriteValidationFailedException, QuarantineRollbackRequiredException};
use App\Models\{CircuitBreakerCall, CircuitBreakerState, Employee, OperationLedger, OperationQuarantine, OutboxMessage, PayslipType, User};
use App\Services\Reliability\{
    HrmCompensationService,
    HrmOperationService,
    HrmOutboxDispatcher,
    HrmPostWriteValidator,
    HrmReliabilityAssessment,
    HrmReliabilityPolicy,
    PostWriteValidationResult,
    QuarantineRemediationJudge,
    QuarantineService,
    ReliabilityPolicy
};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use RuntimeException;
use Tests\TestCase;

#[CoversClass(HrmCompensationService::class)]
#[CoversClass(HrmOperationService::class)]
#[CoversClass(HrmOutboxDispatcher::class)]
#[CoversClass(HrmPostWriteValidator::class)]
#[CoversClass(HrmReliabilityAssessment::class)]
#[CoversClass(HrmReliabilityPolicy::class)]
#[CoversClass(PostWriteValidationResult::class)]
#[CoversClass(QuarantineRemediationJudge::class)]
#[CoversClass(QuarantineService::class)]
#[Group('services')]
#[Group('reliability')]
class HrmReliabilityTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function employee_without_linked_user_is_valid_hrm_state(): void
    {
        $employee = $this->employee();

        $validation = (new HrmPostWriteValidator())->validate('hrm.employee.updated', [
            'employee_id' => (string) $employee->id,
        ]);

        $this->assertTrue($validation->passed);
        $this->assertSame('hrm', $validation->domain);
    }

    #[Test]
    public function employee_user_mismatch_rolls_back_without_quarantine_when_instability_is_not_persistent(): void
    {
        $user = $this->user('company', 'linked-user-' . Str::uuid() . '@example.test');
        $employeeId = (string) Str::uuid();
        $operationKey = 'hrm-user-mismatch-' . Str::uuid();

        try {
            (new HrmOperationService())->run(
                'hrm.employee.update',
                fn(): array => [
                    'employee_id' => $this->employee([
                        'id' => $employeeId,
                        UC::COL_USER_ID => $user->id,
                        'email' => 'employee-' . Str::uuid() . '@example.test',
                    ], force: true)->id,
                ],
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Update employee identity link',
                    'subject_type' => Employee::class,
                    'subject_id' => $employeeId,
                    'event_type' => 'hrm.employee.updated',
                    'post_write_validation' => true,
                    'payload' => fn(array $payload): array => ['employee_id' => (string) $payload['employee_id']],
                ],
            );

            $this->fail('Persistent HRM identity mismatch should fail post-write validation.');
        } catch (HrmPostWriteValidationFailedException $exception) {
            $this->assertArrayHasKey('employee_user_type', $exception->validation()->validationErrors);
            $this->assertFalse($exception->assessment()->quarantineCandidate);
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $this->assertSame(ReliabilityPolicy::STATUS_FAILED, $ledger->status);
        $this->assertFalse(OperationQuarantine::where('operation_ledger_id', $ledger->id)->exists());
    }

    #[Test]
    public function persistent_employee_user_mismatch_routes_to_hrm_quarantine_manual_review(): void
    {
        $this->ensureEmployeeRoleKnown();
        $user = $this->user('employee', 'mismatched-role-' . Str::uuid() . '@example.test');
        $employeeId = (string) Str::uuid();
        $operationKey = 'hrm-quarantine-' . Str::uuid();

        for ($i = 0; $i < 4; $i++) {
            OperationLedger::create([
                'operation_key' => 'hrm-prior-failure-' . $i . '-' . Str::uuid(),
                'operation_type' => 'hrm.employee.update',
                'domain' => 'hrm',
                'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'subject_type' => Employee::class,
                'subject_id' => $employeeId,
                'summary' => 'Prior failed employee identity reconciliation attempt',
                'started_at' => now()->subMinutes(20),
                'failed_at' => now()->subMinutes(10),
                'expires_at' => now()->addDay(),
            ]);
        }

        try {
            (new HrmOperationService())->run(
                'hrm.employee.update',
                fn(): array => [
                    'employee_id' => $this->employee([
                        'id' => $employeeId,
                        UC::COL_USER_ID => $user->id,
                        'email' => 'different-' . Str::uuid() . '@example.test',
                    ], force: true)->id,
                ],
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Update employee identity link with persistent instability',
                    'subject_type' => Employee::class,
                    'subject_id' => $employeeId,
                    'event_type' => 'hrm.employee.updated',
                    'post_write_validation' => true,
                    'payload' => fn(array $payload): array => ['employee_id' => (string) $payload['employee_id']],
                ],
            );

            $this->fail('Persistent HRM identity mismatch should route to quarantine.');
        } catch (QuarantineRollbackRequiredException $exception) {
            $this->assertNotNull($exception->quarantine());
            $this->assertSame('hrm', $exception->quarantine()?->domain);
            $this->assertSame(ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW, $exception->quarantine()?->status);
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINES, [
            'operation_ledger_id' => $ledger->id,
            'source_table' => DC::TABLE_EMPLOYEES,
            'source_record_id' => $employeeId,
            'domain' => 'hrm',
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'event_type' => 'hrm.quarantine.manual_review',
            'severity' => 'critical',
        ]);
    }

    #[Test]
    public function salary_operation_creates_hrm_ledger_outbox_and_retry_metadata(): void
    {
        $salaryType = PayslipType::create(['name' => 'Monthly payroll']);
        $employee = $this->employee([
            UC::COL_SLR_TP => $salaryType->id,
            'salary' => 3500,
        ]);

        $result = (new HrmOperationService())->run(
            'hrm.salary.update',
            function () use ($employee, $salaryType): array {
                $employee->forceFill([
                    'salary' => 4250,
                    UC::COL_SLR_TP => $salaryType->id,
                ])->save();

                return [
                    'employee_id' => (string) $employee->id,
                    'salary' => 4250,
                    'salary_type' => (string) $salaryType->id,
                ];
            },
            [
                'summary' => 'Update employee salary',
                'subject_type' => Employee::class,
                'subject_id' => (string) $employee->id,
                'event_type' => 'hrm.salary.updated',
                'post_write_validation' => true,
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $ledger = $result->ledger();
        $outbox = $result->outboxMessage();

        $this->assertInstanceOf(OperationLedger::class, $ledger);
        $this->assertInstanceOf(OutboxMessage::class, $outbox);
        $this->assertSame('hrm.operations', $outbox->stream);
        $this->assertSame('payroll', data_get($outbox->metadata, 'hrm_reliability.cluster'));
        $this->assertSame(7, data_get($outbox->metadata, 'retry.max_attempts'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger?->id,
            'step_key' => 'hrm.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
    }

    #[Test]
    public function hrm_outbox_dispatcher_accepts_local_signals_and_closes_ledger(): void
    {
        [$ledger, $message] = $this->hrmOutbox('hrm.salary.updated');

        $report = (new HrmOutboxDispatcher())->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dispatched', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DISPATCHED, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_CLOSED, $ledger->status);
        $this->assertGreaterThanOrEqual(5, count($report['signals']));
        $this->assertTrue(CircuitBreakerState::where('breaker_key', 'hrm.outbox.hrm.salary.updated')->exists());
        $this->assertTrue(CircuitBreakerCall::where('breaker_key', 'hrm.outbox.hrm.salary.updated')
            ->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)
            ->exists());
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'hrm.outbox.dispatched',
        ]);
    }

    #[Test]
    public function hrm_outbox_dispatcher_dead_letters_and_marks_compensation_required(): void
    {
        [$ledger, $message] = $this->hrmOutbox('hrm.termination.deleted', ['max_attempts' => 1]);

        $report = (new HrmOutboxDispatcher(handlers: [
            'hrm.termination.deleted' => fn(): array => throw new RuntimeException('rbac reconciliation unavailable'),
        ]))->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dead_letter', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DEAD_LETTER, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_COMPENSATING, $ledger->status);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.required:' . $message->id,
            'status' => ReliabilityPolicy::STEP_PENDING,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'hrm.compensation.required',
            'severity' => 'error',
        ]);
    }

    private function user(string $type = 'employee', ?string $email = null): User
    {
        return User::create([
            UC::COL_NM => 'HRM User ' . Str::uuid(),
            UC::COL_EM => $email ?? 'hrm-user-' . Str::uuid() . '@example.test',
            UC::COL_PW => 'secret',
            UC::COL_TP => $type,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function employee(array $overrides = [], bool $force = false): Employee
    {
        $attributes = array_merge([
            'name' => 'HRM Employee ' . Str::uuid(),
            'email' => 'hrm-employee-' . Str::uuid() . '@example.test',
            'phone' => (string) random_int(10000000000, 99999999999),
            UC::COL_EMP_ID => (string) Str::uuid(),
            'salary' => 2500,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides);

        return $force ? Employee::forceCreate($attributes) : Employee::create($attributes);
    }

    private function ensureEmployeeRoleKnown(): void
    {
        $rolesTable = (string) config('permission.table_names.roles', 'roles');
        if (!DB::table($rolesTable)->where('name', 'Employee')->exists()) {
            DB::table($rolesTable)->insert([
                'id' => (string) Str::uuid(),
                'name' => 'Employee',
                'guard_name' => 'web',
                DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return array{0: OperationLedger, 1: OutboxMessage}
     */
    private function hrmOutbox(string $eventType, array $messageOverrides = []): array
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'hrm-test-' . Str::uuid(),
            'operation_type' => 'hrm.test.operation',
            'domain' => 'hrm',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create(array_merge([
            'message_key' => $eventType . ':' . Str::uuid(),
            'stream' => 'hrm.operations',
            'event_type' => $eventType,
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => Employee::class,
            'aggregate_id' => (string) Str::uuid(),
            'operation_ledger_id' => $ledger->id,
            'payload' => ['employee_id' => (string) Str::uuid()],
            'metadata' => [
                'retry' => ['eligible' => true, 'max_attempts' => 3],
                'circuit_breaker' => ['eligible' => true],
            ],
            'max_attempts' => 3,
            'available_at' => now(),
            'expires_at' => now()->addDay(),
        ], $messageOverrides));

        return [$ledger, $message];
    }
}
