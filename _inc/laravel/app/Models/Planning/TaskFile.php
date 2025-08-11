<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class TaskFile extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'file',
        'name',
        'extension',
        'file_size',
        'task_id',
        'user_type',
        'created_by',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
        // * consider using belongsTo(User::class, 'created_by');
    }
}
