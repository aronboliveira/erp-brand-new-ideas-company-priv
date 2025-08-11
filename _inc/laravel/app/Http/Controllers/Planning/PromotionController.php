<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Designation, Employee, Promotion, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Auth, Mail, Validator};
use Symfony\Component\HttpFoundation\Response;

class PromotionController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;
    private const REDIRECT_INDEX = ViewsConstants::PRM . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_PRM, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $promotions = Promotion::query()
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->when(strtolower($user[UsersConstants::COL_TP]) === 'employee', function ($q) use ($user) {
                $emp = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first();
                return $q->where(UsersConstants::COL_EMP_ID, $emp->id);
            })
            ->with(['designation', 'employee'])
            ->get();
        return view(self::REDIRECT_INDEX, compact('promotions'));
    }

    public function create(Request $request): View|RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'create promotion', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $data = [
            'designations' => Designation::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->pluck('name', 'id'),
            'employees'    => Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->pluck('name', 'id')
        ];
        return view(ViewsConstants::PRM . '.' . __FUNCTION__, $data);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'create promotion', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        try {
            $validator = Validator::make($request->all(), [
                UsersConstants::COL_EMP_ID     => 'required',
                'designation_id'  => 'required',
                'promotion_title' => 'required',
                'promotion_date'  => 'required'
            ]);
            if ($validator->fails())
                return redirect()->back()
                    ->with('error', $validator->errors()->first());
            $data = Arr::only($request->all(), [
                UsersConstants::COL_EMP_ID, 'designation_id',
                'promotion_title', 'promotion_date'
            ]);
            $data['description'] = $request->description ?? '';
            $data[DatabaseConstants::TABLE_CREATOR] = $user?->creatorId();
            $promotion = Promotion::create($data);
            $settings = Utility::settings();
            if ($settings['promotion_sent'] == 1) {
                $employee   = Employee::find($promotion->employee_id);
                $designation = Designation::find($promotion->designation_id);
                $promotionArr = [
                    'employee_name'         => $employee->name,
                    'promotion_designation' => $designation->name,
                    'promotion_title'       => $promotion->promotion_title,
                    'promotion_date'        => $promotion->promotion_date
                ];
                $resp = Utility::sendEmailTemplate(
                    'promotion_sent',
                    [$employee->email],
                    $promotionArr
                );
                $msg = __('Promotion successfully created.');
                $msg .= !empty($resp)
                    && $resp['is_success'] === false
                    && !empty($resp['error'])
                    ? '<br> <span class="text-danger">'
                    . $resp['error']
                    . '</span>'
                    : '';
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', $msg);
            }
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Promotion successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Request $request, Promotion $promotion): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        return redirect()->route(self::REDIRECT_INDEX);
    }

    public function edit(Request $request, Promotion $promotion): View|RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'edit promotion', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if ($promotion->created_by !== $user?->creatorId())
            return response()->json(
                ['error' => __('Permission denied.')],
                Response::HTTP_UNAUTHORIZED
            );
        $data = [
            'promotion'    => $promotion,
            'designations' => Designation::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id'),
            'employees'    => Employee::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id')
        ];
        return view(ViewsConstants::PRM . '.edit', $data);
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'edit promotion', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if ($promotion->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException($promotion->getKey()),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        try {
            $validator = Validator::make($request->all(), [
                UsersConstants::COL_EMP_ID     => 'required',
                'designation_id'  => 'required',
                'promotion_title' => 'required',
                'promotion_date'  => 'required'
            ]);
            if ($validator->fails())
                return redirect()->back()
                    ->with('error', $validator->errors()->first());
            $data = Arr::only($request->all(), [
                UsersConstants::COL_EMP_ID, 'designation_id',
                'promotion_title', 'promotion_date'
            ]);
            $data['description'] = $request->description ?? '';
            $promotion->update($data);
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Promotion successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $request, Promotion $promotion): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'delete promotion', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if ($promotion->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException($promotion->getKey()),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        try {
            $promotion->delete();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Promotion successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }
}
