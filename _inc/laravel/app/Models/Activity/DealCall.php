<?php

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class DealCall extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'deal_id', 'subject', 'call_type', 'duration', 'user_id', 'description', 'call_result'
    ]; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED

    public function getDealCallUser() // * existing relation
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
