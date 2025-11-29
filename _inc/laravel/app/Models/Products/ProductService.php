<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BanksConstants as BKC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    SettingsConstants as SC
};
use App\Traits\{
    ChecksLogin,
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{DB, Log};

class ProductService extends Model
{
    use ChecksLogin, HasAuditFields, HasFactory, NormalizesArrays, UsesUuids;

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
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
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
        'category',
        'taxes',
    ];

    protected $appends = [
        'is_available',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->{BC::COL_AC_CUR}))
                $model->{BC::COL_AC_CUR} = [SC::DEF_SITE_CURRENCY_ID];
            if (empty($model->{BC::COL_AC_MUNITS}))
                $model->{BC::COL_AC_MUNITS} = ['other'];
        });
        static::saving(function (self $m): void {
            foreach (['name', 'sku', 'type', 'icon', DC::COL_PRO_IMG] as $field)
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});

            if (isset($m->sku) && is_string($m->sku))
                $m->sku = strtoupper($m->sku);

            foreach ([BC::COL_SL_PRC, BC::COL_PC_PRC] as $priceField)
                if ($m->{$priceField} === null || $m->{$priceField} < 0)
                    $m->{$priceField} = 0.0000;

            if ($m->quantity === null || $m->quantity < 0)
                $m->quantity = 0.0;

            foreach ([BC::COL_UNITS_SOLD, BC::COL_UNITS_CNC, BC::COL_UNITS_RTRN] as $cField)
                if ($m->{$cField} === null || $m->{$cField} < 0)
                    $m->{$cField} = 0;

            $from  = $m->{AC::COL_AV_FROM};
            $until = $m->{AC::COL_AV_UNTIL};
            if ($from && $until && $until < $from)
                $m->{AC::COL_AV_UNTIL} = $from;

            foreach ([AC::COL_IA, BC::COL_ON_SALE, BC::COL_IS_LK, BC::COL_IS_TRS] as $boolField)
                if ($m->{$boolField} === null)
                    $m->{$boolField} = false;

            try {
                $m->{BC::COL_AC_CUR} = self::sanitizeCurrencies($m->{BC::COL_AC_CUR} ?? null);
            } catch (\Throwable $e) {
                Log::warning(
                    'Failed to normalize accepted currencies for ProductService ' . ($m->id ?? 'new'),
                    ['error' => $e->getMessage()]
                );
                $m->{BC::COL_AC_CUR} = [strtoupper(SC::DEF_SITE_CURRENCY_ID)];
            }

            $m->{BC::COL_AC_MUNITS} = self::sanitizeMeasurementUnits($m->{BC::COL_AC_MUNITS} ?? null);

            foreach (['attributes', 'tags'] as $jsonField)
                $m->{$jsonField} = self::normalizeArrayField($m->{$jsonField} ?? null);

            try {
                $m->categories        = self::sanitizeCategoriesArray($m->categories ?? null);
                $m->{DC::COL_RL_CAT}  = self::sanitizeCategoriesArray($m->{DC::COL_RL_CAT} ?? null);
            } catch (\Throwable $e) {
                Log::warning(
                    'Failed to normalize categories for ProductService ' . ($m->id ?? 'new'),
                    ['error' => $e->getMessage()]
                );
            }

            if ($m->{BC::COL_UNIT_ID}) {
                $exists = ProductServiceUnit::query()
                    ->where('id', $m->{BC::COL_UNIT_ID})
                    ->exists();
                if (!$exists)
                    $m->{BC::COL_UNIT_ID} = null;
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

    // --------- MÉTODOS LEGADOS UTILITÁRIOS (mantidos) ---------

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

    public static function getAllProducts()
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse)
            return $userOrRedirect;

        $user  = $userOrRedirect;
        $table = self::TABLE;

        return self::select($table . '.*', 'c.name as categoryname')
            ->where($table . '.type', 'product')
            ->leftJoin(DC::TABLE_PROD_SERV_CATS . ' as c', 'c.id', '=', $table . '.' . BC::COL_CAT_ID)
            ->where($table . '.' . DC::TABLE_CREATOR, $user?->creatorId())
            ->orderByDesc($table . '.id');
    }

    public function getTotalProductQuantity(): float | RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;

        $user   = $userOrRedirect;
        $userId = $user?->creatorId();
        $pid    = $this->id;

        $purchases = Purchase::where(DC::TABLE_CREATOR, $userId);
        if ($user?->isUser())
            $purchases->where('warehouse_id', $user?->warehouse_id);

        $purchasedQty = $purchases->get()->sum(
            fn($p) => optional(
                PurchaseProduct::where('purchase_id', $p->id)
                    ->where('product_id', $pid)
                    ->first()
            )->quantity ?: 0
        );

        $poses = Pos::where(DC::TABLE_CREATOR, $userId);
        if ($user?->isUser())
            $poses->where('warehouse_id', $user?->warehouse_id);

        $posQty = $poses->get()->sum(
            fn($p) => optional(
                PosProduct::where('pos_id', $p->id)
                    ->where('product_id', $pid)
                    ->first()
            )->quantity ?: 0
        );

        return $purchasedQty - $posQty;
    }

    public static function taxId(int $productId): int | RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;

        $user = $userOrRedirect;

        return DB::table(self::TABLE)
            ->where('id', $productId)
            ->where(DC::TABLE_CREATOR, $user?->creatorId())
            ->value(BC::COL_TAX_ID) ?: 0;
    }

    public function warehouseProduct(string $productId, string $warehouseId): float
    {
        $wp = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        return $wp->quantity ?? 0;
    }
}
