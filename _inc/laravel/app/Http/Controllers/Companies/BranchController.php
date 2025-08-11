<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Traits\ChecksLogin;
use App\Models\{Branch, Department, Employee};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;

class BranchController extends Controller
{
    use ChecksLogin;

    public function index(Request $request): RedirectResponse|View
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$user?->can('manage branch'))
            return defaultPermissionDenial($request, null, __METHOD__);
        try {
            $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(ViewsConstants::BRC . '.' . __FUNCTION__, compact('branches'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function create(Request $request): RedirectResponse|View
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$user?->can('create branch'))
            return defaultPermissionDenial($request, null, __METHOD__);
        return view(ViewsConstants::BRC . '.' . __FUNCTION__);
    }

    public function store(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$user?->can('create branch'))
            return defaultPermissionDenial($request, null, __METHOD__);
        $v = validator($request->all(), ['name' => 'required']);
        if ($v->fails())
            return redirect()->back()->with('error', $v->errors()->first());
        try {
            Branch::create([
                CompaniesConstants::COL_BRC_NM       => $request->input('name'),
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);
            return redirect()->route(ViewsConstants::BRC . '.index')
                ->with('success', __('Branch successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function show(Branch $branch): RedirectResponse
    {
        return redirect()->route(ViewsConstants::BRC . '.index');
    }

    public function edit(Request $request, Branch $branch): RedirectResponse|View
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;

        if (!$user?->can('edit branch'))
            return defaultPermissionDenial($request, null, __METHOD__);
        if ($branch[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial($request, null, __METHOD__);
        return view(ViewsConstants::BRC . '.' . __FUNCTION__, compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$user?->can('edit branch'))
            return defaultPermissionDenial($request, null, __METHOD__);
        if ($branch[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial($request, null, __METHOD__);
        $v = validator($request->all(), [CompaniesConstants::COL_BRC_NM => 'required']);
        if ($v->fails())
            return redirect()->back()->with('error', $v->errors()->first());
        try {
            $branch->update([CompaniesConstants::COL_BRC_NM => $request->input('name')]);
            return redirect()->route(ViewsConstants::BRC . '.index')
                ->with('success', __('Branch successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function destroy(Request $request, Branch $branch): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$user?->can('delete branch'))
            return defaultPermissionDenial($request, null, __METHOD__);
        if ($branch[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial($request, null, __METHOD__);
        try {
            $branch->delete();
            return redirect()->route(ViewsConstants::BRC . '.index')
                ->with('success', __('Branch successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function getDepartment(Request $request): JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json([], 401);
        $branchId = (int) $request->input(CompaniesConstants::COL_BRC_ID, 0);
        $departments = $branchId === 0
            ? Department::pluck(CompaniesConstants::COL_DEP_NM, 'id')->toArray()
            : Department::where(CompaniesConstants::COL_BRC_ID, $branchId)
            ->pluck(CompaniesConstants::COL_DEP_NM, 'id')->toArray();
        return response()->json($departments);
    }

    public function getEmployee(Request $request): JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json([], 401);
        $deptIds = $request->input(CompaniesConstants::COL_DEP_ID, []);
        if (!is_array($deptIds)) $deptIds = [];
        $employees = in_array(0, $deptIds)
            ? Employee::pluck(UsersConstants::COL_NM, 'id')->toArray()
            : Employee::whereIn(CompaniesConstants::COL_DEP_ID, $deptIds)
            ->pluck(UsersConstants::COL_NM, 'id')->toArray();
        return response()->json($employees);
    }
}
