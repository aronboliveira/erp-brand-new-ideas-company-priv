<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\Goal;
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class GoalController extends Controller
{
    use ChecksLogin;

    private const PERM_MANAGE = PermissionsConstants::MNG_GL;
    private const PERM_CREATE = 'create goal';
    private const PERM_EDIT  = 'edit goal';
    private const PERM_DELETE = 'delete goal';

    public function index(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if ($resp = self::_authorize($request, self::PERM_MANAGE)) return $resp;
                $user = $userOrRedirect;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                $goals = Goal::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                return view($view, compact('goals'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function create(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if ($resp = self::_authorize($request, self::PERM_CREATE)) return $resp;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                $types = Goal::$goalType;
                return view($view, compact('types'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if ($resp = self::_authorize($request, self::PERM_CREATE)) return $resp;
                $v = Validator::make($request->all(), [
                    'name'   => 'required',
                    'type'   => 'required',
                    'from'   => 'required|date',
                    'to'     => 'required|date|after_or_equal:from',
                    'amount' => 'required|numeric',
                ]);
                if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
                Goal::create([
                    'name'       => $request->input('name'),
                    'type'       => $request->input('type'),
                    'from'       => $request->input('from'),
                    'to'         => $request->input('to'),
                    'amount'     => $request->input('amount'),
                    'is_display' => $request->boolean('is_display'),
                    DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);
                return redirect()->route(ViewsConstants::GL . '.index')->with('success', __('Goal successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function show(Request $request, Goal $goal): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $goal, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                return redirect()->route(ViewsConstants::GL . '.index')->with($goal);
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function edit(Request $request, Goal $goal): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $goal, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if ($resp = self::_authorize($request, self::PERM_EDIT)) return $resp;
                if ($goal[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($request, new AuthorizationException(), $action);
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                $types = Goal::$goalType;
                return view($view, compact('goal', 'types'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $goal, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if ($resp = self::_authorize($request, self::PERM_EDIT)) return $resp;
                if ($goal[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($request, new AuthorizationException(), $action);
                $v = Validator::make($request->all(), [
                    'name'   => 'required',
                    'type'   => 'required',
                    'from'   => 'required|date',
                    'to'     => 'required|date|after_or_equal:from',
                    'amount' => 'required|numeric',
                ]);
                if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
                $goal->update([
                    'name'       => $request->input('name'),
                    'type'       => $request->input('type'),
                    'from'       => $request->input('from'),
                    'to'         => $request->input('to'),
                    'amount'     => $request->input('amount'),
                    'is_display' => $request->boolean('is_display'),
                ]);
                return redirect()->route(ViewsConstants::GL . '.index')->with('success', __('Goal successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function destroy(Request $request, Goal $goal): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $goal, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if ($resp = self::_authorize($request, self::PERM_DELETE)) return $resp;
                if ($goal[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($request, new AuthorizationException(), $action);
                $goal->delete();
                return redirect()->route(ViewsConstants::GL . '.index')->with('success', __('Goal successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    private static function _authorize(Request $req, string $perm): ?RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $req->user()->can($perm)
            ? null
            : defaultPermissionDenial($req, new AuthorizationException(), $action);
    }
}
