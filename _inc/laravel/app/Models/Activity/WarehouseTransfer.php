<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};

class WarehouseTransfer extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'from_warehouse', 'to_warehouse', 'product_id',
        'quantity', 'date', 'created_by'
    ];

    private const FK_FROM_WH = 'from_warehouse';
    private const FK_TO_WH   = 'to_warehouse';
    private const FK_PRODUCT = 'product_id';
    private const FK_CREATED = 'created_by';

    public function product(): HasOne
    {
        return $this->hasOne(
            ProductService::class,
            'id',
            self::FK_PRODUCT
        );
    }

    public function fromWarehouse(): HasOne
    {
        return $this->hasOne(
            Warehouse::class,
            'id',
            self::FK_FROM_WH
        );
    }

    public function toWarehouse(): HasOne
    {
        return $this->hasOne(
            Warehouse::class,
            'id',
            self::FK_TO_WH,
        );
    }

    public function createdBy(): BelongsTo // * ADDED
    {
        return $this->belongsTo(
            User::class,
            self::FK_CREATED,
            'id'
        );
    }
}
