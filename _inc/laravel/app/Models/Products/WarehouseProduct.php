<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class WarehouseProduct extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY  = 'created_by';
    private const COL_PRODUCT_ID  = 'product_id';
    private const COL_QUANTITY    = 'quantity';
    private const COL_WAREHOUSE_ID = 'warehouse_id';

    protected $fillable = [
        self::COL_WAREHOUSE_ID,
        self::COL_PRODUCT_ID,
        self::COL_QUANTITY,
        self::COL_CREATED_BY,
    ];

    public function product(): HasOne
    {
        return $this
            ->hasOne(ProductService::class, 'id', self::COL_PRODUCT_ID);
        // * consider using belongsTo(ProductService::class, self::COL_PRODUCT_ID)
    }

    public function warehouse(): HasOne
    {
        return $this
            ->hasOne(Warehouse::class, 'id', self::COL_WAREHOUSE_ID); // ! CHANGED
        // * consider using belongsTo(Warehouse::class, self::COL_WAREHOUSE_ID)
    }
}
