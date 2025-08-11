<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    SupportsConstants,
    UsersConstants
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
    Redirect,
    Crypt,
    Validator
};
use Illuminate\View\View;

class ProjectController extends Controller
{

    use ChecksLogin, ChecksPermissions;

    private const ENTITY = 'project';

    public function index(Request $request, string $view = 'grid'): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard(
                $request,
                PermissionsConstants::MNG_PRJ,
                DatabaseConstants::TABLE_PROJECTS . '.' . __FUNCTION__
            ))
            instanceof RedirectResponse
        ) return $guard;
        return view(
            DatabaseConstants::TABLE_PROJECTS . '.' . __FUNCTION__,
            compact('view')
        );
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, 'create project', DatabaseConstants::TABLE_PROJECTS . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        $creatorId = $request->user()->creatorId();
        $users = User::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
            ->get()
            ->pluck(UsersConstants::COL_NM, 'id');
        $clients = User::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->where(UsersConstants::COL_TP, '=', PermissionsConstants::CL)
            ->get()
            ->pluck(UsersConstants::COL_NM, 'id');
        $clients->prepend('Select Client', '');
        $users->prepend('Select User', '');
        return view(DatabaseConstants::TABLE_PROJECTS . '.' . __FUNCTION__, compact(
            DatabaseConstants::TABLE_CLIENTS,
            DatabaseConstants::TABLE_USERS
        ));
    }

    public function store(Request $request): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'create project', DatabaseConstants::TABLE_PROJECTS
            . '.index')) instanceof RedirectResponse) return $g;
        try {
            $data = Validator::make($request->all(), [
                ProjectsConstants::COL_NM   => 'required|string',
                ProjectsConstants::COL_S_DT     => 'required|date',
                ProjectsConstants::COL_E_DT       => 'required|date',
                'project_image'  => 'required|file',
                PermissionsConstants::CL         => 'required',
                'budget'         => 'nullable|numeric',
                ActivitiesConstants::COL_DESC    => 'nullable|string',
                ActivitiesConstants::COL_TSK_STT         => 'required|string',
                ProjectsConstants::COL_E_HRS  => 'nullable',
                'tag'            => 'nullable|string'
            ])->validate();
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
            $pd[ProjectsConstants::COL_E_DT]  = Carbon::parse($data[ProjectsConstants::COL_E_DT])->toDateTimeString();
            if ($request->hasFile('project_image')) {
                $file = $request->file('project_image');
                $size = $file->getSize();
                $res = Utility::updateStorageLimit($request->user()->creatorId(), $size);
                if ($res === 1) {
                    $fn = time() . '.' . $file->extension();
                    $file->storeAs(DatabaseConstants::TABLE_PROJECTS, $fn);
                    $pd['project_image'] = 'projects/' . $fn;
                }
            }
            $pd[DatabaseConstants::TABLE_CREATOR] = $request->user()->creatorId();
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
            $project = Project::create($pd);
            $creator = $request->user();
            $ids = $creator->type === PermissionsConstants::CPN
                ? [$creator->id]
                : [$creator->creatorId(), $creator->id];
            foreach (array_unique(array_merge($ids, $request->user ?? [])) as $uid) {
                ProjectUser::create([
                    ActivitiesConstants::COL_PJ => $project->id,
                    UsersConstants::COL_USER_ID    => $uid,
                ]);
            }
            $setting = Utility::settings($creator->creatorId());
            $notif  = [
                'project_name' => $project[ProjectsConstants::COL_NM],
                'user_name'    => $creator[UsersConstants::COL_NM],
            ];
            foreach (
                [
                    'project_notification' => 'send_slack_msg',
                    'telegram_project_notification' => 'send_telegram_msg'
                ] as $key => $method
            )
                if (!empty($setting[$key])) Utility::$method('new_project', $notif);
            if ($hook = Utility::webhookSetting('New Project')) {
                if (!Utility::webhookCall($hook['url'], $project->toJson(), $hook['method']))
                    return Redirect::back()->with('error', __('Webhook call failed.'));
            }
            return Redirect::route(DatabaseConstants::TABLE_PROJECTS . '.index')
                ->with('success', __('Project Add Successfully')
                    . ($res !== 1 ? '<br><span class="text-danger">' . $res . '</span>' : ''));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Request $request, Project $project): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'view project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        try {
            $usr = $request->user();
            $allowed = $usr->type === PermissionsConstants::CL
                ? Project::where('client_id', $usr->id)->pluck('id')->toArray()
                : $usr->projects->pluck('id')->toArray();
            if (!in_array($project->id, $allowed)) {
                return Redirect::back()->with('error', __('Permission Denied.'));
            }
            $pd = [];
            // Task count
            $tot = ProjectTask::where(ActivitiesConstants::COL_PJ, $project->id)->count();
            $done = ProjectTask::where(ActivitiesConstants::COL_PJ, $project->id)
                ->where(ProjectsConstants::COL_IS_CP, 1)->count();
            $pd['task'] = [
                'total' => $tot,
                'done' => $done,
                'percentage' => Utility::getPercentage($done, $tot)
            ];
            // expense
            $exp = $project->expense->sum('amount');
            $pd['expense'] = [
                'allocated' => $project->budget,
                'total' => $exp,
                'percentage' => Utility::getPercentage($exp, $project->budget)
            ];
            // users assigned
            $totalUsers = User::where(DatabaseConstants::TABLE_CREATOR, $usr->id)->count();
            $pd['user_assigned'] = [
                'total' => "$totalUsers/$totalUsers",
                'percentage' => Utility::getPercentage($totalUsers, $totalUsers)
            ];
            // day left
            $totDays = Carbon::parse($project[ProjectsConstants::COL_S_DT])
                ->diffInDays(Carbon::parse($project[ProjectsConstants::COL_E_DT]));
            $remDays = Carbon::parse($project[ProjectsConstants::COL_S_DT])
                ->diffInDays(now());
            $pd['day_left'] = [
                'day' => "$remDays/$totDays",
                'percentage' => Utility::getPercentage($remDays, $totDays)
            ];
            // open task
            $open = ProjectTask::where(ActivitiesConstants::COL_PJ, $project->id)
                ->where(ProjectsConstants::COL_IS_CP, 0)
                ->where(DatabaseConstants::TABLE_CREATOR, $usr->creatorId())
                ->count();
            $pd['open_task'] = [
                DatabaseConstants::TABLE_TASKS => "$open/{$project->tasks->count()}",
                'percentage' => Utility::getPercentage($open, $project->tasks->count())
            ];
            // milestone
            $totMs = $project->milestones()->count();
            $doneMs = $project->milestones()
                ->where(ActivitiesConstants::COL_TSK_STT, ProjectsConstants::STT_CPT_K)->count();
            $pd['milestone'] = [
                'total' => "$doneMs/$totMs",
                'percentage' => Utility::getPercentage($doneMs, $totMs)
            ];
            // time spent
            $times = $project->timesheets()
                ->where(DatabaseConstants::TABLE_CREATOR, $usr->id)
                ->pluck('time')->toArray();
            $hrs = str_replace(
                ':',
                '.',
                Utility::timeToHr($times)
            );
            $pd['time_spent'] = [
                'total' => "$hrs/$hrs",
                'percentage' => Utility::getPercentage($hrs, $hrs)
            ];
            // allocated hours
            $ah = Project::projectHrs($project->id);
            $pd['task_allocated_hrs'] = [
                'hrs' => "{$ah['allocated']}/{$ah['allocated']}",
                'percentage' => Utility::getPercentage($ah['allocated'], $ah['allocated'])
            ];
            // charts
            $days = Utility::getLastSevenDays();
            $ct = [];
            $ts = [];
            $cntT = 0;
            $cntTs = 0;
            foreach (array_keys($days) as $date) {
                $c = $project->tasks()
                    ->where(ProjectsConstants::COL_IS_CP, 1)
                    ->whereRaw("find_in_set('{$usr->id}'," . ProjectsConstants::COL_ASGN . ")")
                    ->where(ProjectsConstants::COL_M_AT, 'LIKE', $date)
                    ->count();
                $t = str_replace(
                    ':',
                    '.',
                    Utility::timeToHr(
                        $project->timesheets()
                            ->where(DatabaseConstants::TABLE_CREATOR, $usr->id)
                            ->where('date', 'LIKE', $date)
                            ->pluck('time')->toArray()
                    )
                );
                $ct[] = $c;
                $ts[] = $t;
                $cntT += $c;
                $cntTs += $t;
            }
            $pd['task_chart']     = ['chart' => $ct, 'total' => $cntT];
            $pd['timesheet_chart'] = ['chart' => $ts, 'total' => $cntTs];

            $lastTask = \App\Models\TaskStage::where(DatabaseConstants::TABLE_CREATOR, $usr->creatorId())
                ->orderBy(ActivitiesConstants::COL_OD, 'DESC')->first();
            return view(DatabaseConstants::TABLE_PROJECTS . '.view', [
                self::ENTITY => $project,
                'project_data' => $pd,
                'last_task' => $lastTask
            ]);
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(Request $request, Project $project): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'edit project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        try {
            $creatorId = $request->user()->creatorId();
            if ($project->created_by !== $creatorId) {
                return Redirect::back()->with('error', __('Permission Denied.'));
            }
            $clients = User::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where(UsersConstants::COL_TP, PermissionsConstants::CL)
                ->pluck(UsersConstants::COL_NM, 'id');
            return view(DatabaseConstants::TABLE_PROJECTS . '.' . __FUNCTION__, compact(self::ENTITY, DatabaseConstants::TABLE_CLIENTS));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function update(Request $request, Project $project): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'edit project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        try {
            $data = Validator::make($request->all(), [
                ProjectsConstants::COL_NM => 'required|string',
                ProjectsConstants::COL_S_DT   => 'required|date',
                ProjectsConstants::COL_E_DT     => 'required|date',
                'project_image' => 'nullable|file',
                PermissionsConstants::CL       => 'required',
                'budget'       => 'nullable|numeric',
                ActivitiesConstants::COL_DESC  => 'nullable|string',
                ActivitiesConstants::COL_TSK_STT       => 'required|string',
                ProjectsConstants::COL_E_HRS => 'nullable',
                'tag'          => 'nullable|string'
            ])->validate();
            $project[ProjectsConstants::COL_NM] = $data[ProjectsConstants::COL_NM];
            $project[ProjectsConstants::COL_S_DT] = Carbon::parse($data[ProjectsConstants::COL_S_DT])->toDateTimeString();
            $project[ProjectsConstants::COL_E_DT]  = Carbon::parse($data[ProjectsConstants::COL_E_DT])->toDateTimeString();
            if ($request->hasFile('project_image')) {
                $old = $project->project_image;
                $size = $request->file('project_image')->getSize();
                $res = Utility::updateStorageLimit($request->user()->creatorId(), $size);
                if ($res === 1) {
                    Utility::changeStorageLimit($request->user()->creatorId(), $old);
                    $fn = time() . '.' . $request->project_image->extension();
                    $request->file('project_image')
                        ->storeAs(DatabaseConstants::TABLE_PROJECTS, $fn);
                    $project->project_image = 'projects/' . $fn;
                }
            }
            Arr::except($data, [
                ProjectsConstants::COL_NM,
                ProjectsConstants::COL_S_DT,
                ProjectsConstants::COL_E_DT,
                'project_image'
            ])
                + Arr::only($data, [
                    PermissionsConstants::CL,
                    'budget',
                    ActivitiesConstants::COL_DESC,
                    ActivitiesConstants::COL_TSK_STT,
                    ProjectsConstants::COL_E_HRS,
                    'tag'
                ])
                + [ProjectsConstants::COL_NM => $project[ProjectsConstants::COL_NM]]  // ensure name
            ;
            $project->client_id   = $data[PermissionsConstants::CL];
            $project->budget      = $data['budget'] ?? 0;
            $project->description = $data[ActivitiesConstants::COL_DESC];
            $project->status      = $data[ActivitiesConstants::COL_TSK_STT];
            $project->estimated_hrs = $data[ProjectsConstants::COL_E_HRS];
            $project->tags        = $data['tag'];
            $project->save();

            return Redirect::route(DatabaseConstants::TABLE_PROJECTS . '.index')
                ->with('success', __('Project Updated Successfully')
                    . ($res !== 1 ? '<br><span class="text-danger">' . $res . '</span>' : ''));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $request, Project $project): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'delete project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        try {
            if ($project->project_image)
                Utility::changeStorageLimit($request->user()->creatorId(), $project->project_image);
            $project->delete();
            return Redirect::back()->with('success', __('Project Successfully Deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function inviteMemberView(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, PermissionsConstants::MNG_PRJ, DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        try {
            $project = Project::findOrFail($projectId);
            $existing = $project->users->pluck('id')->toArray();
            $users = User::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
                ->whereNotIn('id', $existing)
                ->get();
            return view(DatabaseConstants::TABLE_PROJECTS . '.invite', compact('projectId', DatabaseConstants::TABLE_USERS));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function inviteProjectUserMember(Request $request): JsonResponse
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse)
            return response()->json(['error' => 'Permission denied.'], 401);
        if (($g = self::guard(
            $request,
            'edit project',
            DatabaseConstants::TABLE_PROJECTS . '.index'
        )) instanceof RedirectResponse)
            return response()->json(['error' => 'Permission denied.'], 401);
        try {
            $user = $request->user();
            ProjectUser::create([
                ActivitiesConstants::COL_PJ => $request->project_id,
                UsersConstants::COL_USER_ID    => $request->user_id,
                'invited_by' => $user?->id,
            ]);
            ActivityLog::create([
                UsersConstants::COL_USER_ID    => $user?->id,
                ActivitiesConstants::COL_PJ => $request->project_id,
                'log_type'   => 'Invite User',
                'remark'     => json_encode([ActivitiesConstants::COL_TT => $user[ProjectsConstants::COL_NM]]),
            ]);
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
    }

    public function destroyProjectUser(Request $request, int|string $projectId, int $userId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard(
            $request,
            'delete project',
            DatabaseConstants::TABLE_PROJECTS . '.index'
        )) instanceof RedirectResponse) return $g;
        try {
            $project = Project::findOrFail($projectId);
            if ($project->created_by !== $request->user()->ownerId()) {
                return defaultPermissionDenial(
                    $request,
                    new \Exception,
                    __CLASS__ . '::' . __FUNCTION__,
                    DatabaseConstants::TABLE_PROJECTS . '.index'
                );
            }
            ProjectUser::where(ActivitiesConstants::COL_PJ, $projectId)
                ->where(UsersConstants::COL_USER_ID, $userId)
                ->delete();
            return Redirect::back()->with('success', __('User successfully deleted!'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function loadUser(Request $request): JsonResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return null;
        if (($g = self::guard(
            $request,
            PermissionsConstants::MNG_PRJ,
            DatabaseConstants::TABLE_PROJECTS . '.index'
        )) instanceof RedirectResponse)
            return response()->json(['error' => 'Permission denied.'], 401);
        if (!$request->ajax()) return null;
        try {
            $project = Project::findOrFail($request->project_id);
            $html = view(
                DatabaseConstants::TABLE_PROJECTS . '.' . DatabaseConstants::TABLE_USERS,
                compact(self::ENTITY)
            )->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function milestone(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'create milestone', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $project = Project::findOrFail($projectId);
        return view(DatabaseConstants::TABLE_PROJECTS . '.milestone', compact(self::ENTITY));
    }

    public function milestoneStore(Request $request, int|string $projectId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'create milestone', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $validator = Validator::make($request->all(), [
            ActivitiesConstants::COL_TT      => 'required',
            ActivitiesConstants::COL_TSK_STT     => 'required',
            'cost'       => 'required|numeric',
            ProjectsConstants::COL_S_DT => 'required|date',
            'due_date'   => 'required|date',
        ]);
        if ($validator->fails()) {
            return Redirect::back()->with('error', Utility::errorFormat($validator->getMessageBag()));
        }
        try {
            $m = Milestone::create([
                ActivitiesConstants::COL_PJ  => $projectId,
                ActivitiesConstants::COL_TT       => $request->title,
                ActivitiesConstants::COL_TSK_STT      => $request->status,
                'cost'        => $request->cost,
                ProjectsConstants::COL_S_DT  => Carbon::parse($request[ProjectsConstants::COL_S_DT])->toDateString(),
                'due_date'    => Carbon::parse($request->due_date)->toDateString(),
                ActivitiesConstants::COL_DESC => $request->description,
            ]);
            ActivityLog::create([
                UsersConstants::COL_USER_ID    => $request->user()->id,
                ActivitiesConstants::COL_PJ => $projectId,
                'log_type'   => 'Create Milestone',
                'remark'     => json_encode([ActivitiesConstants::COL_TT => $m->title]),
            ]);
            return Redirect::back()->with('success', __('Milestone successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function milestoneEdit(Request $request, int|string $milestoneId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'edit milestone', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $milestone = Milestone::findOrFail($milestoneId);
        return view(DatabaseConstants::TABLE_PROJECTS . '.milestoneEdit', compact('milestone'));
    }

    public function milestoneUpdate(Request $request, int|string $milestoneId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'edit milestone', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $validator = Validator::make($request->all(), [
            ActivitiesConstants::COL_TT      => 'required',
            ActivitiesConstants::COL_TSK_STT     => 'required',
            'cost'       => 'required|numeric',
            ProjectsConstants::COL_S_DT => 'required|date',
            'due_date'   => 'required|date',
        ]);
        if ($validator->fails()) {
            return Redirect::back()->with('error', Utility::errorFormat($validator->getMessageBag()));
        }
        try {
            $m = Milestone::findOrFail($milestoneId);
            $m->fill([
                ActivitiesConstants::COL_TT       => $request->title,
                ActivitiesConstants::COL_TSK_STT      => $request->status,
                'cost'        => $request->cost,
                ProjectsConstants::COL_PGR    => $request->progress,
                ProjectsConstants::COL_S_DT  => Carbon::parse($request[ProjectsConstants::COL_S_DT])->toDateString(),
                'due_date'    => Carbon::parse($request->due_date)->toDateString(),
                ActivitiesConstants::COL_DESC => $request->description,
            ])->save();
            return Redirect::back()->with('success', __('Milestone updated successfully.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const ML_DST = 'milestoneDestroy';
    public function milestoneDestroy(Request $request, int|string $milestoneId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'delete milestone', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        try {
            Milestone::findOrFail($milestoneId)->delete();
            return Redirect::back()->with('success', __('Milestone successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const ML_SHW = 'milestoneShow';
    public function milestoneShow(Request $request, int|string $milestoneId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'view milestone', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $milestone = Milestone::findOrFail($milestoneId);
        return view(DatabaseConstants::TABLE_PROJECTS . '.milestoneShow', compact('milestone'));
    }

    public const FT_PRJ = 'filterProjectView';
    public function filterProjectView(Request $request): JsonResponse|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, PermissionsConstants::MNG_PRJ, DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        if ($request->ajax() && $request->has('view') && $request->has('sort')) {
            $usr = $request->user();
            $userProjects = $usr->type === PermissionsConstants::CL
                ? Project::where('client_id', $usr->id)
                ->where(DatabaseConstants::TABLE_CREATOR, $usr->creatorId())
                ->pluck('id')
                ->toArray()
                : $usr->projects()->pluck(ActivitiesConstants::COL_PJ)->toArray();
            [$col, $dir] = explode('-', $request->sort);
            $query = Project::whereIn('id', $userProjects)
                ->orderBy($col, $dir);
            if ($kw = $request->keyword) {
                $query->where(function ($q) use ($kw) {
                    $q->where(ProjectsConstants::COL_NM, 'LIKE', "$kw%")
                        ->orWhereRaw("FIND_IN_SET('{$kw}', tags)");
                });
            }
            if ($status = $request->status)
                $query->whereIn(ActivitiesConstants::COL_TSK_STT, $status);
            $projects = $query->get();
            $lastTask = TaskStage::where(DatabaseConstants::TABLE_CREATOR, $usr->creatorId())
                ->orderBy(ActivitiesConstants::COL_OD, 'DESC')
                ->first();
            $html = view(
                DataBaseConstants::TABLE_PROJECTS . '.{$request->view}',
                compact(DatabaseConstants::TABLE_PROJECTS, 'userProjects', 'lastTask')
            )
                ->render();
            return response()->json(['success' => true, 'html' => $html]);
        }
        return null;
    }

    public function gantt(Request $request, int|string $projectId, string $duration = 'Week'): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'view grant chart', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        try {
            $project = Project::findOrFail($projectId);
            $tasks = $project->tasks->map(fn($t) => [
                'id'           => 'task_' . $t->id,
                ProjectsConstants::COL_NM         => $t[ProjectsConstants::COL_NM],
                ProjectsConstants::COL_S_DT        => $t[ProjectsConstants::COL_S_DT],
                ProjectsConstants::COL_E_DT          => $t[ProjectsConstants::COL_E_DT],
                'custom_class' => $t->priority_color ?: '#ecf0f1',
                ProjectsConstants::COL_PGR     => (int)str_replace('%', '', $t->taskProgress($t)['percentage']),
                'extra'        => [
                    ProjectsConstants::COL_PRT => ucfirst(__($t->priority)),
                    'comments' => $t->comments()->count(),
                    'duration' => Utility::getDateFormated($t[ProjectsConstants::COL_S_DT])
                        . ' - ' . Utility::getDateFormated($t[ProjectsConstants::COL_E_DT]),
                ],
            ])->toArray();
            return view(DatabaseConstants::TABLE_PROJECTS . '.gantt', compact(self::ENTITY, DatabaseConstants::TABLE_TASKS, 'duration'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const GT_PT = 'ganttPost';
    public function ganttPost(Request $request, int|string $projectId): RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$user?->can('view project task'))
            return response()->json(['is_success' => false, 'message' => __("You can't change Date!")], 400);
        try {
            $id = (int) str_replace('task_', '', $request->task_id);
            $task = ProjectTask::findOrFail($id);
            $task[ProjectsConstants::COL_S_DT] = $request->start;
            $task[ProjectsConstants::COL_E_DT]  = $request->end;
            $task->save();
            return response()->json(['is_success' => true, 'message' => __("Time Updated")], 200);
        } catch (\Throwable $e) {
            return response()->json(['is_success' => false, 'message' => __("Something is wrong.")], 400);
        }
    }

    public function bug(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can(PermissionsConstants::MNG_BUG_RPT))
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        try {
            $project = Project::findOrFail($projectId);
            if ($project->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
            }
            $user = $request->user();
            $matchedBug = Bug::where(ActivitiesConstants::COL_PJ, $projectId);
            $bugs = match ($user?->type) {
                PermissionsConstants::CPN => $matchedBug->get(),
                PermissionsConstants::CL  => $matchedBug->get(),
                default   => $matchedBug
                    ->whereRaw("find_in_set('{$user?->id}'," . ProjectsConstants::COL_ASGN . ")")
                    ->get()
            };
            return view(DatabaseConstants::TABLE_PROJECTS . '.bug', compact(self::ENTITY, DatabaseConstants::TABLE_BUGS));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const BUG_CRT = 'bugCreate';
    public function bugCreate(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('create bug report')) {
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        }
        $status = BugStatus::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
            ->pluck(ActivitiesConstants::COL_TT, 'id');
        $ids   = ProjectUser::where(ActivitiesConstants::COL_PJ, $projectId)
            ->pluck(UsersConstants::COL_USER_ID)->toArray();
        $users = User::whereIn('id', $ids)->pluck(UsersConstants::COL_NM, 'id');
        $priority = Bug::$priority;
        return view(DatabaseConstants::TABLE_PROJECTS . '.bugCreate', compact(ActivitiesConstants::COL_TSK_STT, 'projectId', ProjectsConstants::COL_PRT, DatabaseConstants::TABLE_USERS));
    }

    public const BUG_ST = 'bugStore';
    public function bugStore(Request $request, int|string $projectId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('create bug report')) {
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        }
        $data = Validator::make($request->all(), [
            ActivitiesConstants::COL_TT      => 'required',
            ProjectsConstants::COL_PRT   => 'required',
            ActivitiesConstants::COL_TSK_STT     => 'required',
            ProjectsConstants::COL_ASGN  => 'required',
            ProjectsConstants::COL_S_DT => 'required|date',
            'due_date'   => 'required|date'
        ])->validate();
        try {
            $bug = Bug::create([
                'bug_id'      => $this->bugNumber(),
                ActivitiesConstants::COL_PJ  => $projectId,
                ActivitiesConstants::COL_TT       => $data[ActivitiesConstants::COL_TT],
                ProjectsConstants::COL_PRT    => $data[ProjectsConstants::COL_PRT],
                ActivitiesConstants::COL_TSK_STT      => $data[ActivitiesConstants::COL_TSK_STT],
                ProjectsConstants::COL_ASGN   => $data[ProjectsConstants::COL_ASGN],
                ProjectsConstants::COL_S_DT  => Carbon::parse($data[ProjectsConstants::COL_S_DT])->toDateString(),
                'due_date'    => Carbon::parse($data['due_date'])->toDateString(),
                ActivitiesConstants::COL_DESC => $data[ActivitiesConstants::COL_DESC] ?? null,
                DatabaseConstants::TABLE_CREATOR  => $request->user()->creatorId(),
            ]);
            ActivityLog::create([
                UsersConstants::COL_USER_ID    => Auth::id(),
                ActivitiesConstants::COL_PJ => $projectId,
                'log_type'   => 'Create Bug',
                'remark'     => json_encode([ActivitiesConstants::COL_TT => $bug->title]),
            ]);
            return Redirect::route('task.bug', $projectId)
                ->with('success', __('Bug successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const BUG_EDT = 'bugEdit';
    public function bugEdit(Request $request, int|string $projectId, int|string $bugId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('edit bug report')) {
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        }
        $bug = Bug::findOrFail($bugId);
        $status = BugStatus::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
            ->pluck(ActivitiesConstants::COL_TT, 'id');
        $ids   = ProjectUser::where(ActivitiesConstants::COL_PJ, $projectId)
            ->pluck(UsersConstants::COL_USER_ID)->toArray();
        $users = User::whereIn('id', $ids)->pluck(UsersConstants::COL_NM, 'id');
        $priority = Bug::$priority;
        return view(DatabaseConstants::TABLE_PROJECTS . '.bugEdit', compact(ActivitiesConstants::COL_TSK_STT, 'projectId', ProjectsConstants::COL_PRT, DatabaseConstants::TABLE_USERS, 'bug'));
    }

    public const BUG_UPD = 'bugUpdate';
    public function bugUpdate(Request $request, int|string $projectId, int|string $bugId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('edit bug report')) {
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        }
        $data = Validator::make($request->all(), [
            ActivitiesConstants::COL_TT      => 'required',
            ProjectsConstants::COL_PRT   => 'required',
            ActivitiesConstants::COL_TSK_STT     => 'required',
            ProjectsConstants::COL_ASGN  => 'required',
            ProjectsConstants::COL_S_DT => 'required|date',
            'due_date'   => 'required|date'
        ])->validate();
        try {
            $bug = Bug::findOrFail($bugId);
            $bug->fill([
                ActivitiesConstants::COL_TT       => $data[ActivitiesConstants::COL_TT],
                ProjectsConstants::COL_PRT    => $data[ProjectsConstants::COL_PRT],
                ActivitiesConstants::COL_TSK_STT      => $data[ActivitiesConstants::COL_TSK_STT],
                ProjectsConstants::COL_ASGN   => $data[ProjectsConstants::COL_ASGN],
                ProjectsConstants::COL_S_DT  => Carbon::parse($data[ProjectsConstants::COL_S_DT])->toDateString(),
                'due_date'    => Carbon::parse($data['due_date'])->toDateString(),
                ActivitiesConstants::COL_DESC => $data[ActivitiesConstants::COL_DESC] ?? null,
            ])->save();
            return Redirect::route('task.bug', $projectId)
                ->with('success', __('Bug successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const BUG_DST = 'bugDestroy';
    public function bugDestroy(Request $request, int|string $projectId, int|string $bugId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('delete bug report')) {
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        }
        try {
            Bug::findOrFail($bugId)->delete();
            return Redirect::route('task.bug', $projectId)
                ->with('success', __('Bug successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const BUG_KB = 'bugKanban';
    public function bugKanban(Request $request, int|string $projectId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('move bug report'))
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        try {
            $project = Project::findOrFail($projectId);
            if ($project->created_by !== $request->user()->creatorId())
                return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
            $bugStatus = BugStatus::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->orderBy(ActivitiesConstants::COL_OD, 'ASC')
                ->get();
            return view(DatabaseConstants::TABLE_PROJECTS . '.bugKanban', compact(self::ENTITY, 'bug_status'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const BUG_KB_OD = 'bugKanbanOrder';
    public function bugKanbanOrder(Request $request): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('move bug report'))
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        $post = $request->all();
        try {
            if (!empty($post['status_id'])) {
                Bug::findOrFail($post['bug_id'])->update([ActivitiesConstants::COL_TSK_STT => $post['status_id']]);
            }
            foreach ($post[ActivitiesConstants::COL_OD] as $key => $item) {
                if ($item !== 'null')
                    Bug::findOrFail($item)
                        ->update([
                            ActivitiesConstants::COL_OD => $key,
                            ActivitiesConstants::COL_TSK_STT => $post['status_id']
                        ]);
            }
            return Redirect::back()->with('success', __('Order updated successfully.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public const BUG_SHW = 'bugShow';
    public function bugShow(Request $request, int|string $projectId, int|string $bugId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (!$request->user()->can('view bug report'))
            return defaultPermissionDenial($request, new \Exception, __CLASS__ . '::' . __FUNCTION__);
        $bug = Bug::findOrFail($bugId);
        return view(DatabaseConstants::TABLE_PROJECTS . '.bugShow', compact('bug'));
    }

    public const BUG_CMT_STR = 'bugCommentStore';
    public function bugCommentStore(Request $request, int $projectId, int $bugId): JsonResponse
    {
        if (!$request->user()->can('create bug report'))
            return response()->json([
                'is_success' => false,
                'message'    => __('Permission denied.')
            ], 403);
        $bug = Bug::findOrFail($bugId);
        if ($bug->project_id !== $projectId)
            return response()->json([
                'is_success' => false,
                'message'    => __('Invalid project or bug.')
            ], 400);
        $data = $request->validate([
            'comment' => 'required|string'
        ]);
        $comment = BugComment::create([
            'bug_id'     => $bugId,
            'comment'    => $data['comment'],
            DatabaseConstants::TABLE_CREATOR => Auth::id(),
            'user_type'  => Auth::user()->type,
        ]);

        $comment->deleteUrl = route('bug.comment.destroy', $comment->id);
        return response()->json([
            'is_success' => true,
            'message'    => __("Bug comment successfully created."),
            'data'       => $comment
        ], 200);
    }

    public const BUG_CMT_DST = 'bugCommentDestroy';
    public function bugCommentDestroy(Request $request, int|string $commentId): JsonResponse
    {
        if (!$request->user()->can('delete bug report'))
            return response()->json(['is_success' => false, 'message' => __('Permission denied.')], 403);
        BugComment::findOrFail($commentId)->delete();
        return response()->json(['is_success' => true], 200);
    }

    public const BUG_CMT_STR_F = 'bugCommentStoreFile';
    public function bugCommentStoreFile(Request $request, int|string $bugId): JsonResponse
    {
        if (!$request->user()->can('create bug report')) {
            return response()->json(['is_success' => false, 'message' => __('Permission denied.')], 403);
        }
        $request->validate(['file' => 'required|file']);
        $file = $request->file('file');
        $name = $bugId . time() . '_' . $file->getClientOriginalName();
        $file->storeAs(DatabaseConstants::TABLE_BUGS, $name);
        $bf = BugFile::create([
            'bug_id'     => $bugId,
            'file'       => $name,
            ProjectsConstants::COL_NM       => $file->getClientOriginalName(),
            'extension'  => '.' . $file->getClientOriginalExtension(),
            'file_size'  => round($file->getSize() / 1024 / 1024, 2) . ' MB',
            DatabaseConstants::TABLE_CREATOR => Auth::id(),
            'user_type'  => Auth::user()->type,
        ]);
        $bf->deleteUrl = route('bug.comment.file.destroy', $bf->id);
        return response()->json($bf, 200);
    }

    public const BUG_CMT_DST_F = 'bugCommentDestroyFile';
    public function bugCommentDestroyFile(Request $request, int|string $fileId): JsonResponse
    {
        if (!$request->user()->can('delete bug report'))
            return response()->json(['is_success' => false, 'message' => __('Permission denied.')], 403);
        $bf = BugFile::findOrFail($fileId);
        $path = storage_path('bugs/' . $bf->file);
        if (File::exists($path)) File::delete($path);
        $bf->delete();
        return response()->json(['is_success' => true], 200);
    }

    public function tracker(Request $request, int $projectId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'view project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $trackers = TimeTracker::where(ActivitiesConstants::COL_PJ, $projectId)->get();
        return view('time_trackers.index', compact('trackers'));
    }

    public const GET_PRJ_CHT = 'getProjectChart';
    public function getProjectChart(array $params): array
    {
        $dates = [];
        if (($params['duration'] ?? '') === 'week')
            foreach (Utility::getFirstSeventhWeekDay(-1)['datePeriod'] as $date)
                $dates[$date->format('Y-m-d')] = $date->format('D');
        $stages = TaskStage::where(
            DatabaseConstants::TABLE_CREATOR,
            $params[DatabaseConstants::TABLE_CREATOR]
        )
            ->orderBy(ActivitiesConstants::COL_OD)
            ->pluck(ProjectsConstants::COL_NM, 'id')
            ->toArray();
        $result = ['label' => [], 'color' => [], 'stages' => $stages];
        foreach ($dates as $date => $label) {
            $query = ProjectTask::select(ProjectsConstants::COL_STAGE_ID, DB::raw('count(*) as total'))
                ->whereDate('updated_at', $date);
            if (!empty($params[ActivitiesConstants::COL_PJ]))
                $query->where(ActivitiesConstants::COL_PJ, $params[ActivitiesConstants::COL_PJ]);
            $data = $query->groupBy(ProjectsConstants::COL_STAGE_ID)->pluck('total', ProjectsConstants::COL_STAGE_ID)->all();
            foreach ($stages as $id => $_)
                $result[$id][] = $data[$id] ?? 0;
            $result['label'][] = __($label);
        }
        return $result;
    }

    public const CP_PRJ = 'copyProject';
    public function copyProject(Request $request, int $projectId): View|RedirectResponse|JsonResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'create project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $project = Project::findOrFail($projectId);
        return view(DatabaseConstants::TABLE_PROJECTS . '.copy', compact(self::ENTITY));
    }

    public const CP_PRJ_ST = 'copyProjectStore';
    public function copyProjectStore(Request $request, int $projectId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'create project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
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
                + [DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId()];
            $new = Project::create($dupData);
            $ids = array_merge(
                $request->user ?? [],
                [$request->user()->id]
            );
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
                            $nc = $c->replicate(['comment', UsersConstants::COL_USER_ID, 'user_type', DatabaseConstants::TABLE_CREATOR]);
                            $nc->task_id = $clone->id;
                            $nc->save();
                        }
                    if (in_array('task_files', $request->task))
                        foreach ($task->files as $f) {
                            $nf = $f->replicate(['ile', ProjectsConstants::COL_NM, 'extension', 'file_size', DatabaseConstants::TABLE_CREATOR, 'user_type']);
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
                            $nc = $c->replicate(['comment', 'user_type', DatabaseConstants::TABLE_CREATOR]);
                            $nc->bug_id = $clone->id;
                            $nc->save();
                        }
                    }
                    if (in_array('bug_files', $request->bug)) {
                        foreach ($bug->files as $f) {
                            $nf = $f->replicate(['file', ProjectsConstants::COL_NM, 'extension', 'file_size', 'user_type', DatabaseConstants::TABLE_CREATOR]);
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
        return Redirect::back()->with('success', __('Project duplicated successfully.'));
    }

    public const CP_LNK_ST_CRT = 'copyLinkSettingCreate';
    public function copyLinkSettingCreate(Request $request, int $projectId): View|RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'view project', DatabaseConstants::TABLE_PROJECTS . '.index')) instanceof RedirectResponse) return $g;
        $project = Project::join('project_users', DatabaseConstants::TABLE_PROJECTS . '.id', '=', 'project_users.project_id')
            ->where('project_users.user_id', Auth::id())
            ->where(DatabaseConstants::TABLE_PROJECTS . '.id', $projectId)
            ->firstOrFail();
        $settings = json_decode($project->copylinksetting, true) ?? [];
        return view(DatabaseConstants::TABLE_PROJECTS . '.copylink_setting', compact(self::ENTITY, 'projectId', 'settings'));
    }

    public const CP_LNK_ST = 'copyLinkSetting';
    public function copyLinkSetting(Request $request, int $projectId): RedirectResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if (($g = self::guard($request, 'edit project', DatabaseConstants::TABLE_PROJECTS
            . '.index')) instanceof RedirectResponse) return $g;
        $project = Project::join('project_users', DatabaseConstants::TABLE_PROJECTS . '.id', '=', 'project_users.project_id')
            ->where('project_users.user_id', Auth::id())
            ->where(DatabaseConstants::TABLE_PROJECTS . '.id', $projectId)
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
        foreach ($fields as $f)
            $data[$f] = $request->has($f) ? 'on' : 'off';
        if ($data['password_protected'] === 'on')
            $project->password = base64_encode($request->password);
        else
            $project->password = null;
        $project->copylinksetting = json_encode($data);
        $project->save();
        return Redirect::back()->with('success', __('Copy Link Setting saved.'));
    }

    public const PRJ_LNK = 'projectLink';
    public function projectLink(Request $request, string $encrypted, string $lang = DatabaseConstants::DEFAULT_LANG): View|RedirectResponse|null
    {
        try {
            $id = Crypt::decrypt($encrypted);
        } catch (\Throwable $e) {
            return Redirect::back()->with('error', __('Project not found.'));
        }
        $project = Project::findOrFail($id);
        $settings = json_decode($project->copylinksetting, true) ?? [];
        App::setLocale($lang ?: (Auth::user()->lang ?? env('DEFAULT_ADMIN_LANG')));
        if (
            ($settings['password_protected'] ?? '') === 'on'
            && $request->password !== base64_decode($project->password)
            && session("copy_pass_true{$id}") !== "{$project->password}-{$id}"
        )
            return view(
                DatabaseConstants::TABLE_PROJECTS . '.copylink_password',
                compact('id')
            );
        session(["copy_pass_true{$id}" => "{$project->password}-{$id}"]);
        $usr = Auth::user() ?: User::find($project->created_by);
        $totalTasks    = $project->tasks->count();
        $doneTasks     = $project->tasks->where(ProjectsConstants::COL_IS_CP, 1)->count();
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
        $totalUsers = User::where(DatabaseConstants::TABLE_CREATOR, $usr->id)->count();
        $project_data['user_assigned'] = [
            'total'      => number_format($totalUsers) . '/' . number_format($totalUsers),
            'percentage' => Utility::getPercentage($totalUsers, $totalUsers),
        ];
        $totalDays    = Carbon::parse($project[ProjectsConstants::COL_S_DT])
            ->diffInDays(Carbon::parse($project[ProjectsConstants::COL_E_DT]));
        $remainingDays = Carbon::parse($project[ProjectsConstants::COL_S_DT])
            ->diffInDays(now());
        $project_data['day_left'] = [
            'day'        => number_format($remainingDays) . '/' . number_format($totalDays),
            'percentage' => Utility::getPercentage($remainingDays, $totalDays),
        ];
        $openQuery = ProjectTask::where(ActivitiesConstants::COL_PJ, $id)
            ->where(ProjectsConstants::COL_IS_CP, 0);
        if ($usr->checkProject($id) !== 'Owner')
            $openQuery->whereRaw("find_in_set('{$usr->id}'," . ProjectsConstants::COL_ASGN . ")");
        $openCount = $openQuery->count();
        $totalCount = $project->tasks->count();
        $project_data['open_task'] = [
            DatabaseConstants::TABLE_TASKS      => number_format($openCount) . '/' . number_format($totalCount),
            'percentage' => Utility::getPercentage($openCount, $totalCount),
        ];
        $totalMs = $project->milestones()->count();
        $doneMs = $project->milestones()->where(ActivitiesConstants::COL_TSK_STT, ProjectsConstants::STT_CPT_K)
            ->count();
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
            : $project->timesheets()->where(DatabaseConstants::TABLE_CREATOR, $usr->id);
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
        foreach (array_keys($sevenDays) as $date) {
            $taskCnt = $project->tasks()
                ->where(ProjectsConstants::COL_IS_CP, 1)
                ->where(ProjectsConstants::COL_M_AT, 'LIKE', $date)
                ->when(
                    $usr->checkProject($id) !== 'Owner',
                    fn($q) =>
                    $q->whereRaw("find_in_set('{$usr->id}'," . ProjectsConstants::COL_ASGN . ")")
                )->count();
            $tsArr = $project->timesheets()
                ->when(
                    $usr->checkProject($id) !== 'Owner',
                    fn($q) =>
                    $q->where(DatabaseConstants::TABLE_CREATOR, $usr->id)
                )->where('date', 'LIKE', $date)
                ->pluck('time')->toArray();
            $tsCnt = str_replace(':', '.', $tsArr ? Utility::timeToHr($tsArr) : 0);
            $chartTask[] = $taskCnt;
            $sumTask += $taskCnt;
            $chartTs[] = $tsCnt;
            $sumTs   += $tsCnt;
        }
        $project_data['task_chart']     = ['chart' => $chartTask, 'total' => $sumTask];
        $project_data['timesheet_chart'] = ['chart' => $chartTs, 'total' => $sumTs];

        $stages = TaskStage::orderBy(ActivitiesConstants::COL_OD)
            ->where(DatabaseConstants::TABLE_CREATOR, $project->created_by)
            ->get()
            ->map(function ($s) use ($usr, $id) {
                $tasks = ProjectTask::where(ActivitiesConstants::COL_PJ, $id)
                    ->when(
                        $usr->checkProject($id) !== 'Owner',
                        fn($q) =>
                        $q->whereRaw("find_in_set('{$usr->id}'," .
                            ProjectsConstants::COL_ASGN . ")")
                    )->where(ProjectsConstants::COL_STAGE_ID, $s->id)
                    ->orderBy(ActivitiesConstants::COL_OD)
                    ->get();
                return [
                    'id' => $s->id,
                    ProjectsConstants::COL_NM => $s[ProjectsConstants::COL_NM],
                    DatabaseConstants::TABLE_TASKS => $tasks
                ];
            });
        $trackers = TimeTracker::where(ActivitiesConstants::COL_PJ, $id)
            ->when(Auth::check(), fn($q) => $q->where(DatabaseConstants::TABLE_CREATOR, Auth::id()))
            ->get();
        $bugs = Bug::where(ActivitiesConstants::COL_PJ, $id)->get();
        $tasks = ProjectTask::where(ActivitiesConstants::COL_PJ, $id)->get();
        return view(DatabaseConstants::TABLE_PROJECTS . '.copylink', compact(
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
    }

    public const BUG_NB = 'bugNumber';
    private function bugNumber(): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $max = Bug::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->max('bug_id');
        return ($max ?: 0) + 1;
    }
}
