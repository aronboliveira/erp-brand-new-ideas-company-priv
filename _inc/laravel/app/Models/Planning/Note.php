<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants, DatabaseConstants};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Builder, Model, Relations\MorphTo};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use UsesUuids;

    private const COL_MODULE_ID      = ActivitiesConstants::COL_MI;
    private const COL_MODULE_TYPE    = ActivitiesConstants::COL_MT;
    private const COL_NOTE           = ActivitiesConstants::COL_NT;
    private const COL_NOTE_CREATED_BY = self::COL_NOTE . '_' . DatabaseConstants::COL_TABLE_CREATOR;

    protected $fillable = [
        'id',
        self::COL_NOTE,
        self::COL_MODULE_ID,
        self::COL_MODULE_TYPE,
        self::COL_NOTE_CREATED_BY,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(
            DatabaseConstants::ORDER_ID,
            function (Builder $builder) {
                $builder->orderBy('id', 'desc');
            }
        );
    }

    public function creator(): BelongsTo
    {
        return $this
            ->belongsTo(User::class, self::COL_NOTE_CREATED_BY);
        // * consider defining a User relation alias or withDefault()
    }

    public function module(): MorphTo
    {
        return $this->morphTo(
            ActivitiesConstants::COL_MD,
            self::COL_MODULE_TYPE,
            self::COL_MODULE_ID
        );
    }
}
