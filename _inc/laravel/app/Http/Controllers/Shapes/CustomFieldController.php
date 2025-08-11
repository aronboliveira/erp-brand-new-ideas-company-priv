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
use Illuminate\Http\{JsonResponse, Request, RedirectResponse};
use Illuminate\Support\Facades\{Log, Validator};
use Illuminate\View\View;

class CustomFieldController extends Controller
{
    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    use ChecksLogin;
    use ChecksPermissions;

    private const REDIRECT_ROUTE = ViewsConstants::CST_FD . '.index';

    public function index(Request $request): RedirectResponse|JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $request->user()->can(PermissionsConstants::MNG_CT_CST_FD)
            ?: defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException, __METHOD__, route(self::REDIRECT_ROUTE));
        $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get();
        return response()->view(ViewsConstants::CST_FD . '.' . __FUNCTION__, compact('customFields'));
    }

    public function create(Request $request): RedirectResponse|JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if (($redirect = self::guard($request, 'create constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;
        $types  = CustomField::$fieldTypes;
        $modules = CustomField::$modules;
        return response()->view(ViewsConstants::CST_FD . '.' . __FUNCTION__, compact('types', 'modules'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if (($redirect = self::guard($request, 'create constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;
        $rules = ['name' => 'required|max:40', 'type' => 'required', 'module' => 'required'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return redirect()->route(self::REDIRECT_ROUTE)
            ->with('error', $validator->errors()->first());
        try {
            CustomField::create([
                'name'       => $request->input('name'),
                'type'       => $request->input('type'),
                'module'     => $request->input('module'),
                DatabaseConstants::TABLE_CREATOR => $request->user()->creatorId(),
            ]);
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Custom Field successfully created!'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
        }
    }

    public function show(Request $request, CustomField $customField): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
        $user = $ur;
        if ($g = self::guard($request, 'view constant custom field', self::REDIRECT_ROUTE)) {
            Log::warning("$action permission denied", ['user_id' => $user?->id]);
            return $g;
        }
        if ($customField->created_by !== $user?->creatorId()) {
            Log::warning("$action ownership mismatch", [
                'user_id' => $user?->id,
                'fieldId' => $customField->id
            ]);
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_ROUTE)
            );
        }
        Log::info("$action started", [
            'user_id'   => $user?->id,
            'fieldId'  => $customField->id,
            'name'     => $customField->name
        ]);
        try {
            Log::info("$action succeeded", ['user_id' => $user?->id]);
            return response()->view(
                ViewsConstants::CST_FD . '.show',
                compact('customField')
            );
        } catch (\Throwable $e) {
            Log::error("$action error", [
                'user_id' => $user?->id,
                'error'  => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_ROUTE)
            );
        }
    }

    public function edit(Request $request, CustomField $customField): RedirectResponse|JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if (($redirect = self::guard($request, 'edit constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;
        if ($customField->created_by !== $request->user()->creatorId())
            return defaultPermissionDenial($request, new \Exception('owner'), __METHOD__, route(self::REDIRECT_ROUTE));
        $types  = CustomField::$fieldTypes;
        $modules = CustomField::$modules;
        return response()->view(ViewsConstants::CST_FD . '.edit', compact('customField', 'types', 'modules'));
    }

    public function update(Request $request, CustomField $customField): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if (($redirect = self::guard($request, 'edit constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;
        if ($customField->created_by !== $request->user()->creatorId())
            return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_ROUTE));
        $validator = Validator::make($request->all(), ['name' => 'required|max:40']);
        if ($validator->fails()) return redirect()->route(self::REDIRECT_ROUTE)
            ->with('error', $validator->errors()->first());
        try {
            $customField->update($request->only(['name']));
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Custom Field successfully updated!'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
        }
    }

    public function destroy(Request $request, CustomField $customField): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if (($redirect = self::guard($request, 'delete constant custom field', self::REDIRECT_ROUTE)) !== true) return $redirect;
        if ($customField->created_by !== $request->user()->creatorId())
            return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_ROUTE));
        try {
            $customField->delete();
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Custom Field successfully deleted!'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_ROUTE));
        }
    }
}
