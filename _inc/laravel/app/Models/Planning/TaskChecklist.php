<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class TaskChecklist extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_NAME      = 'name';
    private const COL_STATUS    = 'status';
    private const COL_TASK_ID   = 'task_id';
    private const COL_USER_TYPE = 'user_type';

    private const FILLABLE = [
        self::COL_NAME,
        self::COL_TASK_ID,
        self::COL_USER_TYPE,
        self::COL_CREATED_BY,
        self::COL_STATUS,
    ];

    protected $fillable = self::FILLABLE;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider belongsTo(User::class, self::COL_CREATED_BY, 'id')
    }
}
