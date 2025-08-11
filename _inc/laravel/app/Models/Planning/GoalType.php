<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class GoalType extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_NAME      = 'name';

    protected $fillable = [
        self::COL_NAME,
        self::COL_CREATED_BY,
    ];

    // * consider defining createdBy(): belongsTo(User::class, self::COL_CREATED_BY)
}
