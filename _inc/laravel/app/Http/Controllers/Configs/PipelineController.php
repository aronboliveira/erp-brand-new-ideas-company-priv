<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  ActivitiesConstants,
  DatabaseConstants,
  PermissionsConstants,
  ProjectsConstants,
  MiddlewaresConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{
  ActivityLog,
  ClientDeal,
  Deal,
  DealDiscussion,
  DealFile,
  DealTask,
  Pipeline,
  UserDeal
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\{
  Auth,
  Log,
  Validator
};

final class PipelineController extends Controller
{
  use ChecksLogin, ChecksPermissions;
  private const REDIRECT_INDEX = '/';

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
  }

  public function index(Request $req)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($req, PermissionsConstants::MNG_PPL, self::REDIRECT_INDEX)) return $c;
    $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
    return view(ViewsConstants::PPL . '.' . __FUNCTION__, compact('pipelines'));
  }

  public function create(Request $req)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($req, 'create pipeline', self::REDIRECT_INDEX)) return $c;
    return view(ViewsConstants::PPL . '.' . __FUNCTION__);
  }

  public function show(Request $request, Pipeline $pipeline): RedirectResponse
  {
    $action = __METHOD__;
    Log::info("$action start", [ProjectsConstants::COL_PPL_ID => $pipeline->id, UsersConstants::COL_USER_ID => Auth::id()]);
    if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
    if ($c = self::guard($request, PermissionsConstants::MNG_PPL, self::REDIRECT_INDEX)) {
      Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
      return $c;
    }
    if ($pipeline[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
      Log::warning("$action ownership denied", [UsersConstants::COL_USER_ID => $user?->id, ProjectsConstants::COL_PPL_ID => $pipeline->id]);
      return defaultPermissionDenial($request, null, $action);
    }
    Log::info("$action redirecting to index", [ProjectsConstants::COL_PPL_ID => $pipeline->id]);
    return redirect()->route(ViewsConstants::PPL . '.index');
  }

  public function store(Request $req)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($req, 'create pipeline', self::REDIRECT_INDEX)) return $c;
    if ($c = self::v($req, [ProjectsConstants::COL_PPL_NM => 'required|max:20'])) return $c;
    Pipeline::create([
      ProjectsConstants::COL_PPL_NM => $req->name,
      DatabaseConstants::TABLE_CREATOR => $u->creatorId()
    ]);
    return redirect()->route(ViewsConstants::PPL . '.index')
      ->with('success', __('Pipeline successfully created!'));
  }

  public function edit(Request $req, Pipeline $pipeline)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($req, 'edit pipeline', self::REDIRECT_INDEX)) return $c;
    if ($pipeline[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId())
      return defaultPermissionDenial($req, new \Exception('owner'));
    return view(ViewsConstants::PPL . '.' . __FUNCTION__, compact('pipeline'));
  }

  public function update(Request $req, Pipeline $pipeline)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($req, 'edit pipeline', self::REDIRECT_INDEX)) return $c;
    if ($pipeline[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId())
      return redirect()->back()->with('error', __('Permission Denied.'));
    if ($c = self::v($req, [ProjectsConstants::COL_PPL_NM => 'required|max:20'])) return $c;
    $pipeline->update([ProjectsConstants::COL_PPL_NM => $req->name]);
    return redirect()->route(ViewsConstants::PPL . '.index')
      ->with('success', __('Pipeline successfully updated!'));
  }

  public function destroy(Request $req, Pipeline $pipeline)
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
    if ($c = self::guard($req, 'delete pipeline', self::REDIRECT_INDEX)) return $c;
    if ($pipeline[DatabaseConstants::TABLE_CREATOR] !== $u->creatorId())
      return redirect()->back()->with('error', __('Permission Denied.'));
    if ($pipeline->stages->isNotEmpty())
      return redirect()->route(ViewsConstants::PPL . '.index')
        ->with('error', __('There are stages or deals in this pipeline, remove them first!'));
    /* cascade delete */
    foreach ($pipeline->stages as $stage)
      foreach (Deal::where([
        [ProjectsConstants::COL_PPL_ID, $pipeline->id],
        ['stage_id', $stage->id]
      ])->get() as $deal) {
        DealDiscussion::where(ActivitiesConstants::COL_DL, $deal->id)->delete();
        DealFile::where(ActivitiesConstants::COL_DL, $deal->id)->delete();
        ClientDeal::where(ActivitiesConstants::COL_DL, $deal->id)->delete();
        UserDeal::where(ActivitiesConstants::COL_DL, $deal->id)->delete();
        DealTask::where(ActivitiesConstants::COL_DL, $deal->id)->delete();
        ActivityLog::where(ActivitiesConstants::COL_DL, $deal->id)->delete();
        $deal->delete();
      }

    $pipeline->delete();
    return redirect()->route(ViewsConstants::PPL . '.index')
      ->with('success', __('Pipeline successfully deleted!'));
  }

  private static function v(Request $r, array $rules): ?RedirectResponse
  {
    $v = Validator::make($r->all(), $rules);
    return $v->fails()
      ? redirect()->back()->with('error', $v->getMessageBag()->first())
      : null;
  }
}
