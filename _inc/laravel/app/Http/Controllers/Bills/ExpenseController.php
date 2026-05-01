<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BanksConstants as BKC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    MiddlewaresConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants as UC,
    ViewsConstants
};
use App\Models\{
    Bill,
    BillAccount,
    BillPayment,
    BillProduct,
    ChartOfAccount,
    Customer,
    CustomField,
    Employee,
    ProductService,
    ProductServiceCategory,
    Utility,
    Vendor,
    BankAccount
};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{
    Request,
    JsonResponse,
    RedirectResponse,
    Response
};
use Illuminate\Support\Facades\{
    Auth,
    Cache,
    Crypt,
    DB,
    Log,
    Route,
    Storage,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;
use Throwable;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
final class ExpenseController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;
    /** Cache TTL in seconds — 2 minutes for expense list data */
    private const CACHE_TTL = 120;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $r): Response|RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $r;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, PermissionsConstants::MNG_BIL)) !== true) return $g;
            Log::info("[{$base}::{$action}] listing expenses", [UC::COL_USER_ID => $request->user()->id, 'filters' => $request->only(['vendor', 'bill_date', 'category']), 'method' => $method]);
            try {
                $uidStart = microtime(true);
                $uid = $request->user()->creatorId();
                $this->logExecutionTime($uidStart, $action, 'resolveCreatorId');
                $qryStart = microtime(true);
                $q = Bill::where('type', 'Expense')->where(DC::COL_TABLE_CREATOR, $uid);
                if ($request->filled('vendor')) $q->where('vendor_id', $request->vendor);
                if ($request->filled('bill_date')) {
                    $parts = explode(' to ', $request->bill_date);
                    $s = $parts[0] ?? $request->bill_date;
                    $e = $parts[1] ?? $request->bill_date;
                    $q->whereBetween('bill_date', [$s, $e]);
                }
                if ($request->filled('category')) $q->where('category_id', $request->category);
                $expenses = $q->get();
                $this->logExecutionTime($qryStart, $action, 'queryExpenses');
                Log::info("[{$base}::{$action}] expenses loaded", ['count' => $expenses->count()]);
                $venStart = microtime(true);
                $vendorLst = Cache::remember("exp.vendors.{$uid}", self::CACHE_TTL, fn() => Vendor::where(DC::COL_TABLE_CREATOR, $uid)->pluck(UC::COL_NM, 'id'))->prepend('Select Vendor', '');
                $this->logExecutionTime($venStart, $action, 'loadVendors');
                $catStart = microtime(true);
                $catLst = Cache::remember("exp.categories.{$uid}", self::CACHE_TTL, fn() => ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $uid)->whereNotIn('type', ['product & service', 'income'])->pluck('name', 'id'))->prepend('Select Category', '');
                $this->logExecutionTime($catStart, $action, 'loadCategories');
                $viewPath = ViewsConstants::EXP . '.index';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['expenses', 'vendor', 'status', 'category']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['expenses' => $expenses, 'vendor' => $vendorLst, 'status' => Bill::$statuses, 'category' => $catLst]);
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'filters' => $request->only(['vendor', 'bill_date', 'category'])]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(string|int $refId = ''): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $req = request();
        return $this->measureProfile($action, function () use ($req, $refId, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($g = self::guard($req, 'create bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] showing expense create form", [UC::COL_USER_ID => $user?->id, 'reference' => $refId, 'method' => $method]);
            try {
                $uidStart = microtime(true);
                $uid = $user?->creatorId();
                $this->logExecutionTime($uidStart, $action, 'resolveCreatorId');
                $empStart = microtime(true);
                $employees = Employee::where(DC::COL_TABLE_CREATOR, $uid)->pluck(UC::COL_NM, 'id')->prepend('Select Employee', '');
                $this->logExecutionTime($empStart, $action, 'loadEmployees');
                $cusStart = microtime(true);
                $customers = Customer::where(DC::COL_TABLE_CREATOR, $uid)->pluck(UC::COL_NM, 'id')->prepend('Select Customer', '');
                $this->logExecutionTime($cusStart, $action, 'loadCustomers');
                $venStart = microtime(true);
                $vendors = Vendor::where(DC::COL_TABLE_CREATOR, $uid)->pluck(UC::COL_NM, 'id')->prepend('Select Vendor', '');
                $this->logExecutionTime($venStart, $action, 'loadVendors');
                $numStart = microtime(true);
                $num = $user?->expenseNumberFormat($this->expenseNumber());
                $this->logExecutionTime($numStart, $action, 'formatExpenseNumber');
                $itmStart = microtime(true);
                $items = ProductService::where(DC::COL_TABLE_CREATOR, $uid)->pluck('name', 'id')->prepend('Select Item', '');
                $this->logExecutionTime($itmStart, $action, 'loadItems');
                $catStart = microtime(true);
                $categories = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $uid)->whereNotIn('type', ['product & service', 'income'])->pluck('name', 'id')->prepend('Select Category', '');
                $this->logExecutionTime($catStart, $action, 'loadCategories');
                $cfStart = microtime(true);
                $customFields = CustomField::where([[DC::COL_TABLE_CREATOR, $uid], ['module', 'bill']])->get();
                $this->logExecutionTime($cfStart, $action, 'loadCustomFields');
                $accStart = microtime(true);
                $accounts = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')->where(DC::COL_TABLE_CREATOR, $uid)->pluck('code_name', 'id')->prepend('Select Account', '');
                $this->logExecutionTime($accStart, $action, 'loadChartAccounts');
                $bnkStart = microtime(true);
                $banks = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name, id")->where(DC::COL_TABLE_CREATOR, $uid)->pluck('name', 'id');
                $this->logExecutionTime($bnkStart, $action, 'loadBanks');
                $data = ['employees' => $employees, 'customers' => $customers, 'vendors' => $vendors, 'num' => $num, 'items' => $items, 'categories' => $categories, 'customFields' => $customFields, 'accounts' => $accounts, 'banks' => $banks, 'id' => $refId];
                $viewPath = ViewsConstants::EXP . '.create';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'data_keys' => array_keys($data)]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, $data);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'reference' => $refId]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, BC::COL_REF_ID => $refId]);
    }

    public function store(Request $r): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $r;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'create bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UC::COL_USER_ID => $request->user()?->id, 'input' => $request->all(), 'method' => $method]);
            $valStart = microtime(true);
            if ($resp = self::validateOrRedirect($request, ['payment_date' => 'required|date'])) {
                $this->logExecutionTime($valStart, $action, 'validateRequest');
                return $resp;
            }
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            $txnStart = microtime(true);
            try {
                DB::beginTransaction();
                $buildStart = microtime(true);
                $vendorId = match ($request->type) {
                    'employee' => $request->employee_id,
                    'customer' => $request->customer_id,
                    default => $request->vendor_id
                };
                $bill = new Bill([
                    'bill_id' => $this->expenseNumber(),
                    'vendor_id' => $vendorId,
                    'bill_date' => $request->payment_date,
                    'due_date' => $request->payment_date,
                    'status' => 4,
                    'type' => 'Expense',
                    'user_type' => $request->type,
                    'category_id' => $request->category_id ?? '0',
                    'order_id' => '0',
                    DC::COL_TABLE_CREATOR => $request->user()->creatorId(),
                ]);
                $bill->save();
                $this->logExecutionTime($buildStart, $action, 'createBill');
                $linesStart = microtime(true);
                $this->syncLines($bill, $request->items ?? []);
                $this->logExecutionTime($linesStart, $action, 'syncLines');
                $payStart = microtime(true);
                BillPayment::create([
                    'bill_id' => $bill->id,
                    'date' => $request->payment_date,
                    'amount' => $request->totalAmount,
                    'account_id' => $request->account_id,
                    'payment_method' => 0,
                    'reference' => null,
                    'description' => null,
                    'add_receipt' => null,
                ]);
                $this->logExecutionTime($payStart, $action, 'createPayment');
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                Log::info("[{$base}::{$action}] stored", ['bill_id' => $bill->id]);
                return redirect()->route(ViewsConstants::EXP . '.index')->with('success', __('Expense successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'input_keys' => array_keys($request->all())]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $r, string $encId): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $r;
        return $this->measureProfile($action, function () use ($request, $encId, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'show bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", ['enc_id' => $encId, UC::COL_USER_ID => $request->user()?->id, 'method' => $method]);
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($encId);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $findStart = microtime(true);
                $exp = Bill::with(['items', 'accounts', 'payments'])->findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findExpense');
                if ($exp[DC::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                    Log::warning("[{$base}::{$action}] unauthorized", [UC::COL_USER_ID => $request->user()?->id, 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] ownership mismatch", ['expected_creator' => $request->user()->creatorId(), 'actual_creator' => $exp[DC::COL_TABLE_CREATOR] ?? null]);
                    return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
                }
                $assocStart = microtime(true);
                $assocUser = match ($exp->user_type) {
                    'employee' => Employee::find($exp->user_id),
                    'customer' => Customer::find($exp->user_id),
                    default => Vendor::find($exp->vendor_id)
                };
                $this->logExecutionTime($assocStart, $action, 'resolveAssociatedUser');
                Log::info("[{$base}::{$action}] loaded", ['bill_id' => $id]);
                $viewPath = ViewsConstants::EXP . '.view';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['exp', 'user', 'items', 'payment']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['exp' => $exp, 'user' => $assocUser, 'items' => $exp->items, 'payment' => $exp->payments->first()]);
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'enc_id' => $encId]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    public function edit(Request $r, string $encId): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $r;
        return $this->measureProfile($action, function () use ($request, $encId, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'edit bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", ['enc_id' => $encId, UC::COL_USER_ID => $request->user()?->id, 'method' => $method]);
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($encId);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $findStart = microtime(true);
                $exp = Bill::with(['items', 'accounts'])->findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findExpense');
                if ($exp[DC::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                    Log::warning("[{$base}::{$action}] unauthorized", [UC::COL_USER_ID => $request->user()?->id, 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] ownership mismatch", ['expected_creator' => $request->user()->creatorId(), 'actual_creator' => $exp[DC::COL_TABLE_CREATOR] ?? null]);
                    return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
                }
                $uidStart = microtime(true);
                $uid = $request->user()->creatorId();
                $this->logExecutionTime($uidStart, $action, 'resolveCreatorId');
                $numStart = microtime(true);
                $num = $request->user()->expenseNumberFormat($exp->bill_id);
                $this->logExecutionTime($numStart, $action, 'formatExpenseNumber');
                $empStart = microtime(true);
                $employees = Employee::where(DC::COL_TABLE_CREATOR, $uid)->pluck(UC::COL_NM, 'id')->prepend('Select Employee', '');
                $this->logExecutionTime($empStart, $action, 'loadEmployees');
                $cusStart = microtime(true);
                $customers = Customer::where(DC::COL_TABLE_CREATOR, $uid)->pluck(UC::COL_NM, 'id')->prepend('Select Customer', '');
                $this->logExecutionTime($cusStart, $action, 'loadCustomers');
                $venStart = microtime(true);
                $vendors = Vendor::where(DC::COL_TABLE_CREATOR, $uid)->pluck(UC::COL_NM, 'id')->prepend('Select Vendor', '');
                $this->logExecutionTime($venStart, $action, 'loadVendors');
                $prdStart = microtime(true);
                $products = ProductService::where(DC::COL_TABLE_CREATOR, $uid)->pluck('name', 'id');
                $this->logExecutionTime($prdStart, $action, 'loadProducts');
                $catStart = microtime(true);
                $categories = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $uid)->whereNotIn('type', ['product & service', 'income'])->pluck('name', 'id')->prepend('Select Category', '');
                $this->logExecutionTime($catStart, $action, 'loadCategories');
                $cfStart = microtime(true);
                $customFields = CustomField::where([[DC::COL_TABLE_CREATOR, $uid], ['module', 'bill']])->get();
                $this->logExecutionTime($cfStart, $action, 'loadCustomFields');
                $accStart = microtime(true);
                $accounts = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')->where(DC::COL_TABLE_CREATOR, $uid)->pluck('code_name', 'id')->prepend('Select Account', '');
                $this->logExecutionTime($accStart, $action, 'loadChartAccounts');
                $bnkStart = microtime(true);
                $banks = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name, id")->where(DC::COL_TABLE_CREATOR, $uid)->pluck('name', 'id');
                $this->logExecutionTime($bnkStart, $action, 'loadBanks');
                $data = ['exp' => $exp, 'num' => $num, 'employees' => $employees, 'customers' => $customers, 'vendors' => $vendors, 'products' => $products, 'categories' => $categories, 'customFields' => $customFields, 'accounts' => $accounts, 'banks' => $banks];
                Log::info("[{$base}::{$action}] loaded", ['bill_id' => $id]);
                $viewPath = ViewsConstants::EXP . '.edit';
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
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'enc_id' => $encId]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    public function update(Request $r, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $r;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'edit bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", ['bill_id' => $id, 'input' => $request->all(), 'method' => $method]);
            $valStart = microtime(true);
            if ($resp = self::validateOrRedirect($request, ['bill_date' => 'required|date'])) {
                $this->logExecutionTime($valStart, $action, 'validateRequest');
                return $resp;
            }
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            $txnStart = microtime(true);
            try {
                DB::beginTransaction();
                $findStart = microtime(true);
                $exp = Bill::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findExpense');
                if ($exp[DC::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                    Log::warning("[{$base}::{$action}] unauthorized", [UC::COL_USER_ID => $request->user()?->id, 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] ownership mismatch", ['expected_creator' => $request->user()->creatorId(), 'actual_creator' => $exp[DC::COL_TABLE_CREATOR] ?? null]);
                    return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
                }
                $updStart = microtime(true);
                $vendorId = match ($request->type) {
                    'employee' => $request->employee_id,
                    'customer' => $request->customer_id,
                    default => $request->vendor_id
                };
                $exp->update(['vendor_id' => $vendorId, 'bill_date' => $request->bill_date, 'due_date' => $request->bill_date, 'category_id' => $request->category_id]);
                $this->logExecutionTime($updStart, $action, 'updateExpense');
                $linesStart = microtime(true);
                $this->syncLines($exp, $request->items ?? []);
                $this->logExecutionTime($linesStart, $action, 'syncLines');
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                Log::info("[{$base}::{$action}] success", ['bill_id' => $id]);
                return redirect()->route(ViewsConstants::EXP . '.index')->with('success', __('Expense successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'bill_id' => $id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'input_keys' => array_keys($request->all())]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $id]);
    }

    public const PRD_DST = 'productDestroy';
    public function productDestroy(Request $r): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $r;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'delete bill product')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", ['product_id' => $request->id, UC::COL_USER_ID => $request->user()?->id, 'method' => $method]);
            $txnStart = microtime(true);
            try {
                DB::beginTransaction();
                $findStart = microtime(true);
                $bp = BillProduct::findOrFail($request->id);
                $exp = Bill::findOrFail($bp->bill_id);
                $this->logExecutionTime($findStart, $action, 'findModels');
                $balStart = microtime(true);
                Utility::updateUserBalance('vendor', $exp->vendor_id, $request->amount, 'credit');
                $this->logExecutionTime($balStart, $action, 'updateBalance');
                $delStart = microtime(true);
                $bp->delete();
                $this->logExecutionTime($delStart, $action, 'deleteBillProduct');
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                Log::info("[{$base}::{$action}] success", ['product_id' => $request->id, 'bill_id' => $exp->id]);
                return redirect()->back()->with('success', __('Expense product successfully deleted.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'product_id' => $request->id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'input_keys' => array_keys($request->all())]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'product_id' => $request->id]);
    }

    public function destroy(Request $r, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        $request = $r;
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, 'delete bill')) !== true) return $g;
            Log::info("[{$base}::{$action}] start", ['bill_id' => $id, UC::COL_USER_ID => $request->user()?->id, 'method' => $method]);
            $txnStart = microtime(true);
            try {
                DB::beginTransaction();
                $findStart = microtime(true);
                $exp = Bill::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findExpense');
                if ($exp[DC::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
                    Log::warning("[{$base}::{$action}] unauthorized", [UC::COL_USER_ID => $request->user()?->id, 'bill_id' => $id]);
                    Log::debug("[{$base}::{$action}] ownership mismatch", ['expected_creator' => $request->user()->creatorId(), 'actual_creator' => $exp[DC::COL_TABLE_CREATOR] ?? null]);
                    return defaultPermissionDenial($request, new \Exception('owner'), $class . '::' . $action);
                }
                $payLoopStart = microtime(true);
                foreach ($exp->payments as $p) {
                    $baStart = microtime(true);
                    Utility::bankAccountBalance($p->account_id, $p->amount, 'credit');
                    $this->logExecutionTime($baStart, $action, 'bankAccountCredit');
                    $delPStart = microtime(true);
                    $p->delete();
                    $this->logExecutionTime($delPStart, $action, 'deletePayment');
                }
                $this->logExecutionTime($payLoopStart, $action, 'processPayments');
                if ($exp->vendor_id && $exp->status) {
                    $balStart = microtime(true);
                    Utility::updateUserBalance('vendor', $exp->vendor_id, $exp->getDue(), 'credit');
                    $this->logExecutionTime($balStart, $action, 'updateVendorBalance');
                }
                $delProdStart = microtime(true);
                BillProduct::where('bill_id', $exp->id)->delete();
                $this->logExecutionTime($delProdStart, $action, 'deleteBillProducts');
                $delAccStart = microtime(true);
                BillAccount::where(BC::COL_REF_ID, $exp->id)->delete();
                $this->logExecutionTime($delAccStart, $action, 'deleteBillAccounts');
                $delExpStart = microtime(true);
                $exp->delete();
                $this->logExecutionTime($delExpStart, $action, 'deleteExpense');
                DB::commit();
                $this->logExecutionTime($txnStart, $action, 'transactionCommit');
                Log::info("[{$base}::{$action}] success", ['bill_id' => $id]);
                return redirect()->route(ViewsConstants::EXP . '.index')->with('success', __('Expense successfully deleted.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->logExecutionTime($txnStart, $action, 'transactionRollback');
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'bill_id' => $id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $id]);
    }

    public const EMP = 'employee';
    public function employee(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, PermissionsConstants::MNG_BIL)) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UC::COL_USER_ID => $request->user()?->id, 'employee_id' => $request->id, 'method' => $method]);
            try {
                $findStart = microtime(true);
                $emp = Employee::find($request->id);
                $this->logExecutionTime($findStart, $action, 'findEmployee');
                if (!$emp) {
                    Log::warning("[{$base}::{$action}] not found", ['employee_id' => $request->id]);
                    return back()->with('error', __('Employee not found.'));
                }
                $viewPath = ViewsConstants::EXP . '.employee_detail';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['employee']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['employee' => $emp]);
                $this->logExecutionTime($renderStart, $action, 'renderEmployee');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'employee_id' => $request->id]);
    }

    public const PRD = 'product';
    public function product(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $valStart = microtime(true);
            $request->validate(['product_id' => 'required|integer|exists:product_services,id']);
            $this->logExecutionTime($valStart, $action, 'validateRequest');
            Log::info("[{$base}::product] start", ['product_id' => $request->input('product_id')]);
            try {
                $findStart = microtime(true);
                $productId = $request->input('product_id');
                $product = ProductService::findOrFail($productId);
                $this->logExecutionTime($findStart, $action, 'findProduct');
                $unitStart = microtime(true);
                $unit = $product->unit?->name ?? '';
                $this->logExecutionTime($unitStart, $action, 'resolveUnit');
                $rateStart = microtime(true);
                $taxRate = $product->tax_id ? $product->taxRate($product->tax_id) : 0;
                $this->logExecutionTime($rateStart, $action, 'computeTaxRate');
                $taxStart = microtime(true);
                $taxes = $product->tax_id ? $product->tax($product->tax_id) : 0;
                $this->logExecutionTime($taxStart, $action, 'fetchTaxes');
                $purchasePrice = $product->purchase_price;
                $totalAmount = $purchasePrice * 1;
                Log::info("[{$base}::product] success", ['product_id' => $productId]);
                return response()->json(['product' => $product, 'unit' => $unit, 'taxRate' => $taxRate, 'taxes' => $taxes, 'totalAmount' => $totalAmount], Response::HTTP_OK);
            } catch (ModelNotFoundException $e) {
                Log::warning("[{$base}::product] not found", ['product_id' => $request->input('product_id')]);
                return response()->json(['error' => __('Product not found.')], Response::HTTP_NOT_FOUND);
            } catch (\Throwable $e) {
                Log::error("[{$base}::product] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::product] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::product', route(ViewsConstants::EXP . '.index'));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'product_id' => $request->input('product_id')]);
    }

    public const VND = 'vendor';
    public function vendor(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, PermissionsConstants::MNG_BIL)) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UC::COL_USER_ID => $request->user()?->id, 'vendor_id' => $request->id, 'method' => $method]);
            try {
                $findStart = microtime(true);
                $vendor = Vendor::find($request->id);
                $this->logExecutionTime($findStart, $action, 'findVendor');
                if (!$vendor) {
                    Log::warning("[{$base}::{$action}] not found", ['vendor_id' => $request->id]);
                    return back()->with('error', __('Vendor not found.'));
                }
                $viewPath = ViewsConstants::EXP . '.vendor_detail';
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
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'vendor_id' => $request->id]);
    }

    public const CST = 'customer';
    public function customer(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, PermissionsConstants::MNG_BIL)) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UC::COL_USER_ID => $request->user()?->id, 'customer_id' => $request->id, 'method' => $method]);
            try {
                $findStart = microtime(true);
                $customer = Customer::find($request->id);
                $this->logExecutionTime($findStart, $action, 'findCustomer');
                if (!$customer) {
                    Log::warning("[{$base}::{$action}] not found", ['customer_id' => $request->id]);
                    return back()->with('error', __('Customer not found.'));
                }
                $viewPath = ViewsConstants::EXP . '.customer_detail';
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['customer']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, ['customer' => $customer]);
                $this->logExecutionTime($renderStart, $action, 'renderCustomer');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'customer_id' => $request->id]);
    }

    public const ITM = 'items';
    public function items(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $action, $method, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($g = self::guard($request, PermissionsConstants::MNG_BIL)) !== true) return $g;
            Log::info("[{$base}::{$action}] start", [UC::COL_USER_ID => $request->user()?->id, 'bill_id' => $request->bill_id, 'product_id' => $request->product_id, 'method' => $method]);
            try {
                $buildStart = microtime(true);
                $query = BillProduct::where('bill_id', $request->bill_id)->where('product_id', $request->product_id);
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $item = $query->first();
                $this->logExecutionTime($fetchStart, $action, 'fetchItem');
                return response()->json($item);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return response()->json(['error' => __('An unexpected error occurred.')], 500);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'bill_id' => $request->bill_id, 'product_id' => $request->product_id]);
    }

    public const EXP = 'expense';
    public function expense(Request $request, string $encId): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($request, $encId, $action, $base) {
            Log::info("[{$base}::expense] start", ['enc_id' => $encId, UC::COL_USER_ID => $request->user()?->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($encId);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $fetchStart = microtime(true);
                $expense = Bill::with(['items.product.unit', 'items.product'])->whereKey($id)->firstOrFail();
                $this->logExecutionTime($fetchStart, $action, 'fetchExpense');
                if ($expense[DC::COL_TABLE_CREATOR] !== $request->user()->creatorId()) return defaultPermissionDenial($request, new \Exception('owner'), $action);
                $setStart = microtime(true);
                $settings = Utility::settingsById($expense[DC::COL_TABLE_CREATOR]);
                DB::table(DC::TABLE_SETTINGS)->where(DC::COL_TABLE_CREATOR, $expense[DC::COL_TABLE_CREATOR])->get()->each(fn($row) => $settings[$row->name] = $row->value);
                $this->logExecutionTime($setStart, $action, 'loadSettings');
                $prepStart = microtime(true);
                $totals = ['quantity' => 0, 'rate' => 0, 'discount' => 0, 'taxPrice' => 0];
                $taxesData = [];
                $items = [];
                foreach ($expense->items as $line) {
                    $prod = $line->product;
                    $qty = $line->quantity;
                    $price = $line->price;
                    $disc = $line->discount;
                    $totals['quantity'] += $qty;
                    $totals['rate'] += $price;
                    $totals['discount'] += $disc;
                    $unitName = $prod->unit->name ?? '';
                    $itemTaxes = [];
                    if ($line->tax) {
                        foreach (Utility::tax($line->tax) as $tax) {
                            $tp = Utility::taxRate($tax->rate, $price, $qty, $disc);
                            $totals['taxPrice'] += $tp;
                            $itemTaxes[] = ['name' => $tax->name, 'rate' => "{$tax->rate}%", 'price' => Utility::priceFormat($settings, $tp), 'taxPrice' => $tp];
                            $taxesData[$tax->name] = ($taxesData[$tax->name] ?? 0) + $tp;
                        }
                    }
                    $items[] = (object)['name' => $prod->name ?? '', 'quantity' => $qty, 'price' => $price, 'discount' => $disc, 'description' => $line->description, 'unit' => $unitName, 'itemTax' => $itemTaxes];
                }
                $expense->itemData = $items;
                $expense->totalQuantity = $totals['quantity'];
                $expense->totalRate = $totals['rate'];
                $expense->totalDiscount = $totals['discount'];
                $expense->totalTaxPrice = $totals['taxPrice'];
                $expense->taxesData = $taxesData;
                $expense->customField = CustomField::getData($expense, 'bill');
                $logoStart = microtime(true);
                $logoDir = Storage::url('uploads/logo');
                $settingsData = Utility::settingsById($expense[DC::COL_TABLE_CREATOR]);
                $logoFile = $settingsData['bill_logo'] ?? $settings[SettingsConstants::CPN_LG_DK] ?? SettingsConstants::CPN_LG_DK_DEF;
                $img = asset("$logoDir/$logoFile");
                $this->logExecutionTime($logoStart, $action, 'resolveLogo');
                $colorStart = microtime(true);
                $color = '#' . ($settings['bill_color'] ?? '000000');
                $fontColor = Utility::getFontColor($color);
                $this->logExecutionTime($colorStart, $action, 'resolveColors');
                $viewPath = ViewsConstants::BIL . ".templates.{$settings[BC::COL_BIL_TMP]}";
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::expense] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::expense] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['expense', DC::TABLE_SETTINGS, 'img', 'fontColor']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('expense', DC::TABLE_SETTINGS, 'img', 'fontColor'));
                $this->logExecutionTime($renderStart, $action, 'renderExpense');
                return $resp;
            } catch (DecryptException $e) {
                Log::warning("[{$base}::expense] decrypt failed", ['enc_id' => $encId]);
                return back()->with('error', __('Bill not found.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::expense] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::expense] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(ViewsConstants::EXP . '.index'));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc_id' => $encId]);
    }

    private static function _authorize(Request $r, string $perm): RedirectResponse|true
    {
        if (!$r->user()->can($perm)) {
            Log::warning("ExpenseController::authorize failed", [
                UC::COL_USER_ID   => $r->user()->id,
                'permission' => $perm
            ]);
            return defaultPermissionDenial(
                $r,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
            );
        }
        return true;
    }

    private static function validateOrRedirect(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        if ($v->fails()) {
            $err = $v->errors()->first();
            Log::warning("ExpenseController::validate failed", [
                'errors' => $v->errors()->all(),
                'input'  => $r->all()
            ]);
            return redirect()->back()->with('error', $err);
        }
        return null;
    }

    /** build and save line items + accounts */
    private function syncLines(Bill $bill, array $lines): void
    {
        BillProduct::where('bill_id', $bill->id)->delete();
        BillAccount::where(BC::COL_REF_ID, $bill->id)->where('type', 'Bill')->delete();
        $total = 0;
        foreach ($lines as $itm) {
            if (!empty($itm['item'])) {
                BillProduct::create([
                    'bill_id'     => $bill->id,
                    'product_id'  => $itm['item'],
                    'quantity'    => $itm['quantity'],
                    'tax'         => $itm['tax'],
                    'discount'    => $itm['discount'],
                    'price'       => $itm['price'],
                    'description' => $itm['description'],
                ]);
                Utility::totalQuantity('plus', $itm['quantity'], $itm['item']);
                Utility::addProductStock(
                    $itm['item'],
                    $itm['quantity'],
                    'bill',
                    "{$itm['quantity']} purchased in expense {$bill->bill_id}",
                    $bill->id
                );
                $total += $itm['quantity'] * $itm['price'];
            }
            if (!empty($itm[BKC::COL_COA])) {
                BillAccount::create([
                    BKC::COL_COA => $itm[BKC::COL_COA],
                    'price'            => $itm['amount'],
                    'description'      => $itm['description'],
                    'type'             => 'Bill',
                    BC::COL_REF_ID     => $bill->id,
                ]);
                $total += $itm['amount'];
            }
        }
        // sync "Bill Category" account if provided
        if (!empty(request(BKC::COL_COA))) {
            $cat = ProductServiceCategory::find(request('category_id'));
            BillAccount::updateOrCreate(
                ['type' => 'Bill Category', BC::COL_REF_ID => $bill->id],
                [
                    BKC::COL_COA => $cat->chart_account_id ?? null,
                    'price'           => $total,
                    'description'     => request('description'),
                ]
            );
        }
    }

    private function expenseNumber(): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return optional(
            Bill::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                ->latest('bill_id')->first()
        )->bill_id + 1 ?? 1;
    }

    private function billNumber(): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creatorId = $user?->creatorId();
        $latest   = Bill::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('type', 'Bill')
            ->latest('bill_id')
            ->first();
        $next     = $latest?->bill_id + 1 ?? 1;
        Log::info(__METHOD__, ['creatorId' => $creatorId, 'nextBill' => $next]);
        return $next;
    }
<<<<<<< HEAD
=======

    /**
     * Stub: Show expense payment view.
     * TODO: Implement full payment flow.
     */
    public const PAY = 'payment';
    public function payment(Request $r, int|string $id): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if (($g = self::_authorize($r, PMC::MNG_BIL)) !== true) return $g;
        return redirect()->route(VW::EXP . '.show', $id)
            ->with('info', __('Payment feature is not yet implemented.'));
    }

    /**
     * Alias for index — expense list.
     */
    public const EXP_LST = 'expenseList';
    public function expenseList(Request $r): Response|RedirectResponse|View
    {
        return $this->index($r);
    }
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
}
