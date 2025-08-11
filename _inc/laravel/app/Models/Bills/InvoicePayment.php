<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class InvoicePayment extends Model
{
    use UsesUuids;

    private const COL_ACCOUNT_ID = 'account_id';
    private const COL_INVOICE_ID = 'invoice_id';

    protected $fillable = [
        self::COL_INVOICE_ID,
        'date',
        'amount',
        self::COL_ACCOUNT_ID,
        'payment_method',
        'order_id',
        'currency',
        'txn_id',
        'payment_type',
        'receipt',
        'add_receipt',
        'reference',
        'description',
    ];

    public function bankAccount(): HasOne
    {
        return $this
            ->hasOne(BankAccount::class, 'id', self::COL_ACCOUNT_ID);
        // * consider belongsTo(BankAccount::class, self::COL_ACCOUNT_ID)
    }
}
