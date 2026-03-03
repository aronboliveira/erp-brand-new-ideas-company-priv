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
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{DB, Log, Route, Storage, View as ViewFacade};
use Illuminate\View\View;
use Throwable;

class ProjectTaskController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ENTITY = 'project';
    private const SINGULAR = self::ENTITY . '_task';
    private const REDIRECT_INDEX = ViewsConstants::PRJ . '.index';

    public function index(Request $request, string|int $projectId): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = self::SINGULAR . 's.index';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_PRJ_TSK, self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                $creatorId = $user?->creatorId();
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
                $stages = TaskStage::orderBy(ActivitiesConstants::COL_OD)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
                foreach ($stages as $stage) {
                    $stage->cssClass = 'task-list-' . $stage->id;
                    $stage[DatabaseConstants::TABLE_TASKS] = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $projectId)->where('stage_id', $stage->id)->orderBy(ActivitiesConstants::COL_OD)->get();
                }
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                return view($viewPath, compact(self::ENTITY, 'stages'));
            } catch (Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId]);
    }

    public function create(Request $request, string|int $projectId, string|int $stageId)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = self::SINGULAR . 's.create';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $stageId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create project task', self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                $creatorId = $user?->creatorId();
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
                $hrs = Project::projectHrs($projectId);
                $settings = Utility::settings($creatorId);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                return view($viewPath, compact(self::ENTITY, 'stageId', 'hrs', DatabaseConstants::TABLE_SETTINGS));
            } catch (Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'stage_id' => $stageId]);
    }

    public function store(Request $request, string|int $projectId, string|int $stageId): ?RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $stageId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create project task', self::REDIRECT_INDEX)) !== true) return $redirect;
            $valStart = microtime(true);
            $validator = $req->validate([ProjectsConstants::COL_NM => 'required|string', ProjectsConstants::COL_E_HRS => 'required|numeric', ProjectsConstants::COL_PRT => 'required|string']);
            $this->logExecutionTime($valStart, $action, 'validateStore');
            DB::beginTransaction();
            try {
                $creatorId = $user?->creatorId();
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
                $data = array_merge($validator, [ProjectsConstants::COL_PJ_ID => $project->id, 'stage_id' => $stageId, ProjectsConstants::COL_ASGN => $req->input(ProjectsConstants::COL_ASGN), ProjectsConstants::COL_S_DT => $req->input(ProjectsConstants::COL_S_DT), ProjectsConstants::COL_E_DT => $req->input(ProjectsConstants::COL_E_DT), DatabaseConstants::COL_TABLE_CREATOR => $creatorId]);
                if ($stageId == TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->orderByDesc(ActivitiesConstants::COL_OD)->first()->id) $data['marked_at'] = now()->toDateString();
                $task = ProjectTask::create($data);
                ActivityLog::create([UsersConstants::COL_USER_ID => $user?->id, ProjectsConstants::COL_PJ_ID => $project->id, ActivitiesConstants::COL_TSK_ID => $task->id, 'log_type' => 'Create Task', 'remark' => json_encode(['title' => $task[ProjectsConstants::COL_NM]])]);
                $settings = Utility::settings($creatorId);
                $payload = ['taskName' => $task[ProjectsConstants::COL_NM], 'projectName' => $project[ProjectsConstants::COL_NM], 'userName' => $user[UsersConstants::COL_NM]];
                $settings['taskNotification'] ?? null ? Utility::sendSlackMsg('new_task', $payload) : null;
                $settings['telegramTaskNotification'] ?? null ? Utility::sendTelegramMsg('new_task', $payload) : null;
                if ($req->input('synchronizeType') === 'google_calendar') Utility::addCalendarData((object)['title' => $task[ProjectsConstants::COL_NM], ProjectsConstants::COL_S_DT => $task[ProjectsConstants::COL_S_DT], ProjectsConstants::COL_E_DT => $task[ProjectsConstants::COL_E_DT]], 'task');
                if ($hook = Utility::webhookSetting('New Task')) {
                    $ok = Utility::webhookCall($hook['url'], $task->toJson(), $hook['method']);
                    if (!$ok) throw new \Exception('Webhook call failed');
                }
                $txnStart = microtime(true);
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'storeTransaction');
                return redirect()->back()->with('success', __('Task added successfully.'));
            } catch (Throwable $e) {
                DB::rollBack();
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'stage_id' => $stageId]);
    }

    public const TSK_BD = 'taskBoard';
    public function taskBoard(Request $request, string $view): View|RedirectResponse|null|bool
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $view, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", ['view' => $view, UsersConstants::COL_USER_ID => $user?->id]);
            try {
                if ($view === 'list') {
                    $viewPath = self::SINGULAR . 's.taskboard';
                    if (!ViewFacade::exists($viewPath)) {
                        Log::debug("[{$class}::{$action}] view not found", ['view' => $viewPath]);
                        return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                    }
                    return view($viewPath, compact('view'));
                }
                $creatorId = $user?->creatorId();
                $userProjects = $user->{UsersConstants::COL_TP} == PermissionsConstants::CL ? Project::where('client_id', $user?->id)->pluck('id', 'id')->toArray() : $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID, ProjectsConstants::COL_PJ_ID)->toArray();
                $tasks = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $userProjects);
                if (($user->{UsersConstants::COL_TP} != PermissionsConstants::CPN && $user->{UsersConstants::COL_TP} != PermissionsConstants::SA))
                    $tasks = $user->{UsersConstants::COL_TP} == PermissionsConstants::CL ? $tasks->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId) : $tasks->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$user?->id]);
                else
                    $tasks = $tasks->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
                $tasks = $tasks->get();
                Log::info("[{$class}::{$action}] fetched tasks", ['count' => $tasks->count()]);
                $viewPath = self::SINGULAR . 's.grid';
                if (!ViewFacade::exists($viewPath)) {
                    Log::debug("[{$class}::{$action}] view not found", ['view' => $viewPath]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                return view($viewPath, compact(DatabaseConstants::TABLE_TASKS, 'view'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'view' => $view]);
    }

    public const TSK_BD_VW = 'taskBoardView';
    public function taskBoardView(Request $request): JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return null;
            $user = $userOrRedirect;
            if (!$req->ajax() || !$req->has(['view', 'sort'])) return null;
            if (self::guard($req, 'view project task', self::REDIRECT_INDEX)) return response()->json(['error' => __('Permission denied.')], 401);
            Log::info("[{$class}::{$action}] start", $req->only('sort', 'keyword', ActivitiesConstants::COL_TSK_ID));
            try {
                $creatorId = $user?->creatorId();
                $userProjects = $user->{UsersConstants::COL_TP} == PermissionsConstants::CL ? Project::where('client_id', $user?->id)->pluck('id', 'id')->toArray() : $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID, ProjectsConstants::COL_PJ_ID)->toArray();
                [$col, $dir] = explode('-', $req->sort);
                $tasks = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $userProjects)->orderBy($col, $dir);
                if ($user->{UsersConstants::COL_TP} != PermissionsConstants::CPN) $tasks = $user->{UsersConstants::COL_TP} == PermissionsConstants::CL ? $tasks->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId) : $tasks->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$user?->id]);
                else $tasks->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
                if ($keyword = $req->keyword) $tasks->where(ProjectsConstants::COL_NM, 'LIKE', "$keyword%");
                if ($statuses = (array)$req[ActivitiesConstants::COL_TSK_ID]) {
                    $today = now()->toDateString();
                    $plain = array_diff($statuses, ['due_today', 'over_due', 'starred', 'see_my_tasks']);
                    $plain && $tasks->whereIn(ProjectsConstants::COL_PRT, $plain);
                    in_array('due_today', $statuses) && $tasks->where(ProjectsConstants::COL_E_DT, $today);
                    in_array('over_due', $statuses) && $tasks->where(ProjectsConstants::COL_E_DT, '<', $today);
                    in_array('starred', $statuses) && $tasks->where(ProjectsConstants::COL_IS_FV, 1);
                }
                $tasks = $tasks->with(self::ENTITY)->get();
                $viewPath = "project_task.{$req->view}";
                if (!ViewFacade::exists($viewPath)) return response()->json(['error' => "HTTP 404: Page {$viewPath} not found!"], 404);
                $html = view($viewPath, [DatabaseConstants::TABLE_TASKS => $tasks, 'view' => $req->view])->render();
                return response()->json(['success' => true, 'html' => $html]);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'view' => $request->view]);
    }

    public const ALL_BUG = 'allBugList';
    public function allBugList(Request $request, string $view): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $view, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", ['view' => $view, UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $creatorId = $user?->creatorId();
                $bug_status = BugStatus::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
                if ($user->{UsersConstants::COL_TP} == PermissionsConstants::CPN) $bugs = Bug::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->with([self::ENTITY, DatabaseConstants::COL_TABLE_CREATOR, 'project_bug'])->get();
                elseif ($user->{UsersConstants::COL_TP} == PermissionsConstants::CL) {
                    $ids = Project::where('client_id', $user?->id)->pluck('id', 'id')->toArray();
                    $bugs = Bug::whereIn(ProjectsConstants::COL_PJ_ID, $ids)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->with([self::ENTITY, DatabaseConstants::COL_TABLE_CREATOR])->get();
                } else $bugs = Bug::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$user?->id])->with([self::ENTITY, DatabaseConstants::COL_TABLE_CREATOR])->get();
                Log::info("[{$class}::{$action}] fetched bugs", ['count' => $bugs->count()]);
                $tpl = $view === 'list' ? 'projects.allBugListView' : 'projects.allBugGridView';
                if (!ViewFacade::exists($tpl)) return redirect()->back()->with('error', "HTTP 404: Page {$tpl} not found!");
                return view($tpl, compact('bugs', 'bug_status', 'view'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'view' => $view]);
    }

    /**
     * Show a single task.
     */
    public function show(Request $request, string|int $projectId, string|int $taskId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = self::SINGULAR . 's.view';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId, UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $allowProgress = Project::whereKey($projectId)->value('task_progress');
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $projectId)->findOrFail($taskId);
                Log::info("[{$class}::{$action}] loaded task", [ActivitiesConstants::COL_TSK_ID => $taskId]);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                return view($viewPath, compact('task', 'allowProgress'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    /**
     * Edit a task.
     */
    public function edit(Request $request, string|int $projectId, string|int $taskId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = self::SINGULAR . 's.edit';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($resp = self::guard($req, 'edit project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $project = Project::findOrFail($projectId);
                $task = ProjectTask::findOrFail($taskId);
                $hrs = Project::projectHrs($projectId);
                Log::info("[{$class}::{$action}] data ready");
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                return view($viewPath, compact(self::ENTITY, 'task', 'hrs'));
            } catch (Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    /**
     * Update a task.
     */
    public function update(Request $request, string|int $projectId, string|int $taskId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'edit project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId, 'input' => $req->all()]);
            $valStart = microtime(true);
            $data = $req->validate([ProjectsConstants::COL_NM => 'required|string', ProjectsConstants::COL_E_HRS => 'required|numeric', ProjectsConstants::COL_PRT => 'required|string']);
            $this->logExecutionTime($valStart, $action, 'validateUpdate');
            try {
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                $task->update($data);
                Log::info("[{$class}::{$action}] updated", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
                return redirect()->route('projects.tasks.index', $projectId)->with('success', __('Task updated successfully.'));
            } catch (ModelNotFoundException $e) {
                Log::warning("[{$class}::{$action}] not found", ['error' => $e->getMessage()]);
                return redirect()->route(self::REDIRECT_INDEX)->with('error', __('Project or Task not found.'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, string|int $projectId, string|int $taskId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'delete project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            DB::beginTransaction();
            try {
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                ProjectTask::deleteTask([$task->id]);
                DB::commit();
                Log::info("[{$class}::{$action}] deleted", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
                return redirect()->route('projects.tasks.index', $projectId)->with('success', __('Task deleted successfully.'));
            } catch (ModelNotFoundException $e) {
                DB::rollBack();
                Log::warning("[{$class}::{$action}] not found", ['error' => $e->getMessage()]);
                return redirect()->route(self::REDIRECT_INDEX)->with('error', __('Project or Task not found.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const GET_STG_TSK = 'getStageTasks';
    public function getStageTasks(Request $request, string|int $stageId): JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $stageId, $action, $method, $class) {
            if ((self::_checkLogin()) instanceof RedirectResponse) return null;
            if (self::guard($req, 'view project task', self::REDIRECT_INDEX)) return response()->json(['error' => __('Permission denied.')], 401);
            Log::info("[{$class}::{$action}] start", ['stage_id' => $stageId]);
            try {
                $count = ProjectTask::where(ProjectsConstants::COL_STAGE_ID, $stageId)->count();
                return response()->json(['count' => $count]);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'stage_id' => $stageId]);
    }

    public const CG_COM = 'changeCom';
    public function changeCom(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                $creatorId = $user?->creatorId();
                $stage = $task[ProjectsConstants::COL_IS_CP] == 0 ? TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->orderByDesc(ActivitiesConstants::COL_OD)->first() : TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->orderBy(ActivitiesConstants::COL_OD)->first();
                $task[ProjectsConstants::COL_IS_CP] = $task[ProjectsConstants::COL_IS_CP] ? 0 : 1;
                $task->marked_at = $task[ProjectsConstants::COL_IS_CP] ? now()->toDateString() : null;
                $task->stage_id = $stage->id;
                $task->save();
                Log::info("[{$class}::{$action}] toggled", [ActivitiesConstants::COL_TSK_ID => $task->id, 'isComplete' => $task[ProjectsConstants::COL_IS_CP]]);
                return response()->json(['com' => $task[ProjectsConstants::COL_IS_CP], 'task' => $task->id, 'stage' => $stage->id]);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const CG_FAV = 'changeFav';
    public function changeFav(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                $task[ProjectsConstants::COL_IS_FV] = $task[ProjectsConstants::COL_IS_FV] ? 0 : 1;
                $task->save();
                Log::info("[{$class}::{$action}] toggled", [ActivitiesConstants::COL_TSK_ID => $task->id, 'fav' => $task[ProjectsConstants::COL_IS_FV]]);
                return response()->json(['fav' => $task[ProjectsConstants::COL_IS_FV]]);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const CG_PRG = 'changeProg';
    public function changeProg(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId, ProjectsConstants::COL_PGR => $req->progress]);
            try {
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                $task->progress = (float)$req->progress;
                $task->save();
                Log::info("[{$class}::{$action}] updated", [ActivitiesConstants::COL_TSK_ID => $task->id, ProjectsConstants::COL_PGR => $task->progress]);
                return response()->json([ActivitiesConstants::COL_TSK_ID => $task->id]);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId, 'progress' => $request->progress]);
    }

    public const CHKL_STR = 'checkListStore';
    public function checkListStore(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $valStart = microtime(true);
                $data = $req->validate([ProjectsConstants::COL_NM => 'required|string']);
                $this->logExecutionTime($valStart, $action, 'validateChecklistStore');
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                $check = TaskChecklist::create([ActivitiesConstants::COL_TSK_ID => $taskId, ProjectsConstants::COL_NM => $data[ProjectsConstants::COL_NM], 'user_type' => 'User', DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()]);
                $check->updateUrl = route('projects.tasks.checklist.update', [$projectId, $check->id]);
                $check->deleteUrl = route('projects.tasks.checklist.destroy', [$projectId, $check->id]);
                Log::info("[{$class}::{$action}] created", ['checklistId' => $check->id]);
                return response()->json($check);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const CHKL_UPD = 'checkListUpdate';
    public function checkListUpdate(Request $request, string|int $projectId, string|int $checklistId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $checklistId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, 'checklistId' => $checklistId]);
            try {
                $check = TaskChecklist::findOrFail($checklistId);
                if ($check->created_by !== $user?->creatorId()) throw new AuthorizationException;
                $check[ActivitiesConstants::COL_TSK_ID] = $check[ActivitiesConstants::COL_TSK_ID] ? 0 : 1;
                $check->save();
                Log::info("[{$class}::{$action}] toggled", ['checklistId' => $check->id, ActivitiesConstants::COL_TSK_ID => $check[ActivitiesConstants::COL_TSK_ID]]);
                return response()->json($check);
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] denied", ['error' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'checklist_id' => $checklistId]);
    }

    public const CHKL_DST = 'checkListDestroy';
    public function checkListDestroy(Request $request, string|int $projectId, string|int $checklistId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $checklistId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, 'checklistId' => $checklistId]);
            try {
                $check = TaskChecklist::findOrFail($checklistId);
                if ($check->created_by !== $user?->creatorId()) throw new AuthorizationException;
                $check->delete();
                Log::info("[{$class}::{$action}] deleted", ['checklistId' => $checklistId]);
                return response()->json(true);
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] denied", ['error' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'checklist_id' => $checklistId]);
    }

    public const CM_STR_F = 'commentStoreFile';
    public function commentStoreFile(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $valStart = microtime(true);
                $req->validate(['file' => 'required|file']);
                $this->logExecutionTime($valStart, $action, 'validateUpload');
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                $file = $req->file('file');
                $fileName = $taskId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs(DatabaseConstants::TABLE_TASKS, $fileName);
                $tf = TaskFile::create([ActivitiesConstants::COL_TSK_ID => $taskId, 'file' => $fileName, 'name' => $file->getClientOriginalName(), 'extension' => $file->getClientOriginalExtension(), 'file_size' => round($file->getSize() / 1024 / 1024, 2) . ' MB', 'user_type' => 'User', DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()]);
                $tf->deleteUrl = route('projects.tasks.comment.file.destroy', [$projectId, $taskId, $tf->id]);
                Log::info("[{$class}::{$action}] uploaded", ['fileId' => $tf->id]);
                return response()->json($tf);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const CM_DST_F = 'commentDestroyFile';
    public function commentDestroyFile(Request $request, string|int $projectId, string|int $taskId, string|int $fileId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $fileId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, 'fileId' => $fileId]);
            try {
                $tf = TaskFile::findOrFail($fileId);
                if ($tf->created_by !== $user?->creatorId()) throw new AuthorizationException;
                Storage::delete('tasks/' . $tf->file);
                $tf->delete();
                Log::info("[{$class}::{$action}] deleted", ['fileId' => $fileId]);
                return response()->json(true);
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] denied", ['error' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId, 'file_id' => $fileId]);
    }

    public const CM_DST = 'commentDestroy';
    public function commentDestroy(Request $request, string|int $projectId, string|int $taskId, string|int $commentId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $commentId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, 'commentId' => $commentId]);
            try {
                $c = TaskComment::findOrFail($commentId);
                if ($c->created_by !== $user?->creatorId()) throw new AuthorizationException;
                $c->delete();
                Log::info("[{$class}::{$action}] deleted", ['commentId' => $commentId]);
                return response()->json(true);
            } catch (AuthorizationException $e) {
                Log::warning("[{$class}::{$action}] denied", ['error' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId, 'comment_id' => $commentId]);
    }

    public const CM_STR = 'commentStore';
    public function commentStore(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $valStart = microtime(true);
                $data = $req->validate(['comment' => 'required|string']);
                $this->logExecutionTime($valStart, $action, 'validateCommentStore');
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                $c = TaskComment::create([ActivitiesConstants::COL_TSK_ID => $taskId, UsersConstants::COL_USER_ID => $user?->id, DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(), 'user_type' => $user?->type, 'comment' => $data['comment']]);
                $c->deleteUrl = route('projects.tasks.comment.destroy', [$projectId, $taskId, $c->id]);
                $c->current_time = $c->created_at->diffForHumans();
                $c->default_img = asset(Storage::url('uploads/avatar/avatar.png'));
                Log::info("[{$class}::{$action}] created", ['commentId' => $c->id]);
                return response()->json($c);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const TSK_OD_UPD = 'taskOrderUpdate';
    public function taskOrderUpdate(Request $request, string|int $projectId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, 'sort' => $req->sort]);
            try {
                $txnStart = microtime(true);
                DB::beginTransaction();
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                foreach ($req->input('sort', []) as $idx => $tid) ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($tid)->update([ActivitiesConstants::COL_OD => $idx]);
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'taskOrderUpdateTransaction');
                Log::info("[{$class}::{$action}] reordered");
                return response()->json(true);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'sort_count' => is_array($request->input('sort')) ? count($request->input('sort')) : 0]);
    }

    public const GET_TSK = 'taskGet';
    public function taskGet(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = self::SINGULAR . 's.partials.card';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                if (!ViewFacade::exists($viewPath)) return response()->json(['error' => "HTTP 404: Page {$viewPath} not found!"], 404);
                $html = view($viewPath, compact('task'))->render();
                return response()->json(['html' => $html]);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const GET_DF_TSK_IF = 'getDefaultTaskInfo';
    public function getDefaultTaskInfo(Request $request, string|int $projectId, string|int $taskId): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json([], Response::HTTP_UNAUTHORIZED);
            $user = $userOrRedirect;
            if (self::guard($req, 'view project task', self::REDIRECT_INDEX)) return response()->json(['error' => __('Permission denied.')], Response::HTTP_UNAUTHORIZED);
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $project = Project::whereKey($projectId)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->firstOrFail();
                $task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)->whereKey($taskId)->firstOrFail();
                Log::info("[{$class}::{$action}] loaded", [ActivitiesConstants::COL_TSK_ID => $task->id]);
                return response()->json(['task_name' => $task[ProjectsConstants::COL_NM], 'task_due_date' => $task->due_date]);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const CLD_VW = 'calendarView';
    public function calendarView(Request $request, string $taskBy, mixed $projectId = null): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = 'tasks.calendar';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $taskBy, $projectId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", ['taskBy' => $taskBy, ProjectsConstants::COL_PJ_ID => $projectId]);
            try {
                $creatorId = $user?->creatorId();
                $type = $user?->type;
                $usrId = $user?->id;
                $userProjects = $type === PermissionsConstants::CL ? Project::where('client_id', $usrId)->pluck('id')->toArray() : $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID)->toArray();
                if ($projectId) $userProjects = [$projectId];
                $tasksQuery = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $userProjects);
                if ($type !== PermissionsConstants::CPN && $type !== PermissionsConstants::CL) $tasksQuery->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$usrId]);
                if ($type === PermissionsConstants::CL && $taskBy === 'all') $tasksQuery->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
                elseif ($type !== PermissionsConstants::CL && $taskBy === 'my') $tasksQuery->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$usrId]);
                $tasks = $tasksQuery->get();
                $transdate = date('Y-m-d');
                $arrTasks = Utility::getTaskCalendarArray($tasks);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                return view($viewPath, compact('arrTasks', ProjectsConstants::COL_PJ_ID, 'taskBy', 'transdate'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'task_by' => $taskBy, 'project_id' => $projectId]);
    }

    public const CLD_SHW = 'calendarShow';
    public function calendarShow(Request $request, string|int $projectId, string|int $taskId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = 'tasks.calendar_show';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PJ_ID => $projectId, ActivitiesConstants::COL_TSK_ID => $taskId]);
            try {
                $task = ProjectTask::findOrFail($taskId);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                return view($viewPath, compact('task'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId]);
    }

    public const CLD_DRG = 'calendarDrag';
    public function calendarDrag(Request $request, string|int $projectId, string|int $taskId): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $taskId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", [ActivitiesConstants::COL_TSK_ID => $taskId, 'start' => $req->start, 'end' => $req->end]);
            try {
                $task = ProjectTask::findOrFail($taskId);
                $task[ProjectsConstants::COL_S_DT] = $req->input('start');
                $task[ProjectsConstants::COL_E_DT] = $req->input('end');
                $saveStart = microtime(true);
                $task->save();
                $this->logExecutionTime($saveStart, $action, 'saveCalendarDrag');
                return response()->json(true);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'project_id' => $projectId, 'task_id' => $taskId, 'start' => $request->start, 'end' => $request->end]);
    }

    public const GET_TSK_D = 'getTaskData';
    public function getTaskData(Request $request, mixed $projectId = null): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $projectId, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'view project task', self::REDIRECT_INDEX)) !== true) return $resp;
            Log::info("[{$class}::{$action}] start", ['calendarType' => $req->get('calendar_type'), ProjectsConstants::COL_PJ_ID => $projectId]);
            try {
                if ($req->get('calendar_type') === 'google_calendar') $arrayJson = Utility::getCalendarData('task');
                else {
                    $creatorId = $user?->creatorId();
                    $q = ProjectTask::query();
                    if ($projectId) $q->where(ProjectsConstants::COL_PJ_ID, $projectId);
                    if ($user->{UsersConstants::COL_TP} == PermissionsConstants::CL) {
                        $proj = Project::where('client_id', $user?->id)->pluck('id');
                        $q->whereIn(ProjectsConstants::COL_PJ_ID, $proj);
                    } elseif ($user->{UsersConstants::COL_TP} != PermissionsConstants::CPN) {
                        $proj = $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID);
                        $q->whereIn(ProjectsConstants::COL_PJ_ID, $proj)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$user?->id]);
                    } else $q->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
                    $data = $q->get();
                    $arrayJson = $data->map(function ($val) {
                        $end = date_create($val[ProjectsConstants::COL_E_DT]);
                        date_add($end, date_interval_create_from_date_string('1 days'));
                        return ['id' => $val->id, 'title' => $val->name, 'start' => $val[ProjectsConstants::COL_S_DT], 'end' => date_format($end, 'Y-m-d H:i:s'), 'className' => 'event-primary', 'textColor' => '#51459d', 'allDay' => true, 'url' => route(ViewsConstants::PRJ_TSK_C . '.calendar.show', $val->id), 'resize_url' => route(ViewsConstants::PRJ_TSK_C . '.calendar.drag', $val->id)];
                    })->toArray();
                }
                Log::info("[{$class}::{$action}] complete", ['count' => count($arrayJson)]);
                return response()->json($arrayJson);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'calendar_type' => $request->get('calendar_type'), 'project_id' => $projectId]);
    }

    public const UPD_TSK_PR_CL = 'updateTaskPriorityColor';
    public function updateTaskPriorityColor(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($resp = self::guard($req, 'edit project task', self::REDIRECT_INDEX)) !== true) return $resp;
            $taskId = $req->input(ActivitiesConstants::COL_TSK_ID);
            $color = $req->input(ProjectsConstants::COL_CL);
            Log::info("[{$class}::{$action}] start", [ActivitiesConstants::COL_TSK_ID => $taskId, ProjectsConstants::COL_CL => $color]);
            try {
                $findStart = microtime(true);
                $task = ProjectTask::findOrFail($taskId);
                $this->logExecutionTime($findStart, $action, 'findTask');
                $task->priority_color = $color;
                $saveStart = microtime(true);
                $task->save();
                $this->logExecutionTime($saveStart, $action, 'saveTaskPriorityColor');
                Log::info("[{$class}::{$action}] success", [ActivitiesConstants::COL_TSK_ID => $taskId, ProjectsConstants::COL_CL => $color]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", ['task_id' => $taskId, 'color' => $color, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'task_id' => $request->input(ActivitiesConstants::COL_TSK_ID), 'color' => $request->input(ProjectsConstants::COL_CL)]);
    }
}
