<?php

namespace App\Services\Reliability;

use App\Config\Constants\{
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Models\{Employee, Leave, PayslipType, Termination, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Schema};

class HrmPostWriteValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public function validate(string $eventType, array $payload, ?\App\Models\OperationLedger $ledger = null): PostWriteValidationResult
    {
        return match ($eventType) {
            'hrm.employee.created', 'hrm.employee.updated' => $this->validateEmployeePresent($eventType, $payload, $ledger),
            'hrm.employee.deleted' => $this->validateEmployeeDeleted($payload, $ledger),
            'hrm.salary.updated' => $this->validateSalaryUpdated($payload, $ledger),
            'hrm.leave.status_changed' => $this->validateLeaveStatusChanged($payload, $ledger),
            'hrm.termination.created', 'hrm.termination.updated' => $this->validateTerminationPresent($eventType, $payload, $ledger),
            'hrm.termination.deleted' => $this->validateTerminationDeleted($payload, $ledger),
            default => PostWriteValidationResult::pass(
                'hrm',
                (string) ($payload['source_table'] ?? 'hrm'),
                null,
                isset($payload['id']) ? (string) $payload['id'] : null,
                $payload,
                $this->originEvent($eventType, $payload, $ledger),
            ),
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateEmployeePresent(string $eventType, array $payload, ?\App\Models\OperationLedger $ledger): PostWriteValidationResult
    {
        $employeeId = $this->stringOrNull($payload['employee_id'] ?? $payload['id'] ?? null);
        $employee = $employeeId ? Employee::query()->find($employeeId) : null;
        $errors = [];

        if (!$employee) {
            $errors['employee'] = 'Employee was not persisted.';
        } else {
            $this->validateOptionalEmployeeUserLink($employee, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_EMPLOYEES,
            Employee::class,
            $employeeId,
            [
                'payload' => $payload,
                'employee' => $employee?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateEmployeeDeleted(array $payload, ?\App\Models\OperationLedger $ledger): PostWriteValidationResult
    {
        $employeeId = $this->stringOrNull($payload['employee_id'] ?? $payload['id'] ?? null);
        $employee = $employeeId ? Employee::query()->find($employeeId) : null;
        $errors = [];

        if ($employee) {
            $errors['employee_delete'] = 'Employee still exists after the delete operation.';
        }

        return $this->result(
            $errors,
            DC::TABLE_EMPLOYEES,
            Employee::class,
            $employeeId,
            [
                'payload' => $payload,
                'employee_exists_after_delete' => (bool) $employee,
            ],
            $this->originEvent('hrm.employee.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateSalaryUpdated(array $payload, ?\App\Models\OperationLedger $ledger): PostWriteValidationResult
    {
        $employeeId = $this->stringOrNull($payload['employee_id'] ?? $payload['id'] ?? null);
        $employee = $employeeId ? Employee::query()->find($employeeId) : null;
        $errors = [];

        if (!$employee) {
            $errors['employee'] = 'Employee referenced by the salary operation was not found.';
        } else {
            $salary = (float) ($employee->salary ?? 0);
            if ($salary < 0.0) {
                $errors['salary'] = 'Employee salary cannot be negative.';
            }

            $salaryType = $this->stringOrNull($employee->getAttribute(UC::COL_SLR_TP));
            if (!$salaryType) {
                $errors['salary_type'] = 'Employee salary_type must be populated after salary update.';
            } elseif (!PayslipType::query()->whereKey($salaryType)->exists()) {
                $errors['salary_type'] = 'Employee salary_type does not reference a payslip type.';
            }

            $this->validateOptionalEmployeeUserLink($employee, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_EMPLOYEES,
            Employee::class,
            $employeeId,
            [
                'payload' => $payload,
                'employee' => $employee?->getAttributes(),
            ],
            $this->originEvent('hrm.salary.updated', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateLeaveStatusChanged(array $payload, ?\App\Models\OperationLedger $ledger): PostWriteValidationResult
    {
        $leaveId = $this->stringOrNull($payload['leave_id'] ?? $payload['id'] ?? null);
        $leave = $leaveId ? Leave::query()->find($leaveId) : null;
        $errors = [];

        if (!$leave) {
            $errors['leave'] = 'Leave record was not found after status change.';
        } else {
            if (!Employee::query()->whereKey($leave->getAttribute(UC::COL_EMP_ID))->exists()) {
                $errors['leave_employee'] = 'Leave points to a missing employee.';
            }

            $status = strtolower(trim((string) $leave->getAttribute(PJC::COL_STATUS)));
            if ($status === '') {
                $errors['leave_status'] = 'Leave status cannot be empty after decision.';
            }

            if (!$this->validDateRange($leave->getAttribute(PJC::COL_S_DT), $leave->getAttribute(PJC::COL_E_DT))) {
                $errors['leave_dates'] = 'Leave start/end dates are not coherent after decision.';
            }

            $days = (int) ($leave->getAttribute(PJC::COL_TT_LV_DY) ?? 0);
            if ($days < 0) {
                $errors['leave_dates'] = 'Leave total days cannot be negative.';
            }
        }

        return $this->result(
            $errors,
            DC::TABLE_LV,
            Leave::class,
            $leaveId,
            [
                'payload' => $payload,
                'leave' => $leave?->getAttributes(),
            ],
            $this->originEvent('hrm.leave.status_changed', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateTerminationPresent(string $eventType, array $payload, ?\App\Models\OperationLedger $ledger): PostWriteValidationResult
    {
        $terminationId = $this->stringOrNull($payload['termination_id'] ?? $payload['id'] ?? null);
        $termination = $terminationId ? Termination::query()->find($terminationId) : null;
        $errors = [];

        if (!$termination) {
            $errors['termination'] = 'Termination record was not persisted.';
        } else {
            if (!Employee::query()->whereKey($termination->getAttribute(UC::COL_EMP_ID))->exists()) {
                $errors['termination_employee'] = 'Termination points to a missing employee.';
            }
            if (!$this->validDateRange(
                $termination->getAttribute(UC::COL_TERMINATION_NDT),
                $termination->getAttribute(UC::COL_TERMINATION_DT),
                allowSameDay: true,
            )) {
                $errors['termination_dates'] = 'Termination notice_date must not be after termination_date.';
            }
            if (!$this->stringOrNull($termination->getAttribute(UC::COL_TERMINATION_TP))) {
                $errors['termination_type'] = 'Termination type must be populated.';
            }
        }

        return $this->result(
            $errors,
            DC::TABLE_TERMINATIONS,
            Termination::class,
            $terminationId,
            [
                'payload' => $payload,
                'termination' => $termination?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateTerminationDeleted(array $payload, ?\App\Models\OperationLedger $ledger): PostWriteValidationResult
    {
        $terminationId = $this->stringOrNull($payload['termination_id'] ?? $payload['id'] ?? null);
        $termination = $terminationId ? Termination::query()->find($terminationId) : null;
        $errors = [];

        if ($termination) {
            $errors['termination_delete'] = 'Termination still exists after the delete operation.';
        }

        return $this->result(
            $errors,
            DC::TABLE_TERMINATIONS,
            Termination::class,
            $terminationId,
            [
                'payload' => $payload,
                'termination_exists_after_delete' => (bool) $termination,
            ],
            $this->originEvent('hrm.termination.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function validateOptionalEmployeeUserLink(Employee $employee, array &$errors): void
    {
        $userId = $this->stringOrNull($employee->getAttribute(UC::COL_USER_ID));
        if (!$userId) {
            return;
        }

        $user = User::query()->find($userId);
        if (!$user) {
            $errors['employee_user_link'] = 'Employee user_id points to a missing user.';
            return;
        }

        $userType = strtolower((string) ($user->getAttribute(UC::COL_TP) ?? ''));
        if ($userType !== '' && $userType !== 'employee') {
            $errors['employee_user_type'] = 'Linked user type is not employee.';
        }

        $employeeCreator = $this->stringOrNull($employee->getAttribute(DC::COL_TABLE_CREATOR));
        $userCreator = $this->stringOrNull($user->getAttribute(DC::COL_TABLE_CREATOR));
        if ($employeeCreator && $userCreator && $employeeCreator !== $userCreator) {
            $errors['employee_user_creator'] = 'Linked employee and user belong to different creators.';
        }

        $employeeEmail = $this->normalizeEmail($employee->getAttribute(UC::COL_EM));
        $userEmail = $this->normalizeEmail($user->getAttribute(UC::COL_EM));
        if ($employeeEmail && $userEmail && $employeeEmail !== $userEmail) {
            $errors['employee_user_email'] = 'Linked employee and user emails do not match.';
        }

        if ($this->employeeRoleIsKnown() && method_exists($user, 'hasRole')) {
            try {
                if (!$user->hasRole('Employee')) {
                    $errors['employee_user_role'] = 'Linked user does not hold the Employee role.';
                }
            } catch (\Throwable) {
                $errors['employee_user_role'] = 'Linked user role/RBAC state could not be verified.';
            }
        }
    }

    private function employeeRoleIsKnown(): bool
    {
        $rolesTable = (string) config('permission.table_names.roles', 'roles');

        return Schema::hasTable($rolesTable)
            && DB::table($rolesTable)->where('name', 'Employee')->exists();
    }

    private function validDateRange(mixed $start, mixed $end, bool $allowSameDay = false): bool
    {
        if (!$start || !$end) {
            return false;
        }

        try {
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            return $allowSameDay ? $startDate->lte($endDate) : $startDate->lt($endDate);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $snapshot
     * @param array<string, mixed> $originEvent
     */
    private function result(
        array $errors,
        string $sourceTable,
        string $sourceType,
        ?string $sourceRecordId,
        array $snapshot,
        array $originEvent,
    ): PostWriteValidationResult {
        if ($errors === []) {
            return PostWriteValidationResult::pass('hrm', $sourceTable, $sourceType, $sourceRecordId, $snapshot, $originEvent);
        }

        return PostWriteValidationResult::fail(
            'hrm',
            $sourceTable,
            $sourceType,
            $sourceRecordId,
            $this->criteriaForErrors($errors),
            $errors,
            $snapshot,
            $originEvent,
        );
    }

    /**
     * @param array<string, mixed> $errors
     * @return array<int, string>
     */
    private function criteriaForErrors(array $errors): array
    {
        $criteria = ['C1', 'C2'];

        if (array_key_exists('employee', $errors) || array_key_exists('termination', $errors) || array_key_exists('leave', $errors)) {
            $criteria[] = 'C3';
        }
        if (
            array_key_exists('salary', $errors)
            || array_key_exists('salary_type', $errors)
            || array_key_exists('leave_dates', $errors)
            || array_key_exists('termination_dates', $errors)
        ) {
            $criteria[] = 'C4';
        }
        if (
            array_key_exists('employee_user_link', $errors)
            || array_key_exists('employee_user_type', $errors)
            || array_key_exists('employee_user_role', $errors)
            || array_key_exists('employee_user_creator', $errors)
            || array_key_exists('employee_user_email', $errors)
        ) {
            $criteria[] = 'C7';
        }

        return array_values(array_unique($criteria));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function originEvent(string $eventType, array $payload, ?\App\Models\OperationLedger $ledger): array
    {
        return [
            'event_type' => $eventType,
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'payload' => $payload,
        ];
    }

    private function normalizeEmail(mixed $value): ?string
    {
        $email = $this->stringOrNull($value);

        return $email ? strtolower(trim($email)) : null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
