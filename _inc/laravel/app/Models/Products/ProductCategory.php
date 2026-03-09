<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model};

class ProductCategory extends Model
{
    use UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_PRD_CAT;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        'name',
        'description',
        PJC::COL_PRD_SERV_CAT_ID,
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}
