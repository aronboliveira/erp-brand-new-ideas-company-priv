<?php

namespace App\Models;

use App\Models\{
    ChartOfAccount,
    ProductService
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class BillProduct extends Model
{
    use UsesUuids;

    private const COL_BILL_ID          = 'bill_id';
    private const COL_CHART_ACCOUNT_ID = 'chart_account_id';
    private const COL_PRODUCT_ID       = 'product_id';

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_BILL_ID,
        self::COL_CHART_ACCOUNT_ID,
        'quantity',
        'tax',
        'discount',
        'total',
        'description', // * added to match migration
    ];

    public function chartAccount(): HasOne
    {
        return $this
            ->hasOne(ChartOfAccount::class, 'id', self::COL_CHART_ACCOUNT_ID);
        // * consider using belongsTo(ChartOfAccount::class, self::COL_CHART_ACCOUNT_ID)
    }

    public function product(): HasOne
    {
        return $this
            ->hasOne(ProductService::class, 'id', self::COL_PRODUCT_ID);
        // * consider using belongsTo(ProductService::class, self::COL_PRODUCT_ID)
    }
}
