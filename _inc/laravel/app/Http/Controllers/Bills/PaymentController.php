<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Validator};

final class PaymentController extends Controller
{
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
        $action = 'index';
        Log::info(__CLASS__ . "::{$action} start", [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'filters' => $req->only('date', 'vendor', 'account', 'category'),
        ]);
        if ($deny = $this->deny($req, self::PERM_MANAGE, $action))
            return $deny;
        $uid = $req->user()->creatorId();
        $vendors = Vendor::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->pluck(UsersConstants::COL_NM, 'id')
            ->prepend('Select Vendor', '');
        $accounts = BankAccount::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->pluck('holder_name', 'id')
            ->prepend('Select Account', '');
        $cats    = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->where('type', 'expense')
            ->pluck('name', 'id')
            ->prepend('Select Category', '');
        $q = Payment::where(DatabaseConstants::TABLE_CREATOR, $uid);
        if ($req->filled('date')) {
            $range = preg_split('/\s+to\s+/i', $req->date);
            $q->whereBetween('date', count($range) > 1 ? $range : [$req->date, $req->date]);
        }
        if ($req->filled('vendor'))   $q->where('vendor_id',  $req->vendor);
        if ($req->filled('account'))  $q->where('account_id', $req->account);
        if ($req->filled('category')) $q->where('category_id', $req->category);
        $payments = $q->get();
        Log::info(__CLASS__ . "::{$action} loaded", ['count' => $payments->count()]);
        return view(ViewsConstants::PAY . '.index', compact('payments', 'vendors', 'accounts', 'cats'));
    }

    /**
     * GET /payments/create
     */
    public function create(Request $req)
    {
        $action = 'create';
        Log::info(__CLASS__ . "::{$action} start", [UsersConstants::COL_USER_ID => $req->user()->id]);
        if ($deny = $this->deny($req, self::PERM_CREATE, $action))
            return response()->json(['error' => __('Permission denied.')], 401);
        $uid = $req->user()->creatorId();
        $vendors = Vendor::where(DatabaseConstants::TABLE_CREATOR, $uid)->pluck('name', 'id')->prepend('--', 0);
        $cats    = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->whereNotIn('type', ['product & service', 'income'])
            ->pluck('name', 'id')
            ->prepend('Select Category', '');
        $accounts = BankAccount::selectRaw("id, CONCAT(bank_name,' ',holder_name) as name")
            ->where(DatabaseConstants::TABLE_CREATOR, $uid)->pluck('name', 'id');
        $chartAcc = ChartOfAccount::selectRaw("id, CONCAT(code,' - ',name) as code_name")
            ->where(DatabaseConstants::TABLE_CREATOR, $uid)->pluck('code_name', 'id')->prepend('Select Account', '');
        return view(ViewsConstants::PAY . '.create', compact('vendors', 'cats', 'accounts', 'chartAcc'));
    }

    /**
     * POST /payments
     */
    public function store(Request $req): RedirectResponse
    {
        $action = 'store';
        Log::info(__CLASS__ . "::{$action} start", [
            UsersConstants::COL_USER_ID => $req->user()->id, 'input' => $req->all()
        ]);
        if ($deny = $this->deny($req, self::PERM_CREATE, $action)) {
            return $deny;
        }
        if ($resp = $this->validateInput($req, [
            'date'       => 'required|date',
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:bank_accounts,id',
            'category_id' => 'required|exists:product_service_categories,id',
        ], $action)) {
            return $resp;
        }

        try {
            $payment = DB::transaction(function () use ($req, $action) {
                // create payment
                $p = new Payment([
                    'date'           => $req->date,
                    'amount'         => $req->amount,
                    'account_id'     => $req->account_id,
                    'vendor_id'      => $req->input('vendor_id', 0),
                    'category_id'    => $req->category_id,
                    'payment_method' => 0,
                    'reference'      => $req->reference,
                    'description'    => $req->description,
                    DatabaseConstants::TABLE_CREATOR     => $req->user()->creatorId(),
                ]);

                // handle receipt
                if ($req->hasFile('add_receipt')) {
                    $file = $req->file('add_receipt');
                    $safe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                    $name = time() . '_' . $safe;
                    $upload = Utility::uploadFile($req, 'add_receipt', $name, 'uploads/payment', []);
                    if (!$upload['flag']) {
                        throw new \RuntimeException($upload['msg']);
                    }
                    $p->add_receipt = $name;
                }

                $p->save();
                Log::info(__CLASS__ . "::{$action} payment saved", ['payment_id' => $p->id]);

                // ledger
                BillAccount::create([
                    'chart_account_id' => $req->account_id,
                    'price'           => $p->amount,
                    'description'     => $p->description,
                    'type'            => 'Payment',
                    'ref_id'          => $p->id,
                ]);

                // transaction
                $cat = ProductServiceCategory::find($p->category_id);
                $p->fill([
                    'payment_id' => $p->id,
                    'type'      => 'Payment',
                    'category'  => $cat?->name,
                    UsersConstants::COL_USER_ID   => $p->vendor_id,
                    'user_type' => 'Vendor',
                    'account'   => $p->account_id,
                ]);
                Transaction::addTransaction($p);

                // balances
                if ($p->vendor_id) {
                    Utility::userBalance('vendor', $p->vendor_id, $p->amount, 'debit');
                }
                Utility::bankAccountBalance($p->account_id, $p->amount, 'debit');

                // twilio notification
                $settings = Utility::settings($req->user()->creatorId());
                if (!empty($settings['twilio_payment_notification']) && $p->vendor_id) {
                    Utility::sendTwilioMsg(
                        $req->contact,
                        'bill_payment',
                        [
                            'payment_amount' => $req->user()->priceFormat($p->amount),
                            'vendor_name'   => Vendor::find($p->vendor_id)?->name,
                            'payment_type'  => 'Payment',
                        ]
                    );
                }

                return $p;
            });
            return redirect()->route(ViewsConstants::PAY . '.index')
                ->with('success', __('Payment successfully created'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} failed", [
                'error'      => $e->getMessage(),
                'input'      => $req->all(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . "::{$action} failed", [
                'error'      => $e->getMessage(),
                'stack'      => $e->getTraceAsString(),
                'input'      => $req->all(),
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . "::{$action}");
        }
    }

    /**
     * GET /payments/{payment}/edit
     */
    public function edit(Request $req, Payment $payment)
    {
        $action = 'edit';
        Log::info(__CLASS__ . "::{$action} start", ['payment_id' => $payment->id]);
        if ($deny = $this->deny($req, self::PERM_EDIT, $action)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $uid = $req->user()->creatorId();
        $vendors = Vendor::where(DatabaseConstants::TABLE_CREATOR, $uid)->pluck('name', 'id')->prepend('--', 0);
        $cats    = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->whereNotIn('type', ['product & service', 'income'])
            ->pluck('name', 'id')->prepend('Select Category', '');
        $accounts = BankAccount::selectRaw("id, CONCAT(bank_name,' ',holder_name) AS name")
            ->where(DatabaseConstants::TABLE_CREATOR, $uid)->pluck('name', 'id');
        $chartAcc = ChartOfAccount::selectRaw("id, CONCAT(code,' - ',name) AS code_name")
            ->where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->pluck('code_name', 'id')
            ->prepend('Select Account', '');

        return view(ViewsConstants::PAY . '.edit', compact('payment', 'vendors', 'cats', 'accounts', 'chartAcc'));
    }

    /**
     * PUT /payments/{payment}
     */
    public function update(Request $req, Payment $payment): RedirectResponse
    {
        $action = 'update';
        Log::info(__CLASS__ . "::{$action} start", [
            'payment_id' => $payment->id, 'input' => $req->all()
        ]);
        if ($deny = $this->deny($req, self::PERM_EDIT, $action)) {
            return $deny;
        }
        if ($resp = $this->validateInput($req, [
            'date'        => 'required|date',
            'amount'      => 'required|numeric|min:0.01',
            'account_id'  => 'required|exists:bank_accounts,id',
            'vendor_id'   => 'required|exists:vendors,id',
            'category_id' => 'required|exists:product_service_categories,id',
        ], $action)) {
            return $resp;
        }

        try {
            DB::transaction(function () use ($req, $payment, $action) {
                // rollback old
                if ($payment->vendor_id) {
                    Utility::userBalance('vendor', $payment->vendor_id, $payment->amount, 'credit');
                }
                Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

                // update fields
                $payment->fill($req->only(
                    'date',
                    'amount',
                    'account_id',
                    'vendor_id',
                    'category_id',
                    'reference',
                    'description'
                ));

                // replace receipt
                if ($req->hasFile('add_receipt')) {
                    if ($payment->add_receipt) {
                        Utility::changeStorageLimit(
                            $req->user()->creatorId(),
                            "/uploads/payment/{$payment->add_receipt}"
                        );
                    }
                    $file = $req->file('add_receipt');
                    $safe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                    $name = time() . '_' . $safe;
                    $upload = Utility::uploadFile($req, 'add_receipt', $name, 'uploads/payment', []);
                    if (!$upload['flag']) {
                        throw new \RuntimeException($upload['msg']);
                    }
                    $payment->add_receipt = $name;
                }

                $payment->save();
                Log::info(__CLASS__ . "::{$action} saved", ['payment_id' => $payment->id]);

                // update transaction
                $cat = ProductServiceCategory::find($payment->category_id);
                $payment->fill([
                    'category' => $cat?->name,
                    'account' => $payment->account_id,
                ]);
                Transaction::editTransaction($payment);
                if ($payment->vendor_id) {
                    Utility::userBalance('vendor', $payment->vendor_id, $payment->amount, 'debit');
                }
                Utility::bankAccountBalance($payment->account_id, $payment->amount, 'debit');
            });
            return redirect()->route(ViewsConstants::PAY . '.index')
                ->with('success', __('Payment Updated Successfully'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} failed", [
                'payment_id' => $payment->id,
                'error'     => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . "::{$action} failed", [
                'payment_id' => $payment->id,
                'error'     => $e->getMessage(),
                'stack'     => $e->getTraceAsString(),
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . "::{$action}");
        }
    }

    /**
     * DELETE /payments/{payment}
     */
    public function destroy(Request $req, Payment $payment): RedirectResponse
    {
        $action = 'destroy';
        Log::info(__CLASS__ . "::{$action} start", ['payment_id' => $payment->id]);
        if ($deny = $this->deny($req, self::PERM_DELETE, $action))
            return $deny;
        if ($payment->created_by !== $req->user()->creatorId()) {
            Log::warning(__CLASS__ . "::{$action} forbidden owner mismatch", [
                'payment_id' => $payment->id, UsersConstants::COL_USER_ID => $req->user()->id
            ]);
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        try {
            DB::transaction(function () use ($req, $payment, $action) {
                if ($payment->add_receipt)
                    Utility::changeStorageLimit(
                        $req->user()->creatorId(),
                        "/uploads/payment/{$payment->add_receipt}"
                    );
                $pid = $payment->id;
                $vid = $payment->vendor_id;
                $amt = $payment->amount;
                $aid = $payment->account_id;
                $payment->delete();
                Transaction::destroyTransaction($pid, 'Payment', 'Vendor');
                if ($vid) Utility::userBalance('vendor', $vid, $amt, 'credit');
                Utility::bankAccountBalance($aid, $amt, 'credit');
                Log::info(__CLASS__ . "::{$action} completed", ['payment_id' => $pid]);
            });
            return redirect()->route(ViewsConstants::PAY . '.index')
                ->with('success', __('Payment successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} failed", [
                'payment_id' => $payment->id,
                'error'     => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . "::{$action} failed", [
                'payment_id' => $payment->id,
                'error'     => $e->getMessage(),
                'stack'     => $e->getTraceAsString(),
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . "::{$action}");
        }
    }

    /**
     * Centralized permission check.
     * Returns a RedirectResponse on denial (after logging), or null if allowed.
     */
    private function deny(Request $req, string $permission, string $action): ?RedirectResponse
    {
        if (!$req->user()->can($permission)) {
            Log::warning(__CLASS__ . "::{$action} — permission denied", [
                UsersConstants::COL_USER_ID    => $req->user()->id,
                'permission' => $permission,
            ]);
            return defaultPermissionDenial(
                $req,
                new AuthorizationException($permission),
                __CLASS__ . '::' . $action
            );
        }
        return null;
    }

    /**
     * Validate inputs, log failures.
     */
    private function validateInput(Request $req, array $rules, string $action): ?RedirectResponse
    {
        $v = Validator::make($req->all(), $rules);
        if ($v->fails()) {
            $msg = $v->errors()->first();
            Log::warning(__CLASS__ . "::{$action} — validation failed", [
                'errors' => $msg,
                'input'  => $req->all(),
            ]);
            return redirect()->back()->with('error', $msg);
        }
        return null;
    }
}
