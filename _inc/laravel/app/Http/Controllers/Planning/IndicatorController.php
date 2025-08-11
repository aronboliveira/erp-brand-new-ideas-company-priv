<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Branch, Department, Employee, Indicator, PerformanceType};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Log, Redirect, Validator};

class IndicatorController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|JsonResponse|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, PermissionsConstants::MNG_IND, ViewsConstants::IND . '.' . __FUNCTION__))
            instanceof RedirectResponse
        ) return $guard;
        try {
            $user = $request->user();
            $query = Indicator::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId());
            if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                $query = $query
                    ->where('branch', $user?->employee->branch_id)
                    ->where('department', $user?->employee->department_id);
            }
            $indicators = $query
                ->with(['branches', 'departments', 'designations', 'user'])
                ->get();
            return view(ViewsConstants::IND . '.' . __FUNCTION__, compact('indicators'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, 'create indicator', ViewsConstants::IND . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        $creatorId = $request->user()->creatorId();
        $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->get()
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        $performance = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->get();
        $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->get()
            ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
        $departments->prepend('Select Department', '');
        return view(ViewsConstants::IND . '.' . __FUNCTION__, compact(
            'branches',
            'departments',
            'performance'
        ));
    }

    public function store(Request $request): RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, 'create indicator', ViewsConstants::IND . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        $validator = Validator::make(
            $request->all(),
            ['branch' => 'required', 'department' => 'required', 'designation' => 'required']
        );
        if ($validator->fails()) return Redirect::back()
            ->with('error', $validator->errors()->first());
        try {
            $data = $request->all();
            foreach (['branch', 'department', 'designation'] as $field)
                $createData[$field] = $data[$field];
            $createData['rating'] = json_encode($data['rating'] ?? [], true);
            $createData['created_user'] = $request->user()->type === 'company'
                ? $request->user()->creatorId()
                : $request->user()->id;
            $createData[DatabaseConstants::TABLE_CREATOR] = $request->user()->creatorId();
            Indicator::create($createData);
            return Redirect::route(ViewsConstants::IND . '.index')
                ->with('success', __('Indicator successfully created.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Request $request, Indicator $indicator): View|JsonResponse|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, 'view indicator', ViewsConstants::IND . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        try {
            $ratings = json_decode($indicator->rating, true);
            $performance = PerformanceType::where(
                DatabaseConstants::TABLE_CREATOR,
                $request->user()->creatorId()
            )->get();
            return view(ViewsConstants::IND . '.show', compact(
                'indicator',
                'ratings',
                'performance'
            ));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(Request $request, Indicator $indicator): View|JsonResponse|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, 'edit indicator', ViewsConstants::IND . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        try {
            $creatorId = $request->user()->creatorId();
            $performance = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
            $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->get()
                ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->get()
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $departments->prepend('Select Department', '');
            $ratings = json_decode($indicator->rating, true);
            return view(ViewsConstants::IND . '.' . __FUNCTION__, compact(
                'branches',
                'departments',
                'performance',
                'indicator',
                'ratings'
            ));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function update(Request $request, Indicator $indicator): RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, 'edit indicator', ViewsConstants::IND . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        $validator = Validator::make(
            $request->all(),
            ['branch' => 'required', 'department' => 'required', 'designation' => 'required']
        );
        if ($validator->fails()) return Redirect::back()
            ->with('error', $validator->errors()->first());
        try {
            $data = $request->all();
            $updateData = Arr::only($data, ['branch', 'department', 'designation']);
            $updateData['rating'] = json_encode($data['rating'] ?? [], true);
            $indicator->update($updateData);
            return Redirect::route(ViewsConstants::IND . '.index')
                ->with('success', __('Indicator successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $request, Indicator $indicator): RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, 'delete indicator', ViewsConstants::IND . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        try {
            if ($indicator->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial(
                    $request,
                    new \Exception,
                    __CLASS__ . '::' . __FUNCTION__,
                    route(ViewsConstants::IND . '.index')
                );
            }
            $indicator->delete();
            return Redirect::route(ViewsConstants::IND . '.index')
                ->with('success', __('Indicator successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }
}
