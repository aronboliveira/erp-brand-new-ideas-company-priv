<?php

namespace App\Models;

use App\Models\ProductService;
use App\Traits\UsesUuids;use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class ProposalProduct extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'proposal_id',
        'product_id',
        'quantity',
        'tax',
        'discount',
        'price',        // ! CHANGED from total
        'description'   // ! CHANGED added
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function product(): HasOne
    {
        return $this->hasOne(ProductService::class, 'id', 'product_id');
        // * consider belongsTo(ProductService::class,'product_id','id')
    }
}
