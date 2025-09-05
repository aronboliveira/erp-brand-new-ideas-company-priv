<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BillsConstants,
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants as VW
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
use App\Traits\ChecksPermissions;
use GuzzleHttp\Client;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Log,
    Mail,
    Route,
    Storage,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class InvoiceController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    private const SINGULAR = 'invoice';

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $req): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id ?? null, PermissionsConstants::CT => $req->customer ?? null, 'issue_date' => $req->issue_date ?? null, 'status' => $req->status ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($r = self::guard($req, PermissionsConstants::MNG_INV)) !== true) return $r;
            try {
                $custStart = microtime(true);
                $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Customer', '');
                $this->logExecutionTime($custStart, $action, 'fetchCustomers');
                $stStart = microtime(true);
                $status = Invoice::$statuses;
                $this->logExecutionTime($stStart, $action, 'loadStatuses');
                $qryStart = microtime(true);
                $q = Invoice::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId());
                if ($req->customer ?? null) $q->where('customer_id', $req->customer);
                if ($req->issue_date ?? null) {
                    $parts = array_map('trim', explode('to', $req->issue_date));
                    $range = count($parts) > 1 ? $parts : [$req->issue_date, $req->issue_date];
                    $q->whereBetween('issue_date', $range);
                }
                if ($req->status ?? null) $q->where('status', $req->status);
                $invoices = $q->get();
                $this->logExecutionTime($qryStart, $action, 'queryInvoices');
                Log::info("[{$base}::{$action}] fetched", ['count' => $invoices->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [DatabaseConstants::TABLE_INVS, PermissionsConstants::CT, 'status']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = response()->view($viewPath, [DatabaseConstants::TABLE_INVS => $invoices, PermissionsConstants::CT => $customers, 'status' => $status]);
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'trace' => $e->getTraceAsString()]);
                return self::_catch($req, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $req, string|int $customer_id = ''): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.create';
        return $this->measureProfile($action, function () use ($req, $customer_id, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id ?? null, 'customer_id' => $customer_id ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($r = self::guard($req, 'create invoice', VW::INV . '.index')) !== true) return $r;
            try {
                $cfStart = microtime(true);
                $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->where('module', self::SINGULAR)->get();
                $this->logExecutionTime($cfStart, $action, 'loadCustomFields');
                $numStart = microtime(true);
                $invoiceNum = $req->user()->invoiceNumberFormat(self::_nextNumber());
                $this->logExecutionTime($numStart, $action, 'formatInvoiceNumber');
                $custStart = microtime(true);
                $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Customer', '');
                $this->logExecutionTime($custStart, $action, 'fetchCustomers');
                $catStart = microtime(true);
                $category = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->where('type', 'income')->pluck('name', 'id')->prepend('Select Category', '');
                $this->logExecutionTime($catStart, $action, 'fetchCategories');
                $prodStart = microtime(true);
                $products = ProductService::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->pluck('name', 'id')->prepend('--', '');
                $this->logExecutionTime($prodStart, $action, 'fetchProducts');
                Log::info("[{$base}::{$action}] data ready", ['fields' => $customFields->count(), DatabaseConstants::TABLE_CUSTOMERS => $customers->count(), 'categories' => $category->count(), DatabaseConstants::TABLE_PRODUCTS => $products->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [DatabaseConstants::TABLE_CUSTOMERS, 'invoice_number', DatabaseConstants::TABLE_PROD_SERVS, 'category', DatabaseConstants::TABLE_CUSTOM_FIELDS, 'customer_id']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = response()->view($viewPath, [DatabaseConstants::TABLE_CUSTOMERS => $customers, 'invoice_number' => $invoiceNum, DatabaseConstants::TABLE_PROD_SERVS => $products, 'category' => $category, DatabaseConstants::TABLE_CUSTOM_FIELDS => $customFields, 'customer_id' => $customer_id]);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'trace' => $e->getTraceAsString()]);
                return self::_catch($req, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'customer_id' => $customer_id ?? null]);
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['user' => $req->user()->id ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($r = self::guard($req, 'create invoice', VW::INV . '.index')) !== true) return $r;
            if ($r = self::_validate($req->all(), ['customer_id' => 'required', 'issue_date' => 'required', 'due_date' => 'required', 'category_id' => 'required', 'items' => 'required'])) return $r;
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action) {
                    $invBuildStart = microtime(true);
                    $invData = Arr::only($req->all(), ['customer_id', 'issue_date', 'due_date', 'ref_number', 'category_id']);
                    $invData['invoice_id'] = self::_nextNumber();
                    $invData['status'] = 0;
                    $invData[DatabaseConstants::TABLE_CREATOR] = $req->user()->creatorId();
                    $invoice = Invoice::create($invData);
                    $this->logExecutionTime($invBuildStart, $action, 'createInvoice');
                    Log::info('Invoice created', ['invoice_pk' => $invoice->id, 'customer_id' => $invoice->customer_id]);
                    $cfStart = microtime(true);
                    CustomField::saveData($invoice, $req->customField ?? null);
                    $this->logExecutionTime($cfStart, $action, 'saveCustomFields');
                    $itemsStart = microtime(true);
                    foreach ($req->items as $item) {
                        $prodData = Arr::only($item, ['quantity', 'tax', 'discount', 'price', 'description']);
                        $prodData['invoice_id'] = $invoice->id;
                        $prodData['product_id'] = $item['item'] ?? null;
                        InvoiceProduct::create($prodData);
                        Utility::totalQuantity('minus', $prodData['quantity'] ?? 0, $prodData['product_id']);
                    }
                    $this->logExecutionTime($itemsStart, $action, 'createInvoiceItems');
                    $notifyStart = microtime(true);
                    $this->_notifyInvoice($invoice, true);
                    $this->logExecutionTime($notifyStart, $action, 'notifyInvoice');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(VW::INV . '.index')->with('success', ucfirst(self::SINGULAR) . ' successfully created.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $req, string $encId): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.edit';
        return $this->measureProfile($action, function () use ($req, $encId, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()->id ?? null, 'enc_id' => $encId ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($r = self::guard($req, 'edit invoice', VW::INV . '.index')) !== true) return $r;
            try {
                $decStart = microtime(true);
                $id = Crypt::decrypt($encId);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $findStart = microtime(true);
                $invoice = Invoice::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findInvoice');
                $numStart = microtime(true);
                $invoiceNum = $req->user()->invoiceNumberFormat($invoice->invoice_id);
                $this->logExecutionTime($numStart, $action, 'formatInvoiceNumber');
                $custStart = microtime(true);
                $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->pluck('name', 'id');
                $this->logExecutionTime($custStart, $action, 'fetchCustomers');
                $catStart = microtime(true);
                $category = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->where('type', 'income')->pluck('name', 'id')->prepend('Select Category', '');
                $this->logExecutionTime($catStart, $action, 'fetchCategories');
                $prodStart = microtime(true);
                $products = ProductService::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->pluck('name', 'id');
                $this->logExecutionTime($prodStart, $action, 'fetchProducts');
                $cfDataStart = microtime(true);
                $invoice->customField = CustomField::getData($invoice, self::SINGULAR);
                $this->logExecutionTime($cfDataStart, $action, 'loadCustomFieldData');
                $cfStart = microtime(true);
                $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->where('module', self::SINGULAR)->get();
                $this->logExecutionTime($cfStart, $action, 'loadCustomFields');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [DatabaseConstants::TABLE_CUSTOMERS, DatabaseConstants::TABLE_PROD_SERVS, self::SINGULAR, 'invoice_number', 'category', DatabaseConstants::TABLE_CUSTOM_FIELDS]]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = response()->view($viewPath, [DatabaseConstants::TABLE_CUSTOMERS => $customers, DatabaseConstants::TABLE_PROD_SERVS => $products, self::SINGULAR => $invoice, 'invoice_number' => $invoiceNum, 'category' => $category, DatabaseConstants::TABLE_CUSTOM_FIELDS => $customFields]);
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'trace' => $e->getTraceAsString()]);
                return self::_catch($req, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId ?? null]);
    }

    public function update(Request $req, Invoice $invoice): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $invoice, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['invoice_id' => $invoice->id ?? null, 'ip' => $req->ip(), 'method' => $method]);
            if (($r = self::guard($req, 'edit invoice', VW::INV . '.index')) !== true) return $r;
            if (($invoice->created_by ?? null) !== ($req->user()->creatorId() ?? null)) return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
            if ($r = self::_validate($req->all(), ['customer_id' => 'required', 'issue_date' => 'required', 'due_date' => 'required', 'category_id' => 'required', 'items' => 'required'])) return $r;
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $invoice, $action) {
                    $hdrStart = microtime(true);
                    $invoice->update($req->only(['customer_id', 'issue_date', 'due_date', 'ref_number', 'category_id']));
                    $this->logExecutionTime($hdrStart, $action, 'updateHeader');
                    Log::info('Invoice header updated', ['invoice_pk' => $invoice->id, 'customer_id' => $invoice->customer_id]);
                    $cfStart = microtime(true);
                    CustomField::saveData($invoice, $req->customField ?? null);
                    $this->logExecutionTime($cfStart, $action, 'saveCustomFields');
                    $itemsStart = microtime(true);
                    foreach ($req->items as $item) {
                        $invProd = InvoiceProduct::find($item['id'] ?? null);
                        if ($invProd) {
                            Utility::totalQuantity('plus', $invProd->quantity ?? 0, $invProd->product_id ?? null);
                        } else $invProd = new InvoiceProduct(['invoice_id' => $invoice->id]);
                        $data = Arr::only($item, ['quantity', 'tax', 'discount', 'price', 'description']);
                        $data['quantity'] = $data['quantity'] ?? 0;
                        $data['product_id'] = $item['item'] ?? ($invProd->product_id ?? null);
                        $invProd->fill($data)->save();
                        Utility::totalQuantity('minus', $data['quantity'], $invProd->product_id ?? null);
                    }
                    $this->logExecutionTime($itemsStart, $action, 'upsertItems');
                    Log::info('Invoice updated', ['invoice_pk' => $invoice->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(VW::INV . '.index')->with('success', ucfirst(self::SINGULAR) . ' successfully updated.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'invoice_id' => $invoice->id ?? null]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoice->id ?? null]);
    }

    public function destroy(Request $req, Invoice $invoice): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $invoice, $action, $method, $class, $base) {
            Log::debug("[{$base}::{$action}] start", ['invoice_id' => $invoice->id ?? null]);
            if (($r = self::guard($req, 'delete invoice', VW::INV . '.index')) !== true) return $r;
            if (($invoice->created_by ?? null) !== ($req->user()->creatorId() ?? null))
                return defaultPermissionDenial($req, new \Exception('permission denied'), $class . '::' . $action);
            try {
                DB::transaction(function () use ($invoice, $base, $action) {
                    foreach ($invoice->payments as $p) {
                        Utility::bankAccountBalance($p->account_id, $p->amount, 'debit');
                        $p->delete();
                    }
                    if (($invoice->customer_id ?? null) && ($invoice->status ?? null)) {
                        Utility::updateUserBalance(PermissionsConstants::CT, $invoice->customer_id, $invoice->getDue(), 'debit');
                    }
                    CreditNote::where(self::SINGULAR, $invoice->id)->delete();
                    InvoiceProduct::where('invoice_id', $invoice->id)->delete();
                    $invoice->delete();
                    Log::info("[{$base}::{$action}] invoice deleted", ['invoice_id' => $invoice->id]);
                });
                return redirect()->route(VW::INV . '.index')->with('success', ucfirst(self::SINGULAR) . ' successfully deleted.');
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'invoice_id' => $invoice->id ?? null]);
                return self::_catch($req, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoice->id ?? null]);
    }

    public function customer(Request $req): Response
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.customer_detail';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::debug("[{$base}::{$action}] start", ['customer_id' => $req->id ?? null]);
            $customer = Customer::findOrFail($req->id);
            Log::info("[{$base}::{$action}] fetched", ['customer_id' => $customer->id ?? null]);
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            return response()->view($viewPath, compact(PermissionsConstants::CT));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'customer_id' => $req->id ?? null]);
    }

    public function product(Request $req): JsonResponse
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' start', ['product_id' => $req->product_id ?? null]);
        try {
            $prod    = ProductService::findOrFail($req->product_id);
            $unit    = $prod->unit?->name ?? '';
            $taxRate = $prod->tax_id ? $prod->taxRate($prod->tax_id) : 0;
            $taxes   = $prod->tax_id ? $prod->tax($prod->tax_id) : 0;
            $sale    = $prod->sale_price ?? 0;
            $total   = $sale;
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' computed', ['unit' => $unit, 'taxRate' => $taxRate, 'total' => $total]);
            return response()->json(['product' => $prod, 'unit' => $unit, 'taxRate' => $taxRate, DatabaseConstants::TABLE_TAXES => $taxes, 'totalAmount' => $total]);
        } catch (\Throwable $e) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage(), 'product_id' => $req->product_id ?? null]);
            return response()->json(['error' => 'Product not found'], 404);
        }
    }

    public function items(Request $req): JsonResponse
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' start', ['invoice_id' => $req->invoice_id ?? null, 'product_id' => $req->product_id ?? null]);
        $item = InvoiceProduct::where('invoice_id', $req->invoice_id ?? null)->where('product_id', $req->product_id ?? null)->first();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' found', ['exists' => (bool) $item]);
        return response()->json($item);
    }

    public const GET_INV = 'getInvoice';
    public function getInvoice(Request $req): JsonResponse
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $req->id ?? null]);
        try {
            $due = Invoice::findOrFail($req->id)->getDue();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' due', ['due' => $due]);
            return response()->json(['due' => $due]);
        } catch (\Throwable $e) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not found', ['id' => $req->id ?? null, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Invoice not found'], 404);
        }
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
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' alias called');
        return self::_nextNumber();
    }

    public function show(Request $req, string $encId): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.view';
        return $this->measureProfile($action, function () use ($req, $encId, $action, $method, $class, $base, $viewPath) {
            Log::debug("[{$base}::{$action}] start", ['enc_id' => $encId]);
            if (($r = self::guard($req, 'show invoice', VW::INV . '.index')) !== true) return $r;
            try {
                $id = Crypt::decrypt($encId);
                Log::info("[{$base}::{$action}] decrypted id", ['id' => $id]);
                $invoice = Invoice::with('credit_note')->findOrFail($id);
                if (($invoice->created_by ?? null) !== ($req->user()->creatorId() ?? null))
                    return defaultPermissionDenial($req, new \Exception('not owner'), $class . '::' . $action);
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();
                $viewData = [
                    self::SINGULAR => $invoice,
                    PermissionsConstants::CT => $invoice->customer,
                    'items' => $invoice->items,
                    'invoicePayment' => $invoicePayment,
                    DatabaseConstants::TABLE_CUSTOM_FIELDS => CustomField::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())->where('module', self::SINGULAR)->get(),
                    'user' => $req->user(),
                    'invoice_user' => User::find($invoice->created_by),
                    'user_plan' => Plan::getPlan(User::find($invoice->created_by)?->plan),
                ];
                Log::info("[{$base}::{$action}] data ready", ['invoice_id' => $id]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                return response()->view($viewPath, $viewData);
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                return self::_catch($req, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    public const PRD_DST = 'productDestroy';
    public function productDestroy(Request $req): RedirectResponse
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $req->id ?? null, 'amount' => $req->amount ?? null]);
        if ($r = self::_authorize($req, 'delete invoice product')) return $r;
        $invProd = InvoiceProduct::findOrFail($req->id);
        $invoice = Invoice::findOrFail($invProd->invoice_id);
        Utility::updateUserBalance(PermissionsConstants::CT, $invoice->customer_id, $req->amount ?? 0, 'debit');
        $invProd->delete();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' product deleted', ['id' => $req->id]);
        return back()->with('success', ucfirst(self::SINGULAR) . ' product successfully deleted.');
    }

    public const CST_INV = 'customerInvoice';
    public function customerInvoice(Request $req): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::debug("[{$base}::{$action}] start", ['user' => $req->user()->id ?? null]);
            if (($r = self::guard($req, 'manage customer invoice', VW::INV . '.index')) !== true) return $r;
            $status = Invoice::$statuses;
            $q = Invoice::where('customer_id', $req->user()->id ?? null)->where('status', '!=', 0)->where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId() ?? null);
            if ($req->issue_date ?? null) $q->whereBetween('issue_date', explode(' - ', $req->issue_date));
            if ($req->status ?? null) $q->where('status', $req->status);
            $invoices = $q->get();
            Log::info("[{$base}::{$action}] fetched", ['count' => $invoices->count()]);
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            return response()->view($viewPath, [DatabaseConstants::TABLE_INVS => $invoices, 'status' => $status]);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const CST_INV_SHW = 'customerInvoiceShow';
    public function customerInvoiceShow(Request $req, string|int $id): Response|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            Log::debug("[{$base}::{$action}] start", ['id' => $id]);
            $invoice = Invoice::with('payments.bankAccount')->findOrFail($id);
            if (($invoice->customer_id ?? null) !== ($req->user()->id ?? null)) {
                Log::warning("[{$base}::{$action}] denied", ['user' => Auth::id()]);
                return back()->with('error', 'Permission denied.');
            }
            $viewPath = $invoice->owner?->type === PermissionsConstants::SA ? VW::INV . '.view' : VW::INV . '.customer_invoice';
            Log::info("[{$base}::{$action}] view", ['view' => $viewPath]);
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            return response()->view($viewPath, [self::SINGULAR => $invoice, PermissionsConstants::CT => $invoice->customer, 'items' => $invoice->items, 'user' => $invoice->owner ?? User::find($invoice->created_by)]);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $id]);
    }

    public function sent(Request $req, string|int $id): RedirectResponse
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' start', ['invoice_id' => $id]);
        if (($r = self::guard($req, 'send invoice', VW::INV . '.index')) !== true) return $r;
        try {
            DB::transaction(function () use ($req, $id) {
                $settings = Utility::settings();
                if (!($settings['customer_invoice_sent'] ?? 0)) throw new \Exception('Mail disabled by admin.');
                $invoice = Invoice::findOrFail($id);
                $invoice->update(['send_date' => now()->toDateString(), 'status' => 1]);
                $customer = Customer::find($invoice->customer_id);
                Utility::updateUserBalance(PermissionsConstants::CT, $customer?->id, $invoice->getTotal(), 'credit');
                $this->_mailInvoice($invoice, $customer);
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' invoice sent', ['invoice_id' => $id]);
            });
            return back()->with('success', ucfirst(self::SINGULAR) . ' successfully sent.');
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Mail disabled by admin.') {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' mail disabled', ['user' => Auth::id()]);
                return back()->with('error', $e->getMessage());
            }
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage(), 'invoice_id' => $id]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function resent(Request $req, string|int $id): RedirectResponse
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $id]);
        if (($r = self::guard($req, 'send invoice', VW::INV . '.index')) !== true) return $r;
        $invoice = Invoice::findOrFail($id);
        $customer = Customer::find($invoice->customer_id);
        $this->_mailInvoice($invoice, $customer);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' resent', ['invoice_id' => $id]);
        return back()->with('success', ucfirst(self::SINGULAR) . ' successfully sent.');
    }

    public function payment(Request $req, string|int $invoiceId): Response|RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.payment';
        return $this->measureProfile($action, function () use ($req, $invoiceId, $action, $method, $class, $base, $viewPath) {
            Log::debug("[{$base}::{$action}] start", ['invoice_id' => $invoiceId ?? null]);
            if (($r = self::guard($req, 'create payment invoice', VW::INV . '.index')) !== true) return $r;
            $invoice = Invoice::findOrFail($invoiceId);
            $viewData = [
                self::SINGULAR => $invoice,
                DatabaseConstants::TABLE_CUSTOMERS => Customer::where(DatabaseConstants::TABLE_CREATOR, $req->user()?->creatorId() ?? null)->pluck('name', 'id'),
                'categories' => ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $req->user()?->creatorId() ?? null)->pluck('name', 'id'),
                'accounts' => BankAccount::select('*', DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                    ->where(DatabaseConstants::TABLE_CREATOR, $req->user()?->creatorId() ?? null)
                    ->pluck('name', 'id'),
            ];
            Log::info("[{$base}::{$action}] view data ready", ['invoice_id' => $invoice->id ?? null]);
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            return response()->view($viewPath, $viewData);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId ?? null]);
    }

    public const PAY_CRT = 'createPayment';
    public function createPayment(Request $req, string|int $invoiceId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $invoiceId, $action, $method, $class, $base) {
            Log::debug("[{$base}::{$action}] start", ['invoice_id' => $invoiceId ?? null]);
            if (($r = self::guard($req, 'create payment invoice', VW::INV . '.index')) !== true) return $r;
            $invoice = Invoice::findOrFail($invoiceId);
            if (($invoice->getSubTotal() ?? 0) < ($req->amount ?? 0)) return back()->with('error', 'Amount exceeds subtotal.');
            if ($r = self::_validate($req->all(), ['date' => 'required', 'amount' => 'required', 'account_id' => 'required'])) return $r;
            try {
                DB::transaction(function () use ($req, $invoice, $base, $action) {
                    $pay = InvoicePayment::create([
                        'invoice_id'     => $invoice->id,
                        'date'           => $req->date ?? now()->toDateString(),
                        'amount'         => $req->amount ?? 0,
                        'account_id'     => $req->account_id ?? null,
                        'payment_method' => 0,
                        'reference'      => $req->reference ?? null,
                        'description'    => $req->description ?? null,
                        'add_receipt'    => $this->_handleReceipt($req),
                    ]);
                    $this->_syncInvoiceAfterPayment($invoice, $pay, $req->amount ?? 0);
                    Log::info("[{$base}::{$action}] payment added", ['payment_id' => $pay->id ?? null, 'invoice_id' => $invoice->id ?? null]);
                });
                return back()->with('success', 'Payment successfully added.');
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId ?? null]);
    }

    public const PAY_DST = 'paymentDestroy';
    public function paymentDestroy(Request $req, string|int $invoiceId, string|int $paymentId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $invoiceId, $paymentId, $action, $method, $class, $base) {
            Log::debug("[{$base}::{$action}] start", ['payment_id' => $paymentId ?? null, 'invoice_id' => $invoiceId ?? null]);
            if (($r = self::guard($req, 'delete payment invoice', VW::INV . '.index')) !== true) return $r;
            try {
                DB::transaction(function () use ($req, $invoiceId, $paymentId, $base, $action) {
                    $pay     = InvoicePayment::findOrFail($paymentId);
                    $invoice = Invoice::findOrFail($invoiceId);
                    $pay->delete();
                    InvoiceBankTransfer::where('payment_id', $paymentId)->delete();
                    $due = $invoice->getDue();
                    $invoice->status = (($due ?? 0) > 0 && ($invoice->getTotal() ?? 0) !== ($due ?? 0)) ? 3 : 2;
                    $invoice->save();
                    if ($pay->add_receipt ?? false) {
                        Utility::changeStorageLimit($req->user()?->creatorId() ?? null, '/uploads/payment/' . $pay->add_receipt);
                    }
                    Transaction::destroyTransaction($paymentId, 'Partial', ucfirst(PermissionsConstants::CT));
                    Utility::updateUserBalance(PermissionsConstants::CT, $invoice->customer_id ?? null, $pay->amount ?? 0, 'credit');
                    Utility::bankAccountBalance($pay->account_id ?? null, $pay->amount ?? 0, 'debit');
                    Log::info("[{$base}::{$action}] payment deleted", ['payment_id' => $paymentId ?? null]);
                });
                return back()->with('success', 'Payment successfully deleted.');
            } catch (Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'payment_id' => $paymentId ?? null, 'invoice_id' => $invoiceId ?? null]);
    }

    public const PAY_RMD = 'paymentReminder';
    public function paymentReminder(Request $req, string|int $invoiceId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $invoiceId, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'invoice_id' => $invoiceId ?? null]);
            if (($r = self::guard($req, 'create payment invoice', VW::INV . '.index')) !== true) return $r;
            $invoice  = Invoice::findOrFail($invoiceId);
            $customer = Customer::find($invoice->customer_id);
            $setting  = Utility::settings($req->user()?->creatorId() ?? null);
            if (($setting['twilio_reminder_notification'] ?? 0) == 1) {
                Utility::sendTwilioMsg($customer?->contact ?? '', 'invoice_payment_reminder', [
                    'invoice_number' => $user?->invoiceNumberFormat($invoice->invoice_id ?? 0),
                    'customer_name'  => $customer?->name ?? '',
                    'user_name'      => $req->user()?->name ?? '',
                ]);
                Log::info("[{$base}::{$action}] twilio sent", ['contact' => $customer?->contact ?? null, 'invoice_id' => $invoiceId ?? null]);
            }
            $resp = ['is_success' => true];
            if (($setting['new_payment_reminder'] ?? 0) == 1) {
                $payload = [
                    'payment_reminder_name'      => $customer?->name ?? '',
                    'invoice_payment_number'     => $user?->invoiceNumberFormat($invoice->invoice_id ?? 0),
                    'invoice_payment_dueAmount'  => $user?->priceFormat($invoice->getDue() ?? 0),
                    'payment_reminder_date'      => $user?->dateFormat($invoice->send_date ?? now()->toDateString()),
                ];
                $resp = Utility::sendEmailTemplate('new_payment_reminder', [$customer?->id => $customer?->email], $payload);
                Log::info("[{$base}::{$action}] email sent", ['email' => $customer?->email ?? null, 'is_success' => $resp['is_success'] ?? false, 'error' => $resp['error'] ?? null]);
            }
            $msg = 'Payment reminder successfully sent.' . (!($resp['is_success'] ?? true) && !empty($resp['error']) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : '');
            Log::info("[{$base}::{$action}] completed", ['final_msg' => strip_tags($msg), 'is_success' => $resp['is_success'] ?? false]);
            return back()->with('success', $msg);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $invoiceId ?? null]);
    }

    public const SHP_DSP = 'shippingDisplay';
    public function shippingDisplay(Request $req, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            Log::debug("[{$base}::{$action}] start", ['invoice_id' => $id ?? null]);
            if (($r = self::guard($req, 'edit invoice', VW::INV . '.index')) !== true) return $r;
            $invoice = Invoice::findOrFail($id);
            $invoice->shipping_display = filter_var($req->is_display ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $invoice->save();
            Log::info("[{$base}::{$action}] updated", ['invoice_id' => $id ?? null, 'shipping_display' => $invoice->shipping_display ?? null]);
            return back()->with('success', 'Shipping address status updated.');
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $id ?? null]);
    }

    public function duplicate(Request $req, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            Log::debug("[{$base}::{$action}] start", ['invoice_id' => $id ?? null, UsersConstants::COL_USER_ID => $req->user()?->id ?? null]);
            if (($r = self::guard($req, 'duplicate invoice', VW::INV . '.index')) !== true) return $r;
            $inv = Invoice::findOrFail($id);
            DB::transaction(function () use ($inv, $base, $action) {
                $dup = $inv->replicate(['invoice_id', 'issue_date', 'send_date', 'status']);
                $dup->invoice_id = self::_nextNumber();
                $dup->issue_date = now()->toDateString();
                $dup->status     = 0;
                $dup->save();
                $inv->items->each(function ($p) use ($dup) {
                    InvoiceProduct::create($p->only(['product_id', 'quantity', 'tax', 'discount', 'price']) + ['invoice_id' => $dup->id]);
                });
                Log::info("[{$base}::{$action}] completed", ['original_id' => $inv->id ?? null, 'duplicate_id' => $dup->id ?? null]);
            });
            return back()->with('success', ucfirst(self::SINGULAR) . ' duplicated successfully.');
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'invoice_id' => $id ?? null]);
    }

    public const IV_LK = 'invoiceLink';
    public function invoiceLink(Request $req, string $encId): Response|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::INV . '.customer_invoice';
        return $this->measureProfile($action, function () use ($req, $encId, $action, $method, $class, $base, $viewPath) {
            Log::debug("[{$base}::{$action}] start", ['enc_id' => $encId]);
            try {
                $id = Crypt::decrypt($encId);
                Log::info("[{$base}::{$action}] decrypted", ['id' => $id]);
            } catch (Throwable $e) {
                Log::warning("[{$base}::{$action}] decrypt failed", ['enc_id' => $encId, 'error' => $e->getMessage()]);
                return back()->with('error', ucfirst(self::SINGULAR) . ' Not Found.');
            }
            $invoice  = Invoice::findOrFail($id);
            $settings = Utility::settingsById($invoice->created_by ?? null);
            $data = [
                DatabaseConstants::TABLE_SETTINGS => $settings,
                self::SINGULAR => $invoice,
                PermissionsConstants::CT => $invoice->customer,
                'items' => $invoice->items,
                'invoicePayment' => InvoicePayment::where('invoice_id', $id)->get(),
                DatabaseConstants::TABLE_CUSTOM_FIELDS => CustomField::where('module', self::SINGULAR)->get(),
                'user' => User::find($invoice->created_by),
                'company_payment_setting' => Utility::getCompanyPaymentSetting($invoice->created_by ?? null),
                'invoice_user' => User::find($invoice->created_by),
                'user_plan' => Plan::getPlan(User::find($invoice->created_by)?->plan),
            ];
            Log::info("[{$base}::{$action}] data prepared", ['invoice_id' => $id ?? null]);
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            return response()->view($viewPath, $data);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    public const SV_IV_TMP = 'saveTemplateSettings';
    public function saveTemplateSettings(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => Auth::id()]);
            $post = $req->except('_token');
            if (!empty($post[BillsConstants::COL_INV_TMP]) && empty($post['invoice_color'])) {
                $post['invoice_color'] = 'ffffff';
                Log::info("[{$base}::{$action}] default color applied", ['invoice_color' => 'ffffff']);
            }
            if ($req->file('invoice_logo')) {
                $fn   = (Auth::id() ?? 'user') . '_invoice_logo.png';
                $size = $req->file('invoice_logo')->getSize() ?? 0;
                $path = Utility::uploadFile($req, 'invoice_logo', $fn, 'invoice_logo/', ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]);
                if (($path['flag'] ?? 0) == 0) {
                    Log::warning("[{$base}::{$action}] logo upload failed", ['error' => $path['msg'] ?? null]);
                    return back()->with('error', $path['msg'] ?? __('Upload failed.'));
                }
                $post['invoice_logo'] = $fn;
                Log::info("[{$base}::{$action}] logo uploaded", ['file' => $fn, 'size' => $size]);
            }
            foreach ($post as $k => $v) {
                DB::table('settings')->updateOrInsert(
                    ['name' => $k, DatabaseConstants::TABLE_CREATOR => $user?->creatorId()],
                    ['value' => $v, 'name' => $k, DatabaseConstants::TABLE_CREATOR => $user?->creatorId()]
                );
                Log::info("[{$base}::{$action}] setting saved", ['name' => $k, 'value' => $v]);
            }
            Log::info("[{$base}::{$action}] completed");
            return back()->with('success', ucfirst(self::SINGULAR) . ' setting updated successfully');
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function invoice(Request $request, string $encId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);

        return $this->measureProfile($action, function () use ($request, $encId, $action, $method, $class, $base) {
            Log::debug("[{$base}::{$action}] start", ['enc_id' => $encId ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);

            if (($r = self::guard($request, PermissionsConstants::MNG_INV, VW::INV . '.index')) !== true) return $r;

            try {
                $id = Crypt::decrypt($encId);
                Log::info("[{$base}::{$action}] decrypted id", ['id' => $id ?? null]);

                $invoice = Invoice::with('items.product')->findOrFail($id);
                if (($invoice->created_by ?? null) !== ($request->user()?->creatorId() ?? null)) {
                    Log::warning("[{$base}::{$action}] ownership mismatch", [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'invoiceId' => $invoice->id ?? null]);
                    return defaultPermissionDenial($request, new \Exception('owner'), $method, route(VW::INV . '.index'));
                }

                $settings = Utility::settingsById($invoice->created_by ?? null);
                Log::info("[{$base}::{$action}] settings loaded", ['count' => is_array($settings) ? count($settings) : 0]);

                $customer  = $invoice->customer;
                $items     = [];
                $totalTax = $totalQty = $totalRate = $totalDisc = 0;
                $taxesData = [];

                foreach ($invoice->items as $prod) {
                    $item = (object)[
                        'name'        => $prod->product->name ?? '',
                        'quantity'    => $prod->quantity ?? 0,
                        'tax'         => $prod->tax ?? null,
                        'unit'        => $prod->product->unit_id ?? '',
                        'discount'    => $prod->discount ?? 0,
                        'price'       => $prod->price ?? 0,
                        'description' => $prod->description ?? null,
                    ];

                    $totalQty  += (float)$item->quantity;
                    $totalRate += (float)$item->price;
                    $totalDisc += (float)$item->discount;

                    $itemTaxes = [];
                    if (!empty($item->tax)) {
                        foreach (Utility::tax($item->tax) as $tx) {
                            $tp = Utility::taxRate($tx->rate ?? 0, $item->price ?? 0, $item->quantity ?? 0, $item->discount ?? 0);
                            $totalTax += $tp;
                            $it = [
                                'name'      => $tx->name ?? '',
                                'rate'      => sprintf('%s%%', $tx->rate ?? 0),
                                'price'     => Utility::priceFormat($settings, $tp),
                                'tax_price' => $tp,
                            ];
                            $itemTaxes[] = $it;
                            $taxesData[$tx->name ?? ''] = ($taxesData[$tx->name ?? ''] ?? 0) + $tp;
                        }
                    }

                    $item->itemTax = $itemTaxes;
                    $items[] = $item;
                }

                $invoice->itemData       = $items;
                $invoice->totalTaxPrice  = $totalTax;
                $invoice->totalQuantity  = $totalQty;
                $invoice->totalRate      = $totalRate;
                $invoice->totalDiscount  = $totalDisc;
                $invoice->taxesData      = $taxesData;
                $invoice->customField    = CustomField::getData($invoice, self::SINGULAR);

                $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $request->user()?->creatorId() ?? null)
                    ->where('module', self::SINGULAR)->get();

                $logo    = asset(Storage::url('uploads/logo/'));
                $invLogo = $settings['invoice_logo'] ?? '';
                $img     = $invLogo
                    ? (Utility::getFile('invoice_logo/') . $invLogo)
                    : asset($logo . '/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF));

                $hex      = $settings['invoice_color'] ?? 'ffffff';
                $color    = '#' . ltrim($hex, '#');
                $fontColor = Utility::getFontColor($color);

                $viewPath = VW::INV_TMP . ($settings[BillsConstants::COL_INV_TMP] ?? '');
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                Log::info("[{$base}::{$action}] succeeded", ['invoiceId' => $invoice->id ?? null]);

                return response()->view($viewPath, compact(
                    self::SINGULAR,
                    'color',
                    DatabaseConstants::TABLE_SETTINGS,
                    PermissionsConstants::CT,
                    'img',
                    'fontColor',
                    DatabaseConstants::TABLE_CUSTOM_FIELDS
                ));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                return self::_catch($request, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    public const CST_INV_SD = 'customerInvoiceSend';
    public function customerInvoiceSend(Request $request, string $encId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);

        return $this->measureProfile($action, function () use ($request, $encId, $action, $method, $class, $base) {
            if (($r = self::guard($request, 'send invoice', VW::INV . '.index')) !== true) return $r;
            Log::debug("[{$base}::{$action}] start", ['enc_id' => $encId ?? null, UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);

            $viewPath = VW::CST . '.invoice_send';
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");

            return response()->view($viewPath, compact('encId'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    public const CST_INV_SD_ML = 'customerInvoiceSendMail';
    public function customerInvoiceSendMail(Request $request, string $encId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);

        return $this->measureProfile($action, function () use ($request, $encId, $action, $method, $class, $base) {
            if (($r = self::guard($request, 'send invoice', VW::INV . '.index')) !== true) return $r;
            if ($v = self::_validate($request->all(), ['email' => 'required|email'])) return $v;

            Log::debug("[{$base}::{$action}] start", ['email' => $request->email ?? null, 'enc_id' => $encId ?? null]);

            try {
                $id      = Crypt::decrypt($encId);
                $invoice = Invoice::findOrFail($id);

                if (($invoice->created_by ?? null) !== ($request->user()?->creatorId() ?? null)) {
                    Log::warning("[{$base}::{$action}] ownership mismatch", [UsersConstants::COL_USER_ID => $request->user()?->id ?? null, 'invoiceId' => $id ?? null]);
                    return defaultPermissionDenial($request, new \Exception('owner'), $method, route(VW::INV . '.index'));
                }

                $customer        = Customer::find($invoice->customer_id);
                $invoice->name   = $customer->name ?? '';
                $invoice->invoice = $request->user()?->invoiceNumberFormat($invoice->invoice_id ?? 0);
                $invoice->url    = route(VW::INV . '.pdf', Crypt::encryptString($invoice->id ?? 0));

                Mail::to($request->email)->send(new CustomerInvoiceSend($invoice));
                Log::info("[{$base}::{$action}] mail sent", ['to' => $request->email ?? null, 'invoiceId' => $id ?? null]);

                return redirect()->back()->with('success', __(ucfirst(self::SINGULAR) . ' successfully sent.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(VW::INV . '.index'));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    public const INV_PRV = 'previewInvoice';
    public function previewInvoice(Request $request, string $template, string $colorHex): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);

        return $this->measureProfile($action, function () use ($request, $template, $colorHex, $action, $method, $class, $base) {
            if (($r = self::guard($request, PermissionsConstants::MNG_INV, VW::INV . '.index')) !== true) return $r;
            Log::debug("[{$base}::{$action}] start", ['template' => $template ?? null, 'color' => $colorHex ?? null]);

            try {
                $user     = $request->user();
                $settings = Utility::settingsById($user?->creatorId() ?? null);

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
                    'billing_address' => '<Addr>',
                ];

                $items = [];
                $taxesData = [];
                $totalTax = $totalQty = $totalRate = $totalDisc = 0;

                for ($i = 1; $i <= 3; $i++) {
                    $item = (object)[
                        'name'        => "Item {$i}",
                        'quantity'    => 1,
                        'tax'         => 5,
                        'discount'    => 50,
                        'price'       => 100,
                        'unit'        => 1,
                        'description' => 'XYZ',
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

                    $totalQty  += $item->quantity;
                    $totalRate += $item->price;
                    $totalDisc += $item->discount;

                    $items[] = $item;
                }

                $invoice->itemData       = $items;
                $invoice->totalTaxPrice  = $totalTax;
                $invoice->totalQuantity  = $totalQty;
                $invoice->totalRate      = $totalRate;
                $invoice->totalDiscount  = $totalDisc;
                $invoice->taxesData      = $taxesData;
                $invoice->created_by     = $user?->creatorId() ?? null;
                $invoice->invoice_id     = 1;
                $invoice->issue_date     = now();
                $invoice->due_date       = now();

                $preview = true;
                $color   = '#' . ltrim($colorHex ?? 'ffffff', '#');
                $font    = Utility::getFontColor($color);
                $logo    = asset(Storage::url('uploads/logo/'));
                $invLogo = $settings['invoice_logo'] ?? '';
                $img     = $invLogo
                    ? (Utility::getFile('invoice_logo/') . $invLogo)
                    : asset($logo . '/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF));

                $viewPath = VW::INV_TMP . $template;
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");

                Log::info("[{$base}::{$action}] succeeded");

                return response()->view($viewPath, compact(
                    self::SINGULAR,
                    'preview',
                    'color',
                    'img',
                    DatabaseConstants::TABLE_SETTINGS,
                    PermissionsConstants::CT,
                    'font'
                ));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                return self::_catch($request, $e);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'template' => $template ?? null]);
    }

    private function _notifyInvoice(Invoice $invoice, bool $isNew = false)
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
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
            instanceof RedirectResponse
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
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $invoice->name    = $customer->name;
        $invoice->invoice = $user
            ->invoiceNumberFormat($invoice->invoice_id);
        $enc              = Crypt::encryptString($invoice->id);
        $invoice->url     = route(VW::INV . '.pdf', $enc);
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
            instanceof RedirectResponse
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
