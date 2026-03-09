<?php

namespace App\Http\Controllers\Planning;

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PMC, ViewsConstants as VW};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Goal;
use App\Traits\{ChecksLogin, ConsoleOutputs};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\{DB, Log, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

class GoalController extends Controller
{
    use ChecksLogin, ConsoleOutputs;
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';


    public function index(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::GL . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $goals ??= collect();
            try {
                if (($redirect = $this->authorizeAction($request, PMC::MNG_GL, $method)) instanceof RedirectResponse)
                    return $redirect;

                $t = microtime(true);
                $goals = Goal::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get() ?? $goals;
                $this->logExecutionTime($t, $action . '::fetchGoals', 'completed');

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('goals'));
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
                return defaultUndefinedException($request, $e, $method);
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
                return defaultUndefinedException($request, $e, $method);
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
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function create(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::GL . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $types ??= [];
            try {
                if (($redirect = $this->authorizeAction($request, 'create goal', $method)) instanceof RedirectResponse)
                    return $redirect;

                $types = Goal::$goalType ?? $types;

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('types'));
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
                return defaultUndefinedException($request, $e, $method);
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
                return defaultUndefinedException($request, $e, $method);
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
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $inTransaction ??= false;
            try {
                if (($redirect = $this->authorizeAction($request, 'create goal', $method)) instanceof RedirectResponse)
                    return $redirect;

                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    'name' => 'required',
                    'type' => 'required',
                    'from' => 'required|date',
                    'to' => 'required|date|after_or_equal:from',
                    'amount' => 'required|numeric',
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                Goal::create([
                    'name' => $data['name'] ?? '',
                    'type' => $data['type'] ?? '',
                    'from' => $data['from'] ?? null,
                    'to' => $data['to'] ?? null,
                    'amount' => $data['amount'] ?? 0,
                    'is_display' => (bool) ($request->boolean('is_display') ?? false),
                    DC::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);
                DB::commit();
                $inTransaction = false;

                return Redirect::route(VW::GL . '.index')
                    ->with('success', __('Goal successfully created.'));
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
                return Redirect::back()->with('error', $e->validator?->errors()->first());
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
                return defaultUndefinedException($request, $e, $method);
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
                return defaultUndefinedException($request, $e, $method);
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
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function show(Request $request, Goal $goal): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $goal, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            return Redirect::route(VW::GL . '.index')->with($goal);
        });
    }

    public function edit(Request $request, Goal $goal): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::GL . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $goal, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $types ??= [];
            try {
                if (($redirect = $this->authorizeAction($request, 'edit goal', $method)) instanceof RedirectResponse)
                    return $redirect;
                if (($goal[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $method);

                $types = Goal::$goalType ?? $types;

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'goal_id' => $goal?->id,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('goal', 'types'));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $goal, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $inTransaction ??= false;
            try {
                if (($redirect = $this->authorizeAction($request, 'edit goal', $method)) instanceof RedirectResponse)
                    return $redirect;
                if (($goal[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $method);

                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    'name' => 'required',
                    'type' => 'required',
                    'from' => 'required|date',
                    'to' => 'required|date|after_or_equal:from',
                    'amount' => 'required|numeric',
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $goal->update([
                    'name' => $data['name'] ?? $goal->name,
                    'type' => $data['type'] ?? $goal->type,
                    'from' => $data['from'] ?? $goal->from,
                    'to' => $data['to'] ?? $goal->to,
                    'amount' => $data['amount'] ?? $goal->amount,
                    'is_display' => (bool) ($request->boolean('is_display') ?? $goal->is_display),
                ]);
                DB::commit();
                $inTransaction = false;

                return Redirect::route(VW::GL . '.index')
                    ->with('success', __('Goal successfully updated.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return Redirect::back()->with('error', $e->validator?->errors()->first());
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function destroy(Request $request, Goal $goal): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $goal, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                if (($redirect = $this->authorizeAction($request, 'delete goal', $method)) instanceof RedirectResponse)
                    return $redirect;
                if (($goal[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $method);

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $goal->delete();
                DB::commit();
                $inTransaction = false;

                return Redirect::route(VW::GL . '.index')
                    ->with('success', __('Goal successfully deleted.'));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'goal_id' => $goal?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    private function authorizeAction(Request $request, string $ability, string $method): ?RedirectResponse
    {
        $user ??= $request->user();
        if ($user?->can($ability)) return null;
        Log::warning($method . ' permission denied', [
            'error' => 'permission_denied',
            'error_class' => AuthorizationException::class,
            'file' => __FILE__,
            'line' => __LINE__,
            'ability' => $ability,
            'user_id' => $user?->id,
        ]);
        $this->consoleOutput($method . ' permission denied', 'error');
        return defaultPermissionDenial($request, new AuthorizationException(), $method);
    }
}
