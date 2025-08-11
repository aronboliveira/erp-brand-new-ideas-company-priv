<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants, DatabaseConstants};
use App\Models\Branch;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Department extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        CompaniesConstants::COL_BRC_ID,
        DatabaseConstants::TABLE_CREATOR,
        CompaniesConstants::COL_DEP_NM
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    protected $with = ['branch'];

    public function branch(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', CompaniesConstants::COL_BRC_ID);
        // * consider using belongsTo(Branch::class,CompaniesConstants::COL_BRC_ID,'id')
    }
}
