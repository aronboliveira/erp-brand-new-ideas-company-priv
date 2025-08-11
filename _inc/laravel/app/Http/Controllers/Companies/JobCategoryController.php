<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\JobCategory;
use App\Traits\ChecksLogin;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log, Validator};
use Illuminate\View\View;

class JobCategoryController extends Controller
{
    use ChecksLogin;

    public function index(Request $request): View|RedirectResponse
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id()]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = $this->_authorize($request, 'manage job category'))
                return $resp;
            $categories = JobCategory::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            Log::info(__METHOD__ . ' retrieved', ['count' => $categories->count()]);
            return view(ViewsConstants::JB_CAT . '.' . __FUNCTION__, compact('categories'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id()]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            if ($resp = $this->_authorize($request, 'create job category'))
                return $resp;
            return view(ViewsConstants::JB_CAT . '.' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Request $request, JobCategory $jobCategory): View|RedirectResponse
    {
        Log::info(__METHOD__, [
            UsersConstants::COL_USER_ID     => Auth::id(),
            'category_id' => $jobCategory->id
        ]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            if ($resp = $this->_authorize($request, 'view job category'))
                return $resp;
            return view(ViewsConstants::JB_CAT . '.' . __FUNCTION__, compact('jobCategory'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::JB_CAT . '.index')
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id()]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            if ($resp = $this->_authorize($request, 'create job category'))
                return $resp;
            $v = Validator::make($request->all(), ['title' => 'required']);
            if ($v->fails()) {
                Log::warning(__METHOD__ . ' validation failed', [
                    'errors' => $v->errors()->all()
                ]);
                return redirect()->back()
                    ->with('error', $v->getMessageBag()->first());
            }
            $category = JobCategory::create([
                'title'      => $request->title,
                DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId()
            ]);
            Log::info(__METHOD__ . ' created', [
                'category_id' => $category->id
            ]);
            return redirect()->route(ViewsConstants::JB_CAT . '.index')
                ->with('success', __('Job category successfully created.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning(__METHOD__ . ' auth failed', ['error' => $e->getMessage()]);
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::JB_CAT . '.index')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(Request $request, string $id): View|RedirectResponse
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id(), 'category_id' => $id]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            if ($resp = $this->_authorize($request, 'edit job category'))
                return $resp;
            $jobCategory = JobCategory::findOrFail($id);
            Log::info(__METHOD__ . ' retrieved', ['category_id' => $id]);
            return view(ViewsConstants::JB_CAT . '.' . __FUNCTION__, compact('jobCategory'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning(__METHOD__ . ' auth failed', ['error' => $e->getMessage()]);
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::JB_CAT . '.index')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id(), 'category_id' => $id]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            if ($resp = $this->_authorize($request, 'edit job category'))
                return $resp;
            $v = Validator::make($request->all(), ['title' => 'required']);
            if ($v->fails()) {
                Log::warning(__METHOD__ . ' validation failed', [
                    'errors' => $v->errors()->all()
                ]);
                return redirect()->back()
                    ->with('error', $v->getMessageBag()->first());
            }
            $jobCategory = JobCategory::findOrFail($id);
            $jobCategory->title = $request->title;
            $jobCategory->save();
            Log::info(__METHOD__ . ' updated', ['category_id' => $id]);
            return redirect()->route(ViewsConstants::JB_CAT . '.index')
                ->with('success', __('Job category successfully updated.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning(__METHOD__ . ' auth failed', ['error' => $e->getMessage()]);
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::JB_CAT . '.index')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id(), 'category_id' => $id]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            if ($resp = $this->_authorize($request, 'delete job category'))
                return $resp;
            $jobCategory = JobCategory::findOrFail($id);
            if ($jobCategory->created_by !== $request->user()->creatorId()) {
                Log::warning(__METHOD__ . ' forbidden', ['category_id' => $id]);
                throw new \Illuminate\Auth\Access\AuthorizationException;
            }
            $jobCategory->delete();
            Log::info(__METHOD__ . ' deleted', ['category_id' => $id]);
            return redirect()->route(ViewsConstants::JB_CAT . '.index')
                ->with('success', __('Job category successfully deleted.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::JB_CAT . '.index')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    private function _authorize(Request $request, string $ability): RedirectResponse|null
    {
        if (!$request->user()->can($ability)) {
            Log::warning(__METHOD__ . ' permission denied', [
                UsersConstants::COL_USER_ID => Auth::id(), 'ability' => $ability
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
