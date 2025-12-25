<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\UserType;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TaskChecklist extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_TSK_CHKL;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        'name',
        'description',
        'url',

        'completed',
        PJC::COL_CMP_AT,
        PJC::COL_D_DATE,

        AC::COL_IS_FV,
        AC::COL_TSK_ID,
        UC::COL_U_TP,

        'status',
        'order',

        'stage',
        'involved',
        'attachments',
        'tags',
        'positioning',
    ];

    protected $casts = [
        'completed'       => 'boolean',
        AC::COL_IS_FV     => 'boolean',

        PJC::COL_CMP_AT   => 'datetime',
        PJC::COL_D_DATE   => 'date',

        UC::COL_U_TP      => UserType::class,

        'status'          => 'integer',
        'order'           => 'integer',

        'involved'        => 'array',
        'attachments'     => 'array',
        'tags'            => 'array',
        'positioning'     => 'array',
    ];

    protected $with = [
        'task',
        'stageModel',
        'createdBy',
    ];

    protected $appends = [
        'task_title',
        'stage_name',
        'is_overdue',
    ];

    private const JSON_FIELDS = [
        'involved',
        'attachments',
        'tags',
        'positioning',
    ];

    private static array $taskDateCache = [];
    private static array $taskChecklistCountCache = [];
    private static array $stageBelongsCache = [];

    protected static function booted(): void
    {
        is_callable('parent::booted') && parent::booted();
        static::creating(function (self $m): void {
            $name = trim((string)($m->getAttribute('name') ?? ''));
            if ($name === '') {
                $m->setAttribute('name', self::generateUniqueName());
            }
        });
        static::saving(function (self $m): void {
            $m->ensureJsonAttributesAreEncoded(self::JSON_FIELDS);

            $m->setAttribute('completed', (bool)$m->getAttribute('completed'));
            $m->setAttribute(AC::COL_IS_FV, (bool)$m->getAttribute(AC::COL_IS_FV));

            try {
                $ut = $m->getAttribute(UC::COL_U_TP);
                $m->setAttribute(UC::COL_U_TP, UserType::normalize($ut instanceof UserType ? $ut : (string)($ut ?? null)));
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize user_type', [
                    'id' => $m->getAttribute('id'),
                    'err' => $e->getMessage(),
                ]);
                $m->setAttribute(UC::COL_U_TP, UserType::Customer);
            }

            $m->setAttribute('status', self::clampTinyInt($m->getAttribute('status')));
            $taskId = (string)($m->getAttribute(AC::COL_TSK_ID) ?? '');

            if ($taskId !== '') {
                $maxOrder = self::existingCountForTask($taskId, (string)($m->getAttribute('id') ?? null));
                $m->setAttribute('order', self::clampOrder($m->getAttribute('order'), $maxOrder));
            } else {
                $m->setAttribute('order', self::clampOrder($m->getAttribute('order'), 0));
            }

            $completed = (bool)$m->getAttribute('completed');
            $cmpAt = $m->getAttribute(PJC::COL_CMP_AT);

            if (!$completed) {
                $m->setAttribute(PJC::COL_CMP_AT, null);
            } elseif ($cmpAt === null) {
                $m->setAttribute(PJC::COL_CMP_AT, now());
            }

            if ($taskId !== '') {
                $taskDate = self::fetchTaskDate($taskId);

                $due = $m->getAttribute(PJC::COL_D_DATE);
                if ($taskDate !== null) {
                    if ($due === null) {
                        $m->setAttribute(PJC::COL_D_DATE, $taskDate->toDateString());
                    } else {
                        try {
                            $dueDate = $due instanceof Carbon ? $due : Carbon::parse((string)$due);
                            if ($dueDate->greaterThan($taskDate)) {
                                $m->setAttribute(PJC::COL_D_DATE, $taskDate->toDateString());
                            }
                        } catch (\Throwable $e) {
                            Log::warning(self::class . ' invalid due_date, coercing to task date', [
                                'id' => $m->getAttribute('id'),
                                'task_id' => $taskId,
                                'due_date' => $due,
                                'err' => $e->getMessage(),
                            ]);
                            $m->setAttribute(PJC::COL_D_DATE, $taskDate->toDateString());
                        }
                    }
                }

                $stageId = (string)($m->getAttribute('stage') ?? '');
                if ($stageId !== '' && !self::stageBelongsToTask($stageId, $taskId)) {
                    $m->setAttribute('stage', null);
                }

                self::normalizeInvolvedSubsetOfTask($m, $taskId);
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, AC::COL_TSK_ID);
    }

    public function stageModel(): BelongsTo
    {
        return $this->belongsTo(TaskStage::class, 'stage', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function markCompleted(?Carbon $when = null): self
    {
        $this->setAttribute('completed', true);
        $this->setAttribute(PJC::COL_CMP_AT, $when ?? now());
        return $this;
    }

    public function markIncomplete(): self
    {
        $this->setAttribute('completed', false);
        $this->setAttribute(PJC::COL_CMP_AT, null);
        return $this;
    }

    public function scopeForTask($query, string $taskId)
    {
        return $query->where(AC::COL_TSK_ID, $taskId);
    }

    public function scopeCompleted($query, bool $completed = true)
    {
        return $query->where('completed', $completed);
    }

    protected function taskTitle(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->relationLoaded('task') && $this->task) {
                    $t = $this->task->getAttribute('title');
                    return is_string($t) ? $t : (string)($t ?? '');
                }
                return '';
            }
        );
    }

    protected function stageName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->relationLoaded('stageModel') && $this->stageModel) {
                    $n = $this->stageModel->getAttribute('name');
                    return is_string($n) ? $n : (string)($n ?? '');
                }
                return '';
            }
        );
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                if ((bool)$this->getAttribute('completed')) return false;

                $due = $this->getAttribute(PJC::COL_D_DATE);
                if ($due === null) return false;

                try {
                    $d = $due instanceof Carbon ? $due : Carbon::parse((string)$due);
                    return $d->startOfDay()->lessThan(now()->startOfDay());
                } catch (\Throwable) {
                    return false;
                }
            }
        );
    }

    private static function clampTinyInt(mixed $value): int
    {
        $n = is_numeric($value) ? (int)$value : 0;
        if ($n < 0) $n = 0;
        if ($n > 255) $n = 255;
        return $n;
    }

    private static function clampOrder(mixed $value, int $max): int
    {
        $n = is_numeric($value) ? (int)$value : $max;
        if ($n < 0) $n = 0;
        if ($n > $max) $n = $max;
        if ($n > 255) $n = 255;
        return $n;
    }

    private static function generateUniqueName(): string
    {
        for ($i = 0; $i < 6; $i++) {
            $candidate = 'TSK-CHKL-' . (string)Str::uuid();
            try {
                if (!self::query()->where('name', $candidate)->exists())
                    return $candidate;
            } catch (\Throwable $e) {
                Log::warning(self::class . ' name uniqueness check failed', ['err' => $e->getMessage()]);
                return $candidate;
            }
        }
        return 'TSK-CHKL-' . (string)Str::uuid();
    }

    private static function fetchTaskDate(string $taskId): ?Carbon
    {
        if (array_key_exists($taskId, self::$taskDateCache))
            return self::$taskDateCache[$taskId];

        try {
            $raw = Task::query()->whereKey($taskId)->value('date');
            if ($raw === null) return self::$taskDateCache[$taskId] = null;

            $dt = $raw instanceof Carbon ? $raw : Carbon::parse((string)$raw)->startOfDay();
            return self::$taskDateCache[$taskId] = $dt;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to fetch task date', [
                'task_id' => $taskId,
                'err' => $e->getMessage(),
            ]);
            return self::$taskDateCache[$taskId] = null;
        }
    }

    private static function existingCountForTask(string $taskId, ?string $exceptId = null): int
    {
        $cacheKey = $taskId . '|' . (string)($exceptId ?? '');
        if (isset(self::$taskChecklistCountCache[$cacheKey]))
            return self::$taskChecklistCountCache[$cacheKey];

        try {
            $q = self::query()->where(AC::COL_TSK_ID, $taskId);
            if (!empty($exceptId)) $q->where('id', '!=', $exceptId);
            $count = (int)$q->count();
            if ($count < 0) $count = 0;
            if ($count > 255) $count = 255;
            return self::$taskChecklistCountCache[$cacheKey] = $count;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to count task checklists', [
                'task_id' => $taskId,
                'err' => $e->getMessage(),
            ]);
            return self::$taskChecklistCountCache[$cacheKey] = 0;
        }
    }

    private static function stageBelongsToTask(string $stageId, string $taskId): bool
    {
        $k = $stageId . '|' . $taskId;
        if (isset(self::$stageBelongsCache[$k]))
            return self::$stageBelongsCache[$k];

        try {
            $ok = TaskStage::query()
                ->whereKey($stageId)
                ->where(AC::COL_TSK_ID, $taskId)
                ->exists();

            return self::$stageBelongsCache[$k] = (bool)$ok;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to validate stage/task ownership', [
                'stage_id' => $stageId,
                'task_id' => $taskId,
                'err' => $e->getMessage(),
            ]);
            return self::$stageBelongsCache[$k] = false;
        }
    }

    private static function normalizeInvolvedSubsetOfTask(self $m, string $taskId): void
    {
        $rawInvolved = $m->getAttribute('involved');
        if ($rawInvolved === null) return;

        $mine = self::normalizeArrayField($rawInvolved);
        if ($mine === []) return;

        try {
            $taskInvolved = null;

            if ($m->relationLoaded('task') && $m->task) {
                $taskInvolved = $m->task->getAttribute('involved');
            } else {
                $taskInvolved = Task::query()->whereKey($taskId)->value('involved');
            }

            $theirs = self::normalizeArrayField($taskInvolved);
            if ($theirs === []) return;

            $mineS = [];
            foreach ($mine as $v) {
                if (!is_scalar($v)) continue;
                $s = trim((string)$v);
                if ($s === '') continue;
                $mineS[$s] = true;
            }

            $theirsS = [];
            foreach ($theirs as $v) {
                if (!is_scalar($v)) continue;
                $s = trim((string)$v);
                if ($s === '') continue;
                $theirsS[$s] = true;
            }

            if ($mineS === [] || $theirsS === []) return;

            $out = [];
            foreach ($mineS as $k => $_) {
                if (isset($theirsS[$k])) $out[] = $k;
            }

            if ($out !== [])
                $m->setAttribute('involved', $out);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to normalize involved subset', [
                'id' => $m->getAttribute('id'),
                'task_id' => $taskId,
                'err' => $e->getMessage(),
            ]);
        }
    }
}
