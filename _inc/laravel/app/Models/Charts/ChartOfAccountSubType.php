<?php

namespace App\Models;

use App\Config\Constants\{ChartsConstants, DatabaseConstants};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class ChartOfAccountSubType extends Model
{
    private const FILLABLE = [
        'id',
        ChartsConstants::COL_NM,
        ChartsConstants::COL_TP,
        ChartsConstants::COL_TP_NM,
        DatabaseConstants::TABLE_CREATOR
    ];
    protected $fillable  = self::FILLABLE;

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', DatabaseConstants::TABLE_CREATOR);
        // * consider belongsTo(User::class,'created_by','id')
    }
}
