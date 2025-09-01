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
use Illuminate\Support\Facades\{DB, Log, Route, Validator};

class TaskStageController extends Controller
{
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
                $query = TaskStage::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->orderBy('order', 'asc');
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $stages = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchStages');
                Log::debug("[{$base}::{$action}] fetched", ['count' => $stages->count()]);
                if (!\Illuminate\Support\Facades\View::exists($viewPath)) {
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
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create project task stage', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['user_id' => $user?->id, 'method' => $method]);
            if (!\Illuminate\Support\Facades\View::exists($viewPath)) {
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
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $redirect = self::guard(
                $request,
                PermissionsConstants::MNG_PRJ_TSK_STG,
                self::REDIRECT_INDEX
            )
        ) return $redirect;

        if ($taskStage[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_INDEX)
            );

        Log::info("$action called", [
            'stageId' => $taskStage->id,
            UsersConstants::COL_USER_ID  => $user?->id
        ]);

        try {
            return $request->wantsJson()
                ? response()->json($taskStage)
                : view(ViewsConstants::TSK_STG . '.show', compact('taskStage'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    /**
     * Add one stage
     */
    public function storingValue(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create project task stage',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        Log::info("$action called", [
            'user_id' => $user?->id,
            'input'  => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:20',
            'color' => 'required|regex:/^[0-9A-Fa-f]{6}$/'
        ]);
        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->first();
            Log::warning("$action validation failed", ['message' => $msg]);
            return redirect()
                ->back()
                ->with('error', $msg);
        }

        try {
            $stage = DB::transaction(function () use ($request, $user) {
                $order = TaskStage::where(
                    DatabaseConstants::TABLE_CREATOR,
                    $user?->ownerId()
                )->count() + 1;
                return TaskStage::create([
                    'name'       => $request->name,
                    'order'      => $order,
                    'color'      => '#' . $request->color,
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
                ]);
            });
            Log::info("$action created", ['stageId' => $stage->id]);

            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Project Task Stage Added Successfully'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    /**
     * Bulk upsert stages
     */
    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create project task stage',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", [
            'user_id' => $user?->id,
            'input'  => $request->all()
        ]);
        $rules = ['stages' => 'required|array'];
        $attr = [];
        foreach ($request->input('stages', []) as $i => $st) {
            $rules["stages.$i.name"] = 'required|max:255';
            $attr["stages.$i.name"] = __('Stage Name');
        }
        $validator = Validator::make(
            $request->all(),
            $rules,
            [],
            $attr
        );
        if ($validator->fails()) {
            Log::warning("$action validation failed");
            return redirect()
                ->back()
                ->with('errors', Utility::errorFormat(
                    $validator->getMessageBag()
                ));
        }

        $existing = TaskStage::where(
            DatabaseConstants::TABLE_CREATOR,
            $user?->creatorId()
        )->pluck('id')->all();

        try {
            DB::transaction(function () use ($request, $user, $existing) {
                $order = 0;
                foreach ($request->input('stages') as $st) {
                    $obj = isset($st['id']) && in_array($st['id'], $existing)
                        ? TaskStage::find($st['id'])
                        : new TaskStage();
                    $obj->fill([
                        'name'       => $st['name'],
                        'order'      => $order++,
                        'color'      => '#' . $request->color,
                        DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
                    ]);
                    $obj->save();
                    $existing = array_diff($existing, [$obj->id]);
                }
                if ($existing) {
                    TaskStage::destroy($existing);
                }
            });
            Log::info("$action upserted stages");
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Task Stage Add Successfully'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function edit(Request $request, int|string $id): View|JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::MNG_PRJ_TSK_STG,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        $stage = TaskStage::findOrFail($id);
        if ($stage[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
        Log::info("$action fetching", ['stageId' => $id]);
        return view(ViewsConstants::TSK_STG . '.edit', compact('stage'));
    }

    public function update(Request $request, int|string $id): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::MNG_PRJ_TSK_STG,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        $stage = TaskStage::findOrFail($id);
        if ($stage[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:20',
            'color' => 'required|regex:/^[0-9A-Fa-f]{6}$/'
        ]);
        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->first();
            Log::warning("$action validation failed", ['message' => $msg]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('error', $msg);
        }

        try {
            DB::transaction(fn() => $stage->update([
                'name'  => $request->name,
                'color' => '#' . $request->color
            ]));
            Log::info("$action updated", ['stageId' => $id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Task Stage successfully updated.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, int|string $id): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'delete project task stage',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;

        $stage = TaskStage::findOrFail($id);
        if ($stage[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
        try {
            DB::transaction(fn() => $stage->delete());
            Log::info("$action deleted", ['stageId' => $id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Task Stage Successfully Deleted.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function order(Request $request): JsonResponse|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::MNG_PRJ_TSK_STG,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", ['order' => $request->order]);
        try {
            DB::transaction(function () use ($request) {
                foreach ($request->input('order', []) as $pos => $id) {
                    TaskStage::where('id', $id)->update(['order' => $pos]);
                }
            });
            Log::info("$action completed");
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return response()->json(
                ['error' => __('Server error')],
                500
            );
        }
    }
}
