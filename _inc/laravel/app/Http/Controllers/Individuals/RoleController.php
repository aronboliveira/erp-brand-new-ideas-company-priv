<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants};
use App\Models\{Permission, Role};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    DB,
    Log,
    View as ViewFacade
};
use Illuminate\View\View;

class RoleController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'role';
    private const REDIRECT_ROUTE = self::SINGULAR . '.index';

    public function index(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::MNG_ROLE, self::REDIRECT_ROUTE)) !== true) return $c;
            try {
                $roles = Role::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
                $view = self::SINGULAR . '.' . __FUNCTION__;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method, route(self::REDIRECT_ROUTE)); // ! ALERT
                Log::debug($method . ' loaded', ['count' => $roles->count()]);
                return ViewFacade::make($view, compact(DatabaseConstants::TABLE_ROLES));
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE)); // ! ALERT
            }
        }, []);
    }

    public function create(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::CR_ROLE, self::REDIRECT_ROUTE)) !== true) return $c;
            try {
                $permissions = $user->type == PermissionsConstants::SA
                    ? Permission::pluck('name', 'id')->toArray()
                    : $user?->roles->flatMap->permissions->pluck('name', 'id')->toArray();
                $view = self::SINGULAR . '.' . __FUNCTION__;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method, route(self::REDIRECT_ROUTE)); // ! ALERT
                return ViewFacade::make($view, compact(DatabaseConstants::TABLE_PERMISSIONS));
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE)); // ! ALERT
            }
        }, []);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::CR_ROLE, self::REDIRECT_ROUTE)) !== true) return $c;
            $request->validate([
                'name' => 'required|max:100|unique:roles,name,NULL,id,' . DatabaseConstants::TABLE_CREATOR . ',' . $user?->creatorId(),
                DatabaseConstants::TABLE_PERMISSIONS => 'required|array'
            ]);
            DB::beginTransaction();
            try {
                $role = Role::create([
                    'name' => $request->input('name'),
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
                ]);
                foreach ($request->input(DatabaseConstants::TABLE_PERMISSIONS) as $pid) {
                    $role->givePermissionTo(Permission::findOrFail($pid));
                }
                DB::commit();
                Log::debug($method . ' created', ['role_id' => $role->id]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __(ucfirst(self::SINGULAR) . ' successfully created.')); // ! ALERT
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE)); // ! ALERT
            }
        }, ['name' => $request->input('name')]);
    }

    public function edit(Request $request, Role $role): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $role, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::ED_ROLE, self::REDIRECT_ROUTE)) !== true) return $c;
            try {
                $permissions = $user->type == PermissionsConstants::SA
                    ? Permission::pluck('name', 'id')->toArray()
                    : $user?->roles->flatMap->permissions->pluck('name', 'id')->toArray();
                $view = self::SINGULAR . '.' . __FUNCTION__;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $method, route(self::REDIRECT_ROUTE)); // ! ALERT
                return ViewFacade::make($view, compact(self::SINGULAR, DatabaseConstants::TABLE_PERMISSIONS));
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE)); // ! ALERT
            }
        }, ['role_id' => $role->id]);
    }

    public function update(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $role, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::ED_ROLE, self::REDIRECT_ROUTE)) !== true) return $c;
            $request->validate([
                'name' => 'required|max:100|unique:roles,name,' . $role->id . ',id,' . DatabaseConstants::TABLE_CREATOR . ',' . $user?->creatorId(),
                DatabaseConstants::TABLE_PERMISSIONS => 'required|array'
            ]);
            DB::beginTransaction();
            try {
                $role->update(['name' => $request->input('name')]);
                $role->syncPermissions($request->input(DatabaseConstants::TABLE_PERMISSIONS));
                DB::commit();
                Log::debug($method . ' updated', ['role_id' => $role->id]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __(ucfirst(self::SINGULAR) . ' successfully updated.')); // ! ALERT
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE)); // ! ALERT
            }
        }, ['role_id' => $role->id, 'name' => $request->input('name')]);
    }

    public function destroy(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $role, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::DEL_ROLE, self::REDIRECT_ROUTE)) !== true) return $c;
            DB::beginTransaction();
            try {
                $role->delete();
                DB::commit();
                Log::debug($method . ' deleted', ['role_id' => $role->id]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __(ucfirst(self::SINGULAR) . ' successfully deleted.')); // ! ALERT
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE)); // ! ALERT
            }
        }, ['role_id' => $role->id]);
    }
}
