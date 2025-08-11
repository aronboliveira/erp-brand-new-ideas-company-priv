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
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{

  public function index(Request $request): mixed
  {
    try {
      self::_setAuth($request, PermissionsConstants::MNG_EVT);
      $creatorId          = $request->user()->creatorId();
      $employees          = Employee::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->get();
      $events             = Event::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->get();
      $transDate          = date('Y-m-d');
      $todayMonth         = date('m');
      $currentMonthEvents = Event::query()
        ->select('id', 'start_date', 'end_date', 'title', 'created_at', 'color')
        ->whereRaw('MONTH(start_date)=' . $todayMonth)
        ->whereRaw('MONTH(end_date)=' . $todayMonth)
        ->get();
      $arrEvents          = $events
        ->map(fn ($e) => [
          'id'        => $e->id,
          'title'     => $e->title,
          'start'     => $e->start_date,
          'end'       => $e->end_date,
          'className' => $e->color,
          'url'       => route(ViewsConstants::EVT . '.edit', $e->id),
        ])
        ->toJson();
      return view(ViewsConstants::EVT . '.' . __FUNCTION__, [
        'arrEvents'          => $arrEvents,
        'employees'          => $employees,
        'transDate'          => $transDate,
        'events'             => $events,
        'currentMonthEvents' => $currentMonthEvents,
      ]);
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function create(Request $request): mixed
  {
    try {
      self::_setAuth($request, 'create event');
      $creatorId  = $request->user()->creatorId();
      $employees  = Employee::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->pluck(UsersConstants::COL_NM, 'id');
      $branch     = Branch::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->get();
      $departments = Department::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->get();
      $settings   = Utility::settings();
      return view(ViewsConstants::EVT . '.' . __FUNCTION__, [
        'employees'   => $employees,
        'branch'      => $branch,
        'departments' => $departments,
        'settings'    => $settings,
      ]);
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function store(Request $request): mixed
  {
    try {
      self::_setAuth($request, 'create event');
      $validator = Validator::make($request->all(), [
        UsersConstants::COL_BRC_ID     => 'required',
        UsersConstants::COL_DEP_ID => 'required',
        UsersConstants::COL_EMP_ID   => 'required',
        'title'         => 'required',
        'start_date'    => 'required',
        'end_date'      => 'required',
        'color'         => 'required',
      ]);
      if ($validator->fails())
        return redirect()
          ->back()
          ->with('error', $validator->errors()->first());
      $event = new Event();
      $event->branch_id    = $request->input(UsersConstants::COL_BRC_ID);
      $event->department_id = json_encode($request->input(UsersConstants::COL_DEP_ID));
      $event->employee_id  = json_encode($request->input(UsersConstants::COL_EMP_ID));
      $event->title        = $request->input('title');
      $event->start_date   = $request->input('start_date');
      $event->end_date     = $request->input('end_date');
      $event->color        = $request->input('color');
      $event->description  = $request->input('description');
      $event[DatabaseConstants::TABLE_CREATOR]   = $request->user()->creatorId();
      $event->save();
      $deptEmployees = in_array(
        '0',
        $request->input(UsersConstants::COL_EMP_ID, [])
      )
        ? Employee::query()
        ->whereIn(
          UsersConstants::COL_DEP_ID,
          [$request->input(UsersConstants::COL_DEP_ID)]
        )
        ->pluck('id')
        : $request->input(UsersConstants::COL_EMP_ID);
      foreach ($deptEmployees as $emp) {
        EventEmployee::create([
          'event_id'    => $event->id,
          UsersConstants::COL_EMP_ID => $emp,
          DatabaseConstants::TABLE_CREATOR  => $request->user()->creatorId(),
        ]);
      }
      $setting   = Utility::settings($request->user()->creatorId());
      $branchName = $request->input(UsersConstants::COL_BRC_ID) == 0
        ? implode(',', Branch::all()->pluck('name')->toArray())
        : Branch::find($request->input(UsersConstants::COL_BRC_ID))->name;
      $notif     = [
        'event_title'      => $request->input('title'),
        CompaniesConstants::COL_BRC_NM      => $branchName,
        'event_start_date' => $request->input('start_date'),
        'event_end_date'   => $request->input('end_date'),
      ];
      isset($setting['event_notification'])
        && $setting['event_notification'] == 1
        ? Utility::sendSlackMsg('new_event', $notif)
        : null;
      isset($setting['telegram_event_notification'])
        && $setting['telegram_event_notification'] == 1
        ? Utility::sendTelegramMsg('new_event', $notif)
        : null;
      $request->input('synchronize_type') === 'google_calendar'
        ? Utility::addCalendarData($request, 'event')
        : null;
      $webhook = Utility::webhookSetting('New Event');
      $webhook
        ? Utility::webhookCall(
          $webhook['url'],
          json_encode($event),
          $webhook['method']
        )
        : null;
      return redirect()
        ->route(ViewsConstants::EVT . '.index')
        ->with('success', __('Event successfully created.'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function show(Request $request, Event $event): RedirectResponse
  {
    try {
      return redirect()->route(ViewsConstants::EVT . '.index');
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function edit(Request $request, string|int $id): mixed
  {
    try {
      self::_setAuth($request, 'edit event');
      $event = Event::findOrFail($id);
      if ($event[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
        throw new AuthorizationException;
      }
      $employees = Employee::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->pluck(UsersConstants::COL_NM, 'id');
      return view(ViewsConstants::EVT . '.' . __FUNCTION__, [
        'event'     => $event,
        'employees' => $employees,
      ]);
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function update(Request $request, Event $event): mixed
  {
    try {
      self::_setAuth($request, 'edit event');
      if ($event[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
        throw new AuthorizationException;
      }
      $validator = Validator::make($request->all(), [
        'title'      => 'required',
        'start_date' => 'required',
        'end_date'   => 'required',
        'color'      => 'required',
      ]);
      if ($validator->fails()) {
        return redirect()
          ->back()
          ->with('error', $validator->errors()->first());
      }
      $event->title      = $request->input('title');
      $event->start_date = $request->input('start_date');
      $event->end_date   = $request->input('end_date');
      $event->color      = $request->input('color');
      $event->description = $request->input('description');
      $event->save();
      return redirect()
        ->route(ViewsConstants::EVT . '.index')
        ->with('success', __('Event successfully updated.'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function destroy(Request $request, Event $event): mixed
  {
    try {
      self::_setAuth($request, 'delete event');
      if ($event[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
        throw new AuthorizationException;
      }
      $event->delete();
      return redirect()
        ->route(ViewsConstants::EVT . '.index')
        ->with('success', __('Event successfully deleted.'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function getDepartment(Request $request): JsonResponse
  {
    $creatorId = $request->user()->creatorId();
    $departments = $request->input(UsersConstants::COL_BRC_ID) == 0
      ? Department::query()
      ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
      ->pluck(CompaniesConstants::COL_DEP_ID, 'id')
      ->toArray()
      : Department::query()
      ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
      ->where(UsersConstants::COL_BRC_ID, $request->input(UsersConstants::COL_BRC_ID))
      ->pluck(CompaniesConstants::COL_DEP_ID, 'id')
      ->toArray();
    return response()->json($departments);
  }

  public function getEmployee(Request $request): JsonResponse
  {
    $creatorId = $request->user()->creatorId();
    $deptIds  = $request->input(UsersConstants::COL_DEP_ID, []);
    $employees = in_array('0', $deptIds)
      ? Employee::query()
      ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
      ->pluck(UsersConstants::COL_NM, 'id')
      ->toArray()
      : Employee::query()
      ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
      ->whereIn(UsersConstants::COL_DEP_ID, $deptIds)
      ->pluck(UsersConstants::COL_NM, 'id')
      ->toArray();
    return response()->json($employees);
  }

  public function getEventData(Request $request): JsonResponse
  {
    $arrayJson = [];
    if ($request->input('calendar_type') === 'google_calendar') {
      $arrayJson = Utility::getCalendarData('event');
    } else {
      $data = Event::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->get();
      foreach ($data as $val) {
        $endDate = date_create($val->end_date);
        date_add(
          $endDate,
          date_interval_create_from_date_string("1 days")
        );
        $arrayJson[] = [
          'id'        => $val->id,
          'title'     => $val->title,
          'start'     => $val->start_date,
          'end'       => date_format($endDate, "Y-m-d H:i:s"),
          'className' => $val->color,
          'url'       => route(ViewsConstants::EVT . '.edit', $val->id),
          'allDay'    => true,
        ];
      }
    }
    return response()->json($arrayJson);
  }

  protected static function _setAuth(Request $request, string $permission): void
  {
    if (!$request->user()->can($permission))
      throw new AuthorizationException;
  }
}
