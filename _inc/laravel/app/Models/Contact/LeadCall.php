<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class LeadCall extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'lead_id', 'subject', 'call_type', 'duration',
        'user_id', 'description', 'call_result'
    ]; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED

    public function getLeadCallUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
        // * consider belongsTo(User::class,'user_id');
    }
}
