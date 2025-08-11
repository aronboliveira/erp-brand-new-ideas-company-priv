<?php

namespace App\Models;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use App\Traits\UsesUuids;

class PurchaseProduct extends Model
{
    use UsesUuids;

    protected $fillable = [
        'product_id',
        'purchase_id',
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    public function product(): HasOne
    {
        // * consider belongsTo if inverted relation
        return $this->hasOne(ProductService::class, 'id', 'product_id');
    }
}
