<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class DealFile extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['deal_id', 'file_name', 'file_path'];
    protected $fillable = self::FILLABLE_FIELDS;

    protected $with = ['deal'];

    public function deal(): BelongsTo  // * ADDED
    {
        return $this->belongsTo(Deal::class, 'deal_id', 'id');
    }
}