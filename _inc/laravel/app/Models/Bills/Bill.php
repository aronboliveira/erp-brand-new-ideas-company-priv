<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Enums\{
    BillStatus,
    ConsumableType,
    PaymentStatus,
    TransactionType,
    UserType
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany
};
use Illuminate\Support\Facades\Log;

class Bill extends Model
{
    use HasAuditFields;
    use HasFactory;
    use NormalizesArrays;
    use UsesUuids;

    protected $table = DC::TABLE_BILLS;

    protected $fillable = [
        BC::COL_BL_ID,
        BC::COL_BL_DT,
        BC::COL_SD_DT,
        PJC::COL_D_DATE,
        UC::COL_VD_ID,
        BC::COL_CAT_ID,
        BC::COL_OD_ID,
        PJC::COL_STATUS,
        BC::COL_STT_LB,
        BC::COL_PAY_STT,
        'type',
        UC::COL_U_TP,
        BC::COL_PRC_CUR,
        BC::COL_SHIP_DSP,
        'amount',
        BC::COL_DSC_APL,
        'discount',
        'taxes',
        'items',
        'notes',
        'attachments',
        // billing
        BC::COL_BL_NAME,
        BC::COL_BL_EMAIL,
        BC::COL_BL_ADR,
        BC::COL_BL_TEL,
        BC::COL_BL_ZIP,
        BC::COL_BL_CTY,
        BC::COL_BL_ST,
        BC::COL_BL_CTR,
        BC::COL_BL_DTL,
        // shipping
        BC::COL_SHIP_NAME,
        BC::COL_SHIP_EMAIL,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_ZIP,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_DTL,
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        BC::COL_BL_DT    => 'date',
        BC::COL_SD_DT    => 'date',
        PJC::COL_D_DATE  => 'date',
        PJC::COL_STATUS  => 'integer',
        BC::COL_STT_LB   => BillStatus::class,
        BC::COL_PAY_STT  => PaymentStatus::class,
        UC::COL_U_TP     => UserType::class,
        'amount'         => 'decimal:2',
        'discount'       => 'decimal:2',
        BC::COL_DSC_APL  => 'integer',
        BC::COL_SHIP_DSP => 'integer',
        'taxes'          => 'array',
        'items'          => 'array',
        'attachments'    => 'array',
    ];

    protected $with = [
        'vendor',
        'productServiceCategory',
        'products',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $bill): void {
            try {
                // Trim simple string fields
                foreach ([BC::COL_PRC_CUR, 'notes'] as $field)
                    if (isset($bill->{$field}) && is_string($bill->{$field}))
                        $bill->{$field} = trim($bill->{$field});

                // Currency normalization / default
                if ($bill->{BC::COL_PRC_CUR}) {
                    $curr = strtoupper(trim((string) $bill->{BC::COL_PRC_CUR}));
                    if (strlen($curr) < 3)
                        $curr = strtoupper(SC::DEF_SITE_CURRENCY_ID);
                    elseif (strlen($curr) > 10)
                        $curr = substr($curr, 0, 10);
                    $bill->{BC::COL_PRC_CUR} = $curr;
                } else {
                    $bill->{BC::COL_PRC_CUR} = strtoupper(SC::DEF_SITE_CURRENCY_ID);
                }

                $today    = Carbon::today();
                $sendDate = self::safeParseDate($bill->{BC::COL_SD_DT} ?? null) ?? $today->copy();
                $dueDate  = self::safeParseDate($bill->{PJC::COL_D_DATE} ?? null) ?? $today->copy()->addDay();
                $billDate = self::safeParseDate($bill->{BC::COL_BL_DT} ?? null) ?? $today->copy()->addDays(2);

                // due_date must be >= bill_date and >= send_date
                $maxDate = $dueDate;
                if ($sendDate->gt($maxDate)) $maxDate = $sendDate;
                if ($billDate->gt($maxDate)) $maxDate = $billDate;
                $dueDate = $maxDate;

                $bill->{BC::COL_SD_DT}   = $sendDate->toDateString();
                $bill->{PJC::COL_D_DATE} = $dueDate->toDateString();
                $bill->{BC::COL_BL_DT}   = $billDate->toDateString();

                $status = $bill->{BC::COL_STT_LB} ?? null;
                if ($status instanceof BillStatus)
                    $billStatus = $status;
                else
                    $billStatus = BillStatus::tryFrom((string) $status) ?? BillStatus::Draft;
                $bill->{BC::COL_STT_LB} = $billStatus;

                $originalRaw  = $bill->getOriginal(BC::COL_PAY_STT);
                $originalEnum = $originalRaw !== null
                    ? PaymentStatus::normalize($originalRaw)
                    : PaymentStatus::Processing;

                $newEnum = PaymentStatus::normalize($bill->{BC::COL_PAY_STT} ?? null);

                if (!$bill->exists && $newEnum === PaymentStatus::Undefined)
                    $newEnum = PaymentStatus::Processing;

                $finalStatuses = [
                    PaymentStatus::Completed,
                    PaymentStatus::Cancelled,
                    PaymentStatus::Failed,
                    PaymentStatus::Refunded,
                    PaymentStatus::PartiallyRefunded,
                    PaymentStatus::Expired,
                    PaymentStatus::Disputed,
                ];

                if (
                    $bill->exists
                    && in_array($originalEnum, $finalStatuses, true)
                    && $newEnum !== $originalEnum
                ) $bill->{BC::COL_PAY_STT} = $originalEnum;
                else
                    $bill->{BC::COL_PAY_STT} = $newEnum;

                $allowedTypes = [];
                try {
                    $allowedTypes = array_unique(array_merge(
                        ConsumableType::values(),
                        array_filter(
                            TransactionType::values(),
                            fn(string $v): bool => $v === TransactionType::Bill->value
                        )
                    ));
                } catch (\Throwable $e) {
                    Log::warning(self::class . '::saving failed to load type values', [
                        'error' => $e->getMessage(),
                    ]);
                }

                $rawType = strtolower(trim((string) ($bill->type ?? '')));
                if ($rawType === '') $rawType = ConsumableType::Other->value;
                if ($allowedTypes && !in_array($rawType, $allowedTypes, true))
                    $rawType = ConsumableType::Other->value;
                $bill->type = $rawType;

                $userType = $bill->{UC::COL_U_TP} ?? null;
                if ($userType instanceof UserType)
                    $bill->{UC::COL_U_TP} = $userType;
                else
                    $bill->{UC::COL_U_TP} = UserType::tryFrom((string) $userType) ?? UserType::Customer;

                $amount = (float) ($bill->amount ?? 0.0);
                if ($amount < 0) $amount = 0.0;
                $amount = round($amount, 2);

                $discount = (float) ($bill->discount ?? 0.0);
                if ($discount < 0) $discount = 0.0;
                if ($discount > $amount) $discount = $amount;

                $bill->amount   = $amount;
                $bill->discount = $discount;

                $bill->{BC::COL_DSC_APL} = (int) ($bill->{BC::COL_DSC_APL} ?? 0);
                if ($bill->{BC::COL_DSC_APL} < 0) $bill->{BC::COL_DSC_APL} = 0;
                if ($bill->{BC::COL_DSC_APL} > 1) $bill->{BC::COL_DSC_APL} = 1;

                $bill->{BC::COL_SHIP_DSP} = (int) ($bill->{BC::COL_SHIP_DSP} ?? 1);
                if ($bill->{BC::COL_SHIP_DSP} < 0) $bill->{BC::COL_SHIP_DSP} = 0;
                if ($bill->{BC::COL_SHIP_DSP} > 1) $bill->{BC::COL_SHIP_DSP} = 1;

                $bill->taxes       = self::sanitizeTaxes($bill->taxes ?? null);
                $bill->items       = self::normalizeArrayField($bill->items ?? null);
                $bill->attachments = self::normalizeArrayField($bill->attachments ?? null);
            } catch (\Throwable $e) {
                Log::warning(self::class . '::saving normalization failed', [
                    'id'    => $bill->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    protected static function safeParseDate(mixed $value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function sanitizeTaxes(mixed $raw): array
    {
        $items = self::normalizeArrayField($raw);
        if (!$items) return [];

        $valid = [];

        foreach ($items as $item) {
            if (!is_array($item))
                continue;

            $value = $item['id']
                ?? $item['tax_id']
                ?? $item['key']
                ?? null;

            if (is_string($value))
                $value = trim($value);

            if ($value === null || $value === '')
                continue;

            try {
                if (Tax::where('id', $value)->exists())
                    $valid[] = $item;
            } catch (\Throwable $e) {
                Log::warning(self::class . '::sanitizeTaxes query failed', [
                    'tax_id' => $value,
                    'error'  => $e->getMessage(),
                ]);
            }
        }

        return $valid;
    }

    public function getSubTotal(): float
    {
        $items = is_array($this->items) ? $this->items : [];
        $sub   = 0.0;

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $price    = (float) ($item['price'] ?? 0);
            $quantity = (float) ($item['quantity'] ?? 1);

            if ($price < 0) $price = 0;
            if ($quantity <= 0) $quantity = 1;

            $sub += $price * $quantity;
        }

        return $sub + $this->getAccountTotal();
    }

    public function getTotalDiscount(): float
    {
        $items = is_array($this->items) ? $this->items : [];
        $total = 0.0;

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $discount = (float) ($item['discount'] ?? 0);
            if ($discount < 0) $discount = 0;

            $total += $discount;
        }

        return $total;
    }

    public function getTotalTax(): float
    {
        $items = is_array($this->items) ? $this->items : [];
        $total = 0.0;

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $price    = (float) ($item['price'] ?? 0);
            $quantity = (float) ($item['quantity'] ?? 1);
            $discount = (float) ($item['discount'] ?? 0);

            if ($price < 0) $price = 0;
            if ($quantity <= 0) $quantity = 1;
            if ($discount < 0) $discount = 0;

            $base = $price * $quantity - $discount;
            if ($base <= 0) continue;

            $rate = 0.0;
            try {
                $rate = (float) Utility::totalTaxRate($item['tax'] ?? null);
            } catch (\Throwable $e) {
                Log::warning(self::class . '::getTotalTax totalTaxRate failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            if ($rate < 0) $rate = 0;
            $total += ($rate / 100.0) * $base;
        }

        return $total;
    }

    public function getAccountTotal(): float
    {
        return (float) $this->accounts()->sum('price');
    }

    public function getTotal(): float
    {
        return ($this->getSubTotal() - $this->getTotalDiscount())
            + $this->getTotalTax();
    }

    public function getDue(): float
    {
        $paid = (float) $this->payments()->sum('amount');
        return max(0.0, $this->getTotal() - $paid - $this->billTotalDebitNote());
    }

    public function billTotalDebitNote(): float
    {
        return (float) $this->debitNotes()->sum('amount');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, UC::COL_VD_ID, 'id');
    }

    public function vender(): BelongsTo
    {
        return $this->vendor();
    }

    public function productServiceCategory(): BelongsTo
    {
        return $this->belongsTo(
            ProductServiceCategory::class,
            BC::COL_CAT_ID,
            'id'
        );
    }

    public function category(): BelongsTo // * kepted for compatibility, don't reuse
    {
        return $this->productServiceCategory();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, BC::COL_OD_ID, 'id');
    }

    public function debitNotes(): HasMany
    {
        return $this->hasMany(DebitNote::class, 'bill', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class, BC::COL_BL_ID, 'id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(BillAccount::class, 'ref_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, BC::COL_PAY_ID, 'id')
            ->where(BC::COL_PAY_TP, TransactionType::Bill->value);
    }
}
