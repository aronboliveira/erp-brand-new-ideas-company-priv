<?php

namespace App\Models;

use App\Models\{
    Customer,
    InvoiceBankTransfer,
    InvoicePayment,
    InvoiceProduct,
    ProductServiceCategory,
    Tax,
    Utility
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};

class Invoice extends Model
{
    use UsesUuids;

    private const COL_CATEGORY_ID     = 'category_id';
    private const COL_CREATED_BY      = 'created_by';
    private const COL_customer_id     = 'customer_id';
    private const COL_DISCOUNT_APPLY  = 'discount_apply';
    private const COL_DUE_DATE        = 'due_date';
    private const COL_ISSUE_DATE      = 'issue_date';
    private const COL_INVOICE_ID      = 'invoice_id';
    private const COL_REF_NUMBER      = 'ref_number';
    private const COL_SEND_DATE       = 'send_date';
    private const COL_SHIPPING_DISPLAY = 'shipping_display';
    private const COL_STATUS          = 'status';
    private const COL_TAX_ID          = 'tax_id';

    protected $fillable = [
        self::COL_INVOICE_ID,
        self::COL_customer_id,
        self::COL_ISSUE_DATE,
        self::COL_DUE_DATE,
        self::COL_SEND_DATE,
        self::COL_REF_NUMBER,
        self::COL_STATUS,
        self::COL_SHIPPING_DISPLAY,
        self::COL_DISCOUNT_APPLY,
        self::COL_CATEGORY_ID,
        self::COL_TAX_ID,
        self::COL_CREATED_BY,
    ];

    public static $statuses = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partially Paid',
        'Paid',
    ];

    public function tax(): HasOne
    {
        return $this
            ->hasOne(Tax::class, 'id', self::COL_TAX_ID);
        // * consider belongsTo(Tax::class, self::COL_TAX_ID)
    }

    public function taxes(): HasOne
    {
        return $this
            ->hasOne(Tax::class, 'id', 'tax');
        // * consider belongsTo(Tax::class, 'tax')
    }

    public function items(): HasMany
    {
        return $this
            ->hasMany(InvoiceProduct::class, 'invoice_id', 'id');
    }

    public function products(): HasMany
    {
        return $this
            ->hasMany(InvoiceProduct::class);
    }

    public function payments(): HasMany
    {
        return $this
            ->hasMany(InvoicePayment::class, 'invoice_id', 'id');
    }

    public function lastPayments(): HasOne
    {
        return $this
            ->hasOne(InvoicePayment::class, 'id', 'invoice_id');
        // * consider hasMany()->orderBy('created_at','desc')->limit(1)
    }

    public function bankPayments(): HasMany
    {
        return $this
            ->hasMany(InvoiceBankTransfer::class, 'invoice_id', 'id')
            ->where('status', '!=', 'Approved');
    }

    public function customer(): HasOne
    {
        return $this
            ->hasOne(Customer::class, 'id', self::COL_customer_id);
        // * consider belongsTo(Customer::class, self::COL_customer_id)
    }

    public function category(): HasOne
    {
        return $this
            ->hasOne(ProductServiceCategory::class, 'id', self::COL_CATEGORY_ID);
        // * consider belongsTo(ProductServiceCategory::class, self::COL_CATEGORY_ID)
    }

    public function creditNote(): HasMany
    {
        return $this
            ->hasMany(\App\Models\CreditNote::class, 'invoice', 'id');
    }

    public function invoiceTotalCreditNote(): float
    {
        return $this->creditNote->sum('amount');
    }

    public function getSubTotal(): float
    {
        return $this->items->sum(fn ($p) => $p->price * $p->quantity);
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum(fn ($p) => $p->discount);
    }

    public function getTotalTax(): float
    {
        return $this->items->sum(
            fn ($p) => (Utility::totalTaxRate($p->tax) / 100)
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

    public static function changeStatus(int $invoiceId, string $status): void
    {
        $invoice        = self::find($invoiceId);
        $invoice->status = $status;
        $invoice->update();
    }
}
