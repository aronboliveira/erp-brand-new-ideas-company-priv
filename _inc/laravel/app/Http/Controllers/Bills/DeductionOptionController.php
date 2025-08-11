<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Http\Controllers\Controller;
use App\Models\DeductionOption;
use App\Traits\ChecksLogin;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log, Validator};
use Illuminate\View\View;

final class DeductionOptionController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $req): View|RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [UsersConstants::COL_USER_ID => Auth::id()]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($r = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(__METHOD__ . ' not logged in', [UsersConstants::COL_USER_ID => Auth::id()]);
            return $r;
        }
        if ($r = self::setAuth($req, 'manage deduction option'))
            return $r;
        try {
            $userId = $user?->creatorId();
            $opts  = DeductionOption::where(DatabaseConstants::TABLE_CREATOR, $userId)->get();
            Log::info(__METHOD__ . ' fetched', [
                'creator_id' => $userId, 'count' => $opts->count()
            ]);
            return view(ViewsConstants::DDT_OPT . '.index', compact('opts'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . ' failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __METHOD__
            );
        }
    }

    public function create(Request $req): View|RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [UsersConstants::COL_USER_ID => Auth::id()]);
        if ($r = self::setAuth($req, 'create deduction option'))
            return $r;
        return view(ViewsConstants::DDT_OPT . '.create');
    }

    public function store(Request $req): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(), 'input' => $req->all()
        ]);
        if ($r = self::setAuth($req, 'create deduction option')) {
            return $r;
        }
        if ($r = self::validateName($req)) {
            return $r;
        }
        try {
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) {
                return $ur;
            }
            $userId = $ur->creatorId();
            $opt   = DeductionOption::create([
                'name'       => $req->name,
                DatabaseConstants::TABLE_CREATOR => $userId
            ]);
            Log::info(__METHOD__ . ' created', [
                'id' => $opt->id,
                'name' => $opt->name,
                DatabaseConstants::TABLE_CREATOR => $userId
            ]);
            return redirect()->route(ViewsConstants::DDT_OPT . '.index')
                ->with('success', __('DeductionOption successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                'error' => $e->getMessage(),
                'input' => $req->all()
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __METHOD__
            );
        }
    }

    public function show(): RedirectResponse
    {
        Log::info(__METHOD__ . ' redirect', [UsersConstants::COL_USER_ID => Auth::id()]);
        return redirect()->route(ViewsConstants::DDT_OPT . '.index');
    }

    public function edit(Request $req, int|string $id): View|RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(), 'id' => $id
        ]);
        if ($r = self::setAuth($req, 'edit deduction option')) {
            return $r;
        }
        $opt = DeductionOption::find($id);
        if (!$opt) {
            Log::warning(__METHOD__ . ' not found', ['id' => $id]);
            return defaultUndefinedException(
                $req,
                new \RuntimeException('Not found'),
                __METHOD__
            );
        }
        if ((self::_checkLogin() ?? null)->creatorId() !== $opt->created_by) {
            Log::warning(__METHOD__ . ' unauthorized', [
                UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $id
            ]);
            return defaultPermissionDenial(
                $req,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __METHOD__
            );
        }
        Log::info(__METHOD__ . ' editing', ['opt_id' => $id]);
        return view(ViewsConstants::DDT_OPT . '.edit', compact('opt'));
    }

    public function update(
        Request $req,
        DeductionOption $deductionOption
    ): RedirectResponse {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $deductionOption->id
        ]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($r = self::setAuth($req, 'edit deduction option')) {
            return $r;
        }
        if ($deductionOption->created_by !== $user?->creatorId()) {
            Log::warning(__METHOD__ . ' unauthorized', [
                UsersConstants::COL_USER_ID => Auth::id(),
                'opt_id'  => $deductionOption->id
            ]);
            return defaultPermissionDenial(
                $req,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __METHOD__
            );
        }
        if ($r = self::validateName($req)) {
            return $r;
        }
        try {
            $old = $deductionOption->name;
            $deductionOption->update(['name' => $req->name]);
            Log::info(__METHOD__ . ' updated', [
                'opt_id' => $deductionOption->id,
                'from'   => $old,
                'to'     => $req->name
            ]);
            return redirect()->route(ViewsConstants::DDT_OPT . '.index')
                ->with('success', __('DeductionOption successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                'error' => $e->getMessage(),
                'input' => $req->all()
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __METHOD__
            );
        }
    }

    public function destroy(
        Request $req,
        DeductionOption $deductionOption
    ): RedirectResponse {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $deductionOption->id
        ]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($r = self::setAuth($req, 'delete deduction option')) {
            return $r;
        }
        if ($deductionOption->created_by !== $user?->creatorId()) {
            Log::warning(__METHOD__ . ' unauthorized', [
                UsersConstants::COL_USER_ID => Auth::id(),
                'opt_id'  => $deductionOption->id
            ]);
            return defaultPermissionDenial(
                $req,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __METHOD__
            );
        }
        try {
            $deductionOption->delete();
            Log::info(__METHOD__ . ' deleted', [
                'opt_id' => $deductionOption->id
            ]);
            return redirect()->route(ViewsConstants::DDT_OPT . '.index')
                ->with('success', __('DeductionOption successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                'error' => $e->getMessage(),
                'opt_id' => $deductionOption->id
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __METHOD__
            );
        }
    }

    protected static function setAuth(Request $req, string $perm): RedirectResponse|null
    {
        $user = $req->user();
        if ($user?->can($perm)) {
            Log::info(__METHOD__ . ' permission granted', [
                UsersConstants::COL_USER_ID => $user?->id, 'perm' => $perm
            ]);
            return null;
        }
        Log::warning(__METHOD__ . ' permission denied', [
            UsersConstants::COL_USER_ID => $user?->id, 'perm' => $perm
        ]);
        return defaultPermissionDenial(
            $req,
            new \Illuminate\Auth\Access\AuthorizationException($perm),
            __METHOD__,
            route(ViewsConstants::DDT_OPT . '.index')
        );
    }

    protected static function validateName(Request $req): RedirectResponse|null
    {
        $v = Validator::make($req->all(), ['name' => 'required|string']);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', [
                'errors' => $v->errors()->all()
            ]);
            return redirect()->back()
                ->with('error', $v->errors()->first());
        }
        Log::info(__METHOD__ . ' validation passed', [
            'name' => $req->name
        ]);
        return null;
    }
}
