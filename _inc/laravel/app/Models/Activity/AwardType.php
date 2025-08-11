<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwardType extends Model
{
    use HasFactory;
    use UsesUuids;
    protected $fillable = [
        'name',
        'created_by',
    ];
}
