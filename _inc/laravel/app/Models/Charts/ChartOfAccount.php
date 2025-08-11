<?php

namespace App\Models;

use App\Config\Constants\{
    ChartsConstants,
    DatabaseConstants,
    UsersConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use Illuminate\Support\Facades\DB;

class ChartOfAccount extends Model
{
    use UsesUuids;

    private const COL_NAME       = ChartsConstants::COL_NM;
    private const COL_CODE       = ChartsConstants::COL_CD;
    private const COL_DESCRIPTION = ChartsConstants::COL_DESC;
    private const COL_IS_ENABLED = ChartsConstants::COL_ENB;
    private const COL_SUB_TYPE   = ChartsConstants::COL_SUBTP;
    private const COL_TYPE       = ChartsConstants::COL_TP;
    private const COL_CREATED_BY = DatabaseConstants::TABLE_CREATOR;

    protected $fillable = [
        self::COL_NAME,
        self::COL_CODE,
        self::COL_TYPE,
        self::COL_SUB_TYPE,
        self::COL_IS_ENABLED,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
        UsersConstants::COL_USER_ID
    ];

    protected static function booted()
    {
        static::creating(function (ChartOfAccount $coa) {
            if (empty($coa->user_id) && !empty($coa->{DatabaseConstants::TABLE_CREATOR}))
                $coa->user_id = $coa->{DatabaseConstants::TABLE_CREATOR};
        });
    }

    public function types(): HasOne
    {
        return $this
            ->hasOne(ChartOfAccountType::class, 'id', self::COL_TYPE);
        // * consider using belongsTo(ChartOfAccountType::class, self::COL_TYPE)
    }

    public function accounts(): HasOne
    {
        return $this
            ->hasOne(JournalItem::class, 'account', 'id');
        // * consider using belongsTo(JournalItem::class, 'account')
    }

    public function balance(): array
    {
        $item = JournalItem::select(
            DB::raw('sum(credit) as totalCredit'),
            DB::raw('sum(debit) as totalDebit'),
            DB::raw('sum(credit) - sum(debit) as netAmount')
        )
            ->where('account', $this->id)
            ->first();
        return [
            'totalCredit' => $item->totalCredit,
            'totalDebit'  => $item->totalDebit,
            'netAmount'   => $item->netAmount,
        ];
    }

    public function subType(): HasOne
    {
        return $this
            ->hasOne(ChartOfAccountSubType::class, 'id', self::COL_SUB_TYPE);
        // * consider using belongsTo(ChartOfAccountSubType::class, self::COL_SUB_TYPE)
    }
}
