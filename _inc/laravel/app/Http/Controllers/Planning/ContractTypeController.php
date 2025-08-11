<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Contract, ContractType};
use App\Traits\ChecksLogin;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, Log};

class ContractTypeController extends Controller
{
    use ChecksLogin;

    public function index(Request $request): RedirectResponse|View
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $function) {
            Log::info("[$action] started", ['user_ip' => $request->ip()]);
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('manage contract type')) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id]);
                return defaultPermissionDenial($request, null, $action);
            }
            try {
                if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CPN) {
                    Log::warning("[$action] invalid user type", ['user_id' => $user?->id, 'type' => $user[UsersConstants::COL_TP]]);
                    return defaultPermissionDenial($request, null, $action);
                }
                $typesStart = microtime(true);
                $types = ContractType::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($typesStart, $action . '::fetchTypes', 'completed');
                Log::info("[$action] loaded types", ['count' => $types->count(), 'user_id' => $user?->id]);
                return view(ViewsConstants::CTC_TP . '.' . $function, compact('types'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'user_id' => $user?->id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['user_id' => Auth::id()]);
    }

    public function create(Request $request): RedirectResponse|View
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => auth()->id()]);
        return $this->measureProfile($method, function () use ($request, $method, $function) {
            $stepStart = microtime(true);
            Log::info($method . ' started');
            $this->logExecutionTime($stepStart, 'logStart', 'completed');
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can('create contract type')) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            Log::info($method . ' rendering form', ['user_id' => $user?->id]);
            return view(ViewsConstants::CTC_TP . '.' . $function);
        }, ['user_id' => auth()->id()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            $startAction = microtime(true);
            Log::info($method . ' started', ['input' => $request->only('name')]);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startAction, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startAction, $function . '::login', 'completed');
            if (!$user?->can('create contract type')) {
                Log::warning($method . ' permission denied', [UsersConstants::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            $startValidation = microtime(true);
            $v = validator($request->all(), ['name' => 'required|max:255']);
            if ($v->fails()) {
                Log::warning($method . ' validation failed', ['errors' => $v->errors()->all()]);
                $this->logExecutionTime($startValidation, $function . '::validation', 'failed');
                return redirect()->back()->with('error', $v->errors()->first());
            }
            $this->logExecutionTime($startValidation, $function . '::validation', 'completed');
            try {
                $startCreate = microtime(true);
                ContractType::create([
                    'name' => $request->input('name'),
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                ]);
                $this->logExecutionTime($startCreate, $function . '::createContractType', 'completed');
                Log::info($method . ' created type', ['name' => $request->input('name'), UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->route(ViewsConstants::CTC_TP . '.index')
                    ->with('success', __('Contract Type successfully created.'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public function show(Request $request, ContractType $contractType): RedirectResponse|View|null
    {
        $class = static::class;
        $function = __FUNCTION__;
        $action = "{$class}::{$function}";
        return $this->measureProfile($action, function () use ($request, $contractType, $action, $function) {
            Log::info("$action started", ['contract_type_id' => $contractType->id]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (!$user?->can('manage contract type')) {
                    Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
                    return defaultPermissionDenial($request, null, $action);
                }
                if ($contractType[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                    Log::warning("$action ownership denied", [
                        UsersConstants::COL_USER_ID => $user?->id,
                        'contract_type_id' => $contractType->id
                    ]);
                    return defaultPermissionDenial($request, null, $action);
                }
                Log::info("$action rendering", ['contract_type_id' => $contractType->id]);
                $this->logExecutionTime($stepStart, 'render contract type view', 'completed');
                return view(ViewsConstants::CTC_TP . '.' . $function, compact('contractType'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'contract_type_id' => $contractType->id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'contract_type_id' => $contractType->id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function edit(Request $request, ContractType $contractType): RedirectResponse|View
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $contractType, $function) {
            Log::info("[$action] started", ['contract_type_id' => $contractType->id]);
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('edit contract type')) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id]);
                return defaultPermissionDenial($request, null, $action);
            }
            if ($contractType[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("[$action] ownership denied", ['user_id' => $user?->id, 'contract_type_id' => $contractType->id]);
                return defaultPermissionDenial($request, null, $action);
            }
            Log::info("[$action] rendering form", ['contract_type_id' => $contractType->id]);
            return view(ViewsConstants::CTC_TP . '.' . $function, compact('contractType'));
        }, ['contract_type_id' => $contractType->id]);
    }

    public function update(Request $request, ContractType $contractType): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['contract_type_id' => $contractType->id, 'input' => $request->only('name')]);
        return $this->measureProfile($method, function () use ($request, $contractType, $method) {
            $stepStart = microtime(true);
            Log::info($method . ' started', ['contract_type_id' => $contractType->id, 'input' => $request->only('name')]);
            $this->logExecutionTime($stepStart, 'logStart', 'completed');
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can('edit contract type')) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            $stepStart = microtime(true);
            if ($contractType[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning($method . ' ownership denied', ['user_id' => $user?->id, 'contract_type_id' => $contractType->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            $this->logExecutionTime($stepStart, 'checkOwnership', 'completed');
            $stepStart = microtime(true);
            $v = validator($request->all(), ['name' => 'required|max:255']);
            $this->logExecutionTime($stepStart, 'validateRequest', 'completed');
            if ($v->fails()) {
                Log::warning($method . ' validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            $stepStart = microtime(true);
            try {
                $contractType->update([
                    'name' => $request->input('name'),
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                ]);
                $this->logExecutionTime($stepStart, 'updateContractType', 'completed');
                Log::info($method . ' updated', ['contract_type_id' => $contractType->id]);
                return redirect()->route(ViewsConstants::CTC_TP . '.index')
                    ->with('success', __('Contract Type successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['contract_type_id' => $contractType->id]);
    }

    public function destroy(Request $request, ContractType $contractType): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $contractType, $function) {
            $method = static::class . '::' . $function;
            $startAction = microtime(true);
            Log::info($method . ' started', ['contract_type_id' => $contractType->id]);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startAction, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startAction, $function . '::login', 'completed');
            if (!$user?->can('delete contract type')) {
                Log::warning($method . ' permission denied', [UsersConstants::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            if ($contractType[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning($method . ' ownership denied', [UsersConstants::COL_USER_ID => $user?->id, 'contract_type_id' => $contractType->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            if (Contract::where('type', $contractType->id)->exists()) {
                Log::warning($method . ' prevented—type in use', ['contract_type_id' => $contractType->id]);
                return redirect()->back()->with('error', __('This type is in use; please reassign or delete related contracts first.'));
            }
            try {
                $startDelete = microtime(true);
                $contractType->delete();
                $this->logExecutionTime($startDelete, $function . '::delete', 'completed');
                Log::info($method . ' deleted', ['contract_type_id' => $contractType->id]);
                return redirect()->route(ViewsConstants::CTC_TP . '.index')
                    ->with('success', __('Contract Type successfully deleted.'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }
}
