<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['name', 'rate', 'created_by']; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED
}
