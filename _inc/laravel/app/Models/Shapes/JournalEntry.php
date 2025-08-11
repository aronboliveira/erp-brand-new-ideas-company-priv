<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasMany};
use Illuminate\Support\Facades\Log;

class JournalEntry extends Model
{
    use UsesUuids;

    protected $fillable = [
        'date',
        'description',
        'journal_id',
        'reference',
        'created_by',
    ];

    private const COL_CREDIT = 'credit';
    private const COL_DEBIT = 'debit';

    public function accounts(): HasMany
    {
        return $this->hasMany(
            JournalItem::class,
            'journal_id', // * ADJUST if column differs
            'id'
        );
    }

    public function totalCredit(): float
    {
        try {
            return $this->accounts->sum(self::COL_CREDIT);
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . " failed summing credits: {$e->getMessage()}"
            );
            return 0.0;
        }
    }

    public function totalDebit(): float
    {
        try {
            return $this->accounts->sum(self::COL_DEBIT);
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . " failed summing debits: {$e->getMessage()}"
            );
            return 0.0;
        }
    }
}
