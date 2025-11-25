<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Enums\ProductStatus;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class ProductServiceUnit extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, SoftDeletes;

    public const TABLE = DC::TABLE_PROD_SERV_UNITS;

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'code',
        'status',
        AC::COL_MUNIT,
        'quantity',
        BC::COL_BS_PRC,
        BC::COL_CUR_ID,
        'attributes',
        'categories',
        'description',
        'notes',
        AC::COL_AV_FROM,
        AC::COL_AV_UNTIL,
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        'quantity'           => 'integer',
        BC::COL_BS_PRC       => 'decimal:4',
        BC::COL_CUR_ID       => 'string',
        'attributes'         => 'array',
        'categories'         => 'array',
        AC::COL_AV_FROM      => 'datetime',
        AC::COL_AV_UNTIL     => 'datetime',
        'status'             => ProductStatus::class,
    ];

    protected $with = [
        'user',
    ];

    protected $appends = [
        'is_active',
        'is_available',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            foreach (['name', 'code', BC::COL_CUR_ID, AC::COL_MUNIT] as $field)
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});
            $normalized = ProductStatus::normalize($m->status);
            $m->status = $normalized ?? ProductStatus::Undefined;
            if ($m->quantity === null || $m->quantity < 1)
                $m->quantity = 1;
            if ($m->{BC::COL_BS_PRC} === null || $m->{BC::COL_BS_PRC} < 0)
                $m->{BC::COL_BS_PRC} = 0.0000;
            $from  = $m->{AC::COL_AV_FROM};
            $until = $m->{AC::COL_AV_UNTIL};
            if ($from && $until && $until < $from)
                $m->{AC::COL_AV_UNTIL} = $from;
            try {
                $m->categories = self::sanitizeCategories($m->categories ?? null);
            } catch (\Exception $e) {
                Log::warning('Failed to normalize categories for ProductServiceUnit ' . $m->id, [
                    'error' => $e->getMessage(),
                ]);
                $m->categories = [];
            }
        });
    }

    protected static function sanitizeCategories(mixed $raw): ?array
    {
        if ($raw === null)
            return null;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $items   = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) $items = $raw;
        else $items = [];
        if (!$items) return null;
        $valid = [];
        foreach ($items as $item) {
            if (!is_array($item))
                continue;
            if (!self::categoryHasRequiredKey($item))
                continue;
            $valid[] = $item;
        }
        return $valid ?: null;
    }

    protected static function categoryHasRequiredKey(array $item): bool
    {
        foreach (['key', 'id', 'category_id'] as $k) {
            if (!array_key_exists($k, $item))
                continue;
            $value = $item[$k];
            if ($value === null)
                continue;
            if (is_string($value) && trim($value) === '')
                continue;
            if (ProductServiceCategory::where('id', $value)->exists())
                return true;
        }
        return false;
    }

    public static function statusLabels(): array
    {
        return ProductStatus::labels();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function getIsAvailableAttribute(): bool
    {
        $now   = now();
        $from  = $this->{AC::COL_AV_FROM};
        $until = $this->{AC::COL_AV_UNTIL};

        return $this->status === ProductStatus::Active
            && (!$from || $from <= $now)
            && (!$until || $until >= $now);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', DC::TABLE_CREATOR);
        // * consider belongsTo(User::class, DC::TABLE_CREATOR, 'id')
    }
}
