<?php

namespace App\Models;

use App\Models\BankAccount;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class PurchasePayment extends Model
{
    use UsesUuids;

    private const COL_ACCOUNT_ID = 'account_id';
    private const COL_PURCHASE_ID = 'purchase_id';

    protected $fillable = [
        self::COL_PURCHASE_ID,
        'date',
        self::COL_ACCOUNT_ID,
        'amount',          // * added to match migration
        'payment_method',
        'reference',
        'description',
        'add_receipt',     // * added to match migration
    ];

    public function bankAccount(): HasOne
    {
        return $this
            ->hasOne(BankAccount::class, 'id', self::COL_ACCOUNT_ID);
        // * consider using belongsTo(BankAccount::class, self::COL_ACCOUNT_ID)
    }
}
