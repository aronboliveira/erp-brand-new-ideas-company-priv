<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{ExtendsProductServiceTable, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Log};

final class WarehouseProduct extends Model
{
    use ExtendsProductServiceTable, HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_WRH_PRD;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        BC::COL_WRH_ID,
        BC::COL_PRD_ID,
        'quantity',
    ];

    protected $with = [
        'warehouse',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    protected $appends = [
        'is_empty',
    ];

    protected static function booted(): void
    {
        try {
            static::saving(function (self $m): void {
                $qty = (int) ($m->getAttribute('quantity') ?? 0);
                if ($qty < 0) $qty = 0;
                $m->setAttribute('quantity', $qty);
            });
        } catch (\Throwable $e) {
            Log::error(static::class . '::booted failed', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    protected static function getExtendsBaseTableExcludedColumns(): array
    {
        return [
            ...self::BASE_TABLE_EXCLUDED_COLUMNS,
            'quantity',
        ];
    }

    /** Warehouse owning this row. */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, BC::COL_WRH_ID, 'id');
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

    /** True when quantity is zero. */
    public function getIsEmptyAttribute(): bool
    {
        return (int) ($this->getAttribute('quantity') ?? 0) <= 0;
    }

    /** Cacheable aggregation: total quantity by warehouse. */
    public static function cachedQuantityForWarehouse(string $warehouseId): int
    {
        static $cache = [];
        if (isset($cache[$warehouseId])) return (int) $cache[$warehouseId];

        try {
            $tbl = (new static)->getTable();
            $row = DB::selectOne(
                "select coalesce(sum(quantity), 0) as s from {$tbl} where " . BC::COL_WRH_ID . " = ?",
                [$warehouseId]
            );
            $cache[$warehouseId] = (int) ($row?->s ?? 0);
            return (int) $cache[$warehouseId];
        } catch (\Throwable $e) {
            Log::warning(static::class . '::cachedQuantityForWarehouse failed', [
                'warehouse_id' => $warehouseId,
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return 0;
        }
    }
}
