<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    UsersConstants,
    ViewsConstants
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{
    DB,
    Log,
    Validator,
    View as ViewFacade
};
use App\Models\{
    Allowance,
    AllowanceOption,
    Commission,
    DeductionOption,
    Employee,
    Loan,
    LoanOption,
    OtherPayment,
    Overtime,
    PayslipType,
    SaturationDeduction
};

class SetSalaryController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_ROUTE = ViewsConstants::S_SLR . '.index';

    public function index(Request $request): ViewContract|RedirectResponse
    {
        $action = __METHOD__;
        $view   = ViewsConstants::S_SLR . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id]);

            if ($denial = $this->guard($request, 'manage set salary', self::REDIRECT_ROUTE)) {
                return $denial;
            }

            $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->with('salary_type')
                ->get();

            Log::info('Fetched employees for salary list', ['count' => $employees->count()]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_ROUTE));
            }

            return view($view, compact('employees'));
        });
    }

    public function show(Request $request, int|string $id): ViewContract|RedirectResponse
    {
        $action = __METHOD__;
        $view   = ViewsConstants::S_SLR . '.employee_salary';

        return $this->measureProfile($action, function () use ($request, $id, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info($action, [UsersConstants::COL_USER_ID => $user?->id, 'employee' => $id]);

            if ($denial = $this->guard($request, 'view set salary', self::REDIRECT_ROUTE)) {
                return $denial;
            }

            // dropdowns
            $payslipTypes     = PayslipType::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $allowanceOptions = AllowanceOption::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $loanOptions      = LoanOption::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $deductionOptions = DeductionOption::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');

            // Which employee?
            $empId = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                : $id;

            $employee = Employee::with('salary_type')->findOrFail($empId);

            // Related collections
            $relations = [
                'allowances'           => Allowance::where(UsersConstants::COL_EMP_ID, $empId)->with('allowanceOption')->get(),
                'commissions'          => Commission::where(UsersConstants::COL_EMP_ID, $empId)->get(),
                'loans'                => Loan::where(UsersConstants::COL_EMP_ID, $empId)->with('loanOption')->get(),
                'saturationDeductions' => SaturationDeduction::where(UsersConstants::COL_EMP_ID, $empId)->with('deductionOption')->get(),
                'otherPayments'        => OtherPayment::where(UsersConstants::COL_EMP_ID, $empId)->get(),
                'overtimes'            => Overtime::where(UsersConstants::COL_EMP_ID, $empId)->get(),
            ];

            // Percentage totals
            foreach ($relations as $items) {
                foreach ($items as $item) {
                    if (($item->type ?? '') === 'percentage') {
                        $item->totalAllow = $item->amount * $employee->salary / 100;
                    }
                }
            }

            Log::info($action . ' loaded data', [
                'employee' => $empId,
                'counts'   => [
                    'allowances'           => $relations['allowances']->count(),
                    'commissions'          => $relations['commissions']->count(),
                    'loans'                => $relations['loans']->count(),
                    'saturationDeductions' => $relations['saturationDeductions']->count(),
                    'otherPayments'        => $relations['otherPayments']->count(),
                    'overtimes'            => $relations['overtimes']->count(),
                ],
            ]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_ROUTE));
            }

            return view($view, compact(
                'employee',
                'payslipTypes',
                'allowanceOptions',
                'loanOptions',
                'deductionOptions'
            ) + $relations);
        });
    }

    public function edit(Request $request, string|int $id): ViewContract|RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id, 'employee' => $id]);

            if ($denial = $this->guard($request, 'edit set salary', self::REDIRECT_ROUTE)) {
                return $denial;
            }

            // Common dropdowns
            $payslipTypes     = PayslipType::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $allowanceOptions = AllowanceOption::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $loanOptions      = LoanOption::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $deductionOptions = DeductionOption::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');

            // Which employee?
            $empId = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                : $id;

            $employee = Employee::with('salary_type')->findOrFail($empId);
            Log::info('Loaded employee for edit', ['employee' => $empId]);

            // Related data (simple lists here)
            $relations = [
                'allowances'           => Allowance::where(UsersConstants::COL_EMP_ID, $empId)->get(),
                'commissions'          => Commission::where(UsersConstants::COL_EMP_ID, $empId)->get(),
                'loans'                => Loan::where(UsersConstants::COL_EMP_ID, $empId)->get(),
                'saturationDeductions' => SaturationDeduction::where(UsersConstants::COL_EMP_ID, $empId)->get(),
                'otherPayments'        => OtherPayment::where(UsersConstants::COL_EMP_ID, $empId)->get(),
                'overtimes'            => Overtime::where(UsersConstants::COL_EMP_ID, $empId)->get(),
            ];

            $view = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                ? ViewsConstants::S_SLR . '.employee_salary'
                : ViewsConstants::S_SLR . '.edit';

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_ROUTE));
            }

            return view(
                $view,
                compact(
                    'employee',
                    'payslipTypes',
                    'allowanceOptions',
                    'loanOptions',
                    'deductionOptions'
                ) + $relations
            );
        });
    }

    public const EMP_SL_UPDATE = 'employeeSalaryUpdate';
    public function employeeUpdateSalary(Request $request, string|int $id): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id, 'employee' => $id]);

            if ($denial = $this->guard($request, 'edit set salary', self::REDIRECT_ROUTE)) {
                return $denial;
            }

            $v = Validator::make($request->all(), [
                'salary_type' => 'required|string',
                'salary'      => 'required|numeric|min:0'
            ]);

            if ($v->fails()) {
                Log::warning('Validation failed on updateSalary', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }

            try {
                DB::transaction(function () use ($request, $id, $user) {
                    $emp = Employee::findOrFail($id);
                    $old = $emp->only('salary_type', 'salary');
                    $emp->fill($request->only('salary_type', 'salary'))->save();
                    Log::info('Employee salary updated within transaction', [
                        'employee'   => $id,
                        'old_values' => $old,
                        'new_values' => $emp->only('salary_type', 'salary'),
                        'by_user'    => $user?->id
                    ]);
                }, 3);

                return redirect()->back()->with('success', __('Employee salary updated.'));
            } catch (\Throwable $e) {
                Log::error('Transaction failed in ' . $action, ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
            }
        });
    }

    public const EMP_SL = 'employeeSalary';
    public function employeeSalary(Request $request): ViewContract|RedirectResponse
    {
        $action = __METHOD__;
        $view   = ViewsConstants::S_SLR . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id]);

            if (strtolower($user->{UsersConstants::COL_TP} ?? '') !== 'employee') {
                return redirect()->route(self::REDIRECT_ROUTE);
            }

            $employees = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->get();
            Log::info('Fetched own employee records', ['count' => $employees->count()]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_ROUTE));
            }

            return view($view, compact('employees'));
        });
    }

    public const EMP_SL_BASIC = 'employeeBasicSalary';
    public function employeeBasicSalary(Request $request, string|int $id): ViewContract|RedirectResponse
    {
        $action = __METHOD__;
        $view   = ViewsConstants::S_SLR . '.basic_salary';

        return $this->measureProfile($action, function () use ($request, $id, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id, 'employee' => $id]);

            if ($denial = $this->guard($request, 'edit set salary', self::REDIRECT_ROUTE)) {
                return $denial;
            }

            $payslipTypes = PayslipType::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $employee     = Employee::findOrFail($id);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_ROUTE));
            }

            return view($view, compact('employee', 'payslipTypes'));
        });
    }

    private function requireLogin(Request $r)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            return $u;
        }
        return $u; // returns the authenticated user object
    }
}
