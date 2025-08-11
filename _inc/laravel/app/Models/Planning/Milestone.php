<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    ProjectsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasMany};

class Milestone extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        ProjectsConstants::COL_PJ_ID, ActivitiesConstants::COL_TT,
        ActivitiesConstants::COL_TSK_STT, ActivitiesConstants::COL_DESC
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, ProjectsConstants::COL_ML_ID, 'id');
        // * consider adding: belongsTo(Project::class,ProjectsConstants::COL_PJ_ID,'id')
    }
}
