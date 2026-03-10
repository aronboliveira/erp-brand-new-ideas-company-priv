<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Models\{Employee, OtherPayment};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class OtherPaymentController extends Controller
{

    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public const OT_PAY_CR = 'otherPaymentCreate';

    public function otherPaymentCreate(Request $req, int|string $employeeId): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $employeeId, $action, $method) {
            Log::debug($method . ' start', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, UsersConstants::COL_EMP_ID => $employeeId]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'create other payment', VW::OT_PAY . '.index')) !== true) return $r;
            try {
                $qStart = microtime(true);
                $employee = Employee::findOrFail($employeeId);
                $this->logExecutionTime($qStart, $action, 'findEmployee');
                Log::info($method . ' employee loaded', [UsersConstants::COL_EMP_ID => $employeeId]);
                $viewPath = VW::OT_PAY . '.create';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error($method . ' missing view', ['view_path' => $viewPath]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['employee' => $employee, 'otherpaytype' => OtherPayment::$otherPaymentType]);
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (ModelNotFoundException $e) {
                Log::error($method . ' employee not found', [UsersConstants::COL_EMP_ID => $employeeId, 'error' => $e->getMessage()]);
                return redirect()->back()->with('error', __('Employee not found.'));
            } catch (\Throwable $e) {
                Log::error($method . ' unexpected error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $method);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, UsersConstants::COL_EMP_ID => $employeeId]);
    }

    public function show(Request $request, int|string $id): View|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $id, $action, $class) {
            Log::debug($class . '::' . $action . ' start', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'id' => $id]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($request, 'show other payment', VW::OT_PAY . '.index')) !== true) return $r;
            try {
                $qStart = microtime(true);
                $otherPayment = OtherPayment::findOrFail($id);
                $this->logExecutionTime($qStart, $action, 'findOtherPayment');
                if ($otherPayment->created_by !== ($request->user()?->creatorId() ?? null)) {
                    Log::warning($class . '::' . $action . ' forbidden', [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'otherpayment_id' => $id, 'owner_id' => $otherPayment->created_by]);
                    return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
                }
                Log::info($class . '::' . $action . ' loaded', ['otherpayment_id' => $id]);
                $viewPath = VW::OT_PAY . '.show';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error($class . '::' . $action . ' missing view', ['view_path' => $viewPath]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['otherpayment' => $otherPayment]);
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (ModelNotFoundException $e) {
                Log::warning($class . '::' . $action . ' not found', ['id' => $id, 'error' => $e->getMessage()]);
                return redirect()->back()->with('error', __('Other payment not found.'));
            } catch (\Throwable $e) {
                Log::error($class . '::' . $action . ' error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'otherpayment_id' => $id]);
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            Log::debug($method . ' start', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'input' => $req->all()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'create other payment', VW::OT_PAY . '.index')) !== true) return $r;
            if ($r = self::validateInput($req)) return $r;
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $method) {
                    $data = $req->only([UsersConstants::COL_EMP_ID, 'title', 'type', 'amount']);
                    $data[DatabaseConstants::COL_TABLE_CREATOR] = $req->user()?->creatorId() ?? null;
                    Log::info($method . ' creating OtherPayment', ['data' => $data]);
                    $crtStart = microtime(true);
                    $op = OtherPayment::create($data);
                    $this->logExecutionTime($crtStart, 'store', 'createOtherPayment');
                    Log::info($method . ' created', ['id' => $op->id ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', __('Other payment successfully created.'));
            } catch (\Throwable $e) {
                Log::error($method . ' creation failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $method);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $req, int|string $id): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $id, $action, $method) {
            Log::debug($method . ' start', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'id' => $id]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'edit other payment', VW::OT_PAY . '.index')) !== true) return $r;
            try {
                $qStart = microtime(true);
                $op = OtherPayment::findOrFail($id);
                $this->logExecutionTime($qStart, $action, 'findOtherPayment');
                if ($op->created_by !== ($req->user()?->creatorId() ?? null)) {
                    Log::warning($method . ' unauthorized access', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'op_id' => $id]);
                    return defaultPermissionDenial($req, new \Exception('owner'), $method);
                }
                Log::info($method . ' loaded for edit', ['id' => $id]);
                $viewPath = VW::OT_PAY . '.' . $action;
                if (!ViewFacade::exists($viewPath)) {
                    Log::error($method . ' missing view', ['view_path' => $viewPath]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['otherpayment' => $op, 'otherpaytypes' => OtherPayment::$otherPaymentType]);
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (ModelNotFoundException $e) {
                Log::error($method . ' record not found', ['id' => $id, 'error' => $e->getMessage()]);
                return redirect()->back()->with('error', __('Other payment not found.'));
            } catch (\Throwable $e) {
                Log::error($method . ' unexpected error', ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $method);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'otherpayment_id' => $id]);
    }

    public function update(Request $req, int|string $id): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $id, $action, $method) {
            Log::debug($method . ' start', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'id' => $id, 'input' => $req->all()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'edit other payment', VW::OT_PAY . '.index')) !== true) return $r;
            if ($r = self::validateInput($req)) return $r;
            try {
                $findStart = microtime(true);
                $op = OtherPayment::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findOtherPayment');
                if ($op->created_by !== ($req->user()?->creatorId() ?? null)) {
                    Log::warning($method . ' unauthorized update', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'op_id' => $id]);
                    return defaultPermissionDenial($req, new \Exception('owner'), $method);
                }
                $updates = $req->only(['title', 'type', 'amount']);
                Log::info($method . ' updating', ['id' => $id, 'updates' => $updates]);
                $txnStart = microtime(true);
                DB::transaction(function () use ($op, $updates, $method) {
                    $updStart = microtime(true);
                    $op->update($updates);
                    $this->logExecutionTime($updStart, 'update', 'updateOtherPayment');
                    Log::info($method . ' updated', ['id' => $op->id ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', __('Other payment successfully updated.'));
            } catch (ModelNotFoundException $e) {
                return redirect()->back()->with('error', __('Other payment not found.'));
            } catch (\Throwable $e) {
                Log::error($method . ' update failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $method);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'otherpayment_id' => $id]);
    }

    public function destroy(Request $req, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $id, $action, $method) {
            Log::debug($method . ' start', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'id' => $id]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'delete other payment', VW::OT_PAY . '.index')) !== true) return $r;
            try {
                $findStart = microtime(true);
                $op = OtherPayment::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findOtherPayment');
                if ($op->created_by !== ($req->user()?->creatorId() ?? null)) {
                    Log::warning($method . ' unauthorized delete', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'op_id' => $id]);
                    return defaultPermissionDenial($req, new \Exception('owner'), $method);
                }
                Log::info($method . ' deleting', ['id' => $id]);
                $txnStart = microtime(true);
                DB::transaction(function () use ($op, $method) {
                    $delStart = microtime(true);
                    $op->delete();
                    $this->logExecutionTime($delStart, 'destroy', 'deleteOtherPayment');
                    Log::info($method . ' deleted', ['id' => $op->id ?? null]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', __('Other payment successfully deleted.'));
            } catch (ModelNotFoundException $e) {
                return redirect()->back()->with('error', __('Other payment not found.'));
            } catch (\Throwable $e) {
                Log::error($method . ' delete failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $method);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'otherpayment_id' => $id]);
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

    public function index(Request $req): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $action = __FUNCTION__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($req, $action, $class) {
            if (($u = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $u;
            if (($r = self::guard($req, 'manage other payment')) !== true) return $r;
            try {
                $viewPath = 'other_payments.index';
                if (!\Illuminate\Support\Facades\View::exists($viewPath))
                    return redirect()->route('dashboard')->with('error', 'Other payments index view not found.');
                $payments = \App\Models\Bills\OtherPayment::where('created_by', $u->creatorId())->get();
                return response()->view($viewPath, ['payments' => $payments]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("[$class::$action] failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "$class::$action");
            }
        });
    }

}
