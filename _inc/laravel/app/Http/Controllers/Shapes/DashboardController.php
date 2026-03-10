<?php

namespace App\Http\Controllers\Shapes;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC,
    ViewsConstants as VW
};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{
    Announcement,
    BalanceSheet,
    BankAccount,
    Bill,
    Bug,
    BugStatus,
    Contract,
    Deal,
    DealTask,
    Employee,
    EmployeeAttendance,
    Event,
    Expense,
    Goal,
    Invoice,
    Job,
    LandingPageSection,
    Lead,
    LeadStage,
    Meeting,
    Order,
    Payees,
    Payer,
    Payment,
    Plan,
    Pos,
    ProductServiceCategory,
    ProductServiceUnit,
    Project,
    ProjectTask,
    Purchase,
    Revenue,
    Stage,
    Tax,
    Ticket,
    Timesheet,
    TimeTracker,
    Trainer,
    Training,
    User,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Auth, Cache, DB, Log, Redirect, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\LandingPage\Config\Constants\{
    ExtendingLandingPageLayoutConstants as E,
    RoutesResourcesConstants as R
};
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
final class DashboardController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    public const ENTITY = 'dashboard';
    private const REDIRECT_INDEX = '/';
    private const ACCOUNT_DASHBOARD_ROUTE = self::ENTITY . '.account';
    private const CLIENT_DASHBOARD_ROUTE = PMC::CL . '.' . self::ENTITY . '.view';
    /** Cache TTL in seconds — 2 minutes for most dashboard data */
    private const CACHE_TTL = 120;
    /** Cache TTL for less dynamic data — 5 minutes */
    private const CACHE_TTL_LONG = 300;

    public function __construct()
    {
        Log::debug('Constructing ' . __CLASS__ . '...');
    }

    public const ACC_DSB_IDX = 'accountDashboardIndex';
    public function accountDashboardIndex(Request $req): View|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            try {
                $startOverall = microtime(true);
                $output = new ConsoleOutput();
                $ctx = ['ip' => $req->ip() ?? 'unknown_ip', 'referrer' => Utility::getReferrer($req) ?? 'no_referrer', 'uri' => $req->getRequestUri() ?? 'unknown_uri', 'route' => $req->route()?->getName() ?? '#UNIDENTIFIED', 'controller_method' => $action];
                Log::info("{$action} called", $ctx);
                $output->writeln("\n<question>Calling Dashboard::index</question>\n");
                try {
                    $userOrRedirect = self::_checkLogin();
                } catch (\Throwable $e) {
                    Log::error("[$action] login check exception", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()] + $ctx);

                    return redirect()->route(self::REDIRECT_INDEX)->with('error', 'Login validation failed');
                }
                $this->logExecutionTime($startOverall, "{$action} loginCheck", 'completed');
                if (!($userOrRedirect instanceof User)) {
                    Log::warning("[$action] Login check failed – redirecting", $ctx);
                    $output->writeln("\n<comment>User not authenticated. Handling Landing.</comment>\n");
                    return $this->handleLandingOrInstall($req);
                }
                $user = $userOrRedirect;
                Log::info("[$action] start", ['user_id' => $user->id ?? 'undefined', 'user_type' => $user[UC::COL_TP] ?? 'unknown']);
                $startGuard = microtime(true);
                if (!self::guard($req, PMC::SHW_ACC_DSB, self::REDIRECT_INDEX) && $user[UC::COL_TP] === PMC::SA) Log::notice("[$action] super admin bypass", ['user_id' => $user->id ?? 'undefined']);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                if (in_array($user[UC::COL_TP] ?? '', [PMC::CL], true)) return Log::info("[$action] Client user – redirecting", ['user_id' => $user->id ?? 'undefined']) or redirect()->route(self::CLIENT_DASHBOARD_ROUTE);
                $data = ['latestIncome' => collect(), 'latestExpense' => collect(), 'incomeCategoryColor' => [], 'incomeCategory' => [], 'incomeCatAmount' => [], 'expenseCategoryColor' => [], 'expenseCategory' => [], 'expenseCatAmount' => [], 'incExpBarChartData' => [], 'incExpLineChartData' => [], 'currentYear' => now()->year, 'currentMonth' => now()->format('M'), 'constant' => [], 'bankAccountDetail' => collect(), 'recentInvoice' => collect(), 'weeklyInvoice' => [], 'monthlyInvoice' => [], 'recentBill' => collect(), 'weeklyBill' => [], 'monthlyBill' => [], 'goals' => collect(), DC::TABLE_USERS => null, 'plan' => null, 'storage_limit' => SC::MAX_SL_LIMIT_MB];
                $creatorId = 0;
                $startCreator = microtime(true);
                try {
                    $creatorId = $user->creatorId() ?? 0;
                } catch (\Throwable $e) {
                    Log::error("[$action] failed to get creatorId", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()] + $ctx);
                }
                $this->logExecutionTime($startCreator, "{$action} getCreatorId", 'completed');
                $startIncome = microtime(true);
                try {
                    $data['latestIncome'] = Cache::remember("dsb.latest_income.{$creatorId}", self::CACHE_TTL, fn() => Revenue::latest()->where(DC::COL_TABLE_CREATOR, $creatorId)->limit(5)->get());
                } catch (\Throwable $e) {
                    Log::error("[$action] failed latestIncome", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startIncome, "{$action} fetchLatestIncome", 'completed');
                $startExpense = microtime(true);
                try {
                    $data['latestExpense'] = Cache::remember("dsb.latest_expense.{$creatorId}", self::CACHE_TTL, fn() => Payment::latest()->where(DC::COL_TABLE_CREATOR, $creatorId)->limit(5)->get());
                } catch (\Throwable $e) {
                    Log::error("[$action] failed latestExpense", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startExpense, "{$action} fetchLatestExpense", 'completed');
                $startIncChart = microtime(true);
                try {
                    [$data['incomeCategoryColor'], $data['incomeCategory'], $data['incomeCatAmount']] = $this->buildCategoryChart('income', $creatorId);
                } catch (\Throwable $e) {
                    Log::error("[$action] failed buildIncomeChart", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startIncChart, "{$action} buildIncomeChart", 'completed');
                $startExpChart = microtime(true);
                try {
                    [$data['expenseCategoryColor'], $data['expenseCategory'], $data['expenseCatAmount']] = $this->buildCategoryChart('expense', $creatorId);
                } catch (\Throwable $e) {
                    Log::error("[$action] failed buildExpenseChart", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startExpChart, "{$action} buildExpenseChart", 'completed');
                $startBar = microtime(true);
                try {
                    $data['incExpBarChartData'] = $user->getIncExpBarChartData() ?? [];
                } catch (\Throwable $e) {
                    Log::error("[$action] failed barChartData", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startBar, "{$action} barChartData", 'completed');
                $startLine = microtime(true);
                try {
                    $data['incExpLineChartData'] = $user->getIncExpLineChartDate() ?? [];
                } catch (\Throwable $e) {
                    Log::error("[$action] failed lineChartData", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startLine, "{$action} lineChartData", 'completed');
                $startConst = microtime(true);
                try {
                    $data['constant'] = $this->loadConstants($creatorId) ?? [];
                } catch (\Throwable $e) {
                    Log::error("[$action] failed loadConstants", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startConst, "{$action} loadConstants", 'completed');
                $startBank = microtime(true);
                try {
                    $data['bankAccountDetail'] = Cache::remember("dsb.bank_accounts.{$creatorId}", self::CACHE_TTL_LONG, fn() => BankAccount::where(DC::COL_TABLE_CREATOR, $creatorId)->get());
                } catch (\Throwable $e) {
                    Log::error("[$action] failed bankAccountDetail", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startBank, "{$action} fetchBankAccount", 'completed');
                $startInv = microtime(true);
                try {
                    $data['recentInvoice'] = Cache::remember("dsb.recent_invoice.{$creatorId}", self::CACHE_TTL, fn() => Invoice::latest()->where(DC::COL_TABLE_CREATOR, $creatorId)->limit(5)->get());
                } catch (\Throwable $e) {
                    Log::error("[$action] failed recentInvoice", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startInv, "{$action} fetchRecentInvoice", 'completed');
                $startWkInv = microtime(true);
                try {
                    $data['weeklyInvoice'] = $user->weeklyInvoice() ?? [];
                } catch (\Throwable $e) {
                    Log::error("[$action] failed weeklyInvoice", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startWkInv, "{$action} weeklyInvoice", 'completed');
                $startMthInv = microtime(true);
                try {
                    $data['monthlyInvoice'] = $user->monthlyInvoice() ?? [];
                } catch (\Throwable $e) {
                    Log::error("[$action] failed monthlyInvoice", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startMthInv, "{$action} monthlyInvoice", 'completed');
                $startBill = microtime(true);
                try {
                    $data['recentBill'] = Cache::remember("dsb.recent_bill.{$creatorId}", self::CACHE_TTL, fn() => Bill::latest()->where(DC::COL_TABLE_CREATOR, $creatorId)->limit(5)->get());
                } catch (\Throwable $e) {
                    Log::error("[$action] failed recentBill", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startBill, "{$action} fetchRecentBill", 'completed');
                $startWkBill = microtime(true);
                try {
                    $data['weeklyBill'] = $user->weeklyBill() ?? [];
                } catch (\Throwable $e) {
                    Log::error("[$action] failed weeklyBill", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startWkBill, "{$action} weeklyBill", 'completed');
                $startMthBill = microtime(true);
                try {
                    $data['monthlyBill'] = $user->monthlyBill() ?? [];
                } catch (\Throwable $e) {
                    Log::error("[$action] failed monthlyBill", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startMthBill, "{$action} monthlyBill", 'completed');
                $startGoals = microtime(true);
                try {
                    $data['goals'] = Cache::remember("dsb.goals.{$creatorId}", self::CACHE_TTL_LONG, fn() => Goal::where(DC::COL_TABLE_CREATOR, $creatorId)->where('is_display', 1)->get());
                } catch (\Throwable $e) {
                    Log::error("[$action] failed fetchGoals", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startGoals, "{$action} fetchGoals", 'completed');
                $startUserRec = microtime(true);
                try {
                    $data[DC::TABLE_USERS] = User::find($creatorId);
                } catch (\Throwable $e) {
                    Log::error("[$action] failed fetchUserRecord", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startUserRec, "{$action} fetchUserRecord", 'completed');
                $startPlan = microtime(true);
                try {
                    $planId = $user->showDashboard();
                    $data['plan'] = $planId ? Plan::find($planId) : DC::DEFAULT_PLAN;
                } catch (\Throwable $e) {
                    Log::error("[$action] failed fetchPlan", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startPlan, "{$action} fetchPlan", 'completed');
                $startStorage = microtime(true);
                try {
                    $data['storage_limit'] = $this->calcStorageUsage($creatorId);
                } catch (\Throwable $e) {
                    Log::error("[$action] failed calcStorageUsage", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startStorage, "{$action} calcStorageUsage", 'completed');
                Log::info("[$action] rendering view", ['data_keys' => array_keys($data)]);
                $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                $view = VW::DSB . '.account_dashboard';
                if (!ViewFacade::exists($view)) return Redirect::back()->with('error', "HTTP 404: Dashboard Page not found");
                return view($view, $data);
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                return Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const PRJ_DSB_IDX = 'projectDashboardIndex';
    public function projectDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            $startOverall = microtime(true);
            try {
                $startLogin = microtime(true);
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                $guard = self::guard($req, PMC::SHW_PRJ_DSB, Redirect::back());
                if ($guard !== true)
                    return redirect(self::REDIRECT_INDEX)->with('error', 'Unauthorized access to project dashboard');
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                $startRole = microtime(true);
                if ($user[UC::COL_TP] === PMC::ADM || $user[UC::COL_TP] === PMC::SA)
                    return view(PMC::ADM . '.' . self::ENTITY);
                $this->logExecutionTime($startRole, "{$action} roleCheck", 'completed');
                $startFetch = microtime(true);
                $projectIds = $user?->projects()->pluck(PJC::COL_PJ_ID);
                $tasks       = ProjectTask::whereIn(PJC::COL_PJ_ID, $projectIds)->get();
                $expenses    = Expense::whereIn(PJC::COL_PJ_ID, $projectIds)->get();
                $sevenDays   = Utility::getLastSevenDays();
                $homeData    = [];
                $homeData['totalProject'] = ['total' => count($projectIds), 'percentage' => Utility::getPercentage($user?->projects()->where(AC::COL_TSK_STT, PJC::STT_CPT_K)->count(), count($projectIds))];
                $homeData['totalTask']    = ['total' => $tasks->count(), 'percentage' => Utility::getPercentage($tasks->where(PJC::COL_IS_CP, 1)->whereRaw("find_in_set('{$user?->id}'," . PJC::COL_ASGN . ")")->count(), $tasks->count())];
                $totalBudget = $user?->projects->sum('budget');
                $totalExpense = $expenses->sum('amount');
                $homeData['totalExpense'] = ['total' => $expenses->count(), 'percentage' => Utility::getPercentage($totalExpense, $totalBudget)];
                $homeData['totalUser']    = $user?->contacts->count();
                $homeData['taskOverview']   = [];
                $homeData['timesheetLogged'] = [];
                foreach ($sevenDays as $date => $day) {
                    $homeData['taskOverview'][$day]    = ProjectTask::where(PJC::COL_IS_CP, 1)->where(PJC::COL_M_AT, 'like', $date)->whereIn(PJC::COL_PJ_ID, $projectIds)->count();
                    $times = Timesheet::whereIn(PJC::COL_PJ_ID, $projectIds)->where('date', 'like', $date)->pluck('time')->toArray();
                    $homeData['timesheetLogged'][$day] = str_replace(':', '. ', Utility::calculateTimesheetHours($times));
                }
                $totalProj = count($projectIds);
                $statuses  = [];
                foreach (Project::$project_status as $k => $v) {
                    $count = $user?->projects->where(AC::COL_TSK_STT, $k)->count();
                    $statuses[$k] = ['total' => $count, 'percentage' => Utility::getPercentage($count, $totalProj)];
                }
                $homeData['projectStatus'] = $statuses;
                $homeData['dueProject']    = $user?->projects()->orderBy(PJC::COL_E_DT, 'desc')->limit(5)->get();
                $homeData['dueTasks']      = ProjectTask::where(PJC::COL_IS_CP, 0)->whereIn(PJC::COL_PJ_ID, $projectIds)->orderBy(PJC::COL_E_DT, 'desc')->limit(5)->get();
                $homeData['lastTasks']     = ProjectTask::whereIn(PJC::COL_PJ_ID, $projectIds)->orderBy(PJC::COL_E_DT, 'desc')->limit(5)->get();
                $this->logExecutionTime($startFetch, "{$action} dataFetch", 'completed');
                $viewName = VW::DSB . '.project_dashboard';
                if (!ViewFacade::exists($viewName)) {
                    Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                    return Redirect::back()->with('error', "HTTP 404: Project Dashboard Page not found");
                }
                Log::info("{$action} rendering view", ['data_keys' => array_keys($homeData)]);
                $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                return view($viewName, compact('homeData'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", ['file' => $file, 'class' => $cls, 'error_class' => get_class($re), 'message' => $re->getMessage()]);
                return defaultUndefinedException($req, $re, $action);
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['req' => $req]);
    }

    public const HRM_DSB_IDX = 'hrmDashboardIndex';
    public function hrmDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                $guard = self::guard($req, PMC::SHW_HRM_DSB, Redirect::back());
                if ($guard !== true)
                    return redirect(self::REDIRECT_INDEX)->with('error', 'Unauthorized access to HRM dashboard');
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                $startType = microtime(true);
                if (!in_array($user[UC::COL_TP], [PMC::CL, PMC::CPN], true)) {
                    try {
                        $startEmp = microtime(true);
                        $emp = Employee::where(UC::COL_USER_ID, $user->id)->first();
                        $this->logExecutionTime($startEmp, "{$action} fetchEmployee", 'completed');
                        $startAnn = microtime(true);
                        $announcements = Announcement::join('employee_announcements', 'announcements.id', '=', 'employee_announcements.announcement_id')->where('employee_announcements.' . UC::COL_EMP_ID, $emp->id)->orWhere(fn($q) => $q->where('announcements.' . CPC::COL_DEP_ID, '["0"]')->where('employee_announcements.' . UC::COL_EMP_ID, '["0"]'))->orderByDesc('announcements.id')->limit(5)->get();
                        $this->logExecutionTime($startAnn, "{$action} fetchAnnouncements", 'completed');
                        $startMeet = microtime(true);
                        $meetings = Meeting::join('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.' . UC::COL_EMP_ID, $emp->id)->orWhere(fn($q) => $q->where('meetings.' . CPC::COL_DEP_ID, '["0"]')->where('meeting_employees.' . UC::COL_EMP_ID, '["0"]'))->orderByDesc('meetings.id')->limit(5)->get();
                        $this->logExecutionTime($startMeet, "{$action} fetchMeetings", 'completed');
                        $startEvents = microtime(true);
                        $events = Event::join('event_employees', 'events.id', '=', 'event_employees.event_id')->where('event_employees.' . UC::COL_EMP_ID, $emp->id)->orWhere(fn($q) => $q->where('events.' . CPC::COL_DEP_ID, '["0"]')->where('event_employees.' . UC::COL_EMP_ID, '["0"]'))->get();
                        $this->logExecutionTime($startEvents, "{$action} fetchEvents", 'completed');
                        $startBuild = microtime(true);
                        $arrEvents = [];
                        foreach ($events as $e) {
                            $arrEvents[] = Arr::only((array)$e->only('id', 'title'), ['id', 'title']) + ['start' => $e->start_date, 'end' => $e->end_date, 'backgroundColor' => $e->color, 'borderColor' => '#fff', 'textColor' => 'white'];
                        }
                        $this->logExecutionTime($startBuild, "{$action} buildArrEvents", 'completed');
                        $startAtt = microtime(true);
                        $today = now()->toDateString();
                        $attendance = EmployeeAttendance::where(UC::COL_EMP_ID, $emp->id)->where('date', $today)->latest()->first();
                        $this->logExecutionTime($startAtt, "{$action} fetchAttendance", 'completed');
                        $startOffice = microtime(true);
                        $officeTime = ['startTime' => Utility::getValByName('company_start_time'), 'endTime' => Utility::getValByName('company_end_time')];
                        $this->logExecutionTime($startOffice, "{$action} fetchOfficeTime", 'completed');
                        $viewName = VW::DSB . '.' . self::ENTITY;
                        if (!ViewFacade::exists($viewName)) {
                            Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                            return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                        }
                        Log::info("{$action} rendering view");
                        $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                        $employeeAttendance = $attendance;
                        return view($viewName, compact('arrEvents', 'announcements', DC::TABLE_MEETINGS, 'employeeAttendance', 'officeTime'));
                    } catch (\Throwable $e) {
                        Log::error("{$action} employee dashboard error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                        return defaultUndefinedException($req, $e, $action, '/', true, null, 500, false);
                    }
                }
                $this->logExecutionTime($startType, "{$action} typeCheck", 'completed');
                if ($user[UC::COL_TP] === PMC::SA) {
                    $startSA = microtime(true);
                    $userMetrics = ['total_user' => $user->countCompany(), 'total_paid_user' => $user->countPaidCompany(), 'totalOrders' => Order::totalOrders(), 'totalOrders_price' => Order::totalOrdersPrice(), 'total_plan' => Plan::totalPlan(), 'most_purchase_plan' => optional(Plan::mostPurchasePlan())->name];
                    $chartData = $this->getOrderChart(['duration' => 'week']);
                    $this->logExecutionTime($startSA, "{$action} superAdminData", 'completed');
                    $viewName = VW::DSB . '.super_admin';
                    if (!ViewFacade::exists($viewName)) {
                        Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                        return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    }
                    Log::info("{$action} rendering super admin view");
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('userMetrics', 'chartData'));
                }
                try {
                    $startCreator = microtime(true);
                    $creatorId = $user->creatorId();
                    $this->logExecutionTime($startCreator, "{$action} getCreatorId", 'completed');
                    $startEv = microtime(true);
                    $events = Event::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
                    $this->logExecutionTime($startEv, "{$action} fetchEvents", 'completed');
                    $startArr2 = microtime(true);
                    $arrEvents = [];
                    foreach ($events as $e) {
                        $arrEvents[] = ['id' => $e->id, 'title' => $e[AC::COL_TT], 'start' => $e[PJC::COL_S_DT], 'end' => $e[PJC::COL_E_DT], 'backgroundColor' => $e->color, 'borderColor' => '#fff', 'textColor' => 'white', 'url' => route('event.edit', $e->id)];
                    }
                    $this->logExecutionTime($startArr2, "{$action} buildArrEvents", 'completed');
                    $startAnn2 = microtime(true);
                    $announcements = Announcement::where(DC::COL_TABLE_CREATOR, $creatorId)->orderByDesc('id')->limit(5)->get();
                    $this->logExecutionTime($startAnn2, "{$action} fetchAnnouncements", 'completed');
                    $startCountUser = microtime(true);
                    $countUser = User::whereNotIn(UC::COL_TP, [PMC::CL, PMC::CPN])->where(DC::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startCountUser, "{$action} countUser", 'completed');
                    $startCountTrainer = microtime(true);
                    $countTrainer = Trainer::where(DC::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startCountTrainer, "{$action} countTrainer", 'completed');
                    $startOnGoing = microtime(true);
                    $onGoingTraining = Training::whereStatus(1)->where(DC::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startOnGoing, "{$action} countOnGoingTraining", 'completed');
                    $startDone = microtime(true);
                    $doneTraining = Training::whereStatus(2)->where(DC::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startDone, "{$action} countDoneTraining", 'completed');
                    $startEmpList = microtime(true);
                    $employees = User::where(UC::COL_TP, PMC::CL)->where(DC::COL_TABLE_CREATOR, $creatorId)->get();
                    $countClient = $employees->count();
                    $this->logExecutionTime($startEmpList, "{$action} fetchEmployees", 'completed');
                    $startNotClock = microtime(true);
                    $notClockIn = EmployeeAttendance::whereDate('date', now()->toDateString())->pluck(UC::COL_EMP_ID)->toArray();
                    $notClockIns = Employee::where(DC::COL_TABLE_CREATOR, $creatorId)->whereNotIn('id', $notClockIn)->get();
                    $this->logExecutionTime($startNotClock, "{$action} fetchNotClockIns", 'completed');
                    $startJobs = microtime(true);
                    $activeJob = Job::whereStatus('active')->where(DC::COL_TABLE_CREATOR, $creatorId)->count();
                    $inActiveJob = Job::whereStatus('in_active')->where(DC::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startJobs, "{$action} countJobs", 'completed');
                    $startMeet2 = microtime(true);
                    $meetings = Meeting::where(DC::COL_TABLE_CREATOR, $creatorId)->limit(5)->get();
                    $this->logExecutionTime($startMeet2, "{$action} fetchMeetings", 'completed');
                    $viewName = VW::DSB . '.' . self::ENTITY;
                    if (!ViewFacade::exists($viewName)) {
                        Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                        return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    }
                    Log::info("{$action} rendering view");
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('arrEvents', 'announcements', DC::TABLE_EMPLOYEES, DC::TABLE_MEETINGS, 'countTrainer', 'countClient', 'countUser', 'notClockIns', 'activeJob', 'inActiveJob', 'onGoingTraining', 'doneTraining'));
                } catch (\Throwable $e) {
                    Log::error("{$action} company dashboard error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                    return defaultUndefinedException($req, $e, $action, '/', true, null, 500, false);
                }
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", ['file' => $file, 'class' => $cls, 'error_class' => get_class($re), 'message' => $re->getMessage()]);
                return redirect('/')->with('error', "HTTP 500: Unexpected error");
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return redirect('/')->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const CRM_DSB_IDX = 'crmDashboardIndex';
    public function crmDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                $guard = self::guard($req, PMC::SHW_CRM_DSB, Redirect::back());
                if ($guard !== true)
                    return $this->accountDashboardIndex($req);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                if ($user[UC::COL_TP] === PMC::ADM || $user[UC::COL_TP] === PMC::SA) {
                    $viewName = PMC::ADM . '.' . self::ENTITY;
                    if (!ViewFacade::exists($viewName)) {
                        Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                        return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    }
                    Log::info("{$action} rendering admin view");
                    $this->logExecutionTime($startOverall, "{$action} renderAdmin", 'completed');
                    return view($viewName);
                }
                try {
                    $startFetch = microtime(true);
                    $creatorId = $user->creatorId();
                    $leads = Lead::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
                    $deals = Deal::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
                    $crmData = [
                        'total_' . DC::TABLE_LEADS    => $leads->count(),
                        'total_' . DC::TABLE_DEALS    => $deals->count(),
                        'total_' . DC::TABLE_CONTRACTS => Contract::where(DC::COL_TABLE_CREATOR, $creatorId)->count(),
                    ];
                    $this->logExecutionTime($startFetch, "{$action} fetchCounts", 'completed');
                    $startBuild = microtime(true);
                    $crmData['lead_status'] = $this->buildPipelineStats(LeadStage::class, 'lead', $crmData['total_' . DC::TABLE_LEADS]);
                    $crmData['deal_status'] = $this->buildPipelineStats(Stage::class, 'deal', $crmData['total_' . DC::TABLE_DEALS]);
                    $this->logExecutionTime($startBuild, "{$action} buildStats", 'completed');
                    $startLatest = microtime(true);
                    $crmData['latestContract'] = Contract::where(DC::COL_TABLE_CREATOR, $creatorId)
                        ->with([DC::TABLE_CLIENTS, DC::TABLE_PROJECTS, 'types'])
                        ->latest()->limit(5)->get();
                    $this->logExecutionTime($startLatest, "{$action} fetchLatestContracts", 'completed');
                    $viewName = VW::DSB . '.crm_dashboard';
                    if (!ViewFacade::exists($viewName)) {
                        Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                        return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    }
                    Log::info("{$action} rendering view", ['data_keys' => array_keys($crmData)]);
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('crmData'));
                } catch (\Throwable $e) {
                    Log::error("{$action} CRM data error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                    return defaultUndefinedException($req, $e, $action);
                }
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", ['file' => $file, 'class' => $cls, 'error_class' => get_class($re), 'message' => $re->getMessage()]);
                return Redirect::back()->with('error', "HTTP 500: Unexpected error");
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const POS_DSB_IDX = 'posDashboardIndex';
    public function posDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                $guard = self::guard($req, PMC::SHW_POS_DSB, Redirect::back());
                if ($guard !== true)
                    return $this->accountDashboardIndex($req);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                $startRole = microtime(true);
                if ($user[UC::COL_TP] === PMC::ADM || $user[UC::COL_TP] === PMC::SA) {
                    $viewName = PMC::ADM . '.' . self::ENTITY;
                    if (!ViewFacade::exists($viewName)) {
                        Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                        return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    }
                    Log::info("{$action} rendering admin view");
                    $this->logExecutionTime($startOverall, "{$action} renderAdmin", 'completed');
                    return view($viewName);
                }
                $this->logExecutionTime($startRole, "{$action} roleCheck", 'completed');
                try {
                    $startFetch = microtime(true);
                    $pos_data = ['monthlyPosAmount' => Pos::totalPosAmount(true), 'totalPosAmount' => Pos::totalPosAmount(), 'monthlyPurchaseAmount' => Purchase::totalPurchaseAmount(true), 'totalPurchaseAmount' => Purchase::totalPurchaseAmount()];
                    $purchasesArray = Purchase::getPurchaseReportChart();
                    $posesArray = Pos::getPosReportChart();
                    $this->logExecutionTime($startFetch, "{$action} fetchData", 'completed');
                    $viewName = VW::DSB . '.pos_dashboard';
                    if (!ViewFacade::exists($viewName)) {
                        Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                        return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    }
                    Log::info("{$action} rendering view", ['data_keys' => array_keys($pos_data)]);
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('pos_data', 'purchasesArray', 'posesArray'));
                } catch (\Throwable $e) {
                    Log::error("{$action} POS data error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                    return defaultUndefinedException($req, $e, $action);
                }
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", ['file' => $file, 'class' => $cls, 'error_class' => get_class($re), 'message' => $re->getMessage()]);
                return Redirect::back()->with('error', "HTTP 500: Unexpected error");
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const FT_VW = 'filterView';
    public function filterView(Request $req): ?JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            $startOverall = microtime(true);
            try {
                Log::info("{$action} start", ['user_id' => Auth::id(), 'keyword' => $req->keyword]);
                if (!$req->ajax())
                    return null;
                $this->logExecutionTime($startOverall, "{$action} ajaxCheck", 'completed');
                $users = User::where('id', '!=', Auth::id());
                $kw = $req->keyword;
                if (!empty($kw) && is_string($kw)) {
                    $users->where(fn($q) => $q->where(UC::COL_NM, 'like', "{$kw}%")->orWhereRaw('find_in_set(?,skills)', [$kw]));
                    Log::info("{$action} applied filter", ['keyword' => $kw]);
                }
                $list = $users->get();
                $html = view(VW::DSB . '.view', compact('list'))->render();
                Log::info("{$action} returning html", ['count' => $list->count()]);
                $this->logExecutionTime($startOverall, "{$action} renderHtml", 'completed');
                return response()->json(['success' => true, 'html' => $html]);
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['req' => $req]);
    }

    public const CL_VW = 'clientView';
    public function clientView(Request $req): Response|RedirectResponse|View|int
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                Log::info("{$action} start", ['user_id' => $user->id ?? null, 'type' => $user[UC::COL_TP] ?? null]);
                if ($user[UC::COL_TP] === PMC::SA) {
                    $user['total_user'] = $user->countCompany();
                    $user['total_paid_user'] = $user->countPaidCompany();
                    $user['totalOrders'] = Order::totalOrders();
                    $user['totalOrders_price'] = Order::totalOrdersPrice();
                    $user['total_plan'] = Plan::totalPlan();
                    $user['mostPurchasedPlan'] = optional(Plan::mostPurchasedPlan())->total ?? 0;
                    $chartData = $this->getOrderChart(['duration' => 'week']);
                    $viewName = VW::DSB . '.super_admin';
                    if (!ViewFacade::exists($viewName)) {
                        Log::error("{$action} View not found", ['file' => $file, 'class' => $cls, 'view' => $viewName]);
                        return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    }
                    Log::info("{$action} rendering super admin");
                    $this->logExecutionTime($startOverall, "{$action} renderSuperAdmin", 'completed');
                    return view($viewName, compact('user', 'chartData'));
                }
                if ($user[UC::COL_TP] === PMC::CL) {
                    try {
                        $startClient = microtime(true);
                        $today = now()->toDateString();
                        $weekLabels = collect(range(0, 6))->map(fn($i) => now()->subDays($i)->format('D'));
                        $chartData = ['date' => $weekLabels, 'invoice' => array_fill(0, 7, 10), 'payment' => array_fill(0, 7, 20)];
                        $calendarTasks = [];
                        $clientDeals = $user->clientDeals()->with('tasks')->get();
                        foreach ($clientDeals as $deal) {
                            foreach ($deal->tasks as $task) $calendarTasks[] = ['title' => $task->name, 'start' => $task->date, 'url' => route('deals.tasks.show', [$deal->id, $task->id]), 'className' => $task->status ? 'bg-primary border-primary' : 'bg-warning border-warning'];
                            $calendarTasks[] = ['title' => $deal->name, 'start' => $deal->created_at->toDateString(), 'url' => route('deals.show', $deal->id), 'className' => 'deal bg-primary border-primary'];
                        }
                        $dealIds = $clientDeals->pluck('id');
                        $arrCount = ['deal' => $dealIds->count(), 'task' => $dealIds->isEmpty() ? 0 : DealTask::whereIn(AC::COL_DL, [$dealIds->first()])->count()];
                        $projects = Project::where('client_id', $user->id)->where(DC::COL_TABLE_CREATOR, $user->creatorId())->where(PJC::COL_E_DT, '>', $today)->orderBy(PJC::COL_E_DT)->limit(5)->get();
                        $projectIds = $projects->pluck('id');
                        $tasksCount = ProjectTask::whereIn(PJC::COL_PJ_ID, $projectIds)->where(DC::COL_TABLE_CREATOR, $user->creatorId())->count();
                        $projectBudget = Project::where('client_id', $user->id)->sum('budget');
                        $projectMetrics = [DC::TABLE_PROJECTS => $projects, 'projects_count' => $projects->count(), 'projects_tasks_count' => $tasksCount, 'project_budget' => $projectBudget];
                        $totalProjects = $user->userProject();
                        $totalTasks = $user->createdTotalProjectTask();
                        $allProjects = Project::where('client_id', $user->id)->where(DC::COL_TABLE_CREATOR, $user->creatorId())->get();
                        $allCount = $allProjects->count();
                        $completedCount = Project::where('client_id', $user->id)->where(AC::COL_TSK_STT, PJC::STT_CPT_K)->where(DC::COL_TABLE_CREATOR, $user->creatorId())->count();
                        $bugs = Bug::whereIn(PJC::COL_PJ_ID, $projectIds)->where(DC::COL_TABLE_CREATOR, $user->creatorId())->get();
                        $bugLastStatus = BugStatus::latest(AC::COL_OD)->first();
                        $completedBugs = $bugLastStatus ? Bug::whereIn(PJC::COL_PJ_ID, $projectIds)->where(AC::COL_TSK_STT, $bugLastStatus->id)->where(DC::COL_TABLE_CREATOR, $user->creatorId())->count() : 0;
                        $projectMetrics += ['projects_bugs_count' => $bugs->count(), 'project_bug_percentage' => $allCount ? intval($completedBugs / $allCount * 100) : 0, 'project_percentage' => $allCount ? intval($completedCount / $allCount * 100) : 0, 'project_task_percentage' => $totalTasks ? intval($user->projectCompleteTask($user->lastProjectStage()?->id ?? 0) / $totalTasks * 100) : 0];
                        $invoices = Invoice::where(DC::COL_TABLE_CREATOR, $user->creatorId())->where('client_id', $user->id)->get();
                        $dueInvoices = $invoices->filter(fn($inv) => $inv->getDue() > 0);
                        $invoiceMetrics = ['total_invoice' => $invoices->count(), 'complete_invoice' => $invoices->where(fn($inv) => $inv->getDue() === 0)->count(), 'due_amount' => $dueInvoices->sum(fn($inv) => $inv->getDue()), 'top_due_invoice' => $dueInvoices->sortByDesc(fn($inv) => $inv->getDue())->take(5)->values()];
                        $usersMetrics = ['staff' => User::where(DC::COL_TABLE_CREATOR, $user->creatorId())->count(), 'user' => User::where(DC::COL_TABLE_CREATOR, $user->creatorId())->where(UC::COL_TP, '!=', PMC::CL)->count(), PMC::CL => User::where(DC::COL_TABLE_CREATOR, $user->creatorId())->where(UC::COL_TP, PMC::CL)->count()];
                        $projectStatus = array_values(Project::$project_status);
                        $projectData = Project::getProjectStatus();
                        $taskData = \App\Models\TaskStage::getChartData();
                        $viewName = VW::DSB . '.client_view';
                        if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                        Log::info("[$action] rendering client view");
                        $this->logExecutionTime($startOverall, "{$action} renderClient", 'completed');
                        return view($viewName, compact('calendarTasks', 'arrCount', 'chartData', 'projectMetrics', 'invoiceMetrics', 'usersMetrics', 'projectStatus', 'projectData', 'taskData'));
                    } catch (\Throwable $e) {
                        Log::error("{$action} client view error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                        return defaultUndefinedException($req, $e, $action);
                    }
                }
                abort(HttpResponse::HTTP_FORBIDDEN, 'Permission denied.');
                return 403;
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", ['file' => $file, 'class' => $cls, 'error_class' => get_class($re), 'message' => $re->getMessage()]);
                return Redirect::back()->with('error', "HTTP 500: Unexpected error");
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const GET_OC = 'getOrderChart';
    public function getOrderChart(array $params): array
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($params, $action, $file, $cls, $fn) {
            $data = [];
            try {
                $startOverall = microtime(true);
                Log::info("{$action} start", ['params' => $params]);
                $labels = [];
                $duration = $params['duration'] ?? null;
                if ($duration === 'week') {
                    Log::info("{$action} building weekly chart", ['duration' => $duration]);
                    $start = now()->subDays(13);
                    $labels = collect()->times(14)->mapWithKeys(fn($i) => [$start->copy()->addDays($i)->toDateString() => $start->copy()->addDays($i)->format('d-M')])->all();
                }
                $data = ['label' => array_values($labels), 'data' => []];
                foreach ($labels as $date => $lbl) {
                    Log::debug("{$action} processing", ['date' => $date, 'label' => $lbl]);
                    $data['data'][] = Order::whereDate('created_at', $date)->count();
                }
                $this->logExecutionTime($startOverall, "{$action} buildData", 'completed');
                return $data;
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return $data;
            }
        }, []);
    }

    public const STP_TRK = 'stopTracker';
    public function stopTracker(Request $req): JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use ($req, $action, $file, $cls, $fn) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return response()->json(['error' => 'Unauthenticated.'], 401);
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                Log::info("{$action} start", ['user_id' => $user->id ?? null, 'input' => $req->all()]);
                if ($user->isClient()) {
                    Log::warning("{$action} denied for client", ['user_id' => $user->id ?? null]);
                    return Utility::errorRes(__('Permission denied.'));
                }
                $startVal = microtime(true);
                $v = Validator::make($req->all(), ['name' => 'required|string|max:120', PJC::COL_PJ_ID => 'required|integer']);
                if ($v->fails()) {
                    Log::warning("{$action} validation failed", ['errors' => $v->errors()->all()]);
                    return Utility::errorRes($v->errors()->first());
                }
                $this->logExecutionTime($startVal, "{$action} validation", 'completed');
                DB::beginTransaction();
                try {
                    $startTrack = microtime(true);
                    $tracker = TimeTracker::where(DC::COL_TABLE_CREATOR, $user->id)->where(AC::COL_IA, 1)->firstOrFail();
                    $end = $req->input(AC::COL_E_TIME, now()->toDateTimeString());
                    $tracker->update([AC::COL_E_TIME => $end, AC::COL_IA => 0, AC::COL_TTL_TIME => Utility::differenceToTime($tracker[AC::COL_ST_TIME], $end)]);
                    DB::commit();
                    $this->logExecutionTime($startTrack, "{$action} updateTracker", 'completed');
                    Log::info("{$action} stopped", ['tracker_id' => $tracker->id]);
                    $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                    return Utility::successRes(__('Add Time successfully.'));
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error("{$action} tracker update failed", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                    return Utility::errorRes(__('Tracker not found.'));
                }
            } catch (ValidationException $ve) {
                $errors = $ve->errors();
                $msg = is_array($errors) ? (collect($errors)->flatten()->first() ?? __('Validation failed')) : __('Validation failed');
                Log::warning("{$action} Validation failed", ['file' => $file, 'class' => $cls, 'error_class' => get_class($ve), 'errors' => $errors]);
                return Utility::errorRes($msg);
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", ['file' => $file, 'class' => $cls, 'error_class' => get_class($re), 'message' => $re->getMessage()]);
                return Utility::errorRes(__('An unexpected error occurred.'));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", ['file' => $file, 'class' => $cls, 'error_class' => get_class($e), 'message' => $e->getMessage()]);
                return Utility::errorRes(__('An unexpected error occurred.'));
            }
        }, ['req' => $req]);
    }

    private function loadConstants(int|string $creatorId): array
    {
        return [
            DC::TABLE_TAXES => Tax::where(DC::COL_TABLE_CREATOR, $creatorId)->count(),
            'category' => ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $creatorId)->count(),
            'units' => ProductServiceUnit::where(DC::COL_TABLE_CREATOR, $creatorId)->count(),
            'bankAccount' => BankAccount::where(DC::COL_TABLE_CREATOR, $creatorId)->count(),
        ];
    }

    private function buildCategoryChart(string $type, int|string $creatorId): array
    {
        $cats = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->where(UC::COL_TP, $type)->get();
        $colors = $cats->map(fn($c) => "#{$c->color}")->toArray();
        $names = $cats->pluck('name')->toArray();
        $amounts = $cats->map(
            fn($c) => $type === 'income'
                ? $c->incomeCategoryRevenueAmount()
                : $c->expenseCategoryAmount()
        )->toArray();
        return [$colors, $names, $amounts];
    }

    private function calcStorageUsage(int|string $creatorId): float
    {
        $user = User::find($creatorId);
        $plan = Plan::find($user?->showDashboard());
        return $plan?->storage_limit > 0
            ? ($user?->storage_limit / $plan->storage_limit) * 100
            : SC::MAX_SL_LIMIT_MB;
    }

    private function handleLandingOrInstall(Request $req): RedirectResponse|View
    {
        $output = new ConsoleOutput();
        if (!file_exists(storage_path('installed'))) {
            $output->writeln('');
            $output->writeln('<error>No installation detected. Killing process. </error>');
            $output->writeln('');
            Log::Error('No installation detected. Killing process.');
            header('Location:install');
            die;
        }
        $settings = Utility::settings();
        if (($settings['display_landing_page'] ?? '') === 'on' && app()->runningInConsole() === false) {
            $output->writeln('');
            $output->writeln('<comment>App was detected to be running in the console. Redirecting to landing page, if available.</comment> ');
            $output->writeln('');
            return view(R::LP . '::' . E::LOS . '.landingpage', compact('settings'));
        }
        $output->writeln('');
        $output->writeln('<comment>Redirecting to Login...</comment> ');
        $output->writeln('');
        return redirect('login');
    }

    private function buildPipelineStats(string $model, string $key, int $total): array
    {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse)
            return [];
        $user = $userOrRedirect;
        $class = $model;
        $alias = strtolower($key);
        $items = $class::select(
            "{$model::$table}.*",
            DC::TABLE_PIPELINES . '.name as pipeline'
        )
            ->join(
                DC::TABLE_PIPELINES,
                DC::TABLE_PIPELINES . '.id',
                "{$model::$table}.pipeline_id"
            )
            ->where(DC::TABLE_PIPELINES . '.created_by', $user?->creatorId())
            ->where("{$model::$table}.created_by", $user?->creatorId())
            ->orderBy("{$model::$table}.pipeline_id")->get();
        $stats = [];
        foreach ($items as $i => $it) {
            $count = $it->{$alias . 's'}()->count();
            $stats[$i] = [
                "{$alias}_stage" => $it->name,
                "{$alias}_total" => $count,
                "{$alias}_percentage" => Utility::getCrmPercentage($count, $total),
            ];
        }
        return $stats;
    }
}
