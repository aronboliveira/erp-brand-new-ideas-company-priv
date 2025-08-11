<?php

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class ChMessage extends Model
{
    use UsesUuids;

    private const COL_FROM_ID = 'from_id';
    private const COL_TO_ID  = 'to_id';
    private const COL_MESSAGE = 'message';
    private const COL_SEEN   = 'seen';
    private const FILLABLE   = [
        self::COL_FROM_ID,
        self::COL_TO_ID,
        self::COL_MESSAGE,
        self::COL_SEEN,
    ];

    protected $fillable = self::FILLABLE;

    public function from(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_FROM_ID);
        // * consider belongsTo(User::class,self::COL_FROM_ID,'id')
    }

    public function to(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_TO_ID);
        // * consider belongsTo(User::class,self::COL_TO_ID,'id')
    }
}
