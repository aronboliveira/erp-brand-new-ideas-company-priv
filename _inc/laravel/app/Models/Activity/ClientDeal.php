<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class ClientDeal extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['client_id', 'deal_id']; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED
}
