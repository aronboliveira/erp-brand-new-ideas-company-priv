<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{Commission, Employee};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Auth, DB, Log, Route, View as ViewFacade};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class CommissionController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

  use ChecksLogin;
  use ChecksPermissions;

  public function __construct()
  {
    $this->middleware(MiddlewaresConstants::AUTH);
  }

  public const COM_CR = 'commissionCreate';
  public function commissionCreate(Request $request, int|string $employeeId): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::COM . '.create';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $employeeId, $action, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id, UsersConstants::COL_EMP_ID => $employeeId]);
        if ($resp = $this->_authorize($req, 'create commission')) {
          Log::warning("[{$class}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $req->user()->id]);
          return $resp;
        }
        $employee = Employee::find($employeeId);
        if (!$employee) {
          Log::warning("[{$class}::{$action}] employee not found", [UsersConstants::COL_EMP_ID => $employeeId]);
          return redirect()->back()->with('error', __('Employee not found.'));
        }
        $types = Commission::$commissionType;
        Log::info("[{$class}::{$action}] ready", [UsersConstants::COL_EMP_ID => $employee->id, 'types_count' => count($types)]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('employee', 'types'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'employee_id' => $employeeId]);
  }

  public function index(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::COM . '.index';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id]);
        if ($resp = $this->_authorize($req, 'view commission')) {
          Log::warning("[{$class}::{$action}] unauthorized");
          return $resp;
        }
        $creatorId = $user?->creatorId();
        $commissions = Commission::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->orderByDesc('id')->get();
        Log::info("[{$class}::{$action}] fetched commissions", ['count' => $commissions->count()]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('commissions'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request, int|string|null $employeeId = null): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::COM . '.create';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $employeeId, $action, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id, UsersConstants::COL_EMP_ID => $employeeId]);
        if ($resp = $this->_authorize($req, 'create commission')) {
          Log::warning("[{$class}::{$action}] unauthorized");
          return $resp;
        }
        $employee = Employee::find($employeeId);
        if (!$employee) {
          Log::warning("[{$class}::{$action}] employee not found", [UsersConstants::COL_EMP_ID => $employeeId]);
          return redirect()->back()->with('error', __('Employee not found.'));
        }
        $types = Commission::$commissionType;
        Log::info("[{$class}::{$action}] data ready", ['types' => count($types)]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('employee', 'types'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'employee_id' => $employeeId]);
  }

  public function store(Request $request): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'input' => $req->only([UsersConstants::COL_EMP_ID, 'title', 'type', 'amount'])]);
        if ($resp = $this->_authorize($req, 'create commission')) {
          Log::warning("[{$class}::{$action}] unauthorized");
          return $resp;
        }
        $valStart = microtime(true);
        $data = $req->validate([UsersConstants::COL_EMP_ID => 'required|exists:employees,id', 'title' => 'required|string', 'type' => 'required', 'amount' => 'required|numeric']);
        $this->logExecutionTime($valStart, $action, 'validateStore');
        $creatorId = $user?->creatorId();
        $txnStart = microtime(true);
        DB::transaction(function () use ($data, $creatorId, $action, $class) {
          $commission = Commission::create([UsersConstants::COL_EMP_ID => $data[UsersConstants::COL_EMP_ID], 'title' => $data['title'], 'type' => $data['type'], 'amount' => $data['amount'], DatabaseConstants::COL_TABLE_CREATOR => $creatorId]);
          Log::info("[{$class}::{$action}] created", ['commission_id' => $commission->id]);
        });
        $this->logExecutionTime($txnStart, $action, 'storeTransaction');
        return redirect()->back()->with('success', __('Commission successfully created.'));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Commission $commission): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($commission, $action, $class) {
      Log::info("[{$class}::{$action}] redirecting", ['commission_id' => $commission->id]);
      return redirect()->route(ViewsConstants::COM . '.index');
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'commission_id' => $commission->id]);
  }

  public function edit(Request $request, int|string $id): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::COM . '.edit';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $id, $action, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", ['id' => $id]);
        if ($resp = $this->_authorize($req, 'edit commission')) {
          Log::warning("[{$class}::{$action}] unauthorized");
          return $resp;
        }
        $commission = Commission::findOrFail($id);
        if ($resp = $this->authorizeOwnership($req, $commission)) return $resp;
        $types = Commission::$commissionType;
        Log::info("[{$class}::{$action}] data ready", ['commission_id' => $commission->id, 'types' => count($types)]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('commission', 'types'));
      } catch (AuthorizationException $e) {
        Log::error("[{$class}::{$action}] auth error", ['error' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'commission_id' => $id]);
  }

  public function update(Request $request, Commission $commission): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $commission, $action, $class) {
      try {
        Log::info("[{$class}::{$action}] start", ['commission_id' => $commission->id, 'input' => $req->only(['title', 'type', 'amount'])]);
        if ($resp = $this->_authorize($req, 'edit commission')) {
          Log::warning("[{$class}::{$action}] unauthorized");
          return $resp;
        }
        if ($resp = $this->authorizeOwnership($req, $commission)) return $resp;
        $valStart = microtime(true);
        $data = $req->validate(['title' => 'required|string', 'type' => 'required', 'amount' => 'required|numeric']);
        $this->logExecutionTime($valStart, $action, 'validateUpdate');
        $txnStart = microtime(true);
        DB::transaction(function () use ($commission, $data, $action, $class) {
          $lock = Commission::where('id', $commission->id)->lockForUpdate()->firstOrFail();
          $lock->update($data);
          Log::info("[{$class}::{$action}] updated", ['commission_id' => $commission->id]);
        });
        $this->logExecutionTime($txnStart, $action, 'updateTransaction');
        return redirect()->back()->with('success', __('Commission successfully updated.'));
      } catch (AuthorizationException $e) {
        Log::error("[{$class}::{$action}] auth error", ['error' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'commission_id' => $commission->id]);
  }

  public function destroy(Request $request, Commission $commission): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $commission, $action, $class) {
      try {
        Log::info("[{$class}::{$action}] start", ['commission_id' => $commission->id]);
        if ($resp = $this->_authorize($req, 'delete commission')) {
          Log::warning("[{$class}::{$action}] unauthorized");
          return $resp;
        }
        if ($resp = $this->authorizeOwnership($req, $commission)) return $resp;
        $txnStart = microtime(true);
        DB::transaction(function () use ($commission, $action, $class) {
          $lock = Commission::where('id', $commission->id)->lockForUpdate()->firstOrFail();
          $lock->delete();
          Log::info("[{$class}::{$action}] deleted", ['commission_id' => $commission->id]);
        });
        $this->logExecutionTime($txnStart, $action, 'destroyTransaction');
        return redirect()->back()->with('success', __('Commission successfully deleted.'));
      } catch (AuthorizationException $e) {
        Log::error("[{$class}::{$action}] auth error", ['error' => $e->getMessage()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e->getMessage()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'commission_id' => $commission->id]);
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

  /**
   * Authorize the current request for a given permission.
   *
   * Returns a redirect response when denied, or null when authorized.
   */
  private function _authorize(Request $request, string $ability): ?RedirectResponse
  {
    $result = self::guard($request, $ability);
    return $result === true ? null : $result;
  }
}
