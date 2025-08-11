<?php

namespace App\Models;

use App\Models\Employee;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class EmployeeAttendance extends Model
{
    use UsesUuids;

    private const COL_CLOCK_IN     = 'clock_in';
    private const COL_CLOCK_OUT    = 'clock_out';
    private const COL_CREATED_BY   = 'created_by';
    private const COL_DATE         = 'date';
    private const COL_EARLY_LEAVING = 'early_leaving';
    private const COL_EMPLOYEE_ID  = 'employee_id';
    private const COL_LATE         = 'late';
    private const COL_OVERTIME     = 'overtime';
    private const COL_STATUS       = 'status';
    private const COL_TOTAL_REST   = 'total_rest';
    private const FILLABLE         = [
        self::COL_EMPLOYEE_ID,
        self::COL_DATE,
        self::COL_STATUS,
        self::COL_CLOCK_IN,
        self::COL_CLOCK_OUT,
        self::COL_LATE,
        self::COL_EARLY_LEAVING,
        self::COL_OVERTIME,
        self::COL_TOTAL_REST,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function employees(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id', self::COL_EMPLOYEE_ID);
        // * consider belongsTo(Employee::class, self::COL_EMPLOYEE_ID, 'user_id')
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', self::COL_EMPLOYEE_ID);
        // * consider belongsTo(Employee::class, self::COL_EMPLOYEE_ID, 'id')
    }
}
