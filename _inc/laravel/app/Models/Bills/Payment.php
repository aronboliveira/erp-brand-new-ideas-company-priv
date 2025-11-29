<?php

namespace App\Models;

use App\Config\Constants\{
    BanksConstants as BKC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\{
    PaymentMethod,
    PaymentStatus,
    TransferType
};
use App\Traits\{
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};

class Payment extends Model
{
    use HasAuditFields;
    use HasFactory;
    use NormalizesAddresses;
    use UsesUuids;

    protected $table = DC::TABLE_PAY;

    protected $fillable = [
        'date',
        'discount',
        'recurring',
        'status',

        BC::COL_BACC_ID,
        BC::COL_ACC_TO,
        BKC::COL_COA,
        UC::COL_VD_ID,
        BC::COL_CAT_ID,

        BC::COL_PAY_MTD,
        BC::COL_PAY_STT,
        BC::COL_STT_LB,
        BC::COL_PAY_MTD_LB,
        BC::COL_ACC_FROM,
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        BC::COL_TXS_LST,
        BC::COL_SCHD_TRF_TS,
        BC::COL_IS_SCD,
        BC::COL_CAN_CHG_BK,
        BC::COL_EXC_AT,
        BC::COL_CNC_AT,
        BC::COL_CMP_AT,
        BC::COL_CNC_RS,
        BC::COL_PPS_CD,
        BC::COL_TRF_TP,
        BC::COL_PPS_DS,
        BC::COL_PRC_AMT,
        BC::COL_INTR_AMT,
        BC::COL_N_INTR,
        BC::COL_CURR_N_INTR,
        BC::COL_SL_PRC,
        BC::COL_PC_PRC,

        BC::COL_ADD_RCP,
        BC::COL_RCP_MD,

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

    protected $guarded = [];

    protected $with = [];

    protected $casts = [
        'date'           => 'date',
        'discount'       => 'decimal:2',

        'status'         => PaymentStatus::class,
        BC::COL_PAY_MTD  => PaymentMethod::class,
        BC::COL_PAY_STT  => PaymentStatus::class,
        BC::COL_TRF_TP   => TransferType::class,

        BC::COL_IS_SCD      => 'boolean',
        BC::COL_CAN_CHG_BK  => 'boolean',

        BC::COL_SCHD_TRF_TS => 'datetime',
        BC::COL_EXC_AT      => 'datetime',
        BC::COL_CNC_AT      => 'datetime',
        BC::COL_CMP_AT      => 'datetime',

        BC::COL_TXS_LST => 'array',
        BC::COL_RCP_MD  => 'array',

        DC::COL_FL_AT      => 'datetime',
        DC::COL_LST_RTR_AT => 'datetime',
        DC::COL_ER_LG      => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            $ownerId = $m->{UC::COL_VD_ID} ?? null;

            $m->{BC::COL_BL_EMAIL} = static::normalizeEmail(
                $m->{BC::COL_BL_EMAIL} ?? null,
                'payment_billing',
                $ownerId
            );

            $m->{BC::COL_BL_TEL} = static::normalizePhone(
                $m->{BC::COL_BL_TEL} ?? null,
                'payment_billing',
                $ownerId
            );

            $m->{BC::COL_BL_ZIP} = static::normalizeZip(
                $m->{BC::COL_BL_ZIP} ?? null,
                $m->{BC::COL_BL_CTR} ?? null,
                'payment_billing',
                $ownerId
            );

            if ($m->discount !== null) {
                $disc = (float) $m->discount;
                if ($disc < 0.0) {
                    $disc = 0.0;
                }
                $m->discount = $disc;
            }

            foreach ([BC::COL_PRC_AMT, BC::COL_INTR_AMT, BC::COL_SVC_FEE] as $field) {
                if ($m->{$field} !== null) {
                    $val = (float) $m->{$field};
                    if ($val < 0.0) {
                        $val = 0.0;
                    }
                    $m->{$field} = $val;
                }
            }

            if ($m->status !== null) {
                $m->status = PaymentStatus::normalize($m->status);
            }

            if ($m->{BC::COL_PAY_STT} ?? null) {
                $m->{BC::COL_PAY_STT} = PaymentStatus::normalize($m->{BC::COL_PAY_STT});
            }

            if ($m->{BC::COL_PAY_MTD} ?? null) {
                $m->{BC::COL_PAY_MTD} = PaymentMethod::normalize($m->{BC::COL_PAY_MTD});
            }

            if ($m->{BC::COL_TRF_TP} ?? null) {
                $m->{BC::COL_TRF_TP} = TransferType::normalize($m->{BC::COL_TRF_TP});
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ProductServiceCategory::class,
            BC::COL_CAT_ID,
            'id'
        );
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(
            Vendor::class,
            UC::COL_VD_ID,
            'id'
        );
    }

    // Mantido por compatibilidade com código legado. Não usar em código novo.
    public function vender(): BelongsTo
    {
        return $this->vendor();
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class,
            BC::COL_BACC_ID,
            'id'
        );
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class,
            BC::COL_ACC_TO,
            'id'
        );
    }

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            BKC::COL_COA,
            'id'
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::Completed;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function methodIsCard(): bool
    {
        $m = $this->{BC::COL_PAY_MTD};
        return $m instanceof PaymentMethod && $m->isCard();
    }

    public function methodIsInstant(): bool
    {
        $m = $this->{BC::COL_PAY_MTD};
        return $m instanceof PaymentMethod && $m->isInstant();
    }
}
