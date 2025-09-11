<?php

namespace App\Models;

use App\Models\Allowance;
use App\Models\Commission;
use App\Models\EmployeeDocument;
use App\Models\Loan;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\PayslipType;
use App\Models\SaturationDeduction;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\{Model, Relations\HasMany};
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Employee extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'user_id',
        'name',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'designation_id',
        'company_doj',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_name',
        'bank_identifier_code',
        'branch_location',
        'tax_payer_id',
        'salary_type',
        'salary',
        'created_by'
    ]; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS;                  // ! CHANGED

    public function documents(): Collection
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id', 'employee_id')->get();
    }

    public function salary_type(): HasOne // * KEPT FOR COMPATIBILITY, SHOULD NOT BE CALLED IN ENDPOINT
    {
        return $this->salaryTypeName();
    }

    public function salaryTypeName(): mixed
    {
        return $this->hasOne(PayslipType::class, 'id', 'salary_type')->pluck('name')->first();
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function saturationDeductions(): HasMany
    {
        return $this->hasMany(SaturationDeduction::class);
    }

    public function otherPayments(): HasMany
    {
        return $this->hasMany(OtherPayment::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function getNetSalary(): float
    {
        $total_allowance = $this->allowances->sum(
            fn($a) =>
            $a->type === 'fixed' ? $a->amount : $a->amount * $this->salary / 100
        );
        $total_commission = $this->commissions->sum(
            fn($c) =>
            $c->type === 'fixed' ? $c->amount : $c->amount * $this->salary / 100
        );
        $total_loan = $this->loans->sum(
            fn($l) =>
            $l->type === 'fixed' ? $l->amount : $l->amount * $this->salary / 100
        );
        $total_saturation_deduction = $this->saturationDeductions->sum(
            fn($d) =>
            $d->type === 'fixed' ? $d->amount : $d->amount * $this->salary / 100
        );
        $total_other_payment = $this->otherPayments->sum(
            fn($o) =>
            $o->type === 'fixed' ? $o->amount : $o->amount * $this->salary / 100
        );
        $total_over_time = $this->overtimes->sum(
            fn($ot) =>
            $ot->number_of_days * $ot->hours * $ot->rate
        );

        return $this->salary
            + $total_allowance
            + $total_commission
            - $total_loan
            - $total_saturation_deduction
            + $total_other_payment
            + $total_over_time;
    }

    public static function allowance(string|int $id): string|false
    {
        $allowances = Allowance::where('employee_id', $id)->get();
        return json_encode($allowances);
    }

    public static function commission(string|int $id): string|false
    {
        $commissions = Commission::where('employee_id', $id)->get();
        return json_encode($commissions);
    }

    public static function loan(string|int $id): string|false
    {
        $loans = Loan::where('employee_id', $id)->get();
        return json_encode($loans);
    }

    public static function saturationDeduction(string|int $id): string|false
    {
        $deductions = SaturationDeduction::where('employee_id', $id)->get();
        return json_encode($deductions);
    }

    public static function otherPayment(string|int $id): string|false
    {
        $payments = OtherPayment::where('employee_id', $id)->get();
        return json_encode($payments);
    }

    public static function overtime(string|int $id): string|false
    {
        $ots = Overtime::where('employee_id', $id)->get();
        return json_encode($ots);
    }

    public static function employeeId(): string|int
    {
        $e = self::latest()->first();
        if (!$e) return 1;
        $id = $e->getKey();
        return is_numeric($id)
            ? ((int)$id + 1)
            : (string) Str::uuid();
    }

    public function branch(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function department(): HasOne
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    public function designation(): HasOne
    {
        return $this->hasOne(Designation::class, 'id', 'designation_id');
    }

    public function salaryType(): HasOne
    {
        return $this->hasOne(PayslipType::class, 'id', 'salary_type');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function paySlip(): HasOne
    {
        return $this->hasOne(Payslip::class, 'id', 'employee_id');
    }

    public function presentStatus($employee_id, $date): mixed
    {
        return \App\Models\EmployeeAttendance::where('employee_id', $employee_id)
            ->where('date', $date)
            ->first();
    }

    public static function employeeSalary($salary): float
    {
        $e = self::where('salary', $salary)->first();
        return $e && $e->salary > 0 ? $e->salary : '-';
    }
}
