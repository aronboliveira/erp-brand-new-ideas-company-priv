<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\LoanOption;
use App\Traits\ChecksLogin;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Log};

final class LoanOptionController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $request): \Illuminate\View\View|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $request->user()->id,
        ]);
        if ($r = self::deny($request, 'manage loan option'))
            return $r;
        try {
            $loanOptions = LoanOption::where(
                DatabaseConstants::TABLE_CREATOR,
                $request->user()->creatorId()
            )->get();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', [
                'count'   => $loanOptions->count(),
                UsersConstants::COL_USER_ID => $request->user()->id,
            ]);
            return view(ViewsConstants::LN_OPT . '.' . __FUNCTION__, compact('loanOptions'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error'   => $e->getMessage(),
                UsersConstants::COL_USER_ID => $request->user()->id,
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request): \Illuminate\View\View|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $request->user()->id,
        ]);
        if ($r = self::deny($request, 'create loan option'))
            return $r;
        return view(ViewsConstants::LN_OPT . '.' . __FUNCTION__);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $request->user()->id,
            'input'   => $request->only('name'),
        ]);
        if ($r = self::deny($request, 'create loan option'))
            return $r;
        $request->validate(['name' => 'required|string|max:20']);
        try {
            DB::transaction(function () use ($request) {
                $opt = LoanOption::create([
                    'name'       => $request->name,
                    DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId(),
                ]);
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' created', [
                    'loan_option_id' => $opt->id,
                    UsersConstants::COL_USER_ID        => $request->user()->id,
                ]);
            });
            return redirect()->route(ViewsConstants::LN_OPT . '.index')
                ->with('success', __('Loan option successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error'   => $e->getMessage(),
                UsersConstants::COL_USER_ID => $request->user()->id,
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(LoanOption $loanOption, Request $request): \Illuminate\View\View|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID         => $request->user()->id,
            'loan_option_id'  => $loanOption->id,
        ]);
        if ($r = self::deny($request, 'edit loan option'))
            return $r;
        if (!self::isOwner($loanOption)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' forbidden', [
                'loan_option_id' => $loanOption->id,
                UsersConstants::COL_USER_ID        => $request->user()->id,
            ]);
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        return view(ViewsConstants::LN_OPT . '.' . __FUNCTION__, compact('loanOption'));
    }

    public function update(Request $request, LoanOption $loanOption): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID        => $request->user()->id,
            'loan_option_id' => $loanOption->id,
            'input'          => $request->only('name'),
        ]);
        if ($r = self::deny($request, 'edit loan option')) {
            return $r;
        }
        if (!self::isOwner($loanOption)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' forbidden', [
                'loan_option_id' => $loanOption->id,
                UsersConstants::COL_USER_ID        => $request->user()->id,
            ]);
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        $request->validate(['name' => 'required|string|max:20']);
        try {
            DB::transaction(function () use ($request, $loanOption) {
                $loanOption->update(['name' => $request->name]);
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' updated', [
                    'loan_option_id' => $loanOption->id,
                    UsersConstants::COL_USER_ID        => $request->user()->id,
                ]);
            });
            return redirect()->route(ViewsConstants::LN_OPT . '.index')
                ->with('success', __('Loan option successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error'           => $e->getMessage(),
                'loan_option_id'  => $loanOption->id,
                UsersConstants::COL_USER_ID         => $request->user()->id,
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(LoanOption $loanOption, Request $request): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID        => $request->user()->id,
            'loan_option_id' => $loanOption->id,
        ]);
        if ($r = self::deny($request, 'delete loan option'))
            return $r;
        if (!self::isOwner($loanOption)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' forbidden', [
                'loan_option_id' => $loanOption->id,
                UsersConstants::COL_USER_ID        => $request->user()->id,
            ]);
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        try {
            DB::transaction(function () use ($loanOption, $request) {
                $loanOption->delete();
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' deleted', [
                    'loan_option_id' => $loanOption->id,
                    UsersConstants::COL_USER_ID        => $request->user()->id,
                ]);
            });
            return redirect()->route(ViewsConstants::LN_OPT . '.index')
                ->with('success', __('Loan option successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error'           => $e->getMessage(),
                'loan_option_id'  => $loanOption->id,
                UsersConstants::COL_USER_ID         => $request->user()->id,
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(): RedirectResponse
    {
        return redirect()->route(ViewsConstants::LN_OPT . '.index');
    }

    private static function deny(Request $request, string $permission): RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
            UsersConstants::COL_USER_ID    => $request->user()->id,
            'permission' => $permission,
        ]);
        if (!$request->user()->can($permission)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [
                UsersConstants::COL_USER_ID    => $request->user()->id,
                'permission' => $permission,
            ]);
            return defaultPermissionDenial(
                $request,
                new \Illuminate\Auth\Access\AuthorizationException($permission),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' granted', [
            UsersConstants::COL_USER_ID    => $request->user()->id,
            'permission' => $permission,
        ]);
        return null;
    }

    private static function isOwner(LoanOption $opt): bool
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $opt[DatabaseConstants::TABLE_CREATOR] === $user?->creatorId();
    }
}
