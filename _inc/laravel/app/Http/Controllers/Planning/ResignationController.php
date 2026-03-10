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
use Illuminate\Support\Facades\{DB, Log, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class ResignationController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::RSG . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = ViewsConstants::RSG . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($resp = self::guard($request, PermissionsConstants::MNG_RSG, self::REDIRECT_INDEX)) !== true) return $resp;

            try {
                $query = Resignation::with('employee')
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());

                $resignations = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                    ? $query->where(
                        UsersConstants::COL_EMP_ID,
                        Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                    )->get()
                    : $query->get();

                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                }
                return view($view, compact('resignations'));
            } catch (\Throwable $e) {
                Log::error("$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = ViewsConstants::RSG . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($resp = self::guard($request, 'create resignation', self::REDIRECT_INDEX)) !== true) return $resp;

            try {
                $employees = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN
                    ? Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')
                    : Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');

                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                }
                return view($view, compact('employees'));
            } catch (\Throwable $e) {
                Log::error("$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function show(Request $request, Resignation $resignation): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = ViewsConstants::RSG . '.show';

        return $this->measureProfile($action, function () use ($request, $resignation, $action, $view) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($resp = self::guard($request, 'view resignation', self::REDIRECT_INDEX)) !== true) return $resp;

            try {
                if ($resignation[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                    Log::warning("$action forbidden", [UsersConstants::COL_USER_ID => $user?->id, 'resignation_id' => $resignation->id]);
                    return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), $action, route(self::REDIRECT_INDEX));
                }
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                }
                return view($view, compact('resignation'));
            } catch (\Throwable $e) {
                Log::error("$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($resp = self::guard($request, 'create resignation', self::REDIRECT_INDEX)) !== true) return $resp;

            $request->validate([
                'notice_date'       => 'required|date',
                'resignation_date'  => 'required|date',
                'description'       => 'nullable|string',
                UsersConstants::COL_EMP_ID => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                $employeeId = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                    ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                    : $request->input(UsersConstants::COL_EMP_ID);

                $resignation = Resignation::create([
                    UsersConstants::COL_EMP_ID        => $employeeId,
                    'notice_date'        => $request->input('notice_date'),
                    'resignation_date'   => $request->input('resignation_date'),
                    'description'        => $request->input('description'),
                    DatabaseConstants::COL_TABLE_CREATOR  => $user?->creatorId(),
                ]);
                DB::commit();

                $msgSuffix = '';
                $settings = Utility::settingsById($user?->creatorId());
                if (!empty($settings['resignation_sent'])) {
                    $emp = $resignation->employee;
                    $data = [
                        'resignation_email' => $emp->email,
                        'assign_user'       => $emp->name,
                        'resignation_date'  => $resignation->resignation_date,
                        'notice_date'       => $resignation->notice_date,
                    ];
                    $resp = Utility::sendEmailTemplate('resignation_sent', [$emp->id => $emp->email], $data);
                    if (empty($resp['is_success']) && !empty($resp['error'])) {
                        $msgSuffix = '<br><span class="text-danger">' . $resp['error'] . '</span>';
                    }
                }

                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Resignation successfully created.') . $msgSuffix);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, string|int $id): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = ViewsConstants::RSG . '.edit';

        return $this->measureProfile($action, function () use ($request, $id, $action, $view) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($resp = self::guard($request, 'edit resignation', self::REDIRECT_INDEX)) !== true) return $resp;

            try {
                $resignation = Resignation::findOrFail($id);
                if ($resignation[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

                $employees = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN
                    ? Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck(UsersConstants::COL_NM, 'id')
                    : Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck(UsersConstants::COL_NM, 'id');

                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                }
                return view($view, compact('resignation', 'employees'));
            } catch (\Throwable $e) {
                Log::error("$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function update(Request $request, string|int $id): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($resp = self::guard($request, 'edit resignation', self::REDIRECT_INDEX)) !== true) return $resp;

            $request->validate([
                'notice_date'       => 'required|date',
                'resignation_date'  => 'required|date',
                'description'       => 'nullable|string',
                UsersConstants::COL_EMP_ID => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                $resignation = Resignation::findOrFail($id);
                if ($resignation[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

                if ($user->type != 'Employee' && $request->filled(UsersConstants::COL_EMP_ID)) {
                    $resignation->employee_id = $request->input(UsersConstants::COL_EMP_ID);
                }

                $resignation->fill([
                    'notice_date'       => $request->input('notice_date'),
                    'resignation_date'  => $request->input('resignation_date'),
                    'description'       => $request->input('description'),
                ])->save();

                DB::commit();
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Resignation successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$action error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, string|int $id): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($resp = self::guard($request, 'delete resignation', self::REDIRECT_INDEX)) !== true) return $resp;

            DB::beginTransaction();
            try {
                $resignation = Resignation::findOrFail($id);
                if ($resignation[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
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
        });
    }
}
