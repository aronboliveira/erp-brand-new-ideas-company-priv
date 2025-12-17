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
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
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
                if (!empty($customer->getAttribute($field)) && is_string($customer->getAttribute($field)))
                    $customer->setAttribute($field, trim($customer->getAttribute($field)));
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            if ($isNormalizeEmailCallable) {
                if ($customer->getAttribute(UC::COL_EM) ?? null)
                    $customer->setAttribute(UC::COL_EM, self::normalizeEmail(
                        $customer->getAttribute(UC::COL_EM),
                        'main',
                        $customer->getAttribute('id') ?? null
                    ));
                if ($customer->getAttribute(BC::COL_BL_EMAIL) ?? null)
                    $customer->setAttribute(BC::COL_BL_EMAIL, self::normalizeEmail(
                        $customer->getAttribute(BC::COL_BL_EMAIL),
                        'billing',
                        $customer->getAttribute('id') ?? null
                    ));
            }
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            if ($isNormalizePhoneCallable) {
                $customer->setAttribute('contact', self::normalizePhone(
                    $customer->getAttribute('contact') ?? null,
                    'contact',
                    $customer->getAttribute('id') ?? null,
                    true // todo remove after tests
                ));

                $customer->setAttribute(BC::COL_BL_TEL, self::normalizePhone(
                    $customer->getAttribute(BC::COL_BL_TEL) ?? null,
                    'billing',
                    $customer->getAttribute('id') ?? null,
                    true
                ));

                $customer->setAttribute(BC::COL_SHIP_TEL, self::normalizePhone(
                    $customer->getAttribute(BC::COL_SHIP_TEL) ?? null,
                    'shipping',
                    $customer->getAttribute('id') ?? null,
                    true
                ));
            }
            $isNormalizeBillingCountryCallable = is_callable([self::class, 'normalizeBillingCountry']);
            $isNormalizeBillingCountryCallable && self::normalizeBillingCountry($customer);
            $isNormalizeShippingCountryCallable = is_callable([self::class, 'normalizeShippingCountry']);
            $isNormalizeShippingCountryCallable && self::normalizeShippingCountry($customer);
            if ($customer->getAttribute(BC::COL_TX_N)) {
                if (!is_string($customer->getAttribute(BC::COL_TX_N))) {
                    if (is_numeric($customer->getAttribute(BC::COL_TX_N)))
                        $customer->setAttribute(BC::COL_TX_N, (string) $customer->getAttribute(BC::COL_TX_N));
                    else
                        $customer->setAttribute(BC::COL_TX_N, null);
                } else {
                    $raw = trim((string) $customer->getAttribute(BC::COL_TX_N));
                    if (static::looksLikeUuid($raw))
                        $customer->setAttribute(BC::COL_TX_N, strtolower($raw));
                    else {
                        $digits = preg_replace('/\D+/', '', $raw);
                        if (strlen($digits) === 11 || strlen($digits) === 14)
                            $customer->setAttribute(BC::COL_TX_N, $digits);
                        else
                            $customer->setAttribute(BC::COL_TX_N, null);
                    }
                }
            }

            try {
                $customer->setAttribute(BC::COL_OT_TX_ID, self::normalizeArrayField($customer->getAttribute(BC::COL_OT_TX_ID) ?? []));
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize other_taxes_ids', [
                    'customer_id' => $customer->id ?? null,
                    'error'       => $e->getMessage(),
                ]);
                $customer->setAttribute(BC::COL_OT_TX_ID, []);
            }

            try {
                $customer->setAttribute('preferences', self::normalizeArrayField($customer->getAttribute('preferences') ?? []));
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize preferences', [
                    'customer_id' => $customer->id ?? null,
                    'error'       => $e->getMessage(),
                ]);
                $customer->setAttribute('preferences', []);
            }
            if ($customer->getAttribute(UC::COL_AVG_RT) !== null) {
                $rating = (float) $customer->getAttribute(UC::COL_AVG_RT);
                if ($rating < 0) $rating = 0;
                if ($rating > 5) $rating = 5;
                $customer->setAttribute(UC::COL_AVG_RT, $rating);
            }
            if ($customer->getAttribute(BC::COL_OD_C) !== null && $customer->getAttribute(BC::COL_OD_C) < 0)
                $customer->setAttribute(BC::COL_OD_C, 0);
            if ($customer->getAttribute('balance') === null || !is_numeric($customer->getAttribute('balance')) || $customer->getAttribute('balance') < 0)
                $customer->setAttribute('balance', 0.00);
            if ($customer->getAttribute(UC::COL_LG))
                $customer->setAttribute(UC::COL_LG, strtolower(trim($customer->getAttribute(UC::COL_LG))));
            else
                $customer->setAttribute(UC::COL_LG, DC::DEFAULT_LANG);
            $billingCountryEnum = CountryName::normalize($customer->getAttribute(BC::COL_BL_CTR) ?? null)
                ?? CountryName::Brazil;
            $shippingCountryEnum = CountryName::normalize($customer->getAttribute(BC::COL_SHIP_CTR) ?? null)
                ?? CountryName::Brazil;
            $customer->setAttribute(BC::COL_BL_CTR, $billingCountryEnum->value);
            $customer->setAttribute(BC::COL_SHIP_CTR, $shippingCountryEnum->value);
            self::normalizeStateField($customer, BC::COL_BL_ST, $billingCountryEnum);
            self::normalizeStateField($customer, BC::COL_SHIP_ST, $shippingCountryEnum);
            $customer->setAttribute(BC::COL_BL_ZIP, self::normalizeZip(
                $customer->getAttribute(BC::COL_BL_ZIP) ?? null,
                $customer->getAttribute(BC::COL_BL_CTR),
                'billing',
                $customer->id ?? null
            ));
            $customer->setAttribute(BC::COL_SHIP_ZIP, self::normalizeZip(
                $customer->getAttribute(BC::COL_SHIP_ZIP) ?? null,
                $customer->getAttribute(BC::COL_SHIP_CTR),
                'shipping',
                $customer->id ?? null
            ));
            if ($customer->getAttribute(UC::COL_IA) === null)
                $customer->setAttribute(UC::COL_IA, true);

            if ($customer->getAttribute(BC::COL_IS_PRM) === null)
                $customer->setAttribute(BC::COL_IS_PRM, false);
            if (!$customer->getAttribute(BC::COL_CST_ID) && auth()->check())
                $customer->setAttribute(BC::COL_CST_ID, auth()->id());
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
            : ($this->{DC::COL_TABLE_CREATOR} ?? $this->id);
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
            ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
            ->value('id') ?? 0;
    }
}
