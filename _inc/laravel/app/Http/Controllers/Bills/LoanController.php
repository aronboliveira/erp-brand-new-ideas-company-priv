<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants as VW,
};
use App\Models\{Employee, Loan, LoanOption, Utility};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Route, View as ViewFacade};

final class LoanController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    /** Show the form to create a loan */
    public const LN_CRT = 'loanCreate';
    public function loanCreate(string|int $employeeId, Request $request): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $employeeId, $action, $method, $class, $base) {
            Log::debug("{$class}::{$action} start", [UsersConstants::COL_EMP_ID => $employeeId ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($deny = self::guard($request, 'create loan', VW::LN . '.index')) !== true) return $deny;
            try {
                $empStart = microtime(true);
                $employee = Employee::findOrFail($employeeId);
                $this->logExecutionTime($empStart, $action, 'findEmployee');
                $optStart = microtime(true);
                $creatorId = $request->user()?->creatorId() ?? null;
                $options = LoanOption::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');
                $this->logExecutionTime($optStart, $action, 'loadOptions');
                $typeStart = microtime(true);
                $types = self::loanTypes();
                $this->logExecutionTime($typeStart, $action, 'loadTypes');
                Log::debug("{$class}::{$action} ready form", [UsersConstants::COL_EMP_ID => $employeeId ?? null, 'option_count' => $options->count(), 'type_count' => count($types)]);
                $viewPath = VW::LN . '.create';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("{$class}::{$action} missing view", ['view_path' => $viewPath]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('employee', 'options', 'types'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} error", [UsersConstants::COL_EMP_ID => $employeeId ?? null, 'exception' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("{$class}::{$action} error", [UsersConstants::COL_EMP_ID => $employeeId ?? null, 'exception' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, UsersConstants::COL_EMP_ID => $employeeId]);
    }

    /** Persist a new loan */
    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            Log::debug("{$class}::{$action} start", ['input' => $request->all(), UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($deny = self::guard($request, 'create loan', VW::LN . '.index')) !== true) return $deny;
            $valStart = microtime(true);
            $request->validate([
                UsersConstants::COL_EMP_ID => 'required|exists:employees,id',
                'loan_option'              => 'required|exists:loan_options,id',
                'title'                    => 'required|string|max:191',
                'amount'                   => 'required|numeric|min:0.01',
                'reason'                   => 'required|string',
                'type'                     => 'nullable|string',
            ]);
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $action, $class) {
                    $data = [
                        UsersConstants::COL_EMP_ID       => $request->employee_id,
                        'loan_option'                    => $request->loan_option,
                        'title'                          => $request->title,
                        'amount'                         => (float) $request->amount,
                        'reason'                         => $request->reason,
                        'type'                           => $request->type ?? null,
                        DatabaseConstants::TABLE_CREATOR => $request->user()?->creatorId() ?? null,
                    ];
                    $createStart = microtime(true);
                    $new = Loan::create($data);
                    $this->logExecutionTime($createStart, $action, 'createLoan');
                    Log::info("{$class}::{$action} created", ['loan_id' => $new->id ?? null, UsersConstants::COL_EMP_ID => $new->employee_id ?? null, 'amount' => $new->amount ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', __('Loan successfully created.'));
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['exception' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("{$class}::{$action} failed", ['exception' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    /** Show the form to edit a loan */
    public function edit(string|int $loanId, Request $request): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $loanId, $action, $method, $class, $base) {
            Log::debug("{$class}::{$action} start", ['loan_id' => $loanId ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($deny = self::guard($request, 'edit loan', VW::LN . '.index')) !== true) return $deny;
            try {
                $findStart = microtime(true);
                $loan = Loan::findOrFail($loanId);
                $this->logExecutionTime($findStart, $action, 'findLoan');
                if (($loan->created_by ?? null) !== ($request->user()?->creatorId() ?? null)) {
                    Log::warning("{$class}::{$action} forbidden", ['loan_id' => $loanId ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                    return defaultPermissionDenial($request, new \Exception('owner'), "{$class}::{$action}");
                }
                $optStart = microtime(true);
                $options = LoanOption::where(DatabaseConstants::TABLE_CREATOR, $request->user()?->creatorId() ?? null)->pluck('name', 'id');
                $this->logExecutionTime($optStart, $action, 'loadOptions');
                $typeStart = microtime(true);
                $types = self::loanTypes();
                $this->logExecutionTime($typeStart, $action, 'loadTypes');
                Log::debug("{$class}::{$action} ready form", ['loan_id' => $loanId ?? null]);
                $viewPath = VW::LN . '.edit';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("{$class}::{$action} missing view", ['view_path' => $viewPath]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('loan', 'options', 'types'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} error", ['loan_id' => $loanId ?? null, 'exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$class}::{$action}");
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'loan_id' => $loanId]);
    }

    /** Persist updates to a loan */
    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $loan, $action, $method, $class, $base) {
            Log::debug("{$class}::{$action} start", ['loan_id' => $loan->id ?? null, 'input' => $request->all(), UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($deny = self::guard($request, 'edit loan', VW::LN . '.index')) !== true) return $deny;
            if (($loan->created_by ?? null) !== ($request->user()?->creatorId() ?? null)) {
                Log::warning("{$class}::{$action} forbidden owner-mismatch", ['loan_id' => $loan->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), "{$class}::{$action}");
            }
            $valStart = microtime(true);
            $request->validate([
                'loan_option' => 'required|exists:loan_options,id',
                'title'       => 'required|string|max:191',
                'amount'      => 'required|numeric|min:0.01',
                'reason'      => 'required|string',
                'type'        => 'nullable|string',
            ]);
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($loan, $request, $action, $class) {
                    $repStart = microtime(true);
                    $old = $loan->replicate();
                    $this->logExecutionTime($repStart, $action, 'replicateLoan');
                    $updStart = microtime(true);
                    $loan->update($request->only(['loan_option', 'title', 'amount', 'reason', 'type']));
                    $this->logExecutionTime($updStart, $action, 'updateLoan');
                    Log::info("{$class}::{$action} updated", ['loan_id' => $loan->id ?? null, 'before' => $old->toArray(), 'after' => $loan->toArray()]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', __('Loan successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['loan_id' => $loan->id ?? null, 'exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$class}::{$action}");
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'loan_id' => $loan->id ?? null]);
    }

    /** Delete a loan */
    public function destroy(Request $request, Loan $loan): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $loan, $action, $method, $class, $base) {
            Log::debug("{$class}::{$action} start", ['loan_id' => $loan->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($deny = self::guard($request, 'delete loan', VW::LN . '.index')) !== true) return $deny;
            if (($loan->created_by ?? null) !== ($request->user()?->creatorId() ?? null)) {
                Log::warning("{$class}::{$action} forbidden owner-mismatch", ['loan_id' => $loan->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), "{$class}::{$action}");
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($loan, $action, $class) {
                    $delStart = microtime(true);
                    $loan->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteLoan');
                    Log::info("{$class}::{$action} deleted", ['loan_id' => $loan->id ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', __('Loan successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['loan_id' => $loan->id ?? null, 'exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$class}::{$action}");
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'loan_id' => $loan->id ?? null]);
    }

    /** Stub for compatibility */
    public function show(): RedirectResponse
    {
        return redirect()->route(VW::LN . '.index');
    }


    /** Centralized permission check with logging */
    private function deny(Request $request, string $permission, string $action): ?RedirectResponse
    {
        if (!$request->user()->can($permission)) {
            Log::warning(__CLASS__ . "::{$action} permission denied", [
                'user_id'   => $request->user()->id,
                'permission' => $permission,
            ]);
            return defaultPermissionDenial(
                $request,
                new \Illuminate\Auth\Access\AuthorizationException($permission),
                __CLASS__ . '::' . $action
            );
        }
        return null;
    }

    /** 
     * Fallback for loan types 
     * @return array<string,string>
     */
    private static function loanTypes(): array
    {
        return Loan::$loanTypes
            ?? Loan::$Loantypes
            ?? [];
    }
}
