<?php

namespace App\Models;

use App\Config\Constants\{ChartsConstants, DatabaseConstants};
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountType extends Model
{
    private const FILLABLE_FIELDS = [
        'id',
        ChartsConstants::COL_NM,
        DatabaseConstants::TABLE_CREATOR
    ]; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED
}
