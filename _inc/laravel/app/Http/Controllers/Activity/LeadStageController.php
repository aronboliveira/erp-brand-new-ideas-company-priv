<?php

namespace App\Http\Controllers;

use App\Config\Constants\{MiddlewaresConstants, PermissionsConstants};
use App\Models\{LeadStage, Pipeline};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class LeadStageController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
  }

  public function index(Request $req): RedirectResponse|JsonResponse|View
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = 'lead_stages.index';
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['owner_id' => $req->user()?->ownerId()]);
      if ($r = self::_deny($req, PermissionsConstants::MNG_LD_ST)) return $r;
      try {
        $ownerId = $req->user()->ownerId();
        $fetchStart = microtime(true);
        $leadStages = LeadStage::with('pipeline')->where('lead_stages.created_by', $ownerId)->whereHas('pipeline', fn($q) => $q->where('created_by', $ownerId))->orderBy('pipeline_id')->orderBy('order')->get();
        $this->logExecutionTime($fetchStart, $action, 'fetchLeadStages');
        $groupStart = microtime(true);
        $pipelines = $leadStages->groupBy('pipeline_id')->map(fn($c) => ['name' => $c->first()->pipeline->name, 'leadStages' => $c])->all();
        $this->logExecutionTime($groupStart, $action, 'groupLeadStages');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] complete", ['lead_stage_count' => $leadStages->count(), 'pipeline_count' => count($pipelines)]);
        return view($viewPath, compact('pipelines'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'owner_id' => $req->user()?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $req): RedirectResponse|JsonResponse|View
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = 'lead_stages.create';
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['owner_id' => $req->user()?->ownerId()]);
      if ($r = self::_deny($req, 'create lead stage')) return $r;
      try {
        $ownerId = $req->user()->ownerId();
        $fetchStart = microtime(true);
        $pipelines = Pipeline::where('created_by', $ownerId)->pluck('name', 'id');
        $this->logExecutionTime($fetchStart, $action, 'fetchPipelines');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] complete", ['pipeline_count' => $pipelines->count()]);
        return view($viewPath, compact('pipelines'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'owner_id' => $req->user()?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $req): RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['owner_id' => $req->user()?->ownerId(), 'input_keys' => array_keys($req->all())]);
      if ($r = self::_deny($req, 'create lead stage')) return $r;
      if ($v = self::_validate($req->all(), ['name' => 'required|max:20', 'pipeline_id' => 'required'])) return $v;
      try {
        $createStart = microtime(true);
        LeadStage::create(['name' => $req->name, 'pipeline_id' => $req->pipeline_id, 'created_by' => $req->user()->ownerId()]);
        $this->logExecutionTime($createStart, $action, 'createLeadStage');
        Log::info("[{$class}::{$action}] success", ['name' => $req->name, 'pipeline_id' => $req->pipeline_id]);
        return redirect()->route('lead_stages.index')->with('success', 'Lead Stage successfully created!');
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'owner_id' => $req->user()?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $req, LeadStage $leadStage): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $leadStage, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['lead_stage_id' => $leadStage->getKey()]);
      try {
        $redirStart = microtime(true);
        $resp = redirect()->route('lead_stages.index');
        $this->logExecutionTime($redirStart, $action, 'redirectToIndex');
        Log::info("[{$class}::{$action}] complete");
        return $resp;
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['lead_stage_id' => $leadStage->getKey(), 'route' => Route::getCurrentRoute()?->getName(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_stage_id' => $leadStage->getKey()]);
  }

  public function edit(Request $req, LeadStage $leadStage): RedirectResponse|JsonResponse|View
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = 'lead_stages.edit';
    return $this->measureProfile($action, function () use ($req, $leadStage, $action, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", ['lead_stage_id' => $leadStage->getKey(), 'owner_id' => $req->user()?->ownerId()]);
      if ($r = self::_deny($req, 'edit lead stage')) return $r;
      if ($leadStage->created_by !== $req->user()->ownerId()) return defaultPermissionDenial($req, new AuthorizationException('lead owner'), $class . '::' . $action);
      try {
        $ownerId = $req->user()->ownerId();
        $fetchStart = microtime(true);
        $pipelines = Pipeline::where('created_by', $ownerId)->pluck('name', 'id');
        $this->logExecutionTime($fetchStart, $action, 'fetchPipelines');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        Log::info("[{$class}::{$action}] complete", ['pipeline_count' => $pipelines->count()]);
        return view($viewPath, compact('leadStage', 'pipelines'));
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'lead_stage_id' => $leadStage->getKey(), 'owner_id' => $req->user()?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_stage_id' => $leadStage->getKey()]);
  }

  public function update(Request $req, LeadStage $leadStage): RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $leadStage, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['lead_stage_id' => $leadStage->getKey(), 'owner_id' => $req->user()?->ownerId(), 'input_keys' => array_keys($req->all())]);
      if ($r = self::_deny($req, 'edit lead stage')) return $r;
      if ($leadStage->created_by !== $req->user()->ownerId()) return defaultPermissionDenial($req, new AuthorizationException('lead owner'), $class . '::' . $action);
      if ($v = self::_validate($req->all(), ['name' => 'required|max:20', 'pipeline_id' => 'required'])) return $v;
      try {
        $updateStart = microtime(true);
        $leadStage->update(['name' => $req->name, 'pipeline_id' => $req->pipeline_id]);
        $this->logExecutionTime($updateStart, $action, 'updateLeadStage');
        Log::info("[{$class}::{$action}] success", ['lead_stage_id' => $leadStage->getKey(), 'name' => $req->name, 'pipeline_id' => $req->pipeline_id]);
        return redirect()->route('lead_stages.index')->with('success', 'Lead Stage successfully updated!');
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'lead_stage_id' => $leadStage->getKey(), 'owner_id' => $req->user()?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_stage_id' => $leadStage->getKey()]);
  }

  public function destroy(Request $req, LeadStage $leadStage): RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $leadStage, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['lead_stage_id' => $leadStage->getKey(), 'owner_id' => $req->user()?->ownerId()]);
      if ($r = self::_deny($req, 'delete lead stage')) return $r;
      try {
        $deleteStart = microtime(true);
        $leadStage->delete();
        $this->logExecutionTime($deleteStart, $action, 'deleteLeadStage');
        Log::info("[{$class}::{$action}] success", ['lead_stage_id' => $leadStage->getKey()]);
        return redirect()->route('lead_stages.index')->with('success', 'Lead Stage successfully deleted!');
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'lead_stage_id' => $leadStage->getKey(), 'owner_id' => $req->user()?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_stage_id' => $leadStage->getKey()]);
  }

  public function order(Request $req): RedirectResponse|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      Log::info("[{$class}::{$action}] start", ['owner_id' => $req->user()?->ownerId(), 'payload_count' => is_array($req->input('order')) ? count($req->input('order')) : 0]);
      if ($r = self::_deny($req, 'edit lead stage')) return $r;
      $payload = $req->input('order', []);
      try {
        $updStart = microtime(true);
        foreach ($payload as $idx => $id) LeadStage::where('id', $id)->update(['order' => $idx]);
        $this->logExecutionTime($updStart, $action, 'bulkUpdateOrder');
        Log::info("[{$class}::{$action}] success", ['updated' => count($payload)]);
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
      } catch (\Throwable $e) {
        Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'owner_id' => $req->user()?->ownerId(), 'payload_count' => is_array($payload) ? count($payload) : 0, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'payload_count' => is_array($req->input('order')) ? count($req->input('order')) : 0]);
  }

  private static function _deny(
    Request $req,
    string  $perm
  ): RedirectResponse|JsonResponse|null {
    return $req->user()->can($perm)
      ? null
      : defaultPermissionDenial(
        $req,
        new \Illuminate\Auth\Access\AuthorizationException($perm),
        __CLASS__ . '::' . debug_backtrace(
          DEBUG_BACKTRACE_IGNORE_ARGS,
          2
        )[1]['function']
      );
  }

  private static function _validate(
    array $data,
    array $rules
  ): ?RedirectResponse {
    $v = Validator::make($data, $rules);
    if ($v->fails()) {
      return redirect()
        ->back()
        ->with('error', $v->getMessageBag()->first());
    }
    return null;
  }
}

// ! ALERT order() trusts client‑supplied indexes; consider validating that each ID belongs to the authenticated owner and wrapping updates in a DB transaction with row‑level locking to prevent race conditions.