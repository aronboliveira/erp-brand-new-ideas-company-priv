<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillAccount extends Model
{
    use HasFactory, UsesUuids;

    private const COL_CHART_ACCOUNT_ID = 'chart_account_id';
    private const COL_REF_ID          = 'ref_id';

    protected $fillable = [
        self::COL_CHART_ACCOUNT_ID,
        'price',
        'description',
        'type',
        self::COL_REF_ID,
    ];
}
