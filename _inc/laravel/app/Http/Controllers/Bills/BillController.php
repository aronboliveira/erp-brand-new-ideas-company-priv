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
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Crypt, DB, Log, Mail, Redirect, Storage};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

final class BillController extends Controller
{

    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $req): mixed
    {
        if ($r = self::_deny($req, PermissionsConstants::MNG_BIL)) return $r;
        Log::info('Listing bills', [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'filters' => $req->all()
        ]);
        try {
            $uid   = $req->user()->creatorId();
            $vendor = self::_vendors()->prepend('Select Vendor', '');
            $status = Bill::$statuses;
            $bills = Bill::where('type', 'Bill')
                ->where(DatabaseConstants::TABLE_CREATOR, $uid)
                ->when($req->vendor, fn ($q) => $q->where('vendor_id', $req->vendor))
                ->when($req->bill_date, function ($q) use ($req) {
                    $parts = array_map('trim', explode('to', $req->bill_date));
                    $range = count($parts) > 1 ? $parts : [
                        $req->bill_date,
                        $req->bill_date
                    ];
                    $q->whereBetween('bill_date', $range);
                })
                ->when($req->status, fn ($q) => $q->where('status', $req->status))
                ->with('category')
                ->get();
            Log::info('Loaded bills', ['count' => $bills->count()]);
            return view(
                ViewsConstants::BIL . '.index',
                compact(DatabaseConstants::TABLE_BILLS, 'vendor', 'status')
            );
        } catch (\Throwable $e) {
            Log::error('BillController::index error', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(int|string $vendorId): mixed
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        $req = request();
        if ($r = self::_deny($req, 'create bill')) return $r;
        Log::info('Showing bill create form', [
            UsersConstants::COL_USER_ID => $user?->id,
            'vendor_id' => $vendorId
        ]);
        $cf = CustomField::where([
            [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()],
            ['module', 'bill']
        ])->get();
        $data = [
            DatabaseConstants::TABLE_VENDORS         => self::_vendors()->prepend('Select Vendor', ''),
            'billNumber'      => $user?->billNumberFormat(self::_billNumber()),
            'productServices' => ProductService::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->pluck('name', 'id')->prepend('Select Item', ''),
            'category'        => self::_categories()->prepend('Select Category', ''),
            'customFields'    => $cf,
            'vendorId'        => $vendorId,
            'chartAccounts'   => self::_chartAccounts()->prepend('Select Account', ''),
        ];
        return view(ViewsConstants::BIL . '.create', $data);
    }

    public function store(Request $req): mixed
    {
        if ($r = $this->_authorize($req, 'create bill')) return $r;
        Log::info('Storing new bill', [UsersConstants::COL_USER_ID => $req->user()->id, 'input' => $req->all()]);
        $req->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date'
        ]);
        try {
            $user = self::_checkLogin();
            DB::beginTransaction();
            $bill = Bill::create([
                'bill_id'      => self::_billNumber(),
                'vendor_id'    => $req->vendor_id,
                'bill_date'    => $req->bill_date,
                'due_date'     => $req->due_date,
                'status'       => 0,
                'type'         => 'Bill',
                'user_type'    => 'vendor',
                'category_id'  => $req->category_id ?: 0,
                'order_number' => $req->order_number ?: 0,
                DatabaseConstants::TABLE_CREATOR   => $user?->creatorId(),
            ]);
            Log::info('Bill created', ['bill_id' => $bill->id]);
            CustomField::saveData($bill, $req->customField);
            $total = 0;
            foreach ($req->items ?? [] as $item) {
                if (!empty($item['item'])) {
                    $bp = BillProduct::create([
                        'bill_id'    => $bill->id,
                        'product_id' => $item['item'],
                        'quantity'   => $item['quantity'],
                        'tax'        => $item['tax'],
                        'discount'   => $item['discount'],
                        'price'      => $item['price'],
                        'description' => $item['description'],
                    ]);
                    Utility::totalQuantity('plus', $bp->quantity, $bp->product_id);
                    Utility::addProductStock(
                        $bp->product_id,
                        $bp->quantity,
                        'bill',
                        "{$bp->quantity} purchased in bill " . $user?->billNumberFormat($bill->bill_id),
                        $bill->id
                    );
                    $total += $bp->quantity * $bp->price;
                    Log::info('BillProduct created', ['bp_id' => $bp->id]);
                }
                if (!empty($item['chart_account_id'])) {
                    $ba = BillAccount::create([
                        'chart_account_id' => $item['chart_account_id'],
                        'price'           => $item['amount'],
                        'description'     => $item['description'],
                        'type'            => 'Bill',
                        'ref_id'          => $bill->id,
                    ]);
                    $total += $ba->price;
                    Log::info('BillAccount created', ['ba_id' => $ba->id]);
                }
            }
            if ($req->chart_account_id) {
                $cat = ProductServiceCategory::find($req->category_id);
                $bac = BillAccount::create([
                    'chart_account_id' => $cat->chart_account_id ?? 0,
                    'price' => $total,
                    'description' => $req->description,
                    'type' => 'Bill Category',
                    'ref_id' => $bill->id,
                ]);
                Log::info('Category BillAccount created', ['ba_id' => $bac->id]);
            }
            DB::commit();
            return Redirect::route(ViewsConstants::BIL . '.index')
                ->with('success', __('Bill successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BillController::store failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(string $encrypted): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $req = request();
        if ($r = self::_deny($req, 'show bill'))
            return $r;
        try {
            $id  = Crypt::decrypt($encrypted);
            $bill = Bill::with('debitNote')->findOrFail($id);
            if (!self::_isOwner($bill)) {
                Log::warning('Unauthorized show bill', [
                    UsersConstants::COL_USER_ID => auth()->id(),
                    'bill_id' => $id,
                ]);
                throw new \Illuminate\Auth\Access\AuthorizationException;
            }
            Log::info('Showing bill detail', [
                UsersConstants::COL_USER_ID => auth()->id(),
                'bill_id' => $id,
            ]);
            $vendor   = $bill->vendor;
            $payment  = BillPayment::where('bill_id', $id)->first();
            $items    = $this->_mergeItemsAccounts($bill);
            $cf       = CustomField::where([
                [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()],
                ['module',     'bill'],
            ])->get();
            return view(ViewsConstants::BIL . '.view', compact(
                'bill',
                'vendor',
                'items',
                'payment',
                'cf'
            ));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            Log::error('BillController::show failed', [
                'error'       => $e->getMessage(),
                'encrypted'   => $encrypted,
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(string $encrypted): View|RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $req = request();
        if ($r = self::_deny($req, 'edit bill'))
            return $r;
        try {
            $id      = Crypt::decrypt($encrypted);
            $bill    = Bill::findOrFail($id);
            if (!self::_isOwner($bill)) {
                Log::warning('Unauthorized edit bill', [
                    UsersConstants::COL_USER_ID => auth()->id(),
                    'bill_id' => $id,
                ]);
                throw new \Illuminate\Auth\Access\AuthorizationException;
            }
            Log::info('Editing bill', [
                UsersConstants::COL_USER_ID => auth()->id(),
                'bill_id' => $id,
            ]);
            $data = [
                DatabaseConstants::TABLE_VENDORS         => self::_vendors(),
                'productServices' => ProductService::where(
                    DatabaseConstants::TABLE_CREATOR,
                    $user?->creatorId()
                )->pluck('name', 'id'),
                'bill'            => $bill,
                'billNumber'      => $user
                    ->billNumberFormat($bill->bill_id),
                'category'        => self::_categories()->prepend(
                    'Select Category',
                    ''
                ),
                'customFields'    => CustomField::where([
                    [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()],
                    ['module',     'bill'],
                ])->get(),
                'chartAccounts'   => self::_chartAccounts()->prepend(
                    'Select Account',
                    ''
                ),
                'items'           => $this->_mergeItemsAccounts($bill),
            ];
            return view(ViewsConstants::BIL . '.edit', $data);
        } catch (\Throwable $e) {
            Log::error('BillController::edit failed', [
                'error'     => $e->getMessage(),
                'encrypted' => $encrypted,
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function update(Request $req, Bill $bill): RedirectResponse|JsonResponse
    {
        if ($r = self::_deny($req, 'edit bill')) {
            return $r;
        }
        if (!self::_isOwner($bill)) {
            Log::warning('Unauthorized update bill', [
                UsersConstants::COL_USER_ID => auth()->id(),
                'bill_id' => $bill->id,
            ]);
            return defaultPermissionDenial(
                $req,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        Log::info('Updating bill', [
            UsersConstants::COL_USER_ID => auth()->id(),
            'bill_id' => $bill->id,
            'input'   => $req->all(),
        ]);
        $req->validate([
            'vendor_id'   => 'required|exists:vendors,id',
            'bill_date'   => 'required|date',
            'due_date'    => 'required|date',
        ]);
        try {
            DB::beginTransaction();
            $bill->update([
                'vendor_id'    => $req->vendor_id,
                'bill_date'    => $req->bill_date,
                'due_date'     => $req->due_date,
                'order_number' => $req->order_number,
                'category_id'  => $req->category_id,
            ]);
            CustomField::saveData($bill, $req->customField);
            $total = 0;
            foreach ($req->items as $row) {
                $bp = BillProduct::updateOrCreate(
                    ['id' => $row['id'] ?? null, 'bill_id' => $bill->id],
                    [
                        'product_id'  => $row['items']     ?? null,
                        'quantity'    => $row['quantity']  ?? 0,
                        'tax'         => $row['tax']       ?? 0,
                        'discount'    => $row['discount']  ?? 0,
                        'price'       => $row['price']     ?? 0,
                        'description' => $row['description'] ?? '',
                    ]
                );
                Utility::totalQuantity(
                    $bp->wasRecentlyCreated ? 'plus' : 'minus',
                    $bp->quantity,
                    $bp->product_id
                );
                $total += $bp->quantity * $bp->price;
                Log::info('BillProduct saved', [
                    'bp_id'   => $bp->id,
                    'bill_id' => $bill->id,
                ]);
                if (!empty($row['chart_account_id'])) {
                    $ba = BillAccount::updateOrCreate(
                        ['id' => $row['id'] ?? null, 'type' => 'Bill', 'ref_id' => $bill->id],
                        [
                            'chart_account_id' => $row['chart_account_id'],
                            'price'           => $row['amount']    ?? 0,
                            'description'     => $row['description'] ?? '',
                        ]
                    );
                    $total += $ba->price;
                    Log::info('BillAccount saved', [
                        'ba_id'   => $ba->id,
                        'bill_id' => $bill->id,
                    ]);
                }
            }
            if ($req->chart_account_id) {
                $cat = ProductServiceCategory::find($req->category_id);
                $bac = BillAccount::updateOrCreate(
                    ['type' => 'Bill Category', 'ref_id' => $bill->id],
                    [
                        'chart_account_id' => $cat->chart_account_id ?? 0,
                        'price'           => $total,
                        'description'     => $req->description,
                    ]
                );
                Log::info('Category BillAccount saved', [
                    'ba_id' => $bac->id, 'bill_id' => $bill->id
                ]);
            }
            DB::commit();
            Log::info('Bill updated successfully', [
                'bill_id' => $bill->id,
            ]);
            return redirect()->route(ViewsConstants::BIL . '.index')
                ->with('success', __('Bill successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BillController::update failed', [
                'error'   => $e->getMessage(),
                'bill_id' => $bill->id,
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Bill $bill): RedirectResponse|JsonResponse
    {
        $req = request();
        if ($r = self::_deny($req, 'delete bill')) {
            return $r;
        }
        if (!self::_isOwner($bill)) {
            Log::warning('Unauthorized destroy bill', [
                UsersConstants::COL_USER_ID => auth()->id(),
                'bill_id' => $bill->id,
            ]);
            return defaultPermissionDenial(
                $req,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        Log::info('Destroying bill', [
            UsersConstants::COL_USER_ID => auth()->id(),
            'bill_id' => $bill->id,
        ]);
        try {
            DB::beginTransaction();
            foreach ($bill->payments as $p) {
                Utility::bankAccountBalance($p->account_id, $p->amount, 'credit');
                Transaction::where('payment_id', $p->id)->delete();
                $p->delete();
                Log::info('Deleted BillPayment', [
                    'payment_id' => $p->id, 'bill_id' => $bill->id
                ]);
            }
            if ($bill->vendor_id && $bill->status) {
                Utility::updateUserBalance(
                    'vendor',
                    $bill->vendor_id,
                    $bill->getDue(),
                    'credit'
                );
                Log::info('Updated vendor balance on destroy', [
                    'vendor_id' => $bill->vendor_id,
                    'amount' => $bill->getDue()
                ]);
            }
            BillProduct::where('bill_id', $bill->id)->delete();
            BillAccount::where('ref_id', $bill->id)->delete();
            DebitNote::where('bill', $bill->id)->delete();
            $bill->delete();
            DB::commit();
            Log::info('Bill deleted', [
                UsersConstants::COL_USER_ID => auth()->id(),
                'bill_id' => $bill->id
            ]);
            return redirect()->route(ViewsConstants::BIL . '.index')
                ->with('success', __('Bill successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BillController::destroy failed', [
                'error'   => $e->getMessage(),
                'bill_id' => $bill->id,
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function product(Request $req): JsonResponse
    {
        try {
            $prod  = ProductService::findOrFail($req->product_id);
            $unit  = $prod->unit->name ?? '';
            $rate  = $prod->taxRate($prod->tax_id);
            $taxes = $prod->tax($prod->tax_id);
            $amount = $prod->purchase_price;
            Log::info('Fetched product detail', [
                'product_id' => $prod->id
            ]);
            return response()->json([
                'product' => $prod,
                'unit'   => $unit,
                'taxRate' => $rate,
                'taxes'  => $taxes,
                'totalAmount' => $amount
            ]);
        } catch (\Throwable $e) {
            Log::error('BillController::product failed', [
                'error' => $e->getMessage(),
                'product_id' => $req->product_id
            ]);
            return response()->json([]);
        }
    }

    public function productDestroy(Request $req): RedirectResponse|JsonResponse
    {
        if ($r = self::_deny($req, 'delete bill product')) {
            return $r;
        }
        Log::info('Deleting bill product', [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'product_id' => $req->id
        ]);
        try {
            DB::beginTransaction();
            $bp  = BillProduct::findOrFail($req->id);
            $bill = Bill::findOrFail($bp->bill_id);
            Utility::updateUserBalance(
                'vendor',
                $bill->vendor_id,
                $req->amount,
                'credit'
            );
            $bp->delete();
            DB::commit();
            Log::info('BillProduct deleted', [
                'bp_id' => $req->id, 'bill_id' => $bill->id
            ]);
            return redirect()->back()
                ->with('success', __('Bill product successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BillController::productDestroy failed', [
                'error' => $e->getMessage(),
                'id' => $req->id
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function sent(string $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $req = request();
        if ($r = self::_deny($req, 'send bill')) {
            return $r;
        }
        Log::info('Sending bill email', [
            UsersConstants::COL_USER_ID => auth()->id(),
            'bill_id' => $id
        ]);
        try {
            $bill  = Bill::findOrFail($id);
            $bill->update(['send_date' => now(), 'status' => 1]);
            $vendor = Vendor::findOrFail($bill->vendor_id);
            $bill->name = $vendor->name;
            $bill->bill = $user?->billNumberFormat($bill->bill_id);
            $bill->url = route(ViewsConstants::BIL . '.pdf', Crypt::encrypt($bill->id));
            Utility::updateUserBalance(
                'vendor',
                $vendor->id,
                $bill->getTotal(),
                'debit'
            );
            $resp = Utility::sendEmailTemplate(
                'vendor_bill_sent',
                [$vendor->id => $vendor->email],
                [
                    'vendor_bill_name'   => $bill->name,
                    'vendor_bill_number' => $bill->bill,
                    'vendor_bill_url'    => $bill->url
                ]
            );
            Log::info('Bill sent', [
                'bill_id' => $bill->id,
                'success' => $resp['is_success']
            ]);
            return redirect()->back()
                ->with(
                    'success',
                    __('Bill successfully sent.')
                        . (!$resp['is_success'] && $resp['error']
                            ? '<br><span class="text-danger">'
                            . $resp['error'] . '</span>' : ''
                        )
                );
        } catch (\Throwable $e) {
            Log::error('BillController::sent failed', [
                'error' => $e->getMessage(), 'bill_id' => $id
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function resent(string $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $req = request();
        if ($r = self::_deny($req, 'send bill')) {
            return $r;
        }
        Log::info('Resending bill email', [
            UsersConstants::COL_USER_ID => auth()->id(),
            'bill_id' => $id
        ]);
        try {
            $settings = Utility::settings();
            if (($settings['bill_resent'] ?? 0) != 1) {
                Log::warning('Bill resent disabled', ['bill_id' => $id]);
                return redirect()->back()
                    ->with('error', __('Resend disabled.'));
            }
            $bill  = Bill::findOrFail($id);
            $vendor = Vendor::findOrFail($bill->vendor_id);
            $bill->name = $vendor->name;
            $bill->bill = $user?->billNumberFormat($bill->bill_id);
            $bill->url = route(ViewsConstants::BIL . '.pdf', Crypt::encrypt($bill->id));
            $resp = Utility::sendEmailTemplate(
                'bill_resent',
                [$vendor->id => $vendor->email],
                [
                    'vendor_name'   => $vendor->name,
                    'vendor_email'  => $vendor->email,
                    'bill_name'     => $bill->name,
                    'bill_number'   => $bill->bill,
                    'bill_url'      => $bill->url
                ]
            );
            Log::info('Bill resent', [
                'bill_id' => $bill->id,
                'success' => $resp['is_success']
            ]);
            return redirect()->back()
                ->with(
                    'success',
                    __('Bill successfully sent.')
                        . (!$resp['is_success'] && $resp['error']
                            ? '<br><span class="text-danger">'
                            . $resp['error'] . '</span>' : ''
                        )
                );
        } catch (\Throwable $e) {
            Log::error('BillController::resent failed', [
                'error' => $e->getMessage(), 'bill_id' => $id
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function billNumber(): int|string
    {
        Log::info('Generating next bill number', [UsersConstants::COL_USER_ID => auth()->id()]);
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
            Log::warning('billNumber called without authentication');
            return $userOrRedirect;
        }
        $user = $userOrRedirect;
        $last = Bill::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->latest('bill_id')
            ->value('bill_id');
        $next = $last
            ? (is_numeric($last) ? $last + 1 : $last)
            : 1; /*! ALERT UUID-based IDs will bypass +1 logic */
        Log::info('Next bill number determined', [
            UsersConstants::COL_USER_ID => $user?->id,
            'next'    => $next,
        ]);
        return $next;
    }

    public function payment(Request $req, string|int $billId): View|RedirectResponse|JsonResponse
    {
        if ($r = self::_deny($req, 'create payment bill')) {
            Log::warning('Permission denied to view payment form', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        Log::info('Preparing payment form', [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'bill_id' => $billId,
        ]);
        try {
            $bill = Bill::findOrFail($billId);
            $uid = $req->user()->creatorId();
            $data = [
                'bill'       => $bill,
                DatabaseConstants::TABLE_VENDORS    => self::_vendors()->prepend('Select Vendor', ''),
                'categories' => ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck('name', 'id'),
                'accounts'   => BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name", 'id')
                    ->where(DatabaseConstants::TABLE_CREATOR, $uid)
                    ->pluck('name', 'id'),
            ];
            return view(ViewsConstants::BIL . '.payment', $data);
        } catch (\Throwable $e) {
            Log::error('BillController::payment failed', [
                'error'   => $e->getMessage(),
                'bill_id' => $billId,
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function createPayment(Request $req, string|int $billId): RedirectResponse|JsonResponse
    {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
            Log::warning('Unauthenticated createPayment call');
            return $userOrRedirect;
        }
        $user = $userOrRedirect;
        if ($r = self::_deny($req, 'create payment bill')) {
            Log::warning('Permission denied to create payment', [UsersConstants::COL_USER_ID => $user?->id]);
            return $r;
        }
        Log::info('Creating payment', [
            UsersConstants::COL_USER_ID => $user?->id,
            'bill_id' => $billId,
            'input'   => $req->only(['date', 'amount', 'account_id']),
        ]);
        $req->validate([
            'date'       => 'required|date',
            'amount'     => 'required|numeric',
            'account_id' => 'required|numeric',
        ]);
        try {
            DB::transaction(function () use ($req, $billId, &$result, $user) {
                $bp = BillPayment::create([
                    'bill_id'        => $billId,
                    'date'           => $req->date,
                    'amount'         => $req->amount,
                    'account_id'     => $req->account_id,
                    'payment_method' => 0,
                    'reference'      => $req->reference,
                    'description'    => $req->description,
                ]);
                Log::info('BillPayment created', ['payment_id' => $bp->id]);

                if ($req->file('add_receipt')) {
                    $size = $req->file('add_receipt')->getSize();
                    if (Utility::updateStorageLimit($user?->creatorId(), $size) != 1) {
                        Log::error('Storage limit exceeded for receipt upload', [UsersConstants::COL_USER_ID => $user?->id]);
                        throw new \RuntimeException('storage');
                    }
                    $fname = time() . '_' . $req->file('add_receipt')->getClientOriginalName();
                    $path = Utility::uploadFile($req, 'add_receipt', $fname, 'uploads/payment', []);
                    if ($path['flag'] == 0) {
                        Log::error('Receipt upload failed', ['message' => $path['msg']]);
                        throw new \RuntimeException($path['msg']);
                    }
                    $bp->add_receipt = $fname;
                    $bp->save();
                    Log::info('Receipt uploaded', ['payment_id' => $bp->id, 'filename' => $fname]);
                }

                $bill = Bill::findOrFail($billId);
                $bill->status   = $bill->getDue() <= 0 ? 4 : 3;
                $bill->send_date = $bill->send_date ?? now();
                $bill->save();
                Log::info('Bill status updated after payment', [
                    'bill_id' => $bill->id,
                    'status'  => $bill->status,
                ]);

                $bp->fill([
                    UsersConstants::COL_USER_ID    => $bill->vendor_id,
                    'user_type'  => 'Vendor',
                    'type'       => 'Partial',
                    DatabaseConstants::TABLE_CREATOR => auth()->id(),
                    'payment_id' => $bp->id,
                    'category'   => 'Bill',
                    'account'    => $req->account_id,
                ])->save();
                Transaction::addTransaction($bp);
                Utility::updateUserBalance('vendor', $bill->vendor_id, $req->amount, 'credit');
                Utility::bankAccountBalance($req->account_id, $req->amount, 'debit');

                $settings = Utility::settings();
                if (($settings['new_bill_payment'] ?? 0) == 1) {
                    $vendor = Vendor::findOrFail($bill->vendor_id);
                    $payload = [
                        'vendor_name'  => $vendor->name,
                        'vendor_email' => $vendor->email,
                        'payment_amount' => $user?->priceFormat($req->amount),
                        'payment_bill'   => 'bill ' . $user?->billNumberFormat($bill->bill_id),
                        'payment_date'   => $user?->dateFormat($req->date),
                        'payment_method' => '-',
                        'company_name'   => $vendor->name,
                    ];
                    $result = Utility::sendEmailTemplate('new_bill_payment', [
                        $vendor->id => $vendor->email
                    ], $payload);
                    Log::info('Payment notification email sent', [
                        'bill_id' => $bill->id,
                        'success' => $result['is_success'] ?? false
                    ]);
                }
            });
            return back()->with('success', 'Payment successfully added.'
                . (($result['is_success'] ?? true)
                    ? '' : '<br><span class="text-danger">' . $result['error'] . '</span>'));
        } catch (\Throwable $e) {
            Log::error('BillController::createPayment failed', [
                'error'   => $e->getMessage(),
                'bill_id' => $billId,
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function paymentDestroy(Request $req, string|int $billId, string|int $paymentId): RedirectResponse|JsonResponse
    {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
            Log::warning('Unauthenticated paymentDestroy call');
            return $userOrRedirect;
        }
        $user = $userOrRedirect;
        if ($r = self::_deny($req, 'delete payment bill')) {
            Log::warning('Permission denied to delete payment', [UsersConstants::COL_USER_ID => $user?->id]);
            return $r;
        }
        Log::info('Deleting payment', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'payment_id'  => $paymentId,
            'bill_id'     => $billId,
        ]);
        try {
            DB::transaction(function () use ($billId, $paymentId, $user) {
                $payment = BillPayment::findOrFail($paymentId);
                BillPayment::destroy($paymentId);
                Log::info('BillPayment record removed', ['payment_id' => $paymentId]);
                $bill = Bill::findOrFail($billId);
                $bill->status = ($bill->getDue() > 0 && $bill->getTotal() != $bill->getDue()) ? 3 : 2;
                $bill->save();
                Log::info('Bill status updated after payment deletion', [
                    'bill_id' => $bill->id,
                    'status'  => $bill->status,
                ]);
                Utility::updateUserBalance('vendor', $bill->vendor_id, $payment->amount, 'debit');
                Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');
                if ($payment->add_receipt) {
                    Utility::changeStorageLimit(
                        $user?->creatorId(),
                        'uploads/payment/' . $payment->add_receipt
                    );
                    Log::info('Receipt file removed from storage', ['filename' => $payment->add_receipt]);
                }
                Transaction::destroyTransaction($paymentId, 'Partial', 'Vendor');
            });
            return back()->with('success', 'Payment successfully deleted.');
        } catch (\Throwable $e) {
            Log::error('BillController::paymentDestroy failed', [
                'error'      => $e->getMessage(),
                'bill_id'    => $billId,
                'payment_id' => $paymentId,
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function vendorBill(Request $req): View|RedirectResponse|JsonResponse
    {
        if ($r = self::_deny($req, 'manage vendor bill')) {
            Log::warning('Permission denied vendorBill', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        Log::info('Listing vendor bills', ['vendor_id' => $req->user()->vendor_id]);
        $status = Bill::$statuses;
        $bills = Bill::where([
            ['vendor_id', $req->user()->vendor_id],
            ['status', '!=', 0],
            [DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId()],
        ])
            ->when($req->vendor, fn ($q) => $q->where('id', $req->vendor))
            ->when($req->bill_date, function ($q) use ($req) {
                $range = array_map('trim', explode(' - ', $req->bill_date));
                $q->whereBetween('bill_date', $range);
            })
            ->when($req->status, fn ($q) => $q->where('status', $req->status))
            ->get();
        Log::info('Loaded vendor bills', ['count' => $bills->count()]);
        return view(ViewsConstants::BIL . '.index', compact(DatabaseConstants::TABLE_BILLS, 'status'));
    }

    public function vendorBillShow(string $enc): View|RedirectResponse
    {
        $req = request();
        if ($r = self::_deny($req, 'show bill')) {
            Log::warning('Permission denied vendorBillShow', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        try {
            $id  = Crypt::decrypt($enc);
            $bill = Bill::findOrFail($id);
            if (!self::_isOwner($bill)) {
                Log::warning('Unauthorized vendorBillShow', [
                    UsersConstants::COL_USER_ID => auth()->id(), 'bill_id' => $id
                ]);
                throw new \Illuminate\Auth\Access\AuthorizationException;
            }
            Log::info('Showing vendor bill', ['bill_id' => $id]);
            return view(ViewsConstants::BIL . '.view', [
                'bill'   => $bill,
                'vendor' => $bill->vendor,
                'items'  => $bill->items,
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . 'failed', [
                'error'     => $e->getMessage(),
                'encrypted' => $enc,
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function vendor(Request $req): View
    {
        Log::info('Fetching vendor detail', ['id' => $req->id]);
        return view(ViewsConstants::BIL . '.vendor_detail', [
            'vendor' => Vendor::findOrFail($req->id)
        ]);
    }

    public function vendorBillSend(string|int $billId): View
    {
        Log::info('Displaying vendor bill send modal', ['bill_id' => $billId]);
        return view('vendor.bill_send', compact('billId'));
    }


    public function vendorBillSendMail(Request $req, string|int $billId): RedirectResponse
    {
        // ensure user is logged in
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
            Log::warning('vendorBillSendMail: unauthenticated access');
            return $userOrRedirect;
        }
        $user = $userOrRedirect;
        // validate email
        $req->validate(['email' => 'required|email']);
        Log::info('vendorBillSendMail: validation passed', [
            UsersConstants::COL_USER_ID => $user?->id,
            'bill_id' => $billId,
            'to'      => $req->email,
        ]);
        // load bill and vendor
        try {
            $bill = Bill::with('vendor')->findOrFail($billId);
        } catch (\Throwable $e) {
            Log::error('vendorBillSendMail: bill not found', [
                'bill_id' => $billId,
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', __('Bill not found.'));
        }
        $vendor = $bill->vendor;
        $bill->name = $vendor[UsersConstants::COL_NM] ?? '';
        $bill->bill = $user?->billNumberFormat($bill->bill_id);
        $bill->url = route(ViewsConstants::BIL . '.pdf', Crypt::encrypt($bill->id));
        try {
            Mail::to($req->email)
                ->queue(new VendorBillMail($bill));
            Log::info('vendorBillSendMail: invoice email queued', [
                'bill_id' => $bill->id,
                'to'      => $req->email,
            ]);
            return back()->with('success', __('Bill successfully sent.'));
        } catch (\Throwable $e) {
            Log::error('vendorBillSendMail: email dispatch failed', [
                'bill_id' => $bill->id,
                'to'      => $req->email,
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', __('E-Mail has not been sent due to SMTP configuration'));
        }
    }

    public function shippingDisplay(Request $req, string|int $id): RedirectResponse
    {
        try {
            $bill = Bill::findOrFail($id);
            $bill->shipping_display = $req->boolean('is_display');
            $bill->save();
            Log::info('Toggled shipping display', [
                'bill_id' => $id,
                'display' => $bill->shipping_display
            ]);
            return back()->with('success', __('Shipping address status successfully changed.'));
        } catch (\Throwable $e) {
            Log::error('BillController::shippingDisplay failed', [
                'error'   => $e->getMessage(),
                'bill_id' => $id,
            ]);
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function duplicate(string|int $billId): RedirectResponse|JsonResponse
    {
        if ($r = self::_deny(request(), 'duplicate bill')) return $r;
        $bill = Bill::findOrFail($billId);
        $copy = $bill->replicate(['bill_id', 'status', 'send_date']);
        $copy->bill_id = $this->billNumber();
        $copy->bill_date = now()->toDateString();
        $copy->status = 0;
        $copy->save();
        $bill->items()->each(fn ($p) => $copy->items()->create($p->only(
            'product_id',
            'quantity',
            'tax',
            'discount',
            'price'
        )));
        return back()->with('success', 'Bill duplicated successfully.');
    }

    public const PV_BIL = 'previewBill';
    public function previewBill(string $template, string $color): View
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $settings = Utility::settings();
        $vendor = (object)[
            'email' => '<Email>', 'shipping_name' => '<Vendor Name>',
            'shipping_country' => '<Country>', 'shipping_state' => '<State>',
            'shipping_city' => '<City>', 'shipping_phone' => '<Vendor Phone Number>',
            'shipping_zip' => '<Zip>', 'shipping_address' => '<Address>',
            'billing_name' => '<Vendor Name>', 'billing_country' => '<Country>',
            'billing_state' => '<State>', 'billing_city' => '<City>',
            'billing_phone' => '<Vendor Phone Number>', 'billing_zip' => '<Zip>',
            'billing_address' => '<Address>',
        ];
        $items = collect(range(1, 3))->map(function ($i) {
            return (object)[
                'name' => "Item $i", 'quantity' => 1, 'tax' => 5, 'discount' => 50,
                'price' => 100, 'unit' => 1, 'itemTax' => [['name' => 'Tax', 'rate' => '10 %', 'price' => '$10', 'tax_price' => 10]],
            ];
        });
        $bill = new Bill([
            'bill_id' => 1, 'issue_date' => now(), 'due_date' => now(),
            'itemData' => $items, 'totalTaxPrice' => 60, 'totalQuantity' => 3,
            'totalRate' => 300, 'totalDiscount' => 10, 'taxesData' => [],
            DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
        ]);
        $img = Utility::getLogo('bill_logo', SettingsConstants::CPN_LG_DK);
        $fontColor = Utility::getFontColor("#$color");
        return view(ViewsConstants::BIL_TMP . "$template", [
            'bill' => $bill, 'preview' => 1, 'color' => "#$color", 'img' => $img,
            'settings' => $settings, 'vendor' => $vendor, 'font_color' => $fontColor,
            'customFields' => [],
        ]);
    }

    public function bill(string $enc): View|RedirectResponse
    {
        try {
            $id = Crypt::decrypt($enc);
        } catch (\Throwable $e) {
            return back()->with('error', 'Bill Not Found.');
        }
        $bill = Bill::with('items.product')->findOrFail($id);
        if (!self::_isOwner($bill))
            return defaultPermissionDenial(request(), new \Exception('owner'), __CLASS__ . '::' . __FUNCTION__);
        $settings = Utility::settingsById($bill[DatabaseConstants::TABLE_CREATOR]);
        [$items, $taxesData, $totTax, $totQty, $totRate, $totDisc] = Utility::billItemStats($bill, $settings);
        $bill->fill([
            'itemData' => $items, 'taxesData' => $taxesData, 'totalTaxPrice' => $totTax,
            'totalQuantity' => $totQty, 'totalRate' => $totRate, 'totalDiscount' => $totDisc,
            'customField' => CustomField::getData($bill, 'bill'),
        ]);
        $vendor = $bill->vendor;
        $img = Utility::getLogo(
            'bill_logo',
            SettingsConstants::CPN_LG_DK,
            $bill[DatabaseConstants::TABLE_CREATOR]
        );
        $color = '#' . $settings['bill_color'];
        return view(ViewsConstants::BIL_TMP . "{$settings[BillsConstants::COL_BIL_TMP]}", [
            'bill' => $bill, 'color' => $color, 'settings' => $settings, 'vendor' => $vendor,
            'img' => $img, 'font_color' => Utility::getFontColor($color),
            'customFields' => CustomField::where(
                DatabaseConstants::TABLE_CREATOR,
                $bill[DatabaseConstants::TABLE_CREATOR]
            )->where('module', 'bill')->get(),
        ]);
    }

    public const SV_BIL_TMP = 'saveBillTemplateSettings';
    public function saveBillTemplateSettings(Request $req): RedirectResponse
    {
        $data = $req->except('_token');
        $data['bill_color'] = $data['bill_color'] ?? 'ffffff';
        if ($req->file('bill_logo')) {
            $upload = Utility::uploadFile($req, 'bill_logo', $req->user()->id
                . '_bill_logo.png', 'bill_logo/', ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]);
            if ($upload['flag'] == 0) return back()->with('error', $upload['msg']);
            $data['bill_logo'] = $req->user()->id . '_bill_logo.png';
        }
        $creatorCol = DatabaseConstants::TABLE_CREATOR;
        foreach ($data as $k => $v)
            DB::insert(
                'INSERT INTO settings (`value`,`name`,`' . $creatorCol . '`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                [$v, $k, $req->user()->creatorId()]
            );
        return back()->with('success', 'Bill setting updated successfully');
    }

    public function items(Request $req): JsonResponse
    {
        return response()->json(
            BillProduct::where([
                ['bill_id', $req->bill_id],
                ['product_id', $req->product_id],
            ])->first()
        );
    }

    public const IV_LK = 'invoiceLink';
    public function invoiceLink(string $enc): View|RedirectResponse
    {
        try {
            $id = Crypt::decrypt($enc);
        } catch (\Throwable) {
            return back()->with('error', 'Bill Not Found.');
        }
        $bill = Bill::with('items', 'vendor')->findOrFail($id);
        $bill->customField = CustomField::getData($bill, 'bill');
        return view(ViewsConstants::BIL . '.customer_bill', [
            'bill' => $bill,
            'vendor' => $bill->vendor,
            'iteams' => $bill->items,
            'customFields' => CustomField::where('module', 'bill')->get(),
            'billPayment' => BillPayment::where('bill_id', $bill->id)->get(),
            'user' => User::find($bill[DatabaseConstants::TABLE_CREATOR]),
        ]);
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new BillExport(), 'bill_' . now()
            ->format('Y-m-d_H-i-s') . '.xlsx');
    }

    private function _authorize(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        Log::info('Authorizing permission', [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'permission' => $perm
        ]);
        return $req->user()->can($perm)
            ? null
            : defaultPermissionDenial(
                $req,
                new \Illuminate\Auth\Access\AuthorizationException($perm),
                __CLASS__ . '::' . __FUNCTION__
            );
    }

    private static function _deny(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        Log::info('Checking permission', [
            UsersConstants::COL_USER_ID => $req->user()->id ?? null,
            'permission' => $perm
        ]);
        return $req->user()->can($perm)
            ? null
            : defaultPermissionDenial(
                $req,
                new \Illuminate\Auth\Access\AuthorizationException($perm),
                __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
            );
    }

    private static function _isOwner(object $model): bool
    {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
            Log::warning('Unauthenticated owner check');
            throw new \Illuminate\Auth\Access\AuthorizationException;
        }
        $user = $userOrRedirect;
        $owner = $model[DatabaseConstants::TABLE_CREATOR] === $user?->creatorId();
        Log::info('Owner check', [
            UsersConstants::COL_USER_ID => $user?->id,
            'model_id' => $model->id ?? null,
            'is_owner' => $owner
        ]);
        return $owner;
    }

    private static function _vendors(): \Illuminate\Support\Collection
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        return Vendor::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->pluck(UsersConstants::COL_NM, 'id');
    }

    private static function _categories(): \Illuminate\Support\Collection
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        return ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->whereNotIn('type', ['product & service', 'income'])
            ->pluck('name', 'id');
    }

    private static function _chartAccounts(): \Illuminate\Support\Collection
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        return ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
            ->where(DatabaseConstants::TABLE_CREATOR, $uid)
            ->pluck('code_name', 'id');
    }

    private static function _billNumber(): int
    {
        $user = self::_checkLogin();
        $uid = $user?->creatorId();
        $last = Bill::where(DatabaseConstants::TABLE_CREATOR, $uid)->latest()->first();
        $next = $last ? $last->bill_id + 1 : 1;
        Log::info('Next bill number', [UsersConstants::COL_USER_ID => $user?->id, 'next' => $next]);
        return $next;
    }
}
