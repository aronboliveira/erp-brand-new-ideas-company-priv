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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{
    Request,
    JsonResponse,
    RedirectResponse,
    Response
};
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    DB,
    Log,
    Storage,
    Validator
};
use Illuminate\View\View;
use Throwable;

final class ExpenseController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $r): Response|RedirectResponse
    {
        if (($auth = self::_authorize($r, PermissionsConstants::MNG_BIL)) !== true)
            return $auth;
        Log::info('ExpenseController@index', [
            UsersConstants::COL_USER_ID => $r->user()->id,
            'filters' => $r->only(['vendor', 'bill_date', 'category'])
        ]);
        $uid = $r->user()->creatorId();
        $q  = Bill::where('type', 'Expense')->where(DatabaseConstants::TABLE_CREATOR, $uid);
        if ($r->filled('vendor'))      $q->where('vendor_id', $r->vendor);
        if ($r->filled('bill_date')) {
            [$s, $e] = explode(' to ', $r->bill_date) + [$r->bill_date, $r->bill_date];
            $q->whereBetween('bill_date', [$s, $e]);
        }
        if ($r->filled('category'))    $q->where('category_id', $r->category);
        $expenses = $q->get();
        $vendorLst = Vendor::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->pluck(UsersConstants::COL_NM, 'id')
            ->prepend('Select Vendor', '');
        $catLst   = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->whereNotIn('type', ['product & service', 'income'])
            ->pluck('name', 'id')->prepend('Select Category', '');
        Log::info('expenses loaded', ['count' => $expenses->count()]);
        return view(ViewsConstants::EXP . '.index', [
            'expenses' => $expenses,
            'vendor' => $vendorLst,
            'status' => Bill::$statuses,
            'category' => $catLst
        ]);
    }

    public function create(string|int $refId): mixed
    {
        $r = request();
        if ((self::_authorize($r, 'create bill')) !== true)
            return response()->json(['error' => __('Permission denied.')], 401);
        Log::info(__METHOD__, ['reference' => $refId, 'user' => Auth::id()]);
        /** @var \App\Models\User $user */
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        $user = $u;
        $uid = $user?->creatorId();
        $data = [
            'employees'    => Employee::where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend('Select Employee', ''),
            'customers'    => Customer::where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend('Select Customer', ''),
            'vendors'      => Vendor::where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend('Select Vendor', ''),
            'num'          => $user?->expenseNumberFormat($this->expenseNumber()),
            'items'        => ProductService::where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->pluck('name', 'id')->prepend('Select Item', ''),
            'categories'   => ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->whereNotIn('type', ['product & service', 'income'])
                ->pluck('name', 'id')->prepend('Select Category', ''),
            'customFields' => CustomField::where([[DatabaseConstants::TABLE_CREATOR, $uid], ['module', 'bill']])->get(),
            'accounts'     => ChartOfAccount::select(DB::raw('CONCAT(code," - ",name) AS code_name,id'))
                ->where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->pluck('code_name', 'id')->prepend('Select Account', ''),
            'banks'        => BankAccount::select(DB::raw("CONCAT(bank_name,' ',holder_name) AS name"), 'id')
                ->where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->pluck('name', 'id'),
            'id'           => $refId
        ];

        return view(ViewsConstants::EXP . '.create', $data);
    }

    public function store(Request $r): RedirectResponse
    {
        if (($auth = self::_authorize($r, 'create bill')) !== true)
            return $auth;
        if ($resp = self::validateOrRedirect($r, ['payment_date' => 'required|date']))
            return $resp;
        Log::info(__CLASS__ . '@' . __FUNCTION__, [
            UsersConstants::COL_USER_ID => Auth::id(),
            'input' => $r->all()
        ]);
        DB::beginTransaction();
        try {
            $bill = new Bill([
                'bill_id'       => $this->expenseNumber(),
                'vendor_id'     => match ($r->type) {
                    'employee' => $r->employee_id,
                    'customer' => $r->customer_id,
                    default => $r->vendor_id
                },
                'bill_date'     => $r->payment_date,
                'due_date'      => $r->payment_date,
                'status'        => 4,
                'type'          => 'Expense',
                'user_type'     => $r->type,
                'category_id'   => $r->category_id ?? 0,
                'order_number'  => 0,
                DatabaseConstants::TABLE_CREATOR    => $r->user()->creatorId(),
            ]);
            $bill->save();
            // line items + accounts
            $this->syncLines($bill, $r->items ?? []);
            // immediate full payment record
            BillPayment::create([
                'bill_id'       => $bill->id,
                'date'          => $r->payment_date,
                'amount'        => $r->totalAmount,
                'account_id'    => $r->account_id,
                'payment_method' => 0,
                'reference'     => null,
                'description'   => null,
                'add_receipt'   => null,
            ]);
            DB::commit();
            Log::info('Expense stored', ['bill_id' => $bill->id]);
            return redirect()->route(ViewsConstants::EXP . '.index')->with('success', __('Expense successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ExpenseController@store failed', [
                'exception' => $e->getMessage(),
                UsersConstants::COL_USER_ID => Auth::id()
            ]);
            return defaultUndefinedException($r, $e, __CLASS__ . '::store');
        }
    }

    public function show(Request $r, string $encId): mixed
    {
        if ((self::_authorize($r, 'show bill')) !== true)
            return redirect()->back()->with('error', __('Permission denied.'));
        Log::info('ExpenseController@show start', ['encId' => $encId]);
        try {
            $id = Crypt::decryptString($encId);
            $exp = Bill::with(['items', 'accounts', 'payments'])->findOrFail($id);
            if ($exp[DatabaseConstants::TABLE_CREATOR] !== $r->user()->creatorId()) {
                Log::warning('ExpenseController@show unauthorized', ['user' => Auth::id(), 'bill' => $id]);
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            // choose associated user (employee/customer/vendor)
            $assocUser = match ($exp->user_type) {
                'employee' => Employee::find($exp->user_id),
                'customer' => Customer::find($exp->user_id),
                default   => Vendor::find($exp->vendor_id)
            };
            Log::info('ExpenseController@show loaded', ['bill_id' => $id]);
            return view(ViewsConstants::EXP . '.view', [
                'exp' => $exp,
                'user' => $assocUser,
                'items' => $exp->items,
                'payment' => $exp->payments->first()
            ]);
        } catch (\Throwable $e) {
            Log::error('ExpenseController@show error', ['exception' => $e->getMessage(), 'encId' => $encId]);
            return defaultUndefinedException($r, $e, __CLASS__ . '::show');
        }
    }

    public function edit(Request $r, string $encId): mixed
    {
        if ((self::_authorize($r, 'edit bill')) !== true)
            return response()->json(['error' => __('Permission denied.')], 401);
        Log::info(__CLASS__ . '@' . __FUNCTION__ . ' start', ['encId' => $encId]);
        try {
            $id = Crypt::decryptString($encId);
            $exp = Bill::with(['items', 'accounts'])->findOrFail($id);
            if ($exp[DatabaseConstants::TABLE_CREATOR] !== $r->user()->creatorId()) {
                Log::warning(__CLASS__ . '@' . __FUNCTION__ . ' unauthorized', ['user' => Auth::id(), 'bill_id' => $id]);
                return response()->json(['error' => __('Permission denied.')], 401);
            }
            $uid = $r->user()->creatorId();
            $data = [
                'exp' => $exp,
                'num' => $r->user()->expenseNumberFormat($exp->bill_id),
                'employees' => Employee::where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Employee', ''),
                'customers' => Customer::where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Customer', ''),
                'vendors' => Vendor::where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Vendor', ''),
                'products' => ProductService::where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck('name', 'id'),
                'categories' => ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->whereNotIn('type', ['product & service', 'income'])
                    ->pluck('name', 'id')->prepend('Select Category', ''),
                'customFields' => CustomField::where([[DatabaseConstants::TABLE_CREATOR, $uid], ['module', 'bill']])->get(),
                'accounts' => ChartOfAccount::select(DB::raw('CONCAT(code," - ",name) AS code_name,id'))
                    ->where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck('code_name', 'id')->prepend('Select Account', ''),
                'banks' => BankAccount::select(DB::raw("CONCAT(bank_name,' ',holder_name) AS name"), 'id')
                    ->where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck('name', 'id')
            ];
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' loaded', ['bill_id' => $id]);
            return view(ViewsConstants::EXP . '.edit', $data);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', ['exception' => $e->getMessage(), 'encId' => $encId]);
            return defaultUndefinedException($r, $e, __CLASS__ . '::edit');
        }
    }

    public function update(Request $r, string|int $id): RedirectResponse
    {
        if (($auth = self::_authorize($r, 'edit bill')) !== true)
            return $auth;
        if ($resp = self::validateOrRedirect($r, ['bill_date' => 'required|date']))
            return $resp;
        Log::info(__CLASS__ . '@' . __FUNCTION__ . ' start', ['bill_id' => $id, 'input' => $r->all()]);
        DB::beginTransaction();
        try {
            $exp = Bill::findOrFail($id);
            if ($exp[DatabaseConstants::TABLE_CREATOR] !== $r->user()->creatorId()) {
                Log::warning(__CLASS__ . '@' . __FUNCTION__ . ' unauthorized', ['user' => Auth::id(), 'bill_id' => $id]);
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            $exp->update([
                'vendor_id'   => match ($r->type) {
                    'employee' => $r->employee_id,
                    'customer' => $r->customer_id,
                    default   => $r->vendor_id
                },
                'bill_date'   => $r->bill_date,
                'due_date'    => $r->bill_date,
                'category_id' => $r->category_id,
            ]);
            // re-sync lines
            $this->syncLines($exp, $r->items ?? []);
            DB::commit();
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' success', ['bill_id' => $id]);
            return redirect()->route(ViewsConstants::EXP . '.index')->with('success', __('Expense successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', ['exception' => $e->getMessage(), 'bill_id' => $id]);
            return defaultUndefinedException($r, $e, __CLASS__ . '::update');
        }
    }

    public function productDestroy(Request $r): RedirectResponse
    {
        if (($auth = self::_authorize($r, 'delete bill product')) !== true)
            return $auth;
        Log::info(__CLASS__ . '@' . __FUNCTION__, ['product_id' => $r->id, 'user' => Auth::id()]);
        DB::beginTransaction();
        try {
            $bp = BillProduct::findOrFail($r->id);
            $exp = Bill::findOrFail($bp->bill_id);
            Utility::updateUserBalance('vendor', $exp->vendor_id, $r->amount, 'credit');
            $bp->delete();
            DB::commit();
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' success', ['product_id' => $bp->id]);
            return redirect()->back()->with('success', __('Expense product successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', ['exception' => $e->getMessage(), 'product_id' => $r->id]);
            return defaultUndefinedException($r, $e, __CLASS__ . '::productDestroy');
        }
    }

    public function destroy(Request $r, string|int $id): RedirectResponse
    {
        if (($auth = self::_authorize($r, 'delete bill')) !== true)
            return $auth;
        Log::info(__CLASS__ . '@' . __FUNCTION__, ['bill_id' => $id, 'user' => Auth::id()]);
        DB::beginTransaction();
        try {
            $exp = Bill::findOrFail($id);
            if ($exp[DatabaseConstants::TABLE_CREATOR] !== $r->user()->creatorId()) {
                Log::warning(__CLASS__ . '@' . __FUNCTION__ . ' unauthorized', ['user' => Auth::id(), 'bill_id' => $id]);
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            // reverse payments
            foreach ($exp->payments as $p) {
                Utility::bankAccountBalance($p->account_id, $p->amount, 'credit');
                $p->delete();
            }
            if ($exp->vendor_id && $exp->status)
                Utility::updateUserBalance('vendor', $exp->vendor_id, $exp->getDue(), 'credit');
            BillProduct::where('bill_id', $exp->id)->delete();
            BillAccount::where('ref_id', $exp->id)->delete();
            $exp->delete();
            DB::commit();
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' success', ['bill_id' => $id]);
            return redirect()->route(ViewsConstants::EXP . '.index')->with('success', __('Expense successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', ['exception' => $e->getMessage(), 'bill_id' => $id]);
            return defaultUndefinedException($r, $e, __CLASS__ . '::destroy');
        }
    }

    public function employee(Request $request): View|RedirectResponse
    {
        Log::info(__METHOD__, [
            UsersConstants::COL_USER_ID     => $request->user()->id,
            'employee_id' => $request->id,
        ]);
        if (($auth = self::_authorize($request, PermissionsConstants::MNG_BIL)) !== true)
            return $auth;
        try {
            $emp = Employee::find($request->id);
            if (!$emp) {
                Log::warning(__METHOD__ . ' not found', ['employee_id' => $request->id]);
                return redirect()->back()->with('error', __('Employee not found.'));
            }
            return view(ViewsConstants::EXP . '.employee_detail', ['employee' => $emp]);
        } catch (Throwable $e) {
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function product(Request $request): JsonResponse|RedirectResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        $request->validate([
            'product_id' => 'required|integer|exists:product_services,id',
        ]);
        Log::info("$action start", ['product_id' => $request->input('product_id')]);
        try {
            $productId    = $request->input('product_id');
            $product      = ProductService::findOrFail($productId);
            $unit         = $product->unit?->name ?? '';
            $taxRate      = $product->tax_id
                ? $product->taxRate($product->tax_id)
                : 0;
            $taxes        = $product->tax_id
                ? $product->tax($product->tax_id)
                : 0;
            $purchasePrice = $product->purchase_price;
            $quantity     = 1;
            $totalAmount  = $purchasePrice * $quantity;
            Log::info("$action success", ['productId' => $productId]);
            return response()->json([
                'product'     => $product,
                'unit'        => $unit,
                'taxRate'     => $taxRate,
                'taxes'       => $taxes,
                'totalAmount' => $totalAmount,
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::warning("$action not found", ['productId' => $request->input('product_id')]);
            return response()->json(
                ['error' => __('Product not found.')],
                Response::HTTP_NOT_FOUND
            );
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(ViewsConstants::EXP . '.index')
            );
        }
    }

    public function vendor(Request $request): View|RedirectResponse
    {
        Log::info(__METHOD__, [
            UsersConstants::COL_USER_ID   => $request->user()->id,
            'vendor_id' => $request->id,
        ]);
        if (($auth = self::_authorize($request, PermissionsConstants::MNG_BIL)) !== true)
            return $auth;
        try {
            $vendor = Vendor::find($request->id);
            if (!$vendor) {
                Log::warning(__METHOD__ . ' not found', ['vendor_id' => $request->id]);
                return redirect()->back()->with('error', __('Vendor not found.'));
            }
            return view(ViewsConstants::EXP . '.vendor_detail', ['vendor' => $vendor]);
        } catch (Throwable $e) {
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function customer(Request $request): View|RedirectResponse
    {
        Log::info(__METHOD__, [
            UsersConstants::COL_USER_ID     => $request->user()->id,
            'customer_id' => $request->id,
        ]);
        if (($auth = self::_authorize($request, PermissionsConstants::MNG_BIL)) !== true)
            return $auth;
        try {
            $customer = Customer::find($request->id);
            if (!$customer) {
                Log::warning(__METHOD__ . ' not found', ['customer_id' => $request->id]);
                return redirect()->back()->with('error', __('Customer not found.'));
            }
            return view(ViewsConstants::EXP . '.customer_detail', ['customer' => $customer]);
        } catch (Throwable $e) {
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __METHOD__);
        }
    }

    public function items(Request $request): JsonResponse|RedirectResponse
    {
        Log::info(__METHOD__, [
            UsersConstants::COL_USER_ID    => $request->user()->id,
            'bill_id'    => $request->bill_id,
            'product_id' => $request->product_id,
        ]);
        if (($auth = self::_authorize($request, PermissionsConstants::MNG_BIL)) !== true)
            return $auth;
        try {
            $item = BillProduct::where('bill_id', $request->bill_id)
                ->where('product_id', $request->product_id)
                ->first();
            return response()->json($item);
        } catch (Throwable $e) {
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return response()->json(['error' => __('An unexpected error occurred.')], 500);
        }
    }

    public function expense(Request $request, string $encId): View|RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        Log::info("$action start", ['encId' => $encId, 'user' => $request->user()->id]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        try {
            $id = Crypt::decryptString($encId);
            $expense = Bill::with(['items.product.unit', 'items.product'])
                ->whereKey($id)
                ->firstOrFail();
            if ($expense[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action);
            // load settings
            $settings = Utility::settings($expense[DatabaseConstants::TABLE_CREATOR]);
            DB::table(DatabaseConstants::TABLE_SETTINGS)
                ->where(DatabaseConstants::TABLE_CREATOR, $expense[DatabaseConstants::TABLE_CREATOR])
                ->get()
                ->each(fn ($row) => $settings[$row->name] = $row->value);
            // prepare items and totals
            $totals = ['quantity' => 0, 'rate' => 0, 'discount' => 0, 'taxPrice' => 0];
            $taxesData = [];
            $items = [];
            foreach ($expense->items as $line) {
                $prod = $line->product;
                $qty = $line->quantity;
                $price = $line->price;
                $disc = $line->discount;
                $totals['quantity'] += $qty;
                $totals['rate']     += $price;
                $totals['discount'] += $disc;
                $unitName = $prod->unit->name ?? '';
                $itemTaxes = [];
                if ($line->tax) {
                    foreach (Utility::tax($line->tax) as $tax) {
                        $taxPrice = Utility::taxRate($tax->rate, $price, $qty, $disc);
                        $totals['taxPrice'] += $taxPrice;
                        $itemTaxes[] = [
                            'name'     => $tax->name,
                            'rate'     => "{$tax->rate}%",
                            'price'    => Utility::priceFormat($settings, $taxPrice),
                            'taxPrice' => $taxPrice,
                        ];
                        $taxesData[$tax->name] = ($taxesData[$tax->name] ?? 0) + $taxPrice;
                    }
                }
                $items[] = (object)[
                    'name'        => $prod->name ?? '',
                    'quantity'    => $qty,
                    'price'       => $price,
                    'discount'    => $disc,
                    'description' => $line->description,
                    'unit'        => $unitName,
                    'itemTax'     => $itemTaxes,
                ];
            }
            $expense->itemData     = $items;
            $expense->totalQuantity = $totals['quantity'];
            $expense->totalRate    = $totals['rate'];
            $expense->totalDiscount = $totals['discount'];
            $expense->totalTaxPrice = $totals['taxPrice'];
            $expense->taxesData    = $taxesData;
            $expense->customField  = CustomField::getData($expense, 'bill');
            $logoDir      = Storage::url('uploads/logo');
            $settingsData = Utility::settingsById($expense[DatabaseConstants::TABLE_CREATOR]);
            $logoFile     = $settingsData['bill_logo'] ?? $settings[SettingsConstants::CPN_LG_DK] ?? SettingsConstants::CPN_LG_DK_DEF;
            $img          = asset("$logoDir/$logoFile");
            $color     = '#' . ($settings['bill_color'] ?? '000000');
            $fontColor = Utility::getFontColor($color);
            Log::info("$action success", ['bill_id' => $expense->id]);
            return view(
                ViewsConstants::BIL . ".templates.{$settings[BillsConstants::COL_BIL_TMP]}",
                compact('expense', DatabaseConstants::TABLE_SETTINGS, 'img', 'fontColor')
            );
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::warning("$action decrypt failed", ['encId' => $encId]);
            return redirect()->back()->with('error', __('Bill not found.'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(ViewsConstants::EXP . '.index'));
        }
    }

    private static function _authorize(Request $r, string $perm): RedirectResponse|true
    {
        if (!$r->user()->can($perm)) {
            Log::warning("ExpenseController::authorize failed", [
                UsersConstants::COL_USER_ID   => $r->user()->id,
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
        BillAccount::where('ref_id', $bill->id)->where('type', 'Bill')->delete();
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
            if (!empty($itm['chart_account_id'])) {
                BillAccount::create([
                    'chart_account_id' => $itm['chart_account_id'],
                    'price'            => $itm['amount'],
                    'description'      => $itm['description'],
                    'type'             => 'Bill',
                    'ref_id'           => $bill->id,
                ]);
                $total += $itm['amount'];
            }
        }
        // sync "Bill Category" account if provided
        if (!empty(request('chart_account_id'))) {
            $cat = ProductServiceCategory::find(request('category_id'));
            BillAccount::updateOrCreate(
                ['type' => 'Bill Category', 'ref_id' => $bill->id],
                [
                    'chart_account_id' => $cat->chart_account_id ?? null,
                    'price'           => $total,
                    'description'     => request('description'),
                ]
            );
        }
    }

    private function expenseNumber(): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return optional(
            Bill::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->latest('bill_id')->first()
        )->bill_id + 1 ?? 1;
    }

    private function billNumber(): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creatorId = $user?->creatorId();
        $latest   = Bill::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->where('type', 'Bill')
            ->latest('bill_id')
            ->first();
        $next     = $latest?->bill_id + 1 ?? 1;
        Log::info(__METHOD__, ['creatorId' => $creatorId, 'nextBill' => $next]);
        return $next;
    }
}
