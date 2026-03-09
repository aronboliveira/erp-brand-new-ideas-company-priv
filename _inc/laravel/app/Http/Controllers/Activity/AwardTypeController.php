<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, UsersConstants};
use App\Models\AwardType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Route, Validator, View as ViewFacade};
use Illuminate\Support\Str;

class AwardTypeController extends Controller
{

  public function index(Request $request): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    $viewPath = Str::snake(Str::replaceLast('Controller', '', class_basename($class))) . '.index';
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id]);
        self::_setAuth($req, 'manage award type');
        $loadStart = microtime(true);
        $awardTypes = AwardType::query()->where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId())->get();
        $this->logExecutionTime($loadStart, $action, 'loadAwardTypes');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, ['awardTypes' => $awardTypes]);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    $viewPath = Str::snake(Str::replaceLast('Controller', '', class_basename($class))) . '.create';
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id]);
        self::_setAuth($req, 'create award type');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $request): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      try {
        self::_setAuth($req, 'create award type');
        $valStart = microtime(true);
        $validator = Validator::make($req->all(), ['name' => 'required|max:20']);
        if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
        $this->logExecutionTime($valStart, $action, 'validateStore');
        $awardType = new AwardType();
        $awardType->name = $req->input('name');
        $awardType->created_by = $req->user()->creatorId();
        $saveStart = microtime(true);
        $awardType->save();
        $this->logExecutionTime($saveStart, $action, 'saveAwardType');
        $resource = Str::snake(Str::replaceLast('Controller', '', class_basename($class)));
        return redirect()->route($resource . '.index')->with('success', __(':resource successfully created.', ['resource' => Str::of($resource)->replace('_', ' ')->title()]));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $request, AwardType $awardType): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      try {
        $resource = Str::snake(Str::replaceLast('Controller', '', class_basename($class)));
        return redirect()->route($resource . '.index');
      } catch (\Throwable $e) {
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'award_type_id' => $awardType->id]);
  }

  public function edit(Request $request, AwardType $awardType): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    $viewPath = Str::snake(Str::replaceLast('Controller', '', class_basename($class))) . '.edit';
    return $this->measureProfile($action, function () use ($req, $awardType, $action, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", ['award_type_id' => $awardType->id, UsersConstants::COL_USER_ID => $req->user()->id]);
        self::_setAuth($req, 'edit award type');
        if ($awardType->created_by !== $req->user()->creatorId()) throw new AuthorizationException;
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, ['awardType' => $awardType]);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'award_type_id' => $awardType->id]);
  }

  public function update(Request $request, AwardType $awardType): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $awardType, $action, $class) {
      try {
        self::_setAuth($req, 'edit award type');
        if ($awardType->created_by !== $req->user()->creatorId()) throw new AuthorizationException;
        $valStart = microtime(true);
        $validator = Validator::make($req->all(), ['name' => 'required|max:20']);
        if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
        $this->logExecutionTime($valStart, $action, 'validateUpdate');
        $awardType->name = $req->input('name');
        $saveStart = microtime(true);
        $awardType->save();
        $this->logExecutionTime($saveStart, $action, 'saveAwardType');
        $resource = Str::snake(Str::replaceLast('Controller', '', class_basename($class)));
        return redirect()->route($resource . '.index')->with('success', __(':resource successfully updated.', ['resource' => Str::of($resource)->replace('_', ' ')->title()]));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'award_type_id' => $awardType->id]);
  }

  public function destroy(Request $request, AwardType $awardType): mixed
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $awardType, $action, $class) {
      try {
        self::_setAuth($req, 'delete award type');
        if ($awardType->created_by !== $req->user()->creatorId()) throw new AuthorizationException;
        $delStart = microtime(true);
        $awardType->delete();
        $this->logExecutionTime($delStart, $action, 'deleteAwardType');
        $resource = Str::snake(Str::replaceLast('Controller', '', class_basename($class)));
        return redirect()->route($resource . '.index')->with('success', __(':resource successfully deleted.', ['resource' => Str::of($resource)->replace('_', ' ')->title()]));
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'award_type_id' => $awardType->id]);
  }

  protected static function _setAuth(Request $request, string $permission, string $suffix = ''): void
  {
    $perm = $permission . ($suffix ? " {$suffix}" : '');
    if (!$request->user()->can($perm)) {
      throw new AuthorizationException;
    }
  }
}
