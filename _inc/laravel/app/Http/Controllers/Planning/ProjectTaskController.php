<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{
    ActivityLog,
    Bug,
    BugStatus,
    Project,
    ProjectTask,
    TaskComment,
    TaskChecklist,
    TaskFile,
    TaskStage,
    User,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{DB, Log, Storage};
use Illuminate\View\View;
use Throwable;

class ProjectTaskController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ENTITY = 'project';
    private const SINGULAR = self::ENTITY . '_task';
    private const REDIRECT_INDEX = ViewsConstants::PRJ . '.index';

    public function index(Request $request, string|int $projectId)
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_PRJ_TSK, self::REDIRECT_INDEX)) !== true) return $redirect;
        try {
            $creatorId = $user?->creatorId();
            $project  = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->firstOrFail();
            $stages = TaskStage::orderBy(ActivitiesConstants::COL_OD)
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->get();
            foreach ($stages as $stage) {
                $stage->cssClass = 'task-list-' . $stage->id;
                $stage[DatabaseConstants::TABLE_TASKS]   = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $projectId)
                    ->where('stage_id', $stage->id)
                    ->orderBy(ActivitiesConstants::COL_OD)
                    ->get();
            }
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(self::ENTITY, 'stages'));
        } catch (Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function create(Request $request, string|int $projectId, string|int $stageId)
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create project task', self::REDIRECT_INDEX)) !== true) return $redirect;
        try {
            $creatorId = $user?->creatorId();
            $project  = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->firstOrFail();

            $hrs     = Project::projectHrs($projectId);
            $settings = Utility::settings($creatorId);
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(
                self::ENTITY,
                'stageId',
                'hrs',
                DatabaseConstants::TABLE_SETTINGS
            ));
        } catch (Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function store(Request $request, string|int $projectId, string|int $stageId): ?RedirectResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create project task', self::REDIRECT_INDEX)) !== true) return $redirect;
        $validator = $request->validate([
            ProjectsConstants::COL_NM          => 'required|string',
            ProjectsConstants::COL_E_HRS  => 'required|numeric',
            ProjectsConstants::COL_PRT      => 'required|string',
        ]);
        DB::beginTransaction();
        try {
            $creatorId = $user?->creatorId();
            $project  = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->firstOrFail();
            $data = array_merge(
                $validator,
                [
                    ProjectsConstants::COL_PJ_ID => $project->id,
                    'stage_id'   => $stageId,
                    ProjectsConstants::COL_ASGN  => $request->input(ProjectsConstants::COL_ASGN),
                    ProjectsConstants::COL_S_DT => $request->input(ProjectsConstants::COL_S_DT),
                    ProjectsConstants::COL_E_DT   => $request->input(ProjectsConstants::COL_E_DT),
                    DatabaseConstants::TABLE_CREATOR => $creatorId,
                ]
            );

            // ! ALERT: ensure 'markedAt' logic is correct
            if (
                $stageId == TaskStage::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->orderByDesc(ActivitiesConstants::COL_OD)->first()->id
            )
                $data['marked_at'] = now()->toDateString();
            /** @var ProjectTask $task */
            $task = ProjectTask::create($data);
            ActivityLog::create([
                UsersConstants::COL_USER_ID    => $user?->id,
                ProjectsConstants::COL_PJ_ID => $project->id,
                ActivitiesConstants::COL_TSK_ID    => $task->id,
                'log_type'   => 'Create Task',
                'remark'     => json_encode(['title' => $task[ProjectsConstants::COL_NM]]),
            ]);

            // Slack / Telegram notifications
            $settings  = Utility::settings($creatorId);
            $payload   = [
                'taskName' => $task[ProjectsConstants::COL_NM],
                'projectName' => $project[ProjectsConstants::COL_NM],
                'userName' => $user[UsersConstants::COL_NM]
            ];
            $settings['taskNotification'] ?? null
                ? Utility::sendSlackMsg('new_task', $payload)
                : null;
            $settings['telegramTaskNotification'] ?? null
                ? Utility::sendTelegramMsg('new_task', $payload)
                : null;
            // Google Calendar sync
            if ($request->input('synchronizeType') === 'google_calendar') {
                Utility::addCalendarData((object)[
                    'title' => $task[ProjectsConstants::COL_NM],
                    ProjectsConstants::COL_S_DT => $task[ProjectsConstants::COL_S_DT],
                    ProjectsConstants::COL_E_DT => $task[ProjectsConstants::COL_E_DT]
                ], 'task');
            }

            // Webhook
            if ($hook = Utility::webhookSetting('New Task')) {
                $ok = Utility::webhookCall($hook['url'], $task->toJson(), $hook['method']);
                if (!$ok) throw new \Exception('Webhook call failed');
            }

            DB::commit();
            return redirect()->back()->with('success', __('Task added successfully.'));
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const TSK_BD = 'taskBoard';
    public function taskBoard(Request $request, string $view): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", ['view' => $view, UsersConstants::COL_USER_ID => $user?->id]);
        try {
            if ($view === 'list') return view(self::SINGULAR . '.taskboard', compact('view'));
            $creatorId   = $user?->creatorId();
            $userProjects = $user->type == PermissionsConstants::CL
                ? Project::where('client_id', $user?->id)->pluck('id', 'id')->toArray()
                : $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID, ProjectsConstants::COL_PJ_ID)->toArray();
            $tasks = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $userProjects);
            if ($user->type != PermissionsConstants::CPN) {
                $tasks = $user->type == PermissionsConstants::CL
                    ? $tasks->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    : $tasks->whereRaw("find_in_set('{$user?->id}'," . ProjectsConstants::COL_ASGN . ")");
            } else {
                $tasks->where(DatabaseConstants::TABLE_CREATOR, $creatorId);
            }
            $tasks = $tasks->get();
            Log::info("$action fetched tasks", ['count' => $tasks->count()]);
            return view(self::SINGULAR . '.grid', compact(DatabaseConstants::TABLE_TASKS, 'view'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const TSK_BD_VW = 'taskBoardView';
    public function taskBoardView(Request $request): JsonResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return null;
        if (!$request->ajax() || !$request->has(['view', 'sort'])) return null;
        if (self::guard($request, 'view project task', self::REDIRECT_INDEX))
            return response()->json(['error' => 'Permission denied.'], 401);
        Log::info("$action start", $request->only('sort', 'keyword', ActivitiesConstants::COL_TSK_ID));
        try {
            $creatorId   = $user?->creatorId();
            $userProjects = $user->type == PermissionsConstants::CL
                ? Project::where('client_id', $user?->id)->pluck('id', 'id')->toArray()
                : $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID, ProjectsConstants::COL_PJ_ID)->toArray();
            [$col, $dir]  = explode('-', $request->sort);
            $tasks       = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $userProjects)
                ->orderBy($col, $dir);
            if ($user->type != PermissionsConstants::CPN) {
                $tasks = $user->type == PermissionsConstants::CL
                    ? $tasks->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    : $tasks->whereRaw("find_in_set('{$user?->id}'," . ProjectsConstants::COL_ASGN . ")");
            } else {
                $tasks->where(DatabaseConstants::TABLE_CREATOR, $creatorId);
            }
            if ($keyword = $request->keyword) {
                $tasks->where(ProjectsConstants::COL_NM, 'LIKE', "$keyword%");
            }
            if ($statuses = (array)$request[ActivitiesConstants::COL_TSK_ID]) {
                $today = now()->toDateString();
                $plain = array_diff($statuses, ['due_today', 'over_due', 'starred', 'see_my_tasks']);
                $plain && $tasks->whereIn(ProjectsConstants::COL_PRT, $plain);
                in_array('due_today', $statuses)  && $tasks->where(ProjectsConstants::COL_E_DT, $today);
                in_array('over_due', $statuses)   && $tasks->where(ProjectsConstants::COL_E_DT, '<', $today);
                in_array('starred', $statuses)    && $tasks->where(ProjectsConstants::COL_IS_FV, 1);
            }
            $tasks    = $tasks->with(self::ENTITY)->get();
            $html     = view("project_task.{$request->view}", [DatabaseConstants::TABLE_TASKS => $tasks, 'view' => $request->view])->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public const ALL_BUG = 'allBugList';
    public function allBugList(Request $request, string $view): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", ['view' => $view, UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $creatorId = $user?->creatorId();
            $bugStatus = BugStatus::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
            if ($user->type == PermissionsConstants::CPN) {
                $bugs = Bug::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->with([
                    self::ENTITY,
                    DatabaseConstants::TABLE_CREATOR,
                    'project_bug'
                ])->get();
            } elseif ($user->type == PermissionsConstants::CL) {
                $ids = Project::where('client_id', $user?->id)->pluck('id', 'id')->toArray();
                $bugs = Bug::whereIn(ProjectsConstants::COL_PJ_ID, $ids)
                    ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    ->with([self::ENTITY, DatabaseConstants::TABLE_CREATOR])
                    ->get();
            } else {
                $bugs = Bug::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    ->whereRaw("find_in_set('{$user?->id}'," . ProjectsConstants::COL_ASGN . ")")
                    ->with([self::ENTITY, DatabaseConstants::TABLE_CREATOR])
                    ->get();
            }
            Log::info("$action fetched bugs", ['count' => $bugs->count()]);
            $tpl = $view === 'list' ? 'projects.allBugListView' : 'projects.allBugGridView';
            return view($tpl, compact('bugs', 'bug_status', 'view'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * Show a single task.
     */
    public function show(Request $request, string|int $projectId, string|int $taskId): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId, UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $allowProgress = Project::whereKey($projectId)->value('task_progress');
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $projectId)->findOrFail($taskId);
            Log::info("$action loaded task", [ActivitiesConstants::COL_TSK_ID => $taskId]);
            return view(self::SINGULAR . '.view', compact('task', 'allowProgress'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * Edit a task.
     */
    public function edit(Request $request, string|int $projectId, string|int $taskId): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'edit project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        try {
            $project = Project::findOrFail($projectId);
            $task   = ProjectTask::findOrFail($taskId);
            $hrs    = Project::projectHrs($projectId);
            Log::info("$action data ready");
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(self::ENTITY, 'task', 'hrs'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * Update a task.
     */
    public function update(Request $request, string|int $projectId, string|int $taskId): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'edit project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [
            ProjectsConstants::COL_PJ_ID => $projectId,
            ActivitiesConstants::COL_TSK_ID => $taskId,
            'input' => $request->all()
        ]);
        $data = $request->validate([
            ProjectsConstants::COL_NM          => 'required|string',
            ProjectsConstants::COL_E_HRS  => 'required|numeric',
            ProjectsConstants::COL_PRT      => 'required|string',
        ]);
        try {
            // ensure project exists and belongs to this user
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            // ensure task belongs to that project
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $task->update($data);
            Log::info("$action updated", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            return redirect()
                ->route('projects.tasks.index', $projectId)
                ->with('success', __('Task updated successfully.'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("$action not found", ['error' => $e->getMessage()]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('error', __('Project or Task not found.'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, string|int $projectId, string|int $taskId): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'delete project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        DB::beginTransaction();
        try {
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            ProjectTask::deleteTask([$task->id]);
            DB::commit();
            Log::info("$action deleted", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            return redirect()
                ->route('projects.tasks.index', $projectId)
                ->with('success', __('Task deleted successfully.'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning("$action not found", ['error' => $e->getMessage()]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('error', __('Project or Task not found.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const GET_STG_TSK = 'getStageTasks';
    public function getStageTasks(Request $request, string|int $stageId): JsonResponse|null
    {
        $action = __METHOD__;
        if ((self::_checkLogin()) instanceof RedirectResponse) return null;
        if (self::guard($request, 'view project task', self::REDIRECT_INDEX)) return response()->json(['error' => 'Permission denied.'], 401);
        Log::info("$action start", ['stage_id' => $stageId]);
        try {
            $count = ProjectTask::where('stage_id', $stageId)->count();
            return response()->json(['count' => $count]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public const CG_COM = 'changeCom';
    public function changeCom(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        try {
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $creatorId = $user?->creatorId();
            // pick either last or first stage
            $stage = $task[ProjectsConstants::COL_IS_CP] == 0
                ? TaskStage::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->orderByDesc(ActivitiesConstants::COL_OD)->first()
                : TaskStage::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->orderBy(ActivitiesConstants::COL_OD)->first();

            $task[ProjectsConstants::COL_IS_CP] = $task[ProjectsConstants::COL_IS_CP] ? 0 : 1;
            $task->marked_at  = $task[ProjectsConstants::COL_IS_CP] ? now()->toDateString() : null;
            $task->stage_id   = $stage->id;
            $task->save();

            Log::info("$action toggled", [
                ActivitiesConstants::COL_TSK_ID => $task->id,
                'isComplete' => $task[ProjectsConstants::COL_IS_CP]
            ]);
            return response()->json([
                'com'   => $task[ProjectsConstants::COL_IS_CP],
                'task'  => $task->id,
                'stage' => $stage->id,
            ]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CG_FAV = 'changeFav';
    public function changeFav(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        try {
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $task[ProjectsConstants::COL_IS_FV] = $task[ProjectsConstants::COL_IS_FV] ? 0 : 1;
            $task->save();
            Log::info("$action toggled", [
                ActivitiesConstants::COL_TSK_ID => $task->id,
                'fav' => $task[ProjectsConstants::COL_IS_FV]
            ]);
            return response()->json(['fav' => $task[ProjectsConstants::COL_IS_FV]]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CG_PRG = 'changeProg';
    public function changeProg(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [
            ProjectsConstants::COL_PJ_ID => $projectId,
            ActivitiesConstants::COL_TSK_ID => $taskId,
            ProjectsConstants::COL_PGR => $request->progress
        ]);
        try {
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $task->progress = (float)$request->progress;
            $task->save();
            Log::info("$action updated", [
                ActivitiesConstants::COL_TSK_ID => $task->id,
                ProjectsConstants::COL_PGR => $task->progress
            ]);
            return response()->json([ActivitiesConstants::COL_TSK_ID => $task->id]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CHKL_STR = 'checkListStore';
    public function checkListStore(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        try {
            $data = $request->validate([ProjectsConstants::COL_NM => 'required|string']);
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $check = TaskChecklist::create([
                ActivitiesConstants::COL_TSK_ID    => $taskId,
                ProjectsConstants::COL_NM       => $data[ProjectsConstants::COL_NM],
                ActivitiesConstants::COL_TSK_ID     => 0,
                'user_type'  => 'User',
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);

            $check->updateUrl = route('projects.tasks.checklist.update', [$projectId, $check->id]);
            $check->deleteUrl = route('projects.tasks.checklist.destroy', [$projectId, $check->id]);
            Log::info("$action created", ['checklistId' => $check->id]);
            return response()->json($check);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CHKL_UPD = 'checkListUpdate';
    public function checklistUpdate(Request $request, string|int $projectId, string|int $checklistId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, 'checklistId' => $checklistId]);
        try {
            $check = TaskChecklist::findOrFail($checklistId);
            if ($check->created_by !== $user?->creatorId()) throw new AuthorizationException;
            $check[ActivitiesConstants::COL_TSK_ID] = $check[ActivitiesConstants::COL_TSK_ID] ? 0 : 1;
            $check->save();
            Log::info("$action toggled", [
                'checklistId' => $check->id,
                ActivitiesConstants::COL_TSK_ID => $check[ActivitiesConstants::COL_TSK_ID]
            ]);
            return response()->json($check);
        } catch (AuthorizationException $e) {
            Log::warning("$action denied", ['error' => $e->getMessage()]);
            return defaultPermissionDenial($request, $e, $action, route(self::REDIRECT_INDEX));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CHKL_DST = 'checkListDestroy';
    public function checkListDestroy(Request $request, string|int $projectId, string|int $checklistId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, 'checklistId' => $checklistId]);
        try {
            $check = TaskChecklist::findOrFail($checklistId);
            if ($check->created_by !== $user?->creatorId()) throw new AuthorizationException;
            $check->delete();

            Log::info("$action deleted", ['checklistId' => $checklistId]);
            return response()->json(true);
        } catch (AuthorizationException $e) {
            Log::warning("$action denied", ['error' => $e->getMessage()]);
            return defaultPermissionDenial($request, $e, $action, route(self::REDIRECT_INDEX));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CM_STR_F = 'commentStoreFile';
    public function commentStoreFile(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        try {
            $request->validate(['file' => 'required|file']);
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $file    = $request->file('file');
            $fileName = $taskId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path    = $file->storeAs(DatabaseConstants::TABLE_TASKS, $fileName);
            $tf = TaskFile::create([
                ActivitiesConstants::COL_TSK_ID    => $taskId,
                'file'       => $fileName,
                'name'       => $file->getClientOriginalName(),
                'extension'  => $file->getClientOriginalExtension(),
                'file_size'  => round($file->getSize() / 1024 / 1024, 2) . ' MB',
                'user_type'  => 'User',
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);
            $tf->deleteUrl = route('projects.tasks.comment.file.destroy', [$projectId, $taskId, $tf->id]);
            Log::info("$action uploaded", ['fileId' => $tf->id]);
            return response()->json($tf);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CM_DST_F = 'commentDestroyFile';
    public function commentDestroyFile(Request $request, string|int $projectId, string|int $taskId, string|int $fileId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, 'fileId' => $fileId]);
        try {
            $tf = TaskFile::findOrFail($fileId);
            if ($tf->created_by !== $user?->creatorId()) throw new AuthorizationException;
            Storage::delete('tasks/' . $tf->file);
            $tf->delete();
            Log::info("$action deleted", ['fileId' => $fileId]);
            return response()->json(true);
        } catch (AuthorizationException $e) {
            Log::warning("$action denied", ['error' => $e->getMessage()]);
            return defaultPermissionDenial($request, $e, $action, route(self::REDIRECT_INDEX));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CM_DST = 'commentDestroy';
    public function commentDestroy(Request $request, string|int $projectId, string|int $taskId, string|int $commentId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, 'commentId' => $commentId]);
        try {
            $c = TaskComment::findOrFail($commentId);
            if ($c->created_by !== $user?->creatorId()) throw new AuthorizationException;
            $c->delete();
            Log::info("$action deleted", ['commentId' => $commentId]);
            return response()->json(true);
        } catch (AuthorizationException $e) {
            Log::warning("$action denied", ['error' => $e->getMessage()]);
            return defaultPermissionDenial($request, $e, $action, route(self::REDIRECT_INDEX));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CM_STR = 'commentStore';
    public function commentStore(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [
            ProjectsConstants::COL_PJ_ID => $projectId,
            ActivitiesConstants::COL_TSK_ID => $taskId
        ]);
        try {
            $data = $request->validate(['comment' => 'required|string']);
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $c = TaskComment::create([
                ActivitiesConstants::COL_TSK_ID    => $taskId,
                UsersConstants::COL_USER_ID    => $user?->id,
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                'user_type'  => $user?->type,
                'comment'    => $data['comment'],
            ]);
            $c->deleteUrl   = route('projects.tasks.comment.destroy', [$projectId, $taskId, $c->id]);
            $c->current_time = $c->created_at->diffForHumans();
            $c->default_img = asset(Storage::url('uploads/avatar/avatar.png'));
            // optional: notifications & webhook (omitted for brevity)
            Log::info("$action created", ['commentId' => $c->id]);
            return response()->json($c);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const TSK_OD_UPD = 'taskOrderUpdate';
    public function taskOrderUpdate(Request $request, string|int $projectId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, 'sort' => $request->sort]);
        try {
            DB::beginTransaction();
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            foreach ($request->input('sort', []) as $idx => $tid)
                ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                    ->whereKey($tid)
                    ->update([ActivitiesConstants::COL_OD => $idx]);
            DB::commit();
            Log::info("$action reordered");
            return response()->json(true);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const GET_TSK = 'taskGet';
    public function taskGet(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        try {
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            $html = view(self::SINGULAR . '.partials.card', compact('task'))->render();
            return response()->json(['html' => $html]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }


    public const GET_DF_TSK_IF = 'getDefaultTaskInfo';
    public function getDefaultTaskInfo(Request $request, string|int $projectId, string|int $taskId): JsonResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX))
            return $resp;
        Log::info("$action start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
        try {
            $project = Project::whereKey($projectId)
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->firstOrFail();
            $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                ->whereKey($taskId)
                ->firstOrFail();
            Log::info("$action loaded", [ActivitiesConstants::COL_TSK_ID => $task->id]);
            return response()->json([
                'task_name'     => $task[ProjectsConstants::COL_NM],
                'task_due_date' => $task->due_date,
            ]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public const CLD_VW = 'calendarView';
    public function calendarView(Request $request, string $taskBy, mixed $projectId = null): View|RedirectResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", ['taskBy' => $taskBy, ProjectsConstants::COL_PJ_ID => $projectId]);
        try {
            $creatorId   = $user?->creatorId();
            $type        = $user?->type;
            $usrId       = $user?->id;
            $userProjects = $type === PermissionsConstants::CL
                ? Project::where('client_id', $usrId)->pluck('id')->toArray()
                : $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID)->toArray();
            if ($projectId) $userProjects = [$projectId];
            $tasksQuery = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $userProjects);
            if ($type !== PermissionsConstants::CPN && $type !== PermissionsConstants::CL)
                $tasksQuery->whereRaw("find_in_set('{$usrId}'," . ProjectsConstants::COL_ASGN . ")");
            if ($type === PermissionsConstants::CL && $taskBy === 'all') {
                $tasksQuery->where(DatabaseConstants::TABLE_CREATOR, $creatorId);
            } elseif ($type !== PermissionsConstants::CL && $taskBy === 'my') {
                $tasksQuery->whereRaw("find_in_set('{$usrId}'," . ProjectsConstants::COL_ASGN . ")");
            }
            $tasks    = $tasksQuery->get();
            $transdate = date('Y-m-d');
            $arrTasks = Utility::getTaskCalendarArray($tasks);
            return view('tasks.calendar', compact('arrTasks', ProjectsConstants::COL_PJ_ID, 'taskBy', 'transdate'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const CLD_SHW = 'calendarShow';
    public function calendarShow(Request $request, string|int $projectId, string|int $taskId)
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        $task = ProjectTask::findOrFail($taskId);
        return view('tasks.calendar_show', compact('task'));
    }

    public const CLD_DRG = 'calendarDrag';
    public function calendarDrag(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX)) return $resp;
        Log::info("$action start", [ActivitiesConstants::COL_TSK_ID => $taskId, 'start' => $request->start, 'end' => $request->end]);
        try {
            $task = ProjectTask::findOrFail($taskId);
            $task[ProjectsConstants::COL_S_DT] = $request->input('start');
            $task[ProjectsConstants::COL_E_DT]  = $request->input('end');
            $task->save();
            return response()->json(true);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public const GET_TSK_D = 'getTaskData';
    public function getTaskData(Request $request, mixed $projectId = null): JsonResponse|RedirectResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($resp = self::guard($request, 'view project task', self::REDIRECT_INDEX))
            return $resp;
        Log::info("$action start", [
            'calendarType' => $request->get('calendar_type'),
            ProjectsConstants::COL_PJ_ID    => $projectId,
        ]);
        try {
            if ($request->get('calendar_type') === 'google_calendar')
                $arrayJson = Utility::getCalendarData('task');
            else {
                $creatorId = $user?->creatorId();
                $q = ProjectTask::query();
                if ($projectId)
                    $q->where(ProjectsConstants::COL_PJ_ID, $projectId);
                if ($user->type == PermissionsConstants::CL) {
                    $proj = Project::where('client_id', $user?->id)->pluck('id');
                    $q->whereIn(ProjectsConstants::COL_PJ_ID, $proj);
                } elseif ($user->type != PermissionsConstants::CPN) {
                    $proj = $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID);
                    $q->whereIn(ProjectsConstants::COL_PJ_ID, $proj)
                        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                        ->whereRaw("find_in_set('{$user?->id}'," . ProjectsConstants::COL_ASGN . ")");
                } else
                    $q->where(DatabaseConstants::TABLE_CREATOR, $creatorId);
                $data = $q->get();
                $arrayJson = $data->map(function ($val) {
                    $end = date_create($val[ProjectsConstants::COL_E_DT]);
                    date_add($end, date_interval_create_from_date_string('1 days'));
                    return [
                        'id'         => $val->id,
                        'title'      => $val->name,
                        'start'      => $val[ProjectsConstants::COL_S_DT],
                        'end'        => date_format($end, 'Y-m-d H:i:s'),
                        'className'  => 'event-primary',
                        'textColor'  => '#51459d',
                        'allDay'     => true,
                        'url'        => route('task.calendar.show', $val->id),
                        'resize_url' => route('task.calendar.drag', $val->id),
                    ];
                })->toArray();
            }
            Log::info("$action complete", ['count' => count($arrayJson)]);
            return response()->json($arrayJson);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const UPD_TSK_PR_CL = 'updateTaskPriorityColor';
    public function updateTaskPriorityColor(Request $request): JsonResponse|RedirectResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($request, 'edit project task', self::REDIRECT_INDEX)) return $resp;
        $taskId = $request->input(ActivitiesConstants::COL_TSK_ID);
        $color = $request->input(ProjectsConstants::COL_CL);
        Log::info("$action start", [
            ActivitiesConstants::COL_TSK_ID => $taskId,
            ProjectsConstants::COL_CL => $color
        ]);
        try {
            $task = ProjectTask::findOrFail($taskId);
            $task->priority_color = $color;
            $task->save();
            Log::info("$action success", [ActivitiesConstants::COL_TSK_ID => $taskId]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
