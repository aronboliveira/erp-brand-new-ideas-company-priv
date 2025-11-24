<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    PermissionsConstants as PC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Models\Utility;
use App\Traits\{
    ChecksLogin,
    HasAuditFields,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\{
    Auth,
    DB
};
use Spatie\Permission\Traits\HasRoles;

class Customer extends Authenticatable
{
    use ChecksLogin, HasRoles, Notifiable, UsesUuids, HasAuditFields;

    public $settings;

    protected $guard_name = 'web';

    private const MONTHS = [
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

    protected $table = DC::TABLE_CUSTOMERS;

    protected $fillable = [
        BC::COL_BL_ADR,
        BC::COL_BL_CTY,
        BC::COL_BL_CTR,
        BC::COL_BL_NAME,
        BC::COL_BL_TEL,
        BC::COL_BL_ST,
        BC::COL_BL_ZIP,
        'contact',
        'email',
        UC::COL_EM_V_AT,
        'avatar',
        UC::COL_IA,
        'lang',
        'name',
        SC::PPS_PFX,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_NAME,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_ZIP,
        BC::COL_TX_N,
        BC::COL_OT_TX_ID,
        BC::COL_CST_ID,
        BC::COL_OD_C,
        BC::COL_IS_PRM,
        UC::COL_AVG_RT,
        'preferences',
        'balance',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $hidden = [
        'password',
        UC::COL_RT,
        'remember_token',
    ];

    protected $casts = [
        UC::COL_EM_V_AT   => 'datetime',
        UC::COL_IA        => 'boolean',
        BC::COL_OD_C      => 'integer',
        BC::COL_IS_PRM    => 'boolean',
        UC::COL_AVG_RT    => 'decimal:2',
        BC::COL_OT_TX_ID  => 'array',
        'preferences'     => 'array',
        'balance'         => 'decimal:2',
    ];

    protected $appends = [
        'is_verified',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $customer): void {
            if ($customer->email)
                $customer->email = mb_strtolower(trim($customer->email));

            if ($customer->name)
                $customer->name = trim($customer->name);

            if ($customer->{BC::COL_TX_N}) {
                $digits = preg_replace('/\D+/', '', (string) $customer->{BC::COL_TX_N});
                if (strlen($digits) === 11 || strlen($digits) === 14)
                    $customer->{BC::COL_TX_N} = $digits;
                else
                    $customer->{BC::COL_TX_N} = null;
            }

            if ($customer->{BC::COL_OT_TX_ID} === null)
                $customer->{BC::COL_OT_TX_ID} = [];
            elseif (!is_array($customer->{BC::COL_OT_TX_ID}))
                $customer->{BC::COL_OT_TX_ID} = (array) $customer->{BC::COL_OT_TX_ID};

            if ($customer->preferences === null)
                $customer->preferences = [];
            elseif (!is_array($customer->preferences))
                $customer->preferences = (array) $customer->preferences;

            if ($customer->{UC::COL_AVG_RT} !== null) {
                $rating = (float) $customer->{UC::COL_AVG_RT};
                if ($rating < 0) $rating = 0;
                if ($rating > 5) $rating = 5;
                $customer->{UC::COL_AVG_RT} = $rating;
            }

            if ($customer->{BC::COL_OD_C} !== null && $customer->{BC::COL_OD_C} < 0)
                $customer->{BC::COL_OD_C} = 0;

            if ($customer->balance !== null && $customer->balance < 0)
                $customer->balance = 0.00;

            if ($customer->lang)
                $customer->lang = strtolower(trim($customer->lang));
            else
                $customer->lang = DC::DEFAULT_LANG;

            if (!$customer->{BC::COL_CST_ID} && auth()->check())
                $customer->{BC::COL_CST_ID} = auth()->id();
        });
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->{UC::COL_EM_V_AT} !== null;
    }

    public function authId(): string
    {
        return $this->id;
    }

    public function creatorId(): string
    {
        return ($this->type === PC::CPN || $this->type === PC::SA)
            ? $this->id
            : ($this->{DC::TABLE_CREATOR} ?? $this->id);
    }

    public function currentLanguage(): string
    {
        return $this->lang;
    }

    public function currencySymbol(): string
    {
        $s = Utility::settings();
        return $s[SC::CR_SB];
    }

    public function dateFormat(string $date): string
    {
        return date(Utility::settings()['site_date_format'], strtotime($date));
    }

    public function invoiceNumberFormat(int $n): string
    {
        return Utility::settings()[SC::INV_PFX] . sprintf('%05d', $n);
    }

    public function priceFormat(float $price): string
    {
        $s   = Utility::settings();
        $fmt = number_format($price, Utility::getValByName('decimal_number'));

        return ($s[SC::CR_SB_P] === 'pre' ? $s[SC::CR_SB] : '')
            . $fmt
            . ($s[SC::CR_SB_P] === 'post' ? $s[SC::CR_SB] : '');
    }

    public function proposalNumberFormat(int $n): string
    {
        return Utility::settings()[SC::PPS_PFX] . sprintf('%05d', $n);
    }

    public function timeFormat(string $time): string
    {
        return date(
            Utility::settings()[SC::TM_FM] ?? 'H:i:s',
            strtotime($time)
        );
    }

    public function invoiceChartData(): array
    {
        $userId   = Auth::id();
        $year     = date('Y');
        $today    = today();

        $invoices = Invoice::where(BC::COL_CST_ID, $userId)
            ->whereYear('send_date', $year)
            ->get();

        $data['month']       = array_map(fn(string $m): string => __($m), self::MONTHS);
        $data['currentYear'] = date('M-Y');

        $statusData = ['unpaid' => [], 'paid' => [], 'partial' => [], 'due' => []];

        foreach (self::MONTHS as $idx => $_) {
            $monthNum      = $idx + 1;
            $monthInvoices = $invoices->filter(
                fn($inv): bool =>
                Carbon::parse($inv->send_date)->month === $monthNum
            );

            $statusData['unpaid'][] = (float) $monthInvoices
                ->filter(
                    fn($inv): bool =>
                    $inv->status === 1 && Carbon::parse($inv->due_date)->gt($today)
                )
                ->sum(fn($inv): float => $inv->getDue());

            $statusData['paid'][] = (float) $monthInvoices
                ->filter(fn($inv): bool => $inv->status === 4)
                ->sum(fn($inv): float => $inv->getTotal());

            $statusData['partial'][] = (float) $monthInvoices
                ->filter(fn($inv): bool => $inv->status === 3)
                ->sum(fn($inv): float => $inv->getDue());

            $statusData['due'][] = (float) $monthInvoices
                ->filter(
                    fn($inv): bool =>
                    $inv->status === 1 && Carbon::parse($inv->due_date)->lt($today)
                )
                ->sum(fn($inv): float => $inv->getDue());
        }

        $data['data'] = $statusData;

        $totalCount   = $invoices->count();
        $unpaidCount  = $invoices
            ->filter(
                fn($inv): bool =>
                $inv->status === 1 && Carbon::parse($inv->due_date)->gt($today)
            )
            ->count();

        $paidCount    = $invoices->where('status', 4)->count();
        $partialCount = $invoices->where('status', 3)->count();
        $dueCount     = $invoices
            ->filter(
                fn($inv): bool =>
                $inv->status === 1 && Carbon::parse($inv->due_date)->lt($today)
            )
            ->count();

        $progressData = [
            'totalInvoice'        => $totalCount,
            'totalUnpaidInvoice'  => $unpaidCount,
            'totalPaidInvoice'    => $paidCount,
            'totalPartialInvoice' => $partialCount,
            'totalDueInvoice'     => $dueCount,
            'unpaidPr'            => $totalCount ? $unpaidCount * 100 / $totalCount : 0,
            'paidPr'              => $totalCount ? $paidCount * 100 / $totalCount : 0,
            'partialPr'           => $totalCount ? $partialCount * 100 / $totalCount : 0,
            'duePr'               => $totalCount ? $dueCount * 100 / $totalCount : 0,
            'unpaidColor'         => '#fc544b',
            'paidColor'           => '#63ed7a',
            'partialColor'        => '#6777ef',
            'dueColor'            => '#ffa426',
        ];

        $data['progressData'] = $progressData;

        return $data;
    }

    public function customerInvoice(string $customerId)
    {
        return Invoice::where(BC::COL_CST_ID, $customerId)
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function customerProposal(string $customerId)
    {
        return Proposal::where(BC::COL_CST_ID, $customerId)
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function customerOverdue(string $customerId): float
    {
        return Invoice::where(BC::COL_CST_ID, $customerId)
            ->whereNotIn('status', ['0', '4'])
            ->whereDate('due_date', '<', now()->format('Y-m-d'))
            ->get()
            ->sum(fn(Invoice $inv): float => $inv->getDue());
    }

    public function customerTotalInvoiceSum(string $customerId): float
    {
        return Invoice::where(BC::COL_CST_ID, $customerId)
            ->get()
            ->sum(fn(Invoice $inv): float => $inv->getTotal());
    }

    public function customerTotalInvoice(string $customerId): int
    {
        return Invoice::where(BC::COL_CST_ID, $customerId)
            ->count();
    }

    public static function customerId(string $customerName)
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;

        $user = $userOrRedirect;

        return DB::table(DC::TABLE_CUSTOMERS)
            ->where('name', $customerName)
            ->where(DC::TABLE_CREATOR, $user?->creatorId())
            ->value('id') ?? 0;
    }
}
