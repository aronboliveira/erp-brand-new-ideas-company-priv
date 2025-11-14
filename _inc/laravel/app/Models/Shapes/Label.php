<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants, ProjectsConstants as PJC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class Label extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $fillable = [
        PJC::COL_LB_NM,
        PJC::COL_CL,
        PJC::COL_PPL_ID,
    ];
    protected $guarded  = [
        'id',
        DatabaseConstants::TABLE_CREATOR,
    ];

    public static array $colors = [
        'primary',
        'secondary',
        PJC::STT_DGR,
        PJC::STT_WRN,
        PJC::STT_INF,
        PJC::STT_SCS,
    ];
}
