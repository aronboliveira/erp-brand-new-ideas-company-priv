<?php

namespace App\Models;

use App\Models\BankAccount;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class BankTransfer extends Model
{
    use UsesUuids;

    private const COL_FROM_ACCOUNT = 'from_account';
    private const COL_TO_ACCOUNT  = 'to_account';
    private const COL_CREATED_BY  = 'created_by';

    protected $fillable = [
        self::COL_FROM_ACCOUNT,
        self::COL_TO_ACCOUNT,
        'amount',
        'date',
        'payment_method',
        'reference',
        'description',
        self::COL_CREATED_BY,
    ];

    public function fromBankAccount(): HasOne
    {
        return $this
            ->hasOne(BankAccount::class, 'id', self::COL_FROM_ACCOUNT)
            ->first();
        // * consider using belongsTo(BankAccount::class, self::COL_FROM_ACCOUNT)
    }

    public function toBankAccount(): HasOne
    {
        return $this
            ->hasOne(BankAccount::class, 'id', self::COL_TO_ACCOUNT)
            ->first();
        // * consider using belongsTo(BankAccount::class, self::COL_TO_ACCOUNT)
    }
}
