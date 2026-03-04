<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{
    BillStatus,
    PaymentStatus,
    TransactionType,
    UserType
};
use App\Traits\{
    DefinesDates,
    HasAuditFields,
    NormalizesAddresses,
    StoresManyRefJson,
    UsesCountryRegions,
    UsesUuids,
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany
};
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Collection;

// Same-namespace explicit imports (silences PHP Namespace Resolver)
use App\Models\BillAccount;
use App\Models\BillPayment;
use App\Models\BillProduct;
use App\Models\DebitNote;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductCategory;
use App\Models\ProductServiceCategory;
use App\Models\Tax;
use App\Models\Transaction;
use App\Models\Utility;

/**
 * @property string $id
 * @property string $bill_id
 * @property string|null $bill_date
 * @property string|null $send_date
 * @property string|null $due_date
 * @property string|null $vendor_id
 * @property string|null $category_id
 * @property string|null $order_id
 * @property int $status
 * @property string|null $status_label
 * @property string|null $payment_status
 * @property string|null $type
 * @property string|null $user_type
 * @property string|null $price_currency
 * @property string|null $currency_id
 * @property int $shipping_display
 * @property float $amount
 * @property float|null $discount
 * @property float|null $service_fee
 * @property float|null $taxes_fee
 * @property string|null $reference
 * @property string|null $description
 * @property string|null $notes
 * @property array|null $attachments
 * @property string|null $name
 * @property string|null $bill
 * @property string|null $url
 * @property mixed $customField
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Bill extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use StoresManyRefJson;
    use NormalizesAddresses;
    use UsesCountryRegions;
    use DefinesDates;

    protected $table = DC::TABLE_BILLS;

    /**
     * Legacy numeric status map (mirrors BillStatus enum values).
     */
    public static array $statuses = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partially Paid',
        'Paid',
    ];

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
        BC::COL_CUR_ID,
        BC::COL_SHIP_DSP,
        // Financial issuing
        'amount',
        'discount',
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        'reference',
        'description',
        'notes',
        'attachments',
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
        'contract',
        'loan',
        BC::COL_PRD_SV_UNT,
        // Aggregated taxes / items
        'taxes',
        'items',
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
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        BC::COL_BL_DT        => 'date',
        BC::COL_SD_DT        => 'date',
        PJC::COL_D_DATE      => 'date',
        PJC::COL_STATUS      => 'integer',
        BC::COL_STT_LB       => BillStatus::class,
        BC::COL_PAY_STT      => PaymentStatus::class,
        UC::COL_U_TP         => UserType::class,
        BC::COL_CUR_ID       => 'string',
        'amount'             => 'decimal:2',
        'discount'           => 'decimal:2',
        BC::COL_SVC_FEE      => 'decimal:2',
        BC::COL_TXS_FEE      => 'decimal:2',
        BC::COL_DSC_APL      => 'integer',
        BC::COL_SHIP_DSP     => 'integer',
        BC::COL_AUTORCC      => 'boolean',
        BC::COL_TC           => 'array',
        BC::COL_RCC_RL       => 'array',
        'taxes'              => 'array',
        'items'              => 'array',
        'attachments'        => 'array',
        BC::COL_SCHD_TRF_TS  => 'datetime',
        BC::COL_EXC_AT       => 'datetime',
        BC::COL_CNC_AT       => 'datetime',
        BC::COL_CMP_AT       => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::booted();
        // todo too heavy for mocking, use only in production
        // static::saving(function (self $bill): void {
        //     try {
        //         foreach ([BC::COL_PRC_CUR, 'notes'] as $field)
        //             if (!empty($bill->getAttribute($field)) && is_string($bill->getAttribute($field)))
        //                 $bill->setAttribute($field, trim($bill->getAttribute($field)));
        //         if ($bill->getAttribute(BC::COL_PRC_CUR)) {
        //             $curr = strtoupper(trim((string) $bill->getAttribute(BC::COL_PRC_CUR)));
        //             if (strlen($curr) < 3)
        //                 $curr = strtoupper(SC::DEF_SITE_CURRENCY_ID);
        //             elseif (strlen($curr) > 10)
        //                 $curr = substr($curr, 0, 10);
        //             $bill->setAttribute(BC::COL_PRC_CUR, $curr);
        //         } else
        //             $bill->setAttribute(BC::COL_PRC_CUR, strtoupper(SC::DEF_SITE_CURRENCY_ID));
        //         $bill->setAttribute(BC::COL_CUR_ID, strtoupper(substr(
        //             (string) $bill->getAttribute(BC::COL_PRC_CUR),
        //             0,
        //             3
        //         )));
        //         $today    = Carbon::today();
        //         $sendDate = self::safeParseDate($bill->getAttribute(BC::COL_SD_DT) ?? null) ?? $today->copy();
        //         $dueDate  = self::safeParseDate($bill->getAttribute(PJC::COL_D_DATE) ?? null) ?? $today->copy()->addDay();
        //         $billDate = self::safeParseDate($bill->getAttribute(BC::COL_BL_DT) ?? null) ?? $today->copy()->addDays(2);
        //         $maxDate = $dueDate;
        //         if ($sendDate->gt($maxDate))
        //             $maxDate = $sendDate;
        //         if ($billDate->gt($maxDate))
        //             $maxDate = $billDate;
        //         $dueDate = $maxDate;
        //         $bill->setAttribute(BC::COL_SD_DT, $sendDate->toDateString());
        //         $bill->setAttribute(PJC::COL_D_DATE, $dueDate->toDateString());
        //         $bill->setAttribute(BC::COL_BL_DT, $billDate->toDateString());
        //         $status = $bill->getAttribute(BC::COL_STT_LB) ?? null;
        //         if ($status instanceof BillStatus)
        //             $billStatus = $status;
        //         else
        //             $billStatus = BillStatus::tryFrom((string) $status) ?? BillStatus::Draft;
        //         $bill->setAttribute(BC::COL_STT_LB, $billStatus);
        //         $originalRaw  = $bill->getOriginal(BC::COL_PAY_STT);
        //         $originalEnum = $originalRaw !== null
        //             ? PaymentStatus::normalize($originalRaw)
        //             : PaymentStatus::Processing;
        //         $newEnum = PaymentStatus::normalize($bill->getAttribute(BC::COL_PAY_STT) ?? null);
        //         if (!$bill->exists && $newEnum === PaymentStatus::Undefined)
        //             $newEnum = PaymentStatus::Processing;
        //         $finalStatuses = [
        //             PaymentStatus::Completed,
        //             PaymentStatus::Cancelled,
        //             PaymentStatus::Failed,
        //             PaymentStatus::Refunded,
        //             PaymentStatus::PartiallyRefunded,
        //             PaymentStatus::Expired,
        //             PaymentStatus::Disputed,
        //         ];
        //         if (
        //             $bill->exists
        //             && in_array($originalEnum, $finalStatuses, true)
        //             && $newEnum !== $originalEnum
        //         )
        //             $bill->setAttribute(BC::COL_PAY_STT, $originalEnum);
        //         else
        //             $bill->setAttribute(BC::COL_PAY_STT, $newEnum);
        //         $allowedTypes = [];
        //         try {
        //             $allowedTypes = array_unique(array_merge(
        //                 ConsumableType::values(),
        //                 array_filter(
        //                     TransactionType::values(),
        //                     fn(string $v): bool => $v === TransactionType::Bill->value
        //                 )
        //             ));
        //         } catch (\Throwable $e) {
        //             Log::warning(self::class . '::saving failed to load type values', [
        //                 'error' => $e->getMessage(),
        //             ]);
        //         }
        //         $rawType = strtolower(trim((string) ($bill->type ?? '')));
        //         if ($rawType === '')
        //             $rawType = ConsumableType::Other->value;
        //         if ($allowedTypes && !in_array($rawType, $allowedTypes, true))
        //             $rawType = ConsumableType::Other->value;
        //         $bill->setAttribute('type', $rawType);
        //         $userType = $bill->getAttribute(UC::COL_U_TP) ?? null;
        //         if ($userType instanceof UserType)
        //             $bill->setAttribute(UC::COL_U_TP, $userType);
        //         else
        //             $bill->setAttribute(UC::COL_U_TP, UserType::tryFrom((string) $userType) ?? UserType::Customer);
        //         $amount = (float) ($bill->amount ?? 0.0);
        //         if ($amount < 0)
        //             $amount = 0.0;
        //         $amount = round($amount, 2);
        //         $discount = (float) ($bill->discount ?? 0.0);
        //         if ($discount < 0)
        //             $discount = 0.0;
        //         if ($discount > $amount)
        //             $discount = $amount;
        //         $bill->setAttribute('amount', $amount);
        //         $bill->setAttribute('discount', $discount);
        //         $svcFee = (float) ($bill->getAttribute(BC::COL_SVC_FEE) ?? 0.0);
        //         if ($svcFee < 0)
        //             $svcFee = 0.0;
        //         $bill->setAttribute(BC::COL_SVC_FEE, round($svcFee, 2));
        //         $taxFee = (float) ($bill->getAttribute(BC::COL_TXS_FEE) ?? 0.0);
        //         if ($taxFee < 0)
        //             $taxFee = 0.0;
        //         $bill->setAttribute(BC::COL_TXS_FEE, round($taxFee, 2));
        //         $bill->setAttribute(BC::COL_DSC_APL, (int) ($bill->getAttribute(BC::COL_DSC_APL) ?? 0));
        //         if ($bill->getAttribute(BC::COL_DSC_APL) < 0)
        //             $bill->setAttribute(BC::COL_DSC_APL, 0);
        //         if ($bill->getAttribute(BC::COL_DSC_APL) > 1)
        //             $bill->setAttribute(BC::COL_DSC_APL, 1);
        //         $bill->setAttribute(BC::COL_SHIP_DSP, (int) ($bill->getAttribute(BC::COL_SHIP_DSP) ?? 1));
        //         if ($bill->getAttribute(BC::COL_SHIP_DSP) < 0)
        //             $bill->setAttribute(BC::COL_SHIP_DSP, 0);
        //         if ($bill->getAttribute(BC::COL_SHIP_DSP) > 1)
        //             $bill->setAttribute(BC::COL_SHIP_DSP, 1);
        //         $bill->setAttribute('taxes', self::sanitizeTaxes($bill->getAttribute('taxes') ?? null));
        //         $bill->setAttribute('items', self::normalizeArrayField($bill->getAttribute('items') ?? null));
        //         $bill->setAttribute('attachments', self::normalizeArrayField($bill->getAttribute('attachments') ?? null));
        //         self::normalizeBillingCountry($bill);
        //         self::normalizeShippingCountry($bill);
        //         $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
        //         if (!empty($bill->getAttribute(BC::COL_BL_EMAIL)) && $isNormalizeEmailCallable)
        //             $bill->setAttribute(BC::COL_BL_EMAIL, self::normalizeEmail($bill->getAttribute(BC::COL_BL_EMAIL), $bill->getAttribute(BC::COL_BL_NAME) ?? null, $bill->getAttribute('id') ?? null));
        //         if (!empty($bill->getAttribute(BC::COL_SHIP_EMAIL)) && $isNormalizeEmailCallable)
        //             $bill->setAttribute(BC::COL_SHIP_EMAIL, self::normalizeEmail($bill->getAttribute(BC::COL_SHIP_EMAIL), $bill->getAttribute(BC::COL_SHIP_NAME) ?? null, $bill->getAttribute('id') ?? null));
        //         $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
        //         if (!empty($bill->getAttribute(BC::COL_BL_TEL)) && $isNormalizePhoneCallable)
        //             $bill->setAttribute(BC::COL_BL_TEL, self::normalizePhone($bill->getAttribute(BC::COL_BL_TEL), $bill->getAttribute(BC::COL_BL_NAME) ?? null, $bill->getAttribute('id') ?? null));
        //         if (!empty($bill->getAttribute(BC::COL_SHIP_TEL)) && $isNormalizePhoneCallable)
        //             $bill->setAttribute(BC::COL_SHIP_TEL, self::normalizePhone($bill->getAttribute(BC::COL_SHIP_TEL), $bill->getAttribute(BC::COL_SHIP_NAME) ?? null, $bill->getAttribute('id') ?? null));
        //         $isNormalizeZipCallable = is_callable([self::class, 'normalizeZip']);
        //         if (!empty($bill->getAttribute(BC::COL_BL_ZIP)) && !empty($bill->getAttribute(BC::COL_BL_CTR)) && $isNormalizeZipCallable)
        //             $bill->setAttribute(BC::COL_BL_ZIP, self::normalizeZip(
        //                 $bill->getAttribute(BC::COL_BL_ZIP),
        //                 $bill->getAttribute(BC::COL_BL_CTR),
        //                 'Bill billing',
        //                 $bill->getAttribute('id') ?? null
        //             ));
        //         if (!empty($bill->getAttribute(BC::COL_SHIP_ZIP)) && !empty($bill->getAttribute(BC::COL_SHIP_CTR)) && $isNormalizeZipCallable)
        //             $bill->setAttribute(BC::COL_SHIP_ZIP, self::normalizeZip(
        //                 $bill->getAttribute(BC::COL_SHIP_ZIP),
        //                 $bill->getAttribute(BC::COL_SHIP_CTR),
        //                 'Bill shipping',
        //                 $bill->getAttribute('id') ?? null
        //             ));
        //     } catch (\Throwable $e) {
        //         Log::warning(self::class . '::saving normalization failed', [
        //             'id'    => $bill->id ?? null,
        //             'error' => $e->getMessage(),
        //             'line' => $e->getLine(),
        //             'file' => $e->getFile(),
        //         ]);
        //     }
        // });
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
        return Utility::getVendor($this);
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

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, BC::COL_CAT_ID, 'id');
    }

    public function category(): BelongsTo
    {
        return Utility::getCategory($this);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, BC::COL_OD_ID, 'id');
    }

    public function debitNote(): HasMany
    {
        return $this->debitNotes();
    }

    public function debitNotes(): HasMany
    {
        return $this->hasMany(DebitNote::class, 'bill', 'id');
    }

    public function billPayments(): HasMany
    {
        try {
            $billPaymentsTable = (new BillPayment)->getTable();
            $billForeignKeyColumn = null;

            if (Schema::hasColumn($billPaymentsTable, BC::COL_BL_ID)) $billForeignKeyColumn = BC::COL_BL_ID;
            elseif (Schema::hasColumn($billPaymentsTable, BC::COL_BIL_ID)) $billForeignKeyColumn = BC::COL_BIL_ID;
            elseif (Schema::hasColumn($billPaymentsTable, 'bill')) $billForeignKeyColumn = 'bill';

            if (!$billForeignKeyColumn) {
                Log::error('Bill::billPayments - No valid bill FK column found in bill_payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $billPaymentsTable,
                    'checked_columns' => [BC::COL_BL_ID, BC::COL_BIL_ID, 'bill'],
                ]);

                return $this->hasMany(BillPayment::class, BC::COL_BL_ID, $this->getKeyName());
            }

            Log::debug('Bill::billPayments - Using bill FK column in bill_payments table', [
                'class' => static::class,
                'column_used' => $billForeignKeyColumn,
            ]);

            return $this->hasMany(BillPayment::class, $billForeignKeyColumn, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Bill::billPayments - Failed to determine bill FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(BillPayment::class, BC::COL_BL_ID, $this->getKeyName());
        }
    }

    public function paymentPayments(): HasMany
    {
        try {
            $paymentsTable = (new Payment)->getTable();
            $billForeignKeyColumn = null;

            if (Schema::hasColumn($paymentsTable, BC::COL_BL_ID)) $billForeignKeyColumn = BC::COL_BL_ID;
            elseif (Schema::hasColumn($paymentsTable, BC::COL_BIL_ID)) $billForeignKeyColumn = BC::COL_BIL_ID;
            elseif (Schema::hasColumn($paymentsTable, 'bill')) $billForeignKeyColumn = 'bill';

            if (!$billForeignKeyColumn) {
                Log::error('Bill::paymentPayments - No valid bill FK column found in payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $paymentsTable,
                    'checked_columns' => [BC::COL_BL_ID, BC::COL_BIL_ID, 'bill'],
                ]);

                return $this->hasMany(Payment::class, BC::COL_BL_ID, $this->getKeyName());
            }

            Log::debug('Bill::paymentPayments - Using bill FK column in payments table', [
                'class' => static::class,
                'column_used' => $billForeignKeyColumn,
            ]);

            return $this->hasMany(Payment::class, $billForeignKeyColumn, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Bill::paymentPayments - Failed to determine bill FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Payment::class, BC::COL_BL_ID, $this->getKeyName());
        }
    }

    public function payments(): HasMany
    {
        try {
            $billPaymentsTable = (new BillPayment)->getTable();
            $paymentsTable = (new Payment)->getTable();

            $billForeignKeyAlias = BC::COL_BL_ID;

            $billForeignKeyInBillPayments = Schema::hasColumn($billPaymentsTable, BC::COL_BL_ID)
                ? BC::COL_BL_ID
                : (Schema::hasColumn($billPaymentsTable, BC::COL_BIL_ID)
                    ? BC::COL_BIL_ID
                    : (Schema::hasColumn($billPaymentsTable, 'bill') ? 'bill' : null));

            $billForeignKeyInPayments = Schema::hasColumn($paymentsTable, BC::COL_BL_ID)
                ? BC::COL_BL_ID
                : (Schema::hasColumn($paymentsTable, BC::COL_BIL_ID)
                    ? BC::COL_BIL_ID
                    : (Schema::hasColumn($paymentsTable, 'bill') ? 'bill' : null));

            if (!$billForeignKeyInBillPayments || !$billForeignKeyInPayments) {
                Log::error('Bill::payments - Missing bill FK column in one or both tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'bill_payments_table' => $billPaymentsTable,
                    'payments_table' => $paymentsTable,
                    'bill_payments_fk' => $billForeignKeyInBillPayments,
                    'payments_fk' => $billForeignKeyInPayments,
                    'checked_columns' => [BC::COL_BL_ID, BC::COL_BIL_ID, 'bill'],
                ]);

                return $this->hasMany(Payment::class, $billForeignKeyAlias, $this->getKeyName());
            }

            $billPaymentsColumns = Schema::getColumnListing($billPaymentsTable);
            $paymentsColumns = Schema::getColumnListing($paymentsTable);

            $unionColumns = array_values(array_unique(array_merge(
                $billPaymentsColumns,
                $paymentsColumns,
                [$billForeignKeyAlias, '_source']
            )));

            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $billPaymentsSelect = [];
            foreach ($unionColumns as $col) {
                if ($col === '_source') {
                    $billPaymentsSelect[] = "'bill_payments' as `_source`";
                    continue;
                }
                if ($col === $billForeignKeyAlias) {
                    $billPaymentsSelect[] = "`{$billPaymentsTable}`.`{$billForeignKeyInBillPayments}` as `{$billForeignKeyAlias}`";
                    continue;
                }
                $billPaymentsSelect[] = \in_array($col, $billPaymentsColumns, true)
                    ? "`{$billPaymentsTable}`.`{$col}` as `{$col}`"
                    : "NULL as `{$col}`";
            }

            $paymentsSelect = [];
            foreach ($unionColumns as $col) {
                if ($col === '_source') {
                    $paymentsSelect[] = "'payments' as `_source`";
                    continue;
                }
                if ($col === $billForeignKeyAlias) {
                    $paymentsSelect[] = "`{$paymentsTable}`.`{$billForeignKeyInPayments}` as `{$billForeignKeyAlias}`";
                    continue;
                }
                $paymentsSelect[] = \in_array($col, $paymentsColumns, true)
                    ? "`{$paymentsTable}`.`{$col}` as `{$col}`"
                    : "NULL as `{$col}`";
            }

            $derivedAlias = 'bill_payments_union';

            $unionSql =
                "SELECT " . implode(', ', $billPaymentsSelect) . " FROM `{$billPaymentsTable}` " .
                "UNION ALL " .
                "SELECT " . implode(', ', $paymentsSelect) . " FROM `{$paymentsTable}`";

            $relation = $this->hasMany(Payment::class, $billForeignKeyAlias, $this->getKeyName());
            $relation->getQuery()->from(DB::raw("({$unionSql}) as `{$derivedAlias}`"));

            Log::debug('Bill::payments - Using union-backed HasMany', [
                'class' => static::class,
                'bill_payments_table' => $billPaymentsTable,
                'payments_table' => $paymentsTable,
                'bill_fk_alias' => $billForeignKeyAlias,
                'bill_payments_fk' => $billForeignKeyInBillPayments,
                'payments_fk' => $billForeignKeyInPayments,
                'columns' => \count($unionColumns),
                'alias' => $derivedAlias,
            ]);

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Bill::payments - Failed to build union-backed HasMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'bill_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Payment::class, BC::COL_BL_ID, $this->getKeyName());
        }
    }

    public function billPaymentsRaw(): Collection
    {
        try {
            $billPaymentsTable = (new BillPayment)->getTable();
            $billForeignKeyColumn = null;

            if (Schema::hasColumn($billPaymentsTable, BC::COL_BL_ID)) $billForeignKeyColumn = BC::COL_BL_ID;
            elseif (Schema::hasColumn($billPaymentsTable, BC::COL_BIL_ID)) $billForeignKeyColumn = BC::COL_BIL_ID;
            elseif (Schema::hasColumn($billPaymentsTable, 'bill')) $billForeignKeyColumn = 'bill';

            if (!$billForeignKeyColumn) {
                Log::error('Bill::billPaymentsRaw - No valid bill FK column found in bill_payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $billPaymentsTable,
                    'checked_columns' => [BC::COL_BL_ID, BC::COL_BIL_ID, 'bill'],
                ]);

                return collect([]);
            }

            $sql = "SELECT * FROM `{$billPaymentsTable}` WHERE `{$billForeignKeyColumn}` = ?";
            $rows = DB::select($sql, [$this->getKey()]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Bill::billPaymentsRaw - Failed to retrieve bill payments', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'bill_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function paymentPaymentsRaw(): Collection
    {
        try {
            $paymentsTable = (new Payment)->getTable();
            $billForeignKeyColumn = null;

            if (Schema::hasColumn($paymentsTable, BC::COL_BL_ID)) $billForeignKeyColumn = BC::COL_BL_ID;
            elseif (Schema::hasColumn($paymentsTable, BC::COL_BIL_ID)) $billForeignKeyColumn = BC::COL_BIL_ID;
            elseif (Schema::hasColumn($paymentsTable, 'bill')) $billForeignKeyColumn = 'bill';

            if (!$billForeignKeyColumn) {
                Log::error('Bill::paymentPaymentsRaw - No valid bill FK column found in payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $paymentsTable,
                    'checked_columns' => [BC::COL_BL_ID, BC::COL_BIL_ID, 'bill'],
                ]);

                return collect([]);
            }

            $sql = "SELECT * FROM `{$paymentsTable}` WHERE `{$billForeignKeyColumn}` = ?";
            $rows = DB::select($sql, [$this->getKey()]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Bill::paymentPaymentsRaw - Failed to retrieve payments', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'bill_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function paymentsRaw(): Collection
    {
        try {
            $billPaymentsTable = (new BillPayment)->getTable();
            $paymentsTable = (new Payment)->getTable();

            $billForeignKeyInBillPayments = Schema::hasColumn($billPaymentsTable, BC::COL_BL_ID)
                ? BC::COL_BL_ID
                : (Schema::hasColumn($billPaymentsTable, BC::COL_BIL_ID)
                    ? BC::COL_BIL_ID
                    : (Schema::hasColumn($billPaymentsTable, 'bill') ? 'bill' : null));

            $billForeignKeyInPayments = Schema::hasColumn($paymentsTable, BC::COL_BL_ID)
                ? BC::COL_BL_ID
                : (Schema::hasColumn($paymentsTable, BC::COL_BIL_ID)
                    ? BC::COL_BIL_ID
                    : (Schema::hasColumn($paymentsTable, 'bill') ? 'bill' : null));

            if (!$billForeignKeyInBillPayments || !$billForeignKeyInPayments) {
                Log::error('Bill::paymentsRaw - Missing bill FK column in one or both tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'bill_payments_table' => $billPaymentsTable,
                    'payments_table' => $paymentsTable,
                    'bill_payments_fk' => $billForeignKeyInBillPayments,
                    'payments_fk' => $billForeignKeyInPayments,
                    'checked_columns' => [BC::COL_BL_ID, BC::COL_BIL_ID, 'bill'],
                ]);

                return collect([]);
            }

            $billId = $this->getKey();

            $billPaymentsColumns = Schema::getColumnListing($billPaymentsTable);
            $paymentsColumns = Schema::getColumnListing($paymentsTable);

            $unionColumns = array_values(array_unique(array_merge(
                $billPaymentsColumns,
                $paymentsColumns,
                [BC::COL_BL_ID, '_source']
            )));

            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $billPaymentsSelect = [];
            foreach ($unionColumns as $col) {
                if ($col === '_source') {
                    $billPaymentsSelect[] = "'bill_payments' as `_source`";
                    continue;
                }
                if ($col === BC::COL_BL_ID) {
                    $billPaymentsSelect[] = "`{$billPaymentsTable}`.`{$billForeignKeyInBillPayments}` as `" . BC::COL_BL_ID . "`";
                    continue;
                }
                $billPaymentsSelect[] = \in_array($col, $billPaymentsColumns, true)
                    ? "`{$billPaymentsTable}`.`{$col}` as `{$col}`"
                    : "NULL as `{$col}`";
            }

            $paymentsSelect = [];
            foreach ($unionColumns as $col) {
                if ($col === '_source') {
                    $paymentsSelect[] = "'payments' as `_source`";
                    continue;
                }
                if ($col === BC::COL_BL_ID) {
                    $paymentsSelect[] = "`{$paymentsTable}`.`{$billForeignKeyInPayments}` as `" . BC::COL_BL_ID . "`";
                    continue;
                }
                $paymentsSelect[] = \in_array($col, $paymentsColumns, true)
                    ? "`{$paymentsTable}`.`{$col}` as `{$col}`"
                    : "NULL as `{$col}`";
            }

            $sql =
                "SELECT " . implode(', ', $billPaymentsSelect) . " FROM `{$billPaymentsTable}` WHERE `{$billForeignKeyInBillPayments}` = ? " .
                "UNION ALL " .
                "SELECT " . implode(', ', $paymentsSelect) . " FROM `{$paymentsTable}` WHERE `{$billForeignKeyInPayments}` = ?";

            $rows = DB::select($sql, [$billId, $billId]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Bill::paymentsRaw - Failed to retrieve merged payments', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'bill_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    /**
     * Bill line-item products (bill_products table).
     *
     * @return HasMany<BillProduct, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillProduct::class, BC::COL_BL_ID, 'id');
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
