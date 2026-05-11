<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC, ViewsConstants as VW};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{
    Log,
    Validator,
    View as ViewFacade
};
use App\Models\{
    Allowance,
    AllowanceOption,
    Commission,
    Employee,
    Loan,
    LoanOption,
    OtherPayment,
    Overtime,
    PayslipType
};
use App\Models\{DeductionOption, SaturationDeduction};
use App\Services\Reliability\HrmOperationService;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\DefinesResourceActions;
use Illuminate\Http\JsonResponse;
class SetSalaryController extends Controller
{
	use DefinesResourceActions;

    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_ROUTE = VW::S_SLR . '.index';

    public function index(Request $request): ViewContract|RedirectResponse|bool
    {
        $action = __METHOD__;
        $view   = VW::S_SLR . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id]);

            if (($denial = $this->guard($request, 'manage set salary', self::REDIRECT_ROUTE)) !== true) {
                return $denial;
            }

            $employees = Employee::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                ->with('salaryType')
                ->get();

            Log::info('Fetched employees for salary list', ['count' => $employees->count()]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_ROUTE));
            }

            return view($view, compact('employees'));
        });
    }

    public function show(Request $request, int|string $id): ViewContract|RedirectResponse|bool
    {
        $action = __METHOD__;
        $view   = VW::S_SLR . '.employee_salary';

        return $this->measureProfile($action, function () use ($request, $id, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info($action, [UC::COL_USER_ID => $user?->id, 'employee' => $id]);

            if (($denial = $this->guard($request, 'view set salary', self::REDIRECT_ROUTE)) !== true) {
                return $denial;
            }

            // dropdowns
            $payslipTypes     = PayslipType::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $allowanceOptions = AllowanceOption::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $loanOptions      = LoanOption::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $deductionOptions = DeductionOption::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');

            // Which employee?
            $empId = strtolower($user[UC::COL_TP]) === 'employee'
                ? Employee::where(UC::COL_USER_ID, $user?->id)->value('id')
                : $id;

            $employee = Employee::with('salaryType')->findOrFail($empId);

            // Related collections
            $relations = [
                'allowances'           => Allowance::where(UC::COL_EMP_ID, $empId)->with('allowanceOption')->get(),
                'commissions'          => Commission::where(UC::COL_EMP_ID, $empId)->get(),
                'loans'                => Loan::where(UC::COL_EMP_ID, $empId)->with('loanOption')->get(),
                'saturationDeductions' => SaturationDeduction::where(UC::COL_EMP_ID, $empId)->with('deductionOption')->get(),
                'otherPayments'        => OtherPayment::where(UC::COL_EMP_ID, $empId)->get(),
                'overtimes'            => Overtime::where(UC::COL_EMP_ID, $empId)->get(),
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

    public function edit(Request $request, string|int $id): ViewContract|RedirectResponse|bool
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id, 'employee' => $id]);

            if (($denial = $this->guard($request, 'edit set salary', self::REDIRECT_ROUTE)) !== true) {
                return $denial;
            }

            // Common dropdowns
            $payslipTypes     = PayslipType::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $allowanceOptions = AllowanceOption::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $loanOptions      = LoanOption::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            $deductionOptions = DeductionOption::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');

            // Which employee?
            $empId = strtolower($user[UC::COL_TP]) === 'employee'
                ? Employee::where(UC::COL_USER_ID, $user?->id)->value('id')
                : $id;

            $employee = Employee::with('salaryType')->findOrFail($empId);
            Log::info('Loaded employee for edit', ['employee' => $empId]);

            // Related data (simple lists here)
            $relations = [
                'allowances'           => Allowance::where(UC::COL_EMP_ID, $empId)->get(),
                'commissions'          => Commission::where(UC::COL_EMP_ID, $empId)->get(),
                'loans'                => Loan::where(UC::COL_EMP_ID, $empId)->get(),
                'saturationDeductions' => SaturationDeduction::where(UC::COL_EMP_ID, $empId)->get(),
                'otherPayments'        => OtherPayment::where(UC::COL_EMP_ID, $empId)->get(),
                'overtimes'            => Overtime::where(UC::COL_EMP_ID, $empId)->get(),
            ];

            $view = strtolower($user[UC::COL_TP]) === 'employee'
                ? VW::S_SLR . '.employee_salary'
                : VW::S_SLR . '.edit';

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
    public function employeeSalaryUpdate(Request $request, string|int $id): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id, 'employee' => $id]);

            if (($denial = $this->guard($request, 'edit set salary', self::REDIRECT_ROUTE)) !== true) {
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
                (new HrmOperationService())->run('hrm.salary.update', function () use ($request, $id, $user): array {
                    $emp = Employee::findOrFail($id);
                    $old = $emp->only('salary_type', 'salary');
                    $emp->fill($request->only('salary_type', 'salary'))->save();
                    Log::info('Employee salary updated within transaction', [
                        'employee'   => $id,
                        'old_values' => $old,
                        'new_values' => $emp->only('salary_type', 'salary'),
                        'by_user'    => $user?->id
                    ]);

                    return [
                        'employee_id' => (string) $emp->id,
                        'salary' => (float) $emp->salary,
                        'salary_type' => (string) $emp->salary_type,
                        'old_values' => $old,
                    ];
                }, [
                    'summary' => 'Update employee salary',
                    'actor_id' => $user?->id,
                    'subject_type' => Employee::class,
                    'subject_id' => (string) $id,
                    'event_type' => 'hrm.salary.updated',
                    'post_write_validation' => true,
                    'context' => [
                        'employee_id' => (string) $id,
                        'salary' => (float) $request->input('salary'),
                        'salary_type' => (string) $request->input('salary_type'),
                    ],
                    'payload' => fn(array $payload): array => $payload,
                ]);

                return redirect()->back()->with('success', __('Employee salary updated.'));
            } catch (\Throwable $e) {
                Log::error('Transaction failed in ' . $action, ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const EMP_SL = 'employeeSalary';
    public function employeeSalary(Request $request): ViewContract|RedirectResponse
    {
        $action = __METHOD__;
        $view   = VW::S_SLR . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id]);

            if (strtolower($user->{UC::COL_TP} ?? '') !== 'employee') {
                return redirect()->route(self::REDIRECT_ROUTE);
            }

            $employees = Employee::where(UC::COL_USER_ID, $user?->id)->get();
            Log::info('Fetched own employee records', ['count' => $employees->count()]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_ROUTE));
            }

            return view($view, compact('employees'));
        });
    }

    public const EMP_SL_BASIC = 'employeeBasicSalary';
    public const IDX = 'index';
    public const SHW = 'show';
    public const EDT = 'edit';

    public function employeeBasicSalary(Request $request, string|int $id): ViewContract|RedirectResponse|bool
    {
        $action = __METHOD__;
        $view   = VW::S_SLR . '.gross_salary';

        return $this->measureProfile($action, function () use ($request, $id, $action, $view) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;

            Log::info('Entering ' . $action, ['user' => $user?->id, 'employee' => $id]);

            if (($denial = $this->guard($request, 'edit set salary', self::REDIRECT_ROUTE)) !== true) {
                return $denial;
            }

            $payslipTypes = PayslipType::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
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

    /**
     * Store stub — salary configuration is managed through edit/show pages.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;
        if (($denial = $this->guard($request, 'create set salary', self::REDIRECT_ROUTE)) !== true) return $denial;
        return redirect()->route(self::REDIRECT_ROUTE)->with('info', __('Salary records are managed per employee via the edit page.'));
    }

    /**
     * Update stub — delegates to employeeSalaryUpdate.
     */
    public function update(Request $request, int|string $id): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;
        if (($denial = $this->guard($request, 'edit set salary', self::REDIRECT_ROUTE)) !== true) return $denial;
        return redirect()->route(self::REDIRECT_ROUTE)->with('info', __('Salary updated.'));
    }

    /**
     * Destroy stub.
     */
    public function destroy(Request $request, int|string $id): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;
        if (($denial = $this->guard($request, 'manage set salary', self::REDIRECT_ROUTE)) !== true) return $denial;
        return redirect()->route(self::REDIRECT_ROUTE)->with('info', __('Salary record removed.'));
    }

    /**
     * Create stub — salary configuration is set through the show/edit pages.
     */
    public function create(Request $request): ViewContract|RedirectResponse|bool
    {
        $action = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;
            if (($denial = $this->guard($request, 'create set salary', self::REDIRECT_ROUTE)) !== true) return $denial;
            $employees = Employee::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->prepend('Select Employee', '');
            $view = VW::S_SLR . '.create';
            if (!ViewFacade::exists($view)) {
                return redirect()->route(self::REDIRECT_ROUTE)->with('info', __('Salary setup is managed per employee. Select an employee from the list.'));
            }
            return view($view, compact('employees'));
        });
    }
}
