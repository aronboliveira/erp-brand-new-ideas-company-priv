<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\PerformanceType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;

class PerformanceTypeController extends Controller
{
  private const PERM_CREATE = PermissionsConstants::CRT_PRF_TP;
  private const PERM_DELETE = PermissionsConstants::DEL_PRF_TP;
  private const PERM_EDIT = PermissionsConstants::ED_PRF_TP;
  private const PERM_MANAGE = PermissionsConstants::MNG_PRF_TP;

  public function index(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::PFM_TP . '.' . $action;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['creator_id' => $req->user()?->creatorId()]);
      if (!self::authorizeCompany($req, self::PERM_MANAGE)) return redirect()->back();
      try {
        $creatorId = $req->user()->creatorId();
        $fetchStart = microtime(true);
        $types = PerformanceType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($fetchStart, $action, 'fetchPerformanceTypes');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] complete", ['count' => is_countable($types) ? count($types) : null]);
        return view($viewPath, compact('types'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::PFM_TP . '.' . $action;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['creator_id' => $req->user()?->creatorId()]);
      if (!self::authorizeCompany($req, self::PERM_CREATE)) return redirect()->back();
      try {
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = view($viewPath);
        $this->logExecutionTime($renderStart, $action, 'renderPerformanceTypeCreate');
        Log::info("[{$class}::{$action}] complete");
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $request): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all())]);
      if (!self::authorizeCompany($req, self::PERM_CREATE)) return redirect()->back();
      $valStart = microtime(true);
      $v = Validator::make($req->all(), ['name' => 'required']);
      $this->logExecutionTime($valStart, $action, 'buildValidator');
      if ($v->fails()) return redirect()->back()->with('error', $v->getMessageBag()->first());
      try {
        $type = new PerformanceType();
        $type->name = $req->input('name');
        $type[DatabaseConstants::COL_TABLE_CREATOR] = $req->user()->creatorId();
        $saveStart = microtime(true);
        $type->save();
        $this->logExecutionTime($saveStart, $action, 'savePerformanceType');
        Log::info("[{$class}::{$action}] success", ['performance_type_id' => $type->getKey(), 'name' => $type->name]);
        return redirect()->route(ViewsConstants::PFM_TP . '.index')->with('success', __('Performance Type successfully created.'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(PerformanceType $performanceType): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($performanceType, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['performance_type_id' => $performanceType->getKey()]);
      try {
        $redirStart = microtime(true);
        $resp = redirect()->route(ViewsConstants::PFM_TP . '.index')->with($performanceType);
        $this->logExecutionTime($redirStart, $action, 'redirectToIndex');
        Log::info("[{$class}::{$action}] complete", ['performance_type_id' => $performanceType->getKey()]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'performance_type_id' => $performanceType->getKey(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
        throw $e;
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'performance_type_id' => $performanceType->getKey()]);
  }

  public function edit(Request $request, PerformanceType $performanceType): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::PFM_TP . '.' . $action;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $performanceType, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['performance_type_id' => $performanceType->getKey(), 'creator_id' => $req->user()?->creatorId()]);
      if (!self::authorizeCompany($req, self::PERM_EDIT)) return redirect()->back();
      if ($performanceType[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
      try {
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('performanceType'));
        $this->logExecutionTime($renderStart, $action, 'renderPerformanceTypeEdit');
        Log::info("[{$class}::{$action}] complete", ['performance_type_id' => $performanceType->getKey()]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'performance_type_id' => $performanceType->getKey(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'performance_type_id' => $performanceType->getKey()]);
  }

  public function update(Request $request, PerformanceType $performanceType): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $performanceType, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['performance_type_id' => $performanceType->getKey(), 'creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all())]);
      if (!self::authorizeCompany($req, self::PERM_EDIT)) return redirect()->back();
      if ($performanceType[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
      $valStart = microtime(true);
      $v = Validator::make($req->all(), ['name' => 'required']);
      $this->logExecutionTime($valStart, $action, 'buildValidator');
      if ($v->fails()) return redirect()->back()->with('error', $v->getMessageBag()->first());
      try {
        $performanceType->name = $req->input('name');
        $saveStart = microtime(true);
        $performanceType->save();
        $this->logExecutionTime($saveStart, $action, 'savePerformanceType');
        Log::info("[{$class}::{$action}] success", ['performance_type_id' => $performanceType->getKey(), 'name' => $performanceType->name]);
        return redirect()->route(ViewsConstants::PFM_TP . '.index')->with('success', __('Performance Type successfully updated.'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'performance_type_id' => $performanceType->getKey(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'performance_type_id' => $performanceType->getKey()]);
  }

  public function destroy(Request $request, PerformanceType $performanceType): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $performanceType, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['performance_type_id' => $performanceType->getKey(), 'creator_id' => $req->user()?->creatorId()]);
      if (!self::authorizeCompany($req, self::PERM_DELETE)) return redirect()->back();
      if ($performanceType[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
      try {
        $delStart = microtime(true);
        $performanceType->delete();
        $this->logExecutionTime($delStart, $action, 'deletePerformanceType');
        Log::info("[{$class}::{$action}] success", ['performance_type_id' => $performanceType->getKey()]);
        return redirect()->route(ViewsConstants::PFM_TP . '.index')->with('success', __('Performance Type successfully deleted.'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'performance_type_id' => $performanceType->getKey(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'performance_type_id' => $performanceType->getKey()]);
  }

  protected static function authorizeCompany(Request $request, string $perm): bool
  {
    if ($request->user()->{UsersConstants::COL_TP} === PermissionsConstants::CPN && $request->user()->can($perm)) return true;
    defaultPermissionDenial(
      $request,
      new AuthorizationException(),
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
    );
    return false;
  }
}
