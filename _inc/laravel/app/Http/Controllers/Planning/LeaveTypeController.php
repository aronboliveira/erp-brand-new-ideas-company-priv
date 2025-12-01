<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, ViewsConstants as VW};
use App\Models\LeaveType;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewFacade;
use Symfony\Component\HttpFoundation\Response;

class LeaveTypeController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::LV_TP . '.index';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage leave type', VW::LV_TP . '.index')) !== true) return $redirect;
            $leaveTypes = LeaveType::query()
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->get();
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(VW::LV_TP . '.index'));
            return view($view, compact('leaveTypes'));
        });
    }

    public function create(Request $request): View|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::LV_TP . '.create';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'create leave type', VW::LV_TP . '.index')) !== true) return $redirect;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(VW::LV_TP . '.index'));
            return view($view);
        });
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'create leave type', VW::LV_TP . '.index')) !== true) return $redirect;
            try {
                $validator = Validator::make($request->all(), [
                    'title' => 'required',
                    'days'  => 'required'
                ]);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
                $data = Arr::only($request->all(), ['title', 'days']);
                $data[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
                LeaveType::create($data);
                return redirect()->route(VW::LV_TP . '.index')->with('success', __('LeaveType successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function show(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $leaveType) {
            if ((self::_checkLogin()) instanceof RedirectResponse) return self::_checkLogin();
            return redirect()->route(VW::LV_TP . '.index')->with([$request, $leaveType]);
        });
    }

    public function edit(Request $request, LeaveType $leaveType): View|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::LV_TP . '.edit';

        return $this->measureProfile($action, function () use ($request, $leaveType, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'edit leave type', VW::LV_TP . '.index')) !== true) return $redirect;
            if ($leaveType->created_by !== $user?->creatorId())
                return response()->json(['error' => __('Permission denied.')], Response::HTTP_UNAUTHORIZED);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(VW::LV_TP . '.index'));
            return view($view, compact('leaveType'));
        });
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $leaveType, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'edit leave type', VW::LV_TP . '.index')) !== true) return $redirect;
            if ($leaveType->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new AuthorizationException($leaveType->getKey()), $action, route(VW::LV_TP . '.index'));
            try {
                $validator = Validator::make($request->all(), [
                    'title' => 'required',
                    'days'  => 'required'
                ]);
                if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first());
                $data = Arr::only($request->all(), ['title', 'days']);
                $leaveType->update($data);
                return redirect()->route(VW::LV_TP . '.index')->with('success', __('LeaveType successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function destroy(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $leaveType, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'delete leave type', VW::LV_TP . '.index')) !== true) return $redirect;
            if ($leaveType->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new AuthorizationException($leaveType->getKey()), $action, route(VW::LV_TP . '.index'));
            try {
                $leaveType->delete();
                return redirect()->route(VW::LV_TP . '.index')->with('success', __('LeaveType successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }
}
