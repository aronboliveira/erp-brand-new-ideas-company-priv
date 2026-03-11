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
    ViewsConstants as VW
};
use App\Models\{
    BankAccount,
    BillAccount,
    BillPayment,
    ChartOfAccount,
    Payment,
    ProductServiceCategory,
    Transaction,
    Utility,
    Vendor
};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Validator};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
final class PaymentController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const PERM_MANAGE = PermissionsConstants::MNG_PMT;
    private const PERM_CREATE = 'create payment';
    private const PERM_EDIT  = 'edit payment';
    private const PERM_DELETE = 'delete payment';

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $req)
    {
        $action = __METHOD__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($req, $action, $cls) {
            Log::debug($action . ' start', [
                UC::COL_USER_ID => $req->user()?->id ?? null,
                'filters' => $req->only('date', 'vendor', 'account', 'category'),
            ]);

            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($deny = self::guard($req, self::PERM_MANAGE, VW::PAY . '.index')) !== true) return $deny;

            $uid = $req->user()?->creatorId() ?? null;

            $t = microtime(true);
            $vendors  = Vendor::where(DC::COL_TABLE_CREATOR, $uid)
                ->pluck(UC::COL_NM, 'id')
                ->prepend('Select Vendor', '');
            $accounts = BankAccount::where(DC::COL_TABLE_CREATOR, $uid)
                ->pluck('holder_name', 'id')
                ->prepend('Select Account', '');
            $cats     = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $uid)
                ->where('type', 'expense')
                ->pluck('name', 'id')
                ->prepend('Select Category', '');
            $this->logExecutionTime($t, $action, 'loadFilters');

            $t2 = microtime(true);
            $payments = Payment::where(DC::COL_TABLE_CREATOR, $uid)
                ->when($req->filled('date'), function ($q) use ($req) {
                    $range = preg_split('/\s+to\s+/i', $req->date);
                    $q->whereBetween('date', count($range) > 1 ? $range : [$req->date, $req->date]);
                })
                ->when($req->filled('vendor'), fn($q) => $q->where('vendor_id', $req->vendor))
                ->when($req->filled('account'), fn($q) => $q->where('account_id', $req->account))
                ->when($req->filled('category'), fn($q) => $q->where('category_id', $req->category))
                ->get();
            $this->logExecutionTime($t2, $action, 'loadPayments');

            Log::info($cls . '::index loaded', ['count' => $payments->count()]);
            return view(VW::PAY . '.index', compact('payments', 'vendors', 'accounts', 'cats'));
        }, [UC::COL_USER_ID => $req->user()?->id ?? null]);
    }

    public function create(Request $req)
    {
        $action = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action) {
            Log::debug($action . ' start', [UC::COL_USER_ID => $req->user()?->id ?? null]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($deny = self::guard($req, self::PERM_CREATE, VW::PAY . '.index')) !== true) return $deny;

            $uid = $req->user()?->creatorId() ?? null;

            $t = microtime(true);
            $vendors  = Vendor::where(DC::COL_TABLE_CREATOR, $uid)->pluck('name', 'id')->prepend('--', 0);
            $cats     = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $uid)
                ->whereNotIn('type', ['product & service', 'income'])
                ->pluck('name', 'id')
                ->prepend('Select Category', '');
            $accounts = BankAccount::selectRaw("id, CONCAT(bank_name,' ',holder_name) as name")
                ->where(DC::COL_TABLE_CREATOR, $uid)
                ->pluck('name', 'id');
            $chartAcc = ChartOfAccount::selectRaw("id, CONCAT(code,' - ',name) as code_name")
                ->where(DC::COL_TABLE_CREATOR, $uid)
                ->pluck('code_name', 'id')
                ->prepend('Select Account', '');
            $this->logExecutionTime($t, $action, 'loadCreateFormData');

            return view(VW::PAY . '.create', compact('vendors', 'cats', 'accounts', 'chartAcc'));
        }, [UC::COL_USER_ID => $req->user()?->id ?? null]);
    }

    public function store(Request $req): RedirectResponse
    {
        $action = __METHOD__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($req, $action, $cls) {
            Log::debug($action . ' start', [
                UC::COL_USER_ID => $req->user()?->id ?? null,
                'input' => $req->all()
            ]);

            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($deny = self::guard($req, self::PERM_CREATE, VW::PAY . '.index')) !== true) return $deny;
            if ($resp = $this->validateInput($req, [
                'date'         => 'required|date',
                'amount'       => 'required|numeric|min:0.01',
                'account_id'   => 'required|exists:bank_accounts,id',
                'category_id'  => 'required|exists:product_service_categories,id',
            ], 'store')) return $resp;

            try {
                $t = microtime(true);
                DB::transaction(function () use ($req, $cls) {
                    $p = new Payment([
                        'date'           => $req->date,
                        'amount'         => $req->amount,
                        'account_id'     => $req->account_id,
                        'vendor_id'      => $req->input('vendor_id') ?? 0,
                        'category_id'    => $req->category_id,
                        'payment_method' => 0,
                        'reference'      => $req->reference,
                        'description'    => $req->description,
                        DC::COL_TABLE_CREATOR => $req->user()?->creatorId() ?? null,
                    ]);

                    if ($req->hasFile('add_receipt')) {
                        $file = $req->file('add_receipt');
                        $safe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                        $name = time() . '_' . $safe;
                        $upload = Utility::uploadFile($req, 'add_receipt', $name, 'uploads/payment', []);
                        if (!($upload['flag'] ?? false)) {
                            throw new \RuntimeException($upload['msg'] ?? 'Upload failed');
                        }
                        $p->add_receipt = $name;
                    }

                    $p->save();
                    Log::info($cls . '::store payment saved', ['payment_id' => $p->id]);

                    BillAccount::create([
                        BKC::COL_COA       => $req->account_id,
                        'price'            => $p->amount,
                        'description'      => $p->description,
                        'type'             => 'Payment',
                        BC::COL_REF_ID     => $p->id,
                    ]);

                    $cat = ProductServiceCategory::find($p->category_id);
                    $p->fill([
                        'payment_id' => $p->id,
                        'type'       => 'Payment',
                        'category'   => $cat?->name ?? null,
                        UC::COL_USER_ID => $p->vendor_id,
                        'user_type'  => 'Vendor',
                        'account'    => $p->account_id,
                    ]);
                    Transaction::addTransaction($p);

                    if ($p->vendor_id) {
                        Utility::updateUserBalance('vendor', $p->vendor_id, $p->amount, 'debit');
                    }
                    Utility::bankAccountBalance($p->account_id, $p->amount, 'debit');

                    $settings = Utility::settingsById($req->user()?->creatorId() ?? null);
                    if (!empty($settings['twilio_payment_notification'] ?? null) && $p->vendor_id) {
                        Utility::sendTwilioMsg(
                            Vendor::find($p->vendor_id)?->contact ?? '',
                            'bill_payment',
                            [
                                'payment_amount' => $req->user()?->priceFormat($p->amount) ?? (string) $p->amount,
                                'vendor_name'    => Vendor::find($p->vendor_id)?->name ?? '',
                                'payment_type'   => 'Payment',
                            ]
                        );
                    }
                });
                $this->logExecutionTime($t, $action, 'storeTransaction');

                return redirect()->route(VW::PAY . '.index')
                    ->with('success', __('Payment successfully created'));
            } catch (\Throwable $e) {
                Log::error($cls . '::store failed', [
                    'error' => $e->getMessage(),
                    'input' => $req->all(),
                ]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($cls . '::store failed', [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString(),
                    'input' => $req->all(),
                ]);
                return defaultUndefinedException($req, $e, $cls . '::store');
            }
        }, [UC::COL_USER_ID => $req->user()?->id ?? null]);
    }

    public function edit(Request $req, Payment $payment)
    {
        $action = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $payment, $action) {
            Log::debug($action . ' start', ['payment_id' => $payment->id ?? null]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($deny = self::guard($req, self::PERM_EDIT, VW::PAY . '.index')) !== true) return $deny;

            $uid = $req->user()?->creatorId() ?? null;

            $t = microtime(true);
            $vendors  = Vendor::where(DC::COL_TABLE_CREATOR, $uid)->pluck('name', 'id')->prepend('--', 0);
            $cats     = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $uid)
                ->whereNotIn('type', ['product & service', 'income'])
                ->pluck('name', 'id')
                ->prepend('Select Category', '');
            $accounts = BankAccount::selectRaw("id, CONCAT(bank_name,' ',holder_name) AS name")
                ->where(DC::COL_TABLE_CREATOR, $uid)
                ->pluck('name', 'id');
            $chartAcc = ChartOfAccount::selectRaw("id, CONCAT(code,' - ',name) AS code_name")
                ->where(DC::COL_TABLE_CREATOR, $uid)
                ->pluck('code_name', 'id')
                ->prepend('Select Account', '');
            $this->logExecutionTime($t, $action, 'loadEditFormData');

            return view(VW::PAY . '.edit', compact('payment', 'vendors', 'cats', 'accounts', 'chartAcc'));
        }, ['payment_id' => $payment->id ?? null]);
    }

    public function update(Request $req, Payment $payment): RedirectResponse
    {
        $action = __METHOD__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($req, $payment, $action, $cls) {
            Log::debug($action . ' start', [
                'payment_id' => $payment->id ?? null,
                'input' => $req->all()
            ]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($deny = self::guard($req, self::PERM_EDIT, VW::PAY . '.index')) !== true) return $deny;
            if ($resp = $this->validateInput($req, [
                'date'        => 'required|date',
                'amount'      => 'required|numeric|min:0.01',
                'account_id'  => 'required|exists:bank_accounts,id',
                'vendor_id'   => 'required|exists:vendors,id',
                'category_id' => 'required|exists:product_service_categories,id',
            ], 'update')) return $resp;

            try {
                $t = microtime(true);
                DB::transaction(function () use ($req, $payment, $cls) {
                    if ($payment->vendor_id) {
                        Utility::updateUserBalance('vendor', $payment->vendor_id, $payment->amount, 'credit');
                    }
                    Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

                    $payment->fill($req->only(
                        'date',
                        'amount',
                        'account_id',
                        'vendor_id',
                        'category_id',
                        'reference',
                        'description'
                    ));

                    if ($req->hasFile('add_receipt')) {
                        if ($payment->add_receipt) {
                            Utility::changeStorageLimit(
                                $req->user()?->creatorId() ?? null,
                                "/uploads/payment/{$payment->add_receipt}"
                            );
                        }
                        $file = $req->file('add_receipt');
                        $safe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                        $name = time() . '_' . $safe;
                        $upload = Utility::uploadFile($req, 'add_receipt', $name, 'uploads/payment', []);
                        if (!($upload['flag'] ?? false)) {
                            throw new \RuntimeException($upload['msg'] ?? 'Upload failed');
                        }
                        $payment->add_receipt = $name;
                    }

                    $payment->save();
                    Log::info($cls . '::update saved', ['payment_id' => $payment->id ?? null]);

                    $cat = ProductServiceCategory::find($payment->category_id);
                    $payment->fill([
                        'category' => $cat?->name ?? null,
                        'account'  => $payment->account_id,
                    ]);
                    Transaction::editTransaction($payment);

                    if ($payment->vendor_id) {
                        Utility::updateUserBalance('vendor', $payment->vendor_id, $payment->amount, 'debit');
                    }
                    Utility::bankAccountBalance($payment->account_id, $payment->amount, 'debit');
                });
                $this->logExecutionTime($t, $action, 'updateTransaction');

                return redirect()->route(VW::PAY . '.index')
                    ->with('success', __('Payment Updated Successfully'));
            } catch (\Throwable $e) {
                Log::error($cls . '::update failed', [
                    'payment_id' => $payment->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($cls . '::update failed', [
                    'payment_id' => $payment->id ?? null,
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString(),
                ]);
                return defaultUndefinedException($req, $e, $cls . '::update');
            }
        }, ['payment_id' => $payment->id ?? null]);
    }

    public function destroy(Request $req, Payment $payment): RedirectResponse
    {
        $action = __METHOD__;
        $cls = __CLASS__;
        return $this->measureProfile($action, function () use ($req, $payment, $action, $cls) {
            Log::debug($action . ' start', ['payment_id' => $payment->id ?? null]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($deny = self::guard($req, self::PERM_DELETE, VW::PAY . '.index')) !== true) return $deny;

            if (($payment[DC::COL_TABLE_CREATOR] ?? null) !== ($req->user()?->creatorId() ?? null)) {
                Log::warning($cls . '::destroy forbidden owner mismatch', [
                    'payment_id' => $payment->id ?? null,
                    UC::COL_USER_ID => $req->user()?->id ?? null
                ]);
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($req, $payment, $cls) {
                    if ($payment->add_receipt) {
                        Utility::changeStorageLimit(
                            $req->user()?->creatorId() ?? null,
                            "/uploads/payment/{$payment->add_receipt}"
                        );
                    }

                    $pid = $payment->id;
                    $vid = $payment->vendor_id;
                    $amt = $payment->amount;
                    $aid = $payment->account_id;

                    $payment->delete();
                    Transaction::destroyTransaction($pid, 'Payment', 'Vendor');

                    if ($vid) {
                        Utility::updateUserBalance('vendor', $vid, $amt, 'credit');
                    }
                    Utility::bankAccountBalance($aid, $amt, 'credit');

                    Log::info($cls . '::destroy completed', ['payment_id' => $pid]);
                });
                $this->logExecutionTime($t, $action, 'destroyTransaction');

                return redirect()->route(VW::PAY . '.index')
                    ->with('success', __('Payment successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error($cls . '::destroy failed', [
                    'payment_id' => $payment->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug($cls . '::destroy failed', [
                    'payment_id' => $payment->id ?? null,
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString(),
                ]);
                return defaultUndefinedException($req, $e, $cls . '::destroy');
            }
        }, ['payment_id' => $payment->id ?? null]);
    }

    /**
     * Centralized permission check.
     * Returns a RedirectResponse on denial (after logging), or null if allowed.
     */
    private function deny(Request $req, string $permission, string $action): ?RedirectResponse
    {
        $cls = __CLASS__;
        if (!$req->user()->can($permission)) {
            Log::warning($cls . "::{$action} — permission denied", [
                UC::COL_USER_ID    => $req->user()->id,
                'permission' => $permission,
            ]);
            return defaultPermissionDenial(
                $req,
                new AuthorizationException($permission),
                $cls . '::' . $action
            );
        }
        return null;
    }

    /**
     * Validate inputs, log failures.
     */
    private function validateInput(Request $req, array $rules, string $action): ?RedirectResponse
    {
        $cls = __CLASS__;
        $v = Validator::make($req->all(), $rules);
        if ($v->fails()) {
            $msg = $v->errors()->first();
            Log::warning($cls . "::{$action} — validation failed", [
                'errors' => $msg,
                'input'  => $req->all(),
            ]);
            return redirect()->back()->with('error', $msg);
        }
        return null;
    }

    public function show(Request $req, \App\Models\Payment $payment): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $action = __FUNCTION__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($req, $payment, $action, $class) {
            if (($u = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $u;
            if (($r = self::guard($req, 'manage payment')) !== true) return $r;
            try {
                $viewPath = 'payments.show';
                if (!\Illuminate\Support\Facades\View::exists($viewPath))
                    return redirect()->route('dashboard')->with('error', 'Payment show view not found.');
                return response()->view($viewPath, ['payment' => $payment]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("[$class::$action] failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "$class::$action");
            }
        });
    }

}
