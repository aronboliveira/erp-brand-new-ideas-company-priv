<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BanksConstants as BKC,
    DatabaseConstants as DC
};
use App\Enums\ConsumableType;
use App\Traits\{
    ChecksLogin,
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Collection,
    Model,
    Relations\BelongsTo,
    Relations\HasMany
};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class ProductServiceCategory extends Model
{
    use ChecksLogin, HasAuditFields, NormalizesArrays, UsesUuids;

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
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});

            $m->type = self::normalizeType($m->type ?? null);

            $label = $m->{DC::COL_TP_LB} ?? null;

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

            $m->{DC::COL_TP_LB} = $normalized;

            if ($m->color === null || $m->color === '')
                $m->color = '#fc544b';

            $m->attributes = self::normalizeArrayField($m->attributes);

            $m->{DC::COL_RL_CAT} = self::normalizeAndFilterRelatedCategories(
                $m->{DC::COL_RL_CAT} ?? null
            );

            if ($m->{AC::COL_IA} === null)
                $m->{AC::COL_IA} = true;
        });
    }

    protected static function normalizeType(mixed $value): int
    {
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
    }

    protected static function normalizeAndFilterRelatedCategories(mixed $value): array
    {
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
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Revenue::class, 'category_id', 'id');
        // * relação original mantida para compatibilidade
    }

    public function incomeCategoryRevenueAmount(): float|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;

        $user   = $userOrRedirect;
        $userId = $user?->creatorId();
        $year   = date('Y');

        $revenue = $this->categories()
            ->where(DC::COL_TABLE_CREATOR, $userId)
            ->whereYear('date', $year)
            ->sum('amount');

        $invoices = Invoice::where('category_id', $this->id)
            ->where(DC::COL_TABLE_CREATOR, $userId)
            ->whereYear('send_date', $year)
            ->get();

        $totals = $invoices
            ->map(fn(Invoice $inv) => $inv->getTotal())
            ->all();

        return ($revenue ?: 0) + array_sum($totals);
    }

    public function expenseCategoryAmount(): float|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;

        $user   = $userOrRedirect;
        $userId = $user?->creatorId();
        $year   = date('Y');

        $payment = Payment::where('category_id', $this->id)
            ->where(DC::COL_TABLE_CREATOR, $userId)
            ->whereYear('date', $year)
            ->sum('amount');

        $bills = Bill::where('category_id', $this->id)
            ->where(DC::COL_TABLE_CREATOR, $userId)
            ->whereYear('send_date', $year)
            ->get();

        $totals = $bills
            ->map(fn(Bill $bill) => $bill->getTotal())
            ->all();

        return ($payment ?: 0) + array_sum($totals);
    }

    public static function getAllCategories(): Collection|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;

        $user    = $userOrRedirect;
        $creator = $user?->creatorId();

        return DB::table(self::TABLE . ' as c')
            ->select('c.*', DB::raw('COUNT(p.category_id) as product_services'))
            ->leftJoin('product_services as p', 'c.id', '=', 'p.category_id')
            ->where('c.' . DC::COL_TABLE_CREATOR, $creator)
            ->where('c.type', 0)
            ->groupBy('c.id')
            ->orderBy('c.id', 'desc')
            ->get();
    }

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, BKC::COL_COA, 'id');
    }
}
