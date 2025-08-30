<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  CompaniesConstants,
  DatabaseConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{
  Branch,
  Department,
  Employee,
  Event,
  EventEmployee,
  Utility
};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{
  Log,
  Route,
  Validator,
  View as ViewFacade
};

class EventController extends Controller
{

  public function index(Request $request): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    $viewPath = ViewsConstants::EVT . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      try {
        $authStart = microtime(true);
        self::_setAuth($req, PermissionsConstants::MNG_EVT);
        $this->logExecutionTime($authStart, $action, 'setAuth');
        $creatorId = $req->user()->creatorId();
        Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId]);
        $empQStart = microtime(true);
        $employees = Employee::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($empQStart, $action, 'fetchEmployees');
        $evtQStart = microtime(true);
        $events = Event::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($evtQStart, $action, 'fetchEvents');
        $transDate = date('Y-m-d');
        $todayMonth = date('m');
        $cmQStart = microtime(true);
        $currentMonthEvents = Event::query()->select('id', 'start_date', 'end_date', 'title', 'created_at', 'color')->whereRaw('MONTH(start_date)=' . $todayMonth)->whereRaw('MONTH(end_date)=' . $todayMonth)->get();
        $this->logExecutionTime($cmQStart, $action, 'fetchCurrentMonthEvents');
        $mapStart = microtime(true);
        $arrEvents = $events->map(fn($e) => ['id' => $e->id, 'title' => $e->title, 'start' => $e->start_date, 'end' => $e->end_date, 'className' => $e->color, 'url' => route(ViewsConstants::EVT . '.edit', $e->id)])->toJson();
        $this->logExecutionTime($mapStart, $action, 'mapEventsToJson');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] ready", ['employees' => $employees->count(), 'events' => $events->count(), 'current_month_events' => $currentMonthEvents->count()]);
        return view($viewPath, ['arrEvents' => $arrEvents, 'employees' => $employees, 'transDate' => $transDate, 'events' => $events, 'currentMonthEvents' => $currentMonthEvents]);
      } catch (AuthorizationException $e) {
        Log::debug("[{$class}::{$action}] debug auth error", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    $viewPath = ViewsConstants::EVT . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      try {
        $authStart = microtime(true);
        self::_setAuth($req, 'create event');
        $this->logExecutionTime($authStart, $action, 'setAuth');
        $creatorId = $req->user()->creatorId();
        Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId]);
        $empStart = microtime(true);
        $employees = Employee::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck(UsersConstants::COL_NM, 'id');
        $this->logExecutionTime($empStart, $action, 'pluckEmployees');
        $brStart = microtime(true);
        $branch = Branch::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($brStart, $action, 'fetchBranches');
        $depStart = microtime(true);
        $departments = Department::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($depStart, $action, 'fetchDepartments');
        $settingsStart = microtime(true);
        $settings = Utility::settings();
        $this->logExecutionTime($settingsStart, $action, 'loadSettings');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] ready", ['employees' => count($employees ?? []), 'branches' => $branch->count(), 'departments' => $departments->count()]);
        return view($viewPath, ['employees' => $employees, 'branch' => $branch, 'departments' => $departments, 'settings' => $settings]);
      } catch (AuthorizationException $e) {
        Log::debug("[{$class}::{$action}] debug auth error", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $request): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      try {
        $authStart = microtime(true);
        self::_setAuth($req, 'create event');
        $this->logExecutionTime($authStart, $action, 'setAuth');
        $valStart = microtime(true);
        $validator = Validator::make($req->all(), [UsersConstants::COL_BRC_ID => 'required', UsersConstants::COL_DEP_ID => 'required', UsersConstants::COL_EMP_ID => 'required', 'title' => 'required', 'start_date' => 'required', 'end_date' => 'required', 'color' => 'required']);
        $this->logExecutionTime($valStart, $action, 'buildValidator');
        $failCheckStart = microtime(true);
        if ($validator->fails()) {
          $this->logExecutionTime($failCheckStart, $action, 'validatorFailsCheck');
          Log::debug("[{$class}::{$action}] validation failed", ['first_error' => $validator->errors()->first(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
          return redirect()->back()->with('error', $validator->errors()->first());
        }
        $event = new Event();
        $event->branch_id = $req->input(UsersConstants::COL_BRC_ID);
        $event->department_id = json_encode($req->input(UsersConstants::COL_DEP_ID));
        $event->employee_id = json_encode($req->input(UsersConstants::COL_EMP_ID));
        $event->title = $req->input('title');
        $event->start_date = $req->input('start_date');
        $event->end_date = $req->input('end_date');
        $event->color = $req->input('color');
        $event->description = $req->input('description');
        $event[DatabaseConstants::TABLE_CREATOR] = $req->user()->creatorId();
        $saveStart = microtime(true);
        $event->save();
        $this->logExecutionTime($saveStart, $action, 'saveEvent');
        $deptStart = microtime(true);
        $deptEmployees = in_array('0', $req->input(UsersConstants::COL_EMP_ID, [])) ? Employee::query()->whereIn(UsersConstants::COL_DEP_ID, [$req->input(UsersConstants::COL_DEP_ID)])->pluck('id') : $req->input(UsersConstants::COL_EMP_ID);
        $this->logExecutionTime($deptStart, $action, 'resolveDeptEmployees');
        $linkStart = microtime(true);
        foreach ($deptEmployees as $emp) EventEmployee::create(['event_id' => $event->id, UsersConstants::COL_EMP_ID => $emp, DatabaseConstants::TABLE_CREATOR => $req->user()->creatorId()]);
        $this->logExecutionTime($linkStart, $action, 'linkEventEmployees');
        $settingsStart = microtime(true);
        $setting = Utility::settings($req->user()->creatorId());
        $this->logExecutionTime($settingsStart, $action, 'loadSettings');
        $branchStart = microtime(true);
        $branchName = $req->input(UsersConstants::COL_BRC_ID) == 0 ? implode(',', Branch::all()->pluck('name')->toArray()) : Branch::find($req->input(UsersConstants::COL_BRC_ID))->name;
        $this->logExecutionTime($branchStart, $action, 'resolveBranchName');
        $notif = ['event_title' => $req->input('title'), CompaniesConstants::COL_BRC_NM => $branchName, 'event_start_date' => $req->input('start_date'), 'event_end_date' => $req->input('end_date')];
        isset($setting['event_notification']) && $setting['event_notification'] == 1 ? (function () use ($notif, $action) {
          $t = microtime(true);
          Utility::sendSlackMsg('new_event', $notif);
          $this->logExecutionTime($t, $action, 'sendSlack');
        })() : null;
        isset($setting['telegram_event_notification']) && $setting['telegram_event_notification'] == 1 ? (function () use ($notif, $action) {
          $t = microtime(true);
          Utility::sendTelegramMsg('new_event', $notif);
          $this->logExecutionTime($t, $action, 'sendTelegram');
        })() : null;
        $req->input('synchronize_type') === 'google_calendar' ? (function () use ($req, $action) {
          $t = microtime(true);
          Utility::addCalendarData($req, 'event');
          $this->logExecutionTime($t, $action, 'addCalendarData');
        })() : null;
        $webhook = Utility::webhookSetting('New Event');
        $webhook ? (function () use ($webhook, $event, $action) {
          $t = microtime(true);
          Utility::webhookCall($webhook['url'], json_encode($event), $webhook['method']);
          $this->logExecutionTime($t, $action, 'webhookCall');
        })() : null;
        return redirect()->route(ViewsConstants::EVT . '.index')->with('success', __('Event successfully created.'));
      } catch (AuthorizationException $e) {
        Log::debug("[{$class}::{$action}] debug auth error", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $request, Event $event): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $event, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", ['event_id' => $event->id]);
        $redirStart = microtime(true);
        $resp = redirect()->route(ViewsConstants::EVT . '.index');
        $this->logExecutionTime($redirStart, $action, 'buildRedirect');
        Log::info("[{$class}::{$action}] redirecting to index");
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['event_id' => $event->id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'event_id' => $event->id]);
  }

  public function edit(Request $request, string|int $id): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    $viewPath = ViewsConstants::EVT . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
      try {
        $authStart = microtime(true);
        self::_setAuth($req, 'edit event');
        $this->logExecutionTime($authStart, $action, 'setAuth');
        $findStart = microtime(true);
        $event = Event::findOrFail($id);
        $this->logExecutionTime($findStart, $action, 'findEvent');
        if ($event[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId()) throw new AuthorizationException;
        $empStart = microtime(true);
        $employees = Employee::query()->where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->pluck(UsersConstants::COL_NM, 'id');
        $this->logExecutionTime($empStart, $action, 'pluckEmployees');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] ready", ['event_id' => $event->id, 'employees' => count($employees ?? [])]);
        return view($viewPath, ['event' => $event, 'employees' => $employees]);
      } catch (AuthorizationException $e) {
        Log::debug("[{$class}::{$action}] debug auth error", ['event_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['event_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'event_id' => $id]);
  }

  public function update(Request $request, Event $event): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $event, $action, $method, $class) {
      try {
        $authStart = microtime(true);
        self::_setAuth($req, 'edit event');
        $this->logExecutionTime($authStart, $action, 'setAuth');
        if ($event[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId()) throw new AuthorizationException;
        Log::info("[{$class}::{$action}] start", ['event_id' => $event->id]);
        $valStart = microtime(true);
        $validator = Validator::make($req->all(), ['title' => 'required', 'start_date' => 'required', 'end_date' => 'required', 'color' => 'required']);
        $this->logExecutionTime($valStart, $action, 'buildValidator');
        $failCheckStart = microtime(true);
        if ($validator->fails()) {
          $this->logExecutionTime($failCheckStart, $action, 'validatorFailsCheck');
          Log::debug("[{$class}::{$action}] validation failed", ['event_id' => $event->id, 'first_error' => $validator->errors()->first(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
          return redirect()->back()->with('error', $validator->errors()->first());
        }
        $event->title = $req->input('title');
        $event->start_date = $req->input('start_date');
        $event->end_date = $req->input('end_date');
        $event->color = $req->input('color');
        $event->description = $req->input('description');
        $saveStart = microtime(true);
        $event->save();
        $this->logExecutionTime($saveStart, $action, 'saveEvent');
        Log::info("[{$class}::{$action}] updated", ['event_id' => $event->id]);
        return redirect()->route(ViewsConstants::EVT . '.index')->with('success', __('Event successfully updated.'));
      } catch (AuthorizationException $e) {
        Log::debug("[{$class}::{$action}] debug auth error", ['event_id' => $event->id ?? null, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['event_id' => $event->id ?? null, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'event_id' => $event->id]);
  }

  public function destroy(Request $request, Event $event): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $event, $action, $method, $class) {
      try {
        $authStart = microtime(true);
        self::_setAuth($req, 'delete event');
        $this->logExecutionTime($authStart, $action, 'setAuth');
        if ($event[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId()) throw new AuthorizationException;
        Log::info("[{$class}::{$action}] start", ['event_id' => $event->id]);
        $delStart = microtime(true);
        $event->delete();
        $this->logExecutionTime($delStart, $action, 'deleteEvent');
        Log::info("[{$class}::{$action}] deleted", ['event_id' => $event->id]);
        return redirect()->route(ViewsConstants::EVT . '.index')->with('success', __('Event successfully deleted.'));
      } catch (AuthorizationException $e) {
        Log::debug("[{$class}::{$action}] debug auth error", ['event_id' => $event->id ?? null, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['event_id' => $event->id ?? null, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'user_id' => $req->user()->id ?? null, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'event_id' => $event->id]);
  }

  public const GET_DPT = 'getDepartment';
  public function getDepartment(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      try {
        $creatorId = $req->user()->creatorId();
        Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId, UsersConstants::COL_BRC_ID => $req->input(UsersConstants::COL_BRC_ID)]);
        $depStart = microtime(true);
        $departments = $req->input(UsersConstants::COL_BRC_ID) == 0 ? Department::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck(CompaniesConstants::COL_DEP_ID, 'id')->toArray() : Department::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->where(UsersConstants::COL_BRC_ID, $req->input(UsersConstants::COL_BRC_ID))->pluck(CompaniesConstants::COL_DEP_ID, 'id')->toArray();
        $this->logExecutionTime($depStart, $action, 'fetchDepartments');
        Log::info("[{$class}::{$action}] ready", ['count' => count($departments)]);
        return response()->json($departments);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'creator_id' => $req->user()->creatorId() ?? null, UsersConstants::COL_BRC_ID => $req->input(UsersConstants::COL_BRC_ID), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, UsersConstants::COL_BRC_ID => $request->input(UsersConstants::COL_BRC_ID)]);
  }

  public const GET_EMP = 'getEmployee';
  public function getEmployee(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      try {
        $creatorId = $req->user()->creatorId();
        $deptIds = $req->input(UsersConstants::COL_DEP_ID, []);
        Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId, UsersConstants::COL_DEP_ID => $deptIds]);
        $empStart = microtime(true);
        $employees = in_array('0', $deptIds) ? Employee::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck(UsersConstants::COL_NM, 'id')->toArray() : Employee::query()->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->whereIn(UsersConstants::COL_DEP_ID, $deptIds)->pluck(UsersConstants::COL_NM, 'id')->toArray();
        $this->logExecutionTime($empStart, $action, 'fetchEmployees');
        Log::info("[{$class}::{$action}] ready", ['count' => count($employees)]);
        return response()->json($employees);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'creator_id' => $req->user()->creatorId() ?? null, UsersConstants::COL_DEP_ID => $req->input(UsersConstants::COL_DEP_ID, []), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, UsersConstants::COL_DEP_ID => $request->input(UsersConstants::COL_DEP_ID, [])]);
  }

  public const GET_EV_D = 'getEventData';
  public function getEventData(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", ['calendar_type' => $req->input('calendar_type')]);
        $arrayJson = [];
        if ($req->input('calendar_type') === 'google_calendar') {
          $gcStart = microtime(true);
          $arrayJson = Utility::getCalendarData('event');
          $this->logExecutionTime($gcStart, $action, 'getGoogleCalendarData');
        } else {
          $qStart = microtime(true);
          $data = Event::query()->where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->get();
          $this->logExecutionTime($qStart, $action, 'fetchEvents');
          $mapStart = microtime(true);
          foreach ($data as $val) {
            $endDate = date_create($val->end_date);
            date_add($endDate, date_interval_create_from_date_string("1 days"));
            $arrayJson[] = ['id' => $val->id, 'title' => $val->title, 'start' => $val->start_date, 'end' => date_format($endDate, "Y-m-d H:i:s"), 'className' => $val->color, 'url' => route(ViewsConstants::EVT . '.edit', $val->id), 'allDay' => true];
          }
          $this->logExecutionTime($mapStart, $action, 'mapEvents');
        }
        Log::info("[{$class}::{$action}] ready", ['count' => count($arrayJson)]);
        return response()->json($arrayJson);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'calendar_type' => $req->input('calendar_type'), 'user_id' => $req->user()->id ?? null, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'calendar_type' => $request->input('calendar_type')]);
  }

  protected static function _setAuth(Request $request, string $permission): void
  {
    if (!$request->user()->can($permission))
      throw new AuthorizationException;
  }
}
