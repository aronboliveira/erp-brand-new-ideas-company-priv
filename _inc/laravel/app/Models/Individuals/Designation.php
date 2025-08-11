<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    UsersConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        DatabaseConstants::TABLE_CREATOR,
        CompaniesConstants::COL_DEP_ID,
        UsersConstants::COL_DSG_NM
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    // * consider adding: belongsTo(Department::class,CompaniesConstants::COL_DEP_ID,'id')
    // * consider adding: belongsTo(User::class,DatabaseConstants::TABLE_CREATOR,'id')
}
