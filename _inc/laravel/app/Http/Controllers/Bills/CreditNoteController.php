<?php

namespace App\Http\Controllers\Bills;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{
    MiddlewaresConstants as MWC,
    PermissionsConstants as PMC,
    ViewsConstants as VW
};
use App\Models\{CreditNote, Invoice, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;
use Throwable;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\DefinesResourceActions;
final class CreditNoteController extends Controller
{
	use DefinesResourceActions;

    use ChecksLogin;
    use ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MWC::AUTH);
    }

    public function index(Request $request): Response|RedirectResponse|JsonResponse|View|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::CRD_NT . '.index';
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['ip' => $request->ip(), 'query' => $request->query(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($request, PMC::MNG_CRD, VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied", ['user_id' => $user?->id]);
                return $guard;
            }
            try {
                $fetchStart = microtime(true);
                $invoices = Invoice::where('created_by', $user?->creatorId())->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchInvoices');
                Log::info("[{$base}::{$action}] loaded invoices", ['count' => $invoices->count(), 'user_id' => $user?->id]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['invoices']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('invoices'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'user_id' => $user?->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request, string|int $invoiceId): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::CRD_NT . '.create';
        return $this->measureProfile($action, function () use ($request, $invoiceId, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['invoice_id' => $invoiceId, 'method' => $method, 'ip' => $request->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($request, 'create credit note', VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied", ['user_id' => $user?->id]);
                return $guard;
            }
            try {
                $findStart = microtime(true);
                $invoiceDue = Invoice::findOrFail($invoiceId);
                $this->logExecutionTime($findStart, $action, 'findInvoice');
                Log::info("[{$base}::{$action}] loaded invoice", ['invoice_id' => $invoiceId, 'due' => $invoiceDue->getDue()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['invoiceDue', 'invoiceId']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('invoiceDue', 'invoiceId'));
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'invoice_id' => $invoiceId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId]);
    }

    public function store(Request $request, string|int $invoiceId): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $invoiceId, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['invoice_id' => $invoiceId, 'input' => $request->only(['amount', 'date']), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($request, 'create credit note', VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied", ['user_id' => $user?->id]);
                return $guard;
            }
            $valStart = microtime(true);
            $request->validate(['amount' => 'required|numeric', 'date' => 'required|date']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $invoiceId, $user, $action, $base) {
                    $findStart = microtime(true);
                    $invoice = Invoice::findOrFail($invoiceId);
                    $this->logExecutionTime($findStart, $action, 'findInvoice');
                    $dueStart = microtime(true);
                    $due = $invoice->getDue();
                    $amt = (float) ($request->input('amount') ?? 0);
                    $this->logExecutionTime($dueStart, $action, 'computeDue');
                    if ($amt > $due) throw new \RuntimeException('Maximum ' . ($user?->priceFormat($due) ?? (string) $due) . ' credit limit of this invoice.');
                    $dataStart = microtime(true);
                    $data = Arr::only($request->all(), ['date', 'amount', 'description']);
                    $data['invoice'] = $invoiceId;
                    $data['customer'] = $invoice->customer_id ?? null;
                    CreditNote::create($data);
                    $this->logExecutionTime($dataStart, $action, 'createCreditNote');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('customer', $invoice->customer_id ?? 0, $amt, 'debit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    Log::info("[{$base}::{$action}] credit note created", ['invoice_id' => $invoiceId, 'amount' => $amt]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', 'Credit Note successfully created.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'invoice_id' => $invoiceId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId]);
    }

    public function edit(Request $request, string|int $invoiceId, string|int $creditNoteId): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::CRD_NT . '.edit';
        return $this->measureProfile($action, function () use ($request, $invoiceId, $creditNoteId, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['invoice_id' => $invoiceId, 'credit_note_id' => $creditNoteId, 'method' => $method, 'ip' => $request->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $guard = self::guard($request, 'edit credit note', VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied", ['user_id' => $u?->id ?? null]);
                return $guard;
            }
            try {
                $findStart = microtime(true);
                $creditNote = CreditNote::findOrFail($creditNoteId);
                $this->logExecutionTime($findStart, $action, 'findCreditNote');
                Log::info("[{$base}::{$action}] loaded credit note", ['id' => $creditNoteId]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['creditNote']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('creditNote'));
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'credit_note_id' => $creditNoteId, 'invoice_id' => $invoiceId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId, 'credit_note_id' => $creditNoteId]);
    }

    public function update(Request $request, string|int $invoiceId, string|int $creditNoteId): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $invoiceId, $creditNoteId, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['invoice_id' => $invoiceId, 'credit_note_id' => $creditNoteId, 'input' => $request->only(['amount', 'date']), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($request, 'edit credit note', VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied", ['user_id' => $user?->id ?? null]);
                return $guard;
            }
            $valStart = microtime(true);
            $request->validate(['amount' => 'required|numeric', 'date' => 'required|date']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $invoiceId, $creditNoteId, $user, $action, $base) {
                    $findInvStart = microtime(true);
                    $invoice = Invoice::findOrFail($invoiceId);
                    $this->logExecutionTime($findInvStart, $action, 'findInvoice');
                    $findCnStart = microtime(true);
                    $credit = CreditNote::findOrFail($creditNoteId);
                    $this->logExecutionTime($findCnStart, $action, 'findCreditNote');
                    $calcStart = microtime(true);
                    $max = ($invoice->getDue() ?? 0) + ($credit->amount ?? 0);
                    $amt = (float) ($request->input('amount') ?? 0);
                    $this->logExecutionTime($calcStart, $action, 'computeMax');
                    if ($amt > $max) throw new \RuntimeException('Maximum ' . ($user?->priceFormat($max) ?? (string) $max) . ' credit limit of this invoice.');
                    $rbStart = microtime(true);
                    Utility::updateUserBalance('customer', $invoice->customer_id ?? 0, $credit->amount ?? 0, 'credit');
                    $this->logExecutionTime($rbStart, $action, 'rollbackOldBalance');
                    $updStart = microtime(true);
                    $credit->update(Arr::only($request->all(), ['date', 'amount', 'description']));
                    $this->logExecutionTime($updStart, $action, 'updateCreditNote');
                    $apStart = microtime(true);
                    Utility::updateUserBalance('customer', $invoice->customer_id ?? 0, $amt, 'debit');
                    $this->logExecutionTime($apStart, $action, 'applyNewBalance');
                    Log::info("[{$base}::{$action}] credit note updated", ['credit_note_id' => $creditNoteId, 'new_amount' => $amt]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', 'Credit Note successfully updated.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'credit_note_id' => $creditNoteId, 'invoice_id' => $invoiceId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId, 'credit_note_id' => $creditNoteId]);
    }

    public function destroy(Request $request, string|int $invoiceId, string|int $creditNoteId): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $invoiceId, $creditNoteId, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['invoice_id' => $invoiceId, 'credit_note_id' => $creditNoteId, 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $guard = self::guard($request, 'delete credit note', VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied");
                return $guard;
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($creditNoteId, $action, $base) {
                    $findStart = microtime(true);
                    $cn = CreditNote::findOrFail($creditNoteId);
                    $this->logExecutionTime($findStart, $action, 'findCreditNote');
                    $customerId = $cn->customer_id ?? $cn->customer ?? 0;
                    $amount = (float) ($cn->amount ?? 0);
                    $delStart = microtime(true);
                    $cn->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteCreditNote');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('customer', $customerId, $amount, 'credit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    Log::info("[{$base}::{$action}] credit note deleted", ['credit_note_id' => $creditNoteId, 'customer_id' => $customerId, 'amount' => $amount]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', 'Credit Note successfully deleted.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'credit_note_id' => $creditNoteId, 'invoice_id' => $invoiceId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId, 'credit_note_id' => $creditNoteId]);
    }

    public const CST_CRT = 'customCreate';
    public function customCreate(Request $request): Response|RedirectResponse|JsonResponse|View|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::CRD_NT . '.custom_create';
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['method' => $method, 'ip' => $request->ip()]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($request, 'create credit note', VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied", ['user_id' => $user?->id]);
                return $guard;
            }
            try {
                $fetchStart = microtime(true);
                $invoices = Invoice::where('created_by', $user?->creatorId())->pluck('invoice_id', 'id');
                $this->logExecutionTime($fetchStart, $action, 'fetchInvoices');
                Log::info("[{$base}::{$action}] loaded invoices", ['count' => $invoices->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['invoices']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('invoices'));
                $this->logExecutionTime($renderStart, $action, 'renderCustomCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const CST_STR = 'customStore';
    public function customStore(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['input' => $request->only(['invoice', 'amount', 'date']), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] abort: not logged in");
                return $u;
            }
            $user = $u;
            $guard = self::guard($request, 'create credit note', VW::CRD_NT . '.index');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] abort: permission denied");
                return $guard;
            }
            $valStart = microtime(true);
            $request->validate(['invoice' => 'required|numeric', 'amount' => 'required|numeric', 'date' => 'required|date']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($request, $user, $action, $base) {
                    $findStart = microtime(true);
                    $invoice = Invoice::findOrFail($request->input('invoice'));
                    $this->logExecutionTime($findStart, $action, 'findInvoice');
                    $calcStart = microtime(true);
                    $due = $invoice->getDue() ?? 0;
                    $amt = (float) ($request->input('amount') ?? 0);
                    $this->logExecutionTime($calcStart, $action, 'computeDue');
                    if ($amt > $due) throw new \RuntimeException('Maximum ' . ($user?->priceFormat($due) ?? (string) $due) . ' credit limit of this invoice.');
                    $dataStart = microtime(true);
                    $data = Arr::only($request->all(), ['invoice', 'date', 'amount', 'description']);
                    $data['customer'] = $invoice->customer_id ?? null;
                    CreditNote::create($data);
                    $this->logExecutionTime($dataStart, $action, 'createCreditNote');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('customer', $invoice->customer_id ?? 0, $amt, 'debit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    Log::info("[{$base}::{$action}] created credit note", ['invoice_id' => $request->input('invoice'), 'amount' => $amt]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', 'Credit Note successfully created.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const GET_INV = 'getInvoice';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function getInvoice(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['id' => $request->input('id'), 'method' => $method, 'ip' => $request->ip()]);
            $u = self::_checkLogin(true);
            if ($u === false) {
                Log::warning("[{$base}::{$action}] unauthenticated");
                return response()->json(['error' => 'unauthenticated'], 401);
            }
            $guard = self::guard($request, 'create credit note', null, fn() => response()->json(['error' => 'unauthorized'], 403), false);
            if ($guard !== true) return $guard;
            try {
                $id = $request->input('id') ?? null;
                if (empty($id)) {
                    Log::warning("[{$base}::{$action}] empty id");
                    return response()->json(['due' => 0]);
                }
                $findStart = microtime(true);
                $invoice = Invoice::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findInvoice');
                $dueStart = microtime(true);
                $due = $invoice->getDue() ?? 0;
                $this->logExecutionTime($dueStart, $action, 'computeDue');
                Log::info("[{$base}::{$action}] result", ['due' => $due]);
                return response()->json(compact('due'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'id' => $request->input('id') ?? null]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }
}
