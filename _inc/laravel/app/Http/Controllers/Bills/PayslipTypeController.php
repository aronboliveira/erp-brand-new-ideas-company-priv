<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\PayslipType;
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Auth\Access\AuthorizationException;

final class PayslipTypeController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $request): Response|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $func, $action) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);

            if (($r = self::guard($request, 'manage payslip type', ViewsConstants::PY_SLP . '.index')) !== true) return $r;

            try {
                $creatorId = $request->user()?->creatorId() ?? null;
                $payslipTypes = PayslipType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();

                Log::info($action . ' fetched types', ['creator_id' => $creatorId, 'count' => $payslipTypes->count()]);

                return response()->view(ViewsConstants::PY_SLP . '.' . $func, compact('payslipTypes'));
            } catch (\Throwable $e) {
                Log::error($action . ' unexpected error', ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($action . ' unexpected error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function create(Request $request): Response|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $func, $action) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);

            if (($r = self::guard($request, 'create payslip type', ViewsConstants::PY_SLP . '.index')) !== true) return $r;

            return response()->view(ViewsConstants::PY_SLP . '.' . $func);
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'input' => $request->only('name')]);

            if (($r = self::guard($request, 'create payslip type', ViewsConstants::PY_SLP . '.index')) !== true) return $r;

            $request->validate(['name' => 'required|string|max:20']);
            Log::debug($action . ' validation passed', ['name' => $request->name]);

            try {
                DB::transaction(function () use ($request, $action) {
                    $data = [
                        'name' => $request->name,
                        DatabaseConstants::COL_TABLE_CREATOR => $request->user()?->creatorId() ?? null,
                    ];
                    Log::info($action . ' creating PayslipType', ['data' => $data]);
                    $type = PayslipType::create($data);
                    Log::info($action . ' created', ['type_id' => $type->id ?? null]);
                });

                return redirect()->route(ViewsConstants::PY_SLP . '.index')->with('success', __('Payslip type successfully created.'));
            } catch (\Throwable $e) {
                Log::error($action . ' creation failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function edit(PayslipType $payslipType, Request $request): Response|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($payslipType, $request, $func, $action) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'type_id' => $payslipType->id ?? null]);

            if (($r = self::guard($request, 'edit payslip type', ViewsConstants::PY_SLP . '.index')) !== true) return $r;

            if (!self::isOwner($payslipType)) {
                Log::warning($action . ' not owner', ['type_id' => $payslipType->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), $action);
            }

            return response()->view(ViewsConstants::PY_SLP . '.' . $func, compact('payslipType'));
        }, ['type_id' => $payslipType->id ?? null]);
    }

    public function update(Request $request, PayslipType $payslipType): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $payslipType, $action) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'type_id' => $payslipType->id ?? null, 'input' => $request->only('name')]);

            if (($r = self::guard($request, 'edit payslip type', ViewsConstants::PY_SLP . '.index')) !== true) return $r;

            if (!self::isOwner($payslipType)) {
                Log::warning($action . ' not owner', ['type_id' => $payslipType->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), $action);
            }

            $request->validate(['name' => 'required|string|max:20']);
            Log::debug($action . ' validation passed', ['name' => $request->name]);

            try {
                DB::transaction(function () use ($request, $payslipType, $action) {
                    Log::info($action . ' updating PayslipType', ['type_id' => $payslipType->id ?? null, 'new_name' => $request->name]);
                    $payslipType->update(['name' => $request->name]);
                    Log::info($action . ' updated', ['type_id' => $payslipType->id ?? null]);
                });

                return redirect()->route(ViewsConstants::PY_SLP . '.index')->with('success', __('Payslip type successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' update failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['type_id' => $payslipType->id ?? null]);
    }

    public function destroy(PayslipType $payslipType, Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($payslipType, $request, $action) {
            Log::debug($action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'type_id' => $payslipType->id ?? null]);

            if (($r = self::guard($request, 'delete payslip type', ViewsConstants::PY_SLP . '.index')) !== true) return $r;

            if (!self::isOwner($payslipType)) {
                Log::warning($action . ' not owner', ['type_id' => $payslipType->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), $action);
            }

            try {
                DB::transaction(function () use ($payslipType, $action) {
                    Log::info($action . ' deleting PayslipType', ['type_id' => $payslipType->id ?? null]);
                    $payslipType->delete();
                    Log::info($action . ' deleted', ['type_id' => $payslipType->id ?? null]);
                });

                return redirect()->route(ViewsConstants::PY_SLP . '.index')->with('success', __('Payslip type successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error($action . ' delete failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['type_id' => $payslipType->id ?? null]);
    }


    /*──── legacy stub ────*/
    public function show(): RedirectResponse
    {
        Log::info(__METHOD__ . ' redirecting to index');
        return redirect()->route(ViewsConstants::PY_SLP . '.index');
    }

    private static function deny(Request $request, string $permission): RedirectResponse|JsonResponse|null
    {
        $user = $request->user();
        Log::info(__METHOD__ . ' checking permission', [
            UsersConstants::COL_USER_ID => $user?->id,
            'permission' => $permission
        ]);
        if ($user?->can($permission)) {
            Log::info(__METHOD__ . ' permission granted', [
                UsersConstants::COL_USER_ID => $user?->id,
                'permission' => $permission
            ]);
            return null;
        }
        Log::warning(__METHOD__ . ' permission denied', [
            UsersConstants::COL_USER_ID => $user?->id,
            'permission' => $permission
        ]);
        return defaultPermissionDenial(
            $request,
            new AuthorizationException($permission),
            __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
        );
    }

    private static function isOwner(PayslipType $type): bool|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $isOwner = $type->created_by === $user?->creatorId();
        Log::info(__METHOD__ . ' ownership check', [
            'type_id'   => $type->id,
            DatabaseConstants::COL_TABLE_CREATOR => $type->created_by,
            'current'   => $user?->creatorId(),
            'is_owner'  => $isOwner
        ]);
        return $isOwner;
    }
}
