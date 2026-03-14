<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC,
    ViewsConstants as VW
};
use App\Services\ProjectRequestService;
use App\Traits\{DefinesDates, HasAuditFields, PlansWithSchedule, StoresManyRefJson, UsesUuids};
use Carbon\Carbon;
use Illuminate\Support\Facades\{Storage};
use Illuminate\{Database\Eloquent\Builder, Database\Eloquent\Model, Support\Collection};
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo, BelongsToMany};
use Illuminate\Http\RedirectResponse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property array|string|null $checklist
 * @property string|null $image
 * @property string|null $name
 * @property string|null $project_image
 * @property \Illuminate\Support\Carbon|string|null $end_date
 */
class Project extends Model
{
    use HasFactory;
    use UsesUuids, HasAuditFields, DefinesDates, StoresManyRefJson, PlansWithSchedule;

    protected $fillable = [
        PJC::COL_NM,
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        PJC::COL_IMG,
        PJC::COL_BUDGET,
        PJC::COL_CLIENT_ID,
        PJC::COL_STAGE_ID,
        PJC::COL_DESCRIPTION,
        PJC::COL_STATUS,
        PJC::COL_E_HRS,
        PJC::COL_COPYLINK,
        PJC::COL_TAGS,
    ];

    protected $hidden = [PJC::COL_PASSWORD];

    protected $casts = [
        PJC::COL_PASSWORD => 'hashed',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $appends = ['img_image'];

    private static $projectTask = NULL;

    public static $project_status = [
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'complete' => 'Complete',
        'canceled' => 'Canceled'
    ];

    /** Alias for camelCase access. */
    public static $projectStatus = [
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'complete' => 'Complete',
        'canceled' => 'Canceled'
    ];

    public static $status_color = [
        'on_hold' => 'warning',
        'in_progress' => 'info',
        'complete' => 'success',
        'canceled' => 'danger',
    ];

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, PJC::COL_PJ_ID, 'id');
        // * consider belongsTo(User::class,'client_id','id')
    }

    public function getImgImageAttribute(): string
    {
        $path = $this->project_image;
        return Storage::exists($path)
            ? 'src=' . asset(Storage::url($path))
            : 'src=' . asset(Storage::url('uploads/avatar/default.png'));
    }

    public function projectAttachments(): Collection
    {
        $ids = $this->tasks->pluck('id');
        return TaskFile::whereIn(AC::COL_TSK_ID, $ids)->get();
    }

    public static function projectHrs(int|string $projectId, int|string $taskId = ''): array
    {
        $allocated = self::projectTask($projectId)->sum(PJC::COL_E_HRS);
        return ['allocated' => $allocated];
    }

    public static function projectTask(int|string $id): Collection
    {
        if (self::$projectTask === null)
            self::$projectTask = ProjectTask::where(PJC::COL_PJ_ID, $id)->get();
        return self::$projectTask;
        // * consider caching per project id instead of globally
    }

    public function projectProgress(self $project, int|string $lastTask): array
    {
        $total = $project->tasks->count();
        $completed = $project->tasks
            ->where(PJC::COL_STAGE_ID, $lastTask)
            ->where(PJC::COL_IS_CP, 1)
            ->count();
        $percentage = $total > 0 ? intval(($completed / $total) * 100) : 0;
        $color = Utility::getProgressColor($percentage);
        return [PJC::COL_CL => $color, 'percentage' => $percentage . '%'];
    }

    public function projectProgressCopy(int|string $userId): array
    {
        $last = TaskStage::orderBy('order', 'desc')
            ->where(DC::COL_TABLE_CREATOR, $userId)
            ->first();
        $total = $this->tasks->count();
        $completed = $this->tasks()
            ->where(PJC::COL_STAGE_ID, $last->id)
            ->where(PJC::COL_IS_CP, 1)
            ->count();
        $percentage = $total > 0 ? intval(($completed / $total) * 100) : 0;
        $color = Utility::getProgressColor($percentage);
        return [PJC::COL_CL => $color, 'percentage' => $percentage . '%'];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, PJC::COL_PJ_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_users',
            PJC::COL_PJ_ID,
            UC::COL_USER_ID
        );
    }

    public function client(): ?BelongsTo
    {
        return Utility::getClient($this);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, PJC::COL_PJ_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public function expense(): HasMany
    {
        return $this->hasMany(Expense::class, PJC::COL_PJ_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public static function getProjectAssignedTimesheetHtml(
        $projectsTimesheet = null,
        array $timesheets = [],
        array $days = [],
        int|string|null $projectId = null
    ): string {
        $allProjects = $projectId === '0';
        $timesheetArray = [];
        $totals = [];
        if ($allProjects) {
            foreach ($timesheets as $pid => $taskSheets) {
                $project = self::find($pid);
                if (!$project) continue;
                $taskArray = [];
                foreach ($taskSheets as $tid => $sheet) {
                    $task = ProjectTask::find($tid);
                    if (!$task) continue;
                    $users = $projectsTimesheet
                        ?->where(AC::COL_TSK_ID, $tid)
                        ->pluck(DC::COL_TABLE_CREATOR)
                        ->unique()
                        ->toArray() ?? [];
                    $dateArray = [];
                    foreach ($users as $uid) {
                        $week = [];
                        foreach ($days['datePeriod'] as $dateObj) {
                            $date = $dateObj->format('Y-m-d');
                            $entry = collect($sheet)
                                ->first(fn($v) => $v[DC::COL_TABLE_CREATOR] === $uid
                                    && $v['date'] === $date);
                            $time = $entry
                                ? Carbon::parse($entry['time'])->format('H:i')
                                : '00:00';
                            $type = $entry ? 'edit' : 'create';
                            $url = $entry
                                ? route(VW::PRJ . '.' . VW::TMS . '.edit', [$pid, $entry['id']])
                                : route(VW::PRJ . '.' . VW::TMS . '.create', $pid);
                            $week[] = compact('date', 'time', 'type', 'url');
                        }
                        $tot = Utility::calculateTimesheetHours(
                            array_column($week, 'time')
                        );
                        $dateArray[] = [
                            UC::COL_USER_ID   => $uid,
                            'user_name' => User::find($uid)?->name ?? '',
                            'week'      => $week,
                            'totaltime' => $tot
                        ];
                        $totals[] = $tot;
                    }
                    $taskArray[] = [
                        AC::COL_TSK_ID   => $task->id,
                        'task_name' => $task[PJC::COL_NM],
                        'dateArray' => $dateArray
                    ];
                }
                $timesheetArray[] = [
                    PJC::COL_PJ_ID   => $project->id,
                    PJC::COL_NM => $project->name,
                    'taskArray'    => $taskArray
                ];
            }
        } else {
            foreach ($timesheets as $tid => $sheet) {
                $task = ProjectTask::find($tid);
                if (!$task) continue;
                $week = [];
                $times = [];
                foreach ($days['datePeriod'] as $dateObj) {
                    $date = $dateObj->format('Y-m-d');
                    $entry = collect($sheet)
                        ->first(fn($v) => $v['date'] === $date);
                    $time = $entry
                        ? Carbon::parse($entry['time'])->format('H:i')
                        : '00:00';
                    $times[] = $time;
                    $week[] = [
                        'date' => $date,
                        'time' => $time,
                        'type' => $entry ? 'edit' : 'create',
                        'url'  => $entry
                            ? route(VW::PRJ . '.' . VW::TMS . '.edit', [$projectId, $entry['id']])
                            : route(VW::PRJ . '.' . VW::TMS . '.create', $projectId)
                    ];
                }
                $tot = Utility::calculateTimesheetHours($times);
                $timesheetArray[] = [
                    AC::COL_TSK_ID   => $task->id,
                    'task_name' => $task[PJC::COL_NM],
                    'dateArray' => $week,
                    'totaltime' => $tot
                ];
                $totals[] = $tot;
            }
        }

        $totalTime = Utility::calculateTimesheetHours($totals);
        $totalDateTimes = [];
        foreach ($days['datePeriod'] as $dateObj) {
            $d = $dateObj->format('Y-m-d');
            $times = $projectsTimesheet
                ?->where('date', $d)
                ->pluck('time')
                ->toArray() ?? [];
            $totalDateTimes[$d] = Utility::calculateTimesheetHours($times);
        }

        return view(DC::TABLE_PROJECTS . '.timesheets.week', compact(
            'timesheetArray',
            'totalDateTimes',
            'totalTime',
            'days',
            'allProjects'
        ))->render();
    }

    public function taskSections(): HasMany
    {
        return $this->hasMany(
            Milestone::class,
            PJC::COL_PJ_ID,
            'id'
        )->orderBy('id', 'desc');
    }

    /**
     * Get assigned project tasks
     * Pure alias to ProjectRequestService - handles auth internally
     */
    public static function getAssignedProjectTasks(
        int|string|null $projectId = null,
        int|string|null $stageId = null,
        array $filterData = []
    ): Builder {
        return app(ProjectRequestService::class)->getAssignedProjectTasks(
            $projectId,
            $stageId,
            $filterData
        );
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(
            Timesheet::class,
            PJC::COL_PJ_ID,
            'id'
        )->orderBy('id', 'desc');
    }

    public static function deleteProject(int|string $projectId): void
    {
        $project = self::find($projectId);
        if (!$project) return;
        Utility::checkFileExistsAndDelete([$project->image]);
        $project->milestones()->delete();
        $project->activities()->delete();
        $project->timesheets()->delete();
        $project->users()->detach();
        $taskIds = ProjectTask::where(PJC::COL_PJ_ID, $project->id)
            ->pluck('id')
            ->toArray();
        if ($taskIds) ProjectTask::deleteTask($taskIds);
        $project->delete();
    }

    public function label(): ?Label
    {
        return $this->hasOne(
            Label::class,
            'id',
            AC::COL_TSK_STT
        )->first();
    }

    public function projectUser(): HasMany
    {
        return $this->hasMany(
            ProjectUser::class,
            UC::COL_USER_ID,
            'id'
        );
    }

    /**
     * Count tasks for project
     * Pure alias to ProjectRequestService - handles auth internally
     */
    public function countTask(int|string $userId = 0): string
    {
        return app(ProjectRequestService::class)->countTask($this, $userId) ?? '';
    }

    /**
     * Get project status statistics
     * Pure alias to ProjectRequestService - handles auth internally
     */
    public static function getProjectStatus(): array
    {
        return app(ProjectRequestService::class)->getProjectStatus();
    }

    /**
     * Get last task stage for project creator
     * Pure alias to ProjectRequestService - handles auth internally
     */
    public function projectLastStage(): TaskStage|RedirectResponse|null
    {
        return app(ProjectRequestService::class)->getProjectLastStage();
    }

    public function projectTotalTask(int|string $projectId): int
    {
        return ProjectTask::where(PJC::COL_PJ_ID, $projectId)->count();
    }

    public function projectCompleteTask(
        int|string $projectId,
        int|string $lastStageId
    ): int {
        return ProjectTask::where(PJC::COL_PJ_ID, $projectId)
            ->where(PJC::COL_STAGE_ID, $lastStageId)
            ->count();
    }

    public function projectMilestoneProgress(): array
    {
        $milestones = Milestone::query()
            ->where(PJC::COL_PJ_ID, $this->id);
        $total = $milestones->count();
        $sum = $milestones->sum(PJC::COL_PGR);
        $pct = $total
            ? intval($sum / $total)
            : 0;
        return ['percentage' => $pct . '%'];
    }
}
