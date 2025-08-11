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
    Log
};
use Illuminate\View\View;

class RoleController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'role';
    private const REDIRECT_ROUTE = self::SINGULAR . '.index';

    public function index(Request $request): RedirectResponse|View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard($request, PermissionsConstants::MNG_ROLE, self::REDIRECT_ROUTE))
            return $c;
        try {
            $roles = Role::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_ROLES));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE));
        }
    }

    public function create(Request $request): RedirectResponse|View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard($request, PermissionsConstants::CR_ROLE, self::REDIRECT_ROUTE))
            return $c;
        try {
            $permissions = $user->type == PermissionsConstants::SA
                ? Permission::pluck('name', 'id')->toArray()
                : $user?->roles->flatMap->permissions->pluck('name', 'id')->toArray();
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_PERMISSIONS));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE));
        }
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard($request, PermissionsConstants::CR_ROLE, self::REDIRECT_ROUTE))
            return $c;
        $request->validate([
            'name'        => 'required|max:100|unique:roles,name,NULL,id,' .
                DatabaseConstants::TABLE_CREATOR . ',' . $user?->creatorId(),
            DatabaseConstants::TABLE_PERMISSIONS => 'required|array'
        ]);
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name'       => $request->input('name'),
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
            ]);
            foreach ($request->input(DatabaseConstants::TABLE_PERMISSIONS) as $pid)
                $role->givePermissionTo(Permission::findOrFail($pid));
            DB::commit();
            return redirect()
                ->route(self::REDIRECT_ROUTE)
                ->with('success', __(ucfirst(self::SINGULAR) . ' successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE));
        }
    }

    public function edit(Request $request, Role $role): RedirectResponse|View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard(
            $request,
            PermissionsConstants::ED_ROLE,
            self::REDIRECT_ROUTE
        ))
            return $c;
        try {
            $permissions = $user->type == PermissionsConstants::SA
                ? Permission::pluck('name', 'id')->toArray()
                : $user?->roles->flatMap->permissions->pluck('name', 'id')->toArray();
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(self::SINGULAR, DatabaseConstants::TABLE_PERMISSIONS));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE));
        }
    }

    public function update(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard(
            $request,
            PermissionsConstants::ED_ROLE,
            self::REDIRECT_ROUTE
        ))
            return $c;
        $request->validate([
            'name'        => 'required|max:100|unique:roles,name,' . $role->id . ',id,' .
                DatabaseConstants::TABLE_CREATOR . ',' . $user?->creatorId(),
            DatabaseConstants::TABLE_PERMISSIONS => 'required|array'
        ]);
        DB::beginTransaction();
        try {
            $role->update(['name' => $request->input('name')]);
            $role->syncPermissions($request->input(DatabaseConstants::TABLE_PERMISSIONS));
            DB::commit();

            return redirect()
                ->route(self::REDIRECT_ROUTE)
                ->with('success', __(ucfirst(self::SINGULAR) . ' successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE));
        }
    }

    public function destroy(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if ($c = self::guard($request, PermissionsConstants::DEL_ROLE, self::REDIRECT_ROUTE))
            return $c;
        DB::beginTransaction();
        try {
            $role->delete();
            DB::commit();
            return redirect()
                ->route(self::REDIRECT_ROUTE)
                ->with('success', __(ucfirst(self::SINGULAR) . ' successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE));
        }
    }
}
