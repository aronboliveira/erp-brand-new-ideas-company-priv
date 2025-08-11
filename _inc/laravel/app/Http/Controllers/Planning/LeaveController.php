<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Employee, Leave, LeaveType, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\{Auth, Log, Mail, Validator};
use Illuminate\Support\Arr;

class LeaveController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    public function index(Request $request): string|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, PermissionsConstants::MNG_LV, ViewsConstants::LV . '.index')) return $r;
            $cid = $user?->creatorId() ?: $user?->id;
            $leaves = Leave::query()
                ->with(['leaveType', 'employees'])
                ->when(
                    strtolower($user[UsersConstants::COL_TP]) === 'employee',
                    fn ($q) => $q->where(
                        UsersConstants::COL_EMP_ID,
                        Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                    )
                )
                ->when(
                    $user->type != 'Employee',
                    fn ($q) => $q->where(DatabaseConstants::TABLE_CREATOR, $cid)
                )
                ->get();
            return view(ViewsConstants::LV . '.' . __FUNCTION__, compact('leaves'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $request): string|RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, 'create leave', ViewsConstants::LV . '.' . __FUNCTION__)) return $r;
            $cid = $user?->creatorId() ?: $user?->id;
            $employees = Employee::query()
                ->when(
                    strtolower($user[UsersConstants::COL_TP]) === 'employee',
                    fn ($q) => $q->where(UsersConstants::COL_USER_ID, $user?->id)
                )
                ->when(
                    $user->type != 'Employee',
                    fn ($q) => $q->where(DatabaseConstants::TABLE_CREATOR, $cid)
                )
                ->get()
                ->pluck(UsersConstants::COL_NM, 'id');
            $leaveTypes = LeaveType::where(DatabaseConstants::TABLE_CREATOR, $cid)->get();
            return view(ViewsConstants::LV . '.' . __FUNCTION__, compact('employees', 'leaveTypes'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, 'create leave', ViewsConstants::LV . '.store')) return $r;
            $v = Validator::make($request->all(), [
                'leave_type_id' => 'required',
                'start_date'   => 'required',
                'end_date'     => 'required',
                'leave_reason' => 'required',
                'remark'       => 'required'
            ]);
            if ($v->fails()) return redirect()->back()
                ->with('error', $v->getMessageBag()->first());
            $empId = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                : $request->input(UsersConstants::COL_EMP_ID);
            $lt = LeaveType::find($request->input('leave_type_id'));
            $sd = new \DateTime($request->input('start_date'));
            $ed = new \DateTime($request->input('end_date'));
            $ed->add(new \DateInterval('P1D'));
            $days = $sd->diff($ed)->days ?? 0;
            if ($lt->days < $days) return redirect()->back()
                ->with('error', 'Leave type ' . $lt->name
                    . ' allows max ' . $lt->days . ' days');
            $data = $request->only([
                'leave_type_id', 'start_date', 'end_date',
                'leave_reason', 'remark'
            ]);
            foreach ([
                UsersConstants::COL_EMP_ID => $empId,
                'applied_on' => date('Y-m-d'),
                'total_leave_days' => $days,
                'status' => 'Pending',
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
            ] as $f => $val) $data[$f] = $val;
            Leave::create($data);
            return redirect()->route(ViewsConstants::LV . '.index')
                ->with('success', 'Leave successfully created.');
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(Leave $leave): RedirectResponse
    {
        return redirect()->route(ViewsConstants::LV . '.index');
    }

    public function edit(Request $request, Leave $leave): string|RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, 'edit leave', ViewsConstants::LV . '.edit')) return $r;
            if ($leave[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return defaultPermissionDenial(
                    $request,
                    new AuthorizationException(),
                    __CLASS__ . '::' . __FUNCTION__
                );
            $cid = $user?->creatorId();
            $emps = Employee::where(DatabaseConstants::TABLE_CREATOR, $cid)
                ->get()->pluck('name', 'id');
            $lts = LeaveType::where(DatabaseConstants::TABLE_CREATOR, $cid)
                ->get()->pluck('title', 'id');
            return view(ViewsConstants::LV . '.edit', compact('leave', 'emps', 'lts'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, 'edit leave', ViewsConstants::LV . '.update')) return $r;
            $leave = Leave::find($request->input('leave_id'));
            if ($leave[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return defaultPermissionDenial(
                    $request,
                    new AuthorizationException(),
                    __CLASS__ . '::' . __FUNCTION__
                );
            $v = Validator::make($request->all(), [
                'leave_type_id' => 'required',
                'start_date'   => 'required',
                'end_date'     => 'required',
                'leave_reason' => 'required',
                'remark'       => 'required'
            ]);
            if ($v->fails()) return redirect()->back()
                ->with('error', $v->getMessageBag()->first());
            $lt = LeaveType::find($request->input('leave_type_id'));
            $sd = new \DateTime($request->input('start_date'));
            $ed = new \DateTime($request->input('end_date'));
            $ed->add(new \DateInterval('P1D'));
            $days = $sd->diff($ed)->days ?? 0;
            if ($lt->days < $days) return redirect()->back()
                ->with('error', 'Leave type ' . $lt->name
                    . ' allows max ' . $lt->days . ' days');
            $upd = $request->only([
                UsersConstants::COL_EMP_ID, 'leave_type_id',
                'start_date', 'end_date',
                'leave_reason', 'remark'
            ]);
            $upd['total_leave_days'] = $days;
            $leave->update($upd);
            return redirect()->route(ViewsConstants::LV . '.index')
                ->with('success', 'Leave successfully updated.');
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $request, Leave $leave): RedirectResponse|null
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, 'delete leave', ViewsConstants::LV . '.destroy')) return $r;
            if ($leave[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return defaultPermissionDenial(
                    $request,
                    new AuthorizationException(),
                    __CLASS__ . '::' . __FUNCTION__
                );
            $leave->delete();
            return redirect()->route(ViewsConstants::LV . '.index')
                ->with('success', 'Leave successfully deleted.');
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function action(Request $request, int $id): string|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, PermissionsConstants::MNG_LV, ViewsConstants::LV . '.action')) return $r;
            $lv = Leave::find($id);
            $emp = Employee::find($lv->employee_id);
            $lt = LeaveType::find($lv->leave_type_id);
            return view(ViewsConstants::LV . '.action', compact('emp', 'lt', 'lv'));
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function changeAction(Request $request): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        try {
            if ($r = self::guard($request, PermissionsConstants::MNG_LV, ViewsConstants::LV . '.changeaction')) return $r;
            $leave = Leave::find($request->input('leave_id'));
            $st = $request->input('status');
            $upd = ['status' => $st];
            if ($st === 'Approval') {
                $sd = new \DateTime($leave->start_date);
                $ed = new \DateTime($leave->end_date);
                $upd['total_leave_days'] = $sd->diff($ed)->days;
                $upd['status']          = 'Approved';
            }
            $leave->update($upd);
            $set = Utility::settings();
            if ($set['leave_status'] ?? 0) {
                $emp = Employee::where('id', $leave->employee_id)
                    ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                    ->first();
                $arr = [
                    'leave_name' => $emp->name ?? '',
                    'leave_status' => $leave->status,
                    'leave_reason' => $leave->leave_reason,
                    'leave_start_date' => $leave->start_date,
                    'leave_end_date' => $leave->end_date,
                    'total_leave_days' => $leave->total_leave_days
                ];
                $resp = Utility::sendEmailTemplate(
                    'leave_action_sent',
                    [$emp->id => $emp->email],
                    $arr
                );
                return redirect()->route(ViewsConstants::LV . '.index')
                    ->with(
                        'success',
                        'Leave status updated.'
                            . (
                                $resp['is_success'] === false
                                && ($resp['error'] ?? false)
                                ? '<br><span class="text-danger">'
                                . $resp['error'] . '</span>' : ''
                            )
                    );
            }
            return redirect()->route(ViewsConstants::LV . '.index')
                ->with('success', 'Leave status updated.');
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function jsonCount(Request $request): array
    {
        if (self::_checkLogin() instanceof RedirectResponse) return [];
        $user = self::_checkLogin();
        try {
            if (self::guard($request, 'view leave', ViewsConstants::LV . '.jsoncount')) return [];
            $cid  = $user?->creatorId();
            $types = LeaveType::where(DatabaseConstants::TABLE_CREATOR, $cid)->get();
            $out  = [];
            foreach ($types as $t) {
                $sum = Leave::where('leave_type_id', $t->id)
                    ->where(UsersConstants::COL_EMP_ID, $request->input(UsersConstants::COL_EMP_ID))
                    ->sum('total_leave_days');
                $out[] = Arr::only([
                    'total_leave' => $sum,
                    'title' => $t->title,
                    'days' => $t->days,
                    'id' => $t->id
                ], ['total_leave', 'title', 'days', 'id']);
            }
            return $out;
        } catch (AuthorizationException $e) {
            Log::warning('jsonCount auth failed', ['error' => $e]);
            return [];
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e]);
            return [];
        }
    }
}
