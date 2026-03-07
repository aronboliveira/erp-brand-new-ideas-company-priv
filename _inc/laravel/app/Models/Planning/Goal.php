<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\GoalType as GoalTypeEnum;
use App\Services\GoalRequestService;
use App\Traits\{HasAuditFields, NormalizesArrays, PlansWithSchedule, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\{DB, Log};

class Goal extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays, PlansWithSchedule;

    protected $table = DC::TABLE_GL;

    private const LEGACY_GOAL_TYPES = [
        'Invoice',
        'Bill',
        'Revenue',
        'Payment',
    ];

    public static array $goalType = [];

    protected $fillable = [
        'name',
        'type',
        'from',
        'to',
        'amount',
        'description',
        PJC::COL_IS_DSP,
        'metrics',
        'trackings',
        'leader',
        'involved',
        'sponsors',
        'stakeholders',
        'tags',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        PJC::COL_IS_DSP => 'boolean',
        'metrics'       => 'array',
        'trackings'     => 'array',
        'involved'      => 'array',
        'sponsors'      => 'array',
        'stakeholders'  => 'array',
        'tags'          => 'array',
        'from'          => 'datetime',
        'to'            => 'datetime',
        DC::COL_C_AT    => 'datetime',
        DC::COL_U_AT    => 'datetime',
    ];

    protected $with = [
        'leader',
    ];

    protected $appends = [
        'type_enum',
        'period',
        'has_amount',
    ];

    public static function booted(): void
    {
        if (self::$goalType === []) {
            try {
                $enumValues = method_exists(GoalTypeEnum::class, 'values')
                    ? GoalTypeEnum::values()
                    : array_map(static fn(GoalTypeEnum $c) => $c->value, GoalTypeEnum::cases());

                self::$goalType = array_values(array_unique([
                    ...self::LEGACY_GOAL_TYPES,
                    ...$enumValues,
                ]));
            } catch (\Throwable $e) {
                self::$goalType = self::LEGACY_GOAL_TYPES;
                Log::warning(static::class . ' failed to hydrate goalType list from enum', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        static::saving(function (self $model): void {
            try {
                $normalizedType = GoalTypeEnum::normalize(
                    (string) ($model->getAttribute('type') ?? '')
                );
                $model->setAttribute('type', $normalizedType->value);

                $from = self::parseDateTimeOrNull($model->getAttribute('from'));
                $to   = self::parseDateTimeOrNull($model->getAttribute('to'));

                if ($from && $to && $to->lessThan($from)) {
                    $tmp = $from;
                    $from = $to;
                    $to   = $tmp;
                }

                $amountRaw = $model->getAttribute('amount');
                $amount    = is_numeric($amountRaw) ? (float) $amountRaw : 0.0;
                if ($amount < 0) {
                    $amount = 0.0;
                }

                $isDisplayRaw = $model->getAttribute(PJC::COL_IS_DSP);
                $isDisplay    = (bool) $isDisplayRaw;

                $metrics      = self::normalizeArrayField($model->getAttribute('metrics'));
                $trackings    = self::normalizeArrayField($model->getAttribute('trackings'));
                $involved     = self::normalizeArrayField($model->getAttribute('involved'));
                $sponsors     = self::normalizeArrayField($model->getAttribute('sponsors'));
                $stakeholders = self::normalizeArrayField($model->getAttribute('stakeholders'));
                $tags         = self::normalizeArrayField($model->getAttribute('tags'));

                $metrics = self::normalizeMetricsArray($metrics);
                $trackings = self::normalizeUuidList($trackings);

                $leader = $model->getAttribute('leader');
                $leader = is_string($leader) ? trim($leader) : (string) $leader;
                if ($leader === '' || !Utility::looksLikeUuid($leader)) {
                    $leader = null;
                } else {
                    if (class_exists(Employee::class)) {
                        try {
                            $exists = Employee::query()->whereKey($leader)->exists();
                            if (!$exists) {
                                Log::debug(static::class . ' leader does not exist, nulling', [
                                    'leader_id' => $leader,
                                ]);
                                $leader = null;
                            }
                        } catch (\Throwable $e) {
                            Log::warning(static::class . ' failed to validate leader', [
                                'leader_id' => $leader,
                                'error'     => $e->getMessage(),
                            ]);
                        }
                    }
                }

                $involved = self::normalizeUuidList($involved);
                if ($leader !== null && !in_array($leader, $involved, true)) {
                    $involved[] = $leader;
                }

                $sponsors = self::normalizeUuidList($sponsors);
                if (class_exists(User::class)) {
                    try {
                        $sponsors = User::query()
                            ->whereIn('id', $sponsors)
                            ->where('type', 'company')
                            ->pluck('id')
                            ->map(static fn($id) => (string) $id)
                            ->all();
                    } catch (\Throwable $e) {
                        Log::warning(static::class . ' failed to filter sponsors', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $stakeholders = self::normalizeUuidList($stakeholders);
                $stakeholders = self::resolveStakeholders($stakeholders);

                $tags = collect($tags)
                    ->filter(static fn($t) => is_string($t) && trim($t) !== '')
                    ->map(static fn($t) => Str::slug((string) $t, '_'))
                    ->unique()
                    ->values()
                    ->all();

                $rules = null;
                if (class_exists(GoalType::class)) {
                    try {
                        $definition = GoalType::query()
                            ->where('category', $normalizedType->value)
                            ->first();

                        if ($definition) {
                            $rawRules = $definition->getAttribute('rules');
                            if (is_array($rawRules))
                                $rules = $rawRules;
                            elseif (is_string($rawRules)) {
                                $decoded = json_decode($rawRules, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                                    $rules = $decoded;
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning(static::class . ' failed to resolve goal type rules', [
                            'error' => $e->getMessage(),
                            'type'  => $normalizedType->value,
                        ]);
                    }
                }

                if (is_array($rules) && $rules !== []) {
                    if (isset($rules['amount']) && is_array($rules['amount']))
                        [$amount] = self::applyAmountRules($amount, $rules['amount']);
                    if (isset($rules['from']) && is_array($rules['from']))
                        $from = self::applyDateBoundaryRules($from, $rules['from'], 'from');
                    if (isset($rules['to']) && is_array($rules['to']))
                        $to = self::applyDateBoundaryRules($to, $rules['to'], 'to');
                    if (isset($rules['period']) && is_array($rules['period']) && $from && $to)
                        [$from, $to] = self::applyPeriodRules($from, $to, $rules['period']);
                    if (isset($rules['metrics']) && is_array($rules['metrics']))
                        $metrics = self::applyMetricRules($metrics, $rules['metrics']);
                    if (isset($rules['trackings']) && is_array($rules['trackings']))
                        $trackings = self::applyTrackingRules($trackings, $rules['trackings']);
                    if (isset($rules['involved']) && is_array($rules['involved'])) {
                        $involved = self::applyParticipantRules($involved, $rules['involved'], 'involved');
                        if ($leader !== null && !in_array($leader, $involved, true))
                            $involved[] = $leader;
                    }
                    if (isset($rules['sponsors']) && is_array($rules['sponsors']))
                        $sponsors = self::applyParticipantRules($sponsors, $rules['sponsors'], 'sponsors');
                    if (isset($rules['stakeholders']) && is_array($rules['stakeholders']))
                        $stakeholders = self::applyParticipantRules($stakeholders, $rules['stakeholders'], 'stakeholders');
                    if (isset($rules['tags']) && is_array($rules['tags']))
                        $tags = self::applyTagRules($tags, $rules['tags']);
                }

                $model->setAttribute('type', $normalizedType->value);
                $model->setAttribute('amount', $amount);
                $model->setAttribute(PJC::COL_IS_DSP, $isDisplay);
                $model->setAttribute('leader', $leader);

                $model->setAttribute('from', $from?->copy());
                $model->setAttribute('to', $to?->copy());

                $model->setAttribute('metrics', array_values($metrics));
                $model->setAttribute('trackings', array_values(array_unique($trackings)));
                $model->setAttribute('involved', array_values(array_unique($involved)));
                $model->setAttribute('sponsors', array_values(array_unique($sponsors)));
                $model->setAttribute('stakeholders', array_values(array_unique($stakeholders)));
                $model->setAttribute('tags', array_values($tags));

                $model->ensureJsonAttributesAreEncoded([
                    'metrics',
                    'trackings',
                    'involved',
                    'sponsors',
                    'stakeholders',
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

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader', 'id');
    }

    public function getTypeEnumAttribute(): GoalTypeEnum
    {
        return GoalTypeEnum::normalize((string) ($this->getAttribute('type') ?? ''));
    }

    public function getPeriodAttribute(): ?array
    {
        $from = $this->getAttribute('from');
        $to   = $this->getAttribute('to');

        if (!$from instanceof Carbon && !$to instanceof Carbon) {
            return null;
        }

        return [
            'from' => $from instanceof Carbon ? $from->toDateTimeString() : null,
            'to'   => $to instanceof Carbon ? $to->toDateTimeString() : null,
        ];
    }

    public function getHasAmountAttribute(): bool
    {
        $value = $this->getAttribute('amount');
        return is_numeric($value) && (float) $value > 0.0;
    }

    public function target(string $type, string $from, string $to, float $amount): array|RedirectResponse
    {
        return app(GoalRequestService::class)->calculateTarget(
            $type,
            $from,
            $to,
            $amount
        );
    }

    public static function parseDateTimeOrNull(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        try {
            return Carbon::parse($string);
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed to parse datetime', [
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
            if (!is_array($metric)) {
                continue;
            }

            $clean = [];

            foreach (['key', 'label', 'target', 'unit', 'weight', 'direction'] as $field) {
                if (!array_key_exists($field, $metric)) {
                    continue;
                }

                $value = $metric[$field];

                if (in_array($field, ['key', 'label', 'unit', 'direction'], true)) {
                    if (!is_string($value)) {
                        continue;
                    }
                    $value = trim($value);
                    if ($value === '') {
                        continue;
                    }
                    $clean[$field] = $value;
                } elseif (in_array($field, ['target', 'weight'], true)) {
                    if (!is_numeric($value)) {
                        continue;
                    }
                    $clean[$field] = (float) $value;
                }
            }

            if ($clean !== []) {
                $result[] = $clean;
            }
        }

        return $result;
    }

    protected static function normalizeUuidList(array $values): array
    {
        $ids = [];

        foreach ($values as $value) {
            if (is_array($value) && isset($value['id']))
                $value = $value['id'];
            $string = trim((string) $value);
            if ($string === '' || !Utility::looksLikeUuid($string))
                continue;
            $ids[] = $string;
        }

        return array_values(array_unique($ids));
    }

    protected static function resolveStakeholders(array $ids): array
    {
        if ($ids === [])
            return [];
        $resolved = [];
        if (class_exists(Employee::class)) {
            try {
                $employeeIds = Employee::query()
                    ->whereIn('id', $ids)
                    ->pluck('id')
                    ->map(static fn($id) => (string) $id)
                    ->all();

                $resolved = [...$resolved, ...$employeeIds];
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to resolve stakeholder employees', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (class_exists(User::class)) {
            try {
                $userIds = User::query()
                    ->whereIn('id', $ids)
                    ->whereIn('type', ['company', 'admin', 'super admin'])
                    ->pluck('id')
                    ->map(static fn($id) => (string) $id)
                    ->all();

                $resolved = [...$resolved, ...$userIds];
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to resolve stakeholder users', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($resolved === []) {
            return $ids;
        }

        return array_values(array_unique($resolved));
    }

    protected static function applyAmountRules(float $amount, array $rules): array
    {
        if (isset($rules['min']) && is_numeric($rules['min'])) {
            $min = (float) $rules['min'];
            if ($amount < $min) {
                $amount = $min;
            }
        }

        if (isset($rules['max']) && is_numeric($rules['max'])) {
            $max = (float) $rules['max'];
            if ($amount > $max) {
                $amount = $max;
            }
        }

        return [$amount];
    }

    protected static function applyDateBoundaryRules(?Carbon $date, array $rules, string $field): ?Carbon
    {
        if ($date === null) {
            if (!empty($rules['required'])) {
                $date = Carbon::now();
            } else {
                return null;
            }
        }

        if (isset($rules['earliest']) && is_string($rules['earliest'])) {
            $earliest = self::parseDateTimeOrNull($rules['earliest']);
            if ($earliest && $date->lessThan($earliest)) {
                $date = $earliest;
            }
        }

        if (isset($rules['latest']) && is_string($rules['latest'])) {
            $latest = self::parseDateTimeOrNull($rules['latest']);
            if ($latest && $date->greaterThan($latest)) {
                $date = $latest;
            }
        }

        if (array_key_exists('allow_past', $rules) && !$rules['allow_past']) {
            $now = Carbon::now();
            if ($date->lessThan($now)) {
                $date = $now;
            }
        }

        return $date;
    }

    protected static function applyPeriodRules(Carbon $from, Carbon $to, array $rules): array
    {
        if (isset($rules['max_days']) && is_numeric($rules['max_days'])) {
            $maxDays = (int) $rules['max_days'];
            if ($maxDays > 0) {
                $diff = $from->diffInDays($to);
                if ($diff > $maxDays) {
                    $to = $from->copy()->addDays($maxDays);
                }
            }
        }

        return [$from, $to];
    }

    protected static function applyMetricRules(array $metrics, array $rules): array
    {
        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && \count($metrics) > $maxCount) {
                $metrics = \array_slice($metrics, 0, $maxCount);
            }
        }

        if (isset($rules['required_keys']) && is_array($rules['required_keys'])) {
            $required = [];
            foreach ($rules['required_keys'] as $key) {
                if (is_string($key) && $key !== '') {
                    $required[] = $key;
                }
            }

            if ($required !== []) {
                foreach ($metrics as $idx => $metric) {
                    if (!is_array($metric)) {
                        continue;
                    }
                    foreach ($required as $key) {
                        if (!array_key_exists($key, $metric)) {
                            Log::warning(static::class . ' metric missing required key', [
                                'index' => $idx,
                                'key'   => $key,
                            ]);
                        }
                    }
                }
            }
        }

        return $metrics;
    }

    protected static function applyTrackingRules(array $trackings, array $rules): array
    {
        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && \count($trackings) > $maxCount) {
                $trackings = \array_slice($trackings, 0, $maxCount);
            }
        }

        if (isset($rules['must_exist']) && $rules['must_exist'] && $trackings !== []) {
            if (class_exists(GoalTracking::class)) {
                try {
                    $valid = GoalTracking::query()
                        ->whereIn('id', $trackings)
                        ->pluck('id')
                        ->map(static fn($id) => (string) $id)
                        ->all();

                    $trackings = $valid ?: $trackings;
                } catch (\Throwable $e) {
                    Log::warning(static::class . ' failed to validate goal trackings', [
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                try {
                    if (DB::getSchemaBuilder()->hasTable(DC::TABLE_GL_TRK)) {
                        $valid = DB::table(DC::TABLE_GL_TRK)
                            ->whereIn('id', $trackings)
                            ->pluck('id')
                            ->map(static fn($id) => (string) $id)
                            ->all();

                        $trackings = $valid ?: $trackings;
                    }
                } catch (\Throwable $e) {
                    Log::warning(static::class . ' failed to validate goal trackings via DB', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $trackings;
    }

    protected static function applyParticipantRules(array $ids, array $rules, string $field): array
    {
        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && \count($ids) > $maxCount) {
                $ids = \array_slice($ids, 0, $maxCount);
            }
        }

        if (isset($rules['only']) && is_array($rules['only'])) {
            $allowed = [];
            foreach ($rules['only'] as $id) {
                $id = trim((string) $id);
                if ($id !== '' && Utility::looksLikeUuid($id)) {
                    $allowed[] = $id;
                }
            }

            if ($allowed !== []) {
                $ids = array_values(
                    array_intersect($ids, array_unique($allowed))
                );
            }
        }

        if (isset($rules['except']) && is_array($rules['except'])) {
            $blocked = [];
            foreach ($rules['except'] as $id) {
                $id = trim((string) $id);
                if ($id !== '' && Utility::looksLikeUuid($id)) {
                    $blocked[] = $id;
                }
            }

            if ($blocked !== []) {
                $ids = array_values(
                    array_diff($ids, array_unique($blocked))
                );
            }
        }

        return $ids;
    }

    protected static function applyTagRules(array $tags, array $rules): array
    {
        if (isset($rules['max_count']) && is_numeric($rules['max_count'])) {
            $maxCount = (int) $rules['max_count'];
            if ($maxCount > 0 && \count($tags) > $maxCount) {
                $tags = \array_slice($tags, 0, $maxCount);
            }
        }

        if (isset($rules['prefix']) && is_string($rules['prefix'])) {
            $prefix = trim($rules['prefix']);
            if ($prefix !== '') {
                $tags = array_map(
                    static fn(string $tag) => Str::startsWith($tag, $prefix)
                        ? $tag
                        : $prefix . $tag,
                    $tags
                );
            }
        }

        return $tags;
    }
}
