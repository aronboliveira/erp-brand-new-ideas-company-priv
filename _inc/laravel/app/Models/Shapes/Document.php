<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Document extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_IS_REQUIRED = 'is_required';
    private const COL_NAME       = 'name';

    private const FILLABLE = [
        self::COL_NAME,
        self::COL_IS_REQUIRED,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider belongsTo(User::class, self::COL_CREATED_BY, 'id')
    }
}
