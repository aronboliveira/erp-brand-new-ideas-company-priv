<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{DeductionOption, Employee, SaturationDeduction, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log, Validator, View as ViewFacade};
use Illuminate\View\View;

class SaturationDeductionController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::STR_DD . '.index';

    public const STR_DD_CR = 'saturationDeductionCreate';
    public function saturationDeductionCreate(string|int $employeeId, Request $request): RedirectResponse|View|string
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($employeeId, $request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'create saturation deduction', self::REDIRECT_INDEX)) !== true) return $redirect;

            $employee = Employee::findOrFail($employeeId);
            $options = DeductionOption::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->pluck('name', 'id');
            $types = SaturationDeduction::$saturationDeductionType;

            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()->id, UsersConstants::COL_EMP_ID => $employeeId]);

            $view = ViewsConstants::STR_DD . '.create';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::REDIRECT_INDEX));

            return ViewFacade::make($view, compact('employee', 'options', 'types'));
        }, [UsersConstants::COL_EMP_ID => $employeeId, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function show(Request $request, SaturationDeduction $saturationDeduction): View|RedirectResponse|null
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $saturationDeduction, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'view saturation deduction', self::REDIRECT_INDEX)) !== true) return $redirect;

            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $user?->id, 'deduction_id' => $saturationDeduction->id]);

            try {
                if ($saturationDeduction[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                    return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
                }

                $view = ViewsConstants::STR_DD . '.show';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::REDIRECT_INDEX));

                return ViewFacade::make($view, ['deduction' => $saturationDeduction]);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['deduction_id' => $saturationDeduction->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'create saturation deduction', self::REDIRECT_INDEX)) !== true) return $redirect;

            $rules = [
                UsersConstants::COL_EMP_ID => 'required|uuid',
                'deduction_option'         => 'required|uuid',
                'title'                    => 'required|string',
                'amount'                   => 'required|numeric',
            ];
            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                Log::debug($action . ' validation failed', $v->errors()->toArray());
                return redirect()->back()->with('error', $v->errors()->first());
            }

            DB::beginTransaction();
            try {
                $data = Arr::only($request->all(), [
                    UsersConstants::COL_EMP_ID,
                    'deduction_option',
                    'title',
                    'type',
                    'amount'
                ]) + [DatabaseConstants::COL_TABLE_CREATOR => $request->user()->creatorId()];

                $deduction = SaturationDeduction::create($data);
                Log::info($action . ' success', ['deduction_id' => $deduction->id]);

                DB::commit();
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Saturation deduction successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($action . ' error', ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($action . ' error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function edit(string|int $id, Request $request): RedirectResponse|View|string
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($id, $request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit saturation deduction', self::REDIRECT_INDEX)) !== true) return $redirect;

            $deduction = SaturationDeduction::findOrFail($id);
            if ($deduction[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            }

            $options = DeductionOption::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->pluck('name', 'id');
            $types = SaturationDeduction::$saturationDeductionType;

            Log::debug($action . ' start', ['deduction_id' => $id]);

            $view = ViewsConstants::STR_DD . '.edit';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::REDIRECT_INDEX));

            return ViewFacade::make($view, compact('deduction', 'options', 'types'));
        }, ['deduction_id' => $id]);
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit saturation deduction', self::REDIRECT_INDEX)) !== true) return $redirect;

            $deduction = SaturationDeduction::findOrFail($id);
            if ($deduction[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            }

            $rules = [
                'deduction_option' => 'required|uuid',
                'title'            => 'required|string',
                'amount'           => 'required|numeric',
            ];
            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                Log::debug($action . ' validation failed', $v->errors()->toArray());
                return redirect()->back()->with('error', $v->errors()->first());
            }

            DB::beginTransaction();
            try {
                $deduction->update(Arr::only($request->all(), ['deduction_option', 'title', 'type', 'amount']));
                Log::info($action . ' success', ['deduction_id' => $id]);
                DB::commit();
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Saturation deduction successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($action . ' error', ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($action . ' error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['deduction_id' => $id]);
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'delete saturation deduction', self::REDIRECT_INDEX)) !== true) return $redirect;

            $deduction = SaturationDeduction::findOrFail($id);
            if ($deduction[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            }

            DB::beginTransaction();
            try {
                $deduction->delete();
                Log::info($action . ' deleted', ['deduction_id' => $id]);
                DB::commit();
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Saturation deduction successfully deleted.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($action . ' error', ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($action . ' error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['deduction_id' => $id]);
    }
}
