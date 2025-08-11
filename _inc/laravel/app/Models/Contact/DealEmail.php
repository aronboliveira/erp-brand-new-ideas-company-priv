<?php

namespace App\Models;

use App\Models\Deal;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class DealEmail extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'deal_id', 'to', 'subject', 'description'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    protected $with = ['deal']; // * eager load deal relation

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id', 'id');
        // * consider scopes for filtering by deal
    }
}
