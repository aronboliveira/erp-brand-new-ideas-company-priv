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
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, Route, View as ViewFacade};
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
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $viewPath = ViewsConstants::ALW_OPT . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
      Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id(), 'method' => $method]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($r = self::authorizePerm($req, 'manage allowance option')) !== true) return $r;
      try {
        $creatorId = $req->user()->creatorId();
        $fetchStart = microtime(true);
        $options = AllowanceOption::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($fetchStart, $action, 'fetchOptions');
        Log::info("[{$base}::{$action}] fetched options", ['count' => $options->count()]);
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['options']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('options'));
        $this->logExecutionTime($renderStart, $action, 'renderIndex');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function create(Request $req): View|JsonResponse|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $viewPath = ViewsConstants::ALW_OPT . '.create';
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
      Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id(), 'method' => $method]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($r = self::authorizePerm($req, 'create allowance option')) !== true) return $r;
      try {
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName()]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath);
        $this->logExecutionTime($renderStart, $action, 'renderCreate');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function store(Request $req): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
      Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id(), 'method' => $method]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($r = self::authorizePerm($req, 'create allowance option')) !== true) return $r;
      $valStart = microtime(true);
      if ($r = self::validateName($req)) {
        $this->logExecutionTime($valStart, $action, 'validateName');
        return $r;
      }
      $this->logExecutionTime($valStart, $action, 'validateName');
      try {
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $action, $base) {
          $createStart = microtime(true);
          $opt = AllowanceOption::create([
            'name' => $req->name,
            DatabaseConstants::TABLE_CREATOR => $req->user()->creatorId()
          ]);
          $this->logExecutionTime($createStart, $action, 'createOption');
          Log::info("[{$base}::{$action}] created", ['id' => $opt->id, 'name' => $opt->name]);
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return redirect()->route(ViewsConstants::ALW_OPT . '.index')->with('success', __('AllowanceOption successfully created.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function show(Request $req, AllowanceOption $allowanceOption): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $allowanceOption, $action, $method, $class, $base) {
      Log::info("[{$base}::{$action}] redirecting", [UsersConstants::COL_USER_ID => Auth::id(), 'allowance_option_id' => $allowanceOption->id, 'method' => $method]);
      try {
        $redirStart = microtime(true);
        $resp = redirect()->route(ViewsConstants::ALW_OPT . '.index')->with([$allowanceOption, $req]);
        $this->logExecutionTime($redirStart, $action, 'redirect');
        Log::info("[{$base}::{$action}] complete", ['allowance_option_id' => $allowanceOption->id]);
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'allowance_option_id' => $allowanceOption->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return redirect()->route(ViewsConstants::ALW_OPT . '.index')->with('error', __('Unexpected error.'));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'allowance_option_id' => $allowanceOption->id]);
  }

  public function edit(Request $req, AllowanceOption $opt): View|JsonResponse|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $viewPath = ViewsConstants::ALW_OPT . '.edit';
    return $this->measureProfile($action, function () use ($req, $opt, $action, $method, $class, $base, $viewPath) {
      Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $opt->id, 'method' => $method]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($r = self::authorizePerm($req, 'edit allowance option')) !== true) return $r;
      if (($r = $this->authorizeOwnership($req, $opt)) !== true) return $r;
      try {
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'opt_id' => $opt->id]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['allowanceOption']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, ['allowanceOption' => $opt]);
        $this->logExecutionTime($renderStart, $action, 'renderEdit');
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage(), 'opt_id' => $opt->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'opt_id' => $opt->id]);
  }

  public function update(Request $req, AllowanceOption $opt): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $opt, $action, $method, $class, $base) {
      Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $opt->id, 'method' => $method]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($r = self::authorizePerm($req, 'edit allowance option')) !== true) return $r;
      if (($r = $this->authorizeOwnership($req, $opt)) !== true) return $r;
      $valStart = microtime(true);
      if ($r = self::validateName($req)) {
        $this->logExecutionTime($valStart, $action, 'validateName');
        return $r;
      }
      $this->logExecutionTime($valStart, $action, 'validateName');
      try {
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $opt, $action, $base) {
          $lockStart = microtime(true);
          $lock = AllowanceOption::where('id', $opt->id)->lockForUpdate()->firstOrFail();
          $this->logExecutionTime($lockStart, $action, 'lockRow');
          $old = $lock->name;
          $updStart = microtime(true);
          $lock->update(['name' => $req->name]);
          $this->logExecutionTime($updStart, $action, 'updateOption');
          Log::info("[{$base}::{$action}] updated", ['id' => $lock->id, 'from' => $old, 'to' => $req->name]);
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return redirect()->route(ViewsConstants::ALW_OPT . '.index')->with('success', __('AllowanceOption successfully updated.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage(), 'opt_id' => $opt->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'opt_id' => $opt->id]);
  }

  public function destroy(Request $req, AllowanceOption $opt): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $opt, $action, $method, $class, $base) {
      Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id(), 'opt_id' => $opt->id, 'method' => $method]);
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($r = self::authorizePerm($req, 'delete allowance option')) !== true) return $r;
      if (($r = $this->authorizeOwnership($req, $opt)) !== true) return $r;
      try {
        $txnStart = microtime(true);
        DB::transaction(function () use ($opt, $action, $base) {
          $lockStart = microtime(true);
          $lock = AllowanceOption::where('id', $opt->id)->lockForUpdate()->firstOrFail();
          $this->logExecutionTime($lockStart, $action, 'lockRow');
          $delStart = microtime(true);
          $lock->delete();
          $this->logExecutionTime($delStart, $action, 'deleteOption');
          Log::info("[{$base}::{$action}] deleted", ['id' => $opt->id]);
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return redirect()->route(ViewsConstants::ALW_OPT . '.index')->with('success', __('AllowanceOption successfully deleted.'));
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage(), 'opt_id' => $opt->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::handleException($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'opt_id' => $opt->id]);
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