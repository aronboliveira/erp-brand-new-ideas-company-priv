<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Payslip extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_EMPLOYEE_ID = 'employee_id';

    protected $fillable = [
        self::COL_EMPLOYEE_ID,
        'net_payble',
        'basic_salary',
        'salary_month',
        'status',
        'allowance',
        'commission',
        'loan',
        'saturation_deduction',
        'other_payment',
        'overtime',
        self::COL_CREATED_BY,
    ];

    public static function employee(string $id): ?Employee
    {
        return Employee::find($id);
    }

    public function employees(): HasOne
    {
        return $this
            ->hasOne(Employee::class, 'id', self::COL_EMPLOYEE_ID);
        // * consider using belongsTo(Employee::class, self::COL_EMPLOYEE_ID)
    }
}
