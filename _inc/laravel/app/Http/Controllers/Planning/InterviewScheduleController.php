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
    Employee,
    InterviewSchedule,
    JobApplication,
    JobStage,
    User,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Log, Validator, View as ViewFacade};
use Illuminate\View\View;

class InterviewScheduleController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    private const ENTITY = VW::ITV_SCD;

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::ITV_SCD . '.' . $fn;
        $routeShow = $cls . '::show';

        return $this->measureProfile($action, function () use ($request, $view, $action, $routeShow) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            try {
                $transDate = now()->format('Y-m-d');
                $schedules = InterviewSchedule::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $arrSchedule = $schedules->map(fn($schedule) => [
                    'id'        => $schedule->id,
                    'title'     => $schedule->applications->jobs->title ?? '',
                    'start'     => $schedule->date,
                    'className' => 'event-primary',
                    'url'       => route($routeShow, $schedule->id),
                ])->toJson();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                return view($view, compact('arrSchedule', DatabaseConstants::TABLE_SCHEDULES, 'transDate'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed to list interview schedules: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function create(Request $request, string|int $candidate = ''): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::ITV_SCD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $candidate, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::CR_ITV_SCHD, self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;
            try {
                $user = $userOrRedirect;
                $employees = User::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where(UsersConstants::COL_TP, class_basename(strtolower(Employee::class)))
                    ->orWhere('id', $user?->creatorId())
                    ->pluck(UsersConstants::COL_NM, 'id')
                    ->prepend('--', '');
                $candidates = JobApplication::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id')
                    ->prepend('--', '');
                $settings = Utility::settings();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                return view($view, compact(DatabaseConstants::TABLE_EMPLOYEES, 'candidates', 'candidate', DatabaseConstants::TABLE_SETTINGS));
            } catch (\Throwable $e) {
                Log::error($action . ' failed to prepare create form: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::CR_ITV_SCHD, self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;
            try {
                $validator = Validator::make($request->all(), [
                    'candidate' => 'required',
                    class_basename(strtolower(Employee::class)) => 'required',
                    'date'      => 'required',
                    'time'      => 'required',
                ]);
                if ($validator->fails()) {
                    return defaultUndefinedException($request, new \Exception($validator->errors()->first()), $action, route(self::ENTITY . '.create'));
                }
                $user = $userOrRedirect;
                $data = $validator->validated();
                $createData = [];
                foreach (['candidate', class_basename(strtolower(Employee::class)), 'date', 'time'] as $field) {
                    $createData[$field] = $data[$field];
                }
                $createData['comment'] = $request->comment ?? '';
                $createData[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
                $schedule = InterviewSchedule::create($createData);
                $request->input('synchronizeType') === 'googleCalendar' ? Utility::addCalendarData($schedule, 'interview_schedule') : null;
                return redirect()->back()->with('success', __('Interview schedule successfully created.'));
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed to store interview schedule: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function show(Request $request, InterviewSchedule $interviewSchedule): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::ITV_SCD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'view interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;
            try {
                $user = $userOrRedirect;
                $stages = JobStage::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                return view($view, compact('interviewSchedule', 'stages'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed to show interview schedule: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function edit(Request $request, InterviewSchedule $interviewSchedule): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::ITV_SCD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;
            try {
                $user = $userOrRedirect;
                $employees = User::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where(UsersConstants::COL_TP, class_basename(strtolower(Employee::class)))
                    ->orWhere('id', $user?->creatorId())
                    ->pluck(UsersConstants::COL_NM, 'id')
                    ->prepend('--', '');
                $candidates = JobApplication::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id')
                    ->prepend('--', '');
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                return view($view, compact(DatabaseConstants::TABLE_EMPLOYEES, 'candidates', 'interviewSchedule'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed to prepare edit form: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function update(Request $request, InterviewSchedule $interviewSchedule): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;
            try {
                $validator = Validator::make($request->all(), [
                    'candidate' => 'required',
                    class_basename(strtolower(Employee::class)) => 'required',
                    'date'      => 'required',
                    'time'      => 'required',
                ]);
                if ($validator->fails()) {
                    return defaultUndefinedException($request, new \Exception($validator->errors()->first()), $action, route(self::ENTITY . '.edit', $interviewSchedule->id));
                }
                $data = $validator->validated();
                $updateData = [];
                foreach (['candidate', class_basename(strtolower(Employee::class)), 'date', 'time'] as $field) {
                    $updateData[$field] = $data[$field];
                }
                $updateData['comment'] = $request->comment ?? '';
                $interviewSchedule->update($updateData);
                return redirect()->back()->with('success', __('Interview schedule successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed to update interview schedule: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function destroy(Request $request, InterviewSchedule $interviewSchedule): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $interviewSchedule, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'delete interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;
            try {
                $interviewSchedule->delete();
                return redirect()->back()->with('success', __('Interview schedule successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed to delete interview schedule: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const GET_ITV_D = 'getInterviewData';
    public function getInterviewData(Request $request): array|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $routeShow = $cls . '::show';

        return $this->measureProfile($action, function () use ($request, $action, $routeShow) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'view interview schedule', self::ENTITY . '.index')) instanceof RedirectResponse) return $redirect;
            try {
                $user = $userOrRedirect;
                return $request->input('calendarType') === 'googleCalendar'
                    ? Utility::getCalendarData('interview_schedule')
                    : InterviewSchedule::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->get()
                    ->map(fn($val) => [
                        'id'        => $val->id,
                        'title'     => $val->comment,
                        'start'     => $val->date . ' ' . $val->time,
                        'className' => 'event-primary',
                        'textColor' => '#51459d',
                        'url'       => route($routeShow, $val->id),
                        'allDay'    => false,
                    ])
                    ->toArray();
            } catch (\Throwable $e) {
                Log::error($action . ' failed to fetch interview data: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }
}
