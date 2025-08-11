<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{Commission, Employee};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};

class CommissionController extends Controller
{
  use ChecksLogin;
  use ChecksPermissions;

  public function __construct()
  {
    $this->middleware('auth');
  }

  public const COM_CR = 'commissionCreate';
  public function commissionCreate(Request $request, int|string $employeeId): View|RedirectResponse
  {
    Log::info(__METHOD__ . ' start', [
      UsersConstants::COL_USER_ID     => $request->user()->id,
      UsersConstants::COL_EMP_ID => $employeeId,
    ]);
    try {
      if ($resp = $this->_authorize($request, 'create commission')) {
        Log::warning(__METHOD__ . ' unauthorized', [UsersConstants::COL_USER_ID => $request->user()->id]);
        return $resp;
      }
      $employee = Employee::find($employeeId);
      if (!$employee) {
        Log::warning(__METHOD__ . ' employee not found', [UsersConstants::COL_EMP_ID => $employeeId]);
        return redirect()->back()->with('error', __('Employee not found.'));
      }
      $types = Commission::$commissiontype;
      Log::info(__METHOD__ . ' ready', [
        UsersConstants::COL_EMP_ID => $employee->id,
        'types_count' => count($types),
      ]);
      return view('commissions.create', compact('employee', 'types'));
    } catch (AuthorizationException $e) {
      Log::error(__METHOD__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial($request, $e, __METHOD__);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' unexpected error', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__);
    }
  }

  public function index(Request $request): View|RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
      UsersConstants::COL_USER_ID => $request->user()->id
    ]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      if ($resp = $this->_authorize($request, 'view commission')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized');
        return $resp;
      }
      $user      = $request->user();
      $creatorId = $user?->creatorId();
      $commissions = Commission::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->orderByDesc('id')
        ->get();
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched commissions', [
        'count' => $commissions->count()
      ]);
      return view('commissions.index', compact('commissions'));
    } catch (AuthorizationException $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e->getMessage()]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function create(Request $request, int|string $employeeId): View|RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
      UsersConstants::COL_USER_ID    => $request->user()->id,
      UsersConstants::COL_EMP_ID => $employeeId
    ]);
    try {
      if ($resp = $this->_authorize($request, 'create commission')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized');
        return $resp;
      }
      $employee = Employee::find($employeeId);
      if (!$employee) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' employee not found', [UsersConstants::COL_EMP_ID => $employeeId]);
        return redirect()->back()
          ->with('error', __('Employee not found.'));
      }
      $types = Commission::$commissiontype;
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data ready', ['types' => count($types)]);
      return view('commissions.create', compact('employee', 'types'));
    } catch (AuthorizationException $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function store(Request $request): RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
      UsersConstants::COL_USER_ID => $request->user()->id,
      'input'   => $request->only([UsersConstants::COL_EMP_ID, 'title', 'type', 'amount'])
    ]);
    try {
      if (($r = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
        return $r;
      }
      if ($resp = $this->_authorize($request, 'create commission')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized');
        return $resp;
      }
      $data = $request->validate([
        UsersConstants::COL_EMP_ID => 'required|exists:employees,id',
        'title'       => 'required|string',
        'type'        => 'required',
        'amount'      => 'required|numeric'
      ]);
      $user     = $request->user();
      $creatorId = $user?->creatorId();
      DB::transaction(function () use ($data, $creatorId) {
        $commission = Commission::create([
          UsersConstants::COL_EMP_ID => $data[UsersConstants::COL_EMP_ID],
          'title'       => $data['title'],
          'type'        => $data['type'],
          'amount'      => $data['amount'],
          DatabaseConstants::TABLE_CREATOR  => $creatorId
        ]);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' created', ['commission_id' => $commission->id]);
      });
      return redirect()->back()
        ->with('success', __('Commission successfully created.'));
    } catch (AuthorizationException $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function show(Commission $commission): RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' redirecting', ['commission_id' => $commission->id]);
    return redirect()->route('commissions.index');
  }

  public function edit(Request $request, int|string $id): View|RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $id]);
    try {
      if ($resp = $this->_authorize($request, 'edit commission')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized');
        return $resp;
      }
      $commission = Commission::findOrFail($id);
      if ($resp = $this->authorizeOwnership($request, $commission)) {
        return $resp;
      }
      $types = Commission::$commissiontype;
      Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data ready', [
        'commission_id' => $commission->id,
        'types' => count($types)
      ]);
      return view('commissions.edit', compact('commission', 'types'));
    } catch (AuthorizationException $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function update(Request $request, Commission $commission): RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
      'commission_id' => $commission->id,
      'input' => $request->only(['title', 'type', 'amount'])
    ]);
    try {
      if ($resp = $this->_authorize($request, 'edit commission')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized');
        return $resp;
      }
      if ($resp = $this->authorizeOwnership($request, $commission)) {
        return $resp;
      }
      $data = $request->validate([
        'title'  => 'required|string',
        'type'   => 'required',
        'amount' => 'required|numeric'
      ]);
      DB::transaction(function () use ($commission, $data) {
        $lock = Commission::where('id', $commission->id)
          ->lockForUpdate()
          ->firstOrFail();
        $lock->update($data);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' updated', ['commission_id' => $commission->id]);
      });
      return redirect()->back()
        ->with('success', __('Commission successfully updated.'));
    } catch (AuthorizationException $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function destroy(Request $request, Commission $commission): RedirectResponse
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['commission_id' => $commission->id]);
    try {
      if ($resp = $this->_authorize($request, 'delete commission')) {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized');
        return $resp;
      }
      if ($resp = $this->authorizeOwnership($request, $commission)) {
        return $resp;
      }
      DB::transaction(function () use ($commission) {
        $lock = Commission::where('id', $commission->id)
          ->lockForUpdate()
          ->firstOrFail();
        $lock->delete();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' deleted', ['commission_id' => $commission->id]);
      });
      return redirect()->back()
        ->with('success', __('Commission successfully deleted.'));
    } catch (AuthorizationException $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' auth error', ['error' => $e->getMessage()]);
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  protected function authorizeOwnership(Request $request, Commission $commission): RedirectResponse|null
  {
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
      UsersConstants::COL_USER_ID       => $request->user()->id,
      'commission_id' => $commission->id
    ]);
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged in');
      return $userOrRedirect;
    }
    $user = $userOrRedirect;
    if ($commission->created_by !== $user?->creatorId()) {
      Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' forbidden', [
        UsersConstants::COL_USER_ID          => $user?->id,
        'commission_owner' => $commission->created_by
      ]);
      return defaultPermissionDenial(
        $request,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    Log::info(__CLASS__ . '::' . __FUNCTION__ . ' ownership verified');
    return null;
  }
}
