<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{Allowance, AllowanceOption, Employee};
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse, Response};
use Illuminate\Support\Facades\{DB, Log, Route, Validator, View as ViewFacade};
use Throwable;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class AllowanceController extends Controller
{
  use ChecksLogin;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH]);
  }

  public const ALW_CR = 'allowanceCreate';
  public function allowanceCreate(Request $req, int|string $id): View|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $viewPath = ViewsConstants::ALW . '.create';
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base, $viewPath) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::info("[{$base}::{$action}] redirecting to login", ['method' => $method]);
        return $userOrRedirect;
      }
      $user = $userOrRedirect;
      if ($r = self::_authorize($req, 'create allowance')) return $r;
      Log::info("[{$base}::{$action}] rendering create form", [UsersConstants::COL_USER_ID => $user?->id, UsersConstants::COL_EMP_ID => $id, 'method' => $method]);
      try {
        $creatorId = $user?->creatorId();
        $listsStart = microtime(true);
        $options = AllowanceOption::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
        $types = Allowance::$Allowancetype;
        $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
        $empStart = microtime(true);
        $employee = Employee::findOrFail($id);
        $this->logExecutionTime($empStart, $action, 'fetchEmployee');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['employee', 'options', 'types']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('employee', 'options', 'types'));
        $this->logExecutionTime($renderStart, $action, 'renderCreate');
        return $resp;
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] authz debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::catchErr($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, UsersConstants::COL_EMP_ID => $id]);
  }

  public function store(Request $req): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::info("[{$base}::{$action}] redirecting to login", ['method' => $method]);
        return $u;
      }
      $user = $u;
      if ($r = self::_authorize($req, 'create allowance')) return $r;
      $valStart = microtime(true);
      if ($r = self::validateReq($req->all(), [
        UsersConstants::COL_EMP_ID => 'required|exists:employees,id',
        'allowance_option' => 'required|exists:allowance_options,id',
        'title' => 'required|string',
        'amount' => 'required|numeric|min:0',
      ])) {
        $this->logExecutionTime($valStart, $action, 'validateRequest');
        return $r;
      }
      $this->logExecutionTime($valStart, $action, 'validateRequest');
      Log::info("[{$base}::{$action}] creating allowance", [
        UsersConstants::COL_USER_ID => $user?->id,
        UsersConstants::COL_EMP_ID => $req->employee_id,
        'allowance_option' => $req->allowance_option,
        'title' => $req->title,
        'amount' => $req->amount,
        'type' => $req->type,
        'method' => $method
      ]);
      try {
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $user, $action, $base) {
          $createStart = microtime(true);
          $allowance = Allowance::create([
            UsersConstants::COL_EMP_ID => $req->employee_id,
            'allowance_option' => $req->allowance_option,
            'title' => $req->title,
            'type' => $req->type,
            'amount' => $req->amount,
            DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
          ]);
          $this->logExecutionTime($createStart, $action, 'createAllowance');
          Log::info("[{$base}::{$action}] allowance record created", ['allowance_id' => $allowance->id, UsersConstants::COL_EMP_ID => $allowance->employee_id]);
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return redirect()->back()->with('success', __('Allowance successfully created.'));
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::catchErr($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function edit(Request $req, int|string $allowanceId): View|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $viewPath = ViewsConstants::ALW . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $allowanceId, $action, $method, $class, $base, $viewPath) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::info("[{$base}::{$action}] redirecting to login");
        return $u;
      }
      $user = $u;
      if ($r = self::_authorize($req, 'edit allowance')) return $r;
      Log::info("[{$base}::{$action}] rendering edit form", [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowanceId, 'method' => $method]);
      try {
        $fetchStart = microtime(true);
        $allowance = Allowance::findOrFail($allowanceId);
        $this->logExecutionTime($fetchStart, $action, 'fetchAllowance');
        if ($allowance[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new AuthorizationException();
        $creatorId = $user?->creatorId();
        $listsStart = microtime(true);
        $options = AllowanceOption::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
        $types = Allowance::$Allowancetype;
        $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['allowance', 'options', 'types']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('allowance', 'options', 'types'));
        $this->logExecutionTime($renderStart, $action, 'renderEdit');
        return $resp;
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowanceId]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::catchErr($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'allowance_id' => $allowanceId]);
  }

  public function update(Request $req, Allowance $allowance): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $allowance, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::info("[{$base}::{$action}] redirecting to login", ['method' => $method]);
        return $u;
      }
      $user = $u;
      if ($r = self::_authorize($req, 'edit allowance')) return $r;
      if ($allowance[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
        Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id]);
        return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
      }
      $valStart = microtime(true);
      if ($r = self::validateReq($req->all(), ['allowance_option' => 'required|exists:allowance_options,id', 'title' => 'required|string', 'amount' => 'required|numeric|min:0'])) {
        $this->logExecutionTime($valStart, $action, 'validateRequest');
        return $r;
      }
      $this->logExecutionTime($valStart, $action, 'validateRequest');
      Log::info("[{$base}::{$action}] updating allowance", [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id, 'new_option' => $req->allowance_option, 'new_title' => $req->title, 'new_amount' => $req->amount, 'new_type' => $req->type, 'method' => $method]);
      try {
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $allowance, $action, $base) {
          $lockStart = microtime(true);
          $lock = Allowance::where('id', $allowance->id)->lockForUpdate()->firstOrFail();
          $this->logExecutionTime($lockStart, $action, 'lockRow');
          $updStart = microtime(true);
          $lock->update(['allowance_option' => $req->allowance_option, 'title' => $req->title, 'type' => $req->type, 'amount' => $req->amount]);
          $this->logExecutionTime($updStart, $action, 'updateAllowance');
          Log::info("[{$base}::{$action}] allowance record updated", ['allowance_id' => $lock->id]);
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return redirect()->back()->with('success', __('Allowance successfully updated.'));
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] permission denied in transaction", [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage(), 'allowance_id' => $allowance->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::catchErr($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'allowance_id' => $allowance->id]);
  }

  public function destroy(Request $req, Allowance $allowance): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $allowance, $action, $method, $class, $base) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) {
        Log::info("[{$base}::{$action}] redirecting to login");
        return $u;
      }
      $user = $u;
      if ($r = self::_authorize($req, 'delete allowance')) return $r;
      Log::info("[{$base}::{$action}] deleting allowance", [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id, 'method' => $method]);
      try {
        if ($allowance[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new AuthorizationException();
        $txnStart = microtime(true);
        DB::transaction(function () use ($allowance, $action, $base) {
          $lockStart = microtime(true);
          $lock = Allowance::where('id', $allowance->id)->lockForUpdate()->firstOrFail();
          $this->logExecutionTime($lockStart, $action, 'lockRow');
          $delStart = microtime(true);
          $lock->delete();
          $this->logExecutionTime($delStart, $action, 'deleteAllowance');
          Log::info("[{$base}::{$action}] allowance record deleted", ['allowance_id' => $allowance->id]);
        });
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return redirect()->back()->with('success', __('Allowance successfully deleted.'));
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] permission denied in destroy", [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (Throwable $e) {
        Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage(), 'allowance_id' => $allowance->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return self::catchErr($req, $e);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'allowance_id' => $allowance->id]);
  }

  public function show(Request $req, Allowance $allowance): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    return $this->measureProfile($action, function () use ($req, $allowance, $action, $method, $base) {
      Log::info("[{$base}::{$action}] called", ['allowance_id' => $allowance->id, 'method' => $method]);
      try {
        $redirStart = microtime(true);
        $resp = redirect()->route(ViewsConstants::ALW . '.index')->with([$req, $allowance]);
        $this->logExecutionTime($redirStart, $action, 'redirect');
        Log::info("[{$base}::{$action}] complete", ['allowance_id' => $allowance->id]);
        return $resp;
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'allowance_id' => $allowance->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return redirect()->route(ViewsConstants::ALW . '.index')->with('error', __('Unexpected error.'));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'allowance_id' => $allowance->id]);
  }

  private static function _authorize(Request $req, string $perm): RedirectResponse|JsonResponse|null
  {
    if (!$req->user()->can($perm)) {
      Log::warning("Authorization failed for {$perm}", [
        UsersConstants::COL_USER_ID => $req->user()->id,
        'route'   => $req->path(),
      ]);
      return defaultPermissionDenial(
        $req,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    return null;
  }

  private static function validateReq(array $data, array $rules): RedirectResponse|JsonResponse|null
  {
    $v = Validator::make($data, $rules);
    if ($v->fails()) {
      Log::info('Validation failed', ['errors' => $v->errors()->all()]);
      return redirect()->back()->with('error', $v->errors()->first());
    }
    return null;
  }

  private static function catchErr(Request $req, Throwable $e): RedirectResponse|JsonResponse|null
  {
    Log::error('Unexpected error in ' . __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'], [
      'exception' => $e,
      UsersConstants::COL_USER_ID   => $req->user()?->id,
      'input'     => $req->all(),
    ]);
    return defaultUndefinedException(
      $req,
      $e,
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
    );
  }

    public function index(Request $req): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $action = __FUNCTION__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($req, $action, $class) {
            if (($u = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $u;
            if (($r = self::guard($req, 'manage allowance')) !== true) return $r;
            try {
                $allowances = \App\Models\Bills\Allowance::where('created_by', $u->creatorId())->get();
                $viewPath = 'allowances.index';
                if (!\Illuminate\Support\Facades\View::exists($viewPath))
                    return redirect()->route('dashboard')->with('error', 'Allowances index view not found.');
                return response()->view($viewPath, ['allowances' => $allowances]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("[$class::$action] failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "$class::$action");
            }
        });
    }

}

// ! ALERT store and update accept raw amount; consider casting to numeric and validating range to prevent injection or overflow.