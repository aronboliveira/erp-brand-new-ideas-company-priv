<?php

namespace App\Http\Controllers\Planning;

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PMC, UsersConstants as UC, ViewsConstants as VW};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{Contract, ContractType};
use App\Traits\{ChecksLogin, ConsoleOutputs};
use Illuminate\Database\QueryException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

class ContractTypeController extends Controller
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
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $viewPath ??= VW::CTC_TP . '.' . $action;
            $types ??= collect();
            try {
                if (!$user?->can('manage contract type'))
                    return defaultPermissionDenial($request, new \Exception('Permission denied'), $method);
                if (!in_array(($user[UC::COL_TP] ?? null), [PMC::CPN, PMC::SA], true))
                    return defaultPermissionDenial($request, new \Exception('Invalid user type'), $method);

                $t = microtime(true);
                $types = ContractType::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get() ?? $types;
                $this->logExecutionTime($t, $action . '::fetchTypes', 'completed');

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

                $this->consoleOutput($method . ' loaded', 'info');
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
        }, ['user_id' => Auth::id()]);
    }

    public function create(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $viewPath ??= VW::CTC_TP . '.' . $action;
            try {
                if (!$user?->can('create contract type'))
                    return defaultPermissionDenial($request, new \Exception('Permission denied'), $method);

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

                $this->consoleOutput($method . ' loaded', 'info');
                return view($viewPath);
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
        }, ['user_id' => Auth::id()]);
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
                if (!$user?->can('create contract type'))
                    return defaultPermissionDenial($request, new \Exception('Permission denied'), $method);

                $t = microtime(true);
                $data = Validator::make($request->all(), ['name' => 'required|max:255'])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                DB::beginTransaction();
                $inTransaction = true;
                ContractType::create([
                    'name' => $data['name'] ?? '',
                    DC::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);
                DB::commit();
                $inTransaction = false;

                $this->consoleOutput($method . ' created', 'info');
                return Redirect::route(VW::CTC_TP . '.index')
                    ->with('success', __('Contract Type successfully created.'));
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

    public function show(Request $request, ContractType $contractType): RedirectResponse|View|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $contractType, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $viewPath ??= VW::CTC_TP . '.' . $action;
            try {
                if (!$user?->can('manage contract type'))
                    return defaultPermissionDenial($request, new \Exception('Permission denied'), $method);
                if (($contractType[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new \Exception('Ownership denied'), $method);

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
                        'contract_type_id' => $contractType?->id,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                $this->consoleOutput($method . ' loaded', 'info');
                return view($viewPath, compact('contractType'));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public function edit(Request $request, ContractType $contractType): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $contractType, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $viewPath ??= VW::CTC_TP . '.' . $action;
            try {
                if (!$user?->can('edit contract type'))
                    return defaultPermissionDenial($request, new \Exception('Permission denied'), $method);
                if (($contractType[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new \Exception('Ownership denied'), $method);

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
                        'contract_type_id' => $contractType?->id,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                $this->consoleOutput($method . ' loaded', 'info');
                return view($viewPath, compact('contractType'));
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['contract_type_id' => $contractType->id]);
    }

    public function update(Request $request, ContractType $contractType): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $contractType, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $data ??= [];
            $inTransaction ??= false;
            try {
                if (!$user?->can('edit contract type'))
                    return defaultPermissionDenial($request, new \Exception('Permission denied'), $method);
                if (($contractType[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new \Exception('Ownership denied'), $method);

                $t = microtime(true);
                $data = Validator::make($request->all(), ['name' => 'required|max:255'])->validate();
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                DB::beginTransaction();
                $inTransaction = true;
                $contractType->update([
                    'name' => $data['name'] ?? $contractType->name,
                    DC::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);
                DB::commit();
                $inTransaction = false;

                $this->consoleOutput($method . ' updated', 'info');
                return Redirect::route(VW::CTC_TP . '.index')
                    ->with('success', __('Contract Type successfully updated.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['contract_type_id' => $contractType->id]);
    }

    public function destroy(Request $request, ContractType $contractType): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $contractType, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                if (!$user?->can('delete contract type'))
                    return defaultPermissionDenial($request, new \Exception('Permission denied'), $method);
                if (($contractType[DC::COL_TABLE_CREATOR] ?? null) !== $user?->creatorId())
                    return defaultPermissionDenial($request, new \Exception('Ownership denied'), $method);

                $typeId = $contractType?->id;
                $typeId = is_numeric($typeId) ? (int) $typeId : null;
                if (!$typeId) return Redirect::back()->with('error', __('Contract Type not found.'));
                if (Contract::where('type', $typeId)->exists())
                    return Redirect::back()->with('error', __('This type is in use; please reassign or delete related contracts first.'));

                DB::beginTransaction();
                $inTransaction = true;
                $contractType->delete();
                DB::commit();
                $inTransaction = false;

                $this->consoleOutput($method . ' deleted', 'info');
                return Redirect::route(VW::CTC_TP . '.index')
                    ->with('success', __('Contract Type successfully deleted.'));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
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
                    'contract_type_id' => $contractType?->id,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['contract_type_id' => $contractType->id]);
    }
}
