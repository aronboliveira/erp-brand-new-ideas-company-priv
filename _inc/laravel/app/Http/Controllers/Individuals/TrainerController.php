<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Branch, Trainer};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    DB,
    Log,
    Validator
};

class TrainerController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::TNR . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::MNG_TNR,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $creatorId = $user?->creatorId();
            $trainers = Trainer::with('branches')
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->get();
            Log::debug("$action fetched", ['count' => $trainers->count()]);
            return view(ViewsConstants::TNR . '.' . __FUNCTION__, compact('trainers'));
        } catch (\Throwable $e) {
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::error("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create trainer',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        Log::info("$action called", [UsersConstants::COL_USER_ID => $user?->id]);
        $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        return view(ViewsConstants::TNR . '.' . __FUNCTION__, compact('branches'));
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create trainer',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        Log::info("$action called", ['input' => $request->all()]);
        $rules = [
            'branch'    => 'required|exists:branches,id',
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'contact'   => 'required|string',
            'email'     => 'required|email'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->first();
            Log::warning("$action validation failed", ['message' => $msg]);
            return redirect()
                ->back()
                ->with('error', $msg);
        }

        try {
            DB::transaction(fn () => Trainer::create([
                'branch'     => $request->branch,
                'first_name'  => $request->first_name,
                'last_name'   => $request->lastname,
                'contact'    => $request->contact,
                'email'      => $request->email,
                'address'    => $request->address,
                'expertise'  => $request->expertise,
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
            ]));
            Log::info("$action committed");
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Trainer successfully created.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function show(Request $request, Trainer $trainer): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'view trainer',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        Log::info("$action called", ['trainer_id' => $trainer->id]);
        return view(ViewsConstants::TNR . '.show', compact('trainer'));
    }

    public function edit(Request $request, Trainer $trainer): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit trainer',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", ['trainer_id' => $trainer->id]);
        $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        return view(ViewsConstants::TNR . '.' . __FUNCTION__, compact('branches', 'trainer'));
    }

    public function update(Request $request, Trainer $trainer): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit trainer',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", [
            'trainer_id' => $trainer->id,
            'input'     => $request->all()
        ]);
        $rules = [
            'branch'    => 'required|exists:branches,id',
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'contact'   => 'required|string',
            'email'     => 'required|email'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->first();
            Log::warning("$action validation failed", ['message' => $msg]);
            return redirect()
                ->back()
                ->with('error', $msg);
        }
        try {
            DB::transaction(fn () => $trainer->update([
                'branch'    => $request->branch,
                'first_name' => $request->first_name,
                'last_name'  => $request->lastname,
                'contact'   => $request->contact,
                'email'     => $request->email,
                'address'   => $request->address,
                'expertise' => $request->expertise
            ]));
            Log::info("$action committed", ['trainer_id' => $trainer->id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Trainer successfully updated.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, Trainer $trainer): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'delete trainer',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        Log::info("$action called", ['trainer_id' => $trainer->id]);
        try {
            DB::transaction(fn () => $trainer->delete());
            Log::info("$action committed", ['trainer_id' => $trainer->id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Trainer successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
