<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants,
    ViewsConstants as VW,
};
use App\Models\{Bill, DebitNote, Utility};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{DB, Log, Route, View as ViewFacade};
use Illuminate\View\View;

final class DebitNoteController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $req): View|Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::DBT_NT . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method, 'ip' => $req->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($req, PermissionsConstants::MNG_DBT, VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null]);
                return $guard;
            }
            try {
                $fetchStart = microtime(true);
                $bills = Bill::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchBills');
                Log::info("[{$base}::{$action}] bills fetched", [UsersConstants::COL_USER_ID => $user?->id ?? null, 'bill_count' => $bills->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bills']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('bills'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(string|int $billId, Request $req): View|Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::DBT_NT . '.create';
        return $this->measureProfile($action, function () use ($billId, $req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'bill_id' => $billId, 'method' => $method, 'ip' => $req->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $guard = self::guard($req, 'create debit note', VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $u?->id ?? null]);
                return $guard;
            }
            try {
                $fetchStart = microtime(true);
                $bill = self::_bill($billId);
                $this->logExecutionTime($fetchStart, $action, 'fetchBill');
                Log::info("[{$base}::{$action}] ready", ['bill_id' => $bill->id ?? null]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['billDue', 'bill_id']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['billDue' => $bill, 'bill_id' => $billId]);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['bill_id' => $billId, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId]);
    }

    public function store(Request $req, string|int $billId): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $billId, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'bill_id' => $billId, 'input' => $req->only(['amount', 'date', 'description']), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($req, 'create debit note', VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null]);
                return $guard;
            }
            $valStart = microtime(true);
            $req->validate(['amount' => 'required|numeric', 'date' => 'required|date', 'description' => 'nullable|string']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                $resp = DB::transaction(function () use ($req, $billId, $user, $action, $base) {
                    $fetchStart = microtime(true);
                    $bill = self::_bill($billId);
                    $this->logExecutionTime($fetchStart, $action, 'fetchBill');
                    $calcStart = microtime(true);
                    $due = $bill->getDue() ?? 0;
                    $amt = (float) ($req->input('amount') ?? 0);
                    $this->logExecutionTime($calcStart, $action, 'computeDue');
                    if ($amt > $due) {
                        Log::warning("[{$base}::{$action}] amount exceeds due", [UsersConstants::COL_USER_ID => $user?->id ?? null, 'bill_id' => $billId, 'amount' => $amt, 'due' => $due]);
                        return back()->with('error', 'Maximum ' . ($user?->priceFormat($due) ?? (string) $due) . ' credit limit of this bill.');
                    }
                    $createStart = microtime(true);
                    $note = DebitNote::create([
                        'bill' => $billId,
                        'vendor' => $bill->vendor_id ?? 0,
                        'date' => $req->input('date'),
                        'amount' => $amt,
                        'description' => $req->input('description') ?? null,
                        DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createDebitNote');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $bill->vendor_id ?? 0, $amt, 'credit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    Log::info("[{$base}::{$action}] created", ['note_id' => $note->id ?? null, 'bill_id' => $billId, UsersConstants::COL_USER_ID => $user?->id ?? null, 'amount' => $amt]);
                    return back()->with('success', 'Debit Note successfully created.');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['bill_id' => $billId, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId]);
    }

    public function edit(string|int $billId, string|int $noteId, Request $req): View|Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::DBT_NT . '.' . $action;
        return $this->measureProfile($action, function () use ($billId, $noteId, $req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'bill_id' => $billId, 'note_id' => $noteId, 'method' => $method, 'ip' => $req->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $guard = self::guard($req, 'edit debit note', VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $u?->id ?? null]);
                return $guard;
            }
            try {
                $findStart = microtime(true);
                $note = DebitNote::findOrFail($noteId);
                $this->logExecutionTime($findStart, $action, 'findDebitNote');
                Log::info("[{$base}::{$action}] ready", ['note_id' => $note->id ?? null]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['note']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('note'));
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['note_id' => $noteId, 'bill_id' => $billId, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId, 'note_id' => $noteId]);
    }

    public function update(Request $req, string|int $billId, string|int $noteId): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $billId, $noteId, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'bill_id' => $billId, 'note_id' => $noteId, 'input' => $req->only(['amount', 'date', 'description']), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($req, 'edit debit note', VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null]);
                return $guard;
            }
            $valStart = microtime(true);
            $req->validate(['amount' => 'required|numeric', 'date' => 'required|date', 'description' => 'nullable|string']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                $resp = DB::transaction(function () use ($req, $billId, $noteId, $user, $action, $base) {
                    $fetchStart = microtime(true);
                    $bill = self::_bill($billId);
                    $note = DebitNote::findOrFail($noteId);
                    $this->logExecutionTime($fetchStart, $action, 'fetchBillAndNote');
                    $calcStart = microtime(true);
                    $currentDue = $bill->getDue() ?? 0;
                    $oldAmt = (float) ($note->amount ?? 0);
                    $amt = (float) ($req->input('amount') ?? 0);
                    $max = $currentDue + $oldAmt;
                    $this->logExecutionTime($calcStart, $action, 'computeLimits');
                    if ($amt > $max) {
                        Log::warning("[{$base}::{$action}] amount exceeds limit", ['bill_id' => $billId, 'note_id' => $noteId, 'requested' => $amt, 'max' => $max]);
                        return back()->with('error', 'Maximum ' . ($user?->priceFormat($max) ?? (string) $max) . ' credit limit of this bill.');
                    }
                    $rbStart = microtime(true);
                    Utility::updateUserBalance('vendor', $bill->vendor_id ?? 0, $oldAmt, 'debit');
                    $this->logExecutionTime($rbStart, $action, 'rollbackOldBalance');
                    $updStart = microtime(true);
                    $note->update(['date' => $req->input('date'), 'amount' => $amt, 'description' => $req->input('description') ?? null]);
                    $this->logExecutionTime($updStart, $action, 'updateDebitNote');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $bill->vendor_id ?? 0, $amt, 'credit');
                    $this->logExecutionTime($balStart, $action, 'applyNewBalance');
                    Log::info("[{$base}::{$action}] updated", ['note_id' => $noteId, 'old_amount' => $oldAmt, 'new_amount' => $amt, 'bill_id' => $billId]);
                    return back()->with('success', 'Debit Note successfully updated.');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['note_id' => $noteId, 'bill_id' => $billId, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId, 'note_id' => $noteId]);
    }

    public function destroy(string|int $billId, string|int $noteId, Request $req): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($billId, $noteId, $req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'bill_id' => $billId, 'note_id' => $noteId, 'method' => $method, 'ip' => $req->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $guard = self::guard($req, 'delete debit note', VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $u?->id ?? null]);
                return $guard;
            }
            try {
                $txnStart = microtime(true);
                $resp = DB::transaction(function () use ($billId, $noteId, $action, $base) {
                    $findStart = microtime(true);
                    $note = DebitNote::findOrFail($noteId);
                    $this->logExecutionTime($findStart, $action, 'findDebitNote');
                    $billStart = microtime(true);
                    self::_bill($billId);
                    $this->logExecutionTime($billStart, $action, 'fetchBill');
                    $delStart = microtime(true);
                    $note->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteDebitNote');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $note->vendor ?? 0, $note->amount ?? 0, 'debit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    Log::info("[{$base}::{$action}] deleted", ['note_id' => $noteId, 'bill_id' => $billId, 'amount' => $note->amount ?? 0]);
                    return back()->with('success', 'Debit Note successfully deleted.');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['note_id' => $noteId, 'bill_id' => $billId, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId, 'note_id' => $noteId]);
    }

    public const CST_CRT = 'customCreate';
    public function customCreate(Request $req): View|Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::DBT_NT . '.custom_create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'method' => $method, 'ip' => $req->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($req, 'create debit note', VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null]);
                return $guard;
            }
            try {
                $fetchStart = microtime(true);
                $bills = Bill::where([[DatabaseConstants::TABLE_CREATOR, $user?->creatorId()], ['type', 'Bill']])->pluck('bill_id', 'id');
                $this->logExecutionTime($fetchStart, $action, 'fetchBills');
                Log::info("[{$base}::{$action}] bills fetched", [UsersConstants::COL_USER_ID => $user?->id ?? null, 'count' => $bills->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bills']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('bills'));
                $this->logExecutionTime($renderStart, $action, 'renderCustomCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const CST_STR = 'customStore';
    public function customStore(Request $req): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'input_keys' => array_keys($req->all() ?? []), 'method' => $method, 'ip' => $req->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($req, 'create debit note', VW::DBT_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => $user?->id ?? null]);
                return $guard;
            }
            $valStart = microtime(true);
            $req->validate(['bill' => 'required', 'amount' => 'required|numeric', 'date' => 'required|date']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            $billId = $req->input('bill') ?? 0;
            Log::info("[{$base}::{$action}] delegating to store", ['bill' => $billId]);
            $callStart = microtime(true);
            $resp = $this->store($req, $billId);
            $this->logExecutionTime($callStart, $action, 'delegateStore');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const GET_BIL = 'getBill';
    public function getBill(Request $req): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['bill_id' => $req->input('bill_id') ?? null, UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'method' => $method, 'ip' => $req->ip()]);
            $billId = $req->input('bill_id') ?? null;
            if (empty($billId)) {
                Log::warning("[{$base}::{$action}] missing bill_id");
                return response()->json(['error' => 'bill_id is required'], 422);
            }
            try {
                $findStart = microtime(true);
                $bill = self::_bill($billId);
                $this->logExecutionTime($findStart, $action, 'fetchBill');
                $dueStart = microtime(true);
                $due = $bill->getDue() ?? 0;
                $this->logExecutionTime($dueStart, $action, 'computeDue');
                Log::info("[{$base}::{$action}] success", ['bill_id' => $billId, 'due' => $due]);
                return response()->json(['due' => $due]);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] not found", ['bill_id' => $billId, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return response()->json(['error' => 'Bill not found'], 404);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    private static function _deny(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' called', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'permission' => $perm,
            'route'      => $req->path()
        ]);
        if (!$req->user()->can($perm)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [
                UsersConstants::COL_USER_ID    => $req->user()->id,
                'permission' => $perm
            ]);
            return defaultPermissionDenial(
                $req,
                new AuthorizationException($perm),
                __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
            );
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' granted', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'permission' => $perm
        ]);
        return null;
    }

    private static function _bill(string|int $id): Bill
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetching bill', ['bill_id' => $id]);
        $bill = Bill::findOrFail($id);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', ['bill_id' => $bill->id]);
        return $bill;
    }
}
