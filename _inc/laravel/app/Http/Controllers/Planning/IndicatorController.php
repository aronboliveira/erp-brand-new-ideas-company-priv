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
use Illuminate\Support\Facades\{Log, Redirect, Validator, View as ViewFacade};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class IndicatorController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|JsonResponse|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::IND . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, PermissionsConstants::MNG_IND, ViewsConstants::IND . '.index')) instanceof RedirectResponse) return $guard;
            $user = $request->user();
            $query = Indicator::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
            if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                $query->where('branch', $user?->employee->branch_id)
                    ->where('department', $user?->employee->department_id);
            }
            $indicators = $query->with(['branches', 'departments', 'designations', 'user'])->get();
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('indicators'));
        });
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::IND . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, 'create indicator', ViewsConstants::IND . '.index')) instanceof RedirectResponse) return $guard;
            $creatorId = $request->user()->creatorId();
            $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $performance = PerformanceType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
            $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $departments->prepend('Select Department', '');
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('branches', 'departments', 'performance'));
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile(function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, 'create indicator', ViewsConstants::IND . '.index')) instanceof RedirectResponse) return $guard;
            $validator = Validator::make($request->all(), ['branch' => 'required', 'department' => 'required', 'designation' => 'required']);
            if ($validator->fails()) return Redirect::back()->with('error', $validator->errors()->first());
            $data = $request->all();
            $createData = Arr::only($data, ['branch', 'department', 'designation']);
            $createData['rating'] = json_encode($data['rating'] ?? [], true);
            $createData['created_user'] = $request->user()->type === 'company' ? $request->user()->creatorId() : $request->user()->id;
            $createData[DatabaseConstants::COL_TABLE_CREATOR] = $request->user()->creatorId();
            Indicator::create($createData);
            return Redirect::route(ViewsConstants::IND . '.index')->with('success', __('Indicator successfully created.'));
        });
    }

    public function show(Request $request, Indicator $indicator): View|JsonResponse|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::IND . '.show';

        return $this->measureProfile($action, function () use ($request, $indicator, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, 'view indicator', ViewsConstants::IND . '.index')) instanceof RedirectResponse) return $guard;
            $ratings = json_decode($indicator->rating, true);
            $performance = PerformanceType::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->get();
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('indicator', 'ratings', 'performance'));
        });
    }

    public function edit(Request $request, Indicator $indicator): View|JsonResponse|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::IND . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $indicator, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, 'edit indicator', ViewsConstants::IND . '.index')) instanceof RedirectResponse) return $guard;
            $creatorId = $request->user()->creatorId();
            $performance = PerformanceType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
            $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $departments->prepend('Select Department', '');
            $ratings = json_decode($indicator->rating, true);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('branches', 'departments', 'performance', 'indicator', 'ratings'));
        });
    }

    public function update(Request $request, Indicator $indicator): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile(function () use ($request, $indicator) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, 'edit indicator', ViewsConstants::IND . '.index')) instanceof RedirectResponse) return $guard;
            $validator = Validator::make($request->all(), ['branch' => 'required', 'department' => 'required', 'designation' => 'required']);
            if ($validator->fails()) return Redirect::back()->with('error', $validator->errors()->first());
            $data = $request->all();
            $updateData = Arr::only($data, ['branch', 'department', 'designation']);
            $updateData['rating'] = json_encode($data['rating'] ?? [], true);
            $indicator->update($updateData);
            return Redirect::route(ViewsConstants::IND . '.index')->with('success', __('Indicator successfully updated.'));
        });
    }

    public function destroy(Request $request, Indicator $indicator): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $indicator, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, 'delete indicator', ViewsConstants::IND . '.index')) instanceof RedirectResponse) return $guard;
            if ($indicator[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception, $action, route(ViewsConstants::IND . '.index'));
            }
            $indicator->delete();
            return Redirect::route(ViewsConstants::IND . '.index')->with('success', __('Indicator successfully deleted.'));
        });
    }
}
