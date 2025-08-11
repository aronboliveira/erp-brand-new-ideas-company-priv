<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class Template extends Model
{
    use HasFactory, UsesUuids;

    protected $table = DatabaseConstants::TABLE_TEMPLATES;
    protected $fillable = [
        'template_name',
        'prompt',
        'module', // ! CHANGED
        'field_json',
        'is_tone',
    ];
}
