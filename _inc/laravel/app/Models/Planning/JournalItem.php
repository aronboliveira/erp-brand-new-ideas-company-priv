<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class JournalItem extends Model
{
    use UsesUuids;

    private const COL_ACCOUNT    = 'account';
    private const COL_CREDIT     = 'credit';
    private const COL_DEBIT      = 'debit';
    private const COL_DESCRIPTION = 'description';
    private const COL_JOURNAL    = 'journal';

    protected $fillable = [
        self::COL_JOURNAL,
        self::COL_ACCOUNT,
        self::COL_DESCRIPTION, // * added to match migration
        self::COL_DEBIT,
        self::COL_CREDIT,
    ];

    public function accounts(): HasOne
    {
        return $this
            ->hasOne(ChartOfAccount::class, 'id', self::COL_ACCOUNT);
        // * consider using belongsTo(ChartOfAccount::class, self::COL_ACCOUNT)
    }
}
