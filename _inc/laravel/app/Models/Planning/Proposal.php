<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};

class Proposal extends Model
{
    use UsesUuids;

    private const COL_PROPOSAL_ID           = 'proposal_id';
    private const COL_CUSTOMER_ID           = 'customer_id';
    private const COL_ISSUE_DATE            = 'issue_date';
    private const COL_SEND_DATE             = 'send_date';
    private const COL_CATEGORY_ID           = 'category_id';
    private const COL_STATUS                = 'status';
    private const COL_DISCOUNT_APPLY        = 'discount_apply';
    private const COL_IS_CONVERT            = 'is_convert';
    private const COL_CONVERTED_INVOICE_ID  = 'converted_invoice_id';
    private const COL_CREATED_BY            = 'created_by';
    private const COL_TAX_ID                = 'tax_id';

    protected $fillable = [
        self::COL_PROPOSAL_ID,
        self::COL_CUSTOMER_ID,
        self::COL_ISSUE_DATE,
        self::COL_SEND_DATE,
        self::COL_STATUS,
        self::COL_CATEGORY_ID,
        self::COL_DISCOUNT_APPLY,
        self::COL_IS_CONVERT,
        self::COL_CONVERTED_INVOICE_ID,
        self::COL_CREATED_BY,
        self::COL_TAX_ID,
    ];

    public static $statuses = [
        'Draft',
        'Open',
        'Accepted',
        'Declined',
        'Close',
    ];

    public function tax(): HasOne
    {
        return $this
            ->hasOne(Tax::class, 'id', self::COL_TAX_ID);
        // * consider belongsTo(Tax::class, self::COL_TAX_ID)
    }

    public function taxes(): HasOne // * KEPT FOR COMPATIBILITY
    {
        return $this
            ->hasOne(Tax::class, 'id', self::COL_TAX_ID);
        // * consider belongsTo(Tax::class, self::COL_TAX_ID)
    }

    public function items(): HasMany
    {
        return $this
            ->hasMany(ProposalProduct::class, 'proposal_id', 'id');
    }

    public function customer(): HasOne
    {
        return $this
            ->hasOne(\App\Models\Customer::class, 'id', self::COL_CUSTOMER_ID);
        // * consider belongsTo(Customer::class, self::COL_CUSTOMER_ID)
    }

    public function category(): HasOne
    {
        return $this
            ->hasOne(ProductServiceCategory::class, 'id', self::COL_CATEGORY_ID);
        // * consider belongsTo(ProductServiceCategory::class, self::COL_CATEGORY_ID)
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
        $due = 0;
        foreach ($this->payments as $payment) // * payments() relation needed
            $due += $payment->amount;
        return ($this->getTotal() - $due)
            - $this->invoiceTotalCreditNote(); // * invoiceTotalCreditNote() needed
    }

    public static function changeStatus(int $proposalId, string $status): void
    {
        $proposal      = self::find($proposalId);
        $proposal->status = $status;
        $proposal->update();
    }
}
