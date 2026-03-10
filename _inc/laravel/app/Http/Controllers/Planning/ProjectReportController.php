<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Models\{
    Milestone,
    Project,
    ProjectMilestone,
    ProjectStage,
    ProjectTask,
    ProjectUser,
    TaskStage,
    Timesheet,
    User,
    UserDefualtView,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{DB, Log, View as ViewFacade};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class ProjectReportController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ENTITY = 'project';
    private const SINGULAR = VW::PRJ_RPT;

    public function index(Request $request): View|string|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($r = self::guard($request, 'view project report', self::SINGULAR . '.' . $action)) !== true)
                return $r;
            try {
                $cid = $user?->creatorId() ?: $user?->id;

                $buildQStart = microtime(true);
                $projQ = Project::query();
                $projQ = match ($user[UsersConstants::COL_TP]) {
                    PermissionsConstants::CL   => $projQ->where('client_id', $user?->id),
                    PermissionsConstants::CPN  => $projQ
                        ->when(
                            $request->filled('all_users'),
                            fn($q) => $q
                                ->select(DatabaseConstants::TABLE_PROJECTS . '.*')
                                ->leftJoin(
                                    'project_users',
                                    'project_users.' . ProjectsConstants::COL_PJ_ID,
                                    DatabaseConstants::TABLE_PROJECTS . '.id'
                                )
                                ->where('project_users.' . UsersConstants::COL_USER_ID, $request->input('all_users'))
                        )
                        ->when(
                            !$request->filled('all_users'),
                            fn($q) => $q->where(DatabaseConstants::TABLE_PROJECTS . '.' . DatabaseConstants::COL_TABLE_CREATOR, $user?->id)
                        ),
                    default    => $projQ
                        ->select(DatabaseConstants::TABLE_PROJECTS . '.*')
                        ->leftJoin(
                            'project_users',
                            'project_users.' . ProjectsConstants::COL_PJ_ID,
                            DatabaseConstants::TABLE_PROJECTS . '.id'
                        )
                        ->where('project_users.' . UsersConstants::COL_USER_ID, $user?->id),
                };
                $this->logExecutionTime($buildQStart, $action, 'buildProjectQuery');

                $filtersStart = microtime(true);
                foreach ([ActivitiesConstants::COL_TSK_STT, ProjectsConstants::COL_S_DT, ProjectsConstants::COL_E_DT] as $f) {
                    if ($request->filled($f)) {
                        $projQ->where($f, $request->input($f));
                    }
                }
                $this->logExecutionTime($filtersStart, $action, 'applyFilters');

                $usersStart = microtime(true);
                $users = match ($user[UsersConstants::COL_TP]) {
                    PermissionsConstants::SA => User::where(DatabaseConstants::COL_TABLE_CREATOR, $cid)
                        ->where(UsersConstants::COL_TP, PermissionsConstants::CPN)->get(),
                    PermissionsConstants::CPN => User::where(DatabaseConstants::COL_TABLE_CREATOR, $cid)
                        ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)->get(),
                    default => [],
                };
                $this->logExecutionTime($usersStart, $action, 'fetchUsers');

                $statusStart = microtime(true);
                $statusList = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN ? Project::$project_status : [];
                $this->logExecutionTime($statusStart, $action, 'statusList');

                $fetchStart = microtime(true);
                $projects = $projQ->with(DatabaseConstants::TABLE_TASKS)->orderByDesc('id')->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchProjects');

                $lastStageStart = microtime(true);
                $lastTask = TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $cid)
                    ->orderByDesc(ActivitiesConstants::COL_OD)
                    ->first();
                $this->logExecutionTime($lastStageStart, $action, 'fetchLastTaskStage');

                $viewPath = self::SINGULAR . '.' . $action;
                $existsStart = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($existsStart, $action, 'viewExistsCheck');
                if (!$exists) {
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact(
                    DatabaseConstants::TABLE_PROJECTS,
                    DatabaseConstants::TABLE_USERS,
                    'statusList',
                    'lastTask'
                ));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function show(Request $request, string|int $id): View|string|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($r = self::guard($request, 'view project report', self::SINGULAR . '.show')) !== true)
                return $r;
            try {
                $cid = $user?->creatorId() ?: $user?->id;

                $buildQStart = microtime(true);
                $projQ = Project::query();
                $projQ = match ($user[UsersConstants::COL_TP]) {
                    PermissionsConstants::CL   => $projQ->where('client_id', $user?->id),
                    'Employee' => $projQ
                        ->select(DatabaseConstants::TABLE_PROJECTS . '.*')
                        ->leftJoin(
                            'project_users',
                            'project_users.' . ProjectsConstants::COL_PJ_ID,
                            DatabaseConstants::TABLE_PROJECTS . '.id'
                        )
                        ->where('project_users.' . UsersConstants::COL_USER_ID, $user?->id),
                    default    => $projQ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->id),
                };
                $this->logExecutionTime($buildQStart, $action, 'buildProjectQuery');

                $projectStart = microtime(true);
                $project = $projQ->where('id', $id)->firstOrFail();
                $this->logExecutionTime($projectStart, $action, 'fetchProject');

                $usersStart = microtime(true);
                $users = User::where(DatabaseConstants::COL_TABLE_CREATOR, $cid)
                    ->when(
                        $user[UsersConstants::COL_TP] === PermissionsConstants::SA,
                        fn($q) => $q->where(UsersConstants::COL_TP, PermissionsConstants::CPN),
                        fn($q) => $q->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
                    )
                    ->get();
                $this->logExecutionTime($usersStart, $action, 'fetchUsers');

                $chartStart = microtime(true);
                $chartData = $this->getProjectChart([
                    ProjectsConstants::COL_PJ_ID => $id,
                    'duration' => 'week'
                ]);
                $this->logExecutionTime($chartStart, $action, 'getProjectChart');

                $calcStart = microtime(true);
                $daysLeft = round((strtotime($project->end_date) - strtotime(date('Y-m-d'))) / 3600 / 24);
                $total = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $id)->count();
                $byStage = TaskStage::join('project_tasks', 'project_tasks.stage_id', DatabaseConstants::TABLE_TSK_STGS . '.id')
                    ->where('project_tasks.' . ProjectsConstants::COL_PJ_ID, $id)
                    ->groupBy(DatabaseConstants::TABLE_TSK_STGS . '.' . ProjectsConstants::COL_NM)
                    ->pluck('count', DatabaseConstants::TABLE_TSK_STGS . '.' . ProjectsConstants::COL_NM);
                $statusLabels = $byStage->keys()->all();
                $statusPercents = array_map(fn($c) => $total ? round($c * 100 / $total, 2) : 0.00, $byStage->all());

                $byPri = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $id)
                    ->groupBy(ProjectsConstants::COL_PRT)
                    ->pluck('count', ProjectsConstants::COL_PRT);
                $priLabels = $byPri->keys()->all();
                $priPercents = array_map(fn($c) => $total ? round($c * 100 / $total, 2) : 0.00, $byPri->all());
                $priorityClasses = ['text-success', 'text-primary', 'text-danger'];
                $stages = TaskStage::all();
                $milestones = Milestone::where(ProjectsConstants::COL_PJ_ID, $id)->get();
                $logged = Timesheet::where(ProjectsConstants::COL_PJ_ID, $id)->get()->sum(function ($ts) {
                    $h = date('H', strtotime($ts->time));
                    $m = date('i', strtotime($ts->time));
                    return $h + $m / 60;
                });
                $loggedChart = number_format($logged, 2, '.', '');
                $estimated = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $id)->sum(ProjectsConstants::COL_E_HRS);
                $tasks = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $id)->get();
                $lastTask = TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $cid)
                    ->orderByDesc(ActivitiesConstants::COL_OD)
                    ->first();
                $this->logExecutionTime($calcStart, $action, 'computeMetrics');

                $viewPath = self::SINGULAR . '.' . $action;
                $existsStart = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($existsStart, $action, 'viewExistsCheck');
                if (!$exists) {
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact(
                    'user',
                    DatabaseConstants::TABLE_USERS,
                    self::ENTITY,
                    'chartData',
                    'daysLeft',
                    'statusLabels',
                    'statusPercents',
                    'priLabels',
                    'priPercents',
                    'priorityClasses',
                    'stages',
                    'milestones',
                    'loggedChart',
                    'estimated',
                    DatabaseConstants::TABLE_TASKS,
                    'lastTask'
                ));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const GET_PRJ_CHT = 'getProjectChart';
    public function getProjectChart(array $params): array
    {
        $dates = $labels = [];
        if (($params['duration'] ?? '') === 'week') {
            foreach (Utility::getFirstSeventhWeekDay(-1)['datePeriod'] as $d) {
                $dates[] = $d->format('Y-m-d');
                $labels[] = $d->format('D');
            }
        }

        $stages = TaskStage::when(
            $params[DatabaseConstants::COL_TABLE_CREATOR] ?? null,
            fn($q) => $q->where(DatabaseConstants::COL_TABLE_CREATOR, $params[DatabaseConstants::COL_TABLE_CREATOR] ?? null)
        )
            ->orderBy(ActivitiesConstants::COL_OD)
            ->get(['id', ProjectsConstants::COL_NM]);

        $palette = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

        $datasets = [];
        foreach ($stages as $i => $stage) {
            $datasets[$stage->id] = [
                'label'           => $stage->name,
                'data'            => [],
                'backgroundColor' => $palette[$i % count($palette)]
            ];
        }

        foreach ($dates as $date) {
            $counts = ProjectTask::select('stage_id', DB::raw('count(*) as total'))
                ->whereDate(DatabaseConstants::COL_U_AT, $date)
                ->when(isset($params[ProjectsConstants::COL_PJ_ID]), fn($q) => $q->where(ProjectsConstants::COL_PJ_ID, $params[ProjectsConstants::COL_PJ_ID]))
                ->when(isset($params[DatabaseConstants::COL_TABLE_CREATOR]), fn($q) => $q->whereIn(
                    ProjectsConstants::COL_PJ_ID,
                    fn($sub) => $sub->select('id')->from(DatabaseConstants::TABLE_PROJECTS)->where(DatabaseConstants::COL_TABLE_CREATOR, $params[DatabaseConstants::COL_TABLE_CREATOR])
                ))
                ->pluck('total', 'stage_id')
                ->all();

            foreach ($datasets as $sid => &$ds) {
                $ds['data'][] = $counts[$sid] ?? 0;
            }
            unset($ds);
        }

        return ['labels' => $labels, 'datasets' => array_values($datasets)];
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        return redirect()->route(static::SINGULAR . '.index')->with('info', __('Feature not implemented.'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        return redirect()->route(static::SINGULAR . '.index')->with('info', __('Feature not implemented.'));
    }

    public function edit(Request $request, string|int $id): View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        return redirect()->route(static::SINGULAR . '.index')->with('info', __('Feature not implemented.'));
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        return redirect()->route(static::SINGULAR . '.index')->with('info', __('Feature not implemented.'));
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        return redirect()->route(static::SINGULAR . '.index')->with('info', __('Feature not implemented.'));
    }

    public function ajax_data(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function ajax_tasks_report(Request $request, string|int $id): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function export(string|int $id): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($id, $action, $method) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $request = request();
            if (($r = self::guard($request, 'export project report', self::SINGULAR . '.' . $action)) !== true)
                return $r;
            try {
                $name = 'task_report_' . date('Y-m-d_H:i:s');
                return Excel::download(new \App\Exports\TaskReportExport($id), $name . '.xlsx');
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }
}
