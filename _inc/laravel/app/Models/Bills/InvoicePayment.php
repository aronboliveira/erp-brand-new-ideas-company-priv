<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Enums\{
    PaymentMethod,
    PaymentStatus,
    PaymentType
};
use App\Traits\{
    HasAuditFields,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class InvoicePayment extends Model
{
    use HasAuditFields;
    use UsesUuids;

    protected $table = DC::TABLE_INV_PAY;

    private const COL_CODE      = 'code';
    private const COL_STATUS    = 'status';

    private const FILLABLE_FIELDS = [
        self::COL_CODE,

        // vínculo principal
        BC::COL_INV_ID,

        // emissão financeira
        BC::COL_CUR_ID,
        'amount',
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        'reference',
        'description',
        'notes',
        'attachments',
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,

        // pagamento em si
        'date',
        BC::COL_IS_SCD,
        BC::COL_CAN_CHG_BK,
        BC::COL_PPS_CD,
        BC::COL_TRF_TP,
        BC::COL_PPS_DS,
        BC::COL_TXS_LST,
        BC::COL_PAY_MTD,
        BC::COL_PAY_MTD_LB,
        self::COL_STATUS,
        BC::COL_N_INTR,
        BC::COL_CURR_N_INTR,
        BC::COL_RCC_AT,
        BC::COL_RCC_BY,

        // conclusão do pagamento
        BC::COL_BACC_ID,
        BC::COL_CAT_ID,
        BC::COL_ADD_RCP,
        BC::COL_RCP_MD,

        // referências externas
        'contract',
        'loan',
        'payslip',
        BC::COL_PRD_SV_UNT,
        BC::COL_OD_ID,
        BC::COL_TAX_ID,

        // legado / compatibilidade
        'currency',
        'receipt',
        BC::COL_PAY_TP,

        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $with = [
        'invoice',
        'bankAccount',
        'category',
        'order',
        'tax',
        'createdBy',
    ];

    protected $casts = [
        'date'                  => 'date',
        'amount'                => 'decimal:2',
        BC::COL_SVC_FEE         => 'decimal:2',
        BC::COL_TXS_FEE         => 'decimal:2',
        BC::COL_IS_SCD          => 'bool',
        BC::COL_CAN_CHG_BK      => 'bool',
        BC::COL_AUTORCC         => 'bool',
        BC::COL_N_INTR          => 'int',
        BC::COL_CURR_N_INTR     => 'int',
        BC::COL_RCC_AT          => 'datetime',
        BC::COL_TXS_LST         => 'array',
        'attachments'           => 'array',
        BC::COL_TC              => 'array',
        BC::COL_RCC_RL          => 'array',
        BC::COL_RCP_MD          => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $payment): void {
            $payment->normalizeStatus();
            $payment->normalizePaymentType();
            $payment->normalizePaymentMethodLabel();
            $payment->syncCurrency();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, BC::COL_INV_ID, 'id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, BC::COL_BACC_ID, 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductServiceCategory::class, BC::COL_CAT_ID, 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, BC::COL_OD_ID, 'id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_RCC_BY, 'id');
    }

    public function getStatusEnumAttribute(): PaymentStatus
    {
        return PaymentStatus::normalize($this->{self::COL_STATUS} ?? null);
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes[self::COL_STATUS] = PaymentStatus::normalize($value)->value;
    }

    public function getPaymentTypeEnumAttribute(): PaymentType
    {
        $raw = $this->{BC::COL_PAY_TP} ?? null;

        return PaymentType::normalize(
            $raw === null ? null : (string) $raw
        );
    }

    public function setPaymentTypeAttribute($value): void
    {
        if ($value instanceof PaymentType) {
            $this->attributes[BC::COL_PAY_TP] = $value->value;
            return;
        }

        $this->attributes[BC::COL_PAY_TP] = PaymentType::normalize(
            $value === null ? null : (string) $value
        )->value;
    }

    public function getPaymentMethodEnumAttribute(): PaymentMethod
    {
        $raw = $this->{BC::COL_PAY_MTD_LB} ?? null;

        return PaymentMethod::normalize(
            $raw === null ? null : (string) $raw
        );
    }

    public function setPaymentMethodLabelAttribute($value): void
    {
        if ($value instanceof PaymentMethod) {
            $this->attributes[BC::COL_PAY_MTD_LB] = $value->value;
            return;
        }

        $this->attributes[BC::COL_PAY_MTD_LB] = PaymentMethod::normalize(
            $value === null ? null : (string) $value
        )->value;
    }

    public function isPendingLike(): bool
    {
        return in_array(
            $this->status_enum,
            [
                PaymentStatus::Pending,
                PaymentStatus::Processing,
                PaymentStatus::Authorized,
            ],
            true
        );
    }

    public function isCompletedLike(): bool
    {
        return in_array(
            $this->status_enum,
            [
                PaymentStatus::Completed,
                PaymentStatus::Refunded,
                PaymentStatus::PartiallyRefunded,
            ],
            true
        );
    }

    public function isFailedLike(): bool
    {
        return in_array(
            $this->status_enum,
            [
                PaymentStatus::Failed,
                PaymentStatus::Cancelled,
                PaymentStatus::Declined,
                PaymentStatus::Disputed,
                PaymentStatus::Expired,
            ],
            true
        );
    }

    public function isAutomatic(): bool
    {
        return $this->payment_type_enum->isAutomatic();
    }

    public function isScheduled(): bool
    {
        return $this->payment_type_enum->isScheduled();
    }

    public function isImmediate(): bool
    {
        return $this->payment_type_enum->isImmediate();
    }

    public function requiresApproval(): bool
    {
        return $this->payment_type_enum->requiresApproval();
    }

    public function canBeRecurring(): bool
    {
        return $this->payment_type_enum->canBeRecurring();
    }

    public function getTotalFeesAttribute(): float
    {
        return (float) ($this->{BC::COL_SVC_FEE} ?? 0)
            + (float) ($this->{BC::COL_TXS_FEE} ?? 0);
    }

    public function getNetAmountAttribute(): float
    {
        return (float) ($this->amount ?? 0) - $this->total_fees;
    }

    public function getIsReconciledAttribute(): bool
    {
        return $this->{BC::COL_RCC_AT} !== null;
    }

    private function normalizeStatus(): void
    {
        $this->status = $this->status_enum->value;
    }

    private function normalizePaymentType(): void
    {
        $this->payment_type = $this->payment_type_enum->value;
    }

    private function normalizePaymentMethodLabel(): void
    {
        $this->payment_method_label = $this->payment_method_enum->value;
    }

    private function syncCurrency(): void
    {
        $invoiceCurrency = null;

        if ($this->relationLoaded('invoice') && $this->invoice)
            $invoiceCurrency = $this->invoice->{BC::COL_CUR_ID} ?? null;
        elseif ($this->{BC::COL_INV_ID} ?? null) {
            $invoice = Invoice::find($this->{BC::COL_INV_ID});
            if ($invoice)
                $invoiceCurrency = $invoice->{BC::COL_CUR_ID} ?? null;
        }

        $currentCurrencyId = $this->{BC::COL_CUR_ID} ?? null;
        $legacyCurrency    = $this->currency ?? null;

        $currency = $invoiceCurrency
            ?? $currentCurrencyId
            ?? $legacyCurrency;

        if ($currency === null)
            return;
        $this->{BC::COL_CUR_ID} = $currency;
        $this->currency         = $currency;
    }
}
