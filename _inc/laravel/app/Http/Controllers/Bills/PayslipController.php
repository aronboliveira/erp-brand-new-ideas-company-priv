<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Exports\PayslipExport;
use App\Models\{
    Allowance,
    Commission,
    Employee,
    Loan,
    OtherPayment,
    Overtime,
    Payslip,
    SaturationDeduction,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Crypt, DB, Log, Validator, View as ViewFacade};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

final class PayslipController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    private const REDIRECT_INDEX = VW::PY_SLP . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action, $cls, $func) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) {
                Log::warning('index: not logged in');
                return $ur;
            }
            $user = $ur;

            $c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) {
                Log::warning('index: guard failed');
                return $c;
            }

            try {
                $t = microtime(true);
                $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null)->get();
                $this->logExecutionTime($t, $action, 'fetchEmployees');

                return view(VW::PY_SLP . '.index', [
                    'employees' => $employees,
                    'month'     => [
                        '01' => 'JAN',
                        '02' => 'FEB',
                        '03' => 'MAR',
                        '04' => 'APR',
                        '05' => 'MAY',
                        '06' => 'JUN',
                        '07' => 'JUL',
                        '08' => 'AUG',
                        '09' => 'SEP',
                        '10' => 'OCT',
                        '11' => 'NOV',
                        '12' => 'DEC'
                    ],
                    'year'      => array_combine(
                        range((int)date('Y'), (int)date('Y') + 7),
                        range((int)date('Y'), (int)date('Y') + 7)
                    ),
                ]);
            } catch (\Throwable $e) {
                Log::error('index error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $cls . '::' . $func);
            }
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action, $cls, $func) {
            Log::debug($action . ' start', [
                'input' => $request->only('month', 'year'),
                UsersConstants::COL_USER_ID => $request->user()?->id ?? null
            ]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) {
                Log::warning('store: not logged in');
                return $ur;
            }
            $user = $ur;

            $c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) {
                Log::warning('store: guard failed');
                return $c;
            }

            $validator = Validator::make($request->all(), [
                'month' => 'required',
                'year'  => 'required'
            ]);
            if ($validator->fails()) {
                Log::warning('store: validation failed', ['errors' => $validator->errors()->first()]);
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $fmt     = sprintf('%04d-%02d', (int)$request->year, (int)$request->month);
            $creator = $user?->creatorId() ?? null;

            try {
                $t = microtime(true);
                DB::transaction(function () use ($fmt, $creator) {
                    $exists = Payslip::where('salary_month', $fmt)
                        ->where(DatabaseConstants::COL_TABLE_CREATOR, $creator)
                        ->pluck('employee_id');

                    $totalEmp = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creator)
                        ->where('company_doj', '<=', date("{$fmt}-t"))
                        ->count();

                    if ($totalEmp <= $exists->count()) {
                        throw new \Exception('already created');
                    }

                    if (Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creator)->where('salary', '<=', 0)->exists()) {
                        throw new \Exception('salary missing');
                    }

                    $newEmployees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creator)
                        ->where('company_doj', '<=', date("{$fmt}-t"))
                        ->whereNotIn('id', $exists)
                        ->get();

                    foreach ($newEmployees as $e) {
                        Payslip::create([
                            'employee_id'          => $e->id,
                            'net_payable'          => $e->getNetSalary(),
                            'salary_month'         => $fmt,
                            'status'               => 0,
                            'gross_salary'         => $e->salary ?? 0,
                            'allowance'            => Employee::allowance($e->id),
                            'commission'           => Employee::commission($e->id),
                            'loan'                 => Employee::loan($e->id),
                            'saturation_deduction' => Employee::saturationDeduction($e->id),
                            'other_payment'        => Employee::otherPayment($e->id),
                            'overtime'             => Employee::overtime($e->id),
                            DatabaseConstants::COL_TABLE_CREATOR => $creator,
                        ]);
                    }
                });
                $this->logExecutionTime($t, $action, 'createPayslips');

                Log::info('store: payslips created');
                return redirect()->route(VW::PY_SLP . '.index')
                    ->with('success', __('Payslip successfully created.'));
            } catch (\Exception $e) {
                $msg = match ($e->getMessage()) {
                    'already created' => __('Payslip already created.'),
                    'salary missing'  => __('Please set employee salary.'),
                    default           => $e->getMessage()
                };
                Log::error('store transaction error', ['error' => $e->getMessage()]);
                if (in_array($e->getMessage(), ['already created', 'salary missing'], true)) {
                    return redirect()->route(VW::PY_SLP . '.index')->with('error', $msg);
                }
                return defaultUndefinedException($request, $e, $cls . '::' . $func);
            }
        }, [
            'month' => $request->input('month'),
            'year'  => $request->input('year'),
            UsersConstants::COL_USER_ID => $request->user()?->id ?? null
        ]);
    }

    public function destroy(int|string $id): bool|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $action, $cls, $func) {
            Log::debug($action . ' start', ['id' => $id]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) {
                Log::warning('destroy: not logged in');
                return $ur;
            }

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            try {
                $t = microtime(true);
                Payslip::destroy($id);
                $this->logExecutionTime($t, $action, 'deletePayslip');

                Log::info('destroy: payslip deleted', ['id' => $id]);
                return true;
            } catch (\Throwable $e) {
                Log::error('destroy error', ['error' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $cls . '::' . $func);
            }
        }, ['payslip_id' => $id]);
    }

    public const SHW_EMP = 'showEmployee';
    public function showEmployee(int|string $id): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $action, $cls, $func) {
            Log::debug($action . ' start', ['id' => $id]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            try {
                $t = microtime(true);
                $payslip = Payslip::findOrFail($id);
                $this->logExecutionTime($t, $action, 'loadPayslip');

                return view(VW::PY_SLP . '.show', compact('payslip'));
            } catch (\Throwable $e) {
                Log::error('showEmployee error', ['error' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $cls . '::' . $func);
            }
        }, ['payslip_id' => $id]);
    }

    public const SRC_JSN = 'searchJson';
    public function searchJson(Request $request): array|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action, $cls, $func) {
            Log::debug($action . ' start', [
                'fmt' => $request->datePicker ?? null,
                UsersConstants::COL_USER_ID => $request->user()?->id ?? null
            ]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            $fmt = $request->datePicker ?? '';
            $t = microtime(true);
            $slips = Payslip::where('salary_month', $fmt)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null)
                ->get();
            $this->logExecutionTime($t, $action, 'queryPayslips');

            $data = [];
            $t2 = microtime(true);
            foreach ($slips as $p) {
                $e = $p->employee;
                if (strtolower($user[UsersConstants::COL_TP] ?? '') === 'employee' && ($user->id ?? null) !== ($e->user_id ?? null)) {
                    continue;
                }
                $data[] = [
                    'id'           => $e->id ?? null,
                    'code'         => strtolower($user[UsersConstants::COL_TP] ?? '') === 'employee'
                        ? ($e->name ?? '')
                        : ($user?->employeeIdFormat($e->employee_id ?? null) ?? ''),
                    'name'         => $e->name ?? '',
                    'type'         => $e->payslipType?->name ?? '',
                    'gross_salary' => $user?->priceFormat($p->gross_salary ?? 0) ?? '-',
                    'net_payable'   => $user?->priceFormat($p->net_payable ?? 0) ?? '-',
                    'status'       => ($p->status ?? 0) ? 'Paid' : 'Unpaid',
                    'url'          => route('employee.show', Crypt::encryptString($e->id ?? 0)), // ! ALERT
                ];
            }
            $this->logExecutionTime($t2, $action, 'buildResponse');

            Log::debug('searchJson: returning', ['count' => count($data)]);
            return $data;
        }, ['fmt' => $request->datePicker ?? null]);
    }

    public const PAY_SLR = 'paySalary';
    public function paySalary(int|string $id, string $date): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $date, $action, $cls, $func) {
            Log::debug($action . ' start', [
                UsersConstants::COL_EMP_ID => $id,
                'month' => $date
            ]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            try {
                $t = microtime(true);
                $p = Payslip::where([
                    ['employee_id', $id],
                    [DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null],
                    ['salary_month', $date]
                ])->first();

                if (!$p) {
                    Log::warning('paySalary: not found');
                    return redirect()->route(VW::PY_SLP . '.index')->with('error', __('Payslip payment failed.'));
                }

                $p->status = 1;
                $p->save();
                $this->logExecutionTime($t, $action, 'updatePayslipStatus');

                Log::info('paySalary: updated', ['payslip_id' => $p->id ?? null]);

                return redirect()->route(VW::PY_SLP . '.index')
                    ->with('success', __('Payslip payment successfully.'));
            } catch (\Throwable $e) {
                Log::error('paySalary error', ['error' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, $cls . '::' . $func);
            }
        }, [UsersConstants::COL_EMP_ID => $id, 'month' => $date]);
    }

    public const BLK_PAY_CRT = 'bulkPayCreate';
    public function bulkPayCreate(string $date): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($date, $action, $cls, $func) {
            Log::debug($action . ' start', ['date' => $date]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            $t = microtime(true);
            $all = Payslip::where('salary_month', $date)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null)
                ->get();
            $this->logExecutionTime($t, $action, 'fetchAll');

            $unpaid = $all->where('status', 0);
            Log::debug('bulkPayCreate: counts', ['all' => $all->count(), 'unpaid' => $unpaid->count()]);

            return ViewFacade::make(VW::PY_SLP . '.bulkcreate', [
                'Employees'       => $all,
                'unpaidEmployees' => $unpaid,
                'date'            => $date
            ]);
        }, ['date' => $date]);
    }

    public const BLK_PAY = 'bulkPayment';
    public function bulkPayment(Request $request, string $date): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $date, $action, $cls, $func) {
            Log::debug($action . ' start', ['date' => $date]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            try {
                $t = microtime(true);
                DB::transaction(function () use ($date, $user) {
                    Payslip::where([
                        ['salary_month', $date],
                        [DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null],
                        ['status', 0]
                    ])->update(['status' => 1]);
                });
                $this->logExecutionTime($t, $action, 'bulkUpdate');

                Log::info('bulkPayment: completed');

                return redirect()->route(VW::PY_SLP . '.index')
                    ->with('success', __('Payslip bulk payment successfully.'));
            } catch (\Throwable $e) {
                Log::error('bulkPayment error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['date' => $date]);
    }

    public const EMP_PAY_SLP = 'employeePayslip';
    public function employeePayslip(): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($action, $cls, $func) {
            Log::debug($action . ' start');

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            $t = microtime(true);
            $e = Employee::where('user_id', $user?->id ?? null)->first();
            $slips = $e ? Payslip::where('employee_id', $e->id)->get() : collect();
            $this->logExecutionTime($t, $action, 'fetchEmployeeSlips');

            if (!$e) return back()->with('error', __('Employee not found.'));

            Log::debug('employeePayslip: count', ['count' => $slips->count()]);

            return ViewFacade::make(VW::PY_SLP . '.employeepayslip', ['payslip' => $slips]);
        });
    }

    public function pdf(int|string $id, string $month): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $month, $action, $cls, $func) {
            Log::debug($action . ' start', ['employee_id' => $id, 'month' => $month]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            $t = microtime(true);
            $p = Payslip::where([
                ['employee_id', $id],
                ['salary_month', $month],
                [DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null]
            ])->firstOrFail();
            $detail = Utility::employeePayslipDetail($id, $month);
            $this->logExecutionTime($t, $action, 'loadPayslipAndDetail');

            Log::info('pdf: prepared', ['payslip_id' => $p->id ?? null]);

            return ViewFacade::make(VW::PY_SLP . '.pdf', [
                'payslip'       => $p,
                'employee'      => Employee::find($id),
                'payslipDetail' => $detail
            ]);
        }, ['employee_id' => $id, 'month' => $month]);
    }

    public function send(int|string $id, string $month): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $month, $action, $cls, $func) {
            Log::debug($action . ' start', ['employee_id' => $id, 'month' => $month]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            $set = Utility::settings();
            if (!(($set['payslip_sent'] ?? 0) == 1)) {
                Log::info('send: disabled by settings');
                return back()->with('success', __('Payslip successfully sent.'));
            }

            $t = microtime(true);
            $p = Payslip::where([
                ['employee_id', $id],
                ['salary_month', $month],
                [DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null]
            ])->firstOrFail();
            $e = Employee::find($p->employee_id);
            $p->name  = $e->name ?? '';
            $p->email = $e->email ?? '';
            $pId      = Crypt::encryptString((string)$p->id);
            $p->url   = route(VW::PY_SLP . '.payslipPdf', [$pId, $p->salary_month]);
            Utility::sendEmailTemplate('payslip_sent', [$e->id => $e->email], [
                'employee_name'        => $e->name,
                'employee_email'       => $e->email,
                'payslip_name'         => $p->name,
                'payslip_salary_month' => $p->salary_month,
                'payslip_url'          => $p->url,
            ]);
            $this->logExecutionTime($t, $action, 'prepareAndSendMail');

            Log::info('send: email dispatched', ['payslip_id' => $p->id ?? null]);

            return back()->with('success', __('Payslip successfully sent.'));
        }, ['employee_id' => $id, 'month' => $month]);
    }

    public const PAY_SLP_PDF = 'payslipPdf';
    public function payslipPdf(int|string $id, string $month): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $month, $action, $cls, $func) {
            Log::debug($action . ' start', ['encrypted_id' => $id, 'month' => $month]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            $t = microtime(true);
            $pid = Crypt::decryptString((string)$id);
            $p = Payslip::whereKey($pid)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId() ?? null)
                ->firstOrFail();
            $detail = Utility::employeePayslipDetail($p->employee_id, $month);
            $this->logExecutionTime($t, $action, 'loadPayslipPdf');

            Log::info('payslipPdf: loaded', ['payslip_id' => $p->id ?? null]);

            return ViewFacade::make(VW::PY_SLP . '.payslipPdf', [
                'payslip'       => $p,
                'employee'      => Employee::find($p->employee_id),
                'payslipDetail' => $detail
            ]);
        }, ['month' => $month]);
    }

    public const EDT_EMP = 'editEmployee';
    public function editEmployee(int|string $id): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $action, $cls, $func) {
            Log::debug($action . ' start', ['payslip_id' => $id]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;

            $c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            return ViewFacade::make(VW::PY_SLP . '.salaryEdit', [
                'payslip' => Payslip::findOrFail($id)
            ]);
        }, ['payslip_id' => $id]);
    }

    public const UPD_EMP = 'updateEmployee';
    public function updateEmployee(Request $request, int|string $id): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $id, $action, $cls, $func) {
            Log::debug($action . ' start', [
                'payslip_id' => $id,
                'input'      => $request->only(
                    'allowance',
                    'commission',
                    'loan',
                    'saturation_deductions',
                    'other_payment',
                    'rate',
                    'hours'
                )
            ]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;

            $c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            try {
                $t = microtime(true);
                DB::transaction(function () use ($request) {
                    foreach (['allowance', 'commission', 'loan', 'saturation_deductions', 'other_payment'] as $f) {
                        $clsMap = [
                            'allowance'             => Allowance::class,
                            'commission'            => Commission::class,
                            'loan'                  => Loan::class,
                            'saturation_deductions' => SaturationDeduction::class,
                            'other_payment'         => OtherPayment::class
                        ];
                        foreach (($request->$f ?? []) as $k => $v) {
                            $idKey = "{$f}_id";
                            $modelId = $request->$idKey[$k] ?? null;
                            $clsMap[$f]::find($modelId)?->update(['amount' => $v]);
                        }
                    }

                    foreach (($request->rate ?? []) as $k => $v) {
                        $rateId = $request->rate_id[$k] ?? null;
                        $hours  = $request->hours[$k] ?? null;
                        Overtime::find($rateId)?->update(['rate' => $v, 'hours' => $hours]);
                    }

                    $p = Payslip::findOrFail($request->payslip_id);

                    foreach (['allowance', 'commission', 'loan', 'saturation_deduction', 'other_payment', 'overtime'] as $f) {
                        $p->$f = Employee::{$f}($p->employee_id);
                    }

                    $p->net_payable = Employee::find($p->employee_id)?->getNetSalary() ?? 0;
                    $p->save();
                });
                $this->logExecutionTime($t, $action, 'updateEmployeeTransaction');

                Log::info('updateEmployee: saved changes', ['payslip_id' => $id]);

                return redirect()->route(VW::PY_SLP . '.index')
                    ->with('success', __('Employee payroll successfully updated.'));
            } catch (\Throwable $e) {
                Log::error('updateEmployee error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['payslip_id' => $id]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action, $cls, $func) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);

            $ur = self::_checkLogin();
            if ($ur instanceof RedirectResponse) return $ur;

            $c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX);
            if ($c !== true) return $c;

            $fileName = 'payslip_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            Log::info('export: downloading', ['file' => $fileName]);

            return Excel::download(new PayslipExport($request), $fileName);
        });
    }
}
