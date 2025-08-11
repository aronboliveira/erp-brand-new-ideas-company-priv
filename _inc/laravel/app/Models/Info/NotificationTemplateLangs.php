<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class NotificationTemplateLangs extends Model
{
    use HasFactory;
    use UsesUuids;

    private const FILLABLE = [
        'parent_id', 'lang', 'content', 'variables', 'created_by'
    ];
    protected $fillable = self::FILLABLE;
}
