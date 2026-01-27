<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, PermissionsConstants as PC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Services\TaskRequestService;
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Casts\Attribute, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Redirect};

class TaskStage extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use FiltersSecureAttachments;
    use NormalizesArrays;
    use DefinesDates;

    protected $table = DC::TABLE_TSK_STGS;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        AC::COL_PJ,
        'task',
        'name',
        'description',
        PJC::COL_D_DATE, // due_date

        'priority',
        'progress',
        'status',
        'complete',
        'color',
        'order',

        'responsible',
        'involved',

        'metadata',
        'attachments',
        'tags',

        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        PJC::COL_D_DATE => 'datetime',
        'priority' => 'string',
        'status' => 'string',

        'progress' => 'integer',
        'complete' => 'boolean',
        'order' => 'integer',

        'involved' => 'array',
        'metadata' => 'array',
        'attachments' => 'array',
        'tags' => 'array',
    ];

    protected $with = [
        'project',
        'taskModel',
        'responsibleUser',
    ];

    protected $appends = [
        'priority_label',
        'status_label',
        'is_overdue',
        'due_label',
        'involved_count',
        'project_name',
        'task_name',
        'responsible_name',
    ];

    private static array $cache = [
        'project_name' => [],
        'task_name' => [],
        'responsible_name' => [],
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureDefaults();
                $m->normalizeCoreFields();
                $m->normalizeJsonFields();
                $m->enforceCoherence();
            } catch (\Throwable $ex) {
                Log::error(static::class . ' saving() failed', [
                    'id' => (string) ($m->getAttribute('id') ?? ''),
                    'error' => $ex->getMessage(),
                ]);
                throw $ex;
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, AC::COL_PJ, 'id');
    }

    public function taskModel(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task', 'id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
    }

    protected function priority(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): string => $this->normalizeEnumValueSafe(PriorityLevel::class, $value, 'Medium'),
            set: fn(mixed $value): string => $this->normalizeEnumValueSafe(PriorityLevel::class, $value, 'Medium'),
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): string => $this->normalizeEnumValueSafe(EvaluationStatus::class, $value, 'Pending'),
            set: fn(mixed $value): string => $this->normalizeEnumValueSafe(EvaluationStatus::class, $value, 'Pending'),
        );
    }

    protected function progress(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): int => $this->clampInt($value, 0, 100),
            set: fn(mixed $value): int => $this->clampInt($value, 0, 100),
        );
    }

    protected function color(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): string => $this->normalizeCssColor(is_string($value) ? $value : null),
            set: fn(mixed $value): string => $this->normalizeCssColor(is_string($value) ? $value : null),
        );
    }

    public function getPriorityLabelAttribute(): string
    {
        return $this->resolveEnumLabelSafe(PriorityLevel::class, (string) ($this->getAttribute('priority') ?? ''));
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->resolveEnumLabelSafe(\App\Enums\EvaluationStatus::class, (string) ($this->getAttribute('status') ?? ''));
    }

    public function getIsOverdueAttribute(): bool
    {
        $due = $this->getAttribute(PJC::COL_D_DATE);
        if (!$due) return false;

        try {
            $dt = $due instanceof Carbon ? $due : Carbon::parse((string) $due);
            return $dt->isPast() && !$this->isComplete();
        } catch (\Throwable) {
            return false;
        }
    }

    public function getDueLabelAttribute(): string
    {
        $due = $this->getAttribute(PJC::COL_D_DATE);
        if (!$due) return '';

        try {
            $dt = $due instanceof Carbon ? $due : Carbon::parse((string) $due);
            return $dt->toDateTimeString();
        } catch (\Throwable) {
            return '';
        }
    }

    public function getInvolvedCountAttribute(): int
    {
        return count(self::normalizeArrayField($this->getAttribute('involved')));
    }

    public function getProjectNameAttribute(): ?string
    {
        return $this->resolveProjectNameCached();
    }

    public function getTaskNameAttribute(): ?string
    {
        return $this->resolveTaskNameCached();
    }

    public function getResponsibleNameAttribute(): ?string
    {
        return $this->resolveResponsibleNameCached();
    }

    public function isComplete(): bool
    {
        return (bool) ($this->getAttribute('complete') ?? false);
    }

    public function markComplete(bool $value = true): void
    {
        $this->setAttribute('complete', $value);
        if ($value) $this->setAttribute('progress', 100);
    }

    public function scopeForProject($q, ?string $projectId)
    {
        if (!$projectId) return $q;
        return $q->where(AC::COL_PJ, $projectId);
    }

    public function scopeForTask($q, ?string $taskId)
    {
        if (!$taskId) return $q;
        return $q->where('task', $taskId);
    }

    public static function getChartData(): array|RedirectResponse
    {
        return app(TaskRequestService::class)->getStageChartData();
    }

    private function ensureDefaults(): void
    {
        if ($this->getAttribute('progress') === null) $this->setAttribute('progress', 0);
        if ($this->getAttribute('complete') === null) $this->setAttribute('complete', false);

        $color = trim((string) ($this->getAttribute('color') ?? ''));
        if ($color === '') $this->setAttribute('color', '#558855');

        $priority = trim((string) ($this->getAttribute('priority') ?? ''));
        if ($priority === '') $this->setAttribute('priority', $this->normalizeEnumValueSafe(PriorityLevel::class, null, 'Medium'));

        $status = trim((string) ($this->getAttribute('status') ?? ''));
        if ($status === '') $this->setAttribute('status', $this->normalizeEnumValueSafe(EvaluationStatus::class, null, 'Pending'));

        $name = $this->getAttribute('name');
        if (is_string($name)) $this->setAttribute('name', mb_substr(trim($name), 0, 254));

        $ord = $this->getAttribute('order');
        $this->setAttribute('order', $this->clampInt($ord, 0, 65535));
    }

    private function normalizeCoreFields(): void
    {
        $desc = $this->getAttribute('description');
        if (is_string($desc)) $this->setAttribute('description', trim($desc));

        try {
            $due = $this->getAttribute(PJC::COL_D_DATE);
            if ($due && !$due instanceof Carbon) Carbon::parse((string) $due);
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' invalid due_date, nulling', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'due' => (string) ($this->getAttribute(PJC::COL_D_DATE) ?? ''),
                'error' => $ex->getMessage(),
            ]);
            $this->setAttribute(PJC::COL_D_DATE, null);
        }
    }

    private function normalizeJsonFields(): void
    {
        $this->setAttribute('metadata', self::normalizeArrayField($this->getAttribute('metadata')));
        $this->setAttribute('attachments', self::normalizeArrayField($this->getAttribute('attachments')));
        $this->setAttribute('tags', self::normalizeArrayField($this->getAttribute('tags')));

        $involved = self::normalizeArrayField($this->getAttribute('involved'));
        $this->setAttribute('involved', $this->filterInvolved($involved));
    }

    private function enforceCoherence(): void
    {
        $p = (int) ($this->getAttribute('progress') ?? 0);
        $c = (bool) ($this->getAttribute('complete') ?? false);

        if ($c && $p < 100) $this->setAttribute('progress', 100);
        if (!$c && $p >= 100) $this->setAttribute('complete', true);

        $completedValue = $this->enumCaseValueByName(EvaluationStatus::class, 'Completed');
        $status = (string) ($this->getAttribute('status') ?? '');

        if ($completedValue !== null && $status === $completedValue) {
            $this->setAttribute('complete', true);
            $this->setAttribute('progress', 100);
        }
    }

    private function filterInvolved(array $value): array
    {
        $items = [];

        foreach ($value as $v) {
            if (is_string($v)) {
                $t = trim($v);
                if ($t !== '') $items[] = $t;
                continue;
            }
            if (is_array($v)) {
                $id = $v['id'] ?? null;
                if (is_string($id) && trim($id) !== '') $items[] = trim($id);
                continue;
            }
            if (is_scalar($v)) {
                $t = trim((string) $v);
                if ($t !== '') $items[] = $t;
            }
        }

        if (empty($items)) return [];

        $uuids = [];
        $names = [];

        foreach ($items as $it) {
            if (Utility::looksLikeUuid($it)) $uuids[] = $it;
            else $names[] = $it;
        }

        $valid = [];

        if (!empty($uuids)) {
            $uuids = array_values(array_unique($uuids));

            try {
                $existingUsers = DB::table(DC::TABLE_USERS)->whereIn('id', $uuids)->pluck('id')->all();
                $existingEmps  = DB::table(DC::TABLE_EMPLOYEES)->whereIn('id', $uuids)->pluck('id')->all();

                foreach (array_merge($existingUsers, $existingEmps) as $id) {
                    if (is_string($id) && $id !== '') $valid[] = $id;
                }
            } catch (\Throwable $ex) {
                Log::warning(static::class . ' failed validating involved against users/employees', [
                    'id' => (string) ($this->getAttribute('id') ?? ''),
                    'error' => $ex->getMessage(),
                ]);
                $valid = $uuids; // modo compatível/legado
            }
        }

        $names = array_values(array_filter(array_unique(array_map('trim', $names)), fn($s) => $s !== ''));
        $out = array_values(array_unique(array_merge($valid, $names)));

        return array_slice($out, 0, 512);
    }

    private function normalizeEnumValueSafe(string $enumClass, mixed $value, string $defaultCaseName): string
    {
        try {
            if (is_object($value) && $value instanceof $enumClass)
                return (string) ($value->value ?? '');

            $raw = is_scalar($value) ? trim((string) $value) : '';

            if ($raw === '') {
                $def = $this->enumCaseValueByName($enumClass, $defaultCaseName);
                return $def ?? (string) (($enumClass::cases()[0]->value) ?? '');
            }

            if (method_exists($enumClass, 'normalize')) {
                $norm = $enumClass::normalize($raw);
                return is_object($norm) ? (string) ($norm->value ?? $raw) : $raw;
            }

            if (method_exists($enumClass, 'tryFrom')) {
                $case = $enumClass::tryFrom($raw);
                if (is_object($case)) return (string) ($case->value ?? $raw);
            }

            $allowed = array_map(fn($c) => (string) $c->value, $enumClass::cases());
            return in_array($raw, $allowed, true) ? $raw : (string) ($allowed[0] ?? $raw);
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' enum normalize failed', [
                'enum'  => $enumClass,
                'value' => is_scalar($value) ? (string) $value : gettype($value),
                'error' => $ex->getMessage(),
            ]);

            $def = $this->enumCaseValueByName($enumClass, $defaultCaseName);
            return $def ?? (string) (($enumClass::cases()[0]->value) ?? '');
        }
    }


    private function enumCaseValueByName(string $enumClass, string $caseName): ?string
    {
        try {
            foreach ($enumClass::cases() as $c) {
                if ($c->name === $caseName) return (string) $c->value;
            }
        } catch (\Throwable) {
        }
        return null;
    }

    private function clampInt(mixed $value, int $min, int $max): int
    {
        $v = is_numeric($value) ? (int) $value : $min;
        if ($v < $min) return $min;
        if ($v > $max) return $max;
        return $v;
    }

    private function normalizeCssColor(?string $color): string
    {
        $c = trim((string) ($color ?? ''));

        if ($c === '') return '#558855';
        if (strlen($c) > 15) return '#558855';

        if (preg_match('/^\#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $c)) return $c;
        if (preg_match('/^[a-zA-Z]{3,15}$/', $c)) return strtolower($c);

        Log::debug(static::class . ' invalid color, defaulting', [
            'id' => (string) ($this->getAttribute('id') ?? ''),
            'color' => $c,
        ]);

        return '#558855';
    }

    private function resolveProjectNameCached(): ?string
    {
        $id = (string) ($this->getAttribute(AC::COL_PJ) ?? '');
        if ($id === '') return null;

        if (array_key_exists($id, self::$cache['project_name']))
            return self::$cache['project_name'][$id];

        try {
            $name = DB::table(DC::TABLE_PROJECTS)->where('id', $id)->value('name');
            $name = is_string($name) ? trim($name) : null;
            self::$cache['project_name'][$id] = $name ?: null;
            return self::$cache['project_name'][$id];
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed resolving project name', ['project' => $id, 'error' => $ex->getMessage()]);
            self::$cache['project_name'][$id] = null;
            return null;
        }
    }

    private function resolveTaskNameCached(): ?string
    {
        $id = (string) ($this->getAttribute('task') ?? '');
        if ($id === '') return null;

        if (array_key_exists($id, self::$cache['task_name']))
            return self::$cache['task_name'][$id];

        try {
            $title = DB::table(DC::TABLE_TASKS)->where('id', $id)->value('name');
            if (!is_string($title) || trim($title) === '')
                $title = DB::table(DC::TABLE_TASKS)->where('id', $id)->value('title');

            $title = is_string($title) ? trim($title) : null;

            self::$cache['task_name'][$id] = $title ?: null;
            return self::$cache['task_name'][$id];
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed resolving task name', ['task' => $id, 'error' => $ex->getMessage()]);
            self::$cache['task_name'][$id] = null;
            return null;
        }
    }

    private function resolveResponsibleNameCached(): ?string
    {
        $id = (string) ($this->getAttribute('responsible') ?? '');
        if ($id === '') return null;

        if (array_key_exists($id, self::$cache['responsible_name']))
            return self::$cache['responsible_name'][$id];

        try {
            $name = DB::table(DC::TABLE_USERS)->where('id', $id)->value('name');
            $name = is_string($name) ? trim($name) : null;
            self::$cache['responsible_name'][$id] = $name ?: null;
            return self::$cache['responsible_name'][$id];
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed resolving responsible name', ['responsible' => $id, 'error' => $ex->getMessage()]);
            self::$cache['responsible_name'][$id] = null;
            return null;
        }
    }
}
