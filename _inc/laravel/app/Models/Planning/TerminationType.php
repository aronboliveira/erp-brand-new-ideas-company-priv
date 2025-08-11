<?php

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class TerminationType extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_NAME      = 'name';

    protected $fillable = [
        self::COL_NAME,
        self::COL_CREATED_BY,
    ];

    public function createdBy(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider using belongsTo(User::class, self::COL_CREATED_BY)
    }
}
