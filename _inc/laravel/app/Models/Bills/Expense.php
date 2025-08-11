<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Expense extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_PROJECT_ID = 'project_id';
    private const COL_TASK_ID   = 'task_id';

    protected $fillable = [
        'name',
        'date',
        'description',
        'amount',
        'attachment',
        self::COL_PROJECT_ID,
        self::COL_TASK_ID,
        self::COL_CREATED_BY,
    ];

    public function project(): HasOne
    {
        return $this
            ->hasOne(Project::class, 'id', self::COL_PROJECT_ID);
        // * consider belongsTo(Project::class, self::COL_PROJECT_ID);
    }

    public function task(): HasOne
    {
        return $this
            ->hasOne(ProjectTask::class, 'id', self::COL_TASK_ID);
        // * consider belongsTo(ProjectTask::class, self::COL_TASK_ID);
    }
}
