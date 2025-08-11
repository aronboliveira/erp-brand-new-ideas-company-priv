<?php

namespace App\Http\Controllers;

use App\Config\Constants\{MiddlewaresConstants, PermissionsConstants};
use App\Models\{LeadStage, Pipeline};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log, Validator};
use Symfony\Component\HttpFoundation\Response;

class LeadStageController extends Controller
{
  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
  }

  public function index(Request $req): RedirectResponse|JsonResponse|\Illuminate\View\View
  {
    if ($r = self::_deny($req, PermissionsConstants::MNG_LD_ST)) return $r;
    try {
      $leadStages = LeadStage::with('pipeline')
        ->where('lead_stages.created_by', $req->user()->ownerId())
        ->whereHas(
          'pipeline',
          fn ($q) => $q->where(
            'created_by',
            $req->user()->ownerId()
          )
        )
        ->orderBy('pipeline_id')
        ->orderBy('order')
        ->get();
      $pipelines = $leadStages
        ->groupBy('pipeline_id')
        ->map(fn ($c) => [
          'name' => $c->first()->pipeline->name,
          'leadStages' => $c,
        ])
        ->all();
      return view('leadStages.index', compact('pipelines'));
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function create(Request $req): RedirectResponse|JsonResponse|\Illuminate\View\View
  {
    if ($r = self::_deny($req, 'create lead stage')) return $r;
    try {
      $pipelines = Pipeline::where(
        'created_by',
        $req->user()->ownerId()
      )->pluck('name', 'id');
      return view('leadStages.create', compact('pipelines'));
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function store(Request $req): RedirectResponse|JsonResponse
  {
    if ($r = self::_deny($req, 'create lead stage')) return $r;
    if ($v = self::_validate(
      $req->all(),
      ['name' => 'required|max:20', 'pipeline_id' => 'required']
    )) return $v;
    try {
      LeadStage::create([
        'name' => $req->name,
        'pipeline_id' => $req->pipeline_id,
        'created_by' => $req->user()->ownerId(),
      ]);
      return redirect()
        ->route('leadStages.index')
        ->with('success', 'Lead Stage successfully created!');
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function show(Request $req, LeadStage $leadStage): RedirectResponse
  {
    return redirect()->route('leadStages.index');
  }

  public function edit(Request $req, LeadStage $leadStage): RedirectResponse|JsonResponse|\Illuminate\View\View
  {
    if ($r = self::_deny($req, 'edit lead stage')) return $r;
    if ($leadStage->created_by !== $req->user()->ownerId()) {
      return defaultPermissionDenial(
        $req,
        new \Illuminate\Auth\Access\AuthorizationException('lead owner'),
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    try {
      $pipelines = Pipeline::where(
        'created_by',
        $req->user()->ownerId()
      )->pluck('name', 'id');
      return view('leadStages.edit', compact('leadStage', 'pipelines'));
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function update(
    Request    $req,
    LeadStage $leadStage
  ): RedirectResponse|JsonResponse {
    if ($r = self::_deny($req, 'edit lead stage')) return $r;
    if ($leadStage->created_by !== $req->user()->ownerId()) {
      return defaultPermissionDenial(
        $req,
        new \Illuminate\Auth\Access\AuthorizationException('lead owner'),
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    if ($v = self::_validate(
      $req->all(),
      ['name' => 'required|max:20', 'pipeline_id' => 'required']
    )) return $v;
    try {
      $leadStage->update([
        'name' => $req->name,
        'pipeline_id' => $req->pipeline_id,
      ]);
      return redirect()
        ->route('leadStages.index')
        ->with('success', 'Lead Stage successfully updated!');
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function destroy(Request $req, LeadStage $leadStage): RedirectResponse|JsonResponse
  {
    if ($r = self::_deny($req, 'delete lead stage')) return $r;
    try {
      $leadStage->delete();
      return redirect()
        ->route('leadStages.index')
        ->with('success', 'Lead Stage successfully deleted!');
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function order(Request $req): RedirectResponse|JsonResponse
  {
    if ($r = self::_deny($req, 'edit lead stage')) return $r;
    $payload = $req->input('order', []);
    try {
      foreach ($payload as $idx => $id) {
        LeadStage::where('id', $id)->update(['order' => $idx]);
      }
      return response()
        ->json(['status' => 'ok'], Response::HTTP_OK);
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
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