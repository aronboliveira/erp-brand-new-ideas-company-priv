<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};

class ProjectTask extends Model
{
    use ChecksLogin, UsesUuids;

    private const COL_ASSIGN_TO     = ProjectsConstants::COL_ASGN;
    private const COL_DESCRIPTION   = ActivitiesConstants::COL_DESC;
    private const COL_START_DATE    = ProjectsConstants::COL_S_DT;
    private const COL_END_DATE      = ProjectsConstants::COL_E_DT;
    private const COL_ESTIMATED_HRS = ProjectsConstants::COL_E_HRS;
    private const COL_IS_COMPLETE   = ProjectsConstants::COL_IS_CP;
    private const COL_IS_FAVOURITE  = ProjectsConstants::COL_IS_FV;
    private const COL_MARKED_AT     = ProjectsConstants::COL_M_AT;
    private const COL_MILESTONE_ID  = ProjectsConstants::COL_ML_ID;
    private const COL_NAME          = ProjectsConstants::COL_NM;
    private const COL_ORDER         = ActivitiesConstants::COL_OD;
    private const COL_PROGRESS      = ProjectsConstants::COL_PGR;
    private const COL_PRIORITY      = ProjectsConstants::COL_PRT;
    private const COL_PRIORITY_COLOR = ProjectsConstants::COL_PR_CL;
    private const COL_PROJECT_ID    = ProjectsConstants::COL_PJ_ID;
    private const COL_STAGE_ID      = ProjectsConstants::COL_STAGE_ID;
    private const COL_CREATED_BY    = DatabaseConstants::TABLE_CREATOR;

    private const FILLABLE = [
        self::COL_NAME,
        self::COL_DESCRIPTION,
        self::COL_ESTIMATED_HRS,
        self::COL_START_DATE,
        self::COL_END_DATE,
        self::COL_PRIORITY,
        self::COL_PRIORITY_COLOR,
        self::COL_ASSIGN_TO,
        self::COL_PROJECT_ID,
        self::COL_MILESTONE_ID,
        self::COL_STAGE_ID,
        self::COL_ORDER,
        self::COL_CREATED_BY,
        self::COL_IS_FAVOURITE,
        self::COL_IS_COMPLETE,
        self::COL_MARKED_AT,
        self::COL_PROGRESS,
    ];

    protected $fillable = self::FILLABLE;

    public static array $priority = [
        'critical' => 'Critical',
        'high'     => 'High',
        'medium'   => 'Medium',
        'low'      => 'Low',
    ];

    public static array $priorityColor = [
        'critical' => ProjectsConstants::STT_DGR,
        'high'     => ProjectsConstants::STT_WRN,
        'medium'   => 'primary',
        'low'      => ProjectsConstants::STT_INF,
    ];

    public function milestone(): HasOne
    {
        return $this->hasOne(Milestone::class, 'id', self::COL_MILESTONE_ID);
        // * consider belongsTo(Milestone::class, self::COL_MILESTONE_ID,'id')
    }

    public function users(): Collection
    {
        // * consider a proper relation via belongsToMany with pivot table
        return User::whereIn('id', explode(',', $this->assign_to))->get();
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', self::COL_PROJECT_ID);
        // * consider belongsTo(Project::class, self::COL_PROJECT_ID,'id')
    }

    public function stage(): HasOne
    {
        return $this->hasOne(TaskStage::class, 'id', self::COL_STAGE_ID);
        // * consider belongsTo(TaskStage::class, self::COL_STAGE_ID,'id')
    }

    public function taskProgress(Project $project): array
    {
        $total     = $this->checklist->count();
        $completed = $project->checklist->where(ActivitiesConstants::COL_TSK_STT, '1')->count();
        $pct       = $total > 0 ? intval($completed / $total * 100) : 0;
        $color     = Utility::getProgressColor($pct);
        return [ProjectsConstants::COL_CL => $color, 'percentage' => "$pct%"];
    }

    public function taskUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_ASSIGN_TO);
        // * consider belongsTo(User::class, self::COL_ASSIGN_TO,'id')
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(TaskChecklist::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function taskFiles(): HasMany
    {
        return $this->hasMany(TaskFile::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function countTaskChecklist(): string
    {
        $done = $this->checklist->where(ActivitiesConstants::COL_TSK_STT, 1)->count();
        $total = $this->checklist->count();
        return "$done/$total";
    }

    public static function deleteTask(array $taskIds): bool
    {
        try {
            DB::transaction(function () use ($taskIds) {
                foreach ($taskIds as $id) {
                    $task = self::find($id);
                    if (!$task) continue;
                    $files = TaskFile::where(ActivitiesConstants::COL_TSK_ID, $task->id)
                        ->pluck('file')->toArray();
                    Utility::checkFileExistsAndDelete($files);
                    TaskFile::where(ActivitiesConstants::COL_TSK_ID, $task->id)->delete();
                    $task->timesheets()->delete();
                    TaskChecklist::where(ActivitiesConstants::COL_TSK_ID, $task->id)->delete();
                    TaskComment::where(ActivitiesConstants::COL_TSK_ID, $task->id)->delete();
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
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return ActivityLog::where(UsersConstants::COL_USER_ID, $user::id())
            ->where(ProjectsConstants::COL_PJ_ID, $this[ProjectsConstants::COL_PJ_ID])
            ->where(ActivitiesConstants::COL_TSK_ID, $this->id)
            ->get();
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->orderBy('id', 'desc');
    }

    public static function getAllSectionedTaskList(
        Request $request,
        $project,
        array $filterData = [],
        array $exclude   = []
    ): array {
        // * consider moving to dedicated service
        $sections   = $project->tasksections()->pluck(ActivitiesConstants::COL_TT, 'id')->toArray();
        $sectionIds = array_keys($sections);
        $allTasks   = Project::getAssignedProjectTasks(
            $project->id,
            null,
            $filterData
        );
        $result     = [];
        $controller = app(\App\Http\Controllers\ProjectTaskController::class);
        // others
        $others = $allTasks
            ->whereNotIn(ProjectsConstants::COL_ML_ID, $sectionIds)
            ->whereNotIn('id', $exclude)
            ->orderBy('id', 'desc')
            ->pluck('id')
            ->toArray();
        if ($others)
            $result[] = self::buildSection(0, '', $others, $request, $controller);
        // per-section
        foreach ($sections as $secId => $secName) {
            $ids = $allTasks
                ->where(DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_ML_ID, $secId)
                ->whereNotIn('id', $exclude)
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->toArray();
            $result[] = self::buildSection($secId, $secName, $ids, $request, $controller);
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
        $tasks = array_map(fn ($tid) => tap(
            self::find($tid)->toArray(),
            fn (&$t) => $t['taskinfo'] = json_decode(
                $controller->getDefaultTaskInfo($request, $tid),
                true
            )
        ), $taskIds);
        return [
            'section_id'    => $secId,
            'section_name'  => $secName,
            'sections'      => $tasks,
            'sectionsClass' => 'active',
        ];
    }
}
