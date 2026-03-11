<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  SettingsConstants,
  ViewsConstants,
  UsersConstants
};
use App\Models\{Award, AwardType, Employee, Utility};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\{DB, Log, Route, Validator, View as ViewFacade};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
final class AwardController extends Controller
{
    use HasCrudConstants;


  public function __construct()
  {
    $this->middleware(MiddlewaresConstants::AUTH);
  }

  public function index(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::AWD . '.index';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id]);
      if ($deny = $this->authorizeOrDeny($req, PermissionsConstants::MNG_AWD, $action)) return $deny;
      try {
        $user = $req->user();
        $creatorId = $user?->creatorId();
        $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
        $types = AwardType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
        $query = Award::with(['employee', 'awardType']);
        if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
          $empId = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id');
          $query->where('employee_id', $empId);
        } else $query->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
        $awards = $query->get();
        Log::info("[{$class}::{$action}] success", ['count' => $awards->count()]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('awards', 'employees', 'types'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['err' => $e->getMessage()]);
        Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$class}::{$action}] error", ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::AWD . '.create';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id]);
      if ($deny = $this->authorizeOrDeny($req, 'create award', $action)) return $deny;
      try {
        $creatorId = $req->user()->creatorId();
        $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
        $types = AwardType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
        Log::info("[{$class}::{$action}] success");
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('employees', 'types'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['err' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $request): RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id, 'input' => $req->all()]);
      if ($deny = $this->authorizeOrDeny($req, 'create award', $action)) return $deny;
      $valStart = microtime(true);
      $validator = Validator::make($req->all(), ['employee_id' => 'required|exists:employees,id', 'award_type' => 'required|exists:award_types,id', 'date' => 'required|date', 'gift' => 'required|string']);
      if ($validator->fails()) {
        $msg = $validator->errors()->first();
        Log::warning("[{$class}::{$action}] validation failed", ['error' => $msg]);
        return back()->with('error', $msg);
      }
      $this->logExecutionTime($valStart, $action, 'validateStore');
      try {
        $txnStart = microtime(true);
        $award = DB::transaction(function () use ($req) {
          return Award::create(['employee_id' => $req->input('employee_id'), 'award_type' => $req->input('award_type'), 'date' => $req->input('date'), 'gift' => $req->input('gift'), 'description' => $req->input('description'), DatabaseConstants::COL_TABLE_CREATOR => $req->user()->creatorId()]);
        });
        $this->logExecutionTime($txnStart, $action, 'storeTransaction');
        Log::info("[{$class}::{$action}] created award", ['award_id' => $award->id]);
        try {
          $creatorId = $req->user()->creatorId();
          $setting = Utility::settingsById($creatorId);
          $employee = Employee::findOrFail($award->employee_id);
          $awardType = AwardType::findOrFail($award->award_type);
          $data = ['award_name' => $awardType->name, 'employee_name' => $employee->name, 'award_date' => $award->date];
          if (!empty($setting['award_notification'])) Utility::sendSlackMsg('new_award', $data);
          if (!empty($setting['telegram_award_notification'])) Utility::sendTelegramMsg('new_award', $data);
          if (!empty(Utility::settings()['new_award'])) Utility::sendEmailTemplate('new_award', [$employee->id => $employee->email], ['award_name' => $awardType->name, 'award_email' => $employee->email]);
        } catch (\Throwable $notifEx) {
          Log::warning("[{$class}::{$action}] notifications failed", ['err' => $notifEx->getMessage()]);
        }
        try {
          if ($hook = Utility::webhookSetting('New Award')) {
            if (!Utility::webhookCall($hook['url'], $award->toJson(), $hook['method'])) {
              Log::warning("[{$class}::{$action}] webhook returned false");
              return redirect()->back()->with('error', __('Webhook call failed.'));
            }
          }
        } catch (\Throwable $hookEx) {
          Log::warning("[{$class}::{$action}] webhook error", ['err' => $hookEx->getMessage()]);
        }
        return redirect()->route(ViewsConstants::AWD . '.index')->with('success', __('Award successfully created.'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['err' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $request, Award $award): RedirectResponse
  {
    return redirect()->route(ViewsConstants::AWD . '.index')->with($request);
  }

  public function edit(Request $request, Award $award): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::AWD . '.edit';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $award, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['award_id' => $award->id, UsersConstants::COL_USER_ID => $req->user()->id]);
      if ($deny = $this->authorizeOrDeny($req, 'edit award', $action)) return $deny;
      if ($award->created_by !== $req->user()->creatorId()) {
        Log::warning("[{$class}::{$action}] forbidden owner-mismatch", ['award_id' => $award->id, UsersConstants::COL_USER_ID => $req->user()->id]);
        return response()->json(['error' => __('Permission denied.')], 401);
      }
      try {
        $creatorId = $req->user()->creatorId();
        $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
        $types = AwardType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
        Log::info("[{$class}::{$action}] success");
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('award', 'employees', 'types'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['err' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'award_id' => $award->id]);
  }

  public function update(Request $request, Award $award): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $award, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['award_id' => $award->id, 'input' => $req->all()]);
      if ($deny = $this->authorizeOrDeny($req, 'edit award', $action)) return $deny;
      if ($award->created_by !== $req->user()->creatorId()) return redirect()->back()->with('error', __('Permission denied.'));
      $valStart = microtime(true);
      $validator = Validator::make($req->all(), ['employee_id' => 'required|exists:employees,id', 'award_type' => 'required|exists:award_types,id', 'date' => 'required|date', 'gift' => 'required|string']);
      if ($validator->fails()) {
        $msg = $validator->errors()->first();
        Log::warning("[{$class}::{$action}] validation failed", ['error' => $msg]);
        return back()->with('error', $msg);
      }
      $this->logExecutionTime($valStart, $action, 'validateUpdate');
      try {
        $txnStart = microtime(true);
        DB::transaction(fn() => Award::where('id', $award->id)->lockForUpdate()->firstOrFail()->update(array_merge($validator->validated(), ['description' => $req->input('description')])));
        $this->logExecutionTime($txnStart, $action, 'updateTransaction');
        Log::info("[{$class}::{$action}] success", ['award_id' => $award->id]);
        return redirect()->route(ViewsConstants::AWD . '.index')->with('success', __('Award successfully updated.'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['err' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'award_id' => $award->id]);
  }

  public function destroy(Request $request, Award $award): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $award, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['award_id' => $award->id]);
      if ($deny = $this->authorizeOrDeny($req, 'delete award', $action)) return $deny;
      if ($award->created_by !== $req->user()->creatorId()) return redirect()->back()->with('error', __('Permission denied.'));
      try {
        $txnStart = microtime(true);
        DB::transaction(fn() => Award::where('id', $award->id)->lockForUpdate()->firstOrFail()->delete());
        $this->logExecutionTime($txnStart, $action, 'destroyTransaction');
        Log::info("[{$class}::{$action}] success", ['award_id' => $award->id]);
        return redirect()->route(ViewsConstants::AWD . '.index')->with('success', __('Award successfully deleted.'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['err' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'award_id' => $award->id]);
  }

  /** 
   * Centralize authorization and log failures 
   * @return null|RedirectResponse
   */
  private function authorizeOrDeny(Request $req, string $permission, string $action): ?RedirectResponse
  {
    if (!$req->user()->can($permission)) {
      Log::warning(__CLASS__ . "::{$action} unauthorized", [
        UsersConstants::COL_USER_ID   => $req->user()->id,
        'permission' => $permission,
      ]);
      return defaultPermissionDenial(
        $req,
        new AuthorizationException(),
        __CLASS__ . '::' . $action
      );
    }
    return null;
  }
}
