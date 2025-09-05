<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Exports\TransactionExport;
use App\Models\{BankAccount, ProductServiceCategory, Transaction};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::TST . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        try {
            if ($c = self::guard($request, PermissionsConstants::MNG_TRT, self::ROUTE_INDEX)) return $c;
            $user       = $request->user();
            Log::info(__METHOD__ . ' start', ['user' => $user?->id, 'input' => $request->all()]);

            $filter     = ['account' => __('All'), 'category' => __('All')];
            $accountList = BankAccount::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->get()->pluck('holder_name', 'id');
            $accountList->prepend(__('Stripe / Paypal'), 'stripe-paypal');
            $accountList->prepend(__('Select Account'), '');
            $categoryList = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->whereIn('type', [1, 2])->get()->pluck('name', 'name');
            $categoryList->prepend('Invoice', 'Invoice');
            $categoryList->prepend('Bill', 'Bill');
            $categoryList->prepend(__('Select Category'), '');
            $transactions = Transaction::orderByDesc('id');
            $accountSums = Transaction::leftJoin(DatabaseConstants::TABLE_BANK_ACC, ViewsConstants::TST . '.account', '=', DatabaseConstants::TABLE_BANK_ACC . '.id')
                ->select(ViewsConstants::TST . '.account')
                ->selectRaw('sum(amount) as total')
                ->groupBy(ViewsConstants::TST . '.account');

            $startMonth = $request->startMonth
                ? strtotime($request->startMonth)
                : strtotime(date('Y-m'));
            $endMonth   = $request->endMonth
                ? strtotime($request->endMonth)
                : strtotime('-5 month');
            $currentDate = $startMonth;

            while ($currentDate <= $endMonth) {
                $m = date('m', $currentDate);
                $y = date('Y', $currentDate);
                $transactions->orWhere(fn($q) => $q->whereMonth('date', $m)
                    ->whereYear('date', $y)
                    ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId()));
                $accountSums->orWhere(fn($q) => $q->whereMonth('date', $m)
                    ->whereYear('date', $y)
                    ->where(ViewsConstants::TST . '.' . DatabaseConstants::TABLE_CREATOR, $user?->creatorId()));
                $currentDate = strtotime('+1 month', $currentDate);
            }

            $filter['startDateRange'] = date('M-Y', $startMonth);
            $filter['endDateRange']  = date('M-Y', $endMonth);

            if ($request->account) {
                $transactions->where('account', $request->account);
                $accountSums->where(
                    ViewsConstants::TST . '.account',
                    $request->account === 'stripe-paypal' ? 0 : $request->account
                );
                $acc = $request->account === 'stripe-paypal'
                    ? __('Stripe / Paypal')
                    : optional(BankAccount::find($request->account))->holder_name
                    . ' - ' . optional(BankAccount::find($request->account))->bank_name;
                $filter['account'] = $acc;
            }

            if ($request->category) {
                $transactions->where('category', $request->category);
                $accountSums->where('category', $request->category);
                $filter['category'] = $request->category;
            }

            $transactions = $transactions
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->get();
            $accountSums = $accountSums->get();

            Log::info(__METHOD__ . ' success', [
                'txCount'      => $transactions->count(),
                'accountCount' => $accountSums->count(),
            ]);

            return view(ViewsConstants::TST . '.' . __FUNCTION__, [
                'transactions' => $transactions,
                'account'      => $accountList,
                'category'     => $categoryList,
                'filter'       => $filter,
                'accounts'     => $accountSums,
            ]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function export(Request $request): BinaryFileResponse|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        try {
            if ($c = self::guard($request, PermissionsConstants::MNG_TRT, self::ROUTE_INDEX)) return $c;
            $fileName = 'transaction_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            Log::info(__METHOD__ . ' exporting', ['file' => $fileName]);
            return Excel::download(new TransactionExport(), $fileName);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' export failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }
}
