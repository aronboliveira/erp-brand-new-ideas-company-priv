<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{IsNumericBenefit, UsesUuids};
use Illuminate\Database\Eloquent\Model;

class LoanOption extends Model
{
    use IsNumericBenefit, UsesUuids;

    protected const TABLE = DC::TABLE_LOAN_OPTS;
    protected $fillable = ['name', 'description', BC::COL_EXP_BDG, BC::COL_MAX_BDG, BC::COL_MIN_ITM, BC::COL_MAX_ITM, BC::COL_FGTS_PCT, BC::COL_SVR_GRT, BC::COL_RNGT, BC::COL_GRC_PRD_DYS, BC::COL_TC];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $casts = [
        BC::COL_EXP_BDG => 'decimal:2',
        BC::COL_MAX_BDG => 'decimal:2',
    ];
    protected static function booted(): void
    {
        parent::booted();
        static::saving(function ($model) {
            static::verifyMaxBudget($model);
        });
    }
}
