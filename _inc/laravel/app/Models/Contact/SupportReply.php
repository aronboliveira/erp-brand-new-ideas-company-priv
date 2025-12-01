<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    MessagesConstants,
    SupportsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class SupportReply extends Model
{
    use UsesUuids;

    private const FILLABLE = [
        SupportsConstants::COL_SPT_ID,
        SupportsConstants::COL_USR,
        ActivitiesConstants::COL_DESC,
        DatabaseConstants::COL_TABLE_CREATOR,
        MessagesConstants::COL_IS_RD
    ];
    protected $fillable  = self::FILLABLE;

    public function users(): HasOne // TODO SHOULD BE CHANGED
    {
        return $this->hasOne(User::class, 'id', SupportsConstants::COL_USR);
        // * consider belongsTo(User::class,SupportsConstants::COL_USR,'id')
    }
}
