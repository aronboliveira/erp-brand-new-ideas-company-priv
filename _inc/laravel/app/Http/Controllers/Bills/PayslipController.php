<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    UsersConstants
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
use Illuminate\Support\Facades\{Auth, Crypt, DB, Log, Validator};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

final class PayslipController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    private const REDIRECT_INDEX = 'payslip.index';

    public function index(Request $request): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'user_id' => $request->user()?->id
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning('index: not logged in');
            return $user;
        }
        if ($c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            Log::warning('index: guard failed');
            return $c;
        }
        try {
            $employees = Employee::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->get();
            return view('payslip.index', [
                'employees' => $employees,
                'month'     => [
                    '01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
                    '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG',
                    '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC'
                ],
                'year'      => array_combine(
                    range(date('Y'), date('Y') + 7),
                    range(date('Y'), date('Y') + 7)
                )
            ]);
        } catch (\Throwable $e) {
            Log::error('index error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'input' => $request->only('month', 'year'),
            'user_id' => $request->user()?->id
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning('store: not logged in');
            return $user;
        }
        if ($c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            Log::warning('store: guard failed');
            return $c;
        }
        $validator = Validator::make($request->all(), [
            'month' => 'required', 'year' => 'required'
        ]);
        if ($validator->fails()) {
            Log::warning('store: validation failed', [
                'errors' => $validator->errors()->first()
            ]);
            return redirect()->back()
                ->with('error', $validator->errors()->first());
        }
        $fmt    = "{$request->year}-{$request->month}";
        $creator = $user?->creatorId();
        try {
            DB::transaction(function () use ($fmt, $creator) {
                $exists  = Payslip::where(
                    'salary_month',
                    $fmt
                )->where(DatabaseConstants::TABLE_CREATOR, $creator)
                    ->pluck('employee_id');
                $totalEmp = Employee::where(
                    DatabaseConstants::TABLE_CREATOR,
                    $creator
                )->where('company_doj', '<=', date("$fmt-t"))
                    ->count();
                if ($totalEmp <= $exists->count()) {
                    throw new \Exception('already created');
                }
                if (Employee::where(DatabaseConstants::TABLE_CREATOR, $creator)
                    ->where('salary', '<=', 0)->exists()
                ) {
                    throw new \Exception('salary missing');
                }
                $newEmployees = Employee::where(
                    DatabaseConstants::TABLE_CREATOR,
                    $creator
                )->where('company_doj', '<=', date("$fmt-t"))
                    ->whereNotIn('id', $exists)->get();
                foreach ($newEmployees as $e) {
                    Payslip::create([
                        'employee_id'          => $e->id,
                        'net_payble'           => $e->getNetSalary(),
                        'salary_month'         => $fmt,
                        'status'               => 0,
                        'basic_salary'         => $e->salary ?? 0,
                        'allowance'            => Employee::allowance($e->id),
                        'commission'           => Employee::commission($e->id),
                        'loan'                 => Employee::loan($e->id),
                        'saturation_deduction' => Employee::saturationDeduction($e->id),
                        'other_payment'        => Employee::otherPayment($e->id),
                        'overtime'             => Employee::overtime($e->id),
                        DatabaseConstants::TABLE_CREATOR           => $creator,
                    ]);
                }
            });
            Log::info('store: payslips created');
            return redirect()->route('payslip.index')
                ->with('success', __('Payslip successfully created.'));
        } catch (\Exception $e) {
            $msg = match ($e->getMessage()) {
                'already created' => __('Payslip already created.'),
                'salary missing'  => __('Please set employee salary.'),
                default           => $e->getMessage()
            };
            Log::error('store transaction error', ['error' => $e->getMessage()]);
            if (in_array($e->getMessage(), ['already created', 'salary missing'])) {
                return redirect()->route('payslip.index')
                    ->with('error', $msg);
            }
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(int|string $id): bool|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $id]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning('destroy: not logged in');
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        try {
            Payslip::destroy($id);
            Log::info('destroy: payslip deleted', ['id' => $id]);
            return true;
        } catch (\Throwable $e) {
            Log::error('destroy error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                request(),
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function showEmployee(int|string $id): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $id]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        try {
            $payslip = Payslip::findOrFail($id);
            return view('payslip.show', compact('payslip'));
        } catch (\Throwable $e) {
            Log::error('showEmployee error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                request(),
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function searchJson(Request $request): array|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'fmt' => $request->datePicker,
            'user_id' => $request->user()?->id
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        $fmt = $request->datePicker;
        $slips = Payslip::where(
            'salary_month',
            $fmt
        )->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
        $data = [];
        foreach ($slips as $p) {
            $e = $p->employee;
            if (strtolower($user[UsersConstants::COL_TP]) === 'employee' && $user->id != $e->user_id) {
                continue;
            }
            $data[] = [
                'id'           => $e->id,
                'code'         => strtolower($user[UsersConstants::COL_TP]) === 'employee'
                    ? $e->name
                    : $user?->employeeIdFormat($e->employee_id),
                'name'         => $e->name,
                'type'         => $e->payslipType?->name,
                'basic_salary' => $user?->priceFormat($p->basic_salary) ?: '-',
                'net_payble'   => $user?->priceFormat($p->net_payble) ?: '-',
                'status'       => $p->status ? 'Paid' : 'UnPaid',
                'url'          => route(
                    'employee.show',
                    Crypt::encrypt($e->id)
                ),
            ];
        }
        Log::info('searchJson: returning', ['count' => count($data)]);
        return $data;
    }

    public function paySalary(int|string $id, string $date): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'employee_id' => $id, 'month' => $date
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        try {
            $p = Payslip::where([
                ['employee_id', $id],
                [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()],
                ['salary_month', $date]
            ])->first();
            if (!$p) {
                Log::warning('paySalary: not found');
                return redirect()->route('payslip.index')
                    ->with('error', __('Payslip payment failed.'));
            }
            $p->status = 1;
            $p->save();
            Log::info('paySalary: updated', [
                'payslip_id' => $p->id
            ]);
            return redirect()->route('payslip.index')
                ->with('success', __('Payslip payment successfully.'));
        } catch (\Throwable $e) {
            Log::error('paySalary error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                request(),
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function bulkPayCreate(string $date): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['date' => $date]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        $all = Payslip::where(
            'salary_month',
            $date
        )->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
        $unpaid = $all->where('status', 0);
        Log::info('bulkPayCreate: counts', [
            'all' => $all->count(), 'unpaid' => $unpaid->count()
        ]);
        return view('payslip.bulkcreate', [
            'Employees' => $all,
            'unpaidEmployees' => $unpaid,
            'date' => $date
        ]);
    }

    public function bulkPayment(Request $request, string $date): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['date' => $date]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        try {
            DB::transaction(function () use ($date, $user) {
                Payslip::where([
                    ['salary_month', $date],
                    [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()],
                    ['status', 0]
                ])->update(['status' => 1]);
            });
            Log::info('bulkPayment: completed');
            return redirect()->route('payslip.index')
                ->with('success', __('Payslip bulk payment successfully.'));
        } catch (\Throwable $e) {
            Log::error('bulkPayment error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function employeePayslip(): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start');
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        $e = Employee::where('user_id', $user?->id)->first();
        $slips = Payslip::where('employee_id', $e->id)->get();
        Log::info('employeePayslip: count', ['count' => $slips->count()]);
        return view('payslip.employeepayslip', ['payslip' => $slips]);
    }

    public function pdf(int|string $id, string $month): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'employee_id' => $id, 'month' => $month
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        $p = Payslip::where([
            ['employee_id', $id],
            ['salary_month', $month],
            [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()]
        ])->first();
        $detail = Utility::employeePayslipDetail($id, $month);
        Log::info('pdf: prepared', ['payslip_id' => $p->id]);
        return view('payslip.pdf', [
            'payslip'       => $p,
            'employee'      => Employee::find($id),
            'payslipDetail' => $detail
        ]);
    }

    public function send(int|string $id, string $month): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'employee_id' => $id, 'month' => $month
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        $set = Utility::settings();
        if (!$set['payslip_sent']) {
            Log::info('send: disabled by settings');
            return back()->with('success', __('Payslip successfully sent.'));
        }
        $p = Payslip::where([
            ['employee_id', $id],
            ['salary_month', $month],
            [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()]
        ])->first();
        $e = Employee::find($p->employee_id);
        $p->name = $e->name;
        $p->email = $e->email;
        $pId     = Crypt::encrypt($p->id);
        $p->url  = route('payslip.payslipPdf', $pId);
        Utility::sendEmailTemplate('payslip_sent', [
            $e->id => $e->email
        ], [
            'employee_name'        => $e->name,
            'employee_email'       => $e->email,
            'payslip_name'         => $p->name,
            'payslip_salary_month' => $p->salary_month,
            'payslip_url'          => $p->url,
        ]);
        Log::info('send: email dispatched', ['payslip_id' => $p->id]);
        return back()->with('success', __('Payslip successfully sent.'));
    }

    public function payslipPdf(int|string $id, string $month): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'encrypted_id' => $id, 'month' => $month
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        $pid = Crypt::decrypt($id);
        $p  = Payslip::whereKey($pid)
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->first();
        $detail = Utility::employeePayslipDetail($p->employee_id, $month);
        Log::info('payslipPdf: loaded', ['payslip_id' => $p->id]);
        return view('payslip.payslipPdf', [
            'payslip'       => $p,
            'employee'      => Employee::find($p->employee_id),
            'payslipDetail' => $detail
        ]);
    }

    public function editEmployee(int|string $id): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['payslip_id' => $id]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard(request(), PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        return view('payslip.salaryEdit', [
            'payslip' => Payslip::find($id)
        ]);
    }

    public function updateEmployee(Request $request, int $id): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'payslip_id' => $id,
            'input' => $request->only(
                'allowance',
                'commission',
                'loan',
                'saturation_deductions',
                'other_payment',
                'rate',
                'hours'
            )
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        try {
            DB::transaction(function () use ($request, $id) {
                foreach ([
                    'allowance', 'commission', 'loan', 'saturation_deductions',
                    'other_payment'
                ] as $f) {
                    $cls = [
                        'allowance' => Allowance::class,
                        'commission' => Commission::class,
                        'loan'      => Loan::class,
                        'saturation_deductions' => SaturationDeduction::class,
                        'other_payment' => OtherPayment::class
                    ][$f];
                    foreach ($request->$f ?? [] as $k => $v) {
                        $cls::find($request->{"{$f}_id"}[$k])
                            ->update(['amount' => $v]);
                    }
                }
                foreach ($request->rate ?? [] as $k => $v) {
                    Overtime::find($request->rate_id[$k])
                        ->update(['rate' => $v, 'hours' => $request->hours[$k]]);
                }
                $p = Payslip::findOrFail($request->payslip_id);
                foreach ([
                    'allowance', 'commission', 'loan', 'saturation_deduction',
                    'other_payment', 'overtime'
                ] as $f) {
                    $p->$f = Employee::{$f}($p->employee_id);
                }
                $p->net_payble = Employee::find($p->employee_id)->getNetSalary();
                $p->save();
            });
            Log::info('updateEmployee: saved changes', ['payslip_id' => $id]);
            return redirect()->route('payslip.index')
                ->with('success', __('Employee payroll successfully updated.'));
        } catch (\Throwable $e) {
            Log::error('updateEmployee error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'user_id' => $request->user()?->id
        ]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) {
            return $user;
        }
        if ($c = self::guard($request, PermissionsConstants::MNG_PSL, self::REDIRECT_INDEX)) {
            return $c;
        }
        $fileName = 'payslip_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        Log::info('export: downloading', ['file' => $fileName]);
        return Excel::download(new PayslipExport($request), $fileName);
    }
}
