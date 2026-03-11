<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BanksConstants,
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Models\{Branch, Department, Employee, Transfer, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Route, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
class TransferController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::TRF . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TRF . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            try {
                if (($redirect = self::guard($req, PermissionsConstants::MNG_TRF, self::ROUTE_INDEX)) !== true) return $redirect;
                $user = $req->user();
                Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'method' => $method]);
                $buildStart = microtime(true);
                $query = Transfer::with(['employee', 'branch', 'department'])->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
                if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                    $empId = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id');
                    $query->where(UsersConstants::COL_EMP_ID, $empId);
                }
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $transfers = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchTransfers');
                Log::info("[{$base}::{$action}] loaded transfers", ['count' => $transfers->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfers']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['transfers' => $transfers]);
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TRF . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'create transfer', self::ROUTE_INDEX)) !== true) return $redirect;
            $user = $req->user();
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'method' => $method]);
            $listsStart = microtime(true);
            $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(UsersConstants::COL_NM, 'id');
            $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['employees', 'departments', 'branches']]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('employees', 'departments', 'branches'));
            $this->logExecutionTime($renderStart, $action, 'renderCreate');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $request, Transfer $transfer): View|JsonResponse|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TRF . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_TRF, self::ROUTE_INDEX)) !== true) return $redirect;
            if ($transfer->created_by !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::ROUTE_INDEX));
            Log::info("[{$base}::{$action}] called", ['transferId' => $transfer->id, UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            try {
                if ($req->wantsJson()) return response()->json($transfer);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfer']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('transfer'));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'create transfer', self::ROUTE_INDEX)) !== true) return $redirect;
            $user = $req->user();
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'input' => $req->all(), 'method' => $method]);
            try {
                $valStart = microtime(true);
                $validator = Validator::make($req->all(), [
                    UsersConstants::COL_EMP_ID => 'required',
                    CompaniesConstants::COL_BRC_ID => 'required',
                    CompaniesConstants::COL_DEP_ID => 'required',
                    'transferDate' => 'required|date',
                ]);
                $this->logExecutionTime($valStart, $action, 'buildValidator');
                $data = $validator->validate();
                $txnStart = microtime(true);
                DB::transaction(function () use ($data, $user, $action, $base) {
                    $createStart = microtime(true);
                    $transfer = new Transfer();
                    $transfer->employee_id = $data[UsersConstants::COL_EMP_ID];
                    $transfer->branch_id = $data[CompaniesConstants::COL_BRC_ID];
                    $transfer->department_id = $data[CompaniesConstants::COL_DEP_ID];
                    $transfer->transfer_date = $data['transferDate'];
                    $transfer->description = $data['description'] ?? '';
                    $transfer->created_by = $user?->creatorId();
                    $transfer->save();
                    $this->logExecutionTime($createStart, $action, 'createTransfer');
                    Log::info("[{$base}::{$action}] created transfer", ['id' => $transfer->id]);
                    $settingsStart = microtime(true);
                    $settings = Utility::settingsById($user?->creatorId());
                    $this->logExecutionTime($settingsStart, $action, 'loadSettings');
                    if ($settings['transfer_sent'] ?? false) {
                        $mailPrepStart = microtime(true);
                        $emp = Employee::findOrFail($transfer->employee_id);
                        $branch = Branch::findOrFail($transfer->branch_id);
                        $dept = Department::findOrFail($transfer->department_id);
                        $transferArr = [
                            'transfer_name' => $emp->name,
                            'transfer_email' => $emp->email,
                            'transfer_date' => $transfer->transfer_date,
                            'transfer_department' => $dept->name,
                            'transfer_branch' => $branch->name,
                            'transfer_description' => $transfer->description,
                        ];
                        $this->logExecutionTime($mailPrepStart, $action, 'prepareEmail');
                        $sendStart = microtime(true);
                        $resp = Utility::sendEmailTemplate('transfer_sent', [$emp->id => $emp->email], $transferArr);
                        $this->logExecutionTime($sendStart, $action, 'sendEmail');
                        Log::info("[{$base}::{$action}] emailed transfer", ['success' => $resp['is_success']]);
                    }
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Transfer successfully created.'));
            } catch (ValidationException $e) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $e->errors()]);
                Log::debug("[{$base}::{$action}] validation debug", ['route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $request, Transfer $transfer): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TRF . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'edit transfer', self::ROUTE_INDEX)) !== true) return $redirect;
            $user = $req->user();
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'transfer_id' => $transfer->id, 'method' => $method]);
            if ($transfer->created_by !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::ROUTE_INDEX), false);
            $listsStart = microtime(true);
            $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(UsersConstants::COL_NM, 'id');
            $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfer', 'employees', 'departments', 'branches']]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('transfer', 'employees', 'departments', 'branches'));
            $this->logExecutionTime($renderStart, $action, 'renderEdit');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
    }

    public function update(Request $request, Transfer $transfer): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'edit transfer', self::ROUTE_INDEX)) !== true) return $redirect;
            $user = $req->user();
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'transfer_id' => $transfer->id, 'method' => $method]);
            if ($transfer->created_by !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::ROUTE_INDEX), false);
            try {
                $valStart = microtime(true);
                $validator = Validator::make($req->all(), [
                    UsersConstants::COL_EMP_ID => 'required',
                    CompaniesConstants::COL_BRC_ID => 'required',
                    CompaniesConstants::COL_DEP_ID => 'required',
                    'transferDate' => 'required|date',
                ]);
                $this->logExecutionTime($valStart, $action, 'buildValidator');
                $data = $validator->validate();
                $txnStart = microtime(true);
                DB::transaction(function () use ($transfer, $data, $action) {
                    $updStart = microtime(true);
                    $transfer->employee_id = $data[UsersConstants::COL_EMP_ID];
                    $transfer->branch_id = $data[CompaniesConstants::COL_BRC_ID];
                    $transfer->department_id = $data[CompaniesConstants::COL_DEP_ID];
                    $transfer->transfer_date = $data['transferDate'];
                    $transfer->description = $data['description'] ?? '';
                    $transfer->save();
                    $this->logExecutionTime($updStart, $action, 'updateTransfer');
                    Log::info('update transfer', ['id' => $transfer->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Transfer successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'transfer_id' => $transfer->id, 'input_keys' => array_keys($req->all())]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
    }

    public function destroy(Request $request, Transfer $transfer): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'delete transfer', self::ROUTE_INDEX)) !== true) return $redirect;
            $user = $req->user();
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'transfer_id' => $transfer->id, 'method' => $method]);
            if ($transfer->created_by !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::ROUTE_INDEX), false);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($transfer, $action) {
                    $delStart = microtime(true);
                    $transfer->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteTransfer');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] deleted transfer", ['id' => $transfer->id]);
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Transfer successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'transfer_id' => $transfer->id]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
    }
}
