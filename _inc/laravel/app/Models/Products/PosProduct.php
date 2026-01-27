<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{ExtendsProductServiceTable, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Log};

final class PosProduct extends Model
{
    use UsesUuids, HasAuditFields, ExtendsProductServiceTable;

    protected $table = DC::TABLE_POS_PRD;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        BC::COL_PRD_ID,
        BC::COL_POS_ID,
        'quantity',
        'tax',
        'discount',
        'price',
        'description',
    ];

    protected $with = [
        'pos',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'tax' => 'decimal:2',
        'discount' => 'float',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
    ];

    protected static function booted(): void
    {
        try {
            static::saving(function (self $m): void {
                $qty = (int) ($m->getAttribute('quantity') ?? 0);
                if ($qty < 0) $qty = 0;
                $m->setAttribute('quantity', $qty);

                foreach (['tax', 'discount', 'price'] as $k) {
                    $v = $m->getAttribute($k);
                    if ($v === null) continue;
                    $f = (float) $v;
                    if ($f < 0.0) $f = 0.0;
                    $m->setAttribute($k, $f);
                }

                $desc = $m->getAttribute('description');
                if ($desc !== null) {
                    $s = trim((string) $desc);
                    $m->setAttribute('description', $s === '' ? null : $s);
                }
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
            'tax',
            'discount',
            'price',
        ];
    }

    /** POS owning this line item. */
    public function pos(): BelongsTo
    {
        return $this->belongsTo(Pos::class, BC::COL_POS_ID, 'id');
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

    /** Line subtotal (quantity * price). */
    public function getSubtotalAttribute(): float
    {
        $qty = (int) ($this->getAttribute('quantity') ?? 0);
        $price = (float) ($this->getAttribute('price') ?? 0.0);
        if ($qty <= 0 || $price <= 0.0) return 0.0;
        return (float) ($qty * $price);
    }

    /** Discount amount inferred from discount value. */
    public function getDiscountAmountAttribute(): float
    {
        $subtotal = (float) ($this->getAttribute('subtotal') ?? 0.0);
        if ($subtotal <= 0.0) return 0.0;
        return $this->inferAmountFromRateOrAbsolute((float) ($this->getAttribute('discount') ?? 0.0), $subtotal);
    }

    /** Tax amount inferred from tax value. */
    public function getTaxAmountAttribute(): float
    {
        $subtotal = (float) ($this->getAttribute('subtotal') ?? 0.0);
        if ($subtotal <= 0.0) return 0.0;
        return $this->inferAmountFromRateOrAbsolute((float) ($this->getAttribute('tax') ?? 0.0), $subtotal);
    }

    /** Total = subtotal - discount + tax. */
    public function getTotalAttribute(): float
    {
        $subtotal = (float) ($this->getAttribute('subtotal') ?? 0.0);
        if ($subtotal <= 0.0) return 0.0;

        $disc = (float) ($this->getAttribute('discount_amount') ?? 0.0);
        $tax = (float) ($this->getAttribute('tax_amount') ?? 0.0);

        $t = $subtotal - $disc + $tax;
        return $t < 0.0 ? 0.0 : $t;
    }

    /** Cacheable aggregation: sum(total) for a POS. */
    public static function cachedTotalForPos(string $posId): float
    {
        static $cache = [];
        if (isset($cache[$posId])) return (float) $cache[$posId];

        try {
            $tbl = (new static)->getTable();
            $row = DB::selectOne(
                "select coalesce(sum(quantity * price), 0) as s from {$tbl} where " . BC::COL_POS_ID . " = ?",
                [$posId]
            );
            $cache[$posId] = (float) ($row?->s ?? 0.0);
            return (float) $cache[$posId];
        } catch (\Throwable $e) {
            Log::warning(static::class . '::cachedTotalForPos failed', [
                'pos_id' => $posId,
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return 0.0;
        }
    }

    /** Interprets $v as fraction (0..1), percent (0..100), or absolute (>=100). */
    protected function inferAmountFromRateOrAbsolute(float $v, float $base): float
    {
        if ($v <= 0.0 || $base <= 0.0) return 0.0;
        if ($v <= 1.0) return $base * $v;
        if ($v <= 100.0) return $base * ($v / 100.0);
        return $v > $base ? $base : $v;
    }
}
