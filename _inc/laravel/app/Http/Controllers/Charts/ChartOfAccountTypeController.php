<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ChartsConstants,
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
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
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;

final class ChartOfAccountTypeController extends Controller
{

    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'chart-of-account-type'; // ! ALERT
    private const REDIRECT_INDEX = self::SINGULAR . '.index'; // ! ALERT

    public function index(Request $req): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $func, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, PermissionsConstants::MNG_COA_TYPE, self::REDIRECT_INDEX)) !== true) return $c;

            try {
                $t = microtime(true);
                $types = ChartOfAccountType::where(DatabaseConstants::COL_TABLE_CREATOR, $req->user()?->creatorId())->get();
                $this->logExecutionTime($t, $action, 'fetchTypes');

                $view = self::SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \Exception('view'), $action, route(self::REDIRECT_INDEX));
                return ViewFacade::make($view, compact('types'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action);
            }
        }, [UsersConstants::COL_USER_ID => $req->user()?->id ?? null]);
    }

    public function create(Request $req): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $func, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, PermissionsConstants::CR_COA_TYPE, self::REDIRECT_INDEX)) !== true) return $c;

            $view = self::SINGULAR . '.' . $func;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \Exception('view'), $action, route(self::REDIRECT_INDEX));
            return ViewFacade::make($view);
        }, [UsersConstants::COL_USER_ID => $req->user()?->id ?? null]);
    }

    public function store(Request $req): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, PermissionsConstants::CR_COA_TYPE, self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($req, [ChartsConstants::COL_NM => 'required'])) return $c;

            try {
                $t = microtime(true);
                ChartOfAccountType::create([
                    ChartsConstants::COL_NM          => $req->name,
                    DatabaseConstants::COL_TABLE_CREATOR => $req->user()?->creatorId(),
                ]);
                $this->logExecutionTime($t, $action, 'createType');

                return redirect()->route(self::SINGULAR . '.index')
                    ->with('success', __('Chart of account type successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action);
            }
        }, [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'name' => $req->input('name')]);
    }

    public function show(Request $req, ChartOfAccountType $chartOfAccountType): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile(function () use ($req) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, PermissionsConstants::MNG_COA_TYPE, self::REDIRECT_INDEX)) !== true) return $c;

            return redirect()->route(self::SINGULAR . '.index');
        }, ['type_id' => $chartOfAccountType->id ?? null]);
    }

    public function edit(Request $req, ChartOfAccountType $chartOfAccountType): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $chartOfAccountType, $func, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, 'edit constant chart of account type', self::REDIRECT_INDEX)) !== true) return $c;

            $view = self::SINGULAR . '.' . $func;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \Exception('view'), $action, route(self::REDIRECT_INDEX));
            return ViewFacade::make($view, compact('chartOfAccountType'));
        }, ['type_id' => $chartOfAccountType->id ?? null]);
    }

    public function update(Request $req, ChartOfAccountType $chartOfAccountType): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $chartOfAccountType, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, 'edit constant chart of account type', self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($req, [ChartsConstants::COL_NM => 'required'])) return $c;

            try {
                $t = microtime(true);
                $chartOfAccountType->update([ChartsConstants::COL_NM => $req->name]);
                $this->logExecutionTime($t, $action, 'updateType');

                return redirect()->route(self::SINGULAR . '.index')
                    ->with('success', __('Chart of account type successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['type_id' => $chartOfAccountType->id ?? null, 'name' => $req->input('name')]);
    }

    public function destroy(Request $req, ChartOfAccountType $chartOfAccountType): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $chartOfAccountType, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, 'delete constant chart of account type', self::REDIRECT_INDEX)) !== true) return $c;

            try {
                $t = microtime(true);
                $chartOfAccountType->delete();
                $this->logExecutionTime($t, $action, 'deleteType');

                return redirect()->route(self::SINGULAR . '.index')
                    ->with('success', __('Chart of account type successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['type_id' => $chartOfAccountType->id ?? null]);
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
