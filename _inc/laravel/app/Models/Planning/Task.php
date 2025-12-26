<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{AppModuleType, PriorityLevel};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    protected $table = DC::TABLE_TASKS;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $with = ['assignee', 'project', 'milestone'];

    protected $fillable = [
        'title',
        AC::COL_A_O_M,
        PJC::COL_AOM_ID,
        'date',
        'time',
        'description',
        AC::COL_MT,
        AC::COL_MI,
        PJC::COL_ASGN,
        PJC::COL_PJ_ID,
        PJC::COL_ML_ID,
        'stages',
        'attachments',
        'involved',
        'tags',
        'metadata',
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'title' => 'string',
        AC::COL_A_O_M => 'string',
        PJC::COL_AOM_ID => 'string',
        'date' => 'date',
        'time' => 'string',
        'description' => 'string',
        'priority' => PriorityLevel::class,
        AC::COL_MT => AppModuleType::class,

        AC::COL_MI => 'string',
        PJC::COL_ASGN => 'string',
        PJC::COL_PJ_ID => 'string',
        PJC::COL_ML_ID => 'string',

        'stages' => 'array',
        'attachments' => 'array',
        'involved' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'module_type_enum',
        'module_type_label',
        'is_overdue',
        'date_time_label',
        'involved_count',
        'stages_count',
    ];

    private static array $cache = [
        'milestone_involved' => [],
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_ASGN, 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, PJC::COL_ML_ID, 'id');
    }

    public function agentOrManagerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, PJC::COL_AOM_ID, 'id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, AC::COL_TSK_ID, 'id')->orderBy('id', 'DESC');
    }

    public function taskFiles(): HasMany
    {
        return $this->hasMany(TaskFile::class, AC::COL_TSK_ID, 'id')->orderBy('id', 'DESC');
    }

    public function taskCheckList(): HasMany
    {
        return $this->hasMany(TaskCheckList::class, AC::COL_TSK_ID, 'id')->orderBy('id', 'DESC');
    }

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureDefaults();
                $m->normalizeJsonFields();
                $m->normalizeModuleType();
                $m->mergeMilestoneInvolved();
                $m->validateStageIdsFormat();
                $priority = $m->getAttribute('priority');
                if ($priority === null || $priority === '' || !PriorityLevel::tryFrom($priority))
                    $m->setAttribute('priority', PriorityLevel::Medium);
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
        if ($title === '') $this->setAttribute('title', 'Task');

        $aom = trim((string) ($this->getAttribute(AC::COL_A_O_M) ?? ''));
        if ($aom === '') $this->setAttribute(AC::COL_A_O_M, 'N/A');

        $mt = $this->getAttribute(AC::COL_MT);
        if ($mt === null || $mt === '') $this->setAttribute(AC::COL_MT, AppModuleType::Other);
    }

    private function normalizeModuleType(): void
    {
        $raw = $this->getAttribute(AC::COL_MT);

        try {
            if ($raw instanceof AppModuleType) return;

            $norm = AppModuleType::normalize(is_string($raw) ? $raw : null);
            $this->setAttribute(AC::COL_MT, $norm);
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed normalizing module_type', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'value' => is_scalar($raw) ? (string) $raw : gettype($raw),
                'error' => $ex->getMessage(),
            ]);
            $this->setAttribute(AC::COL_MT, AppModuleType::Other);
        }
    }

    private function normalizeJsonFields(): void
    {
        $this->setAttribute('stages', self::normalizeArrayField($this->getAttribute('stages')));
        $this->setAttribute('attachments', self::normalizeArrayField($this->getAttribute('attachments')));
        $this->setAttribute('tags', self::normalizeArrayField($this->getAttribute('tags')));
        $this->setAttribute('metadata', self::normalizeArrayField($this->getAttribute('metadata')));

        $involved = self::normalizeArrayField($this->getAttribute('involved'));
        $this->setAttribute('involved', $this->filterInvolved($involved));
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
        return array_slice($out, 0, 1024);
    }

    private function mergeMilestoneInvolved(): void
    {
        $mid = (string) ($this->getAttribute(PJC::COL_ML_ID) ?? '');
        if ($mid === '') return;

        $cur = self::normalizeArrayField($this->getAttribute('involved'));
        $extra = $this->getMilestoneInvolvedCached($mid);
        if (empty($extra)) return;

        $merged = array_values(array_unique(array_merge($cur, $extra), SORT_REGULAR));
        $this->setAttribute('involved', array_slice($merged, 0, 1024));
    }

    private function getMilestoneInvolvedCached(string $milestoneId): array
    {
        $key = trim($milestoneId);
        if ($key === '') return [];

        if (array_key_exists($key, self::$cache['milestone_involved']))
            return self::$cache['milestone_involved'][$key];

        try {
            $row = Milestone::query()->where('id', $key)->value('involved');
            $list = self::normalizeArrayField($row);
            self::$cache['milestone_involved'][$key] = $this->filterInvolved($list);
            return self::$cache['milestone_involved'][$key];
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed fetching milestone involved', [
                'milestone_id' => $key,
                'error' => $ex->getMessage(),
            ]);
            self::$cache['milestone_involved'][$key] = [];
            return [];
        }
    }

    private function validateStageIdsFormat(): void
    {
        $stages = self::normalizeArrayField($this->getAttribute('stages'));
        if (empty($stages)) return;

        $valid = [];
        foreach ($stages as $v) {
            if (!is_string($v)) continue;
            $id = trim($v);
            if ($id !== '' && $this->looksLikeUuidSafe($id)) $valid[] = $id;
        }

        $valid = array_values(array_unique($valid));
        $this->setAttribute('stages', array_slice($valid, 0, 512));
    }

    private function looksLikeUuidSafe(string $value): bool
    {
        $v = trim($value);
        if ($v === '') return false;
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/',
            $v
        );
    }

    public function getModuleTypeEnumAttribute(): AppModuleType
    {
        $raw = $this->getAttribute(AC::COL_MT);
        return $raw instanceof AppModuleType
            ? $raw
            : AppModuleType::normalize(is_string($raw) ? $raw : null);
    }

    public function getModuleTypeLabelAttribute(): string
    {
        return $this->getModuleTypeEnumAttribute()->label();
    }

    public function getIsOverdueAttribute(): bool
    {
        $date = $this->getAttribute('date');
        if (!$date) return false;

        try {
            $d = $date instanceof Carbon ? $date : Carbon::parse((string) $date);
            return $d->isPast();
        } catch (\Throwable) {
            return false;
        }
    }

    public function getDateTimeLabelAttribute(): string
    {
        $date = $this->getAttribute('date');
        $time = $this->getAttribute('time');

        try {
            $d = $date instanceof Carbon ? $date->toDateString() : ($date ? Carbon::parse((string) $date)->toDateString() : '');
        } catch (\Throwable) {
            $d = is_string($date) ? trim($date) : '';
        }

        $t = is_string($time) ? trim($time) : (is_scalar($time) ? (string) $time : '');
        return trim($d . ' ' . $t);
    }

    public function getInvolvedCountAttribute(): int
    {
        return count(self::normalizeArrayField($this->getAttribute('involved')));
    }

    public function getStagesCountAttribute(): int
    {
        return count(self::normalizeArrayField($this->getAttribute('stages')));
    }

    public function taskCompleteCheckListCount(): int
    {
        return $this->taskCheckList()
            ->where(AC::COL_TSK_STT, '1')
            ->count();
    }

    public function taskTotalCheckListCount(): int
    {
        return $this->taskCheckList()->count();
    }
}
