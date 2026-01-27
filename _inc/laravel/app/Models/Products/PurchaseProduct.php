<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use App\Traits\{ExtendsProductServiceTable, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseProduct extends Model
{
    use UsesUuids, HasAuditFields, ExtendsProductServiceTable;

    protected $fillable = [
        BC::COL_PRD_ID,
        BC::COL_PRC_ID,
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $with = [
        'purchase',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'discount' => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, BC::COL_PRC_ID);
    }

    public function productProduct(): ?BelongsTo
    {
        return $this->belongsTo(Product::class, BC::COL_PRD_ID);
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, BC::COL_PRD_ID);
    }

    public function product(): ?BelongsTo
    {
        return Utility::getProduct($this);
    }
}
