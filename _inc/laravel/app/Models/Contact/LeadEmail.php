<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class LeadEmail extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['lead_id', 'to', 'subject', 'description'];
    protected $fillable = self::FILLABLE_FIELDS;

    protected $with = ['lead'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'id');
        // * consider adding scopes for filtering by lead_id
    }
}
