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
use Illuminate\Support\Facades\{DB, Log, Validator};
use Illuminate\View\View;

class SaturationDeductionController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::STR_DD . '.index';

    /**
     * Show the form to create a new saturation deduction for an employee.
     */
    public const STR_DD_CR = 'saturationDeductionCreate';
    public function saturationDeductionCreate(string|int $employeeId, Request $request): RedirectResponse|View|string
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'create saturation deduction', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $employee = Employee::findOrFail($employeeId);
        $options = DeductionOption::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
            ->pluck('name', 'id');
        $types   = SaturationDeduction::$saturationDeductionType;
        Log::info(__METHOD__, [
            UsersConstants::COL_USER_ID     => $request->user()->id,
            UsersConstants::COL_EMP_ID => $employeeId,
        ]);
        return view(ViewsConstants::STR_DD . '.create', compact('employee', 'options', 'types'));
    }

    public function show(Request $request, SaturationDeduction $saturationDeduction): \Illuminate\View\View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $redirect = self::guard(
                $request,
                'view saturation deduction',
                self::REDIRECT_INDEX
            )
        ) return $redirect;
        Log::info(__METHOD__ . ' started', [
            UsersConstants::COL_USER_ID      => $user?->id,
            'deduction_id' => $saturationDeduction->id,
        ]);
        try {
            if (
                $saturationDeduction[DatabaseConstants::TABLE_CREATOR]
                !== $user?->creatorId()
            ) return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
            Log::info(__METHOD__ . ' authorized', [
                'deduction_id' => $saturationDeduction->id,
            ]);
            return view(
                ViewsConstants::STR_DD . '.show',
                ['deduction' => $saturationDeduction]
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                'error' => $e->getMessage(),
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    /**
     * Persist a new SaturationDeduction.
     */
    public function store(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'create saturation deduction', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $rules = [
            UsersConstants::COL_EMP_ID      => 'required|uuid',
            'deduction_option' => 'required|uuid',
            'title'            => 'required|string',
            'amount'           => 'required|numeric',
        ];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', $v->errors()->toArray());
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
            ]) + [DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId()];
            $deduction = SaturationDeduction::create($data);
            Log::info(__METHOD__ . ' success', ['deduction_id' => $deduction->id]);
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Saturation deduction successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' error', [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . ' error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    /**
     * Show form to edit an existing SaturationDeduction.
     */
    public function edit(string|int $id, Request $request): RedirectResponse|View|string
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'edit saturation deduction', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $deduction = SaturationDeduction::findOrFail($id);
        if ($deduction[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );

        $options = DeductionOption::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
            ->pluck('name', 'id');
        $types  = SaturationDeduction::$saturationDeductionType;

        Log::info(__METHOD__, ['deduction_id' => $id]);

        return view(ViewsConstants::STR_DD . '.edit', compact(
            'deduction',
            'options',
            'types'
        ));
    }

    /**
     * Update an existing SaturationDeduction.
     */
    public function update(Request $request, string|int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'edit saturation deduction', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $deduction = SaturationDeduction::findOrFail($id);
        if ($deduction[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        $rules = [
            'deduction_option' => 'required|uuid',
            'title'            => 'required|string',
            'amount'           => 'required|numeric',
        ];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', $v->errors()->toArray());
            return redirect()->back()->with('error', $v->errors()->first());
        }
        DB::beginTransaction();
        try {
            $deduction->update(
                Arr::only($request->all(), [
                    'deduction_option',
                    'title',
                    'type',
                    'amount'
                ])
            );
            Log::info(__METHOD__ . ' success', ['deduction_id' => $id]);
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Saturation deduction successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' error', [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . ' error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    /**
     * Delete a SaturationDeduction.
     */
    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'delete saturation deduction', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $deduction = SaturationDeduction::findOrFail($id);
        if ($deduction[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        DB::beginTransaction();
        try {
            $deduction->delete();
            // TODO: ! ALERT: cascade-clean any related records if needed
            Log::info(__METHOD__ . ' deleted', ['deduction_id' => $id]);
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Saturation deduction successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' error', [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . ' error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
