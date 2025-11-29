<?php

namespace App\Models;

use Throwable;
use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    EmailsConstants,
    PermissionsConstants as PMC,
    PlansConstants as PLC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, DB, Log, Storage};
use Illuminate\Http\RedirectResponse;
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

    private const APPENDS       = ['profile'];
    private const FILLABLE_FIELDS = [
        UC::COL_NM,
        UC::COL_EM,
        UC::COL_PW,
        UC::COL_TP,
        UC::COL_SL,
        UC::COL_AV,
        UC::COL_LG,
        UC::COL_MD,
        UC::COL_D_ST,
        UC::COL_PL,
        UC::COL_EM_V_AT,
        UC::COL_PED,
        UC::COL_RP,
        UC::COL_IA,
        UC::COL_IB,
        UC::COL_LLA,
        DC::TABLE_CREATOR,
        UC::COL_MC,
        UC::COL_DPL,
        UC::COL_A_ST,
        UC::COL_DM
    ];
    private const HIDDEN_FIELDS = [UC::COL_PW, UC::COL_RT];
    private const CASTS_FIELDS  = [
        // UC::COL_PW => 'hashed', // ! CAST QUANDO SAIR DO TESTE
        UC::COL_EM_V_AT => 'datetime'
    ];
    protected $appends = self::APPENDS;
    protected $fillable = self::FILLABLE_FIELDS;
    protected $hidden = self::HIDDEN_FIELDS;
    protected $casts = self::CASTS_FIELDS;
    private const REL_PROJECTS  = DC::TABLE_PROJECTS;
    private const COL_PROJECT_ID = PJC::COL_PJ_ID;
    private const DEFAULT_WAREHOUSE = [
        UC::COL_NM     => 'North Warehouse',
        'address'  => '723 N. Tillamook Street Portland, OR Portland, United States',
        'city'     => 'Portland',
        'zip' => 97227,
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
            return $this->attributes[UC::COL_AV] =
                asset(Storage::url($this->avatar));
        return $this->attributes[UC::COL_AV] =
            asset(Storage::url('avatar.png'));
    }

    public function authId(): int|string
    {
        return $this->id;
    }

    public function creatorId(): int|string
    {
        return in_array($this[UC::COL_TP], [PMC::CPN, PMC::SA], true)
            ? $this->id
            : $this[DC::TABLE_CREATOR];
    }

    public function ownerId(): int|string
    {
        return in_array($this[UC::COL_TP], [PMC::CPN, PMC::SA], true)
            ? $this->id
            : $this[DC::TABLE_CREATOR];
    }

    public function ownerDetails(): ?self
    {
        return self::whereKey($this->ownerId())->first();
    }

    public function currentLanguage(): string
    {
        return $this->lang;
    }

    public function priceFormat(float $price, bool $numeric = false): string|array
    {
        $settings = Utility::settings();
        $decimal = Utility::getValByName('decimal_number') ?: 0;
        $symbol = isset($settings[SC::CR_SB]) ? $settings[SC::CR_SB] : 'R$';
        return $numeric ? [number_format($price, $decimal), $symbol] : (($settings[SC::CR_SB_P] === 'pre'
            ? $symbol : '')
            . number_format($price, $decimal)
            . ($settings[SC::CR_SB_P] === 'post'
                ? $symbol : ''));
    }

    public static function priceFormats(float $price): string
    {
        $settings = Utility::settings();
        $decimal = Utility::getValByName('decimal_number') ?: 0;
        return ($settings[SC::CR_SB_P] === 'pre'
            ? $settings[SC::CR_SB] : '')
            . number_format($price, $decimal)
            . ($settings[SC::CR_SB_P] === 'post'
                ? $settings[SC::CR_SB] : '');
    }

    public function currencySymbol(): string
    {
        return Utility::settings()[SC::CR_SB];
    }

    public function dateFormat(string $date): string
    {
        return date(Utility::settings()[SC::DT_FM], strtotime($date));
    }

    public function timeFormat(string $time): string
    {
        return date(Utility::settings()[SC::TM_FM], strtotime($time));
    }

    public function purchaseNumberFormat(int $number): string
    {
        return Utility::settings()[SC::PRC_PFX]
            . sprintf('%05d', $number);
    }

    public function posNumberFormat(int $number): string
    {
        return Utility::settings()[SC::POS_PFX]
            . sprintf('%05d', $number);
    }

    public function invoiceNumberFormat(int $number): string
    {
        return Utility::settings()[SC::INV_PFX]
            . sprintf('%05d', $number);
    }

    public function proposalNumberFormat(int $number): string
    {
        return Utility::settings()[SC::PPS_PFX]
            . sprintf('%05d', $number);
    }

    public function contractNumberFormat(int $number): string
    {
        return Utility::settings()[SC::CTC_PFX]
            . sprintf('%05d', $number);
    }

    public function billNumberFormat(int $number): string
    {
        return Utility::settings()[SC::BL_PFX]
            . sprintf('%05d', $number);
    }

    public function expenseNumberFormat(int $number): string
    {
        return Utility::settings()[SC::EXP_PFX]
            . sprintf('%05d', $number);
    }

    public function journalNumberFormat(int $number): string
    {
        return Utility::settings()[SC::JRN_PFX]
            . sprintf('%05d', $number);
    }

    public function getPlan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', UC::COL_PL);
        // * consider belongsTo(Plan::class,UC::COL_PL)
    }

    public function assignPlan(int|string $planId, int|string $companyId = 0): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $plan = Plan::find($planId);
        if (!$plan) return ['is_success' => false, 'error' => 'Plan is deleted.'];
        $this[UC::COL_PL] = $plan->id;
        $this[UC::COL_PED] = match ($plan[PLC::COL_DUR]) {
            'month' => Carbon::now()->addMonth()->isoFormat('YYYY-MM-DD'),
            'year'  => Carbon::now()->addYear()->isoFormat('YYYY-MM-DD'),
            default => null
        };
        $this->save();
        $userId = $companyId ?: $user?->creatorId();
        $users = User::where(DC::TABLE_CREATOR, $userId)
            ->whereNotIn(UC::COL_TP, [
                PMC::SA,
                PMC::CPN,
                PMC::CL
            ])->get();
        $clients = User::where(DC::TABLE_CREATOR, $userId)
            ->where(UC::COL_TP, PMC::CL)->get();
        $customers = Customer::where(DC::TABLE_CREATOR, $userId)->get();
        $vendors = Vendor::where(DC::TABLE_CREATOR, $userId)->get();
        foreach (
            [
                PLC::COL_MAX_U  => $users,
                PLC::COL_MAX_CL => $clients,
                PLC::COL_MAX_CR => $customers,
                PLC::COL_MAX_V  => $vendors,
            ] as $prop => $collection
        )
            $this->_syncActive($collection, $plan->{$prop});
        return ['is_success' => true];
    }

    public function customerNumberFormat(int $number): string
    {
        return Utility::settings()[SC::CST_PFX]
            . sprintf('%05d', $number);
    }

    public function vendorNumberFormat(int $number): string
    {
        return Utility::settings()[SC::VND_PFX]
            . sprintf('%05d', $number);
    }

    public function venderNumberFormat(int $number): string // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINT
    {
        return $this->vendorNumberFormat($number);
    }

    public function countUsers(): int
    {
        return User::whereNotIn(UC::COL_TP, [
            PMC::SA,
            PMC::CPN,
            PMC::CL
        ])
            ->where(DC::TABLE_CREATOR, $this->creatorId())
            ->count();
    }

    public function countCompany(): int
    {
        return User::where(UC::COL_TP, PMC::CPN)
            ->where(DC::TABLE_CREATOR, $this->creatorId())
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
        return User::where(UC::COL_TP, PMC::CPN)
            ->whereNotIn(UC::COL_PL, [0, 1])
            ->where(DC::TABLE_CREATOR, Auth::user()->id)
            ->count();
    }

    public function countCustomers(): int
    {
        return Customer::where(DC::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function countVendors(): int
    {
        return Vendor::where(DC::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function countVenders(): int // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINT
    {
        return $this->countVendors();
    }

    public function countInvoices(): int
    {
        return Invoice::where(DC::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function countBills(): int
    {
        return Bill::where(DC::TABLE_CREATOR, $this->creatorId())->count();
    }

    public function todayIncome(): float
    {
        $userId = $this->creatorId();
        $revenue = Revenue::where(DC::TABLE_CREATOR, $userId)
            ->whereDate('date', today())->sum('amount');
        $invoices = Invoice::where(DC::TABLE_CREATOR, $userId)
            ->whereDate('send_date', today())->get();
        $invoiceTotal = $invoices->sum(fn($inv) => $inv->getTotal());
        return $revenue + $invoiceTotal;
    }

    public function todayExpense(): float
    {
        $userId = $this->creatorId();
        $payment = Payment::where(DC::TABLE_CREATOR, $userId)
            ->whereDate('date', today())->sum('amount');
        $bills = Bill::where(DC::TABLE_CREATOR, $userId)
            ->whereDate('send_date', today())->get();
        $billTotal = $bills->sum(fn($b) => $b->getTotal());
        return $payment + $billTotal;
    }

    public function incomeCurrentMonth(): float
    {
        $userId = $this->creatorId();
        $month = now()->month;
        $revenue = Revenue::where(DC::TABLE_CREATOR, $userId)
            ->whereMonth('date', $month)->sum('amount');
        $invoices = Invoice::where(DC::TABLE_CREATOR, $userId)
            ->whereMonth('send_date', $month)->get();
        $invoiceTotal = $invoices->sum(fn($inv) => $inv->getTotal());
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
        $invoiceTotal = Invoice::where(DC::TABLE_CREATOR, $userId)
            ->whereMonth('send_date', $month)
            ->get()
            ->sum(fn($inv) => $inv->getTotal());
        return $incomesSum + $invoiceTotal;
    }

    public function expenseCurrentMonth(): float
    {
        $userId = $this->creatorId();
        $payment = Payment::where(DC::TABLE_CREATOR, $userId)
            ->whereMonth('date', now()->month)->sum('amount');
        $billTotal = Bill::where(DC::TABLE_CREATOR, $userId)
            ->whereMonth('send_date', now()->month)
            ->get()->sum(fn($b) => $b->getTotal());
        return $payment + $billTotal;
    }

    public function getIncExpBarChartData(): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $months = [
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
            __('December')
        ];
        $dataArr['month'] = $months;
        $userId = $user?->creatorId();
        $year = now()->year;
        $incomeArr = [];
        $expenseArr = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyIncome = Revenue::where(DC::TABLE_CREATOR, $userId)
                ->whereYear('date', $year)->whereMonth('date', $i)
                ->sum('amount');
            $invoiceSum = Invoice::where(DC::TABLE_CREATOR, $userId)
                ->whereYear('send_date', $year)->whereMonth('send_date', $i)
                ->get()->sum(fn($inv) => $inv->getTotal());
            $incomeArr[] = (float) ($monthlyIncome + $invoiceSum);

            $monthlyExpense = Payment::where(DC::TABLE_CREATOR, $userId)
                ->whereYear('date', $year)->whereMonth('date', $i)
                ->sum('amount');
            $billSum = Bill::where(DC::TABLE_CREATOR, $userId)
                ->whereYear('send_date', $year)->whereMonth('send_date', $i)
                ->get()->sum(fn($b) => $b->getTotal());
            $expenseArr[] = (float) ($monthlyExpense + $billSum);
        }
        $dataArr['income'] = $incomeArr;
        $dataArr['expense'] = $expenseArr;
        return $dataArr;
    }

    public function getIncExpLineChartDate(): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $userId = $user?->creatorId();
        $dates = collect(range(0, 14))
            ->map(fn($i) => now()->subDays($i)->format('Y-m-d'))
            ->reverse()
            ->values();
        $labels = $dates->map(
            fn($d) =>
            Carbon::parse($d)->format('d-M')
        )->toArray();
        $incomeArr = $dates->map(
            fn($d) =>
            Revenue::where(DC::TABLE_CREATOR, $userId)
                ->whereDate('date', $d)->sum('amount')
                + Invoice::where(DC::TABLE_CREATOR, $userId)
                ->whereDate('send_date', $d)
                ->get()->sum(fn($inv) => $inv->getTotal())
        )->toArray();
        $expenseArr = $dates->map(
            fn($d) =>
            Payment::where(DC::TABLE_CREATOR, $userId)
                ->whereDate('date', $d)->sum('amount')
                + Bill::where(DC::TABLE_CREATOR, $userId)
                ->whereDate('send_date', $d)
                ->get()->sum(fn($b) => $b->getTotal())
        )->toArray();
        return [
            'day'     => $labels,
            'income'  => $incomeArr,
            'expense' => $expenseArr
        ];
    }

    public function totalCompanyUser(int|string $id): int
    {
        return self::where(DC::TABLE_CREATOR, $id)->count();
    }

    public function totalCompanyCustomer(int|string $id): int
    {
        return Customer::where(DC::TABLE_CREATOR, $id)->count();
    }

    public function totalCompanyVendor(int|string $id): int
    {
        return Vendor::where(DC::TABLE_CREATOR, $id)->count();
    }

    public function totalCompanyVender(int|string $id): int // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINT
    {
        return $this->totalCompanyVendor($id);
    }

    public function planPrice(): array
    {
        $user = Auth::user();
        $userId = $user[UC::COL_TP] === PMC::SA
            ? $user?->id
            : $user[DC::TABLE_CREATOR];
        return DB::table(DC::TABLE_SETTINGS)
            ->where(DC::TABLE_CREATOR, $userId)
            ->pluck('value', UC::COL_NM)
            ->toArray();
    }

    public function currentPlan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', UC::COL_PL);
        // * consider belongsTo(Plan::class,UC::COL_PL)
    }

    public function weeklyInvoice(): array
    {
        $start = now()->subWeek()->toDateString();
        $end  = now()->toDateString();
        $invoices = Invoice::where(DC::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('issue_date', [$start, $end])->get();
        $invoiceTotal = $invoices->sum(fn($inv) => $inv->getTotal());
        $invoicePaid = $invoices->sum(fn($inv) => $inv->getTotal() - $inv->getDue());
        $invoiceDue  = $invoices->sum(fn($inv) => $inv->getDue());
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
        $invoices = Invoice::where(DC::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('issue_date', [$start, $end])->get();
        $invoiceTotal = $invoices->sum(fn($inv) => $inv->getTotal());
        $invoicePaid = $invoices->sum(fn($inv) => $inv->getTotal() - $inv->getDue());
        $invoiceDue  = $invoices->sum(fn($inv) => $inv->getDue());
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
        $bills = Bill::where(DC::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('bill_date', [$start, $end])->get();
        $billTotal = $bills->sum(fn($b) => $b->getTotal());
        $billPaid = $bills->sum(fn($b) => $b->getTotal() - $b->getDue());
        $billDue  = $bills->sum(fn($b) => $b->getDue());
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
        $bills = Bill::where(DC::TABLE_CREATOR, $this->creatorId())
            ->whereBetween('bill_date', [$start, $end])->get();
        $billTotal = $bills->sum(fn($b) => $b->getTotal());
        $billPaid = $bills->sum(fn($b) => $b->getTotal() - $b->getDue());
        $billDue  = $bills->sum(fn($b) => $b->getDue());
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
        return $this->belongsToMany(Deal::class, 'user_deals', UC::COL_USER_ID, AC::COL_DL);
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'user_leads', UC::COL_USER_ID, 'lead_id');
    }

    public function clientDeals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'client_deals', 'client_id', AC::COL_DL);
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
        return $this->belongsToMany(Project::class, 'project_users', UC::COL_USER_ID, PJC::COL_PJ_ID)
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
        $detail = Employee::where(UC::COL_USER_ID, $this->id)->first();
        if ($detail && !empty($detail->avatar))
            return asset(Storage::url($detail->avatar));
        return asset(Storage::url('avatar.png'));
    }

    public function tasks(): Collection
    {
        $user = Auth::check() ? Auth::user() : self::find($this->id);
        if ($user[UC::COL_TP] === PMC::CPN)
            return ProjectTask::where(DC::TABLE_CREATOR, $user?->creatorId())->get();
        return ProjectTask::whereRaw("find_in_set('{$this->id}'," . PJC::COL_ASGN . ")")->get();
    }

    public function bugNumberFormat(int $number): string
    {
        return Utility::settings()[SC::BUG_PFX] . sprintf('%05d', $number);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class, 'parent_id', 'id');
    }

    public function todo(): HasMany
    {
        return $this->hasMany(UserToDo::class, UC::COL_USER_ID, 'id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, UC::COL_USER_ID, 'id');
    }

    public function totalLead(): int
    {
        return Auth::user()[UC::COL_TP] === PMC::CPN
            ? Lead::where(DC::TABLE_CREATOR, $this->creatorId())->count()
            : (Auth::user()[UC::COL_TP] === PMC::CL
                ? Lead::where(PMC::CL, $this->authId())->count()
                : Lead::where('owner', $this->authId())->count());
    }

    public function lastProjectStage(): ?TaskStage
    {
        return TaskStage::where(DC::TABLE_CREATOR, $this->creatorId())
            ->orderByDesc(AC::COL_OD)->first();
    }

    public function userProject(): int
    {
        return Auth::user()[UC::COL_TP] !== PMC::CL
            ? $this->projects()->count()
            : Project::where('client_id', $this->authId())->count();
    }

    public function createdTotalProjectTask(): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $userId = $user?->creatorId();
        return match (Auth::user()[UC::COL_TP]) {
            PMC::CPN => ProjectTask::join(
                DC::TABLE_PROJECTS,
                DC::TABLE_PROJECTS . 'id',
                '=',
                DC::TABLE_PROJ_TSKS . '.' . PJC::COL_PJ_ID
            )
                ->where(DC::TABLE_PROJECTS . DC::TABLE_CREATOR, $userId)->count(),
            PMC::CL  => ProjectTask::join(
                DC::TABLE_PROJECTS,
                DC::TABLE_PROJECTS . 'id',
                '=',
                DC::TABLE_PROJ_TSKS . '.' . PJC::COL_PJ_ID
            )
                ->where(DC::TABLE_PROJECTS . 'client_id', $user?->authId())->count(),
            default   => ProjectTask::join(
                'project_users',
                'project_users.' . PJC::COL_PJ_ID,
                '=',
                DC::TABLE_PROJ_TSKS . '.' . PJC::COL_PJ_ID
            )
                ->where('project_users.' . UC::COL_USER_ID, $user?->authId())->count(),
        };
    }

    public function projectCompleteTask(int|string $projectLastStage): int
    {
        $user = Auth::user();
        return match ($user[UC::COL_TP]) {
            PMC::CPN => ProjectTask::join(
                DC::TABLE_PROJECTS,
                DC::TABLE_PROJECTS . 'id',
                '=',
                DC::TABLE_PROJ_TSKS . '.' . PJC::COL_PJ_ID
            )
                ->where(DC::TABLE_PROJECTS . DC::TABLE_CREATOR, $this->creatorId())
                ->where(DC::TABLE_PROJ_TSKS . '.' . PJC::COL_STAGE_ID, $projectLastStage)
                ->count(),
            PMC::CL  => ProjectTask::whereIn(
                PJC::COL_PJ_ID,
                Project::where('client_id', $user?->id)->pluck('id')
            )
                ->where(DC::TABLE_PROJ_TSKS . '.' . PJC::COL_STAGE_ID, $projectLastStage)
                ->count(),
            default   => ProjectTask::join(
                'project_users',
                'project_users.' . PJC::COL_PJ_ID,
                '=',
                DC::TABLE_PROJ_TSKS . '.' . PJC::COL_PJ_ID
            )
                ->where('project_users.' . UC::COL_USER_ID, $this->authId())
                ->where(DC::TABLE_PROJ_TSKS . '.' . PJC::COL_STAGE_ID, $projectLastStage)
                ->count(),
        };
    }

    public function createdTopDueTask(): Collection
    {
        $user = Auth::user();
        $query = ProjectTask::query();
        if ($user[UC::COL_TP] === PMC::CPN) {
            $query->join(
                DC::TABLE_PROJECTS,
                DC::TABLE_PROJECTS . 'id',
                '=',
                DC::TABLE_PROJ_TSKS . '.' . PJC::COL_PJ_ID
            )
                ->where(DC::TABLE_PROJECTS . DC::TABLE_CREATOR, $this->creatorId());
        } elseif ($user[UC::COL_TP] === PMC::CL) {
            $query->whereIn(PJC::COL_PJ_ID, Project::where('client_id', $user?->id)->pluck('id'));
        } else {
            $query->select(
                DC::TABLE_PROJ_TSKS . '.*',
                'project_users.id as up_id',
                DC::TABLE_PROJECTS . '.project_name',
                DC::TABLE_PROJ_STAGES . '.name as stage_name'
            )
                ->join(
                    'project_users',
                    'project_users.' . PJC::COL_PJ_ID,
                    '=',
                    DC::TABLE_PROJ_TSKS . '.' . PJC::COL_PJ_ID
                )
                ->join(
                    DC::TABLE_PROJECTS,
                    DC::TABLE_PROJECTS . 'id',
                    '=',
                    'project_users.' . PJC::COL_PJ_ID
                )
                ->join(
                    DC::TABLE_PROJ_STAGES,
                    DC::TABLE_PROJ_TSKS . '.' . PJC::COL_STAGE_ID,
                    '=',
                    DC::TABLE_PROJ_STAGES . '.id'
                )
                ->where('project_users.' . UC::COL_USER_ID, $this->authId());
        }
        return $query->where(
            DC::TABLE_PROJ_TSKS . '.' . PJC::COL_E_DT,
            '>',
            now()->toDateString()
        )
            ->orderBy(DC::TABLE_PROJ_TSKS . '.' . PJC::COL_E_DT, 'ASC')
            ->limit(5)->get();
    }

    public function showDashboard(): int|string
    {
        $user = Auth::user();
        if ($user instanceof User)
            $user = in_array($user->{UC::COL_TP}, [PMC::CPN, PMC::SA], true)
                ? $user
                : self::find($user->{DC::TABLE_CREATOR});
        return $user?->plan ?? DC::DEFAULT_PLAN;
    }

    public static function showCrm(): string
    {
        $user = in_array(Auth::user()[UC::COL_TP], [PMC::CPN, PMC::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DC::TABLE_CREATOR]);
        return Plan::find($user?->plan)->crm ?? '';
    }

    public static function showHrm(): string
    {
        $user = in_array(Auth::user()[UC::COL_TP], [PMC::CPN, PMC::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DC::TABLE_CREATOR]);
        return Plan::find($user?->plan)->hrm ?? '';
    }

    public static function showAccount(): string
    {
        $user = in_array(Auth::user()[UC::COL_TP], [PMC::CPN, PMC::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DC::TABLE_CREATOR]);
        return Plan::find($user?->plan)->account ?? '';
    }

    public static function showProject(): string
    {
        $user = in_array(Auth::user()[UC::COL_TP], [PMC::CPN, PMC::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DC::TABLE_CREATOR]);
        return Plan::find($user?->plan)->project ?? '';
    }

    public static function showPos(): string
    {
        $user = in_array(Auth::user()[UC::COL_TP], [PMC::CPN, PMC::SA], true)
            ? Auth::user()
            : self::find(Auth::user()[DC::TABLE_CREATOR]);
        return Plan::find($user?->plan)->pos ?? '';
    }

    public function clientProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id', 'id');
    }

    public function isUser(): int
    {
        return $this[UC::COL_TP] === 'user' ? 1 : 0;
    }

    public function isClient(): int
    {
        return $this[UC::COL_TP] === PMC::CL ? 1 : 0;
    }

    public static function defaultEmail(string|int $createdBy): void
    {
        foreach (EmailsConstants::STATUS_MAP as $slug => $title) {
            try {
                if (!EmailTemplate::where(EmailsConstants::COL_SLG, $slug)->exists())
                    EmailTemplate::create([
                        EmailsConstants::COL_TT       => $title,
                        EmailsConstants::COL_FROM       => config('app.name'),
                        EmailsConstants::COL_SLG           => $slug,
                        DC::TABLE_CREATOR => $createdBy,
                    ]);
            } catch (ModelNotFoundException $e) {
                Log::warning("defaultEmail: missing model for slug [{$slug}]", ['exception' => $e]);
            } catch (QueryException $e) {
                Log::error("defaultEmail: DB error inserting [$slug]: {$e->getMessage()}", [
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings()
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
                    UC::COL_USER_ID     => DC::DEFAULT_UUID,
                ], [
                    EmailsConstants::COL_IA   => true,
                    DC::TABLE_CREATOR    => DC::DEFAULT_UUID,
                ]);
            } catch (QueryException $e) {
                Log::error("userDefaultData: DB error for template {$tmpl->id}", [
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings()
                ]);
            } catch (Throwable $e) {
                Log::critical("userDefaultData: unexpected for template {$tmpl->id}: {$e->getMessage()}");
            }
        }
    }

    public const USR_DEF_DT_REG = 'userDefaultDataRegister';
    public static function userDefaultDataRegister(string|int $userId): void
    {
        foreach (EmailTemplate::all() as $tmpl) {
            try {
                UserEmailTemplate::firstOrCreate([
                    EmailsConstants::COL_TMP => $tmpl->id,
                    UC::COL_USER_ID     => $userId,
                ], [
                    EmailsConstants::COL_IA   => true,
                    DC::TABLE_CREATOR    => $userId,
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
            UC::COL_NM => 'North Warehouse',
            'address' => '723 N. Tillamook Street Portland, OR Portland, United States',
            'city' => 'Portland',
            'zip' => 97227,
            DC::TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    public const USR_WA_REG = 'userDefaultWarehouse';
    public function userWarehouseRegister(int|string $userId): void
    {
        Warehouse::create([
            UC::COL_NM => 'North Warehouse',
            'address' => '723 N. Tillamook Street Portland, OR Portland, United States',
            'city' => 'Portland',
            'zip' => 97227,
            DC::TABLE_CREATOR => $userId
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
            DC::TABLE_CREATOR => $userId
        ]);
    }

    public function extraKeyword(): array
    {
        return [
            __('Sun'),
            __('Mon'),
            __('Tue'),
            __('Wed'),
            __('Thu'),
            __('Fri'),
            __('Last 7 Days'),
            __('In Progress'),
            __('Complete'),
            __('Canceled'),
            __('Lead User Name'),
            __('CRM'),
            __('POS'),
            __('HRM'),
            __('Old Stage Name'),
            __('New Stage Name'),
            __('Contract Start Date'),
            __('Contract Name'),
            __('Contract Price'),
            __('Branch Name'),
            __('Support User Name'),
            __('Award Date'),
            __('Holiday Title'),
            __('Holiday Date'),
            __('Event Start Date'),
            __('Company Policy Name'),
            __('Invoice Issue Date'),
            __('Invoice Due Date'),
            __('Budget Name'),
            __('Budget Year'),
            __('Revenue Amount'),
            __('Revenue Date'),
            __('Payment Price'),
            __('New User'),
            __('Lifetime'),
            __('Coupon'),
            __('CoinGate'),
            __('Cashflow')
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
        return $settings[SC::EMP_PFX] . sprintf('%05d', $number);
    }

    public static function userCurrentLocation(): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $companyId = $user?->creatorId(); // * method name might differ
        $user = Auth::user();
        if ($user->type == PMC::CPN) {
            $loc = Location::where([
                'id' => $user?->current_location,
                'company_id' => $companyId,
                UC::COL_IA => 1
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
        return Employee::where(DC::TABLE_CREATOR, $this->creatorId())->count();
    }

    private function _syncActive(Collection $items, int $max): void
    {
        if ($max === -1)
            $items->each(fn($m) => $m->fill([UC::COL_IA => 1])->save());
        else
            $items->each(function ($m, $i) use ($max) {
                $m->is_active = $i < $max ? 1 : 0;
                $m->save();
            });
    }

    private static function createWarehouse(int|string $userId): void
    {
        Warehouse::create(self::DEFAULT_WAREHOUSE + [DC::TABLE_CREATOR => $userId]);
    }

    private static function createBankAccount(int|string $userId): void
    {
        BankAccount::create(self::DEFAULT_BANK_ACCOUNT + [DC::TABLE_CREATOR => $userId]);
    }
}
