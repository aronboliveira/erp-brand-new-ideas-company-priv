<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants, ProjectsConstants};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class DealTask extends Model
{
    use UsesUuids;

    private const COL_DATE    = ActivitiesConstants::COL_TSK_DATE;
    private const COL_DEAL_ID = ActivitiesConstants::COL_DL;
    private const COL_NAME    = ProjectsConstants::COL_NM;
    private const COL_PRIORITY = ProjectsConstants::COL_PRT;
    private const COL_STATUS  = ActivitiesConstants::COL_TSK_STT;
    private const COL_TIME    = ActivitiesConstants::COL_TSK_TIME;

    protected $fillable = [
        self::COL_DEAL_ID,
        self::COL_NAME,
        self::COL_DATE,
        self::COL_TIME,
        self::COL_PRIORITY,
        self::COL_STATUS,
    ];

    public static $priorities = [
        1 => 'Low',
        2 => 'Medium',
        3 => 'High',
    ];

    public static $status = [
        0 => 'On Going',
        1 => 'Completed',
    ];
}
