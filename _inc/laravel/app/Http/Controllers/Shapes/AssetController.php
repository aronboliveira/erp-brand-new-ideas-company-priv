<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  BaseRoutesConstants,
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

final class AssetController extends Controller
{
  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
  }

  public function index(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $action, $method) {
      try {
        $t = microtime(true);
        if (!$request->user()?->can(PermissionsConstants::MNG_AST)) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::authorize', 'completed');

        $t = microtime(true);
        $user   = $request->user();
        $assets = Asset::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
        $this->logExecutionTime($t, $action . '::fetchAssets', 'completed');

        $viewPath = ViewsConstants::AST . '.' . $action;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
        if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

        return view($viewPath, compact('assets'));
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, $method);
      }
    });
  }

  public function create(Request $request): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $action, $method) {
      try {
        $t = microtime(true);
        if (!$request->user()?->can(PermissionsConstants::CRT_AST)) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::authorize', 'completed');

        $t = microtime(true);
        $employeeList = Employee::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
          ->pluck(UsersConstants::COL_NM, 'id');
        $this->logExecutionTime($t, $action . '::pluckEmployees', 'completed');

        $viewPath = ViewsConstants::AST . '.' . $action;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
        if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

        return view($viewPath, compact('employeeList'));
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, $method);
      }
    });
  }

  public function store(Request $request): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $action, $method) {
      try {
        $t = microtime(true);
        if (!$request->user()?->can(PermissionsConstants::CRT_AST)) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::authorize', 'completed');

        $t = microtime(true);
        $data = $request->validate([
          'name'           => 'required|string',
          'purchase_date'  => 'required|date',
          'supported_date' => 'required|date',
          'amount'         => 'required|numeric',
          UsersConstants::COL_EMP_ID => 'array',
          'description'    => 'nullable|string',
        ]);
        $this->logExecutionTime($t, $action . '::validate', 'completed');

        $t = microtime(true);
        $asset = new Asset();
        $asset->fill([
          'name'           => $data['name'],
          'purchase_date'  => $data['purchase_date'],
          'supported_date' => $data['supported_date'],
          'amount'         => $data['amount'],
          'description'    => $data['description'] ?? '',
          UsersConstants::COL_EMP_ID => isset($data[UsersConstants::COL_EMP_ID])
            ? implode(',', $data[UsersConstants::COL_EMP_ID])
            : '',
          DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId(),
        ]);
        $asset->save();
        $this->logExecutionTime($t, $action . '::persist', 'completed');

        return redirect()
          ->route(BaseRoutesConstants::ACC_AST . '.' . $action)
          ->with('success', __('Assets successfully created.'));
      } catch (\Throwable $e) {
        return defaultUndefinedException(
          $request,
          $e,
          $method,
          route(BaseRoutesConstants::ACC_AST . '.' . $action)
        );
      }
    });
  }

  public function show(Request $request, Asset $asset): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $asset, $action, $method) {
      try {
        $t = microtime(true);
        if (
          !$request->user()?->can(PermissionsConstants::VIW_AST)
          || $asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()
        ) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::authorizeAndOwnership', 'completed');

        $viewPath = ViewsConstants::AST . '.' . $action;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
        if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

        return view($viewPath, compact('asset'));
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, $method);
      }
    });
  }

  public function edit(Request $request, int|string $id): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
      try {
        $t = microtime(true);
        if (!$request->user()?->can(PermissionsConstants::ED_AST)) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::authorize', 'completed');

        $t = microtime(true);
        $asset = Asset::findOrFail($id);
        if ($asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
          return defaultPermissionDenial($request, null, $method);
        }
        $employeeList = Employee::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
          ->pluck(UsersConstants::COL_NM, 'id');
        $asset[UsersConstants::COL_EMP_ID] = explode(',', $asset[UsersConstants::COL_EMP_ID]);
        $this->logExecutionTime($t, $action . '::loadAssetAndEmployees', 'completed');

        $viewPath = ViewsConstants::AST . '.' . $action;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
        if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

        return view($viewPath, compact('asset', 'employeeList'));
      } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, $method);
      }
    });
  }

  public function update(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
      try {
        $t = microtime(true);
        if (!$request->user()?->can(PermissionsConstants::ED_AST)) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::authorize', 'completed');

        $t = microtime(true);
        $asset = Asset::findOrFail($id);
        if ($asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::ownershipCheck', 'completed');

        $t = microtime(true);
        $data = $request->validate([
          'name'           => 'required|string',
          'purchase_date'  => 'required|date',
          'supported_date' => 'required|date',
          'amount'         => 'required|numeric',
          UsersConstants::COL_EMP_ID => 'array',
          'description'    => 'nullable|string',
        ]);
        $this->logExecutionTime($t, $action . '::validate', 'completed');

        $t = microtime(true);
        $asset->update([
          'name'           => $data['name'],
          'purchase_date'  => $data['purchase_date'],
          'supported_date' => $data['supported_date'],
          'amount'         => $data['amount'],
          'description'    => $data['description'] ?? '',
          UsersConstants::COL_EMP_ID => isset($data[UsersConstants::COL_EMP_ID])
            ? implode(',', $data[UsersConstants::COL_EMP_ID])
            : '',
        ]);
        $this->logExecutionTime($t, $action . '::persist', 'completed');

        return redirect()
          ->route(BaseRoutesConstants::ACC_AST . '.index')
          ->with('success', __('Assets successfully updated.'));
      } catch (\Throwable $e) {
        return defaultUndefinedException(
          $request,
          $e,
          $method,
          route(BaseRoutesConstants::ACC_AST . '.index')
        );
      }
    });
  }

  public function destroy(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
      try {
        $t = microtime(true);
        if (!$request->user()?->can(PermissionsConstants::DEL_AST)) {
          return defaultPermissionDenial($request, null, $method);
        }
        $this->logExecutionTime($t, $action . '::authorize', 'completed');

        $t = microtime(true);
        $asset = Asset::findOrFail($id);
        if ($asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
          return defaultPermissionDenial($request, null, $method);
        }
        $asset->delete();
        $this->logExecutionTime($t, $action . '::delete', 'completed');

        return redirect()
          ->route(BaseRoutesConstants::ACC_AST . '.index')
          ->with('success', __('Assets successfully deleted.'));
      } catch (\Throwable $e) {
        return defaultUndefinedException(
          $request,
          $e,
          $method,
          route(BaseRoutesConstants::ACC_AST . '.index')
        );
      }
    });
  }
}
