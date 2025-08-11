<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class PayslipType extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['name', 'created_by']; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED
}
