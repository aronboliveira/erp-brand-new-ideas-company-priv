<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\PaymentPatternType;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};

class OtherPayment extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_OT_PYMTS;

    /** @var array<string,string> */
    public static array $otherPaymentType = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    protected $fillable = [
        UC::COL_EMP_ID,
        'title',
        'amount',
        'type',
        'description',
        'notes',
        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        UC::COL_EMP_ID => 'string',
        'type'         => PaymentPatternType::class,
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

        static::saving(function (OtherPayment $m): void {
            if ($m->type !== null) {
                $norm = PaymentPatternType::normalize($m->type);
                if ($norm) $m->type = $norm;
            }

            if ($m->amount < 0) $m->amount = 0;

            if ($m->type === PaymentPatternType::Percentage && $m->amount > 100)
                $m->amount = 100;
        });
    }

    public function getIsPercentageAttribute(): bool
    {
        return $this->type === PaymentPatternType::Percentage;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', UC::COL_EMP_ID);
    }
}
