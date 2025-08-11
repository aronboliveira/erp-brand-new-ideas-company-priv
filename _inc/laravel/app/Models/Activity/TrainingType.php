<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class TrainingType extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = ['name', 'created_by'];

    private const FK_CREATED_BY = 'created_by';

    public function createdBy(): BelongsTo // * ADDED
    {
        return $this->belongsTo(User::class, self::FK_CREATED_BY, 'id');
    }
}
