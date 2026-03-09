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
    DefinesDates,
    HasAuditFields,
    HasPaymentColumns,
    NormalizesAddresses,
    TracksFailures,
    UsesCountryRegions,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Schema;

/**
 * @property string $id
 * @property string|null $date
 * @property float $amount
 * @property float|null $discount
 * @property string|null $account_id
 * @property string|null $to_account
 * @property string|null $chart_account_id
 * @property string|null $vendor_id
 * @property string|null $category_id
 * @property string|null $currency_id
 * @property string|null $reference
 * @property string|null $description
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $status
 */
class Payment extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use HasPaymentColumns;
    use NormalizesAddresses;
    use TracksFailures;
    use UsesCountryRegions;
    use DefinesDates;

    protected $table = DC::TABLE_PAY;

    protected $fillable = [
        'date',
        'amount',
        'discount',
        'recurring',

        BC::COL_CUR_ID,
        BC::COL_BACC_ID,
        BC::COL_ACC_TO,
        BKC::COL_COA,
        UC::COL_VD_ID,
        BC::COL_CAT_ID,

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

        BC::COL_IS_SCD,
        BC::COL_CAN_CHG_BK,
        BC::COL_PPS_CD,
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

        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,

        ...self::FAILURE_TRACKING_COLS,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $with = [];

    protected $casts = [
        'date'      => 'date',
        'amount'    => 'decimal:2',
        'discount'  => 'decimal:2',

        BC::COL_CUR_ID  => 'string',
        BC::COL_SVC_FEE => 'decimal:2',
        BC::COL_TXS_FEE => 'decimal:2',

        'status'                 => PaymentStatus::class,
        BC::COL_PAY_MTD         => 'integer',                // tinyint da migration
        BC::COL_PAY_MTD_LB      => PaymentMethod::class,     // enum(string)
        BC::COL_TRF_TP          => TransferType::class,

        BC::COL_IS_SCD          => 'boolean',
        BC::COL_CAN_CHG_BK      => 'boolean',
        BC::COL_AUTORCC         => 'boolean',

        BC::COL_SCHD_TRF_TS     => 'datetime',
        BC::COL_EXC_AT          => 'datetime',
        BC::COL_CNC_AT          => 'datetime',
        BC::COL_CMP_AT          => 'datetime',
        BC::COL_RCC_AT          => 'datetime',

        BC::COL_N_INTR          => 'integer',
        BC::COL_CURR_N_INTR     => 'integer',

        'attachments'           => 'array',
        BC::COL_TC              => 'array',
        BC::COL_TXS_LST         => 'array',
        BC::COL_RCP_MD          => 'array',
        BC::COL_RCC_RL          => 'array',

        DC::COL_FL_AT           => 'datetime',
        DC::COL_LST_RTR_AT      => 'datetime',
        DC::COL_ER_LG           => 'array',
    ];

    protected static array $paymentsErrors = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // compatibilidade: alguns ambientes antigos podem ter BC::COL_PAY_STT
        try {
            $statusColumn = Schema::hasColumn($this->getTable(), BC::COL_PAY_STT)
                ? BC::COL_PAY_STT
                : 'status';

            $this->casts[$statusColumn] = PaymentStatus::class;
        } catch (\Throwable $e) {
            // silencioso em fase de migrations/boot inicial
        }
    }

    protected static function booted(): void
    {
        parent::booted();
        // todo too heavy for mocking, use only in production  
        // static::saving(function (self $m): void {
        //     try {
        //         $ownerId = $m->getAttribute(UC::COL_VD_ID) ?? null;
        //         try {
        //             $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
        //             $isNormalizeEmailCallable && $m->setAttribute(BC::COL_BL_EMAIL, static::normalizeEmail(
        //                 $m->getAttribute(BC::COL_BL_EMAIL) ?? null,
        //                 'payment_billing',
        //                 $ownerId
        //             ));
        //         } catch (\Throwable $e) {
        //             ErrorHandler::evaluateExistenceToLogChannel(
        //                 'payment_errors',
        //                 candidate: [
        //                     'message' => 'Payment model saving hook failed to normalize billing email',
        //                     'context' => [
        //                         'id'    => $m->id ?? null,
        //                         'err'   => $e->getMessage(),
        //                         'file'  => $e->getFile(),
        //                         'line'  => $e->getLine(),
        //                     ]
        //                 ],
        //             );
        //         }
        //         try {
        //             $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
        //             $isNormalizePhoneCallable && $m->setAttribute(BC::COL_BL_TEL, static::normalizePhone(
        //                 $m->getAttribute(BC::COL_BL_TEL) ?? null,
        //                 'payment_billing',
        //                 $ownerId
        //             ));
        //         } catch (\Throwable $e) {
        //             ErrorHandler::evaluateExistenceToLogChannel(
        //                 'payment_errors',
        //                 candidate: [
        //                     'message' => 'Payment model saving hook failed to normalize billing phone',
        //                     'context' => [
        //                         'id'    => $m->id ?? null,
        //                         'err'   => $e->getMessage(),
        //                         'file'  => $e->getFile(),
        //                         'line'  => $e->getLine(),
        //                     ]
        //                 ],
        //             );
        //         }
        //         try {
        //             $isNormalizeZipCallable = is_callable([self::class, 'normalizeZip']);
        //             $isNormalizeZipCallable && $m->setAttribute(BC::COL_BL_ZIP, static::normalizeZip(
        //                 $m->getAttribute(BC::COL_BL_ZIP) ?? null,
        //                 $m->getAttribute(BC::COL_BL_CTR) ?? null,
        //                 'payment_billing',
        //                 $ownerId
        //             ));
        //         } catch (\Throwable $e) {
        //             ErrorHandler::evaluateExistenceToLogChannel(
        //                 'payment_errors',
        //                 candidate: [
        //                     'message' => 'Payment model saving hook failed to normalize billing zip',
        //                     'context' => [
        //                         'id'    => $m->id ?? null,
        //                         'err'   => $e->getMessage(),
        //                         'file'  => $e->getFile(),
        //                         'line'  => $e->getLine(),
        //                     ]
        //                 ],
        //             );
        //         }
        //         try {
        //             $isNormalizeBillingCallable = is_callable([self::class, 'normalizeBillingCountry']);
        //             $isNormalizeBillingCallable && self::normalizeBillingCountry($m);
        //             if ($m->getAttribute('discount') !== null) {
        //                 $disc = (float) $m->getAttribute('discount');
        //                 if ($disc < 0.0) $disc = 0.0;
        //                 $m->setAttribute('discount', $disc);
        //             }
        //         } catch (\Throwable $e) {
        //             ErrorHandler::evaluateExistenceToLogChannel(
        //                 'payment_errors',
        //                 candidate: [
        //                     'message' => 'Payment model saving hook failed to normalize billing country',
        //                     'context' => [
        //                         'id'    => $m->id ?? null,
        //                         'err'   => $e->getMessage(),
        //                         'file'  => $e->getFile(),
        //                         'line'  => $e->getLine(),
        //                     ]
        //                 ],
        //             );
        //         }
        //         foreach (['amount', BC::COL_SVC_FEE, BC::COL_TXS_FEE] as $field) {
        //             if ($m->getAttribute($field) !== null) {
        //                 $val = (float) $m->getAttribute($field);
        //                 if ($val < 0.0) $val = 0.0;
        //                 $m->setAttribute($field, $val);
        //             }
        //         }
        //         if ($m->getAttribute(BC::COL_CUR_ID) ?? null)
        //             $m->setAttribute(BC::COL_CUR_ID, strtoupper(trim((string) $m->getAttribute(BC::COL_CUR_ID))));
        //         foreach ([BC::COL_N_INTR, BC::COL_CURR_N_INTR] as $field) {
        //             if ($m->getAttribute($field) !== null) {
        //                 $n = (int) $m->getAttribute($field);
        //                 if ($n < 1) $n = 1;
        //                 $m->setAttribute($field, $n);
        //             }
        //         }
        //         try {
        //             if ($m->getAttribute('status') !== null)
        //                 $m->setAttribute('status', PaymentStatus::normalize($m->getAttribute('status')));
        //             if ($m->getAttribute(BC::COL_PAY_STT) ?? null)
        //                 $m->setAttribute(BC::COL_PAY_STT, PaymentStatus::normalize($m->getAttribute(BC::COL_PAY_STT)));
        //         } catch (\Throwable $e) {
        //             ErrorHandler::evaluateExistenceToLogChannel(
        //                 'payment_errors',
        //                 candidate: [
        //                     'message' => 'Payment model saving hook failed to normalize payment status type',
        //                     'context' => [
        //                         'id'    => $m->id ?? null,
        //                         'err'   => $e->getMessage(),
        //                         'file'  => $e->getFile(),
        //                         'line'  => $e->getLine(),
        //                     ]
        //                 ],
        //             );
        //         }
        //         try {
        //             $methodLabel = $m->getAttribute(BC::COL_PAY_MTD_LB) ?? null;
        //             if ($methodLabel !== null && !($methodLabel instanceof PaymentMethod))
        //                 $m->setAttribute(BC::COL_PAY_MTD_LB, PaymentMethod::normalize((string) $methodLabel));
        //         } catch (\Throwable $e) {
        //             ErrorHandler::evaluateExistenceToLogChannel(
        //                 'payment_errors',
        //                 candidate: [
        //                     'message' => 'Payment model saving hook failed to normalize payment method',
        //                     'context' => [
        //                         'id'    => $m->id ?? null,
        //                         'err'   => $e->getMessage(),
        //                         'file'  => $e->getFile(),
        //                         'line'  => $e->getLine(),
        //                     ]
        //                 ],
        //             );
        //         }
        //         try {
        //             $trf = $m->getAttribute(BC::COL_TRF_TP) ?? null;
        //             if ($trf !== null && !($trf instanceof TransferType) && \method_exists(TransferType::class, 'normalize'))
        //                 $m->setAttribute(BC::COL_TRF_TP, TransferType::normalize((string) $trf));
        //         } catch (\Throwable $e) {
        //             ErrorHandler::evaluateExistenceToLogChannel(
        //                 'payment_errors',
        //                 candidate: [
        //                     'message' => 'Payment model saving hook failed to normalize transfer type',
        //                     'context' => [
        //                         'id'    => $m->id ?? null,
        //                         'err'   => $e->getMessage(),
        //                         'file'  => $e->getFile(),
        //                         'line'  => $e->getLine(),
        //                     ]
        //                 ],
        //             );
        //         }
        //     } catch (\Throwable $e) {
        //         ErrorHandler::evaluateExistenceToLogChannel(
        //             'payment_errors',
        //             candidate: [
        //                 'message' => 'Payment model saving hook failed',
        //                 'context' => [
        //                     'id'    => $m->id ?? null,
        //                     'err'   => $e->getMessage(),
        //                     'file'  => $e->getFile(),
        //                     'line'  => $e->getLine(),
        //                 ]
        //             ],
        //         );
        //     }
        // });
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

    public function vendor(): ?BelongsTo
    {
        return Utility::getVendor($this);
    }

    // * compat com legado
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

    public function paymentMethod(): PaymentMethod
    {
        $raw = $this->getAttribute(BC::COL_PAY_MTD_LB) ?? null;
        if ($raw instanceof PaymentMethod)
            return $raw;
        return PaymentMethod::normalize($raw !== null ? (string) $raw : null);
    }

    public function methodIsCard(): bool
    {
        return $this->paymentMethod()->isCard();
    }

    public function methodIsInstant(): bool
    {
        return $this->paymentMethod()->isInstant();
    }
}
