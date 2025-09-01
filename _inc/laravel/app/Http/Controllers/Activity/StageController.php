<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{Deal, Pipeline, Stage};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Route, Validator};
use Illuminate\View\View;

class StageController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_ROUTE = ViewsConstants::STG . '.index';

    public function __construct()
    {
        $this->middleware(
            [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
            ]
        );
    }

    public function index(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::STG . '.' . $action;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = $this->requireLogin($req)) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info("[{$class}::{$action}] start", ['user' => $user?->id, 'method' => $method]);
            if ($denial = $this->guard($req, PermissionsConstants::MNG_ST, self::REDIRECT_ROUTE)) return $denial;
            try {
                $ownerId = $user?->ownerId();
                $fetchStart = microtime(true);
                $stages = Stage::select(DatabaseConstants::TABLE_STAGES . '.*', DatabaseConstants::TABLE_PIPELINES . '.name as pipeline')
                    ->join(DatabaseConstants::TABLE_PIPELINES, DatabaseConstants::TABLE_PIPELINES . '.id', '=', DatabaseConstants::TABLE_STAGES . '.pipeline_id')
                    ->where(DatabaseConstants::TABLE_PIPELINES . '.' . DatabaseConstants::TABLE_CREATOR, $ownerId)
                    ->where(DatabaseConstants::TABLE_STAGES . '.' . DatabaseConstants::TABLE_CREATOR, $ownerId)
                    ->orderBy(DatabaseConstants::TABLE_STAGES . '.pipeline_id')
                    ->orderBy(DatabaseConstants::TABLE_STAGES . '.order')
                    ->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchStages');
                Log::info("[{$class}::{$action}] fetched stages", ['count' => $stages->count()]);
                $groupStart = microtime(true);
                $pipelines = [];
                foreach ($stages as $stage) {
                    $pid = $stage->pipeline_id;
                    if (!isset($pipelines[$pid])) $pipelines[$pid] = ['name' => $stage->pipeline, 'stages' => []];
                    $pipelines[$pid]['stages'][] = $stage;
                }
                $this->logExecutionTime($groupStart, $action, 'groupStagesByPipeline');
                if (!View::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'pipelines_count' => count($pipelines)]);
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('pipelines'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'user_id' => $user?->id, 'owner_id' => $user?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_ROUTE));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function create(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::STG . '.create';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$class}::{$action}] start", ['user' => $user?->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'create stage', self::REDIRECT_ROUTE)) return $denial;
            try {
                $ownerId = $user?->ownerId();
                $fetchStart = microtime(true);
                $pipelines = Pipeline::where('created_by', $ownerId)->pluck('name', 'id');
                $this->logExecutionTime($fetchStart, $action, 'fetchPipelines');
                if (!View::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('pipelines'));
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                Log::info("[{$class}::{$action}] complete", ['pipelines_count' => is_countable($pipelines) ? count($pipelines) : null]);
                return $resp;
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'user' => $user?->id, 'owner_id' => $user?->ownerId(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_ROUTE));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function show(Request $request, Stage $stage): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $stage, $action, $method, $class) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$class}::{$action}] start", ['user_id' => $user?->id, 'stageId' => $stage->id]);
            if ($denial = $this->guard($req, PermissionsConstants::MNG_ST, self::REDIRECT_ROUTE)) {
                Log::warning("[{$class}::{$action}] permission denied", ['user_id' => $user?->id]);
                return $denial;
            }
            if ($stage->created_by !== $user?->ownerId()) {
                Log::warning("[{$class}::{$action}] ownership denied", ['user_id' => $user?->id, 'stageId' => $stage->id]);
                return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_ROUTE), false);
            }
            return redirect()->route(self::REDIRECT_ROUTE);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'stage_id' => $stage->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$class}::{$action}] start", ['user' => $user?->id, 'input_keys' => array_keys($req->all()), 'method' => $method]);
            if ($denial = $this->guard($req, 'create stage', self::REDIRECT_ROUTE)) return $denial;
            $valStart = microtime(true);
            $v = Validator::make($req->all(), ['name' => 'required|max:20', 'pipeline_id' => 'required|exists:pipelines,id']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($v->fails()) {
                Log::warning("[{$class}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('error', $v->errors()->first());
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $user, $action, $class) {
                    $createStart = microtime(true);
                    $stage = Stage::create(['name' => $req->name, 'pipeline_id' => $req->pipeline_id, 'created_by' => $user?->ownerId()]);
                    $this->logExecutionTime($createStart, $action, 'createStage');
                    Log::info("[{$class}::{$action}] stage created", ['stage_id' => $stage->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Deal Stage successfully created!'));
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'user_id' => $user?->id, 'input' => $req->all(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function edit(Request $request, Stage $stage)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::STG . '.edit';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $stage, $action, $method, $class, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$class}::{$action}] start", ['user' => $user?->id, 'stage' => $stage->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'edit stage', self::REDIRECT_ROUTE)) return $denial;
            if ($stage->created_by !== $user?->ownerId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_ROUTE), false);
            $fetchStart = microtime(true);
            $pipelines = Pipeline::where('created_by', $user?->ownerId())->pluck('name', 'id');
            $this->logExecutionTime($fetchStart, $action, 'fetchPipelines');
            if (!View::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('stage', 'pipelines'));
            $this->logExecutionTime($renderStart, $action, 'renderEdit');
            Log::info("[{$class}::{$action}] complete", ['stage_id' => $stage->id, 'pipelines_count' => is_countable($pipelines) ? count($pipelines) : null]);
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'stage_id' => $stage->id]);
    }

    public function update(Request $request, Stage $stage): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $stage, $action, $method, $class) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$class}::{$action}] start", ['user' => $user?->id, 'stage' => $stage->id, 'input_keys' => array_keys($req->all()), 'method' => $method]);
            if ($denial = $this->guard($req, 'edit stage', self::REDIRECT_ROUTE)) return $denial;
            if ($stage->created_by !== $user?->ownerId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_ROUTE), false);
            $valStart = microtime(true);
            $v = Validator::make($req->all(), ['name' => 'required|max:20', 'pipeline_id' => 'required|exists:pipelines,id']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($v->fails()) {
                Log::warning("[{$class}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('error', $v->errors()->first());
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $stage, $action, $class) {
                    $updStart = microtime(true);
                    $old = $stage->only('name', 'pipeline_id');
                    $stage->update($req->only('name', 'pipeline_id'));
                    $this->logExecutionTime($updStart, $action, 'updateStage');
                    Log::info("[{$class}::update] Stage updated", ['stage' => $stage->id, 'old' => $old, 'new' => $stage->only('name', 'pipeline_id')]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Deal Stage successfully updated!'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['route' => Route::getCurrentRoute()?->getName(), 'user' => $user?->id, 'stage' => $stage->id, 'input' => $req->all(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'stage_id' => $stage->id]);
    }

    public function destroy(Request $request, Stage $stage): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $stage, $action, $method, $class, $base) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'stage_id' => $stage->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'delete stage', self::REDIRECT_ROUTE)) return $denial;
            if ($stage->created_by !== $user?->ownerId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_ROUTE), false);
            $countStart = microtime(true);
            $count = Deal::where('stage_id', $stage->id)->where('created_by', $stage->created_by)->count();
            $this->logExecutionTime($countStart, $action, 'countDeals');
            if ($count > 0) {
                Log::warning("[{$base}::{$action}] cannot delete stage with deals", ['stage_id' => $stage->id, 'deals' => $count]);
                Log::debug("[{$base}::{$action}] delete blocked details", ['created_by' => $stage->created_by, 'route' => Route::getCurrentRoute()?->getName()]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('error', __('There are some deals on stage, please remove it first!'));
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($stage, $action, $base) {
                    $delStart = microtime(true);
                    $stage->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteStage');
                    Log::info("[{$base}::{$action}] stage deleted", ['stage_id' => $stage->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Deal Stage successfully deleted!'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] destroy transaction failed", ['error' => $e->getMessage(), 'stage_id' => $stage->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'stage_id' => $stage->id]);
    }

    public function order(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", ['order' => $req->order, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action) {
                    $loopStart = microtime(true);
                    foreach ($req->input('order', []) as $position => $id) Stage::where('id', $id)->update(['order' => $position]);
                    $this->logExecutionTime($loopStart, $action, 'reorderStages');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] order applied", ['count' => is_array($req->input('order', [])) ? count($req->input('order', [])) : null]);
                return response()->json(['status' => 'ok']);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] order failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'order' => $req->input('order', [])]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function json(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", ['pipeline_id' => $req->pipeline_id, 'method' => $method]);
            try {
                $buildStart = microtime(true);
                $builder = Stage::query();
                if ($pid = $req->pipeline_id) $builder->where('pipeline_id', $pid);
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $list = $builder->get()->pluck('name', 'id');
                $this->logExecutionTime($fetchStart, $action, 'fetchList');
                Log::info("[{$base}::{$action}] json ready", ['count' => $list->count()]);
                return response()->json($list);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] json failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'pipeline_id' => $req->pipeline_id]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    private function requireLogin(Request $r)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        return $u;
    }
}
