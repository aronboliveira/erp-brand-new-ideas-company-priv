<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Termination extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY      = 'created_by';
    private const COL_DESCRIPTION     = 'description';
    private const COL_EMPLOYEE_ID     = 'employee_id';
    private const COL_NOTICE_DATE     = 'notice_date';
    private const COL_TERMINATION_DATE = 'termination_date';
    private const COL_TERMINATION_TYPE = 'termination_type';
    private const FILLABLE = [
        self::COL_EMPLOYEE_ID,
        self::COL_NOTICE_DATE,
        self::COL_TERMINATION_DATE,
        self::COL_TERMINATION_TYPE,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', self::COL_EMPLOYEE_ID);
        // * consider belongsTo(Employee::class,self::COL_EMPLOYEE_ID,'id')
    }

    public function terminationType(): HasOne
    {
        return $this->hasOne(TerminationType::class, 'id', self::COL_TERMINATION_TYPE);
        // * consider belongsTo(TerminationType::class,self::COL_TERMINATION_TYPE,'id')
    }
}
