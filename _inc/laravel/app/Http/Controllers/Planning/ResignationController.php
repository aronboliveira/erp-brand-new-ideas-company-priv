<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Employee, Resignation, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};

class ResignationController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::RSG . '.index';

    public function index(Request $request): \Illuminate\View\View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_RSG, self::REDIRECT_INDEX)) !== true) return $redirect;
        try {
            $query = Resignation::with('employee')
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId());
            $resignations = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                ? $query->where(UsersConstants::COL_EMP_ID, Employee::where(UsersConstants::COL_USER_ID, $user?->id)
                    ->value('id'))->get()
                : $query->get();
            return view(ViewsConstants::RSG . '.' . __FUNCTION__, compact('resignations'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function create(Request $request): \Illuminate\View\View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create resignation', self::REDIRECT_INDEX)) !== true) return $redirect;
        try {
            $employees = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN
                ? Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')
                : Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
            return view(ViewsConstants::RSG . '.' . __FUNCTION__, compact('employees'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function show(Request $request, Resignation $resignation): \Illuminate\View\View|\Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($resp = self::guard($request, 'view resignation', self::REDIRECT_INDEX)) return $resp;
        try {
            if ($resignation->created_by !== $user?->creatorId()) {
                Log::warning("$action forbidden", [
                    UsersConstants::COL_USER_ID       => $user?->id,
                    'resignation_id' => $resignation->id
                ]);
                return defaultPermissionDenial(
                    $request,
                    new \Illuminate\Auth\Access\AuthorizationException(),
                    $action,
                    route(self::REDIRECT_INDEX)
                );
            }
            Log::info("$action loaded", ['resignation_id' => $resignation->id]);
            return view(ViewsConstants::RSG . '.' . __FUNCTION__, compact('resignation'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create resignation', self::REDIRECT_INDEX)) !== true) return $redirect;

        $request->validate([
            'notice_date'      => 'required|date',
            'resignation_date' => 'required|date',
            'description'     => 'nullable|string',
            UsersConstants::COL_EMP_ID      => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $employeeId = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                : $request->input(UsersConstants::COL_EMP_ID);
            $resignation = Resignation::create([
                UsersConstants::COL_EMP_ID       => $employeeId,
                'notice_date'       => $request->input('noticeDate'),
                'resignation_date'  => $request->input('resignationDate'),
                'description'       => $request->input('description'),
                DatabaseConstants::TABLE_CREATOR        => $user?->creatorId(),
            ]);
            DB::commit();

            $settings = Utility::settings($user?->creatorId());
            if (!empty($settings['resignation_sent'])) {
                $emp = $resignation->employee;
                $data = [
                    'resignation_email' => $emp->email,
                    'assign_user'      => $emp->name,
                    'resignation_date' => $resignation->resignation_date,
                    'notice_date'      => $resignation->notice_date,
                ];
                $resp = Utility::sendEmailTemplate('resignation_sent', [$emp->email], $data);
                $msgSuffix = (!empty($resp['is_success']) || empty($resp['error']))
                    ? ''
                    : '<br><span class="text-danger">' . $resp['error'] . '</span>';
            }

            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Resignation successfully created.') . ($msgSuffix ?? ''));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function edit(Request $request, string|int $id): \Illuminate\View\View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'edit resignation', self::REDIRECT_INDEX)) !== true) return $redirect;

        try {
            $resignation = Resignation::findOrFail($id);
            if ($resignation->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            $employees = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN
                ? Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck(UsersConstants::COL_NM, 'id')
                : Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck(UsersConstants::COL_NM, 'id');
            return view(ViewsConstants::RSG . '.' . __FUNCTION__, compact('resignation', 'employees'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function update(Request $request, string|int $id): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'edit resignation', self::REDIRECT_INDEX)) !== true) return $redirect;
        $request->validate([
            'notice_date'      => 'required|date',
            'resignation_date' => 'required|date',
            'description'     => 'nullable|string',
            UsersConstants::COL_EMP_ID      => 'nullable|string',
        ]);
        DB::beginTransaction();
        try {
            $resignation = Resignation::findOrFail($id);
            if ($resignation->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

            if ($user->type != 'Employee' && $request->filled(UsersConstants::COL_EMP_ID))
                $resignation->employee_id = $request->input(UsersConstants::COL_EMP_ID);
            $resignation->fill([
                'notice_date'      => $request->input('noticeDate'),
                'resignation_date' => $request->input('resignationDate'),
                'description'      => $request->input('description'),
            ])->save();
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Resignation successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function destroy(Request $request, string|int $id): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'delete resignation', self::REDIRECT_INDEX)) !== true) return $redirect;
        DB::beginTransaction();
        try {
            $resignation = Resignation::findOrFail($id);
            if ($resignation->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            $resignation->delete();
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Resignation successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }
}
