<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\AllowanceType;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Allowance extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_ALW;

    protected $table = self::TABLE;

    protected $fillable = [
        UC::COL_EMP_ID,
        BC::COL_ALW_OPT,
        'title',
        'amount',
        'type',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    /** @var array<string,string> */
    public static array $allowanceType = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    protected $casts = [
        'amount'                    => 'decimal:2',
        UC::COL_EMP_ID             => 'string',
        BC::COL_ALW_OPT => 'string',
        DC::TABLE_CREATOR          => 'string',
        DC::TABLE_UPDATER          => 'string',
        'type' => AllowanceType::class,
    ];

    protected $with = [
        'employee',
        'allowanceOption',
    ];

    protected $appends = [
        'is_percentage',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            $t = $m->type instanceof AllowanceType ? $m->type : AllowanceType::normalize((string)$m->type);
            $m->type = $t ?? AllowanceType::Fixed;
            $amt = (float) $m->amount;
            if ($m->type === AllowanceType::Percentage) $m->amount = max(0.0, min(100.0, $amt));
            else $m->amount = max(0.0, $amt);
            $m->{BC::COL_ALW_OPT} = $m->{BC::COL_ALW_OPT} ?: null;
            if ($m->{BC::COL_ALW_OPT} !== null) {
                $opt = AllowanceOption::find($m->{BC::COL_ALW_OPT});
                if ($opt !== null) {
                    if ($m->type === AllowanceType::Percentage && $opt->{BC::COL_MIN_PCT} !== null)
                        $m->amount = min($m->amount, (float)$opt->{BC::COL_MIN_PCT});
                    if ($m->type === AllowanceType::Percentage && $opt->{BC::COL_MAX_PCT} !== null)
                        $m->amount = max($m->amount, (float)$opt->{BC::COL_MAX_PCT});
                }
            }
        });
    }

    public function getIsPercentageAttribute(): bool
    {
        return $this->type instanceof AllowanceType
            ? $this->type->isPercentage()
            : (string)$this->type === 'percentage';
    }

    public function scopeOfType($q, AllowanceType|string $type)
    {
        $val = $type instanceof AllowanceType ? $type->value : (string)$type;
        return $q->where('type', $val);
    }


    public function employee(): HasOne
    {
        return $this->hasOne(
            Employee::class,
            'id',
            UC::COL_EMP_ID
        );
    }

    public function allowanceOption(): HasOne
    {
        return $this->hasOne(
            AllowanceOption::class,
            'id',
            BC::COL_ALW_OPT
        );
    }
}
