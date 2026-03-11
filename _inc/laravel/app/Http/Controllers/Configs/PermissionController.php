<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants};
use App\Models\{Permission, Role};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\HasCrudConstants;
class PermissionController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'permission';
    private const ROUTE_INDEX = 'permissions.index'; // ! ALERT

    public function index(Request $request): View|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $function) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::MNG_PERM, self::ROUTE_INDEX)) !== true) return $c;
            $permissions = Permission::all();
            return view(self::SINGULAR . '.' . $function, compact(DatabaseConstants::TABLE_PERMISSIONS)); // ! ALERT
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $function) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::CR_PERM, self::ROUTE_INDEX)) !== true) return $c;
            $roles = Role::all();
            return view(self::SINGULAR . '.' . $function, compact(DatabaseConstants::TABLE_ROLES)); // ! ALERT
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, PermissionsConstants::CR_PERM, self::ROUTE_INDEX)) !== true) return $c;

                $data = $request->validate([
                    'name'                          => 'required|string|max:40',
                    DatabaseConstants::TABLE_ROLES  => 'nullable|array',
                ]);

                $permission = Permission::create(['name' => $data['name']]);

                foreach ($data[DatabaseConstants::TABLE_ROLES] ?? [] as $roleId) {
                    $role = Role::findOrFail($roleId);
                    $role->givePermissionTo($permission);
                }

                return redirect()->route(self::ROUTE_INDEX)->with('success', "Permission {$permission->name} added!"); // ! ALERT
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function edit(Request $request, Permission $permission): View|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $permission, $action, $function) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::ED_PERM, self::ROUTE_INDEX)) !== true) return $c;

            $roles = Role::where(DatabaseConstants::COL_TABLE_CREATOR, $userOrRedirect?->creatorId())->get();

            return view(self::SINGULAR . '.' . $function, compact(self::SINGULAR, DatabaseConstants::TABLE_ROLES)); // ! ALERT
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $permission, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, PermissionsConstants::ED_PERM, self::ROUTE_INDEX)) !== true) return $c;

                $data = $request->validate([
                    'name' => 'required|string|max:40',
                ]);

                $permission->update(['name' => $data['name']]);

                return redirect()->route(self::ROUTE_INDEX)->with('success', "Permission {$permission->name} updated!"); // ! ALERT
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, PermissionsConstants::DEL_PERM, self::ROUTE_INDEX)) !== true) return $c;

                $permission = Permission::findOrFail($id);
                $permission->delete();

                return redirect()->route(self::ROUTE_INDEX)->with('success', self::SINGULAR . ' successfully deleted.'); // ! ALERT
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }
}
