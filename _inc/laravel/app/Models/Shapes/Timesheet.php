<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{EvaluationStatus, Visibility};
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, PlansByHierarchy, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Log};

/**
 * @property mixed $time
 */
class Timesheet extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays, PlansByHierarchy, FiltersSecureAttachments, DefinesDates;

    protected $table = DC::TABLE_TMS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $with = [
        'projectModel',
        'projectTaskModel',
        'employeeModel',
    ];

    protected $appends = [
        'due_at',
        'is_overdue',
        'attachments_count',
    ];

    protected $casts = [
        PJC::COL_S_DT      => 'date:Y-m-d',
        PJC::COL_E_DT      => 'datetime:H:i:s',
        'date'            => 'date:Y-m-d',
        'time'            => 'datetime:H:i:s',
        AC::COL_TTL_TIME   => 'decimal:2',
        PJC::COL_EXP_DR    => 'decimal:2',
        PJC::COL_SBM_AT    => 'datetime',
        PJC::COL_APV_AT    => 'datetime',
        PJC::COL_REJ_AT    => 'datetime',
        'status'          => EvaluationStatus::class,
        'visibility'      => Visibility::class,
        'attachments'     => 'array',
    ];

    protected static array $localCache = [];

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            try {
                self::ensureUniqueCode($m);
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed to ensure unique code', [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'id'    => $m->getAttribute('id'),
                ]);
            }
        });

        static::saving(function (self $m): void {
            try {
                self::normalizeCoreFields($m);
                self::ensureJsonCoherence($m);
                self::enforceProjectTaskBounds($m);
                self::enforceProjectDateBounds($m);
                self::clampDurations($m);
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving normalization failed', [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'id'    => $m->getAttribute('id'),
                ]);
            }
        });
    }

    protected static function ensureUniqueCode(self $m): void
    {
        $code = $m->getAttribute('code');
        if (is_string($code) && trim($code) !== '')
            return;

        $attempts = 0;
        $maxAttempts = 40;

        do {
            $attempts++;
            $uuid = (string) ($m->getAttribute('id') ?? '');
            if ($uuid === '')
                $uuid = (string) \Illuminate\Support\Str::uuid();

            $candidate = 'TMS-' . $uuid;

            $exists = DB::table($m->getTable())
                ->where('code', $candidate)
                ->exists();

            if (!$exists) {
                $m->setAttribute('code', $candidate);
                return;
            }
        } while ($attempts < $maxAttempts);

        $fallback = 'TMS-' . (string) \Illuminate\Support\Str::uuid();
        $m->setAttribute('code', $fallback);

        Log::warning(static::class . ' code uniqueness hit attempt limit; used fallback', [
            'table' => $m->getTable(),
            'file'  => __FILE__,
            'line'  => __LINE__,
            'id'    => $m->getAttribute('id'),
            'fallback' => $fallback,
        ]);
    }

    protected static function normalizeCoreFields(self $m): void
    {
        $desc = $m->getAttribute('description');
        if (is_string($desc)) {
            $t = trim($desc);
            $m->setAttribute('description', $t === '' ? null : $t);
        }

        $vis = $m->getAttribute('visibility');
        if (is_string($vis) || $vis === null) {
            $enum = Visibility::normalize($vis) ?? Visibility::Private;
            $m->setAttribute('visibility', $enum->value);
        }

        $st = $m->getAttribute('status');
        if (is_string($st) || $st === null) {
            $enum = EvaluationStatus::normalize($st);
            $m->setAttribute('status', $enum->value);
        }

        $ttl = $m->getAttribute(AC::COL_TTL_TIME);
        if ($ttl === null || $ttl === '') $m->setAttribute(AC::COL_TTL_TIME, 0.00);

        $exp = $m->getAttribute(PJC::COL_EXP_DR);
        if ($exp === null || $exp === '') $m->setAttribute(PJC::COL_EXP_DR, 0.00);
    }

    protected static function ensureJsonCoherence(self $m): void
    {
        $m->ensureJsonAttributesAreEncoded([
            'attachments',
        ]);
    }

    protected static function enforceProjectTaskBounds(self $m): void
    {
        $pj = $m->getAttribute(PJC::COL_PJ_ID);
        $task = $m->getAttribute(AC::COL_TSK_ID);
        $pjTask = $m->getAttribute(PJC::COL_PJ_TSK_ID);

        $pj = is_string($pj) && trim($pj) !== '' ? trim($pj) : null;
        $task = is_string($task) && trim($task) !== '' ? trim($task) : null;
        $pjTask = is_string($pjTask) && trim($pjTask) !== '' ? trim($pjTask) : null;

        if (!$pj) {
            $pjTask && $m->setAttribute(PJC::COL_PJ_TSK_ID, null);
            return;
        }

        try {
            if ($pjTask) {
                $row = DB::table(DC::TABLE_PROJ_TSKS)
                    ->where('id', $pjTask)
                    ->first([PJC::COL_PJ_ID, PJC::COL_S_DT, PJC::COL_E_DT]);

                if (!$row || (($row->{PJC::COL_PJ_ID} ?? null) !== $pj))
                    $m->setAttribute(PJC::COL_PJ_TSK_ID, null);
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to validate project_task against project', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $m->getAttribute('id'),
                'project' => $pj,
                'project_task' => $pjTask,
            ]);
        }

        try {
            if ($task) {
                $row = DB::table(DC::TABLE_TASKS)
                    ->where('id', $task)
                    ->first([PJC::COL_PJ_ID, 'date']);

                if (!$row) {
                    $m->setAttribute(AC::COL_TSK_ID, null);
                    return;
                }

                $taskProject = $row->{PJC::COL_PJ_ID} ?? null;
                if ($taskProject && $taskProject !== $pj) {
                    $m->setAttribute(AC::COL_TSK_ID, null);
                    return;
                }

                $project = DB::table(DC::TABLE_PROJECTS)
                    ->where('id', $pj)
                    ->first([PJC::COL_S_DT, PJC::COL_E_DT]);

                if ($project) {
                    $pjStart = $project->{PJC::COL_S_DT} ?? null;
                    $pjEnd = $project->{PJC::COL_E_DT} ?? null;
                    $taskDate = $row->{'date'} ?? null;

                    if ($taskDate && ($pjStart || $pjEnd)) {
                        $td = Carbon::parse($taskDate)->startOfDay();
                        if ($pjStart && $td->lt(Carbon::parse($pjStart)->startOfDay()))
                            $m->setAttribute(AC::COL_TSK_ID, null);
                        elseif ($pjEnd && $td->gt(Carbon::parse($pjEnd)->startOfDay()))
                            $m->setAttribute(AC::COL_TSK_ID, null);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to validate task date bounds', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $m->getAttribute('id'),
                'project' => $pj,
                'task' => $task,
            ]);
        }
    }

    protected static function enforceProjectDateBounds(self $m): void
    {
        $pj = $m->getAttribute(PJC::COL_PJ_ID);
        $pj = is_string($pj) && trim($pj) !== '' ? trim($pj) : null;
        if (!$pj) return;

        try {
            $project = DB::table(DC::TABLE_PROJECTS)
                ->where('id', $pj)
                ->first([PJC::COL_S_DT, PJC::COL_E_DT, PJC::COL_E_HRS]);

            if (!$project) return;

            $pjStart = $project->{PJC::COL_S_DT} ?? null;
            $pjEnd = $project->{PJC::COL_E_DT} ?? null;

            $start = $m->getAttribute(PJC::COL_S_DT);
            if ($start && ($pjStart || $pjEnd)) {
                $sd = Carbon::parse($start)->startOfDay();
                if ($pjStart && $sd->lt(Carbon::parse($pjStart)->startOfDay()))
                    $m->setAttribute(PJC::COL_S_DT, $pjStart);
                elseif ($pjEnd && $sd->gt(Carbon::parse($pjEnd)->startOfDay()))
                    $m->setAttribute(PJC::COL_S_DT, $pjEnd);
            }

            $end = $m->getAttribute(PJC::COL_E_DT);
            if ($end && ($pjStart || $pjEnd)) {
                $ed = Carbon::parse($end);
                if ($pjStart && $ed->lt(Carbon::parse($pjStart)->startOfDay()))
                    $m->setAttribute(PJC::COL_E_DT, Carbon::parse($pjStart)->startOfDay()->format('H:i:s'));
                elseif ($pjEnd && $ed->gt(Carbon::parse($pjEnd)->endOfDay()))
                    $m->setAttribute(PJC::COL_E_DT, Carbon::parse($pjEnd)->endOfDay()->format('H:i:s'));
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to enforce project date bounds', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $m->getAttribute('id'),
                'project' => $pj,
            ]);
        }
    }

    protected static function clampDurations(self $m): void
    {
        $pj = $m->getAttribute(PJC::COL_PJ_ID);
        $pj = is_string($pj) && trim($pj) !== '' ? trim($pj) : null;

        $exp = $m->getAttribute(PJC::COL_EXP_DR);
        $expVal = is_numeric($exp) ? (float) $exp : 0.0;
        if ($expVal < 0) $expVal = 0.0;

        $max = null;

        try {
            if ($pj) {
                $row = DB::table(DC::TABLE_PROJECTS)
                    ->where('id', $pj)
                    ->first([PJC::COL_E_HRS, PJC::COL_S_DT, PJC::COL_E_DT]);

                if ($row) {
                    $ehrs = $row->{PJC::COL_E_HRS} ?? null;
                    if (is_numeric($ehrs)) $max = (float) $ehrs;

                    $s = $row->{PJC::COL_S_DT} ?? null;
                    $e = $row->{PJC::COL_E_DT} ?? null;

                    if ($s && $e) {
                        $diff = Carbon::parse($s)->diffInHours(Carbon::parse($e));
                        $max = $max === null ? (float) $diff : min($max, (float) $diff);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' clampDurations query failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $m->getAttribute('id'),
                'project' => $pj,
            ]);
        }

        if ($max !== null && $expVal > $max) $expVal = $max;

        $m->setAttribute(PJC::COL_EXP_DR, $expVal);

        $ttl = $m->getAttribute(AC::COL_TTL_TIME);
        $ttlVal = is_numeric($ttl) ? (float) $ttl : 0.0;
        if ($ttlVal < 0) $ttlVal = 0.0;

        $m->setAttribute(AC::COL_TTL_TIME, $ttlVal);
    }

    public function projectModel(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function projectTaskModel(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, PJC::COL_PJ_TSK_ID, 'id');
    }

    public function taskModel(): BelongsTo
    {
        return $this->belongsTo(Task::class, AC::COL_TSK_ID, 'id');
    }

    public function employeeModel(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function cachedKey(string $suffix): string
    {
        $id = (string) ($this->getAttribute('id') ?? '');
        return static::class . ':' . $id . ':' . $suffix;
    }

    public function getDueAtAttribute(): ?string
    {
        try {
            $date = $this->getAttribute('date');
            $time = $this->getAttribute('time');

            if (!$date) return null;

            $ts = $time ? Carbon::parse($date . ' ' . $time) : Carbon::parse($date)->endOfDay();
            return $ts->toDateTimeString();
        } catch (\Throwable $e) {
            Log::warning(static::class . ' due_at parse failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $this->getAttribute('id'),
            ]);
        }

        return null;
    }

    public function getIsOverdueAttribute(): bool
    {
        try {
            $due = $this->getAttribute('due_at');
            if (!$due) return false;

            return Carbon::parse($due)->lt(now());
        } catch (\Throwable $e) {
            Log::warning(static::class . ' is_overdue parse failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $this->getAttribute('id'),
            ]);
        }

        return false;
    }

    public function getAttachmentsCountAttribute(): int
    {
        $k = $this->cachedKey('attachments_count');
        if (array_key_exists($k, self::$localCache))
            return (int) self::$localCache[$k];

        try {
            $list = self::normalizeArrayField($this->getAttribute('attachments'));
            return self::$localCache[$k] = (int) count($list);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' attachments_count failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $this->getAttribute('id'),
            ]);
        }

        return self::$localCache[$k] = 0;
    }

    /** @return BelongsTo Alias for projectModel(). */
    public function project(): BelongsTo
    {
        return $this->projectModel();
    }

    /** @return BelongsTo Alias for taskModel(). */
    public function task(): BelongsTo
    {
        return $this->taskModel();
    }
}
