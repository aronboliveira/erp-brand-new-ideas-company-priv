<?php

namespace App\Models;

use App\Models\Employee;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Warning extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY  = 'created_by';
    private const COL_DESCRIPTION = 'description';
    private const COL_SUBJECT     = 'subject';
    private const COL_WARNING_BY  = 'warning_by';
    private const COL_WARNING_DATE = 'warning_date';
    private const COL_WARNING_TO  = 'warning_to';
    private const COL_EMPLOYEE_ID = 'employee_id';
    private const FILLABLE        = [
        self::COL_WARNING_TO,
        self::COL_WARNING_BY,
        self::COL_SUBJECT,
        self::COL_WARNING_DATE,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
        self::COL_EMPLOYEE_ID
    ];

    protected $fillable = self::FILLABLE;

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
    }

    public function warningTo(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', self::COL_WARNING_TO);
    }

    public function warningBy(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', self::COL_WARNING_BY);
    }
}
