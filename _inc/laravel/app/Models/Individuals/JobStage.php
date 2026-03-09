<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\JobStage as JobStageEnum;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Illuminate\Support\Str;
/**
 * @property mixed $created_by
 */

class JobStage extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    protected $table = DC::TABLE_JOB_STG;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        'title',
        'slug',
        'status',
        'order',
        'depth',
        'description',
        'instructions',
        AC::COL_IA,
        'tags',
        'attachments',
        'urls',
        'templates',
        'project',
        'goal',
        'training',
    ];

    protected $attributes = [
        'project'  => null,
        'goal'     => null,
        'training' => null,
    ];

    protected $casts = [
        'status' => JobStageEnum::class,
        'order' => 'int',
        'depth' => 'int',
        AC::COL_IA => 'bool',

        'tags' => 'array',
        'attachments' => 'array',
        'urls' => 'array',
        'templates' => 'array',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'status_icon',
        'status_category',
        'requires_manager_approval',
        'is_positive_stage',
        'is_attention_required',
        'is_end_stage',
    ];

    private const JSON_FIELDS = [
        'tags',
        'attachments',
        'urls',
        'templates',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $m): void {
            try {
                $m->ensureJsonAttributesAreEncoded(self::JSON_FIELDS);
                $title = trim((string) ($m->getAttribute('title') ?? ''));
                if ($title !== '') $m->setAttribute('title', $title);
                $slug = trim((string) ($m->getAttribute('slug') ?? ''));
                if (empty($slug)) {
                    $base = $title !== '' ? Str::slug($title, '_') : '';
                    $candidateUrl = $base !== '' ? $base : ('job_stage_' . now()->timestamp);
                    if (DB::table($m->getTable())->where('slug', $candidateUrl)->where('id', '!=', $m->getAttribute('id') ?? '')->exists()) {
                        $acc = 0;
                        do {
                            $candidateUrl = $base . '_' . Str::uuid()->toString();
                            $acc++;
                        } while (DB::table($m->getTable())->where('slug', $candidateUrl)->where('id', '!=', $m->getAttribute('id') ?? '')->exists());
                        if ($acc > 64000) throw new \RuntimeException('Failed to generate unique slug for JobStage after 64000 attempts');
                        else $m->setAttribute('slug', $candidateUrl);
                    }
                    $m->setAttribute('slug', $candidateUrl);
                } else {
                    $candidateUrl = Str::slug($slug, '_');
                    if (DB::table($m->getTable())->where('slug', $candidateUrl)->exists()) {
                        $acc = 0;
                        do {
                            $candidateUrl = Str::slug($slug . '_' . Str::uuid()->toString(), '_');
                            $acc++;
                        } while (DB::table($m->getTable())->where('slug', $candidateUrl)->exists());
                        if ($acc > 64000) throw new \RuntimeException('Failed to generate unique slug for JobStage after 64000 attempts');
                    } else $m->setAttribute('slug', $candidateUrl);
                }
                $status = $m->getAttribute('status');
                if (!$status instanceof JobStageEnum) {
                    $normalized = JobStageEnum::normalize($status);
                    $m->setAttribute('status', $normalized?->value ?? JobStageEnum::OnHold->value);
                }
                $order = (int) ($m->getAttribute('order') ?? 0);
                $m->setAttribute('order', $order);
                $depthRaw = $m->getAttribute('depth');
                $m->setAttribute('depth', $depthRaw === null ? null : (int) $depthRaw);
                $ia = $m->getAttribute(AC::COL_IA);
                $m->setAttribute(AC::COL_IA, (bool) ($ia ?? false));
                foreach (['project', 'goal', 'training'] as $fk) {
                    if (!array_key_exists($fk, $m->getAttributes())) $m->setAttribute($fk, null);
                    $raw = $m->getAttribute($fk);
                    if ($raw === null) continue;
                    $val = trim((string) $raw);
                    $m->setAttribute($fk, $val === '' ? null : $val);
                }
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving normalization failed', [
                    'id' => $m->getAttribute('id'),
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function projectRef(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project', 'id');
    }

    public function goalRef(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal', 'id');
    }

    public function trainingRef(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training', 'id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where(AC::COL_IA, 1);
    }

    public function scopeForProject(Builder $q, string|null $projectId): Builder
    {
        if ($projectId === null || trim($projectId) === '') return $q;
        return $q->where('project', $projectId);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('order')->orderBy('title');
    }

    public function scopeStatus(Builder $q, JobStageEnum|string|null $status): Builder
    {
        $enum = $status instanceof JobStageEnum ? $status : JobStageEnum::normalize($status);
        if ($enum === null) return $q;
        return $q->where('status', $enum->value);
    }

    public function getStatusLabelAttribute(): string
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->label() ?? '';
    }

    public function getStatusColorAttribute(): string
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->getColor() ?? '#6c757d';
    }

    public function getStatusIconAttribute(): string
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->getIcon() ?? 'user';
    }

    public function getStatusCategoryAttribute(): string
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->getCategory() ?? 'other';
    }

    public function getRequiresManagerApprovalAttribute(): bool
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->requiresManagerApproval() ?? false;
    }

    public function getIsPositiveStageAttribute(): bool
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->isPositiveStage() ?? false;
    }

    public function getIsAttentionRequiredAttribute(): bool
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->isAttentionRequired() ?? false;
    }

    public function getIsEndStageAttribute(): bool
    {
        $enum = $this->getAttribute('status');
        if (!$enum instanceof JobStageEnum) $enum = JobStageEnum::normalize($enum);
        return $enum?->isEndStage() ?? false;
    }

    /* ---------------------------- Business helpers ---------------------------- */

    public function isActive(): bool
    {
        return (bool) ($this->getAttribute(AC::COL_IA) ?? false);
    }

    public function statusEnum(): ?JobStageEnum
    {
        $raw = $this->getAttribute('status');
        return $raw instanceof JobStageEnum ? $raw : JobStageEnum::normalize($raw);
    }

    public function normalizeTags(): void
    {
        $tags = $this->getAttribute('tags');
        $list = $this->normalizeStringList($tags);
        $this->setAttribute('tags', $list);
    }

    public static function cacheKeyForList(?string $projectId = null): string
    {
        $k = $projectId === null || trim($projectId) === '' ? 'all' : trim($projectId);
        return 'job_stages:list:' . $k;
    }

    public static function cachedList(?string $projectId = null, int $ttlSeconds = 300)
    {
        $key = self::cacheKeyForList($projectId);

        try {
            return Cache::remember($key, $ttlSeconds, static function () use ($projectId) {
                return static::query()
                    ->forProject($projectId)
                    ->ordered()
                    ->get();
            });
        } catch (\Throwable $e) {
            Log::warning(static::class . ' cachedList failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);

            return static::query()->forProject($projectId)->ordered()->get();
        }
    }

    public static function invalidateCachedList(?string $projectId = null): void
    {
        try {
            Cache::forget(self::cacheKeyForList($projectId));
        } catch (\Throwable $e) {
            Log::debug(static::class . ' invalidateCachedList failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
