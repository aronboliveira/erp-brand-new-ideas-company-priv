<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{FiltersSecureAttachments, HasAuditFields, NormalizesArrays, StoresManyRefJson, UsesUuids};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\{Builder, Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{Cache, DB, Log};
use Illuminate\Support\Str;

class InterviewSchedule extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays, StoresManyRefJson, FiltersSecureAttachments;

    protected $table = DC::TABLE_ITV_SCD;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        'candidate',
        'employee',
        'date',
        'time',
        'url',
        'location',
        AC::COL_RL_TTL,
        AC::COL_RL_DSC,
        'comment',
        AC::COL_EMP_RES,
        'notes',
        'feedback',
        AC::COL_TSK_ID,
        PJC::COL_PJ_ID,
        'document',
        'todo',
        'steps',
        'results',
        'attachments',
        'involved',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'time' => 'string',
        'steps' => 'array',
        'results' => 'array',
        'attachments' => 'array',
        'involved' => 'array',
    ];

    protected $with = [
        'applications',
        'users',
    ];

    protected $appends = [
        'scheduled_at',
        'is_online',
    ];

    private const JSON_FIELDS = [
        'steps',
        'results',
        'attachments',
        'involved',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            try {
                $model->ensureJsonAttributesAreEncoded(self::JSON_FIELDS);

                if (!$model->enforceEmployeeIsInterviewer())
                    return false;

                $model->nullifyTodoIfNotOwnedByEmployee();
                $model->forceInvolvedContainsInterviewer();

                return true;
            } catch (\Throwable $e) {
                Log::warning(self::class . ' saving hook failed: ' . $e->getMessage(), [
                    'id' => (string) ($model->getKey() ?? ''),
                    'candidate' => (string) ($model->getAttribute('candidate') ?? ''),
                    'employee' => (string) ($model->getAttribute('employee') ?? ''),
                ]);
                return false;
            }
        });
    }

    public function applications(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'candidate', 'id');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee', 'id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, AC::COL_TSK_ID, 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document', 'id');
    }

    public function todoItem(): BelongsTo
    {
        return $this->belongsTo(UserToDo::class, 'todo', 'id');
    }

    public function getScheduledAtAttribute(): ?string
    {
        $date = $this->getAttribute('date');
        $time = trim((string) ($this->getAttribute('time') ?? ''));

        if (!$date || $time === '')
            return null;

        $dateStr = method_exists($date, 'format')
            ? (string) $date->format('Y-m-d')
            : trim((string) $date);

        if ($dateStr === '')
            return null;

        $timeNorm = $this->normalizeTimeString($time);
        if ($timeNorm === null)
            return null;

        try {
            return CarbonImmutable::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $timeNorm)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    public function getIsOnlineAttribute(): bool
    {
        $url = trim((string) ($this->getAttribute('url') ?? ''));
        return $url !== '';
    }

    public function stepsArray(): array
    {
        return self::normalizeArrayField($this->getAttribute('steps'));
    }

    public function resultsArray(): array
    {
        return self::normalizeArrayField($this->getAttribute('results'));
    }

    public function attachmentsArray(): array
    {
        return self::normalizeArrayField($this->getAttribute('attachments'));
    }

    public function involvedArray(): array
    {
        return self::normalizeArrayField($this->getAttribute('involved'));
    }

    public function scopeForEmployee(Builder $q, string $employeeId): Builder
    {
        return $q->where('employee', $employeeId);
    }

    public function scopeOnDate(Builder $q, string $dateYmd): Builder
    {
        return $q->whereDate('date', $dateYmd);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->whereDate('date', '>=', now()->toDateString());
    }

    public static function countUpcomingForEmployeeCached(string $employeeId, int $ttlSeconds = 60): int
    {
        $key = 'itv_scd:upcoming_count:' . $employeeId;

        try {
            return (int) Cache::remember($key, $ttlSeconds, function () use ($employeeId) {
                return (int) static::query()
                    ->forEmployee($employeeId)
                    ->upcoming()
                    ->count();
            });
        } catch (\Throwable $e) {
            Log::debug(self::class . ' countUpcomingForEmployeeCached failed: ' . $e->getMessage(), [
                'employee' => $employeeId,
            ]);
            return 0;
        }
    }

    private function enforceEmployeeIsInterviewer(): bool
    {
        $employeeId = trim((string) ($this->getAttribute('employee') ?? ''));
        if ($employeeId === '')
            return true;

        try {
            $empCode = DB::table(DC::TABLE_USERS)
                ->where('id', $employeeId)
                ->value(UC::COL_EMP_ID);

            $empCode = is_string($empCode) ? trim($empCode) : '';

            if ($empCode !== '')
                return true;

            Log::warning(self::class . ' rejected schedule: employee is not a valid interviewer', [
                'employee' => $employeeId,
                'id' => (string) ($this->getKey() ?? ''),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' interviewer validation failed: ' . $e->getMessage(), [
                'employee' => $employeeId,
                'id' => (string) ($this->getKey() ?? ''),
            ]);
            return false;
        }
    }

    private function nullifyTodoIfNotOwnedByEmployee(): void
    {
        $todoId = trim((string) ($this->getAttribute('todo') ?? ''));
        if ($todoId === '')
            return;

        $employeeId = trim((string) ($this->getAttribute('employee') ?? ''));
        if ($employeeId === '') {
            $this->setAttribute('todo', null);
            return;
        }

        try {
            $todoUserId = DB::table(DC::TABLE_USR_TD)
                ->where('id', $todoId)
                ->value(UC::COL_USER_ID);

            $todoUserId = is_string($todoUserId) ? trim($todoUserId) : '';

            if ($todoUserId === '' || $todoUserId !== $employeeId)
                $this->setAttribute('todo', null);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' todo ownership check failed: ' . $e->getMessage(), [
                'todo' => $todoId,
                'employee' => $employeeId,
                'id' => (string) ($this->getKey() ?? ''),
            ]);
            $this->setAttribute('todo', null);
        }
    }

    private function forceInvolvedContainsInterviewer(): void
    {
        $employeeId = trim((string) ($this->getAttribute('employee') ?? ''));
        if ($employeeId === '')
            return;

        $involved = $this->involvedArray();

        $found = false;
        foreach ($involved as $item) {
            if (is_string($item) && trim($item) === $employeeId) {
                $found = true;
                break;
            }

            if (!is_array($item))
                continue;

            foreach (['id', 'user_id', 'employee', 'uuid'] as $k) {
                $v = $item[$k] ?? null;
                if (is_string($v) && trim($v) === $employeeId) {
                    $found = true;
                    break 2;
                }
            }
        }

        if (!$found)
            $involved[] = $employeeId;

        $this->setAttribute('involved', array_values($involved));
    }

    private function normalizeTimeString(string $time): ?string
    {
        $time = trim($time);
        if ($time === '')
            return null;

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time))
            return $time;

        if (preg_match('/^\d{2}:\d{2}$/', $time))
            return $time . ':00';

        try {
            if (Str::contains($time, 'T') || Str::contains($time, ' '))
                return CarbonImmutable::parse($time)->format('H:i:s');
        } catch (\Throwable) {
        }

        return null;
    }
}
