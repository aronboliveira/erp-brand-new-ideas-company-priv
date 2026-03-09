<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\{AppModuleType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{Cache, Log};

class Note extends Model
{
    use UsesUuids;
    use HasAuditFields;

    protected $table = DC::TABLE_NOTES;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        'title',
        'note',
        AC::COL_MT,
        AC::COL_MI,
        'document',
    ];

    protected $with = [
        'createdBy',
        'documentRef',
    ];

    protected $casts = [
        AC::COL_MT => AppModuleType::class,
    ];

    protected $appends = [
        'module_type_label',
        'module_type_category',
        'module_type_icon',
        'module_type_color',
        'is_business_module',
        'is_technical_module',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $m): void {
            try {
                $title = $m->getAttribute('title');
                $title = is_scalar($title) ? trim((string) $title) : '';
                $m->setAttribute('title', $title !== '' ? Str::limit($title, 1024, '') : null);

                $body = $m->getAttribute('note');
                $body = is_scalar($body) ? trim((string) $body) : '';
                $m->setAttribute('note', $body !== '' ? $body : null);

                $mt = $m->getAttribute(AC::COL_MT);
                if (!$mt instanceof AppModuleType) {
                    $raw = is_scalar($mt) ? (string) $mt : null;
                    $m->setAttribute(AC::COL_MT, AppModuleType::normalize($raw)->value);
                } else {
                    $m->setAttribute(AC::COL_MT, $mt->value);
                }

                $mi = $m->getAttribute(AC::COL_MI);
                $mi = is_scalar($mi) ? trim((string) $mi) : '';
                $m->setAttribute(AC::COL_MI, $mi !== '' ? $mi : null);

                $doc = $m->getAttribute('document');
                $doc = is_scalar($doc) ? trim((string) $doc) : '';
                $m->setAttribute('document', $doc !== '' ? $doc : null);
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving normalization failed', [
                    'id' => $m->getAttribute('id'),
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });

        static::saved(static function (self $m): void {
            static::invalidateCachedForModule(
                $m->getAttribute(AC::COL_MT),
                $m->getAttribute(AC::COL_MI)
            );
        });

        static::deleted(static function (self $m): void {
            static::invalidateCachedForModule(
                $m->getAttribute(AC::COL_MT),
                $m->getAttribute(AC::COL_MI)
            );
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function documentRef(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document', 'id');
    }

    public function moduleRef(): MorphTo
    {
        return $this->morphTo(AC::COL_MD, AC::COL_MT, AC::COL_MI);
    }

    public function scopeRecent(Builder $q): Builder
    {
        return $q->orderBy('id', 'desc');
    }

    public function scopeForModule(Builder $q, AppModuleType|string|null $type, ?string $moduleId): Builder
    {
        $enum = $type instanceof AppModuleType ? $type : AppModuleType::normalize(is_scalar($type) ? (string) $type : null);
        $q->where(AC::COL_MT, $enum->value);

        $mid = trim((string) ($moduleId ?? ''));
        return $mid !== '' ? $q->where(AC::COL_MI, $mid) : $q;
    }

    public function scopeForDocument(Builder $q, ?string $documentId): Builder
    {
        $d = trim((string) ($documentId ?? ''));
        return $d !== '' ? $q->where('document', $d) : $q;
    }

    public function moduleTypeEnum(): AppModuleType
    {
        try {
            $raw = $this->getAttribute(AC::COL_MT);
            return $raw instanceof AppModuleType
                ? $raw
                : AppModuleType::normalize(is_scalar($raw) ? (string) $raw : null);
        } catch (\Throwable $e) {
            Log::error(static::class . '::moduleTypeEnum — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return null;
        }
    }

    public function getModuleTypeLabelAttribute(): string
    {
        return $this->moduleTypeEnum()->label();
    }

    public function getModuleTypeCategoryAttribute(): string
    {
        return $this->moduleTypeEnum()->getCategory();
    }

    public function getModuleTypeIconAttribute(): string
    {
        return $this->moduleTypeEnum()->getIcon();
    }

    public function getModuleTypeColorAttribute(): string
    {
        return $this->moduleTypeEnum()->getColor();
    }

    public function getIsBusinessModuleAttribute(): bool
    {
        return $this->moduleTypeEnum()->isBusinessModule();
    }

    public function getIsTechnicalModuleAttribute(): bool
    {
        return $this->moduleTypeEnum()->isTechnicalModule();
    }

    public static function cacheKeyForModuleNotes(AppModuleType|string|null $type, ?string $moduleId): string
    {
        try {
            $enum = $type instanceof AppModuleType ? $type : AppModuleType::normalize(is_scalar($type) ? (string) $type : null);
            $mid = trim((string) ($moduleId ?? ''));
            $mid = $mid !== '' ? $mid : 'none';
            return 'notes:module:' . $enum->value . ':' . $mid;
        } catch (\Throwable $e) {
            Log::error(static::class . '::cacheKeyForModuleNotes — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    public static function cachedForModule(AppModuleType|string|null $type, ?string $moduleId, int $ttlSeconds = 300)
    {
        try {
            $key = self::cacheKeyForModuleNotes($type, $moduleId);

            try {
                return Cache::remember($key, $ttlSeconds, static function () use ($type, $moduleId) {
                    return static::query()
                        ->forModule($type, $moduleId)
                        ->recent()
                        ->get();
                });
            } catch (\Throwable $e) {
                Log::warning(static::class . ' cachedForModule failed', [
                    'type' => is_scalar($type) ? (string) $type : (is_object($type) ? get_class($type) : null),
                    'module_id' => $moduleId,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return static::query()->forModule($type, $moduleId)->recent()->get();
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::cachedForModule — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return null;
        }
    }

    public static function invalidateCachedForModule(AppModuleType|string|null $type, ?string $moduleId): void
    {
        try {
            Cache::forget(self::cacheKeyForModuleNotes($type, $moduleId));
        } catch (\Throwable $e) {
            Log::debug(static::class . ' invalidateCachedForModule failed', [
                'type' => is_scalar($type) ? (string) $type : (is_object($type) ? get_class($type) : null),
                'module_id' => $moduleId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
