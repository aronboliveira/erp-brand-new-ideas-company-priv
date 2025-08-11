<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BillsConstants,
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Exports\InvoiceExport;
use App\Mail\CustomerInvoiceSend;
use App\Models\{
    BankAccount,
    ChartOfAccount,
    CreditNote,
    Customer,
    CustomField,
    Employee,
    Invoice,
    InvoiceBankTransfer,
    InvoicePayment,
    InvoiceProduct,
    Plan,
    ProductService,
    ProductServiceCategory,
    Products,
    StockReport,
    Transaction,
    User,
    Utility
};
use App\Traits\ChecksLogin;
use GuzzleHttp\Client;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Log,
    Mail,
    Storage,
    Validator
};
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class InvoiceController extends Controller
{
    use ChecksLogin;
    private const SINGULAR = 'invoice';

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(
        Request $req
    ): Response|RedirectResponse|JsonResponse|null {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID       => $req->user()->id,
            PermissionsConstants::CT      => $req->customer,
            'issue_date'    => $req->issue_date,
            'status'        => $req->status
        ]);
        if ($r = self::_authorize($req, PermissionsConstants::MNG_INV))
            return $r;
        try {
            $customers = Customer::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend('Select Customer', '');
            $status = Invoice::$statuses;
            $q = Invoice::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            );
            if ($req->customer) $q->where(
                'customer_id',
                $req->customer
            );
            if ($req->issue_date) {
                $range = count(
                    explode('to', $req->issue_date)
                ) > 1
                    ? explode(' to ', $req->issue_date)
                    : [$req->issue_date, $req->issue_date];
                $q->whereBetween('issue_date', $range);
            }
            if ($req->status) $q->where(
                'status',
                $req->status
            );
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' built query');
            $invoices = $q->get();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', [
                'count' => $invoices->count()
            ]);
            return response()->view(ViewsConstants::INV . '.' . __FUNCTION__, [
                DatabaseConstants::TABLE_INVS => $invoices,
                PermissionsConstants::CT => $customers,
                'status'   => $status
            ]);
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error' => $e->getMessage()
            ]);
            return self::_catch($req, $e);
        }
    }

    public function create(
        Request $req,
        string|int $customer_id = ''
    ): Response|RedirectResponse|JsonResponse|null {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'customer_id' => $customer_id
        ]);
        if ($r = self::_authorize($req, 'create invoice')) {
            return $r;
        }
        try {
            $customFields = CustomField::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )
                ->where('module', self::SINGULAR)
                ->get();
            $invoiceNum = $req->user()->invoiceNumberFormat(
                self::_nextNumber()
            );
            $customers = Customer::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend('Select Customer', '');
            $category = ProductServiceCategory::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )
                ->where('type', 'income')
                ->pluck('name', 'id')
                ->prepend('Select Category', '');
            $products = ProductService::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )
                ->pluck('name', 'id')
                ->prepend('--', '');
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data ready', [
                'fields'      => $customFields->count(),
                DatabaseConstants::TABLE_CUSTOMERS   => $customers->count(),
                'categories'  => $category->count(),
                DatabaseConstants::TABLE_PRODUCTS    => $products->count()
            ]);
            return response()->view(ViewsConstants::INV . '.create', [
                DatabaseConstants::TABLE_CUSTOMERS       => $customers,
                'invoice_number'  => $invoiceNum,
                DatabaseConstants::TABLE_PROD_SERVS => $products,
                'category'        => $category,
                DatabaseConstants::TABLE_CUSTOM_FIELDS    => $customFields,
                'customer_id'      => $customer_id
            ]);
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error' => $e->getMessage()
            ]);
            return self::_catch($req, $e);
        }
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', ['user' => Auth::id()]);
        if ($r = self::_authorize($req, 'create invoice')) return $r;
        if ($r = self::_validate($req->all(), [
            'customer_id' => 'required',
            'issue_date'  => 'required',
            'due_date'    => 'required',
            'category_id' => 'required',
            'items'       => 'required',
        ])) return $r;
        try {
            DB::transaction(function () use ($req) {
                $invData = Arr::only($req->all(), [
                    'customer_id',
                    'issue_date',
                    'due_date',
                    'ref_number',
                    'category_id'
                ]);
                $invData['invoice_id'] = self::_nextNumber();
                $invData['status']    = 0;
                $invData[DatabaseConstants::TABLE_CREATOR] = $req->user()->creatorId();
                $invoice = Invoice::create($invData);
                Log::info(__CLASS__ . '::' . __FUNCTION__ .
                    ' ' . self::SINGULAR . 'created', ['invoice_id' => $invoice->id]);
                CustomField::saveData($invoice, $req->customField);
                foreach ($req->items as $item) {
                    $prodData = Arr::only($item, [
                        'quantity',
                        'tax',
                        'discount',
                        'price',
                        'description'
                    ]);
                    $prodData['invoice_id'] = $invoice->id;
                    $prodData['product_id'] = $item['item'];
                    InvoiceProduct::create($prodData);
                    Utility::totalQuantity(
                        'minus',
                        $item['quantity'],
                        $item['item']
                    );
                }

                $this->_notifyInvoice($invoice, true);
            });

            return redirect()->route(ViewsConstants::INV . '.index')
                ->with('success', ucfirst(self::SINGULAR) . ' successfully created.');
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ .
                ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(
        Request $req,
        string $encId
    ): Response|RedirectResponse|JsonResponse|null {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'enc_id'   => $encId
        ]);
        if ($r = self::_authorize($req, 'edit invoice')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [
                UsersConstants::COL_USER_ID => $req->user()->id
            ]);
            return $r;
        }
        try {
            $id = Crypt::decrypt($encId);
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' decrypted id', ['id' => $id]);
            $invoice = Invoice::findOrFail($id);
            $invoiceNum = $req->user()
                ->invoiceNumberFormat($invoice->invoice_id);
            $customers = Customer::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )->pluck('name', 'id');
            $category = ProductServiceCategory::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )->where('type', 'income')
                ->pluck('name', 'id')
                ->prepend('Select Category', '');
            $products = ProductService::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )->pluck('name', 'id');
            $invoice->customField = CustomField::getData($invoice, self::SINGULAR);
            $customFields = CustomField::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )->where('module', self::SINGULAR)->get();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data ready', [
                'fields' => $customFields->count()
            ]);
            return response()->view(ViewsConstants::INV . '.edit', [
                DatabaseConstants::TABLE_CUSTOMERS        => $customers,
                DatabaseConstants::TABLE_PROD_SERVS => $products,
                self::SINGULAR          => $invoice,
                'invoice_number'   => $invoiceNum,
                'category'         => $category,
                DatabaseConstants::TABLE_CUSTOM_FIELDS     => $customFields,
            ]);
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error' => $e->getMessage()
            ]);
            return self::_catch($req, $e);
        }
    }


    public function update(Request $req, Invoice $invoice): RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ .
            ' started', ['invoice_id' => $invoice->id]);
        if ($r = self::_authorize($req, 'edit invoice')) return $r;
        if ($invoice->created_by !== $req->user()->creatorId())
            return defaultPermissionDenial(
                $req,
                new \Exception('permission denied'),
                __CLASS__ . '::' . __FUNCTION__
            );
        if ($r = self::_validate($req->all(), [
            'customer_id' => 'required',
            'issue_date'  => 'required',
            'due_date'    => 'required',
            'category_id' => 'required',
            'items'       => 'required',
        ])) return $r;
        try {
            DB::transaction(function () use ($req, $invoice) {
                $invoice->update($req->only([
                    'customer_id',
                    'issue_date',
                    'due_date',
                    'ref_number',
                    'category_id'
                ]));
                Log::info(__CLASS__ . '::' . __FUNCTION__ .
                    ' header updated', ['invoice_id' => $invoice->id]);
                CustomField::saveData($invoice, $req->customField);
                foreach ($req->items as $item) {
                    $invProd = InvoiceProduct::find($item['id']);
                    if ($invProd) {
                        Utility::totalQuantity(
                            'plus',
                            $invProd->quantity,
                            $invProd->product_id
                        );
                    } else
                        $invProd = new InvoiceProduct(['invoice_id' => $invoice->id]);
                    $data = Arr::only($item, [
                        'quantity',
                        'tax',
                        'discount',
                        'price',
                        'description'
                    ]);
                    $data['product_id'] = $item['item'] ?? $invProd->product_id;
                    $invProd->fill($data)->save();
                    Utility::totalQuantity(
                        'minus',
                        $data['quantity'],
                        $invProd->product_id
                    );
                }
                Log::info(__CLASS__ . '::' . __FUNCTION__ .
                    ' ' . self::SINGULAR . ' updated', ['invoice_id' => $invoice->id]);
            });

            return redirect()->route(ViewsConstants::INV . '.index')
                ->with('success', ucfirst(self::SINGULAR) . ' successfully updated.');
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ .
                ' failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $req, Invoice $invoice): RedirectResponse|JsonResponse|null
    {
        Log::warning(__CLASS__ . '::' . __FUNCTION__ .
            ' started', ['invoice_id' => $invoice->id]);
        if ($r = self::_authorize($req, 'delete invoice')) return $r;
        if ($invoice->created_by !== $req->user()->creatorId())
            return defaultPermissionDenial(
                $req,
                new \Exception('permission denied'),
                __CLASS__ . '::' . __FUNCTION__
            );

        try {
            DB::transaction(function () use ($invoice) {
                foreach ($invoice->payments as $p) {
                    Utility::bankAccountBalance(
                        $p->account_id,
                        $p->amount,
                        'debit'
                    );
                    $p->delete();
                }
                if ($invoice->customer_id && $invoice->status)
                    Utility::updateUserBalance(
                        PermissionsConstants::CT,
                        $invoice->customer_id,
                        $invoice->getDue(),
                        'debit'
                    );
                CreditNote::where(self::SINGULAR, $invoice->id)->delete();
                InvoiceProduct::where('invoice_id', $invoice->id)->delete();
                $invoice->delete();
                Log::info(__CLASS__ . '::' . __FUNCTION__ .
                    ' ' . self::SINGULAR . ' deleted', ['invoice_id' => $invoice->id]);
            });

            return redirect()->route(ViewsConstants::INV . '.index')
                ->with('success', ucfirst(self::SINGULAR) . ' successfully deleted.');
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ .
                ' failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);
            return self::_catch($req, $e);
        }
    }

    public function customer(Request $req): Response
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'customer_id' => $req->id
        ]);
        $customer = Customer::findOrFail($req->id);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', [
            'customer_id' => $customer->id
        ]);
        return response()->view(ViewsConstants::INV . '.customer_detail', compact(PermissionsConstants::CT));
    }

    public function product(Request $req): JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'product_id' => $req->product_id
        ]);
        $prod   = ProductService::findOrFail($req->product_id);
        $unit   = $prod->unit?->name ?? '';
        $taxRate = $prod->tax_id ? $prod->taxRate($prod->tax_id) : 0;
        $taxes  = $prod->tax_id ? $prod->tax($prod->tax_id)       : 0;
        $sale   = $prod->sale_price;
        $total  = $sale;
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' computed', [
            'unit' => $unit,
            'taxRate' => $taxRate,
            'total' => $total
        ]);
        return response()->json([
            'product'     => $prod,
            'unit'        => $unit,
            'taxRate'     => $taxRate,
            DatabaseConstants::TABLE_TAXES       => $taxes,
            'totalAmount' => $total,
        ]);
    }

    public function items(Request $req): JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'invoice_id' => $req->invoice_id,
            'product_id' => $req->product_id
        ]);
        $item = InvoiceProduct::where(
            'invoice_id',
            $req->invoice_id
        )->where(
            'product_id',
            $req->product_id
        )->first();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' found', [
            'exists' => (bool)$item
        ]);
        return response()->json($item);
    }

    public const GET_INV = 'getInvoice';
    public function getInvoice(Request $req): JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $req->id]);
        $due = Invoice::findOrFail($req->id)->getDue();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' due', ['due' => $due]);
        return response()->json(['due' => $due]);
    }

    public function export(): Response
    {
        $file = 'invoice_' . date('Y-m-d_His') . '.xlsx';
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' exporting', ['file' => $file]);
        return Excel::download(new InvoiceExport(), $file);
    }

    public const INV_N = 'invoiceNumber';
    public function invoiceNumber(): int
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' alias called');
        return self::_nextNumber();
    }

    public function show(Request $req, string $encId): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['enc_id' => $encId]);
        if ($r = self::_authorize($req, 'show invoice')) {
            Log::warning('unauthorized show', ['user' => Auth::id()]);
            return $r;
        }
        try {
            $id = Crypt::decrypt($encId);
            Log::info('decrypted show id', ['id' => $id]);
            $invoice = Invoice::with('credit_note')->findOrFail($id);
            if ($invoice->created_by !== $req->user()->creatorId()) {
                Log::warning('not owner show', ['user' => Auth::id(), 'invoice_id' => $id]);
                return defaultPermissionDenial(
                    $req,
                    new \Exception('not owner'),
                    __CLASS__ . '::' . __FUNCTION__
                );
            }
            $invoicePayment = InvoicePayment::where(
                'invoice_id',
                $invoice->id
            )->first();
            $viewData = [
                self::SINGULAR       => $invoice,
                PermissionsConstants::CT      => $invoice->customer,
                'iteams'        => $invoice->items,
                'invoicePayment' => $invoicePayment,
                DatabaseConstants::TABLE_CUSTOM_FIELDS  => CustomField::where(
                    DatabaseConstants::TABLE_CREATOR,
                    $req->user()->creatorId()
                )->where('module', self::SINGULAR)->get(),
                'user'          => $req->user(),
                'invoice_user'  => User::find($invoice->created_by),
                'user_plan'     => Plan::getPlan(
                    User::find($invoice->created_by)->plan
                ),
            ];
            Log::info('show data ready', ['invoice_id' => $id]);
            return response()->view(ViewsConstants::INV . '.view', $viewData);
        } catch (Throwable $e) {
            Log::error('show failed', ['error' => $e->getMessage()]);
            return self::_catch($req, $e);
        }
    }

    public const PRD_DST = 'productDestroy';
    public function productDestroy(Request $req): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $req->id]);
        if ($r = self::_authorize($req, 'delete invoice product')) {
            return $r;
        }
        $invProd = InvoiceProduct::findOrFail($req->id);
        $invoice = Invoice::findOrFail($invProd->invoice_id);
        Utility::updateUserBalance(
            PermissionsConstants::CT,
            $invoice->customer_id,
            $req->amount,
            'debit'
        );
        $invProd->delete();
        Log::info('productDestroyed', ['id' => $req->id]);
        return back()->with('success', ucfirst(self::SINGULAR) . ' product successfully deleted.');
    }

    public const CST_INV = 'customerInvoice';
    public function customerInvoice(Request $req): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['user' => Auth::id()]);
        if ($r = self::_authorize($req, 'manage customer invoice')) {
            return $r;
        }
        $status = Invoice::$statuses;
        $q = Invoice::where('customer_id', $req->user()->id)
            ->where('status', '!=', 0)
            ->where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId());
        if ($req->issue_date) $q->whereBetween(
            'issue_date',
            explode(' - ', $req->issue_date)
        );
        if ($req->status) $q->where('status', $req->status);
        $invoices = $q->get();
        Log::info('customerInvoice fetched', ['count' => $invoices->count()]);
        return response()->view(ViewsConstants::INV . '.index', [
            DatabaseConstants::TABLE_INVS => $invoices,
            'status' => $status
        ]);
    }

    public const CST_INV_SHW = 'customerInvoiceShow';
    public function customerInvoiceShow(Request $req, string|int $id): Response|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $id]);
        $invoice = Invoice::with('payments.bankAccount')->findOrFail($id);
        $owner  = User::find($invoice->created_by);
        if ($invoice->created_by !== $owner->creatorId()) {
            Log::warning('customerInvoiceShow denied', ['user' => Auth::id()]);
            return back()->with('error', 'Permission denied.');
        }
        $view = $owner->type === PermissionsConstants::SA ? self::SINGULAR . '.view' : self::SINGULAR . '.customer_invoice';
        Log::info('customerInvoiceShow view', ['view' => $view]);
        return response()->view($view, [
            self::SINGULAR => $invoice,
            PermissionsConstants::CT => $invoice->customer,
            'iteams' => $invoice->items,
            'user' => $owner,
        ]);
    }

    public function sent(Request $req, string|int $id): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ .
            ' started', ['invoice_id' => $id]);
        try {
            DB::transaction(function () use ($req, $id) {
                $settings = Utility::settings();
                if (!($settings['customer_invoice_sent'] ?? 0))
                    throw new \Exception('Mail disabled by admin.');
                $invoice = Invoice::findOrFail($id);
                $invoice->update([
                    'send_date' => now()->toDateString(),
                    'status'    => 1
                ]);
                $customer = Customer::find($invoice->customer_id);
                Utility::updateUserBalance(
                    PermissionsConstants::CT,
                    $customer->id,
                    $invoice->getTotal(),
                    'credit'
                );
                $this->_mailInvoice($invoice, $customer);
                Log::info(__CLASS__ . '::' . __FUNCTION__ .
                    ' ' . self::SINGULAR . ' sent', ['invoice_id' => $id]);
            });
            return back()->with('success', ucfirst(self::SINGULAR) . ' successfully sent.');
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Mail disabled by admin.') {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ .
                    ' mail disabled', ['user' => Auth::id()]);
                return back()->with('error', $e->getMessage());
            }
            Log::error(__CLASS__ . '::' . __FUNCTION__ .
                ' failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $id
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function resent(Request $req, string|int $id): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $id]);
        if ($r = self::_authorize($req, 'send invoice')) return $r;
        $invoice = Invoice::findOrFail($id);
        $customer = Customer::find($invoice->customer_id);
        $this->_mailInvoice($invoice, $customer);
        Log::info('resent done', ['invoice_id' => $id]);
        return back()->with('success', ucfirst(self::SINGULAR) . ' successfully sent.');
    }

    public function payment(Request $req, string|int $invoiceId): Response|RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'invoice_id' => $invoiceId
        ]);
        if ($r = self::_authorize($req, 'create payment invoice')) return $r;
        $invoice = Invoice::findOrFail($invoiceId);
        $viewData = [
            self::SINGULAR    => $invoice,
            DatabaseConstants::TABLE_CUSTOMERS  => Customer::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )->pluck('name', 'id'),
            'categories' => ProductServiceCategory::where(
                DatabaseConstants::TABLE_CREATOR,
                $req->user()->creatorId()
            )->pluck('name', 'id'),
            'accounts'   => BankAccount::select(
                '*',
                DB::raw("CONCAT(bank_name,' ',holder_name) AS name")
            )
                ->where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())
                ->pluck('name', 'id'),
        ];
        Log::info('payment viewData ready', [
            'invoice_id' => $invoice->id
        ]);
        return response()->view(ViewsConstants::INV . '.payment', $viewData);
    }

    public const PAY_CRT = 'createPayment';
    public function createPayment(Request $req, string|int $invoiceId): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ .
            ' started', ['invoice_id' => $invoiceId]);
        $invoice = Invoice::findOrFail($invoiceId);
        if ($invoice->getSubTotal() < $req->amount)
            return back()->with('error', 'Amount exceeds subtotal.');
        if ($r = self::_authorize($req, 'create payment invoice')) return $r;
        if ($r = self::_validate($req->all(), [
            'date'       => 'required',
            'amount'     => 'required',
            'account_id' => 'required',
        ])) return $r;
        try {
            DB::transaction(function () use ($req, $invoice) {
                $pay = InvoicePayment::create([
                    'invoice_id'     => $invoice->id,
                    'date'           => $req->date,
                    'amount'         => $req->amount,
                    'account_id'     => $req->account_id,
                    'payment_method' => 0,
                    'reference'      => $req->reference,
                    'description'    => $req->description,
                    'add_receipt'    => $this->_handleReceipt($req),
                ]);
                $this->_syncInvoiceAfterPayment(
                    $invoice,
                    $pay,
                    $req->amount
                );
                Log::info(__CLASS__ . '::' . __FUNCTION__ .
                    ' payment added', ['payment_id' => $pay->id]);
            });
            return back()->with('success', 'Payment successfully added.');
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ .
                ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public const PAY_DST = 'paymentDestroy';
    public function paymentDestroy(
        Request $req,
        string|int $invoiceId,
        string|int $paymentId
    ): RedirectResponse {
        Log::info(__CLASS__ . '::' . __FUNCTION__ .
            ' started', ['payment_id' => $paymentId]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($r = self::_authorize($req, 'delete payment invoice')) return $r;
        try {
            DB::transaction(function () use ($user, $invoiceId, $paymentId) {
                $pay    = InvoicePayment::findOrFail($paymentId);
                $invoice = Invoice::findOrFail($invoiceId);
                $pay->delete();
                InvoiceBankTransfer::where('id', $paymentId)->delete();
                $due = $invoice->getDue();
                $invoice->status = ($due > 0 && $invoice->getTotal() !== $due) ? 3 : 2;
                $invoice->save();
                if ($pay->add_receipt)
                    Utility::changeStorageLimit(
                        $user?->creatorId(),
                        '/uploads/payment/' . $pay->add_receipt
                    );
                Transaction::destroyTransaction(
                    $paymentId,
                    'Partial',
                    ucfirst(PermissionsConstants::CT)
                );
                Utility::updateUserBalance(
                    PermissionsConstants::CT,
                    $invoice->customer_id,
                    $pay->amount,
                    'credit'
                );
                Utility::bankAccountBalance(
                    $pay->account_id,
                    $pay->amount,
                    'debit'
                );
                Log::info(__CLASS__ . '::' . __FUNCTION__ .
                    ' payment deleted', ['payment_id' => $paymentId]);
            });

            return back()->with('success', 'Payment successfully deleted.');
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ .
                ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public const PAY_RMD = 'paymentReminder';
    public function paymentReminder(Request $req, string|int $invoiceId): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'invoice_id' => $invoiceId
        ]);
        $invoice = Invoice::findOrFail($invoiceId);
        $customer = Customer::find($invoice->customer_id);
        $setting = Utility::settings($req->user()->creatorId());
        if (($setting['twilio_reminder_notification'] ?? 0) == 1) {
            Utility::sendTwilioMsg(
                $customer->contact,
                'invoice_payment_reminder',
                [
                    'invoice_number' => $user?->invoiceNumberFormat($invoice->invoice_id),
                    'customer_name'  => $customer->name,
                    'user_name'      => $req->user()->name,
                ]
            );
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' twilio sent', [
                'contact'        => $customer->contact,
                'invoice_id'     => $invoiceId
            ]);
        }
        $resp = ['is_success' => true];
        if (($setting['new_payment_reminder'] ?? 0) == 1) {
            $payload = [
                'payment_reminder_name'      => $customer->name,
                'invoice_payment_number'     => $user?->invoiceNumberFormat($invoice->invoice_id),
                'invoice_payment_dueAmount'  => $user?->priceFormat($invoice->getDue()),
                'payment_reminder_date'      => $user?->dateFormat($invoice->send_date),
            ];
            $resp = Utility::sendEmailTemplate(
                'new_payment_reminder',
                [$customer->id => $customer->email],
                $payload
            );
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' email sent', [
                'email'        => $customer->email,
                'is_success'   => $resp['is_success'],
                'error'        => $resp['error'] ?? null
            ]);
        }
        $msg = 'Payment reminder successfully sent.'
            . (!$resp['is_success'] && !empty($resp['error'])
                ? '<br><span class="text-danger">' . $resp['error'] . '</span>'
                : '');
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' completed', [
            'final_msg'    => strip_tags($msg),
            'is_success'   => $resp['is_success']
        ]);
        return back()->with('success', $msg);
    }

    public const SHP_DSP = 'shippingDisplay';
    public function shippingDisplay(Request $req, string|int $id): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['invoice_id' => $id]);
        $invoice = Invoice::findOrFail($id);
        $invoice->shipping_display = $req->is_display === 'true' ? 1 : 0;
        $invoice->save();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' updated', [
            'invoice_id'       => $id,
            'shipping_display' => $invoice->shipping_display
        ]);
        return back()->with('success', 'Shipping address status updated.');
    }

    public function duplicate(Request $req, string|int $id): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['invoice_id' => $id]);
        if ($r = self::_authorize($req, 'duplicate invoice')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        $inv = Invoice::findOrFail($id);
        $dup = $inv->replicate(['invoice_id', 'issue_date', 'send_date', 'status']);
        $dup->invoice_id = self::_nextNumber();
        $dup->issue_date = now()->toDateString();
        $dup->status    = 0;
        $dup->save();
        $inv->items->each(function ($p) use ($dup) {
            InvoiceProduct::create($p->only([
                'product_id',
                'quantity',
                'tax',
                'discount',
                'price'
            ]) + ['invoice_id' => $dup->id]);
        });
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' completed', [
            'original_id' => $id,
            'duplicate_id' => $dup->id
        ]);
        return back()->with('success', ucfirst(self::SINGULAR) . ' duplicated successfully.');
    }

    public const IV_LK = 'invoiceLink';
    public function invoiceLink(Request $req, string $encId): Response|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['enc_id' => $encId]);
        try {
            $id = Crypt::decrypt($encId);
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' decrypted', ['id' => $id]);
        } catch (Throwable $e) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' decrypt failed', [
                'enc_id' => $encId,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', ucfirst(self::SINGULAR) . ' Not Found.');
        }
        $invoice = Invoice::findOrFail($id);
        $settings = Utility::settingsById($invoice->created_by);
        $data = [
            DatabaseConstants::TABLE_SETTINGS               => $settings,
            self::SINGULAR                => $invoice,
            PermissionsConstants::CT               => $invoice->customer,
            'iteams'                 => $invoice->items,
            'invoicePayment'         => InvoicePayment::where('invoice_id', $id)->get(),
            DatabaseConstants::TABLE_CUSTOM_FIELDS           => CustomField::where('module', self::SINGULAR)->get(),
            'user'                   => User::find($invoice->created_by),
            'company_payment_setting' => Utility::getCompanyPaymentSetting($invoice->created_by),
            'invoice_user'           => User::find($invoice->created_by),
            'user_plan'              => Plan::find(User::find($invoice->created_by)->plan),
        ];
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' data prepared', [
            'invoice_id' => $id
        ]);
        return response()->view(ViewsConstants::INV . '.customer_invoice', $data);
    }

    public const SV_IV_TMP = 'saveTemplateSettings';
    public function saveTemplateSettings(Request $req): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [UsersConstants::COL_USER_ID => Auth::id()]);
        $post = $req->except('_token');
        if (isset($post[BillsConstants::COL_INV_TMP]) && empty($post['invoice_color'])) {
            $post['invoice_color'] = 'ffffff';
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' default color applied', [
                'invoice_color' => 'ffffff'
            ]);
        }
        if ($req->file('invoice_logo')) {
            $fn = Auth::id() . '_invoice_logo.png';
            $size = $req->file('invoice_logo')->getSize();
            $path = Utility::uploadFile(
                $req,
                'invoice_logo',
                $fn,
                'invoice_logo/',
                ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]
            );
            if ($path['flag'] == 0) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' logo upload failed', [
                    'error' => $path['msg']
                ]);
                return back()->with('error', $path['msg']);
            }
            $post['invoice_logo'] = $fn;
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' logo uploaded', [
                'file' => $fn,
                'size' => $size
            ]);
        }
        $creatorCol = DatabaseConstants::TABLE_CREATOR;
        foreach ($post as $k => $v) {
            DB::insert(
                'INSERT INTO settings (`value`,`name`,`' . $creatorCol . '`)
           VALUES (?,?,?)
           ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                [$v, $k, $user?->creatorId()]
            );
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' setting saved', [
                'name' => $k,
                'value' => $v
            ]);
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' completed');
        return back()->with('success', ucfirst(self::SINGULAR) . ' setting updated successfully');
    }

    public function invoice(Request $request, string $encId): \Illuminate\View\View|\Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        if ($r = self::_authorize($request, PermissionsConstants::MNG_INV)) return $r;
        Log::info("$action start", ['enc_id' => $encId, UsersConstants::COL_USER_ID => $request->user()->id]);
        try {
            $id = Crypt::decrypt($encId);
            Log::info("$action decrypted id", ['id' => $id]);
            $invoice = Invoice::with('items.product')->findOrFail($id);
            if ($invoice->created_by !== $request->user()->creatorId()) {
                Log::warning("$action ownership mismatch", [
                    UsersConstants::COL_USER_ID    => $request->user()->id,
                    'invoiceId' => $invoice->id
                ]);
                return defaultPermissionDenial(
                    $request,
                    new \Exception('owner'),
                    $action,
                    route(ViewsConstants::INV . '.index')
                );
            }
            $settings = Utility::settingsById($invoice->created_by);
            Log::info("$action settings loaded", ['count' => count($settings)]);
            $customer     = $invoice->customer;
            $items        = [];
            $totalTax = $totalQty = $totalRate = $totalDisc = 0;
            $taxesData = [];
            foreach ($invoice->items as $prod) {
                $item = (object)[
                    'name'        => $prod->product->name ?? '',
                    'quantity'    => $prod->quantity,
                    'tax'         => $prod->tax,
                    'unit'        => $prod->product->unit_id ?? '',
                    'discount'    => $prod->discount,
                    'price'       => $prod->price,
                    'description' => $prod->description
                ];
                $totalQty  += $item->quantity;
                $totalRate += $item->price;
                $totalDisc += $item->discount;
                $itemTaxes = [];
                if ($item->tax) {
                    foreach (Utility::tax($item->tax) as $tx) {
                        $tp = Utility::taxRate($tx->rate, $item->price, $item->quantity, $item->discount);
                        $totalTax += $tp;
                        $it = [
                            'name'      => $tx->name,
                            'rate'      => "{$tx->rate}%",
                            'price'     => Utility::priceFormat($settings, $tp),
                            'tax_price' => $tp
                        ];
                        $itemTaxes[] = $it;
                        $taxesData[$tx->name] = ($taxesData[$tx->name] ?? 0) + $tp;
                    }
                }
                $item->itemTax = $itemTaxes;
                $items[]      = $item;
            }
            $invoice->itemData     = $items;
            $invoice->totalTaxPrice = $totalTax;
            $invoice->totalQuantity = $totalQty;
            $invoice->totalRate    = $totalRate;
            $invoice->totalDiscount = $totalDisc;
            $invoice->taxesData    = $taxesData;
            $invoice->customField  = CustomField::getData($invoice, self::SINGULAR);
            $customFields          = CustomField::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->where('module', self::SINGULAR)->get();
            $logo       = asset(Storage::url('uploads/logo/'));
            $invLogo    = Utility::settingsById($invoice->created_by)['invoice_logo'] ?? '';
            $img        = $invLogo
                ? Utility::getFile('invoice_logo/') . $invLogo
                : asset($logo . '/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF));
            $color     = '#' . $settings['invoice_color'];
            $fontColor = Utility::getFontColor($color);
            Log::info("$action succeeded", ['invoiceId' => $invoice->id]);
            return view(
                ViewsConstants::INV_TMP . "{$settings[BillsConstants::COL_INV_TMP]}",
                compact(self::SINGULAR, 'color', DatabaseConstants::TABLE_SETTINGS, PermissionsConstants::CT, 'img', 'fontColor', DatabaseConstants::TABLE_CUSTOM_FIELDS)
            );
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return self::_catch($request, $e);
        }
    }

    public const CST_INV_SD = 'customerInvoiceSend';
    public function customerInvoiceSend(Request $request, string $encId): \Illuminate\View\View|\Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        if ($r = self::_authorize($request, 'send invoice')) return $r;
        Log::info("$action start", ['enc_id' => $encId, UsersConstants::COL_USER_ID => $request->user()->id]);
        return view('customer.invoice_send', compact('enc_id'));
    }

    public const CST_INV_SD_ML = 'customerInvoiceSendMail';
    public function customerInvoiceSendMail(Request $request, string $encId): \Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        if ($r = self::_authorize($request, 'send invoice')) return $r;
        if ($v = self::_validate($request->all(), ['email' => 'required|email'])) return $v;
        Log::info("$action start", ['email' => $request->email, 'enc_id' => $encId]);
        try {
            $id     = Crypt::decrypt($encId);
            $invoice = Invoice::findOrFail($id);
            if ($invoice->created_by !== $request->user()->creatorId()) {
                Log::warning("$action ownership mismatch", [UsersConstants::COL_USER_ID => $request->user()->id, 'invoiceId' => $id]);
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(ViewsConstants::INV . '.index'));
            }
            $customer      = Customer::find($invoice->customer_id);
            $invoice->name = $customer->name ?? '';
            $invoice->invoice = $request->user()->invoiceNumberFormat($invoice->invoice_id);
            $invoice->url  = route(ViewsConstants::INV . '.pdf', Crypt::encryptString($invoice->id));
            Mail::to($request->email)->send(new CustomerInvoiceSend($invoice));
            Log::info("$action mail sent", ['to' => $request->email, 'invoiceId' => $id]);
            return redirect()->back()->with('success', __(ucfirst(self::SINGULAR) . ' successfully sent.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(ViewsConstants::INV . '.index'));
        }
    }

    public const INV_PRV = 'previewInvoice';
    public function previewInvoice(Request $request, string $template, string $colorHex): \Illuminate\View\View|\Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        if ($r = self::_authorize($request, PermissionsConstants::MNG_INV)) return $r;
        Log::info("$action start", ['template' => $template, 'color' => $colorHex]);
        try {
            $user    = $request->user();
            $settings = Utility::settingsById($user?->creatorId());
            $invoice = new Invoice();
            $customer = (object)[
                'email' => '<Email>',
                'shipping_name' => '<Customer Name>',
                'shipping_country' => '<Country>',
                'shipping_state' => '<State>',
                'shipping_city' => '<City>',
                'shipping_phone' => '<Phone>',
                'shipping_zip' => '<Zip>',
                'shipping_address' => '<Addr>',
                'billing_name' => '<Customer Name>',
                'billing_country' => '<Country>',
                'billing_state' => '<State>',
                'billing_city' => '<City>',
                'billing_phone' => '<Phone>',
                'billing_zip' => '<Zip>',
                'billing_address' => '<Addr>'
            ];
            $items = $taxesData = [];
            $totalTax = 0;
            for ($i = 1; $i <= 3; $i++) {
                $item = (object)[
                    'name' => "Item {$i}",
                    'quantity' => 1,
                    'tax' => 5,
                    'discount' => 50,
                    'price' => 100,
                    'unit' => 1,
                    'description' => 'XYZ'
                ];
                $itemTaxes = [];
                foreach (['Tax 1', 'Tax 2'] as $k => $t) {
                    $tp = 10;
                    $totalTax += $tp;
                    $it = ['name' => "Tax {$k}", 'rate' => '10%', 'price' => '$10', 'tax_price' => 10];
                    $itemTaxes[] = $it;
                    $taxesData["Tax {$k}"] = ($taxesData["Tax {$k}"] ?? 0) + $tp;
                }
                $item->itemTax = $itemTaxes;
                $items[] = $item;
            }
            $invoice->itemData     = $items;
            $invoice->totalTaxPrice = $totalTax;
            $invoice->totalQuantity = count($items);
            $invoice->totalRate    = array_sum(array_column($items, 'price'));
            $invoice->totalDiscount = array_sum(array_column($items, 'discount'));
            $invoice->taxesData    = $taxesData;
            $invoice->created_by   = $user?->creatorId();
            $invoice->invoice_id   = 1;
            $invoice->issue_date   = now();
            $invoice->due_date     = now();
            $preview = true;
            $color  = "#{$colorHex}";
            $font   = Utility::getFontColor($color);
            $logo   = asset(Storage::url('uploads/logo/'));
            $invLogo = $settings['invoice_logo'] ?? '';
            $img    = $invLogo
                ? Utility::getFile('invoice_logo/') . $invLogo
                : asset($logo . '/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF));
            Log::info("$action succeeded");
            return view(ViewsConstants::INV_TMP . "{$template}", compact(
                self::SINGULAR,
                'preview',
                'color',
                'img',
                DatabaseConstants::TABLE_SETTINGS,
                PermissionsConstants::CT,
                'font'
            ));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return self::_catch($request, $e);
        }
    }

    private function _notifyInvoice(Invoice $invoice, bool $isNew = false)
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $action = $isNew ? 'new_invoice' : 'update_invoice';
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' notify start', [
            'invoice_id' => $invoice->id,
            'action' => $action
        ]);
        $set     = Utility::settings($invoice->created_by);
        $customer = $invoice->customer;
        $arr     = [
            'invoice_number'      => $user?->invoiceNumberFormat($invoice->invoice_id),
            'user_name'           => $user?->name,
            'invoice_issue_date'  => $invoice->issue_date,
            'invoice_due_date'    => $invoice->due_date,
            'customer_name'       => $customer->name,
        ];
        if ($set['invoice_notification'] ?? 0) {
            Utility::sendSlackMsg($action, $arr);
            Log::info('slack msg sent', ['invoice_id' => $invoice->id]);
        }
        if ($set['telegram_invoice_notification'] ?? 0) {
            Utility::sendTelegramMsg($action, $arr);
            Log::info('telegram msg sent', ['invoice_id' => $invoice->id]);
        }
        if ($set['twilio_invoice_notification'] ?? 0) {
            Utility::sendTwilioMsg($customer->contact, $action, $arr);
            Log::info('twilio msg sent', ['invoice_id' => $invoice->id]);
        }
    }

    private static function _authorize(
        Request $req,
        string  $perm
    ): RedirectResponse|JsonResponse|null {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'permission' => $perm
        ]);
        if (!$req->user()->can($perm)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' permission denied', [
                UsersConstants::COL_USER_ID    => $req->user()->id,
                'permission' => $perm
            ]);
            return defaultPermissionDenial(
                $req,
                new \Exception('permission denied'),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' permission granted', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'permission' => $perm
        ]);
        return null;
    }

    private static function _validate(
        array $d,
        array $r
    ): RedirectResponse|JsonResponse|null {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' validating', [
            'rules' => $r
        ]);
        $v = Validator::make($d, $r);
        if ($v->fails()) {
            $msg = $v->getMessageBag()->first();
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error' => $msg
            ]);
            return redirect()->back()->with('error', $msg);
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' passed');
        return null;
    }

    private static function _catch(
        Request   $req,
        Throwable $e
    ): RedirectResponse|JsonResponse|null {
        $func = debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS,
            2
        )[1]['function'];
        Log::error(__CLASS__ . '::' . $func . ' exception', [
            'error' => $e->getMessage(),
        ]);
        Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . '::' . $func . ' exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return defaultUndefinedException(
            $req,
            $e,
            __CLASS__ . '::' . $func
        );
    }

    private static function _nextNumber(): RedirectResponse|int
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' calc next invoice');
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect
            instanceof \Illuminate\Http\RedirectResponse
        ) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not logged');
            return $userOrRedirect;
        }
        $user  = $userOrRedirect;
        $latest = Invoice::where(
            DatabaseConstants::TABLE_CREATOR,
            $user?->creatorId()
        )->latest()->first();
        $next = $latest
            ? $latest->invoice_id + 1
            : 1;
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' next', [
            'next' => $next
        ]);
        return $next;
    }

    private static function _txnId(): string
    {
        $id = strtoupper(
            str_replace('.', '', uniqid('', true))
        );
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' txn', [
            'txn_id' => $id
        ]);
        return $id;
    }

    private static function _client(): Client
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' new HTTP client');
        return new Client(['timeout' => 15]);
    }

    private function _mailInvoice(Invoice $invoice, Customer $customer)
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id
        ]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $invoice->name    = $customer->name;
        $invoice->invoice = $user
            ->invoiceNumberFormat($invoice->invoice_id);
        $enc              = Crypt::encryptString($invoice->id);
        $invoice->url     = route(ViewsConstants::INV . '.pdf', $enc);
        $payload = [
            'customer_name'  => $customer->name,
            'customer_email' => $customer->email,
            'invoice_name'   => $customer->name,
            'invoice_number' => $invoice->invoice,
            'invoice_url'    => $invoice->url,
        ];
        Utility::sendEmailTemplate(
            'customer_invoice_sent',
            [$customer->id => $customer->email],
            $payload
        );
        Log::info('mailInvoice sent', [
            'invoice_id' => $invoice->id
        ]);
    }

    private function _handleReceipt(Request $req): ?string
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' start');
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$req->file('add_receipt')) {
            Log::debug('no receipt file');
            return null;
        }
        $size = $req->file('add_receipt')->getSize();
        if (!Utility::updateStorageLimit(
            $user?->creatorId(),
            $size
        )) {
            Log::warning('receipt exceeds limit', ['size' => $size]);
            return null;
        }
        $fileName = time() . '_' . $req
            ->file('add_receipt')
            ->getClientOriginalName();
        $req->file('add_receipt')
            ->storeAs('uploads/payment', $fileName);
        Log::info('receipt stored', ['file' => $fileName]);
        return $fileName;
    }

    private function _syncInvoiceAfterPayment(
        Invoice $invoice,
        InvoicePayment $pay,
        float $amount
    ): void {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'invoice_id' => $invoice->id,
            'payment_id' => $pay->id,
            'amount' => $amount
        ]);
        $due = $invoice->getDue();
        $invoice->status  = $due <= 0 ? 4 : 3;
        if ($invoice->status == 0) {
            $invoice->send_date = now()->toDateString();
        }
        $invoice->save();
        $pay->fill([
            UsersConstants::COL_USER_ID     => $invoice->customer_id,
            'user_type'   => ucfirst(PermissionsConstants::CT),
            'type'        => 'Partial',
            DatabaseConstants::TABLE_CREATOR  => Auth::id(),
            'payment_id'  => $pay->id,
            'category'    => ucfirst(self::SINGULAR) . '',
            'account'     => $pay->account_id,
        ])->save();
        Transaction::addTransaction($pay);
        Utility::updateUserBalance(
            PermissionsConstants::CT,
            $invoice->customer_id,
            $amount,
            'debit'
        );
        Utility::bankAccountBalance(
            $pay->account_id,
            $amount,
            'credit'
        );
        Log::info('syncInvoiceAfterPayment done', [
            'invoice_id' => $invoice->id
        ]);
    }
}
