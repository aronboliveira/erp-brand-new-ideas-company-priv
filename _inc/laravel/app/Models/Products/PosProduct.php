<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{ExtendsProductServiceTable, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\Facades\{DB, Log};

final class PosProduct extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, ExtendsProductServiceTable;

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
        try {
            return [
                ...self::BASE_TABLE_EXCLUDED_COLUMNS,
                'quantity',
                'tax',
                'discount',
                'price',
            ];
        } catch (\Throwable $e) {
            Log::error(static::class . '::getExtendsBaseTableExcludedColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

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

        public function getSubtotalAttribute(): float
    {
            try {
            $qty = (int) ($this->getAttribute('quantity') ?? 0);
            $price = (float) ($this->getAttribute('price') ?? 0.0);
            if ($qty <= 0 || $price <= 0.0) return 0.0;
            return (float) ($qty * $price);
            } catch (\Throwable $e) {
                Log::error(static::class . '::getSubtotalAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return 0.0;
            }
    }

        public function getDiscountAmountAttribute(): float
    {
            try {
            $subtotal = (float) ($this->getAttribute('subtotal') ?? 0.0);
            if ($subtotal <= 0.0) return 0.0;
            return $this->inferAmountFromRateOrAbsolute((float) ($this->getAttribute('discount') ?? 0.0), $subtotal);
            } catch (\Throwable $e) {
                Log::error(static::class . '::getDiscountAmountAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return 0.0;
            }
    }

        public function getTaxAmountAttribute(): float
    {
            try {
            $subtotal = (float) ($this->getAttribute('subtotal') ?? 0.0);
            if ($subtotal <= 0.0) return 0.0;
            return $this->inferAmountFromRateOrAbsolute((float) ($this->getAttribute('tax') ?? 0.0), $subtotal);
            } catch (\Throwable $e) {
                Log::error(static::class . '::getTaxAmountAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return 0.0;
            }
    }

        public function getTotalAttribute(): float
    {
            try {
            $subtotal = (float) ($this->getAttribute('subtotal') ?? 0.0);
            if ($subtotal <= 0.0) return 0.0;

            $disc = (float) ($this->getAttribute('discount_amount') ?? 0.0);
            $tax = (float) ($this->getAttribute('tax_amount') ?? 0.0);

            $t = $subtotal - $disc + $tax;
            return $t < 0.0 ? 0.0 : $t;
            } catch (\Throwable $e) {
                Log::error(static::class . '::getTotalAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return 0.0;
            }
    }

        public static function cachedTotalForPos(string $posId): float
    {
            try {
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
            } catch (\Throwable $e) {
                Log::error(static::class . '::cachedTotalForPos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return 0.0;
            }
    }

        protected function inferAmountFromRateOrAbsolute(float $v, float $base): float
    {
            try {
            if ($v <= 0.0 || $base <= 0.0) return 0.0;
            if ($v <= 1.0) return $base * $v;
            if ($v <= 100.0) return $base * ($v / 100.0);
            return $v > $base ? $base : $v;
            } catch (\Throwable $e) {
                Log::error(static::class . '::inferAmountFromRateOrAbsolute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return 0.0;
            }
    }
}
