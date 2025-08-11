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

class GoalController extends Controller
{
    use ChecksLogin;

    private const PERM_MANAGE = PermissionsConstants::MNG_GL;
    private const PERM_CREATE = 'create goal';
    private const PERM_EDIT  = 'edit goal';
    private const PERM_DELETE = 'delete goal';

    public function index(Request $request): RedirectResponse|\Illuminate\View\View
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            if ($resp = self::_authorize($request, self::PERM_MANAGE)) return $resp;
            $user = $userOrRedirect;
            $goals = Goal::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(ViewsConstants::GL . '.' . __FUNCTION__, compact('goals'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $request): RedirectResponse|\Illuminate\View\View
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            if ($resp = self::_authorize($request, self::PERM_CREATE)) return $resp;
            $types = Goal::$goalType;
            return view(ViewsConstants::GL . '.' . __FUNCTION__, compact('types'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = self::_authorize($request, self::PERM_CREATE)) return $resp;
            $v = Validator::make($request->all(), [
                'name'      => 'required',
                'type'      => 'required',
                'from'      => 'required|date',
                'to'        => 'required|date|after_or_equal:from',
                'amount'    => 'required|numeric',
            ]);
            if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
            Goal::create([
                'name'       => $request->input('name'),
                'type'       => $request->input('type'),
                'from'       => $request->input('from'),
                'to'         => $request->input('to'),
                'amount'     => $request->input('amount'),
                'is_display' => $request->boolean('is_display'),
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);
            return redirect()->route(ViewsConstants::GL . '.index')->with('success', __('Goal successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(Request $request, Goal $goal): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            return redirect()->route(ViewsConstants::GL . '.index');
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $request, Goal $goal): RedirectResponse|\Illuminate\View\View
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = self::_authorize($request, self::PERM_EDIT)) return $resp;
            if ($goal->created_by !== $user?->creatorId()) return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__
            );
            $types = Goal::$goalType;
            return view(ViewsConstants::GL . '.' . __FUNCTION__, compact('goal', 'types'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = self::_authorize($request, self::PERM_EDIT)) return $resp;
            if ($goal->created_by !== $user?->creatorId()) return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__
            );
            $v = Validator::make($request->all(), [
                'name'      => 'required',
                'type'      => 'required',
                'from'      => 'required|date',
                'to'        => 'required|date|after_or_equal:from',
                'amount'    => 'required|numeric',
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
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $request, Goal $goal): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = self::_authorize($request, self::PERM_DELETE)) return $resp;
            if ($goal->created_by !== $user?->creatorId()) return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__
            );
            $goal->delete();
            return redirect()->route(ViewsConstants::GL . '.index')->with('success', __('Goal successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    private static function _authorize(Request $req, string $perm): ?RedirectResponse
    {
        return $req->user()->can($perm)
            ? null
            : defaultPermissionDenial($req, new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
    }
}
