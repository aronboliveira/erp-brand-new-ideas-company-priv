<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class UserToDo extends Model
{
    use UsesUuids;

    private const COL_TITLE      = ActivitiesConstants::COL_TT;
    private const COL_IS_COMPLETE = ProjectsConstants::COL_IS_CP;
    private const COL_USER_ID    = UsersConstants::COL_USER_ID;

    private const FILLABLE = [
        self::COL_TITLE,
        self::COL_IS_COMPLETE,
        self::COL_USER_ID,
    ];

    protected $fillable = self::FILLABLE;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_USER_ID);
        // * consider belongsTo(User::class,self::COL_USER_ID,'id')
    }
}
