<?php

namespace App\Http\Controllers\Bills;

use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Concerns\HandlesFinanceReliability;

use App\Config\Constants\{
    MiddlewaresConstants as MWC,
    PermissionsConstants as PMC,
    ViewsConstants as VW
};
use App\Models\{CreditNote, Invoice, OperationLedger, Utility};
use App\Services\Reliability\{CriticalOperationService, ReliabilityPolicy};
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
    use HandlesFinanceReliability;

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
                $financeOperation = $this->runFinanceReliabilityOperation('finance.credit_note.create', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($request, $invoiceId, $user, $action, $base): array {
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
                    $note = CreditNote::create($data);
                    $this->logExecutionTime($dataStart, $action, 'createCreditNote');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('customer', $invoice->customer_id ?? 0, $amt, 'debit');
                    $this->logExecutionTime($balStart, $action, 'updateUserBalance');
                    Log::info("[{$base}::{$action}] credit note created", ['invoice_id' => $invoiceId, 'amount' => $amt]);
                    $payload = $this->creditNoteReliabilityPayload($note, $invoice, 'credit_note');
                    $operations->recordStep($ledger, 'finance.credit_note.persisted', 'Persist credit note and customer balance', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Create credit note',
                    'subject_type' => Invoice::class,
                    'subject_id' => (string) $invoiceId,
                    'actor_id' => $request->user()?->id,
                    'context' => ['invoice_id' => (string) $invoiceId, 'amount' => (float) ($request->amount ?? 0)],
                    'event_type' => 'finance.credit_note.created',
                    'post_write_validation' => true,
                    'payload' => fn(array $result): array => $result,
                ]);
                $dispatchReport = $this->dispatchFinanceReliabilityOutbox($financeOperation);
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()
                    ->with('success', 'Credit Note successfully created.')
                    ->with('reliability_operation', $this->financeReliabilityClientPayload($financeOperation, $dispatchReport));
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
                $financeOperation = $this->runFinanceReliabilityOperation('finance.credit_note.update', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($request, $invoiceId, $creditNoteId, $user, $action, $base): array {
                    $findInvStart = microtime(true);
                    $invoice = Invoice::findOrFail($invoiceId);
                    $this->logExecutionTime($findInvStart, $action, 'findInvoice');
                    $findCnStart = microtime(true);
                    $credit = CreditNote::findOrFail($creditNoteId);
                    $this->logExecutionTime($findCnStart, $action, 'findCreditNote');
                    $calcStart = microtime(true);
                    $oldAmt = (float) ($credit->amount ?? 0);
                    $max = ($invoice->getDue() ?? 0) + $oldAmt;
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
                    $payload = $this->creditNoteReliabilityPayload($credit, $invoice, 'credit_note_update');
                    $operations->recordStep($ledger, 'finance.credit_note.updated', 'Update credit note and customer balance', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => array_merge($payload, ['previous_amount' => $oldAmt]),
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Update credit note',
                    'subject_type' => CreditNote::class,
                    'subject_id' => (string) $creditNoteId,
                    'actor_id' => $request->user()?->id,
                    'context' => ['invoice_id' => (string) $invoiceId, 'credit_note_id' => (string) $creditNoteId, 'amount' => (float) ($request->amount ?? 0)],
                    'event_type' => 'finance.credit_note.updated',
                    'post_write_validation' => true,
                    'payload' => fn(array $result): array => $result,
                ]);
                $dispatchReport = $this->dispatchFinanceReliabilityOutbox($financeOperation);
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()
                    ->with('success', 'Credit Note successfully updated.')
                    ->with('reliability_operation', $this->financeReliabilityClientPayload($financeOperation, $dispatchReport));
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
                $financeOperation = $this->runFinanceReliabilityOperation('finance.credit_note.delete', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($invoiceId, $creditNoteId, $action, $base): array {
                    $findStart = microtime(true);
                    $cn = CreditNote::findOrFail($creditNoteId);
                    $invoice = Invoice::findOrFail($invoiceId);
                    $payload = $this->creditNoteReliabilityPayload($cn, $invoice, 'credit_note_reversal');
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
                    $operations->recordStep($ledger, 'finance.credit_note.deleted', 'Delete credit note and reverse customer balance', [
                        'step_type' => 'db_write',
                        'sequence' => 50,
                        'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                        'payload' => $payload,
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $payload;
                }, [
                    'summary' => 'Delete credit note',
                    'subject_type' => CreditNote::class,
                    'subject_id' => (string) $creditNoteId,
                    'actor_id' => $request->user()?->id,
                    'context' => ['invoice_id' => (string) $invoiceId, 'credit_note_id' => (string) $creditNoteId],
                    'event_type' => 'finance.credit_note.deleted',
                    'post_write_validation' => true,
                    'payload' => fn(array $result): array => $result,
                ]);
                $dispatchReport = $this->dispatchFinanceReliabilityOutbox($financeOperation);
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()
                    ->with('success', 'Credit Note successfully deleted.')
                    ->with('reliability_operation', $this->financeReliabilityClientPayload($financeOperation, $dispatchReport));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'credit_note_id' => $creditNoteId, 'invoice_id' => $invoiceId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId, 'credit_note_id' => $creditNoteId]);
    }

    public const CST_CRT = 'customCreate';

    /**
     * @return array<string, mixed>
     */
    private function creditNoteReliabilityPayload(CreditNote $note, Invoice $invoice, string $direction): array
    {
        return [
            'credit_note_id' => (string) $note->id,
            'note_id' => (string) $note->id,
            'invoice_id' => (string) $invoice->id,
            'invoice' => (string) $invoice->id,
            'customer_id' => $invoice->customer_id ? (string) $invoice->customer_id : null,
            'amount' => (float) ($note->amount ?? 0),
            'date' => (string) ($note->date ?? ''),
            'direction' => $direction,
        ];
    }

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
            return $this->store($request, $request->input('invoice'));
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
