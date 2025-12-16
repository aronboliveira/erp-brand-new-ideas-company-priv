<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    MessagesConstants as MC
};
use App\Enums\{AvailableLang, NotificationTemplateType};
use App\Models\{Language, User};
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\{Cache, Log};

class NotificationTemplate extends Model
{
    use HasAuditFields, HasFactory, UsesUuids, NormalizesArrays;

    protected $table = DC::TABLE_NOTIFICATION_TEMPLATES;
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        AC::COL_AV_FROM,
        AC::COL_DSB,
        'categories',
        MC::COL_EX_PLN,
        'rules',
        MC::COL_AV_LG,
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];
    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];
    protected $casts = [
        'categories'      => 'array',
        MC::COL_EX_PLN    => 'array',
        'rules'           => 'array',
        MC::COL_AV_LG     => 'array',
        AC::COL_AV_FROM   => 'datetime',
        AC::COL_DSB       => 'boolean',
        DC::COL_C_AT      => 'datetime',
        DC::COL_U_AT      => 'datetime',
    ];
    protected $with = [
        'creator',
    ];
    protected $appends = [
        'is_active',
        'type_enum',
        'available_languages_resolved',
    ];

    public static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $rawSlug = (string) ($model->getAttribute('slug') ?? '');
                if ($rawSlug === '' && is_string($model->getAttribute('name')))
                    $rawSlug = (string) $model->getAttribute('name');
                $model->setAttribute('slug', Str::slug($rawSlug));
                $typeEnum = NotificationTemplateType::normalize(
                    (string) ($model->getAttribute('type') ?? '')
                );
                $model->setAttribute('type', $typeEnum->value);
                $availableFrom = $model->getAttribute(AC::COL_AV_FROM);
                if (!$availableFrom instanceof Carbon) {
                    try {
                        $availableFrom = $availableFrom
                            ? Carbon::parse((string) $availableFrom)
                            : Carbon::now();
                    } catch (\Throwable) {
                        $availableFrom = Carbon::now();
                    }
                }
                $model->setAttribute(AC::COL_AV_FROM, $availableFrom);
                $disabledRaw = $model->getAttribute(AC::COL_DSB);
                $model->setAttribute(AC::COL_DSB, (bool) $disabledRaw);
                $categories = self::normalizeArrayField(
                    $model->getAttribute('categories')
                );
                $excludedPlans = self::normalizeArrayField(
                    $model->getAttribute(MC::COL_EX_PLN)
                );
                $rules = self::normalizeArrayField(
                    $model->getAttribute('rules')
                );
                $languages = self::normalizeArrayField(
                    $model->getAttribute(MC::COL_AV_LG)
                );
                if (empty($languages)) {
                    $defaults = [DC::DEFAULT_LANG];
                    if (enum_exists(AvailableLang::class)) {
                        try {
                            /** @var \App\Enums\AvailableLang $ptBr */
                            $ptBr = AvailableLang::PtBr;
                            $defaults[] = $ptBr->value;
                        } catch (\Throwable $e) {
                            Log::debug(static::class . ' could not load AvailableLang::PtBr', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    $languages = collect($defaults)
                        ->filter(fn($v) => is_string($v) && trim($v) !== '')
                        ->values()
                        ->all();
                }

                $model->setAttribute(
                    'categories',
                    collect($categories)->values()->all()
                );
                $model->setAttribute(
                    MC::COL_EX_PLN,
                    collect($excludedPlans)->values()->all()
                );
                $model->setAttribute(
                    'rules',
                    collect($rules)->values()->all()
                );
                $model->setAttribute(
                    MC::COL_AV_LG,
                    collect($languages)->values()->all()
                );
                $model->ensureJsonAttributesAreEncoded([
                    'categories',
                    MC::COL_EX_PLN,
                    'rules',
                    MC::COL_AV_LG,
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
        return $this->belongsTo(
            User::class,
            DC::COL_TABLE_CREATOR,
            'id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->creator();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            DC::COL_TABLE_UPDATER,
            'id'
        );
    }

    public function getIsActiveAttribute(): bool
    {
        $disabled = (bool) $this->getAttribute(AC::COL_DSB);
        if ($disabled) {
            return false;
        }

        $from = $this->getAttribute(AC::COL_AV_FROM);

        if (!$from instanceof Carbon) {
            try {
                $from = Carbon::parse((string) $from);
            } catch (\Throwable) {
                $from = Carbon::now();
            }
        }

        return $from->lessThanOrEqualTo(Carbon::now());
    }

    public function getTypeEnumAttribute(): NotificationTemplateType
    {
        return NotificationTemplateType::normalize(
            (string) ($this->getAttribute('type') ?? '')
        );
    }

    public function getAvailableLanguagesResolvedAttribute(): array
    {
        $codes = self::normalizeArrayField(
            $this->getAttribute(MC::COL_AV_LG)
        );

        if (empty($codes) || !class_exists(Language::class))
            return $codes;
        try {
            $languages = Language::query()
                ->whereIn('code', $codes)
                ->orWhereIn('full_name', $codes)
                ->get(['id', 'code', 'full_name'])
                ->toArray();

            return $languages;
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to resolve languages', [
                'error' => $e->getMessage(),
                'codes' => $codes,
            ]);
            return $codes;
        }
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where(AC::COL_DSB, false)
            ->where(AC::COL_AV_FROM, '<=', Carbon::now()->toDateTimeString());
    }

    public function scopeOfType(Builder $query, string|NotificationTemplateType|null $type): Builder
    {
        $enum = NotificationTemplateType::normalize($type);
        return $query->where('type', $enum->value);
    }

    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', Str::slug($slug));
    }

    public static function findCachedByTypeAndSlug(
        string|NotificationTemplateType|null $type,
        string $slug,
        bool $onlyActive = true
    ): ?self {
        $enum = NotificationTemplateType::normalize($type);
        $normalizedSlug = Str::slug($slug);

        $cacheKey = sprintf(
            'notification_template:%s:%s:%s',
            $enum->value,
            $normalizedSlug,
            $onlyActive ? 'active' : 'any'
        );

        try {
            return Cache::remember(
                $cacheKey,
                Carbon::now()->addMinutes(10),
                function () use ($enum, $normalizedSlug, $onlyActive) {
                    $query = static::query()
                        ->ofType($enum)
                        ->slug($normalizedSlug);

                    if ($onlyActive) {
                        $query->active();
                    }

                    return $query->first();
                }
            );
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to fetch cached notification template', [
                'error' => $e->getMessage(),
                'type'  => $enum->value,
                'slug'  => $normalizedSlug,
            ]);

            try {
                $query = static::query()
                    ->ofType($enum)
                    ->slug($normalizedSlug);

                if ($onlyActive) {
                    $query->active();
                }

                return $query->first();
            } catch (\Throwable $e2) {
                Log::error(static::class . ' failed to fetch notification template without cache', [
                    'error' => $e2->getMessage(),
                    'type'  => $enum->value,
                    'slug'  => $normalizedSlug,
                ]);
                return null;
            }
        }
    }

    public static function aggregateCountByType(bool $onlyActive = true): array
    {
        try {
            $query = static::query();

            if ($onlyActive)
                $query->active();

            $rows = $query
                ->selectRaw('type, COUNT(*) as aggregate_count')
                ->groupBy('type')
                ->get();

            $result = [];
            foreach ($rows as $row) {
                $key = (string) ($row->type ?? 'unknown');
                $result[$key] = [
                    'count' => (int) ($row->aggregate_count ?? 0),
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to aggregate count by type', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
