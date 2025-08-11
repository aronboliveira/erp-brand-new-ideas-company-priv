<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class IpRestrict extends Model
{
    use UsesUuids;

    private const FILLABLE = ['ip', 'created_by'];
    protected $fillable  = self::FILLABLE;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
        // * consider belongsTo(User::class,'created_by','id')
    }
}
