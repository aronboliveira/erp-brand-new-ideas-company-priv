<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BanksConstants,
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Models\{Branch, Department, Employee, Transfer, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Validator};
use Illuminate\View\View;

class TransferController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::TRF . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        try {
            if ($c = self::guard($request, PermissionsConstants::MNG_TRF, self::ROUTE_INDEX)) return $c;
            $user = $request->user();
            Log::info(__METHOD__ . ' start', ['user' => $user?->id]);
            $query = Transfer::with(['employee', 'branch', 'department'])
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId());
            if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                $empId = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id');
                $query->where(UsersConstants::COL_EMP_ID, $empId);
            }
            $transfers = $query->get();
            Log::info(__METHOD__ . ' loaded transfers', ['count' => $transfers->count()]);
            return view(ViewsConstants::TRF . '.' . __FUNCTION__, ['transfers' => $transfers]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($c = self::guard($request, 'create transfer', self::ROUTE_INDEX)) return $c;
        $user = $request->user();
        Log::info(__METHOD__ . ' start', ['user' => $user?->id]);
        $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
        $branches   = Branch::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        $employees  = Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(UsersConstants::COL_NM, 'id');
        return view(ViewsConstants::TRF . '.' . __FUNCTION__, compact('employees', 'departments', 'branches'));
    }

    public function show(
        Request $request,
        Transfer $transfer
    ): View|\Illuminate\Http\JsonResponse|RedirectResponse|null {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $redirect = self::guard(
                $request,
                PermissionsConstants::MNG_TRF,
                self::ROUTE_INDEX
            )
        ) return $redirect;
        if ($transfer->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::ROUTE_INDEX)
            );
        Log::info(
            "$action called",
            ['transferId' => $transfer->id, UsersConstants::COL_USER_ID => $user?->id]
        );
        try {
            return $request->wantsJson()
                ? response()->json($transfer)
                : view(ViewsConstants::TRF . '.' . __FUNCTION__, compact('transfer'));
        } catch (\Throwable $e) {
            Log::error(
                "$action failed",
                [
                    'error' => $e->getMessage(),
                ]
            );
            Log::channel(SettingsConstants::ERR_TRACE)->debug(
                "$action failed",
                [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]
            );
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        try {
            if ($c = self::guard($request, 'create transfer', self::ROUTE_INDEX)) return $c;
            $user      = $request->user();
            Log::info(__METHOD__ . ' start', ['user' => $user?->id, 'input' => $request->all()]);
            $data = Validator::make($request->all(), [
                UsersConstants::COL_EMP_ID   => 'required',
                CompaniesConstants::COL_BRC_ID     => 'required',
                CompaniesConstants::COL_DEP_ID => 'required',
                'transferDate' => 'required|date',
            ])->validate();
            DB::transaction(function () use ($data, $user) {
                $transfer = new Transfer();
                $transfer->employee_id  = $data[UsersConstants::COL_EMP_ID];
                $transfer->branch_id    = $data[CompaniesConstants::COL_BRC_ID];
                $transfer->department_id = $data[CompaniesConstants::COL_DEP_ID];
                $transfer->transfer_date = $data['transferDate'];
                $transfer->description  = $data['description'] ?? '';
                $transfer->created_by   = $user?->creatorId();
                $transfer->save();

                Log::info(__METHOD__ . ' created transfer', ['id' => $transfer->id]);

                if (Utility::settings($user?->creatorId())['transfer_sent'] ?? false) {
                    $emp      = Employee::findOrFail($transfer->employee_id);
                    $branch   = Branch::findOrFail($transfer->branch_id);
                    $dept     = Department::findOrFail($transfer->department_id);
                    $transferArr = [
                        'transfer_name'       => $emp->name,
                        'transfer_email'      => $emp->email,
                        'transfer_date'       => $transfer->transfer_date,
                        'transfer_department' => $dept->name,
                        'transfer_branch'     => $branch->name,
                        'transfer_description' => $transfer->description,
                    ];
                    $resp = Utility::sendEmailTemplate(
                        'transfer_sent',
                        [$emp->id => $emp->email],
                        $transferArr
                    );
                    Log::info(__METHOD__ . ' emailed transfer', ['success' => $resp['is_success']]);
                }
            });

            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Transfer successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function edit(Request $request, Transfer $transfer): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($c = self::guard($request, 'edit transfer', self::ROUTE_INDEX)) return $c;
        $user = $request->user();
        Log::info(__METHOD__ . ' start', ['user' => $user?->id, 'transfer' => $transfer->id]);
        if ($transfer->created_by !== $user?->creatorId()) {
            Log::warning(__METHOD__ . ' unauthorized', ['user' => $user?->id]);
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX),
                false
            );
        }
        $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
        $branches   = Branch::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        $employees  = Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck(UsersConstants::COL_NM, 'id');
        return view(ViewsConstants::TRF . '.' . __FUNCTION__, compact('transfer', 'employees', 'departments', 'branches'));
    }

    public function update(Request $request, Transfer $transfer): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        try {
            if ($c = self::guard($request, 'edit transfer', self::ROUTE_INDEX)) return $c;
            $user = $request->user();
            Log::info(__METHOD__ . ' start', ['user' => $user?->id, 'transfer' => $transfer->id]);
            if ($transfer->created_by !== $user?->creatorId())
                throw new \Exception('owner');
            $data = Validator::make($request->all(), [
                UsersConstants::COL_EMP_ID   => 'required',
                CompaniesConstants::COL_BRC_ID     => 'required',
                CompaniesConstants::COL_DEP_ID => 'required',
                'transferDate' => 'required|date',
            ])->validate();
            DB::transaction(function () use ($transfer, $data) {
                $transfer->employee_id  = $data[UsersConstants::COL_EMP_ID];
                $transfer->branch_id    = $data[CompaniesConstants::COL_BRC_ID];
                $transfer->department_id = $data[CompaniesConstants::COL_DEP_ID];
                $transfer->transfer_date = $data['transferDate'];
                $transfer->description  = $data['description'] ?? '';
                $transfer->save();
                Log::info(__METHOD__ . ' updated transfer', ['id' => $transfer->id]);
            });
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Transfer successfully updated.'));
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return $e->getMessage() === 'owner'
                ? defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX))
                : defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    public function destroy(Request $request, Transfer $transfer): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        try {
            if ($c = self::guard($request, 'delete transfer', self::ROUTE_INDEX)) return $c;
            $user = $request->user();
            Log::info(__METHOD__ . ' start', ['user' => $user?->id, 'transfer' => $transfer->id]);

            if ($transfer->created_by !== $user?->creatorId()) {
                throw new \Exception('owner');
            }

            DB::transaction(fn () => $transfer->delete());

            Log::info(__METHOD__ . ' deleted transfer', ['id' => $transfer->id]);
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Transfer successfully deleted.'));
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return $e->getMessage() === 'owner'
                ? defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX))
                : defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }
}
