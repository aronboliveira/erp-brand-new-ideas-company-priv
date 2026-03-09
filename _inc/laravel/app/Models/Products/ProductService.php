<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BanksConstants as BKC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    SettingsConstants as SC
};
use App\Services\ProductOrServiceRequestService;
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany
};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * @property string $id
 * @property string|null $name
 * @property string|null $sku
 * @property float|null $sale_price
 * @property float|null $purchase_price
 * @property string|null $description
 * @property float|null $quantity
 * @property string|null $tax_id
 * @property string|null $category_id
 * @property string|null $unit_id
 * @property string|null $type
 * @property bool $is_active
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|string|null $available_from
 * @property \Illuminate\Support\Carbon|string|null $available_until
 * @property array|string|null $customField
 * @property string|null $item
 * @property string|null $pro_image
 * @property string|null $tax
 * @property string|null $unit

 * @property mixed $custom
 */
class ProductService extends Model
{
    use HasAuditFields, HasFactory, NormalizesArrays, UsesUuids;

    public const TABLE = DC::TABLE_PROD_SERVS;

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'sku',
        BC::COL_SL_PRC,
        BC::COL_PC_PRC,
        BC::COL_AC_CUR,
        BC::COL_AC_MUNITS,
        'description',
        'attributes',
        'tags',
        DC::COL_PRO_IMG,
        'icon',
        'quantity',
        BC::COL_TAX_ID,
        BC::COL_CAT_ID,
        'categories',
        DC::COL_RL_CAT,
        BC::COL_UNIT_ID,
        BC::COL_UNITS_SOLD,
        BC::COL_UNITS_CNC,
        BC::COL_UNITS_RTRN,
        'type',
        BKC::COL_SL_COA,
        BKC::COL_EXP_COA,
        AC::COL_AV_FROM,
        AC::COL_AV_UNTIL,
        AC::COL_IA,
        BC::COL_ON_SALE,
        BC::COL_IS_LK,
        BC::COL_IS_TRS,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        BC::COL_SL_PRC       => 'decimal:4',
        BC::COL_PC_PRC       => 'decimal:4',
        'quantity'           => 'float',
        BC::COL_AC_CUR       => 'array',
        BC::COL_AC_MUNITS    => 'array',
        'attributes'         => 'array',
        'tags'               => 'array',
        'categories'         => 'array',
        DC::COL_RL_CAT       => 'array',
        BC::COL_UNITS_SOLD   => 'integer',
        BC::COL_UNITS_CNC    => 'integer',
        BC::COL_UNITS_RTRN   => 'integer',
        AC::COL_AV_FROM      => 'datetime',
        AC::COL_AV_UNTIL     => 'datetime',
        AC::COL_IA           => 'boolean',
        BC::COL_ON_SALE      => 'boolean',
        BC::COL_IS_LK        => 'boolean',
        BC::COL_IS_TRS       => 'boolean',
    ];

    protected $with = [
        'taxes',
    ];

    protected $appends = [
        'is_available',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->getAttribute(BC::COL_AC_CUR)))
                $model->setAttribute(BC::COL_AC_CUR, [SC::DEF_SITE_CURRENCY_ID]);
            if (empty($model->getAttribute(BC::COL_AC_MUNITS)))
                $model->setAttribute(BC::COL_AC_MUNITS, ['other']);
        });
        static::saving(function (self $m): void {
            foreach (['name', 'sku', 'type', 'icon', DC::COL_PRO_IMG] as $field)
                if (!empty($m->getAttribute($field)) && is_string($m->getAttribute($field)))
                    $m->setAttribute($field, trim($m->getAttribute($field)));
            if (!empty($m->getAttribute('sku')) && is_string($m->getAttribute('sku')))
                $m->setAttribute('sku', strtoupper($m->getAttribute('sku')));
            foreach ([BC::COL_SL_PRC, BC::COL_PC_PRC] as $priceField)
                if ($m->getAttribute($priceField) === null || $m->getAttribute($priceField) < 0)
                    $m->setAttribute($priceField, 0.0000);
            if ($m->getAttribute('quantity') === null || $m->getAttribute('quantity') < 0)
                $m->setAttribute('quantity', 0.0);
            foreach ([BC::COL_UNITS_SOLD, BC::COL_UNITS_CNC, BC::COL_UNITS_RTRN] as $cField)
                if ($m->getAttribute($cField) === null || $m->getAttribute($cField) < 0)
                    $m->setAttribute($cField, 0);
            $from  = $m->getAttribute(AC::COL_AV_FROM);
            $until = $m->getAttribute(AC::COL_AV_UNTIL);
            if ($from && $until && $until < $from)
                $m->setAttribute(AC::COL_AV_UNTIL, $from);
            foreach ([AC::COL_IA, BC::COL_ON_SALE, BC::COL_IS_LK, BC::COL_IS_TRS] as $boolField)
                if ($m->getAttribute($boolField) === null)
                    $m->setAttribute($boolField, false);
            try {
                $m->setAttribute(BC::COL_AC_CUR, self::sanitizeCurrencies($m->getAttribute(BC::COL_AC_CUR) ?? null));
            } catch (\Throwable $e) {
                Log::warning(
                    'Failed to normalize accepted currencies for ProductService ' . ($m->id ?? 'new'),
                    ['error' => $e->getMessage()]
                );
                $m->setAttribute(BC::COL_AC_CUR, [strtoupper(SC::DEF_SITE_CURRENCY_ID)]);
            }
            $m->setAttribute(BC::COL_AC_MUNITS, self::sanitizeMeasurementUnits($m->getAttribute(BC::COL_AC_MUNITS) ?? null));
            foreach (['attributes', 'tags'] as $jsonField)
                $m->setAttribute($jsonField, self::normalizeArrayField($m->getAttribute($jsonField) ?? null));
            try {
                $m->setAttribute('categories', self::sanitizeCategoriesArray($m->getAttribute('categories') ?? null));
                $m->setAttribute(DC::COL_RL_CAT, self::sanitizeCategoriesArray($m->getAttribute(DC::COL_RL_CAT) ?? null));
            } catch (\Throwable $e) {
                Log::warning(
                    'Failed to normalize categories for ProductService ' . ($m->id ?? 'new'),
                    ['error' => $e->getMessage()]
                );
            }
            if ($m->getAttribute(BC::COL_UNIT_ID)) {
                $exists = ProductServiceUnit::query()
                    ->where('id', $m->getAttribute(BC::COL_UNIT_ID))
                    ->exists();
                if (!$exists)
                    $m->setAttribute(BC::COL_UNIT_ID, null);
            }
        });
    }

    protected static function sanitizeCurrencies(mixed $raw): array
    {
        $items = self::normalizeArrayField($raw);
        $normalized = [];
        foreach ($items as $item) {
            if (!is_string($item))
                continue;
            $code = strtoupper(substr(trim($item), 0, 3));
            if ($code === '')
                continue;
            $normalized[$code] = $code;
        }
        $default = strtoupper(SC::DEF_SITE_CURRENCY_ID);
        if (!isset($normalized[$default]))
            $normalized[$default] = $default;
        return array_values($normalized);
    }

    protected static function sanitizeMeasurementUnits(mixed $raw): array
    {
        $items = self::normalizeArrayField($raw);
        $normalized = [];
        foreach ($items as $item) {
            if (!is_string($item))
                continue;
            $v = trim($item);
            if ($v === '')
                continue;
            $normalized[strtolower($v)] = $v;
        }
        if (!$normalized)
            $normalized['other'] = 'other';
        return array_values($normalized);
    }

    protected static function sanitizeCategoriesArray(mixed $raw): array
    {
        $items = self::normalizeArrayField($raw);
        if (!$items)
            return [];
        $valid = [];
        foreach ($items as $item) {
            if (!is_array($item))
                continue;
            $id = null;
            foreach (['id', 'category_id'] as $key) {
                if (!array_key_exists($key, $item))
                    continue;
                $value = $item[$key];
                if (!is_string($value))
                    continue;
                $value = trim($value);
                if ($value === '')
                    continue;
                $id = $value;
                break;
            }
            if (!$id)
                continue;
            if (!ProductServiceCategory::query()->where('id', $id)->exists())
                continue;
            $item['id'] = $id;
            unset($item['category_id']);
            $valid[$id] = $item;
        }
        return array_values($valid);
    }

    public function getIsAvailableAttribute(): bool
    {
        $now   = now();
        $from  = $this->{AC::COL_AV_FROM};
        $until = $this->{AC::COL_AV_UNTIL};

        if (!$this->{AC::COL_IA})
            return false;

        return (!$from || $from <= $now) && (!$until || $until >= $now);
    }

    public function taxes(): BelongsTo
    {
        return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductServiceCategory::class, BC::COL_CAT_ID, 'id');
    }

    public function saleChartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, BKC::COL_SL_COA, 'id');
    }

    public function expenseChartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, BKC::COL_EXP_COA, 'id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductServiceUnit::class, 'product_service_id', 'id');
    }

    public function legacyUnit(): BelongsTo
    {
        return $this->belongsTo(ProductServiceUnit::class, BC::COL_UNIT_ID, 'id');
    }

    public function tax(string $taxes): array
    {
        $ids = explode(',', $taxes);
        return array_map(fn($id) => Tax::find($id), $ids);
    }

    public function taxRate(string $taxes): float
    {
        return array_reduce(
            explode(',', $taxes),
            fn($sum, $id) => $sum + (optional(Tax::find($id))->rate ?? 0),
            0.0
        );
    }

    public static function taxData(string $taxes): string
    {
        $names = array_map(
            fn($id) => optional(Tax::find($id))->name ?: '',
            explode(',', $taxes)
        );
        return implode(',', $names);
    }

    public static function getAllProducts(): Builder|RedirectResponse
    {
        return app(ProductOrServiceRequestService::class)->getAllProducts();
    }

    public function getTotalProductQuantity(): float | RedirectResponse
    {
        return app(ProductOrServiceRequestService::class)->getTotalProductQuantity($this);
    }

    public static function taxId(string|int $productId): int | RedirectResponse
    {
        return app(ProductOrServiceRequestService::class)->getProductTaxId($productId);
    }

    public function warehouseProduct(string|int $productId, string|int $warehouseId): float
    {
        $wp = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
        return $wp->quantity ?? 0;
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ProductServiceUnit, $this> */
    public function unit(): BelongsTo
    {
        return $this->legacyUnit();
    }
}
