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
use App\Models\AllowanceOption;
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Throwable;

final class AllowanceOptionController extends Controller
{
  use ChecksLogin;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH]);
  }

  public function index(Request $req): View|RedirectResponse|JsonResponse|null
  {
    Log::info(__METHOD__ . " start", [UsersConstants::COL_USER_ID => Auth::id()]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($r = self::authorizePerm($req, 'manage allowance option')) return $r;
    try {
      $creatorId = $req->user()->creatorId();
      $options  = AllowanceOption::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      Log::info(__METHOD__ . " fetched options", ['count' => $options->count()]);
      return view(ViewsConstants::ALW_OPT . '.' . __FUNCTION__, compact('options'));
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function create(Request $req): View|JsonResponse|null
  {
    Log::info(__METHOD__ . " start", [UsersConstants::COL_USER_ID => Auth::id()]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($r = self::authorizePerm($req, 'create allowance option')) return $r;
    return view(ViewsConstants::ALW_OPT . '.' . __FUNCTION__);
  }

  public function store(Request $req): RedirectResponse|JsonResponse|null
  {
    Log::info(__METHOD__ . " start", [UsersConstants::COL_USER_ID => Auth::id()]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($r = self::authorizePerm($req, 'create allowance option')) return $r;
    if ($r = self::validateName($req)) return $r;
    try {
      DB::transaction(function () use ($req) {
        $opt = AllowanceOption::create([
          'name'       => $req->name,
          DatabaseConstants::TABLE_CREATOR => $req->user()->creatorId()
        ]);
        Log::info(__METHOD__ . " created", ['id' => $opt->id, 'name' => $opt->name]);
      });
      return redirect()
        ->route(ViewsConstants::ALW_OPT . '.index')
        ->with('success', __('AllowanceOption successfully created.'));
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function show(Request $req, AllowanceOption $allowanceOption): RedirectResponse
  {
    Log::info(__METHOD__ . " redirecting", [UsersConstants::COL_USER_ID => Auth::id()]);
    return redirect()->route(ViewsConstants::ALW_OPT . '.index');
  }

  public function edit(Request $req, AllowanceOption $opt): View|JsonResponse|null
  {
    Log::info(__METHOD__ . " start", [UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $opt->id]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($r = self::authorizePerm($req, 'edit allowance option')) return $r;
    if ($r = $this->authorizeOwnership($req, $opt)) return $r;
    return view(ViewsConstants::ALW_OPT . '.edit', ['allowanceOption' => $opt]);
  }

  public function update(Request $req, AllowanceOption $opt): RedirectResponse|JsonResponse|null
  {
    Log::info(__METHOD__ . " start", [UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $opt->id]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($r = self::authorizePerm($req, 'edit allowance option')) return $r;
    if ($r = $this->authorizeOwnership($req, $opt)) return $r;
    if ($r = self::validateName($req)) return $r;

    try {
      DB::transaction(function () use ($req, $opt) {
        $lock = AllowanceOption::where('id', $opt->id)
          ->lockForUpdate()
          ->firstOrFail();
        $old = $lock->name;
        $lock->update(['name' => $req->name]);
        Log::info(__METHOD__ . " updated", ['id' => $lock->id, 'from' => $old, 'to' => $req->name]);
      });
      return redirect()
        ->route(ViewsConstants::ALW_OPT . '.index')
        ->with('success', __('AllowanceOption successfully updated.'));
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function destroy(Request $req, AllowanceOption $opt): RedirectResponse|JsonResponse|null
  {
    Log::info(__METHOD__ . " start", [UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $opt->id]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($r = self::authorizePerm($req, 'delete allowance option')) return $r;
    if ($r = $this->authorizeOwnership($req, $opt)) return $r;

    try {
      DB::transaction(function () use ($opt) {
        $lock = AllowanceOption::where('id', $opt->id)
          ->lockForUpdate()
          ->firstOrFail();
        $lock->delete();
        Log::info(__METHOD__ . " deleted", ['id' => $opt->id]);
      });
      return redirect()
        ->route(ViewsConstants::ALW_OPT . '.index')
        ->with('success', __('AllowanceOption successfully deleted.'));
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  private static function authorizePerm(Request $req, string $perm): RedirectResponse|JsonResponse|null
  {
    $user = $req->user();
    if ($user?->can($perm)) {
      Log::info(__METHOD__ . " permission granted", [UsersConstants::COL_USER_ID => $user?->id, 'perm' => $perm]);
      return null;
    }
    Log::warning(__METHOD__ . " permission denied", [UsersConstants::COL_USER_ID => $user?->id, 'perm' => $perm]);
    return defaultPermissionDenial(
      $req,
      new AuthorizationException($perm),
      __CLASS__ . '::' . __FUNCTION__
    );
  }

  private static function validateName(Request $req): RedirectResponse|null
  {
    $v = Validator::make($req->all(), ['name' => 'required|string|max:20']);
    if ($v->fails()) {
      $errors = $v->errors()->all();
      Log::warning(__METHOD__ . " validation failed", ['errors' => $errors]);
      return redirect()->back()->with('error', $v->errors()->first());
    }
    Log::info(__METHOD__ . " validation passed", ['name' => $req->name]);
    return null;
  }

  private static function handleException(Request $req, Throwable $e): RedirectResponse|JsonResponse|null
  {
    Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . " exception", [
      'message' => $e->getMessage(),
    ]);
    Log::error(__METHOD__ . " exception", [
      'message' => $e->getMessage(),
      'trace'   => $e->getTraceAsString()
    ]);
    return defaultUndefinedException(
      $req,
      $e,
      __CLASS__ . '::' . __FUNCTION__
    );
  }

  private function authorizeOwnership(Request $req, AllowanceOption $opt): RedirectResponse|null
  {
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::warning(__METHOD__ . " not logged in");
      return $userOrRedirect;
    }
    $user = $userOrRedirect;
    if ($opt->created_by !== $user?->creatorId()) {
      Log::warning(__METHOD__ . " ownership denied", [UsersConstants::COL_USER_ID => $user?->id, 'opt_id' => $opt->id]);
      return defaultPermissionDenial(
        $req,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    Log::info(__METHOD__ . " ownership granted", [UsersConstants::COL_USER_ID => $user?->id, 'opt_id' => $opt->id]);
    return null;
  }
}


// ! ALERT index() returns all records without pagination; add pagination or server‑side filtering if list size may grow.