<?php

namespace App\Http\Controllers\Shapes;

use App\Config\Constants\{
  BaseRoutesConstants as BRC,
  CompaniesConstants as CPC,
  DatabaseConstants as DC,
  MiddlewaresConstants as MWC,
  PermissionsConstants as PMC,
  UsersConstants as UC,
  ViewsConstants as VW
};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{Asset, Employee};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\DefinesResourceActions;
final class AssetController extends Controller
{
	use DefinesResourceActions;

  use ChecksLogin, ChecksPermissions;

  private const REDIRECT_INDEX = BRC::ACC_AST . '.index';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

  public function __construct()
  {
    $this->middleware([MWC::AUTH, MWC::XSS]);
  }

  private function checkOwnership(
    Request $request,
    Asset $asset,
    string $action
  ): ?RedirectResponse {
    $file = __FILE__;
    $user = $request->user();
    $creatorId = $user?->creatorId() ?? null;
    $assetCreator = $asset[DC::COL_TABLE_CREATOR] ?? null;
    if (empty($creatorId) || $assetCreator !== $creatorId) {
      Log::warning("{$action} ownership validation failed", [
        'file' => $file,
        'class' => __CLASS__,
        'asset_id' => $asset->id ?? null,
        'asset_creator' => $assetCreator,
        'user_creator' => $creatorId
      ]);
      return defaultPermissionDenial($request, new \Exception('Owner mismatch'), $action);
    }
    return null;
  }

  public function index(Request $request): View|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "{$cls}::{$fn}";
    $file = __FILE__;
    return $this->measureProfile($action, function () use (
      $request,
      $action,
      $file,
      $cls,
      $fn
    ) {
      try {
        $t = microtime(true);
        $user = $request->user();
        if (empty($user) || !$user->can(PMC::MNG_AST))
          return defaultPermissionDenial($request, null, $action);
        $this->logExecutionTime($t, $fn . '::authorize', 'completed');
        $t = microtime(true);
        $creatorId = $user->creatorId() ?? null;
        $assets = Asset::where(DC::COL_TABLE_CREATOR, $creatorId)->with('employees')->get();
        $this->logExecutionTime($t, $fn . '::fetchAssets', 'completed');
        $viewPath = VW::AST . '.' . $fn;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $fn . '::viewExistsCheck', 'completed');
        if (!$exists) {
          Log::error("{$action} View not found", [
            'file' => $file,
            'class' => $cls,
            'view' => $viewPath
          ]);
          return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        return view($viewPath, compact('assets'));
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException($request, $re, $action);
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException($request, $e, $action);
      }
    });
  }

  public function create(Request $request): View|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "{$cls}::{$fn}";
    $file = __FILE__;
    return $this->measureProfile($action, function () use (
      $request,
      $action,
      $file,
      $cls,
      $fn
    ) {
      try {
        $t = microtime(true);
        $user = $request->user();
        if (empty($user) || !$user->can(PMC::CRT_AST))
          return defaultPermissionDenial($request, null, $action);
        $this->logExecutionTime($t, $fn . '::authorize', 'completed');
        $t = microtime(true);
        $creatorId = $user->creatorId() ?? null;
        $employeeList = Employee::where(DC::COL_TABLE_CREATOR, $creatorId)
          ->pluck(UC::COL_NM, 'id');
        $this->logExecutionTime($t, $fn . '::pluckEmployees', 'completed');
        $viewPath = VW::AST . '.' . $fn;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $fn . '::viewExistsCheck', 'completed');
        if (!$exists) {
          Log::error("{$action} View not found", [
            'file' => $file,
            'class' => $cls,
            'view' => $viewPath
          ]);
          return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        return view($viewPath, compact('employeeList'));
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException($request, $re, $action);
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException($request, $e, $action);
      }
    });
  }

  public function store(Request $request): RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "{$cls}::{$fn}";
    $file = __FILE__;
    return $this->measureProfile($action, function () use (
      $request,
      $action,
      $file,
      $cls,
      $fn
    ) {
      try {
        $t = microtime(true);
        $user = $request->user();
        if (empty($user) || !$user->can(PMC::CRT_AST))
          return defaultPermissionDenial($request, null, $action);
        $this->logExecutionTime($t, $fn . '::authorize', 'completed');
        $t = microtime(true);
        $data = $request->validate([
          'name' => 'required|string',
          CPC::COL_PRC_DT => 'required|date',
          CPC::COL_SPT_DT => 'required|date',
          'amount' => 'required|numeric',
          UC::COL_EMP_ID => 'array',
          'description' => 'nullable|string',
        ]);
        $this->logExecutionTime($t, $fn . '::validate', 'completed');
        $t = microtime(true);
        $asset = new Asset();
        $empIds = $data[UC::COL_EMP_ID] ?? null;
        $asset->fill([
          'name' => $data['name'] ?? '',
          CPC::COL_PRC_DT => $data[CPC::COL_PRC_DT] ?? null,
          CPC::COL_SPT_DT => $data[CPC::COL_SPT_DT] ?? null,
          'amount' => $data['amount'] ?? 0,
          'description' => $data['description'] ?? '',
          UC::COL_EMP_ID => is_array($empIds) ? implode(',', $empIds) : '',
          DC::COL_TABLE_CREATOR => $user->creatorId() ?? null,
        ]);
        $asset->save();
        $this->logExecutionTime($t, $fn . '::persist', 'completed');
        Log::info("{$action} success", ['asset_id' => $asset->id ?? null]);
        return redirect()
          ->route(BRC::ACC_AST . '.' . $fn)
          ->with('success', __('Assets successfully created.'));
      } catch (ValidationException $ve) {
        $errors = $ve->errors();
        $msg = is_array($errors)
          ? (collect($errors)->flatten()->first() ?? __('Validation failed'))
          : __('Validation failed');
        Log::warning("{$action} Validation failed", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($ve),
          'errors' => $errors
        ]);
        return redirect()->back()->withErrors($errors)->withInput();
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException(
          $request,
          $re,
          $action,
          route(BRC::ACC_AST . '.' . $fn)
        );
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException(
          $request,
          $e,
          $action,
          route(BRC::ACC_AST . '.' . $fn)
        );
      }
    });
  }

  public function show(Request $request, Asset $asset): View|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "{$cls}::{$fn}";
    $file = __FILE__;
    return $this->measureProfile($action, function () use (
      $request,
      $asset,
      $action,
      $file,
      $cls,
      $fn
    ) {
      try {
        $t = microtime(true);
        $user = $request->user();
        if (empty($user) || !$user->can(PMC::VIW_AST))
          return defaultPermissionDenial($request, null, $action);
        $ownershipError = $this->checkOwnership($request, $asset, $action);
        if ($ownershipError !== null)
          return $ownershipError;
        $this->logExecutionTime($t, $fn . '::authorizeAndOwnership', 'completed');
        $viewPath = VW::AST . '.' . $fn;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $fn . '::viewExistsCheck', 'completed');
        if (!$exists) {
          Log::error("{$action} View not found", [
            'file' => $file,
            'class' => $cls,
            'view' => $viewPath
          ]);
          return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        return view($viewPath, compact('asset'));
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException($request, $re, $action);
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException($request, $e, $action);
      }
    });
  }

  public function edit(Request $request, int|string $id): View|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "{$cls}::{$fn}";
    $file = __FILE__;
    return $this->measureProfile($action, function () use (
      $request,
      $id,
      $action,
      $file,
      $cls,
      $fn
    ) {
      try {
        $t = microtime(true);
        $user = $request->user();
        if (empty($user) || !$user->can(PMC::ED_AST))
          return defaultPermissionDenial($request, null, $action);
        $this->logExecutionTime($t, $fn . '::authorize', 'completed');
        $t = microtime(true);
        $asset = Asset::findOrFail($id);
        $ownershipError = $this->checkOwnership($request, $asset, $action);
        if ($ownershipError !== null)
          return $ownershipError;
        $creatorId = $user->creatorId() ?? null;
        $employeeList = Employee::where(DC::COL_TABLE_CREATOR, $creatorId)
          ->pluck(UC::COL_NM, 'id');
        $empIdStr = $asset[UC::COL_EMP_ID] ?? '';
        $asset[UC::COL_EMP_ID] = is_string($empIdStr) && !empty($empIdStr)
          ? explode(',', $empIdStr)
          : [];
        $this->logExecutionTime($t, $fn . '::loadAssetAndEmployees', 'completed');
        $viewPath = VW::AST . '.' . $fn;
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $fn . '::viewExistsCheck', 'completed');
        if (!$exists) {
          Log::error("{$action} View not found", [
            'file' => $file,
            'class' => $cls,
            'view' => $viewPath
          ]);
          return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        return view($viewPath, compact('asset', 'employeeList'));
      } catch (ModelNotFoundException $mnf) {
        Log::warning("{$action} Asset not found", [
          'file' => $file,
          'class' => $cls,
          'asset_id' => $id,
          'error_class' => get_class($mnf)
        ]);
        return redirect()->route(self::REDIRECT_INDEX)
          ->with('error', __('Asset not found.'));
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException($request, $re, $action);
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException($request, $e, $action);
      }
    });
  }

  public function update(Request $request, int|string $id): RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "{$cls}::{$fn}";
    $file = __FILE__;
    return $this->measureProfile($action, function () use (
      $request,
      $id,
      $action,
      $file,
      $cls,
      $fn
    ) {
      try {
        $t = microtime(true);
        $user = $request->user();
        if (empty($user) || !$user->can(PMC::ED_AST))
          return defaultPermissionDenial($request, null, $action);
        $this->logExecutionTime($t, $fn . '::authorize', 'completed');
        $t = microtime(true);
        $asset = Asset::findOrFail($id);
        $ownershipError = $this->checkOwnership($request, $asset, $action);
        if ($ownershipError !== null)
          return $ownershipError;
        $this->logExecutionTime($t, $fn . '::ownershipCheck', 'completed');
        $t = microtime(true);
        $data = $request->validate([
          'name' => 'required|string',
          CPC::COL_PRC_DT => 'required|date',
          CPC::COL_SPT_DT => 'required|date',
          'amount' => 'required|numeric',
          UC::COL_EMP_ID => 'array',
          'description' => 'nullable|string',
        ]);
        $this->logExecutionTime($t, $fn . '::validate', 'completed');
        $t = microtime(true);
        $empIds = $data[UC::COL_EMP_ID] ?? null;
        $asset->update([
          'name' => $data['name'] ?? '',
          CPC::COL_PRC_DT => $data[CPC::COL_PRC_DT] ?? null,
          CPC::COL_SPT_DT => $data[CPC::COL_SPT_DT] ?? null,
          'amount' => $data['amount'] ?? 0,
          'description' => $data['description'] ?? '',
          UC::COL_EMP_ID => is_array($empIds) ? implode(',', $empIds) : '',
        ]);
        $this->logExecutionTime($t, $fn . '::persist', 'completed');
        Log::info("{$action} success", ['asset_id' => $id]);
        return redirect()
          ->route(self::REDIRECT_INDEX)
          ->with('success', __('Assets successfully updated.'));
      } catch (ModelNotFoundException $mnf) {
        Log::warning("{$action} Asset not found", [
          'file' => $file,
          'class' => $cls,
          'asset_id' => $id,
          'error_class' => get_class($mnf)
        ]);
        return redirect()->route(self::REDIRECT_INDEX)
          ->with('error', __('Asset not found.'));
      } catch (ValidationException $ve) {
        $errors = $ve->errors();
        Log::warning("{$action} Validation failed", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($ve),
          'errors' => $errors
        ]);
        return redirect()->back()->withErrors($errors)->withInput();
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException(
          $request,
          $re,
          $action,
          route(self::REDIRECT_INDEX)
        );
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException(
          $request,
          $e,
          $action,
          route(self::REDIRECT_INDEX)
        );
      }
    });
  }

  public function destroy(Request $request, int|string $id): RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "{$cls}::{$fn}";
    $file = __FILE__;
    return $this->measureProfile($action, function () use (
      $request,
      $id,
      $action,
      $file,
      $cls,
      $fn
    ) {
      try {
        $t = microtime(true);
        $user = $request->user();
        if (empty($user) || !$user->can(PMC::DEL_AST))
          return defaultPermissionDenial($request, null, $action);
        $this->logExecutionTime($t, $fn . '::authorize', 'completed');
        $t = microtime(true);
        $asset = Asset::findOrFail($id);
        $ownershipError = $this->checkOwnership($request, $asset, $action);
        if ($ownershipError !== null)
          return $ownershipError;
        $asset->delete();
        $this->logExecutionTime($t, $fn . '::delete', 'completed');
        Log::info("{$action} deleted", ['asset_id' => $id]);
        return redirect()
          ->route(self::REDIRECT_INDEX)
          ->with('success', __('Assets successfully deleted.'));
      } catch (ModelNotFoundException $mnf) {
        Log::warning("{$action} Asset not found", [
          'file' => $file,
          'class' => $cls,
          'asset_id' => $id,
          'error_class' => get_class($mnf)
        ]);
        return redirect()->route(self::REDIRECT_INDEX)
          ->with('error', __('Asset not found.'));
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException(
          $request,
          $re,
          $action,
          route(self::REDIRECT_INDEX)
        );
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => $cls,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException(
          $request,
          $e,
          $action,
          route(self::REDIRECT_INDEX)
        );
      }
    });
  }
}
