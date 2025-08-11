<?php

namespace App\Models;

use App\Models\BankAccount;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class PosPayment extends Model
{
    use UsesUuids;

    private const COL_POS_ID         = 'pos_id';
    private const COL_DATE           = 'date';
    private const COL_AMOUNT         = 'amount';
    private const COL_DISCOUNT       = 'discount';
    private const COL_DISCOUNT_AMOUNT = 'discount_amount';
    private const COL_ACCOUNT_ID     = 'account_id';      // ! CHANGED
    private const COL_CREATED_BY     = 'created_by';
    private const FILLABLE           = [
        self::COL_POS_ID,
        self::COL_DATE,
        self::COL_AMOUNT,
        self::COL_DISCOUNT,
        self::COL_DISCOUNT_AMOUNT,
        self::COL_ACCOUNT_ID,                         // ! CHANGED
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'id', self::COL_ACCOUNT_ID);
        // * consider belongsTo(BankAccount::class, self::COL_ACCOUNT_ID)
    }
}
