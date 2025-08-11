<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class ContractNotes extends Model
{
    use UsesUuids;

    protected $table = 'contract_notes';

    private const COL_CONTRACT_ID = 'contract_id';
    private const COL_CREATED_BY = 'created_by';
    private const COL_NOTES      = 'notes';
    private const COL_USER_ID    = 'user_id';
    private const FILLABLE       = [
        self::COL_CONTRACT_ID,
        self::COL_CREATED_BY,
        self::COL_NOTES,
        self::COL_USER_ID,
    ];

    protected $fillable = self::FILLABLE;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider belongsTo(User::class,self::COL_CREATED_BY,'id')
    }
}
