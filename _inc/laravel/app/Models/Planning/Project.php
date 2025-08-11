<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Traits\{ChecksLogin, UsesUuids};
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, Storage};
use Illuminate\{Database\Eloquent\Model, Support\Collection};
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne, BelongsToMany};

class Project extends Model
{
    use ChecksLogin, UsesUuids;

    protected $fillable = [
        ProjectsConstants::COL_NM,
        ProjectsConstants::COL_S_DT,
        ProjectsConstants::COL_E_DT,
        ProjectsConstants::COL_IMG,
        ProjectsConstants::COL_BUDGET,
        ProjectsConstants::COL_CLIENT_ID,
        ProjectsConstants::COL_STAGE_ID,
        ProjectsConstants::COL_DESCRIPTION,
        ProjectsConstants::COL_STATUS,
        ProjectsConstants::COL_E_HRS,
        ProjectsConstants::COL_PASSWORD,
        ProjectsConstants::COL_COPYLINK,
        ProjectsConstants::COL_TAGS,
        DatabaseConstants::TABLE_CREATOR,
    ];

    protected $appends = ['img_image'];

    private static $projectTask = NULL;

    public function milestones(): HasMany
    {
        return $this->hasMany(\App\Models\Milestone::class, ProjectsConstants::COL_PJ_ID, 'id');
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
        return TaskFile::whereIn(ActivitiesConstants::COL_TSK_ID, $ids)->get();
    }

    public static function projectHrs(int|string $projectId, int|string $taskId = ''): array
    {
        $allocated = self::projectTask($projectId)->sum(ProjectsConstants::COL_E_HRS);
        return ['allocated' => $allocated];
    }

    public static function projectTask(int|string $id): Collection
    {
        if (self::$projectTask === null)
            self::$projectTask = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $id)->get();
        return self::$projectTask;
        // * consider caching per project id instead of globally
    }

    public function projectProgress(self $project, int|string $lastTask): array
    {
        $total = $project->tasks->count();
        $completed = $project->tasks
            ->where(ProjectsConstants::COL_STAGE_ID, $lastTask)
            ->where(ProjectsConstants::COL_IS_CP, 1)
            ->count();
        $percentage = $total > 0 ? intval(($completed / $total) * 100) : 0;
        $color = Utility::getProgressColor($percentage);
        return [ProjectsConstants::COL_CL => $color, 'percentage' => $percentage . '%'];
    }

    public function projectProgressCopy(int|string $userId): array
    {
        $last = TaskStage::orderBy('order', 'desc')
            ->where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->first();
        $total = $this->tasks->count();
        $completed = $this->tasks()
            ->where(ProjectsConstants::COL_STAGE_ID, $last->id)
            ->where(ProjectsConstants::COL_IS_CP, 1)
            ->count();
        $percentage = $total > 0 ? intval(($completed / $total) * 100) : 0;
        $color = Utility::getProgressColor($percentage);
        return [ProjectsConstants::COL_CL => $color, 'percentage' => $percentage . '%'];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, ProjectsConstants::COL_PJ_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'project_users',
            ProjectsConstants::COL_PJ_ID,
            UsersConstants::COL_USER_ID
        );
    }

    public function client(): HasOne
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'client_id');
        // * consider belongsTo(User::class,'client_id','id')
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, ProjectsConstants::COL_PJ_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public function expense(): HasMany
    {
        return $this->hasMany(Expense::class, ProjectsConstants::COL_PJ_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public static function getProjectAssignedTimesheetHtml(
        $projectsTimesheet = null,
        array $timesheets = [],
        array $days = [],
        int|string $projectId = null
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
                        ?->where(ActivitiesConstants::COL_TSK_ID, $tid)
                        ->pluck(DatabaseConstants::TABLE_CREATOR)
                        ->unique()
                        ->toArray() ?? [];
                    $dateArray = [];
                    foreach ($users as $uid) {
                        $week = [];
                        foreach ($days['datePeriod'] as $dateObj) {
                            $date = $dateObj->format('Y-m-d');
                            $entry = collect($sheet)
                                ->first(fn ($v) => $v[DatabaseConstants::TABLE_CREATOR] === $uid
                                    && $v['date'] === $date);
                            $time = $entry
                                ? Carbon::parse($entry['time'])->format('H:i')
                                : '00:00';
                            $type = $entry ? 'edit' : 'create';
                            $url = $entry
                                ? route('timesheet.edit', [$pid, $entry['id']])
                                : route('timesheet.create', $pid);
                            $week[] = compact('date', 'time', 'type', 'url');
                        }
                        $tot = Utility::calculateTimesheetHours(
                            array_column($week, 'time')
                        );
                        $dateArray[] = [
                            UsersConstants::COL_USER_ID   => $uid,
                            'user_name' => User::find($uid)?->name ?? '',
                            'week'      => $week,
                            'totaltime' => $tot
                        ];
                        $totals[] = $tot;
                    }
                    $taskArray[] = [
                        ActivitiesConstants::COL_TSK_ID   => $task->id,
                        'task_name' => $task[ProjectsConstants::COL_NM],
                        'dateArray' => $dateArray
                    ];
                }
                $timesheetArray[] = [
                    ProjectsConstants::COL_PJ_ID   => $project->id,
                    ProjectsConstants::COL_NM => $project->name,
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
                        ->first(fn ($v) => $v['date'] === $date);
                    $time = $entry
                        ? Carbon::parse($entry['time'])->format('H:i')
                        : '00:00';
                    $times[] = $time;
                    $week[] = [
                        'date' => $date,
                        'time' => $time,
                        'type' => $entry ? 'edit' : 'create',
                        'url'  => $entry
                            ? route('timesheet.edit', [$projectId, $entry['id']])
                            : route('timesheet.create', $projectId)
                    ];
                }
                $tot = Utility::calculateTimesheetHours($times);
                $timesheetArray[] = [
                    ActivitiesConstants::COL_TSK_ID   => $task->id,
                    'task_name' => $task[ProjectsConstants::COL_NM],
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

        return view(DatabaseConstants::TABLE_PROJECTS . '.timesheets.week', compact(
            'timesheetArray',
            'totalDateTimes',
            'totalTime',
            'days',
            'allProjects'
        ))->render();
    }

    public function taskSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            \App\Models\Milestone::class,
            ProjectsConstants::COL_PJ_ID,
            'id'
        )->orderBy('id', 'desc');
    }

    public static function getAssignedProjectTasks(
        int|string $projectId = null,
        int|string $stageId = null,
        array $filterData = []
    ): \Illuminate\Database\Eloquent\Builder {
        $project = self::find($projectId);
        $user = Auth::user()
            ?: User::where('id', $project[DatabaseConstants::TABLE_CREATOR])->first();
        $ids = $user?->tasks()->pluck('id')->toArray();
        $q = ProjectTask::whereIn('id', $ids);
        $q = $project
            ? $q->where(ProjectsConstants::COL_PJ_ID, $projectId)
            : $q;
        if ($stageId) $q->where(ProjectsConstants::COL_STAGE_ID, $stageId);
        foreach ($filterData as $col => $val)
            if ($val !== null && $val !== '')
                $q->where($col, $val);
        return $q;
    }

    public function timesheets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            \App\Models\Timesheet::class,
            ProjectsConstants::COL_PJ_ID,
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
        $taskIds = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
            ->pluck('id')
            ->toArray();
        if ($taskIds) ProjectTask::deleteTask($taskIds);
        $project->delete();
    }

    public function label(): ?\App\Models\Label
    {
        return $this->hasOne(
            \App\Models\Label::class,
            'id',
            ActivitiesConstants::COL_TSK_STT
        )->first();
    }

    public function projectUser(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            \App\Models\ProjectUser::class,
            UsersConstants::COL_USER_ID,
            'id'
        );
    }

    public function countTask(int|string $userId = 0): string
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $authUser = $userOrRedirect;
        $userString = is_string($authUser->checkProject($this->id));
        $isOwner = $userString
            && strtolower($userString) === 'owner';
        $complete = $isOwner
            ? $this->tasks->where(ProjectsConstants::COL_IS_CP, 1)->count()
            : $this->tasks()
            ->where(ProjectsConstants::COL_IS_CP, 1)
            ->whereRaw("find_in_set('{$userId}'," . ProjectsConstants::COL_ASGN . ")")
            ->count();
        $total = $isOwner
            ? $this->tasks->count()
            : $this->tasks()
            ->whereRaw("find_in_set('{$userId}'," . ProjectsConstants::COL_ASGN . ")")
            ->count();
        return "{$complete}/{$total}";
    }

    public static function getProjectStatus(): array
    {
        $u = Auth::user();
        $type = $u->type;
        $keys = array_keys(ProjectsConstants::$projectStatus);
        $counts = [];
        foreach ($keys as $status) {
            $counts[$status] = match ($type) {
                PermissionsConstants::CPN => self::where(ActivitiesConstants::COL_TSK_STT, $status)
                    ->where(DatabaseConstants::TABLE_CREATOR, $u->id)->count(),
                PermissionsConstants::CL => self::where(ActivitiesConstants::COL_TSK_STT, $status)
                    ->where('client_id', $u->id)->count(),
                default => \App\Models\ProjectUser::join(
                    DatabaseConstants::TABLE_PROJECTS,
                    'project_users.' . ProjectsConstants::COL_PJ_ID,
                    '=',
                    DatabaseConstants::TABLE_PROJECTS . '.id'
                )->where(DatabaseConstants::TABLE_PROJECTS . '.' .
                    ActivitiesConstants::COL_TSK_STT, $status)
                    ->where(UsersConstants::COL_USER_ID, $u->id)->count()
            };
        }
        $total = array_sum($counts);
        return array_map(
            fn ($c) => $total
                ? round(($c / $total) * 100, 2)
                : 0,
            $counts
        );
    }

    public function projectLastStage(): ?\App\Models\TaskStage
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return TaskStage::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->orderBy('order', 'desc')
            ->first();
    }

    public function projectTotalTask(int|string $projectId): int
    {
        return ProjectTask::where(ProjectsConstants::COL_PJ_ID, $projectId)->count();
    }

    public function projectCompleteTask(
        int|string $projectId,
        int|string $lastStageId
    ): int {
        return ProjectTask::where(ProjectsConstants::COL_PJ_ID, $projectId)
            ->where(ProjectsConstants::COL_STAGE_ID, $lastStageId)
            ->count();
    }

    public function projectMilestoneProgress(): array
    {
        $milestones = \App\Models\Milestone::query()
            ->where(ProjectsConstants::COL_PJ_ID, $this->id);
        $total = $milestones->count();
        $sum = $milestones->sum(ProjectsConstants::COL_PGR);
        $pct = $total
            ? intval($sum / $total)
            : 0;
        return ['percentage' => $pct . '%'];
    }
}
