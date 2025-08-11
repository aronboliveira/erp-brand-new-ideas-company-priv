<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Holiday extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_DATE       = 'date';
    private const COL_END_DATE   = 'end_date';         // ! CHANGED
    private const COL_OCCASION   = 'occasion';
    private const FILLABLE       = [
        self::COL_DATE,
        self::COL_END_DATE,                             // ! CHANGED
        self::COL_OCCASION,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider belongsTo(User::class,self::COL_CREATED_BY,'id')
    }
}
