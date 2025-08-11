<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class Source extends Model
{
    use HasFactory;
    use UsesUuids;

    protected $fillable = ['name', DatabaseConstants::TABLE_CREATOR];

    private const FK_CREATED_BY = DatabaseConstants::TABLE_CREATOR;

    public function user(): BelongsTo // * ADDED
    {
        return $this->belongsTo(User::class, self::FK_CREATED_BY, 'id');
    }
}
