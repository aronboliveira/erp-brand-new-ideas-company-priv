<?php

namespace App\Models;

use App\Config\Constants\{
    ChartsConstants as CHTC,
    DatabaseConstants as DC
};
use App\Traits\{HasAuditFields, HasInheritedRules, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\{Arr, Str};

class ChartOfAccountSubType extends Model
{
    use HasFactory, HasAuditFields, HasInheritedRules, NormalizesArrays, UsesUuids;

    protected $table = DC::TABLE_COA_SUBTYPES;

    protected $fillable = [
        CHTC::COL_CD,
        CHTC::COL_NM,
        'description',
        CHTC::COL_TP,
        CHTC::COL_TP_NM,
        CHTC::COL_DR_TP,
        CHTC::COL_CC_RL,
        CHTC::COL_VL_RL,
        CHTC::COL_RQ_APV,
        CHTC::COL_ALW_MNL_ENT,
        'rules',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        CHTC::COL_DR_TP        => 'array',
        CHTC::COL_CC_RL        => 'array',
        CHTC::COL_VL_RL        => 'array',
        CHTC::COL_RQ_APV       => 'boolean',
        CHTC::COL_ALW_MNL_ENT  => 'boolean',
        'rules'           => 'array',
    ];

    protected $with = [
        'type',
        'createdBy',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->{CHTC::COL_DR_TP})) {
                $model->{CHTC::COL_DR_TP} = [
                    'type'   => 'line',
                    'colors' => [
                        'primary'   => '#3b82f6',
                        'secondary' => '#93c5fd',
                        'tertiary'  => '#bfdbfe',
                    ],
                    'options' => [],
                ];
            }
        });
        static::saving(function (self $m): void {
            foreach ([CHTC::COL_CD, CHTC::COL_NM, CHTC::COL_TP_NM] as $field)
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});
            if ($m->rules === null)          $m->rules = [];
            elseif (!is_array($m->rules))    $m->rules = (array) $m->rules;
            foreach ([CHTC::COL_DR_TP, CHTC::COL_CC_RL, CHTC::COL_VL_RL] as $jsonField)
                if ($m->{$jsonField} !== null && !is_array($m->{$jsonField}))
                    $m->{$jsonField} = (array) $m->{$jsonField};
            $m->{CHTC::COL_DR_TP} = static::normalizeDrawingConfig($m->{CHTC::COL_DR_TP} ?? []);
            if ($m->{CHTC::COL_TP}) {
                /** @var \App\Models\ChartOfAccountType|null $type */
                $type = $m->type()->first();
                if ($type)
                    static::applyTypeConstraints($m, $type);
            }

            if (empty($m->{CHTC::COL_CD})) {
                $prefix = '';
                if ($m->relationLoaded('type') && $m->type) {
                    $prefix = strtoupper(Str::snake((string) $m->type->{CHTC::COL_CD}));
                    $prefix = $prefix !== '' ? $prefix . '_' : '';
                }
                $m->{CHTC::COL_CD} = strtoupper(Str::snake($prefix . (string) $m->{CHTC::COL_NM}));
            }
        });
    }

    /**
     * Herda/impõe parâmetros do ChartOfAccountType associado.
     * - Garante o nome do tipo (tp_name) em sincronia
     * - Mescla atributos: os do tipo são baseline e podem ser sobrescritos localmente
     * - Injeta unidade/metadata de eixo do "type->units" como defaults em draw_type.options (se ausentes)
     */

    protected static function applyTypeConstraints(self $m, \App\Models\ChartOfAccountType $type): void
    {
        $m->{CHTC::COL_TP_NM} = (string) ($type->{CHTC::COL_NM} ?? $m->{CHTC::COL_TP_NM});
        $typeRules = NormalizesArrays::normalizeArrayField($type->rules ?? []);
        $subRules  = NormalizesArrays::normalizeArrayField($m->rules ?? []);
        $mergedRules = static::mergeRulesRecursively($typeRules, $subRules);
        $m->rules = $mergedRules;
        if (isset($mergedRules['description']) && is_array($mergedRules['description'])) {
            $descCfg = $mergedRules['description'];
            $maxLen  = $descCfg['max_length'] ?? null;
            if (
                is_numeric($maxLen)
                && (int) $maxLen > 0
                && is_string($m->description ?? null)
            )
                $m->description = mb_substr($m->description, 0, (int) $maxLen);
        }
        $units   = NormalizesArrays::normalizeArrayField($type->units ?? []);
        $xUnit   = Arr::get($units, 'X.unit');
        $xValues = Arr::get($units, 'X.values', []);
        $yUnit   = Arr::get($units, 'Y.unit');
        $yValues = Arr::get($units, 'Y.values', []);
        $draw = is_array($m->{CHTC::COL_DR_TP} ?? null)
            ? $m->{CHTC::COL_DR_TP}
            : [];
        $draw = static::normalizeDrawingConfig($draw);
        $opts = $draw['options'] ?? [];
        $opts['x_unit']   = $opts['x_unit']   ?? $xUnit;
        $opts['x_values'] = $opts['x_values'] ?? $xValues;
        $opts['y_unit']   = $opts['y_unit']   ?? $yUnit;
        $opts['y_values'] = $opts['y_values'] ?? $yValues;
        $draw['options']      = $opts;
        $m->{CHTC::COL_DR_TP} = $draw;
    }

    protected static function normalizeDrawingConfig(array $cfg): array
    {
        if (!isset($cfg['type']) || !is_string($cfg['type']) || trim($cfg['type']) === '') {
            $cfg['type'] = 'line';
        }

        if (!isset($cfg['colors']) || !is_array($cfg['colors'])) {
            $cfg['colors'] = [
                'primary'   => '#3b82f6',
                'secondary' => '#93c5fd',
                'tertiary'  => '#bfdbfe',
            ];
        }

        if (!isset($cfg['options']) || !is_array($cfg['options'])) {
            $cfg['options'] = [];
        }

        return $cfg;
    }

    public function getChartTypeAttribute(): ?string
    {
        $cfg = $this->{CHTC::COL_DR_TP};

        return is_array($cfg) && isset($cfg['type'])
            ? $cfg['type']
            : null;
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\ChartOfAccountType::class,
            CHTC::COL_TP,
            'id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\User::class,
            DC::TABLE_CREATOR,
            'id'
        );
    }
}
