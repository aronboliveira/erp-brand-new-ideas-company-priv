<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, UsersConstants, ViewsConstants};
use App\Models\{ProjectStages, Task};
use App\Traits\ChecksPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{DB, Log, Validator, View as ViewFacade};
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class ProjectStagesController extends Controller
{
    use ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::PRJ_STG . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($r = self::guard($request, 'manage project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

            try {
                $user = $request->user();
                Log::info($method . ' start', [UsersConstants::COL_USER_ID => $user?->id]);

                $buildStart = microtime(true);
                $query = ProjectStages::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->orderBy('order');
                $this->logExecutionTime($buildStart, $action, 'buildQuery');

                $fetchStart = microtime(true);
                $stages = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchStages');

                $viewPath = ViewsConstants::PRJ_STG . '.' . $action;
                return $this->renderViewChecked($viewPath, ['projectStages' => $stages], $action);
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($r = self::guard($request, 'create project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
            $viewPath = ViewsConstants::PRJ_STG . '.' . $action;
            return $this->renderViewChecked($viewPath, [], $action);
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($r = self::guard($request, 'create project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

            Log::info($method . ' input', ['input' => $request->all()]);

            $valStart = microtime(true);
            $v = Validator::make($request->all(), ['name' => 'required|string|max:20']);
            $this->logExecutionTime($valStart, $action, 'validate');
            if ($v->fails()) {
                Log::warning($method . ' validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->route(self::ROUTE_INDEX)->with('error', $v->errors()->first());
            }

            DB::beginTransaction();
            try {
                $user = $request->user();

                $lastStart = microtime(true);
                $last = ProjectStages::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->orderByDesc('order')
                    ->first();
                $this->logExecutionTime($lastStart, $action, 'fetchLastStage');

                $createStart = microtime(true);
                $stage = ProjectStages::create([
                    'name'       => $request->name,
                    'color'      => '#' . $request->input('color', '000000'),
                    DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                    'order'      => $last ? $last->order + 1 : 0,
                ]);
                $this->logExecutionTime($createStart, $action, 'createStage');

                DB::commit();
                Log::info($method . ' created', ['stage_id' => $stage->id]);

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Project stage successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function edit(Request $request, int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            if (($r = self::guard($request, 'edit project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

            try {
                $findStart = microtime(true);
                $stage = ProjectStages::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findStage');

                if ($stage[DatabaseConstants::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                    return defaultPermissionDenial(
                        $request,
                        new AuthorizationException('edit project stage'),
                        $method,
                        route(self::ROUTE_INDEX)
                    );
                }

                $viewPath = ViewsConstants::PRJ_STG . '.' . $action;
                return $this->renderViewChecked($viewPath, ['projectStage' => $stage], $action);
            } catch (\Throwable $e) {
                return $e instanceof AuthorizationException
                    ? defaultPermissionDenial($request, $e, $method)
                    : defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            if (($r = self::guard($request, 'edit project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

            Log::info($method . ' input', ['id' => $id, 'input' => $request->all()]);

            $valStart = microtime(true);
            $v = Validator::make($request->all(), ['name' => 'required|string|max:20']);
            $this->logExecutionTime($valStart, $action, 'validate');
            if ($v->fails()) {
                Log::warning($method . ' validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->route(self::ROUTE_INDEX)->with('error', $v->errors()->first());
            }

            DB::beginTransaction();
            try {
                $user = $request->user();

                $findStart = microtime(true);
                $stage = ProjectStages::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findStage');

                if ($stage[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                    throw new AuthorizationException('edit project stage');

                $updStart = microtime(true);
                $stage->update([
                    'name'  => $request->name,
                    'color' => '#' . $request->input('color', '000000'),
                ]);
                $this->logExecutionTime($updStart, $action, 'updateStage');

                DB::commit();
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Project stage successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                return $e instanceof AuthorizationException
                    ? defaultPermissionDenial($request, $e, $method, route(self::ROUTE_INDEX))
                    : defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            if (($r = self::guard($request, 'delete project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

            try {
                $user = $request->user();

                $findStart = microtime(true);
                $stage = ProjectStages::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findStage');

                if ($stage[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                    throw new AuthorizationException('delete project stage');

                $usedCheckStart = microtime(true);
                $used = Task::where('stage', $stage->id)->exists();
                $this->logExecutionTime($usedCheckStart, $action, 'checkTasksUsingStage');

                if ($used) {
                    return redirect()->route(self::ROUTE_INDEX)
                        ->with('error', __('Project task already assigned to this stage; please move them first.'));
                }

                $delStart = microtime(true);
                $stage->delete();
                $this->logExecutionTime($delStart, $action, 'deleteStage');

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Project stage successfully deleted.'));
            } catch (\Throwable $e) {
                return $e instanceof AuthorizationException
                    ? defaultPermissionDenial($request, $e, $method, route(self::ROUTE_INDEX))
                    : defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function order(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($r = self::guard($request, 'move project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) {
                return response()->json(['error' => __('Permission denied.')], 401);
            }

            Log::info($method, ['order' => $request->input('order')]);

            DB::beginTransaction();
            try {
                $loopStart = microtime(true);
                foreach ($request->input('order', []) as $idx => $id) {
                    ProjectStages::where('id', $id)->update(['order' => $idx]);
                }
                $this->logExecutionTime($loopStart, $action, 'applyOrder');

                DB::commit();
                return response()->json(['success' => true], 200);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($method . ' failed', ['err' => $e->getMessage()]);
                return response()->json(['error' => __('An unexpected error occurred.')], 500);
            }
        });
    }

    /**
     * Timed view existence check + render.
     */
    private function renderViewChecked(string $viewPath, array $data, string $action): View|RedirectResponse
    {
        $existsStart = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($existsStart, $action, 'viewExistsCheck');

        if (!$exists) {
            Log::warning('[renderViewChecked] view missing', ['view' => $viewPath]);
            return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }

        $renderStart = microtime(true);
        $response = view($viewPath, $data);
        $this->logExecutionTime($renderStart, $action, 'viewRender');

        return $response;
    }
}
