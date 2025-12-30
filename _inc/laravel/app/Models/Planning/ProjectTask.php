<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{AppModuleType, EvaluationStatus, PriorityLevel};
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class ProjectTask extends Model
{
    use ChecksLogin, UsesUuids;

    protected $table = DC::TABLE_PROJ_TSKS;

    protected $fillable = [
        'code',
        PJC::COL_NM,
        'description',
        PJC::COL_CL,

        'responsible',
        PJC::COL_ASGN,
        PJC::COL_ASG_BY,
        PJC::COL_ASG_AT,

        PJC::COL_E_HRS,
        PJC::COL_ACT_HRS,
        PJC::COL_LAST_ACT_AT,

        PJC::COL_S_DT,
        PJC::COL_E_DT,

        AC::COL_MT,
        'priority',
        PJC::COL_PGR,
        'status',
        PJC::COL_PR_CL,

        AC::COL_OD,
        'depth',

        PJC::COL_PJ_ID,
        'parent',
        PJC::COL_ML_ID,
        PJC::COL_STAGE_ID,

        PJC::COL_IS_FV,
        PJC::COL_IS_CP,
        PJC::COL_M_AT,

        'recurring',

        'attachments',
        'involved',
        'tags',
        'metadata',
        'positioning',
        'notes',
        'rules',
        'reactions',

        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        PJC::COL_E_HRS => 'integer',
        PJC::COL_ACT_HRS => 'integer',
        'depth' => 'integer',
        AC::COL_OD => 'integer',

        PJC::COL_IS_FV => 'boolean',
        PJC::COL_IS_CP => 'boolean',
        'recurring' => 'boolean',

        PJC::COL_ASG_AT => 'datetime',
        PJC::COL_LAST_ACT_AT => 'datetime',
        PJC::COL_M_AT => 'date',
        PJC::COL_S_DT => 'date',
        PJC::COL_E_DT => 'date',

        'attachments' => 'array',
        'involved' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'positioning' => 'array',
        'notes' => 'array',
        'rules' => 'array',
        'reactions' => 'array',
    ];

    public static array $priority = [
        'critical' => 'Critical',
        'high'     => 'High',
        'medium'   => 'Medium',
        'low'      => 'Low',
    ];

    public static array $priorityColor = [
        'critical' => PJC::STT_DGR,
        'high'     => PJC::STT_WRN,
        'medium'   => 'primary',
        'low'      => PJC::STT_INF,
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $m): void {
            try {
                $code = (string) ($m->getAttribute('code') ?? '');
                if (trim($code) === '') {
                    $attempts = 0;
                    do {
                        $attempts++;
                        if ($attempts > 48) {
                            Log::warning(__CLASS__ . ' failed to generate unique code for ' . $m::class . ' after excessive attempts.');
                            break;
                        }
                        $candidate = 'PRJ-TSK-' . Str::uuid();
                    } while (self::query()->where('code', $candidate)->exists());
                    if (isset($candidate)) $m->setAttribute('code', $candidate);
                }

                $mt = $m->getAttribute(AC::COL_MT);
                $mtNorm = $mt instanceof AppModuleType ? $mt : AppModuleType::normalize(is_string($mt) ? $mt : null);
                $m->setAttribute(AC::COL_MT, $mtNorm->value);

                $prio = $m->getAttribute('priority');
                $prioNorm = $prio instanceof PriorityLevel ? $prio : PriorityLevel::normalize(is_string($prio) ? $prio : null);
                $m->setAttribute('priority', $prioNorm->value);

                $m->setAttribute(PJC::COL_PR_CL, PriorityLevel::colorCodes()[$prioNorm->value] ?? PriorityLevel::colorCodes()[PriorityLevel::Medium->value]);

                $pRaw = $m->getAttribute(PJC::COL_PGR);
                $pNum = is_numeric($pRaw) ? (float) $pRaw : 0.0;
                if ($pNum < 0) $pNum = 0.0;
                if ($pNum > 100) $pNum = 100.0;

                $pStr = rtrim(rtrim(number_format($pNum, 2, '.', ''), '0'), '.');
                if ($pStr === '') $pStr = '0';
                $m->setAttribute(PJC::COL_PGR, $pStr);

                $pInt = (int) round($pNum);

                if ($pInt >= 100) {
                    $m->setAttribute('status', EvaluationStatus::Completed->value);
                    $m->setAttribute(PJC::COL_IS_CP, true);
                } elseif ($pInt > 0) {
                    $m->setAttribute('status', EvaluationStatus::InProgress->value);
                    $m->setAttribute(PJC::COL_IS_CP, false);
                } else {
                    $m->setAttribute('status', EvaluationStatus::NotStarted->value);
                    $m->setAttribute(PJC::COL_IS_CP, false);
                }

                $lAct = $m->getAttribute(PJC::COL_LAST_ACT_AT);
                if ($lAct === null && $pInt > 0) {
                    $m->setAttribute(PJC::COL_LAST_ACT_AT, now());
                }

                $asgn = $m->getAttribute(PJC::COL_ASGN);
                $asgnStr = is_string($asgn) ? $asgn : null;

                $parts = $asgnStr !== null
                    ? array_values(array_map(static fn($v) => trim((string) $v), explode(',', $asgnStr)))
                    : [];

                $parts = array_values(array_filter($parts, static fn($v) => $v !== ''));

                $involved = $m->getAttribute('involved');
                $involvedArr = is_array($involved) ? $involved : [];

                $mustInclude = [
                    $m->getAttribute(PJC::COL_ASG_BY),
                    $m->getAttribute(PJC::COL_ASGN),
                    $m->getAttribute('responsible'),
                ];

                foreach ($mustInclude as $v) {
                    if (is_string($v) && trim($v) !== '') $parts[] = trim($v);
                }

                $merged = array_values(array_unique(array_merge($involvedArr, $parts)));

                $m->setAttribute('involved', $merged);
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::saving normalization failed: ' . $e->getMessage());
            }
        });
    }

    public function milestone(): HasOne
    {
        return $this->hasOne(Milestone::class, 'id', PJC::COL_ML_ID);
    }

    public function users(): Collection
    {
        $raw = $this->getAttribute(PJC::COL_ASGN);
        if (!is_string($raw) || trim($raw) === '') return new Collection();

        $parts = array_values(array_map(static fn($v) => trim((string) $v), explode(',', $raw)));
        $parts = array_values(array_filter($parts, static fn($v) => $v !== ''));

        $ids = [];
        foreach ($parts as $v) {
            if (Str::isUuid($v)) $ids[] = $v;
        }
        $ids = array_values(array_unique($ids));
        if ($ids === []) return new Collection();

        return User::query()->whereIn('id', $ids)->get();
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', PJC::COL_PJ_ID);
    }

    public function stage(): HasOne
    {
        return $this->hasOne(TaskStage::class, 'id', PJC::COL_STAGE_ID);
    }

    public function taskProgress(Project $project): array
    {
        $total     = $this->checklist->count();
        $completed = $project->checklist->where(AC::COL_TSK_STT, '1')->count();
        $pct       = $total > 0 ? intval($completed / $total * 100) : 0;
        $color     = Utility::getProgressColor($pct);

        return [PJC::COL_CL => $color, 'percentage' => "{$pct}%"];
    }

    public function taskUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', PJC::COL_ASGN);
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(TaskChecklist::class, AC::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function taskFiles(): HasMany
    {
        return $this->hasMany(TaskFile::class, AC::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, AC::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function countTaskChecklist(): string
    {
        $done = $this->checklist->where(AC::COL_TSK_STT, 1)->count();
        $total = $this->checklist->count();
        return "{$done}/{$total}";
    }

    public static function deleteTask(array $taskIds): bool
    {
        try {
            DB::transaction(function () use ($taskIds) {
                foreach ($taskIds as $id) {
                    $task = self::find($id);
                    if (!$task) continue;

                    $files = TaskFile::where(AC::COL_TSK_ID, $task->id)
                        ->pluck('file')
                        ->toArray();

                    Utility::checkFileExistsAndDelete($files);

                    TaskFile::where(AC::COL_TSK_ID, $task->id)->delete();
                    $task->timesheets()->delete();
                    TaskChecklist::where(AC::COL_TSK_ID, $task->id)->delete();
                    TaskComment::where(AC::COL_TSK_ID, $task->id)->delete();
                    $task->delete();
                }
            });

            return true;
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
            return false;
        }
    }

    public function activityLog()
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) {
            return $userOrRedirect;
        }

        $user = $userOrRedirect;

        return ActivityLog::where(UC::COL_USER_ID, $user::id())
            ->where(PJC::COL_PJ_ID, $this[PJC::COL_PJ_ID])
            ->where(AC::COL_TSK_ID, $this->id)
            ->get();
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, AC::COL_TSK_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public static function getAllSectionedTaskList(
        Request $request,
        $project,
        array $filterData = [],
        array $exclude = []
    ): array {
        $sections   = $project->tasksections()->pluck(AC::COL_TT, 'id')->toArray();
        $sectionIds = array_keys($sections);

        $allTasks   = Project::getAssignedProjectTasks($project->id, null, $filterData);

        $result     = [];
        $controller = app(\App\Http\Controllers\ProjectTaskController::class);

        $others = $allTasks
            ->whereNotIn(PJC::COL_ML_ID, $sectionIds)
            ->whereNotIn('id', $exclude)
            ->orderBy('id', 'desc')
            ->pluck('id')
            ->toArray();

        if ($others) {
            $result[] = self::buildSection(0, '', $others, $request, $controller);
        }

        foreach ($sections as $secId => $secName) {
            $ids = $allTasks
                ->whereNotIn(DC::TABLE_PROJ_TSKS . '.' . PJC::COL_ML_ID, [$secId]) // preserve legacy semantics, avoid breaking collection filters
                ->whereIn(DC::TABLE_PROJ_TSKS . '.' . PJC::COL_ML_ID, [$secId])
                ->whereNotIn('id', $exclude)
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->toArray();

            $result[] = self::buildSection((int) $secId, (string) $secName, $ids, $request, $controller);
        }

        return $result;
    }

    private static function buildSection(
        int $secId,
        string $secName,
        array $taskIds,
        Request $request,
        $controller
    ): array {
        $tasks = array_map(
            static fn($tid) => tap(
                self::find($tid)->toArray(),
                static fn(&$t) => $t['taskinfo'] = json_decode(
                    $controller->getDefaultTaskInfo($request, $tid),
                    true
                )
            ),
            $taskIds
        );

        return [
            'section_id'    => $secId,
            'section_name'  => $secName,
            'sections'      => $tasks,
            'sectionsClass' => 'active',
        ];
    }
}
