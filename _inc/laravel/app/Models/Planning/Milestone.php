<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{
    EvaluationStatus,
    PriorityLevel
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Milestone extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    protected $table = DC::TABLE_MSS;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $with = ['project'];

    protected $fillable = [
        PJC::COL_PJ_ID,
        'title',
        'description',
        'priority',
        'status',
        'progress',
        'cost',
        PJC::COL_S_DT,
        PJC::COL_D_DATE,
        'metadata',
        'tags',
        'involved',
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'priority' => 'string',
        'status' => 'string',
        'progress' => 'decimal:2',
        'cost' => 'decimal:2',
        PJC::COL_S_DT => 'date',
        PJC::COL_D_DATE => 'date',
        'metadata' => 'array',
        'tags' => 'array',
        'involved' => 'array',
    ];

    protected $appends = [
        'priority_enum',
        'status_enum',
        'priority_label',
        'status_label',
        'is_overdue',
        'date_range_label',
        'involved_count',
    ];

    /** cache simples por request */
    private static array $cache = [
        'project_name' => [],
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, PJC::COL_ML_ID, 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureDefaults();
                $m->normalizeNumbers();
                $m->normalizeJsonFields();
                $m->validateDates();
            } catch (\Throwable $ex) {
                Log::error(static::class . ' saving() failed', [
                    'id' => (string) ($m->getAttribute('id') ?? ''),
                    'error' => $ex->getMessage(),
                ]);
                throw $ex;
            }
        });
    }

    private function ensureDefaults(): void
    {
        $title = trim((string) ($this->getAttribute('title') ?? ''));
        if ($title === '') $this->setAttribute('title', $this->makeDefaultTitle());

        $priority = trim((string) ($this->getAttribute('priority') ?? ''));
        if ($priority === '') $this->setAttribute('priority', PriorityLevel::Medium->value);
        else $this->setAttribute('priority', $this->normalizeEnumValueSafe(PriorityLevel::class, $priority, 'Medium'));

        $status = trim((string) ($this->getAttribute('status') ?? ''));
        if ($status === '') $this->setAttribute('status', EvaluationStatus::Pending->value);
        else $this->setAttribute('status', $this->normalizeEnumValueSafe(EvaluationStatus::class, $status, 'Pending'));
    }

    private function makeDefaultTitle(): string
    {
        $pj = (string) ($this->getAttribute(PJC::COL_PJ_ID) ?? '');
        $due = $this->getAttribute(PJC::COL_D_DATE);

        $pjName = $this->getProjectNameCached($pj) ?: ($pj !== '' ? mb_substr($pj, 0, 8) : 'project');
        $dueStr = $due instanceof Carbon ? $due->toDateString() : (is_string($due) && trim($due) !== '' ? trim($due) : 'no-date');

        return mb_substr("Milestone — {$pjName} — {$dueStr}", 0, 255);
    }

    private function normalizeNumbers(): void
    {
        // cost: >= 0
        $rawCost = $this->getAttribute('cost');
        $cost = is_numeric($rawCost) ? (float) $rawCost : 0.0;
        if ($cost < 0) $cost = 0.0;
        $this->setAttribute('cost', $cost);

        // progress: 0..100 (decimal 2)
        $rawProg = $this->getAttribute('progress');
        $prog = is_numeric($rawProg) ? (float) $rawProg : 0.0;
        if ($prog < 0) $prog = 0.0;
        if ($prog > 100) $prog = 100.0;
        $this->setAttribute('progress', round($prog, 2));
    }

    private function normalizeJsonFields(): void
    {
        $this->setAttribute('metadata', self::normalizeArrayField($this->getAttribute('metadata')));
        $this->setAttribute('tags', self::normalizeArrayField($this->getAttribute('tags')));
        $this->setAttribute('involved', $this->filterInvolved(self::normalizeArrayField($this->getAttribute('involved'))));
    }

    private function filterInvolved(array $value): array
    {
        if (empty($value)) return [];

        $out = [];
        foreach ($value as $v) {
            if (is_string($v)) {
                $t = trim($v);
                if ($t !== '') $out[] = $t;
                continue;
            }
            if (is_numeric($v)) $out[] = (string) $v;
            if (is_array($v) || is_object($v)) $out[] = (array) $v;
        }

        $out = array_values(array_unique($out, SORT_REGULAR));
        return array_slice($out, 0, 512);
    }

    private function validateDates(): void
    {
        $s = $this->getAttribute(PJC::COL_S_DT);
        $d = $this->getAttribute(PJC::COL_D_DATE);

        if (!$s || !$d) return;

        try {
            $sd = $s instanceof Carbon ? $s : Carbon::parse((string) $s);
            $dd = $d instanceof Carbon ? $d : Carbon::parse((string) $d);

            if ($dd->lt($sd))
                throw \Illuminate\Validation\ValidationException::withMessages([
                    PJC::COL_D_DATE => 'due_date não pode ser menor que start_date.',
                ]);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            Log::warning(static::class . ' failed to validate dates', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'start' => (string) ($s ?? ''),
                'due' => (string) ($d ?? ''),
                'error' => $ex->getMessage(),
            ]);
        }
    }

    /* -----------------------------
	| Accessors / Appends
	------------------------------ */

    public function getPriorityEnumAttribute(): PriorityLevel
    {
        $v = (string) ($this->getAttribute('priority') ?? '');
        return PriorityLevel::tryFrom($v) ?? PriorityLevel::Medium;
    }

    public function getStatusEnumAttribute(): EvaluationStatus
    {
        $v = (string) ($this->getAttribute('status') ?? '');
        return EvaluationStatus::tryFrom($v) ?? EvaluationStatus::Pending;
    }

    public function getPriorityLabelAttribute(): string
    {
        return $this->resolveEnumLabelSafe(PriorityLevel::class, (string) ($this->getAttribute('priority') ?? ''));
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->resolveEnumLabelSafe(EvaluationStatus::class, (string) ($this->getAttribute('status') ?? ''));
    }

    public function getIsOverdueAttribute(): bool
    {
        $due = $this->getAttribute(PJC::COL_D_DATE);
        if (!$due) return false;

        try {
            $dd = $due instanceof Carbon ? $due : Carbon::parse((string) $due);
            $progress = (float) ($this->getAttribute('progress') ?? 0);
            $status = strtolower((string) ($this->getAttribute('status') ?? ''));

            // heurística defensiva: considera "done/completed/complete" como finalizado se enum não expõe helpers
            $isFinished = in_array($status, ['complete', 'completed', 'done', 'terminated', 'canceled', 'cancelled'], true);
            if ($isFinished || $progress >= 100) return false;

            return $dd->isPast();
        } catch (\Throwable) {
            return false;
        }
    }

    public function getDateRangeLabelAttribute(): string
    {
        $s = $this->getAttribute(PJC::COL_S_DT);
        $d = $this->getAttribute(PJC::COL_D_DATE);

        try {
            $sd = $s instanceof Carbon ? $s : ($s ? Carbon::parse((string) $s) : null);
            $dd = $d instanceof Carbon ? $d : ($d ? Carbon::parse((string) $d) : null);

            if (!$sd && !$dd) return '';

            $left = $sd ? $sd->toDateString() : '';
            $right = $dd ? $dd->toDateString() : '';
            if ($left !== '' && $right !== '' && $left === $right) return $left;

            return trim($left . ' → ' . $right);
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed to build date_range_label', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'error' => $ex->getMessage(),
            ]);
            return '';
        }
    }

    public function getInvolvedCountAttribute(): int
    {
        return count(self::normalizeArrayField($this->getAttribute('involved')));
    }

    private function normalizeEnumValueSafe(string $enumClass, mixed $value, string $defaultCaseName): string
    {
        try {
            if (is_object($value) && $value instanceof $enumClass)
                return (string) ($value->value ?? '');

            $raw = is_scalar($value) ? trim((string) $value) : '';
            if ($raw === '') return $this->enumCaseValueByName($enumClass, $defaultCaseName)
                ?? (string) (($enumClass::cases()[0]->value) ?? '');

            if (method_exists($enumClass, 'normalize')) {
                $norm = $enumClass::normalize($raw);
                return is_object($norm) ? (string) ($norm->value ?? $raw) : $raw;
            }

            $case = method_exists($enumClass, 'tryFrom') ? $enumClass::tryFrom($raw) : null;
            if (is_object($case)) return (string) ($case->value ?? $raw);

            $allowed = array_map(fn($c) => (string) $c->value, $enumClass::cases());
            return in_array($raw, $allowed, true) ? $raw : (string) ($allowed[0] ?? $raw);
        } catch (\Throwable) {
            return $this->enumCaseValueByName($enumClass, $defaultCaseName)
                ?? (string) (($enumClass::cases()[0]->value) ?? '');
        }
    }

    private function enumCaseValueByName(string $enumClass, string $caseName): ?string
    {
        foreach ($enumClass::cases() as $c) {
            if (($c->name ?? null) === $caseName)
                return (string) ($c->value ?? null);
        }
        return null;
    }

    private function resolveEnumLabelSafe(string $enumClass, string $value, ?string $lang = null): string
    {
        $value = trim($value);
        if ($value === '') return '';

        try {
            if (method_exists($enumClass, 'labels')) {
                $labels = $enumClass::labels($lang ?? DC::DEFAULT_LANG);
                if (is_array($labels) && array_key_exists($value, $labels))
                    return (string) $labels[$value];
            }
        } catch (\Throwable) {
            Log::debug(static::class . ' failed to resolve enum labels', [
                'enum' => $enumClass,
                'value' => $value,
                'lang' => $lang,
            ]);
        }

        try {
            $case = method_exists($enumClass, 'tryFrom') ? $enumClass::tryFrom($value) : null;
            if (is_object($case) && is_callable([$case, 'label']))
                return (string) call_user_func([$case, 'label'], $lang ?? DC::DEFAULT_LANG);
        } catch (\Throwable) {
            Log::debug(static::class . ' failed to resolve enum label via case method', [
                'enum' => $enumClass,
                'value' => $value,
                'lang' => $lang,
            ]);
        }

        return $value;
    }

    public function getProjectNameCached(string $projectId): ?string
    {
        $key = trim($projectId);
        if ($key === '') return null;

        if (array_key_exists($key, self::$cache['project_name']))
            return self::$cache['project_name'][$key];

        try {
            $name = Project::query()->where('id', $key)->value(PJC::COL_NM);
            $name = is_string($name) ? trim($name) : null;

            self::$cache['project_name'][$key] = $name ?: null;
            return self::$cache['project_name'][$key];
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed to resolve project name', [
                'project_id' => $key,
                'error' => $ex->getMessage(),
            ]);
            self::$cache['project_name'][$key] = null;
            return null;
        }
    }
}
