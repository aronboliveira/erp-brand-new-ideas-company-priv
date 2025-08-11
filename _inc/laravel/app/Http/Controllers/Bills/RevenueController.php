<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{
    BankAccount,
    Customer,
    ProductServiceCategory,
    Revenue,
    Transaction,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log};

class RevenueController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::RVN . '.index';

    public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_RVN, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $filters = $request->only(['customer', 'account', 'category', 'payment', 'date']);
        Log::info("$action called", [UsersConstants::COL_USER_ID => $user?->id, 'filters' => $filters]);
        try {
            $creatorId = $user?->creatorId();
            $revenues = DB::transaction(function () use ($filters, $creatorId, $action) {
                $q = Revenue::where(DatabaseConstants::TABLE_CREATOR, $creatorId);
                if ($filters['customer'] ?? null)  $q->where('customer_id', $filters['customer']);
                if ($filters['account']  ?? null)  $q->where('account_id', $filters['account']);
                if ($filters['category'] ?? null)  $q->where('category_id', $filters['category']);
                if ($filters['payment']  ?? null)  $q->where('payment_method', $filters['payment']);
                if ($filters['date']     ?? null) {
                    $range = strpos($filters['date'], 'to') !== false
                        ? explode(' to ', $filters['date'])
                        : [$filters['date'], $filters['date']];
                    $q->whereBetween('date', $range);
                }
                $res = $q->get();
                Log::debug("$action DB query complete", ['count' => $res->count()]);
                return $res;
            });
            $customerList = Customer::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id')
                ->prepend('Select Customer', '');
            $accountList = BankAccount::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('holder_name', 'id')
                ->prepend('Select Account', '');
            $categoryList = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('type', 'income')
                ->pluck('name', 'id')
                ->prepend('Select Category', '');

            Log::info("$action returning view", [UsersConstants::COL_USER_ID => $user?->id, 'resultCount' => $revenues->count()]);
            return view(ViewsConstants::RVN . '.index', [
                'revenues' => $revenues,
                'customer' => $customerList,
                'account'  => $accountList,
                'category' => $categoryList,
            ]);
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function create(Request $request): \Illuminate\View\View|\Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        Log::info("$action start", [UsersConstants::COL_USER_ID => Auth::id()]);
        if (($user = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create revenue', self::REDIRECT_INDEX)) !== true) {
            Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
            return $redirect;
        }
        try {
            $creatorId = $user?->creatorId();
            $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id')->prepend('--', 0);
            $categories = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('type', 'income')
                ->pluck('name', 'id');
            $accounts  = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name, id")
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id');
            return view(ViewsConstants::RVN . '.create', compact('customers', 'categories', 'accounts'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        Log::info("$action start", ['input' => $request->only(
            'date',
            'amount',
            'account_id',
            'category_id'
        )]);
        if (($user = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create revenue', self::REDIRECT_INDEX)) !== true) {
            Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
            return $redirect;
        }
        $request->validate([
            'date'        => 'required|date',
            'amount'      => 'required|numeric',
            'account_id'  => 'required|exists:bank_accounts,id',
            'category_id' => 'required|exists:product_service_categories,id',
        ]);
        try {
            $creatorId = $user?->creatorId();
            $data     = $request->only([
                'date',
                'amount',
                'account_id',
                'customer_id',
                'category_id',
                'reference',
                'description'
            ]);
            if ($file = $request->file('add_receipt')) {
                $size  = $file->getSize();
                $limit = Utility::updateStorageLimit($creatorId, $size);
                if ($limit === 1) {
                    $name  = time() . '_' . $file->getClientOriginalName();
                    $upload = Utility::uploadFile(
                        $request,
                        'add_receipt',
                        $name,
                        'uploads/revenue',
                        []
                    );
                    if ($upload['flag'] === 0) {
                        Log::warning("$action upload failed", ['msg' => $upload['msg']]);
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $data['add_receipt'] = $name;
                }
            }
            $data['payment_method'] = 0;
            $data[DatabaseConstants::TABLE_CREATOR]    = $creatorId;
            $revenue               = Revenue::create($data);
            Log::info("$action created", ['revenueId' => $revenue->id]);

            $category = ProductServiceCategory::find($revenue->category_id);
            $payload = array_merge(
                $revenue->toArray(),
                [
                    'payment_id' => $revenue->id,
                    'type'       => 'Revenue',
                    'category'   => $category->name ?? '',
                    UsersConstants::COL_USER_ID    => $revenue->customer_id,
                    'user_type'  => 'Customer',
                    'account'    => $revenue->account_id
                ]
            );
            Transaction::addTransaction((object) $payload);
            if ($revenue->customer_id) {
                Utility::userBalance(
                    'customer',
                    $revenue->customer_id,
                    $revenue->amount,
                    'credit'
                );
            }
            Utility::bankAccountBalance(
                $revenue->account_id,
                $revenue->amount,
                'credit'
            );

            $settings = Utility::settings($creatorId);
            $notify = [
                'revenue_amount' => $user?->priceFormat($revenue->amount),
                'customer_name'  => Customer::find($revenue->customer_id)?->name ?? '-',
                'user_name'      => $user?->name,
                'revenue_date'   => $revenue->date,
            ];
            foreach ([
                'revenue_notification'           => 'slack',
                'telegram_revenue_notification'  => 'telegram',
                'twilio_revenue_notification'    => 'twilio'
            ] as $key => $method) {
                if (($settings[$key] ?? 0) == 1) {
                    Utility::{"send_{$method}_msg"}('new_revenue', $notify);
                }
            }
            if ($webhook = Utility::webhookSetting('New Revenue')) {
                $ok = Utility::webhookCall(
                    $webhook['url'],
                    json_encode($revenue),
                    $webhook['method']
                );
                if (!$ok) {
                    Log::warning("$action webhook failed");
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }
            Log::info("$action completed");
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Revenue successfully created.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function show(Request $request, Revenue $revenue): \Illuminate\Http\RedirectResponse
    {
        $action = __METHOD__;
        Log::info("$action start", ['revenueId' => $revenue->id]);
        if (($user = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $user;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_RVN, self::REDIRECT_INDEX)) !== true) {
            Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
            return $redirect;
        }
        if ($revenue->created_by !== $user?->creatorId()) {
            Log::warning("$action ownership denied", [
                UsersConstants::COL_USER_ID    => $user?->id,
                'revenueId' => $revenue->id
            ]);
            return defaultPermissionDenial($request, null, $action);
        }
        return redirect()->route(self::REDIRECT_INDEX);
    }

    public function edit(Request $request, Revenue $revenue): \Illuminate\View\View|\Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        Log::info("$action start", ['revenueId' => $revenue->id]);
        if (($user = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'edit revenue', self::REDIRECT_INDEX)) !== true) {
            Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
            return $redirect;
        }
        if ($revenue->created_by !== $user?->creatorId()) {
            Log::warning("$action ownership denied", [
                UsersConstants::COL_USER_ID    => $user?->id,
                'revenueId' => $revenue->id
            ]);
            return defaultPermissionDenial($request, null, $action);
        }
        try {
            $creatorId = $user?->creatorId();
            $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id')->prepend('--', 0);
            $categories = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('type', 'income')
                ->pluck('name', 'id');
            $accounts  = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name, id")
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id');
            return view(
                ViewsConstants::RVN . '.edit',
                compact('customers', 'categories', 'accounts', 'revenue')
            );
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function update(Request $request, Revenue $revenue): \Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        Log::info("$action start", ['revenueId' => $revenue->id]);
        if (($user = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'edit revenue', self::REDIRECT_INDEX)) !== true) {
            Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
            return $redirect;
        }
        if ($revenue->created_by !== $user?->creatorId()) {
            Log::warning("$action ownership denied", [
                UsersConstants::COL_USER_ID    => $user?->id,
                'revenueId' => $revenue->id
            ]);
            return defaultPermissionDenial($request, null, $action);
        }
        $request->validate([
            'date'        => 'required|date',
            'amount'      => 'required|numeric',
            'account_id'  => 'required|exists:bank_accounts,id',
            'category_id' => 'required|exists:product_service_categories,id',
        ]);
        try {
            // reverse old balances
            Utility::userBalance(
                'customer',
                $revenue->customer_id,
                $revenue->amount,
                'debit'
            );
            Utility::bankAccountBalance(
                $revenue->account_id,
                $revenue->amount,
                'debit'
            );

            $revenue->update($request->only(
                'date',
                'amount',
                'account_id',
                'customer_id',
                'category_id',
                'reference',
                'description'
            ));
            Log::info("$action updated", ['revenueId' => $revenue->id]);
            // reapply transaction
            Transaction::editTransaction($revenue);
            Utility::userBalance(
                'customer',
                $revenue->customer_id,
                $revenue->amount,
                'credit'
            );
            Utility::bankAccountBalance(
                $revenue->account_id,
                $revenue->amount,
                'credit'
            );
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Revenue updated successfully.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, Revenue $revenue): \Illuminate\Http\RedirectResponse|null
    {
        $action = __METHOD__;
        Log::info("$action start", ['revenueId' => $revenue->id]);
        if (($user = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'delete revenue', self::REDIRECT_INDEX)) !== true) {
            Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
            return $redirect;
        }
        if ($revenue->created_by !== $user?->creatorId()) {
            Log::warning("$action ownership denied", [
                UsersConstants::COL_USER_ID    => $user?->id,
                'revenueId' => $revenue->id
            ]);
            return defaultPermissionDenial($request, null, $action);
        }
        try {
            DB::transaction(function () use ($action, $revenue) {
                if ($path = $revenue->add_receipt) {
                    Utility::changeStorageLimit(
                        $revenue->created_by,
                        "/uploads/revenue/{$path}"
                    );
                }
                $revenue->delete();
                Transaction::destroyTransaction(
                    $revenue->id,
                    'Revenue',
                    'Customer'
                );
                Utility::userBalance(
                    'customer',
                    $revenue->customer_id,
                    $revenue->amount,
                    'debit'
                );
                Utility::bankAccountBalance(
                    $revenue->account_id,
                    $revenue->amount,
                    'debit'
                );
                Log::info("$action committed", ['revenueId' => $revenue->id]);
            });
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Revenue successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
