<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class DeductionOption extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';

    protected $fillable = [
        'name',
        self::COL_CREATED_BY,
    ];
}
