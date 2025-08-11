<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  CompaniesConstants,
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants,
};
use App\Models\{
  Announcement,
  Branch,
  Department,
  Employee,
  EmployeeAnnouncement,
  Utility
};
use App\Traits\ChecksLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
  use ChecksLogin;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::XSS]);
  }


  public function index(Request $request)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      if (!$user?->can(PermissionsConstants::MNG_ANC))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
        $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first();
        $announcements  = Announcement::orderByDesc('id')
          ->leftJoin('employee_announcements', 'announcements.id', '=', 'employee_announcements.announcement_id')
          ->where('employee_announcements.employee_id', $currentEmployee->id)
          ->orWhere(
            fn ($q) => $q
              ->where('announcements.department_id', '["0"]')
              ->where('announcements.employee_id', '["0"]')
          )
          ->get();
      } else {
        $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first();
        $announcements  = Announcement::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
      }
      return view(ViewsConstants::ANC . '.' . __FUNCTION__, [
        'announcements'    => $announcements,
        'currentEmployee'  => $currentEmployee,
      ]);
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function create(Request $request)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      if (!$user?->can('create announcement'))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $employees  = Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
        ->pluck('name', 'id');
      $branches   = Branch::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
      $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
      return view(ViewsConstants::ANC . '.' . __FUNCTION__, [
        'employees'   => $employees,
        'branch'      => $branches,
        'departments' => $departments,
      ]);
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function store(Request $request)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      if (!$user?->can('create announcement'))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $validator = Validator::make($request->all(), [
        'title'        => 'required',
        'start_date'    => 'required',
        'end_date'      => 'required',
        CompaniesConstants::COL_BRC_ID     => 'required',
        CompaniesConstants::COL_DEP_ID => 'required',
        UsersConstants::COL_EMP_ID   => 'required',
      ]);
      if ($validator->fails())
        return redirect()->back()->with('error', $validator->errors()->first());
      $announcement = new Announcement();
      $announcement->title       = $request->title;
      $announcement->start_date  = $request->startDate;
      $announcement->end_date    = $request->endDate;
      $announcement->branch_id   = $request->branchId;
      $announcement->department_id = json_encode($request->departmentId);
      $announcement->employee_id = json_encode($request->employeeId);
      $announcement->description = $request->description;
      $announcement->created_by  = $user?->creatorId();
      $announcement->save();
      $employeeIds = in_array('0', $request->departmentId)
        ? Employee::whereIn(CompaniesConstants::COL_DEP_ID, $request->departmentId)->pluck('id')->toArray()
        : $request->employeeId;
      foreach ($employeeIds as $empId) {
        $ea = new EmployeeAnnouncement();
        $ea->announcement_id = $announcement->id;
        $ea->employee_id    = $empId;
        $ea->created_by     = $user?->creatorId();
        $ea->save();
      }
      $settings = Utility::settings($user?->creatorId());
      $branchNames = $request->branchId === 0
        ? Branch::pluck('name')->toArray()
        : explode(',', Branch::find($request->branchId)->name);
      $notif = [
        'announcement_title' => $request->title,
        'branch_name'       => implode(',', $branchNames),
        'start_date'        => $request->startDate,
        'end_date'          => $request->endDate,
      ];
      if (!empty($settings['announcement_notification']))
        Utility::sendSlackMsg('new_announcement', $notif);
      if (!empty($settings['telegram_announcement_notification']))
        Utility::sendTelegramMsg('new_announcement', $notif);
      if ($webhook = Utility::webhookSetting('New Announcement')) {
        $status = Utility::webhookCall($webhook['url'], json_encode($announcement), $webhook['method']);
        return $status
          ? redirect()->route(ViewsConstants::ANC . '.index')->with('success', __('Announcement successfully created.'))
          : redirect()->back()->with('error', __('Webhook call failed.'));
      }
      return redirect()->route(ViewsConstants::ANC . '.index')->with('success', __('Announcement successfully created.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function show(Request $request, Announcement $announcement)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      if (!$user?->can('view announcement'))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      if ($announcement->created_by !== $user?->creatorId())
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      return view(ViewsConstants::ANC . '.show', ['announcement' => $announcement]);
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function edit(Request $request, Announcement $announcement)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      if (!$user?->can('edit announcement'))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $branches   = Branch::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
      $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
      return view(ViewsConstants::ANC . '.edit', [
        'announcement' => $announcement,
        'branch'      => $branches,
        'departments' => $departments,
      ]);
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function update(Request $request, Announcement $announcement)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      if (!$user?->can('edit announcement'))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      if ($announcement->created_by !== $user?->creatorId())
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $validator = Validator::make($request->all(), [
        'title'       => 'required',
        'start_date'   => 'required',
        'end_date'     => 'required',
        CompaniesConstants::COL_BRC_ID    => 'required',
        CompaniesConstants::COL_DEP_ID => 'required',
      ]);
      if ($validator->fails())
        return redirect()->back()->with('error', $validator->errors()->first());
      $announcement->title        = $request->title;
      $announcement->start_date   = $request->startDate;
      $announcement->end_date     = $request->endDate;
      $announcement->branch_id    = $request->branchId;
      $announcement->department_id = json_encode($request->departmentId);
      $announcement->description  = $request->description;
      $announcement->save();
      return redirect()->route(ViewsConstants::ANC . '.index')->with('success', __('Announcement successfully updated.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function destroy(Request $request, Announcement $announcement)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      if (!$user?->can('delete announcement'))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      if ($announcement->created_by !== $user?->creatorId())
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $announcement->delete();
      return redirect()->route(ViewsConstants::ANC . '.index')->with('success', __('Announcement successfully deleted.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function getDepartment(Request $request)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    $departments = $request->branchId === 0
      ? Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->toArray()
      : Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->where(CompaniesConstants::COL_BRC_ID, $request->branchId)->pluck('name', 'id')->toArray();
    return response()->json($departments);
  }

  public function getEmployee(Request $request)
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof \Illuminate\Http\RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    $employees = empty($request->departmentId)
      ? Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->toArray()
      : Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->where(CompaniesConstants::COL_DEP_ID, $request->departmentId)->pluck('name', 'id')->toArray();
    return response()->json($employees);
  }
}
