<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{IsNumericBenefit, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};
/**
 * @property mixed $created_by
 */

class AllowanceOption extends Model
{
    use HasFactory, IsNumericBenefit, UsesUuids;

    protected const TABLE = DC::TABLE_ALLOWANCE_OPTS;
    protected $fillable = ['name', 'description', BC::COL_EXP_BDG, BC::COL_MAX_BDG, BC::COL_VLD_FRM, BC::COL_VLD_TO, 'renews', DC::COL_TABLE_CREATOR];
    protected $guarded = ['id'];
    protected $casts = [
        BC::COL_EXP_BDG => 'decimal:2',
        BC::COL_MAX_BDG => 'decimal:2',
        BC::COL_VLD_FRM => 'date',
        BC::COL_VLD_TO => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function ($model) {
            static::verifyMaxBudget($model);
            if (
                isset($model->{BC::COL_MIN_ITM}, $model->{BC::COL_MAX_ITM})
                && $model->{BC::COL_MIN_ITM} > $model->{BC::COL_MAX_ITM}
            ) {
                [$model->{BC::COL_MIN_ITM}, $model->{BC::COL_MAX_ITM}] =
                    [$model->{BC::COL_MAX_ITM}, $model->{BC::COL_MIN_ITM}];
            }

            if (isset($model->{BC::COL_FGTS_PCT})) {
                $pct = (int) $model->{BC::COL_FGTS_PCT};
                $model->{BC::COL_FGTS_PCT} = max(0, min(50, $pct));
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }
}
