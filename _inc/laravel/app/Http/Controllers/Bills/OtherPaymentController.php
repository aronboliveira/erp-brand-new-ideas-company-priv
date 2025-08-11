<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Employee, OtherPayment};
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class OtherPaymentController extends Controller
{

    use ChecksLogin;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public const OT_PAY_CR = 'otherPaymentCreate';
    public function otherPaymentCreate(Request $req, int|string $employeeId): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID     => Auth::id(),
            UsersConstants::COL_EMP_ID => $employeeId
        ]);
        if ($r = self::authorizePerm($req, 'create other payment'))
            return $r;
        try {
            $employee = Employee::findOrFail($employeeId);
            Log::info(__METHOD__ . ' employee loaded', [UsersConstants::COL_EMP_ID => $employeeId]);
            return response()->view(ViewsConstants::OT_PAY . '.create', [
                'employee'     => $employee,
                'otherpaytype' => OtherPayment::$otherPaymentType,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error(__METHOD__ . ' employee not found', [
                UsersConstants::COL_EMP_ID => $employeeId,
                'error'       => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __METHOD__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', [
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __METHOD__);
        }
    }

    public function show(Request $request, int|string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $request->user()->id,
            'id'       => $id,
        ]);
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if ($resp = self::authorizePerm($request, 'show other payment')) return $resp;
            $otherPayment = OtherPayment::findOrFail($id);
            if ($otherPayment->created_by !== $request->user()->creatorId()) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' forbidden', [
                    UsersConstants::COL_USER_ID          => $request->user()->id,
                    'otherpayment_id'  => $id,
                    'owner_id'         => $otherPayment->created_by,
                ]);
                return defaultPermissionDenial(
                    $request,
                    new AuthorizationException(),
                    __CLASS__ . '::' . __FUNCTION__
                );
            }
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' loaded', ['otherpayment_id' => $id]);
            return view(ViewsConstants::OT_PAY . '.show', ['otherpayment' => $otherPayment]);
        } catch (ModelNotFoundException $e) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not found', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', __('Other payment not found.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(),
            'input'   => $req->all()
        ]);
        if ($r = self::authorizePerm($req, 'create other payment'))
            return $r;
        if ($r = self::validateInput($req))
            return $r;
        DB::beginTransaction();
        try {
            $data = $req->only([UsersConstants::COL_EMP_ID, 'title', 'type', 'amount']);
            $data[DatabaseConstants::TABLE_CREATOR] = $req->user()->creatorId();
            Log::info(__METHOD__ . ' creating OtherPayment', ['data' => $data]);
            $op = OtherPayment::create($data);
            DB::commit();
            Log::info(__METHOD__ . ' created', ['id' => $op->id]);
            return back()->with('success', 'Other payment successfully created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' creation failed', [
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __METHOD__);
        }
    }

    public function edit(Request $req, int|string $id): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(),
            'id'      => $id
        ]);
        if ($r = self::authorizePerm($req, 'edit other payment')) {
            return $r;
        }
        try {
            $op = OtherPayment::findOrFail($id);
            if ($op->created_by !== $req->user()->creatorId()) {
                Log::warning(__METHOD__ . ' unauthorized access', [
                    UsersConstants::COL_USER_ID => $req->user()->id,
                    'op_id'   => $id
                ]);
                return defaultPermissionDenial($req, new AuthorizationException, __METHOD__);
            }
            Log::info(__METHOD__ . ' loaded for edit', ['id' => $id]);
            return response()->view(ViewsConstants::OT_PAY . '.' . __FUNCTION__, [
                'otherpayment' => $op,
                'otherpaytypes' => OtherPayment::$otherPaymentType,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error(__METHOD__ . ' record not found', [
                'id'    => $id,
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __METHOD__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __METHOD__);
        }
    }

    public function update(Request $req, int|string $id): RedirectResponse|JsonResponse|null
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(),
            'id'      => $id,
            'input'   => $req->all()
        ]);
        if ($r = self::authorizePerm($req, 'edit other payment'))
            return $r;
        if ($r = self::validateInput($req))
            return $r;
        DB::beginTransaction();
        try {
            $op = OtherPayment::findOrFail($id);
            if ($op->created_by !== $req->user()->creatorId()) {
                Log::warning(__METHOD__ . ' unauthorized update', [
                    UsersConstants::COL_USER_ID => $req->user()->id,
                    'op_id'   => $id
                ]);
                throw new AuthorizationException;
            }
            $updates = $req->only(['title', 'type', 'amount']);
            Log::info(__METHOD__ . ' updating', ['id' => $id, 'updates' => $updates]);
            $op->update($updates);
            DB::commit();
            Log::info(__METHOD__ . ' updated', ['id' => $id]);
            return back()->with('success', 'Other payment successfully updated.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            return defaultPermissionDenial($req, $e, __METHOD__);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' update failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __METHOD__);
        }
    }

    public function destroy(Request $req, int|string $id): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID => Auth::id(),
            'id'      => $id
        ]);
        if ($r = self::authorizePerm($req, 'delete other payment'))
            return $r;
        DB::beginTransaction();
        try {
            $op = OtherPayment::findOrFail($id);
            if ($op->created_by !== $req->user()->creatorId()) {
                Log::warning(__METHOD__ . ' unauthorized delete', [
                    UsersConstants::COL_USER_ID => $req->user()->id,
                    'op_id'   => $id
                ]);
                throw new AuthorizationException;
            }
            Log::info(__METHOD__ . ' deleting', ['id' => $id]);
            $op->delete();
            DB::commit();
            Log::info(__METHOD__ . ' deleted', ['id' => $id]);
            return back()->with('success', 'Other payment successfully deleted.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            return defaultPermissionDenial($req, $e, __METHOD__);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' delete failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __METHOD__);
        }
    }

    private static function authorizePerm(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        $user = $req->user();
        if ($user?->can($perm)) {
            Log::info(__METHOD__ . ' permission granted', [
                UsersConstants::COL_USER_ID => $user?->id,
                'perm' => $perm
            ]);
            return null;
        }
        Log::warning(__METHOD__ . ' permission denied', [
            UsersConstants::COL_USER_ID => $user?->id,
            'perm' => $perm
        ]);
        return defaultPermissionDenial(
            $req,
            new \Illuminate\Auth\Access\AuthorizationException($perm),
            __METHOD__
        );
    }

    private static function validateInput(Request $req): RedirectResponse|JsonResponse|null
    {
        $rules = [
            UsersConstants::COL_EMP_ID => 'required|exists:employees,id',
            'title'       => 'required|string',
            'amount'      => 'required|numeric',
        ];
        $v = Validator::make($req->all(), $rules);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', [
                'errors' => $v->errors()->all()
            ]);
            return redirect()->back()
                ->with('error', $v->errors()->first());
        }
        Log::info(__METHOD__ . ' validation passed', [
            'input' => $req->only(array_keys($rules))
        ]);
        return null;
    }
}
