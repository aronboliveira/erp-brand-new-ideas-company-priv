<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class TimeTracker extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        ProjectsConstants::COL_PJ_ID,
        ActivitiesConstants::COL_TSK_ID,
        ActivitiesConstants::COL_IA,
        'tag_id',
        ProjectsConstants::COL_NM,
        'is_billable',
        ActivitiesConstants::COL_ST_TIME,
        ActivitiesConstants::COL_E_TIME,
        ActivitiesConstants::COL_TTL_TIME,
        DatabaseConstants::COL_TABLE_CREATOR,
    ];

    protected $appends = [
        ActivitiesConstants::COL_PJ_NM,
        'project_task',
        'total',
    ];

    public function getProjectNameAttribute(string $value): string
    {
        $project = Project::select(ActivitiesConstants::COL_PJ_NM)
            ->where('id', $this->project_id)
            ->first();
        return $project->project_name ?? '';
    }

    public function getProjectTaskAttribute(string $value): string
    {
        $task = ProjectTask::select(ProjectsConstants::COL_NM)
            ->where('id', $this->task_id)
            ->first();
        return $task->name ?? '';
    }

    public function getTotalAttribute(string $value): string
    {
        $total = Utility::secondToTime($this->total_time);
        return $total ?: '00:00:00';
    }
}
