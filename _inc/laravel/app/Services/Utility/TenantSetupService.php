<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    FormsConstants as FC,
    ProjectsConstants as PJC,
    UsersConstants as UC,
};
use App\Models\{
    Branch,
    BugStatus,
    Department,
    Designation,
    Employee,
    JobStage,
    Label,
    LeadStage,
    Payslip,
    PayslipType,
    Permission,
    Pipeline,
    Role,
    Source,
    Stage,
    TaskStage,
    Tax,
    User,
    Utility,
};
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{Artisan, DB, Log};

/**
 * TenantSetupService — extracted from Utility.php
 *
 * Bootstrap / seed data for new company tenants:
 * pipelines, stages, labels, job stages, sources,
 * employee records, payslip details, permissions, starting-numbers.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class TenantSetupService
{
    use \App\Traits\ChecksLogin;

    // ─────────────────────────────────────────────────────────
    //  Pipeline / Stage / Label seeding
    // ─────────────────────────────────────────────────────────

    public static function pipelineLeadDealStage(string|int $createdId): void
    {
        try {
            DB::transaction(function () use ($createdId) {
                $pipeline = Pipeline::create([
                    PJC::COL_PPL_NM         => 'Sales',
                    DC::COL_TABLE_CREATOR      => $createdId,
                ]);
                $stages = ['Draft', 'Sent', 'Open', 'Revised', 'Declined'];
                foreach ($stages as $order => $stageName) {
                    LeadStage::create([
                        PJC::COL_STG_NM         => $stageName,
                        PJC::COL_PPL_ID         => $pipeline->id,
                        AC::COL_OD           => $order,
                        DC::COL_TABLE_CREATOR      => $createdId,
                    ]);
                    Stage::create([
                        PJC::COL_STG_NM         => $stageName,
                        PJC::COL_PPL_ID         => $pipeline->id,
                        AC::COL_OD           => $order,
                        DC::COL_TABLE_CREATOR      => $createdId,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating " . __FUNCTION__ . " for [{$createdId}]: {$e->getMessage()}");
        }
    }

    public static function projectTaskStages(string $projectId, string $createdBy): void
    {
        $projectStages = ['To Do', 'In Progress', 'Review', 'Done'];
        try {
            DB::transaction(function () use ($projectStages, $projectId, $createdBy) {
                foreach ($projectStages as $order => $stageName) {
                    TaskStage::create([
                        AC::COL_PJ       => $projectId,
                        PJC::COL_STG_NM     => $stageName,
                        AC::COL_OD       => $order,
                        DC::COL_TABLE_CREATOR  => $createdBy,
                    ]);
                }
            });
        } catch (ModelNotFoundException $e) {
            Log::warning(
                __CLASS__ . '::' . __FUNCTION__
                    . " – project not found [{$projectId}]: {$e->getMessage()}",
                ['exception' => $e]
            );
        } catch (QueryException $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__
                    . " – database error creating TaskStages for project [{$projectId}]: {$e->getMessage()}",
                [
                    'sql'       => $e->getSql(),
                    'bindings'  => $e->getBindings(),
                    'exception' => $e,
                ]
            );
        } catch (\RuntimeException $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__
                    . " – runtime error for project [{$projectId}]: {$e->getMessage()}",
                ['exception' => $e]
            );
        } catch (\Throwable $e) {
            Log::critical(
                __CLASS__ . '::' . __FUNCTION__
                    . " – unexpected error for project [{$projectId}]: {$e->getMessage()}",
                ['exception' => $e]
            );
        }
    }

    public static function jobStage(string|int $creatorId): void
    {
        $stages = ['Applied', 'Phone Screen', 'Interview', 'Hired', 'Rejected'];
        try {
            DB::transaction(function () use ($stages, $creatorId) {
                foreach ($stages as $order => $title)
                    JobStage::create([
                        AC::COL_TT        => $title,
                        AC::COL_OD        => $order,
                        DC::COL_TABLE_CREATOR   => $creatorId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating JobStage entries for [{$creatorId}]: {$e->getMessage()}");
        }
    }

    public static function labels(string|int $creatorId): void
    {
        $pipeline = null;
        try {
            DB::transaction(function () use ($creatorId, &$pipeline) {
                $pipeline = Pipeline::create([
                    PJC::COL_PPL_NM      => 'Default Pipeline',
                    DC::COL_TABLE_CREATOR  => $creatorId,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating Pipeline: {$e->getMessage()}");
            return;
        }
        $labelData = [
            [PJC::COL_LB_NM => 'On Hold',  PJC::COL_CL => 'primary'],
            [PJC::COL_LB_NM => 'New',      PJC::COL_CL => PJC::STT_INF],
            [PJC::COL_LB_NM => 'Pending',  PJC::COL_CL => PJC::STT_WRN],
            [PJC::COL_LB_NM => 'Loss',     PJC::COL_CL => PJC::STT_DGR],
            [PJC::COL_LB_NM => 'Win',      PJC::COL_CL => 'success'],
        ];
        try {
            DB::transaction(function () use ($labelData, $creatorId, $pipeline) {
                foreach ($labelData as $item)
                    Label::create([
                        PJC::COL_LB_NM      => $item[PJC::COL_LB_NM],
                        PJC::COL_CL         => $item[PJC::COL_CL],
                        PJC::COL_PPL_ID     => $pipeline?->id,
                        DC::COL_TABLE_CREATOR  => $creatorId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating Label entries: {$e->getMessage()}");
        }
        $bugStatusData = ['Confirmed', 'Resolved', 'Unconfirmed', 'In Progress', 'Verified'];
        try {
            DB::transaction(function () use ($bugStatusData, $creatorId) {
                foreach ($bugStatusData as $order => $status)
                    BugStatus::create([
                        AC::COL_TT        => $status,
                        AC::COL_OD        => $order,
                        DC::COL_TABLE_CREATOR   => $creatorId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating BugStatus entries: {$e->getMessage()}");
        }
    }

    public static function sources(string|int $createdId): void
    {
        $sourceNames = ['Websites', 'Facebook', 'Naukari.com', 'Phone', 'LinkedIn'];
        try {
            DB::transaction(function () use ($sourceNames, $createdId) {
                foreach ($sourceNames as $name)
                    Source::create([
                        'name'                             => $name,
                        DC::COL_TABLE_CREATOR   => $createdId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating Source entries: {$e->getMessage()}");
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Employee setup
    // ─────────────────────────────────────────────────────────

    public static function employeeNumber($userId): string|int
    {
        if (is_string($userId)) return (string) Str::uuid();
        $employee = Employee::where(UC::COL_USER_ID, $userId)->latest()->first();
        return $employee?->id ?? (string) Str::uuid();
    }

    public static function employeeDetails(string|int $userId, string|int $createdBy): void
    {
        $user = User::find($userId);
        if (!$user) return;
        if (Employee::where(UC::COL_USER_ID, $user->id)->exists()) return;
        $faker = Faker::create('pt_BR');
        try {
            DB::transaction(function () use ($user, $createdBy, $faker) {
                $branchBudget   = $faker->randomFloat(2, 100_000, 1_000_000);
                $branchExpenses = $faker->randomFloat(2, 0, $branchBudget);
                $branchProfit   = $branchBudget - $branchExpenses;
                $branch = Branch::create([
                    CPC::COL_BRC_NM       => $faker->company . ' ' . Str::upper(Str::random(4)),
                    'address'             => $faker->streetAddress(),
                    'phone'               => $faker->phoneNumber(),
                    CPC::COL_FND          => (string) $createdBy,
                    CPC::COL_MNG          => $createdBy,
                    CPC::COL_ADM          => $createdBy,
                    'description'         => $faker->sentence(),
                    'departments'         => '[]',
                    'budget'              => $branchBudget,
                    'expenses'            => $branchExpenses,
                    'profit'              => $branchProfit,
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);

                $deptBudget   = $faker->randomFloat(2, 10_000, 200_000);
                $deptExpenses = $faker->randomFloat(2, 0, $deptBudget);
                $deptProfit   = $deptBudget - $deptExpenses;
                $department = Department::create([
                    CPC::COL_DEP_NM       => ucfirst($faker->unique()->word()),
                    CPC::COL_BRC_ID       => $branch->id,
                    'description'         => $faker->sentence(),
                    'phone'               => $faker->phoneNumber(),
                    'email'               => $faker->companyEmail(),
                    CPC::COL_MNG          => $createdBy,
                    'budget'              => $deptBudget,
                    'expenses'            => $deptExpenses,
                    'profit'              => $deptProfit,
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);

                $dsgBudget = $faker->randomFloat(2, 5_000, 100_000);
                $validFrom = $faker->dateTimeBetween('-1 year', 'now');
                $validTo   = $faker->dateTimeBetween('now', '+10 years');

                $designation = Designation::create([
                    UC::COL_DSG_NM        => $faker->jobTitle(),
                    CPC::COL_DEP_ID       => $department->id,
                    CPC::COL_EBDG         => $dsgBudget,
                    'description'         => $faker->sentence(),
                    'notes'               => $faker->sentence(),
                    CPC::COL_VFROM        => $validFrom->format('Y-m-d'),
                    CPC::COL_VTO          => $validTo->format('Y-m-d'),
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);

                $taxName = 'Tax ' . $faker->unique()->randomNumber(3);
                $taxRate = $faker->randomFloat(2, 0, 30);

                $tax = Tax::create([
                    BC::COL_TAX_NM       => $taxName,
                    BC::COL_TAX_RT       => $taxRate,
                    DC::COL_TABLE_CREATOR            => $createdBy,
                    DC::COL_TABLE_UPDATER            => $createdBy,
                ]);

                $payslipTypeName = $faker->randomElement(['Monthly', 'Hourly', 'Daily']);

                $code = (string) Str::uuid();
                while (PayslipType::where('code', $code)->exists())
                    $code = (string) Str::uuid();

                $minAmount = $faker->randomFloat(2, 0, 1_000);
                $maxAmount = $minAmount + $faker->randomFloat(2, 0, 10_000);

                $rolesApplies = json_encode(
                    $faker->randomElement([
                        ['all'],
                        ['employee', 'manager'],
                        ['contractor'],
                    ])
                );

                $payslipType = PayslipType::create([
                    'code'                   => $code,
                    BC::COL_PAY_SLP_NM => $payslipTypeName,
                    'description'            => $faker->sentence(),
                    BC::COL_MIN_AMT    => $minAmount,
                    BC::COL_MAX_AMT    => $maxAmount,
                    BC::COL_RL_APL     => $rolesApplies,
                    DC::COL_TABLE_CREATOR    => $createdBy,
                    DC::COL_TABLE_UPDATER    => $createdBy,
                ]);

                $phone = $faker->phoneNumber();
                while (Employee::where('phone', $phone)->exists()) {
                    $phone = $faker->phoneNumber();
                }

                $email = $user[UC::COL_EM] ?? $user->email ?? $faker->unique()->safeEmail();
                if (Employee::where('email', $email)->exists()) {
                    $email = $faker->unique()->safeEmail();
                }

                $accountName = $faker->bothify('ACC-####-' . substr((string) $user->id, 0, 4));
                while (Employee::where(UC::COL_ACC_NM, $accountName)->exists()) {
                    $accountName = $faker->bothify('ACC-####-' . substr((string) Str::uuid(), 0, 4));
                }

                $employeeNumber = Utility::employeeNumber($createdBy);

                $documents = json_encode([
                    'id_card'  => (string) Str::uuid(),
                    'contract' => (string) Str::uuid(),
                ]);

                $dob = $faker->optional()->dateTimeBetween('-60 years', '-18 years');

                Employee::create([
                    UC::COL_EMP_ID        => $employeeNumber,
                    UC::COL_USER_ID       => $user->id,
                    'name'                => $user[UC::COL_NM] ?? $user->name,
                    'phone'               => $phone,
                    'email'               => $email,
                    'gender'              => $faker->randomElement(['male', 'female', 'other']),
                    'notes'               => $faker->sentence(),
                    'password'            => $user[UC::COL_PW] ?? $user->password,
                    'address'             => $faker->address(),
                    'dob'                 => $dob ? $dob->format('Y-m-d') : null,
                    CPC::COL_BRC_ID       => $branch->id,
                    CPC::COL_BRC_LC       => $branch->address,
                    CPC::COL_DEP_ID       => $department->id,
                    UC::COL_DSG_ID        => $designation->id,
                    CPC::COL_DOJ          => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                    'documents'           => $documents,
                    UC::COL_ACC_HD        => $user[UC::COL_NM] ?? $user->name,
                    UC::COL_ACC_NM        => $accountName,
                    UC::COL_BANK_NM       => $faker->company() . ' Bank',
                    UC::COL_BANK_IC       => strtoupper($faker->bothify('BR##-####')),
                    UC::COL_TAX_ID        => $tax->id,
                    'salary'              => $faker->randomFloat(2, 30_000, 100_000),
                    UC::COL_SLR_TP        => $payslipType->id,
                    UC::COL_IA            => 1,
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__
                    . " failed creating Employee for user[{$userId}]: {$e->getMessage()}"
            );
        }
    }

    public static function employeeDetailsUpdate(string|int $userId, string|int $createdBy): void
    {
        $user = User::find($userId);
        if (!$user) return;
        try {
            DB::transaction(function () use ($user) {
                Employee::where(UC::COL_USER_ID, $user?->id)->update([
                    UC::COL_NM  => $user[UC::COL_NM],
                    UC::COL_EM => $user[UC::COL_EM],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating Employee for user[{$userId}]: {$e->getMessage()}");
        }
    }

    public static function employeePayslipDetail(string|int $employeeId, string $month): array
    {
        $payslips = Payslip::where('employee_id', $employeeId)
            ->where('salary_month', $month)
            ->get();
        $totalAllowance   = 0;
        $totalCommission  = 0;
        $totalOtherPayment = 0;
        $totalOvertime    = 0;
        $totalLoan        = 0;
        $totalDeduction   = 0;

        foreach ($payslips as $p) {
            $basic = $p->gross_salary;
            $allowances = json_decode($p->allowance, true) ?: [];
            foreach ($allowances as $a) {
                $amount = $a['type'] === 'percentage'
                    ? ($a['amount'] * $basic / 100)
                    : $a['amount'];
                $totalAllowance += $amount;
            }
            $commissions = json_decode($p->commission, true) ?: [];
            foreach ($commissions as $c) {
                $amount = $c['type'] === 'percentage'
                    ? ($c['amount'] * $basic / 100)
                    : $c['amount'];
                $totalCommission += $amount;
            }
            $otherPays = json_decode($p->other_payment, true) ?: [];
            foreach ($otherPays as $o) {
                $amount = $o['type'] === 'percentage'
                    ? ($o['amount'] * $basic / 100)
                    : $o['amount'];
                $totalOtherPayment += $amount;
            }
            $overtimes = json_decode($p->overtime, true) ?: [];
            foreach ($overtimes as $o) {
                $totalOvertime += ($o['number_of_days'] * $o['hours'] * $o['rate']);
            }
            $loans = json_decode($p->loan, true) ?: [];
            foreach ($loans as $l) {
                $amount = $l['type'] === 'percentage'
                    ? ($l['amount'] * $basic / 100)
                    : $l['amount'];
                $totalLoan += $amount;
            }
            $deductions = json_decode($p->saturation_deduction, true) ?: [];
            foreach ($deductions as $d) {
                $amount = $d['type'] === 'percentage'
                    ? ($d['amount'] * $basic / 100)
                    : $d['amount'];
                $totalDeduction += $amount;
            }
        }

        return [
            'earning'        => [
                'allowance'   => $payslips,
                'commission'  => $payslips,
                'otherPayment' => $payslips,
                'overTime'    => $payslips,
            ],
            'totalEarning'   => $totalAllowance + $totalCommission + $totalOtherPayment + $totalOvertime,
            'deduction'      => [
                'loan'      => $payslips,
                'deduction' => $payslips,
            ],
            'totalDeduction' => $totalLoan + $totalDeduction,
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  Permissions / starting numbers
    // ─────────────────────────────────────────────────────────

    private const ARR_PERMISSIONS = FC::PERMISSIONS;
    private const COMPANY_DATA_PERMISSIONS = FC::PERMISSIONS;

    public static function addNewData(): void
    {
        Artisan::call('cache:forget spatie.permission.cache');
        Artisan::call('cache:clear');
        $allPermissions     = self::ARR_PERMISSIONS;
        $companyDataPerms   = self::COMPANY_DATA_PERMISSIONS;
        $companyRole = Role::where('name', 'LIKE', 'company')->first();
        if (!$companyRole) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " company role not found");
            return;
        }
        $existingPermissions = $companyRole->getPermissionNames()->toArray();
        try {
            DB::transaction(function () use ($allPermissions, $companyDataPerms, $companyRole, $existingPermissions) {
                foreach ($allPermissions as $permName)
                    Permission::firstOrCreate(['name' => $permName]);
                foreach ($companyDataPerms as $permName) {
                    if (!in_array($permName, $existingPermissions, true)) {
                        $permission = Permission::findByName($permName);
                        $companyRole->givePermissionTo($permission);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed addNewData transaction: {$e->getMessage()}");
        }
    }

    public static function startingNumber(int $id, string $type): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creator = $user?->creatorId();
        $mapping = [
            'invoice'  => 'invoice_starting_number',
            'proposal' => 'proposal_starting_number',
            'bill'     => 'bill_starting_number',
        ];
        if (!isset($mapping[$type])) return 0;
        return DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, $creator)
            ->where('name', $mapping[$type])
            ->update(['value' => $id]);
    }
}
