<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class StockReport extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_DESCRIPTION = 'description';
    private const COL_PRODUCT_ID = 'product_id';
    private const COL_QUANTITY   = 'quantity';
    private const COL_TYPE       = 'type';
    private const COL_TYPE_ID    = 'type_id';

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_QUANTITY,
        self::COL_TYPE,
        self::COL_TYPE_ID,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
    ];

    public function product(): HasOne
    {
        return $this
            ->hasOne(ProductService::class, 'id', self::COL_PRODUCT_ID);
        // * consider using belongsTo(ProductService::class, self::COL_PRODUCT_ID)
    }

    public static function products(string $productCsv): mixed
    {
        $categoryArr = explode(',', $productCsv);
        foreach ($categoryArr as $product) {
            $productObj = ProductService::find($product);
            $categoryArr = isset($productObj)
                ? $productObj->name
                : '';
        }
        return $categoryArr;
        // * TODO: logic returns string; consider refactoring to return array of names
    }
}
