<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    SettingsConstants as SC
};
use App\Enums\ProductStatus;
use App\Traits\{
    HasAuditFields,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasOne
};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductServiceUnit extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, SoftDeletes;

    public const TABLE = DC::TABLE_PROD_SERV_UNITS;

    protected $table = self::TABLE;

    protected $fillable = [
        'product_service_id',
        'name',
        'code',
        'status',
        AC::COL_MUNIT,
        'purchase_index',
        BC::COL_BS_PRC,
        'discount',
        BC::COL_CUR_ID,
        'attributes',
        'notes',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        'purchase_index'     => 'integer',
        BC::COL_BS_PRC       => 'decimal:4',
        'discount'           => 'decimal:4',
        BC::COL_CUR_ID       => 'string',
        'attributes'         => 'array',
        'status'             => ProductStatus::class,
    ];

    protected $with = [
        'user',
    ];

    protected $appends = [
        'is_active',
        'price_with_discount',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            foreach (['name', 'code', BC::COL_CUR_ID, AC::COL_MUNIT] as $field)
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});

            $currentStatus = $m->status instanceof ProductStatus
                ? $m->status->value
                : (string) $m->status;

            $normalizedStatus = ProductStatus::normalize($currentStatus);
            $m->status = $normalizedStatus ?? ProductStatus::Undefined;

            $idx = $m->purchase_index;
            if (!is_numeric($idx) || (int) $idx < 1)
                $m->purchase_index = 1;
            else
                $m->purchase_index = (int) $idx;

            if ($m->{BC::COL_BS_PRC} === null || $m->{BC::COL_BS_PRC} < 0)
                $m->{BC::COL_BS_PRC} = 0.0000;

            if ($m->discount === null || $m->discount < 0)
                $m->discount = 0.0000;
            if ($m->discount > $m->{BC::COL_BS_PRC})
                $m->discount = $m->{BC::COL_BS_PRC};

            $m->attributes = self::normalizeArrayField($m->attributes ?? null);

            if ($m->product_service_id) {
                $product = $m->relationLoaded('productService')
                    ? $m->productService
                    : ProductService::find($m->product_service_id);

                if ($product) {
                    $baseName = trim((string) $product->name);
                    $m->name  = self::buildUnitName(
                        $baseName,
                        (string) ($m->getOriginal('name') ?? $m->name)
                    );

                    if (!$m->{AC::COL_MUNIT}) {
                        $units = self::normalizeArrayField($product->{BC::COL_AC_MUNITS} ?? null);
                        $firstUnit = $units ? reset($units) : null;
                        if (is_string($firstUnit) && trim($firstUnit) !== '')
                            $m->{AC::COL_MUNIT} = trim($firstUnit);
                    }

                    if (!$m->{BC::COL_CUR_ID}) {
                        $currencies = self::normalizeArrayField($product->{BC::COL_AC_CUR} ?? null);
                        $firstCur   = $currencies ? reset($currencies) : null;
                        $code       = $firstCur && is_string($firstCur)
                            ? strtoupper(substr(trim($firstCur), 0, 3))
                            : strtoupper(SC::DEF_SITE_CURRENCY_ID);
                        $m->{BC::COL_CUR_ID} = $code;
                    }
                }
            }

            if ($m->{BC::COL_CUR_ID})
                $m->{BC::COL_CUR_ID} = strtoupper(substr((string) $m->{BC::COL_CUR_ID}, 0, 3));
        });
    }

    protected static function normalizeArrayField(mixed $value): array
    {
        if ($value === null)
            return [];

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : (array) $value;
    }

    protected static function buildUnitName(string $baseName, string $currentName): string
    {
        $baseName    = trim($baseName);
        $currentName = trim($currentName);
        $separator   = ' — ';

        if ($baseName === '') {
            if ($currentName === '')
                return (string) Str::uuid();

            return $currentName;
        }

        if ($currentName === '' || strpos($currentName, $baseName) === false) {
            $suffix = (string) Str::uuid();
            return $baseName . $separator . $suffix;
        }

        $prefix = $baseName . $separator;
        if (str_starts_with($currentName, $prefix)) {
            $suffix = trim(substr($currentName, strlen($prefix)));
            if ($suffix === '')
                $suffix = (string) Str::uuid();

            return $baseName . $separator . $suffix;
        }

        if (str_starts_with($currentName, $baseName)) {
            $rest = trim(substr($currentName, strlen($baseName)));
            $rest = ltrim($rest, "-— \t");
            $suffix = $rest !== '' ? $rest : (string) Str::uuid();
            return $baseName . $separator . $suffix;
        }

        return $baseName . $separator . Str::uuid()->toString();
    }

    public static function statusLabels(): array
    {
        return ProductStatus::labels();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function getPriceWithDiscountAttribute(): float
    {
        return round(
            (float) $this->{BC::COL_BS_PRC} - (float) $this->discount,
            4
        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', DC::TABLE_CREATOR);
        // * considerar belongsTo(User::class, DC::TABLE_CREATOR, 'id')
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, 'product_service_id', 'id');
    }
}
