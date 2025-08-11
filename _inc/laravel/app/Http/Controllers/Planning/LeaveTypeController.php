<?php

namespace App\Http\Controllers;

use App\Config\Constants\DatabaseConstants;
use App\Models\LeaveType;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LeaveTypeController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'manage leave type', 'leavetype.index')) !== true)
            return $redirect;
        $leaveTypes = LeaveType::query()
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->get();
        return view('leavetype.index', compact('leaveTypes'));
    }

    public function create(Request $request): View|RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'create leave type', 'leavetype.index')) !== true)
            return $redirect;
        return view('leavetype.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'create leave type', 'leavetype.index')) !== true)
            return $redirect;
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'days'  => 'required'
            ]);
            if ($validator->fails())
                return redirect()->back()->with('error', $validator->errors()->first());
            $data             = Arr::only($request->all(), ['title', 'days']);
            $data[DatabaseConstants::TABLE_CREATOR] = $user?->creatorId();
            $leaveType        = LeaveType::create($data);
            return redirect()->route('leavetype.index')
                ->with('success', __('LeaveType successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Request $request, LeaveType $leaveType): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        return redirect()->route('leavetype.index');
    }

    public function edit(Request $request, LeaveType $leaveType): View|RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'edit leave type', 'leavetype.index')) !== true)
            return $redirect;
        if ($leaveType->created_by !== $user?->creatorId())
            return response()->json(
                ['error' => __('Permission denied.')],
                Response::HTTP_UNAUTHORIZED
            );
        return view('leavetype.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'edit leave type', 'leavetype.index')) !== true)
            return $redirect;
        if ($leaveType->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException($leaveType->getKey()),
                __CLASS__ . '::' . __FUNCTION__,
                route('leavetype.index')
            );
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'days'  => 'required'
            ]);
            if ($validator->fails())
                return redirect()->back()->with('error', $validator->errors()->first());
            $data     = Arr::only($request->all(), ['title', 'days']);
            $leaveType->update($data);
            return redirect()->route('leavetype.index')
                ->with('success', __('LeaveType successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $request, LeaveType $leaveType): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'delete leave type', 'leavetype.index')) !== true)
            return $redirect;
        if ($leaveType->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException($leaveType->getKey()),
                __CLASS__ . '::' . __FUNCTION__,
                route('leavetype.index')
            );
        try {
            $leaveType->delete();
            return redirect()->route('leavetype.index')
                ->with('success', __('LeaveType successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }
}
