<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class CustomFieldValue extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'record_id', 'field_id', 'value'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'field_id', 'id');
        // * consider belongsTo record owner model via record_id
    }
}
