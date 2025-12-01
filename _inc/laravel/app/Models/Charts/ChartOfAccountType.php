<?php

namespace App\Models;

use App\Config\Constants\{
    ChartsConstants as CHTC,
    DatabaseConstants as DC,
    SettingsConstants as SC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model
};

class ChartOfAccountType extends Model
{
    use UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_COA_TYPES;

    protected $fillable = [
        CHTC::COL_NM,
        CHTC::COL_CD,
        'category',
        'description',
        'rules',
        'units',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'rules' => 'array',
        'units'      => 'array',
    ];

    protected $appends = [
        'x_units',
        'y_units',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->units)) {
                $model->units = [
                    'X' => [
                        'type'   => 'timestamp',
                        'unit'   => 'MM',
                        'values' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
                    ],
                    'Y' => [
                        'type'   => 'value',
                        'unit'   => SC::DEF_SITE_CURRENCY_SB,
                        'values' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    ],
                ];
            }
        });
        static::saving(function (self $m): void {
            foreach ([CHTC::COL_NM, CHTC::COL_CD, 'category'] as $field)
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});

            if ($m->rules === null)
                $m->rules = [];
            elseif (!is_array($m->rules))
                $m->rules = (array) $m->rules;

            if ($m->units !== null && !is_array($m->units))
                $m->units = (array) $m->units;

            $m->units = static::normalizeUnitsArray($m->units ?? []);
        });
    }

    protected static function normalizeUnitsArray(array $units): array
    {
        $units['X'] ??= [];
        $units['Y'] ??= [];

        $units['X']['type']   ??= 'timestamp';
        $units['X']['unit']   ??= 'MM';
        $units['X']['values'] = isset($units['X']['values'])
            ? (array) $units['X']['values']
            : [];

        $units['Y']['type']   ??= 'value';
        $units['Y']['unit']   ??= null;
        $units['Y']['values'] = isset($units['Y']['values'])
            ? (array) $units['Y']['values']
            : [];

        return $units;
    }

    public function getXUnitsAttribute(): ?array
    {
        return $this->units['X'] ?? null;
    }

    public function getYUnitsAttribute(): ?array
    {
        return $this->units['Y'] ?? null;
    }
}
