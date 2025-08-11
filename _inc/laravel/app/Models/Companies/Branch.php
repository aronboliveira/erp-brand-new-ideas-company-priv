<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants, DatabaseConstants};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use UsesUuids;

    private const FILLABLE = [CompaniesConstants::COL_BRC_NM, DatabaseConstants::TABLE_CREATOR];
    protected $fillable  = self::FILLABLE;
}
