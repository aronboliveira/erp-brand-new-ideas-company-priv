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
use App\Exports\BillExport;
use App\Mail\VendorBillMail;
use App\Models\{
    BankAccount,
    Bill,
    BillAccount,
    BillPayment,
    BillProduct,
    ChartOfAccount,
    CustomField,
    DebitNote,
    ProductService,
    ProductServiceCategory,
    StockReport,
    Transaction,
    User,
    Utility,
    Vendor
};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Crypt, DB, Log, Mail, Redirect, Route, Storage, View as ViewFacade};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BillController extends Controller
{

    use ChecksLogin;
    use ChecksPermissions;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $req): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $req;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($g = self::guard($request, PermissionsConstants::MNG_BIL)) !== true) return $g;
            Log::info("[{$base}::{$action}] listing bills", [UsersConstants::COL_USER_ID => $request->user()->id, 'filters' => $request->all(), 'method' => $method]);
            try {
                $uidStart = microtime(true);
                $uid = $request->user()->creatorId();
                $this->logExecutionTime($uidStart, $action, 'resolveCreatorId');
                $venStart = microtime(true);
                $vendor = self::_vendors()->prepend('Select Vendor', '');
                $this->logExecutionTime($venStart, $action, 'loadVendors');
                $stStart = microtime(true);
                $status = Bill::$statuses;
                $this->logExecutionTime($stStart, $action, 'loadStatuses');
                $qryStart = microtime(true);
                $bills = Bill::where('type', 'Bill')->where(DatabaseConstants::COL_TABLE_CREATOR, $uid)->when($request->vendor, fn($q) => $q->where('vendor_id', $request->vendor))->when($request->bill_date, function ($q) use ($request) {
                    $parts = array_map('trim', explode('to', $request->bill_date));
                    $range = count($parts) > 1 ? $parts : [$request->bill_date, $request->bill_date];
                    $q->whereBetween('bill_date', $range);
                })->when($request->status, fn($q) => $q->where('status', $request->status))->with('category')->get();
                $this->logExecutionTime($qryStart, $action, 'queryBills');
                Log::info("[{$base}::{$action}] loaded bills", ['count' => $bills->count()]);
                $viewPath = VW::BIL . '.index';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [DatabaseConstants::TABLE_BILLS, 'vendor', 'status']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact(DatabaseConstants::TABLE_BILLS, 'vendor', 'status'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'filters' => $request->all()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(int|string $vendorId): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($vendorId, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $request = request();
            if (($g = self::guard($request, 'create bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] showing bill create form", [UsersConstants::COL_USER_ID => $user?->id, 'vendor_id' => $vendorId, 'method' => $method]);
            try {
                $cfStart = microtime(true);
                $cf = CustomField::where([[DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()], ['module', 'bill']])->get();
                $this->logExecutionTime($cfStart, $action, 'loadCustomFields');
                $venStart = microtime(true);
                $vendors = self::_vendors()->prepend('Select Vendor', '');
                $this->logExecutionTime($venStart, $action, 'loadVendors');
                $psStart = microtime(true);
                $productServices = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id')->prepend('Select Item', '');
                $this->logExecutionTime($psStart, $action, 'loadProductServices');
                $catStart = microtime(true);
                $category = self::_categories()->prepend('Select Category', '');
                $this->logExecutionTime($catStart, $action, 'loadCategories');
                $accStart = microtime(true);
                $chartAccounts = self::_chartAccounts()->prepend('Select Account', '');
                $this->logExecutionTime($accStart, $action, 'loadChartAccounts');
                $numStart = microtime(true);
                $billNumber = $user?->billNumberFormat(self::_billNumber());
                $this->logExecutionTime($numStart, $action, 'formatBillNumber');
                $data = [DatabaseConstants::TABLE_VENDORS => $vendors, 'billNumber' => $billNumber, 'productServices' => $productServices, 'category' => $category, 'customFields' => $cf, 'vendorId' => $vendorId, 'chartAccounts' => $chartAccounts];
                $viewPath = VW::BIL . '.create';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'vendor_id' => $vendorId]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'data_keys' => array_keys($data)]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, $data);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'vendor_id' => $vendorId]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'vendor_id' => $vendorId]);
    }

    public function store(Request $req): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $req;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($g = self::guard($request, 'create bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] storing new bill", [UsersConstants::COL_USER_ID => $request->user()->id, 'input' => $request->all(), 'method' => $method]);
            $valStart = microtime(true);
            $request->validate(['vendor_id' => 'required|exists:vendors,id', 'bill_date' => 'required|date', 'due_date' => 'required|date']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                $txnStart = microtime(true);
                DB::beginTransaction();
                try {
                    $createStart = microtime(true);
                    $bill = Bill::create([
                        'bill_id' => self::_billNumber(),
                        'vendor_id' => $request->vendor_id,
                        'bill_date' => $request->bill_date,
                        'due_date' => $request->due_date,
                        'status' => 0,
                        'type' => 'Bill',
                        'user_type' => 'vendor',
                        'category_id' => $request->category_id ?: '0',
                        'order_id' => $request->order_id ?: '0',
                        DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createBill');
                    Log::info("[{$base}::{$action}] bill created", ['bill_id' => $bill->id]);
                    $cfStart = microtime(true);
                    CustomField::saveData($bill, $request->customField ?? []);
                    $this->logExecutionTime($cfStart, $action, 'saveCustomFields');
                    $total = 0;
                    $loopStart = microtime(true);
                    foreach ($request->items ?? [] as $item) {
                        if (!empty($item['item'])) {
                            $bpStart = microtime(true);
                            $bp = BillProduct::create([
                                'bill_id' => $bill->id,
                                'product_id' => $item['item'],
                                'quantity' => $item['quantity'],
                                'tax' => $item['tax'],
                                'discount' => $item['discount'],
                                'price' => $item['price'],
                                'description' => $item['description'],
                            ]);
                            $this->logExecutionTime($bpStart, $action, 'createBillProduct');
                            Utility::totalQuantity('plus', $bp->quantity, $bp->product_id);
                            Utility::addProductStock($bp->product_id, $bp->quantity, 'bill', "{$bp->quantity} purchased in bill " . $user?->billNumberFormat($bill->bill_id), $bill->id);
                            $total += $bp->quantity * $bp->price;
                            Log::info("[{$base}::{$action}] bill product created", ['bp_id' => $bp->id]);
                        }
                        if (!empty($item['chart_account_id'])) {
                            $baStart = microtime(true);
                            $ba = BillAccount::create([
                                'chart_account_id' => $item['chart_account_id'],
                                'price' => $item['amount'],
                                'description' => $item['description'],
                                'type' => 'Bill',
                                'ref_id' => $bill->id,
                            ]);
                            $this->logExecutionTime($baStart, $action, 'createBillAccount');
                            $total += $ba->price;
                            Log::info("[{$base}::{$action}] bill account created", ['ba_id' => $ba->id]);
                        }
                    }
                    $this->logExecutionTime($loopStart, $action, 'processItems');
                    if ($request->chart_account_id) {
                        $catStart = microtime(true);
                        $cat = ProductServiceCategory::find($request->category_id);
                        $this->logExecutionTime($catStart, $action, 'findCategory');
                        $bacStart = microtime(true);
                        $bac = BillAccount::create([
                            'chart_account_id' => $cat->chart_account_id ?? 0,
                            'price' => $total,
                            'description' => $request->description,
                            'type' => 'Bill Category',
                            'ref_id' => $bill->id,
                        ]);
                        $this->logExecutionTime($bacStart, $action, 'createCategoryBillAccount');
                        Log::info("[{$base}::{$action}] category bill account created", ['ba_id' => $bac->id, 'total' => $total]);
                    }
                    DB::commit();
                    $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                    return Redirect::route(VW::BIL . '.index')->with('success', __('Bill successfully created.'));
                } catch (\Throwable $e) {
                    DB::rollBack();
                    $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                    Log::error("[{$base}::{$action}] store failed", ['error' => $e->getMessage()]);
                    Log::debug("[{$base}::{$action}] failure context", ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'input' => $request->all()]);
                    throw $e;
                }
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] unexpected error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(string $encrypted): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($encrypted, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $req = request();
            if (($g = self::guard($req, 'show bill')) !== true) return $g;
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($encrypted);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $billStart = microtime(true);
                $bill = Bill::with('debitNote')->findOrFail($id);
                $this->logExecutionTime($billStart, $action, 'findBill');
                if (!self::_isOwner($bill)) {
                    Log::warning("[{$base}::{$action}] unauthorized show bill", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] ownership check failed", ['expected_user_id' => $user?->id, 'bill_creator' => $bill->{DatabaseConstants::COL_TABLE_CREATOR} ?? null]);
                    throw new \Illuminate\Auth\Access\AuthorizationException;
                }
                Log::info("[{$base}::{$action}] showing bill detail", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $id]);
                $venStart = microtime(true);
                $vendor = $bill->vendor;
                $this->logExecutionTime($venStart, $action, 'loadVendor');
                $payStart = microtime(true);
                $payment = BillPayment::where('bill_id', $id)->first();
                $this->logExecutionTime($payStart, $action, 'loadPayment');
                $itemsStart = microtime(true);
                $items = $this->_mergeItemsAccounts($bill);
                $this->logExecutionTime($itemsStart, $action, 'mergeItemsAccounts');
                $cfStart = microtime(true);
                $cf = CustomField::where([[DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()], ['module', 'bill']])->get();
                $this->logExecutionTime($cfStart, $action, 'loadCustomFields');
                $viewPath = VW::BIL . '.view';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bill', 'vendor', 'items', 'payment', 'cf']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('bill', 'vendor', 'items', 'payment', 'cf'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'encrypted' => $encrypted]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'encrypted' => $encrypted]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(string $encrypted): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($encrypted, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $req = request();
            if (($g = self::guard($req, 'edit bill')) !== true) return $g;
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($encrypted);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $billStart = microtime(true);
                $bill = Bill::findOrFail($id);
                $this->logExecutionTime($billStart, $action, 'findBill');
                if (!self::_isOwner($bill)) {
                    Log::warning("[{$base}::{$action}] unauthorized edit bill", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] ownership check failed", ['expected_user_id' => $user?->id, 'bill_creator' => $bill->{DatabaseConstants::COL_TABLE_CREATOR} ?? null]);
                    throw new AuthorizationException;
                }
                Log::info("[{$base}::{$action}] editing bill", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $id, 'method' => $method]);
                $venStart = microtime(true);
                $vendors = self::_vendors();
                $this->logExecutionTime($venStart, $action, 'loadVendors');
                $psStart = microtime(true);
                $productServices = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
                $this->logExecutionTime($psStart, $action, 'loadProductServices');
                $numStart = microtime(true);
                $billNumber = $user->billNumberFormat($bill->bill_id);
                $this->logExecutionTime($numStart, $action, 'formatBillNumber');
                $catStart = microtime(true);
                $category = self::_categories()->prepend('Select Category', '');
                $this->logExecutionTime($catStart, $action, 'loadCategories');
                $cfStart = microtime(true);
                $customFields = CustomField::where([[DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()], ['module', 'bill']])->get();
                $this->logExecutionTime($cfStart, $action, 'loadCustomFields');
                $accStart = microtime(true);
                $chartAccounts = self::_chartAccounts()->prepend('Select Account', '');
                $this->logExecutionTime($accStart, $action, 'loadChartAccounts');
                $itemsStart = microtime(true);
                $items = $this->_mergeItemsAccounts($bill);
                $this->logExecutionTime($itemsStart, $action, 'mergeItemsAccounts');
                $data = [DatabaseConstants::TABLE_VENDORS => $vendors, 'productServices' => $productServices, 'bill' => $bill, 'billNumber' => $billNumber, 'category' => $category, 'customFields' => $customFields, 'chartAccounts' => $chartAccounts, 'items' => $items];
                $viewPath = VW::BIL . '.edit';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'data_keys' => array_keys($data)]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, $data);
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'encrypted' => $encrypted]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'encrypted' => $encrypted]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'encrypted' => $encrypted]);
    }

    public function update(Request $req, Bill $bill): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $req;
        return $this->measureProfile($action, function () use ($request, $bill, $action, $method, $class, $base) {
            if (($g = self::guard($request, 'edit bill')) !== true) return $g;
            if (!self::_isOwner($bill)) {
                Log::warning("[{$base}::{$action}] unauthorized update bill", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $bill->id]);
                Log::debug("[{$base}::{$action}] ownership check failed", ['expected_user_id' => auth()->id(), 'bill_creator' => $bill->{DatabaseConstants::COL_TABLE_CREATOR} ?? null]);
                return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
            }
            Log::info("[{$base}::{$action}] updating bill", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $bill->id, 'input' => $request->all(), 'method' => $method]);
            $valStart = microtime(true);
            $request->validate(['vendor_id' => 'required|exists:vendors,id', 'bill_date' => 'required|date', 'due_date' => 'required|date']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $txnStart = microtime(true);
                DB::beginTransaction();
                $updStart = microtime(true);
                $bill->update(['vendor_id' => $request->vendor_id, 'bill_date' => $request->bill_date, 'due_date' => $request->due_date, 'order_id' => $request->order_id, 'category_id' => $request->category_id]);
                $this->logExecutionTime($updStart, $action, 'updateBill');
                $cfStart = microtime(true);
                CustomField::saveData($bill, $request->customField ?? []);
                $this->logExecutionTime($cfStart, $action, 'saveCustomFields');
                $total = 0;
                $existingStart = microtime(true);
                $existing = BillProduct::where('bill_id', $bill->id)->get()->keyBy('id');
                $this->logExecutionTime($existingStart, $action, 'loadExistingBillProducts');
                $seenIds = [];
                $loopStart = microtime(true);
                foreach (($request->items ?? []) as $row) {
                    $rowId = $row['id'] ?? null;
                    $newProd = $row['items'] ?? null;
                    $newQty  = (float) ($row['quantity'] ?? 0);
                    $newTax  = $row['tax'] ?? 0;
                    $newDisc = $row['discount'] ?? 0;
                    $newPrice = $row['price'] ?? 0;
                    $newDesc  = $row['description'] ?? '';
                    if ($rowId && $existing->has($rowId)) {
                        $bp = $existing[$rowId];
                        $oldProd = $bp->product_id;
                        $oldQty  = (float) $bp->quantity;
                        $bpStart = microtime(true);
                        $bp->update([
                            'product_id' => $newProd ?? $bp->product_id,
                            'quantity'   => $newQty,
                            'tax'        => $newTax,
                            'discount'   => $newDisc,
                            'price'      => $newPrice,
                            'description' => $newDesc,
                        ]);
                        $this->logExecutionTime($bpStart, $action, 'saveBillProduct');
                        if ($newProd && $oldProd !== $newProd) {
                            if ($oldQty > 0) Utility::totalQuantity('minus', $oldQty, $oldProd);
                            if ($newQty > 0) Utility::totalQuantity('plus', $newQty, $newProd);
                        } else {
                            $delta = $newQty - $oldQty;
                            if ($delta > 0) Utility::totalQuantity('plus', $delta, $bp->product_id);
                            elseif ($delta < 0) Utility::totalQuantity('minus', abs($delta), $bp->product_id);
                        }
                        $total += $bp->quantity * $bp->price;
                        $seenIds[] = $bp->id;
                        if (!empty($row['chart_account_id'])) {
                            $baStart = microtime(true);
                            $ba = BillAccount::updateOrCreate(
                                ['id' => $row['id'] ?? null, 'type' => 'Bill', 'ref_id' => $bill->id],
                                ['chart_account_id' => $row['chart_account_id'], 'price' => $row['amount'] ?? 0, 'description' => $newDesc]
                            );
                            $this->logExecutionTime($baStart, $action, 'saveBillAccount');
                            $total += $ba->price;
                            Log::info("[{$base}::{$action}] bill account saved", ['ba_id' => $ba->id, 'bill_id' => $bill->id]);
                        }
                    } elseif (!empty($newProd)) {
                        $bpCreateStart = microtime(true);
                        $bp = BillProduct::create([
                            'bill_id'    => $bill->id,
                            'product_id' => $newProd,
                            'quantity'   => $newQty,
                            'tax'        => $newTax,
                            'discount'   => $newDisc,
                            'price'      => $newPrice,
                            'description' => $newDesc,
                        ]);
                        $this->logExecutionTime($bpCreateStart, $action, 'createBillProduct');
                        Utility::totalQuantity('plus', $bp->quantity, $bp->product_id);
                        $total += $bp->quantity * $bp->price;
                        $seenIds[] = $bp->id;
                        Log::info("[{$base}::{$action}] bill product created", ['bp_id' => $bp->id, 'bill_id' => $bill->id]);
                        if (!empty($row['chart_account_id'])) {
                            $baStart = microtime(true);
                            $ba = BillAccount::updateOrCreate(
                                ['id' => $row['id'] ?? null, 'type' => 'Bill', 'ref_id' => $bill->id],
                                ['chart_account_id' => $row['chart_account_id'], 'price' => $row['amount'] ?? 0, 'description' => $newDesc]
                            );
                            $this->logExecutionTime($baStart, $action, 'saveBillAccount');
                            $total += $ba->price;
                            Log::info("[{$base}::{$action}] bill account saved", ['ba_id' => $ba->id, 'bill_id' => $bill->id]);
                        }
                    }
                }
                $this->logExecutionTime($loopStart, $action, 'processItems');
                $deletionsStart = microtime(true);
                foreach ($existing as $id => $bp)
                    if (!in_array($id, $seenIds, true)) {
                        Utility::totalQuantity('minus', $bp->quantity, $bp->product_id);
                        $bp->delete();
                        Log::info("[{$base}::{$action}] removed bill product", ['bp_id' => $id, 'bill_id' => $bill->id]);
                    }
                $this->logExecutionTime($deletionsStart, $action, 'removeDeletedItems');
                if ($request->chart_account_id) {
                    $catStart = microtime(true);
                    $cat = ProductServiceCategory::find($request->category_id);
                    $this->logExecutionTime($catStart, $action, 'findCategory');
                    $bacStart = microtime(true);
                    $bac = BillAccount::updateOrCreate(
                        ['type' => 'Bill Category', 'ref_id' => $bill->id],
                        ['chart_account_id' => $cat->chart_account_id ?? 0, 'price' => $total, 'description' => $request->description]
                    );
                    $this->logExecutionTime($bacStart, $action, 'saveCategoryBillAccount');
                    Log::info("[{$base}::{$action}] category bill account saved", ['ba_id' => $bac->id, 'bill_id' => $bill->id, 'total' => $total]);
                }
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                Log::info("[{$base}::{$action}] bill updated successfully", ['bill_id' => $bill->id]);
                return redirect()->route(VW::BIL . '.index')->with('success', __('Bill successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                Log::error("[{$base}::{$action}] update failed", ['error' => $e->getMessage(), 'bill_id' => $bill->id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'input' => $request->all(), 'bill_id' => $bill->id]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $bill->id]);
    }

    public function destroy(Bill $bill): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($bill, $action, $method, $class, $base) {
            $req = request();
            if (($g = self::guard($req, 'delete bill')) !== true) return $g;
            if (!self::_isOwner($bill)) {
                Log::warning("[{$base}::{$action}] unauthorized destroy bill", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $bill->id]);
                Log::debug("[{$base}::{$action}] ownership check failed", ['expected_user_id' => auth()->id(), 'bill_creator' => $bill->{DatabaseConstants::COL_TABLE_CREATOR} ?? null]);
                return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action);
            }
            Log::info("[{$base}::{$action}] destroying bill", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $bill->id, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::beginTransaction();
                $payLoopStart = microtime(true);
                foreach ($bill->payments as $p) {
                    $baStart = microtime(true);
                    Utility::bankAccountBalance($p->account_id, $p->amount, 'credit');
                    $this->logExecutionTime($baStart, $action, 'bankAccountCredit');
                    $txDelStart = microtime(true);
                    Transaction::where('payment_id', $p->id)->delete();
                    $this->logExecutionTime($txDelStart, $action, 'deleteTransactions');
                    $delPStart = microtime(true);
                    $p->delete();
                    $this->logExecutionTime($delPStart, $action, 'deleteBillPayment');
                    Log::info("[{$base}::{$action}] deleted BillPayment", ['payment_id' => $p->id, 'bill_id' => $bill->id]);
                }
                $this->logExecutionTime($payLoopStart, $action, 'processPayments');
                if ($bill->vendor_id) {
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $bill->vendor_id, $bill->getDue(), 'credit');
                    $this->logExecutionTime($balStart, $action, 'updateVendorBalance');
                    Log::info("[{$base}::{$action}] updated vendor balance on destroy", ['vendor_id' => $bill->vendor_id, 'amount' => $bill->getDue()]);
                }
                $delProdStart = microtime(true);
                BillProduct::where('bill_id', $bill->id)->delete();
                $this->logExecutionTime($delProdStart, $action, 'deleteBillProducts');
                $delAccStart = microtime(true);
                BillAccount::where('ref_id', $bill->id)->delete();
                $this->logExecutionTime($delAccStart, $action, 'deleteBillAccounts');
                $delDNStart = microtime(true);
                DebitNote::where('bill', $bill->id)->delete();
                $this->logExecutionTime($delDNStart, $action, 'deleteDebitNotes');
                $delBillStart = microtime(true);
                $bill->delete();
                $this->logExecutionTime($delBillStart, $action, 'deleteBill');
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                Log::info("[{$base}::{$action}] bill deleted", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $bill->id]);
                return redirect()->route(VW::BIL . '.index')->with('success', __('Bill successfully deleted.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                Log::error("[{$base}::{$action}] destroy failed", ['error' => $e->getMessage(), 'bill_id' => $bill->id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'bill_id' => $bill->id]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $bill->id]);
    }

    public function product(Request $req): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $req;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            try {
                $findStart = microtime(true);
                $prod = ProductService::findOrFail($request->product_id);
                $this->logExecutionTime($findStart, $action, 'findProduct');
                $unitStart = microtime(true);
                $unit = $prod->unit->name ?? '';
                $this->logExecutionTime($unitStart, $action, 'resolveUnit');
                $rateStart = microtime(true);
                $rate = $prod->taxRate($prod->tax_id);
                $this->logExecutionTime($rateStart, $action, 'computeTaxRate');
                $taxStart = microtime(true);
                $taxes = $prod->tax($prod->tax_id);
                $this->logExecutionTime($taxStart, $action, 'fetchTaxes');
                $amount = $prod->purchase_price;
                Log::info("[{$base}::{$action}] fetched product detail", ['product_id' => $prod->id, 'method' => $method]);
                return response()->json(['product' => $prod, 'unit' => $unit, 'taxRate' => $rate, 'taxes' => $taxes, 'totalAmount' => $amount]);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'product_id' => $request->product_id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'input' => $request->all()]);
                return response()->json([]);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'product_id' => $req->product_id]);
    }

    public const PRD_DST = 'productDestroy';
    public function productDestroy(Request $req): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($g = self::guard($req, 'delete bill product')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'product_id' => $req->id, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action, $base) {
                    $findStart = microtime(true);
                    $bp = BillProduct::findOrFail($req->id);
                    $bill = Bill::findOrFail($bp->bill_id);
                    $this->logExecutionTime($findStart, $action, 'findModels');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $bill->vendor_id, $req->amount, 'credit');
                    $this->logExecutionTime($balStart, $action, 'updateBalance');
                    $delStart = microtime(true);
                    $bp->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteBillProduct');
                    Log::info("[{$base}::{$action}] deleted", ['bp_id' => $req->id, 'bill_id' => $bill->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->back()->with('success', __('Bill product successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'id' => $req->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'product_id' => $req->id]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'product_id' => $req->id]);
    }

    public function sent(int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($g = self::guard($req, 'send bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'bill_id' => $id, 'method' => $method]);
            try {
                $findStart = microtime(true);
                $bill = Bill::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findBill');
                $updStart = microtime(true);
                $bill->update(['send_date' => now(), 'status' => 1]);
                $this->logExecutionTime($updStart, $action, 'updateBill');
                $venStart = microtime(true);
                $vendor = Vendor::findOrFail($bill->vendor_id);
                $this->logExecutionTime($venStart, $action, 'findVendor');
                $fmtStart = microtime(true);
                $bill->name = $vendor->name ?? '';
                $bill->bill = $user?->billNumberFormat($bill->bill_id);
                $bill->url = route(VW::BIL . '.pdf', Crypt::encrypt($bill->id));
                $this->logExecutionTime($fmtStart, $action, 'formatPayload');
                $balStart = microtime(true);
                Utility::updateUserBalance('vendor', $vendor->id, $bill->getTotal(), 'debit');
                $this->logExecutionTime($balStart, $action, 'updateBalance');
                $mailStart = microtime(true);
                $resp = Utility::sendEmailTemplate('vendor_bill_sent', [$vendor->id => $vendor->email ?? ''], [
                    'vendor_bill_name' => $bill->name,
                    'vendor_bill_id' => $bill->bill,
                    'vendor_bill_url' => $bill->url
                ]);
                $this->logExecutionTime($mailStart, $action, 'sendEmail');
                Log::info("[{$base}::{$action}] sent", ['bill_id' => $bill->id, 'success' => $resp['is_success'] ?? false]);
                return redirect()->back()->with('success', __('Bill successfully sent.') . (!($resp['is_success'] ?? true) && ($resp['error'] ?? null) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'bill_id' => $id]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed trace", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $id]);
    }

    public function resent(int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($g = self::guard($req, 'send bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'bill_id' => $id, 'method' => $method]);
            try {
                $setStart = microtime(true);
                $settings = Utility::settings() ?? [];
                $this->logExecutionTime($setStart, $action, 'loadSettings');
                if ((($settings['bill_resent'] ?? 0)) != 1) {
                    Log::warning("[{$base}::{$action}] resend disabled", ['bill_id' => $id]);
                    return redirect()->back()->with('error', __('Resend disabled.'));
                }
                $findStart = microtime(true);
                $bill = Bill::findOrFail($id);
                $vendor = Vendor::findOrFail($bill->vendor_id);
                $this->logExecutionTime($findStart, $action, 'findModels');
                $fmtStart = microtime(true);
                $bill->name = $vendor->name ?? '';
                $bill->bill = $user?->billNumberFormat($bill->bill_id);
                $bill->url = route(VW::BIL . '.pdf', Crypt::encrypt($bill->id));
                $this->logExecutionTime($fmtStart, $action, 'formatPayload');
                $mailStart = microtime(true);
                $resp = Utility::sendEmailTemplate('bill_resent', [$vendor->id => $vendor->email ?? ''], [
                    'vendor_name' => $vendor->name ?? '',
                    'vendor_email' => $vendor->email ?? '',
                    'bill_name' => $bill->name,
                    'bill_id' => $bill->bill,
                    'bill_url' => $bill->url
                ]);
                $this->logExecutionTime($mailStart, $action, 'sendEmail');
                Log::info("[{$base}::{$action}] resent", ['bill_id' => $bill->id, 'success' => $resp['is_success'] ?? false]);
                return redirect()->back()->with('success', __('Bill successfully sent.') . (!($resp['is_success'] ?? true) && ($resp['error'] ?? null) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'bill_id' => $id]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed trace", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $id]);
    }

    public const BIL_N = 'billNumber';
    public function billNumber(): int|string
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] generating", [UsersConstants::COL_USER_ID => auth()->id(), 'method' => $method]);
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] called without authentication");
                return $userOrRedirect;
            }
            try {
                $user = $userOrRedirect;
                $qStart = microtime(true);
                $last = Bill::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->latest('bill_id')->value('bill_id');
                $this->logExecutionTime($qStart, $action, 'fetchLastBillId');
                $calcStart = microtime(true);
                $next = $last ? (is_numeric($last) ? $last + 1 : $last) : 1;
                $this->logExecutionTime($calcStart, $action, 'computeNextNumber');
                Log::info("[{$base}::{$action}] next determined", [UsersConstants::COL_USER_ID => $user?->id, 'last' => $last, 'next' => $next]);
                return $next;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function payment(Request $req, string|int $billId): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::BIL . '.payment';
        return $this->measureProfile($action, function () use ($req, $billId, $action, $method, $class, $base, $viewPath) {
            if (($g = self::guard($req, 'create payment bill')) !== true) {
                Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $req->user()?->id, 'bill_id' => $billId, 'method' => $method]);
                return $g;
            }
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'bill_id' => $billId, 'method' => $method]);
            try {
                $findStart = microtime(true);
                $bill = Bill::findOrFail($billId);
                $this->logExecutionTime($findStart, $action, 'findBill');
                $uid = $req->user()->creatorId();
                $dataStart = microtime(true);
                $data = [
                    'bill' => $bill,
                    DatabaseConstants::TABLE_VENDORS => self::_vendors()->prepend('Select Vendor', ''),
                    'categories' => ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $uid)->pluck('name', 'id'),
                    'accounts' => BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name", 'id')->where(DatabaseConstants::COL_TABLE_CREATOR, $uid)->pluck('name', 'id'),
                ];
                $this->logExecutionTime($dataStart, $action, 'buildData');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => array_keys($data)]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, $data);
                $this->logExecutionTime($renderStart, $action, 'renderPayment');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $billId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId]);
    }

    public const PAY_CRT = 'createPayment';
    public function createPayment(Request $req, string|int $billId): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $billId, $action, $method, $class, $base) {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] unauthenticated call");
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            if ($r = self::_deny($req, 'create payment bill')) {
                Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
                return $r;
            }
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'bill_id' => $billId, 'method' => $method, 'input' => $req->only(['date', 'amount', 'account_id'])]);
            $valStart = microtime(true);
            $req->validate(['date' => 'required|date', 'amount' => 'required|numeric', 'account_id' => 'required|numeric']);
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            try {
                $result = null;
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $billId, &$result, $user, $action, $base) {
                    $createStart = microtime(true);
                    $bp = BillPayment::create([
                        'bill_id' => $billId,
                        'date' => $req->date,
                        'amount' => $req->amount,
                        'account_id' => $req->account_id,
                        'payment_method' => 0,
                        'reference' => $req->reference,
                        'description' => $req->description,
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createBillPayment');
                    Log::info("[{$base}::{$action}] payment created", ['payment_id' => $bp->id]);
                    if ($req->file('add_receipt')) {
                        $recStart = microtime(true);
                        $size = $req->file('add_receipt')->getSize();
                        if (Utility::updateStorageLimit($user?->creatorId(), $size) != 1) {
                            Log::error("[{$base}::{$action}] storage limit exceeded", [UsersConstants::COL_USER_ID => $user?->id]);
                            throw new \RuntimeException('storage');
                        }
                        $fname = time() . '_' . $req->file('add_receipt')->getClientOriginalName();
                        $path = Utility::uploadFile($req, 'add_receipt', $fname, 'uploads/payment', []);
                        if (($path['flag'] ?? 0) == 0) {
                            Log::error("[{$base}::{$action}] receipt upload failed", ['message' => $path['msg'] ?? 'unknown']);
                            throw new \RuntimeException($path['msg'] ?? 'upload failed');
                        }
                        $bp->add_receipt = $fname;
                        $bp->save();
                        $this->logExecutionTime($recStart, $action, 'uploadReceipt');
                        Log::info("[{$base}::{$action}] receipt uploaded", ['payment_id' => $bp->id, 'filename' => $fname]);
                    }
                    $findBillStart = microtime(true);
                    $bill = Bill::findOrFail($billId);
                    $this->logExecutionTime($findBillStart, $action, 'findBill');
                    $billUpdStart = microtime(true);
                    $bill->status = $bill->getDue() <= 0 ? 4 : 3;
                    $bill->send_date = $bill->send_date ?? now();
                    $bill->save();
                    $this->logExecutionTime($billUpdStart, $action, 'updateBill');
                    Log::info("[{$base}::{$action}] bill updated post-payment", ['bill_id' => $bill->id, 'status' => $bill->status]);
                    $fillStart = microtime(true);
                    $bp->fill([
                        UsersConstants::COL_USER_ID => $bill->vendor_id,
                        'user_type' => 'Vendor',
                        'type' => 'Partial',
                        DatabaseConstants::COL_TABLE_CREATOR => auth()->id(),
                        'payment_id' => $bp->id,
                        'category' => 'Bill',
                        'account' => $req->account_id,
                    ])->save();
                    $this->logExecutionTime($fillStart, $action, 'enrichPayment');
                    $txStart = microtime(true);
                    Transaction::addTransaction($bp);
                    $this->logExecutionTime($txStart, $action, 'addTransaction');
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $bill->vendor_id, $req->amount, 'credit');
                    Utility::bankAccountBalance($req->account_id, $req->amount, 'debit');
                    $this->logExecutionTime($balStart, $action, 'updateBalances');
                    $setStart = microtime(true);
                    $settings = Utility::settings();
                    $this->logExecutionTime($setStart, $action, 'loadSettings');
                    if (($settings['new_bill_payment'] ?? 0) == 1) {
                        $venStart = microtime(true);
                        $vendor = Vendor::findOrFail($bill->vendor_id);
                        $this->logExecutionTime($venStart, $action, 'findVendor');
                        $payload = [
                            'vendor_name' => $vendor->name,
                            'vendor_email' => $vendor->email,
                            'payment_amount' => $user?->priceFormat($req->amount),
                            'payment_bill' => 'bill ' . $user?->billNumberFormat($bill->bill_id),
                            'payment_date' => $user?->dateFormat($req->date),
                            'payment_method' => '-',
                            'company_name' => $vendor->name,
                        ];
                        $mailStart = microtime(true);
                        $result = Utility::sendEmailTemplate('new_bill_payment', [$vendor->id => $vendor->email], $payload);
                        $this->logExecutionTime($mailStart, $action, 'sendEmail');
                        Log::info("[{$base}::{$action}] payment email sent", ['bill_id' => $bill->id, 'success' => $result['is_success'] ?? false]);
                    }
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', 'Payment successfully added.' . (($result['is_success'] ?? true) ? '' : '<br><span class="text-danger">' . $result['error'] . '</span>'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $billId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId]);
    }

    public const PAY_DST = 'paymentDestroy';
    public function paymentDestroy(Request $req, string|int $billId, string|int $paymentId): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $billId, $paymentId, $action, $method, $class, $base) {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] unauthenticated call");
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            if ($r = self::_deny($req, 'delete payment bill')) {
                Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
                return $r;
            }
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'payment_id' => $paymentId, 'bill_id' => $billId, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($billId, $paymentId, $user, $action, $base) {
                    $findStart = microtime(true);
                    $payment = BillPayment::findOrFail($paymentId);
                    $bill = Bill::findOrFail($billId);
                    $this->logExecutionTime($findStart, $action, 'findModels');
                    $delStart = microtime(true);
                    BillPayment::destroy($paymentId);
                    $this->logExecutionTime($delStart, $action, 'deletePayment');
                    Log::info("[{$base}::{$action}] payment record removed", ['payment_id' => $paymentId]);
                    $billUpdStart = microtime(true);
                    $bill->status = ($bill->getDue() > 0 && $bill->getTotal() != $bill->getDue()) ? 3 : 2;
                    $bill->save();
                    $this->logExecutionTime($billUpdStart, $action, 'updateBill');
                    Log::info("[{$base}::{$action}] bill updated post-delete", ['bill_id' => $bill->id, 'status' => $bill->status]);
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $bill->vendor_id, $payment->amount, 'debit');
                    Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');
                    $this->logExecutionTime($balStart, $action, 'updateBalances');
                    if ($payment->add_receipt) {
                        $storStart = microtime(true);
                        Utility::changeStorageLimit($user?->creatorId(), 'uploads/payment/' . $payment->add_receipt);
                        $this->logExecutionTime($storStart, $action, 'changeStorageLimit');
                        Log::info("[{$base}::{$action}] receipt removed from storage", ['filename' => $payment->add_receipt]);
                    }
                    $txDelStart = microtime(true);
                    Transaction::destroyTransaction($paymentId, 'Partial', 'Vendor');
                    $this->logExecutionTime($txDelStart, $action, 'destroyTransaction');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return back()->with('success', 'Payment successfully deleted.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $billId, 'payment_id' => $paymentId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId, 'payment_id' => $paymentId]);
    }

    public const VD_BIL = 'vendorBill';
    public function vendorBill(Request $req): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::BIL . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            $guard = self::guard($req, 'manage vendor bill');
            if ($guard !== true) {
                Log::warning("[{$base}::{$action}] permission denied", [UsersConstants::COL_USER_ID => $req->user()?->id]);
                return $guard;
            }
            Log::info("[{$base}::{$action}] start", ['vendor_id' => $req->user()?->vendor_id, 'method' => $method]);
            $buildStart = microtime(true);
            $status = Bill::$statuses;
            $bills = Bill::where([['vendor_id', $req->user()->vendor_id], ['status', '!=', 0], [DatabaseConstants::COL_TABLE_CREATOR, $req->user()->creatorId()]])
                ->when($req->vendor, fn($q) => $q->where('id', $req->vendor))
                ->when($req->bill_date, function ($q) use ($req) {
                    $range = array_map('trim', explode(' - ', $req->bill_date));
                    $q->whereBetween('bill_date', $range);
                })
                ->when($req->status, fn($q) => $q->where('status', $req->status))
                ->get();
            $this->logExecutionTime($buildStart, $action, 'buildAndFetchBills');
            Log::info("[{$base}::{$action}] loaded vendor bills", ['count' => $bills->count()]);
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [DatabaseConstants::TABLE_BILLS, 'status']]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(DatabaseConstants::TABLE_BILLS, 'status'));
            $this->logExecutionTime($renderStart, $action, 'renderVendorBill');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'vendor_id' => $req->user()?->vendor_id]);
    }

    public const VD_BIL_SHW = 'vendorBillShow';
    public function vendorBillShow(string $enc): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        $viewPath = VW::BIL . '.view';
        return $this->measureProfile($action, function () use ($req, $enc, $action, $method, $class, $base, $viewPath) {
            if (($g = self::guard($req, 'show bill')) !== true) return $g;
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($enc);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $findStart = microtime(true);
                $bill = Bill::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findBill');
                if (!self::_isOwner($bill)) {
                    Log::warning("[{$base}::{$action}] unauthorized", [UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $id]);
                    throw new AuthorizationException;
                }
                Log::info("[{$base}::{$action}] showing vendor bill", ['bill_id' => $id, 'method' => $method]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bill', 'vendor', 'items']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['bill' => $bill, 'vendor' => $bill->vendor, 'items' => $bill->items]);
                $this->logExecutionTime($renderStart, $action, 'renderVendorBillShow');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'encrypted' => $enc]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'encrypted' => $enc]);
    }

    public function vendor(Request $req): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::BIL . '.vendor_detail';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['vendor_id' => $req->id, 'method' => $method]);
            try {
                $findStart = microtime(true);
                $vendor = Vendor::findOrFail($req->id);
                $this->logExecutionTime($findStart, $action, 'findVendor');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['vendor']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['vendor' => $vendor]);
                $this->logExecutionTime($renderStart, $action, 'renderVendor');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'vendor_id' => $req->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'vendor_id' => $req->id]);
    }

    public const VD_BIL_SND = 'vendorBillSend';
    public function vendorBillSend(string|int $billId): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::VND . '.bill_send';
        return $this->measureProfile($action, function () use ($billId, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['bill_id' => $billId, 'method' => $method]);
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['billId']]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('billId'));
            $this->logExecutionTime($renderStart, $action, 'renderVendorBillSend');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId]);
    }

    public const VD_BIL_SND_M = 'vendorBillSendMail';
    public function vendorBillSendMail(Request $req, string|int $billId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $billId, $action, $method, $class, $base) {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning("[{$base}::{$action}] unauthenticated access");
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $valStart = microtime(true);
            $req->validate(['email' => 'required|email']);
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            Log::info("[{$base}::{$action}] validation passed", [UsersConstants::COL_USER_ID => $user?->id, 'bill_id' => $billId, 'to' => $req->email, 'method' => $method]);
            try {
                $fetchStart = microtime(true);
                $bill = Bill::with('vendor')->findOrFail($billId);
                $this->logExecutionTime($fetchStart, $action, 'fetchBill');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] bill not found", ['bill_id' => $billId, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] fetch error context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return back()->with('error', __('Bill not found.'));
            }
            $vendor = $bill->vendor;
            $fmtStart = microtime(true);
            $bill->name = $vendor[UsersConstants::COL_NM] ?? '';
            $bill->bill = $user?->billNumberFormat($bill->bill_id);
            $bill->url = route(VW::BIL . '.pdf', Crypt::decrypt(Crypt::encrypt($bill->id)));
            $this->logExecutionTime($fmtStart, $action, 'formatPayload');
            try {
                $mailStart = microtime(true);
                Mail::to($req->email)->queue(new VendorBillMail($bill));
                $this->logExecutionTime($mailStart, $action, 'queueMail');
                Log::info("[{$base}::{$action}] invoice email queued", ['bill_id' => $bill->id, 'to' => $req->email]);
                return back()->with('success', __('Bill successfully sent.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] email dispatch failed", ['bill_id' => $bill->id, 'to' => $req->email, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] email error context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return back()->with('error', __('E-Mail has not been sent due to SMTP configuration'));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId, 'to' => $req->email ?? null]);
    }

    public const SHP_DSP = 'shippingDisplay';
    public function shippingDisplay(Request $req, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            try {
                $findStart = microtime(true);
                $bill = Bill::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findBill');
                $updStart = microtime(true);
                $bill->shipping_display = $req->boolean('is_display');
                $bill->save();
                $this->logExecutionTime($updStart, $action, 'updateShippingDisplay');
                Log::info("[{$base}::{$action}] toggled shipping display", ['bill_id' => $id, 'display' => $bill->shipping_display, 'method' => $method]);
                return back()->with('success', __('Shipping address status successfully changed.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $id]);
    }

    public function duplicate(string|int $billId): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        return $this->measureProfile($action, function () use ($req, $billId, $action, $method, $class, $base) {
            if ($r = self::guard($req, 'duplicate bill')) return $r;
            Log::info("[{$base}::{$action}] start", ['bill_id' => $billId, 'method' => $method]);
            try {
                $findStart = microtime(true);
                $bill = Bill::findOrFail($billId);
                $this->logExecutionTime($findStart, $action, 'findBill');
                $repStart = microtime(true);
                $copy = $bill->replicate(['bill_id', 'status', 'send_date']);
                $copy->bill_id = $this->billNumber();
                $copy->bill_date = now()->toDateString();
                $copy->status = 0;
                $copy->save();
                $this->logExecutionTime($repStart, $action, 'replicateAndSave');
                $itemsStart = microtime(true);
                $bill->items()->each(fn($p) => $copy->items()->create($p->only('product_id', 'quantity', 'tax', 'discount', 'price')));
                $this->logExecutionTime($itemsStart, $action, 'duplicateItems');
                Log::info("[{$base}::{$action}] duplicated", ['original_id' => $bill->id, 'copy_id' => $copy->id]);
                return back()->with('success', 'Bill duplicated successfully.');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $billId]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $billId]);
    }

    public const PV_BIL = 'previewBill';
    public function previewBill(string $template, string $color): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        // ! Validate template to avoid traversal
        if (!preg_match('/^[a-z0-9_\-]+$/i', $template)) return back()->with('error', 'Invalid template.');
        $viewPath = VW::BIL_TMP . "{$template}";
        return $this->measureProfile($action, function () use ($req, $template, $color, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            try {
                $setStart = microtime(true);
                $settings = Utility::settings();
                $this->logExecutionTime($setStart, $action, 'loadSettings');
                $vendorStart = microtime(true);
                $vendor = (object)['email' => '<Email>', 'shipping_name' => '<Vendor Name>', 'shipping_country' => '<Country>', 'shipping_state' => '<State>', 'shipping_city' => '<City>', 'shipping_phone' => '<Vendor Phone Number>', 'shipping_zip' => '<Zip>', 'shipping_address' => '<Address>', 'billing_name' => '<Vendor Name>', 'billing_country' => '<Country>', 'billing_state' => '<State>', 'billing_city' => '<City>', 'billing_phone' => '<Vendor Phone Number>', 'billing_zip' => '<Zip>', 'billing_address' => '<Address>'];
                $this->logExecutionTime($vendorStart, $action, 'buildVendor');
                $itemsStart = microtime(true);
                $items = collect(range(1, 3))->map(fn($i) => (object)['name' => "Item $i", 'quantity' => 1, 'tax' => 5, 'discount' => 50, 'price' => 100, 'unit' => 1, 'itemTax' => [['name' => 'Tax', 'rate' => '10 %', 'price' => '$10', 'tax_price' => 10]]]);
                $this->logExecutionTime($itemsStart, $action, 'buildItems');
                $billStart = microtime(true);
                $bill = new Bill(['bill_id' => 1, 'issue_date' => now(), 'due_date' => now(), 'itemData' => $items, 'totalTaxPrice' => 60, 'totalQuantity' => 3, 'totalRate' => 300, 'totalDiscount' => 10, 'taxesData' => [], DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()]);
                $this->logExecutionTime($billStart, $action, 'buildBill');
                $logoStart = microtime(true);
                $img = Utility::getLogo('bill_logo', SettingsConstants::CPN_LG_DK);
                $this->logExecutionTime($logoStart, $action, 'getLogo');
                $fontStart = microtime(true);
                $fontColor = Utility::getFontColor('#' . ($color ?? 'ffffff'));
                $this->logExecutionTime($fontStart, $action, 'getFontColor');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bill', 'preview', 'color', 'img', 'settings', 'vendor', 'font_color', 'customFields']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['bill' => $bill, 'preview' => 1, 'color' => '#' . ($color ?? 'ffffff'), 'img' => $img, 'settings' => $settings, 'vendor' => $vendor, 'font_color' => $fontColor, 'customFields' => []]);
                $this->logExecutionTime($renderStart, $action, 'renderPreview');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'template' => $template, 'color' => $color]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'template' => $template]);
    }

    public function bill(string $enc): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        return $this->measureProfile($action, function () use ($req, $enc, $action, $method, $class, $base) {
            $decStart = microtime(true);
            try {
                $id = Crypt::decrypt($enc);
                $this->logExecutionTime($decStart, $action, 'decryptId');
            } catch (\Throwable $e) {
                Log::warning("[{$base}::{$action}] decrypt failed", ['encrypted' => $enc, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] decrypt context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return back()->with('error', 'Bill Not Found.');
            }
            try {
                $fetchStart = microtime(true);
                $bill = Bill::with('items.product')->findOrFail($id);
                $this->logExecutionTime($fetchStart, $action, 'fetchBill');
                if (!self::_isOwner($bill)) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action);
                $setStart = microtime(true);
                $settings = Utility::settingsById($bill[DatabaseConstants::COL_TABLE_CREATOR]);
                $this->logExecutionTime($setStart, $action, 'loadSettings');
                $statsStart = microtime(true);
                [$items, $taxesData, $totTax, $totQty, $totRate, $totDisc] = Utility::billItemStats($bill, $settings);
                $bill->fill(['itemData' => $items, 'taxesData' => $taxesData, 'totalTaxPrice' => $totTax, 'totalQuantity' => $totQty, 'totalRate' => $totRate, 'totalDiscount' => $totDisc, 'customField' => CustomField::getData($bill, 'bill')]);
                $this->logExecutionTime($statsStart, $action, 'computeStats');
                $logoStart = microtime(true);
                $img = Utility::getLogo('bill_logo', SettingsConstants::CPN_LG_DK, $bill[DatabaseConstants::COL_TABLE_CREATOR]);
                $this->logExecutionTime($logoStart, $action, 'getLogo');
                $billColor = '#' . (($settings['bill_color'] ?? 'ffffff'));
                $templateSlug = ($settings[BillsConstants::COL_BIL_TMP] ?? 'template1');
                $viewPath = VW::BIL_TMP . "{$templateSlug}";
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bill', 'color', 'settings', 'vendor', 'img', 'font_color', 'customFields']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['bill' => $bill, 'color' => $billColor, 'settings' => $settings, 'vendor' => $bill->vendor, 'img' => $img, 'font_color' => Utility::getFontColor($billColor), 'customFields' => CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $bill[DatabaseConstants::COL_TABLE_CREATOR])->where('module', 'bill')->get()]);
                $this->logExecutionTime($renderStart, $action, 'renderBill');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'encrypted' => $enc, 'bill_id' => isset($bill) ? $bill->id : null]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'encrypted' => $enc]);
    }

    public const SV_BIL_TMP = 'saveBillTemplateSettings';
    public function saveBillTemplateSettings(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            try {
                $buildStart = microtime(true);
                $data = $req->except('_token');
                $data['bill_color'] = $data['bill_color'] ?? 'ffffff';
                $this->logExecutionTime($buildStart, $action, 'prepareData');
                if ($req->file('bill_logo')) {
                    $uplStart = microtime(true);
                    $upload = Utility::uploadFile($req, 'bill_logo', $req->user()?->id . '_bill_logo.png', 'bill_logo/', ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]);
                    $this->logExecutionTime($uplStart, $action, 'uploadLogo');
                    if (($upload['flag'] ?? 0) == 0) return back()->with('error', $upload['msg'] ?? 'Upload failed');
                    $data['bill_logo'] = ($req->user()?->id ?? 'user') . '_bill_logo.png';
                }
                $insStart = microtime(true);
                $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
                foreach ($data as $k => $v) DB::insert('INSERT INTO settings (`value`,`name`,`' . $creatorCol . '`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)', [$v, $k, $req->user()->creatorId()]);
                $this->logExecutionTime($insStart, $action, 'upsertSettings');
                Log::info("[{$base}::{$action}] settings updated", ['user_id' => $req->user()?->id, 'count' => count($data), 'method' => $method]);
                return back()->with('success', 'Bill setting updated successfully');
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->except('_token'))]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function items(Request $req): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['bill_id' => $req->bill_id, 'product_id' => $req->product_id, 'method' => $method]);
            try {
                $buildStart = microtime(true);
                $query = BillProduct::where([['bill_id', $req->bill_id], ['product_id', $req->product_id]]);
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $item = $query->first();
                $this->logExecutionTime($fetchStart, $action, 'fetchItem');
                Log::info("[{$base}::{$action}] fetched", ['found' => (bool)$item]);
                return response()->json($item);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'bill_id' => $req->bill_id, 'product_id' => $req->product_id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $req->bill_id, 'product_id' => $req->product_id]);
    }

    public const IV_LK = 'invoiceLink';
    public function invoiceLink(string $enc): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        $viewPath = VW::BIL . '.customer_bill';
        return $this->measureProfile($action, function () use ($req, $enc, $action, $method, $class, $base, $viewPath) {
            $decStart = microtime(true);
            try {
                $id = Crypt::decrypt($enc);
                $this->logExecutionTime($decStart, $action, 'decryptId');
            } catch (\Throwable $e) {
                Log::warning("[{$base}::{$action}] decrypt failed", ['encrypted' => $enc, 'error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] decrypt context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return back()->with('error', 'Bill Not Found.');
            }
            try {
                $fetchStart = microtime(true);
                $bill = Bill::with('items', 'vendor')->findOrFail($id);
                $this->logExecutionTime($fetchStart, $action, 'fetchBill');
                $cfStart = microtime(true);
                $bill->customField = CustomField::getData($bill, 'bill');
                $this->logExecutionTime($cfStart, $action, 'loadCustomField');
                $bpStart = microtime(true);
                $billPayment = BillPayment::where('bill_id', $bill->id)->get();
                $this->logExecutionTime($bpStart, $action, 'fetchBillPayments');
                $usrStart = microtime(true);
                $user = User::find($bill[DatabaseConstants::COL_TABLE_CREATOR]);
                $this->logExecutionTime($usrStart, $action, 'fetchCreator');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bill', 'vendor', 'items', 'customFields', 'billPayment', 'user']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['bill' => $bill, 'vendor' => $bill->vendor, 'items' => $bill->items, 'customFields' => CustomField::where('module', 'bill')->get(), 'billPayment' => $billPayment, 'user' => $user]);
                $this->logExecutionTime($renderStart, $action, 'renderInvoiceLink');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'encrypted' => $enc]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'encrypted' => $enc]);
    }

    public function export(): BinaryFileResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($action, $method, $class, $base) {
            try {
                Log::info("[{$base}::{$action}] start", ['method' => $method]);
                $nameStart = microtime(true);
                $filename = 'bill_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                $this->logExecutionTime($nameStart, $action, 'buildFilename');
                $dlStart = microtime(true);
                $resp = Excel::download(new BillExport(), $filename);
                $this->logExecutionTime($dlStart, $action, 'exportDownload');
                Log::info("[{$base}::{$action}] exported", ['filename' => $filename]);
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
                throw $e;
            }
        }, ['method' => $method, 'class' => $base]);
    }

    private function _authorize(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        Log::info('Authorizing permission', [UsersConstants::COL_USER_ID => $req->user()?->id, 'permission' => $perm]);
        return $req->user()?->can($perm)
            ? null
            : defaultPermissionDenial($req, new AuthorizationException($perm), __CLASS__ . '::' . __FUNCTION__);
    }

    private static function _deny(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        Log::info('Checking permission', [UsersConstants::COL_USER_ID => $req->user()?->id ?? null, 'permission' => $perm]);
        return $req->user()?->can($perm)
            ? null
            : defaultPermissionDenial($req, new AuthorizationException($perm), __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']);
    }

    private static function _isOwner(object $model): bool
    {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
            Log::warning('Unauthenticated owner check');
            throw new AuthorizationException;
        }
        $user = $userOrRedirect;
        $owner = $model[DatabaseConstants::COL_TABLE_CREATOR] === $user?->creatorId();
        Log::info('Owner check', [UsersConstants::COL_USER_ID => $user?->id, 'model_id' => $model->id ?? null, 'is_owner' => $owner]);
        return $owner;
    }

    private static function _vendors(): Collection
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        return Vendor::where(DatabaseConstants::COL_TABLE_CREATOR, $uid)->pluck(UsersConstants::COL_NM, 'id');
    }

    private static function _categories(): Collection
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        return ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $uid)
            ->whereNotIn('type', ['product & service', 'income'])
            ->pluck('name', 'id');
    }

    private static function _chartAccounts(): Collection
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        return ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
            ->where(DatabaseConstants::COL_TABLE_CREATOR, $uid)
            ->pluck('code_name', 'id');
    }

    private static function _billNumber(): int|string
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        $last = Bill::where(DatabaseConstants::COL_TABLE_CREATOR, $uid)->latest('bill_id')->value('bill_id');
        if (!$last) {
            $next = 1;
            Log::info('Next Bill Identifier', [UsersConstants::COL_USER_ID => $user?->id, 'next' => $next]);
            return $next;
        }
        $next = is_numeric($last) ? ((int)$last + 1) : $last;
        Log::info('Next Bill Identifier', [UsersConstants::COL_USER_ID => $user?->id, 'last' => $last, 'next' => $next]);
        return $next;
    }
}
