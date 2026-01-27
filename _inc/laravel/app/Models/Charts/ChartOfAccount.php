<?php

namespace App\Models;

use App\Config\Constants\{
    ChartsConstants as CHTC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};
use Illuminate\Support\Facades\DB;

class ChartOfAccount extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_COAS;

    protected $table = self::TABLE;

    protected $fillable = [
        CHTC::COL_NM,
        CHTC::COL_CD,
        'depth',
        CHTC::CUR_BL,
        CHTC::INIT_BL,
        CHTC::EXP_NXT_MN_BL,
        'currency_id',
        'rules',
        'restrictions',
        UC::COL_RSP_ID,
        UC::COL_PD_UPD,
        UC::COL_IS_SYS,
        CHTC::COL_TP,
        CHTC::COL_SUBTP,
        CHTC::COL_ENB,
        CHTC::COL_DESC,
        UC::COL_USER_ID,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        CHTC::COL_CD        => 'integer',
        'depth'             => 'integer',
        CHTC::CUR_BL        => 'decimal:6',
        CHTC::INIT_BL       => 'decimal:6',
        CHTC::EXP_NXT_MN_BL => 'decimal:6',
        'currency_id'       => 'string',
        'rules'        => 'array',
        'restrictions'      => 'array',
        UC::COL_RSP_ID      => 'string',
        UC::COL_PD_UPD      => 'boolean',
        UC::COL_IS_SYS      => 'boolean',
        CHTC::COL_ENB       => 'integer',
    ];

    protected $with = [
        'types',
        'subType',
    ];

    protected $appends = [
        'net_balance',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $coa): void {
            if (empty($coa->{UC::COL_USER_ID}) && !empty($coa->{DC::COL_TABLE_CREATOR}))
                $coa->{UC::COL_USER_ID} = $coa->{DC::COL_TABLE_CREATOR};
        });

        static::saving(function (self $coa): void {
            self::enforceTypeAndSubtypeConstraints($coa);
            self::normalizeFields($coa);
        });
    }

    protected static function enforceTypeAndSubtypeConstraints(self $coa): void
    {
        $typeId    = $coa->{CHTC::COL_TP} ?? null;
        $subTypeId = $coa->{CHTC::COL_SUBTP} ?? null;

        if (!$typeId || !$subTypeId)
            throw new \InvalidArgumentException('Chart of account must have both type and subtype defined.');

        $type = ChartOfAccountType::query()->find($typeId);
        if (!$type) throw new \RuntimeException("Invalid chart of account type: {$typeId}");

        $subType = ChartOfAccountSubType::query()->find($subTypeId);
        if (!$subType) throw new \RuntimeException("Invalid chart of account subtype: {$subTypeId}");

        if ((string) $subType->{CHTC::COL_TP} !== (string) $type->id)
            throw new \RuntimeException('Chart of account subtype does not belong to the provided type.');

        $typeRules = self::decodeRules($type->getAttribute('rules') ?? []);
        $typeCalc  = is_array($typeRules['calc'] ?? null) ? $typeRules['calc'] : [];
        $typeVal   = is_array($typeRules['validation'] ?? null) ? $typeRules['validation'] : [];

        $subCalc = self::decodeRules($subType->getAttribute(CHTC::COL_CC_RL) ?? []);
        $subVal  = self::decodeRules($subType->getAttribute(CHTC::COL_VL_RL) ?? []);

        $calcRules  = array_replace_recursive($typeCalc, $subCalc);
        $valRules   = array_replace_recursive($typeVal, $subVal);

        self::applyCalculationRules($coa, $calcRules);
        self::applyValidationRules($coa, $valRules);
    }

    protected static function decodeRules(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    protected static function applyCalculationRules(self $coa, array $rules): void
    {
        foreach ($rules as $field => $default) {
            if (!is_string($field)) continue;
            if (!array_key_exists($field, $coa->getAttributes())) continue;
            if ($coa->{$field} === null)
                $coa->{$field} = $default;
        }
    }

    protected static function applyValidationRules(self $coa, array $rules): void
    {
        $allowNegative = false;
        if (array_key_exists('allow_negative_balances', $rules)) {
            $allowNegative = (bool) $rules['allow_negative_balances'];
            unset($rules['allow_negative_balances']);
        }

        foreach ($rules as $field => $constraints) {
            if (!is_string($field) || !is_array($constraints)) continue;
            if (!array_key_exists($field, $coa->getAttributes())) continue;

            $value = $coa->{$field};

            if (array_key_exists('min', $constraints) && is_numeric($constraints['min']) && $value !== null && is_numeric($value)) {
                if ($value < $constraints['min'])
                    $coa->{$field} = $constraints['min'];
            }

            if (array_key_exists('max', $constraints) && is_numeric($constraints['max']) && $value !== null && is_numeric($value)) {
                if ($value > $constraints['max'])
                    $coa->{$field} = $constraints['max'];
            }

            if (array_key_exists('allowed', $constraints) && is_array($constraints['allowed'])) {
                if ($value !== null && !in_array($value, $constraints['allowed'], true))
                    $coa->{$field} = null;
            }
        }

        if (!$allowNegative) {
            foreach ([CHTC::CUR_BL, CHTC::INIT_BL, CHTC::EXP_NXT_MN_BL] as $balField)
                if ($coa->{$balField} !== null && $coa->{$balField} < 0)
                    $coa->{$balField} = 0;
        }
    }

    protected static function normalizeFields(self $coa): void
    {
        foreach ([CHTC::COL_NM, 'currency_id'] as $field)
            if (isset($coa->{$field}) && is_string($coa->{$field}))
                $coa->{$field} = trim($coa->{$field});

        if ($coa->currency_id)
            $coa->currency_id = strtoupper(substr($coa->currency_id, 0, 3));

        foreach (['rules', 'restrictions'] as $jsonField) {
            if ($coa->{$jsonField} === null)
                $coa->{$jsonField} = [];
            elseif (!is_array($coa->{$jsonField}))
                $coa->{$jsonField} = (array) $coa->{$jsonField};
        }

        if ($coa->depth === null || $coa->depth < 0)
            $coa->depth = 0;

        if ($coa->{DC::COL_TABLE_CREATOR} === DC::DEFAULT_UUID)
            $coa->{UC::COL_IS_SYS} = true;
        elseif ($coa->{UC::COL_IS_SYS} === null)
            $coa->{UC::COL_IS_SYS} = false;

        if (!$coa->{UC::COL_RSP_ID} && !empty($coa->{UC::COL_USER_ID}))
            $coa->{UC::COL_RSP_ID} = $coa->{UC::COL_USER_ID};

        if ($coa->isDirty([
            'rules',
            'restrictions',
            CHTC::CUR_BL,
            CHTC::EXP_NXT_MN_BL,
        ]))
            $coa->{UC::COL_PD_UPD} = true;
    }

    public function getNetBalanceAttribute(): float
    {
        $balances = $this->balance();
        return (float) ($balances['netAmount'] ?? 0);
    }

    public function types(): HasOne
    {
        return $this->hasOne(
            ChartOfAccountType::class,
            'id',
            CHTC::COL_TP
        );
    }

    public function accounts(): HasOne
    {
        return $this->hasOne(
            JournalItem::class,
            'account',
            'id'
        );
    }

    public function balance(): array
    {
        $item = JournalItem::select(
            DB::raw('sum(credit) as totalCredit'),
            DB::raw('sum(debit) as totalDebit'),
            DB::raw('sum(credit) - sum(debit) as netAmount')
        )
            ->where('account', $this->id)
            ->first();

        return [
            'totalCredit' => $item->totalCredit ?? 0,
            'totalDebit'  => $item->totalDebit ?? 0,
            'netAmount'   => $item->netAmount ?? 0,
        ];
    }

    public function subType(): HasOne
    {
        return $this->hasOne(
            ChartOfAccountSubType::class,
            'id',
            CHTC::COL_SUBTP
        );
    }
}
