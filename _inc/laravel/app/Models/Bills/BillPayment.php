<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{PaymentMethod, PaymentStatus, PaymentType};
use App\Traits\{DefinesDates, ExtendsPaymentTable, HasAuditFields, HasPaymentColumns, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Str;

class BillPayment extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use HasPaymentColumns;
    use DefinesDates;
    use ExtendsPaymentTable;

    protected $table = DC::TABLE_BL_PAY;

    protected $fillable = [
        'code',
        BC::COL_BL_ID,
        BC::COL_OD_ID,
        'amount',
        'discount',
        BC::COL_CUR_ID,
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        BC::COL_PPS_CD,
        BC::COL_TRF_TP,
        BC::COL_TXS_LST,
        BC::COL_PAY_MTD,
        BC::COL_PAY_MTD_LB,
        'status',
        BC::COL_N_INTR,
        BC::COL_CURR_N_INTR,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
        BC::COL_RCC_AT,
        BC::COL_RCC_BY,
        'invoice',
        'payslip',
        BC::COL_PRD_SV_UNT,
        'date',
        BC::COL_ADD_RCP,
        BC::COL_RCP_MD,
        BC::COL_BACC_ID,
        BC::COL_CAT_ID,
        'currency',
        BC::COL_PAY_TP,
        'receipt',
        'reference',
        'description',
        'notes',
        'attachments',
        BC::COL_TC,
        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $with = [
        'bankAccount',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'discount'            => 'float',
        BC::COL_SVC_FEE       => 'decimal:2',
        BC::COL_TXS_FEE       => 'decimal:2',
        BC::COL_IS_SCD        => 'boolean',
        BC::COL_CAN_CHG_BK    => 'boolean',
        BC::COL_TXS_LST       => 'array',
        'attachments'         => 'array',
        BC::COL_TC            => 'array',
        BC::COL_RCC_RL        => 'array',
        BC::COL_RCP_MD        => 'array',
        'date'                => 'date',
        BC::COL_RCC_AT        => 'datetime',
        'status'              => PaymentStatus::class,
        BC::COL_PAY_TP        => PaymentType::class,
        BC::COL_PAY_MTD_LB    => PaymentMethod::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code))
                $model->code = Str::uuid()->toString();
            if ($model->discount === null)
                $model->discount = 0.0;
        });

        static::saving(function (self $model): void {
            $status = PaymentStatus::normalize($model->status ?? null);
            $model->status = $status->value;

            $type = PaymentType::normalize($model->{BC::COL_PAY_TP} ?? null);
            $model->{BC::COL_PAY_TP} = $type->value;

            if ($model->discount === null || $model->discount < 0)
                $model->discount = 0.0;
            $amount = (float) ($model->amount ?? 0.0);
            if ($model->discount > $amount)
                $model->discount = $amount;
        });
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, BC::COL_BL_ID);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, BC::COL_OD_ID);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class, 'payslip');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, BC::COL_BACC_ID);
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


    public function getGrossAmount(): float
    {
        return (float) ($this->amount ?? 0.0);
    }

    public function getNetAmount(): float
    {
        $gross    = (float) ($this->amount ?? 0.0);
        $discount = (float) ($this->discount ?? 0.0);
        $fees     = (float) ($this->{BC::COL_SVC_FEE} ?? 0.0)
            + (float) ($this->{BC::COL_TXS_FEE} ?? 0.0);

        $net = $gross - $discount + $fees;

        return $net > 0 ? $net : 0.0;
    }

    public function hasDiscount(): bool
    {
        return (float) ($this->discount ?? 0.0) > 0.0;
    }

    public function isCompleted(): bool
    {
        return PaymentStatus::normalize($this->status ?? null) === PaymentStatus::Completed;
    }

    public function isPending(): bool
    {
        $status = PaymentStatus::normalize($this->status ?? null);

        return in_array(
            $status,
            [PaymentStatus::Pending, PaymentStatus::Processing, PaymentStatus::Authorized],
            true
        );
    }

    public function isAutomatic(): bool
    {
        return PaymentType::normalize($this->{BC::COL_PAY_TP} ?? null)->isAutomatic();
    }
}
