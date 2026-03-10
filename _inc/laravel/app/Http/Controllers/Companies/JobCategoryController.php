<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\JobCategory;
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class JobCategoryController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($c = self::guard($request, 'manage job category', ViewsConstants::JB_CAT . '.index')) return $c;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $user->id, 'method' => $method]);
            try {
                $qStart = microtime(true);
                $categories = JobCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->get();
                $this->logExecutionTime($qStart, $action, 'fetchCategories');
                Log::debug("[{$base}::{$action}] fetched", ['count' => $categories->count()]);
                $viewPath = ViewsConstants::JB_CAT . '.' . $action;
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('categories'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception", ['type' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if ($c = self::guard($request, 'create job category', ViewsConstants::JB_CAT . '.index')) return $c;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $request->user()?->id, 'method' => $method]);
            try {
                $viewPath = ViewsConstants::JB_CAT . '.' . $action;
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath);
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception", ['type' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $request, JobCategory $jobCategory): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $jobCategory, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if ($c = self::guard($request, 'view job category', ViewsConstants::JB_CAT . '.index')) return $c;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $request->user()?->id, 'category_id' => $jobCategory->id, 'method' => $method]);
            try {
                $viewPath = ViewsConstants::JB_CAT . '.' . $action;
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('jobCategory'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception", ['type' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action, route(ViewsConstants::JB_CAT . '.index'));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'category_id' => $jobCategory->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if ($c = self::guard($request, 'create job category', ViewsConstants::JB_CAT . '.index')) return $c;
            $v = Validator::make($request->all(), ['title' => 'required']);
            if ($v->fails()) {
                Log::debug("[{$base}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->getMessageBag()->first());
            }
            try {
                $crtStart = microtime(true);
                $category = JobCategory::create([
                    'title' => $request->title,
                    DatabaseConstants::COL_TABLE_CREATOR => $request->user()->creatorId()
                ]);
                $this->logExecutionTime($crtStart, $action, 'createJobCategory');
                Log::info("[{$base}::{$action}] created", ['category_id' => $category->id]);
                return redirect()->route(ViewsConstants::JB_CAT . '.index')->with('success', __('Job category successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception", ['type' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $request, string $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if ($c = self::guard($request, 'edit job category', ViewsConstants::JB_CAT . '.index')) return $c;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $request->user()?->id, 'category_id' => $id, 'method' => $method]);
            try {
                $qStart = microtime(true);
                $jobCategory = JobCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->findOrFail($id);
                $this->logExecutionTime($qStart, $action, 'fetchCategory');
                $viewPath = ViewsConstants::JB_CAT . '.' . $action;
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('jobCategory'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'category_id' => $id]);
                Log::debug("[{$base}::{$action}] exception", ['type' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'category_id' => $id]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $id, $action, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if ($c = self::guard($request, 'edit job category', ViewsConstants::JB_CAT . '.index')) return $c;
            $v = Validator::make($request->all(), ['title' => 'required']);
            if ($v->fails()) {
                Log::debug("[{$base}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->getMessageBag()->first());
            }
            try {
                $qStart = microtime(true);
                $jobCategory = JobCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->findOrFail($id);
                $this->logExecutionTime($qStart, $action, 'fetchCategory');
                $updStart = microtime(true);
                $jobCategory->title = $request->title;
                $jobCategory->save();
                $this->logExecutionTime($updStart, $action, 'updateCategory');
                Log::info("[{$base}::{$action}] updated", ['category_id' => $id]);
                return redirect()->route(ViewsConstants::JB_CAT . '.index')->with('success', __('Job category successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'category_id' => $id]);
                Log::debug("[{$base}::{$action}] exception", ['type' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'category_id' => $id]);
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if ($c = self::guard($request, 'delete job category', ViewsConstants::JB_CAT . '.index')) return $c;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $request->user()?->id, 'category_id' => $id, 'method' => $method]);
            try {
                $jobCategory = JobCategory::findOrFail($id);
                if ($jobCategory->created_by !== $request->user()->creatorId()) return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action, route(ViewsConstants::JB_CAT . '.index'));
                $delStart = microtime(true);
                $jobCategory->delete();
                $this->logExecutionTime($delStart, $action, 'deleteCategory');
                Log::info("[{$base}::{$action}] deleted", ['category_id' => $id]);
                return redirect()->route(ViewsConstants::JB_CAT . '.index')->with('success', __('Job category successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'category_id' => $id]);
                Log::debug("[{$base}::{$action}] exception", ['type' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'category_id' => $id]);
    }

    private function _authorize(Request $request, string $ability): RedirectResponse|null
    {
        if (!$request->user()->can($ability)) {
            Log::warning(__METHOD__ . ' permission denied', [
                UsersConstants::COL_USER_ID => Auth::id(),
                'ability' => $ability
            ]);
            return defaultPermissionDenial(
                $request,
                null,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::JB_CAT . '.index')
            );
        }
        return null;
    }
}
