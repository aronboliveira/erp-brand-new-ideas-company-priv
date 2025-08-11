<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class ClientPermission extends Model
{
    use UsesUuids;

    private const COL_CLIENT_ID   = 'client_id';
    private const COL_DEAL_ID     = 'deal_id';
    private const COL_PERMISSIONS = 'permissions';

    protected $fillable = [
        self::COL_CLIENT_ID,
        self::COL_DEAL_ID,
        self::COL_PERMISSIONS,
    ];

    // * consider defining relations:
    // * client(): belongsTo(Client::class, self::COL_CLIENT_ID)
    // * deal():   belongsTo(Deal::class,   self::COL_DEAL_ID)
}
