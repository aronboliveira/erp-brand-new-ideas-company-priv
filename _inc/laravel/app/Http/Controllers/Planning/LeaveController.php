<?php

namespace App\Http\Controllers\Planning;

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PMC, UsersConstants as UC, ViewsConstants as VW};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{Employee, Leave, LeaveType, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions, ConsoleOutputs};
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\{Arr, Facades\DB, Facades\Log, Facades\Redirect, Facades\Validator, Facades\View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\DefinesResourceActions;
class LeaveController extends Controller
{
	use DefinesResourceActions;

    use ChecksLogin, ChecksPermissions, ConsoleOutputs;

    public function index(Request $request): View|string|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::LV . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $leaves ??= collect();
            try {
                if (($c = self::guard($request, PMC::MNG_LV, VW::LV . '.index')) !== true) return $c;

                $t = microtime(true);
                $creatorId = $user?->creatorId() ?: $user?->id;
                $query = Leave::query()->with(['leaveType', 'employees']);
                if (strtolower((string) ($user[UC::COL_TP] ?? '')) === 'employee') {
                    $empId = Employee::where(UC::COL_USER_ID, $user?->id)->value('id');
                    $empId = is_numeric($empId) ? (int) $empId : null;
                    if ($empId) $query->where(UC::COL_EMP_ID, $empId);
                } else {
                    $query->where(DC::COL_TABLE_CREATOR, $creatorId);
                }
                $leaves = $query->get() ?? $leaves;
                $this->logExecutionTime($t, $action . '::fetchLeaves', 'completed');

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

                return view($viewPath, compact('leaves'));
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
                return defaultPermissionDenial($request, $e, $method);
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

    public function create(Request $request): View|string|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::LV . '.' . $action;

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $employees ??= collect();
            $leaveTypes ??= collect();
            try {
                if (($c = self::guard($request, 'create leave', VW::LV . '.create')) !== true) return $c;

                $t = microtime(true);
                $creatorId = $user?->creatorId() ?: $user?->id;
                $empQuery = Employee::query();
                if (strtolower((string) ($user[UC::COL_TP] ?? '')) === 'employee') {
                    $empQuery->where(UC::COL_USER_ID, $user?->id);
                } else {
                    $empQuery->where(DC::COL_TABLE_CREATOR, $creatorId);
                }
                $employees = $empQuery->get()->pluck(UC::COL_NM, 'id') ?? $employees;
                $leaveTypes = LeaveType::where(DC::COL_TABLE_CREATOR, $creatorId)->get() ?? $leaveTypes;
                $this->logExecutionTime($t, $action . '::fetchFormData', 'completed');

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

                return view($viewPath, compact('employees', 'leaveTypes'));
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
                return defaultPermissionDenial($request, $e, $method);
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

    public function store(Request $request): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $leaveType ??= null;
            $inTransaction ??= false;
            try {
                if (($c = self::guard($request, 'create leave', VW::LV . '.store')) !== true) return $c;

                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    'leave_type_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'leave_reason' => 'required',
                    'remark' => 'required'
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $empId = strtolower((string) ($user[UC::COL_TP] ?? '')) === 'employee'
                    ? Employee::where(UC::COL_USER_ID, $user?->id)->value('id')
                    : $request->input(UC::COL_EMP_ID);
                $empId = is_numeric($empId) ? (int) $empId : null;
                if (!$empId) return Redirect::back()->with('error', __('Employee not found.'));

                $leaveType = LeaveType::find($data['leave_type_id'] ?? null);
                if (!$leaveType) return Redirect::back()->with('error', __('Leave type not found.'));

                $start = Carbon::parse($data['start_date']);
                $end = Carbon::parse($data['end_date']);
                $days = $start->diffInDays($end->copy()->addDay());
                if (($leaveType->days ?? 0) < $days) {
                    return Redirect::back()->with('error', 'Leave type ' . ($leaveType->name ?? '') . ' allows max ' . ($leaveType->days ?? 0) . ' days');
                }

                $payload = Arr::only($data, ['leave_type_id', 'start_date', 'end_date', 'leave_reason', 'remark']);
                $payload[UC::COL_EMP_ID] = $empId;
                $payload['applied_on'] = date('Y-m-d');
                $payload['total_leave_days'] = $days;
                $payload['status'] = 'Pending';
                $payload[DC::COL_TABLE_CREATOR] = $user?->creatorId();

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                Leave::create($payload);
                DB::commit();
                $inTransaction = false;

                return Redirect::route(VW::LV . '.index')->with('success', 'Leave successfully created.');
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
                return defaultPermissionDenial($request, $e, $method);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_type_id' => $leaveType?->id,
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
                    'leave_type_id' => $leaveType?->id,
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
                    'leave_type_id' => $leaveType?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function show(Request $request, Leave $leave): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $leave, $method) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            try {
                if (($leave[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $method);
                return Redirect::route(VW::LV . '.index');
            } catch (\Throwable $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                ]);
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function edit(Request $request, Leave $leave): View|string|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::LV . '.edit';

        return $this->measureProfile($action, function () use ($request, $leave, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $emps ??= collect();
            $lts ??= collect();
            try {
                if (($c = self::guard($request, 'edit leave', VW::LV . '.edit')) !== true) return $c;
                if (($leave[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $method);

                $t = microtime(true);
                $creatorId = $user?->creatorId();
                $emps = Employee::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->get()
                    ->pluck('name', 'id') ?? $emps;
                $lts = LeaveType::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->get()
                    ->pluck('title', 'id') ?? $lts;
                $this->logExecutionTime($t, $action . '::fetchFormData', 'completed');

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
                        'leave_id' => $leave?->id,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('leave', 'emps', 'lts'));
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
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
                    'leave_id' => $leave?->id,
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
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $leaveType ??= null;
            $leave ??= null;
            $inTransaction ??= false;
            try {
                if (($c = self::guard($request, 'edit leave', VW::LV . '.update')) !== true) return $c;

                $leave = Leave::find($request->input('leave_id'));
                if (!$leave) return Redirect::back()->with('error', __('Leave not found.'));
                if (($leave[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $method);

                $t = microtime(true);
                $data = Validator::make($request->all(), [
                    'leave_type_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'leave_reason' => 'required',
                    'remark' => 'required'
                ])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $leaveType = LeaveType::find($data['leave_type_id'] ?? null);
                if (!$leaveType) return Redirect::back()->with('error', __('Leave type not found.'));
                $start = Carbon::parse($data['start_date']);
                $end = Carbon::parse($data['end_date']);
                $days = $start->diffInDays($end->copy()->addDay());
                if (($leaveType->days ?? 0) < $days) {
                    return Redirect::back()->with('error', 'Leave type ' . ($leaveType->name ?? '') . ' allows max ' . ($leaveType->days ?? 0) . ' days');
                }

                $upd = Arr::only($data, [UC::COL_EMP_ID, 'leave_type_id', 'start_date', 'end_date', 'leave_reason', 'remark']);
                $upd['total_leave_days'] = $days;

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $leave->update($upd);
                DB::commit();
                $inTransaction = false;

                return Redirect::route(VW::LV . '.index')->with('success', 'Leave successfully updated.');
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return Redirect::back()->with('error', $e->validator?->errors()->first());
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
                    'leave_type_id' => $leaveType?->id,
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
                    'leave_id' => $leave?->id,
                    'leave_type_id' => $leaveType?->id,
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
                    'leave_id' => $leave?->id,
                    'leave_type_id' => $leaveType?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function destroy(Request $request, Leave $leave): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $leave, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                if (($c = self::guard($request, 'delete leave', VW::LV . '.destroy')) !== true) return $c;
                if (($leave[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $method);

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $leave->delete();
                DB::commit();
                $inTransaction = false;

                return Redirect::route(VW::LV . '.index')->with('success', 'Leave successfully deleted.');
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
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
                    'leave_id' => $leave?->id,
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
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function action(Request $request, int|string $id): View|string|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::LV . '.action';

        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $lv ??= null;
            $emp ??= null;
            $lt ??= null;
            try {
                if (($c = self::guard($request, PMC::MNG_LV, VW::LV . '.action')) !== true) return $c;

                $lv = Leave::find($id);
                if (!$lv) return Redirect::back()->with('error', __('Leave not found.'));
                $emp = Employee::find($lv->employee_id ?? null);
                $lt = LeaveType::find($lv->leave_type_id ?? null);

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
                        'leave_id' => $lv?->id,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('emp', 'lt', 'lv'));
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $lv?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $lv?->id,
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
                    'leave_id' => $lv?->id,
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
                    'leave_id' => $lv?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const CHG_ACT = 'changeAction';
    public function changeAction(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user ??= $u;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $leave ??= null;
            $inTransaction ??= false;
            try {
                if (($c = self::guard($request, PMC::MNG_LV, VW::LV . '.change_action')) !== true) return $c;

                $leave = Leave::find($request->input('leave_id'));
                if (!$leave) return Redirect::back()->with('error', __('Leave not found.'));
                $st = (string) $request->input('status');
                $upd = ['status' => $st];
                if ($st === 'Approval') {
                    $start = $leave->start_date ? Carbon::parse($leave->start_date) : null;
                    $end = $leave->end_date ? Carbon::parse($leave->end_date) : null;
                    $upd['total_leave_days'] = $start && $end ? $start->diffInDays($end) : 0;
                    $upd['status'] = 'Approved';
                }

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $leave->update($upd);
                DB::commit();
                $inTransaction = false;

                $set = Utility::settings();
                if ($set['leave_status'] ?? 0) {
                    $emp = Employee::where('id', $leave->employee_id)
                        ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                        ->first();
                    $arr = [
                        'leave_name' => $emp->name ?? '',
                        'leave_status' => $leave->status,
                        'leave_reason' => $leave->leave_reason,
                        'leave_start_date' => $leave->start_date,
                        'leave_end_date' => $leave->end_date,
                        'total_leave_days' => $leave->total_leave_days
                    ];
                    $resp = Utility::sendEmailTemplate('leave_action_sent', [$emp->id => $emp->email], $arr);
                    return Redirect::route(VW::LV . '.index')
                        ->with('success', 'Leave status updated.' . ($resp['is_success'] === false && ($resp['error'] ?? false) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
                }

                return Redirect::route(VW::LV . '.index')->with('success', 'Leave status updated.');
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'leave_id' => $leave?->id,
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
                    'leave_id' => $leave?->id,
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
                    'leave_id' => $leave?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const JSON_CT = 'jsonCount';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function jsonCount(Request $request): array|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return [];
            $user ??= $userOrRedirect;

            $out ??= [];
            try {
                if (($c = self::guard($request, 'view leave', VW::LV . '.jsoncount')) !== true) return [];

                $creatorId = $user?->creatorId();
                $types = LeaveType::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
                foreach ($types as $t) {
                    $sum = Leave::where('leave_type_id', $t->id)
                        ->where(UC::COL_EMP_ID, $request->input(UC::COL_EMP_ID))
                        ->sum('total_leave_days');
                    $out[] = Arr::only([
                        'total_leave' => $sum,
                        'title' => $t->title,
                        'days' => $t->days,
                        'id' => $t->id
                    ], ['total_leave', 'title', 'days', 'id']);
                }
                return $out;
            } catch (AuthorizationException $e) {
                Log::warning($method . ' auth failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' auth failed', 'error');
                return [];
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
                return [];
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
                return [];
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
                return [];
            }
        });
    }
}
