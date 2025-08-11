<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Department, Designation};
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Validator};
use Illuminate\View\View;

class DesignationController extends Controller
{
    use ChecksLogin;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $this->_authorize($request, 'manage designation');
            $designations = Designation::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(ViewsConstants::DSG . '.' . __FUNCTION__, compact('designations'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->_authorize($request, 'create designation');
            $departmentList = Department::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            return view(ViewsConstants::DSG . '.' . __FUNCTION__, compact('departmentList'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->_authorize($request, 'create designation');
            $validator = Validator::make($request->all(), [
                CompaniesConstants::COL_DEP_ID => 'required',
                CompaniesConstants::COL_DEP_NM => 'required|max:20'
            ]);
            if ($validator->fails()) return redirect()->back()
                ->with('error', $validator->getMessageBag()->first());
            $user = $request->user();
            $designation = Designation::create([
                CompaniesConstants::COL_DEP_ID => $request->input(CompaniesConstants::COL_DEP_ID),
                CompaniesConstants::COL_DEP_NM => $request->input('name'),
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
            ]);
            Log::info('Designation created', $designation->id);
            return redirect()->route(ViewsConstants::DSG . '.index')
                ->with('success', __('Designation successfully created.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(): RedirectResponse
    {
        return redirect()->route(ViewsConstants::DSG . '.index');
    }

    public function edit(Request $request, Designation $designation): View|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->_authorize($request, 'edit designation');
            if ($designation->created_by !== $request->user()->creatorId()) throw new AuthorizationException;
            $departmentList = Department::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            return view(ViewsConstants::DSG . '.' . __FUNCTION__, compact('designation', 'departmentList'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $request, Designation $designation): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->_authorize($request, 'edit designation');
            if ($designation->created_by !== $request->user()->creatorId()) throw new AuthorizationException;
            $validator = Validator::make($request->all(), [
                CompaniesConstants::COL_DEP_ID => 'required',
                'name' => 'required|max:20'
            ]);
            if ($validator->fails()) return redirect()->back()
                ->with('error', $validator->getMessageBag()->first());
            $designation->update([
                CompaniesConstants::COL_DEP_ID => $request->input(CompaniesConstants::COL_DEP_ID),
                'name' => $request->input('name')
            ]);
            return redirect()->route(ViewsConstants::DSG . '.index')
                ->with('success', __('Designation successfully updated.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $request, Designation $designation): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->_authorize($request, 'delete designation');
            if ($designation->created_by !== $request->user()->creatorId()) throw new AuthorizationException;
            $designation->delete();
            return redirect()->route(ViewsConstants::DSG . '.index')
                ->with('success', __('Designation successfully deleted.'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }
}
