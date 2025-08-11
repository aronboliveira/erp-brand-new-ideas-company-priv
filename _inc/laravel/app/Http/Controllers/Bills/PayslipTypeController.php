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
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Auth\Access\AuthorizationException;

final class PayslipTypeController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $request): Response|RedirectResponse|JsonResponse
    {
        Log::info(__METHOD__ . ' start', [UsersConstants::COL_USER_ID => Auth::id()]);
        if ($r = self::deny($request, 'manage payslip type'))
            return $r;
        try {
            $creatorId   = $request->user()->creatorId();
            $payslipTypes = PayslipType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
            Log::info(__METHOD__ . ' fetched types', [
                'creator_id' => $creatorId,
                'count'      => $payslipTypes->count()
            ]);
            return response()->view(ViewsConstants::PY_SLP . '.' . __FUNCTION__, compact('payslipTypes'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . ' unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function create(Request $request): Response|RedirectResponse|JsonResponse
    {
        Log::info(__METHOD__ . ' start', [UsersConstants::COL_USER_ID => Auth::id()]);
        if ($r = self::deny($request, 'create payslip type'))
            return $r;
        return response()->view(ViewsConstants::PY_SLP . '.' . __FUNCTION__);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(),
            'input'   => $request->only('name')
        ]);
        if ($r = self::deny($request, 'create payslip type'))
            return $r;
        $request->validate(['name' => 'required|string|max:20']);
        Log::info(__METHOD__ . ' validation passed', ['name' => $request->name]);
        DB::beginTransaction();
        try {
            $data = [
                'name'       => $request->name,
                DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId(),
            ];
            Log::info(__METHOD__ . ' creating PayslipType', ['data' => $data]);
            $type = PayslipType::create($data);
            DB::commit();
            Log::info(__METHOD__ . ' created', ['type_id' => $type->id]);
            return redirect()->route(ViewsConstants::PY_SLP . '.index')
                ->with('success', __('Payslip type successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' creation failed', [
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function edit(PayslipType $payslipType, Request $request): Response|RedirectResponse|JsonResponse
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(), 'type_id' => $payslipType->id
        ]);
        if ($r = self::deny($request, 'edit payslip type'))
            return $r;
        if (!self::isOwner($payslipType)) {
            Log::warning(__METHOD__ . ' not owner', ['type_id' => $payslipType->id]);
            return defaultPermissionDenial(
                $request,
                new AuthorizationException('owner'),
                __METHOD__
            );
        }
        return response()->view(ViewsConstants::PY_SLP . '.' . __FUNCTION__, compact('payslipType'));
    }

    public function update(Request $request, PayslipType $payslipType): RedirectResponse|JsonResponse
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(),
            'type_id' => $payslipType->id,
            'input'   => $request->only('name')
        ]);
        if ($r = self::deny($request, 'edit payslip type'))
            return $r;
        if (!self::isOwner($payslipType)) {
            Log::warning(__METHOD__ . ' not owner', ['type_id' => $payslipType->id]);
            return defaultPermissionDenial(
                $request,
                new AuthorizationException('owner'),
                __METHOD__
            );
        }
        $request->validate(['name' => 'required|string|max:20']);
        Log::info(__METHOD__ . ' validation passed', ['name' => $request->name]);
        DB::beginTransaction();
        try {
            Log::info(__METHOD__ . ' updating PayslipType', [
                'type_id' => $payslipType->id,
                'new_name' => $request->name
            ]);
            $payslipType->update(['name' => $request->name]);
            DB::commit();
            Log::info(__METHOD__ . ' updated', ['type_id' => $payslipType->id]);
            return redirect()->route(ViewsConstants::PY_SLP . '.index')
                ->with('success', __('Payslip type successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' update failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function destroy(PayslipType $payslipType, Request $request): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(), 'type_id' => $payslipType->id
        ]);
        if ($r = self::deny($request, 'delete payslip type')) {
            return $r;
        }
        if (!self::isOwner($payslipType)) {
            Log::warning(__METHOD__ . ' not owner', ['type_id' => $payslipType->id]);
            return defaultPermissionDenial(
                $request,
                new AuthorizationException('owner'),
                __METHOD__
            );
        }

        DB::beginTransaction();
        try {
            Log::info(__METHOD__ . ' deleting PayslipType', ['type_id' => $payslipType->id]);
            $payslipType->delete();
            DB::commit();
            Log::info(__METHOD__ . ' deleted', ['type_id' => $payslipType->id]);
            return redirect()->route(ViewsConstants::PY_SLP . '.index')
                ->with('success', __('Payslip type successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' delete failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __METHOD__);
        }
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
            UsersConstants::COL_USER_ID => $user?->id, 'permission' => $permission
        ]);
        if ($user?->can($permission)) {
            Log::info(__METHOD__ . ' permission granted', [
                UsersConstants::COL_USER_ID => $user?->id, 'permission' => $permission
            ]);
            return null;
        }
        Log::warning(__METHOD__ . ' permission denied', [
            UsersConstants::COL_USER_ID => $user?->id, 'permission' => $permission
        ]);
        return defaultPermissionDenial(
            $request,
            new AuthorizationException($permission),
            __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
        );
    }

    private static function isOwner(PayslipType $type): bool
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
            DatabaseConstants::TABLE_CREATOR => $type->created_by,
            'current'   => $user?->creatorId(),
            'is_owner'  => $isOwner
        ]);
        return $isOwner;
    }
}
