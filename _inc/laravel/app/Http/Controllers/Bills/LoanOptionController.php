<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Models\LoanOption;
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Log, Route, View as ViewFacade};
use Illuminate\View\View;

final class LoanOptionController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            Log::debug($class . '::' . $action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($request, 'manage loan option', VW::LN_OPT . '.index')) !== true) return $r;
            try {
                $qStart = microtime(true);
                $loanOptions = LoanOption::where(DatabaseConstants::TABLE_CREATOR, $request->user()?->creatorId() ?? null)->get();
                $this->logExecutionTime($qStart, $action, 'fetchLoanOptions');
                Log::info($class . '::' . $action . ' fetched', ['count' => $loanOptions->count(), UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                $viewPath = VW::LN_OPT . '.' . $action;
                if (!ViewFacade::exists($viewPath)) {
                    Log::error($class . '::' . $action . ' missing view', ['view_path' => $viewPath]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('loanOptions'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error($class . '::' . $action . ' failed', ['error' => $e->getMessage(), UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): View|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            Log::debug($class . '::' . $action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($request, 'create loan option', VW::LN_OPT . '.index')) !== true) return $r;
            $viewPath = VW::LN_OPT . '.' . $action;
            if (!ViewFacade::exists($viewPath)) {
                Log::error($class . '::' . $action . ' missing view', ['view_path' => $viewPath]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath);
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            Log::debug($class . '::' . $action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'input' => $request->only('name')]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($request, 'create loan option', VW::LN_OPT . '.index')) !== true) return $r;
            $valStart = microtime(true);
            $request->validate(['name' => 'required|string|max:20']);
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $class, $action) {
                    $crtStart = microtime(true);
                    $opt = LoanOption::create(['name' => $request->name, DatabaseConstants::TABLE_CREATOR => $request->user()?->creatorId() ?? null]);
                    $this->logExecutionTime($crtStart, $action, 'createLoanOption');
                    Log::info($class . '::' . $action . ' created', ['loan_option_id' => $opt->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(VW::LN_OPT . '.index')->with('success', __('Loan option successfully created.'));
            } catch (\Throwable $e) {
                Log::error($class . '::' . $action . ' failed', ['error' => $e->getMessage(), UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(LoanOption $loanOption, Request $request): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($loanOption, $request, $action, $method, $class, $base) {
            Log::debug($class . '::' . $action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'loan_option_id' => $loanOption->id ?? null]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($request, 'edit loan option', VW::LN_OPT . '.index')) !== true) return $r;
            if (!self::isOwner($loanOption)) {
                Log::warning($class . '::' . $action . ' forbidden', ['loan_option_id' => $loanOption->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
            }
            $viewPath = VW::LN_OPT . '.' . $action;
            if (!ViewFacade::exists($viewPath)) {
                Log::error($class . '::' . $action . ' missing view', ['view_path' => $viewPath]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('loanOption'));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'loan_option_id' => $loanOption->id ?? null]);
    }

    public function update(Request $request, LoanOption $loanOption): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $loanOption, $action, $method, $class, $base) {
            Log::debug($class . '::' . $action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'loan_option_id' => $loanOption->id ?? null, 'input' => $request->only('name')]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($request, 'edit loan option', VW::LN_OPT . '.index')) !== true) return $r;
            if (!self::isOwner($loanOption)) {
                Log::warning($class . '::' . $action . ' forbidden', ['loan_option_id' => $loanOption->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
            }
            $valStart = microtime(true);
            $request->validate(['name' => 'required|string|max:20']);
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $loanOption, $class, $action) {
                    $updStart = microtime(true);
                    $loanOption->update(['name' => $request->name]);
                    $this->logExecutionTime($updStart, $action, 'updateLoanOption');
                    Log::info($class . '::' . $action . ' updated', ['loan_option_id' => $loanOption->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(VW::LN_OPT . '.index')->with('success', __('Loan option successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($class . '::' . $action . ' failed', ['error' => $e->getMessage(), 'loan_option_id' => $loanOption->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'loan_option_id' => $loanOption->id ?? null]);
    }

    public function destroy(LoanOption $loanOption, Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($loanOption, $request, $action, $method, $class, $base) {
            Log::debug($class . '::' . $action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'loan_option_id' => $loanOption->id ?? null]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($request, 'delete loan option', VW::LN_OPT . '.index')) !== true) return $r;
            if (!self::isOwner($loanOption)) {
                Log::warning($class . '::' . $action . ' forbidden', ['loan_option_id' => $loanOption->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($loanOption, $request, $class, $action) {
                    $delStart = microtime(true);
                    $loanOption->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteLoanOption');
                    Log::info($class . '::' . $action . ' deleted', ['loan_option_id' => $loanOption->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(VW::LN_OPT . '.index')->with('success', __('Loan option successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error($class . '::' . $action . ' failed', ['error' => $e->getMessage(), 'loan_option_id' => $loanOption->id ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'loan_option_id' => $loanOption->id ?? null]);
    }

    public function show(): RedirectResponse
    {
        return redirect()->route(VW::LN_OPT . '.index');
    }

    private static function deny(Request $request, string $permission): RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
            UsersConstants::COL_USER_ID    => $request->user()->id,
            'permission' => $permission,
        ]);
        if (!$request->user()->can($permission)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [
                UsersConstants::COL_USER_ID    => $request->user()->id,
                'permission' => $permission,
            ]);
            return defaultPermissionDenial(
                $request,
                new \Illuminate\Auth\Access\AuthorizationException($permission),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' granted', [
            UsersConstants::COL_USER_ID    => $request->user()->id,
            'permission' => $permission,
        ]);
        return null;
    }

    private static function isOwner(LoanOption $opt): bool|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $opt[DatabaseConstants::TABLE_CREATOR] === $user?->creatorId();
    }
}
