<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\PaymentPatternType;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};

class Overtime extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_OVT;

    /** @var array<string,string> */
    public static array $overtimeType = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    protected $fillable = [
        UC::COL_EMP_ID,
        'title',
        UC::COL_NDAYS,
        'hours',
        'rate',
        'type',
        'notes',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        UC::COL_EMP_ID   => 'string',
        UC::COL_NDAYS    => 'integer',
        'hours'          => 'integer',
        'rate'           => 'integer',
        'type'           => PaymentPatternType::class,
    ];

    protected $with = [
        'employee',
    ];

    protected $appends = [
        'is_percentage',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (Overtime $m): void {
            if ($m->type !== null) {
                $norm = PaymentPatternType::normalize($m->type);
                $m->type = $norm?->value;
            }
            $daysCol = UC::COL_NDAYS;

            if ((int) $m->{$daysCol} < 0) $m->{$daysCol} = 0;
            if ((int) $m->hours < 0)      $m->hours      = 0;
            if ((int) $m->rate  < 0)      $m->rate       = 0;
        });
    }

    public function getIsPercentageAttribute(): bool
    {
        return $this->type === PaymentPatternType::Percentage;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(
            Employee::class,
            'id',
            UC::COL_EMP_ID
        );
    }
}
