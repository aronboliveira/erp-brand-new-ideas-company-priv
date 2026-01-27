<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants as DC,
    PermissionsConstants,
    UsersConstants as UC,
    ViewsConstants as VW,
};
use App\Exports\{
    AccountStatementExport,
    BalanceSheetExport,
    LeaveReportExport,
    PayrollExport,
    ProductStockExport,
    ProfitLossExport,
    ReceivableExport,
    SalesReportExport,
    TrialBalanceExport
};
use function App\Http\Controllers\defaultUndefinedException;
use App\Models\{
    BankAccount,
    Bill,
    BillProduct,
    Branch,
    ChartOfAccount,
    ChartOfAccountSubType,
    ChartOfAccountType,
    ClientDeal,
    CreditNote,
    Customer,
    Deal,
    DebitNote,
    Department,
    Employee,
    EmployeeAttendance,
    Invoice,
    InvoiceProduct,
    Lead,
    Leave,
    LeaveType,
    Payment,
    Payslip,
    Pipeline,
    Pos,
    ProductServiceCategory,
    Purchase,
    Revenue,
    Source,
    StockReport,
    Tax,
    User,
    UserDeal,
    Utility,
    Vendor,
    warehouse,
    WarehouseProduct
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Carbon\{
    Carbon,
    CarbonPeriod
};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Redirect,
    View as ViewFacade,
    Route
};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\{
    BinaryFileResponse,
    StreamedResponse
};

final class ReportController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INCOME_SUMMARY    = VW::RPT . '.income_summary';
    private const ROUTE_EXPENSE_SUMMARY   = VW::RPT . '.expense_summary';
    private const ROUTE_INCOME_VS_EXPENSE = VW::RPT . '.income_vs_expense_summary';
    private const ROUTE_TAX_SUMMARY      = VW::RPT . '.tax_summary';
    private const ROUTE_INVOICE_REPORT  = VW::RPT . '.invoice';
    private const ROUTE_BILL_REPORT     = VW::RPT . '.bill';
    private const ROUTE_STATEMENT_REPORT = VW::RPT . '.statement_report';
    private const ROUTE_BALANCE_SHEET   = VW::RPT . '.balance_sheet';
    private const ROUTE_LEDGER_SUMMARY  = VW::RPT . '.ledger_summary';
    private const ROUTE_TRIAL_BALANCE   = VW::RPT . '.trial_balance';
    private const ROUTE_LEAVE            = VW::RPT . '.leave';
    private const ROUTE_EMPLOYEE_LEAVE   = VW::RPT . '.employee_leave';
    private const ROUTE_MONTHLY_ATTENDANCE = VW::RPT . '.monthly_attendance';
    private const ROUTE_PAYROLL          = VW::RPT . '.payroll';
    private const ROUTE_PAY_DEPT         = VW::RPT . '.get_payroll_department';
    private const ROUTE_PAY_EMP          = VW::RPT . '.get_payroll_employee';
    private const ROUTE_EXPORT_CSV       = VW::RPT . '.export_csv';
    private const ROUTE_PRODUCT_STOCK    = VW::RPT . '.stock_report';
    private const ROUTE_EXPORT_ACCOUNT  = VW::RPT . '.export';
    private const ROUTE_EXPORT_STOCK    = VW::RPT . '.stock_export';
    private const ROUTE_EXPORT_PAYROLL  = VW::RPT . '.payroll_report_export';
    private const ROUTE_EXPORT_LEAVE    = VW::RPT . '.leave_report_export';
    private const ROUTE_GET_DEPT        = VW::RPT . '.get_department';
    private const ROUTE_GET_EMP         = VW::RPT . '.get_employee';
    private const ROUTE_LEAD_REPORT     = VW::RPT . '.lead';
    private const ROUTE_DEAL_REPORT     = VW::RPT . '.deal';
    private const ROUTE_WAREHOUSE_REPORT = VW::RPT . '.warehouse';
    private const ROUTE_PURCHASE_DAILY  = VW::RPT . '.purchase_daily';
    private const ROUTE_PURCHASE_MONTHLY = VW::RPT . '.purchase_monthly';
    private const ROUTE_POS_DAILY       = VW::RPT . '.pos_daily';
    private const ROUTE_POS_MONTHLY     = VW::RPT . '.pos_monthly';
    private const ROUTE_POS_VS_PURCHASE = VW::RPT . '.pos_vs_purchase';
    private static ?\Illuminate\Support\Collection $dealData = null;

    public const INC_SM = 'incomeSummary';
    public function incomeSummary(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::INC_RPT, self::ROUTE_INCOME_SUMMARY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            $year = $request->year ?? date('Y');
            Log::info("[$action] started", ['user_id' => $user?->id, 'year' => $year]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildIncomeSummaryView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info("[$action] committed", ['user_id' => $user?->id]);
                $viewName = null;
                if (is_object($view)) {
                    if (method_exists($view, 'name')) $viewName = $view->name();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['user_id' => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_INCOME_SUMMARY));
            }
        }, ['req' => $request]);
    }

    public const EXP_SM = 'expenseSummary';
    public function expenseSummary(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::EXP_RPT, self::ROUTE_EXPENSE_SUMMARY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            $year = $request->year ?? date('Y');
            Log::info(get_class($this) . "::{$action} started", [UC::COL_USER_ID => $user?->id, 'year' => $year]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildExpenseSummaryView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . "::{$action} committed", [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_EXPENSE_SUMMARY));
            }
        }, ['req' => $request]);
    }

    public const INC_EXP_SM = 'incomeVsExpenseSummary';
    public function incomeVsExpenseSummary(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::IE_RPT, self::ROUTE_INCOME_VS_EXPENSE)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            $year = $request->year ?? date('Y');
            Log::info(get_class($this) . "::{$action} started", [UC::COL_USER_ID => $user?->id, 'year' => $year]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildIncomeVsExpenseSummaryView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . "::{$action} committed", [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_INCOME_VS_EXPENSE));
            }
        }, ['req' => $request]);
    }

    public const TX_SM = 'taxSummary';
    public function taxSummary(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::TAX_RPT, self::ROUTE_TAX_SUMMARY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . "::{$action} started", [UC::COL_USER_ID => $user?->id, 'year' => $request->year ?? date('Y')]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildTaxSummaryView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . "::{$action} committed", [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_TAX_SUMMARY));
            }
        }, ['req' => $request]);
    }

    public function yearMonth(): array
    {
        return [
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        ];
    }

    public function yearList(): array
    {
        $end  = date('Y');
        $start = date('Y', strtotime('-5 year'));
        $years = [];
        foreach (range($end, $start) as $y) {
            $years[$y] = $y;
        }
        return $years;
    }

    public const INV_SM = 'invoiceSummary';
    public function invoiceSummary(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::INV_RPT, self::ROUTE_INVOICE_REPORT)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . "::{$action} started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildInvoiceSummaryView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . "::{$action} committed", [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage(), 'request' => $request->all(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_INVOICE_REPORT));
            }
        }, ['req' => $request]);
    }

    public const BL_SM = 'billSummary';
    public function billSummary(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::BIL_RPT, self::ROUTE_BILL_REPORT)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::billSummary started', [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildBillSummaryView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::billSummary committed', [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::billSummary failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_BILL_REPORT));
            }
        }, ['req' => $request]);
    }

    public const ACC_STT = 'accountStatement';
    public function accountStatement(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, 'statement report', self::ROUTE_STATEMENT_REPORT)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::warning(get_class($this) . '::accountStatement started', [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildAccountStatementView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::accountStatement committed', [UC::COL_USER_ID => $user?->id, 'view' => $view]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::accountStatement failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_STATEMENT_REPORT));
            }
        }, ['req' => $request]);
    }

    public const BL_SHT = 'balanceSheet';
    public function balanceSheet(Request $request, string $view = '')
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $view, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::BIL_RPT, self::ROUTE_BALANCE_SHEET)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::balanceSheet started', [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $result = DB::transaction(fn() => $this->_buildBalanceSheetView($request, $view, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::balanceSheet committed', [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($result)) $viewName = $result;
                elseif (is_object($result)) {
                    if (method_exists($result, 'getName')) $viewName = $result->getName();
                    elseif (property_exists($result, 'name')) $viewName = $result->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $result;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::balanceSheet failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_BALANCE_SHEET));
            }
        }, ['req' => $request, 'view' => $view]);
    }

    public const LDG_SM = 'ledgerSummary';
    public function ledgerSummary(Request $request, string $account = '')
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $account, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::LDG_RPT, self::ROUTE_LEDGER_SUMMARY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::ledgerSummary started', [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildLedgerSummaryView($request, $account, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::ledgerSummary committed', [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::ledgerSummary failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_LEDGER_SUMMARY));
            }
        }, ['req' => $request, 'account' => $account]);
    }

    public const TRL_BL_SUM = 'trialBalanceSummary';
    public function trialBalanceSummary(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::TRL_RPT, self::ROUTE_TRIAL_BALANCE)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . "::{$action} started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildTrialBalanceSummaryView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . "::{$action} committed", [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_TRIAL_BALANCE));
            }
        }, ['req' => $request]);
    }

    public function leave(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_RPT, self::ROUTE_LEAVE)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::leave started', [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildLeaveView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::leave committed', [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::leave failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_LEAVE));
            }
        }, ['req' => $request]);
    }

    public const EMP_LV = 'employeeLeave';
    public function employeeLeave(Request $request, string|int $employee_id, string $status, string $type, string $month, int $year): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $employee_id, $status, $type, $month, $year, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_RPT, self::ROUTE_EMPLOYEE_LEAVE)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::employeeLeave started', [UC::COL_USER_ID => $user?->id, UC::COL_EMP_ID => $employee_id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildEmployeeLeaveView($employee_id, $status, $type, $month, $year, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::employeeLeave committed', [UC::COL_USER_ID => $user?->id, UC::COL_EMP_ID => $employee_id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::employeeLeave failed', [UC::COL_USER_ID => $user?->id, UC::COL_EMP_ID => $employee_id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_EMPLOYEE_LEAVE));
            }
        }, ['req' => $request, 'employee_id' => $employee_id, 'status' => $status, 'type' => $type, 'month' => $month, 'year' => $year]);
    }

    public const MNT_ATD = 'monthlyAttendance';
    public function monthlyAttendance(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_RPT, self::ROUTE_MONTHLY_ATTENDANCE)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::monthlyAttendance started', [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildMonthlyAttendanceView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::monthlyAttendance committed', [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::monthlyAttendance failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_MONTHLY_ATTENDANCE));
            }
        }, ['req' => $request]);
    }

    public function payroll(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_RPT, self::ROUTE_PAYROLL)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::payroll started', [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $view = DB::transaction(fn() => $this->_buildPayrollView($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info(get_class($this) . '::payroll committed', [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($view)) $viewName = $view;
                elseif (is_object($view)) {
                    if (method_exists($view, 'getName')) $viewName = $view->getName();
                    elseif (property_exists($view, 'name')) $viewName = $view->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::payroll failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_PAYROLL));
            }
        }, ['req' => $request]);
    }

    public const GET_PAY_RL_DEP = 'getPayrollDepartment';
    public function getPayrollDepartment(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            Log::info(get_class($this) . "::{$action}", [CompaniesConstants::COL_BRC_ID => $request[CompaniesConstants::COL_BRC_ID] ?? null]);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            try {
                $startFetch = microtime(true);
                $depts = Department::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->when(($request[CompaniesConstants::COL_BRC_ID] ?? 0) != 0, fn($q) => $q->where(CompaniesConstants::COL_BRC_ID, $request[CompaniesConstants::COL_BRC_ID]), fn($q) => $q)->pluck(CompaniesConstants::COL_DEP_NM, 'id')->toArray();
                $this->logExecutionTime($startFetch, "{$action} fetchDepartments", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return response()->json($depts);
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'branch_id' => $request[CompaniesConstants::COL_BRC_ID] ?? null, 'error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => __('An unexpected error occurred.')], 500);
            }
        }, ['req' => $request]);
    }

    public const GET_PAY_RL_EMP = 'getPayrollEmployee';
    public function getPayrollEmployee(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            Log::info(get_class($this) . "::{$action}", [CompaniesConstants::COL_DEP_ID => $request[CompaniesConstants::COL_DEP_ID] ?? null]);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            try {
                $startFetch = microtime(true);
                $depId = $request[CompaniesConstants::COL_DEP_ID] ?? null;
                $emps = Employee::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->when($depId, fn($q) => $q->where(CompaniesConstants::COL_DEP_ID, $depId), fn($q) => $q)->pluck(UC::COL_NM, 'id')->toArray();
                $this->logExecutionTime($startFetch, "{$action} fetchEmployees", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return response()->json($emps);
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'dept_id' => $request[CompaniesConstants::COL_DEP_ID] ?? null, 'error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => __('An unexpected error occurred.')], 500);
            }
        }, ['req' => $request]);
    }

    public const EXP_CSV = 'exportCsv';
    public function exportCsv(string $filter_month, int $branch, int $department): StreamedResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($filter_month, $branch, $department, $action, $method) {
            $startOverall = microtime(true);
            Log::info(get_class($this) . "::{$action}", ['filter_month' => $filter_month, 'branch' => $branch, 'department' => $department]);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            try {
                $startMeta = microtime(true);
                $branchName = Branch::find($branch)?->name ?? __('All');
                $departmentName = Department::find($department)?->name ?? __('All');
                $dt = strtotime($filter_month);
                $month = date('m', $dt);
                $year = date('Y', $dt);
                $curMonth = date('M-Y', $dt);
                $fileName = "{$branchName} " . __('Branch') . " {$curMonth} " . __('Attendance Report of') . " {$departmentName} " . __('Department') . ".csv";
                $this->logExecutionTime($startMeta, "{$action} prepareMeta", 'completed');
                $startDates = microtime(true);
                $numDays = date('t', mktime(0, 0, 0, $month, 1, $year));
                $dates = [];
                for ($i = 1; $i <= $numDays; $i++) $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $this->logExecutionTime($startDates, "{$action} buildDateRange", 'completed');
                $startEmp = microtime(true);
                $employees = Employee::select('id', 'name')->where(DC::COL_TABLE_CREATOR, $user?->creatorId())->when($branch, fn($q) => $q->where(CompaniesConstants::COL_BRC_ID, $branch), fn($q) => $q)->when($department, fn($q) => $q->where(CompaniesConstants::COL_DEP_ID, $department), fn($q) => $q)->get()->pluck('name', 'id')->toArray();
                $this->logExecutionTime($startEmp, "{$action} fetchEmployees", 'completed');
                Log::info("[$action] employees fetched", ['count' => count($employees)]);
                $startRows = microtime(true);
                $rows = [];
                foreach ($employees as $id => $name) {
                    $row = ['employee' => $name];
                    foreach ($dates as $d) {
                        $dateStr = "{$year}-{$month}-{$d}";
                        try {
                            $att = EmployeeAttendance::where(UC::COL_EMP_ID, $id)->where('date', $dateStr)->first();
                        } catch (\Throwable $e) {
                            Log::warning(get_class($this) . "::{$action} attendance lookup failed", [UC::COL_EMP_ID => $id, 'date' => $dateStr, 'error' => $e->getMessage()]);
                            $att = null;
                        }
                        $row[$d] = match (true) {
                            $att && $att->status === 'Present' => 'P',
                            $att && $att->status === 'Leave'   => 'A',
                            default                          => '-',
                        };
                    }
                    $rows[] = $row;
                }
                $this->logExecutionTime($startRows, "{$action} buildRows", 'completed');
                $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename={$fileName}", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
                $callback = function () use ($rows, $dates) {
                    $handle = fopen('php://output', 'w');
                    fputcsv($handle, array_merge(['employee'], $dates));
                    foreach ($rows as $row) fputcsv($handle, array_values($row));
                    fclose($handle);
                };
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return response()->stream($callback, 200, $headers);
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, "{$method}", route(self::ROUTE_MONTHLY_ATTENDANCE));
            }
        }, ['filter_month' => $filter_month, 'branch' => $branch, 'department' => $department]);
    }

    public const PRD_STK = 'productStock';
    public function productStock(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::STK_RPT, self::ROUTE_PRODUCT_STOCK)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . '::productStock', [UC::COL_USER_ID => $user?->id]);
            try {
                $startFetch = microtime(true);
                $stocks = StockReport::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($startFetch, "{$action} fetchStocks", 'completed');
                $viewName = VW::RPT . '.product_stock_report';
                if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                return view($viewName, compact('stocks'));
            } catch (\Throwable $e) {
                Log::error(get_class($this) . '::productStock failed', [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_PRODUCT_STOCK));
            }
        }, ['req' => $request]);
    }

    public function export(Request $request): RedirectResponse|BinaryFileResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, 'statement report', self::ROUTE_EXPORT_ACCOUNT)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . "::{$action} started", [UC::COL_USER_ID => $user->id]);
            try {
                $startExport = microtime(true);
                $fileName = 'account_statement_' . now()->format('Y-m-d_H-i-s');
                $resp = Excel::download(new AccountStatementExport(), "{$fileName}.xlsx");
                $this->logExecutionTime($startExport, "{$action} exportDownload", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_EXPORT_ACCOUNT));
            }
        }, ['req' => $request]);
    }

    public const STK_EXP = 'stockExport';
    public function stockExport(Request $request): RedirectResponse|BinaryFileResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::STK_RPT, self::ROUTE_EXPORT_STOCK)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info(get_class($this) . "::{$action} started", [UC::COL_USER_ID => $user->id]);
            try {
                $startExport = microtime(true);
                $fileName = 'product_stock_' . now()->format('Y-m-d_H-i-s');
                $resp = Excel::download(new ProductStockExport(), "{$fileName}.xlsx");
                $this->logExecutionTime($startExport, "{$action} exportDownload", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_EXPORT_STOCK));
            }
        }, ['req' => $request]);
    }

    public const PAY_RPT_EXP = 'payrollReportExport';
    public function payrollReportExport(Request $request): RedirectResponse|BinaryFileResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_RPT, self::ROUTE_EXPORT_PAYROLL)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::{$action} started", [UC::COL_USER_ID => $user->id]);
            try {
                $startExport = microtime(true);
                $fileName = 'payroll_' . now()->format('Y-m-d_H-i-s');
                $resp = Excel::download(new PayrollExport(), "{$fileName}.xlsx");
                $this->logExecutionTime($startExport, "{$action} exportDownload", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['user_id' => $user->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_EXPORT_PAYROLL));
            }
        }, ['req' => $request]);
    }

    public const LV_RPT_EXP = 'leaveReportExport';
    public function leaveReportExport(Request $request): RedirectResponse|BinaryFileResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_RPT, self::ROUTE_EXPORT_LEAVE)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::leaveReportExport started", [UC::COL_USER_ID => $user->id]);
            try {
                $startExport = microtime(true);
                $fileName = 'leave_' . now()->format('Y-m-d_H-i-s');
                $resp = Excel::download(new LeaveReportExport(), "{$fileName}.xlsx");
                $this->logExecutionTime($startExport, "{$action} exportDownload", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::leaveReportExport failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_EXPORT_LEAVE));
            }
        }, ['req' => $request]);
    }

    public const GET_DPT = 'getDepartment';
    public function getDepartment(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startVal = microtime(true);
            $request->validate([CompaniesConstants::COL_BRC_ID => 'required|integer']);
            $this->logExecutionTime($startVal, "{$action} validate", 'completed');
            Log::info(get_class($this) . "::{$action}", [CompaniesConstants::COL_BRC_ID => $request[CompaniesConstants::COL_BRC_ID] ?? null]);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            try {
                $startFetch = microtime(true);
                $branchId = $request[CompaniesConstants::COL_BRC_ID] ?? null;
                $branch = Branch::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->when($branchId !== 0, fn($q) => $q->where('id', $branchId), fn($q) => $q)->first();
                $depts = $branch ? $branch->departments()->pluck(CompaniesConstants::COL_BRC_NM, 'id')->toArray() : [];
                $this->logExecutionTime($startFetch, "{$action} fetchDepartments", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return response()->json($depts);
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, CompaniesConstants::COL_BRC_ID => $request[CompaniesConstants::COL_BRC_ID] ?? null, 'error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => __('An unexpected error occurred.')], 500);
            }
        }, ['req' => $request]);
    }

    public const GET_EMP = 'getEmployee';
    public function getEmployee(Request $request): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            $startVal = microtime(true);
            $request->validate([CompaniesConstants::COL_DEP_ID => 'integer']);
            $this->logExecutionTime($startVal, "{$action} validate", 'completed');
            Log::info(get_class($this) . "::{$action}", [CompaniesConstants::COL_DEP_ID => $request[CompaniesConstants::COL_DEP_ID] ?? null]);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            try {
                $startFetch = microtime(true);
                $depId = $request[CompaniesConstants::COL_DEP_ID] ?? null;
                $emps = Employee::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->when($depId, fn($q) => $q->where(CompaniesConstants::COL_DEP_ID, $depId), fn($q) => $q)->pluck('name', 'id')->toArray();
                $this->logExecutionTime($startFetch, "{$action} fetchEmployees", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return response()->json($emps);
            } catch (\Throwable $e) {
                Log::error(get_class($this) . "::{$action} failed", [UC::COL_USER_ID => $user?->id, 'dept_id' => $request[CompaniesConstants::COL_DEP_ID] ?? null, 'error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => __('An unexpected error occurred.')], 500);
            }
        }, ['req' => $request]);
    }

    public const LD_RPT = 'leadReport';
    public function leadReport(Request $request): RedirectResponse|View|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $startGuard = microtime(true);
            if (($r = self::guard($request, 'lead report', self::ROUTE_LEAD_REPORT)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::{$action} started", [UC::COL_USER_ID => $u->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_buildLeadReport($request, $u->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                // try to ensure the view exists when applicable
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return redirect()->back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_LEAD_REPORT));
            }
        }, ['req' => $request]);
    }

    public const DL_RPT = 'dealReport';
    public function dealReport(Request $request): RedirectResponse|View|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $startGuard = microtime(true);
            if (($r = self::guard($request, 'deal report', self::ROUTE_DEAL_REPORT)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::dealReport started", [UC::COL_USER_ID => $u->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_buildDealReport($request, $u->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return redirect()->back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::dealReport failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_DEAL_REPORT));
            }
        }, ['req' => $request]);
    }

    public function deals(): \Illuminate\Support\Collection|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($action, $method, $class) {
            $startOverall = microtime(true);
            if (self::$dealData !== null) {
                Log::info("{$class}::{$action} cache hit", ['count' => self::$dealData->count()]);
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return self::$dealData;
            }
            try {
                $startLogin = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startQuery = microtime(true);
                self::$dealData = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($startQuery, "{$action} fetchUsers", 'completed');
                Log::info("{$class}::{$action} loaded", [UC::COL_USER_ID => $user?->id, 'count' => self::$dealData->count()]);
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return self::$dealData;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['error' => $e->getMessage()]);
                $this->logExecutionTime($startOverall, "{$action} exception", 'exception');
                return collect();
            }
        }, ['cache' => is_null(self::$dealData) ? 'miss' : 'hit']);
    }

    public const WRH_RPT = 'warehouseReport';
    public function warehouseReport(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_POS, self::ROUTE_WAREHOUSE_REPORT)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::warehouseReport started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_renderWarehouse($user?->id));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                Log::info("{$class}::warehouseReport committed", [UC::COL_USER_ID => $user?->id]);
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::warehouseReport failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_WAREHOUSE_REPORT));
            }
        }, ['req' => $request]);
    }

    public const PRC_DLY_RPT = 'purchaseDailyReport';
    public function purchaseDailyReport(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_POS, self::ROUTE_PURCHASE_DAILY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::purchaseDailyReport started", [UC::COL_USER_ID => $u->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_buildPurchaseDaily($request, $u->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::purchaseDailyReport failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_PURCHASE_DAILY));
            }
        }, ['req' => $request]);
    }

    public const PRC_MLY_RPT = 'purchaseMonthlyReport';
    public function purchaseMonthlyReport(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_POS, self::ROUTE_PURCHASE_MONTHLY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::purchaseMonthlyReport started", [UC::COL_USER_ID => $u->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_buildPurchaseMonthly($request, $u->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::purchaseMonthlyReport failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_PURCHASE_MONTHLY));
            }
        }, ['req' => $request]);
    }

    public const POS_DLY_RPT = 'posDailyReport';
    public function posDailyReport(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_POS, self::ROUTE_POS_DAILY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::posDailyReport started", [UC::COL_USER_ID => $u->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_buildPosDaily($request, $u->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::posDailyReport failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_POS_DAILY));
            }
        }, ['req' => $request]);
    }

    public const POS_MLY_RPT = 'posMonthlyReport';
    public function posMonthlyReport(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_POS, self::ROUTE_POS_MONTHLY)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::posMonthlyReport started", [UC::COL_USER_ID => $u->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_buildPosMonthly($request, $u->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::posMonthlyReport failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_POS_MONTHLY));
            }
        }, ['req' => $request]);
    }

    public const POS_PRC_RPT = 'posVsPurchaseReport';
    public function posVsPurchaseReport(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $startGuard = microtime(true);
            if (($r = self::guard($request, PermissionsConstants::MNG_POS, self::ROUTE_POS_VS_PURCHASE)) !== true) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::posVsPurchaseReport started", [UC::COL_USER_ID => $u->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_buildPosVsPurchase($request, $u->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::posVsPurchaseReport failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_POS_VS_PURCHASE));
            }
        }, ['req' => $request]);
    }

    public const PRF_LS = 'profitLoss';
    public function profitLoss(Request $request, string $view = ''): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $view, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::IE_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::profitLoss started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_renderProfitLoss($request, $view, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::profitLoss failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.profit_loss'));
            }
        }, ['req' => $request, 'view' => $view]);
    }

    public const MLY_CSH_FLW = 'monthlyCashflow';
    public function monthlyCashflow(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::LP_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::{$action} started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_renderMonthlyCashflow($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['user_id' => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.monthly_cashflow'));
            }
        }, ['req' => $request]);
    }

    public const QLY_CSH_FLW = 'quarterlyCashflow';
    public function quarterlyCashflow(Request $request): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::LP_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::quarterlyCashflow started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_renderQuarterlyCashflow($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::quarterlyCashflow failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.quarterly_cashflow'));
            }
        }, ['req' => $request]);
    }

    public const TRL_BLC_EXP = 'trialBalanceExport';
    public function trialBalanceExport(Request $request): BinaryFileResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::TRL_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::{$action} started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_doTrialBalanceExport($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                // attempt view check only if a view-like payload is returned
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", ['user_id' => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.trial_balance_export'));
            }
        }, ['req' => $request]);
    }

    public const BLC_SHT_EXP = 'balanceSheetExport';
    public function balanceSheetExport(Request $request): BinaryFileResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::BLC_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::{$action} started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_doBalanceSheetExport($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.balance_sheet_export'));
            }
        }, ['req' => $request]);
    }

    public const TRL_BLC_PRT = 'trialBalancePrint';
    public function trialBalancePrint(Request $request, string $view = ''): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $view, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startPerm = microtime(true);
            if (!$user?->can(PermissionsConstants::TRL_RPT)) {
                Log::warning('[trialBalancePrint] Permission denied', [UC::COL_USER_ID => $user?->id]);
                $this->logExecutionTime($startPerm, "{$action} permissionCheck", 'completed');
                return Redirect::back()->with('error', __('Permission Denied.'));
            }
            $this->logExecutionTime($startPerm, "{$action} permissionCheck", 'completed');
            $startParams = microtime(true);
            $start = $request->start_date ?: now()->startOfYear()->toDateString();
            $end = $request->end_date ?: now()->addDay()->toDateString();
            Log::info('[trialBalancePrint] Generating report', [UC::COL_USER_ID => $user?->id, 'start' => $start, 'end' => $end, 'view' => $view]);
            $this->logExecutionTime($startParams, "{$action} prepareParams", 'completed');
            try {
                $startBuild = microtime(true);
                $totalAccounts = $this->buildTrialBalanceData($user?->creatorId(), $start, $end);
                $this->logExecutionTime($startBuild, "{$action} buildData", 'completed');
                $filter = ['startDateRange' => $start, 'endDateRange' => $end];
                $viewName = $view === 'horizontal' ? VW::RPT . '.trial_balance_receipt_horizontal' : VW::RPT . '.trial_balance_receipt';
                if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return view($viewName, compact('filter', 'totalAccounts'));
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.trial_balance_receipt'));
            }
        }, ['req' => $request, 'view' => $view]);
    }

    public const BLC_SHT_PRT = 'balanceSheetPrint';
    public function balanceSheetPrint(Request $request, string $view = ''): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $view, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::BLC_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::balanceSheetPrint started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_renderBalanceSheetPrint($request, $view, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::balanceSheetPrint failed", ['user_id' => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.balance_sheet_print'));
            }
        }, ['req' => $request, 'view' => $view]);
    }

    public const PRF_LS_EXP = 'profitLossExport';
    public function profitLossExport(Request $request): BinaryFileResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::IE_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::{$action} started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_doProfitLossExport($request, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::{$action} failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.profit_loss_export'));
            }
        }, ['req' => $request]);
    }

    public const PRF_LS_PRT = 'profitLossPrint';
    public function profitLossPrint(Request $request, string $view = ''): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $view, $action, $method, $class) {
            $startOverall = microtime(true);
            $startLogin = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
            $user = $userOrRedirect;
            $startGuard = microtime(true);
            if ($r = self::guard($request, PermissionsConstants::IE_RPT, $method)) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            Log::info("{$class}::profitLossPrint started", [UC::COL_USER_ID => $user?->id]);
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(fn() => $this->_renderProfitLossPrint($request, $view, $user?->creatorId()));
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $viewName = null;
                if (is_string($resp)) $viewName = $resp;
                elseif (is_object($resp)) {
                    if (method_exists($resp, 'getName')) $viewName = $resp->getName();
                    elseif (property_exists($resp, 'name')) $viewName = $resp->name;
                }
                if ($viewName && !ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("{$class}::profitLossPrint failed", [UC::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.profit_loss_print'));
            }
        }, ['req' => $request, 'view' => $view]);
    }

    public const SLS_RPT = 'salesReport';
    public function salesReport(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startAuth = microtime(true);
            if ($r = $this->authorizeReport($request, 'sales report')) return $r;
            $this->logExecutionTime($startAuth, "{$action} authorize", 'completed');
            Log::info('salesReport:start', $this->logContext());
            $startParse = microtime(true);
            [$start, $end] = $this->parseDateRange($request);
            $filter = compact('start', 'end');
            $this->logExecutionTime($startParse, "{$action} parseDateRange", 'completed');
            try {
                $startBuild = microtime(true);
                [$items, $customers] = $this->buildSalesData($start, $end);
                $this->logExecutionTime($startBuild, "{$action} buildSalesData", 'completed');
            } catch (\Throwable $e) {
                Log::error('salesReport:buildError', ['error' => $e->getMessage()] + $this->logContext());
                abort(500, 'Unable to build sales report.');
            }
            $viewName = VW::RPT . '.sales_report';
            if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
            $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
            return view($viewName, compact('filter', 'items', 'customers'));
        }, ['req' => $request]);
    }

    public const SLS_RPT_EXP = 'salesReportExport';
    public function salesReportExport(Request $request): BinaryFileResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startAuth = microtime(true);
            if ($r = $this->authorizeReport($request, 'sales report')) return $r;
            $this->logExecutionTime($startAuth, "{$action} authorize", 'completed');
            Log::info('salesReportExport:start', $this->logContext());
            $startParse = microtime(true);
            [$start, $end] = $this->parseDateRange($request);
            $mode = $request->report === '#item' ? 'Item' : 'Customer';
            $this->logExecutionTime($startParse, "{$action} parseDateRange", 'completed');
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(function () use ($start, $end, $mode) {
                    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                    $user = $userOrRedirect;
                    [$items, $customers] = $this->buildSalesData($start, $end);
                    $data = $mode === 'Item' ? $items : $customers;
                    $company = User::find($user?->creatorId())->name;
                    $filename = "SalesBy{$mode}_{$start}_{$end}.xlsx";
                    ob_end_clean();
                    Log::info('salesReportExport:download', $this->logContext() + compact('mode', 'filename'));
                    return Excel::download(new SalesReportExport($data, $start, $end, $company, $mode), $filename);
                });
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error('salesReportExport:failed', ['error' => $e->getMessage()] + $this->logContext());
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.sales_report_export'));
            }
        }, ['req' => $request]);
    }

    public const SLS_RPT_PRT = 'salesReportPrint';
    public function salesReportPrint(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startAuth = microtime(true);
            if ($r = $this->authorizeReport($request, 'sales report')) return $r;
            $this->logExecutionTime($startAuth, "{$action} authorize", 'completed');
            Log::info('salesReportPrint:start', $this->logContext());
            $startParse = microtime(true);
            [$start, $end] = $this->parseDateRange($request);
            $filter = compact('start', 'end');
            $mode = $request->report === '#item' ? 'Item' : 'Customer';
            $this->logExecutionTime($startParse, "{$action} parseDateRange", 'completed');
            try {
                $startBuild = microtime(true);
                [$items, $customers] = $this->buildSalesData($start, $end);
                $this->logExecutionTime($startBuild, "{$action} buildSalesData", 'completed');
            } catch (\Throwable $e) {
                Log::error('salesReportPrint:buildError', ['error' => $e->getMessage()] + $this->logContext());
                abort(500, 'Unable to build sales report for print.');
            }
            $viewName = VW::RPT . '.sales_report_receipt';
            if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
            $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
            return view($viewName, [
                'filter' => $filter,
                'invoiceItems' => $items,
                'invoiceCustomers' => $customers,
                'reportName' => $mode,
            ]);
        }, ['req' => $request]);
    }

    public const RCV_RPT = 'receivablesReport';
    public function receivablesReport(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startAuth = microtime(true);
            if ($r = $this->authorizeReport($request, 'receivable report')) return $r;
            $this->logExecutionTime($startAuth, "{$action} authorize", 'completed');
            Log::info('receivablesReport:start', $this->logContext());
            $startParse = microtime(true);
            [$start, $end] = $this->parseDateRange($request);
            $filter = compact('start', 'end');
            $this->logExecutionTime($startParse, "{$action} parseDateRange", 'completed');
            try {
                $startBuild = microtime(true);
                [$customers, $summaries, $details, $aging] = $this->buildReceivableData($start, $end);
                $this->logExecutionTime($startBuild, "{$action} buildReceivableData", 'completed');
            } catch (\Throwable $e) {
                Log::error('receivablesReport:buildError', ['error' => $e->getMessage()] + $this->logContext());
                abort(500, 'Unable to build receivables report.');
            }
            $viewName = VW::RPT . '.receivable_report';
            if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
            $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
            return view($viewName, compact('filter', 'customers', 'summaries', 'details', 'aging'));
        }, ['req' => $request]);
    }

    public const RCV_EXP = 'receivablesExport';
    public function receivablesExport(Request $request): BinaryFileResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startAuth = microtime(true);
            if ($r = $this->authorizeReport($request, 'receivable report')) return $r;
            $this->logExecutionTime($startAuth, "{$action} authorize", 'completed');
            Log::info('receivablesExport:start', $this->logContext());
            $startParse = microtime(true);
            [$start, $end] = $this->parseDateRange($request);
            $this->logExecutionTime($startParse, "{$action} parseDateRange", 'completed');
            try {
                $startTxn = microtime(true);
                $resp = DB::transaction(function () use ($start, $end) {
                    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                    $user = $userOrRedirect;
                    [$customers] = $this->buildReceivableData($start, $end);
                    $company = User::find($user?->creatorId())->name;
                    $filename = "Receivables_{$start}_{$end}.xlsx";
                    ob_end_clean();
                    Log::info('receivablesExport:download', $this->logContext() + compact('filename'));
                    return Excel::download(new ReceivableExport($customers, $start, $end, $company), $filename);
                });
                $this->logExecutionTime($startTxn, "{$action} transaction", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $resp;
            } catch (\Throwable $e) {
                Log::error('receivablesExport:failed', ['error' => $e->getMessage()] + $this->logContext());
                return defaultUndefinedException($request, $e, "{$method}", route(VW::RPT . '.receivable_report_export'));
            }
        }, ['req' => $request]);
    }

    public const RCV_PRT = 'receivablesPrint';
    public function receivablesPrint(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
            $startOverall = microtime(true);
            $startAuth = microtime(true);
            if ($r = $this->authorizeReport($request, 'receivable report')) return $r;
            $this->logExecutionTime($startAuth, "{$action} authorize", 'completed');
            Log::info('receivablesPrint:start', $this->logContext());
            $startParse = microtime(true);
            [$start, $end] = $this->parseDateRange($request);
            $filter = compact('start', 'end');
            $this->logExecutionTime($startParse, "{$action} parseDateRange", 'completed');
            try {
                $startBuild = microtime(true);
                [$customers, $summaries, $details, $aging] = $this->buildReceivableData($start, $end);
                $this->logExecutionTime($startBuild, "{$action} buildReceivableData", 'completed');
            } catch (\Throwable $e) {
                Log::error('receivablesPrint:buildError', ['error' => $e->getMessage()] + $this->logContext());
                abort(500, 'Unable to build receivables report for print.');
            }
            $viewName = VW::RPT . '.receivable_report_receipt';
            if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
            $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
            return view($viewName, compact('filter', 'customers', 'summaries', 'details', 'aging'));
        }, ['req' => $request]);
    }

    public const PAY_RPT = 'payablesReport';
    public function payablesReport(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $viewPath = VW::RPT . '.payable_report';
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $viewPath) {
            if ($r = $this->authorizeReport($request, 'payable report')) return $r;
            Log::info("[{$class}::{$action}] start", $this->logContext() + ['method' => $method, 'input_keys' => array_keys($request->all())]);
            [$start, $end] = $this->parseDateRange($request);
            $filter = compact('start', 'end');
            $buildStart = microtime(true);
            try {
                [$vendors, $summaries, $details] = $this->buildPayableData($start, $end);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] payablesReport:buildError", ['error' => $e->getMessage()] + $this->logContext());
                Log::debug("[{$class}::{$action}] debug buildError details", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method, 'action' => $action, 'view_path' => $viewPath, 'input_keys' => array_keys($request->all())]);
                abort(500, 'Unable to build payables report.');
            }
            $this->logExecutionTime($buildStart, $action, 'buildPayableData');
            $renderStart = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$class}::{$action}] missing view", ['view_path' => $viewPath] + $this->logContext());
                Log::debug("[{$class}::{$action}] debug missing view details", ['method' => $method, 'action' => $action, 'route' => Route::getCurrentRoute()?->getName()]);
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'compact_vars' => ['filter', 'vendors', 'summaries', 'details']] + $this->logContext());
            $this->logExecutionTime($renderStart, $action, 'renderPayableReport');
            return view($viewPath, compact('filter', 'vendors', 'summaries', 'details'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public const PAY_PRT = 'payablesPrint';
    public function payablesPrint(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $viewPath = VW::RPT . '.payable_report_receipt';
        return $this->measureProfile($action, function () use ($request, $action, $method, $class, $viewPath) {
            if ($r = $this->authorizeReport($request, 'payable report')) return $r;
            Log::info("[{$class}::{$action}] start", $this->logContext() + ['method' => $method, 'input_keys' => array_keys($request->all())]);
            [$start, $end] = $this->parseDateRange($request);
            $filter = compact('start', 'end');
            $buildStart = microtime(true);
            try {
                [$vendors, $summaries, $details] = $this->buildPayableData($start, $end);
            } catch (\Throwable $e) {
                Log::error("[{$class}::{$action}] payablesPrint:buildError", ['error' => $e->getMessage()] + $this->logContext());
                Log::debug("[{$class}::{$action}] debug buildError details", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method, 'action' => $action, 'view_path' => $viewPath, 'input_keys' => array_keys($request->all())]);
                abort(500, 'Unable to build payables report for print.');
            }
            $this->logExecutionTime($buildStart, $action, 'buildPayableData');
            $renderStart = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$class}::{$action}] missing view", ['view_path' => $viewPath] + $this->logContext());
                Log::debug("[{$class}::{$action}] debug missing view details", ['method' => $method, 'action' => $action, 'route' => Route::getCurrentRoute()?->getName()]);
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            Log::info("[{$class}::{$action}] rendering view", ['view_path' => $viewPath, 'compact_vars' => ['filter', 'vendors', 'summaries', 'details']] + $this->logContext());
            $this->logExecutionTime($renderStart, $action, 'renderPayableReportReceipt');
            return view($viewPath, compact('filter', 'vendors', 'summaries', 'details'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    private function parseDateRange(Request $r): array
    {
        $start = $r->start_date ?: now()->startOfYear()->toDateString();
        $end  = $r->end_date   ?: now()->addDay()->toDateString();
        return [$start, $end];
    }

    private function buildSalesData(string $start, string $end): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creator = $user?->creatorId();
        $items = InvoiceProduct::select(
            DC::TABLE_PROD_SERVS.'.name',
            DB::raw('SUM('.DC::TABLE_INV_PRD.'.quantity)            AS quantity'),
            DB::raw('SUM('.DC::TABLE_INV_PRD.'.price * '.DC::TABLE_INV_PRD.'.quantity) AS price'),
            DB::raw('SUM('.DC::TABLE_INV_PRD.'.price) / SUM('.DC::TABLE_INV_PRD.'.quantity) AS avg_price')
        )
            ->leftJoin(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS.'.id', '=', DC::TABLE_INV_PRD.'.'.BC::COL_PRD_ID)
            ->leftJoin('invoices',         DC::TABLE_INVS.'.id',          '=', DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID)
            ->where(DC::TABLE_PROD_SERVS.'.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_INVS.'.'.BC::COL_ISS_DT, [$start, $end])
            ->groupBy(DC::TABLE_INV_PRD.'.'.BC::COL_PRD_ID)
            ->get()
            ->toArray();
        $raw = Invoice::select(
            'customers.name',
            DB::raw('COUNT(DISTINCT '.DC::TABLE_INVS.'.'.BC::COL_CST_ID.', '.DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID.') AS invoice_count')
        )
            ->selectRaw('SUM(('.DC::TABLE_INV_PRD.'.price * '.DC::TABLE_INV_PRD.'.quantity) - '.DC::TABLE_INV_PRD.'.discount) AS price')
            ->selectRaw(<<<SQL
            (
              SELECT SUM((price * quantity - discount) * (taxes.rate/100))
              FROM invoice_products
              LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax)>0
              WHERE invoice_products.invoice_id = invoices.id
            ) AS total_tax
        SQL)
            ->leftJoin('customers', 'customers.id',       '=', DC::TABLE_INVS.'.'.BC::COL_CST_ID)
            ->leftJoin(DC::TABLE_INV_PRD.'', DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->where(DC::TABLE_INVS.'.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_INVS.'.'.BC::COL_ISS_DT, [$start, $end])
            ->groupBy(DC::TABLE_INVS.'.'.BC::COL_INV_ID)
            ->get()
            ->toArray();
        $merged = [];
        foreach ($raw as $row) {
            $n = $row['name'];
            if (!isset($merged[$n])) {
                $merged[$n] = [
                    'name'          => $n,
                    'invoice_count' => 0,
                    'price'         => 0.0,
                    'total_tax'     => 0.0,
                ];
            }
            $merged[$n]['invoice_count'] += $row['invoice_count'];
            $merged[$n]['price']         += $row['price'];
            $merged[$n]['total_tax']     += $row['total_tax'];
        }
        $customers = array_values($merged);
        return [$items, $customers];
    }

    /**
     * @return [ receivableCustomers, receivableSummaries, receivableDetails, agingSummaries ].
     */
    private function buildReceivableData(string $start, string $end): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creator = $user?->creatorId();
        $receivableCustomers = Invoice::select('customers.name')
            ->selectRaw('SUM(('.DC::TABLE_INV_PRD.'.price * '.DC::TABLE_INV_PRD.'.quantity) - '.DC::TABLE_INV_PRD.'.discount) AS price')
            ->selectRaw('SUM('.DC::TABLE_INV_PAY.'.amount) AS pay_price')
            ->selectRaw(<<<SQL
            (
              SELECT SUM((price * quantity - discount) * (taxes.rate/100))
              FROM invoice_products
              LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax)>0
              WHERE invoice_products.invoice_id = invoices.id
            ) AS total_tax
            SQL)
            ->selectRaw('(SELECT SUM(amount) FROM '.DC::TABLE_CR_NOTES.' WHERE '.DC::TABLE_CR_NOTES.'.invoice = invoices.id) AS credit_price')
            ->leftJoin('customers',        'customers.id',        '=', DC::TABLE_INVS.'.'.BC::COL_CST_ID)
            ->leftJoin(DC::TABLE_INV_PAY, DC::TABLE_INV_PAY.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->leftJoin(DC::TABLE_INV_PRD.'', DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->where(DC::TABLE_INVS.'.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_INVS.'.'.BC::COL_ISS_DT, [$start, $end])
            ->groupBy(DC::TABLE_INVS.'.'.BC::COL_INV_ID)
            ->get()
            ->toArray();
        $sumInv = Invoice::select('customers.name')
            ->selectRaw(DC::TABLE_INVS.'.'.BC::COL_INV_ID.' AS invoice')
            ->selectRaw('SUM(('.DC::TABLE_INV_PRD.'.price * '.DC::TABLE_INV_PRD.'.quantity) - '.DC::TABLE_INV_PRD.'.discount) AS price')
            ->selectRaw('SUM('.DC::TABLE_INV_PAY.'.amount) AS pay_price')
            ->selectRaw(<<<SQL
            (
              SELECT SUM((price * quantity - discount) * (taxes.rate/100))
              FROM invoice_products
              LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax)>0
              WHERE invoice_products.invoice_id = invoices.id
            ) AS total_tax
        SQL)
            ->selectRaw(DC::TABLE_INVS.'.'.BC::COL_ISS_DT.' AS '.BC::COL_ISS_DT)
            ->selectRaw(DC::TABLE_INVS.'.status AS status')
            ->leftJoin('customers',        'customers.id',        '=', DC::TABLE_INVS.'.'.BC::COL_CST_ID)
            ->leftJoin(DC::TABLE_INV_PAY, DC::TABLE_INV_PAY.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->leftJoin(DC::TABLE_INV_PRD.'', DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->where(DC::TABLE_INVS.'.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_INVS.'.'.BC::COL_ISS_DT, [$start, $end])
            ->groupBy(DC::TABLE_INVS.'.'.BC::COL_INV_ID)
            ->get()
            ->toArray();
        $sumCred = CreditNote::select('customers.name')
            ->selectRaw('NULL AS invoice')
            ->selectRaw(DC::TABLE_CR_NOTES.'.amount AS price')
            ->selectRaw('0 AS pay_price')
            ->selectRaw('0 AS total_tax')
            ->selectRaw(DC::TABLE_CR_NOTES.'.date AS '.BC::COL_ISS_DT)
            ->selectRaw('5 AS status')
            ->leftJoin('customers', 'customers.id', '=', DC::TABLE_CR_NOTES.'.customer')
            ->leftJoin('invoices',  DC::TABLE_INVS.'.id',  '=', DC::TABLE_CR_NOTES.'.invoice')
            ->where(DC::TABLE_INVS.'.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_CR_NOTES.'.date', [$start, $end])
            ->groupBy(DC::TABLE_CR_NOTES.'.id')
            ->get()
            ->toArray();
        $receivableSummaries = array_merge($sumCred, $sumInv);
        $detInv = Invoice::select('customers.name')
            ->selectRaw(DC::TABLE_INVS.'.'.BC::COL_INV_ID.' AS invoice')
            ->selectRaw('SUM('.DC::TABLE_INV_PRD.'.price) AS price')
            ->selectRaw(DC::TABLE_INV_PRD.'.quantity AS quantity')
            ->selectRaw(DC::TABLE_PROD_SERVS.'.name AS product_name')
            ->selectRaw(DC::TABLE_INVS.'.issue_date AS '.BC::COL_ISS_DT)
            ->selectRaw(DC::TABLE_INVS.'.status AS status')
            ->leftJoin('customers',         'customers.id',         '=', DC::TABLE_INVS.'.'.BC::COL_CST_ID)
            ->leftJoin(DC::TABLE_INV_PRD.'',  DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->leftJoin(DC::TABLE_PROD_SERVS,  DC::TABLE_PROD_SERVS.'.id',  '=', DC::TABLE_INV_PRD.'.'.BC::COL_PRD_ID)
            ->where(DC::TABLE_INVS.'.' . DC::COL_TABLE_CREATOR,  $creator)
            ->whereBetween(DC::TABLE_INVS.'.'.BC::COL_ISS_DT, [$start, $end])
            ->groupBy(DC::TABLE_INVS.'.'.BC::COL_INV_ID, DC::TABLE_PROD_SERVS.'.name')
            ->get()
            ->toArray();
        $detCredRaw = CreditNote::select('customers.name')
            ->selectRaw('NULL AS invoice')
            ->selectRaw(DC::TABLE_CR_NOTES.'.id AS invoices')
            ->selectRaw(DC::TABLE_CR_NOTES.'.amount AS price')
            ->selectRaw(DC::TABLE_PROD_SERVS.'.name AS product_name')
            ->selectRaw(DC::TABLE_CR_NOTES.'.date AS '.BC::COL_ISS_DT)
            ->selectRaw('5 AS status')
            ->leftJoin('customers',        'customers.id',        '=', DC::TABLE_CR_NOTES.'.customer')
            ->leftJoin(DC::TABLE_INV_PRD.'', DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID, '=', DC::TABLE_CR_NOTES.'.invoice')
            ->leftJoin(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS.'.id', '=', DC::TABLE_INV_PRD.'.'.BC::COL_PRD_ID)
            ->leftJoin('invoices',        DC::TABLE_INVS.'.id',        '=', DC::TABLE_CR_NOTES.'.invoice')
            ->where(DC::TABLE_INVS.'.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_CR_NOTES.'.date', [$start, $end])
            ->groupBy(DC::TABLE_CR_NOTES.'.id', DC::TABLE_PROD_SERVS.'.name')
            ->get()
            ->toArray();
        $merged = [];
        foreach ($detCredRaw as $r) {
            $key = $r['invoices'];
            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'name'         => $r['name'],
                    'invoice'      => $r['invoice'],
                    'invoices'     => $r['invoices'],
                    'price'        => $r['price'],
                    'quantity'     => 0,
                    'product_name' => '',
                    'issue_date'   => $r['issue_date'],
                    'status'       => $r['status'],
                ];
            }
            if (strpos($merged[$key]['product_name'], $r['product_name']) === false)
                $merged[$key]['product_name'] .=
                    ($merged[$key]['product_name'] !== '' ? ', ' : '')
                    . $r['product_name'];
        }
        $detCred   = array_values($merged);
        $receivableDetails = array_merge($detInv, $detCred);
        $ageRaw = Invoice::select('customers.name', DC::TABLE_INVS.'.'.BC::COL_DUE_DT.' as due_date', DC::TABLE_INVS.'.status as status', DC::TABLE_INVS.'.'.BC::COL_INV_ID.' as '.BC::COL_INV_ID)
            ->selectRaw('SUM(('.DC::TABLE_INV_PRD.'.price * '.DC::TABLE_INV_PRD.'.quantity) - '.DC::TABLE_INV_PRD.'.discount) AS price')
            ->selectRaw('SUM('.DC::TABLE_INV_PAY.'.amount) AS pay_price')
            ->selectRaw(<<<SQL
            (
              SELECT SUM((price * quantity - discount) * (taxes.rate/100))
              FROM invoice_products
              LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax)>0
              WHERE invoice_products.invoice_id = invoices.id
            ) AS total_tax
        SQL)
            ->selectRaw('(SELECT SUM(amount) FROM '.DC::TABLE_CR_NOTES.' WHERE '.DC::TABLE_CR_NOTES.'.invoice = invoices.id) AS credit_price')
            ->leftJoin('customers',        'customers.id',        '=', DC::TABLE_INVS.'.'.BC::COL_CST_ID)
            ->leftJoin(DC::TABLE_INV_PAY, DC::TABLE_INV_PAY.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->leftJoin(DC::TABLE_INV_PRD.'', DC::TABLE_INV_PRD.'.'.BC::COL_INV_ID, '=', DC::TABLE_INVS.'.id')
            ->where(DC::TABLE_INVS.'.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_INVS.'.'.BC::COL_ISS_DT, [$start, $end])
            ->groupBy(DC::TABLE_INVS.'.'.BC::COL_INV_ID)
            ->get()
            ->toArray();
        $agingSummaries = [];
        $today = Carbon::today();
        foreach ($ageRaw as $r) {
            $cust   = $r['name'];
            $balance = ($r['price'] + $r['total_tax']) - ($r['pay_price'] + $r['credit_price']);
            $dueDate = Carbon::parse($r['due_date']);
            $diff = $dueDate->diffInDays($today, false);
            if (!isset($agingSummaries[$cust])) {
                $agingSummaries[$cust] = [
                    'current'             => 0.0,
                    '1_15_days'           => 0.0,
                    '16_30_days'          => 0.0,
                    '31_45_days'          => 0.0,
                    'greater_than_45_days' => 0.0,
                    'total_due'           => 0.0,
                ];
            }
            if ($diff <= 0) {
                $agingSummaries[$cust]['current'] += $balance;
            } elseif ($diff <= 15) {
                $agingSummaries[$cust]['1_15_days'] += $balance;
            } elseif ($diff <= 30) {
                $agingSummaries[$cust]['16_30_days'] += $balance;
            } elseif ($diff <= 45) {
                $agingSummaries[$cust]['31_45_days'] += $balance;
            } else {
                $agingSummaries[$cust]['greater_than_45_days'] += $balance;
            }
            $agingSummaries[$cust]['total_due'] += $balance;
        }
        return [
            $receivableCustomers,
            $receivableSummaries,
            $receivableDetails,
            $agingSummaries
        ];
    }

    /**
     * @return [ payableVendors, payableSummaries, payableDetails ].
     */
    private function buildPayableData(string $start, string $end): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creator = $user?->creatorId();

        // 1) vendors summary
        $vendors = Bill::select('vendors.name')
            ->selectRaw('SUM(('.DC::TABLE_BL_PRD.'.price * '.DC::TABLE_BL_PRD.'.quantity) - '.DC::TABLE_BL_PRD.'.discount) AS price')
            ->selectRaw('SUM('.DC::TABLE_BL_PAY.'.amount) AS pay_price')
            ->selectRaw(<<<SQL
            (
              SELECT SUM((price * quantity - discount) * (taxes.rate/100))
              FROM bill_products
              LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax)>0
              WHERE bill_products.bill_id = bills.id
            ) AS total_tax
        SQL)
            ->selectRaw('(SELECT SUM(amount) FROM '.DC::TABLE_DB_NOTES.' WHERE '.DC::TABLE_DB_NOTES.'.bill = bills.id) AS debit_price')
            ->leftJoin('vendors',      'vendors.id',      '=', 'bills.'.UC::COL_VD_ID)
            ->leftJoin(DC::TABLE_BL_PAY, DC::TABLE_BL_PAY.'.bill_id', '=', DC::TABLE_BL.'.id')
            ->leftJoin(DC::TABLE_BL_PRD, DC::TABLE_BL_PRD.'.bill_id', '=', DC::TABLE_BL.'.id')
            ->where('bills.' . DC::COL_TABLE_CREATOR,  $creator)
            ->whereNotIn('bills.'.UC::COL_U_TP, ['employee', 'customer'])
            ->whereBetween('bills.' . BC::COL_BL_DT, [$start, $end])
            ->groupBy('bills.'.BC::COL_BL_ID)
            ->get()
            ->toArray();

        // 2a) bill summaries
        $sumBill = Bill::select('vendors.name')
            ->selectRaw('bills.' . BC::COL_BL_ID . ' AS bill')
            ->selectRaw('SUM(('.DC::TABLE_BL_PRD.'.price * '.DC::TABLE_BL_PRD.'.quantity) - '.DC::TABLE_BL_PRD.'.discount) AS price')
            ->selectRaw('SUM('.DC::TABLE_BL_PAY.'.amount) AS pay_price')
            ->selectRaw(<<<SQL
            (
              SELECT SUM((price * quantity - discount) * (taxes.rate/100))
              FROM bill_products
              LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax)>0
              WHERE bill_products.bill_id = bills.id
            ) AS total_tax
        SQL)
            ->selectRaw('bills.' . BC::COL_BL_DT . ' AS bill_date')
            ->selectRaw('bills.status    AS status')
            ->leftJoin('vendors',      'vendors.id',      '=', 'bills.vendor_id')
            ->leftJoin(DC::TABLE_BL_PAY, DC::TABLE_BL_PAY.'.bill_id', '=', DC::TABLE_BL.'.id')
            ->leftJoin(DC::TABLE_BL_PRD, DC::TABLE_BL_PRD.'.bill_id', '=', DC::TABLE_BL.'.id')
            ->where('bills.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereNotIn('bills.'.UC::COL_U_TP, ['employee', 'customer'])
            ->whereBetween('bills.' . BC::COL_BL_DT, [$start, $end])
            ->groupBy('bills.id')
            ->get()
            ->toArray();

        // 2b) debit‐note summaries
        $sumDebit = DebitNote::select('vendors.name')
            ->selectRaw('NULL AS bill')
            ->selectRaw(DC::TABLE_DB_NOTES.'.amount AS price')
            ->selectRaw('0 AS pay_price')
            ->selectRaw('0 AS total_tax')
            ->selectRaw(DC::TABLE_DB_NOTES.'.date AS '.BC::COL_BL_DT)
            ->selectRaw('5 AS status')
            ->leftJoin('vendors', 'vendors.id', '=', DC::TABLE_DB_NOTES.'.vendor')
            ->leftJoin('bills',  'bills.id', '=', DC::TABLE_DB_NOTES.'.bill')
            ->where('bills.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereBetween(DC::TABLE_DB_NOTES.'.date', [$start, $end])
            ->groupBy(DC::TABLE_DB_NOTES.'.id')
            ->get()
            ->toArray();

        $summaries = array_merge($sumDebit, $sumBill);

        // 3a) bill details
        $detBill = Bill::select('vendors.name')
            ->selectRaw('bills.' . BC::COL_BL_ID . ' AS bill')
            ->selectRaw('SUM(' . DC::TABLE_BL_PRD . '.price) AS price')
            ->selectRaw(DC::TABLE_BL_PRD.'.quantity AS quantity')
            ->selectRaw(DC::TABLE_PROD_SERVS.'.name AS product_name')
            ->selectRaw('bills.' . BC::COL_BL_DT . ' AS ' . BC::COL_BL_DT)
            ->selectRaw('bills.status    AS status')
            ->leftJoin('vendors',       'vendors.id',       '=', 'bills.'.UC::COL_VD_ID)
            ->leftJoin(DC::TABLE_BL_PRD, DC::TABLE_BL_PRD.'.bill_id', '=', 'bills.id')
            ->leftJoin(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS.'.id', '=', DC::TABLE_BL_PRD.'.'.BC::COL_PRD_ID)
            ->where('bills.' . DC::COL_TABLE_CREATOR, $creator)
            ->whereNotIn('bills.' . UC::COL_U_TP, ['employee', 'customer'])
            ->whereBetween('bills.' . BC::COL_BL_DT, [$start, $end])
            ->groupBy('bills.' . BC::COL_BL_ID, DC::TABLE_PROD_SERVS.'.name')
            ->get()
            ->toArray();

        // 3b) debit‐note details
        $detDebitRaw = DebitNote::select('vendors.name')
            ->selectRaw('NULL AS bill')
            ->selectRaw(DC::TABLE_DB_NOTES.'.id AS bills')
            ->selectRaw(DC::TABLE_DB_NOTES.'.amount AS price')
            ->selectRaw(DC::TABLE_PROD_SERVS.'.name AS product_name')
            ->selectRaw(DC::TABLE_DB_NOTES.'.date AS bill_date')
            ->selectRaw('5 AS status')
            ->leftJoin('vendors',         'vendors.id',         '=', DC::TABLE_DB_NOTES.'.vendor')
            ->leftJoin(DC::TABLE_BL_PRD,   DC::TABLE_BL_PRD.'.bill_id', '=', DC::TABLE_DB_NOTES.'.bill')
            ->leftJoin(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS.'.id', '=', DC::TABLE_BL_PRD.'.'.BC::COL_PRD_ID)
            ->leftJoin('bills',           'bills.id',           '=', DC::TABLE_DB_NOTES.'.bill')
            ->where('bills.' . DC::COL_TABLE_CREATOR,   $creator)
            ->whereBetween(DC::TABLE_DB_NOTES.'.date', [$start, $end])
            ->groupBy(DC::TABLE_DB_NOTES.'.id', DC::TABLE_PROD_SERVS.'.name')
            ->get()
            ->toArray();

        $merged = [];
        foreach ($detDebitRaw as $r) {
            $key = $r['bills'];
            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'name'         => $r['name'],
                    'bill'         => $r['bill'],
                    'bills'        => $r['bills'],
                    'price'        => $r['price'],
                    'quantity'     => 0,
                    'product_name' => '',
                    'bill_date'    => $r['bill_date'],
                    'status'       => $r['status'],
                ];
            }
            if (strpos($merged[$key]['product_name'], $r['product_name']) === false) {
                $merged[$key]['product_name'] .=
                    ($merged[$key]['product_name'] !== '' ? ', ' : '')
                    . $r['product_name'];
            }
        }
        $detDebit = array_values($merged);
        $details = array_merge($detBill, $detDebit);

        return [$vendors, $summaries, $details];
    }

    private function authorizeReport(Request $r, string $permission): ?RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($user?->can($permission)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        return null;
    }

    private function logContext(): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $u = $userOrRedirect;
        return [UC::COL_USER_ID => $u->id, 'creator_id' => $u->creatorId()];
    }

    private function _buildIncomeSummaryView(Request $request, int|string $creatorId): View
    {
        $account   = BankAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('holder_name', 'id')
            ->prepend('Select Account', '');
        $customer  = Customer::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id')
            ->prepend('Select Customer', '');
        $category  = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('type', 1)
            ->pluck('name', 'id')
            ->prepend('Select Category', '');
        $monthList = $this->yearMonth();
        $yearList  = $this->yearList();
        $filter    = ['category' => __('All'), 'customer' => __('All')];
        $year      = $request->year ?? date('Y');
        $data      = [
            'monthList'   => $monthList,
            'yearList'    => $yearList,
            'currentYear' => $year,
        ];

        $incomes = Revenue::selectRaw(
            'sum(amount) as amount, MONTH(date) as month, category_id'
        )
            ->leftJoin(
                'product_service_categories',
                'revenues.category_id',
                '=',
                'product_service_categories.id'
            )
            ->where('product_service_categories.type', 1)
            ->where('revenues.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('date', $year)
            ->groupBy('category_id', 'month')
            ->get();

        $tmpIncome = [];
        foreach ($incomes as $inc) {
            $tmpIncome[$inc->category_id][$inc->month] = $inc->amount;
        }

        $incomeArr = [];
        foreach ($tmpIncome as $catId => $recs) {
            $entry = [
                'category' => ProductServiceCategory::find($catId)?->name ?? '',
                'data'     => [],
            ];
            for ($m = 1; $m <= 12; $m++) {
                $entry['data'][$m] = $recs[$m] ?? 0;
            }
            $incomeArr[] = $entry;
        }

        $totalRev = Revenue::selectRaw('sum(amount) as amount, MONTH(date) as month')
            ->where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('date', $year)
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->when(
                $request->customer,
                fn($q, $c) => $q->where('customer_id', $c),
                fn($q) => $q
            )
            ->groupBy('month')
            ->get()
            ->pluck('amount', 'month')
            ->toArray();

        $incomeTotal = [];
        for ($m = 1; $m <= 12; $m++) {
            $incomeTotal[] = $totalRev[$m] ?? 0;
        }

        $invoices = Invoice::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('status', '!=', 0)
            ->whereYear('send_date', $year)
            ->when(
                $request->customer,
                fn($q, $c) => $q->where('customer_id', $c),
                fn($q) => $q
            )
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->get();

        $tmpInv   = [];
        $invByMon = [];
        foreach ($invoices as $inv) {
            $mon                  = $inv->send_date->format('n');
            $tmpInv[$inv->category_id][$mon][] = $inv->getTotal();
        }

        $invoiceArr = [];
        foreach ($tmpInv as $catId => $recs) {
            $entry = [
                'category' => ProductServiceCategory::find($catId)?->name ?? '',
                'data'     => [],
            ];
            for ($m = 1; $m <= 12; $m++) {
                $vals             = $recs[$m] ?? [];
                $entry['data'][$m] = $vals ? array_sum($vals) : 0;
                $invByMon[$m][]   = $entry['data'][$m];
            }
            $invoiceArr[] = $entry;
        }

        $invoiceTotal = [];
        for ($m = 1; $m <= 12; $m++) {
            $invoiceTotal[] = array_sum($invByMon[$m] ?? []);
        }

        $chartIncome = array_map(
            fn() => array_sum(func_get_args()),
            $incomeTotal,
            $invoiceTotal
        );

        $data += [
            'incomeArr'  => $incomeArr,
            'invoiceArr' => $invoiceArr,
            'chartIncome' => $chartIncome,
            'account'    => $account,
            'customer'   => $customer,
            'category'   => $category,
        ];

        return view(VW::RPT . '.income_summary', compact('filter'), $data);
    }

    private function _buildExpenseSummaryView(Request $request, int|string $creatorId): View
    {
        $account   = BankAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('holder_name', 'id')
            ->prepend('Select Account', '');
        $vendor    = Vendor::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id')
            ->prepend('Select Vendor', '');
        $category  = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('type', 2)
            ->pluck('name', 'id')
            ->prepend('Select Category', '');
        $monthList = $this->yearMonth();
        $yearList  = $this->yearList();
        $filter    = ['category' => __('All'), 'vendor' => __('All')];
        $year      = $request->year ?? date('Y');
        $data      = [
            'monthList'   => $monthList,
            'yearList'    => $yearList,
            'currentYear' => $year,
        ];

        $expenses = Payment::selectRaw(
            'sum(amount) as amount, MONTH(date) as month, category_id'
        )
            ->leftJoin(
                'product_service_categories',
                'payments.category_id',
                '=',
                'product_service_categories.id'
            )
            ->where('product_service_categories.type', 2)
            ->where('payments.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('date', $year)
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->when(
                $request->vendor,
                fn($q, $v) => $q->where('vendor_id', $v),
                fn($q) => $q
            )
            ->groupBy('category_id', 'month')
            ->get();

        $tmpExp    = [];
        foreach ($expenses as $exp) {
            $tmpExp[$exp->category_id][$exp->month] = $exp->amount;
        }

        $expenseArr = [];
        foreach ($tmpExp as $catId => $recs) {
            $entry = [
                'category' => ProductServiceCategory::find($catId)?->name ?? '',
                'data'     => [],
            ];
            for ($m = 1; $m <= 12; $m++) {
                $entry['data'][$m] = $recs[$m] ?? 0;
            }
            $expenseArr[] = $entry;
        }

        $totalPay = Payment::selectRaw('sum(amount) as amount, MONTH(date) as month')
            ->where('payments.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('date', $year)
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->when(
                $request->vendor,
                fn($q, $v) => $q->where('vendor_id', $v),
                fn($q) => $q
            )
            ->groupBy('month')
            ->get()
            ->pluck('amount', 'month')
            ->toArray();

        $payTotal = [];
        for ($m = 1; $m <= 12; $m++) {
            $payTotal[] = $totalPay[$m] ?? 0;
        }

        $bills   = Bill::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('status', '!=', 0)
            ->whereYear('send_date', $year)
            ->when(
                $request->vendor,
                fn($q, $v) => $q->where('vendor_id', $v),
                fn($q) => $q
            )
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->get();

        $tmpBill   = [];
        $billByMon = [];
        foreach ($bills as $bill) {
            $mon                   = $bill->send_date->format('n');
            $tmpBill[$bill->category_id][$mon][] = $bill->getTotal();
        }

        $billArr   = [];
        foreach ($tmpBill as $catId => $recs) {
            $entry = [
                'category' => ProductServiceCategory::find($catId)?->name ?? '',
                'data'     => [],
            ];
            for ($m = 1; $m <= 12; $m++) {
                $vals             = $recs[$m] ?? [];
                $entry['data'][$m] = $vals ? array_sum($vals) : 0;
                $billByMon[$m][]  = $entry['data'][$m];
            }
            $billArr[] = $entry;
        }

        $billTotal = [];
        for ($m = 1; $m <= 12; $m++) {
            $billTotal[] = array_sum($billByMon[$m] ?? []);
        }

        $chartExpense = array_map(
            fn() => array_sum(func_get_args()),
            $payTotal,
            $billTotal
        );

        $data += [
            'expenseArr'   => $expenseArr,
            'billArr'      => $billArr,
            'chartExpense' => $chartExpense,
            'account'      => $account,
            'vendor'       => $vendor,
            'category'     => $category,
        ];

        return view(VW::RPT . '.expense_summary', compact('filter'), $data);
    }

    private function _buildIncomeVsExpenseSummaryView(Request $request, int|string $creatorId): View
    {
        $account   = BankAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('holder_name', 'id')
            ->prepend('Select Account', '');
        $vendor    = Vendor::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id')
            ->prepend('Select Vendor', '');
        $customer  = Customer::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id')
            ->prepend('Select Customer', '');
        $category  = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereIn('type', [1, 2])
            ->pluck('name', 'id')
            ->prepend('Select Category', '');
        $monthList = $this->yearMonth();
        $yearList  = $this->yearList();
        $filter    = [
            'category' => __('All'),
            'customer' => __('All'),
            'vendor'   => __('All'),
        ];
        $year      = $request->year ?? date('Y');
        $data      = [
            'monthList'   => $monthList,
            'yearList'    => $yearList,
            'currentYear' => $year,
        ];

        $payData = Payment::selectRaw('sum(amount) as amount, MONTH(date) as month')
            ->where('payments.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('date', $year)
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->when(
                $request->vendor,
                fn($q, $v) => $q->where('vendor_id', $v),
                fn($q) => $q
            )
            ->groupBy('month')
            ->get()
            ->pluck('amount', 'month')
            ->toArray();

        $bills   = Bill::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('status', '!=', 0)
            ->whereYear('send_date', $year)
            ->when(
                $request->vendor,
                fn($q, $v) => $q->where('vendor_id', $v),
                fn($q) => $q
            )
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->get();

        $tmpBill = [];
        foreach ($bills as $bill) {
            $mon            = $bill->send_date->format('n');
            $tmpBill[$mon][] = $bill->getTotal();
        }

        $revData = Revenue::selectRaw('sum(amount) as amount, MONTH(date) as month')
            ->where('revenues.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('date', $year)
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->when(
                $request->customer,
                fn($q, $c) => $q->where('customer_id', $c),
                fn($q) => $q
            )
            ->groupBy('month')
            ->get()
            ->pluck('amount', 'month')
            ->toArray();

        $invData = Invoice::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('status', '!=', 0)
            ->whereYear('send_date', $year)
            ->when(
                $request->customer,
                fn($q, $c) => $q->where('customer_id', $c),
                fn($q) => $q
            )
            ->when(
                $request->category,
                fn($q, $c) => $q->where('category_id', $c),
                fn($q) => $q
            )
            ->get();

        $tmpInv = [];
        foreach ($invData as $inv) {
            $mon            = $inv->send_date->format('n');
            $tmpInv[$mon][] = $inv->getTotal();
        }

        $paymentTotal = [];
        $billTotal   = [];
        $revenueTotal = [];
        $invoiceTotal = [];
        for ($m = 1; $m <= 12; $m++) {
            $paymentTotal[] = $payData[$m] ?? 0;
            $billTotal[]   = array_sum($tmpBill[$m] ?? []);
            $revenueTotal[] = $revData[$m] ?? 0;
            $invoiceTotal[] = array_sum($tmpInv[$m] ?? []);
        }

        $profit = [];
        for ($i = 0; $i < 12; $i++) {
            $profit[$i + 1] = $revenueTotal[$i]
                + $invoiceTotal[$i]
                - ($paymentTotal[$i] + $billTotal[$i]);
        }

        $data += [
            'paymentTotal' => $paymentTotal,
            'billTotal'    => $billTotal,
            'revenueTotal' => $revenueTotal,
            'invoiceTotal' => $invoiceTotal,
            'profit'       => $profit,
            'account'      => $account,
            'vendor'       => $vendor,
            'customer'     => $customer,
            'category'     => $category,
        ];

        return view(VW::RPT . '.income_vs_expense_summary', compact('filter'), $data);
    }

    private function _buildTaxSummaryView(Request $request, int|string $creatorId): View
    {
        $monthList = $this->yearMonth();
        $yearList = $this->yearList();
        $taxList  = Tax::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $year     = $request->year ?? date('Y');

        $invoiceProducts = InvoiceProduct::selectRaw(
            DC::TABLE_INV_PRD.'.*, MONTH('.DC::TABLE_INV_PRD.'.created_at) as month'
        )
            ->leftJoin(
                DC::TABLE_PROD_SERVS,
                DC::TABLE_INV_PRD.'.'.BC::COL_PRD_ID,
                '=',
                DC::TABLE_PROD_SERVS.'.id'
            )
            ->whereYear(DC::TABLE_INV_PRD.'.created_at', $year)
            ->where(DC::TABLE_PROD_SERVS.'.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->get();

        $incomeTaxesData = [];
        foreach ($invoiceProducts as $prod) {
            $incomeTax = [];
            foreach (Utility::tax($prod->tax) as $t) {
                $incomeTax[$t->name ?? ''] =
                    Utility::taxRate($t->rate ?? 0, $prod->price, $prod->quantity);
            }
            $incomeTaxesData[$prod->month][] = $incomeTax;
        }

        $income = [];
        foreach ($incomeTaxesData as $m => $taxes) {
            $rec = [];
            foreach ($taxes as $t) {
                foreach ($t as $name => $amt) {
                    $rec[$name] = ($rec[$name] ?? 0) + $amt;
                }
            }
            $income['data'][$m] = $rec;
        }

        $incomeData = [];
        for ($i = 1; $i <= 12; $i++) {
            $incomeData[$i] = $income['data'][$i] ?? [];
        }

        $incomes = [];
        foreach ($taxList as $tax) {
            foreach ($incomeData as $m => $taxAmounts) {
                $incomes[$tax->name][$m] = $taxAmounts[$tax->name] ?? 0;
            }
        }

        $billProducts = BillProduct::selectRaw(
            'bill_products.*, MONTH(bill_products.created_at) as month'
        )
            ->leftJoin(
                DC::TABLE_PROD_SERVS,
                'bill_products.'.BC::COL_PRD_ID,
                '=',
                DC::TABLE_PROD_SERVS.'.id'
            )
            ->whereYear('bill_products.created_at', $year)
            ->where(DC::TABLE_PROD_SERVS.'.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->get();

        $expenseTaxesData = [];
        foreach ($billProducts as $prod) {
            $billTax = [];
            foreach (Utility::tax($prod->tax) as $t) {
                $billTax[$t->name ?? ''] =
                    Utility::taxRate($t->rate ?? 0, $prod->price, $prod->quantity);
            }
            $expenseTaxesData[$prod->month][] = $billTax;
        }

        $billDataArr = [];
        foreach ($expenseTaxesData as $m => $taxes) {
            $rec = [];
            foreach ($taxes as $t) {
                foreach ($t as $name => $amt) {
                    $rec[$name] = ($rec[$name] ?? 0) + $amt;
                }
            }
            $billDataArr['data'][$m] = $rec;
        }

        $billData = [];
        for ($i = 1; $i <= 12; $i++) {
            $billData[$i] = $billDataArr['data'][$i] ?? [];
        }

        $expenses = [];
        foreach ($taxList as $tax) {
            foreach ($billData as $m => $taxAmounts) {
                $expenses[$tax->name][$m] = $taxAmounts[$tax->name] ?? 0;
            }
        }

        $filter = [
            'startDateRange' => 'Jan-' . $year,
            'endDateRange'   => 'Dec-' . $year,
        ];

        return view(
            VW::RPT . '.tax_summary',
            compact('filter'),
            [
                'monthList' => $monthList,
                'yearList'  => $yearList,
                'taxList'   => $taxList,
                'incomes'   => $incomes,
                'expenses'  => $expenses,
            ]
        );
    }

    private function _buildInvoiceSummaryView(Request $request, int|string $creatorId): View|ResponseFactory
    {
        $filter  = ['customer' => __('All'), 'status' => __('All')];
        $customer = Customer::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id')
            ->prepend('Select Customer', '');
        Log::debug('Creator ID in Invoice Summary: ' . ($creatorId ?? 'null'));
        $status  = Invoice::$statuses;
        $q       = Invoice::selectRaw(
            DC::TABLE_INVS.'.*, MONTH(send_date) as month'
        );
        Log::debug('Invoices queried by month: ' . (json_encode($q->get()->toArray()) ?? 'null'));
        if ($request->status !== '') {
            $q->where('status', $request->status);
            $filter['status'] = $status[$request->status] ?? '';
        } else $q->where('status', '!=', 0);
        Log::debug('After status filter: ' . (json_encode($q->get()->toArray()) ?? 'null'));
        $q->where(DC::COL_TABLE_CREATOR, $creatorId);
        Log::debug('After creator filter: ' . (json_encode($q->get()->toArray()) ?? 'null'));
        $start = !empty($request->start_month)
            ? strtotime($request->start_month)
            : strtotime(date('Y-01'));
        $end  = !empty($request->end_month)
            ? strtotime($request->end_month)
            : strtotime(date('Y-12'));
        $q->where('send_date', '>=', date('Y-m-01', $start))
            ->where('send_date', '<=', date('Y-m-t', $end));
        Log::debug('After date range filter: ' . (json_encode($q->get()->toArray()) ?? 'null'));
        $filter['startDateRange'] = date('M-Y', $start);
        $filter['endDateRange']  = date('M-Y', $end);
        if (!empty($request->customer)) {
            $q->where('customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = $cust->name ?? '';
        }
        Log::debug('After customer filter: ' . (json_encode($q->get()->toArray()) ?? 'null'));
        $invoices      = $q->get();
        $totInv        = 0;
        $totDue        = 0;
        $arr           = [];
        if (!empty($invoices))
            foreach ($invoices as $inv) {
                $totInv += $inv->getTotal();
                $totDue += $inv->getDue();
                $arr[$inv->month][] = $inv->getTotal();
            }
        Log::debug('After processing invoices: ' . json_encode($arr));
        $paid         = $totInv - $totDue;
        $invoiceTotal = [];
        if (!empty($arr))
            for ($i = 1; $i <= 12; $i++)
                $invoiceTotal[] = $arr[$i] ? array_sum($arr[$i]) : 0;
        Log::debug('Invoice totals by month: ' . json_encode($invoiceTotal));
        $monthList = $this->yearMonth();
        $viewCandidate = VW::RPT . '.invoice_report';
        if (!view()->exists($viewCandidate)) {
            Log::error('View not found: ' . $viewCandidate);
            return response('Not Found', 404);
        }
        Log::debug('Rendering view: ' . $viewCandidate);
        return view(
            $viewCandidate,
            compact(
                'invoices',
                'customer',
                'status',
                'totInv',
                'totDue',
                'paid',
                'invoiceTotal',
                'monthList',
                'filter'
            )
        );
    }

    private function _buildBillSummaryView(Request $request, int|string $creatorId): View
    {
        $filter = ['vendor' => __('All'), 'status' => __('All')];
        $vendor = Vendor::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id')
            ->prepend('Select Vendor', '');
        $status = Bill::$statuses;
        $q     = Bill::selectRaw('bills.*, MONTH(send_date) as month');

        if ($request->status !== '') {
            $q->where('status', $request->status);
            $filter['status'] = $status[$request->status] ?? '';
        } else {
            $q->where('status', '!=', 0);
        }

        $q->where(DC::COL_TABLE_CREATOR, $creatorId);

        $start = !empty($request->start_month)
            ? strtotime($request->start_month)
            : strtotime(date('Y-01'));
        $end  = !empty($request->end_month)
            ? strtotime($request->end_month)
            : strtotime(date('Y-12'));

        $q->where('send_date', '>=', date('Y-m-01', $start))
            ->where('send_date', '<=', date('Y-m-t', $end));

        $filter['startDateRange'] = date('M-Y', $start);
        $filter['endDateRange']  = date('M-Y', $end);

        if (!empty($request->vendor)) {
            $q->where('vendor_id', $request->vendor);
            $vend = Vendor::find($request->vendor);
            $filter['vendor'] = $vend->name ?? '';
        }

        $bills      = $q->get();
        $tot        = 0;
        $due        = 0;
        $arr2       = [];

        foreach ($bills as $b) {
            $tot += $b->getTotal();
            $due += $b->getDue();
            $arr2[$b->month][] = $b->getTotal();
        }

        $paid      = $tot - $due;
        $billTotal = [];
        for ($i = 1; $i <= 12; $i++) {
            $billTotal[] = $arr2[$i] ? array_sum($arr2[$i]) : 0;
        }

        $monthList = $this->yearMonth();

        return view(
            VW::RPT . '.bill_report',
            compact(
                'bills',
                'vendor',
                'status',
                'tot',
                'due',
                'paid',
                'billTotal',
                'monthList',
                'filter'
            )
        );
    }

    private function _buildAccountStatementView(
        Request $request,
        int|string $creatorId
    ): View {
        $filter     = ['account' => __('All'), 'type' => __('Revenue')];
        $reportData = [
            'revenues'        => '',
            'payments'        => '',
            'revenueAccounts' => '',
            'paymentAccounts' => '',
        ];
        $account = BankAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('holder_name', 'id')
            ->prepend('Select Account', '');
        $types  = ['revenue' => __('Revenue'), 'payment' => __('Payment')];

        if ($request->type === 'payment') {
            $payAcc = Payment::select(
                DC::TABLE_BANK_ACC . '.id',
                DC::TABLE_BANK_ACC . '.holder_name',
                DC::TABLE_BANK_ACC . '.bank_name'
            )
                ->leftJoin(
                    'bank_accounts',
                    'payments.account_id',
                    '=',
                    DC::TABLE_BANK_ACC . '.id'
                )
                ->groupBy('payments.account_id')
                ->selectRaw('sum(amount) as total')
                ->where('payments.' . DC::COL_TABLE_CREATOR, $creatorId);
            $payments = Payment::where('payments.' . DC::COL_TABLE_CREATOR, $creatorId)
                ->orderBy('id', 'desc');
        } else {
            $revAcc  = Revenue::select(
                DC::TABLE_BANK_ACC . '.id',
                DC::TABLE_BANK_ACC . '.holder_name',
                DC::TABLE_BANK_ACC . '.bank_name'
            )
                ->leftJoin(
                    'bank_accounts',
                    'revenues.account_id',
                    '=',
                    DC::TABLE_BANK_ACC . '.id'
                )
                ->groupBy('revenues.account_id')
                ->selectRaw('sum(amount) as total')
                ->where('revenues.' . DC::COL_TABLE_CREATOR, $creatorId);
            $revenues = Revenue::where('revenues.' . DC::COL_TABLE_CREATOR, $creatorId)
                ->orderBy('id', 'desc');
        }

        $start = !empty($request->start_month)
            ? strtotime($request->start_month)
            : strtotime(date('Y-m'));
        $end  = !empty($request->end_month)
            ? strtotime($request->end_month)
            : strtotime(date('Y-m', strtotime('-5 month')));

        for (
            $cur = $start;
            $cur <= $end;
            $cur = strtotime('+1 month', $cur)
        ) {
            $m = date('m', $cur);
            $y = date('Y', $cur);

            if ($request->type === 'payment') {
                $payments->orWhere(
                    fn($q) => $q
                        ->whereMonth('date', $m)
                        ->whereYear('date', $y)
                        ->where('payments.' . DC::COL_TABLE_CREATOR, $creatorId)
                );
                $payAcc->orWhere(
                    fn($q) => $q
                        ->whereMonth('date', $m)
                        ->whereYear('date', $y)
                        ->where('payments.' . DC::COL_TABLE_CREATOR, $creatorId)
                );
            } else {
                $revenues->orWhere(
                    fn($q) => $q
                        ->whereMonth('date', $m)
                        ->whereYear('date', $y)
                        ->where('revenues.' . DC::COL_TABLE_CREATOR, $creatorId)
                );
                $revAcc->orWhere(
                    fn($q) => $q
                        ->whereMonth('date', $m)
                        ->whereYear('date', $y)
                        ->where('revenues.' . DC::COL_TABLE_CREATOR, $creatorId)
                );
            }
        }

        if (!empty($request->account)) {
            if ($request->type === 'payment') {
                $payments->where('account_id', $request->account);
                $payAcc->where('account_id', $request->account);
            } else {
                $revenues->where('account_id', $request->account);
                $revAcc->where('account_id', $request->account);
            }
            $acc                 = BankAccount::find($request->account);
            $filter['account'] =
                $acc->holder_name === 'Cash'
                ? 'Cash'
                : $acc->holder_name . ' - ' . $acc->bank_name;
        }

        if ($request->type === 'payment') {
            $reportData['payments']       = $payments->get();
            $reportData['paymentAccounts'] = $payAcc
                ->where('payments.' . DC::COL_TABLE_CREATOR, $creatorId)
                ->get();
            $filter['type'] = __('Payment');
        } else {
            $reportData['revenues']       = $revenues->get();
            $reportData['revenueAccounts'] = $revAcc
                ->where('revenues.' . DC::COL_TABLE_CREATOR, $creatorId)
                ->get();
        }

        $filter['startDateRange'] = date('M-Y', $start);
        $filter['endDateRange']  = date('M-Y', $end);

        return view(
            VW::RPT . '.statement_report',
            compact('reportData', 'account', 'types', 'filter')
        );
    }

    private function _buildBalanceSheetView(
        Request $request,
        string $view,
        int|string $creatorId
    ): View {
        $start = $request->start_date ?? date('Y-01-01');
        $end  = $request->end_date ?? date('Y-m-d', strtotime('+1 day'));
        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereIn('name', ['Assets', 'Liabilities', 'Equity'])
            ->get();
        $chartAccounts = [];

        foreach ($types as $type) {
            $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();
            $subArr  = [];

            foreach ($subTypes as $st) {
                $accs = ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->where('type', $type->id)
                    ->where('sub_type', $st->id)
                    ->get();
                $arr  = [];
                $total = 0;

                foreach ($accs as $a) {
                    $bal = Utility::getAccountBalance($a->id, $start, $end);
                    if ($bal != 0) {
                        $arr[] = [
                            'account_id'   => $a->id,
                            'account_code' => $a->code,
                            'account_name' => $a->name,
                            'totalCredit'  => 0,
                            'totalDebit'   => 0,
                            'netAmount'    => $bal,
                        ];
                        $total += $bal;
                    }
                }

                if ($arr) {
                    $arr[] = [
                        'account_id'   => '',
                        'account_code' => '',
                        'account_name' => 'Total ' . $st->name,
                        'totalCredit'  => 0,
                        'totalDebit'   => 0,
                        'netAmount'    => $total,
                    ];
                    $subArr[] = ['subType' => $st->name, 'account' => $arr];
                }
            }
            $chartAccounts[$type->name] = $subArr;
        }

        $filter = [
            'startDateRange' => $start,
            'endDateRange'   => $end,
        ];

        return $view === 'horizontal'
            ? view(VW::RPT . '.balance_sheet_horizontal', compact('filter', 'chartAccounts'))
            : view(VW::RPT . '.balance_sheet', compact('filter', 'chartAccounts'));
    }

    private function _buildLedgerSummaryView(
        Request $request,
        string $acc,
        int|string $creatorId
    ): View {
        $accounts = ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id')
            ->prepend('All', '');
        $start   = $request->start_date ?? date('Y-01-01');
        $end     = $request->end_date ?? date('Y-m-d', strtotime('+1 day'));
        $items   = ChartOfAccount::whereKey(
            $request->account
                ? [$request->account]
                : ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->pluck('id')
                ->all()
        )->get();

        // TODO: implement ledger items aggregation

        $filter = [
            'balance'        => 0,
            'credit'         => 0,
            'debit'          => 0,
            'startDateRange' => $start,
            'endDateRange'   => $end,
        ];

        return view(VW::RPT . '.ledger_summary', compact('filter', 'items', 'accounts'));
    }

    private function _buildTrialBalanceSummaryView(
        Request $request,
        int|string $creatorId
    ): View {
        $start = $request->start_date ?? date('Y-01-01');
        $end  = $request->end_date ?? date('Y-m-d', strtotime('+1 day'));
        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $accounts = [];

        foreach ($types as $type) {
            $t = Utility::trialBalance($type->id, $start, $end);
            $accounts[$type->name][] = $t;
        }

        $totalAccounts = [];
        foreach ($accounts as $cat => $ents) {
            foreach ($ents as $e) {
                $name = &$totalAccounts[$cat][$e['name']];
                $name['id']  = $e['id'];
                $name['code'] = $e['code'];
                $name['name'] = $e['name'];
                $name['totalDebit'] = ($name['totalDebit'] ?? 0)
                    + ($e['totalDebit'] < 0 ? 0 : $e['totalDebit']);
                $name['totalCredit'] = ($name['totalCredit'] ?? 0)
                    + ($e['totalCredit'] + ($e['totalDebit'] < 0 ? -$e['totalDebit'] : 0));
            }
        }

        $filter = ['startDateRange' => $start, 'endDateRange' => $end];

        return view(VW::RPT . '.trial_balance', compact('filter', 'totalAccounts'));
    }

    private function _buildLeaveView(Request $request, int|string $creatorId): View
    {
        $branch    = Branch::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id')
            ->prepend('Select Branch', '');
        $department = Department::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck(CompaniesConstants::COL_DEP_NM, 'id')
            ->prepend('Select Department', '');
        $filterYear = [
            'branch'        => __('All'),
            'department'    => __('All'),
            'type'          => __('Monthly'),
            'dateYearRange' => date('M-Y'),
        ];
        $employees = Employee::where(DC::COL_TABLE_CREATOR, $creatorId);
        if ($request->branch) {
            $employees->where(CompaniesConstants::COL_BRC_ID, $request->branch);
            $filterYear['branch'] = Branch::find($request->branch)?->name ?? '';
        }
        if ($request->department) {
            $employees->where(CompaniesConstants::COL_DEP_ID, $request->department);
            $filterYear['department'] = Department::find($request->department)?->name ?? '';
        }
        $employees = $employees->get();

        $leaves = [];
        $totApp = $totRej = $totPend = 0;
        foreach ($employees as $emp) {
            $app = Leave::where(UC::COL_EMP_ID, $emp->id)
                ->where('status', 'Approved');
            $rej = Leave::where(UC::COL_EMP_ID, $emp->id)
                ->where('status', 'Reject');
            $pend = Leave::where(UC::COL_EMP_ID, $emp->id)
                ->where('status', 'Pending');
            if (($type = $request->type) === 'monthly' && $request->month) {
                $m = date('m', strtotime($request->month));
                $y = date('Y', strtotime($request->month));
                $app->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                $rej->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                $pend->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
            } elseif ($type === 'yearly' && $request->year) {
                $app->whereYear('applied_on', $request->year);
                $rej->whereYear('applied_on', $request->year);
                $pend->whereYear('applied_on', $request->year);
                $filterYear['dateYearRange'] = $request->year;
                $filterYear['type']         = __('Yearly');
            } else {
                $m = date('m');
                $y = date('Y');
                $app->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                $rej->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                $pend->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
            }
            $aCnt = $app->count();
            $rCnt = $rej->count();
            $pCnt = $pend->count();
            $totApp += $aCnt;
            $totRej += $rCnt;
            $totPend += $pCnt;
            $leaves[] = [
                'id'        => $emp->id,
                UC::COL_EMP_ID => $emp->employee_id,
                'employee'  => $emp->name,
                'approved'  => $aCnt,
                'reject'    => $rCnt,
                'pending'   => $pCnt,
            ];
        }
        $filterYear += [
            'starting_year' => date('Y', strtotime('-5 year')),
            'ending_year'   => date('Y', strtotime('+5 year')),
        ];
        $filter = [
            'totalApproved' => $totApp,
            'totalReject'   => $totRej,
            'totalPending'  => $totPend,
        ];
        return view(
            VW::RPT . '.leave',
            compact(
                'department',
                'branch',
                'leaves',
                'filterYear',
                'filter'
            )
        );
    }

    private function _buildEmployeeLeaveView(
        int $employee_id,
        string $status,
        string $type,
        string $month,
        int $year,
        int|string $creatorId
    ): View {
        $leaveTypes = LeaveType::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $leaves    = [];
        foreach ($leaveTypes as $lt) {
            $q = Leave::where(UC::COL_EMP_ID, $employee_id)
                ->where('status', $status)
                ->where('leave_type_id', $lt->id);
            if ($type === 'yearly') {
                $q->whereYear('applied_on', $year);
            } else {
                $m = date('m', strtotime($month));
                $y = date('Y', strtotime($month));
                $q->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
            }
            $leaves[] = (object) [
                'title' => $lt->title,
                'total' => $q->count(),
            ];
        }

        $leaveData = Leave::where(UC::COL_EMP_ID, $employee_id)
            ->where('status', $status);
        if ($type === 'yearly') {
            $leaveData->whereYear('applied_on', $year);
        } else {
            $m = date('m', strtotime($month));
            $y = date('Y', strtotime($month));
            $leaveData->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
        }

        return view(
            VW::RPT . '.leaveShow',
            [
                'leaves'    => $leaves,
                'leaveData' => $leaveData->get(),
            ]
        );
    }

    private function _buildMonthlyAttendanceView(
        Request $request,
        int|string $creatorId
    ): View {
        $branch    = Branch::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $department = Department::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $data      = ['branch' => __('All'), 'department' => __('All')];
        $emps      = Employee::select('id', 'name')
            ->where(DC::COL_TABLE_CREATOR, $creatorId);
        if (!empty($request->employee_id) && $request->employee_id[0] != 0) {
            $emps->whereIn('id', $request->employee_id);
        }
        if ($request->branch) {
            $emps->where(CompaniesConstants::COL_BRC_ID, $request->branch);
            $data['branch'] = Branch::find($request->branch)?->name ?? '';
        }
        if ($request->department) {
            $emps->where(CompaniesConstants::COL_DEP_ID, $request->department);
            $data['department'] = Department::find($request->department)?->name ?? '';
        }
        $emps = $emps->get()->pluck('name', 'id')->all();

        if ($request->month) {
            $cur = strtotime($request->month);
            $m  = date('m', $cur);
            $y  = date('Y', $cur);
            $cm = date('M-Y', $cur);
        } else {
            $m = date('m');
            $y = date('Y');
            $cm = date('M-Y');
        }

        $numDays = date('t', mktime(0, 0, 0, $m, 1, $y));
        $dates  = array_map(
            fn($d) => str_pad($d, 2, '0', STR_PAD_LEFT),
            range(1, $numDays)
        );

        $rows = [];
        $totPresent = $totLeave = 0;
        $oH = $oM = $eH = $eM = $lH = $lM = 0;

        foreach ($emps as $id => $name) {
            $row['name'] = $name;
            $statusMap  = [];
            foreach ($dates as $d) {
                $dt = "$y-$m-$d";
                if ($dt <= date('Y-m-d')) {
                    $att = EmployeeAttendance::where(UC::COL_EMP_ID, $id)
                        ->where('date', $dt)
                        ->first();
                    if ($att && $att->status === 'Present') {
                        $statusMap[$d] = 'P';
                        $totPresent++;
                        $oH += intval(date('h', strtotime($att->overtime)));
                        $oM += intval(date('i', strtotime($att->overtime)));
                        $eH += intval(date('h', strtotime($att->early_leaving)));
                        $eM += intval(date('i', strtotime($att->early_leaving)));
                        $lH += intval(date('h', strtotime($att->late)));
                        $lM += intval(date('i', strtotime($att->late)));
                    } elseif ($att && $att->status === 'Leave') {
                        $statusMap[$d] = 'A';
                        $totLeave++;
                    } else {
                        $statusMap[$d] = '';
                    }
                } else {
                    $statusMap[$d] = '';
                }
            }
            $row['status'] = $statusMap;
            $rows[]       = $row;
        }

        return view(
            VW::RPT . '.monthlyAttendance',
            [
                'employeesAttendance' => $rows,
                'branch'              => $branch,
                'department'          => $department,
                'dates'               => $dates,
                'data'                => [
                    'totalOvertime'   => $oH + ($oM / 60),
                    'totalEarlyLeave' => $eH + ($eM / 60),
                    'totalLate'       => $lH + ($lM / 60),
                    'totalPresent'    => $totPresent,
                    'totalLeave'      => $totLeave,
                    'curMonth'        => $cm,
                    'branch'          => $data['branch'],
                    'department'      => $data['department'],
                ],
            ]
        );
    }

    private function _buildPayrollView(Request $request, int|string $creatorId): View
    {
        $branch    = Branch::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $department = Department::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $emps      = Employee::select('id', UC::COL_NM)
            ->where(DC::COL_TABLE_CREATOR, $creatorId);
        if (!empty($request->employee_id) && $request->employee_id[0] != 0)
            $emps->whereIn('id', $request->employee_id);
        $filterYear = [
            'branch'        => __('All'),
            'department'    => __('All'),
            'type'          => __('Monthly'),
            'dateYearRange' => '',
        ];
        $q = Payslip::select('payslips.*', 'employees.' . UC::COL_NM)
            ->leftJoin('employees', 'payslips.' . UC::COL_EMP_ID, '=', 'employees.id')
            ->where('payslips.' . DC::COL_TABLE_CREATOR, $creatorId);
        if (($t = $request->type) === 'monthly' && $request->month) {
            $q->where('salary_month', $request->month);
            $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
        } elseif ($t === 'yearly' && $request->year) {
            $start = $request->year . '-01';
            $end  = $request->year . '-12';
            $q->whereBetween('salary_month', [$start, $end]);
            $filterYear['dateYearRange'] = $request->year;
            $filterYear['type']         = __('Yearly');
        } else {
            $m  = date('Y-m');
            $q->where('salary_month', $m);
            $filterYear['dateYearRange'] = date('M-Y', strtotime($m));
        }
        if ($request->branch) {
            $q->where('employees.branch_id', $request->branch);
            $filterYear['branch'] = Branch::find($request->branch)?->name ?? '';
        }
        if ($request->department) {
            $q->where('employees.department_id', $request->department);
            $filterYear['department'] = Department::find($request->department)?->name ?? '';
        }
        $empsArr = $emps->get()->pluck('name', 'id')->all();
        $payslips = $q->whereIn('employees.' . UC::COL_NM, $empsArr)
            ->with('employees')
            ->get();
        $totBasic = $totNet = $totAllw = $totCom = $totLoan = 0;
        $totSatDed = $totOther = $totOT = 0;

        foreach ($payslips as $p) {
            $totBasic += $p->gross_salary;
            $totNet   += $p->net_payable;
            foreach (json_decode($p->allowance) as $x) {
                $totAllw += $x->amount;
            }
            foreach (json_decode($p->commission) as $x) {
                $totCom += $x->amount;
            }
            foreach (json_decode($p->loan) as $x) {
                $totLoan += $x->amount;
            }
            foreach (json_decode($p->saturation_deduction) as $x) {
                $totSatDed += $x->amount;
            }
            foreach (json_decode($p->other_payment) as $x) {
                $totOther += $x->amount;
            }
            foreach (json_decode($p->overtime) as $x) {
                $totOT += ($x->rate * $x->hours) * $x->number_of_days;
            }
        }

        $filterData = [
            'totalBasicSalary'           => $totBasic,
            'totalNetSalary'             => $totNet,
            'totalAllowance'             => $totAllw,
            'totalCommision'             => $totCom,
            'totalLoan'                  => $totLoan,
            'totalSaturationDeduction'   => $totSatDed,
            'totalOtherPayment'          => $totOther,
            'totalOverTime'              => $totOT,
        ];
        $filterYear += [
            'starting_year' => date('Y', strtotime('-5 year')),
            'ending_year'   => date('Y', strtotime('+5 year')),
        ];

        return view(
            VW::RPT . '.payroll',
            compact('payslips', 'filterData', 'branch', 'department', 'filterYear')
        );
    }

    private function _buildLeadReport(Request $request, int|string $creatorId)
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd  = Carbon::now()->endOfWeek();
        $period   = CarbonPeriod::create($weekStart, $weekEnd);
        $grouped  = Lead::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get()
            ->groupBy(fn($l) => $l->created_at->format('Y-m-d'));

        $deviceLabels = [];
        $deviceData  = [];
        foreach ($period as $dt) {
            $key = $dt->format('Y-m-d');
            $deviceLabels[] = $dt->format('l');
            $deviceData[]  = $grouped[$key]?->count() ?? 0;
        }

        $sources = Source::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $srcLabels = $sources->pluck('name')->toArray();
        $srcData  = $sources->map(
            fn($s) => Lead::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->where('sources', $s->id)
                ->count()
        )->toArray();

        $start = $request->start_month
            ? strtotime($request->start_month)
            : strtotime(date('Y-01'));
        $end = $request->end_month
            ? strtotime($request->end_month)
            : strtotime(date('Y-12'));

        $labels = [];
        $data  = [];
        for ($cur = $start; $cur <= $end; $cur = strtotime('+1 month', $cur)) {
            $m = date('m', $cur);
            $y = date('Y', $cur);
            $labels[] = date('M Y', $cur);
            $count = Lead::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->whereMonth('date', $request->start_month ? date('m', strtotime($request->start_month)) : $m)
                ->whereYear('date', $y)
                ->count();
            $data[] = $count;
        }

        if ($request->has('start_month')) {
            return response()->json(['data' => $data, 'name' => $labels]);
        }

        $userCounts = [];
        $users = User::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        foreach ($users as $uData) {
            $c = Lead::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->where(UC::COL_USER_ID, $uData->id)
                ->when(
                    $request->From_Date && $request->To_Date,
                    fn($q) => $q->whereBetween('created_at', [
                        Carbon::parse($request->From_Date),
                        Carbon::parse($request->To_Date)
                    ]),
                    fn($q) => $q
                )
                ->count();
            $userCounts['name'][] = $uData->name;
            $userCounts['data'][] = $c;
        }

        $pipeLabels = [];
        $pipeData  = [];
        $pipes = Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        foreach ($pipes as $p) {
            $pipeLabels[] = $p->name;
            $pipeData[]  = Lead::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->where('pipeline_id', $p->id)
                ->count();
        }

        $filter = [
            'startDateRange' => date('M-Y', $start),
            'endDateRange' => date('M-Y', $end)
        ];
        $monthList = $this->yearMonth();

        return view(VW::RPT . '.lead', compact(
            'deviceLabels',
            'deviceData',
            'srcLabels',
            'srcData',
            'labels',
            'data',
            'filter',
            'monthList',
            'userCounts',
            'pipeLabels',
            'pipeData'
        ));
    }

    private function _buildDealReport(Request $request, int|string $creatorId)
    {
        // weekly
        $weekStart = Carbon::now()->startOfWeek();
        $period   = CarbonPeriod::create($weekStart, $weekStart->copy()->endOfWeek());
        $grouped  = Deal::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('created_at', [$weekStart, $weekStart->copy()->endOfWeek()])
            ->get()
            ->groupBy(fn($d) => $d->created_at->format('Y-m-d'));
        $deviceLabels = [];
        $deviceData  = [];
        foreach ($period as $dt) {
            $key = $dt->format('Y-m-d');
            $deviceLabels[] = $dt->format('l');
            $deviceData[]  = $grouped[$key]?->count() ?? 0;
        }
        // source
        $srcs = Source::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $srcLabels = $srcs->pluck('name')->toArray();
        $srcData  = $srcs->map(
            fn($s) => Deal::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->where('sources', $s->id)->count()
        )->toArray();
        // staff
        $userData = [];
        $users = $this->deals();
        foreach ($users as $uData) {
            $userData['name'][] = $uData->name;
            $userData['data'][] = UserDeal::where(UC::COL_USER_ID, $uData->id)
                ->count();
        }
        // client
        $clientData = [];
        $clients = ClientDeal::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('client_id')->unique();
        foreach ($clients as $cid) {
            $name = Customer::find($cid)?->name ?? '';
            $clientData['name'][] = $name;
            $clientData['data'][] = ClientDeal::where('client_id', $cid)
                ->count();
        }
        // monthly
        $start = $request->start_month
            ? strtotime($request->start_month)
            : strtotime(date('Y-01'));
        $end = $request->end_month
            ? strtotime($request->end_month)
            : strtotime(date('Y-12'));
        $labels = [];
        $data  = [];
        for ($cur = $start; $cur <= $end; $cur = strtotime('+1 month', $cur)) {
            $labels[] = date('M Y', $cur);
            $m = date('m', $cur);
            $y = date('Y', $cur);
            $count = Deal::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->count();
            $data[] = $count;
        }
        if ($request->has('start_month')) {
            return response()->json(['data' => $data, 'name' => $labels]);
        }
        $filter = [
            'startDateRange' => date('M-Y', $start),
            'endDateRange' => date('M-Y', $end)
        ];
        $monthList = $this->yearMonth();

        return view(VW::RPT . '.deal', compact(
            'deviceLabels',
            'deviceData',
            'srcLabels',
            'srcData',
            'userData',
            'clientData',
            'labels',
            'data',
            'filter',
            'monthList'
        ));
    }

    private function _renderWarehouse(int $userId): View
    {
        $warehouses     = Warehouse::where(DC::COL_TABLE_CREATOR, $userId)->get();
        $totalWarehouse = $warehouses->count();
        $totalProduct   = WarehouseProduct::where(DC::COL_TABLE_CREATOR, $userId)->count();
        $warehousename  = $warehouses->pluck('name')->all();
        $warehouseCounts = $warehouses
            ->map(
                fn($w) => WarehouseProduct::where(DC::COL_TABLE_CREATOR, $userId)
                    ->where('warehouse_id', $w->id)
                    ->count()
            )
            ->all();

        Log::info(get_class($this) . '::warehouseReport rendered', [
            UC::COL_USER_ID         => $userId,
            'totalWarehouse'  => $totalWarehouse,
            'totalProduct'    => $totalProduct,
        ]);

        return view(VW::RPT . '.warehouse', [
            'warehouse'            => $warehouses,
            'totalWarehouse'       => $totalWarehouse,
            'totalProduct'         => $totalProduct,
            'warehousename'        => $warehousename,
            'warehouseProductData' => $warehouseCounts,
        ]);
    }

    private function _buildPurchaseDaily(Request $request, int|string $creatorId): View
    {
        $start = $request->start_date
            ? $request->start_date
            : now()->subDays(30)->toDateString();
        $end  = $request->end_date
            ? $request->end_date
            : now()->subDay()->toDateString();

        $query = Purchase::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->when(
                $request->warehouse,
                fn($q, $w) => $q->where('warehouse_id', $w),
                fn($q) => $q
            )
            ->when(
                $request->vendor,
                fn($q, $v) => $q->where('vendor_id', $v),
                fn($q) => $q
            )
            ->whereBetween('purchase_date', [$start, $end]);

        $grouped = $query->get()
            ->groupBy(fn($p) => $p->purchase_date->format('Y-m-d'))
            ->map(fn($col) => $col->sum(fn($p) => $p->getTotal()));

        $period = CarbonPeriod::create($start, $end);
        $arrDuration = [];
        $data       = [];
        foreach ($period as $dt) {
            $d = $dt->format('Y-m-d');
            $arrDuration[] = $dt->format('d-M');
            $data[]       = $grouped[$d] ?? 0;
        }

        $filter = [
            'startDate' => $start,
            'endDate' => $end,
            'warehouse' => Branch::find($request->warehouse)?->name ?? '',
            'vendor' => Vendor::find($request->vendor)?->name ?? ''
        ];
        $warehouses = Warehouse::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');
        $vendors   = Vendor::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');

        return view(VW::RPT . '.daily_purchase', compact(
            'warehouses',
            'vendors',
            'arrDuration',
            'data',
            'filter'
        ));
    }

    private function _buildPurchaseMonthly(Request $request, int|string $creatorId): View
    {
        $year = $request->year ?? now()->year;
        $query = Purchase::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->when(
                $request->warehouse,
                fn($q, $w) => $q->where('warehouse_id', $w),
                fn($q) => $q
            )
            ->when(
                $request->vendor,
                fn($q, $v) => $q->where('vendor_id', $v),
                fn($q) => $q
            )
            ->whereYear('purchase_date', $year);

        $grouped = $query->get()
            ->groupBy(fn($p) => $p->purchase_date->format('m'))
            ->map(fn($col) => $col->sum(fn($p) => $p->getTotal()));

        $arrDuration = [];
        $data       = [];
        for ($i = 1; $i <= 12; $i++) {
            $label = date('my', strtotime("$year-$i-01"));
            $arrDuration[] = $label;
            $data[]       = $grouped[str_pad($i, 2, '0', STR_PAD_LEFT)] ?? 0;
        }

        $filter = [
            'startMonth' => "Jan-{$year}",
            'endMonth' => "Dec-{$year}",
            'warehouse' => Branch::find($request->warehouse)?->name ?? '',
            'vendor' => Vendor::find($request->vendor)?->name ?? ''
        ];
        $warehouses = Warehouse::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');
        $vendors   = Vendor::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');

        $monthList = $this->yearMonth();
        $yearList = $this->yearList();

        return view(VW::RPT . '.monthly_purchase', compact(
            'monthList',
            'yearList',
            'warehouses',
            'vendors',
            'arrDuration',
            'data',
            'filter'
        ));
    }

    private function _buildPosDaily(Request $request, int|string $creatorId): View
    {
        $start = $request->start_date
            ? $request->start_date
            : now()->subDays(30)->toDateString();
        $end  = $request->end_date
            ? $request->end_date
            : now()->subDay()->toDateString();

        $query = Pos::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->when(
                $request->warehouse,
                fn($q, $w) => $q->where('warehouse_id', $w),
                fn($q) => $q
            )
            ->when(
                $request->customer,
                fn($q, $c) => $q->where('customer_id', $c),
                fn($q) => $q
            )
            ->whereBetween('pos_date', [$start, $end]);

        $grouped = $query->get()
            ->groupBy(fn($p) => $p->pos_date->format('Y-m-d'))
            ->map(fn($col) => $col->sum(fn($p) => $p->getTotal()));

        $period = CarbonPeriod::create($start, $end);
        $arrDuration = [];
        $data       = [];
        foreach ($period as $dt) {
            $d = $dt->format('Y-m-d');
            $arrDuration[] = $dt->format('d-M');
            $data[]       = $grouped[$d] ?? 0;
        }

        $filter = [
            'startDate' => $start,
            'endDate' => $end,
            'warehouse' => Branch::find($request->warehouse)?->name ?? '',
            'customer' => Customer::find($request->customer)?->name ?? ''
        ];
        $warehouses = Warehouse::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');
        $customers = Customer::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');

        return view(VW::RPT . '.daily_pos', compact(
            'warehouses',
            'customers',
            'arrDuration',
            'data',
            'filter'
        ));
    }

    private function _buildPosMonthly(Request $request, int|string $creatorId): View
    {
        $year = $request->year ?? now()->year;
        $query = Pos::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->when(
                $request->warehouse,
                fn($q, $w) => $q->where('warehouse_id', $w),
                fn($q) => $q
            )
            ->when(
                $request->customer,
                fn($q, $c) => $q->where('customer_id', $c),
                fn($q) => $q
            )
            ->whereYear('pos_date', $year);

        $grouped = $query->get()
            ->groupBy(fn($p) => $p->pos_date->format('m'))
            ->map(fn($col) => $col->sum(fn($p) => $p->getTotal()));

        $arrDuration = [];
        $data       = [];
        for ($i = 1; $i <= 12; $i++) {
            $label = date('my', strtotime("$year-$i-01"));
            $arrDuration[] = $label;
            $data[]       = $grouped[str_pad($i, 2, '0', STR_PAD_LEFT)] ?? 0;
        }

        $filter = [
            'startMonth' => "Jan-{$year}",
            'endMonth' => "Dec-{$year}",
            'warehouse' => Branch::find($request->warehouse)?->name ?? '',
            'customer' => Customer::find($request->customer)?->name ?? ''
        ];
        $warehouses = Warehouse::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');
        $customers = Customer::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');

        $monthList = $this->yearMonth();
        $yearList = $this->yearList();

        return view(VW::RPT . '.monthly_pos', compact(
            'monthList',
            'yearList',
            'warehouses',
            'customers',
            'arrDuration',
            'data',
            'filter'
        ));
    }

    private function _buildPosVsPurchase(Request $request, int|string $creatorId): View
    {
        $year = $request->year ?? now()->year;

        $posTotals = Pos::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('pos_date', $year)
            ->get()
            ->groupBy(fn($p) => $p->pos_date->format('n'))
            ->map(fn($col) => $col->sum(fn($p) => $p->getTotal()));

        $purTotals = Purchase::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereYear('purchase_date', $year)
            ->get()
            ->groupBy(fn($p) => $p->purchase_date->format('n'))
            ->map(fn($col) => $col->sum(fn($p) => $p->getTotal()));

        $posArr = [];
        $purArr = [];
        $profit = [];
        for ($i = 1; $i <= 12; $i++) {
            $pVal = $posTotals[$i] ?? 0;
            $rVal = $purTotals[$i] ?? 0;
            $posArr[] = $pVal;
            $purArr[] = $rVal;
            $profit[] = number_format($pVal - $rVal, 2);
        }

        $filter = [
            'startDateRange' => "Jan-{$year}",
            'endDateRange' => "Dec-{$year}"
        ];

        return view(VW::RPT . '.pos_vs_purchase', compact(
            'filter'
        ), [
            'posTotal' => $posArr,
            'purchaseTotal' => $purArr,
            'profits' => $profit
        ]);
    }

    private function _renderProfitLoss(Request $request, string $view, int|string $creatorId): View
    {
        // parse date range
        $start = $request->start_date ?: now()->startOfYear()->toDateString();
        $end  = $request->end_date   ?: now()->addDay()->toDateString();

        // only three types
        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereIn('name', ['Income', 'Costs of Goods Sold', 'Expenses'])
            ->get();

        $chartAccounts = [];
        foreach ($types as $type) {
            $accounts = ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->where('type', $type->id)
                ->get();

            $rows = [];
            $totals = ['credit' => 0, 'debit' => 0, 'net' => 0];
            foreach ($accounts as $acct) {
                $bal = Utility::getAccountBalance($acct->id, $start, $end);
                if ($bal == 0) continue;
                $rows[] = [
                    'account_id'   => $acct->id,
                    'account_code' => $acct->code,
                    'account_name' => $acct->name,
                    'totalCredit'  => 0,
                    'totalDebit'   => 0,
                    'netAmount'    => $bal,
                ];
                $totals['credit'] += 0;
                $totals['debit']  += 0;
                $totals['net']    += $bal;
            }

            if ($rows) {
                $rows[] = [
                    'account_id'   => '',
                    'account_code' => '',
                    'account_name' => 'Total ' . $type->name,
                    'totalCredit'  => $totals['credit'],
                    'totalDebit'   => $totals['debit'],
                    'netAmount'    => $totals['net'],
                ];
                $chartAccounts[] = [
                    'type'     => $type->name,
                    'accounts' => $rows,
                ];
            }
        }

        $filter = [
            'startDateRange' => $start,
            'endDateRange'   => $end,
        ];

        if ($view === 'horizontal') {
            return view(VW::RPT . '.profit_loss_horizontal', compact('filter', 'chartAccounts'));
        }
        return view(VW::RPT . '.profit_loss', compact('filter', 'chartAccounts'));
    }

    private function _renderMonthlyCashflow(Request $request, int|string $creatorId): View
    {
        $year = $request->year ?: now()->year;

        $sumByMonth = function ($model, string $dateCol, ?int $category = null) use ($creatorId, $year) {
            $q = $model::selectRaw('MONTH(' . $dateCol . ') m, SUM(amount) amt')
                ->where(DC::COL_TABLE_CREATOR, $creatorId)
                ->whereYear($dateCol, $year)
                ->when($category !== null, fn($q) => $q->where('category_id', $category), fn($q) => $q)
                ->groupBy('m')
                ->pluck('amt', 'm')
                ->toArray();
            $result = [];
            for ($i = 1; $i <= 12; $i++) {
                $result[$i] = $q[$i] ?? 0;
            }
            return $result;
        };

        // revenue + invoice
        $revTotals = $sumByMonth(Revenue::class, 'date');
        $invTotals = $sumByMonth(Invoice::class, 'send_date');
        $incomeArr = array_map(fn() => array_sum(func_get_args()), $revTotals, $invTotals);

        // payments + bills
        $payTotals = $sumByMonth(Payment::class, 'date');
        $billTotals = $sumByMonth(Bill::class, 'send_date');
        $expenseArr = array_map(fn() => array_sum(func_get_args()), $payTotals, $billTotals);

        // net profit
        $netArr = [];
        for ($i = 1; $i <= 12; $i++) {
            $netArr[$i] = $incomeArr[$i] - $expenseArr[$i];
        }

        $data = [
            'chartIncomeArr'  => array_values($incomeArr),
            'chartExpenseArr' => array_values($expenseArr),
            'netProfitArray'  => array_values($netArr),
        ];
        $filter = [
            'startDateRange' => "Jan-{$year}",
            'endDateRange'   => "Dec-{$year}",
        ];

        return view(VW::RPT . '.monthly_cashflow', compact('filter') + $data);
    }

    private function _renderQuarterlyCashflow(Request $request, int|string $creatorId): View
    {
        $year = $request->year ?: now()->year;
        $quarters = [
            'Jan-Mar' => [1, 2, 3],
            'Apr-Jun' => [4, 5, 6],
            'Jul-Sep' => [7, 8, 9],
            'Oct-Dec' => [10, 11, 12],
            'Total'   => range(1, 12),
        ];

        // fetch and group function
        $groupByCategory = function ($model, string $dateCol, string $sumCol) use ($creatorId, $year) {
            return $model::selectRaw("category_id, MONTH($dateCol) m, SUM($sumCol) amt")
                ->where(DC::COL_TABLE_CREATOR, $creatorId)
                ->whereYear($dateCol, $year)
                ->groupBy('category_id', 'm')
                ->get()
                ->groupBy('category_id')
                ->map(fn($rows) => $rows->pluck('amt', 'm')->toArray())
                ->toArray();
        };

        $revByCat  = $groupByCategory(Revenue::class, 'date',    'amount');
        $invByCat  = $groupByCategory(Invoice::class, 'send_date', 'getDue()'); // adjust sumCol if needed
        $expByCat  = $groupByCategory(Payment::class, 'date',    'amount');
        $billByCat = $groupByCategory(Bill::class,    'send_date', 'getTotal()');

        $buildArray = function (array $byCat) use ($quarters) {
            $arr   = [];
            $totals = array_fill_keys(array_keys($quarters), 0);
            foreach ($byCat as $cat => $months) {
                $row = ['category' => $cat, 'amounts' => []];
                foreach ($quarters as $label => $ms) {
                    $sum = array_sum(array_map(fn($m) => $months[$m] ?? 0, $ms));
                    $row['amounts'][] = $sum;
                    $totals[$label]  += $sum;
                }
                $arr[] = $row;
            }
            return [$arr, array_values($totals)];
        };

        list($revenueArray, $incomeCatTotals) = $buildArray($revByCat);
        list($invoiceArray, $invoiceCatTotals) = $buildArray($invByCat);
        list($expenseArray, $expenseCatTotals) = $buildArray($expByCat);
        list($billArray,    $billCatTotals)   = $buildArray($billByCat);

        // total income & expense per quarter
        $totalIncome = array_map(fn($i, $j) => $i + $j, $incomeCatTotals, $invoiceCatTotals);
        $totalExpense = array_map(fn($e, $b) => $e + $b, $expenseCatTotals, $billCatTotals);
        $netProfit   = array_map(fn($i, $e) => $i - $e, $totalIncome, $totalExpense);

        $filter = [
            'startDateRange' => "Jan-{$year}",
            'endDateRange'   => "Dec-{$year}",
        ];
        $data = [
            'month'                  => array_keys($quarters),
            'revenueIncomeArray'     => $revenueArray,
            'invoiceIncomeArray'     => $invoiceArray,
            'expenseArray'           => $expenseArray,
            'billExpenseArray'       => $billArray,
            'incomeCatAmount'        => $incomeCatTotals,
            'invoiceIncomeCatAmount' => $invoiceCatTotals,
            'expenseCatAmount'       => $expenseCatTotals,
            'billExpenseCatAmount'   => $billCatTotals,
            'totalIncome'            => $totalIncome,
            'totalExpense'           => $totalExpense,
            'netProfitArray'         => $netProfit,
            'monthList'              => $this->yearMonth(),
            'yearList'               => $this->yearList(),
            'currentYear'            => $year,
        ];

        return view(VW::RPT . '.quarterly_cashflow', compact('filter') + $data);
    }


    private function _doTrialBalanceExport(Request $request, int|string $creatorId): BinaryFileResponse
    {
        $start = $request->start_date ?: now()->startOfMonth()->toDateString();
        $end  = $request->end_date   ?: now()->endOfMonth()->toDateString();

        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $totals = [];

        foreach ($types as $type) {
            $entries = Utility::trialBalance($type->id, $start, $end);
            foreach ($entries as $e) {
                $cat = $type->name;
                $name = $e['name'];
                $totals[$cat][$name]['id']         = $e['id'];
                $totals[$cat][$name]['code']       = $e['code'];
                $totals[$cat][$name]['totalDebit'] = ($totals[$cat][$name]['totalDebit'] ?? 0) + $e['totalDebit'];
                $totals[$cat][$name]['totalCredit'] = ($totals[$cat][$name]['totalCredit'] ?? 0) + $e['totalCredit'];
            }
        }

        $company = User::find($creatorId)->name;
        $filename = "trial_balance_{$start}_{$end}.xlsx";

        ob_end_clean();
        return Excel::download(new TrialBalanceExport($totals, $start, $end, $company), $filename);
    }

    /**
     * @param int    $creatorId
     * @param string $start
     * @param string $end
     * @return array<string, array<string, array{ id:int, code:string, name:string, totalDebit:float, totalCredit:float }>>
     */
    private function buildTrialBalanceData(int|string $creatorId, string $start, string $end): array
    {
        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
        $totalsByType = [];
        foreach ($types as $type) {
            $totalsByType[$type->name] = Utility::trialBalance($type->id, $start, $end);
        }
        $result = [];
        foreach ($totalsByType as $category => $entries) {
            foreach ($entries as $entry) {
                $acctName = $entry['name'];

                // initialize if needed
                if (!isset($result[$category][$acctName])) {
                    $result[$category][$acctName] = [
                        'id'          => $entry['id'],
                        'code'        => $entry['code'],
                        'name'        => $acctName,
                        'totalDebit'  => 0.0,
                        'totalCredit' => 0.0,
                    ];
                }
                $result[$category][$acctName]['totalDebit']  += (float)$entry['totalDebit'];
                $result[$category][$acctName]['totalCredit'] += (float)$entry['totalCredit'];
            }
        }
        return $result;
    }

    private function _doBalanceSheetExport(Request $request, int|string $creatorId): BinaryFileResponse
    {
        $start = $request->start_date ?: now()->startOfMonth()->toDateString();
        $end  = $request->end_date   ?: now()->endOfMonth()->toDateString();

        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereIn('name', ['Assets', 'Liabilities', 'Equity'])
            ->get();

        $structure = [];
        foreach ($types as $type) {
            $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();
            $subs = [];

            foreach ($subTypes as $sub) {
                $accounts = ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->where('type', $type->id)
                    ->where('sub_type', $sub->id)
                    ->get();

                $rows = [];
                foreach ($accounts as $acct) {
                    $bal = Utility::getAccountBalance($acct->id, $start, $end);
                    if ($bal == 0) continue;
                    $rows[] = [
                        'account_no'  => $acct->code,
                        'account_name' => $acct->name,
                        'totalDebit'  => 0,
                        'totalCredit' => 0,
                        'netAmount'   => $bal,
                    ];
                }

                if ($rows) {
                    $subs[] = ['subType' => $sub->name, 'account' => $rows];
                }
            }

            if ($subs) {
                $structure[$type->name] = $subs;
            }
        }

        $company = User::find($creatorId)->name;
        $filename = "balance_sheet_{$start}_{$end}.xlsx";

        ob_end_clean();
        return Excel::download(new BalanceSheetExport($structure, $start, $end, $company), $filename);
    }

    private function _renderBalanceSheetPrint(Request $request, string $view, int|string $creatorId): View
    {
        $start = $request->start_date ?: now()->startOfYear()->toDateString();
        $end  = $request->end_date   ?: now()->addDay()->toDateString();
        $chartAccounts = $this->_doBalanceSheetStructure($creatorId, $start, $end);
        $filter = ['startDateRange' => $start, 'endDateRange' => $end];
        if ($view === 'horizontal')
            return view(VW::RPT . '.balance_sheet_receipt_horizontal', compact('filter', 'chartAccounts'));
        return view(VW::RPT . '.balance_sheet_receipt', compact('filter', 'chartAccounts'));
    }

    private function _doBalanceSheetStructure(int|string $creatorId, string $start, string $end): array
    {
        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereIn('name', ['Assets', 'Liabilities', 'Equity'])
            ->get();

        $structure = [];
        foreach ($types as $type) {
            $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();
            $subs = [];
            foreach ($subTypes as $sub) {
                $accounts = ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->where('type', $type->id)
                    ->where('sub_type', $sub->id)
                    ->get();
                $rows = [];
                foreach ($accounts as $acct) {
                    $bal = Utility::getAccountBalance($acct->id, $start, $end);
                    if ($bal == 0) continue;
                    $rows[] = [
                        'account_id'   => $acct->id,
                        'account_code' => $acct->code,
                        'account_name' => $acct->name,
                        'totalDebit'   => 0,
                        'totalCredit'  => 0,
                        'netAmount'    => $bal,
                    ];
                }
                if ($rows) {
                    $subs[] = ['subType' => $sub->name, 'account' => $rows];
                }
            }
            if ($subs) {
                $structure[$type->name] = $subs;
            }
        }
        return $structure;
    }

    private function _doProfitLossExport(Request $request, int|string $creatorId): BinaryFileResponse
    {
        $start = $request->start_date ?: now()->startOfYear()->toDateString();
        $end  = $request->end_date   ?: now()->addDay()->toDateString();

        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereIn('name', ['Income', 'Costs of Goods Sold', 'Expenses'])
            ->get();

        $structure = [];
        foreach ($types as $type) {
            $accounts = ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->where('type', $type->id)
                ->get();
            $rows = [];
            $totals = ['credit' => 0, 'debit' => 0, 'net' => 0];
            foreach ($accounts as $acct) {
                $bal = Utility::getAccountBalance($acct->id, $start, $end);
                if ($bal == 0) continue;
                $rows[] = [
                    'account_id'   => $acct->id,
                    'account_code' => $acct->code,
                    'account_name' => $acct->name,
                    'totalCredit'  => 0,
                    'totalDebit'   => 0,
                    'netAmount'    => $bal,
                ];
                $totals['net'] += $bal;
            }
            if ($rows) {
                $rows[] = [
                    'account_id' => '',
                    'account_code' => '',
                    'account_name' => "Total {$type->name}",
                    'totalCredit' => $totals['credit'],
                    'totalDebit' => $totals['debit'],
                    'netAmount' => $totals['net'],
                ];
                $structure[] = ['type' => $type->name, 'accounts' => $rows];
            }
        }

        $company = User::find($creatorId)->name;
        $filename = "profit_loss_{$start}_{$end}.xlsx";

        ob_end_clean();
        return Excel::download(new ProfitLossExport($structure, $start, $end, $company), $filename);
    }

    private function _renderProfitLossPrint(Request $request, string $view, int|string $creatorId): View
    {
        $start = $request->start_date ?: now()->startOfYear()->toDateString();
        $end  = $request->end_date   ?: now()->addDay()->toDateString();

        $chartAccounts = $this->_doProfitLossStructure($creatorId, $start, $end);

        $filter = ['startDateRange' => $start, 'endDateRange' => $end];

        if ($view === 'horizontal') {
            return view(VW::RPT . '.profit_loss_receipt_horizontal', compact('filter', 'chartAccounts'));
        }
        return view(VW::RPT . '.profit_loss_receipt', compact('filter', 'chartAccounts'));
    }

    private function _doProfitLossStructure(int|string $creatorId, string $start, string $end): array
    {
        $types = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->whereIn('name', ['Income', 'Costs of Goods Sold', 'Expenses'])
            ->get();

        $structure = [];
        foreach ($types as $type) {
            $accounts = ChartOfAccount::where(DC::COL_TABLE_CREATOR, $creatorId)
                ->where('type', $type->id)
                ->get();
            $rows = [];
            $totals = ['credit' => 0, 'debit' => 0, 'net' => 0];
            foreach ($accounts as $acct) {
                $bal = Utility::getAccountBalance($acct->id, $start, $end);
                if ($bal == 0) continue;
                $rows[] = [
                    'account_id'   => $acct->id,
                    'account_code' => $acct->code,
                    'account_name' => $acct->name,
                    'totalCredit'  => 0,
                    'totalDebit'   => 0,
                    'netAmount'    => $bal,
                ];
                $totals['net'] += $bal;
            }
            if ($rows) {
                $rows[] = [
                    'account_id' => '',
                    'account_code' => '',
                    'account_name' => "Total {$type->name}",
                    'totalCredit' => $totals['credit'],
                    'totalDebit' => $totals['debit'],
                    'netAmount' => $totals['net'],
                ];
                $structure[] = ['type' => $type->name, 'accounts' => $rows];
            }
        }
        return $structure;
    }
}
