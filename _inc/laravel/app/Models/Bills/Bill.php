<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};

class Bill extends Model
{
    use UsesUuids;

    private const COL_CATEGORY_ID = 'category_id';
    private const COL_CREATED_BY = 'created_by';
    private const COL_TAX_ID     = 'tax_id';
    private const COL_VENDOR_ID  = 'vendor_id';

    protected $fillable = [
        self::COL_VENDOR_ID,
        'currency',
        'bill_date',
        'due_date',
        'bill_id',
        'order_number',
        self::COL_CATEGORY_ID,
        self::COL_CREATED_BY,
    ];

    public static $statuses = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partially Paid', // * possible typo: 'Partially Paid'
        'Paid',
    ];

    public function customer(): HasOne
    {
        return $this
            ->hasOne(Customer::class, 'id', self::COL_VENDOR_ID);
        // * consider using belongsTo(Customer::class, self::COL_VENDOR_ID)
    }

    public function debitNote(): HasMany
    {
        return $this
            ->hasMany(DebitNote::class, 'bill', 'id');
    }

    public function employee(): HasOne
    {
        return $this
            ->hasOne(Employee::class, 'id', self::COL_VENDOR_ID);
        // * consider using belongsTo(Employee::class, self::COL_VENDOR_ID)
    }

    public function getDue(): float
    {
        $paid = $this->payments->sum('amount');
        return ($this->getTotal() - $paid)
            - $this->billTotalDebitNote();
    }

    public function getSubTotal(): float
    {
        $subTotal   = 0;
        foreach ($this->items as $product) {
            $subTotal += $product->price * $product->quantity;
        }
        $accountTotal = $this->accounts->sum('price');
        return $subTotal + $accountTotal;
    }

    public function getTotal(): float
    {
        return ($this->getSubTotal() - $this->getTotalDiscount())
            + $this->getTotalTax();
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum(fn ($product) => $product->discount);
    }

    public function getTotalTax(): float
    {
        $totalTax = 0;
        foreach ($this->items as $product) {
            $taxRate  = Utility::totalTaxRate($product->tax);
            $totalTax += ($taxRate / 100)
                * ($product->price * $product->quantity - $product->discount);
        }
        return $totalTax;
    }

    public function getAccountTotal(): float
    {
        return $this->accounts->sum('price');
    }

    public function items(): HasMany
    {
        return $this
            ->hasMany(BillProduct::class, 'bill_id', 'id');
    }

    public function lastPayments(): HasOne
    {
        return $this
            ->hasOne(BillPayment::class, 'id', 'bill_id');
        // * consider hasMany(...)->orderBy('created_at','desc')->limit(1)
    }

    public function payments(): HasMany
    {
        return $this
            ->hasMany(BillPayment::class, 'bill_id', 'id');
    }

    public function tax(): HasOne
    {
        return $this
            ->hasOne(Tax::class, 'id', self::COL_TAX_ID);
        // * consider using belongsTo(Tax::class, self::COL_TAX_ID)
    }

    public function vendor(): HasOne
    {
        return $this
            ->hasOne(Vendor::class, 'id', self::COL_VENDOR_ID);
        // * consider using belongsTo(Vendor::class, self::COL_VENDOR_ID)
    }

    public function vender(): HasOne
    { // * KEPT FOR COMPATIBILITY, DON'T USE IN ENDPOINTS
        return $this->vendor();
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(BillAccount::class, 'ref_id', 'id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(ProductServiceCategory::class, 'id', 'category_id');
    }

    public function billTotalDebitNote(): float
    {
        return $this->debitNote->sum('amount');
    }

    public function taxes(): HasOne
    {
        return $this->hasOne(Tax::class, 'id', 'tax');
    }
}
