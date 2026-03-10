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
  Route,
  Validator,
  View as ViewFacade
};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class MeetingController extends Controller
{
  use ChecksLogin;

  public function index(Request $request): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::MT . '.index';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['user_type' => Auth::user()?->{UsersConstants::COL_TP}, 'creator_id' => $req->user()?->creatorId()]);
      if ($resp = self::_authorize($req, PermissionsConstants::MNG_MT)) return $resp;
      try {
        $empStart = microtime(true);
        $employees = Employee::all();
        $this->logExecutionTime($empStart, $action, 'fetchEmployees');
        $type = strtolower(Auth::user()?->{UsersConstants::COL_TP});
        $mtStart = microtime(true);
        $meetings = $type === 'employee' ? $this->_employeeMeetings() : Meeting::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->get();
        $this->logExecutionTime($mtStart, $action, 'fetchMeetings');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] complete", ['employees' => is_countable($employees) ? count($employees) : null, 'meetings' => is_countable($meetings) ? count($meetings) : null]);
        return response()->view($viewPath, compact('meetings', 'employees'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'user_type' => Auth::user()?->{UsersConstants::COL_TP}, 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::MT . '.' . $action;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['user_type' => Auth::user()?->{UsersConstants::COL_TP}, 'creator_id' => $req->user()?->creatorId()]);
      if ($resp = self::_authorize($req, 'create meeting')) return $resp;
      try {
        $settingsStart = microtime(true);
        $settings = Utility::settings();
        $this->logExecutionTime($settingsStart, $action, 'loadSettings');
        $type = strtolower(Auth::user()[UsersConstants::COL_TP]);
        if ($type === 'employee') {
          $empStart = microtime(true);
          $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->where(UsersConstants::COL_USER_ID, '!=', $req->user()->id)->pluck(UsersConstants::COL_NM, 'id');
          $this->logExecutionTime($empStart, $action, 'fetchEmployees');
          $branches = collect();
          $departments = collect();
        } else {
          $brStart = microtime(true);
          $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->get();
          $this->logExecutionTime($brStart, $action, 'fetchBranches');
          $depStart = microtime(true);
          $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->get();
          $this->logExecutionTime($depStart, $action, 'fetchDepartments');
          $empStart = microtime(true);
          $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->pluck(UsersConstants::COL_NM, 'id');
          $this->logExecutionTime($empStart, $action, 'fetchEmployees');
        }
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] complete", ['branches' => is_countable($branches) ? count($branches) : null, 'departments' => is_countable($departments) ? count($departments) : null, 'employees' => is_countable($employees) ? count($employees) : null]);
        return response()->view($viewPath, compact('employees', 'departments', 'branches', 'settings'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'user_type' => Auth::user()?->{UsersConstants::COL_TP}, 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $request): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all())]);
      if ($resp = self::_authorize($req, 'create meeting')) return $resp;
      if ($resp = self::_validate($req->all(), [CompaniesConstants::COL_BRC_ID => 'required', UsersConstants::COL_EMP_ID => 'required', CompaniesConstants::COL_DEP_ID => 'required', 'title' => 'required', 'date' => 'required', 'time' => 'required'])) return $resp;
      try {
        $createStart = microtime(true);
        $meeting = Meeting::create([CompaniesConstants::COL_BRC_ID => $req->branch_id, CompaniesConstants::COL_DEP_ID => json_encode($req->department_id), UsersConstants::COL_EMP_ID => json_encode($req->employee_id), 'title' => $req->title, 'date' => $req->date, 'time' => $req->time, 'note' => $req->note, DatabaseConstants::COL_TABLE_CREATOR => $req->user()->creatorId()]);
        $this->logExecutionTime($createStart, $action, 'createMeeting');
        $resolveStart = microtime(true);
        $deptEmployees = in_array('0', $req->employee_id, true) ? Employee::whereIn(CompaniesConstants::COL_DEP_ID, $req->department_id)->pluck('id') : collect($req->employee_id);
        $this->logExecutionTime($resolveStart, $action, 'resolveEmployees');
        $attachStart = microtime(true);
        $deptEmployees->each(static function ($emp) use ($meeting, $req) {
          MeetingEmployee::create(['meeting_id' => $meeting->id, UsersConstants::COL_EMP_ID => $emp, DatabaseConstants::COL_TABLE_CREATOR => $req->user()->creatorId()]);
        });
        $this->logExecutionTime($attachStart, $action, 'attachEmployees');
        $notifyStart = microtime(true);
        $this->_notify($req, $meeting);
        $this->logExecutionTime($notifyStart, $action, 'notifyEmployees');
        Log::info("[{$class}::{$action}] success", ['meeting_id' => $meeting->id, 'employees_count' => is_countable($deptEmployees) ? count($deptEmployees) : null]);
        return redirect()->route(ViewsConstants::MT . '.index')->with('success', 'Meeting successfully created.');
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $request, Meeting $meeting): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::MT . '.show';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $meeting, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}]", [UsersConstants::COL_USER_ID => Auth::id(), 'meeting_id' => $meeting->id]);
      if ($resp = self::_authorize($req, 'view meeting')) return $resp;
      try {
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, compact('meeting'));
        $this->logExecutionTime($renderStart, $action, 'renderMeetingShow');
        Log::info("[{$class}::{$action}] complete", ['meeting_id' => $meeting->id]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), UsersConstants::COL_USER_ID => Auth::id(), 'meeting_id' => $meeting->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'meeting_id' => $meeting->id]);
  }

  public function edit(Request $request, int $meeting): Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::MT . '.' . $action;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $meeting, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['creator_id' => $req->user()?->creatorId(), 'meeting_param' => $meeting]);
      if ($resp = self::_authorize($req, 'edit meeting')) return $resp;
      try {
        $findStart = microtime(true);
        $meeting = Meeting::findOrFail($meeting);
        $this->logExecutionTime($findStart, $action, 'findMeeting');
        if ($meeting->created_by !== $req->user()->creatorId()) return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
        $type = strtolower(Auth::user()[UsersConstants::COL_TP]);
        $empStart = microtime(true);
        $employees = $type === 'employee' ? Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->where(UsersConstants::COL_USER_ID, '!=', $req->user()->id)->pluck(UsersConstants::COL_NM, 'id') : Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->pluck(UsersConstants::COL_NM, 'id');
        $this->logExecutionTime($empStart, $action, 'fetchEmployees');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = response()->view($viewPath, compact('meeting', 'employees'));
        $this->logExecutionTime($renderStart, $action, 'renderEditView');
        Log::info("[{$class}::{$action}] complete", ['meeting_id' => $meeting->id, 'employees' => is_countable($employees) ? count($employees) : null]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'creator_id' => $req->user()?->creatorId(), 'meeting_param' => $meeting, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'meeting_param' => $meeting]);
  }

  public function update(Request $request, Meeting $meeting): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $meeting, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['meeting_id' => $meeting->id, 'creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all())]);
      if ($resp = self::_authorize($req, 'edit meeting')) return $resp;
      if ($resp = self::_validate($req->all(), ['title' => 'required', 'date' => 'required', 'time' => 'required'])) return $resp;
      try {
        if ($meeting->created_by !== $req->user()->creatorId()) return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
        $updStart = microtime(true);
        $meeting->update($req->only(['title', 'date', 'time', 'note']));
        $this->logExecutionTime($updStart, $action, 'updateMeeting');
        Log::info("[{$class}::{$action}] success", ['meeting_id' => $meeting->id, 'title' => $req->input('title')]);
        return redirect()->route(ViewsConstants::MT . '.index')->with('success', 'Meeting successfully updated.');
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'meeting_id' => $meeting->id, 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'meeting_id' => $meeting->id]);
  }

  public function destroy(Request $request, Meeting $meeting): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $meeting, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['meeting_id' => $meeting->id, 'creator_id' => $req->user()?->creatorId()]);
      if ($resp = self::_authorize($req, 'delete meeting')) return $resp;
      try {
        if ($meeting->created_by !== $req->user()->creatorId()) return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
        $delStart = microtime(true);
        $meeting->delete();
        $this->logExecutionTime($delStart, $action, 'deleteMeeting');
        Log::info("[{$class}::{$action}] success", ['meeting_id' => $meeting->id]);
        return redirect()->route(ViewsConstants::MT . '.index')->with('success', 'Meeting successfully deleted.');
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'meeting_id' => $meeting->id, 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'meeting_id' => $meeting->id]);
  }

  public const GET_DPT = 'getDepartment';
  public function getDepartment(Request $request): JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      Log::info("[{$class}::{$action}] start", ['branch_id' => $req->branch_id, 'creator_id' => $user?->creatorId()]);
      try {
        $buildStart = microtime(true);
        $deps = $req->branch_id == 0 ? Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()) : Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->where(CompaniesConstants::COL_BRC_ID, $req->branch_id);
        $this->logExecutionTime($buildStart, $action, 'buildDepartmentsQuery');
        $pluckStart = microtime(true);
        $list = $deps->pluck(UsersConstants::COL_NM, 'id')->toArray();
        $this->logExecutionTime($pluckStart, $action, 'pluckDepartments');
        Log::info("[{$class}::{$action}] complete", ['count' => is_countable($list) ? count($list) : null]);
        return response()->json($list);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'branch_id' => $req->branch_id, 'creator_id' => $user?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'branch_id' => $request->branch_id]);
  }

  public const GET_EMP = 'getEmployee';
  public function getEmployee(Request $request): JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      Log::info("[{$class}::{$action}] start", ['department_ids' => $req->department_id, 'creator_id' => $user?->creatorId()]);
      try {
        $buildStart = microtime(true);
        $emps = in_array('0', $req->department_id, true) ? Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()) : Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->whereIn(CompaniesConstants::COL_DEP_ID, $req->department_id);
        $this->logExecutionTime($buildStart, $action, 'buildEmployeesQuery');
        $pluckStart = microtime(true);
        $list = $emps->pluck(UsersConstants::COL_NM, 'id')->toArray();
        $this->logExecutionTime($pluckStart, $action, 'pluckEmployees');
        Log::info("[{$class}::{$action}] complete", ['count' => is_countable($list) ? count($list) : null]);
        return response()->json($list);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'department_ids' => $req->department_id, 'creator_id' => $user?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'department_count' => is_array($request->department_id) ? count($request->department_id) : null]);
  }

  public function calendar(
    Request $request
  ): Response|RedirectResponse|JsonResponse|null {
    if ($resp = self::_authorize($request, PermissionsConstants::MNG_MT)) return $resp;
    try {
      $meetings = Meeting::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
        ->when(
          $request->start_date,
          static fn($q, $v) => $q->where('date', '>=', $v)
        )
        ->when(
          $request->end_date,
          static fn($q, $v) => $q->where('date', '<=', $v)
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

  public const GET_MT_D = 'getMeetingData';
  public function getMeetingData(Request $request): JsonResponse|RedirectResponse|array
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      Log::info("[{$class}::{$action}] start", ['calendar_type' => $req->get('calendar_type'), 'creator_id' => $user?->creatorId()]);
      try {
        if ($req->get('calendar_type') === 'google_calendar') {
          $gcStart = microtime(true);
          $data = Utility::getCalendarData('meeting');
          $this->logExecutionTime($gcStart, $action, 'googleCalendarData');
          Log::info("[{$class}::{$action}] complete", ['count' => is_countable($data) ? count($data) : null]);
          return $data;
        }
        $qStart = microtime(true);
        $q = Meeting::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
        $this->logExecutionTime($qStart, $action, 'buildMeetingsQuery');
        $getStart = microtime(true);
        $items = $q->get();
        $this->logExecutionTime($getStart, $action, 'getMeetings');
        $mapStart = microtime(true);
        $out = $items->map(static function ($m) {
          return ['id' => $m->id, 'title' => $m->title, 'start' => $m->date . ' ' . $m->time, 'className' => 'event-primary', 'textColor' => '#51459d', 'url' => route(ViewsConstants::MT . '.edit', $m->id), 'allDay' => false];
        })->toArray();
        $this->logExecutionTime($mapStart, $action, 'mapMeetings');
        Log::info("[{$class}::{$action}] complete", ['count' => is_countable($out) ? count($out) : null]);
        return $out;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'calendar_type' => $req->get('calendar_type'), 'creator_id' => $user?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return self::_catch($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'calendar_type' => $request->get('calendar_type')]);
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
    $setting = Utility::settingsById($request->user()->creatorId());
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