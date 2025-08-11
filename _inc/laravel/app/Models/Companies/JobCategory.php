<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    use UsesUuids;

    private const FILLABLE = ['title', 'created_by'];
    protected $fillable  = self::FILLABLE;
}
