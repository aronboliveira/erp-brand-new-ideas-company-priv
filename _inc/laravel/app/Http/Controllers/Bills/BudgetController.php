<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Bill, Budget, Invoice, Payment, ProductServiceCategory, Revenue, Utility};
use App\Traits\ChecksLogin;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Crypt, DB, Log};
use Symfony\Component\HttpFoundation\Response;

final class BudgetController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $req): Response|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '@index start', ['user_ip' => $req->ip()]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        $user = $u;
        if ($resp = $this->deny($req, 'manage budget plan'))
            return $resp;
        try {
            $budgets = Budget::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            Log::info(__CLASS__ . '@index loaded budgets', ['count' => $budgets->count()]);
            return view(ViewsConstants::BDG . '.' . __FUNCTION__, [
                'budgets' => $budgets,
                'periods' => Budget::$period,
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', [
                'exception' => $e->getMessage(),
                UsersConstants::COL_USER_ID   => $user?->id
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $req): Response|JsonResponse
    {
        Log::info(__CLASS__ . '@' . __FUNCTION__ . ' start', ['user_ip' => $req->ip()]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        $user = $u;
        if ($resp = $this->deny($req, 'create budget plan'))
            return $resp;
        try {
            $incomeCats = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->where(UsersConstants::COL_TP, 'income')->get();
            $expenseCats = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->where(UsersConstants::COL_TP, 'expense')->get();
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' rendering form', [
                'income_count'  => $incomeCats->count(),
                'expense_count' => $expenseCats->count()
            ]);
            return view(ViewsConstants::BDG . '.' . __FUNCTION__, [
                'periods'               => Budget::$period,
                'incomeproduct'         => $incomeCats,
                'expenseproduct'        => $expenseCats,
                'monthList'             => self::_months(),
                'quarterly_monthlist'   => ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Oct-Dec'],
                'half_yearly_monthlist' => ['Jan-Jun', 'Jul-Dec'],
                'yearly_monthlist'      => ['Jan-Dec'],
                'yearList'              => self::_years(),
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', ['exception' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $req): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '@store start', ['input' => $req->only(['name', 'year', 'period'])]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        $user = $u;
        if ($resp = $this->deny($req, 'create budget plan'))
            return $resp;
        $req->validate([
            'name'   => 'required',
            'period' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $budget = Budget::create([
                'name'         => $req->name,
                'from'         => $req->year,
                'period'       => $req->period,
                'income_data'  => json_encode($req->income),
                'expense_data' => json_encode($req->expense),
                DatabaseConstants::TABLE_CREATOR   => $user?->creatorId(),
            ]);
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' created budget', ['budget_id' => $budget->id]);
            Utility::notifyNewBudget($budget);
            DB::commit();
            return redirect()->route(ViewsConstants::BDG . '.index')
                ->with('success', __('Budget Plan successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' failed', [
                'exception' => $e->getMessage(),
                'input'    => $req->all()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(string $enc): Response|RedirectResponse
    {
        Log::info(__CLASS__ . '@show start', ['enc' => $enc]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        $user = $u;
        if ($resp = $this->deny(request(), 'view budget plan'))
            return $resp;
        try {
            $id    = Crypt::decryptString($enc);
            $budget = Budget::findOrFail($id);
            if (!$this->isOwner($budget, $user)) {
                Log::warning(__CLASS__ . '@' . __FUNCTION__ . ' ownership failed', [
                    'budget_id' => $id, UsersConstants::COL_USER_ID => $user?->id
                ]);
                return defaultPermissionDenial(
                    request(),
                    new \Exception('owner'),
                    __CLASS__ . '::' . __FUNCTION__
                );
            }
            $reports = $this->_buildReports($budget);
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' built reports', ['budget_id' => $id]);
            return view(ViewsConstants::BDG . '.' . __FUNCTION__, array_merge($reports, [
                'id'             => $id,
                'budget'         => $budget,
                'incomeproduct'  => $reports['incomeproduct'],
                'expenseproduct' => $reports['expenseproduct'],
            ]));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', ['exception' => $e->getMessage()]);
            return defaultUndefinedException(request(), $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(string $enc): Response|RedirectResponse
    {
        Log::info(__CLASS__ . '@' . __FUNCTION__ . ' start', ['enc' => $enc]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) {
            return $u;
        }
        $user = $u;

        if ($resp = $this->deny(request(), 'edit budget plan')) {
            return $resp;
        }

        try {
            $id    = Crypt::decryptString($enc);
            $budget = Budget::findOrFail($id);

            if (!$this->isOwner($budget, $user)) {
                Log::warning(__CLASS__ . '@' . __FUNCTION__ . ' ownership failed', ['budget_id' => $id]);
                return defaultPermissionDenial(
                    request(),
                    new \Exception('owner'),
                    __CLASS__ . '::' . __FUNCTION__
                );
            }

            $budget->income_data = json_decode($budget->income_data, true);
            $budget->expense_data = json_decode($budget->expense_data, true);

            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' rendering form', ['budget_id' => $id]);

            return view(ViewsConstants::BDG . '.edit', [
                'periods'               => Budget::$period,
                'budget'                => $budget,
                'incomeproduct'         => ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->where(UsersConstants::COL_TP, 'income')->get(),
                'expenseproduct'        => ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->where(UsersConstants::COL_TP, 'expense')->get(),
                'monthList'             => self::_months(),
                'quarterly_monthlist'   => ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Oct-Dec'],
                'half_yearly_monthlist' => ['Jan-Jun', 'Jul-Dec'],
                'yearly_monthlist'      => ['Jan-Dec'],
                'yearList'              => self::_years(),
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' error', ['exception' => $e->getMessage()]);
            return defaultUndefinedException(request(), $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $req, Budget $budget): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '@' . __FUNCTION__ . ' start', ['budget_id' => $budget->id]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        $user = $u;
        if ($resp = $this->deny($req, 'edit budget plan'))
            return $resp;
        if (!$this->isOwner($budget, $user)) {
            Log::warning(__CLASS__ . '@' . __FUNCTION__ . ' ownership failed', ['budget_id' => $budget->id]);
            return defaultPermissionDenial($req, new \Exception('owner'), __CLASS__ . '::' . __FUNCTION__);
        }
        $req->validate([
            'name'   => 'required',
            'period' => 'required',
        ]);
        try {
            $budget->update([
                'name'         => $req->name,
                'from'         => $req->year,
                'period'       => $req->period,
                'income_data'  => json_encode($req->income),
                'expense_data' => json_encode($req->expense),
            ]);
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' success', ['budget_id' => $budget->id]);
            return redirect()->route(ViewsConstants::BDG . '.index')
                ->with('success', __('Budget Plan successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' failed', ['exception' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Budget $budget): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '@' . __FUNCTION__ . ' start', ['budget_id' => $budget->id]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        $req = request();
        $user = $u;
        if ($resp = $this->deny($req, 'delete budget plan'))
            return $resp;
        if (!$this->isOwner($budget, $user)) {
            Log::warning(__CLASS__ . '@' . __FUNCTION__ . ' ownership failed', ['budget_id' => $budget->id]);
            return defaultPermissionDenial($req, new \Exception('owner'), __CLASS__ . '::' . __FUNCTION__);
        }
        try {
            $budget->delete();
            Log::info(__CLASS__ . '@' . __FUNCTION__ . ' success', ['budget_id' => $budget->id]);
            return redirect()->route(ViewsConstants::BDG . '.index')
                ->with('success', __('Budget Plan successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '@' . __FUNCTION__ . ' failed', ['exception' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function yearMonth(Request $request): array
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id()]);
        if (
            (self::_checkLogin()) instanceof RedirectResponse
        ) return [];
        return [
            'January', 'February', 'March', 'April',
            'May', 'June', 'July', 'August',
            'September', 'October', 'November', 'December'
        ];
    }

    public function yearList(Request $request): array
    {
        Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id()]);
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
                UsersConstants::COL_USER_ID   => $req->user()->id,
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
        return $model[DatabaseConstants::TABLE_CREATOR] === $user?->creatorId();
    }

    private static function _months(): array
    {
        return [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
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
        $creatorId = $budget[DatabaseConstants::TABLE_CREATOR];
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
        $incomeCats = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->where('type', 'income')->get();
        $expenseCats = ProductServiceCategory::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
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
                $rev = Revenue::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', '>=', $start)
                    ->whereMonth('date', '<=', $end)
                    ->sum('amount');
                // 5b) invoices
                $invTotal = 0;
                Invoice::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('send_date', $year)
                    ->whereMonth('send_date', '>=', $start)
                    ->whereMonth('send_date', '<=', $end)
                    ->get()
                    ->each(fn ($inv) => $invTotal += $inv->getTotal());

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
                $pay = Payment::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', '>=', $start)
                    ->whereMonth('date', '<=', $end)
                    ->sum('amount');
                // 6b) bills
                $billTotal = 0;
                Bill::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    ->where('category_id', $cat->id)
                    ->whereYear('send_date', $year)
                    ->whereMonth('send_date', '>=', $start)
                    ->whereMonth('send_date', '<=', $end)
                    ->get()
                    ->each(fn ($b) => $billTotal += $b->getTotal());
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