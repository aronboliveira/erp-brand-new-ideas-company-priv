<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants};
use App\Models\{Permission, Role};
use App\Traits\ChecksLogin;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class PermissionController extends Controller
{
    use ChecksLogin;
    private const SINGULAR = 'permission';

    public function index(Request $request): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::MNG_PERM))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $permissions = Permission::all();
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_PERMISSIONS));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::CR_PERM))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $roles = Role::all();
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_ROLES));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::CR_PERM))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $data = $request->validate([
                'name'  => 'required|string|max:40',
                DatabaseConstants::TABLE_ROLES => 'nullable|array',
            ]);
            $permission = Permission::create(['name' => $data['name']]);
            foreach ($data[DatabaseConstants::TABLE_ROLES] ?? [] as $roleId) {
                $role = Role::findOrFail($roleId);
                $role->givePermissionTo($permission);
            }
            return redirect()
                ->route(self::SINGULAR . 's.index')
                ->with('success', "Permission {$permission->name} added!");
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::SINGULAR . 's.index')
            );
        }
    }

    public function edit(Request $request, Permission $permission): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::ED_PERM))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $roles = Role::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(
                self::SINGULAR . '',
                DatabaseConstants::TABLE_ROLES
            ));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::ED_PERM))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $data = $request->validate([
                'name' => 'required|string|max:40',
            ]);
            $permission->update(['name' => $data['name']]);
            return redirect()
                ->route(self::SINGULAR . 's.index')
                ->with('success', "Permission {$permission->name} updated!");
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::SINGULAR . 's.index')
            );
        }
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;

            if (!$user?->can(PermissionsConstants::DEL_PERM))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);

            $permission = Permission::findOrFail($id);
            $permission->delete();

            return redirect()
                ->route(self::SINGULAR . 's.index')
                ->with('success', self::SINGULAR . ' successfully deleted.');
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::SINGULAR . 's.index')
            );
        }
    }
}
