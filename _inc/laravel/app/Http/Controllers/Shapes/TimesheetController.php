<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    SettingsConstants,
    ViewsConstants
};
use App\Models\{Project, ProjectTask, Timesheet, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Carbon\CarbonPeriod;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\HasCrudConstants;
class TimesheetController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const INDEX_ROUTE = ViewsConstants::PRJ . '.' . ViewsConstants::TMS . '.index';

    /** Show timesheet page for a project */
    public const TMS_VW = 'timesheetView';
    public function timesheetView(Request $request, string $projectId): mixed
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request, $projectId) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) {
                return $user;
            }

            Log::info($scope . ' called', ['user' => $user?->id, 'project' => $projectId]);

            if (($deny = $this->guard($request, PermissionsConstants::MNG_TS, self::INDEX_ROUTE)) !== true) {
                return $deny;
            }

            if (!in_array($projectId, $user?->projects()->pluck('project_id')->toArray(), true)) {
                Log::warning($scope . ' unauthorized project access', ['user' => $user?->id, 'project' => $projectId]);
                return redirect()->back()->with('error', __('Permission Denied.'));
            }

            $project = Project::findOrFail($projectId);
            return view(ViewsConstants::PRJ . '.timesheets.index', compact('project'));
        });
    }

    public function view(Request $request, string $projectId): mixed
    {
        // simple proxy; measured inside timesheetView()
        return $this->timesheetView($request, $projectId);
    }

    /** Return HTML snippet for a single task over a week */
    public const APD_TMS_TSK = 'appendTimesheetTaskHtml';
    public function appendTimesheetTaskHtml(Request $request): JsonResponse
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request) {
            Log::info($scope, $request->only(['project_id', 'task_id', 'selected_dates']));

            $html = '';
            $task = ProjectTask::find($request->task_id);

            if ($task && $request->selected_dates) {
                [$start, $end] = explode(' - ', $request->selected_dates);
                $period = CarbonPeriod::create($start, $end);

                $html .= "<tr><td>" . e($task->name) . "</td>";
                foreach ($period as $date) {
                    $d   = $date->format('Y-m-d');
                    $url = route(ViewsConstants::PRJ . '.' . ViewsConstants::TMS . '.create', $request->project_id);
                    $html .= <<<HTML
                    <td><input class="task-time" data-ajax-timesheet-popup="true" data-type="create"
                    data-task-id="{$task->id}" data-date="{$d}" data-url="{$url}" value="00:00"></td>
                    HTML;
                }
                $html .= '<td><input class="total-task-time" value="00:00" disabled></td>';
            }

            return response()->json(['success' => true, 'html' => $html]);
        });
    }

    /** Show create form */
    public const TMS_CRT = 'timesheetCreate';
    public function timesheetCreate(Request $request, string $projectId): mixed
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request, $projectId) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) {
                return $user;
            }
            if (($deny = $this->guard($request, 'create timesheet', self::INDEX_ROUTE)) !== true) {
                return $deny;
            }
            Log::info($scope, ['user' => $user?->id, 'project' => $projectId]);

            $project   = $user?->projects()->findOrFail($projectId);
            $totalTime = Timesheet::where('task_id', $request->task_id)
                ->where('created_by', $user?->id)
                ->pluck('time')
                ->toArray();

            $total = Utility::calculateTimesheetHours($totalTime);
            [$h, $m] = explode(':', $total);

            return view(ViewsConstants::PRJ . '.timesheets.create', [
                'project'    => $project,
                'parseArray' => [
                    'project_id'       => $project->id,
                    'project_name'     => $project[ProjectsConstants::COL_NM],
                    'task_id'          => $request->task_id,
                    'task_name'        => ProjectTask::findOrFail($request->task_id)->name,
                    'date'             => $request->date,
                    'totaltaskhour'    => $h,
                    'totaltaskminute'  => $m,
                ]
            ]);
        });
    }

    public function create(Request $request, string $projectId): mixed
    {
        return $this->timesheetCreate($request, $projectId);
    }

    /** Persist a new timesheet */
    public const TMS_STR = 'timesheetStore';
    public function timesheetStore(Request $request): RedirectResponse
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) {
                return $user;
            }
            if (($deny = $this->guard($request, 'create timesheet', self::INDEX_ROUTE)) !== true) {
                return $deny;
            }
            Log::info($scope, $request->all());

            $validated = $request->validate([
                'project_id'  => 'required|exists:projects,id',
                'task_id'     => 'required|exists:project_tasks,id',
                'date'        => 'required|date',
                'time_hour'   => 'required|integer|min:0',
                'time_minute' => 'required|integer|min:0',
            ]);

            try {
                DB::transaction(function () use ($validated, $user, $scope) {
                    $h = str_pad($validated['time_hour'], 2, '0', STR_PAD_LEFT);
                    $m = str_pad($validated['time_minute'], 2, '0', STR_PAD_LEFT);

                    Timesheet::create([
                        'project_id'  => $validated['project_id'],
                        'task_id'     => $validated['task_id'],
                        'date'        => $validated['date'],
                        'time'        => "$h:$m",
                        'description' => request('description'),
                        'created_by'  => $user?->id,
                    ]);

                    Log::info($scope . ' stored', ['user' => $user?->id, 'task' => $validated['task_id']]);
                });

                return redirect()->back()->with('success', __('Timesheet Created Successfully!'));
            } catch (\Throwable $e) {
                Log::error($scope . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $scope);
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->timesheetStore($request);
    }

    /** Show edit form */
    public const TMS_ED = 'timesheetEdit';
    public function timesheetEdit(Request $request, string $projectId, string $timesheetId): mixed
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request, $projectId, $timesheetId) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) {
                return $user;
            }
            if (($deny = $this->guard($request, 'edit timesheet', self::INDEX_ROUTE)) !== true) {
                return $deny;
            }
            Log::info($scope, ['timesheet' => $timesheetId]);

            $timesheet = Timesheet::findOrFail($timesheetId);
            $project   = $user?->projects()->findOrFail($projectId);

            [$h, $m]   = explode(':', $timesheet->time);
            $totalTime = Timesheet::where('task_id', $timesheet->task_id)
                ->where('created_by', $user?->id)
                ->pluck('time')
                ->toArray();

            $total     = Utility::calculateTimesheetHours($totalTime);
            [$th, $tm] = explode(':', $total);

            return view(ViewsConstants::PRJ . '.timesheets.edit', [
                'timesheet'  => $timesheet,
                'parseArray' => [
                    'project_id'      => $project->id,
                    'project_name'    => $project[ProjectsConstants::COL_NM],
                    'task_id'         => $timesheet->task_id,
                    'task_name'       => $timesheet->task->name,
                    'time_hour'       => $h,
                    'time_minute'     => $m,
                    'totaltaskhour'   => $th,
                    'totaltaskminute' => $tm,
                ]
            ]);
        });
    }

    public function edit(Request $request, string $projectId, string $timesheetId): mixed
    {
        // Avoid recursion; delegate to the profiled method.
        return $this->timesheetEdit($request, $projectId, $timesheetId);
    }

    /** Persist an updated timesheet */
    public const TMS_UPD = 'timesheetUpdate';
    public function timesheetUpdate(Request $request, string $timesheetId): RedirectResponse
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request, $timesheetId) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) {
                return $user;
            }
            if (($deny = $this->guard($request, 'edit timesheet', self::INDEX_ROUTE)) !== true) {
                return $deny;
            }
            Log::info($scope, ['timesheet' => $timesheetId] + $request->all());

            $v = $request->validate([
                'date'        => 'required|date',
                'time_hour'   => 'required|integer|min:0',
                'time_minute' => 'required|integer|min:0',
            ]);

            try {
                DB::transaction(function () use ($v, $timesheetId, $scope) {
                    $t = Timesheet::findOrFail($timesheetId);
                    $h = str_pad($v['time_hour'], 2, '0', STR_PAD_LEFT);
                    $m = str_pad($v['time_minute'], 2, '0', STR_PAD_LEFT);

                    $t->update([
                        'date'        => $v['date'],
                        'time'        => "$h:$m",
                        'description' => request('description'),
                    ]);

                    Log::info($scope . ' updated', ['id' => $t->id]);
                });

                return redirect()->back()->with('success', __('Timesheet Updated Successfully!'));
            } catch (\Throwable $e) {
                Log::error($scope . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $scope);
            }
        });
    }

    public function update(Request $request, string $timesheetId): RedirectResponse
    {
        return $this->timesheetUpdate($request, $timesheetId);
    }

    /** Delete a timesheet */
    public const TMS_DST = 'timesheetDestroy';
    public function timesheetDestroy(Request $request, string $timesheetId): RedirectResponse
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request, $timesheetId) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) {
                return $user;
            }
            if (($deny = $this->guard($request, 'delete timesheet', self::INDEX_ROUTE)) !== true) {
                return $deny;
            }
            Log::info($scope, ['timesheet' => $timesheetId]);

            try {
                DB::transaction(function () use ($timesheetId, $scope) {
                    Timesheet::findOrFail($timesheetId)->delete();
                    Log::info($scope . ' deleted', ['id' => $timesheetId]);
                });

                return redirect()->back()->with('success', __('Timesheet deleted Successfully!'));
            } catch (\Throwable $e) {
                Log::error($scope . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $scope);
            }
        });
    }

    public function destroy(Request $request, string $timesheetId): RedirectResponse
    {
        return $this->timesheetDestroy($request, $timesheetId);
    }

    public const FT_TMS_TBL = 'filterTimesheetTableView';
    public function filterTimesheetTableView(Request $request): JsonResponse|RedirectResponse|null
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return $userOrRedirect;
            }
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, PermissionsConstants::MNG_TS, self::INDEX_ROUTE)) !== true) {
                return $redirect;
            }

            try {
                if (!$request->has('week') || !$request->has('project_id')) {
                    return defaultUndefinedException($request, new \Exception('Missing parameters'), $scope);
                }

                $projectId    = $request->input('project_id');
                $project      = Project::findOrFail($projectId);
                $authUser     = $user;
                $week         = $request->input('week');
                $timesheetType = 'task';

                $projectIds = $authUser->type === 'client'
                    ? Project::where('client_id', $user?->id)->pluck('id')->toArray()
                    : $authUser->projects()->pluck('project_id')->toArray();

                $qb = Timesheet::select('timesheets.*')
                    ->join('projects', ViewsConstants::PRJ . '.id', '=', 'timesheets.project_id');

                if ($timesheetType === 'task') {
                    $qb->join('project_tasks', 'project_tasks.id', '=', 'timesheets.task_id');
                }

                $qb = $projectId === '0'
                    ? $qb->whereIn(ViewsConstants::PRJ . '.id', $projectIds)
                    : (in_array($projectId, $projectIds, true)
                        ? $qb->where('timesheets.project_id', $projectId)
                        : $qb);

                $days       = Utility::getFirstSeventhWeekDay($week);
                $firstDay   = $days['first_day'];
                $seventhDay = $days['seventh_day'];

                $oneWeekDate  = $firstDay->format('M d') . ' - ' . $seventhDay->format('M d, Y');
                $selectedDate = $firstDay->format('Y-m-d') . ' - ' . $seventhDay->format('Y-m-d');

                $qb->whereDate('date', '>=', $firstDay->format('Y-m-d'))
                    ->whereDate('date', '<=', $seventhDay->format('Y-m-d'));

                $grouped = $projectId === '0'
                    ? $qb->get()->groupBy(['project_id', 'task_id'])->toArray()
                    : (in_array($projectId, $projectIds, true)
                        ? $qb->get()->groupBy('task_id')->toArray()
                        : []);

                $sectionTasks = [];
                if ($projectId !== '0' && in_array($projectId, $projectIds, true)) {
                    $taskIds  = array_keys($grouped);
                    $proj     = Project::findOrFail($projectId);
                    $sections = ProjectTask::getAllSectionedTaskList($request, $proj, [], $taskIds);

                    foreach ($sections as $section) {
                        $tasks = [];
                        foreach ($section['sections'] as $t) {
                            $tasks[] = ['taskId' => $t['id'], 'taskName' => $t['taskinfo']['task_name']];
                        }
                        $sectionTasks[] = [
                            'sectionId'   => $section['section_id'],
                            'sectionName' => $section['section_name'],
                            'tasks'       => $tasks
                        ];
                    }
                }

                $html         = Project::getProjectAssignedTimesheetHTML($qb, $grouped, $days, $projectId);
                $totalRecords = count($grouped);

                return response()->json([
                    'success'       => true,
                    'totalrecords'  => $totalRecords,
                    'selectedDate'  => $selectedDate,
                    'sectiontasks'  => $sectionTasks,
                    'onewWeekDate'  => $oneWeekDate,
                    'html'          => $html,
                ]);
            } catch (\Throwable $e) {
                Log::error($scope . ' failed', ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($scope . ' failed', [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $scope);
            }
        });
    }

    public const TMS_LST = 'timesheetList';
    public function timesheetList(Request $request): mixed
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile(__METHOD__, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return $userOrRedirect;
            }
            if (($redirect = $this->guard($request, PermissionsConstants::MNG_TS, self::INDEX_ROUTE)) !== true) {
                return $redirect;
            }
            return view(ViewsConstants::PRJ . '.timesheet_list');
        });
    }

    public const GET_TMS_LST = 'timesheetListGet';
    public function timesheetListGet(Request $request): JsonResponse|RedirectResponse|null
    {
        $scope = static::class . '::' . __FUNCTION__;

        return $this->measureProfile($scope, function () use ($scope, $request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return $userOrRedirect;
            }
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, PermissionsConstants::MNG_TS, self::INDEX_ROUTE)) !== true) {
                return $redirect;
            }

            try {
                if (!$request->has('week') || !$request->has('project_id')) {
                    return defaultUndefinedException($request, new \Exception('Missing parameters'), $scope);
                }

                $projectId  = $request->input('project_id');
                $week       = $request->input('week');
                $projectIds = $user?->projects()->pluck('project_id')->toArray();

                $qb = Timesheet::select('timesheets.*')
                    ->join('projects', ViewsConstants::PRJ . '.id', '=', 'timesheets.project_id')
                    ->join('project_tasks', 'project_tasks.id', '=', 'timesheets.task_id');

                $qb = $projectId === '0'
                    ? $qb->whereIn(ViewsConstants::PRJ . '.id', $projectIds)
                    : (in_array($projectId, $projectIds, true)
                        ? $qb->where('timesheets.project_id', $projectId)
                        : $qb);

                $days        = Utility::getFirstSeventhWeekDay($week);
                $firstDay    = $days['first_day'];
                $seventhDay  = $days['seventh_day'];
                $oneWeekDate = $firstDay->format('M d') . ' - ' . $seventhDay->format('M d, Y');
                $selectedDate = $firstDay->format('Y-m-d') . ' - ' . $seventhDay->format('Y-m-d');

                $qb->whereDate('date', '>=', $firstDay->format('Y-m-d'))
                    ->whereDate('date', '<=', $seventhDay->format('Y-m-d'));

                $grouped = $projectId === '0'
                    ? $qb->get()->groupBy(['project_id', 'task_id'])->toArray()
                    : (in_array($projectId, $projectIds, true)
                        ? $qb->get()->groupBy('task_id')->toArray()
                        : []);

                $sectionTasks = [];
                if ($projectId !== '0' && in_array($projectId, $projectIds, true)) {
                    $taskIds  = array_keys($grouped);
                    $proj     = Project::findOrFail($projectId);
                    $sections = ProjectTask::getAllSectionedTaskList($request, $proj, [], $taskIds);

                    foreach ($sections as $section) {
                        $tasks = [];
                        foreach ($section['sections'] as $t) {
                            $tasks[] = ['taskId' => $t['id'], 'taskName' => $t['taskinfo']['task_name']];
                        }
                        $sectionTasks[] = [
                            'sectionId'   => $section['section_id'],
                            'sectionName' => $section['section_name'],
                            'tasks'       => $tasks
                        ];
                    }
                }

                $html         = Project::getProjectAssignedTimesheetHTML($qb, $grouped, $days, $projectId);
                $totalRecords = count($grouped);

                return response()->json([
                    'success'       => true,
                    'totalrecords'  => $totalRecords,
                    'selectedDate'  => $selectedDate,
                    'sectiontasks'  => $sectionTasks,
                    'onewWeekDate'  => $oneWeekDate,
                    'html'          => $html,
                ]);
            } catch (\Throwable $e) {
                Log::error($scope . ' failed', ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($scope . ' failed', [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $scope);
            }
        });
    }
}
