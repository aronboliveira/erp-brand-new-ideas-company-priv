<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Enums\{
    PaymentMethod,
    PaymentStatus,
    TransferType
};
use App\Traits\{
    DefinesDates,
    HasAuditFields,
    HasPaymentColumns,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model
};
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property string|\Illuminate\Support\Carbon|null $reconciled_at
 * @property string|null $status
 */
class Revenue extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use HasPaymentColumns;
    use DefinesDates;

    protected $table = DC::TABLE_RVN;

    private const COL_COMPANY = 'company';
    private const COL_USER    = 'user';

    protected $fillable = [
        'date',
        'amount',
        BC::COL_BACC_ID,
        BC::COL_CST_ID,
        BC::COL_CAT_ID,
        self::COL_COMPANY,
        self::COL_USER,
        BC::COL_CUR_ID,
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        BC::COL_PPS_CD,
        'reference',
        'description',
        'notes',
        'attachments',
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
        BC::COL_IS_SCD,
        BC::COL_CAN_CHG_BK,
        BC::COL_TRF_TP,
        BC::COL_PPS_DS,
        BC::COL_TXS_LST,
        BC::COL_PAY_MTD,
        BC::COL_PAY_MTD_LB,
        'status',
        BC::COL_N_INTR,
        BC::COL_CURR_N_INTR,
        BC::COL_RCC_AT,
        BC::COL_RCC_BY,
        'invoice',
        'payslip',
        'contract',
        'loan',
        BC::COL_PRD_SV_UNT,
        BC::COL_ADD_RCP,
        BC::COL_RCP_MD,
    ];

    protected $guarded = ['id'];

    protected $with = [
        'bankAccount',
        'invoice',
        'payslip',
        'createdBy',
    ];

    protected $casts = [
        'date'                => 'date',
        'amount'              => 'decimal:2',
        BC::COL_SVC_FEE       => 'decimal:2',
        BC::COL_TXS_FEE       => 'decimal:2',
        BC::COL_TXS_LST       => 'array',
        'attachments'         => 'array',
        BC::COL_TC            => 'array',
        BC::COL_AUTORCC       => 'bool',
        BC::COL_IS_SCD        => 'bool',
        BC::COL_CAN_CHG_BK    => 'bool',
        BC::COL_RCP_MD        => 'array',
        BC::COL_RCC_RL        => 'array',
        BC::COL_PAY_MTD       => PaymentMethod::class,
        'status'              => PaymentStatus::class,
        BC::COL_TRF_TP        => TransferType::class,
    ];

    public function customer(): ?BelongsTo
    {
        return Utility::getCustomer($this);
    }

    public function productServiceCategory(): ?BelongsTo
    {
        return $this->belongsTo(ProductServiceCategory::class, BC::COL_CAT_ID, 'id');
    }

    public function productCategory(): ?BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, BC::COL_CAT_ID, 'id');
    }

    public function category(): ?BelongsTo
    {
        return Utility::getCategory($this);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class,
            BC::COL_BACC_ID,
            'id'
        );
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice', 'id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class, 'payslip', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract', 'id');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_COMPANY, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_USER, 'id');
    }

    public function setStatusAttribute(mixed $value): void
    {
        $this->attributes['status'] = PaymentStatus::normalize($value)->value;
    }

    public function getStatusEnumAttribute(): PaymentStatus
    {
        return PaymentStatus::normalize($this->attributes['status'] ?? null);
    }

    public function setPaymentMethodAttribute(mixed $value): void
    {
        $this->attributes[BC::COL_PAY_MTD] = PaymentMethod::normalize((string) $value)->value;
    }

    public function getPaymentMethodEnumAttribute(): ?PaymentMethod
    {
        $raw = $this->attributes[BC::COL_PAY_MTD] ?? null;
        return $raw !== null ? PaymentMethod::normalize((string) $raw) : null;
    }

    public function setTransferTypeAttribute(mixed $value): void
    {
        $this->attributes[BC::COL_TRF_TP] = TransferType::normalize((string) $value)->value;
    }

    public function getTransferTypeEnumAttribute(): ?TransferType
    {
        $raw = $this->attributes[BC::COL_TRF_TP] ?? null;

        return $raw !== null
            ? TransferType::normalize((string) $raw)
            : null;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function isProcessing(): bool
    {
        return $this->status === PaymentStatus::Processing;
    }

    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::Failed;
    }

    public function isRefunded(): bool
    {
        return in_array(
            $this->status,
            [PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded],
            true
        );
    }

    public function isFinalized(): bool
    {
        return in_array(
            $this->status,
            [
                PaymentStatus::Completed,
                PaymentStatus::Failed,
                PaymentStatus::Cancelled,
                PaymentStatus::Refunded,
                PaymentStatus::PartiallyRefunded,
                PaymentStatus::Expired,
                PaymentStatus::Declined,
                PaymentStatus::Disputed,
            ],
            true
        );
    }

    public function isReconciled(): bool
    {
        return (bool) $this->{BC::COL_RCC_AT};
    }

    public function getNetAmountAttribute(): float
    {
        $amount   = (float) ($this->amount ?? 0);
        $svcFee   = (float) ($this->{BC::COL_SVC_FEE} ?? 0);
        $taxesFee = (float) ($this->{BC::COL_TXS_FEE} ?? 0);

        return $amount - $svcFee - $taxesFee;
    }
}
