<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Exports\TransactionExport;
use App\Models\{BankAccount, ProductServiceCategory, Transaction};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, View as ViewFacade};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\HasCrudConstants;
class TransactionController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::TST . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $func, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            $user = $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::MNG_TRT, self::ROUTE_INDEX)) !== true) return $c;

            Log::debug($action . ' start', ['user' => $user?->id, 'input' => $request->all()]);

            $t0 = microtime(true);
            $filter = ['account' => __('All'), 'category' => __('All')];

            $accountList = BankAccount::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->pluck('holder_name', 'id');
            $accountList->prepend(__('Stripe / Paypal'), 'stripe-paypal');
            $accountList->prepend(__('Select Account'), '');

            $categoryList = ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->whereIn('type', [1, 2])
                ->pluck('name', 'name');
            $categoryList->prepend('Invoice', 'Invoice');
            $categoryList->prepend('Bill', 'Bill');
            $categoryList->prepend(__('Select Category'), '');
            $this->logExecutionTime($t0, $action, 'loadFilters');

            $startMonth = $request->startMonth ? strtotime($request->startMonth) : strtotime(date('Y-m'));
            $endMonth   = $request->endMonth ? strtotime($request->endMonth) : strtotime('-5 month', $startMonth);
            $from = min($startMonth, $endMonth);
            $to   = max($startMonth, $endMonth);

            $t1 = microtime(true);
            $transactions = Transaction::orderByDesc('id');
            $accountSums = Transaction::leftJoin(
                DatabaseConstants::TABLE_BANK_ACC,
                ViewsConstants::TST . '.account',
                '=',
                DatabaseConstants::TABLE_BANK_ACC . '.id'
            )
                ->select(ViewsConstants::TST . '.account')
                ->selectRaw('sum(amount) as total')
                ->groupBy(ViewsConstants::TST . '.account');

            $currentDate = $from;
            while ($currentDate <= $to) {
                $m = date('m', $currentDate);
                $y = date('Y', $currentDate);
                $transactions->orWhere(fn($q) => $q->whereMonth('date', $m)->whereYear('date', $y)->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()));
                $accountSums->orWhere(fn($q) => $q->whereMonth('date', $m)->whereYear('date', $y)->where(ViewsConstants::TST . '.' . DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()));
                $currentDate = strtotime('+1 month', $currentDate);
            }

            $filter['startDateRange'] = date('M-Y', $from);
            $filter['endDateRange']   = date('M-Y', $to);

            if ($request->account) {
                $transactions->where('account', $request->account);
                $accountSums->where(ViewsConstants::TST . '.account', $request->account === 'stripe-paypal' ? 0 : $request->account);
                $acc = $request->account === 'stripe-paypal'
                    ? __('Stripe / Paypal')
                    : optional(BankAccount::find($request->account))->holder_name . ' - ' . optional(BankAccount::find($request->account))->bank_name;
                $filter['account'] = $acc;
            }

            if ($request->category) {
                $transactions->where('category', $request->category);
                $accountSums->where('category', $request->category);
                $filter['category'] = $request->category;
            }

            $transactions = $transactions->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
            $accountSums  = $accountSums->get();
            $this->logExecutionTime($t1, $action, 'loadTransactions');

            Log::info($action . ' success', ['txCount' => $transactions->count(), 'accountCount' => $accountSums->count()]);

            $view = ViewsConstants::TST . '.' . $func;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::ROUTE_INDEX));

            return ViewFacade::make($view, [
                'transactions' => $transactions,
                'account'      => $accountList,
                'category'     => $categoryList,
                'filter'       => $filter,
                'accounts'     => $accountSums,
            ]);
        }, ['user' => $request->user()?->id ?? null, 'input' => $request->all()]);
    }

    public function export(Request $request): BinaryFileResponse|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::MNG_TRT, self::ROUTE_INDEX)) !== true) return $c;

            $fileName = 'transaction_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            Log::info($action . ' exporting', ['file' => $fileName]);

            return Excel::download(new TransactionExport(), $fileName);
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }
}
