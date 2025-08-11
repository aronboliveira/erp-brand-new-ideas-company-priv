<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  CompaniesConstants,
  DatabaseConstants,
  UsersConstants,
  ViewsConstants,
};
use App\Models\{Branch, Department};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\{
  Log,
  Validator
};

final class DepartmentController extends Controller
{
  use ChecksLogin, ChecksPermissions;
  private const REDIRECT_INDEX = '/';

  public function index(Request $r)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($r, 'manage department', self::REDIRECT_INDEX)) return $c;
    try {
      $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
      return view(ViewsConstants::DPT . '.' . __FUNCTION__, compact('departments'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function create(Request $r)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($r, 'create department', self::REDIRECT_INDEX)) return $c;
    try {
      $branch = self::branches($u->creatorId());
      return view(ViewsConstants::DPT . '.' . __FUNCTION__, compact('branch'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function store(Request $r): RedirectResponse|JsonResponse
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($r, 'create department', self::REDIRECT_INDEX)) return $c;
    if ($c = self::v($r, [CompaniesConstants::COL_BRC_ID => 'required', CompaniesConstants::COL_BRC_NM => 'required|max:20']))
      return $c;
    try {
      Department::create([
        CompaniesConstants::COL_BRC_ID => $r[CompaniesConstants::COL_BRC_ID],
        CompaniesConstants::COL_DEP_NM => $r[CompaniesConstants::COL_DEP_NM],
        DatabaseConstants::TABLE_CREATOR => $u->creatorId()
      ]);
      return redirect()->route(ViewsConstants::DPT . '.index')
        ->with('success', __('Department successfully created.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function edit(Request $r, Department $department)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($r, 'edit department', self::REDIRECT_INDEX)) return $c;
    if ($department[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId())
      return defaultPermissionDenial($r, new \Exception('owner'));
    try {
      $branch = self::branches($u->creatorId());
      return view(ViewsConstants::DPT . '.' . __FUNCTION__, compact('department', 'branch'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function update(
    Request $r,
    Department $department
  ): RedirectResponse|JsonResponse {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($r, 'edit department', self::REDIRECT_INDEX)) return $c;
    if ($department[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId())
      return redirect()->back()->with('error', __('Permission denied.'));
    if ($c = self::v($r, [CompaniesConstants::COL_BRC_ID => 'required', CompaniesConstants::COL_BRC_NM => 'required|max:20']))
      return $c;
    try {
      $department->update([
        CompaniesConstants::COL_BRC_ID => $r[CompaniesConstants::COL_BRC_ID],
        CompaniesConstants::COL_BRC_NM => $r[CompaniesConstants::COL_DEP_NM]
      ]);
      return redirect()->route(ViewsConstants::DPT . '.index')
        ->with('success', __('Department successfully updated.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function destroy(
    Request $r,
    Department $department
  ): RedirectResponse {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($r, 'delete department', self::REDIRECT_INDEX)) return $c;
    if ($department[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId())
      return redirect()->back()->with('error', __('Permission denied.'));
    try {
      $department->delete();
      return redirect()->route(ViewsConstants::DPT . '.index')
        ->with('success', __('Department successfully deleted.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function show(): RedirectResponse
  {
    return redirect()->route(ViewsConstants::DPT . '.index');
  }

  private static function v(Request $r, array $rules): ?RedirectResponse
  {
    $v = Validator::make($r->all(), $rules);
    return $v->fails()
      ? redirect()->back()->with('error', $v->getMessageBag()->first())
      : null;
  }

  private static function branches(int $creator): array
  {
    try {
      return Branch::where(DatabaseConstants::TABLE_CREATOR, $creator)
        ->pluck(CompaniesConstants::COL_BRC_NM, 'id')
        ->all();
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . $e->getMessage());
      return [];
    }
  }
}
