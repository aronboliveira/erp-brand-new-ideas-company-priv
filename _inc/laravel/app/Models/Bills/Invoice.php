<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{
    BillStatus,
    PaymentStatus,
    TransactionType
};
use App\Traits\{
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne
};

class Invoice extends Model
{
    use HasAuditFields;
    use HasFactory;
    use NormalizesAddresses;
    use UsesUuids;

    protected $table = DC::TABLE_INVS;
    protected $fillable = [
        // identificadores
        BC::COL_INV_ID,
        BC::COL_CST_ID,
        BC::COL_BL_ID,
        BC::COL_TAX_ID,

        // emissão / valores
        BC::COL_CUR_ID,
        'amount',
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        BC::COL_PPS_CD,
        'reference',
        BC::COL_REF_N,
        'description',
        'notes',
        'attachments',
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
        'contract',
        'loan',
        BC::COL_PRD_SV_UNT,

        // datas / categoria / status
        BC::COL_SD_DT,
        BC::COL_ISS_DT,
        PJC::COL_D_DATE,
        BC::COL_CAT_ID,
        PJC::COL_STATUS,
        BC::COL_STT_LB,
        BC::COL_PAY_STT,

        // frete / desconto
        BC::COL_SHIP_DSP,
        BC::COL_DSC_APL,
        'discount',
        'taxes',

        // endereço de envio
        BC::COL_SHIP_NAME,
        BC::COL_SHIP_EMAIL,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_ZIP,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_DTL,

        // endereço de cobrança
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
    ];

    protected $with = [
        'customer',
        'category',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'discount'         => 'decimal:2',
        BC::COL_SVC_FEE    => 'decimal:2',
        BC::COL_TXS_FEE    => 'decimal:2',
        BC::COL_AUTORCC    => 'boolean',
        BC::COL_DSC_APL    => 'boolean',
        BC::COL_SD_DT      => 'date',
        BC::COL_ISS_DT     => 'date',
        PJC::COL_D_DATE    => 'date',
        'attachments'      => 'array',
        'taxes'            => 'array',
        BC::COL_TC         => 'array',
        BC::COL_RCC_RL     => 'array',
    ];

    /**
     * Map de status legados (campo numérico `status` -> rótulo).
     */
    public static array $statuses = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partially Paid',
        'Paid',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            if (empty($m->{BC::COL_STT_LB}))
                $m->{BC::COL_STT_LB} = BillStatus::Draft->value;
            if (empty($m->{BC::COL_PAY_STT}))
                $m->{BC::COL_PAY_STT} = PaymentStatus::Processing->value;
            $amount   = (float) ($m->amount ?? 0.0);
            $discount = (float) ($m->discount ?? 0.0);
            if ($discount < 0.0)
                $discount = 0.0;
            elseif ($discount > $amount)
                $discount = $amount;
            $m->amount   = $amount;
            $m->discount = $discount;
            self::normalizeBillingCountry($m);
            self::normalizeShippingCountry($m);
            if (!empty($m->{BC::COL_BL_EMAIL}))
                $m->{BC::COL_BL_EMAIL}    = self::normalizeEmail($m->{BC::COL_BL_EMAIL}, $m->{BC::COL_BL_NAME} ?? null, $m->id);
            if (!empty($m->{BC::COL_SHIP_EMAIL}))
                $m->{BC::COL_SHIP_EMAIL}  = self::normalizeEmail($m->{BC::COL_SHIP_EMAIL}, $m->{BC::COL_SHIP_NAME} ?? null, $m->id);
            if (!empty($m->{BC::COL_BL_TEL}))
                $m->{BC::COL_BL_TEL} = self::normalizePhone($m->{BC::COL_BL_TEL}, $m->{BC::COL_BL_NAME} ?? null, $m->id);
            if (!empty($m->{BC::COL_SHIP_TEL}))
                $m->{BC::COL_SHIP_TEL}   = self::normalizePhone($m->{BC::COL_SHIP_TEL}, $m->{BC::COL_SHIP_NAME} ?? null, $m->id);
            if (!empty($m->{BC::COL_BL_ZIP}) && !empty($m->{BC::COL_BL_CTR}))
                $m->{BC::COL_BL_ZIP}     = self::normalizeZip($m->{BC::COL_BL_ZIP}, $m->{BC::COL_BL_CTR}, $m->{BC::COL_BL_CTR} ?? null, $m->id);
            if (!empty($m->{BC::COL_SHIP_ZIP}) && !empty($m->{BC::COL_SHIP_CTR}))
                $m->{BC::COL_SHIP_ZIP}   = self::normalizeZip($m->{BC::COL_SHIP_ZIP}, $m->{BC::COL_SHIP_CTR}, $m->{BC::COL_SHIP_NAME} ?? null, $m->id);
            $m->attachments           = static::normalizeArrayField($m->attachments ?? null);
            $m->taxes                 = static::normalizeArrayField($m->taxes ?? null);
            $m->{BC::COL_TC}          = static::normalizeArrayField($m->{BC::COL_TC} ?? null);
            $m->{BC::COL_RCC_RL}      = static::normalizeArrayField($m->{BC::COL_RCC_RL} ?? null);
        });
    }

    public function getStatusAttribute($value): string
    {
        $index = (int) $value;

        return static::$statuses[$index] ?? static::$statuses[0];
    }

    public function setStatusAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes[PJC::COL_STATUS] = (int) $value;
            return;
        }

        $normalized = strtolower((string) $value);
        $map        = [];

        foreach (static::$statuses as $i => $label) {
            $map[strtolower($label)] = $i;
        }

        $index = $map[$normalized] ?? 0;
        $this->attributes[PJC::COL_STATUS] = $index;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            BC::COL_CST_ID,
            'id'
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ProductServiceCategory::class,
            BC::COL_CAT_ID,
            'id'
        );
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(
            Tax::class,
            BC::COL_TAX_ID,
            'id'
        );
    }

    public function taxes(): BelongsTo
    {
        return $this->tax();
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            InvoiceProduct::class,
            BC::COL_INV_ID,
            'id'
        );
    }

    public function products(): HasMany
    {
        return $this->items();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            InvoicePayment::class,
            BC::COL_INV_ID,
            'id'
        );
    }

    public function lastPayments(): HasOne
    {
        return $this->hasOne(
            InvoicePayment::class,
            BC::COL_INV_ID,
            'id'
        )->latestOfMany();
    }

    public function bankPayments(): HasMany
    {
        return $this->hasMany(
            InvoiceBankTransfer::class,
            BC::COL_INV_ID,
            'id'
        )->where('status', '!=', 'Approved');
    }

    public function creditNote(): HasMany
    {
        return $this->hasMany(
            CreditNote::class,
            'invoice',
            'id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_id')
            ->where('payment_type', TransactionType::Invoice);
    }

    public function invoiceTotalCreditNote(): float
    {
        return $this->creditNote->sum('amount');
    }

    public function getSubTotal(): float
    {
        return $this->items->sum(fn($p) => $p->price * $p->quantity);
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum(fn($p) => $p->discount);
    }

    public function getTotalTax(): float
    {
        return $this->items->sum(
            fn($p) => (Utility::totalTaxRate($p->tax) / 100)
                * ($p->price * $p->quantity - $p->discount)
        );
    }

    public function getTotal(): float
    {
        return ($this->getSubTotal() - $this->getTotalDiscount())
            + $this->getTotalTax();
    }

    public function getDue(): float
    {
        $paid = $this->payments->sum('amount');

        return ($this->getTotal() - $paid)
            - $this->invoiceTotalCreditNote();
    }

    public static function changeStatus(string|int $invoiceId, string|int $status): void
    {
        $invoice = static::find($invoiceId);

        if (!$invoice) {
            return;
        }

        $invoice->status = $status;
        $invoice->save();
    }
}
