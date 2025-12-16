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
    NormalizesArrays,
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
    use HasAuditFields, HasFactory, NormalizesArrays, SoftDeletes, UsesUuids;

    protected $table = DC::TABLE_PROD_SERV_UNITS;

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
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
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
                if (!empty($m->getAttribute($field)) && is_string($m->getAttribute($field)))
                    $m->setAttribute($field, trim($m->getAttribute($field)));
            $currentStatus = $m->getAttribute('status') instanceof ProductStatus
                ? $m->getAttribute('status')->value
                : (string) $m->getAttribute('status');
            $normalizedStatus = ProductStatus::normalize($currentStatus);
            $m->setAttribute('status', $normalizedStatus ?? ProductStatus::Undefined);
            $idx = $m->getAttribute('purchase_index');
            if (!is_numeric($idx) || (int) $idx < 1)
                $m->setAttribute('purchase_index', 1);
            else
                $m->setAttribute('purchase_index', (int) $idx);
            if ($m->getAttribute(BC::COL_BS_PRC) === null || $m->getAttribute(BC::COL_BS_PRC) < 0)
                $m->setAttribute(BC::COL_BS_PRC, 0.0000);
            if ($m->getAttribute('discount') === null || $m->getAttribute('discount') < 0)
                $m->setAttribute('discount', 0.0000);
            if ($m->getAttribute('discount') > $m->getAttribute(BC::COL_BS_PRC))
                $m->setAttribute('discount', $m->getAttribute(BC::COL_BS_PRC));
            $m->setAttribute('attributes', self::normalizeArrayField($m->getAttribute('attributes') ?? null));
            if ($m->getAttribute('product_service_id')) {
                $product = $m->relationLoaded('productService')
                    ? $m->productService
                    : ProductService::find($m->getAttribute('product_service_id'));
                if ($product) {
                    $baseName = trim((string) $product->getAttribute('name'));
                    $m->name  = self::buildUnitName(
                        $baseName,
                        (string) ($m->getOriginal('name') ?? $m->getAttribute('name'))
                    );
                    if (!$m->getAttribute(AC::COL_MUNIT)) {
                        $units = self::normalizeArrayField($product->getAttribute(BC::COL_AC_MUNITS) ?? null);
                        $firstUnit = $units ? reset($units) : null;
                        if (is_string($firstUnit) && trim($firstUnit) !== '')
                            $m->setAttribute(AC::COL_MUNIT, trim($firstUnit));
                    }
                    if (!$m->getAttribute(BC::COL_CUR_ID)) {
                        $currencies = self::normalizeArrayField($product->getAttribute(BC::COL_AC_CUR) ?? null);
                        $firstCur   = $currencies ? reset($currencies) : null;
                        $code       = $firstCur && is_string($firstCur)
                            ? strtoupper(substr(trim($firstCur), 0, 3))
                            : strtoupper(SC::DEF_SITE_CURRENCY_ID);
                        $m->setAttribute(BC::COL_CUR_ID, $code);
                    }
                }
            }
            if ($m->getAttribute(BC::COL_CUR_ID))
                $m->setAttribute(BC::COL_CUR_ID, strtoupper(substr((string) $m->getAttribute(BC::COL_CUR_ID), 0, 3)));
        });
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
        return $this->hasOne(User::class, 'id', DC::COL_TABLE_CREATOR);
        // * considerar belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id')
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, 'product_service_id', 'id');
    }
}
