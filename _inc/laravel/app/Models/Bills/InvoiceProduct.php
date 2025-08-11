<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class InvoiceProduct extends Model
{
    use UsesUuids;

    private const COL_DESCRIPTION  = 'description';
    private const COL_DISCOUNT     = 'discount';
    private const COL_INVOICE_ID   = 'invoice_id';
    private const COL_PRICE        = 'price';
    private const COL_PRODUCT_ID   = 'product_id';
    private const COL_QUANTITY     = 'quantity';
    private const COL_TAX          = 'tax';

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_INVOICE_ID,
        self::COL_QUANTITY,
        self::COL_TAX,
        self::COL_DISCOUNT,
        self::COL_PRICE,
        self::COL_DESCRIPTION,
    ];

    public function product(): HasOne
    {
        return $this
            ->hasOne(ProductService::class, 'id', self::COL_PRODUCT_ID);
        // * consider belongsTo(ProductService::class, self::COL_PRODUCT_ID)
    }
}
