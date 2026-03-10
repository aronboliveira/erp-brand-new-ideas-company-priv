<?php

namespace App\Http\Controllers\Shapes;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MiddlewaresConstants as MWC,
    PermissionsConstants as PMC,
    ViewsConstants as VW
};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\CustomField;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Log, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class CustomFieldController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_ROUTE = VW::CST_FD . '.index';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function __construct()
    {
        $this->middleware([MWC::AUTH, MWC::XSS]);
    }

    private function checkOwnership(
        Request $request,
        CustomField $customField,
        mixed $user,
        string $action
    ): ?RedirectResponse {
        $file = __FILE__;
        $creatorId = $user?->creatorId() ?? null;
        $fieldCreator = $customField->created_by ?? null;
        if (empty($creatorId) || $fieldCreator !== $creatorId) {
            Log::warning("{$action} ownership validation failed", [
                'file' => $file,
                'class' => __CLASS__,
                'custom_field_id' => $customField->id ?? null,
                'field_creator' => $fieldCreator,
                'user_creator' => $creatorId
            ]);
            return defaultPermissionDenial(
                $request,
                new \Exception('Owner mismatch'),
                $action,
                route(self::REDIRECT_ROUTE)
            );
        }
        return null;
    }

    public function index(Request $request): Response|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        $view = VW::CST_FD . '.' . $fn;
        return $this->measureProfile($action, function () use (
            $request,
            $view,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, PMC::MNG_CT_CST_FD, self::REDIRECT_ROUTE);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $creatorId = $user?->creatorId() ?? null;
                $t = microtime(true);
                $customFields = CustomField::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
                $this->logExecutionTime($t, $action . '::fetchCustomFields', 'completed');
                if (!ViewFacade::exists($view)) {
                    Log::error("{$action} View not found", [
                        'file' => $file,
                        'class' => $cls,
                        'view' => $view
                    ]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$view} not found!");
                }
                return response()->view($view, compact('customFields'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_ROUTE));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function create(Request $request): Response|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        $view = VW::CST_FD . '.' . $fn;
        return $this->measureProfile($action, function () use (
            $request,
            $view,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'create constant custom field', self::REDIRECT_ROUTE);
                if ($guard !== true)
                    return $guard;
                $t = microtime(true);
                $types = CustomField::$fieldTypes;
                $modules = CustomField::$modules;
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');
                if (!ViewFacade::exists($view)) {
                    Log::error("{$action} View not found", [
                        'file' => $file,
                        'class' => $cls,
                        'view' => $view
                    ]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$view} not found!");
                }
                return response()->view($view, compact('types', 'modules'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_ROUTE));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use (
            $request,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'create constant custom field', self::REDIRECT_ROUTE);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $t = microtime(true);
                $validator = Validator::make($request->all(), [
                    'name'   => 'required|max:40',
                    'type'   => 'required',
                    'module' => 'required',
                ]);
                $this->logExecutionTime($t, $action . '::validate', 'completed');
                if ($validator->fails())
                    return redirect()->route(self::REDIRECT_ROUTE)
                        ->with('error', $validator->errors()->first());
                $t = microtime(true);
                CustomField::create([
                    'name'   => $request->input('name'),
                    'type'   => $request->input('type'),
                    'module' => $request->input('module'),
                    DC::COL_TABLE_CREATOR => $user?->creatorId() ?? null,
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');
                return redirect()->route(self::REDIRECT_ROUTE)
                    ->with('success', __('Custom Field successfully created!'));
            } catch (ValidationException $ve) {
                $errors = $ve->errors();
                $msg = is_array($errors)
                    ? (collect($errors)->flatten()->first() ?? __('Validation failed'))
                    : __('Validation failed');
                Log::warning("{$action} Validation failed", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($ve),
                    'errors' => $errors
                ]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('error', $msg);
            } catch (\InvalidArgumentException $iae) {
                Log::error("{$action} InvalidArgumentException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($iae),
                    'message' => $iae->getMessage()
                ]);
                return defaultUndefinedException($request, $iae, $action, route(self::REDIRECT_ROUTE));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function show(Request $request, CustomField $customField): View|Response|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        $view = VW::CST_FD . '.show';
        return $this->measureProfile($action, function () use (
            $request,
            $customField,
            $view,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'view constant custom field', self::REDIRECT_ROUTE);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $ownershipError = $this->checkOwnership($request, $customField, $user, $action);
                if ($ownershipError !== null)
                    return $ownershipError;
                if (!ViewFacade::exists($view)) {
                    Log::error("{$action} View not found", [
                        'file' => $file,
                        'class' => $cls,
                        'view' => $view
                    ]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$view} not found!");
                }
                return response()->view($view, compact('customField'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_ROUTE));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function edit(Request $request, CustomField $customField): Response|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        $view = VW::CST_FD . '.edit';
        return $this->measureProfile($action, function () use (
            $request,
            $customField,
            $view,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'edit constant custom field', self::REDIRECT_ROUTE);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $ownershipError = $this->checkOwnership($request, $customField, $user, $action);
                if ($ownershipError !== null)
                    return $ownershipError;
                $t = microtime(true);
                $types = CustomField::$fieldTypes;
                $modules = CustomField::$modules;
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');
                if (!ViewFacade::exists($view)) {
                    Log::error("{$action} View not found", [
                        'file' => $file,
                        'class' => $cls,
                        'view' => $view
                    ]);
                    return redirect()->back()->with('error', "HTTP 404: Page {$view} not found!");
                }
                return response()->view($view, compact('customField', 'types', 'modules'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_ROUTE));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function update(Request $request, CustomField $customField): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use (
            $request,
            $customField,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'edit constant custom field', self::REDIRECT_ROUTE);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $ownershipError = $this->checkOwnership($request, $customField, $user, $action);
                if ($ownershipError !== null)
                    return $ownershipError;
                $t = microtime(true);
                $validator = Validator::make($request->all(), ['name' => 'required|max:40']);
                $this->logExecutionTime($t, $action . '::validate', 'completed');
                if ($validator->fails())
                    return redirect()->route(self::REDIRECT_ROUTE)
                        ->with('error', $validator->errors()->first());
                $t = microtime(true);
                $customField->update($request->only(['name']));
                $this->logExecutionTime($t, $action . '::persist', 'completed');
                return redirect()->route(self::REDIRECT_ROUTE)
                    ->with('success', __('Custom Field successfully updated!'));
            } catch (ValidationException $ve) {
                $errors = $ve->errors();
                $msg = is_array($errors)
                    ? (collect($errors)->flatten()->first() ?? __('Validation failed'))
                    : __('Validation failed');
                Log::warning("{$action} Validation failed", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($ve),
                    'errors' => $errors
                ]);
                return redirect()->route(self::REDIRECT_ROUTE)->with('error', $msg);
            } catch (\InvalidArgumentException $iae) {
                Log::error("{$action} InvalidArgumentException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($iae),
                    'message' => $iae->getMessage()
                ]);
                return defaultUndefinedException($request, $iae, $action, route(self::REDIRECT_ROUTE));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
            }
        });
    }

    public function destroy(Request $request, CustomField $customField): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use (
            $request,
            $customField,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'delete constant custom field', self::REDIRECT_ROUTE);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $ownershipError = $this->checkOwnership($request, $customField, $user, $action);
                if ($ownershipError !== null)
                    return $ownershipError;
                $t = microtime(true);
                $customField->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');
                Log::info("{$action} deleted", ['custom_field_id' => $customField->id ?? null]);
                return redirect()->route(self::REDIRECT_ROUTE)
                    ->with('success', __('Custom Field successfully deleted!'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_ROUTE));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
            }
        });
    }
}
