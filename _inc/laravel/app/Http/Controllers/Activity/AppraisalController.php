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
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, Request, RedirectResponse};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
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
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
      UsersConstants::COL_USER_ID => $request->user()->id,
    ]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      $user = $request->user();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
        strtolower(class_basename(Permission::class)) => PermissionsConstants::MNG_APR
      ]);
      if (!$user?->can(PermissionsConstants::MNG_APR)) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [
          UsersConstants::COL_USER_ID => $user?->id
        ]);
        throw new \Illuminate\Auth\Access\AuthorizationException();
      }
      $competencyCount = Competencies::where(
        DatabaseConstants::TABLE_CREATOR,
        $user?->creatorId()
      )->count();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' competency count', [
        'count' => $competencyCount
      ]);

      $query = Appraisal::where(
        DatabaseConstants::TABLE_CREATOR,
        $user?->creatorId()
      )->with([DatabaseConstants::TABLE_EMPLOYEES, DatabaseConstants::TABLE_BRANCHES]);
      if (strtolower($user->type ?? '') == strtolower(class_basename(Employee::class))) {
        $employee = Employee::where(
          UsersConstants::COL_USER_ID,
          $user?->id
        )->firstOrFail();
        $query->where(strtolower(class_basename(Branch::class)), $employee->branch_id)
          ->where(strtolower(class_basename(Employee::class)), $employee->id);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' employee filter', [
          strtolower(class_basename(Branch::class))   => $employee->branch_id,
          strtolower(class_basename(Employee::class)) => $employee->id
        ]);
      }
      $appraisals = $query->get();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched appraisals', [
        'count' => $appraisals->count()
      ]);
      return view(self::ENTITY . '.' . __FUNCTION__, compact('appraisals', 'competencyCount'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function create(Request $request): View|RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [UsersConstants::COL_USER_ID => $request->user()->id]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      $user = $request->user();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
        strtolower(class_basename(Permission::class)) => 'create appraisal'
      ]);
      if (!$user?->can('create appraisal')) {
        Log::warning(
          __CLASS__ . '::' . __FUNCTION__ . ' denied',
          [UsersConstants::COL_USER_ID => $user?->id]
        );
        throw new \Illuminate\Auth\Access\AuthorizationException();
      }
      $creatorId       = $user?->creatorId();
      $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      $branches        = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data loaded', [
        'performanceTypes' => $performanceTypes->count(),
        DatabaseConstants::TABLE_BRANCHES        => $branches->count()
      ]);
      return view(self::ENTITY . '.' . __FUNCTION__, compact(
        DatabaseConstants::TABLE_BRANCHES,
        'performanceTypes'
      ));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function store(Request $request): RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['user' => Auth::id()]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      $user = $request->user();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
        strtolower(class_basename(Permission::class)) => 'create appraisal'
      ]);
      if (!$user?->can('create appraisal')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [UsersConstants::COL_USER_ID =>
        $user?->id]);
        throw new \Illuminate\Auth\Access\AuthorizationException();
      }
      $validator = Validator::make($request->all(), [
        strtolower(class_basename(Branch::class))         => 'required|exists:' . DatabaseConstants::TABLE_BRANCHES . ',id',
        strtolower(class_basename(Employee::class))       => 'required|exists:' . DatabaseConstants::TABLE_EMPLOYEES . ',id',
        'appraisal_date' => 'required|date',
        'rating'         => 'required|array',
      ]);
      if ($validator->fails()) {
        $msg = $validator->errors()->first();
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' validation failed', ['error' => $msg]);
        return back()->with('error', $msg);
      }
      DB::transaction(function () use ($request, $user) {
        $data = $request->only([strtolower(class_basename(Branch::class)), strtolower(class_basename(Employee::class)), 'remark', 'appraisal_date', 'rating']);
        $this->buildAppraisal(new Appraisal(), $data, $user?->creatorId());
      });
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' committed');
      return redirect()->route(self::ENTITY . '.index')
        ->with('success', __('Appraisal successfully created.'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function show(Request $request, Appraisal $appraisal): View|RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['appraisal_id' => $appraisal->id]);
    try {
      $rating          = json_decode($appraisal->rating, true);
      $creatorId       = $request->user()->creatorId();
      $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      $employee        = Employee::findOrFail($appraisal->employee);
      $indicator       = Indicator::where([
        [strtolower(class_basename(Branch::class)), $employee->branch_id],
        [strtolower(class_basename(Department::class)), $employee->department_id],
        [strtolower(class_basename(Designation::class)), $employee->designation_id]
      ])->first();
      $ratings         = $indicator
        ? json_decode($indicator->rating, true)
        : [];
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data ready', [
        'performanceTypes' => $performanceTypes->count(),
        'ratings' => count($ratings),
        'rating' => count($rating)
      ]);
      return view(self::ENTITY . '.' . __FUNCTION__, compact(
        'appraisal',
        'performanceTypes',
        'ratings',
        'rating'
      ));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function edit(Request $request, Appraisal $appraisal): View|RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['appraisal_id' => $appraisal->id]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      $user = $request->user();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
        strtolower(class_basename(Permission::class)) => 'edit appraisal'
      ]);
      if (!$user?->can('edit appraisal')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [UsersConstants::COL_USER_ID
        => $user?->id]);
        throw new \Illuminate\Auth\Access\AuthorizationException();
      }

      $creatorId       = $user?->creatorId();
      $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      $branches        = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      $ratings         = json_decode($appraisal->rating, true);
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data loaded', [
        'performanceTypes' => $performanceTypes->count(),
        DatabaseConstants::TABLE_BRANCHES => $branches->count(),
        'ratings' => count($ratings)
      ]);
      return view(self::ENTITY . '.' . __FUNCTION__, compact(
        DatabaseConstants::TABLE_BRANCHES,
        'appraisal',
        'performanceTypes',
        'ratings'
      ));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function update(Request $request, Appraisal $appraisal): RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['appraisal_id' => $appraisal->id]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      $user = $request->user();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
        strtolower(class_basename(Permission::class)) => 'edit appraisal'
      ]);
      if (!$user?->can('edit appraisal')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [UsersConstants::COL_USER_ID
        => $user?->id]);
        throw new \Illuminate\Auth\Access\AuthorizationException();
      }
      $validator = Validator::make($request->all(), [
        strtolower(class_basename(Branch::class))         => 'required|exists:' . DatabaseConstants::TABLE_BRANCHES . ',id',
        strtolower(class_basename(Employee::class))       => 'required|exists:' . DatabaseConstants::TABLE_EMPLOYEES . ',id',
        'appraisal_date' => 'required|date',
        'rating'         => 'required|array',
      ]);
      if ($validator->fails()) {
        $msg = $validator->errors()->first();
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' validation failed', ['error' => $msg]);
        return back()->with('error', $msg);
      }
      DB::transaction(function () use ($request, $appraisal, $user) {
        $lock = Appraisal::where('id', $appraisal->id)
          ->lockForUpdate()->firstOrFail();
        $data = $request->only([strtolower(class_basename(Branch::class)), strtolower(class_basename(Employee::class)), 'remark', 'appraisal_date', 'rating']);
        $this->buildAppraisal($lock, $data, $user?->creatorId());
      });
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' committed');
      return redirect()->route(self::ENTITY . '.index')
        ->with('success', __('Appraisal successfully updated.'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function destroy(Request $request, Appraisal $appraisal): RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['appraisal_id' => $appraisal->id]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      $user = $request->user();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
        strtolower(class_basename(Permission::class)) => 'delete appraisal'
      ]);
      if (
        !$user?->can('delete appraisal') ||
        $appraisal[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()
      ) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [UsersConstants::COL_USER_ID
        => $user?->id]);
        throw new \Illuminate\Auth\Access\AuthorizationException();
      }
      DB::transaction(function () use ($appraisal) {
        $lock = Appraisal::where('id', $appraisal->id)
          ->lockForUpdate()->firstOrFail();
        $lock->delete();
      });
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' deleted');
      return redirect()->route(self::ENTITY . '.index')
        ->with('success', __('Appraisal successfully deleted.'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function empByStar(Request $request): JsonResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['employee_id' => $request->employee]);
    try {
      $employee = Employee::findOrFail($request->employee);
      $indicator = Indicator::where([
        [strtolower(class_basename(Branch::class)), $employee->branch_id],
        [strtolower(class_basename(Department::class)), $employee->department_id],
        [strtolower(class_basename(Designation::class)), $employee->designation_id],
      ])->first();
      $ratings = $indicator ? json_decode($indicator->rating, true) : [];
      $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get();
      $html = view(self::ENTITY . '.star', compact('ratings', 'performanceTypes'))->render();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' rendered', [
        'ratings' => count($ratings),
        'performanceTypes' => count($performanceTypes)
      ]);
      return response()->json(['success' => true, 'html' => $html]);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
      return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function empByStar1(Request $request): JsonResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
      strtolower(class_basename(Employee::class)) => $request->employee, 'appraisal' => $request->appraisal
    ]);
    try {
      $employee = Employee::findOrFail($request->employee);
      $appraisal = Appraisal::findOrFail($request->appraisal);
      $indicator = Indicator::where([
        [strtolower(class_basename(Branch::class)), $employee->branch_id],
        [strtolower(class_basename(Department::class)), $employee->department_id],
        [strtolower(class_basename(Designation::class)), $employee->designation_id],
      ])->first();
      $ratings        = $indicator ? json_decode($indicator->rating, true) : [];
      $rating         = json_decode($appraisal->rating, true);
      $performanceTypes = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $request
        ->user()->creatorId())->get();
      $html = view(self::ENTITY . '.star_edit', compact('ratings', 'rating', 'performanceTypes'))->render();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' rendered', [
        'ratings' => count($ratings),
        'rating' => count($rating),
        'performanceTypes' => count($performanceTypes)
      ]);
      return response()->json(['success' => true, 'html' => $html]);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
      return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getEmployee(Request $request): JsonResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['branch_id' => $request->branch_id]);
    try {
      $employees = Employee::where('branch_id', $request->branch_id)->get();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', ['count' => $employees->count()]);
      return response()->json([strtolower(class_basename(Employee::class)) => $employees]);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
      return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  private function buildAppraisal(Appraisal $appraisal, array $data, int $creatorId): Appraisal
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' building appraisal', [
      'data'      => Arr::only($data, [
        strtolower(class_basename(Branch::class)), strtolower(class_basename(Employee::class)),
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
