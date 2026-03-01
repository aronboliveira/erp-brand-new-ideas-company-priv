<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
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
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request,
    Response
};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Log,
    Redirect,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;
use Modules\LandingPage\Config\Constants\{
    ExtendingLandingPageLayoutConstants as E,
    RoutesResourcesConstants as R
};
use Symfony\Component\{
    Console\Output\ConsoleOutput,
    HttpFoundation\Response as HttpResponse
};

class DashboardController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    public const ENTITY = 'dashboard';
    private const REDIRECT_INDEX = '/';
    private const ACCOUNT_DASHBOARD_ROUTE = self::ENTITY . '.account';
    private const CLIENT_DASHBOARD_ROUTE = PermissionsConstants::CL . '.' . self::ENTITY . '.view';

    public function __construct()
    {
        Log::debug('Constructing ' . self::class . '...');
    }

    public const ACC_DSB_IDX = 'accountDashboardIndex';
    public function accountDashboardIndex(Request $req): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            try {
                $startOverall = microtime(true);
                $output = new ConsoleOutput();
                $ctx = ['ip' => $req->ip() ?? 'unknown_ip', 'referrer' => Utility::getReferrer($req) ?? 'no_referrer', 'uri' => $req->getRequestUri() ?? 'unknown_uri', 'route' => $req->route()?->getName() ?? '#UNIDENTIFIED', 'controller_method' => $method];
                Log::info("[$action] called", $ctx);
                $output->writeln("\n<question>Calling Dashboard::index</question>\n");
                try {
                    $userOrRedirect = self::_checkLogin();
                } catch (\Throwable $e) {
                    Log::error("[$action] login check exception", ['error' => $e->getMessage()] + $ctx);
                    Log::debug($e->getTraceAsString());
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', 'Login validation failed');
                }
                $this->logExecutionTime($startOverall, "{$action} loginCheck", 'completed');
                if (!($userOrRedirect instanceof User)) {
                    Log::warning("[$action] Login check failed – redirecting", $ctx);
                    $output->writeln("\n<comment>User not authenticated. Handling Landing.</comment>\n");
                    return $this->handleLandingOrInstall($req);
                }
                $user = $userOrRedirect;
                Log::info("[$action] start", ['user_id' => $user->id ?? 'undefined', 'user_type' => $user[UsersConstants::COL_TP] ?? 'unknown']);
                $startGuard = microtime(true);
                if (!self::guard($req, PermissionsConstants::SHW_ACC_DSB, self::REDIRECT_INDEX) && $user[UsersConstants::COL_TP] === PermissionsConstants::SA) Log::notice("[$action] super admin bypass", ['user_id' => $user->id ?? 'undefined']);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                if (in_array($user[UsersConstants::COL_TP] ?? '', [PermissionsConstants::CL], true)) return Log::info("[$action] Client user – redirecting", ['user_id' => $user->id ?? 'undefined']) or redirect()->route(self::CLIENT_DASHBOARD_ROUTE);
                $data = ['latestIncome' => collect(), 'latestExpense' => collect(), 'incomeCategoryColor' => [], 'incomeCategory' => [], 'incomeCatAmount' => [], 'expenseCategoryColor' => [], 'expenseCategory' => [], 'expenseCatAmount' => [], 'incExpBarChartData' => [], 'incExpLineChartData' => [], 'currentYear' => now()->year, 'currentMonth' => now()->format('M'), 'constant' => [], 'bankAccountDetail' => collect(), 'recentInvoice' => collect(), 'weeklyInvoice' => [], 'monthlyInvoice' => [], 'recentBill' => collect(), 'weeklyBill' => [], 'monthlyBill' => [], 'goals' => collect(), DatabaseConstants::TABLE_USERS => null, 'plan' => null, 'storage_limit' => SettingsConstants::MAX_SL_LIMIT_MB];
                $creatorId = 0;
                $startCreator = microtime(true);
                try {
                    $creatorId = $user->creatorId() ?? 0;
                } catch (\Throwable $e) {
                    Log::error("[$action] failed to get creatorId", ['error' => $e->getMessage()] + $ctx);
                }
                $this->logExecutionTime($startCreator, "{$action} getCreatorId", 'completed');
                $startIncome = microtime(true);
                try {
                    $data['latestIncome'] = Revenue::latest()->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->limit(5)->get();
                } catch (\Throwable $e) {
                    Log::error("[$action] failed latestIncome", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startIncome, "{$action} fetchLatestIncome", 'completed');
                $startExpense = microtime(true);
                try {
                    $data['latestExpense'] = Payment::latest()->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->limit(5)->get();
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
                    $data['bankAccountDetail'] = BankAccount::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
                } catch (\Throwable $e) {
                    Log::error("[$action] failed bankAccountDetail", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startBank, "{$action} fetchBankAccount", 'completed');
                $startInv = microtime(true);
                try {
                    $data['recentInvoice'] = Invoice::latest()->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->limit(5)->get();
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
                    $data['recentBill'] = Bill::latest()->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->limit(5)->get();
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
                    $data['goals'] = Goal::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->where('is_display', 1)->get();
                } catch (\Throwable $e) {
                    Log::error("[$action] failed fetchGoals", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startGoals, "{$action} fetchGoals", 'completed');
                $startUserRec = microtime(true);
                try {
                    $data[DatabaseConstants::TABLE_USERS] = User::find($creatorId);
                } catch (\Throwable $e) {
                    Log::error("[$action] failed fetchUserRecord", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                }
                $this->logExecutionTime($startUserRec, "{$action} fetchUserRecord", 'completed');
                $startPlan = microtime(true);
                try {
                    $planId = $user->showDashboard();
                    $data['plan'] = $planId ? Plan::find($planId) : DatabaseConstants::DEFAULT_PLAN;
                } catch (\Throwable $e) {
                    Log::error("[$action] failed fetchPlan", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
                $this->logExecutionTime($startPlan, "{$action} fetchPlan", 'completed');
                $startStorage = microtime(true);
                try {
                    $data['storage_limit'] = $this->calcStorageUsage($creatorId);
                } catch (\Throwable $e) {
                    Log::error("[$action] failed calcStorageUsage", ['error' => $e->getMessage(), 'creator_id' => $creatorId]);
                    Log::debug($e->getTraceAsString());
                }
                $this->logExecutionTime($startStorage, "{$action} calcStorageUsage", 'completed');
                Log::info("[$action] rendering view", ['data_keys' => array_keys($data)]);
                $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                $view = ViewsConstants::DSB . '.account_dashboard';
                if (!ViewFacade::exists($view)) return Redirect::back()->with('error', "HTTP 404: Dashboard Page not found");
                return view($view, $data);
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const PRJ_DSB_IDX = 'projectDashboardIndex';
    public function projectDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            $startOverall = microtime(true);
            try {
                $startLogin = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                if ($r = self::guard($req, PermissionsConstants::SHW_PRJ_DSB, Redirect::back())) return $this->projectDashboardIndex($req);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                $startRole = microtime(true);
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::ADM || $user[UsersConstants::COL_TP] === PermissionsConstants::SA) return view(PermissionsConstants::ADM . '.' . self::ENTITY);
                $this->logExecutionTime($startRole, "{$action} roleCheck", 'completed');
                $startFetch = microtime(true);
                $projectIds = $user?->projects()->pluck(ProjectsConstants::COL_PJ_ID);
                $tasks       = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->get();
                $expenses    = Expense::whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->get();
                $sevenDays   = Utility::getLastSevenDays();
                $homeData    = [];
                $homeData['totalProject'] = ['total' => count($projectIds), 'percentage' => Utility::getPercentage($user?->projects()->where(ActivitiesConstants::COL_TSK_STT, ProjectsConstants::STT_CPT_K)->count(), count($projectIds))];
                $homeData['totalTask']    = ['total' => $tasks->count(), 'percentage' => Utility::getPercentage($tasks->where(ProjectsConstants::COL_IS_CP, 1)->whereRaw("find_in_set('{$user?->id}'," . ProjectsConstants::COL_ASGN . ")")->count(), $tasks->count())];
                $totalBudget = $user?->projects->sum('budget');
                $totalExpense = $expenses->sum('amount');
                $homeData['totalExpense'] = ['total' => $expenses->count(), 'percentage' => Utility::getPercentage($totalExpense, $totalBudget)];
                $homeData['totalUser']    = $user?->contacts->count();
                $homeData['taskOverview']   = [];
                $homeData['timesheetLogged'] = [];
                foreach ($sevenDays as $date => $day) {
                    $homeData['taskOverview'][$day]    = ProjectTask::where(ProjectsConstants::COL_IS_CP, 1)->where(ProjectsConstants::COL_M_AT, 'like', $date)->whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->count();
                    $times = Timesheet::whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->where('date', 'like', $date)->pluck('time')->toArray();
                    $homeData['timesheetLogged'][$day] = str_replace(':', '. ', Utility::calculateTimesheetHours($times));
                }
                $totalProj = count($projectIds);
                $statuses  = [];
                foreach (Project::$project_status as $k => $v) {
                    $count = $user?->projects->where(ActivitiesConstants::COL_TSK_STT, $k)->count();
                    $statuses[$k] = ['total' => $count, 'percentage' => Utility::getPercentage($count, $totalProj)];
                }
                $homeData['projectStatus'] = $statuses;
                $homeData['dueProject']    = $user?->projects()->orderBy(ProjectsConstants::COL_E_DT, 'desc')->limit(5)->get();
                $homeData['dueTasks']      = ProjectTask::where(ProjectsConstants::COL_IS_CP, 0)->whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->orderBy(ProjectsConstants::COL_E_DT, 'desc')->limit(5)->get();
                $homeData['lastTasks']     = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->orderBy(ProjectsConstants::COL_E_DT, 'desc')->limit(5)->get();
                $this->logExecutionTime($startFetch, "{$action} dataFetch", 'completed');
                $viewName = ViewsConstants::DSB . '.project_dashboard';
                if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Project Dashboard Page not found");
                Log::info("[$action] rendering view", ['data_keys' => array_keys($homeData)]);
                $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                return view($viewName, compact('homeData'));
            } catch (\Throwable $e) {
                Log::error("[$action] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "{$method}");
            }
        }, ['req' => $req]);
    }

    public const HRM_DSB_IDX = 'hrmDashboardIndex';
    public function hrmDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                if (self::guard($req, PermissionsConstants::SHW_HRM_DSB, Redirect::back())) return $this->projectDashboardIndex($req);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                $startType = microtime(true);
                if (!in_array($user[UsersConstants::COL_TP], [PermissionsConstants::CL, PermissionsConstants::CPN], true)) {
                    try {
                        $startEmp = microtime(true);
                        $emp = Employee::where(UsersConstants::COL_USER_ID, $user->id)->first();
                        $this->logExecutionTime($startEmp, "{$action} fetchEmployee", 'completed');
                        $startAnn = microtime(true);
                        $announcements = Announcement::join('employee_announcements', 'announcements.id', '=', 'employee_announcements.announcement_id')->where('employee_announcements.' . UsersConstants::COL_EMP_ID, $emp->id)->orWhere(fn($q) => $q->where(CompaniesConstants::COL_DEP_ID, '["0"]')->where(UsersConstants::COL_EMP_ID, '["0"]'))->orderByDesc('announcements.id')->limit(5)->get();
                        $this->logExecutionTime($startAnn, "{$action} fetchAnnouncements", 'completed');
                        $startMeet = microtime(true);
                        $meetings = Meeting::join('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.' . UsersConstants::COL_EMP_ID, $emp->id)->orWhere(fn($q) => $q->where(CompaniesConstants::COL_DEP_ID, '["0"]')->where(UsersConstants::COL_EMP_ID, '["0"]'))->orderByDesc('meetings.id')->limit(5)->get();
                        $this->logExecutionTime($startMeet, "{$action} fetchMeetings", 'completed');
                        $startEvents = microtime(true);
                        $events = Event::join('event_employees', 'events.id', '=', 'event_employees.event_id')->where('event_employees.' . UsersConstants::COL_EMP_ID, $emp->id)->orWhere(fn($q) => $q->where(CompaniesConstants::COL_DEP_ID, '["0"]')->where(UsersConstants::COL_EMP_ID, '["0"]'))->get();
                        $this->logExecutionTime($startEvents, "{$action} fetchEvents", 'completed');
                        $startBuild = microtime(true);
                        $arrEvents = [];
                        foreach ($events as $e) $arrEvents[] = Arr::only((array)$e->only('id', 'title'), ['id', 'title']) + ['start' => $e->start_date, 'end' => $e->end_date, 'backgroundColor' => $e->color, 'borderColor' => '#fff', 'textColor' => 'white'];
                        $this->logExecutionTime($startBuild, "{$action} buildArrEvents", 'completed');
                        $startAtt = microtime(true);
                        $today = now()->toDateString();
                        $attendance = EmployeeAttendance::where(UsersConstants::COL_EMP_ID, $emp->id)->where('date', $today)->latest()->first();
                        $this->logExecutionTime($startAtt, "{$action} fetchAttendance", 'completed');
                        $startOffice = microtime(true);
                        $officeTime = ['startTime' => Utility::getValByName('company_start_time'), 'endTime' => Utility::getValByName('company_end_time')];
                        $this->logExecutionTime($startOffice, "{$action} fetchOfficeTime", 'completed');
                        $viewName = ViewsConstants::DSB . '.' . self::ENTITY;
                        if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                        Log::info("[$action] rendering view");
                        $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                        return view($viewName, compact('arrEvents', 'announcements', DatabaseConstants::TABLE_MEETINGS, 'attendance', 'officeTime'));
                    } catch (\Throwable $e) {
                        Log::error("[$action] error", ['err' => $e->getMessage()]);
                        return defaultUndefinedException($req, $e, "{$method}");
                    }
                }
                $this->logExecutionTime($startType, "{$action} typeCheck", 'completed');
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $startSA = microtime(true);
                    $userMetrics = ['total_user' => $user->countCompany(), 'total_paid_user' => $user->countPaidCompany(), 'totalOrders' => Order::totalOrders(), 'totalOrders_price' => Order::totalOrdersPrice(), 'total_plan' => Plan::totalPlan(), 'most_purchase_plan' => optional(Plan::mostPurchasePlan())->name];
                    $chartData = $this->getOrderChart(['duration' => 'week']);
                    $this->logExecutionTime($startSA, "{$action} superAdminData", 'completed');
                    $viewName = ViewsConstants::DSB . '.super_admin';
                    if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    Log::info("[$action] rendering super admin view");
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('userMetrics', 'chartData'));
                }
                try {
                    $startCreator = microtime(true);
                    $creatorId = $user->creatorId();
                    $this->logExecutionTime($startCreator, "{$action} getCreatorId", 'completed');
                    $startEv = microtime(true);
                    $events = Event::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
                    $this->logExecutionTime($startEv, "{$action} fetchEvents", 'completed');
                    $startArr2 = microtime(true);
                    $arrEvents = [];
                    foreach ($events as $e) $arrEvents[] = ['id' => $e->id, 'title' => $e[ActivitiesConstants::COL_TT], 'start' => $e[ProjectsConstants::COL_S_DT], 'end' => $e[ProjectsConstants::COL_E_DT], 'backgroundColor' => $e->color, 'borderColor' => '#fff', 'textColor' => 'white', 'url' => route('event.edit', $e->id)];
                    $this->logExecutionTime($startArr2, "{$action} buildArrEvents", 'completed');
                    $startAnn2 = microtime(true);
                    $announcements = Announcement::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->orderByDesc('id')->limit(5)->get();
                    $this->logExecutionTime($startAnn2, "{$action} fetchAnnouncements", 'completed');
                    $startCountUser = microtime(true);
                    $countUser = User::whereNotIn(UsersConstants::COL_TP, [PermissionsConstants::CL, PermissionsConstants::CPN])->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startCountUser, "{$action} countUser", 'completed');
                    $startCountTrainer = microtime(true);
                    $countTrainer = Trainer::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startCountTrainer, "{$action} countTrainer", 'completed');
                    $startOnGoing = microtime(true);
                    $onGoingTraining = Training::whereStatus(1)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startOnGoing, "{$action} countOnGoingTraining", 'completed');
                    $startDone = microtime(true);
                    $doneTraining = Training::whereStatus(2)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startDone, "{$action} countDoneTraining", 'completed');
                    $startEmpList = microtime(true);
                    $employees = User::where(UsersConstants::COL_TP, PermissionsConstants::CL)->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
                    $countClient = $employees->count();
                    $this->logExecutionTime($startEmpList, "{$action} fetchEmployees", 'completed');
                    $startNotClock = microtime(true);
                    $notClockIn = EmployeeAttendance::whereDate('date', now()->toDateString())->pluck(UsersConstants::COL_EMP_ID)->toArray();
                    $notClockIns = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->whereNotIn('id', $notClockIn)->get();
                    $this->logExecutionTime($startNotClock, "{$action} fetchNotClockIns", 'completed');
                    $startJobs = microtime(true);
                    $activeJob = Job::whereStatus('active')->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->count();
                    $inActiveJob = Job::whereStatus('in_active')->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->count();
                    $this->logExecutionTime($startJobs, "{$action} countJobs", 'completed');
                    $startMeet2 = microtime(true);
                    $meetings = Meeting::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->limit(5)->get();
                    $this->logExecutionTime($startMeet2, "{$action} fetchMeetings", 'completed');
                    $viewName = ViewsConstants::DSB . '.' . self::ENTITY;
                    if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    Log::info("[$action] rendering view");
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('arrEvents', 'announcements', DatabaseConstants::TABLE_EMPLOYEES, DatabaseConstants::TABLE_MEETINGS, 'countTrainer', 'countClient', 'countUser', 'notClockIns', 'activeJob', 'inActiveJob', 'onGoingTraining', 'doneTraining'));
                } catch (\Throwable $e) {
                    Log::error("[$action] error", ['err' => $e->getMessage()]);
                    return defaultUndefinedException($req, $e, "{$method}");
                }
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const CRM_DSB_IDX = 'crmDashboardIndex';
    public function crmDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                if ($r = self::guard($req, PermissionsConstants::SHW_CRM_DSB, Redirect::back())) return $this->accountDashboardIndex($req);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::ADM || $user[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $viewName = PermissionsConstants::ADM . '.' . self::ENTITY;
                    if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    Log::info("[$action] rendering admin view");
                    $this->logExecutionTime($startOverall, "{$action} renderAdmin", 'completed');
                    return view($viewName);
                }
                try {
                    $startFetch = microtime(true);
                    $creatorId = $user->creatorId();
                    $leads = Lead::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
                    $deals = Deal::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get();
                    $crmData = [
                        'total_' . DatabaseConstants::TABLE_LEADS    => $leads->count(),
                        'total_' . DatabaseConstants::TABLE_DEALS    => $deals->count(),
                        'total_' . DatabaseConstants::TABLE_CONTRACTS => Contract::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->count(),
                    ];
                    $this->logExecutionTime($startFetch, "{$action} fetchCounts", 'completed');
                    $startBuild = microtime(true);
                    $crmData['lead_status'] = $this->buildPipelineStats(LeadStage::class, 'lead', $crmData['total_' . DatabaseConstants::TABLE_LEADS]);
                    $crmData['deal_status'] = $this->buildPipelineStats(Stage::class, 'deal', $crmData['total_' . DatabaseConstants::TABLE_DEALS]);
                    $this->logExecutionTime($startBuild, "{$action} buildStats", 'completed');
                    $startLatest = microtime(true);
                    $crmData['latestContract'] = Contract::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                        ->with([DatabaseConstants::TABLE_CLIENTS, DatabaseConstants::TABLE_PROJECTS, 'types'])
                        ->latest()->limit(5)->get();
                    $this->logExecutionTime($startLatest, "{$action} fetchLatestContracts", 'completed');
                    $viewName = ViewsConstants::DSB . '.crm_dashboard';
                    if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    Log::info("[$action] rendering view", ['data_keys' => array_keys($crmData)]);
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('crmData'));
                } catch (\Throwable $e) {
                    Log::error("[$action] error", ['err' => $e->getMessage()]);
                    return defaultUndefinedException($req, $e, "{$method}");
                }
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const POS_DSB_IDX = 'posDashboardIndex';
    public function posDashboardIndex(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                $startGuard = microtime(true);
                if ($r = self::guard($req, PermissionsConstants::SHW_POS_DSB, Redirect::back())) return $this->accountDashboardIndex($req);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                $startRole = microtime(true);
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::ADM || $user[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $viewName = PermissionsConstants::ADM . '.' . self::ENTITY;
                    if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    Log::info("[$action] rendering admin view");
                    $this->logExecutionTime($startOverall, "{$action} renderAdmin", 'completed');
                    return view($viewName);
                }
                $this->logExecutionTime($startRole, "{$action} roleCheck", 'completed');
                try {
                    $startFetch = microtime(true);
                    $posData = ['monthlyPosAmount' => Pos::totalPosAmount(true), 'totalPosAmount' => Pos::totalPosAmount(), 'monthlyPurchaseAmount' => Purchase::totalPurchaseAmount(true), 'totalPurchaseAmount' => Purchase::totalPurchaseAmount()];
                    $purchasesArray = Purchase::getPurchaseReportChart();
                    $posesArray = Pos::getPosReportChart();
                    $this->logExecutionTime($startFetch, "{$action} fetchData", 'completed');
                    $viewName = ViewsConstants::DSB . '.pos_dashboard';
                    if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    Log::info("[$action] rendering view", ['data_keys' => array_keys($posData)]);
                    $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                    return view($viewName, compact('posData', 'purchasesArray', 'posesArray'));
                } catch (\Throwable $e) {
                    Log::error("[$action] error", ['err' => $e->getMessage()]);
                    return defaultUndefinedException($req, $e, "{$method}");
                }
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const FT_VW = 'filterView';
    public function filterView(Request $req): JsonResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            $startOverall = microtime(true);
            try {
                Log::info("[$action] start", ['user_id' => Auth::id(), 'keyword' => $req->keyword]);
                if (!$req->ajax()) return null;
                $this->logExecutionTime($startOverall, "{$action} ajaxCheck", 'completed');
                $users = User::where('id', '!=', Auth::id());
                if ($kw = $req->keyword) {
                    $users->where(fn($q) => $q->where(UsersConstants::COL_NM, 'like', "{$kw}%")->orWhereRaw('find_in_set(?,skills)', [$kw]));
                    Log::info("[$action] applied filter", ['keyword' => $kw]);
                }
                $list = $users->get();
                $html = view(ViewsConstants::DSB . '.view', compact('list'))->render();
                Log::info("[$action] returning html", ['count' => $list->count()]);
                $this->logExecutionTime($startOverall, "{$action} renderHtml", 'completed');
                return response()->json(['success' => true, 'html' => $html]);
            } catch (\Throwable $e) {
                Log::error("[$action] error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "{$method}");
            }
        }, ['req' => $req]);
    }

    public const CL_VW = 'clientView';
    public function clientView(Request $req): Response|RedirectResponse|int
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($req, $action, $method) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                Log::info("[$action] start", ['user_id' => $user->id, 'type' => $user[UsersConstants::COL_TP]]);
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $metrics = ['total_user' => $user->countCompany(), 'total_paid_user' => $user->countPaidCompany(), 'totalOrders' => Order::totalOrders(), 'totalOrders_price' => Order::totalOrdersPrice(), 'total_plan' => Plan::totalPlan(), 'most_purchase_plan' => optional(Plan::mostPurchasePlan())->total ?? 0];
                    $chartData = $this->getOrderChart(['duration' => 'week']);
                    $viewName = ViewsConstants::DSB . '.super_admin';
                    if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                    Log::info("[$action] rendering super admin");
                    $this->logExecutionTime($startOverall, "{$action} renderSuperAdmin", 'completed');
                    return view($viewName, compact('metrics', 'chartData'));
                }
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::CL) {
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
                        $dealIds = $user->clientDeals->pluck('id');
                        $arrCount = ['deal' => $dealIds->count(), 'task' => $dealIds->isEmpty() ? 0 : DealTask::whereIn(ActivitiesConstants::COL_DL, [$dealIds->first()])->count()];
                        $projects = Project::where('client_id', $user->id)->where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->where(ProjectsConstants::COL_E_DT, '>', $today)->orderBy(ProjectsConstants::COL_E_DT)->limit(5)->get();
                        $projectIds = $projects->pluck('id');
                        $tasksCount = ProjectTask::whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->count();
                        $projectBudget = Project::where('client_id', $user->id)->sum('budget');
                        $projectMetrics = [DatabaseConstants::TABLE_PROJECTS => $projects, 'projects_count' => $projects->count(), 'projects_tasks_count' => $tasksCount, 'project_budget' => $projectBudget];
                        $totalProjects = $user->userProject();
                        $totalTasks = $user->createdTotalProjectTask();
                        $allProjects = Project::where('client_id', $user->id)->where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->get();
                        $allCount = $allProjects->count();
                        $completedCount = Project::where('client_id', $user->id)->where(ActivitiesConstants::COL_TSK_STT, ProjectsConstants::STT_CPT_K)->where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->count();
                        $bugs = Bug::whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->get();
                        $bugLastStatus = BugStatus::latest(ActivitiesConstants::COL_OD)->first();
                        $completedBugs = $bugLastStatus ? Bug::whereIn(ProjectsConstants::COL_PJ_ID, $projectIds)->where(ActivitiesConstants::COL_TSK_STT, $bugLastStatus->id)->where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->count() : 0;
                        $projectMetrics += ['projects_bugs_count' => $bugs->count(), 'project_bug_percentage' => $allCount ? intval($completedBugs / $allCount * 100) : 0, 'project_percentage' => $allCount ? intval($completedCount / $allCount * 100) : 0, 'project_task_percentage' => $totalTasks ? intval($user->projectCompleteTask($user->lastProjectStage()?->id ?? 0) / $totalTasks * 100) : 0];
                        $invoices = Invoice::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->where('client_id', $user->id)->get();
                        $dueInvoices = $invoices->filter(fn($inv) => $inv->getDue() > 0);
                        $invoiceMetrics = ['total_invoice' => $invoices->count(), 'complete_invoice' => $invoices->where(fn($inv) => $inv->getDue() === 0)->count(), 'due_amount' => $dueInvoices->sum(fn($inv) => $inv->getDue()), 'top_due_invoice' => $dueInvoices->sortByDesc(fn($inv) => $inv->getDue())->take(5)->values()];
                        $usersMetrics = ['staff' => User::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->count(), 'user' => User::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)->count(), PermissionsConstants::CL => User::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->where(UsersConstants::COL_TP, PermissionsConstants::CL)->count()];
                        $projectStatus = array_values(Project::$project_status);
                        $projectData = Project::getProjectStatus();
                        $taskData = \App\Models\TaskStage::getChartData();
                        $viewName = ViewsConstants::DSB . '.client_view';
                        if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                        Log::info("[$action] rendering client view");
                        $this->logExecutionTime($startOverall, "{$action} renderClient", 'completed');
                        return view($viewName, compact('calendarTasks', 'arrCount', 'chartData', 'projectMetrics', 'invoiceMetrics', 'usersMetrics', 'projectStatus', 'projectData', 'taskData'));
                    } catch (\Throwable $e) {
                        Log::error("[$action] error", ['err' => $e->getMessage()]);
                        return defaultUndefinedException($req, $e, "{$method}");
                    }
                }
                abort(HttpResponse::HTTP_FORBIDDEN, 'Permission denied.');
                return 403;
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    public const GET_OC = 'getOrderChart';
    public function getOrderChart(array $params): array
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($params, $action) {
            $data = [];
            try {
                $startOverall = microtime(true);
                Log::info("[$action] start", ['params' => $params]);
                $labels = [];
                if (($d = $params['duration'] ?? null) === 'week') {
                    Log::info("[$action] building weekly chart", ['duration' => $d]);
                    $start = now()->subDays(13);
                    $labels = collect()->times(14)->mapWithKeys(fn($i) => [$start->copy()->addDays($i)->toDateString() => $start->copy()->addDays($i)->format('d-M')])->all();
                }
                $data = ['label' => array_values($labels), 'data' => []];
                foreach ($labels as $date => $lbl) {
                    Log::debug("[$action] processing", ['date' => $date, 'label' => $lbl]);
                    $data['data'][] = Order::whereDate('created_at', $date)->count();
                }
                $this->logExecutionTime($startOverall, "{$action} buildData", 'completed');
                return $data;
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                return $data;
            }
        }, []);
    }

    public const STP_TRK = 'stopTracker';
    public function stopTracker(Request $req): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($req, $action) {
            try {
                $startOverall = microtime(true);
                $startLogin = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                    return response()->json(['error' => 'Unauthenticated.'], 401);
                }
                $this->logExecutionTime($startLogin, "{$action} loginCheck", 'completed');
                $user = $userOrRedirect;
                Log::info("[$action] start", ['user_id' => $user->id, 'input' => $req->all()]);
                if ($user->isClient()) {
                    Log::warning("[$action] denied for client", ['user_id' => $user->id]);
                    return Utility::errorRes(__('Permission denied.'));
                }
                $startVal = microtime(true);
                $v = Validator::make($req->all(), ['name' => 'required|string|max:120', ProjectsConstants::COL_PJ_ID => 'required|integer']);
                if ($v->fails()) {
                    Log::warning("[$action] validation failed", ['errors' => $v->errors()->all()]);
                    return Utility::errorRes($v->errors()->first());
                }
                $this->logExecutionTime($startVal, "{$action} validation", 'completed');
                DB::beginTransaction();
                try {
                    $startTrack = microtime(true);
                    $tracker = TimeTracker::where(DatabaseConstants::COL_TABLE_CREATOR, $user->id)->where(ActivitiesConstants::COL_IA, 1)->firstOrFail();
                    $end = $req->input(ActivitiesConstants::COL_E_TIME, now()->toDateTimeString());
                    $tracker->update([ActivitiesConstants::COL_E_TIME => $end, ActivitiesConstants::COL_IA => 0, ActivitiesConstants::COL_TTL_TIME => Utility::differenceToTime($tracker[ActivitiesConstants::COL_ST_TIME], $end)]);
                    DB::commit();
                    $this->logExecutionTime($startTrack, "{$action} updateTracker", 'completed');
                    Log::info("[$action] stopped", ['tracker_id' => $tracker->id]);
                    $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                    return Utility::successRes(__('Add Time successfully.'));
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error("[$action] failed", ['err' => $e->getMessage()]);
                    return Utility::errorRes(__('Tracker not found.'));
                }
            } catch (\Throwable $e) {
                Log::error("[$action] error", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    $e->getLine()
                ]);
                Redirect::back()->with('error', "HTTP 500: Unexpected error");
            }
        }, ['req' => $req]);
    }

    private function loadConstants(int|string $creatorId): array
    {
        return [
            DatabaseConstants::TABLE_TAXES => Tax::where(
                DatabaseConstants::COL_TABLE_CREATOR,
                $creatorId
            )->count(),
            'category'    => ProductServiceCategory::where(
                DatabaseConstants::COL_TABLE_CREATOR,
                $creatorId
            )->count(),
            'units'       => ProductServiceUnit::where(
                DatabaseConstants::COL_TABLE_CREATOR,
                $creatorId
            )->count(),
            'bankAccount' => BankAccount::where(
                DatabaseConstants::COL_TABLE_CREATOR,
                $creatorId
            )->count(),
        ];
    }

    private function buildCategoryChart(string $type, int|string $creatorId): array
    {
        $cats = ProductServiceCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
            ->where(UsersConstants::COL_TP, $type)->get();
        $colors = $cats->map(fn($c) => "#{$c->color}")->toArray();
        $names = $cats->pluck('name')->toArray();
        $amounts = $cats->map(
            fn($c) =>
            $type === 'income'
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
            : SettingsConstants::MAX_SL_LIMIT_MB;
    }

    private function handleLandingOrInstall(Request $req): RedirectResponse
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
        if ($settings['display_landing_page'] === 'on' && app()->runningInConsole() === false) {
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
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $class   = $model;
        $alias   = strtolower($key);
        $items   = $class::select(
            "{$model::$table}.*",
            DatabaseConstants::TABLE_PIPELINES . '.name as pipeline'
        )
            ->join(
                DatabaseConstants::TABLE_PIPELINES,
                DatabaseConstants::TABLE_PIPELINES . '.id',
                "{$model::$table}.pipeline_id"
            )
            ->where(DatabaseConstants::TABLE_PIPELINES . '.created_by', $user?->creatorId())
            ->where("{$model::$table}.created_by", $user?->creatorId())
            ->orderBy("{$model::$table}.pipeline_id")->get();
        $stats = [];
        foreach ($items as $i => $it) {
            $count = $it->{$alias . 's'}()->count();
            $stats[$i] = [
                "{$alias}_stage"      => $it->name,
                "{$alias}_total"      => $count,
                "{$alias}_percentage" => Utility::getCrmPercentage($count, $total),
            ];
        }
        return $stats;
    }
}
