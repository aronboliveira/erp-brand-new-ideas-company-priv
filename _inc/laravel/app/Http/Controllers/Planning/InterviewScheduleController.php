<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\{Log, Validator};
use Illuminate\View\View;

class InterviewScheduleController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    private const ENTITY = 'interview-schedule';

    public function index(Request $request): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        $user = $userOrRedirect;
        try {
            $transDate = now()->format('Y-m-d');
            $schedules = InterviewSchedule::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            $arrSchedule = $schedules->map(
                fn ($schedule) => [
                    'id'        => $schedule->id,
                    'title'     => $schedule->applications->jobs->title ?? '',
                    'start'     => $schedule->date,
                    'className' => 'event-primary',
                    'url'       => route(__CLASS__ . '::show', $schedule->id)
                ]
            )->toJson();
            return view(
                self::ENTITY . '.' . __FUNCTION__,
                compact('arrSchedule', DatabaseConstants::TABLE_SCHEDULES, 'transDate')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to list interview schedules: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $request, string|int $candidate = ''): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::CR_ITV_SCHD,
            self::ENTITY . '.index'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $user = $userOrRedirect;
            $employees = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->where(UsersConstants::COL_TP, class_basename(strtolower(Employee::class)))
                ->orWhere('id', $user?->creatorId())
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend('--', '');
            $candidates = JobApplication::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->pluck('name', 'id')
                ->prepend('--', '');
            $settings = Utility::settings();
            return view(
                self::ENTITY . '.' . __FUNCTION__,
                compact(DatabaseConstants::TABLE_EMPLOYEES, 'candidates', 'candidate', DatabaseConstants::TABLE_SETTINGS)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to prepare create form: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $request): RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::CR_ITV_SCHD,
            self::ENTITY . '.index'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $validator = Validator::make($request->all(), [
                'candidate' => 'required',
                class_basename(strtolower(Employee::class))  => 'required',
                'date'      => 'required',
                'time'      => 'required'
            ]);
            if ($validator->fails()) {
                return defaultUndefinedException(
                    $request,
                    new \Exception($validator->errors()->first()),
                    __CLASS__ . '::' . __FUNCTION__,
                    route(self::ENTITY . '.create')
                );
            }
            $data = $validator->validated();
            $createData = [];
            foreach (['candidate', class_basename(strtolower(Employee::class)), 'date', 'time'] as $field) {
                $createData[$field] = $data[$field];
            }
            $createData['comment']   = $request->comment ?? '';
            $createData[DatabaseConstants::TABLE_CREATOR] = $user?->creatorId();
            $schedule = InterviewSchedule::create($createData);
            $request->input('synchronizeType') === 'googleCalendar'
                ? Utility::addCalendarData($schedule, 'interview_schedule')
                : null;
            return redirect()->back()
                ->with('success', __('Interview schedule successfully created.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to store interview schedule: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(Request $request, InterviewSchedule $interviewSchedule): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'view interview schedule',
            self::ENTITY . '.index'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $user  = $userOrRedirect;
            $stages = JobStage::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(
                self::ENTITY . '.' . __FUNCTION__,
                compact('interviewSchedule', 'stages')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to show interview schedule: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $request, InterviewSchedule $interviewSchedule): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit interview schedule',
            self::ENTITY . '.index'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $user = $userOrRedirect;
            $employees = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->where(UsersConstants::COL_TP, class_basename(strtolower(Employee::class)))
                ->orWhere('id', $user?->creatorId())
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend('--', '');
            $candidates = JobApplication::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->pluck('name', 'id')
                ->prepend('--', '');
            return view(
                self::ENTITY . '.' . __FUNCTION__,
                compact(DatabaseConstants::TABLE_EMPLOYEES, 'candidates', 'interviewSchedule')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to prepare edit form: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $request, InterviewSchedule $interviewSchedule): RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit interview schedule',
            self::ENTITY . '.index'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $validator = Validator::make($request->all(), [
                'candidate' => 'required',
                class_basename(strtolower(Employee::class))  => 'required',
                'date'      => 'required',
                'time'      => 'required'
            ]);
            if ($validator->fails()) {
                return defaultUndefinedException(
                    $request,
                    new \Exception($validator->errors()->first()),
                    __CLASS__ . '::' . __FUNCTION__,
                    route(self::ENTITY . '.edit', $interviewSchedule->id)
                );
            }
            $data = $validator->validated();
            $updateData = [];
            foreach ([
                'candidate', class_basename(strtolower(Employee::class)), 'date',
                'time'
            ] as $field)
                $updateData[$field] = $data[$field];
            $updateData['comment'] = $request->comment ?? '';
            $interviewSchedule->update($updateData);
            return redirect()->back()
                ->with('success', __('Interview schedule successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to update interview schedule: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $request, InterviewSchedule $interviewSchedule): RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'delete interview schedule',
            self::ENTITY . '.index'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $interviewSchedule->delete();
            return redirect()->back()
                ->with('success', __('Interview schedule successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to delete interview schedule: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function getInterviewData(Request $request): array|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'view interview schedule',
            self::ENTITY . '.index'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $user = $userOrRedirect;
            return $request->input('calendarType') === 'googleCalendar'
                ? Utility::getCalendarData('interview_schedule')
                : InterviewSchedule::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->get()
                ->map(
                    fn ($val) => [
                        'id'        => $val->id,
                        'title'     => $val->comment,
                        'start'     => $val->date . ' ' . $val->time,
                        'className' => 'event-primary',
                        'textColor' => '#51459d',
                        'url'       => route(__CLASS__ . '::show', $val->id),
                        'allDay'    => false
                    ]
                )
                ->toArray();
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed to fetch interview data: ' . $e->getMessage());
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }
}
