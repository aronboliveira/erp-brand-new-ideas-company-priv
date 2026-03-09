<?php
//TODO STOPPED MEASURING HERE


namespace App\Http\Controllers;

use App\Config\Constants\ViewsConstants as VW;
use App\Models\TrainingType;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Route, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TrainingTypeController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = VW::TNG_TP . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = VW::TNG_TP . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                if (($redirect = self::guard($req, 'manage training type', self::ROUTE_INDEX)) !== true) return $redirect;
                $user = $req->user();
                $buildStart = microtime(true);
                $query = TrainingType::where('created_by', $user?->creatorId());
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $trainingtypes = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchTrainingTypes');
                Log::info("[{$base}::{$action}] fetched training types", ['user_id' => $user?->id, 'count' => $trainingtypes->count(), 'method' => $method]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['trainingtypes']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('trainingtypes'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
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
        $viewPath = VW::TNG_TP . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $base, $viewPath) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = self::guard($req, 'create training type', self::ROUTE_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] displaying create form", ['user_id' => $req->user()->id, 'method' => $method]);
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => []]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath);
            $this->logExecutionTime($renderStart, $action, 'renderCreate');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = self::guard($req, 'create training type', self::ROUTE_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['user_id' => $req->user()->id, 'input' => $req->only('name'), 'method' => $method]);
            $valStart = microtime(true);
            $req->validate(['name' => 'required|string|max:100']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action, $base) {
                    $createStart = microtime(true);
                    $tt = TrainingType::create(['name' => $req->name, 'created_by' => $req->user()->creatorId()]);
                    $this->logExecutionTime($createStart, $action, 'createTrainingType');
                    Log::info("[{$base}::{$action}] TrainingType created", ['id' => $tt->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('TrainingType successfully created.'));
            } catch (ValidationException $e) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $e->errors()]);
                Log::debug("[{$base}::{$action}] validation debug", ['route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(TrainingType $trainingType): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($trainingType, $action, $method, $base) {
            Log::info("[{$base}::{$action}] called", ['training_type_id' => $trainingType->id, 'method' => $method]);
            try {
                $redirStart = microtime(true);
                $resp = redirect()->route(self::ROUTE_INDEX)->with($trainingType);
                $this->logExecutionTime($redirStart, $action, 'redirect');
                Log::info("[{$base}::{$action}] complete", ['training_type_id' => $trainingType->id]);
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'training_type_id' => $trainingType->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return redirect()->route(self::ROUTE_INDEX)->with('error', __('Unexpected error.'));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'training_type_id' => $trainingType->id]);
    }

    public function edit(Request $request, TrainingType $trainingType): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = VW::TNG_TP . '.edit';
        return $this->measureProfile($action, function () use ($req, $trainingType, $action, $method, $class, $base, $viewPath) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = self::guard($req, 'edit training type', self::ROUTE_INDEX)) !== true) return $redirect;
            if ($trainingType->created_by !== $req->user()->creatorId()) {
                Log::warning("[{$base}::{$action}] unauthorized edit attempt", ['user_id' => $req->user()->id, 'tt_id' => $trainingType->id]);
                return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::ROUTE_INDEX));
            }
            Log::info("[{$base}::{$action}] displaying edit form", ['tt_id' => $trainingType->id, 'method' => $method]);
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['trainingType']]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('trainingType'));
            $this->logExecutionTime($renderStart, $action, 'renderEdit');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'tt_id' => $trainingType->id]);
    }

    public function update(Request $request, TrainingType $trainingType): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $trainingType, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = self::guard($req, 'edit training type', self::ROUTE_INDEX)) !== true) return $redirect;
            if ($trainingType->created_by !== $req->user()->creatorId()) {
                Log::warning("[{$base}::{$action}] unauthorized update attempt", ['user_id' => $req->user()->id, 'tt_id' => $trainingType->id]);
                return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::ROUTE_INDEX));
            }
            Log::info("[{$base}::{$action}] called", ['tt_id' => $trainingType->id, 'input' => $req->only('name'), 'method' => $method]);
            $valStart = microtime(true);
            $req->validate(['name' => 'required|string|max:100']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $trainingType, $action, $base) {
                    $updStart = microtime(true);
                    $trainingType->update(['name' => $req->name]);
                    $this->logExecutionTime($updStart, $action, 'updateTrainingType');
                    Log::info("[{$base}::{$action}] TrainingType updated", ['id' => $trainingType->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('TrainingType successfully updated.'));
            } catch (ValidationException $e) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $e->errors()]);
                Log::debug("[{$base}::{$action}] validation debug", ['route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'tt_id' => $trainingType->id]);
    }

    public function destroy(Request $request, TrainingType $trainingType): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $trainingType, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = self::guard($req, 'delete training type', self::ROUTE_INDEX)) !== true) return $redirect;
            if ($trainingType->created_by !== $req->user()->creatorId()) {
                Log::warning("[{$base}::{$action}] unauthorized delete attempt", ['user_id' => $req->user()->id, 'tt_id' => $trainingType->id]);
                return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::ROUTE_INDEX));
            }
            Log::info("[{$base}::{$action}] called", ['tt_id' => $trainingType->id, 'user_id' => $req->user()->id, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($trainingType, $action, $base) {
                    $delStart = microtime(true);
                    $trainingType->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteTrainingType');
                    Log::info("[{$base}::{$action}] TrainingType deleted", ['id' => $trainingType->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('TrainingType successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'tt_id' => $trainingType->id]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'tt_id' => $trainingType->id]);
    }
}
