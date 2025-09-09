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
use Illuminate\Support\Facades\{Auth, Log, Mail, Validator, View as ViewFacade};
use Illuminate\Support\Arr;
use Illuminate\View\View;

class LeaveController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|string|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::LV . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            try {
                if ($r = self::guard($request, PermissionsConstants::MNG_LV, ViewsConstants::LV . '.index')) return $r;
                $cid = $user?->creatorId() ?: $user?->id;
                $leaves = Leave::query()
                    ->with(['leaveType', 'employees'])
                    ->when(
                        strtolower($user[UsersConstants::COL_TP]) === 'employee',
                        fn($q) => $q->where(
                            UsersConstants::COL_EMP_ID,
                            Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                        )
                    )
                    ->when(
                        $user->type != 'Employee',
                        fn($q) => $q->where(DatabaseConstants::TABLE_CREATOR, $cid)
                    )
                    ->get();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(ViewsConstants::LV . '.index'));
                return view($view, compact('leaves'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function create(Request $request): View|string|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::LV . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            try {
                if ($r = self::guard($request, 'create leave', ViewsConstants::LV . '.' . __FUNCTION__)) return $r;
                $cid = $user?->creatorId() ?: $user?->id;
                $employees = Employee::query()
                    ->when(
                        strtolower($user[UsersConstants::COL_TP]) === 'employee',
                        fn($q) => $q->where(UsersConstants::COL_USER_ID, $user?->id)
                    )
                    ->when(
                        $user->type != 'Employee',
                        fn($q) => $q->where(DatabaseConstants::TABLE_CREATOR, $cid)
                    )
                    ->get()
                    ->pluck(UsersConstants::COL_NM, 'id');
                $leaveTypes = LeaveType::where(DatabaseConstants::TABLE_CREATOR, $cid)->get();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(ViewsConstants::LV . '.index'));
                return view($view, compact('employees', 'leaveTypes'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function store(Request $request): View|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
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
                if ($v->fails()) return redirect()->back()->with('error', $v->getMessageBag()->first());
                $empId = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                    ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id')
                    : $request->input(UsersConstants::COL_EMP_ID);
                $lt = LeaveType::find($request->input('leave_type_id'));
                $sd = new \DateTime($request->input('start_date'));
                $ed = new \DateTime($request->input('end_date'));
                $ed->add(new \DateInterval('P1D'));
                $days = $sd->diff($ed)->days ?? 0;
                if ($lt->days < $days) return redirect()->back()->with('error', 'Leave type ' . $lt->name . ' allows max ' . $lt->days . ' days');
                $data = $request->only(['leave_type_id', 'start_date', 'end_date', 'leave_reason', 'remark']);
                foreach (
                    [
                        UsersConstants::COL_EMP_ID => $empId,
                        'applied_on' => date('Y-m-d'),
                        'total_leave_days' => $days,
                        'status' => 'Pending',
                        DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
                    ] as $f => $val
                ) $data[$f] = $val;
                Leave::create($data);
                return redirect()->route(ViewsConstants::LV . '.index')->with('success', 'Leave successfully created.');
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function show(Leave $leave): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($leave) {
            return redirect()->route(ViewsConstants::LV . '.index')->with($leave);
        });
    }

    public function edit(Request $request, Leave $leave): View|string|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::LV . '.edit';

        return $this->measureProfile($action, function () use ($request, $leave, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            try {
                if ($r = self::guard($request, 'edit leave', ViewsConstants::LV . '.edit')) return $r;
                if ($leave[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $action);
                $cid = $user?->creatorId();
                $emps = Employee::where(DatabaseConstants::TABLE_CREATOR, $cid)->get()->pluck('name', 'id');
                $lts = LeaveType::where(DatabaseConstants::TABLE_CREATOR, $cid)->get()->pluck('title', 'id');
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(ViewsConstants::LV . '.index'));
                return view($view, compact('leave', 'emps', 'lts'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            try {
                if ($r = self::guard($request, 'edit leave', ViewsConstants::LV . '.update')) return $r;
                $leave = Leave::find($request->input('leave_id'));
                if ($leave[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $action);
                $v = Validator::make($request->all(), [
                    'leave_type_id' => 'required',
                    'start_date'   => 'required',
                    'end_date'     => 'required',
                    'leave_reason' => 'required',
                    'remark'       => 'required'
                ]);
                if ($v->fails()) return redirect()->back()->with('error', $v->getMessageBag()->first());
                $lt = LeaveType::find($request->input('leave_type_id'));
                $sd = new \DateTime($request->input('start_date'));
                $ed = new \DateTime($request->input('end_date'));
                $ed->add(new \DateInterval('P1D'));
                $days = $sd->diff($ed)->days ?? 0;
                if ($lt->days < $days) return redirect()->back()->with('error', 'Leave type ' . $lt->name . ' allows max ' . $lt->days . ' days');
                $upd = $request->only([UsersConstants::COL_EMP_ID, 'leave_type_id', 'start_date', 'end_date', 'leave_reason', 'remark']);
                $upd['total_leave_days'] = $days;
                $leave->update($upd);
                return redirect()->route(ViewsConstants::LV . '.index')->with('success', 'Leave successfully updated.');
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function destroy(Request $request, Leave $leave): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $leave, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            try {
                if ($r = self::guard($request, 'delete leave', ViewsConstants::LV . '.destroy')) return $r;
                if ($leave[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $action);
                $leave->delete();
                return redirect()->route(ViewsConstants::LV . '.index')->with('success', 'Leave successfully deleted.');
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function action(Request $request, int $id): View|string|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::LV . '.action';

        return $this->measureProfile($action, function () use ($request, $id, $view, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            try {
                if ($r = self::guard($request, PermissionsConstants::MNG_LV, ViewsConstants::LV . '.action')) return $r;
                $lv = Leave::find($id);
                $emp = Employee::find($lv->employee_id);
                $lt = LeaveType::find($lv->leave_type_id);
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(ViewsConstants::LV . '.index'));
                return view($view, compact('emp', 'lt', 'lv'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const CHG_ACT = 'changeAction';
    public function changeAction(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            try {
                if ($r = self::guard($request, PermissionsConstants::MNG_LV, ViewsConstants::LV . '.change_action')) return $r;
                $leave = Leave::find($request->input('leave_id'));
                $st = $request->input('status');
                $upd = ['status' => $st];
                if ($st === 'Approval') {
                    $sd = new \DateTime($leave->start_date);
                    $ed = new \DateTime($leave->end_date);
                    $upd['total_leave_days'] = $sd->diff($ed)->days;
                    $upd['status'] = 'Approved';
                }
                $leave->update($upd);
                $set = Utility::settings();
                if ($set['leave_status'] ?? 0) {
                    $emp = Employee::where('id', $leave->employee_id)->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->first();
                    $arr = [
                        'leave_name' => $emp->name ?? '',
                        'leave_status' => $leave->status,
                        'leave_reason' => $leave->leave_reason,
                        'leave_start_date' => $leave->start_date,
                        'leave_end_date' => $leave->end_date,
                        'total_leave_days' => $leave->total_leave_days
                    ];
                    $resp = Utility::sendEmailTemplate('leave_action_sent', [$emp->id => $emp->email], $arr);
                    return redirect()->route(ViewsConstants::LV . '.index')->with('success', 'Leave status updated.' . ($resp['is_success'] === false && ($resp['error'] ?? false) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
                }
                return redirect()->route(ViewsConstants::LV . '.index')->with('success', 'Leave status updated.');
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const JSON_CT = 'jsonCount';
    public function jsonCount(Request $request): array|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (self::_checkLogin() instanceof RedirectResponse) return [];
            $user = self::_checkLogin();
            try {
                if (self::guard($request, 'view leave', ViewsConstants::LV . '.jsoncount')) return [];
                $cid = $user?->creatorId();
                $types = LeaveType::where(DatabaseConstants::TABLE_CREATOR, $cid)->get();
                $out = [];
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
                Log::error($action . ' failed', ['error' => $e]);
                return [];
            }
        });
    }
}
