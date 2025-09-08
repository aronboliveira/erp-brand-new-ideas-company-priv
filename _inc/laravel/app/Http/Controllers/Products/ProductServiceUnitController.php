<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{ProductService, ProductServiceUnit};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Log,
    Validator
};

class ProductServiceUnitController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const PERM_MANAGE = PermissionsConstants::MNG_CT_UNT;
    private const PERM_CREATE = 'create constant unit';
    private const PERM_EDIT  = 'edit constant unit';
    private const PERM_DELETE = 'delete constant unit';
    private const REDIRECT_INDEX = '/';

    public function index(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV_UNT . '.' . $fn;

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) return $resp;
        try {
            return $this->measureProfile($action, function () use ($userOrRedirect, $view) {
                $units = ProductServiceUnit::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())->get();
                return view($view, compact('units'));
            });
        } catch (\Throwable $e) {
            Log::error($action . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, $action);
        }
    }

    public function create(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV_UNT . '.' . $fn;

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) return $resp;
        return $this->measureProfile($action, fn() => view($view));
    }

    public function store(Request $req): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) return $resp;
        $v = Validator::make($req->all(), ['name' => 'required|max:20']);
        if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
        try {
            return $this->measureProfile($action, function () use ($req, $userOrRedirect) {
                ProductServiceUnit::create([
                    'name'       => $req->input('name'),
                    DatabaseConstants::TABLE_CREATOR => $userOrRedirect->creatorId()
                ]);
                return redirect()->route(ViewsConstants::PRD_SV_UNT . '.index')->with('success', __('Unit successfully created.'));
            });
        } catch (\Throwable $e) {
            Log::error($action . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, $action);
        }
    }

    public function show(Request $req, ProductServiceUnit $unit)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV_UNT . '.' . $fn;

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) return $resp;
        return $this->measureProfile($action, fn() => view($view, compact('unit')));
    }

    public function edit(Request $req, ProductServiceUnit $unit)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV_UNT . '.' . $fn;

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) return $resp;
        return $this->measureProfile($action, fn() => view($view, compact('unit')));
    }

    public function update(Request $req, ProductServiceUnit $unit): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) return $resp;
        $v = Validator::make($req->all(), ['name' => 'required|max:20']);
        if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
        try {
            return $this->measureProfile($action, function () use ($req, $unit) {
                $unit->update(['name' => $req->input('name')]);
                return redirect()->route(ViewsConstants::PRD_SV_UNT . '.index')->with('success', __('Unit successfully updated.'));
            });
        } catch (\Throwable $e) {
            Log::error($action . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, $action);
        }
    }

    public function destroy(Request $req, ProductServiceUnit $unit): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($resp = self::guard($req, self::PERM_DELETE, self::REDIRECT_INDEX)) return $resp;
        if ($unit->created_by !== $userOrRedirect->creatorId())
            return defaultPermissionDenial($req, new AuthorizationException(), $action);
        try {
            return $this->measureProfile($action, function () use ($unit) {
                if (ProductService::where('unit_id', $unit->id)->exists())
                    return redirect()->back()->with('error', __('This unit is already assigned; please reassign or remove related data.'));
                $unit->delete();
                return redirect()->route(ViewsConstants::PRD_SV_UNT . '.index')->with('success', __('Unit successfully deleted.'));
            });
        } catch (\Throwable $e) {
            Log::error($action . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, $action);
        }
    }
}
