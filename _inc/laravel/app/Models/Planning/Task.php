<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};

class Task extends Model
{
    use UsesUuids;

    private const COL_AGENT_OR_MANAGER = ActivitiesConstants::COL_A_O_M;
    private const COL_DATE            = ActivitiesConstants::COL_TSK_DATE;
    private const COL_TIME            = ActivitiesConstants::COL_TSK_TIME;
    private const COL_DESCRIPTION     = ActivitiesConstants::COL_DESC;
    private const COL_MODULE_ID       = ActivitiesConstants::COL_MI;
    private const COL_MODULE_TYPE     = ActivitiesConstants::COL_MT;
    private const COL_TITLE           = ActivitiesConstants::COL_TT;
    private const COL_CREATED_BY      = DatabaseConstants::TABLE_CREATOR;

    private const FILLABLE = [
        self::COL_TITLE,
        self::COL_AGENT_OR_MANAGER,
        self::COL_DATE,
        self::COL_TIME,
        self::COL_DESCRIPTION,
        self::COL_MODULE_TYPE,
        self::COL_MODULE_ID,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function taskUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', ProjectsConstants::COL_ASGN);
        // * consider belongsTo(User::class,ProjectsConstants::COL_ASGN,'id')
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', ProjectsConstants::COL_PJ_ID);
        // * consider belongsTo(Project::class,ProjectsConstants::COL_PJ_ID,'id')
    }

    public function milestone(): HasOne
    {
        return $this->hasOne(Milestone::class, 'id', ProjectsConstants::COL_ML_ID);
        // * consider belongsTo(Milestone::class,ProjectsConstants::COL_ML_ID,'id')
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function taskFiles(): HasMany
    {
        return $this->hasMany(TaskFile::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function taskCheckList(): HasMany
    {
        return $this->hasMany(TaskCheckList::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->orderBy('id', 'DESC');
    }

    public function taskCompleteCheckListCount(): int
    {
        return $this->hasMany(TaskCheckList::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->where(ActivitiesConstants::COL_TSK_STT, '1')
            ->count();
    }

    public function taskTotalCheckListCount(): int
    {
        return $this->hasMany(TaskCheckList::class, ActivitiesConstants::COL_TSK_ID, 'id')
            ->count();
    }
}
