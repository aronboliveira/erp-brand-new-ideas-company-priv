<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{CreditNote, Invoice, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log, Validator};
use Throwable;

final class CreditNoteController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $request): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@index start', [
            'ip' => $request->ip(),
            'query' => $request->query()
        ]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@index abort: not logged in');
            return $u;
        }
        $user = $u;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_CRD, ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@index abort: permission denied', [
                'user_id' => $user?->id
            ]);
            return $redirect;
        }
        try {
            $invoices = Invoice::where('created_by', $user?->creatorId())->get();
            Log::info(get_class($this) . '@index loaded invoices', [
                'count'   => $invoices->count(),
                'user_id' => $user?->id
            ]);
            return response()->view(ViewsConstants::CRD_NT . '.index', compact('invoices'));
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@index error', [
                'exception' => $e->getMessage(),
                'user_id'   => $user?->id
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::index'
            );
        }
    }

    public function create(Request $request, string|int $invoiceId): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@create start', ['invoiceId' => $invoiceId]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@create abort: not logged in');
            return $u;
        }
        $user = $u;
        if (($redirect = self::guard($request, 'create credit note', ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@create abort: permission denied', [
                'user_id' => $user?->id
            ]);
            return $redirect;
        }
        try {
            $invoiceDue = Invoice::findOrFail($invoiceId);
            Log::info(get_class($this) . '@create loaded invoice', [
                'invoiceId' => $invoiceId,
                'due'       => $invoiceDue->getDue()
            ]);
            return response()->view(ViewsConstants::CRD_NT . '.create', compact('invoiceDue', 'invoiceId'));
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@create error', [
                'exception' => $e->getMessage(),
                'invoiceId' => $invoiceId
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::create'
            );
        }
    }

    public function store(Request $request, string|int $invoiceId): RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@store start', [
            'invoiceId' => $invoiceId,
            'input'     => $request->only(['amount', 'date'])
        ]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@store abort: not logged in');
            return $u;
        }
        $user = $u;
        if (($redirect = self::guard($request, 'create credit note', ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@store abort: permission denied', [
                'user_id' => $user?->id
            ]);
            return $redirect;
        }
        $v = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'date'   => 'required|date',
        ]);
        if ($v->fails()) {
            Log::warning(get_class($this) . '@store validation failed', [
                'errors'    => $v->errors()->all(),
                'invoiceId' => $invoiceId
            ]);
            return redirect()->back()->with('error', $v->errors()->first());
        }
        try {
            DB::transaction(function () use ($request, $invoiceId, $user) {
                $invoice = Invoice::findOrFail($invoiceId);
                $due    = $invoice->getDue();
                $amt    = $request->input('amount');
                if ($amt > $due)
                    throw new \RuntimeException(
                        'Maximum ' . $user?->priceFormat($due) . ' credit limit of this invoice.'
                    );
                $data = Arr::only($request->all(), ['date', 'amount', 'description']);
                $data['invoice'] = $invoiceId;
                $data['customer'] = $invoice->customer_id;
                CreditNote::create($data);
                Utility::updateUserBalance(
                    'customer',
                    $invoice->customer_id,
                    $amt,
                    'debit'
                );

                Log::info(get_class($this) . '@store credit note created', [
                    'invoiceId' => $invoiceId,
                    'amount'    => $amt
                ]);
            });

            return back()->with('success', 'Credit Note successfully created.');
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@store error', [
                'exception' => $e->getMessage(),
                'invoiceId' => $invoiceId
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::store'
            );
        }
    }

    public function edit(Request $request, string|int $invoiceId, string|int $creditNoteId): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@edit start', [
            'invoiceId'    => $invoiceId,
            'creditNoteId' => $creditNoteId
        ]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@edit abort: not logged in');
            return $u;
        }
        if (($redirect = self::guard($request, 'edit credit note', ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@edit abort: permission denied');
            return $redirect;
        }
        try {
            $creditNote = CreditNote::findOrFail($creditNoteId);
            Log::info(get_class($this) . '@edit loaded credit note', ['id' => $creditNoteId]);
            return response()->view(ViewsConstants::CRD_NT . '.edit', compact('creditNote'));
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@edit error', [
                'exception'    => $e->getMessage(),
                'creditNoteId' => $creditNoteId
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::edit'
            );
        }
    }

    public function update(Request $request, string|int $invoiceId, string|int $creditNoteId): RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@update start', [
            'invoiceId'    => $invoiceId,
            'creditNoteId' => $creditNoteId,
            'input'        => $request->only(['amount', 'date'])
        ]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@update abort: not logged in');
            return $u;
        }
        $user = $u;
        if (($redirect = self::guard($request, 'edit credit note', ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@update abort: permission denied');
            return $redirect;
        }
        $v = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'date'   => 'required|date',
        ]);
        if ($v->fails()) {
            Log::warning(get_class($this) . '@update validation failed', [
                'errors'        => $v->errors()->all(),
                'creditNoteId'  => $creditNoteId
            ]);
            return redirect()->back()->with('error', $v->errors()->first());
        }

        try {
            DB::transaction(function () use ($request, $invoiceId, $creditNoteId, $user) {
                $invoice = Invoice::findOrFail($invoiceId);
                $credit = CreditNote::findOrFail($creditNoteId);
                $max    = $invoice->getDue() + $credit->amount;
                $amt    = $request->input('amount');

                if ($amt > $max) {
                    throw new \RuntimeException(
                        'Maximum ' . $user?->priceFormat($max) . ' credit limit of this invoice.'
                    );
                }

                // roll back old
                Utility::updateUserBalance(
                    'customer',
                    $invoice->customer_id,
                    $credit->amount,
                    'credit'
                );

                $credit->update(Arr::only($request->all(), ['date', 'amount', 'description']));

                // apply new
                Utility::updateUserBalance(
                    'customer',
                    $invoice->customer_id,
                    $amt,
                    'debit'
                );

                Log::info(get_class($this) . '@update credit note updated', [
                    'creditNoteId' => $creditNoteId,
                    'newAmount'    => $amt
                ]);
            });

            return back()->with('success', 'Credit Note successfully updated.');
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@update error', [
                'exception'    => $e->getMessage(),
                'creditNoteId' => $creditNoteId
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::update'
            );
        }
    }

    public function destroy(Request $request, string|int $invoiceId, string|int $creditNoteId): RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@destroy start', [
            'invoiceId'    => $invoiceId,
            'creditNoteId' => $creditNoteId
        ]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@destroy abort: not logged in');
            return $u;
        }
        if (($redirect = self::guard($request, 'delete credit note', ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@destroy abort: permission denied');
            return $redirect;
        }
        try {
            DB::transaction(function () use ($creditNoteId) {
                $cn      = CreditNote::findOrFail($creditNoteId);
                $customer = $cn->customer;
                $amount  = $cn->amount;
                $cn->delete();
                Utility::updateUserBalance(
                    'customer',
                    $customer,
                    $amount,
                    'credit'
                );
                Log::info(get_class($this) . '@destroy credit note deleted', [
                    'creditNoteId' => $creditNoteId
                ]);
            });
            return back()->with('success', 'Credit Note successfully deleted.');
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@destroy error', [
                'exception'    => $e->getMessage(),
                'creditNoteId' => $creditNoteId
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::destroy'
            );
        }
    }

    public function customCreate(Request $request): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@customCreate start');
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@customCreate abort: not logged in');
            return $u;
        }
        $user = $u;
        if (($redirect = self::guard($request, 'create credit note', ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@customCreate abort: permission denied');
            return $redirect;
        }
        try {
            $invoices = Invoice::where('created_by', $user?->creatorId())
                ->pluck('invoice_id', 'id');
            Log::info(get_class($this) . '@customCreate loaded invoices', [
                'count' => $invoices->count()
            ]);
            return response()->view(ViewsConstants::CRD_NT . '.custom_create', compact('invoices'));
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@customCreate error', [
                'exception' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::customCreate'
            );
        }
    }

    public function customStore(Request $request): RedirectResponse|JsonResponse|null
    {
        Log::info(get_class($this) . '@customStore start', [
            'input' => $request->only(['invoice', 'amount', 'date'])
        ]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            Log::warning(get_class($this) . '@customStore abort: not logged in');
            return $u;
        }
        $user = $u;
        if (($redirect = self::guard($request, 'create credit note', ViewsConstants::CRD_NT . '.index')) !== true) {
            Log::warning(get_class($this) . '@customStore abort: permission denied');
            return $redirect;
        }
        $v = Validator::make($request->all(), [
            'invoice' => 'required|numeric',
            'amount'  => 'required|numeric',
            'date'    => 'required|date',
        ]);
        if ($v->fails()) {
            Log::warning(get_class($this) . '@customStore validation failed', [
                'errors' => $v->errors()->all()
            ]);
            return redirect()->back()->with('error', $v->errors()->first());
        }
        try {
            DB::transaction(function () use ($request, $user) {
                $invoice = Invoice::findOrFail($request->invoice);
                $due    = $invoice->getDue();
                $amt    = $request->amount;
                if ($amt > $due) {
                    throw new \RuntimeException(
                        'Maximum ' . $user?->priceFormat($due) . ' credit limit of this invoice.'
                    );
                }

                $data = Arr::only($request->all(), ['invoice', 'date', 'amount', 'description']);
                $data['customer'] = $invoice->customer_id;

                CreditNote::create($data);
                Utility::updateUserBalance(
                    'customer',
                    $invoice->customer_id,
                    $amt,
                    'debit'
                );

                Log::info(get_class($this) . '@customStore created credit note', [
                    'invoiceId' => $request->invoice,
                    'amount'    => $amt
                ]);
            });

            return back()->with('success', 'Credit Note successfully created.');
        } catch (Throwable $e) {
            Log::error(get_class($this) . '@customStore error', [
                'exception' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::customStore'
            );
        }
    }

    public function getInvoice(Request $request): JsonResponse
    {
        Log::info(get_class($this) . '@getInvoice start', ['id' => $request->id]);
        $due = Invoice::findOrFail($request->id)->getDue();
        Log::info(get_class($this) . '@getInvoice result', ['due' => $due]);
        return response()->json(compact('due'));
    }
}
