<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class ContractType extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['name', 'created_by'];
    protected $fillable = self::FILLABLE_FIELDS;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
