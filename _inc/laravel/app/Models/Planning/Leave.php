<?php

namespace App\Models;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Leave extends Model
{
    use UsesUuids;

    private const COL_APPLIED_ON       = 'applied_on';
    private const COL_CREATED_BY       = 'created_by';
    private const COL_END_DATE         = 'end_date';
    private const COL_EMPLOYEE_ID      = 'employee_id';
    private const COL_LEAVE_REASON     = 'leave_reason';
    private const COL_LEAVE_TYPE_ID    = 'leave_type_id';
    private const COL_REMARK           = 'remark';
    private const COL_START_DATE       = 'start_date';
    private const COL_STATUS           = 'status';
    private const COL_TOTAL_LEAVE_DAYS = 'total_leave_days';
    private const FILLABLE             = [
        self::COL_EMPLOYEE_ID,
        self::COL_LEAVE_TYPE_ID,
        self::COL_APPLIED_ON,
        self::COL_START_DATE,
        self::COL_END_DATE,
        self::COL_TOTAL_LEAVE_DAYS,
        self::COL_LEAVE_REASON,
        self::COL_REMARK,
        self::COL_STATUS,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function employees(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', self::COL_EMPLOYEE_ID);
        // * consider belongsTo(Employee::class,self::COL_EMPLOYEE_ID,'id')
    }

    public function leaveType(): HasOne
    {
        return $this->hasOne(LeaveType::class, 'id', self::COL_LEAVE_TYPE_ID);
        // * consider belongsTo(LeaveType::class,self::COL_LEAVE_TYPE_ID,'id')
    }
}
