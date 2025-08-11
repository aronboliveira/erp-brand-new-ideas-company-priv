<?php

namespace App\Models;

use App\Models\BankAccount;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class BillPayment extends Model
{
    use UsesUuids;

    private const COL_ACCOUNT_ID = 'account_id';
    private const COL_BILL_ID   = 'bill_id';

    protected $fillable = [
        self::COL_BILL_ID,
        'date',
        self::COL_ACCOUNT_ID,
        'payment_method',
        'reference',
        'description',
        'amount' // * ADDED
    ];

    public function bankAccount(): HasOne
    {
        return $this
            ->hasOne(BankAccount::class, 'id', self::COL_ACCOUNT_ID);
        // * consider using belongsTo(BankAccount::class, self::COL_ACCOUNT_ID)
    }
}
