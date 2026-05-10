<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BanksConstants as BKC,
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Enums\{ConsumableType};
use App\Services\{ProductOrServiceRequestService};
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Http\RedirectResponse;

class ProductServiceCategory extends Model
{
    use HasFactory, HasAuditFields, NormalizesArrays, UsesUuids;

    public const TABLE = DC::TABLE_PROD_SERV_CATS;

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'code',
        'type',
        DC::COL_TP_LB,
        BKC::COL_COA,
        'color',
        'icon',
        'attributes',
        'description',
        DC::COL_RL_CAT,
        'notes',
        AC::COL_IA,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'type'          => 'integer',
        DC::COL_TP_LB   => ConsumableType::class,
        'attributes'    => 'array',
        DC::COL_RL_CAT  => 'array',
        AC::COL_IA      => 'boolean',
    ];

    public static array $categoryType = [
        'Product & Service',
        'Income',
        'Expense',
    ];

    public static array $catTypes = [
        'product & service'   => 'Product & Service',
        'income'              => 'Income',
        'expense'             => 'Expense',
        'asset'               => 'Asset',
        'liability'           => 'Liability',
        'equity'              => 'Equity',
        'costs of good sold'  => 'Costs of Goods Sold',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            foreach (['name', 'code', 'color', 'icon'] as $field)
                if (!empty($m->getAttribute($field)) && is_string($m->getAttribute($field)))
                    $m->setAttribute($field, trim($m->getAttribute($field)));
            $m->setAttribute('type', self::normalizeType($m->getAttribute('type') ?? null));
            $label = $m->getAttribute(DC::COL_TP_LB) ?? null;
            if ($label instanceof ConsumableType)
                $normalized = $label;
            else
                $normalized = ConsumableType::normalize(is_string($label) ? $label : null);
            $allowed = [
                ConsumableType::Product,
                ConsumableType::Service,
                ConsumableType::Other,
            ];
            if (!$normalized || !in_array($normalized, $allowed, true))
                $normalized = ConsumableType::Service;
            $m->setAttribute(DC::COL_TP_LB, $normalized);
            if ($m->getAttribute('color') === null || $m->getAttribute('color') === '')
                $m->setAttribute('color', '#fc544b');
            $m->setAttribute('attributes', self::normalizeArrayField($m->getAttribute('attributes') ?? null));
            $m->setAttribute(DC::COL_RL_CAT, self::normalizeAndFilterRelatedCategories(
                $m->getAttribute(DC::COL_RL_CAT) ?? null
            ));
            if ($m->getAttribute(AC::COL_IA) === null)
                $m->setAttribute(AC::COL_IA, true);
        });
    }

    protected static function normalizeType(mixed $value): int
    {
        try {
            if ($value === null)
                return 0;
            if (is_string($value))
                $value = trim($value);
            if (!is_numeric($value))
                return 0;
            $int = (int) $value;
            if ($int < 0)
                $int = 0;
            elseif ($int > 9)
                $int = 9;
            return $int;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeType — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return 0;
        }
    }

    protected static function normalizeAndFilterRelatedCategories(mixed $value): array
    {
        try {
            $items = self::normalizeArrayField($value);
            $items = array_values(array_filter(
                $items,
                fn($item): bool =>
                is_array($item) && isset($item['id']) && is_string($item['id']) && trim($item['id']) !== ''
            ));
            if (!$items)
                return [];
            $ids = array_values(array_unique(array_map(
                fn(array $item): string => $item['id'],
                $items
            )));
            $existingIds = self::query()
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();
            if (!$existingIds)
                return [];
            $existingMap = array_flip($existingIds);
            $filtered = [];
            foreach ($items as $item) {
                $id = $item['id'];
                if (isset($existingMap[$id]))
                    $filtered[] = $item;
            }
            return $filtered;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeAndFilterRelatedCategories — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Revenue::class, BC::COL_CAT_ID, 'id');
            }

    public function incomeCategoryRevenueAmount(): float|RedirectResponse
    {
        return app(ProductOrServiceRequestService::class)->getIncomeCategoryRevenueAmount($this);
    }

    public function expenseCategoryAmount(): float|RedirectResponse
    {
        return app(ProductOrServiceRequestService::class)->getExpenseCategoryAmount($this);
    }

    public static function getAllCategories(): Collection|RedirectResponse
    {
        return app(ProductOrServiceRequestService::class)->getAllCategories();
    }

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, BKC::COL_COA, 'id');
    }
}
