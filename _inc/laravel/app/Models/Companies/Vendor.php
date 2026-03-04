<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    PermissionsConstants,
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
    HasAuditFields,
    NormalizesAddresses,
    UsesCountryRegions,
    UsesUuids,
};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\{Auth, Log};
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string|null $vendor_id
 * @property string|null $name
 * @property string|null $user_id
 * @property string|null $email
 * @property string|null $password
 * @property string|null $contact
 * @property string|null $avatar
 * @property bool $is_active
 * @property string|null $lang
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Vendor extends Authenticatable
{
    use UsesUuids;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use HasAuditFields;
    use NormalizesAddresses;
    use UsesCountryRegions;

    public const TABLE = DC::TABLE_VENDORS;

    protected $table = self::TABLE;

    protected $fillable = [
        UC::COL_VD_ID,

        UC::COL_NM,
        UC::COL_USER_ID,
        UC::COL_EM,
        UC::COL_PW,
        'contact',
        UC::COL_AV,
        UC::COL_IA,
        UC::COL_LG,
        'preferences',
        UC::COL_EM_V_AT,

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
        BC::COL_OT_TX_ID,
        BC::COL_IS_PRM,

        'balance',
        'offers',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $hidden = [
        UC::COL_PW,
        'remember_token',
    ];

    protected $casts = [
        UC::COL_EM_V_AT  => 'datetime',
        UC::COL_IA       => 'boolean',
        'preferences'    => 'array',
        BC::COL_OT_TX_ID => 'array',
        BC::COL_IS_PRM   => 'boolean',
        'balance'        => 'decimal:2',
        'offers'         => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $vendor): void {
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
                if (!empty($vendor->getAttribute($field)) && is_string($vendor->getAttribute($field)))
                    $vendor->setAttribute($field, trim($vendor->getAttribute($field)));
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            if ($isNormalizeEmailCallable) {
                if ($vendor->getAttribute(UC::COL_EM) ?? null)
                    $vendor->setAttribute(UC::COL_EM, self::normalizeEmail(
                        $vendor->getAttribute(UC::COL_EM),
                        'main',
                        $vendor->id ?? null
                    ));
                if ($vendor->getAttribute(BC::COL_BL_EMAIL) ?? null)
                    $vendor->setAttribute(BC::COL_BL_EMAIL, self::normalizeEmail(
                        $vendor->getAttribute(BC::COL_BL_EMAIL),
                        'billing',
                        $vendor->id ?? null
                    ));
            }
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            $isNormalizePhoneCallable && $vendor->setAttribute('contact', self::normalizePhone(
                $vendor->getAttribute('contact') ?? null,
                'contact',
                $vendor->id ?? null
            ));
            $isNormalizePhoneCallable && $vendor->setAttribute(BC::COL_BL_TEL, self::normalizePhone(
                $vendor->getAttribute(BC::COL_BL_TEL) ?? null,
                'billing',
                $vendor->id ?? null
            ));
            $isNormalizePhoneCallable && $vendor->setAttribute(BC::COL_SHIP_TEL, self::normalizePhone(
                $vendor->getAttribute(BC::COL_SHIP_TEL) ?? null,
                'shipping',
                $vendor->id ?? null
            ));
            if (!$vendor->getAttribute(UC::COL_LG))
                $vendor->setAttribute(UC::COL_LG, DC::DEFAULT_LANG);
            $billingCountryEnum = CountryName::normalize($vendor->getAttribute(BC::COL_BL_CTR) ?? null)
                ?? CountryName::Brazil;
            $shippingCountryEnum = CountryName::normalize($vendor->getAttribute(BC::COL_SHIP_CTR) ?? null)
                ?? CountryName::Brazil;
            $vendor->setAttribute(BC::COL_BL_CTR, $billingCountryEnum->value);
            $vendor->setAttribute(BC::COL_SHIP_CTR, $shippingCountryEnum->value);
            self::normalizeStateField(
                $vendor,
                BC::COL_BL_ST,
                $billingCountryEnum
            );
            self::normalizeStateField(
                $vendor,
                BC::COL_SHIP_ST,
                $shippingCountryEnum
            );
            $vendor->setAttribute(BC::COL_BL_ZIP, self::normalizeZip(
                $vendor->getAttribute(BC::COL_BL_ZIP) ?? null,
                $vendor->getAttribute(BC::COL_BL_CTR),
                'billing',
                $vendor->id ?? null
            ));
            $vendor->setAttribute(BC::COL_SHIP_ZIP, self::normalizeZip(
                $vendor->getAttribute(BC::COL_SHIP_ZIP) ?? null,
                $vendor->getAttribute(BC::COL_SHIP_CTR) ?? null,
                'shipping',
                $vendor->id ?? null
            ));
            self::normalizeBillingCountry($vendor);
            self::normalizeShippingCountry($vendor);
            if ($vendor->getAttribute('balance') === null || !is_numeric($vendor->getAttribute('balance')) || $vendor->getAttribute('balance') < 0)
                $vendor->setAttribute('balance', 0.00);
            if ($vendor->getAttribute(UC::COL_IA) === null)
                $vendor->setAttribute(UC::COL_IA, true);
            if ($vendor->getAttribute(BC::COL_IS_PRM) === null)
                $vendor->setAttribute(BC::COL_IS_PRM, false);
            try {
                $vendor->setAttribute('preferences', self::normalizeArrayField($vendor->getAttribute('preferences') ?? []));
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize preferences', [
                    UC::COL_VD_ID => $vendor->id ?? null,
                    'error'     => $e->getMessage(),
                ]);
                $vendor->setAttribute('preferences', []);
            }
            try {
                $vendor->setAttribute(BC::COL_OT_TX_ID, self::normalizeArrayField($vendor->getAttribute(BC::COL_OT_TX_ID) ?? []));
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize other_taxes_ids', [
                    UC::COL_VD_ID => $vendor->id ?? null,
                    'error'     => $e->getMessage(),
                ]);
                $vendor->setAttribute(BC::COL_OT_TX_ID, []);
            }
            try {
                $vendor->setAttribute('offers', self::sanitizeOffers($vendor->getAttribute('offers') ?? []));
            } catch (\Throwable $e) {
                Log::error(self::class . ' failed to normalize offers', [
                    UC::COL_VD_ID => $vendor->id ?? null,
                    'error'     => $e->getMessage(),
                ]);
                $vendor->setAttribute('offers', []);
            }
            try {
                if ($vendor->getAttribute(UC::COL_USER_ID) ?? null) {
                    $user = User::find($vendor->getAttribute(UC::COL_USER_ID));
                    if ($user === null || $user->type !== PermissionsConstants::VD)
                        $vendor->setAttribute(UC::COL_USER_ID, null);
                }
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed during saving hook', [
                    UC::COL_VD_ID => $vendor->id ?? null,
                    'error'     => $e->getMessage(),
                ]);
            }
        });
    }

    protected static function normalizeStateField(self $vendor, string $column, CountryName $country): void
    {
        $raw = $vendor->getAttribute($column) ?? null;
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
                    $vendor->setAttribute($column, strtoupper(trim($raw)));
                return;
        }
        $vendor->setAttribute($column, $normalized->value);
    }

    protected static function sanitizeOffers(mixed $raw): array
    {
        $items = self::normalizeArrayField($raw);
        if (!$items)
            return [];
        $valid = [];
        foreach ($items as $item) {
            if (!is_array($item))
                continue;
            $hasKey = false;
            foreach (['id', 'key', 'product_service_id', 'unit_id'] as $k) {
                if (!array_key_exists($k, $item))
                    continue;
                $v = $item[$k];
                if (is_string($v) && trim($v) !== '') {
                    $hasKey = true;
                    break;
                }
                if (is_int($v) || is_float($v)) {
                    $hasKey = true;
                    break;
                }
            }
            if (!$hasKey)
                continue;
            $valid[] = $item;
        }
        return $valid;
    }

    protected function resolveOfferProductService(mixed $identifier): ?ProductService
    {
        try {
            if (is_array($identifier))
                $identifier = $identifier['id'] ?? $identifier['key'] ?? null;

            if ($identifier === null)
                return null;

            $identifier = is_string($identifier)
                ? trim($identifier)
                : (string) $identifier;

            if ($identifier === '')
                return null;

            if (self::looksLikeUuid($identifier))
                return ProductService::find($identifier);

            $product = ProductService::query()
                ->where('sku', $identifier)
                ->orWhere('name', $identifier)
                ->first();

            return $product ?: null;
        } catch (\Throwable $e) {
            Log::warning(self::class . '::resolveOfferProductService failed', [
                UC::COL_VD_ID  => $this->id ?? null,
                'identifier' => $identifier,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function classifyOffers(): array
    {
        $result = [
            'registered'   => [],
            'unregistered' => [],
        ];

        try {
            $raw = $this->offers ?? [];
            if ($raw instanceof \Illuminate\Support\Collection || $raw instanceof \Illuminate\Database\Eloquent\Collection)
                $offers = $raw->toArray();
            elseif (!is_array($raw))
                $offers = self::normalizeArrayField($raw);
            else
                $offers = $raw;
            if (!$offers || !is_array($offers))
                return $result;
            foreach ($offers as $offer) {
                if (!is_array($offer)) {
                    if ($offer instanceof \JsonSerializable)
                        $offer = (array) $offer->jsonSerialize();
                    elseif (is_object($offer))
                        $offer = (array) $offer;
                    else
                        continue;
                }
                $identifier = $offer['id'] ?? $offer['key'] ?? null;
                if (!is_string($identifier))
                    continue;
                $identifier = trim($identifier);
                if ($identifier === '')
                    continue;
                $productService = $this->resolveOfferProductService($identifier);
                $bucket = $productService ? 'registered' : 'unregistered';
                $result[$bucket][] = [
                    'offer'           => $offer,
                    'product_service' => $productService,
                ];
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::classifyOffers failed', [
                'model_id' => $this->id ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
        return $result;
    }


    public function getRegisteredOffers(): array
    {
        try {
            $classified = $this->classifyOffers();
            return $classified['registered'] ?? [];
        } catch (\Throwable $e) {
            Log::error(self::class . '::getRegisteredOffers failed', [
                UC::COL_VD_ID => $this->id ?? null,
                'error'     => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getUnregisteredOffers(): array
    {
        try {
            $classified = $this->classifyOffers();
            return $classified['unregistered'] ?? [];
        } catch (\Throwable $e) {
            Log::error(self::class . '::getUnregisteredOffers failed', [
                UC::COL_VD_ID => $this->id ?? null,
                'error'     => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getAllOffers(): array
    {
        try {
            $classified = $this->classifyOffers();
            return array_merge(
                $classified['registered'] ?? [],
                $classified['unregistered'] ?? []
            );
        } catch (\Throwable $e) {
            Log::error(self::class . '::getAllOffers failed', [
                UC::COL_VD_ID => $this->id ?? null,
                'error'     => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function authId(): string|int
    {
        return $this->id;
    }

    public function creatorId(): string|int
    {
        return in_array($this->type ?? null, [
            PermissionsConstants::CPN,
            PermissionsConstants::SA,
        ])
            ? $this->id
            : ($this->{DC::COL_TABLE_CREATOR} ?? $this->id);
    }

    public function currentLanguage(): string
    {
        return $this->{UC::COL_LG};
    }

    public function priceFormat(float|int $price): string
    {
        $s = Utility::settings();
        return ($s[SC::CR_SB_P] === 'pre'
            ? $s[SC::CR_SB]
            : '')
            . number_format($price, $s['decimal_number'])
            . ($s[SC::CR_SB_P] === 'post'
                ? $s[SC::CR_SB]
                : '');
    }

    public function currencySymbol(): string
    {
        return Utility::settings()[SC::CR_SB] ?? '';
    }

    public function dateFormat(string $date): string
    {
        return date(
            Utility::settings()[SC::DT_FM] ?? 'Y-m-d',
            strtotime($date)
        );
    }

    public function timeFormat(string $time): string
    {
        return date(
            Utility::settings()[SC::TM_FM] ?? 'H:i:s',
            strtotime($time)
        );
    }

    public function invoiceNumberFormat(int $num): string
    {
        return Utility::settings()[SC::INV_PFX]
            . sprintf('%05d', $num);
    }

    public function purchaseNumberFormat(int $num): string
    {
        return Utility::settings()[SC::PRC_PFX]
            . sprintf('%05d', $num);
    }

    public function billNumberFormat(int $num): string
    {
        return Utility::settings()[SC::BL_PFX]
            . sprintf('%05d', $num);
    }

    public function representative()
    {
        return $this->belongsTo(User::class, UC::COL_VD_ID, 'id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, BC::COL_TX_N, 'id');
    }

    public function billChartData(): array
    {
        $monthsEnum = MonthName::ordered();

        $data['month']       = array_map(
            fn(MonthName $m) => $m->label(),
            $monthsEnum
        );
        $data['currentYear'] = date('Y');
        $user                = Auth::user();

        foreach ($monthsEnum as $monthEnum) {
            $i = $monthEnum->isoIndex();

            $unpaid = Bill::where(UC::COL_VD_ID, $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '1')
                ->where('due_date', '>', date('Y-m-d'))
                ->get()
                ->sum(fn($b) => $b->getDue());

            $paid = Bill::where(UC::COL_VD_ID, $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '4')
                ->get()
                ->sum(fn($b) => $b->getTotal());

            $partial = Bill::where(UC::COL_VD_ID, $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '3')
                ->get()
                ->sum(fn($b) => $b->getDue());

            $due = Bill::where(UC::COL_VD_ID, $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '1')
                ->where('due_date', '<', date('Y-m-d'))
                ->get()
                ->sum(fn($b) => $b->getDue());

            $data['data']['unpaid'][]  = $unpaid;
            $data['data']['paid'][]    = $paid;
            $data['data']['partial'][] = $partial;
            $data['data']['due'][]     = $due;
        }

        $total = Bill::where(UC::COL_VD_ID, $user?->id)
            ->whereYear('send_date', date('Y'))
            ->count();

        foreach (['unpaid', 'paid', 'partial', 'due'] as $k) {
            $cnt = count($data['data'][$k] ?? []);
            $data['progressData']["total{$k}Bill"] = $cnt;
            $data['progressData']["{$k}Pr"]        = $total
                ? ($cnt * 100) / $total
                : 0;
        }

        return $data;
    }

    public function vendorBill(int $vendorId): Collection
    {
        return Bill::where(UC::COL_VD_ID, $vendorId)
            ->orderBy('bill_date', 'desc')
            ->get();
    }

    public function vendorOverdue(int $vendorId): float|int
    {
        return Bill::where(UC::COL_VD_ID, $vendorId)
            ->whereNotIn('status', ['0', '4'])
            ->where('due_date', '<', date('Y-m-d'))
            ->get()
            ->sum(fn($b) => $b->getDue());
    }

    public function vendorTotalBill(int $vendorId): int
    {
        return Bill::where(UC::COL_VD_ID, $vendorId)->count();
    }

    public function vendorTotalBillSum(int $vendorId): float|int
    {
        return Bill::where(UC::COL_VD_ID, $vendorId)
            ->get()
            ->sum(fn($b) => $b->getTotal());
    }
}
