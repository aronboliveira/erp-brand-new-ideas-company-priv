<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\CustomField;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{Response, JsonResponse, Request, RedirectResponse};
use Illuminate\Support\Facades\{Log, Validator, View as ViewFacade};
use Illuminate\View\View;

class CustomFieldController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    private const REDIRECT_ROUTE = ViewsConstants::CST_FD . '.index';

    public function index(Request $request): Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::MNG_CT_CST_FD, self::REDIRECT_ROUTE)) !== true) return $redirect;

            try {
                $t = microtime(true);
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->get();
                $this->logExecutionTime($t, $action . '::fetchCustomFields', 'completed');

                $viewPath = ViewsConstants::CST_FD . '.' . $action;
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return response()->view($viewPath, compact('customFields'));
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function create(Request $request): Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'create constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;

            try {
                $t = microtime(true);
                $types   = CustomField::$fieldTypes;
                $modules = CustomField::$modules;
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');

                $viewPath = ViewsConstants::CST_FD . '.' . $action;
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return response()->view($viewPath, compact('types', 'modules'));
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'create constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;

            try {
                $t = microtime(true);
                $validator = Validator::make($request->all(), [
                    'name'   => 'required|max:40',
                    'type'   => 'required',
                    'module' => 'required',
                ]);
                $this->logExecutionTime($t, $action . '::validate', 'completed');
                if ($validator->fails()) {
                    return redirect()->route(self::REDIRECT_ROUTE)->with('error', $validator->errors()->first());
                }

                $t = microtime(true);
                CustomField::create([
                    'name'       => $request->input('name'),
                    'type'       => $request->input('type'),
                    'module'     => $request->input('module'),
                    DatabaseConstants::COL_TABLE_CREATOR => $request->user()->creatorId(),
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Custom Field successfully created!'));
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function show(Request $request, CustomField $customField): View|Response|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $customField, $action, $method) {
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
            $user = $ur;
            if (($g = self::guard($request, 'view constant custom field', self::REDIRECT_ROUTE)) !== true) return $g;

            if ($customField->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $method, route(self::REDIRECT_ROUTE));
            }

            try {
                $viewPath = ViewsConstants::CST_FD . '.show';
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return response()->view($viewPath, compact('customField'));
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function edit(Request $request, CustomField $customField): Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $customField, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;

            if ($customField->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $method, route(self::REDIRECT_ROUTE));
            }

            try {
                $t = microtime(true);
                $types   = CustomField::$fieldTypes;
                $modules = CustomField::$modules;
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');

                $viewPath = ViewsConstants::CST_FD . '.edit';
                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                return response()->view($viewPath, compact('customField', 'types', 'modules'));
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function update(Request $request, CustomField $customField): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $customField, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;

            if ($customField->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $method, route(self::REDIRECT_ROUTE));
            }

            try {
                $t = microtime(true);
                $validator = Validator::make($request->all(), ['name' => 'required|max:40']);
                $this->logExecutionTime($t, $action . '::validate', 'completed');
                if ($validator->fails()) {
                    return redirect()->route(self::REDIRECT_ROUTE)->with('error', $validator->errors()->first());
                }

                $t = microtime(true);
                $customField->update($request->only(['name']));
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Custom Field successfully updated!'));
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function destroy(Request $request, CustomField $customField): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $customField, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'delete constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;

            if ($customField->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $method, route(self::REDIRECT_ROUTE));
            }

            try {
                $t = microtime(true);
                $customField->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Custom Field successfully deleted!'));
            } catch (\Throwable $e) {
                Log::error($method . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_ROUTE));
            }
        });
    }
}
