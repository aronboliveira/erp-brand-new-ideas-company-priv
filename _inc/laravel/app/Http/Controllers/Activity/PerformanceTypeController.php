<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\PerformanceType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Validator;

class PerformanceTypeController extends Controller
{
  private const PERM_CREATE = PermissionsConstants::CRT_PRF_TP;
  private const PERM_DELETE = PermissionsConstants::DEL_PRF_TP;
  private const PERM_EDIT = PermissionsConstants::ED_PRF_TP;
  private const PERM_MANAGE = PermissionsConstants::MNG_PRF_TP;

  /** @return \Illuminate\View\View|RedirectResponse */
  public function index(Request $request)
  {
    if (!self::authorizeCompany($request, self::PERM_MANAGE)) return redirect()->back();
    $types = PerformanceType::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get();
    return view(ViewsConstants::PFM_TP . '.' . __FUNCTION__, compact('types'));
  }

  /** @return \Illuminate\View\View|RedirectResponse */
  public function create(Request $request)
  {
    if (!self::authorizeCompany($request, self::PERM_CREATE)) return redirect()->back();
    return view(ViewsConstants::PFM_TP . '.' . __FUNCTION__);
  }

  /** @return RedirectResponse|JsonResponse|null */
  public function store(Request $request)
  {
    if (!self::authorizeCompany($request, self::PERM_CREATE)) return redirect()->back();
    $v = Validator::make($request->all(), ['name' => 'required']);
    if ($v->fails()) return redirect()->back()->with('error', $v->getMessageBag()->first());
    try {
      $type = new PerformanceType();
      $type->name = $request->input('name');
      $type[DatabaseConstants::TABLE_CREATOR] = $request->user()->creatorId();
      $type->save();
      return redirect()->route(ViewsConstants::PFM_TP . '.index')
        ->with('success', __('Performance Type successfully created.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  /** @return RedirectResponse */
  public function show(PerformanceType $performanceType)
  {
    return redirect()->route(ViewsConstants::PFM_TP . '.index');
  }

  /** @return \Illuminate\View\View|RedirectResponse */
  public function edit(Request $request, PerformanceType $performanceType)
  {
    if (!self::authorizeCompany($request, self::PERM_EDIT)) return redirect()->back();
    if ($performanceType[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
      return defaultPermissionDenial(
        $request,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    return view(ViewsConstants::PFM_TP . '.' . __FUNCTION__, compact('performanceType'));
  }

  /** @return RedirectResponse|JsonResponse|null */
  public function update(Request $request, PerformanceType $performanceType)
  {
    if (!self::authorizeCompany($request, self::PERM_EDIT)) return redirect()->back();
    if ($performanceType[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
      return defaultPermissionDenial(
        $request,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    $v = Validator::make($request->all(), ['name' => 'required']);
    if ($v->fails()) return redirect()->back()->with('error', $v->getMessageBag()->first());
    try {
      $performanceType->name = $request->input('name');
      $performanceType->save();
      return redirect()->route(ViewsConstants::PFM_TP . '.index')
        ->with('success', __('Performance Type successfully updated.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  /** @return RedirectResponse|JsonResponse|null */
  public function destroy(Request $request, PerformanceType $performanceType)
  {
    if (!self::authorizeCompany($request, self::PERM_DELETE)) return redirect()->back();
    if ($performanceType[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
      return defaultPermissionDenial(
        $request,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    try {
      $performanceType->delete();
      return redirect()->route(ViewsConstants::PFM_TP . '.index')
        ->with('success', __('Performance Type successfully deleted.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  protected static function authorizeCompany(Request $request, string $perm): bool
  {
    if ($request->user()->type === PermissionsConstants::CPN && $request->user()->can($perm)) return true;
    defaultPermissionDenial(
      $request,
      new AuthorizationException(),
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
    );
    return false;
  }
}
