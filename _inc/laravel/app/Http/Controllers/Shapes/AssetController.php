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

final class AssetController extends Controller
{

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
  }

  public function index(Request $request): View|RedirectResponse
  {
    try {
      if (!$request->user()?->can(PermissionsConstants::MNG_AST))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $user      = $request->user();
      $assets    = Asset::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
      return view(ViewsConstants::AST . '.' . __FUNCTION__, compact('assets'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function create(Request $request): View|RedirectResponse
  {
    try {
      if (!$request->user()?->can(PermissionsConstants::CRT_AST))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $employeeList = Employee::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->pluck(UsersConstants::COL_NM, 'id');
      return view(ViewsConstants::AST . '.' . __FUNCTION__, compact('employeeList'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function store(Request $request): RedirectResponse
  {
    try {
      if (!$request->user()?->can(PermissionsConstants::CRT_AST))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $data = $request->validate([
        'name'           => 'required|string',
        'purchase_date'  => 'required|date',
        'supported_date' => 'required|date',
        'amount'         => 'required|numeric',
        UsersConstants::COL_EMP_ID    => 'array',
        'description'    => 'nullable|string',
      ]);
      $asset = new Asset();
      $asset->fill([
        'name'           => $data['name'],
        'purchase_date'  => $data['purchase_date'],
        'supported_date' => $data['supported_date'],
        'amount'         => $data['amount'],
        'description'    => $data['description'] ?? '',
        UsersConstants::COL_EMP_ID    => isset($data[UsersConstants::COL_EMP_ID])
          ? implode(',', $data[UsersConstants::COL_EMP_ID])
          : '',
        DatabaseConstants::TABLE_CREATOR     => $request->user()->creatorId(),
      ]);
      $asset->save();
      return redirect()
        ->route(BaseRoutesConstants::ACC_AST . '.' . __FUNCTION__)
        ->with('success', __('Assets successfully created.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(BaseRoutesConstants::ACC_AST . '.' . __FUNCTION__));
    }
  }

  public function show(Request $request, Asset $asset): View|RedirectResponse
  {
    try {
      if (
        !$request->user()?->can(PermissionsConstants::VIW_AST)
        || $asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()
      )
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      return view(ViewsConstants::AST . '.' . __FUNCTION__, compact('asset'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function edit(Request $request, int|string $id): View|RedirectResponse
  {
    try {
      if (!$request->user()?->can(PermissionsConstants::ED_AST))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $asset       = Asset::findOrFail($id);
      if ($asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $employeeList = Employee::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->pluck(UsersConstants::COL_NM, 'id');
      $asset[UsersConstants::COL_EMP_ID] = explode(',', $asset[UsersConstants::COL_EMP_ID]);
      return view(ViewsConstants::AST . '.' . __FUNCTION__, compact('asset', 'employeeList'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function update(Request $request, int|string $id): RedirectResponse
  {
    try {
      if (!$request->user()?->can(PermissionsConstants::ED_AST))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $asset = Asset::findOrFail($id);
      if ($asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $data = $request->validate([
        'name'           => 'required|string',
        'purchase_date'  => 'required|date',
        'supported_date' => 'required|date',
        'amount'         => 'required|numeric',
        UsersConstants::COL_EMP_ID    => 'array',
        'description'    => 'nullable|string',
      ]);
      $asset->update([
        'name'           => $data['name'],
        'purchase_date'  => $data['purchase_date'],
        'supported_date' => $data['supported_date'],
        'amount'         => $data['amount'],
        'description'    => $data['description'] ?? '',
        UsersConstants::COL_EMP_ID    => isset($data[UsersConstants::COL_EMP_ID])
          ? implode(',', $data[UsersConstants::COL_EMP_ID])
          : '',
      ]);
      return redirect()
        ->route(BaseRoutesConstants::ACC_AST . '.index')
        ->with('success', __('Assets successfully updated.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(BaseRoutesConstants::ACC_AST . '.index'));
    }
  }

  public function destroy(Request $request, int|string $id): RedirectResponse
  {
    try {
      if (!$request->user()?->can(PermissionsConstants::DEL_AST))
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $asset = Asset::findOrFail($id);
      if ($asset[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
        return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
      $asset->delete();
      return redirect()
        ->route(BaseRoutesConstants::ACC_AST . '.index')
        ->with('success', __('Assets successfully deleted.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(BaseRoutesConstants::ACC_AST . '.index'));
    }
  }
}
