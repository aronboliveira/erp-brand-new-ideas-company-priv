<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Promotion extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY     = 'created_by';
    private const COL_DESCRIPTION    = 'description';
    private const COL_DESIGNATION_ID = 'designation_id';
    private const COL_EMPLOYEE_ID    = 'employee_id';
    private const COL_PROMOTION_DATE = 'promotion_date';
    private const COL_PROMOTION_TITLE = 'promotion_title';

    private const FILLABLE = [
        self::COL_EMPLOYEE_ID,
        self::COL_DESIGNATION_ID,
        self::COL_PROMOTION_TITLE,
        self::COL_PROMOTION_DATE,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function designation(): HasOne
    {
        return $this->hasOne(Designation::class, 'id', self::COL_DESIGNATION_ID);
        // * consider belongsTo(Designation::class,self::COL_DESIGNATION_ID,'id')
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', self::COL_EMPLOYEE_ID);
        // * consider belongsTo(Employee::class,self::COL_EMPLOYEE_ID,'id')
    }
}
