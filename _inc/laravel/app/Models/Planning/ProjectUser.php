<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class ProjectUser extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'project_id',
        'user_id',
        'invited_by',
    ];

    public function projectUsers(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
        // * consider using belongsTo(User::class, 'user_id');
    }
}
