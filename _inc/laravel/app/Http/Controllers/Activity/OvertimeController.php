<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, ViewsConstants as VW};
use App\Models\{Employee, Overtime};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
class OvertimeController extends Controller
{
    use HasCrudConstants;

  private const PERM_CREATE = 'create overtime';
  private const PERM_DELETE = 'delete overtime';
  private const PERM_EDIT = 'edit overtime';
  private const PERM_MANAGE = 'manage overtime';

  public function index(Request $request): View|RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = VW::OVT . '.index';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['creator_id' => $req->user()?->creatorId()]);
      if (!self::authorizePerm($req, self::PERM_MANAGE)) return redirect()->back();
      try {
        $creatorId = $req->user()->creatorId();
        $fetchStart = microtime(true);
        $overtimes = Overtime::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
        $this->logExecutionTime($fetchStart, $action, 'fetchOvertimes');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] complete", ['count' => is_countable($overtimes) ? count($overtimes) : null]);
        return view($viewPath, compact('overtimes'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const OVT_CRT = 'overtimeCreate';
  public function overtimeCreate(int|string $id): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = VW::OVT . '.create';
    return $this->measureProfile($action, function () use ($id, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['employee_id_param' => $id]);
      try {
        $findStart = microtime(true);
        $employee = Employee::find($id);
        $this->logExecutionTime($findStart, $action, 'findEmployee');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('employee'));
        $this->logExecutionTime($renderStart, $action, 'renderOvertimeCreate');
        Log::info("[{$class}::{$action}] complete", ['has_employee' => (bool) $employee]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'employee_id_param' => $id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
        throw $e;
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'employee_id_param' => $id]);
  }

  public function store(Request $request): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all())]);
      if (!self::authorizePerm($req, self::PERM_CREATE)) return redirect()->back();
      $rules = ['employee_id' => 'required', 'title' => 'required', 'number_of_days' => 'required', 'hours' => 'required', 'rate' => 'required'];
      $valStart = microtime(true);
      $validator = Validator::make($req->all(), $rules);
      $this->logExecutionTime($valStart, $action, 'buildValidator');
      if ($validator->fails()) return redirect()->back()->with('error', $validator->getMessageBag()->first());
      try {
        $overtime = new Overtime();
        foreach (['employee_id', 'title', 'number_of_days', 'hours', 'rate'] as $k) $overtime->$k = $req->input($k);
        $overtime->created_by = $req->user()->creatorId();
        $saveStart = microtime(true);
        $overtime->save();
        $this->logExecutionTime($saveStart, $action, 'saveOvertime');
        Log::info("[{$class}::{$action}] success", ['overtime_id' => $overtime->getKey()]);
        return redirect()->back()->with('success', __('Overtime successfully created.'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Overtime $overtime): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($overtime, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['overtime_id' => $overtime->getKey()]);
      try {
        $redirStart = microtime(true);
        $resp = redirect()->route(VW::COM . '.index')->with($overtime);
        $this->logExecutionTime($redirStart, $action, 'redirectToIndex');
        Log::info("[{$class}::{$action}] complete", ['overtime_id' => $overtime->getKey()]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'overtime_id' => $overtime->getKey(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
        throw $e;
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'overtime_id' => $overtime->getKey()]);
  }

  public function edit(Request $request, int|string $overtime): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = VW::OVT . '.edit';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $overtime, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['overtime_param' => $overtime, 'creator_id' => $req->user()?->creatorId()]);
      if (!self::authorizePerm($req, self::PERM_EDIT)) return response()->json(['error' => __('Permission denied.')], 401);
      try {
        $getStart = microtime(true);
        $ot = self::getOvertime($req, $overtime);
        $this->logExecutionTime($getStart, $action, 'getOvertime');
        if (!$ot) return response()->json(['error' => __('Permission denied.')], 401);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('overtime'));
        $this->logExecutionTime($renderStart, $action, 'renderOvertimeEdit');
        Log::info("[{$class}::{$action}] complete", ['overtime_param' => $overtime]);
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'overtime_param' => $overtime, 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'overtime_param' => $overtime]);
  }

  public function update(Request $request, int|string $overtime): RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $overtime, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['overtime_param' => $overtime, 'creator_id' => $req->user()?->creatorId(), 'input_keys' => array_keys($req->all())]);
      if (!self::authorizePerm($req, self::PERM_EDIT)) return redirect()->back();
      $ot = self::getOvertime($req, $overtime);
      if (!$ot) return redirect()->back();
      $rules = ['title' => 'required', 'number_of_days' => 'required', 'hours' => 'required', 'rate' => 'required'];
      $valStart = microtime(true);
      $validator = Validator::make($req->all(), $rules);
      $this->logExecutionTime($valStart, $action, 'buildValidator');
      if ($validator->fails()) return redirect()->back()->with('error', $validator->getMessageBag()->first());
      try {
        foreach (['title', 'number_of_days', 'hours', 'rate'] as $k) $ot->$k = $req->input($k);
        $saveStart = microtime(true);
        $ot->save();
        $this->logExecutionTime($saveStart, $action, 'saveOvertime');
        Log::info("[{$class}::{$action}] success", ['overtime_id' => $ot->getKey()]);
        return redirect()->back()->with('success', __('Overtime successfully updated.'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'overtime_param' => $overtime, 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'overtime_param' => $overtime]);
  }

  public function destroy(Request $request, Overtime $overtime): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $overtime, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['overtime_id' => $overtime->getKey(), 'creator_id' => $req->user()?->creatorId()]);
      if (!self::authorizePerm($req, self::PERM_DELETE)) return redirect()->back();
      if ($overtime->created_by !== $req->user()->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
      try {
        $delStart = microtime(true);
        $overtime->delete();
        $this->logExecutionTime($delStart, $action, 'deleteOvertime');
        Log::info("[{$class}::{$action}] success", ['overtime_id' => $overtime->getKey()]);
        return redirect()->back()->with('success', __('Overtime successfully deleted.'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'overtime_id' => $overtime->getKey(), 'creator_id' => $req->user()?->creatorId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'overtime_id' => $overtime->getKey()]);
  }

  protected static function authorizePerm(Request $request, string $perm): bool
  {
    if ($request->user()->can($perm)) return true;
    defaultPermissionDenial(
      $request,
      new AuthorizationException(),
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
    );
    return false;
  }

  protected static function getOvertime(Request $request, int|string $id): ?Overtime
  {
    $ot = Overtime::find($id);
    if (!$ot || $ot->created_by !== $request->user()->creatorId()) {
      defaultPermissionDenial(
        $request,
        new AuthorizationException(),
        __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
      );
      return null;
    }
    return $ot;
  }
}
