<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{TaskStage, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Route, Validator, View as ViewFacade};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class TaskStageController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::PRJ_TSK_STG . '.index';

    public function index(Request $request): View|RedirectResponse|bool|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TSK_STG . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_PRJ_TSK_STG, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['user_id' => $user?->id, 'method' => $method]);
            try {
                $buildStart = microtime(true);
                $query = TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->orderBy('order', 'asc');
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $stages = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchStages');
                Log::debug("[{$base}::{$action}] fetched", ['count' => $stages->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['stages']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('stages'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TSK_STG . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create project task stage', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['user_id' => $user?->id, 'method' => $method]);
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => []]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath);
            $this->logExecutionTime($renderStart, $action, 'renderCreate');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $request, TaskStage $taskStage): View|JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TSK_STG . '.show';
        return $this->measureProfile($action, function () use ($req, $taskStage, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_PRJ_TSK_STG, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['stage_id' => $taskStage->id, UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            $authStart = microtime(true);
            if ($taskStage[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
            $this->logExecutionTime($authStart, $action, 'authorizeOwner');
            try {
                if ($req->wantsJson()) return response()->json($taskStage);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['taskStage']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('taskStage'));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'task_stage_id' => $taskStage->id]);
    }

    /**
     * Add one stage
     */
    public const STR_V = 'storingValue';
    public function storingValue(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create project task stage', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['user_id' => $user?->id, 'input' => $req->all(), 'method' => $method]);
            $valStart = microtime(true);
            $validator = Validator::make($req->all(), ['name' => 'required|max:20', 'color' => 'required|regex:/^[0-9A-Fa-f]{6}$/']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("[{$base}::{$action}] validation failed", ['message' => $msg]);
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return redirect()->back()->with('error', $msg);
            }
            try {
                $txnStart = microtime(true);
                $stage = DB::transaction(function () use ($req, $user, $action) {
                    $orderStart = microtime(true);
                    $order = TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->ownerId())->count() + 1;
                    $this->logExecutionTime($orderStart, $action, 'computeOrder');
                    $createStart = microtime(true);
                    $result = TaskStage::create([
                        'name' => $req->name,
                        'order' => $order,
                        'color' => '#' . $req->color,
                        DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createStage');
                    return $result;
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] created", ['stageId' => $stage->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Project Task Stage Added Successfully'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    /**
     * Bulk upsert stages
     */
    public function store(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create project task stage', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['user_id' => $user?->id, 'method' => $method, 'input' => $req->all()]);
            $rulesStart = microtime(true);
            $rules = ['stages' => 'required|array'];
            $attr = [];
            foreach ($req->input('stages', []) as $i => $st) {
                $rules["stages.$i.name"] = 'required|max:255';
                $attr["stages.$i.name"] = __('Stage Name');
            }
            $this->logExecutionTime($rulesStart, $action, 'buildRules');
            $valStart = microtime(true);
            $validator = Validator::make($req->all(), $rules, [], $attr);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($validator->fails()) {
                Log::warning("[{$base}::{$action}] validation failed");
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'errors' => Utility::errorFormat($validator->getMessageBag()), 'input_keys' => array_keys($req->all())]);
                return redirect()->back()->with('errors', Utility::errorFormat($validator->getMessageBag()));
            }
            $existStart = microtime(true);
            $existing = TaskStage::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('id')->all();
            $this->logExecutionTime($existStart, $action, 'fetchExisting');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $user, $existing, $action, $base) {
                    $order = 0;
                    $loopStart = microtime(true);
                    foreach ($req->input('stages') as $st) {
                        $obj = isset($st['id']) && in_array($st['id'], $existing) ? TaskStage::find($st['id']) : new TaskStage();
                        $obj->fill([
                            'name' => $st['name'],
                            'order' => $order++,
                            'color' => '#' . $req->color,
                            DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()
                        ]);
                        $obj->save();
                        $existing = array_diff($existing, [$obj->id]);
                    }
                    $this->logExecutionTime($loopStart, $action, 'upsertStages');
                    if ($existing) {
                        $delStart = microtime(true);
                        TaskStage::destroy($existing);
                        $this->logExecutionTime($delStart, $action, 'destroyRemaining');
                    }
                    Log::info("[{$base}::{$action}] upserted stages");
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Task Stage Add Successfully'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $request, int|string $id): View|JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TSK_STG . '.edit';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_PRJ_TSK_STG, self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                $fetchStart = microtime(true);
                $stage = TaskStage::findOrFail($id);
                $this->logExecutionTime($fetchStart, $action, 'fetchStage');
                if ($stage[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
                Log::info("[{$base}::{$action}] fetching", ['stageId' => $id, 'method' => $method]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['stage']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('stage'));
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'stage_id' => $id]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'stage_id' => $id]);
    }

    public function update(Request $request, int|string $id): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_PRJ_TSK_STG, self::REDIRECT_INDEX)) !== true) return $redirect;
            $fetchStart = microtime(true);
            $stage = TaskStage::findOrFail($id);
            $this->logExecutionTime($fetchStart, $action, 'fetchStage');
            if ($stage[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
            $valStart = microtime(true);
            $validator = Validator::make($req->all(), ['name' => 'required|max:20', 'color' => 'required|regex:/^[0-9A-Fa-f]{6}$/']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("[{$base}::{$action}] validation failed", ['message' => $msg]);
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all()), 'stage_id' => $id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('error', $msg);
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $stage, $action) {
                    $updStart = microtime(true);
                    $stage->update(['name' => $req->name, 'color' => '#' . $req->color]);
                    $this->logExecutionTime($updStart, $action, 'updateStage');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] updated", ['stageId' => $id, 'method' => $method]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Task Stage successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'stage_id' => $id]);
    }

    public function destroy(Request $request, int|string $id): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'delete project task stage', self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                $fetchStart = microtime(true);
                $stage = TaskStage::findOrFail($id);
                $this->logExecutionTime($fetchStart, $action, 'fetchStage');
                if ($stage[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
                $txnStart = microtime(true);
                DB::transaction(function () use ($stage, $action) {
                    $delStart = microtime(true);
                    $stage->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteStage');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] deleted", ['stageId' => $id, 'method' => $method]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Task Stage Successfully Deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'stage_id' => $id]);
    }

    public function order(Request $request): JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_PRJ_TSK_STG, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['user_id' => $user?->id, 'order' => $req->order, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action) {
                    $loopStart = microtime(true);
                    foreach ($req->input('order', []) as $pos => $id) TaskStage::where('id', $id)->update(['order' => $pos]);
                    $this->logExecutionTime($loopStart, $action, 'reorderStages');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] completed", ['count' => is_array($req->input('order', [])) ? count($req->input('order', [])) : null]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'order' => $req->input('order', [])]);
                return response()->json(['error' => __('Server error')], 500);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }
}
