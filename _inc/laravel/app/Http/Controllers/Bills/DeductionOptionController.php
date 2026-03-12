<?php
//TODO STOPPED MEASURING HERE
namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants as VW,
};
use App\Http\Controllers\Controller;
use App\Models\DeductionOption;
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
final class DeductionOptionController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $req): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::DDT_OPT . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            $guard = self::guard($req, 'manage deduction option', VW::DDT_OPT . '.index');
            if ($guard !== true) return $guard;
            try {
                $uidStart = microtime(true);
                $userId = $user?->creatorId();
                $this->logExecutionTime($uidStart, $action, 'resolveCreatorId');
                $fetchStart = microtime(true);
                $opts = DeductionOption::where(DatabaseConstants::COL_TABLE_CREATOR, $userId)->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchOptions');
                Log::info("[{$base}::{$action}] fetched", ['creator_id' => $userId, 'count' => $opts->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['opts']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('opts'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $req): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::DDT_OPT . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $guard = self::guard($req, 'create deduction option', VW::DDT_OPT . '.index');
            if ($guard !== true) return $guard;
            try {
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => []]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'input_keys' => array_keys($req->all() ?? []), 'ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $guard = self::guard($req, 'create deduction option', VW::DDT_OPT . '.index');
            if ($guard !== true) return $guard;
            $valStart = microtime(true);
            if ($r = self::validateName($req)) return $r;
            $this->logExecutionTime($valStart, $action, 'validateName');
            try {
                $uidStart = microtime(true);
                $userId = $u?->creatorId();
                $this->logExecutionTime($uidStart, $action, 'resolveCreatorId');
                $createStart = microtime(true);
                $opt = DeductionOption::create(['name' => $req->name ?? null, DatabaseConstants::COL_TABLE_CREATOR => $userId]);
                $this->logExecutionTime($createStart, $action, 'createOption');
                Log::info("[{$base}::{$action}] created", ['id' => $opt->id ?? null, 'name' => $opt->name ?? null, DatabaseConstants::COL_TABLE_CREATOR => $userId]);
                return redirect()->route(VW::DDT_OPT . '.index')->with('success', __('DeductionOption successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'input_keys' => array_keys($req->all() ?? [])]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($action, $method, $base) {
            Log::info("[{$base}::{$action}] redirect", [UsersConstants::COL_USER_ID => Auth::id(), 'method' => $method]);
            $redirStart = microtime(true);
            $resp = redirect()->route(VW::DDT_OPT . '.index');
            $this->logExecutionTime($redirStart, $action, 'redirect');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $req, int|string $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::DDT_OPT . '.edit';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'id' => $id, 'ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            $guard = self::guard($req, 'edit deduction option', VW::DDT_OPT . '.index');
            if ($guard !== true) return $guard;
            try {
                $findStart = microtime(true);
                $opt = DeductionOption::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findOption');
                $ownStart = microtime(true);
                $isOwner = ($opt[DatabaseConstants::COL_TABLE_CREATOR] ?? null) === ($user?->creatorId());
                $this->logExecutionTime($ownStart, $action, 'ownerCheck');
                if (!$isOwner) {
                    Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null, 'opt_id' => $id]);
                    return defaultPermissionDenial($req, new \Illuminate\Auth\Access\AuthorizationException('owner'), $class . '::' . $action);
                }
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['opt']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                Log::info("[{$base}::{$action}] editing", ['opt_id' => $id]);
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('opt'));
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'opt_id' => $id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'id' => $id]);
    }

    public function update(Request $req, DeductionOption $deductionOption): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $deductionOption, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'opt_id' => $deductionOption->id ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            $guard = self::guard($req, 'edit deduction option', VW::DDT_OPT . '.index');
            if ($guard !== true) return $guard;
            $ownStart = microtime(true);
            $isOwner = ($deductionOption[DatabaseConstants::COL_TABLE_CREATOR] ?? null) === ($user?->creatorId());
            $this->logExecutionTime($ownStart, $action, 'ownerCheck');
            if (!$isOwner) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null, 'opt_id' => $deductionOption->id ?? null]);
                return defaultPermissionDenial($req, new \Illuminate\Auth\Access\AuthorizationException('owner'), $class . '::' . $action);
            }
            $valStart = microtime(true);
            if ($r = self::validateName($req)) return $r;
            $this->logExecutionTime($valStart, $action, 'validateName');
            try {
                $old = $deductionOption->name ?? null;
                $updStart = microtime(true);
                $deductionOption->update(['name' => $req->name ?? null]);
                $this->logExecutionTime($updStart, $action, 'updateOption');
                Log::info("[{$base}::{$action}] updated", ['opt_id' => $deductionOption->id ?? null, 'from' => $old, 'to' => $req->name ?? null]);
                return redirect()->route(VW::DDT_OPT . '.index')->with('success', __('DeductionOption successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'opt_id' => $deductionOption->id ?? null, 'input_keys' => array_keys($req->all() ?? [])]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'opt_id' => $deductionOption->id ?? null]);
    }

    public function destroy(Request $req, DeductionOption $deductionOption): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $deductionOption, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'opt_id' => $deductionOption->id ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            $guard = self::guard($req, 'delete deduction option', VW::DDT_OPT . '.index');
            if ($guard !== true) return $guard;
            $ownStart = microtime(true);
            $isOwner = ($deductionOption[DatabaseConstants::COL_TABLE_CREATOR] ?? null) === ($user?->creatorId());
            $this->logExecutionTime($ownStart, $action, 'ownerCheck');
            if (!$isOwner) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null, 'opt_id' => $deductionOption->id ?? null]);
                return defaultPermissionDenial($req, new \Illuminate\Auth\Access\AuthorizationException('owner'), $class . '::' . $action);
            }
            try {
                $delStart = microtime(true);
                $deductionOption->delete();
                $this->logExecutionTime($delStart, $action, 'deleteOption');
                Log::info("[{$base}::{$action}] deleted", ['opt_id' => $deductionOption->id ?? null]);
                return redirect()->route(VW::DDT_OPT . '.index')->with('success', __('DeductionOption successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'opt_id' => $deductionOption->id ?? null]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'opt_id' => $deductionOption->id ?? null]);
    }

    protected static function setAuth(Request $req, string $perm): RedirectResponse|null
    {
        $user = $req->user();
        if ($user?->can($perm)) {
            Log::info(__METHOD__ . ' permission granted', [
                UsersConstants::COL_USER_ID => $user?->id,
                'perm' => $perm
            ]);
            return null;
        }
        Log::warning(__METHOD__ . ' permission denied', [
            UsersConstants::COL_USER_ID => $user?->id,
            'perm' => $perm
        ]);
        return defaultPermissionDenial(
            $req,
            new \Illuminate\Auth\Access\AuthorizationException($perm),
            __METHOD__,
            route(VW::DDT_OPT . '.index')
        );
    }

    protected static function validateName(Request $req): RedirectResponse|null
    {
        $v = Validator::make($req->all(), ['name' => 'required|string']);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', [
                'errors' => $v->errors()->all()
            ]);
            return redirect()->back()
                ->with('error', $v->errors()->first());
        }
        Log::info(__METHOD__ . ' validation passed', [
            'name' => $req->name
        ]);
        return null;
    }
}
