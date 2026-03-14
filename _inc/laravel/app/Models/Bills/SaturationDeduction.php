<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\PaymentPatternType;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasOne
};
/**
 * @property string|null $deduction_option

 * @property float|null $amount
 * @property int|null $type
 */

class SaturationDeduction extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_ST_DD;

    /** @var array<string,string> */
    public static array $saturationDeductionType = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    protected $fillable = [
        UC::COL_EMP_ID,
        BC::COL_DD_OPT,
        'title',
        'amount',
        'type',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        UC::COL_EMP_ID     => 'string',
        BC::COL_DD_OPT => 'string',
        'type'             => PaymentPatternType::class,
    ];

    protected $with = [
        'employee',
        'deductionOption',
    ];

    protected $appends = [
        'is_percentage',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (SaturationDeduction $m): void {
            if ($m->type !== null) {
                $norm = PaymentPatternType::normalize($m->type);
                if ($norm) $m->type = $norm; // @phpstan-ignore assign.propertyType
            }

            if ($m->amount < 0)
                $m->amount = 0;

            if ($m->type === PaymentPatternType::Percentage->value) {
                $value = (float) $m->amount;
                if ($value < 0) $value = 0;
                if ($value > 100) $value = 100;

                if ($m->deduction_option) {
                    /** @var DeductionOption|null $opt */
                    $opt = DeductionOption::query()->find($m->deduction_option);

                    if ($opt) {
                        $min = $opt->{BC::COL_MIN_PCT};
                        $max = $opt->{BC::COL_MAX_PCT};

                        if ($min !== null && $value < $min)
                            $value = (float) $min;

                        if ($max !== null && $value > $max)
                            $value = (float) $max;
                    }
                }

                $m->amount = $value;
            }
        });
    }

    public function getIsPercentageAttribute(): bool
    {
        return $this->type === PaymentPatternType::Percentage;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID);
    }

    public function deductionOption(): HasOne
    {
        return $this->hasOne(
            DeductionOption::class,
            'id',
            BC::COL_DD_OPT
        );
    }
}
