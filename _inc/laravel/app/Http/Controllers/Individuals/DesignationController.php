<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Department, Designation};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Validator, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
class DesignationController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|RedirectResponse
    {
        $action = 'DesignationController@index';
        $view   = ViewsConstants::DSG . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            // authorize
            $t = microtime(true);
            try {
                $this->_authorize($request, 'manage designation');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');
            } catch (AuthorizationException $e) {
                $this->logExecutionTime($t, $action . '::authorize', 'denied');
                return defaultPermissionDenial($request, $e, $action);
            }

            // query
            $t = microtime(true);
            $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
            $this->logExecutionTime($t, $action . '::query', 'rows: ' . $designations->count());

            // view check
            $t = microtime(true);
            if (!ViewFacade::exists($view)) {
                $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                return defaultUndefinedException($request, new \RuntimeException("View not found: $view"), $action);
            }
            $this->logExecutionTime($t, $action . '::viewCheck', 'exists');

            return view($view, compact('designations'));
        }, ['uri' => $request->getRequestUri()]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = 'DesignationController@create';
        $view   = ViewsConstants::DSG . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            // authorize
            $t = microtime(true);
            try {
                $this->_authorize($request, 'create designation');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');
            } catch (AuthorizationException $e) {
                $this->logExecutionTime($t, $action . '::authorize', 'denied');
                return defaultPermissionDenial($request, $e, $action);
            }

            // load form data
            $t = microtime(true);
            $departmentList = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $this->logExecutionTime($t, $action . '::loadFormData', 'deps: ' . $departmentList->count());

            // view check
            $t = microtime(true);
            if (!ViewFacade::exists($view)) {
                $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                return defaultUndefinedException($request, new \RuntimeException("View not found: $view"), $action);
            }
            $this->logExecutionTime($t, $action . '::viewCheck', 'exists');

            return view($view, compact('departmentList'));
        }, ['uri' => $request->getRequestUri()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = 'DesignationController@store';

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            // authorize
            $t = microtime(true);
            try {
                $this->_authorize($request, 'create designation');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');
            } catch (AuthorizationException $e) {
                $this->logExecutionTime($t, $action . '::authorize', 'denied');
                return defaultPermissionDenial($request, $e, $action);
            }

            // validate (use 'name' from request body; dep id from constant)
            $t = microtime(true);
            $validator = Validator::make($request->all(), [
                CompaniesConstants::COL_DEP_ID => 'required',
                'name'                         => 'required|max:20',
            ]);
            if ($validator->fails()) {
                $this->logExecutionTime($t, $action . '::validate', 'failed');
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }
            $this->logExecutionTime($t, $action . '::validate', 'ok');

            // persist
            $t = microtime(true);
            $user = $request->user();
            $designation = Designation::create([
                CompaniesConstants::COL_DEP_ID   => $request->input(CompaniesConstants::COL_DEP_ID),
                'name'                           => $request->input('name'),
                DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()
            ]);
            $this->logExecutionTime($t, $action . '::persist', 'id: ' . $designation->id);
            Log::info('Designation created', ['id' => $designation->id]);

            return redirect()->route(ViewsConstants::DSG . '.index')
                ->with('success', __('Designation successfully created.'));
        }, ['uri' => $request->getRequestUri()]);
    }

    public function show(): RedirectResponse
    {
        // simple redirect; profiling not necessary
        return redirect()->route(ViewsConstants::DSG . '.index');
    }

    public function edit(Request $request, Designation $designation): View|RedirectResponse
    {
        $action = 'DesignationController@edit';
        $view   = ViewsConstants::DSG . '.edit';

        return $this->measureProfile($action, function () use ($request, $designation, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            // authorize
            $t = microtime(true);
            try {
                $this->_authorize($request, 'edit designation');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');
            } catch (AuthorizationException $e) {
                $this->logExecutionTime($t, $action . '::authorize', 'denied');
                return defaultPermissionDenial($request, $e, $action);
            }

            // ownership check
            $t = microtime(true);
            if ($designation->created_by !== $request->user()->creatorId()) {
                $this->logExecutionTime($t, $action . '::owner', 'denied');
                return defaultPermissionDenial($request, new AuthorizationException(), $action);
            }
            $this->logExecutionTime($t, $action . '::owner', 'ok');

            // load data
            $t = microtime(true);
            $departmentList = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $this->logExecutionTime($t, $action . '::loadFormData', 'deps: ' . $departmentList->count());

            // view check
            $t = microtime(true);
            if (!ViewFacade::exists($view)) {
                $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                return defaultUndefinedException($request, new \RuntimeException("View not found: $view"), $action);
            }
            $this->logExecutionTime($t, $action . '::viewCheck', 'exists');

            return view($view, compact('designation', 'departmentList'));
        }, ['uri' => $request->getRequestUri(), 'id' => $designation->id]);
    }

    public function update(Request $request, Designation $designation): RedirectResponse
    {
        $action = 'DesignationController@update';

        return $this->measureProfile($action, function () use ($request, $designation, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            // authorize
            $t = microtime(true);
            try {
                $this->_authorize($request, 'edit designation');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');
            } catch (AuthorizationException $e) {
                $this->logExecutionTime($t, $action . '::authorize', 'denied');
                return defaultPermissionDenial($request, $e, $action);
            }

            // ownership check
            $t = microtime(true);
            if ($designation->created_by !== $request->user()->creatorId()) {
                $this->logExecutionTime($t, $action . '::owner', 'denied');
                return defaultPermissionDenial($request, new AuthorizationException(), $action);
            }
            $this->logExecutionTime($t, $action . '::owner', 'ok');

            // validate
            $t = microtime(true);
            $validator = Validator::make($request->all(), [
                CompaniesConstants::COL_DEP_ID => 'required',
                'name'                         => 'required|max:20'
            ]);
            if ($validator->fails()) {
                $this->logExecutionTime($t, $action . '::validate', 'failed');
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }
            $this->logExecutionTime($t, $action . '::validate', 'ok');

            // persist
            $t = microtime(true);
            $designation->update([
                CompaniesConstants::COL_DEP_ID => $request->input(CompaniesConstants::COL_DEP_ID),
                'name'                         => $request->input('name')
            ]);
            $this->logExecutionTime($t, $action . '::persist', 'id: ' . $designation->id);

            return redirect()->route(ViewsConstants::DSG . '.index')
                ->with('success', __('Designation successfully updated.'));
        }, ['uri' => $request->getRequestUri(), 'id' => $designation->id]);
    }

    public function destroy(Request $request, Designation $designation): RedirectResponse
    {
        $action = 'DesignationController@destroy';

        return $this->measureProfile($action, function () use ($request, $designation, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            // authorize
            $t = microtime(true);
            try {
                $this->_authorize($request, 'delete designation');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');
            } catch (AuthorizationException $e) {
                $this->logExecutionTime($t, $action . '::authorize', 'denied');
                return defaultPermissionDenial($request, $e, $action);
            }

            // ownership check
            $t = microtime(true);
            if ($designation->created_by !== $request->user()->creatorId()) {
                $this->logExecutionTime($t, $action . '::owner', 'denied');
                return defaultPermissionDenial($request, new AuthorizationException(), $action);
            }
            $this->logExecutionTime($t, $action . '::owner', 'ok');

            // delete
            $t = microtime(true);
            $designation->delete();
            $this->logExecutionTime($t, $action . '::delete', 'id: ' . $designation->id);

            return redirect()->route(ViewsConstants::DSG . '.index')
                ->with('success', __('Designation successfully deleted.'));
        }, ['uri' => $request->getRequestUri(), 'id' => $designation->id]);
    }

    /**
     * Authorize the current user for a given ability.
     *
     * @throws AuthorizationException
     */
    private function _authorize(Request $request, string $ability): void
    {
        $result = self::guard($request, $ability);
        if ($result !== true) {
            throw new AuthorizationException("Unauthorized: {$ability}");
        }
    }
}
