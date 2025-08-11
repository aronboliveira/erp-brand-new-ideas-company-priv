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

class TimesheetController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const INDEX_ROUTE = ViewsConstants::PRJ . '.timesheets.index';

    /**
     * Show timesheet page for a project
     */
    public const TMS_VW = 'timesheetView';
    public function timesheetView(Request $request, string $projectId): mixed
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info(__METHOD__ . ' called', ['user' => $user?->id, 'project' => $projectId]);
        if ($deny = $this->guard($request, PermissionsConstants::MNG_TS, self::INDEX_ROUTE))
            return $deny;
        if (!in_array($projectId, $user?->projects()->pluck('project_id')->toArray())) {
            Log::warning('Unauthorized project access', ['user' => $user?->id, 'project' => $projectId]);
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $project = Project::findOrFail($projectId);
        return view(ViewsConstants::PRJ . '.timesheets.index', compact('project'));
    }

    public function view(Request $request, string $projectId): mixed
    {
        return $this->timesheetView($request, $projectId);
    }

    /**
     * Return HTML snippet for a single task over a week
     */
    public const APD_TMS_TSK = 'appendTimesheetTaskHtml';
    public function appendTimesheetTaskHtml(Request $request): JsonResponse
    {
        Log::info(__METHOD__, $request->only(['project_id', 'task_id', 'selected_dates']));
        $html = '';

        $task = ProjectTask::find($request->task_id);
        if ($task && $request->selected_dates) {
            [$start, $end] = explode(' - ', $request->selected_dates);
            $period        = CarbonPeriod::create($start, $end);
            $html          .= "<tr><td>{$task->name}</td>";
            foreach ($period as $date) {
                $d = $date->format('Y-m-d');
                $url = route('timesheet.create', $request->project_id);
                $html .= <<<HTML
                <td><input class="task-time" data-ajax-timesheet-popup="true" data-type="create"
                data-task-id="{$task->id}" data-date="{$d}" data-url="{$url}" value="00:00"></td>
                HTML;
            }
            $html .= '<td><input class="total-task-time" value="00:00" disabled></td>';
        }

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Show create form
     */
    public const TMS_CRT = 'timesheetCreate';
    public function timesheetCreate(Request $request, string $projectId): mixed
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($deny = $this->guard($request, 'create timesheet', self::INDEX_ROUTE))
            return $deny;
        Log::info(__METHOD__, ['user' => $user?->id, 'project' => $projectId]);

        $project = $user?->projects()->findOrFail($projectId);
        $totalTime = Timesheet::where('task_id', $request->task_id)
            ->where('created_by', $user?->id)
            ->pluck('time')->toArray();
        $total  = Utility::calculateTimesheetHours($totalTime);
        [$h, $m] = explode(':', $total);

        return view(ViewsConstants::PRJ . '.timesheets.create', [
            'project'        => $project,
            'parseArray'     => [
                'project_id'    => $project->id,
                'project_name'  => $project[ProjectsConstants::COL_NM],
                'task_id'       => $request->task_id,
                'task_name'     => ProjectTask::findOrFail($request->task_id)->name,
                'date'          => $request->date,
                'totaltaskhour' => $h,
                'totaltaskminute' => $m,
            ]
        ]);
    }

    public function create(Request $request, string $projectId): mixed
    {
        return $this->timesheetCreate($request, $projectId);
    }

    /**
     * Persist a new timesheet
     */
    public const TMS_STR = 'timesheetStore';
    public function timesheetStore(Request $request): RedirectResponse
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($deny = $this->guard($request, 'create timesheet', self::INDEX_ROUTE))
            return $deny;
        Log::info(__METHOD__, $request->all());

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id'    => 'required|exists:project_tasks,id',
            'date'       => 'required|date',
            'time_hour'  => 'required|integer|min:0',
            'time_minute' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $user) {
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
                Log::info('Timesheet stored', ['user' => $user?->id, 'task' => $validated['task_id']]);
            });
            return redirect()->back()->with('success', __('Timesheet Created Successfully!'));
        } catch (\Throwable $e) {
            Log::error('Timesheet store failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::store');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->timesheetStore($request);
    }

    /**
     * Show edit form
     */
    public const TMS_ED = 'timesheetEdit';
    public function timesheetEdit(Request $request, string $projectId, string $timesheetId): mixed
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($deny = $this->guard($request, 'edit timesheet', self::INDEX_ROUTE))
            return $deny;
        Log::info(__METHOD__, ['timesheet' => $timesheetId]);

        $timesheet = Timesheet::findOrFail($timesheetId);
        $project  = $user?->projects()->findOrFail($projectId);

        [$h, $m] = explode(':', $timesheet->time);
        $totalTime = Timesheet::where('task_id', $timesheet->task_id)
            ->where('created_by', $user?->id)
            ->pluck('time')->toArray();
        $total   = Utility::calculateTimesheetHours($totalTime);
        [$th, $tm] = explode(':', $total);

        return view(ViewsConstants::PRJ . '.timesheets.edit', [
            'timesheet'   => $timesheet,
            'parseArray'  => [
                'project_id'     => $project->id,
                'project_name'   => $project[ProjectsConstants::COL_NM],
                'task_id'        => $timesheet->task_id,
                'task_name'      => $timesheet->task->name,
                'time_hour'      => $h,
                'time_minute'    => $m,
                'totaltaskhour'  => $th,
                'totaltaskminute' => $tm,
            ]
        ]);
    }

    public function edit(Request $request, string $projectId, string $timesheetId): mixed
    {
        return $this->edit($request, $projectId, $timesheetId);
    }

    /**
     * Persist an updated timesheet
     */
    public const TMS_UPD = 'timesheetUpdate';
    public function timesheetUpdate(Request $request, string $timesheetId): RedirectResponse
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($deny = $this->guard($request, 'edit timesheet', self::INDEX_ROUTE))
            return $deny;
        Log::info(__METHOD__, ['timesheet' => $timesheetId] + $request->all());

        $v = $request->validate([
            'date'        => 'required|date',
            'time_hour'   => 'required|integer|min:0',
            'time_minute' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($v, $timesheetId, $user) {
                $t = Timesheet::findOrFail($timesheetId);
                $h = str_pad($v['time_hour'], 2, '0', STR_PAD_LEFT);
                $m = str_pad($v['time_minute'], 2, '0', STR_PAD_LEFT);
                $t->update([
                    'date'        => $v['date'],
                    'time'        => "$h:$m",
                    'description' => request('description'),
                ]);
                Log::info('Timesheet updated', ['id' => $t->id]);
            });
            return redirect()->back()->with('success', __('Timesheet Updated Successfully!'));
        } catch (\Throwable $e) {
            Log::error('Timesheet update failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::update');
        }
    }

    public function update(Request $request, string $timesheetId): RedirectResponse
    {
        return $this->timesheetUpdate($request, $timesheetId);
    }

    /**
     * Delete a timesheet
     */
    public const TMS_DST = 'timesheetDestroy';
    public function timesheetDestroy(Request $request, string $timesheetId): RedirectResponse
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($deny = $this->guard($request, 'delete timesheet', self::INDEX_ROUTE))
            return $deny;
        Log::info(__METHOD__, ['timesheet' => $timesheetId]);

        try {
            DB::transaction(function () use ($timesheetId) {
                Timesheet::findOrFail($timesheetId)->delete();
                Log::info('Timesheet deleted', ['id' => $timesheetId]);
            });
            return redirect()->back()->with('success', __('Timesheet deleted Successfully!'));
        } catch (\Throwable $e) {
            Log::error('Timesheet delete failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::destroy');
        }
    }

    public function destroy(Request $request, string $timesheetId): RedirectResponse
    {
        return $this->timesheetDestroy($request, $timesheetId);
    }

    public const FT_TMS_TBL = 'filterTimesheetTable';
    public function filterTimesheetTableView(Request $request): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $redirect = self::guard(
                $request,
                PermissionsConstants::MNG_TS,
                self::INDEX_ROUTE
            )
        ) return $redirect;

        try {
            if (!$request->has('week') || !$request->has('project_id')) {
                return defaultUndefinedException(
                    $request,
                    new \Exception('Missing parameters'),
                    $action
                );
            }
            $projectId = $request->input('project_id');
            $project  = Project::findOrFail($projectId);
            $authUser = $user; // authenticated user
            $week     = $request->input('week');
            $timesheetType = 'task';
            $projectIds = $authUser->type === 'client'
                ? Project::where('client_id', $user?->id)
                ->pluck('id')->toArray()
                : $authUser->projects()
                ->pluck('project_id')->toArray();

            $qb = Timesheet::select('timesheets.*')
                ->join('projects', ViewsConstants::PRJ . '.id', '=', 'timesheets.project_id');
            $qb = $timesheetType === 'task'
                ? $qb->join(
                    'project_tasks',
                    'project_tasks.id',
                    '=',
                    'timesheets.task_id'
                )
                : $qb;
            $qb = $projectId === '0'
                ? $qb->whereIn(ViewsConstants::PRJ . '.id', $projectIds)
                : (in_array($projectId, $projectIds)
                    ? $qb->where('timesheets.project_id', $projectId)
                    : $qb);

            $days      = Utility::getFirstSeventhWeekDay($week);
            $firstDay  = $days['first_day'];
            $seventhDay = $days['seventh_day'];
            $oneWeekDate  = $firstDay->format('M d') . ' - '
                . $seventhDay->format('M d, Y');
            $selectedDate = $firstDay->format('Y-m-d') . ' - '
                . $seventhDay->format('Y-m-d');
            $qb = $qb
                ->whereDate('date', '>=', $firstDay->format('Y-m-d'))
                ->whereDate('date', '<=', $seventhDay->format('Y-m-d'));

            $grouped = $projectId === '0'
                ? $qb->get()->groupBy(['project_id', 'task_id'])->toArray()
                : (in_array($projectId, $projectIds)
                    ? $qb->get()->groupBy('task_id')->toArray()
                    : []);

            $sectionTasks = [];
            if ($projectId !== '0' && in_array($projectId, $projectIds)) {
                $taskIds = array_keys($grouped);
                $proj    = Project::findOrFail($projectId);
                $sections = ProjectTask::getAllSectionedTaskList(
                    $request,
                    $proj,
                    [],
                    $taskIds
                );
                foreach ($sections as $section) {
                    $tasks = [];
                    foreach ($section['sections'] as $t)
                        $tasks[] = [
                            'taskId'   => $t['id'],
                            'taskName' => $t['taskinfo']['task_name']
                        ];
                    $sectionTasks[] = [
                        'sectionId'   => $section['section_id'],
                        'sectionName' => $section['section_name'],
                        'tasks'       => $tasks
                    ];
                }
            }
            $html = Project::getProjectAssignedTimesheetHTML(
                $qb,
                $grouped,
                $days,
                $projectId
            );
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
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException($request, $e, $action);
        }
    }

    public function timesheetList(Request $request): mixed
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $redirect = $this->guard(
                $request,
                PermissionsConstants::MNG_TS,
                self::INDEX_ROUTE
            )
        ) return $redirect;
        return view(ViewsConstants::PRJ . '.timesheet_list');
    }

    public function timesheetListGet(Request $request): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $redirect = self::guard(
                $request,
                PermissionsConstants::MNG_TS,
                self::INDEX_ROUTE
            )
        ) return $redirect;

        try {
            if (!$request->has('week') || !$request->has('project_id')) {
                return defaultUndefinedException(
                    $request,
                    new \Exception('Missing parameters'),
                    $action
                );
            }
            $projectId = $request->input('project_id');
            $week     = $request->input('week');
            $projectIds = $user?->projects()
                ->pluck('project_id')->toArray();

            $qb = Timesheet::select('timesheets.*')
                ->join(
                    'projects',
                    ViewsConstants::PRJ . '.id',
                    '=',
                    'timesheets.project_id'
                )
                ->join(
                    'project_tasks',
                    'project_tasks.id',
                    '=',
                    'timesheets.task_id'
                );
            $qb = $projectId === '0'
                ? $qb->whereIn(ViewsConstants::PRJ . '.id', $projectIds)
                : (in_array($projectId, $projectIds)
                    ? $qb->where('timesheets.project_id', $projectId)
                    : $qb);

            $days      = Utility::getFirstSeventhWeekDay($week);
            $firstDay  = $days['first_day'];
            $seventhDay = $days['seventh_day'];
            $oneWeekDate = $firstDay->format('M d') . ' - '
                . $seventhDay->format('M d, Y');
            $selectedDate = $firstDay->format('Y-m-d') . ' - '
                . $seventhDay->format('Y-m-d');
            $qb = $qb
                ->whereDate('date', '>=', $firstDay->format('Y-m-d'))
                ->whereDate('date', '<=', $seventhDay->format('Y-m-d'));

            $grouped = $projectId === '0'
                ? $qb->get()
                ->groupBy(['project_id', 'task_id'])
                ->toArray()
                : (in_array($projectId, $projectIds)
                    ? $qb->get()
                    ->groupBy('task_id')
                    ->toArray()
                    : []);

            $sectionTasks = [];
            if ($projectId !== '0' && in_array($projectId, $projectIds)) {
                $taskIds = array_keys($grouped);
                $proj    = Project::findOrFail($projectId);
                $sections = ProjectTask::getAllSectionedTaskList(
                    $request,
                    $proj,
                    [],
                    $taskIds
                );
                foreach ($sections as $section) {
                    $tasks = [];
                    foreach ($section['sections'] as $t) {
                        $tasks[] = [
                            'taskId'   => $t['id'],
                            'taskName' => $t['taskinfo']['task_name']
                        ];
                    }
                    $sectionTasks[] = [
                        'sectionId'   => $section['section_id'],
                        'sectionName' => $section['section_name'],
                        'tasks'       => $tasks
                    ];
                }
            }

            $html = Project::getProjectAssignedTimesheetHTML(
                $qb,
                $grouped,
                $days,
                $projectId
            );
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
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException($request, $e, $action);
        }
    }
}
