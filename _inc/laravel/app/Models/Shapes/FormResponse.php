<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class FormResponse extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'form_id', 'response'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilder::class, 'form_id', 'id');
        // * consider eager loading form via $with
    }
}
