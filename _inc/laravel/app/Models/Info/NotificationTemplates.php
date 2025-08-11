<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class NotificationTemplates extends Model
{
    use HasFactory, UsesUuids;

    private const FILLABLE_FIELDS = [
        'name', 'type', 'slug', 'created_by'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    // * consider adding: belongsTo(User::class,'created_by','id')
}
