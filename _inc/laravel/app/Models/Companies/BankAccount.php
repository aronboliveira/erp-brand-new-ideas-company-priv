<?php

namespace App\Models;

use App\Config\Constants\{BanksConstants, DatabaseConstants};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class BankAccount extends Model
{
    use UsesUuids;

    private const COL_ACCOUNT_NUMBER  = BanksConstants::COL_ACC_N;
    private const COL_BANK_ADDRESS    = BanksConstants::COL_ADR;
    private const COL_BANK_NAME       = BanksConstants::COL_NM;
    private const COL_CHART_ACCOUNT_ID = BanksConstants::COL_COA;
    private const COL_CONTACT_NUMBER  = BanksConstants::COL_CT;
    private const COL_HOLDER_NAME     = BanksConstants::COL_HNM;
    private const COL_OPENING_BALANCE = BanksConstants::COL_OB;
    private const COL_CREATED_BY      = DatabaseConstants::TABLE_CREATOR;

    protected $fillable = [
        self::COL_HOLDER_NAME,
        self::COL_BANK_NAME,
        self::COL_ACCOUNT_NUMBER,
        self::COL_CHART_ACCOUNT_ID,
        self::COL_OPENING_BALANCE,
        self::COL_CONTACT_NUMBER,
        self::COL_BANK_ADDRESS,
        self::COL_CREATED_BY,
    ];

    public function chartAccount(): HasOne
    {
        return $this
            ->hasOne(ChartOfAccount::class, 'id', self::COL_CHART_ACCOUNT_ID);
        // * consider using belongsTo(ChartOfAccount::class, self::COL_CHART_ACCOUNT_ID)
    }
}
