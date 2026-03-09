<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Models\{Branch, Department, Goal, GoalType as GoalTypeModel, User};
use App\Traits\{DescribesCompanyBranch, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model, Relations\BelongsTo};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\Log;

/**
 * @property int|null $rating
 */
class GoalTracking extends Model
{
    use UsesUuids, HasAuditFields, DescribesCompanyBranch, NormalizesArrays, FiltersSecureAttachments;

    protected $table = DC::TABLE_GL_TRK;

    private const LEGACY_STATUS_LIST = [
        'Not Started',
        'In Progress',
        'Completed',
    ];

    public static array $status = [];

    protected $fillable = [
        'company',
        'branch',
        'department',
        PJC::COL_GL_TP,
        'goal',
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        'subject',
        'rating',
        PJC::COL_TRG_ACHV,
        'description',
        'status',
        'progress',
        'priority',
        'metrics',
        'attachments',
        'tags',
        'steps',
        'metadata',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'progress'      => 'decimal:2',
        'metrics'       => 'array',
        'attachments'   => 'array',
        'tags'          => 'array',
        'steps'         => 'array',
        'metadata'      => 'array',
        PJC::COL_S_DT   => 'date',
        PJC::COL_E_DT   => 'date',
        DC::COL_C_AT    => 'datetime',
        DC::COL_U_AT    => 'datetime',
    ];

    protected $with = [
        'goalType',
        'goal',
    ];

    protected $appends = [
        'status_enum',
        'priority_enum',
        'duration_days',
        'progress_clamped',
    ];

    public static function booted(): void
    {
        if (self::$status === []) {
            try {
                $enumValues = method_exists(EvaluationStatus::class, 'values')
                    ? array_column(EvaluationStatus::cases(), 'value')
                    : array_map(fn(EvaluationStatus $c) => $c->value, EvaluationStatus::cases());

                self::$status = array_values(array_unique([
                    ...self::LEGACY_STATUS_LIST,
                    ...$enumValues,
                ]));
            } catch (\Throwable $e) {
                self::$status = self::LEGACY_STATUS_LIST;
                Log::warning(static::class . ' failed to hydrate status list from enum', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        static::saving(function (self $model): void {
            try {
                foreach (['company', 'branch', 'department', 'goal', PJC::COL_GL_TP] as $column) {
                    $value = $model->getAttribute($column);
                    if ($value === null) continue;
                    $value = trim((string) $value);
                    if ($value === '') $value = null;
                    $model->setAttribute($column, $value);
                }

                $start = self::parseDate($model->getAttribute(PJC::COL_S_DT));
                $end   = self::parseDate($model->getAttribute(PJC::COL_E_DT));

                if ($start && $end && $end->lessThan($start)) {
                    $tmp   = $start;
                    $start = $end;
                    $end   = $tmp;
                }

                $model->setAttribute(PJC::COL_S_DT, $start?->toDateString());
                $model->setAttribute(PJC::COL_E_DT, $end?->toDateString());

                foreach (['subject', 'rating', PJC::COL_TRG_ACHV, 'description'] as $column) {
                    $value = $model->getAttribute($column);
                    if ($value === null) {
                        $model->setAttribute($column, null);
                        continue;
                    }
                    $value = trim((string) $value);
                    if ($value === '') $value = null;
                    $model->setAttribute($column, $value);
                }

                $progressRaw = $model->getAttribute('progress');
                $progress    = is_numeric($progressRaw) ? (float) $progressRaw : 0.0;
                $originalProgress = $model->exists
                    ? (is_numeric($model->getOriginal('progress')) ? (float) $model->getOriginal('progress') : null)
                    : null;

                $progress = max(0.0, min(100.0, $progress));

                $statusRaw   = (string) ($model->getAttribute('status') ?? '');
                $priorityRaw = (string) ($model->getAttribute('priority') ?? '');

                try {
                    $statusEnum = EvaluationStatus::normalize($statusRaw !== '' ? $statusRaw : null);
                    $status     = $statusEnum->value;
                } catch (\Throwable) {
                    $status = $statusRaw;
                }

                try {
                    $priorityEnum = PriorityLevel::normalize($priorityRaw !== '' ? $priorityRaw : null);
                    $priority     = $priorityEnum->value;
                } catch (\Throwable) {
                    $priority = $priorityRaw;
                }

                $metrics     = self::normalizeArrayField($model->getAttribute('metrics'));
                $attachments = self::normalizeArrayField($model->getAttribute('attachments'));
                $tags        = self::normalizeArrayField($model->getAttribute('tags'));
                $steps       = self::normalizeArrayField($model->getAttribute('steps'));
                $metadata    = self::normalizeArrayField($model->getAttribute('metadata'));

                $metrics     = self::normalizeMetricsArray($metrics);
                $attachments = self::sanitizeAttachmentsArray($attachments);
                $tags        = self::sanitizeTagsArray($tags);
                $steps       = self::sanitizeStepsArray($steps);

                $rules = null;
                $goalTypeId = $model->getAttribute(PJC::COL_GL_TP);

                if ($goalTypeId && class_exists(GoalTypeModel::class)) {
                    try {
                        $definition = GoalTypeModel::query()->find($goalTypeId);
                        if ($definition) {
                            $rawRules = $definition->getAttribute('rules');
                            if (is_array($rawRules)) $rules = $rawRules;
                            elseif (is_string($rawRules)) {
                                $decoded = json_decode($rawRules, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $rules = $decoded;
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning(static::class . ' failed to resolve goal type rules for tracking', [
                            'error'     => $e->getMessage(),
                            'goal_type' => $goalTypeId,
                        ]);
                    }
                }

                if (is_array($rules) && $rules !== []) {
                    if (isset($rules['status']) && is_array($rules['status']))
                        $status = self::applyStatusRules($status, $rules['status']);

                    if (isset($rules['priority']) && is_array($rules['priority']))
                        $priority = self::applyPriorityRules($priority, $rules['priority']);

                    if (isset($rules['progress']) && is_array($rules['progress']))
                        $progress = self::applyProgressRules($progress, $rules['progress'], $originalProgress);

                    if (isset($rules['start_date']) && is_array($rules['start_date']))
                        $start = self::applyDateBoundaryRules($start, $rules['start_date'], 'start_date');

                    if (isset($rules['end_date']) && is_array($rules['end_date']))
                        $end = self::applyDateBoundaryRules($end, $rules['end_date'], 'end_date');

                    if (isset($rules['period']) && is_array($rules['period']) && $start && $end)
                        [$start, $end] = self::applyPeriodRules($start, $end, $rules['period']);

                    if (isset($rules['metrics']) && is_array($rules['metrics']))
                        $metrics = self::applyMetricRules($metrics, $rules['metrics']);

                    if (isset($rules['attachments']) && is_array($rules['attachments']))
                        $attachments = self::applyAttachmentRules($attachments, $rules['attachments']);

                    if (isset($rules['tags']) && is_array($rules['tags']))
                        $tags = self::applyTagRules($tags, $rules['tags']);

                    if (isset($rules['steps']) && is_array($rules['steps']))
                        $steps = self::applyStepRules($steps, $rules['steps']);

                    if (isset($rules['rating']) && is_array($rules['rating'])) {
                        $rating = (string) ($model->getAttribute('rating') ?? '');
                        $rating = self::applyRatingRules($rating, $rules['rating']);
                        $model->setAttribute('rating', $rating !== '' ? $rating : null);
                    }

                    if (isset($rules['subject']) && is_array($rules['subject'])) {
                        $subject = (string) ($model->getAttribute('subject') ?? '');
                        $subject = self::applyStringLengthRules($subject, $rules['subject']);
                        $model->setAttribute('subject', $subject !== '' ? $subject : null);
                    }

                    if (isset($rules['description']) && is_array($rules['description'])) {
                        $description = (string) ($model->getAttribute('description') ?? '');
                        $description = self::applyStringLengthRules($description, $rules['description']);
                        $model->setAttribute('description', $description !== '' ? $description : null);
                    }
                }

                $model->setAttribute('status', $status);
                $model->setAttribute('priority', $priority);
                $model->setAttribute('progress', max(0.0, min(100.0, $progress)));

                $model->setAttribute(PJC::COL_S_DT, $start?->toDateString());
                $model->setAttribute(PJC::COL_E_DT, $end?->toDateString());

                $model->setAttribute('metrics', array_values($metrics));
                $model->setAttribute('attachments', array_values($attachments));
                $model->setAttribute('tags', array_values($tags));
                $model->setAttribute('steps', array_values($steps));
                $model->setAttribute('metadata', $metadata);

                $model->ensureJsonAttributesAreEncoded([
                    'metrics',
                    'attachments',
                    'tags',
                    'steps',
                    'metadata',
                ]);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed during saving normalization', [
                    'error' => $e->getMessage(),
                    'id'    => $model->getAttribute('id'),
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function goalType(): BelongsTo
    {
        return $this->belongsTo(GoalTypeModel::class, PJC::COL_GL_TP, 'id');
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getStatusEnumAttribute(): EvaluationStatus
    {
        try {
            return EvaluationStatus::normalize((string) ($this->getAttribute('status') ?? ''));
        } catch (\Throwable) {
            return EvaluationStatus::normalize(null);
        }
    }

    public function getPriorityEnumAttribute(): PriorityLevel
    {
        try {
            return PriorityLevel::normalize((string) ($this->getAttribute('priority') ?? ''));
        } catch (\Throwable) {
            return PriorityLevel::normalize(null);
        }
    }

    public function getDurationDaysAttribute(): ?int
    {
        $start = $this->getAttribute(PJC::COL_S_DT);
        $end   = $this->getAttribute(PJC::COL_E_DT);

        if (!$start instanceof Carbon || !$end instanceof Carbon) return null;

        return $start->diffInDays($end) + 1;
    }

    public function getProgressClampedAttribute(): float
    {
        $value = $this->getAttribute('progress');
        $num   = is_numeric($value) ? (float) $value : 0.0;

        return max(0.0, min(100.0, $num));
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfGoal(Builder $query, string $goalId): Builder
    {
        return $query->where('goal', trim($goalId));
    }

    public function scopeOfGoalType(Builder $query, string $goalTypeId): Builder
    {
        return $query->where(PJC::COL_GL_TP, trim($goalTypeId));
    }

    public function scopeBetweenDates(
        Builder $query,
        string|\DateTimeInterface $start,
        string|\DateTimeInterface $end
    ): Builder {
        $startDate = $start instanceof \DateTimeInterface ? Carbon::instance($start) : Carbon::parse((string) $start);
        $endDate   = $end instanceof \DateTimeInterface ? Carbon::instance($end) : Carbon::parse((string) $end);

        return $query->whereBetween(PJC::COL_S_DT, [$startDate->toDateString(), $endDate->toDateString()]);
    }

    /*
    |--------------------------------------------------------------------------
    | AGGREGATION HELPERS
    |--------------------------------------------------------------------------
    */

    public static function aggregateAverageProgressForGoal(string $goalId): array
    {
        $goalId = trim($goalId);
        if ($goalId === '') return ['count' => 0, 'average' => 0.0];

        try {
            $row = static::query()
                ->ofGoal($goalId)
                ->selectRaw('COUNT(*) as aggregate_count, AVG(progress) as aggregate_avg')
                ->first();

            return [
                'count'   => (int) ($row->aggregate_count ?? 0),
                'average' => (float) ($row->aggregate_avg ?? 0.0),
            ];
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to aggregate average progress for goal', [
                'error' => $e->getMessage(),
                'goal'  => $goalId,
            ]);

            return ['count' => 0, 'average' => 0.0];
        }
    }

    protected static function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) return $value->copy()->startOfDay();
        if ($value instanceof \DateTimeInterface) return Carbon::instance($value)->startOfDay();

        $string = trim((string) $value);
        if ($string === '') return null;

        try {
            return Carbon::parse($string)->startOfDay();
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed to parse date', [
                'value' => $string,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected static function normalizeMetricsArray(array $metrics): array
    {
        $result = [];

        foreach ($metrics as $metric) {
            if (!is_array($metric)) continue;

            $clean = [];

            foreach (['key', 'label', 'target', 'unit', 'weight', 'direction'] as $field) {
                if (!array_key_exists($field, $metric)) continue;

                $value = $metric[$field];

                if (in_array($field, ['key', 'label', 'unit', 'direction'], true)) {
                    if (!is_string($value)) continue;
                    $value = trim($value);
                    if ($value === '') continue;
                    $clean[$field] = $value;
                } elseif (in_array($field, ['target', 'weight'], true)) {
                    if (!is_numeric($value)) continue;
                    $clean[$field] = (float) $value;
                }
            }

            if ($clean !== []) $result[] = $clean;
        }

        return $result;
    }

    protected static function sanitizeAttachmentsArray(array $attachments): array
    {
        $result = [];

        foreach ($attachments as $attachment) {
            if (!is_array($attachment)) continue;

            $path = $attachment['path'] ?? $attachment['file_path'] ?? null;
            $name = $attachment['name'] ?? $attachment['file_name'] ?? null;
            $mime = $attachment['mime'] ?? $attachment['mime_type'] ?? null;
            $size = $attachment['size'] ?? $attachment['file_size'] ?? null;

            $clean = [];

            if (is_string($path) && trim($path) !== '') $clean['path'] = trim($path);
            if (is_string($name) && trim($name) !== '') $clean['name'] = trim($name);
            if (is_string($mime) && trim($mime) !== '') $clean['mime'] = trim($mime);
            if (is_numeric($size)) $clean['size'] = (int) $size;

            if ($clean !== []) $result[] = $clean;
        }

        return $result;
    }

    protected static function sanitizeTagsArray(array $tags): array
    {
        return collect($tags)
            ->filter(fn($t) => is_string($t) && trim($t) !== '')
            ->map(fn($t) => Str::slug((string) $t, '_'))
            ->unique()
            ->values()
            ->all();
    }

    protected static function sanitizeStepsArray(array $steps): array
    {
        $result = [];

        foreach ($steps as $step) {
            if (is_string($step)) {
                $label = trim($step);
                if ($label === '') continue;
                $result[] = ['label' => $label];
                continue;
            }

            if (!is_array($step)) continue;

            $clean = [];

            foreach (['label', 'description', 'status'] as $field) {
                if (!array_key_exists($field, $step)) continue;
                $value = $step[$field];
                if (!is_string($value)) continue;
                $value = trim($value);
                if ($value === '') continue;
                $clean[$field] = $value;
            }

            if ($clean !== []) $result[] = $clean;
        }

        return $result;
    }

    protected static function applyStatusRules(string $status, array $rules): string
    {
        $allowed = [];

        if (isset($rules['allowed']) && is_array($rules['allowed'])) {
            foreach ($rules['allowed'] as $value) {
                if (!is_string($value)) continue;
                $trim = trim($value);
                if ($trim === '') continue;
                try {
                    $allowed[] = EvaluationStatus::normalize($trim)->value;
                } catch (\Throwable) {
                    $allowed[] = $trim;
                }
            }
            $allowed = array_values(array_unique($allowed));
        }

        $default = $status;

        if (isset($rules['default']) && is_string($rules['default']) && $rules['default'] !== '') {
            try {
                $default = EvaluationStatus::normalize($rules['default'])->value;
            } catch (\Throwable) {
                $default = $rules['default'];
            }
        }

        if ($allowed !== [] && !in_array($status, $allowed, true))
            $status = $default !== '' ? $default : $allowed[0];

        return $status;
    }

    protected static function applyPriorityRules(string $priority, array $rules): string
    {
        $allowed = [];

        if (isset($rules['allowed']) && is_array($rules['allowed'])) {
            foreach ($rules['allowed'] as $value) {
                if (!is_string($value)) continue;
                $trim = trim($value);
                if ($trim === '') continue;
                try {
                    $allowed[] = PriorityLevel::normalize($trim)->value;
                } catch (\Throwable) {
                    $allowed[] = $trim;
                }
            }
            $allowed = array_values(array_unique($allowed));
        }

        $default = $priority;

        if (isset($rules['default']) && is_string($rules['default']) && $rules['default'] !== '') {
            try {
                $default = PriorityLevel::normalize($rules['default'])->value;
            } catch (\Throwable) {
                $default = $rules['default'];
            }
        }

        if ($allowed !== [] && !in_array($priority, $allowed, true))
            $priority = $default !== '' ? $default : $allowed[0];

        return $priority;
    }

    protected static function applyProgressRules(
        float $progress,
        array $rules,
        ?float $original
    ): float {
        if (isset($rules['min']) && is_numeric($rules['min'])) {
            $min = (float) $rules['min'];
            if ($progress < $min) $progress = $min;
        }

        if (isset($rules['max']) && is_numeric($rules['max'])) {
            $max = (float) $rules['max'];
            if ($progress > $max) $progress = $max;
        }

        if (
            isset($rules['allow_decrease'])
            && $rules['allow_decrease'] === false
            && $original !== null
            && $progress < $original
        ) $progress = $original;

        return max(0.0, min(100.0, $progress));
    }

    protected static function applyDateBoundaryRules(?Carbon $date, array $rules, string $field): ?Carbon
    {
        if ($date === null) {
            if (!empty($rules['required'])) $date = Carbon::now()->startOfDay();
            else return null;
        }

        if (isset($rules['earliest']) && is_string($rules['earliest'])) {
            $earliest = self::parseDate($rules['earliest']);
            if ($earliest && $date->lessThan($earliest)) $date = $earliest;
        }

        if (isset($rules['latest']) && is_string($rules['latest'])) {
            $latest = self::parseDate($rules['latest']);
            if ($latest && $date->greaterThan($latest)) $date = $latest;
        }

        if (array_key_exists('allow_past', $rules) && !$rules['allow_past']) {
            $now = Carbon::now()->startOfDay();
            if ($date->lessThan($now)) $date = $now;
        }

        return $date;
    }

    protected static function applyPeriodRules(Carbon $start, Carbon $end, array $rules): array
    {
        if (isset($rules['max_days']) && is_numeric($rules['max_days'])) {
            $maxDays = (int) $rules['max_days'];
            if ($maxDays > 0) {
                $diff = $start->diffInDays($end);
                if ($diff > $maxDays) $end = $start->copy()->addDays($maxDays);
            }
        }

        return [$start, $end];
    }

    protected static function applyMetricRules(array $metrics, array $rules): array
    {
        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && count($metrics) > $maxCount)
                $metrics = array_slice($metrics, 0, $maxCount);
        }

        if (isset($rules['required_keys']) && is_array($rules['required_keys'])) {
            $required = [];

            foreach ($rules['required_keys'] as $key) {
                if (!is_string($key)) continue;
                $key = trim($key);
                if ($key === '') continue;
                $required[] = $key;
            }

            if ($required !== []) {
                foreach ($metrics as $idx => $metric) {
                    if (!is_array($metric)) continue;
                    foreach ($required as $key) {
                        if (array_key_exists($key, $metric)) continue;
                        Log::warning(static::class . ' metric missing required key', [
                            'index' => $idx,
                            'key'   => $key,
                        ]);
                    }
                }
            }
        }

        return $metrics;
    }

    protected static function applyAttachmentRules(array $attachments, array $rules): array
    {
        if (isset($rules['accept_mimes']) && is_array($rules['accept_mimes'])) {
            $allowed = [];

            foreach ($rules['accept_mimes'] as $pattern) {
                if (!is_string($pattern)) continue;
                $pattern = trim(strtolower($pattern));
                if ($pattern === '') continue;
                $allowed[] = $pattern;
            }

            if ($allowed !== []) {
                $attachments = array_values(array_filter(
                    $attachments,
                    function ($attachment) use ($allowed): bool {
                        if (!is_array($attachment)) return false;
                        $mime = $attachment['mime'] ?? '';
                        if (!is_string($mime) || $mime === '') return false;

                        $mime = strtolower($mime);

                        foreach ($allowed as $pattern) {
                            if (str_ends_with($pattern, '/*')) {
                                $prefix = rtrim($pattern, '*');
                                if (str_starts_with($mime, rtrim($prefix, '/'))) return true;
                            } elseif ($mime === $pattern) return true;
                        }

                        return false;
                    }
                ));
            }
        }

        if (isset($rules['max_size_kb']) && is_numeric($rules['max_size_kb'])) {
            $maxBytes = (float) $rules['max_size_kb'] * 1024;
            $attachments = array_values(array_filter(
                $attachments,
                static function ($attachment) use ($maxBytes): bool {
                    if (!is_array($attachment)) return false;
                    if (!array_key_exists('size', $attachment)) return true;

                    $size = $attachment['size'];
                    if (!is_numeric($size)) return false;

                    return (float) $size <= $maxBytes;
                }
            ));
        }

        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && count($attachments) > $maxCount)
                $attachments = array_slice($attachments, 0, $maxCount);
        }

        return $attachments;
    }

    protected static function applyTagRules(array $tags, array $rules): array
    {
        if (isset($rules['only']) && is_array($rules['only'])) {
            $allowed = [];

            foreach ($rules['only'] as $tag) {
                if (!is_string($tag)) continue;
                $tag = Str::slug($tag, '_');
                if ($tag === '') continue;
                $allowed[] = $tag;
            }

            if ($allowed !== [])
                $tags = array_values(array_intersect($tags, array_unique($allowed)));
        }

        if (isset($rules['except']) && is_array($rules['except'])) {
            $blocked = [];

            foreach ($rules['except'] as $tag) {
                if (!is_string($tag)) continue;
                $tag = Str::slug($tag, '_');
                if ($tag === '') continue;
                $blocked[] = $tag;
            }

            if ($blocked !== [])
                $tags = array_values(array_diff($tags, array_unique($blocked)));
        }

        if (isset($rules['prefix']) && is_string($rules['prefix'])) {
            $prefix = Str::slug($rules['prefix'], '_');
            if ($prefix !== '') {
                $tags = array_map(
                    static fn(string $tag) => Str::startsWith($tag, $prefix . '_')
                        ? $tag
                        : $prefix . '_' . $tag,
                    $tags
                );
            }
        }

        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && count($tags) > $maxCount)
                $tags = array_slice($tags, 0, $maxCount);
        }

        return $tags;
    }

    protected static function applyStepRules(array $steps, array $rules): array
    {
        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && count($steps) > $maxCount)
                $steps = array_slice($steps, 0, $maxCount);
        }

        return $steps;
    }

    protected static function applyRatingRules(string $rating, array $rules): string
    {
        if ($rating === '') return '';

        if (!empty($rules['numeric'])) {
            if (!is_numeric($rating)) return '';
            $value = (float) $rating;

            if (isset($rules['min']) && is_numeric($rules['min'])) {
                $min = (float) $rules['min'];
                if ($value < $min) $value = $min;
            }

            if (isset($rules['max']) && is_numeric($rules['max'])) {
                $max = (float) $rules['max'];
                if ($value > $max) $value = $max;
            }

            return (string) $value;
        }

        if (isset($rules['max_length']) && is_numeric($rules['max_length'])) {
            $max = (int) $rules['max_length'];
            if ($max > 0 && mb_strlen($rating) > $max)
                $rating = mb_substr($rating, 0, $max);
        }

        return $rating;
    }

    protected static function applyStringLengthRules(string $value, array $rules): string
    {
        $value = trim($value);
        if ($value === '') return '';

        if (!empty($rules['required']) && $value === '') return '';

        if (isset($rules['max_length']) && is_numeric($rules['max_length'])) {
            $max = (int) $rules['max_length'];
            if ($max > 0 && mb_strlen($value) > $max)
                $value = mb_substr($value, 0, $max);
        }

        return $value;
    }
}
