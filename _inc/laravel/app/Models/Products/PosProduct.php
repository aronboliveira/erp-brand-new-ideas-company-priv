<?php

namespace App\Models;

use App\Models\ProductService;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class PosProduct extends Model
{
    use UsesUuids;

    private const COL_DESCRIPTION  = 'description';
    private const COL_DISCOUNT     = 'discount';
    private const COL_PRICE        = 'price';         // ! CHANGED (was total)
    private const COL_POS_ID       = 'pos_id';
    private const COL_PRODUCT_ID   = 'product_id';
    private const COL_QUANTITY     = 'quantity';
    private const COL_TAX          = 'tax';

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_POS_ID,
        self::COL_QUANTITY,
        self::COL_TAX,
        self::COL_DISCOUNT,
        self::COL_PRICE,       // ! CHANGED
        self::COL_DESCRIPTION, // * added to match migration
    ];

    public function product(): HasOne
    {
        return $this
            ->hasOne(ProductService::class, 'id', self::COL_PRODUCT_ID);
        // * consider using belongsTo(ProductService::class, self::COL_PRODUCT_ID)
    }
}
