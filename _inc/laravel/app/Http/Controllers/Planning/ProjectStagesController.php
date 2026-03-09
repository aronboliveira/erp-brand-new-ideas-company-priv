<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC, ViewsConstants as VW};
use App\Models\{ProjectStage, Task};
use App\Traits\{ChecksLogin, ChecksPermissions, ConsoleOutputs};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Redirect, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

class ProjectStagesController extends Controller
{
    use ChecksLogin, ChecksPermissions, ConsoleOutputs;

    private const ROUTE_INDEX = VW::PRJ_STG . '.index';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';


    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PRJ_STG . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $projectStages ??= collect();
            try {
                if (($r = self::guard($request, 'manage project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

                $t = microtime(true);
                $query = ProjectStage::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->orderBy('order');
                $this->logExecutionTime($t, $action . '::buildQuery', 'completed');

                $t = microtime(true);
                $projectStages = $query->get() ?? $projectStages;
                $this->logExecutionTime($t, $action . '::fetchStages', 'completed');

                return $this->renderViewChecked($viewPath, ['projectStages' => $projectStages], $action, $user?->id);
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PRJ_STG . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            try {
                if (($r = self::guard($request, 'create project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
                return $this->renderViewChecked($viewPath, [], $action, $user?->id);
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $stage ??= null;
            $inTransaction ??= false;
            try {
                if (($r = self::guard($request, 'create project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

                $t = microtime(true);
                $data = Validator::make($request->all(), ['name' => 'required|string|max:20'])->validate() ?? $data;
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $t = microtime(true);
                $last = ProjectStage::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                    ->orderByDesc('order')
                    ->first();
                $this->logExecutionTime($t, $action . '::fetchLastStage', 'completed');

                $color = ltrim((string) $request->input('color', '000000'), '#');
                $color = $color !== '' ? $color : '000000';

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $stage = ProjectStage::create([
                    'name' => (string) ($data['name'] ?? ''),
                    'color' => '#' . $color,
                    DC::COL_TABLE_CREATOR => $user?->creatorId(),
                    'order' => $last ? ($last->order + 1) : 0,
                ]);
                DB::commit();
                $inTransaction = false;

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Project stage successfully created.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return redirect()->route(self::ROUTE_INDEX)->with('error', $e->validator?->errors()->first());
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function edit(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PRJ_STG . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $projectStage ??= null;
            try {
                if (($r = self::guard($request, 'edit project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

                $t = microtime(true);
                $projectStage = ProjectStage::findOrFail($id);
                $this->logExecutionTime($t, $action . '::findStage', 'completed');

                if (($projectStage[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    throw new AuthorizationException('edit project stage');

                return $this->renderViewChecked($viewPath, ['projectStage' => $projectStage], $action, $user?->id);
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'stage_id' => $id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (ModelNotFoundException $e) {
                Log::warning($method . ' stage not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'stage_id' => $id,
                ]);
                $this->consoleOutput($method . ' stage not found', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $inTransaction ??= false;
            try {
                if (($r = self::guard($request, 'edit project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

                $t = microtime(true);
                $data = Validator::make($request->all(), ['name' => 'required|string|max:20'])->validate() ?? $data;
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $t = microtime(true);
                $stage = ProjectStage::findOrFail($id);
                $this->logExecutionTime($t, $action . '::findStage', 'completed');

                if (($stage[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    throw new AuthorizationException('edit project stage');

                $color = ltrim((string) $request->input('color', '000000'), '#');
                $color = $color !== '' ? $color : '000000';

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $stage->update([
                    'name' => (string) ($data['name'] ?? ''),
                    'color' => '#' . $color,
                ]);
                DB::commit();
                $inTransaction = false;

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Project stage successfully updated.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return redirect()->route(self::ROUTE_INDEX)->with('error', $e->validator?->errors()->first());
            } catch (AuthorizationException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'stage_id' => $id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (ModelNotFoundException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' stage not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'stage_id' => $id,
                ]);
                $this->consoleOutput($method . ' stage not found', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                if (($r = self::guard($request, 'delete project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;

                $t = microtime(true);
                $stage = ProjectStage::findOrFail($id);
                $this->logExecutionTime($t, $action . '::findStage', 'completed');

                if (($stage[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    throw new AuthorizationException('delete project stage');

                $t = microtime(true);
                $used = Task::where('stage', $stage?->id)->exists();
                $this->logExecutionTime($t, $action . '::checkTasksUsingStage', 'completed');

                if ($used)
                    return redirect()->route(self::ROUTE_INDEX)
                        ->with('error', __('Project task already assigned to this stage; please move them first.'));

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $stage?->delete();
                DB::commit();
                $inTransaction = false;

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Project stage successfully deleted.'));
            } catch (AuthorizationException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'stage_id' => $id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (ModelNotFoundException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' stage not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'stage_id' => $id,
                ]);
                $this->consoleOutput($method . ' stage not found', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }

    public const ORD = 'order';
    public function order(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json(['error' => __('Permission denied.')], 401);
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                if (($r = self::guard($request, 'move project stage', self::ROUTE_INDEX)) instanceof RedirectResponse)
                    return response()->json(['error' => __('Permission denied.')], 401);

                $order = $request->input('order', []);
                if (!is_array($order)) $order = [];

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                foreach ($order as $idx => $stageId) {
                    ProjectStage::where('id', $stageId)->update(['order' => $idx]);
                }
                DB::commit();
                $inTransaction = false;

                return response()->json(['success' => true], 200);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return response()->json(['error' => __('An unexpected error occurred.')], 500);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return response()->json(['error' => __('An unexpected error occurred.')], 500);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return response()->json(['error' => __('An unexpected error occurred.')], 500);
            }
        });
    }

    private function renderViewChecked(string $viewPath, array $data, string $action, int|string|null $userId): View|RedirectResponse
    {
        $t = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
        if (!$exists) {
            $this->consoleOutput(__METHOD__ . ' view missing: ' . $viewPath, 'error');
            Log::error(__METHOD__ . ' view not found', [
                'error' => 'view_missing',
                'error_class' => \RuntimeException::class,
                'file' => __FILE__,
                'line' => __LINE__,
                'action' => $action,
                'view' => $viewPath,
                'user_id' => $userId,
            ]);
            return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }

        return view($viewPath, $data);
    }

    /**
     * Show a single project stage.
     */
    public const SHW = 'show';
    public function show(Request $request, int|string $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PRJ_STG . '.' . $action;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');
            try {
                if (($r = self::guard($request, 'manage project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
                $projectStage = ProjectStage::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->findOrFail($id);
                Log::info($method . ' loaded', ['stage_id' => $id]);
                return $this->renderViewChecked($viewPath, compact('projectStage'), $action, $user?->id);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return Redirect::route(self::ROUTE_INDEX)->with('error', __('Project stage not found.'));
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        });
    }
}
