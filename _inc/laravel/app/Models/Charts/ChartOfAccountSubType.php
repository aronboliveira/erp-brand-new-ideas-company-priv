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
/**
 * @property array|string|null $drawing_types
 */

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
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
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
            if (empty($model->getAttribute(CHTC::COL_DR_TP))) {
                $model->setAttribute(CHTC::COL_DR_TP, [
                    'type'   => 'line',
                    'colors' => [
                        'primary'   => '#3b82f6',
                        'secondary' => '#93c5fd',
                        'tertiary'  => '#bfdbfe',
                    ],
                    'options' => [],
                ]);
            }
        });
        static::saving(function (self $m): void {
            // Trim string fields
            foreach ([CHTC::COL_CD, CHTC::COL_NM, CHTC::COL_TP_NM] as $field) {
                $value = $m->getAttribute($field);
                if (is_string($value))
                    $m->setAttribute($field, trim($value));
            }

            // Normalize rules to array
            $rules = $m->getAttribute('rules');
            if ($rules === null) {
                $m->setAttribute('rules', []);
            } elseif (!is_array($rules)) {
                $m->setAttribute('rules', (array) $rules);
            }

            // Normalize JSON-like fields to arrays
            foreach ([CHTC::COL_DR_TP, CHTC::COL_CC_RL, CHTC::COL_VL_RL] as $jsonField) {
                $value = $m->getAttribute($jsonField);
                if ($value !== null && !is_array($value))
                    $m->setAttribute($jsonField, (array) $value);
            }

            // Normalize drawing config
            $drawingConfig = $m->getAttribute(CHTC::COL_DR_TP) ?? [];
            if (!is_array($drawingConfig))
                $drawingConfig = (array) $drawingConfig;
            $m->setAttribute(
                CHTC::COL_DR_TP,
                static::normalizeDrawingConfig($drawingConfig)
            );
            $typeKey = $m->getAttribute(CHTC::COL_TP);
            if ($typeKey) {
                /** @var \App\Models\ChartOfAccountType|null $type */
                $type = $m->type()->first();
                if ($type)
                    static::applyTypeConstraints($m, $type);
            }
            $code = $m->getAttribute(CHTC::COL_CD);
            if (empty($code)) {
                $prefix = '';
                if ($m->relationLoaded('type') && $m->type) {
                    $typeCode = $m->type->getAttribute(CHTC::COL_CD);
                    $prefix   = strtoupper(Str::snake((string) $typeCode));
                    $prefix   = $prefix !== '' ? $prefix . '_' : '';
                }
                $name = $m->getAttribute(CHTC::COL_NM);
                $m->setAttribute(
                    CHTC::COL_CD,
                    strtoupper(Str::snake($prefix . (string) $name))
                );
            }
        });
    }

    /**
     * Herda/impõe parâmetros do ChartOfAccountType associado.
     * - Garante o nome do tipo (tp_name) em sincronia
     * - Mescla atributos: os do tipo são baseline e podem ser sobrescritos localmente
     * - Injeta unidade/metadata de eixo do "type->units" como defaults em draw_type.options (se ausentes)
     */

    protected static function applyTypeConstraints(self $m, ChartOfAccountType $type): void
    {
        $typeName       = $type->getAttribute(CHTC::COL_NM);
        $currentTypeName = $m->getAttribute(CHTC::COL_TP_NM);
        $m->setAttribute(
            CHTC::COL_TP_NM,
            (string) ($typeName ?? $currentTypeName)
        );
        $typeRulesRaw = $type->getAttribute('rules') ?? [];
        $typeRules    = NormalizesArrays::normalizeArrayField($typeRulesRaw);
        $subRulesRaw  = $m->getAttribute('rules') ?? [];
        $subRules     = NormalizesArrays::normalizeArrayField($subRulesRaw);
        $mergedRules = static::mergeRulesRecursively($typeRules, $subRules);
        $m->setAttribute('rules', $mergedRules);
        if (isset($mergedRules['description']) && is_array($mergedRules['description'])) {
            $descCfg     = $mergedRules['description'];
            $maxLen      = $descCfg['max_length'] ?? null;
            $description = $m->getAttribute('description');

            if (
                is_numeric($maxLen)
                && (int) $maxLen > 0
                && is_string($description)
            )
                $m->setAttribute(
                    'description',
                    mb_substr($description, 0, (int) $maxLen)
                );
        }

        $unitsRaw = $type->getAttribute('units') ?? [];
        $units    = NormalizesArrays::normalizeArrayField($unitsRaw);

        $xUnit   = Arr::get($units, 'X.unit');
        $xValues = Arr::get($units, 'X.values', []);
        $yUnit   = Arr::get($units, 'Y.unit');
        $yValues = Arr::get($units, 'Y.values', []);

        $drawRaw = $m->getAttribute(CHTC::COL_DR_TP) ?? [];
        $drawArr = is_array($drawRaw) ? $drawRaw : [];

        $draw = static::normalizeDrawingConfig($drawArr);

        $opts = $draw['options'] ?? [];
        $opts['x_unit']   = $opts['x_unit']   ?? $xUnit;
        $opts['x_values'] = $opts['x_values'] ?? $xValues;
        $opts['y_unit']   = $opts['y_unit']   ?? $yUnit;
        $opts['y_values'] = $opts['y_values'] ?? $yValues;

        $draw['options'] = $opts;

        $m->setAttribute(CHTC::COL_DR_TP, $draw);
    }

    protected static function normalizeDrawingConfig(array $cfg): array
    {
        if (!isset($cfg['type']) || !is_string($cfg['type']) || trim($cfg['type']) === '')
            $cfg['type'] = 'line';
        if (!isset($cfg['colors']) || !is_array($cfg['colors']))
            $cfg['colors'] = [
                'primary'   => '#3b82f6',
                'secondary' => '#93c5fd',
                'tertiary'  => '#bfdbfe',
            ];
        if (!isset($cfg['options']) || !is_array($cfg['options']))
            $cfg['options'] = [];
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
            ChartOfAccountType::class,
            CHTC::COL_TP,
            'id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            DC::COL_TABLE_CREATOR,
            'id'
        );
    }
}
