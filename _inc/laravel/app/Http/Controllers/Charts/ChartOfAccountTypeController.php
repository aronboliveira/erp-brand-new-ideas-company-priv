<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ChartsConstants,
    DatabaseConstants,
    PermissionsConstants
};
use App\Models\ChartOfAccountType;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    Validator
};

final class ChartOfAccountTypeController extends Controller
{

    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'chart-of-account-type';
    private const REDIRECT_INDEX = self::SINGULAR . '.index';

    public function index(Request $req): \Illuminate\View\View|RedirectResponse
    {
        if (
            ($uor = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $uor;
        $user = $uor;
        if ($c = self::guard($req, PermissionsConstants::MNG_COA_TYPE, self::REDIRECT_INDEX)) return $c;
        try {
            $types = ChartOfAccountType::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(self::SINGULAR . __FUNCTION__, compact('types'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $req): \Illuminate\View\View|RedirectResponse
    {
        if ($c = self::guard($req, PermissionsConstants::CR_COA_TYPE, self::REDIRECT_INDEX)) return $c;
        return view(self::SINGULAR . '.' . __FUNCTION__);
    }

    public function store(Request $req): RedirectResponse|JsonResponse
    {
        if ($c = self::guard($req, PermissionsConstants::CR_COA_TYPE, self::REDIRECT_INDEX)) return $c;
        if ($c = self::v($req, [ChartsConstants::COL_NM => 'required'])) return $c;
        try {
            if (
                ($uor = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return $uor;
            $user = $uor;
            ChartOfAccountType::create([
                ChartsConstants::COL_NM       => $req->name,
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);
            return redirect()->route(self::SINGULAR . '.index')
                ->with('success', __('Chart of account type successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(
        Request $req,
        ChartOfAccountType $chartOfAccountType
    ): RedirectResponse {
        if ($c = self::guard($req, PermissionsConstants::MNG_COA_TYPE, self::REDIRECT_INDEX)) return $c;
        return redirect()->route(self::SINGULAR . '.index');
    }

    public function edit(
        Request $req,
        ChartOfAccountType $chartOfAccountType
    ): \Illuminate\View\View|RedirectResponse {
        if ($c = self::guard($req, 'edit constant chart of account type', self::REDIRECT_INDEX)) return $c;
        return view(self::SINGULAR . '.' . __FUNCTION__, compact('chartOfAccountType'));
    }

    public function update(
        Request $req,
        ChartOfAccountType $chartOfAccountType
    ): RedirectResponse|JsonResponse {
        if ($c = self::guard($req, 'edit constant chart of account type', self::REDIRECT_INDEX)) return $c;
        if ($c = self::v($req, [ChartsConstants::COL_NM => 'required'])) return $c;
        try {
            $chartOfAccountType->update([ChartsConstants::COL_NM => $req->name]);
            return redirect()->route(self::SINGULAR . '.index')
                ->with('success', __('Chart of account type successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(
        Request $req,
        ChartOfAccountType $chartOfAccountType
    ): RedirectResponse|JsonResponse {
        if ($c = self::guard($req, 'delete constant chart of account type', self::REDIRECT_INDEX)) return $c;
        try {
            $chartOfAccountType->delete();
            return redirect()->route(self::SINGULAR . '.index')
                ->with('success', __('Chart of account type successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    private static function v(
        Request $req,
        array $rules
    ): ?RedirectResponse {
        $v = Validator::make($req->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }
}
