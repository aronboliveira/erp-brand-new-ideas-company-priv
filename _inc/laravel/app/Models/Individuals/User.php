<?php

namespace App\Models;

use Throwable;
use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    EmailsConstants,
    PermissionsConstants,
    PlansConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Traits\{ChecksLogin, UsesUuids};
use Carbon\Carbon;
use Illuminate\{
    Contracts\Auth\MustVerifyEmail,
    Foundation\Auth\User as Authenticatable,
    Notifications\Notifiable
};
use Illuminate\Database\{Eloquent\ModelNotFoundException, QueryException};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Illuminate\Support\Facades\{Auth, DB, Log, Storage};
use Laravel\{Fortify\TwoFactorAuthenticatable, Jetstream\HasProfilePhoto, Sanctum\HasApiTokens};
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int|string $id
 */
class User extends Authenticatable implements MustVerifyEmail

{
    use ChecksLogin,
        HasApiTokens,
        HasProfilePhoto,
        HasRoles,
        Notifiable,
        TwoFactorAuthenticatable,
        UsesUuids;

    private const APPENDS       = ['profile'];        // ! CHANGED
    private const FILLABLE_FIELDS = [
        UsersConstants::COL_NM, UsersConstants::COL_EM,
        UsersConstants::COL_PW, UsersConstants::COL_TP, UsersConstants::COL_SL,
        UsersConstants::COL_AV, UsersConstants::COL_LG, UsersConstants::COL_MD,
        UsersConstants::COL_D_ST, UsersConstants::COL_PL, UsersConstants::COL_EM_V_AT,
        UsersConstants::COL_PED, UsersConstants::COL_RP, UsersConstants::COL_IA,
        UsersConstants::COL_IB,
        UsersConstants::COL_LLA, DatabaseConstants::TABLE_CREATOR, UsersConstants::COL_MC,
        UsersConstants::COL_DPL, UsersConstants::COL_A_ST, UsersConstants::COL_DM
    ];                                              // ! CHANGED
    private const HIDDEN_FIELDS = [UsersConstants::COL_PW, UsersConstants::COL_RT]; // ! CHANGED
    private const CASTS_FIELDS  = [UsersConstants::COL_EM_V_AT => 'datetime']; // ! CHANGED
    protected $appends = self::APPENDS;              // ! CHANGED
    protected $fillable = self::FILLABLE_FIELDS;     // ! CHANGED
    protected $hidden = self::HIDDEN_FIELDS;         // ! CHANGED
    protected $casts = self::CASTS_FIELDS;           // ! CHANGED
    private const REL_PROJECTS  = DatabaseConstants::TABLE_PROJECTS;
    private const COL_PROJECT_ID = ProjectsConstants::COL_PJ_ID;
    private const DEFAULT_WAREHOUSE = [
        UsersConstants::COL_NM     => 'North Warehouse',
        'address'  => '723 N. Tillamook Street Portland, OR Portland, United States',
        'city'     => 'Portland',
        'city_zip' => 97227,
    ];

    private const DEFAULT_BANK_ACCOUNT = [
        'holder_name'     => 'cash',
        'bank_name'       => '',
        'account_number'  => '-',
        'opening_balance' => '0.00',
        'contact_number'  => '-',
        'bank_address'    => '-',
    ];

    public $settings;

    public function getProfileAttribute(): string
    {
        if (!empty($this->avatar) && Storage::exists($this->avatar))
            return $this->attributes[UsersConstants::COL_AV] =
                asset(Storage::url($this->avatar));
        return $this->attributes[UsersConstants::COL_AV] =
            asset(Storage::url('avatar.png'));
    }

    public function authId(): int|string
    {
        return $this->id;
    }

    public function creatorId(): int|string
    {
        return in_array($this[UsersConstants::COL_TP], [PermissionsConstants::CPN, PermissionsConstants::SA], true)
            ? $this->id
            : $this[DatabaseConstants::TABLE_CREATOR];
    }

    public function ownerId(): int|string
    {
        return in_array($this[UsersConstants::COL_TP], [PermissionsConstants::CPN, PermissionsConstants::SA], true)
            ? $this->id
            : $this[DatabaseConstants::TABLE_CREATOR];
    }

    public function ownerDetails(): ?self
    {
        return self::whereKey($this->ownerId())->first();
    }

    public function currentLanguage(): string
    {
        return $this->lang;
    }

    public function priceFormat(float $price): string
    {
        $settings = Utility::settings();
        $decimal = Utility::getValByName('decimal_number') ?: 0;
        return ($settings['site_currency_symbol_position'] === 'pre'
            ? $settings['site_currency_symbol'] : '')
            . number_format($price, $decimal)
            . ($settings['site_currency_symbol_position'] === 'post'
                ? $settings['site_currency_symbol'] : '');
    }

    public static function priceFormats(float $price): string
    {
        $settings = Utility::settings();
        $decimal = Utility::getValByName('decimal_number') ?: 0;
        return ($settings['site_currency_symbol_position'] === 'pre'
            ? $settings['site_currency_symbol'] : '')
            . number_format($price, $decimal)
            . ($settings['site_currency_symbol_position'] === 'post'
                ? $settings['site_currency_symbol'] : '');
    }

    public function currencySymbol(): string
    {
        return Utility::settings()['site_currency_symbol'];
    }

    public function dateFormat(string $date): string
    {
        return date(Utility::settings()['site_date_format'], strtotime($date));
    }

    public function timeFormat(string $time): string
    {
        return date(Utility::settings()['site_time_format'], strtotime($time));
    }

    public function purchaseNumberFormat(int $number): string
    {
        return Utility::settings()['purchase_prefix']
            . sprintf('%05d', $number);
    }

    public function posNumberFormat(int $number): string
    {
        return Utility::settings()['pos_prefix']
            . sprintf('%05d', $number);
    }

    public function invoiceNumberFormat(int $number): string
    {
        return Utility::settings()['invoice_prefix']
            . sprintf('%05d', $number);
    }

    public function proposalNumberFormat(int $number): string
    {
        return Utility::settings()['proposal_prefix']
            . sprintf('%05d', $number);
    }

    public function contractNumberFormat(int $number): string
    {
        return Utility::settings()['contract_prefix']
            . sprintf('%05d', $number);
    }

    public function billNumberFormat(int $number): string
    {
        return Utility::settings()['bill_prefix']
            . sprintf('%05d', $number);
    }

    public function expenseNumberFormat(int $number): string
    {
        return Utility::settings()['expense_prefix']
            . sprintf('%05d', $number);
    }

    public function journalNumberFormat(int $number): string
    {
        return Utility::settings()['journal_prefix']
            . sprintf('%05d', $number);
    }

    public function getPlan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', UsersConstants::COL_PL);
        // * consider belongsTo(Plan::class,UsersConstants::COL_PL)
    }

    public function assignPlan(int|string $planId, int|string $companyId = 0): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $plan = Plan::find($planId);
        if (!$plan) return ['is_success' => false, 'error' => 'Plan is deleted.'];
        $this[UsersConstants::COL_PL] = $plan->id;
        $this[UsersConstants::COL_PED] = match ($plan[PlansConstants::COL_DUR]) {
            'month' => Carbon::now()->addMonth()->isoFormat('YYYY-MM-DD'),
            'year'  => Carbon::now()->addYear()->isoFormat('YYYY-MM-DD'),
            default => null
        };
        $this->save();
        $userId = $companyId ?: $user?->creatorId();
        $users = User::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereNotIn(UsersConstants::COL_TP, [
                PermissionsConstants::SA, PermissionsConstants::CPN,
                PermissionsConstants::CL
            ])->get();
        $clients = User::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->where(UsersConstants::COL_TP, PermissionsConstants::CL)->get();
        $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $userId)->get();
        $vendors = Vendor::where(DatabaseConstants::TABLE_CREATOR, $userId)->get();
        foreach ([
            PlansConstants::COL_MAX_U  => $users,
            PlansConstants::COL_MAX_CL => $clients,
            PlansConstants::COL_MAX_CR => $customers,
            PlansConstants::COL_MAX_V  => $vendors,
        ] as $prop => $collection)
            $this->_syncActive($collection, $plan->{$prop});
        return ['is_success' => true];
    }

    public function customerNumberFormat(int $number): string
    {
        return Utility::settings()['customer_prefix']
            . sprintf('%05d', $number);
    }

    public function vendorNumberFormat(int $number): string
    {
        return Utility::settings()['vendor_prefix']
            . sprintf('%05d', $number);
    }

    public function venderNumberFormat(int $number): string // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINT
    {
        return $this->vendorNumberFormat($number);
    }

    public function countUsers(): int
    {
        return User::whereNotIn(UsersConstants::COL_TP, [
            PermissionsConstants::SA, PermissionsConstants::CPN,
            PermissionsConstants::CL
        ])
            ->where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())
            ->count();
    }

    public function countCompany(): int
    {
        return User::where(UsersConstants::COL_TP, PermissionsConstants::CPN)
            ->where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())
            ->count();
    }

    public function countOrder(): int
    {
        return Order::count();
    }

    public function countPlan(): int
    {
        return Plan::count();
    }

    public function countPaidCompany(): int
    {
        return User::where(UsersConstants::COL_TP, PermissionsConstants::CPN)
            ->whereNotIn(UsersConstants::COL_PL, [0, 1])
            ->where(DatabaseConstants::TABLE_CREATOR, Auth::user()->id)
            ->count();
    }

    public function countCustomers(): int
    {
        return Customer::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function countVendors(): int
    {
        return Vendor::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function countVenders(): int // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINT
    {
        return $this->countVendors();
    }

    public function countInvoices(): int
    {
        return Invoice::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function countBills(): int
    {
        return Bill::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function todayIncome(): float
    {
        $userId = $this->creatorId();
        $revenue = Revenue::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereDate('date', today())->sum('amount');
        $invoices = Invoice::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereDate('send_date', today())->get();
        $invoiceTotal = $invoices->sum(fn ($inv) => $inv->getTotal());
        return $revenue + $invoiceTotal;
    }

    public function todayExpense(): float
    {
        $userId = $this->creatorId();
        $payment = Payment::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereDate('date', today())->sum('amount');
        $bills = Bill::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereDate('send_date', today())->get();
        $billTotal = $bills->sum(fn ($b) => $b->getTotal());
        return $payment + $billTotal;
    }

    public function incomeCurrentMonth(): float
    {
        $userId = $this->creatorId();
        $month = now()->month;
        $revenue = Revenue::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereMonth('date', $month)->sum('amount');
        $invoices = Invoice::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereMonth('send_date', $month)->get();
        $invoiceTotal = $invoices->sum(fn ($inv) => $inv->getTotal());
        return $revenue + $invoiceTotal;
    }

    public function incomeCat(): float
    {
        $userId = $this->creatorId();
        $month = now()->month;
        // total revenue by product/service category type = 1
        $incomeByCategory = Revenue::join(
            'product_service_categories',
            'revenues.category_id',
            '=',
            'product_service_categories.id'
        )
            ->where('product_service_categories.type', 1)
            ->where('revenues.created_by', $userId)
            ->whereMonth('revenues.date', $month)
            ->selectRaw('SUM(revenues.amount) AS total, revenues.category_id')
            ->groupBy('revenues.category_id')
            ->pluck('total')
            ->toArray();
        $incomesSum = array_sum($incomeByCategory);
        $invoiceTotal = Invoice::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereMonth('send_date', $month)
            ->get()
            ->sum(fn ($inv) => $inv->getTotal());
        return $incomesSum + $invoiceTotal;
    }

    public function expenseCurrentMonth(): float
    {
        $userId = $this->creatorId();
        $payment = Payment::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereMonth('date', now()->month)->sum('amount');
        $billTotal = Bill::where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->whereMonth('send_date', now()->month)
            ->get()->sum(fn ($b) => $b->getTotal());
        return $payment + $billTotal;
    }

    public function getIncExpBarChartData(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $months = [
            __('January'), __('February'), __('March'), __('April'),
            __('May'), __('June'), __('July'), __('August'),
            __('September'), __('October'), __('November'), __('December')
        ];
        $dataArr['month'] = $months;
        $userId = $user?->creatorId();
        $year = now()->year;
        $incomeArr = [];
        $expenseArr = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyIncome = Revenue::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereYear('date', $year)->whereMonth('date', $i)
                ->sum('amount');
            $invoiceSum = Invoice::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereYear('send_date', $year)->whereMonth('send_date', $i)
                ->get()->sum(fn ($inv) => $inv->getTotal());
            $incomeArr[] = (float) ($monthlyIncome + $invoiceSum);

            $monthlyExpense = Payment::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereYear('date', $year)->whereMonth('date', $i)
                ->sum('amount');
            $billSum = Bill::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereYear('send_date', $year)->whereMonth('send_date', $i)
                ->get()->sum(fn ($b) => $b->getTotal());
            $expenseArr[] = (float) ($monthlyExpense + $billSum);
        }
        $dataArr['income'] = $incomeArr;
        $dataArr['expense'] = $expenseArr;
        return $dataArr;
    }

    public function getIncExpLineChartDate(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $userId = $user?->creatorId();
        $dates = collect(range(0, 14))
            ->map(fn ($i) => now()->subDays($i)->format('Y-m-d'))
            ->reverse()
            ->values();
        $labels = $dates->map(
            fn ($d) =>
            Carbon::parse($d)->format('d-M')
        )->toArray();
        $incomeArr = $dates->map(
            fn ($d) =>
            Revenue::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereDate('date', $d)->sum('amount')
                + Invoice::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereDate('send_date', $d)
                ->get()->sum(fn ($inv) => $inv->getTotal())
        )->toArray();
        $expenseArr = $dates->map(
            fn ($d) =>
            Payment::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereDate('date', $d)->sum('amount')
                + Bill::where(DatabaseConstants::TABLE_CREATOR, $userId)
                ->whereDate('send_date', $d)
                ->get()->sum(fn ($b) => $b->getTotal())
        )->toArray();
        return [
            'day'     => $labels,
            'income'  => $incomeArr,
            'expense' => $expenseArr
        ];
    }

    public function totalCompanyUser(int|string $id): int
    {
        return self::where(DatabaseConstants::TABLE_CREATOR, $id)->count();
    }

    public function totalCompanyCustomer(int|string $id): int
    {
        return Customer::where(DatabaseConstants::TABLE_CREATOR, $id)->count();
    }

    public function totalCompanyVendor(int|string $id): int
    {
        return Vendor::where(DatabaseConstants::TABLE_CREATOR, $id)->count();
    }

    public function totalCompanyVender(int|string $id): int // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINT
    {
        return $this->totalCompanyVendor($id);
    }

    public function planPrice(): array
    {
        $user = Auth::user();
        $userId = $user[UsersConstants::COL_TP] === PermissionsConstants::SA
            ? $user?->id
            : $user[DatabaseConstants::TABLE_CREATOR];
        return DB::table(DatabaseConstants::TABLE_SETTINGS)
            ->where(DatabaseConstants::TABLE_CREATOR, $userId)
            ->pluck('value', UsersConstants::COL_NM)
            ->toArray();
    }

    public function currentPlan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', UsersConstants::COL_PL);
        // * consider belongsTo(Plan::class,UsersConstants::COL_PL)
    }

    public function weeklyInvoice(): array
    {
        $start = now()->subWeek()->toDateString();
        $end  = now()->toDateString();
        $invoices = Invoice::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('issue_date', [$start, $end])->get();
        $invoiceTotal = $invoices->sum(fn ($inv) => $inv->getTotal());
        $invoicePaid = $invoices->sum(fn ($inv) => $inv->getTotal() - $inv->getDue());
        $invoiceDue  = $invoices->sum(fn ($inv) => $inv->getDue());
        return [
            'invoiceTotal' => $invoiceTotal,
            'invoicePaid' => $invoicePaid,
            'invoiceDue' => $invoiceDue
        ];
    }

    public function monthlyInvoice(): array
    {
        $start = now()->subMonth()->toDateString();
        $end  = now()->toDateString();
        $invoices = Invoice::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('issue_date', [$start, $end])->get();
        $invoiceTotal = $invoices->sum(fn ($inv) => $inv->getTotal());
        $invoicePaid = $invoices->sum(fn ($inv) => $inv->getTotal() - $inv->getDue());
        $invoiceDue  = $invoices->sum(fn ($inv) => $inv->getDue());
        return [
            'invoiceTotal' => $invoiceTotal,
            'invoicePaid' => $invoicePaid,
            'invoiceDue' => $invoiceDue
        ];
    }

    public function weeklyBill(): array
    {
        $start = now()->subWeek()->toDateString();
        $end  = now()->toDateString();
        $bills = Bill::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('bill_date', [$start, $end])->get();
        $billTotal = $bills->sum(fn ($b) => $b->getTotal());
        $billPaid = $bills->sum(fn ($b) => $b->getTotal() - $b->getDue());
        $billDue  = $bills->sum(fn ($b) => $b->getDue());
        return [
            'billTotal' => $billTotal,
            'billPaid' => $billPaid,
            'billDue' => $billDue
        ];
    }

    public function monthlyBill(): array
    {
        $start = now()->subMonth()->toDateString();
        $end  = now()->toDateString();
        $bills = Bill::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('bill_date', [$start, $end])->get();
        $billTotal = $bills->sum(fn ($b) => $b->getTotal());
        $billPaid = $bills->sum(fn ($b) => $b->getTotal() - $b->getDue());
        $billDue  = $bills->sum(fn ($b) => $b->getDue());
        return [
            'billTotal' => $billTotal,
            'billPaid' => $billPaid,
            'billDue' => $billDue
        ];
    }

    public function clientEstimations(): HasMany
    {
        return $this->hasMany(Estimation::class, 'client_id', 'id');
    }

    public function clientContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_name', 'id');
    }

    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'user_deals', UsersConstants::COL_USER_ID, ActivitiesConstants::COL_DL);
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'user_leads', UsersConstants::COL_USER_ID, 'lead_id');
    }

    public function clientDeals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'client_deals', 'client_id', ActivitiesConstants::COL_DL);
    }

    public function getBranch(int|string $branchId): ?Branch
    {
        return Branch::whereKey($branchId)->first();
    }

    public function getDepartment(int|string $departmentId): ?Department
    {
        return Department::whereKey($departmentId)->first();
    }

    public function getDesignation(int|string $designationId): ?Designation
    {
        return Designation::whereKey($designationId)->first();
    }

    public function getEmployee(int|string $employee): ?Employee
    {
        return Employee::whereKey($employee)->first();
    }

    public function getLeaveType(int|string $leaveType): ?LeaveType
    {
        return LeaveType::whereKey($leaveType)->first();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users', UsersConstants::COL_USER_ID, ProjectsConstants::COL_PJ_ID)
            ->withTimestamps();
    }

    public function checkProject(int|string $projectId): string
    {
        $ids = $this->{self::REL_PROJECTS}
            ->pluck(self::COL_PROJECT_ID)
            ->toArray();
        return in_array($projectId, $ids, true)
            ? 'Owner'
            : 'Not Owner';
    }

    public function getImgImageAttribute(): string
    {
        $detail = Employee::where(UsersConstants::COL_USER_ID, $this->id)->first();
        if ($detail && !empty($detail->avatar))
            return asset(Storage::url($detail->avatar));
        return asset(Storage::url('avatar.png'));
    }

    public function tasks(): \Illuminate\Support\Collection
    {
        $user = Auth::check() ? Auth::user() : self::find($this->id);
        if ($user[UsersConstants::COL_TP] === PermissionsConstants::CPN)
            return ProjectTask::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
        return ProjectTask::whereRaw("find_in_set('{$this->id}'," . ProjectsConstants::COL_ASGN . ")")->get();
    }

    public function bugNumberFormat(int $number): string
    {
        return Utility::settings()['bug_prefix'] . sprintf('%05d', $number);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class, 'parent_id', 'id');
    }

    public function todo(): HasMany
    {
        return $this->hasMany(UserToDo::class, UsersConstants::COL_USER_ID, 'id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, UsersConstants::COL_USER_ID, 'id');
    }

    public function totalLead(): int
    {
        return Auth::user()[UsersConstants::COL_TP] === PermissionsConstants::CPN
            ? Lead::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())->count()
            : (Auth::user()[UsersConstants::COL_TP] === PermissionsConstants::CL
                ? Lead::where(PermissionsConstants::CL, $this->authId())->count()
                : Lead::where('owner', $this->authId())->count());
    }

    public function lastProjectStage(): ?TaskStage
    {
        return TaskStage::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())
            ->orderByDesc(ActivitiesConstants::COL_OD)->first();
    }

    public function userProject(): int
    {
        return Auth::user()[UsersConstants::COL_TP] !== PermissionsConstants::CL
            ? $this->projects()->count()
            : Project::where('client_id', $this->authId())->count();
    }

    public function createdTotalProjectTask(): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $userId = $user?->creatorId();
        return match (Auth::user()[UsersConstants::COL_TP]) {
            PermissionsConstants::CPN => ProjectTask::join(
                DatabaseConstants::TABLE_PROJECTS,
                DatabaseConstants::TABLE_PROJECTS . 'id',
                '=',
                DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_PJ_ID
            )
                ->where(DatabaseConstants::TABLE_PROJECTS . DatabaseConstants::TABLE_CREATOR, $userId)->count(),
            PermissionsConstants::CL  => ProjectTask::join(
                DatabaseConstants::TABLE_PROJECTS,
                DatabaseConstants::TABLE_PROJECTS . 'id',
                '=',
                DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_PJ_ID
            )
                ->where(DatabaseConstants::TABLE_PROJECTS . 'client_id', $user?->authId())->count(),
            default   => ProjectTask::join(
                'project_users',
                'project_users.' . ProjectsConstants::COL_PJ_ID,
                '=',
                DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_PJ_ID
            )
                ->where('project_users.' . UsersConstants::COL_USER_ID, $user?->authId())->count(),
        };
    }

    public function projectCompleteTask(int|string $projectLastStage): int
    {
        $user = Auth::user();
        return match ($user[UsersConstants::COL_TP]) {
            PermissionsConstants::CPN => ProjectTask::join(
                DatabaseConstants::TABLE_PROJECTS,
                DatabaseConstants::TABLE_PROJECTS . 'id',
                '=',
                DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_PJ_ID
            )
                ->where(DatabaseConstants::TABLE_PROJECTS . DatabaseConstants::TABLE_CREATOR, $this->creatorId())
                ->where(DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_STAGE_ID, $projectLastStage)
                ->count(),
            PermissionsConstants::CL  => ProjectTask::whereIn(
                ProjectsConstants::COL_PJ_ID,
                Project::where('client_id', $user?->id)->pluck('id')
            )
                ->where(DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_STAGE_ID, $projectLastStage)
                ->count(),
            default   => ProjectTask::join(
                'project_users',
                'project_users.' . ProjectsConstants::COL_PJ_ID,
                '=',
                DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_PJ_ID
            )
                ->where('project_users.' . UsersConstants::COL_USER_ID, $this->authId())
                ->where(DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_STAGE_ID, $projectLastStage)
                ->count(),
        };
    }

    public function createdTopDueTask(): \Illuminate\Support\Collection
    {
        $user = Auth::user();
        $query = ProjectTask::query();
        if ($user[UsersConstants::COL_TP] === PermissionsConstants::CPN) {
            $query->join(
                DatabaseConstants::TABLE_PROJECTS,
                DatabaseConstants::TABLE_PROJECTS . 'id',
                '=',
                DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_PJ_ID
            )
                ->where(DatabaseConstants::TABLE_PROJECTS . DatabaseConstants::TABLE_CREATOR, $this->creatorId());
        } elseif ($user[UsersConstants::COL_TP] === PermissionsConstants::CL) {
            $query->whereIn(ProjectsConstants::COL_PJ_ID, Project::where('client_id', $user?->id)->pluck('id'));
        } else {
            $query->select(
                DatabaseConstants::TABLE_PROJ_TSKS . '.*',
                'project_users.id as up_id',
                DatabaseConstants::TABLE_PROJECTS . '.project_name',
                DatabaseConstants::TABLE_PROJ_STAGES . '.name as stage_name'
            )
                ->join(
                    'project_users',
                    'project_users.' . ProjectsConstants::COL_PJ_ID,
                    '=',
                    DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_PJ_ID
                )
                ->join(
                    DatabaseConstants::TABLE_PROJECTS,
                    DatabaseConstants::TABLE_PROJECTS . 'id',
                    '=',
                    'project_users.' . ProjectsConstants::COL_PJ_ID
                )
                ->join(
                    DatabaseConstants::TABLE_PROJ_STAGES,
                    DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_STAGE_ID,
                    '=',
                    DatabaseConstants::TABLE_PROJ_STAGES . '.id'
                )
                ->where('project_users.' . UsersConstants::COL_USER_ID, $this->authId());
        }
        return $query->where(
            DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_E_DT,
            '>',
            now()->toDateString()
        )
            ->orderBy(DatabaseConstants::TABLE_PROJ_TSKS . '.' . ProjectsConstants::COL_E_DT, 'ASC')
            ->limit(5)->get();
    }

    public function showDashboard(): int|string
    {
        $user = Auth::user();
        if ($user instanceof User)
            $user = in_array($user->{UsersConstants::COL_TP}, [PermissionsConstants::CPN, PermissionsConstants::SA], true)
                ? $user
                : self::find($user->{DatabaseConstants::TABLE_CREATOR});
        return $user?->plan ?? DatabaseConstants::DEFAULT_PLAN;
    }

    public static function showCrm(): string
    {
        $user = in_array(Auth::user()[UsersConstants::COL_TP], [PermissionsConstants::CPN, PermissionsConstants::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DatabaseConstants::TABLE_CREATOR]);
        return Plan::find($user?->plan)->crm ?? '';
    }

    public static function showHrm(): string
    {
        $user = in_array(Auth::user()[UsersConstants::COL_TP], [PermissionsConstants::CPN, PermissionsConstants::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DatabaseConstants::TABLE_CREATOR]);
        return Plan::find($user?->plan)->hrm ?? '';
    }

    public static function showAccount(): string
    {
        $user = in_array(Auth::user()[UsersConstants::COL_TP], [PermissionsConstants::CPN, PermissionsConstants::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DatabaseConstants::TABLE_CREATOR]);
        return Plan::find($user?->plan)->account ?? '';
    }

    public static function showProject(): string
    {
        $user = in_array(Auth::user()[UsersConstants::COL_TP], [PermissionsConstants::CPN, PermissionsConstants::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DatabaseConstants::TABLE_CREATOR]);
        return Plan::find($user?->plan)->project ?? '';
    }

    public static function showPos(): string
    {
        $user = in_array(Auth::user()[UsersConstants::COL_TP], [PermissionsConstants::CPN, PermissionsConstants::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DatabaseConstants::TABLE_CREATOR]);
        return Plan::find($user?->plan)->pos ?? '';
    }

    public function clientProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id', 'id');
    }

    public function isUser(): int
    {
        return $this[UsersConstants::COL_TP] === 'user' ? 1 : 0;
    }

    public function isClient(): int
    {
        return $this[UsersConstants::COL_TP] === PermissionsConstants::CL ? 1 : 0;
    }

    public static function defaultEmail(string $createdBy): void
    {
        foreach (EmailsConstants::STATUS_MAP as $slug => $title) {
            try {
                if (!EmailTemplate::where(EmailsConstants::COL_SLG, $slug)->exists())
                    EmailTemplate::create([
                        EmailsConstants::COL_TT       => $title,
                        EmailsConstants::COL_FROM       => config('app.name'),
                        EmailsConstants::COL_SLG           => $slug,
                        DatabaseConstants::TABLE_CREATOR => $createdBy,
                    ]);
            } catch (ModelNotFoundException $e) {
                Log::warning("defaultEmail: missing model for slug [{$slug}]", ['exception' => $e]);
            } catch (QueryException $e) {
                Log::error("defaultEmail: DB error inserting [$slug]: {$e->getMessage()}", [
                    'sql' => $e->getSql(), 'bindings' => $e->getBindings()
                ]);
            } catch (Throwable $e) {
                Log::critical("defaultEmail: unexpected error for [$slug]: {$e->getMessage()}", ['exception' => $e]);
            }
        }
    }

    public static function userDefaultData(): void
    {
        foreach (EmailTemplate::all() as $tmpl) {
            try {
                UserEmailTemplate::firstOrCreate([
                    EmailsConstants::COL_TMP => $tmpl->id,
                    UsersConstants::COL_USER_ID     => DatabaseConstants::DEFAULT_UUID,
                ], [
                    EmailsConstants::COL_IA   => true,
                    DatabaseConstants::TABLE_CREATOR    => DatabaseConstants::DEFAULT_UUID,
                ]);
            } catch (QueryException $e) {
                Log::error("userDefaultData: DB error for template {$tmpl->id}", [
                    'sql' => $e->getSql(), 'bindings' => $e->getBindings()
                ]);
            } catch (Throwable $e) {
                Log::critical("userDefaultData: unexpected for template {$tmpl->id}: {$e->getMessage()}");
            }
        }
    }

    public const USR_DEF_DT_REG = 'userDefaultDataRegister';
    public static function userDefaultDataRegister(string $userId): void
    {
        foreach (EmailTemplate::all() as $tmpl) {
            try {
                UserEmailTemplate::firstOrCreate([
                    EmailsConstants::COL_TMP => $tmpl->id,
                    UsersConstants::COL_USER_ID     => $userId,
                ], [
                    EmailsConstants::COL_IA   => true,
                    DatabaseConstants::TABLE_CREATOR    => $userId,
                ]);
            } catch (\Throwable $e) {
                Log::error("userDefaultDataRegister: failed for user {$userId}, tmpl {$tmpl->id}: {$e->getMessage()}");
            }
        }
    }

    public const USR_DEF_WH = 'userDefaultWarehouse';
    public static function userDefaultWarehouse(): void
    {
        Warehouse::create([
            UsersConstants::COL_NM => 'North Warehouse',
            'address' => '723 N. Tillamook Street Portland, OR Portland, United States',
            'city' => 'Portland',
            'city_zip' => 97227,
            DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
        ]);
    }

    public const USR_WA_REG = 'userDefaultWarehouse';
    public function userWarehouseRegister(int|string $userId): void
    {
        Warehouse::create([
            UsersConstants::COL_NM => 'North Warehouse',
            'address' => '723 N. Tillamook Street Portland, OR Portland, United States',
            'city' => 'Portland',
            'city_zip' => 97227,
            DatabaseConstants::TABLE_CREATOR => $userId
        ]);
    }

    public const USR_DEF_BA = 'userDefaultBankAccount';
    public function userDefaultBankAccount(int|string $userId): void
    {
        BankAccount::create([
            'holder_name' => 'cash',
            'bank_name' => '',
            'account_number' => '-',
            'opening_balance' => '0.00',
            'contact_number' => '-',
            'bank_address' => '-',
            DatabaseConstants::TABLE_CREATOR => $userId
        ]);
    }

    public function extraKeyword(): array
    {
        return [
            __('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'),
            __('Last 7 Days'), __('In Progress'), __('Complete'), __('Canceled'),
            __('Lead User Name'), __('CRM'), __('POS'), __('HRM'), __('Old Stage Name'),
            __('New Stage Name'), __('Contract Start Date'), __('Contract Name'),
            __('Contract Price'), __('Branch Name'), __('Support User Name'),
            __('Award Date'), __('Holiday Title'), __('Holiday Date'),
            __('Event Start Date'), __('Company Policy Name'),
            __('Invoice Issue Date'), __('Invoice Due Date'),
            __('Budget Name'), __('Budget Year'), __('Revenue Amount'),
            __('Revenue Date'), __('Payment Price'), __('New User'),
            __('Lifetime'), __('Coupon'), __('CoinGate'), __('Cashflow')
        ];
    }

    public function barcodeFormat(): string
    {
        $settings = Utility::settings();
        return $settings['barcode_format'] ?? 'code128';
    }

    public function barcodeType(): string
    {
        $settings = Utility::settings();
        return $settings['barcode_type'] ?? 'css';
    }

    public static function employeeIdFormat(int $number): string
    {
        $settings = Utility::settings();
        return $settings['employee_prefix'] . sprintf('%05d', $number);
    }

    public static function userCurrentLocation(): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $companyId = $user?->creatorId(); // * method name might differ
        $user = Auth::user();
        if ($user->type == PermissionsConstants::CPN) {
            $loc = Location::where([
                'id' => $user?->current_location,
                'company_id' => $companyId,
                UsersConstants::COL_IA => 1
            ])->first();
            return $loc->id ?? 0;
        }
        if ($user instanceof User && $user->current_location == 0)
            $user->current_location = $user?->location_id;
        $loc = Location::where('id', $user?->current_location)
            ->where('company_id', $companyId)->first();
        return $loc->id ?? 0;
    }

    public function countEmployees(): int
    {
        return Employee::where(DatabaseConstants::TABLE_CREATOR, $this->creatorId())->count();
    }

    private function _syncActive(\Illuminate\Support\Collection $items, int $max): void
    {
        if ($max === -1)
            $items->each(fn ($m) => $m->fill([UsersConstants::COL_IA => 1])->save());
        else
            $items->each(function ($m, $i) use ($max) {
                $m->is_active = $i < $max ? 1 : 0;
                $m->save();
            });
    }

    private static function createWarehouse(int|string $userId): void
    {
        Warehouse::create(self::DEFAULT_WAREHOUSE + [DatabaseConstants::TABLE_CREATOR => $userId]);
    }

    private static function createBankAccount(int|string $userId): void
    {
        BankAccount::create(self::DEFAULT_BANK_ACCOUNT + [DatabaseConstants::TABLE_CREATOR => $userId]);
    }
}
