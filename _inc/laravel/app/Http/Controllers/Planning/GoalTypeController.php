<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    ViewsConstants
};
use App\Models\GoalType;
use App\Traits\ChecksLogin;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;

class GoalTypeController extends Controller
{
    use ChecksLogin;

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TP . '.index';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = $this->_authorize($request, 'manage goal type')) instanceof RedirectResponse) return $redirect;
            $user = $request->user();
            $goalTypes = GoalType::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('goalTypes'));
        });
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TP . '.create';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = $this->_authorize($request, 'create goal type')) instanceof RedirectResponse) return $redirect;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view);
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = $this->_authorize($request, 'create goal type')) instanceof RedirectResponse) return $redirect;
            $v = Validator::make($request->all(), ['name' => 'required']);
            if ($v->fails()) return back()->with('error', $v->getMessageBag()->first());
            $user = $request->user();
            GoalType::create([
                'name' => $request->name,
                DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()
            ]);
            return redirect()->route(ViewsConstants::GL_TP . '.index')->with('success', __('GoalType successfully created.'));
        });
    }

    public function show(Request $request, GoalType $goalType): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TP . '.show';

        return $this->measureProfile($action, function () use ($request, $goalType, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = $this->_authorize($request, 'manage goal type')) instanceof RedirectResponse) return $redirect;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('goalType'));
        });
    }

    public function edit(Request $request, string|int $id): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TP . '.edit';

        return $this->measureProfile($action, function () use ($request, $id, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = $this->_authorize($request, 'edit goal type')) instanceof RedirectResponse) return $redirect;
            $goalType = GoalType::findOrFail($id);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('goalType'));
        });
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = $this->_authorize($request, 'edit goal type')) instanceof RedirectResponse) return $redirect;
            $v = Validator::make($request->all(), ['name' => 'required']);
            if ($v->fails()) return back()->with('error', $v->getMessageBag()->first());
            $goalType = GoalType::findOrFail($id);
            $goalType->name = $request->name;
            $goalType->save();
            return redirect()->route(ViewsConstants::GL_TP . '.index')->with('success', __('GoalType successfully updated.'));
        });
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($redirect = $this->_authorize($request, 'delete goal type')) instanceof RedirectResponse) return $redirect;
            $goalType = GoalType::findOrFail($id);
            if ($goalType->created_by !== $request->user()->creatorId())
                return defaultPermissionDenial($request, null, $action, route(ViewsConstants::GL_TP . '.index'));
            $goalType->delete();
            return redirect()->route(ViewsConstants::GL_TP . '.index')->with('success', __('GoalType successfully deleted.'));
        });
    }

    private function _authorize(Request $request, string $ability): ?RedirectResponse
    {
        return $request->user()->can($ability)
            ? null
            : defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__, route(ViewsConstants::GL_TP . '.index'));
    }
}
