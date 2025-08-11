<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class TaskComment extends Model
{
    use UsesUuids;

    private const COL_COMMENT   = 'comment';
    private const COL_CREATED_BY = 'created_by';
    private const COL_TASK_ID   = 'task_id';
    private const COL_USER_ID   = 'user_id';
    private const COL_USER_TYPE = 'user_type';

    protected $fillable = [
        self::COL_COMMENT,
        self::COL_TASK_ID,
        self::COL_USER_ID,
        self::COL_USER_TYPE,
        self::COL_CREATED_BY,
    ];

    public function user(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider using belongsTo(User::class, self::COL_CREATED_BY)
    }
}
