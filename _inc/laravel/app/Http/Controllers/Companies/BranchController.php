<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use App\Models\{Branch, Department, Employee};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Route, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class BranchController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'manage branch')) !== true) return $g;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $request->user()?->id, 'method' => $method]);
            try {
                $qStart = microtime(true);
                $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()?->creatorId())->get();
                $this->logExecutionTime($qStart, $action, 'fetchBranches');
                $viewPath = ViewsConstants::BRC . '.' . $action;
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('branches'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'create branch')) !== true) return $g;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $request->user()?->id, 'method' => $method]);
            $viewPath = ViewsConstants::BRC . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath);
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'create branch')) !== true) return $g;
            $valStart = microtime(true);
            $v = validator($request->all(), ['name' => 'required']);
            $this->logExecutionTime($valStart, $action, 'validate');
            if ($v->fails()) return back()->with('error', $v->errors()->first());
            try {
                $crtStart = microtime(true);
                $branch = Branch::create([
                    CompaniesConstants::COL_BRC_NM => $request->input('name'),
                    DatabaseConstants::COL_TABLE_CREATOR => $request->user()?->creatorId(),
                ]);
                $this->logExecutionTime($crtStart, $action, 'createBranch');
                Log::info("[{$base}::{$action}] created", ['branch_id' => $branch->id, UsersConstants::COL_USER_ID => $request->user()?->id]);
                return redirect()->route(ViewsConstants::BRC . '.index')->with('success', __('Branch successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Branch $branch): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($branch) {
            return redirect()->route(ViewsConstants::BRC . '.index')->with($branch);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $request, Branch $branch): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $branch, $action, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'edit branch')) !== true) return $g;
            if ($branch[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()?->creatorId()) return defaultPermissionDenial($request, null, $class . '::' . $action);
            $viewPath = ViewsConstants::BRC . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('branch'));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'branch_id' => $branch->id]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $branch, $action, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'edit branch')) !== true) return $g;
            if ($branch[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()?->creatorId()) return defaultPermissionDenial($request, null, $class . '::' . $action);
            $valStart = microtime(true);
            $v = validator($request->all(), [CompaniesConstants::COL_BRC_NM => 'required']);
            $this->logExecutionTime($valStart, $action, 'validate');
            if ($v->fails()) return back()->with('error', $v->errors()->first());
            try {
                $updStart = microtime(true);
                $branch->update([CompaniesConstants::COL_BRC_NM => $request->input('name')]);
                $this->logExecutionTime($updStart, $action, 'updateBranch');
                Log::info("[{$base}::{$action}] updated", ['branch_id' => $branch->id, UsersConstants::COL_USER_ID => $request->user()?->id]);
                return redirect()->route(ViewsConstants::BRC . '.index')->with('success', __('Branch successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'branch_id' => $branch->id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'branch_id' => $branch->id]);
    }

    public function destroy(Request $request, Branch $branch): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $branch, $action, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'delete branch')) !== true) return $g;
            if ($branch[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()?->creatorId()) return defaultPermissionDenial($request, null, $class . '::' . $action);
            try {
                $delStart = microtime(true);
                $branch->delete();
                $this->logExecutionTime($delStart, $action, 'deleteBranch');
                Log::info("[{$base}::{$action}] deleted", ['branch_id' => $branch->id, UsersConstants::COL_USER_ID => $request->user()?->id]);
                return redirect()->route(ViewsConstants::BRC . '.index')->with('success', __('Branch successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'branch_id' => $branch->id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'branch_id' => $branch->id]);
    }

    public const GET_DPT = 'getDepartment';
    public function getDepartment(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action) {
            if ((self::_checkLogin()) instanceof RedirectResponse) return response()->json([], 401);
            $branchId = (int) $request->input(CompaniesConstants::COL_BRC_ID, 0);
            $qStart = microtime(true);
            $departments = $branchId === 0
                ? Department::pluck(CompaniesConstants::COL_DEP_NM, 'id')->toArray()
                : Department::where(CompaniesConstants::COL_BRC_ID, $branchId)->pluck(CompaniesConstants::COL_DEP_NM, 'id')->toArray();
            $this->logExecutionTime($qStart, $action, 'fetchDepartments');
            return response()->json($departments);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'branch_id' => $request->input(CompaniesConstants::COL_BRC_ID)]);
    }

    public const GET_EMP = 'getEmployee';
    public function getEmployee(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action) {
            if ((self::_checkLogin()) instanceof RedirectResponse) return response()->json([], 401);
            $deptIds = $request->input(CompaniesConstants::COL_DEP_ID, []);
            if (!is_array($deptIds)) $deptIds = [];
            $qStart = microtime(true);
            $employees = in_array(0, $deptIds)
                ? Employee::pluck(UsersConstants::COL_NM, 'id')->toArray()
                : Employee::whereIn(CompaniesConstants::COL_DEP_ID, $deptIds)->pluck(UsersConstants::COL_NM, 'id')->toArray();
            $this->logExecutionTime($qStart, $action, 'fetchEmployees');
            return response()->json($employees);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'dept_ids' => $request->input(CompaniesConstants::COL_DEP_ID)]);
    }
}
