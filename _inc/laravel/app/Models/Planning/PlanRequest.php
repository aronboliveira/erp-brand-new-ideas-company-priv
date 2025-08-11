<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class PlanRequest extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'user_id',
        'plan_id',
        'duration',
    ];

    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', 'plan_id');
        // * consider using belongsTo(Plan::class, 'plan_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
        // * consider using belongsTo(User::class, 'user_id');
    }
}
