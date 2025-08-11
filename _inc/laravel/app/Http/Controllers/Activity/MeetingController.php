<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  CompaniesConstants,
  DatabaseConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants,
};
use App\Models\{
  Branch,
  Department,
  Employee,
  Meeting,
  MeetingEmployee,
  Utility
};
use App\Traits\ChecksLogin;
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request,
  Response
};
use Illuminate\Support\Facades\{
  Auth,
  Log,
  Validator
};

class MeetingController extends Controller
{
  use ChecksLogin;

  public function index(
    Request $request
  ): Response|RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, PermissionsConstants::MNG_MT)) return $resp;
    try {
      $employees = Employee::all();
      $meetings = strtolower(Auth::user()?->{UsersConstants::COL_TP}) === 'employee'
        ? $this->_employeeMeetings()
        : Meeting::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get();
      return response()->view(
        ViewsConstants::MT . '.index',
        compact('meetings', 'employees')
      );
    } catch (\Throwable $e) {
      return self::_catch($request, $e);
    }
  }

  public function create(
    Request $request
  ): Response|RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, 'create meeting')) return $resp;
    try {
      $settings = Utility::settings();
      [$branches, $departments, $employees] = strtolower(Auth::user()[UsersConstants::COL_TP]) === 'employee'
        ? [collect(), collect(), Employee::where(
          DatabaseConstants::TABLE_CREATOR,
          $request->user()->creatorId()
        )->where(
          UsersConstants::COL_USER_ID,
          '!=',
          $request->user()->id
        )->pluck(UsersConstants::COL_NM, 'id')]
        : [
          Branch::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get(),
          Department::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get(),
          Employee::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
            ->pluck(UsersConstants::COL_NM, 'id')
        ];
      return response()->view(
        ViewsConstants::MT . '.' . __FUNCTION__,
        compact('employees', 'departments', 'branches', 'settings')
      );
    } catch (\Throwable $e) {
      return self::_catch($request, $e);
    }
  }

  public function store(
    Request $request
  ): RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, 'create meeting')) return $resp;
    if ($resp = self::_validate($request->all(), [
      CompaniesConstants::COL_BRC_ID => 'required',
      UsersConstants::COL_EMP_ID => 'required',
      CompaniesConstants::COL_DEP_ID => 'required',
      'title' => 'required',
      'date' => 'required',
      'time' => 'required'
    ])) return $resp;
    try {
      $meeting = Meeting::create([
        CompaniesConstants::COL_BRC_ID => $request->branch_id,
        CompaniesConstants::COL_DEP_ID => json_encode($request->department_id),
        UsersConstants::COL_EMP_ID => json_encode($request->employee_id),
        'title' => $request->title,
        'date' => $request->date,
        'time' => $request->time,
        'note' => $request->note,
        DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId()
      ]);
      $deptEmployees = in_array('0', $request->employee_id, true)
        ? Employee::whereIn(CompaniesConstants::COL_DEP_ID, $request->department_id)
        ->pluck('id')
        : collect($request->employee_id);
      $deptEmployees->each(static function ($emp) use ($meeting, $request) {
        MeetingEmployee::create([
          'meeting_id' => $meeting->id,
          UsersConstants::COL_EMP_ID => $emp,
          DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId()
        ]);
      });
      $this->_notify($request, $meeting);
      return redirect()->route(ViewsConstants::MT . '.index')
        ->with('success', 'Meeting successfully created.');
    } catch (\Throwable $e) {
      return self::_catch($request, $e);
    }
  }

  public function show(Request $request, Meeting $meeting): Response|RedirectResponse|JsonResponse|null
  {
    Log::info(__METHOD__, [
      UsersConstants::COL_USER_ID    => Auth::id(),
      'meeting_id' => $meeting->id
    ]);
    if ($resp = self::_authorize($request, 'view meeting')) return $resp;
    try {
      return response()->view(ViewsConstants::MT . '.show', compact('meeting'));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
      return self::_catch($request, $e);
    }
  }

  public function edit(
    Request $request,
    int     $meeting
  ): Response|RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, 'edit meeting')) return $resp;
    try {
      $meeting = Meeting::findOrFail($meeting);
      if ($meeting->created_by !== $request->user()->creatorId())
        return defaultPermissionDenial(
          $request,
          new \Exception('permission denied'),
          __CLASS__ . '::' . __FUNCTION__
        );
      $employees = strtolower(Auth::user()[UsersConstants::COL_TP]) === 'employee'
        ? Employee::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->where(UsersConstants::COL_USER_ID, '!=', $request->user()->id)
        ->pluck(UsersConstants::COL_NM, 'id')
        : Employee::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->pluck(UsersConstants::COL_NM, 'id');
      return response()->view(ViewsConstants::MT . '.' . __FUNCTION__, compact('meeting', 'employees'));
    } catch (\Throwable $e) {
      return self::_catch($request, $e);
    }
  }

  public function update(
    Request $request,
    Meeting $meeting
  ): RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, 'edit meeting')) return $resp;
    if ($resp = self::_validate($request->all(), [
      'title' => 'required',
      'date' => 'required',
      'time' => 'required'
    ])) return $resp;
    try {
      if ($meeting->created_by !== $request->user()->creatorId())
        return defaultPermissionDenial(
          $request,
          new \Exception('permission denied'),
          __CLASS__ . '::' . __FUNCTION__
        );
      $meeting->update($request->only(['title', 'date', 'time', 'note']));
      return redirect()->route(ViewsConstants::MT . '.index')
        ->with('success', 'Meeting successfully updated.');
    } catch (\Throwable $e) {
      return self::_catch($request, $e);
    }
  }

  public function destroy(
    Request $request,
    Meeting $meeting
  ): RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, 'delete meeting')) return $resp;
    try {
      if ($meeting->created_by !== $request->user()->creatorId())
        return defaultPermissionDenial(
          $request,
          new \Exception('permission denied'),
          __CLASS__ . '::' . __FUNCTION__
        );
      $meeting->delete();
      return redirect()->route(ViewsConstants::MT . '.index')
        ->with('success', 'Meeting successfully deleted.');
    } catch (\Throwable $e) {
      return self::_catch($request, $e);
    }
  }

  public function getDepartment(
    Request $request
  ): JsonResponse {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    $deps = $request->branch_id == 0
      ? Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
      : Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
      ->where(CompaniesConstants::COL_BRC_ID, $request->branch_id);
    return response()->json($deps->pluck(UsersConstants::COL_NM, 'id')->toArray());
  }

  public function getEmployee(
    Request $request
  ): JsonResponse {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    $emps = in_array('0', $request->department_id, true)
      ? Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
      : Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
      ->whereIn(CompaniesConstants::COL_DEP_ID, $request->department_id);
    return response()->json($emps->pluck(UsersConstants::COL_NM, 'id')->toArray());
  }

  public function calendar(
    Request $request
  ): Response|RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, PermissionsConstants::MNG_MT)) return $resp;
    try {
      $meetings = Meeting::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->when(
          $request->start_date,
          static fn ($q, $v) => $q->where('date', '>=', $v)
        )
        ->when(
          $request->end_date,
          static fn ($q, $v) => $q->where('date', '<=', $v)
        )
        ->get();
      $arrMeetings = $meetings->map(static function ($m) {
        return [
          'id' => $m->id,
          'title' => $m->title,
          'start' => $m->date,
          'time' => $m->time,
          'className' => 'event-primary',
          'url' => route(ViewsConstants::MT . '.edit', $m->id)
        ];
      });
      return response()->view(ViewsConstants::MT . '.calendar', [
        'arrMeetings' => $arrMeetings->toJson(),
        'transdate' => now()->toDateString(),
        'meetings' => $meetings
      ]);
    } catch (\Throwable $e) {
      return self::_catch($request, $e);
    }
  }

  public function getMeetingData(
    Request $request
  ): JsonResponse|array {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    return $request->get('calendar_type') === 'goggle_calendar'
      ? Utility::getCalendarData('meeting')
      : Meeting::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
      ->get()
      ->map(static function ($m) {
        return [
          'id' => $m->id,
          'title' => $m->title,
          'start' => $m->date . ' ' . $m->time,
          'className' => 'event-primary',
          'textColor' => '#51459d',
          'url' => route(ViewsConstants::MT . '.edit', $m->id),
          'allDay' => false
        ];
      })->toArray();
  }

  private function _employeeMeetings()
  {
    $current = Employee::where(UsersConstants::COL_USER_ID, Auth::id())->first();
    return Meeting::orderByDesc('meetings.id')
      ->leftJoin(
        'meeting_employees',
        'meetings.id',
        '=',
        'meeting_employees.meeting_id'
      )
      ->where('meeting_employees.employee_id', $current->id)
      ->orWhere(static function ($q) {
        $q->where('meetings.department_id', '["0"]')
          ->where('meetings.employee_id', '["0"]');
      })->get();
  }

  private function _notify(Request $request, Meeting $meeting): void
  {
    $setting = Utility::settings($request->user()->creatorId());
    $branch = Branch::find($meeting->branch_id);
    $payload = [
      'meeting_title' => $meeting->title,
      'branch_name' => $branch?->name,
      'meeting_date' => $meeting->date,
      'meeting_time' => $meeting->time
    ];
    ($setting['support_notification'] ?? 0) &&
      Utility::sendSlackMsg('new_meeting', $payload);
    ($setting['telegram_meeting_notification'] ?? 0) &&
      Utility::sendTelegramMsg('new_meeting', $payload);
    if ($request->get('synchronize_type') === 'google_calendar') {
      $m = new Meeting([
        'title' => $meeting->title,
        'start_date' => $meeting->date,
        'end_date' => $meeting->date
      ]);
      Utility::addCalendarData($m, 'meeting');
    }
    if ($wh = Utility::webhookSetting('New Meeting')) {
      $ok = Utility::webhookCall($wh['url'], json_encode($payload), $wh['method']);
      $ok ?: Log::warning('Meeting webhook failed');
    }
  }

  private static function _authorize(
    Request $request,
    string  $perm
  ): RedirectResponse|JsonResponse|null {
    return $request->user()->can($perm)
      ? null
      : defaultPermissionDenial(
        $request,
        new \Exception('permission denied'),
        __CLASS__ . '::' . __FUNCTION__
      );
  }

  private static function _validate(
    array  $data,
    array  $rules
  ): RedirectResponse|JsonResponse|null {
    $v = Validator::make($data, $rules);
    return $v->fails()
      ? redirect()->back()->with('error', $v->getMessageBag()->first())
      : null;
  }

  private static function _catch(
    Request   $request,
    \Throwable $e
  ): RedirectResponse|JsonResponse|null {
    return defaultUndefinedException(
      $request,
      $e,
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? ''
    );
  }
}

// ! ALERT: _notify lacks retry logic and fails silently if Slack/Telegram APIs throw; consider wrapping each call in try/catch with exponential back‑off.