<?php

namespace App\Http\Controllers\Configs;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{
  ActivitiesConstants as AC,
  DatabaseConstants as DC,
  PermissionsConstants as PMC,
  ProjectsConstants as PJC,
  MiddlewaresConstants as MWC,
  UsersConstants as UC,
  ViewsConstants as VW
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
  Validator,
  View as ViewFacade,
};
use Illuminate\Contracts\Support\Renderable;

use function App\Http\Controllers\Helpers\defaultPermissionDenial;
use App\Traits\DefinesResourceActions;
final class PipelineController extends Controller
{
	use DefinesResourceActions;

  use ChecksLogin, ChecksPermissions;

  private const REDIRECT_INDEX = '/';
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

  public function index(Request $req): Renderable|RedirectResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    $function = __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $action, $function) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($c = self::guard($req, PMC::MNG_PPL, self::REDIRECT_INDEX)) !== true) return $c;
      $pipelines = Pipeline::where(DC::COL_TABLE_CREATOR, $u->creatorId())->get();
      Log::info($action . ' fetched', ['count' => $pipelines->count(), UC::COL_USER_ID => $u->id]);
      return ViewFacade::make(VW::PPL . '.' . $function, compact('pipelines'));
    }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
  }

  public function create(Request $req): Renderable|RedirectResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    $function = __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $function) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($c = self::guard($req, 'create pipeline', self::REDIRECT_INDEX)) !== true) return $c;
      return ViewFacade::make(VW::PPL . '.' . $function);
    }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
  }

  public function show(Request $request, Pipeline $pipeline): RedirectResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($request, $pipeline, $action) {
      Log::info("$action start", [PJC::COL_PPL_ID => $pipeline->id, UC::COL_USER_ID => Auth::id()]);
      if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
      if (($c = self::guard($request, PMC::MNG_PPL, self::REDIRECT_INDEX)) !== true) {
        Log::warning("$action permission denied", [UC::COL_USER_ID => $user?->id]);
        return $c;
      }
      if ($pipeline[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) {
        Log::warning("$action ownership denied", [UC::COL_USER_ID => $user?->id, PJC::COL_PPL_ID => $pipeline->id]);
        return defaultPermissionDenial($request, null, $action);
      }
      Log::info("$action redirecting", [PJC::COL_PPL_ID => $pipeline->id]);
      return redirect()->route(VW::PPL . '.index');
    }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
  }

  public function store(Request $req): RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($c = self::guard($req, 'create pipeline', self::REDIRECT_INDEX)) !== true) return $c;
      if ($c = self::v($req, [PJC::COL_PPL_NM => 'required|max:20'])) return $c;
      Pipeline::create([
        PJC::COL_PPL_NM => $req->name,
        DC::COL_TABLE_CREATOR => $u->creatorId()
      ]);
      return redirect()->route(VW::PPL . '.index')->with('success', __('Pipeline successfully created!'));
    }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
  }

  public function edit(Request $req, Pipeline $pipeline): Renderable|RedirectResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    $function = __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $pipeline, $function) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($c = self::guard($req, 'edit pipeline', self::REDIRECT_INDEX)) !== true) return $c;
      if ($pipeline[DC::COL_TABLE_CREATOR] !== $u->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'));
      return ViewFacade::make(VW::PPL . '.' . $function, compact('pipeline'));
    }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
  }

  public function update(Request $req, Pipeline $pipeline): RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $pipeline) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($c = self::guard($req, 'edit pipeline', self::REDIRECT_INDEX)) !== true) return $c;
      if ($pipeline[DC::COL_TABLE_CREATOR] !== $u->creatorId()) return redirect()->back()->with('error', __('Permission Denied.'));
      if ($c = self::v($req, [PJC::COL_PPL_NM => 'required|max:20'])) return $c;
      $pipeline->update([PJC::COL_PPL_NM => $req->name]);
      return redirect()->route(VW::PPL . '.index')->with('success', __('Pipeline successfully updated!'));
    }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
  }

  public function destroy(Request $req, Pipeline $pipeline): RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $pipeline) {
      if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
      if (($c = self::guard($req, 'delete pipeline', self::REDIRECT_INDEX)) !== true) return $c;
      if ($pipeline[DC::COL_TABLE_CREATOR] !== $u->creatorId()) return redirect()->back()->with('error', __('Permission Denied.'));
      if ($pipeline->stages->isNotEmpty()) return redirect()->route(VW::PPL . '.index')->with('error', __('There are stages or deals in this pipeline, remove them first!'));
      foreach ($pipeline->stages as $stage)
        foreach (Deal::where([[PJC::COL_PPL_ID, $pipeline->id], ['stage_id', $stage->id]])->get() as $deal) {
          DealDiscussion::where(AC::COL_DL, $deal->id)->delete();
          DealFile::where(AC::COL_DL, $deal->id)->delete();
          ClientDeal::where(AC::COL_DL, $deal->id)->delete();
          UserDeal::where(AC::COL_DL, $deal->id)->delete();
          DealTask::where(AC::COL_DL, $deal->id)->delete();
          ActivityLog::where(AC::COL_DL, $deal->id)->delete();
          $deal->delete();
        }
      $pipeline->delete();
      return redirect()->route(VW::PPL . '.index')->with('success', __('Pipeline successfully deleted!'));
    }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
  }

  private static function v(Request $r, array $rules): ?RedirectResponse
  {
    $v = Validator::make($r->all(), $rules);
    return $v->fails() ? redirect()->back()->with('error', $v->getMessageBag()->first()) : null;
  }
}
