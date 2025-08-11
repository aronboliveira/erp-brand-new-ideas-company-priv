<?php

namespace App\Models;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Timesheet extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_DATE      = 'date';
    private const COL_DESCRIPTION = 'description';
    private const COL_PROJECT_ID = 'project_id';
    private const COL_TASK_ID   = 'task_id';
    private const COL_TIME      = 'time';

    private const FILLABLE = [
        self::COL_PROJECT_ID,
        self::COL_TASK_ID,
        self::COL_DATE,
        self::COL_TIME,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', self::COL_PROJECT_ID);
        // * consider belongsTo(Project::class,self::COL_PROJECT_ID,'id')
    }

    public function task(): HasOne
    {
        return $this->hasOne(ProjectTask::class, 'id', self::COL_TASK_ID);
        // * consider belongsTo(ProjectTask::class,self::COL_TASK_ID,'id')
    }
}
