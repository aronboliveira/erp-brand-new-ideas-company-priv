<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  CompaniesConstants,
  DatabaseConstants,
  UsersConstants,
  ViewsConstants,
};
use App\Models\{Branch, Department};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\{
  Log,
  Route,
  Validator,
  View as ViewFacade,
};

final class DepartmentController extends Controller
{
  use ChecksLogin, ChecksPermissions;
  private const REDIRECT_INDEX = '/';

  public function index(Request $r)
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $base = class_basename($class);
    return $this->measureProfile($action, function () use ($r, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if ($c = self::guard($r, 'manage department', self::REDIRECT_INDEX)) return $c; // ! ALERT
      Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $r->user()?->id, 'method' => $method]);
      try {
        $qStart = microtime(true);
        $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
        $this->logExecutionTime($qStart, $action, 'fetchDepartments');
        $viewPath = ViewsConstants::DPT . '.' . $action;
        if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('departments'));
        $this->logExecutionTime($renderStart, $action, 'renderView');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($r, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function create(Request $r)
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $base = class_basename($class);
    return $this->measureProfile($action, function () use ($r, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if ($c = self::guard($r, 'create department', self::REDIRECT_INDEX)) return $c; // ! ALERT
      Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $r->user()?->id, 'method' => $method]);
      try {
        $branch = self::branches($u->creatorId());
        $viewPath = ViewsConstants::DPT . '.' . $action;
        if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('branch'));
        $this->logExecutionTime($renderStart, $action, 'renderView');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($r, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function store(Request $r): RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $base = class_basename($class);
    return $this->measureProfile($action, function () use ($r, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if ($c = self::guard($r, 'create department', self::REDIRECT_INDEX)) return $c; // ! ALERT
      if ($c = self::v($r, [CompaniesConstants::COL_BRC_ID => 'required', CompaniesConstants::COL_DEP_NM => 'required|max:20'])) return $c;
      Log::debug("[{$base}::{$action}] validated", ['input' => $r->only([CompaniesConstants::COL_BRC_ID, CompaniesConstants::COL_DEP_NM])]);
      try {
        $crtStart = microtime(true);
        Department::create([
          CompaniesConstants::COL_BRC_ID => $r[CompaniesConstants::COL_BRC_ID],
          CompaniesConstants::COL_DEP_NM => $r[CompaniesConstants::COL_DEP_NM],
          DatabaseConstants::TABLE_CREATOR => $u->creatorId()
        ]);
        $this->logExecutionTime($crtStart, $action, 'createDepartment');
        return redirect()->route(ViewsConstants::DPT . '.index')->with('success', __('Department successfully created.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($r, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function edit(Request $r, Department $department)
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $base = class_basename($class);
    return $this->measureProfile($action, function () use ($r, $department, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if ($c = self::guard($r, 'edit department', self::REDIRECT_INDEX)) return $c; // ! ALERT
      if ($department[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId()) return defaultPermissionDenial($r, new \Exception('owner'));
      try {
        $branch = self::branches($u->creatorId());
        $viewPath = ViewsConstants::DPT . '.' . $action;
        if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('department', 'branch'));
        $this->logExecutionTime($renderStart, $action, 'renderView');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'department_id' => $department->id]);
        Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($r, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'department_id' => $department->id]);
  }

  public function update(Request $r, Department $department): RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $base = class_basename($class);
    return $this->measureProfile($action, function () use ($r, $department, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if ($c = self::guard($r, 'edit department', self::REDIRECT_INDEX)) return $c; // ! ALERT
      if ($department[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId()) return defaultPermissionDenial($r, new \Exception('owner'));
      if ($c = self::v($r, [CompaniesConstants::COL_BRC_ID => 'required', CompaniesConstants::COL_DEP_NM => 'required|max:20'])) return $c;
      try {
        $updStart = microtime(true);
        $department->update([
          CompaniesConstants::COL_BRC_ID => $r[CompaniesConstants::COL_BRC_ID],
          CompaniesConstants::COL_DEP_NM => $r[CompaniesConstants::COL_DEP_NM]
        ]);
        $this->logExecutionTime($updStart, $action, 'updateDepartment');
        return redirect()->route(ViewsConstants::DPT . '.index')->with('success', __('Department successfully updated.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'department_id' => $department->id]);
        Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($r, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'department_id' => $department->id]);
  }

  public function destroy(Request $r, Department $department): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $base = class_basename($class);
    return $this->measureProfile($action, function () use ($r, $department, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if ($c = self::guard($r, 'delete department', self::REDIRECT_INDEX)) return $c; // ! ALERT
      if ($department[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId()) return defaultPermissionDenial($r, new \Exception('owner'));
      try {
        $delStart = microtime(true);
        $department->delete();
        $this->logExecutionTime($delStart, $action, 'deleteDepartment');
        return redirect()->route(ViewsConstants::DPT . '.index')->with('success', __('Department successfully deleted.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'department_id' => $department->id]);
        Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($r, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'department_id' => $department->id]);
  }

  public function show(): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $base = class_basename($class);
    return $this->measureProfile($action, function () {
      return redirect()->route(ViewsConstants::DPT . '.index');
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  private static function v(Request $r, array $rules): ?RedirectResponse
  {
    $v = Validator::make($r->all(), $rules);
    return $v->fails()
      ? redirect()->back()->with('error', $v->getMessageBag()->first())
      : null;
  }

  private static function branches(int $creator): array
  {
    try {
      return Branch::where(DatabaseConstants::TABLE_CREATOR, $creator)
        ->pluck(CompaniesConstants::COL_BRC_NM, 'id')
        ->all();
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . $e->getMessage());
      return [];
    }
  }
}
