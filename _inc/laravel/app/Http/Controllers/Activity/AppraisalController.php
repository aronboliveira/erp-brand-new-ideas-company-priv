<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  UsersConstants
};
use App\Models\{
  Appraisal,
  Branch,
  Competencies,
  Department,
  Designation,
  Employee,
  Indicator,
  PerformanceType,
  Permission
};
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, Request, RedirectResponse};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{
  Auth,
  DB,
  Log,
  ROute,
  Validator,
  View as ViewFacade
};
use Symfony\Component\HttpFoundation\Response;

final class AppraisalController extends Controller
{
  use ChecksLogin;

  private const ENTITY = 'appraisal';

  public function __construct()
  {
    $this->middleware(MiddlewaresConstants::AUTH);
  }

  public function index(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = self::ENTITY . '.index';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id]);
        Log::info("[{$class}::{$action}] checking permission", [strtolower(class_basename(Permission::class)) => PermissionsConstants::MNG_APR]);
        if (!$user?->can(PermissionsConstants::MNG_APR)) {
          Log::warning("[{$class}::{$action}] denied", [UsersConstants::COL_USER_ID => $user?->id]);
          throw new AuthorizationException();
        }
        $competencyCount = Competencies::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->count();
        Log::info("[{$class}::{$action}] competency count", ['count' => $competencyCount]);
        $query = Appraisal::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->with([DatabaseConstants::TABLE_EMPLOYEES, DatabaseConstants::TABLE_BRANCHES]);
        if (strtolower($user->type ?? '') == strtolower(class_basename(Employee::class))) {
          $employee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->firstOrFail();
          $query->where(strtolower(class_basename(Branch::class)), $employee->branch_id)->where(strtolower(class_basename(Employee::class)), $employee->id);
          Log::info("[{$class}::{$action}] employee filter", [strtolower(class_basename(Branch::class)) => $employee->branch_id, strtolower(class_basename(Employee::class)) => $employee->id]);
        }
        $appraisals = $query->get();
        Log::info("[{$class}::{$action}] fetched appraisals", ['count' => $appraisals->count()]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $response = view($viewPath, compact('appraisals', 'competencyCount'));
        return $response;
      } catch (AuthorizationException $e) {
        Log::error("[{$class}::{$action}] auth error", ['error' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = self::ENTITY . '.create';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id]);
        Log::info("[{$class}::{$action}] checking permission", [strtolower(class_basename(Permission::class)) => 'create appraisal']);
        if (!$user?->can('create appraisal')) {
          Log::warning("[{$class}::{$action}] denied", [UsersConstants::COL_USER_ID => $user?->id]);
          throw new AuthorizationException();
        }
        $creatorId = $user?->creatorId();
        $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        Log::info("[{$class}::{$action}] data loaded", ['performanceTypes' => $performanceTypes->count(), DatabaseConstants::TABLE_BRANCHES => $branches->count()]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact(DatabaseConstants::TABLE_BRANCHES, 'performanceTypes'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $request): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", ['user' => Auth::id()]);
        Log::info("[{$class}::{$action}] checking permission", [strtolower(class_basename(Permission::class)) => 'create appraisal']);
        if (!$user?->can('create appraisal')) {
          Log::warning("[{$class}::{$action}] denied", [UsersConstants::COL_USER_ID => $user?->id]);
          throw new AuthorizationException();
        }
        $valStart = microtime(true);
        $validator = Validator::make($req->all(), [strtolower(class_basename(Branch::class)) => 'required|exists:' . DatabaseConstants::TABLE_BRANCHES . ',id', strtolower(class_basename(Employee::class)) => 'required|exists:' . DatabaseConstants::TABLE_EMPLOYEES . ',id', 'appraisal_date' => 'required|date', 'rating' => 'required|array']);
        if ($validator->fails()) {
          $msg = $validator->errors()->first();
          Log::warning("[{$class}::{$action}] validation failed", ['error' => $msg]);
          return back()->with('error', $msg);
        }
        $this->logExecutionTime($valStart, $action, 'validateStore');
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $user) {
          $data = $req->only([strtolower(class_basename(Branch::class)), strtolower(class_basename(Employee::class)), 'remark', 'appraisal_date', 'rating']);
          $this->buildAppraisal(new Appraisal(), $data, $user?->creatorId());
        });
        $this->logExecutionTime($txnStart, $action, 'storeTransaction');
        Log::info("[{$class}::{$action}] committed");
        return redirect()->route(self::ENTITY . '.index')->with('success', __('Appraisal successfully created.'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $request, Appraisal $appraisal): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = self::ENTITY . '.show';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $appraisal, $action, $method, $class, $viewPath) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", ['appraisal_id' => $appraisal->id]);
        $rating = json_decode($appraisal->rating, true);
        $creatorId = $user?->creatorId();
        $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $employee = Employee::findOrFail($appraisal->employee);
        $indicator = Indicator::where([[strtolower(class_basename(Branch::class)), $employee->branch_id], [strtolower(class_basename(Department::class)), $employee->department_id], [strtolower(class_basename(Designation::class)), $employee->designation_id]])->first();
        $ratings = $indicator ? json_decode($indicator->rating, true) : [];
        Log::info("[{$class}::{$action}] data ready", ['performanceTypes' => $performanceTypes->count(), 'ratings' => count($ratings), 'rating' => count($rating)]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $response = view($viewPath, compact('appraisal', 'performanceTypes', 'ratings', 'rating'));
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'appraisal_id' => $appraisal->id]);
  }

  public function edit(Request $request, Appraisal $appraisal): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = self::ENTITY . '.edit';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $appraisal, $action, $method, $class, $viewPath) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", ['appraisal_id' => $appraisal->id]);
        Log::info("[{$class}::{$action}] checking permission", [strtolower(class_basename(Permission::class)) => 'edit appraisal']);
        if (!$user?->can('edit appraisal')) {
          Log::warning("[{$class}::{$action}] denied", [UsersConstants::COL_USER_ID => $user?->id]);
          throw new AuthorizationException();
        }
        $creatorId = $user?->creatorId();
        $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        $ratings = json_decode($appraisal->rating, true);
        Log::info("[{$class}::{$action}] data loaded", ['performanceTypes' => $performanceTypes->count(), DatabaseConstants::TABLE_BRANCHES => $branches->count(), 'ratings' => count($ratings)]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact(DatabaseConstants::TABLE_BRANCHES, 'appraisal', 'performanceTypes', 'ratings'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'appraisal_id' => $appraisal->id]);
  }

  public function update(Request $request, Appraisal $appraisal): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $appraisal, $action, $method, $class) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", ['appraisal_id' => $appraisal->id]);
        Log::info("[{$class}::{$action}] checking permission", [strtolower(class_basename(Permission::class)) => 'edit appraisal']);
        if (!$user?->can('edit appraisal')) {
          Log::warning("[{$class}::{$action}] denied", [UsersConstants::COL_USER_ID => $user?->id]);
          throw new AuthorizationException();
        }
        $valStart = microtime(true);
        $validator = Validator::make($req->all(), [strtolower(class_basename(Branch::class)) => 'required|exists:' . DatabaseConstants::TABLE_BRANCHES . ',id', strtolower(class_basename(Employee::class)) => 'required|exists:' . DatabaseConstants::TABLE_EMPLOYEES . ',id', 'appraisal_date' => 'required|date', 'rating' => 'required|array']);
        if ($validator->fails()) {
          $msg = $validator->errors()->first();
          Log::warning("[{$class}::{$action}] validation failed", ['error' => $msg]);
          return back()->with('error', $msg);
        }
        $this->logExecutionTime($valStart, $action, 'validateUpdate');
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $appraisal, $user) {
          $lock = Appraisal::where('id', $appraisal->id)->lockForUpdate()->firstOrFail();
          $data = $req->only([strtolower(class_basename(Branch::class)), strtolower(class_basename(Employee::class)), 'remark', 'appraisal_date', 'rating']);
          $this->buildAppraisal($lock, $data, $user?->creatorId());
        });
        $this->logExecutionTime($txnStart, $action, 'updateTransaction');
        Log::info("[{$class}::{$action}] committed");
        return redirect()->route(self::ENTITY . '.index')->with('success', __('Appraisal successfully updated.'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'appraisal_id' => $appraisal->id]);
  }

  public function destroy(Request $request, Appraisal $appraisal): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $appraisal, $action, $method, $class) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", ['appraisal_id' => $appraisal->id]);
        Log::info("[{$class}::{$action}] checking permission", [strtolower(class_basename(Permission::class)) => 'delete appraisal']);
        if (!$user?->can('delete appraisal') || $appraisal[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
          Log::warning("[{$class}::{$action}] denied", [UsersConstants::COL_USER_ID => $user?->id]);
          throw new AuthorizationException();
        }
        $txnStart = microtime(true);
        DB::transaction(function () use ($appraisal) {
          $lock = Appraisal::where('id', $appraisal->id)->lockForUpdate()->firstOrFail();
          $lock->delete();
        });
        $this->logExecutionTime($txnStart, $action, 'destroyTransaction');
        Log::info("[{$class}::{$action}] deleted");
        return redirect()->route(self::ENTITY . '.index')->with('success', __('Appraisal successfully deleted.'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'appraisal_id' => $appraisal->id]);
  }

  public const EMP_BY_STR = 'empByStar';
  public function empByStar(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = self::ENTITY . '.star';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", ['employee_id' => $req->employee]);
        $employee = Employee::findOrFail($req->employee);
        $indicator = Indicator::where([[strtolower(class_basename(Branch::class)), $employee->branch_id], [strtolower(class_basename(Department::class)), $employee->department_id], [strtolower(class_basename(Designation::class)), $employee->designation_id]])->first();
        $ratings = $indicator ? json_decode($indicator->rating, true) : [];
        $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->get();
        if (!ViewFacade::exists($viewPath)) return response()->json(['error' => "HTTP 404: Page {$viewPath} not found!"], 404);
        $html = view($viewPath, compact('ratings', 'performanceTypes'))->render();
        Log::info("[{$class}::{$action}] rendered", ['ratings' => count($ratings), 'performanceTypes' => $performanceTypes->count()]);
        return response()->json(['success' => true, 'html' => $html]);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'employee_id' => $request->employee]);
  }

  public const EMP_BY_STR1 = 'empByStar1';
  public function empByStar1(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = self::ENTITY . '.star_edit';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [strtolower(class_basename(Employee::class)) => $req->employee, 'appraisal' => $req->appraisal]);
        $employee = Employee::findOrFail($req->employee);
        $appraisal = Appraisal::findOrFail($req->appraisal);
        $indicator = Indicator::where([[strtolower(class_basename(Branch::class)), $employee->branch_id], [strtolower(class_basename(Department::class)), $employee->department_id], [strtolower(class_basename(Designation::class)), $employee->designation_id]])->first();
        $ratings = $indicator ? json_decode($indicator->rating, true) : [];
        $rating = json_decode($appraisal->rating, true);
        $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->get();
        if (!ViewFacade::exists($viewPath)) return response()->json(['error' => "HTTP 404: Page {$viewPath} not found!"], 404);
        $html = view($viewPath, compact('ratings', 'rating', 'performanceTypes'))->render();
        Log::info("[{$class}::{$action}] rendered", ['ratings' => count($ratings), 'rating' => count($rating), 'performanceTypes' => $performanceTypes->count()]);
        return response()->json(['success' => true, 'html' => $html]);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'employee' => $request->employee, 'appraisal' => $request->appraisal]);
  }

  public const GET_EMP = 'getEmployee';
  public function getEmployee(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", ['branch_id' => $req->branch_id]);
        $employees = Employee::where('branch_id', $req->branch_id)->get();
        Log::info("[{$class}::{$action}] fetched", ['count' => $employees->count()]);
        return response()->json([strtolower(class_basename(Employee::class)) => $employees]);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'branch_id' => $request->branch_id]);
  }

  private function buildAppraisal(Appraisal $appraisal, array $data, int $creatorId): Appraisal
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' building appraisal', [
      'data'      => Arr::only($data, [
        strtolower(class_basename(Branch::class)),
        strtolower(class_basename(Employee::class)),
        'appraisal_date'
      ]),
      'creatorId' => $creatorId
    ]);
    $appraisal->branch        = $data[strtolower(class_basename(Branch::class))];
    $appraisal->employee      = $data[strtolower(class_basename(Employee::class))];
    $appraisal->remark        = $data['remark'] ?? null;
    $appraisal->appraisal_date = $data['appraisal_date'];
    $appraisal->rating        = json_encode($data['rating'] ?? []);
    $appraisal[DatabaseConstants::TABLE_CREATOR]    = $creatorId;
    $appraisal->save();
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' saved', [
      'appraisal_id' => $appraisal->id
    ]);
    return $appraisal;
  }
}
