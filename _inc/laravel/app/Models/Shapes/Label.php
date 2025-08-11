<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants, ProjectsConstants};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class Label extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        ProjectsConstants::COL_LB_NM,
        ProjectsConstants::COL_CL,
        ProjectsConstants::COL_PPL_ID,
        DatabaseConstants::TABLE_CREATOR,
    ];

    public static array $colors = [
        'primary',
        'secondary',
        ProjectsConstants::STT_DGR,
        ProjectsConstants::STT_WRN,
        ProjectsConstants::STT_INF,
        ProjectsConstants::STT_SCS,
    ];
}
