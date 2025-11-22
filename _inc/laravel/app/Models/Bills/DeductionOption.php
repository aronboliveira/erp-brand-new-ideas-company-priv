<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{CalculationBase, Frequency, DeductionType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;

class DeductionOption extends Model
{
    use HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_DEDUCTION_OPTS;

    protected $fillable = [
        'name',
        'code',
        BC::COL_DD_TYPE,
        BC::COL_CCL_BS,
        BC::COL_MIN_PCT,
        BC::COL_MAX_PCT,
        'frequency',
        BC::COL_MDAY_LMT,
    ];

    protected $guarded = ['id', DC::TABLE_CREATOR];

    protected $casts = [
        BC::COL_DD_TYPE => DeductionType::class,
        BC::COL_CCL_BS  => CalculationBase::class,
        'frequency'     => Frequency::class,
        BC::COL_MIN_PCT => 'integer',
        BC::COL_MAX_PCT => 'integer',
        BC::COL_MDAY_LMT => 'integer',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            if ($m->isDirty(BC::COL_DD_TYPE)) {
                $norm = DeductionType::normalize($m->{BC::COL_DD_TYPE});
                $m->{BC::COL_DD_TYPE} = $norm?->value;
            }

            if ($m->isDirty(BC::COL_CCL_BS)) {
                $norm = CalculationBase::normalize($m->{BC::COL_CCL_BS});
                $m->{BC::COL_CCL_BS} = $norm?->value;
            }

            if ($m->isDirty('frequency')) {
                $norm = Frequency::normalize($m->frequency);
                $m->frequency = $norm?->value;
            }

            $min = $m->{BC::COL_MIN_PCT};
            $max = $m->{BC::COL_MAX_PCT};
            if ($min === null || !is_numeric($min) || (int)$min < 0) {
                $m->{BC::COL_MIN_PCT} = 0;
            }
            if ($max === null || !is_numeric($max) || (int)$max > 100) {
                $m->{BC::COL_MAX_PCT} = 100;
            }
            if ($m->{BC::COL_MIN_PCT} > $m->{BC::COL_MAX_PCT}) {
                [$m->{BC::COL_MIN_PCT}, $m->{BC::COL_MAX_PCT}] = [
                    $m->{BC::COL_MAX_PCT},
                    $m->{BC::COL_MIN_PCT}
                ];
            }

            if ($m->{BC::COL_MDAY_LMT} !== null) {
                $m->{BC::COL_MDAY_LMT} = max(1, min(12, (int)$m->{BC::COL_MDAY_LMT}));
            }
        });
    }

    public function setDdTypeAttribute($value): void
    {
        $this->attributes[BC::COL_DD_TYPE] = DeductionType::normalize($value)?->value;
    }
    public function setCalculationBaseAttribute($value): void
    {
        $this->attributes[BC::COL_CCL_BS] = CalculationBase::normalize($value)?->value;
    }
    public function setFrequencyAttribute($value): void
    {
        $this->attributes['frequency'] = Frequency::normalize($value)?->value;
    }
}
