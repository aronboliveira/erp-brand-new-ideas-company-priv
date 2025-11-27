<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    PermissionsConstants as PC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Enums\{
    BrazilState,
    ChinaState,
    CountryName,
    MonthName,
    PortugalState,
    UnitedStatesState
};
use App\Models\Utility;
use App\Traits\{
    ChecksLogin,
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Log
};
use Spatie\Permission\Traits\HasRoles;

class Customer extends Authenticatable
{
    use ChecksLogin, HasRoles, Notifiable, UsesUuids, HasAuditFields, NormalizesAddresses;

    public $settings;

    protected $guard_name = 'web';

    protected $table = DC::TABLE_CUSTOMERS;

    protected $fillable = [
        // user-like
        UC::COL_NM,
        UC::COL_EM,
        UC::COL_EM_V_AT,
        UC::COL_AV,
        UC::COL_IA,
        UC::COL_LG,
        'preferences',

        // sales representative
        'customer_id',
        'contact',
        BC::COL_TX_N,
        BC::COL_OT_TX_ID,
        BC::COL_IS_PRM,

        // extra customer metadata
        BC::COL_CST_ID,
        BC::COL_OD_C,
        UC::COL_AVG_RT,
        'balance',

        // shipping
        BC::COL_SHIP_NAME,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_ZIP,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_DTL,

        // billing
        BC::COL_BL_NAME,
        BC::COL_BL_EMAIL,
        BC::COL_BL_TEL,
        BC::COL_BL_ZIP,
        BC::COL_BL_ADR,
        BC::COL_BL_ST,
        BC::COL_BL_CTY,
        BC::COL_BL_CTR,
        BC::COL_BL_DTL,
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $hidden = [
        UC::COL_PW,
        UC::COL_RT,
        'remember_token',
    ];

    protected $casts = [
        UC::COL_EM_V_AT  => 'datetime',
        UC::COL_IA       => 'boolean',
        BC::COL_OD_C     => 'integer',
        BC::COL_IS_PRM   => 'boolean',
        UC::COL_AVG_RT   => 'decimal:2',
        BC::COL_OT_TX_ID => 'array',
        'preferences'    => 'array',
        'balance'        => 'decimal:2',
    ];

    protected $appends = [
        'is_verified',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $customer): void {
            foreach (
                [
                    UC::COL_NM,
                    UC::COL_EM,
                    'contact',
                    BC::COL_BL_NAME,
                    BC::COL_BL_EMAIL,
                    BC::COL_BL_CTR,
                    BC::COL_BL_ST,
                    BC::COL_BL_CTY,
                    BC::COL_BL_TEL,
                    BC::COL_BL_ZIP,
                    BC::COL_BL_ADR,
                    BC::COL_BL_DTL,
                    BC::COL_SHIP_NAME,
                    BC::COL_SHIP_CTR,
                    BC::COL_SHIP_ST,
                    BC::COL_SHIP_CTY,
                    BC::COL_SHIP_TEL,
                    BC::COL_SHIP_ZIP,
                    BC::COL_SHIP_ADR,
                    BC::COL_SHIP_DTL,
                    BC::COL_TX_N,
                    UC::COL_LG,
                ] as $field
            )
                if (isset($customer->{$field}) && is_string($customer->{$field}))
                    $customer->{$field} = trim($customer->{$field});

            if ($customer->{UC::COL_EM} ?? null)
                $customer->{UC::COL_EM} = self::normalizeEmail(
                    $customer->{UC::COL_EM},
                    'main',
                    $customer->id ?? null
                );

            if ($customer->{BC::COL_BL_EMAIL} ?? null)
                $customer->{BC::COL_BL_EMAIL} = self::normalizeEmail(
                    $customer->{BC::COL_BL_EMAIL},
                    'billing',
                    $customer->id ?? null
                );

            $customer->contact = self::normalizePhone(
                $customer->contact ?? null,
                'contact',
                $customer->id ?? null
            );

            $customer->{BC::COL_BL_TEL} = self::normalizePhone(
                $customer->{BC::COL_BL_TEL} ?? null,
                'billing',
                $customer->id ?? null
            );

            $customer->{BC::COL_SHIP_TEL} = self::normalizePhone(
                $customer->{BC::COL_SHIP_TEL} ?? null,
                'shipping',
                $customer->id ?? null
            );

            if ($customer->{BC::COL_TX_N}) {
                $digits = preg_replace('/\D+/', '', (string) $customer->{BC::COL_TX_N});
                if (strlen($digits) === 11 || strlen($digits) === 14)
                    $customer->{BC::COL_TX_N} = $digits;
                else
                    $customer->{BC::COL_TX_N} = null;
            }

            try {
                $customer->{BC::COL_OT_TX_ID} = self::normalizeArrayField($customer->{BC::COL_OT_TX_ID} ?? []);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize other_taxes_ids', [
                    'customer_id' => $customer->id ?? null,
                    'error'       => $e->getMessage(),
                ]);
                $customer->{BC::COL_OT_TX_ID} = [];
            }

            try {
                $customer->preferences = self::normalizeArrayField($customer->preferences ?? []);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize preferences', [
                    'customer_id' => $customer->id ?? null,
                    'error'       => $e->getMessage(),
                ]);
                $customer->preferences = [];
            }

            if ($customer->{UC::COL_AVG_RT} !== null) {
                $rating = (float) $customer->{UC::COL_AVG_RT};
                if ($rating < 0) $rating = 0;
                if ($rating > 5) $rating = 5;
                $customer->{UC::COL_AVG_RT} = $rating;
            }

            if ($customer->{BC::COL_OD_C} !== null && $customer->{BC::COL_OD_C} < 0)
                $customer->{BC::COL_OD_C} = 0;

            if ($customer->balance === null || !is_numeric($customer->balance) || $customer->balance < 0)
                $customer->balance = 0.00;

            if ($customer->{UC::COL_LG})
                $customer->{UC::COL_LG} = strtolower(trim($customer->{UC::COL_LG}));
            else
                $customer->{UC::COL_LG} = DC::DEFAULT_LANG;

            // Países (billing + shipping)
            $billingCountryEnum = CountryName::normalize($customer->{BC::COL_BL_CTR} ?? null)
                ?? CountryName::Brazil;
            $shippingCountryEnum = CountryName::normalize($customer->{BC::COL_SHIP_CTR} ?? null)
                ?? CountryName::Brazil;

            $customer->{BC::COL_BL_CTR}   = $billingCountryEnum->value;
            $customer->{BC::COL_SHIP_CTR} = $shippingCountryEnum->value;

            self::normalizeStateField($customer, BC::COL_BL_ST, $billingCountryEnum);
            self::normalizeStateField($customer, BC::COL_SHIP_ST, $shippingCountryEnum);

            $customer->{BC::COL_BL_ZIP} = self::normalizeZip(
                $customer->{BC::COL_BL_ZIP} ?? null,
                $customer->{BC::COL_BL_CTR},
                'billing',
                $customer->id ?? null
            );

            $customer->{BC::COL_SHIP_ZIP} = self::normalizeZip(
                $customer->{BC::COL_SHIP_ZIP} ?? null,
                $customer->{BC::COL_SHIP_CTR},
                'shipping',
                $customer->id ?? null
            );

            if ($customer->{UC::COL_IA} === null)
                $customer->{UC::COL_IA} = true;

            if ($customer->{BC::COL_IS_PRM} === null)
                $customer->{BC::COL_IS_PRM} = false;

            if (!$customer->{BC::COL_CST_ID} && auth()->check())
                $customer->{BC::COL_CST_ID} = auth()->id();
        });
    }

    protected static function normalizeStateField(self $customer, string $column, CountryName $country): void
    {
        $raw = $customer->{$column} ?? null;
        $normalized = null;

        switch ($country) {
            case CountryName::Brazil:
                $normalized = BrazilState::normalize($raw) ?? BrazilState::RJ;
                break;
            case CountryName::Portugal:
                $normalized = PortugalState::normalize($raw) ?? PortugalState::LS;
                break;
            case CountryName::UnitedStates:
                $normalized = UnitedStatesState::normalize($raw) ?? UnitedStatesState::CA;
                break;
            case CountryName::China:
                $normalized = ChinaState::normalize($raw) ?? ChinaState::BJ;
                break;
            default:
                if (is_string($raw))
                    $customer->{$column} = strtoupper(trim($raw));
                return;
        }

        $customer->{$column} = $normalized->value;
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
        return $this->{UC::COL_LG};
    }

    public function currencySymbol(): string
    {
        $s = Utility::settings();
        return $s[SC::CR_SB] ?? '';
    }

    public function dateFormat(string $date): string
    {
        $s = Utility::settings();
        return date(
            $s[SC::DT_FM] ?? 'Y-m-d',
            strtotime($date)
        );
    }

    public function invoiceNumberFormat(int $n): string
    {
        $s = Utility::settings();
        return ($s[SC::INV_PFX] ?? '') . sprintf('%05d', $n);
    }

    public function priceFormat(float $price): string
    {
        $s   = Utility::settings();
        $dec = (int) ($s['decimal_number'] ?? 2);
        $fmt = number_format($price, $dec);

        return ($s[SC::CR_SB_P] === 'pre' ? ($s[SC::CR_SB] ?? '') : '')
            . $fmt
            . ($s[SC::CR_SB_P] === 'post' ? ($s[SC::CR_SB] ?? '') : '');
    }

    public function proposalNumberFormat(int $n): string
    {
        $s = Utility::settings();
        return ($s[SC::PPS_PFX] ?? '') . sprintf('%05d', $n);
    }

    public function timeFormat(string $time): string
    {
        $s = Utility::settings();
        return date(
            $s[SC::TM_FM] ?? 'H:i:s',
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

        $monthsEnum = MonthName::ordered();

        $data['month']       = array_map(
            fn(MonthName $m): string => __($m->label()),
            $monthsEnum
        );
        $data['currentYear'] = date('M-Y');

        $statusData = [
            'unpaid'  => [],
            'paid'    => [],
            'partial' => [],
            'due'     => [],
        ];

        foreach ($monthsEnum as $monthEnum) {
            $monthNum      = $monthEnum->isoIndex();
            $monthInvoices = $invoices->filter(
                fn(Invoice $inv): bool =>
                Carbon::parse($inv->send_date)->month === $monthNum
            );

            $statusData['unpaid'][] = (float) $monthInvoices
                ->filter(
                    fn(Invoice $inv): bool =>
                    $inv->status === 1 && Carbon::parse($inv->due_date)->gt($today)
                )
                ->sum(fn(Invoice $inv): float => $inv->getDue());

            $statusData['paid'][] = (float) $monthInvoices
                ->filter(fn(Invoice $inv): bool => $inv->status === 4)
                ->sum(fn(Invoice $inv): float => $inv->getTotal());

            $statusData['partial'][] = (float) $monthInvoices
                ->filter(fn(Invoice $inv): bool => $inv->status === 3)
                ->sum(fn(Invoice $inv): float => $inv->getDue());

            $statusData['due'][] = (float) $monthInvoices
                ->filter(
                    fn(Invoice $inv): bool =>
                    $inv->status === 1 && Carbon::parse($inv->due_date)->lt($today)
                )
                ->sum(fn(Invoice $inv): float => $inv->getDue());
        }

        $data['data'] = $statusData;

        $totalCount   = $invoices->count();
        $unpaidCount  = $invoices
            ->filter(
                fn(Invoice $inv): bool =>
                $inv->status === 1 && Carbon::parse($inv->due_date)->gt($today)
            )
            ->count();

        $paidCount    = $invoices->where('status', 4)->count();
        $partialCount = $invoices->where('status', 3)->count();
        $dueCount     = $invoices
            ->filter(
                fn(Invoice $inv): bool =>
                $inv->status === 1 && Carbon::parse($inv->due_date)->lt($today)
            )
            ->count();

        $data['progressData'] = [
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
            ->where(UC::COL_NM, $customerName)
            ->where(DC::TABLE_CREATOR, $user?->creatorId())
            ->value('id') ?? 0;
    }
}
