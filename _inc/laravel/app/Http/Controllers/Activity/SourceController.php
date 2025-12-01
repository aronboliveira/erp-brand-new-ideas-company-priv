<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\Source;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{DB, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;

class SourceController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(
            [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
            ]
        );
    }

    private const REDIRECT_INDEX = ViewsConstants::SRC . '.index';

    // List all sources.
    public function index(Request $request): RedirectResponse|View|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::SRC . '.index';
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::MNG_SRC, self::REDIRECT_INDEX)) !== true) return $redirect;
            try {
                $creatorId = $request->user()->creatorId();
                $fetchStart = microtime(true);
                Log::info("[{$class}::{$action}] start", ['user_id' => $request->user()->id, 'creator_id' => $creatorId, 'method' => $method]);
                Log::debug("[{$class}::{$action}] fetching sources", ['creator_id' => $creatorId]);
                $sources = Source::where('created_by', $creatorId)->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchSources');
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'count' => is_countable($sources) ? count($sources) : null]);
                return view($viewPath, compact('sources'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function create(Request $request): View|RedirectResponse|string
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::SRC . '.create';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'create source', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$class}::{$action}] start", ['user_id' => $req->user()->id, 'method' => $method]);
            try {
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                Log::info("[{$class}::{$action}] complete", ['view_path' => $viewPath]);
                return $resp;
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'user_id' => $req->user()->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function show(Request $request, Source $source): View|RedirectResponse|string
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::SRC . '.show';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $source, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'view source', self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($source[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
            Log::info("[{$class}::{$action}] start", ['source_id' => $source->id, 'user_id' => $req->user()->id, 'method' => $method]);
            try {
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('source'));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                Log::info("[{$class}::{$action}] complete", ['source_id' => $source->id]);
                return $resp;
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'source_id' => $source->id, 'user_id' => $req->user()->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'source_id' => $source->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'create source', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$class}::{$action}] start", ['user_id' => $req->user()->id, 'method' => $method]);
            $rules = ['name' => 'required|string|max:20'];
            $valStart = microtime(true);
            $v = Validator::make($req->all(), $rules);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($v->fails()) {
                Log::warning("[{$class}::{$action}] validation failed", $v->errors()->toArray());
                return redirect()->route(self::REDIRECT_INDEX)->with('error', $v->errors()->first());
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action, $class) {
                    $createStart = microtime(true);
                    $source = Source::create(['name' => $req->input('name'), 'created_by' => $req->user()->creatorId()]);
                    $this->logExecutionTime($createStart, $action, 'createSource');
                    Log::info("[{$class}::{$action}] success", ['source_id' => $source->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Source successfully created!'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function edit(Request $request, Source $source): View|RedirectResponse|string
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = ViewsConstants::SRC . '.edit';
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $source, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'edit source', self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($source[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
            Log::info("[{$class}::{$action}] start", ['source_id' => $source->id, 'user_id' => $req->user()->id, 'method' => $method]);
            try {
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('source'));
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                Log::info("[{$class}::{$action}] complete", ['source_id' => $source->id]);
                return $resp;
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] error context", ['route' => Route::getCurrentRoute()?->getName(), 'source_id' => $source->id, 'user_id' => $req->user()->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'source_id' => $source->id]);
    }

    public function update(Request $request, Source $source): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $source, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'edit source', self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($source[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
            $rules = ['name' => 'required|string|max:20'];
            $valStart = microtime(true);
            $v = Validator::make($req->all(), $rules);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($v->fails()) {
                Log::warning("[{$class}::{$action}] validation failed", $v->errors()->toArray());
                return redirect()->route(self::REDIRECT_INDEX)->with('error', $v->errors()->first());
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $source, $action) {
                    $updStart = microtime(true);
                    $source->update(['name' => $req->input('name')]);
                    $this->logExecutionTime($updStart, $action, 'updateSource');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$class}::{$action}] success", ['source_id' => $source->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Source successfully updated!'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'source_id' => $source->id]);
    }

    public function destroy(Request $request, Source $source): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $source, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($req, 'delete source', self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($source[DatabaseConstants::COL_TABLE_CREATOR] !== $req->user()->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::REDIRECT_INDEX));
            try {
                Log::info("[{$class}::{$action}] start", ['source_id' => $source->id, 'user_id' => $req->user()->id, 'method' => $method]);
                $txnStart = microtime(true);
                DB::transaction(function () use ($source, $action) {
                    $delStart = microtime(true);
                    $source->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteSource');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$class}::{$action}] deleted", ['source_id' => $source->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Source successfully deleted!'));
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'source_id' => $source->id]);
    }
}
