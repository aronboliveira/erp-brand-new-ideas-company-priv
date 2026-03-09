<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasMany
};

/**
 * @property array|string|null $metrics
 * @property string|null $main_metric
 * @property string|null $name
 */
class PerformanceType extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_PRF_TP;

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'description',
        'category',
        PJC::COL_M_METRIC,
        'metrics',
        PJC::COL_CRT,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'metrics'     => 'array',
        PJC::COL_CRT => 'boolean',
    ];

    protected $with = [
        'types',
    ];

    protected $appends = [
        'metrics_count',
        'has_main_metric',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $model): void {
            foreach (['name', 'description', 'category', PJC::COL_M_METRIC] as $field)
                if (isset($model->{$field}) && is_string($model->{$field}))
                    $model->{$field} = trim($model->{$field});
            if ($model->metrics === null)
                $model->metrics = [];
            elseif (!is_array($model->metrics))
                $model->metrics = (array) $model->metrics;
            if ($model->{PJC::COL_M_METRIC}) {
                $metrics = $model->metrics ?? [];
                if (!in_array($model->{PJC::COL_M_METRIC}, $metrics, true))
                    $metrics[] = $model->{PJC::COL_M_METRIC};
                $model->metrics = $metrics;
            }
        });
    }

    public function getMetricsCountAttribute(): int
    {
        return is_array($this->metrics) ? count($this->metrics) : 0;
    }

    public function getHasMainMetricAttribute(): bool
    {
        return !empty($this->{PJC::COL_M_METRIC});
    }

    public function types(): HasMany
    {
        return $this->hasMany(
            Competencies::class,
            'type',
            'id'
        );
    }
}
