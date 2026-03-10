<?php

namespace App\Http\Controllers\Bills;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MiddlewaresConstants as MWC,
    UsersConstants as UC,
    ViewsConstants as VW
};
use App\Models\{Bill, Budget, Invoice, Payment, ProductServiceCategory, Revenue, Utility};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Crypt, DB, Log, Route, View as ViewFacade};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class BudgetController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    public function __construct()
    {
        $this->middleware([MWC::AUTH]);
    }

    public function index(Request $req): View|Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::BDG . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['user_ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($g = self::guard($req, 'manage budget plan')) !== true) return $g;
            try {
                $fetchStart = microtime(true);
                $budgets = Budget::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchBudgets');
                Log::info("[{$base}::{$action}] loaded budgets", ['count' => $budgets->count()]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, ['budgets' => $budgets, 'periods' => Budget::$frequency]);
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), UC::COL_USER_ID => $user?->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $req): View|Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::BDG . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['user_ip' => $req->ip(), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($g = self::guard($req, 'create budget plan')) !== true) return $g;
            try {
                $incStart = microtime(true);
                $incomeCats = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->where(UC::COL_TP, 'income')->get();
                $this->logExecutionTime($incStart, $action, 'fetchIncomeCategories');
                $expStart = microtime(true);
                $expenseCats = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->where(UC::COL_TP, 'expense')->get();
                $this->logExecutionTime($expStart, $action, 'fetchExpenseCategories');
                Log::info("[{$base}::{$action}] rendering form", ['income_count' => $incomeCats->count(), 'expense_count' => $expenseCats->count()]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, [
                    'periods' => Budget::$frequency,
                    'incomeproduct' => $incomeCats,
                    'expenseproduct' => $expenseCats,
                    'monthList' => self::_months(),
                    'quarterly_monthlist' => ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Oct-Dec'],
                    'half_yearly_monthlist' => ['Jan-Jun', 'Jul-Dec'],
                    'yearly_monthlist' => ['Jan-Dec'],
                    'yearList' => self::_years(),
                ]);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $req): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['input' => $req->only(['name', 'year', 'period']), 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($g = self::guard($req, 'create budget plan')) !== true) return $g;
            $valStart = microtime(true);
            $req->validate(['name' => 'required', 'period' => 'required']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            DB::beginTransaction();
            try {
                $createStart = microtime(true);
                $budget = Budget::create([
                    'name' => $req->name,
                    'from' => $req->year,
                    'period' => $req->period,
                    'income_data' => json_encode(!empty($req->income) ? $req->income : []),
                    'expense_data' => json_encode(!empty($req->expense) ? $req->expense : []),
                    DC::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);
                $this->logExecutionTime($createStart, $action, 'createBudget');
                Log::info("[{$base}::{$action}] created budget", ['budget_id' => $budget->id]);
                $notifyStart = microtime(true);
                Utility::notifyNewBudget($budget);
                $this->logExecutionTime($notifyStart, $action, 'notifyNewBudget');
                $txnEndStart = microtime(true);
                DB::commit();
                $this->logExecutionTime($txnEndStart, $action, 'commitTransaction');
                return redirect()->route(VW::BDG . '.index')->with('success', __('Budget Plan successfully created.'));
            } catch (\Throwable $e) {
                $rbStart = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($rbStart, $action, 'rollbackTransaction');
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'input' => $req->all()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(string $enc): View|Response|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::BDG . '.show';
        return $this->measureProfile($action, function () use ($enc, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['enc' => $enc, 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($g = self::guard(request(), 'view budget plan')) !== true) return $g;
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($enc);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $findStart = microtime(true);
                $budget = Budget::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findBudget');
                if (!$this->isOwner($budget, $user)) {
                    Log::warning("[{$base}::{$action}] ownership failed", ['budget_id' => $id, UC::COL_USER_ID => $user?->id]);
                    return defaultPermissionDenial(request(), new \Exception('owner'), $class . '::' . $action);
                }
                $repStart = microtime(true);
                $reports = $this->_buildReports($budget);
                $this->logExecutionTime($repStart, $action, 'buildReports');
                Log::info("[{$base}::{$action}] reports built", ['budget_id' => $id]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, array_merge($reports, ['id' => $id, 'budget' => $budget, 'incomeproduct' => $reports['incomeproduct'], 'expenseproduct' => $reports['expenseproduct']]));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException(request(), $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc' => $enc]);
    }

    public function edit(string $enc): View|Response|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        $viewPath = VW::BDG . '.edit';
        return $this->measureProfile($action, function () use ($enc, $req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['enc' => $enc, 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($g = self::guard($req, 'edit budget plan')) !== true) return $g;
            try {
                $decStart = microtime(true);
                $id = Crypt::decryptString($enc);
                $this->logExecutionTime($decStart, $action, 'decryptId');
                $findStart = microtime(true);
                $budget = Budget::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findBudget');
                if (!$this->isOwner($budget, $user)) {
                    Log::warning("[{$base}::{$action}] ownership failed", ['budget_id' => $id, UC::COL_USER_ID => $user?->id]);
                    return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action);
                }
                $decodeStart = microtime(true);
                $budget->income_data = json_decode($budget->income_data, true);
                $budget->expense_data = json_decode($budget->expense_data, true);
                $this->logExecutionTime($decodeStart, $action, 'decodeBudgetData');
                $incStart = microtime(true);
                $incomeCats = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->where(UC::COL_TP, 'income')->get();
                $this->logExecutionTime($incStart, $action, 'fetchIncomeCategories');
                $expStart = microtime(true);
                $expenseCats = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->where(UC::COL_TP, 'expense')->get();
                $this->logExecutionTime($expStart, $action, 'fetchExpenseCategories');
                Log::info("[{$base}::{$action}] rendering form", ['budget_id' => $id, 'income_count' => $incomeCats->count(), 'expense_count' => $expenseCats->count()]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, [
                    'periods' => Budget::$frequency,
                    'budget' => $budget,
                    'incomeproduct' => $incomeCats,
                    'expenseproduct' => $expenseCats,
                    'monthList' => self::_months(),
                    'quarterly_monthlist' => ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Oct-Dec'],
                    'half_yearly_monthlist' => ['Jan-Jun', 'Jul-Dec'],
                    'yearly_monthlist' => ['Jan-Dec'],
                    'yearList' => self::_years(),
                ]);
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'enc' => $enc]);
    }

    public function update(Request $req, Budget $budget): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $budget, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['budget_id' => $budget->id, 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($g = self::guard($req, 'edit budget plan')) !== true) return $g;
            if (!$this->isOwner($budget, $user)) {
                Log::warning("[{$base}::{$action}] ownership failed", ['budget_id' => $budget->id, UC::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action);
            }
            $valStart = microtime(true);
            $req->validate(['name' => 'required', 'period' => 'required']);
            $this->logExecutionTime($valStart, $action, 'validateInput');
            try {
                $updStart = microtime(true);
                $budget->update([
                    'name' => $req->name,
                    'from' => $req->year,
                    'period' => $req->period,
                    'income_data' => json_encode(!empty($req->income) ? $req->income : []),
                    'expense_data' => json_encode(!empty($req->expense) ? $req->expense : []),
                ]);
                $this->logExecutionTime($updStart, $action, 'updateBudget');
                Log::info("[{$base}::{$action}] success", ['budget_id' => $budget->id]);
                return redirect()->route(VW::BDG . '.index')->with('success', __('Budget Plan successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'budget_id' => $budget->id]);
    }

    public function destroy(Budget $budget): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = request();
        return $this->measureProfile($action, function () use ($budget, $req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['budget_id' => $budget->id, 'method' => $method]);
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = $u;
            if (($g = self::guard($req, 'delete budget plan')) !== true) return $g;
            if (!$this->isOwner($budget, $user)) {
                Log::warning("[{$base}::{$action}] ownership failed", ['budget_id' => $budget->id, UC::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action);
            }
            try {
                $delStart = microtime(true);
                $budget->delete();
                $this->logExecutionTime($delStart, $action, 'deleteBudget');
                Log::info("[{$base}::{$action}] success", ['budget_id' => $budget->id]);
                return redirect()->route(VW::BDG . '.index')->with('success', __('Budget Plan successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'budget_id' => $budget->id]);
    }

    public const Y_M = 'yearMonth';
    public function yearMonth(Request $request): array
    {
        Log::info(__METHOD__, [UC::COL_USER_ID => Auth::id()]);
        if (
            (self::_checkLogin()) instanceof RedirectResponse
        ) return [];
        return [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];
    }

    public const Y_L = 'yearList';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function yearList(Request $request): array
    {
        Log::info(__METHOD__, [UC::COL_USER_ID => Auth::id()]);
        if (
            (self::_checkLogin()) instanceof RedirectResponse
        ) return [];
        $end  = (int) now()->format('Y');
        $start = $end - 5;
        return array_combine(
            range($end, $start),
            range($end, $start)
        );
    }

    private function deny(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        if (!$req->user()->can($perm)) {
            Log::warning('Permission denied', [
                UC::COL_USER_ID   => $req->user()->id,
                'permission' => $perm,
                'method'    => __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
            ]);
            return defaultPermissionDenial(
                $req,
                new \Illuminate\Auth\Access\AuthorizationException($perm),
                __CLASS__ . '::' . __FUNCTION__
            );
        }
        return null;
    }

    private function isOwner(object $model, object $user): bool
    {
        return $model[DC::COL_TABLE_CREATOR] === $user?->creatorId();
    }

    private static function _months(): array
    {
        return [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
    }

    private static function _years(): array
    {
        $end  = (int) now()->format('Y');
        $start = $end - 5;
        return array_combine(
            range($end, $start),
            range($end, $start)
        );
    }

    private function _buildReports(Budget $budget): array
    {
        $year  = $budget->from ?: now()->year;
        $creatorId = $budget[DC::COL_TABLE_CREATOR];
        // 1) Common labels for all views:
        $common = [
            'monthList'             => self::_months(),
            'quarterly_monthlist'   => ['1-3' => 'Jan-Mar', '4-6' => 'Apr-Jun', '7-9' => 'Jul-Sep', '10-12' => 'Oct-Dec'],
            'half_yearly_monthlist' => ['1-6' => 'Jan-Jun', '7-12' => 'Jul-Dec'],
            'yearly_monthlist'      => ['1-12' => 'Jan-Dec'],
            'yearList'              => self::_years(),
            'currentYear'           => $year,
        ];
        // 2) Categories
        $incomeCats = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('type', 'income')->get();
        $expenseCats = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where('type', 'expense')->get();
        // 3) Budget totals from stored JSON
        $incomeData = json_decode($budget->income_data,  true) ?? [];
        $expenseData = json_decode($budget->expense_data, true) ?? [];
        $budgetTotal       = [];
        $budgetExpenseTotal = [];
        foreach ($incomeData as $catAmounts)
            foreach ($catAmounts as $label => $amt)
                $budgetTotal[$label] = ($budgetTotal[$label] ?? 0) + $amt;
        foreach ($expenseData as $catAmounts)
            foreach ($catAmounts as $label => $amt)
                $budgetExpenseTotal[$label] = ($budgetExpenseTotal[$label] ?? 0) + $amt;
        // 4) Build the “durations” array based on period
        if ($budget->period === 'monthly') {
            $durations = [];
            foreach ($common['monthList'] as $idx => $label)
                $durations[$idx + 1] = $label;
        } elseif ($budget->period === 'quarterly')
            $durations = $common['quarterly_monthlist'];
        elseif ($budget->period === 'half-yearly')
            $durations = $common['half_yearly_monthlist'];
        else
            $durations = $common['yearly_monthlist'];
        // 5) Actual income and its totals
        $incomeArr     = [];
        $incomeTotalArr = [];
        foreach ($incomeCats as $cat) {
            $row   = [];
            foreach ($durations as $key => $label) {
                // determine month range
                if ($budget->period === 'monthly')
                    $start = $end = (int) $key;
                else
                    [$start, $end] = explode('-', $key);
                // 5a) revenue
                $rev = Revenue::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', '>=', $start)
                    ->whereMonth('date', '<=', $end)
                    ->sum('amount');
                // 5b) invoices
                $invTotal = 0;
                Invoice::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('send_date', $year)
                    ->whereMonth('send_date', '>=', $start)
                    ->whereMonth('send_date', '<=', $end)
                    ->get()
                    ->each(fn($inv) => $invTotal += $inv->getTotal());

                $val = $rev + $invTotal;
                $row[$label] = $val;
                $incomeTotalArr[$label] = ($incomeTotalArr[$label] ?? 0) + $val;
            }
            $incomeArr[$cat->id] = $row;
        }
        // 6) Actual expense and its totals
        $expenseArr     = [];
        $expenseTotalArr = [];
        foreach ($expenseCats as $cat) {
            $row   = [];
            foreach ($durations as $key => $label) {
                if ($budget->period === 'monthly')
                    $start = $end = (int) $key;
                else
                    [$start, $end] = explode('-', $key);
                // 6a) payments
                $pay = Payment::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', '>=', $start)
                    ->whereMonth('date', '<=', $end)
                    ->sum('amount');
                // 6b) bills
                $billTotal = 0;
                Bill::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('send_date', $year)
                    ->whereMonth('send_date', '>=', $start)
                    ->whereMonth('send_date', '<=', $end)
                    ->get()
                    ->each(fn($b) => $billTotal += $b->getTotal());
                $val = $pay + $billTotal;
                $row[$label] = $val;
                $expenseTotalArr[$label] = ($expenseTotalArr[$label] ?? 0) + $val;
            }
            $expenseArr[$cat->id] = $row;
        }
        // 7) Profit series
        $budgetProfit = [];
        foreach (array_keys($budgetTotal + $budgetExpenseTotal) as $label)
            $budgetProfit[$label] = ($budgetTotal[$label] ?? 0) - ($budgetExpenseTotal[$label] ?? 0);
        $actualProfit = [];
        foreach (array_keys($incomeTotalArr + $expenseTotalArr) as $label)
            $actualProfit[$label] = ($incomeTotalArr[$label] ?? 0) - ($expenseTotalArr[$label] ?? 0);
        // 8) Merge & return
        return array_merge(
            $common,
            compact(
                'incomeCats',
                'expenseCats',
                'incomeArr',
                'expenseArr',
                'incomeTotalArr',
                'expenseTotalArr',
                'budgetTotal',
                'budgetExpenseTotal'
            ),
            [
                'incomeproduct'  => $incomeCats,
                'expenseproduct' => $expenseCats,
                'budgetprofit'   => $budgetProfit,
                'actualprofit'   => $actualProfit,
            ]
        );
    }
}

// ! ALERT billNumber()‑style sequential IDs break with UUIDs; ensure your migration sets bill_id to UUID or uses a separate sequence table.