<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    SupportsConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Models\{
    ProjectStage,
    Task,
    TaskComment,
    TaskFile,
    TaskStage,
    TimeTracker,
    User,
    Project,
    Utility,
    Bug,
    BugStatus,
    BugFile,
    BugComment,
    Milestone,
    ActivityLog,
    ProjectTask,
    ProjectUser
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request, RedirectResponse};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{
    App,
    Auth,
    DB,
    File,
    Hash,
    Log,
    Redirect,
    Crypt,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class ProjectController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;


    use ChecksLogin, ChecksPermissions;

    private const ENTITY = 'project';
    private const CACHE_TTL = 120;

    public function index(Request $request, string $view = 'grid'): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $view, $action) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($guard = self::guard($request, PermissionsConstants::MNG_PRJ, VW::PRJ . '.' . $action)) instanceof RedirectResponse) return $guard;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $viewPath = VW::PRJ . '.' . $action;
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact('view'));
        }, ['route' => VW::PRJ . '.' . $action, 'view' => $view]);
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($guard = self::guard($request, 'create project', VW::PRJ . '.index')) instanceof RedirectResponse) return $guard;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $creatorId = $request->user()->creatorId();

            $t = microtime(true);
            $users = User::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
                ->pluck(UsersConstants::COL_NM, 'id');
            $this->logExecutionTime($t, $action . '::fetchUsers', 'completed');

            $t = microtime(true);
            $clients = User::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                ->where(UsersConstants::COL_TP, '=', PermissionsConstants::CL)
                ->pluck(UsersConstants::COL_NM, 'id');
            $this->logExecutionTime($t, $action . '::fetchClients', 'completed');

            $clients->prepend('Select Client', '');
            $users->prepend('Select User', '');

            $viewPath = VW::PRJ . '.' . $action;
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact(DatabaseConstants::TABLE_CLIENTS, DatabaseConstants::TABLE_USERS));
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'create project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    ProjectsConstants::COL_NM       => 'required|string',
                    ProjectsConstants::COL_S_DT     => 'required|date',
                    ProjectsConstants::COL_E_DT     => 'required|date',
                    'project_image'                 => 'required|file',
                    PermissionsConstants::CL        => 'required',
                    'budget'                        => 'nullable|numeric',
                    ActivitiesConstants::COL_DESC   => 'nullable|string',
                    ActivitiesConstants::COL_TSK_STT => 'required|string',
                    ProjectsConstants::COL_E_HRS    => 'nullable',
                    'tag'                           => 'nullable|string'
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $pd = Arr::only($data, [
                    ProjectsConstants::COL_NM,
                    PermissionsConstants::CL,
                    'budget',
                    ActivitiesConstants::COL_DESC,
                    ActivitiesConstants::COL_TSK_STT,
                    ProjectsConstants::COL_E_HRS,
                    'tag'
                ]);
                $pd[ProjectsConstants::COL_S_DT] = Carbon::parse($data[ProjectsConstants::COL_S_DT])->toDateTimeString();
                $pd[ProjectsConstants::COL_E_DT] = Carbon::parse($data[ProjectsConstants::COL_E_DT])->toDateTimeString();

                $res = null;
                if ($request->hasFile('project_image')) {
                    $t = microtime(true);
                    $file = $request->file('project_image');
                    $size = $file->getSize();
                    $res  = Utility::updateStorageLimit($request->user()->creatorId(), $size);
                    if ($res === 1) {
                        $fn = time() . '.' . $file->extension();
                        $file->storeAs(DatabaseConstants::TABLE_PROJECTS, $fn);
                        $pd['project_image'] = 'projects/' . $fn;
                    }
                    $this->logExecutionTime($t, $action . '::storeImage', 'completed');
                }

                $pd[DatabaseConstants::COL_TABLE_CREATOR] = $request->user()->creatorId();
                $pd['copylinksetting'] = json_encode([
                    'member' => 'on',
                    'milestone' => 'off',
                    'basic_details' => 'on',
                    'activity' => 'off',
                    'attachment' => 'on',
                    'bug_report' => 'on',
                    'task' => 'off',
                    'tracker_details' => 'off',
                    'timesheet' => 'off',
                    'password_protected' => 'off'
                ]);

                $t = microtime(true);
                $project = Project::create($pd);
                $this->logExecutionTime($t, $action . '::createProject', 'completed');

                $t = microtime(true);
                $creator = $request->user();
                $ids = $creator->type === PermissionsConstants::CPN ? [$creator->id] : [$creator->creatorId(), $creator->id];
                foreach (array_unique(array_merge($ids, $request->user ?? [])) as $uid) {
                    ProjectUser::create([
                        ActivitiesConstants::COL_PJ  => $project->id,
                        UsersConstants::COL_USER_ID  => $uid,
                    ]);
                }
                $this->logExecutionTime($t, $action . '::attachUsers', 'completed');

                $t = microtime(true);
                $setting = Utility::settingsById($creator->creatorId());
                $notif  = ['project_name' => $project[ProjectsConstants::COL_NM], 'user_name' => $creator[UsersConstants::COL_NM]];
                foreach (['project_notification' => 'send_slack_msg', 'telegram_project_notification' => 'send_telegram_msg'] as $key => $method) {
                    if (!empty($setting[$key])) Utility::$method('new_project', $notif);
                }
                $this->logExecutionTime($t, $action . '::notify', 'completed');

                $t = microtime(true);
                if ($hook = Utility::webhookSetting('New Project')) {
                    if (!Utility::webhookCall($hook['url'], $project->toJson(), $hook['method'])) {
                        return Redirect::back()->with('error', __('Webhook call failed.'));
                    }
                }
                $this->logExecutionTime($t, $action . '::webhook', 'completed');

                return Redirect::route(VW::PRJ . '.index')
                    ->with('success', __('Project Add Successfully')
                        . (isset($res) && $res !== 1 ? '<br><span class="text-danger">' . $res . '</span>' : ''));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function show(Request $request, Project $project): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $project, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'view project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $usr = $request->user();

                $t = microtime(true);
                $allowed = $usr->type === PermissionsConstants::CL
                    ? Project::where('client_id', $usr->id)->pluck('id')->toArray()
                    : $usr->projects->pluck('id')->toArray();
                $this->logExecutionTime($t, $action . '::computeAllowed', 'completed');

                if (!in_array($project->id, $allowed)) {
                    return Redirect::back()->with('error', __('Permission Denied.'));
                }

                // Eager-load relations to prevent N+1 queries
                $t = microtime(true);
                $project->load(['expense', 'tasks', 'milestones']);
                $this->logExecutionTime($t, $action . '::eagerLoad', 'completed');

                $pd = [];

                // Task count — use eager-loaded tasks instead of 2 separate queries
                $t = microtime(true);
                $allTasks = $project->tasks;
                $tot  = $allTasks->count();
                $done = $allTasks->where(ProjectsConstants::COL_IS_CP, 1)->count();
                $pd['task'] = ['total' => $tot, 'done' => $done, 'percentage' => Utility::getPercentage($done, $tot)];
                $this->logExecutionTime($t, $action . '::taskCounts', 'completed');

                // expense
                $t = microtime(true);
                $exp = $project->expense->sum('amount');
                $pd['expense'] = ['allocated' => $project->budget, 'total' => $exp, 'percentage' => Utility::getPercentage($exp, $project->budget)];
                $this->logExecutionTime($t, $action . '::expense', 'completed');

                // users assigned
                $t = microtime(true);
                $totalUsers = User::where(DatabaseConstants::COL_TABLE_CREATOR, $usr->id)->count();
                $pd['user_assigned'] = ['total' => "$totalUsers/$totalUsers", 'percentage' => Utility::getPercentage($totalUsers, $totalUsers)];
                $this->logExecutionTime($t, $action . '::usersAssigned', 'completed');

                // day left
                $t = microtime(true);
                $totDays = Carbon::parse($project[ProjectsConstants::COL_S_DT])->diffInDays(Carbon::parse($project[ProjectsConstants::COL_E_DT]));
                $remDays = Carbon::parse($project[ProjectsConstants::COL_S_DT])->diffInDays(now());
                $pd['day_left'] = ['day' => "$remDays/$totDays", 'percentage' => Utility::getPercentage($remDays, $totDays)];
                $this->logExecutionTime($t, $action . '::daysLeft', 'completed');

                // open task — use eager-loaded tasks collection
                $t = microtime(true);
                $open = $allTasks->where(ProjectsConstants::COL_IS_CP, 0)
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $usr->creatorId())
                    ->count();
                $pd['open_task'] = [DatabaseConstants::TABLE_TASKS => "$open/{$tot}", 'percentage' => Utility::getPercentage($open, $tot)];
                $this->logExecutionTime($t, $action . '::openTasks', 'completed');

                // milestone — use eager-loaded milestones collection
                $t = microtime(true);
                $loadedMs = $project->milestones;
                $totMs  = $loadedMs->count();
                $doneMs = $loadedMs->where(ActivitiesConstants::COL_TSK_STT, ProjectsConstants::STT_CPT_K)->count();
                $pd['milestone'] = ['total' => "$doneMs/$totMs", 'percentage' => Utility::getPercentage($doneMs, $totMs)];
                $this->logExecutionTime($t, $action . '::milestones', 'completed');

                // time spent
                $t = microtime(true);
                $times = $project->timesheets()->where(DatabaseConstants::COL_TABLE_CREATOR, $usr->id)->pluck('time')->toArray();
                $hrs = str_replace(':', '.', Utility::timeToHr($times));
                $pd['time_spent'] = ['total' => "$hrs/$hrs", 'percentage' => Utility::getPercentage($hrs, $hrs)];
                $this->logExecutionTime($t, $action . '::timeSpent', 'completed');

                // allocated hours
                $t = microtime(true);
                $ah = Project::projectHrs($project->id);
                $pd['task_allocated_hrs'] = ['hrs' => "{$ah['allocated']}/{$ah['allocated']}", 'percentage' => Utility::getPercentage($ah['allocated'], $ah['allocated'])];
                $this->logExecutionTime($t, $action . '::allocatedHrs', 'completed');

                // charts — 2 batch queries instead of 14 per-day queries
                $t = microtime(true);
                $days = Utility::getLastSevenDays();
                $dateKeys = array_keys($days);
                $ct = [];
                $ts = [];
                $cntT = 0;
                $cntTs = 0;

                $completedByDate = $project->tasks()
                    ->where(ProjectsConstants::COL_IS_CP, 1)
                    ->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$usr->id])
                    ->where(function ($q) use ($dateKeys) {
                        foreach ($dateKeys as $d) $q->orWhere(ProjectsConstants::COL_M_AT, 'LIKE', $d . '%');
                    })
                    ->selectRaw("DATE(" . ProjectsConstants::COL_M_AT . ") as chart_date, COUNT(*) as cnt")
                    ->groupBy('chart_date')
                    ->pluck('cnt', 'chart_date');

                $timesheetsByDate = $project->timesheets()
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $usr->id)
                    ->where(function ($q) use ($dateKeys) {
                        foreach ($dateKeys as $d) $q->orWhere('date', 'LIKE', $d . '%');
                    })
                    ->selectRaw("DATE(date) as chart_date, GROUP_CONCAT(time) as times")
                    ->groupBy('chart_date')
                    ->pluck('times', 'chart_date');

                foreach ($dateKeys as $date) {
                    $c = $completedByDate[$date] ?? 0;
                    $tHrs = str_replace(':', '.', Utility::timeToHr(
                        isset($timesheetsByDate[$date]) ? explode(',', $timesheetsByDate[$date]) : []
                    ));
                    $ct[] = $c;
                    $ts[] = $tHrs;
                    $cntT += $c;
                    $cntTs += $tHrs;
                }
                $pd['task_chart'] = ['chart' => $ct, 'total' => $cntT];
                $pd['timesheet_chart'] = ['chart' => $ts, 'total' => $cntTs];
                $this->logExecutionTime($t, $action . '::charts', 'completed');

                $t = microtime(true);
                $lastTask = \App\Models\TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $usr->creatorId())
                    ->orderBy(ActivitiesConstants::COL_OD, 'DESC')->first();
                $this->logExecutionTime($t, $action . '::lastTaskStage', 'completed');

                $viewPath = VW::PRJ . '.view';
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return view($viewPath, [
                    self::ENTITY   => $project,
                    'project_data' => $pd,
                    'last_task'    => $lastTask
                ]);
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function edit(Request $request, Project $project): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $project, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'edit project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $creatorId = $request->user()->creatorId();
                if ($project->created_by !== $creatorId) {
                    return Redirect::back()->with('error', __('Permission Denied.'));
                }

                $t = microtime(true);
                $clients = User::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->where(UsersConstants::COL_TP, PermissionsConstants::CL)
                    ->pluck(UsersConstants::COL_NM, 'id');
                $this->logExecutionTime($t, $action . '::fetchClients', 'completed');

                $viewPath = VW::PRJ . '.' . $action;
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return view($viewPath, compact(self::ENTITY, DatabaseConstants::TABLE_CLIENTS));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function update(Request $request, Project $project): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $project, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'edit project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    ProjectsConstants::COL_NM       => 'required|string',
                    ProjectsConstants::COL_S_DT     => 'required|date',
                    ProjectsConstants::COL_E_DT     => 'required|date',
                    'project_image'                 => 'nullable|file',
                    PermissionsConstants::CL        => 'required',
                    'budget'                        => 'nullable|numeric',
                    ActivitiesConstants::COL_DESC   => 'nullable|string',
                    ActivitiesConstants::COL_TSK_STT => 'required|string',
                    ProjectsConstants::COL_E_HRS    => 'nullable',
                    'tag'                           => 'nullable|string'
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $project[ProjectsConstants::COL_NM]   = $data[ProjectsConstants::COL_NM];
                $project[ProjectsConstants::COL_S_DT] = Carbon::parse($data[ProjectsConstants::COL_S_DT])->toDateTimeString();
                $project[ProjectsConstants::COL_E_DT] = Carbon::parse($data[ProjectsConstants::COL_E_DT])->toDateTimeString();

                $res = null;
                if ($request->hasFile('project_image')) {
                    $t = microtime(true);
                    $old  = $project->project_image;
                    $size = $request->file('project_image')->getSize();
                    $res  = Utility::updateStorageLimit($request->user()->creatorId(), $size);
                    if ($res === 1) {
                        Utility::changeStorageLimit($request->user()->creatorId(), $old);
                        $fn = time() . '.' . $request->project_image->extension();
                        $request->file('project_image')->storeAs(DatabaseConstants::TABLE_PROJECTS, $fn);
                        $project->project_image = 'projects/' . $fn;
                    }
                    $this->logExecutionTime($t, $action . '::storeImage', 'completed');
                }

                $project->client_id     = $data[PermissionsConstants::CL];
                $project->budget        = $data['budget'] ?? 0;
                $project->description   = $data[ActivitiesConstants::COL_DESC];
                $project->status        = $data[ActivitiesConstants::COL_TSK_STT];
                $project->estimated_hrs = $data[ProjectsConstants::COL_E_HRS];
                $project->tags          = $data['tag'];
                $project->save();

                return Redirect::route(VW::PRJ . '.index')
                    ->with('success', __('Project Updated Successfully')
                        . (isset($res) && $res !== 1 ? '<br><span class="text-danger">' . $res . '</span>' : ''));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function destroy(Request $request, Project $project): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $project, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'delete project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                if ($project->project_image) {
                    Utility::changeStorageLimit($request->user()->creatorId(), $project->project_image);
                }
                $project->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return Redirect::back()->with('success', __('Project Successfully Deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const INV_MB_VW = 'inviteMemberView';
    public function inviteMemberView(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, PermissionsConstants::MNG_PRJ, VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                $project  = Project::findOrFail($projectId);
                $existing = $project->users->pluck('id')->toArray();
                $users    = User::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
                    ->whereNotIn('id', $existing)
                    ->get();
                $this->logExecutionTime($t, $action . '::fetchProjectAndUsers', 'completed');

                $viewPath = VW::PRJ . '.invite';
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return view($viewPath, compact('projectId', DatabaseConstants::TABLE_USERS));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId]);
    }

    public const INV_PRJ_USR_MB = 'inviteProjectUserMember';
    public function inviteProjectUserMember(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json(['error' => 'Permission denied.'], 401);
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'edit project', VW::PRJ . '.index')) instanceof RedirectResponse)
                return response()->json(['error' => 'Permission denied.'], 401);
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                $user = $request->user();
                ProjectUser::create([
                    ActivitiesConstants::COL_PJ => $request->project_id,
                    UsersConstants::COL_USER_ID => $request->user_id,
                    'invited_by' => $user?->id,
                ]);
                ActivityLog::create([
                    UsersConstants::COL_USER_ID    => $user?->id,
                    ActivitiesConstants::COL_PJ    => $request->project_id,
                    'log_type'   => 'Invite User',
                    'remark'     => json_encode([ActivitiesConstants::COL_TT => $user[ProjectsConstants::COL_NM]]),
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return response()->json([
                    'code'    => 200,
                    ActivitiesConstants::COL_TSK_STT  => 'success',
                    'success' => __('User invited successfully.'),
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'code'   => 500,
                    ActivitiesConstants::COL_TSK_STT => 'error',
                    'error'  => $e->getMessage(),
                ], 500);
            }
        });
    }

    public const DST_PRJ_USR = 'destroyProjectUser';
    public function destroyProjectUser(Request $request, int|string $projectId, int $userId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $userId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'delete project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                $project = Project::findOrFail($projectId);
                if ($project->created_by !== $request->user()->ownerId()) {
                    return defaultPermissionDenial($request, new \Exception, $method, VW::PRJ . '.index');
                }
                ProjectUser::where(ActivitiesConstants::COL_PJ, $projectId)
                    ->where(UsersConstants::COL_USER_ID, $userId)
                    ->delete();
                $this->logExecutionTime($t, $action . '::deleteProjectUser', 'completed');

                return Redirect::back()->with('success', __('User successfully deleted!'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId, 'userId' => $userId]);
    }

    public const LD_USR = 'loadUser';
    public function loadUser(Request $request): JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return null;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, PermissionsConstants::MNG_PRJ, VW::PRJ . '.index')) instanceof RedirectResponse)
                return response()->json(['error' => 'Permission denied.'], 401);
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            if (!$request->ajax()) return null;

            try {
                $t = microtime(true);
                $project = Project::findOrFail($request->project_id);
                $this->logExecutionTime($t, $action . '::findProject', 'completed');

                $viewPath = VW::PRJ . '.' . DatabaseConstants::TABLE_USERS;
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    return response()->json(['success' => false, 'error' => "HTTP 404: Page {$viewPath} not found!"], 404);
                }

                $t = microtime(true);
                $html = view($viewPath, compact(self::ENTITY))->render();
                $this->logExecutionTime($t, $action . '::renderPartial', 'completed');

                return response()->json(['success' => true, 'html' => $html]);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }, ['project_id' => $request->project_id]);
    }

    public const MLST = 'milestone';
    public function milestone(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'create milestone', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $project = Project::findOrFail($projectId);
            $this->logExecutionTime($t, $action . '::findProject', 'completed');

            $viewPath = VW::ML;
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact(self::ENTITY));
        }, ['projectId' => $projectId]);
    }

    public const ML_STR = 'milestoneStore';
    public function milestoneStore(Request $request, int|string $projectId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'create milestone', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $validator = Validator::make($request->all(), [
                ActivitiesConstants::COL_TT       => 'required',
                ActivitiesConstants::COL_TSK_STT  => 'required',
                'cost'                             => 'required|numeric',
                ProjectsConstants::COL_S_DT       => 'required|date',
                'due_date'                         => 'required|date',
            ]);
            if ($validator->fails()) {
                $this->logExecutionTime($t, $action . '::validate', 'failed');
                return Redirect::back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }
            $this->logExecutionTime($t, $action . '::validate', 'completed');

            try {
                $t = microtime(true);
                $m = Milestone::create([
                    ActivitiesConstants::COL_PJ   => $projectId,
                    ActivitiesConstants::COL_TT   => $request->title,
                    ActivitiesConstants::COL_TSK_STT => $request->status,
                    'cost'                         => $request->cost,
                    ProjectsConstants::COL_S_DT   => Carbon::parse($request[ProjectsConstants::COL_S_DT])->toDateString(),
                    'due_date'                     => Carbon::parse($request->due_date)->toDateString(),
                    ActivitiesConstants::COL_DESC  => $request->description,
                ]);
                ActivityLog::create([
                    UsersConstants::COL_USER_ID    => $request->user()->id,
                    ActivitiesConstants::COL_PJ    => $projectId,
                    'log_type'   => 'Create Milestone',
                    'remark'     => json_encode([ActivitiesConstants::COL_TT => $m->title]),
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return Redirect::back()->with('success', __('Milestone successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId]);
    }

    public const ML_ED = 'milestoneEdit';
    public function milestoneEdit(Request $request, int|string $milestoneId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $milestoneId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'edit milestone', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $milestone = Milestone::findOrFail($milestoneId);
            $this->logExecutionTime($t, $action . '::findMilestone', 'completed');

            $viewPath = VW::ML . '.edit';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact('milestone'));
        }, ['milestoneId' => $milestoneId]);
    }

    public const ML_UPD = 'milestoneUpdate';
    public function milestoneUpdate(Request $request, int|string $milestoneId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $milestoneId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'edit milestone', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $validator = Validator::make($request->all(), [
                ActivitiesConstants::COL_TT       => 'required',
                ActivitiesConstants::COL_TSK_STT  => 'required',
                'cost'                             => 'required|numeric',
                ProjectsConstants::COL_S_DT       => 'required|date',
                'due_date'                         => 'required|date',
            ]);
            if ($validator->fails()) {
                $this->logExecutionTime($t, $action . '::validate', 'failed');
                return Redirect::back()->with('error', Utility::errorFormat($validator->getMessageBag()));
            }
            $this->logExecutionTime($t, $action . '::validate', 'completed');

            try {
                $t = microtime(true);
                $m = Milestone::findOrFail($milestoneId);
                $m->fill([
                    ActivitiesConstants::COL_TT       => $request->title,
                    ActivitiesConstants::COL_TSK_STT  => $request->status,
                    'cost'                             => $request->cost,
                    ProjectsConstants::COL_PGR        => $request->progress,
                    ProjectsConstants::COL_S_DT       => Carbon::parse($request[ProjectsConstants::COL_S_DT])->toDateString(),
                    'due_date'                         => Carbon::parse($request->due_date)->toDateString(),
                    ActivitiesConstants::COL_DESC     => $request->description,
                ])->save();
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return Redirect::back()->with('success', __('Milestone updated successfully.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['milestoneId' => $milestoneId]);
    }

    public const ML_DST = 'milestoneDestroy';
    public function milestoneDestroy(Request $request, int|string $milestoneId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $milestoneId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'delete milestone', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                Milestone::findOrFail($milestoneId)->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return Redirect::back()->with('success', __('Milestone successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['milestoneId' => $milestoneId]);
    }

    public const ML_SHW = 'milestoneShow';
    public function milestoneShow(Request $request, int|string $milestoneId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $milestoneId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'view milestone', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $milestone = Milestone::findOrFail($milestoneId);
            $this->logExecutionTime($t, $action . '::findMilestone', 'completed');

            $viewPath = VW::ML . '.show';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact('milestone'));
        }, ['milestoneId' => $milestoneId]);
    }

    //todo

    public const FT_PRJ = 'filterProjectView';
    public function filterProjectView(Request $request): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, PermissionsConstants::MNG_PRJ, VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            if (!($request->ajax() && $request->has('view') && $request->has('sort'))) return null;

            try {
                $t = microtime(true);
                $usr = $request->user();
                $userProjects = $usr->type === PermissionsConstants::CL
                    ? Project::where('client_id', $usr->id)
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $usr->creatorId())
                    ->pluck('id')
                    ->toArray()
                    : $usr->projects()->pluck(ActivitiesConstants::COL_PJ)->toArray();
                [$col, $dir] = explode('-', $request->sort);
                $query = Project::whereIn('id', $userProjects)->orderBy($col, $dir);
                if ($kw = $request->keyword) {
                    $query->where(function ($q) use ($kw) {
                        $q->where(ProjectsConstants::COL_NM, 'LIKE', "$kw%")
                            ->orWhereRaw("FIND_IN_SET(?, tags)", [$kw]);
                    });
                }
                if ($status = $request->status) $query->whereIn(ActivitiesConstants::COL_TSK_STT, $status);
                $projects = $query->with(['tasks', 'milestones', 'expense'])->get();
                $lastTask = TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $usr->creatorId())
                    ->orderBy(ActivitiesConstants::COL_OD, 'DESC')->first();
                $this->logExecutionTime($t, $action . '::buildAndRunQuery', 'completed');

                $viewPath = VW::PRJ . '.' . $request->view;
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    return response()->json(['success' => false, 'error' => "HTTP 404: Page {$viewPath} not found!"], 404);
                }

                $t = microtime(true);
                $html = view($viewPath, compact(DatabaseConstants::TABLE_PROJECTS, 'userProjects', 'lastTask'))->render();
                $this->logExecutionTime($t, $action . '::renderPartial', 'completed');

                return response()->json(['success' => true, 'html' => $html]);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }, ['view' => $request->view, 'sort' => $request->sort]);
    }

    public const GT = 'gantt';
    public function gantt(Request $request, int|string $projectId, string $duration = 'Week'): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $duration, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'view grant chart', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                $project = Project::findOrFail($projectId);
                $tasks = $project->tasks->map(fn($t) => [
                    'id'             => 'task_' . $t->id,
                    ProjectsConstants::COL_NM   => $t[ProjectsConstants::COL_NM],
                    ProjectsConstants::COL_S_DT => $t[ProjectsConstants::COL_S_DT],
                    ProjectsConstants::COL_E_DT => $t[ProjectsConstants::COL_E_DT],
                    'custom_class'   => $t->priority_color ?: '#ecf0f1',
                    ProjectsConstants::COL_PGR  => (int)str_replace('%', '', $t->taskProgress($t)['percentage']),
                    'extra'          => [
                        ProjectsConstants::COL_PRT => ucfirst(__($t->priority)),
                        'comments' => $t->comments()->count(),
                        'duration' => Utility::getDateFormated($t[ProjectsConstants::COL_S_DT]) . ' - ' . Utility::getDateFormated($t[ProjectsConstants::COL_E_DT]),
                    ],
                ])->toArray();
                $this->logExecutionTime($t, $action . '::prepareData', 'completed');

                $viewPath = VW::PRJ . '.gantt';
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return view($viewPath, compact(self::ENTITY, DatabaseConstants::TABLE_TASKS, 'duration'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId, 'duration' => $duration]);
    }

    public const GT_PT = 'ganttPost';
    public function ganttPost(Request $request, int|string $projectId): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            $can = $user?->can('view project task');
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');
            if (!$can) return response()->json(['is_success' => false, 'message' => __("You can't change Date!")], 400);

            try {
                $t = microtime(true);
                $id = (int) str_replace('task_', '', $request->task_id);
                $task = ProjectTask::findOrFail($id);
                $task[ProjectsConstants::COL_S_DT] = $request->start;
                $task[ProjectsConstants::COL_E_DT] = $request->end;
                $task->save();
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return response()->json(['is_success' => true, 'message' => __("Time Updated")], 200);
            } catch (\Throwable $e) {
                return response()->json(['is_success' => false, 'message' => __("Something is wrong.")], 400);
            }
        }, ['projectId' => $projectId]);
    }

    public const BG = 'bug';
    public function bug(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can(PermissionsConstants::MNG_BUG_RPT))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            try {
                $t = microtime(true);
                $project = Project::findOrFail($projectId);
                if ($project->created_by !== $request->user()->creatorId())
                    return defaultPermissionDenial($request, new \Exception, $method);
                $user = $request->user();
                $matchedBug = Bug::where(ActivitiesConstants::COL_PJ, $projectId);
                $bugs = match ($user?->type) {
                    PermissionsConstants::CPN => $matchedBug->get(),
                    PermissionsConstants::CL  => $matchedBug->get(),
                    default => $matchedBug->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$user?->id])->get(),
                };
                $this->logExecutionTime($t, $action . '::fetchData', 'completed');

                $viewPath = VW::PRJ . '.bug';
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return view($viewPath, compact(self::ENTITY, DatabaseConstants::TABLE_BUGS));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId]);
    }

    public const BUG_CRT = 'bugCreate';
    public function bugCreate(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('create bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $status = BugStatus::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->pluck(ActivitiesConstants::COL_TT, 'id');
            $ids    = ProjectUser::where(ActivitiesConstants::COL_PJ, $projectId)->pluck(UsersConstants::COL_USER_ID)->toArray();
            $users  = User::whereIn('id', $ids)->pluck(UsersConstants::COL_NM, 'id');
            $priority = Bug::$priority;
            $this->logExecutionTime($t, $action . '::fetchFormData', 'completed');

            $viewPath = VW::PRJ . '.bugCreate';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact(ActivitiesConstants::COL_TSK_STT, 'projectId', ProjectsConstants::COL_PRT, DatabaseConstants::TABLE_USERS));
        }, ['projectId' => $projectId]);
    }

    public const BUG_ST = 'bugStore';
    public function bugStore(Request $request, int|string $projectId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('create bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $data = Validator::make($request->all(), [
                ActivitiesConstants::COL_TT      => 'required',
                ProjectsConstants::COL_PRT       => 'required',
                ActivitiesConstants::COL_TSK_STT => 'required',
                ProjectsConstants::COL_ASGN      => 'required',
                ProjectsConstants::COL_S_DT      => 'required|date',
                'due_date'                        => 'required|date'
            ])->validate();
            $this->logExecutionTime($t, $action . '::validate', 'completed');

            try {
                $t = microtime(true);
                $bug = Bug::create([
                    'bug_id'                         => $this->bugNumber(),
                    ActivitiesConstants::COL_PJ      => $projectId,
                    ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                    ProjectsConstants::COL_PRT       => $data[ProjectsConstants::COL_PRT],
                    ActivitiesConstants::COL_TSK_STT => $data[ActivitiesConstants::COL_TSK_STT],
                    ProjectsConstants::COL_ASGN      => $data[ProjectsConstants::COL_ASGN],
                    ProjectsConstants::COL_S_DT      => Carbon::parse($data[ProjectsConstants::COL_S_DT])->toDateString(),
                    'due_date'                        => Carbon::parse($data['due_date'])->toDateString(),
                    ActivitiesConstants::COL_DESC     => $data[ActivitiesConstants::COL_DESC] ?? null,
                    DatabaseConstants::COL_TABLE_CREATOR  => $request->user()->creatorId(),
                ]);
                ActivityLog::create([
                    UsersConstants::COL_USER_ID => Auth::id(),
                    ActivitiesConstants::COL_PJ => $projectId,
                    'log_type'   => 'Create Bug',
                    'remark'     => json_encode([ActivitiesConstants::COL_TT => $bug->title]),
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return Redirect::route(VW::PRJ_TSK_BUG . '.', $projectId)
                    ->with('success', __('Bug successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId]);
    }

    public const BUG_EDT = 'bugEdit';
    public function bugEdit(Request $request, int|string $projectId, int|string $bugId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $bugId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('edit bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $bug = Bug::findOrFail($bugId);
            $status = BugStatus::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->pluck(ActivitiesConstants::COL_TT, 'id');
            $ids   = ProjectUser::where(ActivitiesConstants::COL_PJ, $projectId)->pluck(UsersConstants::COL_USER_ID)->toArray();
            $users = User::whereIn('id', $ids)->pluck(UsersConstants::COL_NM, 'id');
            $priority = Bug::$priority;
            $this->logExecutionTime($t, $action . '::fetchFormData', 'completed');

            $viewPath = VW::PRJ . '.bugEdit';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact(ActivitiesConstants::COL_TSK_STT, 'projectId', ProjectsConstants::COL_PRT, DatabaseConstants::TABLE_USERS, 'bug'));
        }, ['projectId' => $projectId, 'bugId' => $bugId]);
    }

    public const BUG_UPD = 'bugUpdate';
    public function bugUpdate(Request $request, int|string $projectId, int|string $bugId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $bugId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('edit bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $data = Validator::make($request->all(), [
                ActivitiesConstants::COL_TT      => 'required',
                ProjectsConstants::COL_PRT       => 'required',
                ActivitiesConstants::COL_TSK_STT => 'required',
                ProjectsConstants::COL_ASGN      => 'required',
                ProjectsConstants::COL_S_DT      => 'required|date',
                'due_date'                        => 'required|date'
            ])->validate();
            $this->logExecutionTime($t, $action . '::validate', 'completed');

            try {
                $t = microtime(true);
                $bug = Bug::findOrFail($bugId);
                $bug->fill([
                    ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                    ProjectsConstants::COL_PRT       => $data[ProjectsConstants::COL_PRT],
                    ActivitiesConstants::COL_TSK_STT => $data[ActivitiesConstants::COL_TSK_STT],
                    ProjectsConstants::COL_ASGN      => $data[ProjectsConstants::COL_ASGN],
                    ProjectsConstants::COL_S_DT      => Carbon::parse($data[ProjectsConstants::COL_S_DT])->toDateString(),
                    'due_date'                        => Carbon::parse($data['due_date'])->toDateString(),
                    ActivitiesConstants::COL_DESC     => $data[ActivitiesConstants::COL_DESC] ?? null,
                ])->save();
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return Redirect::route(VW::PRJ_TSK_BUG . '.', $projectId)
                    ->with('success', __('Bug successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId, 'bugId' => $bugId]);
    }

    public const BUG_DST = 'bugDestroy';
    public function bugDestroy(Request $request, int|string $projectId, int|string $bugId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $bugId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('delete bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            try {
                $t = microtime(true);
                Bug::findOrFail($bugId)->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return Redirect::route(VW::PRJ_TSK_BUG . '.', $projectId)
                    ->with('success', __('Bug successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId, 'bugId' => $bugId]);
    }

    public const BUG_KB = 'bugKanban';
    public function bugKanban(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('move bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            try {
                $t = microtime(true);
                $project = Project::findOrFail($projectId);
                if ($project->created_by !== $request->user()->creatorId())
                    return defaultPermissionDenial($request, new \Exception, $method);
                $bug_status = BugStatus::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->orderBy(ActivitiesConstants::COL_OD, 'ASC')->get();
                $this->logExecutionTime($t, $action . '::fetchKanbanData', 'completed');

                $viewPath = VW::PRJ . '.bugKanban';
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return view($viewPath, compact(self::ENTITY, 'bug_status'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['projectId' => $projectId]);
    }

    public const BUG_KB_OD = 'bugKanbanOrder';
    public function bugKanbanOrder(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('move bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            try {
                $t = microtime(true);
                $post = $request->all();
                if (!empty($post['status_id'])) {
                    Bug::findOrFail($post['bug_id'])->update([ActivitiesConstants::COL_TSK_STT => $post['status_id']]);
                }
                foreach ($post[ActivitiesConstants::COL_OD] as $key => $item) {
                    if ($item !== 'null') {
                        Bug::findOrFail($item)->update([
                            ActivitiesConstants::COL_OD      => $key,
                            ActivitiesConstants::COL_TSK_STT => $post['status_id']
                        ]);
                    }
                }
                $this->logExecutionTime($t, $action . '::persistOrder', 'completed');

                return Redirect::back()->with('success', __('Order updated successfully.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const BUG_SHW = 'bugShow';
    public function bugShow(Request $request, int|string $projectId, int|string $bugId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $projectId, $bugId, $action, $method) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (!$request->user()->can('view bug report'))
                return defaultPermissionDenial($request, new \Exception, $method);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $bug = Bug::findOrFail($bugId);
            $this->logExecutionTime($t, $action . '::findBug', 'completed');

            $viewPath = VW::PRJ . '.bugShow';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact('bug'));
        }, ['projectId' => $projectId, 'bugId' => $bugId]);
    }

    public const BUG_CMT_STR = 'bugCommentStore';
    public function bugCommentStore(Request $request, int $projectId, int $bugId): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $projectId, $bugId, $action) {
            $t = microtime(true);
            if (!$request->user()->can('create bug report'))
                return response()->json(['is_success' => false, 'message' => __('Permission denied.')], 403);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $bug = Bug::findOrFail($bugId);
            if ($bug->project_id !== $projectId)
                return response()->json(['is_success' => false, 'message' => __('Invalid project or bug.')], 400);
            $data = $request->validate(['comment' => 'required|string']);
            $this->logExecutionTime($t, $action . '::validateAndCheck', 'completed');

            $t = microtime(true);
            $comment = BugComment::create([
                'bug_id'     => $bugId,
                'comment'    => $data['comment'],
                DatabaseConstants::COL_TABLE_CREATOR => Auth::id(),
                'user_type'  => Auth::user()->type,
            ]);
            $comment->deleteUrl = route(VW::PRJ_BUG_CM . '.destroy', $comment->id);
            $this->logExecutionTime($t, $action . '::persist', 'completed');

            return response()->json([
                'is_success' => true,
                'message'    => __("Bug comment successfully created."),
                'data'       => $comment
            ], 200);
        }, ['projectId' => $projectId, 'bugId' => $bugId]);
    }

    public const BUG_CMT_DST = 'bugCommentDestroy';
    public function bugCommentDestroy(Request $request, int|string $commentId): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $commentId, $action) {
            $t = microtime(true);
            if (!$request->user()->can('delete bug report'))
                return response()->json(['is_success' => false, 'message' => __('Permission denied.')], 403);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            BugComment::findOrFail($commentId)->delete();
            $this->logExecutionTime($t, $action . '::delete', 'completed');

            return response()->json(['is_success' => true], 200);
        }, ['commentId' => $commentId]);
    }

    public const BUG_CMT_STR_F = 'bugCommentStoreFile';
    public function bugCommentStoreFile(Request $request, int|string $bugId): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $bugId, $action) {
            $t = microtime(true);
            if (!$request->user()->can('create bug report'))
                return response()->json(['is_success' => false, 'message' => __('Permission denied.')], 403);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $request->validate(['file' => 'required|file']);
            $file = $request->file('file');
            $name = $bugId . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $file->storeAs(DatabaseConstants::TABLE_BUGS, $name);
            $bf = BugFile::create([
                'bug_id'     => $bugId,
                'file'       => $name,
                ProjectsConstants::COL_NM       => $file->getClientOriginalName(),
                'extension'  => '.' . $file->getClientOriginalExtension(),
                'file_size'  => round($file->getSize() / 1024 / 1024, 2) . ' MB',
                DatabaseConstants::COL_TABLE_CREATOR => Auth::id(),
                'user_type'  => Auth::user()->type,
            ]);
            $bf->deleteUrl = route(VW::PRJ_BUG_CM . '.file.destroy', $bf->id);
            $this->logExecutionTime($t, $action . '::persist', 'completed');

            return response()->json($bf, 200);
        }, ['bugId' => $bugId]);
    }

    public const BUG_CMT_DST_F = 'bugCommentDestroyFile';
    public function bugCommentDestroyFile(Request $request, int|string $fileId): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $fileId, $action) {
            $t = microtime(true);
            if (!$request->user()->can('delete bug report'))
                return response()->json(['is_success' => false, 'message' => __('Permission denied.')], 403);
            $this->logExecutionTime($t, $action . '::authzCheck', 'completed');

            $t = microtime(true);
            $bf = BugFile::findOrFail($fileId);
            $path = storage_path('bugs/' . $bf->file);
            if (File::exists($path)) File::delete($path);
            $bf->delete();
            $this->logExecutionTime($t, $action . '::delete', 'completed');

            return response()->json(['is_success' => true], 200);
        }, ['fileId' => $fileId]);
    }

    public const TRK = 'tracker';
    public function tracker(Request $request, int $projectId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'view project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $trackers = TimeTracker::where(ActivitiesConstants::COL_PJ, $projectId)->get();
            $this->logExecutionTime($t, $action . '::fetchTrackers', 'completed');

            $viewPath = VW::TMT . '.index';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact('trackers'));
        }, ['projectId' => $projectId]);
    }

    public const GET_PRJ_CHT = 'getProjectChart';
    public function getProjectChart(array $params): array
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($params, $action) {
            $t = microtime(true);
            $dates = [];
            if (($params['duration'] ?? '') === 'week')
                foreach (Utility::getFirstSeventhWeekDay(-1)['datePeriod'] as $date)
                    $dates[$date->format('Y-m-d')] = $date->format('D');
            $this->logExecutionTime($t, $action . '::buildDateRange', 'completed');

            $t = microtime(true);
            $stages = TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $params[DatabaseConstants::COL_TABLE_CREATOR])
                ->orderBy(ActivitiesConstants::COL_OD)
                ->pluck(ProjectsConstants::COL_NM, 'id')
                ->toArray();
            $this->logExecutionTime($t, $action . '::fetchStages', 'completed');

            $result = ['label' => [], 'color' => [], 'stages' => $stages];

            // Single batch query instead of N per-date queries
            $t = microtime(true);
            $dateKeys = array_keys($dates);
            $allData = collect();
            if (!empty($dateKeys)) {
                $batchQuery = ProjectTask::select(
                    DB::raw("DATE(updated_at) as chart_date"),
                    ProjectsConstants::COL_STAGE_ID,
                    DB::raw('count(*) as total')
                )->whereIn(DB::raw("DATE(updated_at)"), $dateKeys);
                if (!empty($params[ActivitiesConstants::COL_PJ]))
                    $batchQuery->where(ActivitiesConstants::COL_PJ, $params[ActivitiesConstants::COL_PJ]);
                $allData = $batchQuery->groupBy('chart_date', ProjectsConstants::COL_STAGE_ID)->get();
            }
            foreach ($dates as $date => $label) {
                foreach ($stages as $id => $_) {
                    $row = $allData->where('chart_date', $date)->where(ProjectsConstants::COL_STAGE_ID, $id)->first();
                    $result[$id][] = $row ? $row->total : 0;
                }
                $result['label'][] = __($label);
            }
            $this->logExecutionTime($t, $action . '::aggregate', 'completed');

            return $result;
        }, ['has_project' => isset($params[ActivitiesConstants::COL_PJ])]);
    }

    public const CP_PRJ = 'copyProject';
    public function copyProject(Request $request, int $projectId): View|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'create project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $project = Project::findOrFail($projectId);
            $this->logExecutionTime($t, $action . '::findProject', 'completed');

            $viewPath = VW::PRJ . '.copy';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact(self::ENTITY));
        }, ['projectId' => $projectId]);
    }

    public const CP_PRJ_ST = 'copyProjectStore';
    public function copyProjectStore(Request $request, int $projectId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'create project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            DB::transaction(function () use ($request, $projectId) {
                $orig = Project::findOrFail($projectId);
                $baseFields = [
                    ProjectsConstants::COL_NM,
                    ActivitiesConstants::COL_TSK_STT,
                    'project_image',
                    'client_id',
                    ActivitiesConstants::COL_DESC,
                    ProjectsConstants::COL_S_DT,
                    ProjectsConstants::COL_E_DT,
                    ProjectsConstants::COL_E_HRS
                ];
                $dupData = Arr::only($orig->toArray(), $baseFields)
                    + [DatabaseConstants::COL_TABLE_CREATOR => $request->user()->creatorId()];

                $new = Project::create($dupData);

                $ids = array_merge($request->user ?? [], [$request->user()->id]);
                foreach (array_unique($ids) as $uid)
                    ProjectUser::create([ActivitiesConstants::COL_PJ => $new->id, UsersConstants::COL_USER_ID => $uid]);

                if ($request->has('task')) {
                    foreach (ProjectTask::where(ActivitiesConstants::COL_PJ, $projectId)->get() as $task) {
                        $clone = $task->replicate([
                            ProjectsConstants::COL_NM,
                            ActivitiesConstants::COL_DESC,
                            ProjectsConstants::COL_E_HRS,
                            ProjectsConstants::COL_S_DT,
                            ProjectsConstants::COL_E_DT,
                            ProjectsConstants::COL_PRT,
                            ProjectsConstants::COL_PR_CL,
                            ProjectsConstants::COL_ASGN,
                            ProjectsConstants::COL_ML_ID,
                            ProjectsConstants::COL_STAGE_ID,
                            ActivitiesConstants::COL_OD,
                            ProjectsConstants::COL_IS_FV,
                            ProjectsConstants::COL_IS_CP,
                            ProjectsConstants::COL_M_AT,
                            ProjectsConstants::COL_PGR
                        ]);
                        $clone->project_id = $new->id;
                        $clone->created_by = $request->user()->creatorId();
                        $clone->save();

                        if (in_array('task_comment', $request->task))
                            foreach ($task->comments as $c) {
                                $nc = $c->replicate(['comment', UsersConstants::COL_USER_ID, 'user_type', DatabaseConstants::COL_TABLE_CREATOR]);
                                $nc->task_id = $clone->id;
                                $nc->save();
                            }

                        if (in_array('task_files', $request->task))
                            foreach ($task->files as $f) {
                                $nf = $f->replicate(['ile', ProjectsConstants::COL_NM, 'extension', 'file_size', DatabaseConstants::COL_TABLE_CREATOR, 'user_type']);
                                $nf->task_id = $clone->id;
                                $nf->save();
                            }
                    }
                }

                if ($request->has('bug')) {
                    foreach (Bug::where(ActivitiesConstants::COL_PJ, $projectId)->get() as $bug) {
                        $clone = $bug->replicate([
                            'bug_id',
                            ActivitiesConstants::COL_TT,
                            ProjectsConstants::COL_PRT,
                            ProjectsConstants::COL_S_DT,
                            'due_date',
                            ActivitiesConstants::COL_DESC,
                            ActivitiesConstants::COL_TSK_STT,
                            ActivitiesConstants::COL_OD,
                            ProjectsConstants::COL_ASGN
                        ]);
                        $clone->project_id = $new->id;
                        $clone->created_by = $request->user()->creatorId();
                        $clone->save();

                        if (in_array('bug_comment', $request->bug)) {
                            foreach ($bug->comments as $c) {
                                $nc = $c->replicate(['comment', 'user_type', DatabaseConstants::COL_TABLE_CREATOR]);
                                $nc->bug_id = $clone->id;
                                $nc->save();
                            }
                        }

                        if (in_array('bug_files', $request->bug)) {
                            foreach ($bug->files as $f) {
                                $nf = $f->replicate(['file', ProjectsConstants::COL_NM, 'extension', 'file_size', 'user_type', DatabaseConstants::COL_TABLE_CREATOR]);
                                $nf->bug_id = $clone->id;
                                $nf->save();
                            }
                        }
                    }
                }

                if ($request->has('milestone')) {
                    foreach (Milestone::where(ActivitiesConstants::COL_PJ, $projectId)->get() as $m) {
                        $nm = $m->replicate([ActivitiesConstants::COL_TT, ActivitiesConstants::COL_TSK_STT, 'due_date', ProjectsConstants::COL_S_DT, 'cost', ProjectsConstants::COL_PGR]);
                        $nm->project_id = $new->id;
                        $nm->save();
                    }
                }

                if ($request->has('activity')) {
                    $types = [];
                    foreach (['Create Milestone', 'Create Task', 'Move', 'Create Bug', 'Move Bug', 'Invite User', 'Upload File'] as $t)
                        if (in_array(strtolower(str_replace(' ', '_', $t)), $request->activity))
                            $types[] = $t;

                    ActivityLog::where(ActivitiesConstants::COL_PJ, $projectId)
                        ->whereIn('log_type', $types)
                        ->get()
                        ->each(function ($a) use ($new) {
                            $na = $a->replicate([UsersConstants::COL_USER_ID, 'log_type', 'remark']);
                            $na->project_id = $new->id;
                            $na->save();
                        });
                }
            });
            $this->logExecutionTime($t, $action . '::transaction', 'completed');

            return Redirect::back()->with('success', __('Project duplicated successfully.'));
        }, ['projectId' => $projectId]);
    }

    public const CP_LNK_ST_CRT = 'copyLinkSettingCreate';

    public const PRJ_CP_LNK = 'projectCopyLink';
    /**
     * Gera um link compartilhável para o projeto a partir do ID criptografado.
     */
    public function projectCopyLink(Request $request, int|string $id): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'view project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            try {
                $t = microtime(true);
                $project = Project::findOrFail($id);
                $this->logExecutionTime($t, $action . '::fetchProject', 'completed');

                $encrypted = Crypt::encrypt($project->id);
                $link = url(VW::PRJ . '/link/' . $encrypted);

                Log::info($action . ' generated', ['project_id' => $id, 'link' => $link]);

                if ($request->wantsJson()) {
                    return response()->json(['link' => $link, 'success' => true]);
                }

                return Redirect::back()->with('success', __('Link generated successfully.'))->with('link', $link);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage(), 'project_id' => $id]);
                return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
            }
        }, ['projectId' => $id]);
    }

    public function copyLinkSettingCreate(Request $request, int $projectId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'view project', VW::PRJ . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $project = Project::join('project_users', VW::PRJ . '.id', '=', 'project_users.project_id')
                ->where('project_users.user_id', Auth::id())
                ->where(VW::PRJ . '.id', $projectId)
                ->firstOrFail();
            $settings = json_decode($project->copylinksetting, true) ?? [];
            $this->logExecutionTime($t, $action . '::fetchProject', 'completed');

            $viewPath = VW::PRJ . '.copylink_setting';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact(self::ENTITY, 'projectId', 'settings'));
        }, ['projectId' => $projectId]);
    }

    public const CP_LNK_ST = 'copyLinkSetting';
    public function copyLinkSetting(Request $request, int $projectId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            $t = microtime(true);
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $t = microtime(true);
            if (($g = self::guard($request, 'edit project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
            $this->logExecutionTime($t, $action . '::guard', 'completed');

            $t = microtime(true);
            $project = Project::join('project_users', VW::PRJ . '.id', '=', 'project_users.project_id')
                ->where('project_users.user_id', Auth::id())
                ->where(VW::PRJ . '.id', $projectId)
                ->firstOrFail();

            $fields = [
                'basic_details',
                'member',
                'milestone',
                PermissionsConstants::CL,
                ProjectsConstants::COL_PGR,
                'activity',
                'attachment',
                'bug_report',
                'expense',
                'task',
                'tracker_details',
                'timesheet',
                'password_protected'
            ];
            $data = [];
            foreach ($fields as $f) $data[$f] = $request->has($f) ? 'on' : 'off';
            if ($data['password_protected'] === 'on') {
                if (!empty($request->password)) $project->password = Hash::make($request->password);
            } else {
                $project->password = null;
            }
            $project->copylinksetting = json_encode($data);
            $project->save();
            $this->logExecutionTime($t, $action . '::persist', 'completed');

            return Redirect::back()->with('success', __('Copy Link Setting saved.'));
        }, ['projectId' => $projectId]);
    }

    public const PRJ_LNK = 'projectLink';
    public function projectLink(Request $request, string $encrypted, string $lang = DatabaseConstants::DEFAULT_LANG): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $encrypted, $lang, $action) {
            $t = microtime(true);
            try {
                $id = Crypt::decrypt($encrypted);
            } catch (\Throwable $e) {
                return Redirect::back()->with('error', __('Project not found.'));
            }
            $this->logExecutionTime($t, $action . '::decrypt', 'completed');

            $t = microtime(true);
            $project = Project::findOrFail($id);
            $settings = json_decode($project->copylinksetting, true) ?? [];
            App::setLocale($lang ?: (Auth::user()->lang ?? env('DEFAULT_ADMIN_LANG')));
            $this->logExecutionTime($t, $action . '::loadProjectAndSettings', 'completed');

            // password gate
            $viewPwd = VW::PRJ . '.copylink_password';
            if (($settings['password_protected'] ?? '') === 'on'
                && !Hash::check($request->password ?? '', $project->password ?? '')
                && session("copy_pass_true{$id}") !== "{$project->password}-{$id}"
            ) {
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPwd);
                $this->logExecutionTime($t, $action . '::viewPwdExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPwd} not found!");
                return view($viewPwd, compact('id'));
            }

            session(["copy_pass_true{$id}" => "{$project->password}-{$id}"]);

            // metrics building (unchanged)
            $t = microtime(true);
            $usr = Auth::user() ?: User::find($project->created_by);

            $totalTasks = $project->tasks->count();
            $doneTasks  = $project->tasks->where(ProjectsConstants::COL_IS_CP, 1)->count();
            $project_data['task'] = [
                'total'      => number_format($totalTasks),
                'done'       => number_format($doneTasks),
                'percentage' => Utility::getPercentage($doneTasks, $totalTasks),
            ];

            $expAmt = $project->expense->sum('amount');
            $project_data['expense'] = [
                'allocated'  => $project->budget,
                'total'      => $expAmt,
                'percentage' => Utility::getPercentage($expAmt, $project->budget),
            ];

            $totalUsers = User::where(DatabaseConstants::COL_TABLE_CREATOR, $usr->id)->count();
            $project_data['user_assigned'] = [
                'total'      => number_format($totalUsers) . '/' . number_format($totalUsers),
                'percentage' => Utility::getPercentage($totalUsers, $totalUsers),
            ];

            $totalDays    = Carbon::parse($project[ProjectsConstants::COL_S_DT])->diffInDays(Carbon::parse($project[ProjectsConstants::COL_E_DT]));
            $remainingDays = Carbon::parse($project[ProjectsConstants::COL_S_DT])->diffInDays(now());
            $project_data['day_left'] = [
                'day'        => number_format($remainingDays) . '/' . number_format($totalDays),
                'percentage' => Utility::getPercentage($remainingDays, $totalDays),
            ];

            $openQuery = ProjectTask::where(ActivitiesConstants::COL_PJ, $id)->where(ProjectsConstants::COL_IS_CP, 0);
            if ($usr->checkProject($id) !== 'Owner')
                $openQuery->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$usr->id]);
            $openCount  = $openQuery->count();
            $totalCount = $project->tasks->count();
            $project_data['open_task'] = [
                DatabaseConstants::TABLE_TASKS => number_format($openCount) . '/' . number_format($totalCount),
                'percentage' => Utility::getPercentage($openCount, $totalCount),
            ];

            $totalMs = $project->milestones()->count();
            $doneMs  = $project->milestones()->where(ActivitiesConstants::COL_TSK_STT, ProjectsConstants::STT_CPT_K)->count();
            $project_data['milestone'] = [
                'total'      => number_format($doneMs) . '/' . number_format($totalMs),
                'percentage' => Utility::getPercentage($doneMs, $totalMs),
            ];

            $hrsData = Project::projectHrs($id);
            $project_data['task_allocated_hrs'] = [
                'hrs'        => number_format($hrsData['allocated']) . '/' . number_format($hrsData['allocated']),
                'percentage' => Utility::getPercentage($hrsData['allocated'], $hrsData['allocated']),
            ];

            $timesQuery = $usr->checkProject($id) === 'Owner'
                ? $project->timesheets()
                : $project->timesheets()->where(DatabaseConstants::COL_TABLE_CREATOR, $usr->id);
            $times = $timesQuery->pluck('time')->toArray();
            $totTime = str_replace(':', '.', Utility::timeToHr($times));
            $estHrs = $project->estimated_hrs ?: 0;
            $project_data['time_spent'] = [
                'total'      => number_format($totTime) . '/' . number_format($estHrs),
                'percentage' => Utility::getPercentage($totTime, $estHrs),
            ];

            $sevenDays = Utility::getLastSevenDays();
            $chartTask = $chartTs = [];
            $sumTask = $sumTs = 0;
            $dateKeys = array_keys($sevenDays);
            $isOwner = $usr->checkProject($id) === 'Owner';

            // Batch chart queries (2 instead of 14)
            $completedByDate = $project->tasks()
                ->where(ProjectsConstants::COL_IS_CP, 1)
                ->when(!$isOwner, fn($q) => $q->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$usr->id]))
                ->where(function ($q) use ($dateKeys) {
                    foreach ($dateKeys as $d) $q->orWhere(ProjectsConstants::COL_M_AT, 'LIKE', $d . '%');
                })
                ->selectRaw("DATE(" . ProjectsConstants::COL_M_AT . ") as chart_date, COUNT(*) as cnt")
                ->groupBy('chart_date')
                ->pluck('cnt', 'chart_date');

            $timesheetsByDate = $project->timesheets()
                ->when(!$isOwner, fn($q) => $q->where(DatabaseConstants::COL_TABLE_CREATOR, $usr->id))
                ->where(function ($q) use ($dateKeys) {
                    foreach ($dateKeys as $d) $q->orWhere('date', 'LIKE', $d . '%');
                })
                ->selectRaw("DATE(date) as chart_date, GROUP_CONCAT(time) as times")
                ->groupBy('chart_date')
                ->pluck('times', 'chart_date');

            foreach ($dateKeys as $date) {
                $taskCnt = $completedByDate[$date] ?? 0;
                $tsCnt = str_replace(':', '.', Utility::timeToHr(
                    isset($timesheetsByDate[$date]) ? explode(',', $timesheetsByDate[$date]) : []
                ));
                $chartTask[] = $taskCnt;
                $sumTask += $taskCnt;
                $chartTs[]   = $tsCnt;
                $sumTs   += $tsCnt;
            }
            $project_data['task_chart']      = ['chart' => $chartTask, 'total' => $sumTask];
            $project_data['timesheet_chart'] = ['chart' => $chartTs,   'total' => $sumTs];

            // Batch all tasks for this project (1 query instead of N per stage)
            $allProjectTasks = ProjectTask::where(ActivitiesConstants::COL_PJ, $id)
                ->when(!$isOwner, fn($q) => $q->whereRaw("find_in_set(?," . ProjectsConstants::COL_ASGN . ")", [$usr->id]))
                ->orderBy(ActivitiesConstants::COL_OD)
                ->get();

            $stages = TaskStage::orderBy(ActivitiesConstants::COL_OD)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $project->created_by)
                ->get()
                ->map(function ($s) use ($allProjectTasks) {
                    return [
                        'id' => $s->id,
                        ProjectsConstants::COL_NM => $s[ProjectsConstants::COL_NM],
                        DatabaseConstants::TABLE_TASKS => $allProjectTasks->where(ProjectsConstants::COL_STAGE_ID, $s->id)->values(),
                    ];
                });

            $trackers = TimeTracker::where(ActivitiesConstants::COL_PJ, $id)
                ->when(Auth::check(), fn($q) => $q->where(DatabaseConstants::COL_TABLE_CREATOR, Auth::id()))
                ->get();
            $bugs  = Bug::where(ActivitiesConstants::COL_PJ, $id)->get();
            $tasks = ProjectTask::where(ActivitiesConstants::COL_PJ, $id)->get();
            $users = $project->users ?? collect();
            $this->logExecutionTime($t, $action . '::buildPayload', 'completed');

            $viewPath = VW::PRJ . '.copylink';
            $t = microtime(true);
            $exists = ViewFacade::exists($viewPath);
            $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
            if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return view($viewPath, compact(
                'settings',
                self::ENTITY,
                'project_data',
                'stages',
                'trackers',
                'users',
                DatabaseConstants::TABLE_BUGS,
                DatabaseConstants::TABLE_TASKS,
                'lang'
            ));
        }, ['encrypted' => $encrypted, 'lang' => $lang]);
    }

    public const BUG_NB = 'bugNumber';
    private function bugNumber(): int|string|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $user ??= null;
        $creatorId ??= null;
        $max ??= null;
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $creatorId ??= $user?->creatorId();
            $max = $creatorId
                ? Bug::where(DC::COL_TABLE_CREATOR, $creatorId)->max('bug_id')
                : null;
            if ($max === null) return 1;
            return is_numeric($max) ? $max + 1 : $max;
        } catch (QueryException $e) {
            Log::error($method . ' query failed', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'action' => $action,
                'creator_id' => $creatorId,
                'user_id' => $user?->id,
            ]);
            return 1;
        } catch (\Exception $e) {
            Log::error($method . ' failed', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'action' => $action,
                'creator_id' => $creatorId,
                'user_id' => $user?->id,
            ]);
            return 1;
        } catch (\Throwable $e) {
            Log::error($method . ' throwable', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'action' => $action,
                'creator_id' => $creatorId,
                'user_id' => $user?->id,
            ]);
            return 1;
        }
    }

    /**
     * Generate and return a copy link for a project.
     */
    public const PRJ_CPY_LNK = 'projectCopyLink';
    public function projectCopyLink(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (($guard = self::guard($request, PMC::MNG_PRJ, VW::PRJ . '.index')) instanceof RedirectResponse) return $guard;
                $project = Project::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->findOrFail($id);
                $link = route(VW::PRJ . '.show', Crypt::encrypt($project->id));
                Log::info("[{$action}] link generated", ['project_id' => $id]);
                return response()->json(['success' => true, 'link' => $link]);
            } catch (ModelNotFoundException $e) {
                Log::notice($method . ' project not found', ['id' => $id]);
                return response()->json(['error' => 'Project not found'], 404);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * Share a project with additional users.
     */
    public const SHR_PRJ = 'shareProject';
    public function shareProject(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (($guard = self::guard($request, PMC::MNG_PRJ, VW::PRJ . '.index')) instanceof RedirectResponse) return $guard;
                $project = Project::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->findOrFail($id);
                $userIds = $request->input('user_ids', []);
                foreach ($userIds as $userId) {
                    ProjectUser::firstOrCreate(['project_id' => $project->id, 'user_id' => $userId]);
                }
                Log::info("[{$action}] project shared", ['project_id' => $id, 'shared_with' => $userIds]);
                return response()->json(['success' => true, 'message' => __('Project shared successfully.')]);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * Get or check user permissions for a project.
     */
    public const USR_PRM = 'userPermission';
    public function userPermission(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                $project = Project::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->findOrFail($id);
                $projectUsers = ProjectUser::where('project_id', $project->id)->pluck('user_id');
                $isAssigned = $projectUsers->contains($user?->id);
                Log::info("[{$action}] permission checked", ['project_id' => $id, 'is_assigned' => $isAssigned]);
                return response()->json([
                    'success' => true,
                    'is_assigned' => $isAssigned,
                    'project_users' => $projectUsers,
                ]);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    public const USR_PRM_STR = 'userPermissionStore';
    public function userPermissionStore(Request $request, int|string $id, int|string $uid): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $uid, $action, $method) {
            $inTransaction ??= false;
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user ??= $userOrRedirect;
                if (($guard = self::guard($request, PMC::MNG_PRJ, VW::PRJ . '.index')) instanceof RedirectResponse) return $guard;
                $project ??= Project::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->findOrFail($id);
                $projectUser ??= ProjectUser::where(PJC::COL_PJ_ID, $project->id)
                    ->where(UC::COL_USER_ID, $uid)
                    ->firstOrFail();
                $validated ??= $request->validate([
                    PJC::COL_CAN_WRT_OWN => 'sometimes|boolean',
                    PJC::COL_CAN_WRT_OTH => 'sometimes|boolean',
                    PJC::COL_CAN_RD_OTH => 'sometimes|boolean',
                    PJC::COL_IS_PRJ_LD => 'sometimes|boolean',
                    'role' => 'sometimes|string',
                ]);
                if (empty($validated)) {
                    return response()->json(['error' => 'No permission data provided'], 422);
                }
                DB::beginTransaction();
                $inTransaction = true;
                $projectUser->update($validated);
                DB::commit();
                $inTransaction = false;
                Log::info("[{$action}] permissions updated", [
                    'project_id' => $id,
                    'target_user_id' => $uid,
                ]);
                return response()->json(['success' => true, 'message' => __('Permissions updated successfully.')]);
            } catch (ModelNotFoundException $e) {
                Log::notice($method . ' not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'project_id' => $id,
                    'target_user_id' => $uid,
                ]);
                return response()->json(['error' => 'Project or user not found'], 404);
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'project_id' => $id,
                    'target_user_id' => $uid,
                ]);
                return response()->json(['error' => $e->errors()], 422);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $id,
                    'target_user_id' => $uid,
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $id,
                    'target_user_id' => $uid,
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $id,
                    'target_user_id' => $uid,
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }

    public const RM_USR_PRJ = 'removeUserFromProject';
    public function removeUserFromProject(Request $request, int|string $project_id, int|string $user_id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $project_id, $user_id, $action, $method) {
            $inTransaction ??= false;
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user ??= $userOrRedirect;
                $creatorId ??= $user?->ownerId() ?? $user?->creatorId() ?? $user?->id;
                if (($guard = self::guard($request, 'delete project', VW::PRJ . '.index')) instanceof RedirectResponse) return $guard;
                $project ??= Project::findOrFail($project_id);
                if (($project->{DC::COL_TABLE_CREATOR} ?? null) !== $creatorId) {
                    return defaultPermissionDenial($request, new \Exception, $method, VW::PRJ . '.index');
                }
                DB::beginTransaction();
                $inTransaction = true;
                $deleted ??= ProjectUser::where(PJC::COL_PJ_ID, $project_id)
                    ->where(UC::COL_USER_ID, $user_id)
                    ->delete();
                DB::commit();
                $inTransaction = false;
                if (empty($deleted)) {
                    Log::notice("[{$action}] no matching user found to remove", [
                        'project_id' => $project_id,
                        'target_user_id' => $user_id,
                    ]);
                    return Redirect::back()->with('error', __('User not found in project.'));
                }
                Log::info("[{$action}] user removed from project", [
                    'project_id' => $project_id,
                    'target_user_id' => $user_id,
                ]);
                return Redirect::back()->with('success', __('User successfully removed from project!'));
            } catch (ModelNotFoundException $e) {
                Log::warning($method . ' project not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $project_id,
                    'target_user_id' => $user_id,
                ]);
                return Redirect::back()->with('error', __('Project not found.'));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $project_id,
                    'target_user_id' => $user_id,
                ]);
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $project_id,
                    'target_user_id' => $user_id,
                ]);
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $project_id,
                    'target_user_id' => $user_id,
                ]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['project_id' => $project_id, 'user_id' => $user_id]);
    }

    public const STR_PRJ_TSK_STG = 'storeProjectTaskStages';
    public function storeProjectTaskStages(Request $request, int|string $id, string $slug): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $slug, $action, $method) {
            $inTransaction ??= false;
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user ??= $userOrRedirect;
                $creatorId ??= $user?->creatorId();
                if (($guard = self::guard($request, PMC::MNG_PRJ, VW::PRJ . '.index')) instanceof RedirectResponse) return $guard;
                $project ??= Project::where(DC::COL_TABLE_CREATOR, $creatorId)->findOrFail($id);
                $stages ??= $request->input('stages', []);
                if (!is_array($stages)) {
                    $stages = [];
                }
                DB::beginTransaction();
                $inTransaction = true;
                $order ??= 0;
                foreach ($stages as $stageData) {
                    if (!is_array($stageData)) continue;
                    $order++;
                    TaskStage::updateOrCreate(
                        [
                            AC::COL_PJ => $project->id,
                            'name' => $stageData['name'] ?? $slug,
                            DC::COL_TABLE_CREATOR => $creatorId,
                        ],
                        [
                            'order' => $stageData['order'] ?? $order,
                            'color' => $stageData['color'] ?? null,
                        ]
                    );
                }
                if (empty($stages)) {
                    TaskStage::updateOrCreate(
                        [
                            AC::COL_PJ => $project->id,
                            'name' => $slug,
                            DC::COL_TABLE_CREATOR => $creatorId,
                        ],
                        [
                            'order' => 0,
                        ]
                    );
                }
                DB::commit();
                $inTransaction = false;
                Log::info("[{$action}] stages stored", [
                    'project_id' => $id,
                    'slug' => $slug,
                    'stages_count' => max(count($stages), 1),
                ]);
                return response()->json(['success' => true, 'message' => __('Stages stored successfully.')]);
            } catch (ModelNotFoundException $e) {
                Log::notice($method . ' project not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'project_id' => $id,
                    'slug' => $slug,
                ]);
                return response()->json(['error' => 'Project not found'], 404);
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'project_id' => $id,
                    'slug' => $slug,
                ]);
                return response()->json(['error' => $e->errors()], 422);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $id,
                    'slug' => $slug,
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $id,
                    'slug' => $slug,
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'project_id' => $id,
                    'slug' => $slug,
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    }
}
