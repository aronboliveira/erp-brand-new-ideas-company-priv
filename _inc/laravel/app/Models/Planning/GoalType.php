<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\GoalType as GoalTypeEnum;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\{Carbon};
use Illuminate\Support\Facades\{Log};

class GoalType extends Model
{
    use HasAuditFields, HasFactory, UsesUuids, NormalizesArrays;

    protected $table = DC::TABLE_GOAL_TYPES;

    protected $fillable = [
        'category',
        'name',
        'description',
        'icon',
        'color',
        'rules',
        'metadata',
        'tags',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'rules'      => 'array',
        'metadata'   => 'array',
        'tags'       => 'array',
        DC::COL_C_AT => 'datetime',
        DC::COL_U_AT => 'datetime',
    ];

    protected $with = [
        'creator',
    ];

    protected $appends = [
        'category_enum',
    ];

    public static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $categoryEnum = GoalTypeEnum::normalize(
                    (string) ($model->getAttribute('category') ?? '')
                );
                $model->setAttribute('category', $categoryEnum->value);

                $color = (string) ($model->getAttribute('color') ?? '');
                $color = trim($color);
                if ($color === '' || !preg_match('/^#[0-9a-f]{6}$/i', $color))
                    $color = '#006666';
                $model->setAttribute('color', $color);

                $rules    = self::normalizeArrayField($model->getAttribute('rules'));
                $metadata = self::normalizeArrayField($model->getAttribute('metadata'));
                $tags     = self::normalizeArrayField($model->getAttribute('tags'));

                $metadata = array_values($metadata);

                $tags = collect($tags)
                    ->filter(static fn($t) => is_string($t) && trim($t) !== '')
                    ->map(static fn($t) => trim((string) $t))
                    ->unique()
                    ->values()
                    ->all();

                $model->setAttribute('rules', $rules);
                $model->setAttribute('metadata', $metadata);
                $model->setAttribute('tags', $tags);

                $model->ensureJsonAttributesAreEncoded([
                    'rules',
                    'metadata',
                    'tags',
                ]);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed during saving normalization', [
                    'error' => $e->getMessage(),
                    'id'    => $model->getAttribute('id'),
                ]);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->creator();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
    }

    public function getCategoryEnumAttribute(): GoalTypeEnum
    {
        return GoalTypeEnum::normalize(
            (string) ($this->getAttribute('category') ?? '')
        );
    }

    public function scopeOfCategory(Builder $query, string|GoalTypeEnum|null $category): Builder
    {
        $enum = GoalTypeEnum::normalize($category);
        return $query->where('category', $enum->value);
    }

    public static function findByCategoryCached(string|GoalTypeEnum|null $category): ?self
    {
        $enum = GoalTypeEnum::normalize($category);
        $key  = 'goal_type:' . $enum->value;

        try {
            return cache()->remember(
                $key,
                Carbon::now()->addMinutes(10),
                static fn() => static::query()
                    ->ofCategory($enum)
                    ->first()
            );
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to fetch goal type from cache', [
                'error'    => $e->getMessage(),
                'category' => $enum->value,
            ]);

            try {
                return static::query()
                    ->ofCategory($enum)
                    ->first();
            } catch (\Throwable $e2) {
                Log::error(static::class . ' failed to fetch goal type without cache', [
                    'error'    => $e2->getMessage(),
                    'category' => $enum->value,
                ]);
                return null;
            }
        }
    }
}
