<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  CompaniesConstants,
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
  use ChecksLogin;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::XSS]);
  }

  public function index(Request $request): View|RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        if (!$user?->can(PermissionsConstants::MNG_ANC)) return defaultPermissionDenial($request, null, "$cls::$action");
        $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first();
        if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
          $announcements = Announcement::orderByDesc('id')
            ->leftJoin('employee_announcements', 'announcements.id', '=', 'employee_announcements.announcement_id')
            ->where('employee_announcements.employee_id', optional($currentEmployee)->id)
            ->orWhere(function ($q) {
              $q->where('announcements.department_id', '["0"]')->where('announcements.employee_id', '["0"]');
            })
            ->select('announcements.*')
            ->get();
        } else {
          $announcements = Announcement::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->orderByDesc('id')->get();
        }
        $view = ViewsConstants::ANC . '.index';
        if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
        return ViewFacade::make($view, ['announcements' => $announcements, 'currentEmployee' => $currentEmployee]);
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, "$cls::$action");
      }
    });
  }

  public function create(Request $request): View|RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        if (!$user?->can('create announcement')) return defaultPermissionDenial($request, null, "$cls::$action");
        $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
        $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
        $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
        $view = ViewsConstants::ANC . '.create';
        if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
        return ViewFacade::make($view, ['employees' => $employees, 'branch' => $branches, 'departments' => $departments]);
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, "$cls::$action");
      }
    });
  }

  public function store(Request $request): RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        if (!$user?->can('create announcement')) return defaultPermissionDenial($request, null, "$cls::$action");
        $v = Validator::make($request->all(), [
          'title' => 'required',
          'start_date' => 'required|date',
          'end_date' => 'required|date|after_or_equal:start_date',
          CompaniesConstants::COL_BRC_ID => 'required|integer',
          CompaniesConstants::COL_DEP_ID => 'required|array',
          UsersConstants::COL_EMP_ID => 'required|array'
        ]);
        if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
        $branchId = (int) $request->input(CompaniesConstants::COL_BRC_ID);
        $departmentIds = (array) $request->input(CompaniesConstants::COL_DEP_ID, []);
        $employeeIds = (array) $request->input(UsersConstants::COL_EMP_ID, []);
        $creatorId = $user?->creatorId();
        $announcement = new Announcement([
          'title' => $request->input('title'),
          'start_date' => $request->input('start_date'),
          'end_date' => $request->input('end_date'),
          'branch_id' => $branchId,
          'department_id' => json_encode($departmentIds),
          'employee_id' => json_encode($employeeIds),
          'description' => $request->input('description', ''),
          DatabaseConstants::COL_TABLE_CREATOR => $creatorId
        ]);
        $announcement->save();
        if (in_array(0, $departmentIds, true)) {
          $targetEmployeeIds = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('id')->toArray();
        } elseif (empty($employeeIds)) {
          $targetEmployeeIds = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->whereIn(CompaniesConstants::COL_DEP_ID, $departmentIds)->pluck('id')->toArray();
        } else {
          $targetEmployeeIds = $employeeIds;
        }
        foreach ($targetEmployeeIds as $empId) {
          EmployeeAnnouncement::create([
            'announcement_id' => $announcement->id,
            'employee_id' => $empId,
            DatabaseConstants::COL_TABLE_CREATOR => $creatorId
          ]);
        }
        $settings = Utility::settings($creatorId);
        $branchNames = $branchId === 0
          ? Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name')->toArray()
          : [Branch::whereKey($branchId)->value('name')];
        $payload = [
          'announcement_title' => $announcement->title,
          'branch_name' => implode(',', array_filter($branchNames)),
          'start_date' => $announcement->start_date,
          'end_date' => $announcement->end_date
        ];
        ($settings['announcement_notification'] ?? false) && Utility::sendSlackMsg('new_announcement', $payload);
        ($settings['telegram_announcement_notification'] ?? false) && Utility::sendTelegramMsg('new_announcement', $payload);
        if ($hook = Utility::webhookSetting('New Announcement')) {
          $ok = Utility::webhookCall($hook['url'], $announcement->toJson(), $hook['method']);
          if (!$ok) return redirect()->back()->with('error', __('Webhook call failed.'));
        }
        return redirect()->route(ViewsConstants::ANC . '.index')->with('success', __('Announcement successfully created.'));
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, "$cls::$action");
      }
    });
  }

  public function show(Request $request, Announcement $announcement): View|RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $announcement, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        if (!$user?->can('view announcement')) return defaultPermissionDenial($request, null, "$cls::$action");
        if ($announcement->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, "$cls::$action");
        $view = ViewsConstants::ANC . '.show';
        if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
        return ViewFacade::make($view, ['announcement' => $announcement]);
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, "$cls::$action");
      }
    });
  }

  public function edit(Request $request, Announcement $announcement): View|RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $announcement, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        if (!$user?->can('edit announcement')) return defaultPermissionDenial($request, null, "$cls::$action");
        $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
        $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
        $view = ViewsConstants::ANC . '.edit';
        if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");
        return ViewFacade::make($view, ['announcement' => $announcement, 'branch' => $branches, 'departments' => $departments]);
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, "$cls::$action");
      }
    });
  }

  public function update(Request $request, Announcement $announcement): RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $announcement, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        if (!$user?->can('edit announcement')) return defaultPermissionDenial($request, null, "$cls::$action");
        if ($announcement->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, "$cls::$action");
        $v = Validator::make($request->all(), [
          'title' => 'required',
          'start_date' => 'required|date',
          'end_date' => 'required|date|after_or_equal:start_date',
          CompaniesConstants::COL_BRC_ID => 'required|integer',
          CompaniesConstants::COL_DEP_ID => 'required|array'
        ]);
        if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
        $announcement->update([
          'title' => $request->input('title'),
          'start_date' => $request->input('start_date'),
          'end_date' => $request->input('end_date'),
          'branch_id' => (int) $request->input(CompaniesConstants::COL_BRC_ID),
          'department_id' => json_encode((array) $request->input(CompaniesConstants::COL_DEP_ID, [])),
          'description' => $request->input('description', '')
        ]);
        return redirect()->route(ViewsConstants::ANC . '.index')->with('success', __('Announcement successfully updated.'));
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, "$cls::$action");
      }
    });
  }

  public function destroy(Request $request, Announcement $announcement): RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $announcement, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        if (!$user?->can('delete announcement')) return defaultPermissionDenial($request, null, "$cls::$action");
        if ($announcement->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, "$cls::$action");
        $announcement->delete();
        return redirect()->route(ViewsConstants::ANC . '.index')->with('success', __('Announcement successfully deleted.'));
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, "$cls::$action");
      }
    });
  }

  public const GET_DPT = 'getDepartment';
  public function getDepartment(Request $request): JsonResponse|RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      $branchId = (int) $request->input(CompaniesConstants::COL_BRC_ID, 0);
      $departments = $branchId === 0
        ? Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->toArray()
        : Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->where(CompaniesConstants::COL_BRC_ID, $branchId)->pluck('name', 'id')->toArray();
      return response()->json($departments);
    });
  }

  public const GET_EMP = 'getEmployee';
  public function getEmployee(Request $request): JsonResponse|RedirectResponse
  {
    $cls = __CLASS__;
    $action = __FUNCTION__;
    return $this->measureProfile("$cls::$action", function () use ($request, $cls, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      $deptIds = (array) $request->input(CompaniesConstants::COL_DEP_ID, []);
      $employees = empty($deptIds)
        ? Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->toArray()
        : Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->whereIn(CompaniesConstants::COL_DEP_ID, $deptIds)->pluck('name', 'id')->toArray();
      return response()->json($employees);
    });
  }
}
