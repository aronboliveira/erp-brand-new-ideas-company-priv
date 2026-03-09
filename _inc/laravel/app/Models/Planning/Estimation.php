<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
};
use App\Enums\{
    BillStatus,
    FinancialEstimationStatus
};
use App\Services\BusinessRequestService;
use App\Traits\{
    DefinesDates,
    HasAuditFields,
    NormalizesAddresses,
    StoresManyRefJson,
    UsesCountryRegions,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    BelongsToMany
};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Log, Schema};

/**
 * @property bool|null $is_signed
 * @property string|\Illuminate\Support\Carbon|null $signed_at
 * @property mixed $requests_signature
 */
class Estimation extends Model
{
    use UsesUuids,
        HasAuditFields,
        NormalizesAddresses,
        UsesCountryRegions,
        StoresManyRefJson,
        DefinesDates;

    protected $table = DC::TABLE_EST;

    protected $fillable = [
        BC::COL_EST_ID,
        PJC::COL_CLIENT_ID,
        PJC::COL_PJ_ID,
        BC::COL_TAX_ID,

        'amount',
        'discount',
        BC::COL_CUR_ID,
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        'reference',
        'description',
        'notes',

        BC::COL_BL_NAME,
        BC::COL_BL_EMAIL,
        BC::COL_BL_TEL,
        BC::COL_BL_ZIP,
        BC::COL_BL_ADR,
        BC::COL_BL_ST,
        BC::COL_BL_CTY,
        BC::COL_BL_CTR,
        BC::COL_BL_DTL,

        BC::COL_SHIP_NAME,
        BC::COL_SHIP_EMAIL,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_ZIP,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_DTL,

        BC::COL_SD_DT,
        PJC::COL_D_DATE,
        BC::COL_CAT_ID,
        BC::COL_STT_LB,
        BC::COL_DSC_APL,
        'taxes',

        BC::COL_ISS_DT,
        BC::COL_REF_N,
        BC::COL_VLD_TO,
        BC::COL_RQ_SIGN,
        BC::COL_IS_SIGN,
        BC::COL_SIGN_AT,
        BC::COL_SIGN_BY,
        BC::COL_SIGN_BY_NAME,
        BC::COL_VW_AT,

        'payments',
        'status',
        'terms',

        'attachments',
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR
    ];

    protected $casts = [
        BC::COL_ISS_DT  => 'date',
        BC::COL_SD_DT   => 'date',
        PJC::COL_D_DATE => 'date',

        BC::COL_VLD_TO  => 'datetime',
        BC::COL_VW_AT   => 'datetime',
        BC::COL_SIGN_AT => 'datetime',

        'amount'        => 'decimal:2',
        'discount'      => 'decimal:2',
        BC::COL_SVC_FEE => 'decimal:2',
        BC::COL_TXS_FEE => 'decimal:2',

        BC::COL_AUTORCC => 'boolean',
        BC::COL_DSC_APL => 'boolean',
        BC::COL_RQ_SIGN => 'boolean',
        BC::COL_IS_SIGN => 'boolean',

        BC::COL_STT_LB  => BillStatus::class,
        'status'        => FinancialEstimationStatus::class,

        'attachments'   => 'array',
        BC::COL_TC      => 'array',
        BC::COL_RCC_RL  => 'array',
        'taxes'         => 'array',
        'payments'      => 'array',
    ];

    protected $with = [
        'project',
        'tax',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            if (empty($m->getAttribute(BC::COL_STT_LB)))
                $m->setAttribute(BC::COL_STT_LB, BillStatus::Draft->value);
            $m->setAttribute('status', FinancialEstimationStatus::normalize($m->getAttribute('status') ?? null));
            $amount   = (float) ($m->getAttribute('amount') ?? 0.0);
            $discount = (float) ($m->getAttribute('discount') ?? 0.0);
            if ($discount < 0.0)
                $discount = 0.0;
            elseif ($discount > $amount)
                $discount = $amount;
            $m->setAttribute('amount', $amount);
            $m->setAttribute('discount', $discount);
            $ownerId = $m->getAttribute(PJC::COL_CLIENT_ID) ?? null;
            self::normalizeBillingCountry($m);
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            if ($isNormalizeEmailCallable) {
                if (!empty($m->getAttribute(BC::COL_BL_EMAIL)))
                    $m->setAttribute(BC::COL_BL_EMAIL, self::normalizeEmail(
                        $m->getAttribute(BC::COL_BL_EMAIL),
                        'estimation_billing',
                        $ownerId
                    ));

                if (!empty($m->getAttribute(BC::COL_SHIP_EMAIL)))
                    $m->setAttribute(BC::COL_SHIP_EMAIL, self::normalizeEmail(
                        $m->getAttribute(BC::COL_SHIP_EMAIL),
                        'estimation_shipping',
                        $ownerId
                    ));
            }
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            if ($isNormalizePhoneCallable) {
                if (!empty($m->getAttribute(BC::COL_BL_TEL)))
                    $m->setAttribute(BC::COL_BL_TEL, self::normalizePhone(
                        $m->getAttribute(BC::COL_BL_TEL),
                        'estimation_billing',
                        $ownerId
                    ));
                if (!empty($m->getAttribute(BC::COL_SHIP_TEL)))
                    $m->setAttribute(BC::COL_SHIP_TEL, self::normalizePhone(
                        $m->getAttribute(BC::COL_SHIP_TEL),
                        'estimation_shipping',
                        $ownerId
                    ));
            }
            $isNormalizeZipCallable = is_callable([self::class, 'normalizeZip']);
            if ($isNormalizeZipCallable) {
                if (!empty($m->getAttribute(BC::COL_BL_ZIP)) && !empty($m->getAttribute(BC::COL_BL_CTR)))
                    $m->setAttribute(BC::COL_BL_ZIP, self::normalizeZip(
                        $m->getAttribute(BC::COL_BL_ZIP),
                        $m->getAttribute(BC::COL_BL_CTR),
                        'estimation_billing',
                        $ownerId
                    ));
                if (!empty($m->getAttribute(BC::COL_SHIP_ZIP)) && !empty($m->getAttribute(BC::COL_SHIP_CTR)))
                    $m->setAttribute(BC::COL_SHIP_ZIP, self::normalizeZip(
                        $m->getAttribute(BC::COL_SHIP_ZIP),
                        $m->getAttribute(BC::COL_SHIP_CTR),
                        'estimation_shipping',
                        $ownerId
                    ));
            }
            $m->setAttribute('attachments', static::normalizeArrayField($m->getAttribute('attachments') ?? null));
            $m->setAttribute('taxes', static::normalizeArrayField($m->getAttribute('taxes') ?? null));
            $m->setAttribute(BC::COL_TC, static::normalizeArrayField($m->getAttribute(BC::COL_TC) ?? null));
            $m->setAttribute(BC::COL_RCC_RL, static::normalizeArrayField($m->getAttribute(BC::COL_RCC_RL) ?? null));
            $m->setAttribute('payments', static::normalizePaymentIds($m->getAttribute('payments') ?? null));
        });
    }

    protected static function normalizePaymentIds(mixed $value): ?array
    {
        $ids = static::normalizeArrayField($value);

        if ($ids === null)
            return null;

        $ids = array_values(array_unique(array_filter(
            array_map('strval', $ids),
            fn(string $v): bool => $v !== ''
        )));

        if (!$ids)
            return null;

        try {
            $existing = Payment::query()
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            $existing = array_map('strval', $existing);
            $final    = array_values(array_intersect($ids, $existing));

            return $final ?: null;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to normalize payments array', [
                'ids'   => $ids,
                'error' => $e->getMessage(),
            ]);

            return $ids ?: null;
        }
    }

    public function client(): ?BelongsTo
    {
        return Utility::getClient($this);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_SIGN_BY, 'id');
    }

    /**
     * Get product services through junction table
     * 
     * @return BelongsToMany
     */
    public function productServices(): BelongsToMany
    {
        try {
            $junctionTable = 'estimation_products';
            $estimationColumnName = null;
            $productServiceColumnName = null;

            if (Schema::hasColumn($junctionTable, BC::COL_EST_ID))
                $estimationColumnName = BC::COL_EST_ID;
            elseif (Schema::hasColumn($junctionTable, 'estimation'))
                $estimationColumnName = 'estimation';

            if (Schema::hasColumn($junctionTable, BC::COL_PRD_SV_ID))
                $productServiceColumnName = BC::COL_PRD_SV_ID;
            elseif (Schema::hasColumn($junctionTable, 'product_service'))
                $productServiceColumnName = 'product_service';

            if (!$estimationColumnName || !$productServiceColumnName) {
                Log::error('Estimation::productServices - Missing required columns in junction table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $junctionTable,
                    'estimation_column' => $estimationColumnName,
                    'product_service_column' => $productServiceColumnName
                ]);
                return $this->belongsToMany(ProductService::class, $junctionTable, BC::COL_EST_ID, BC::COL_PRD_SV_ID)
                    ->withPivot('id', 'price', 'quantity', 'description');
            }

            Log::debug('Estimation::productServices - Using columns for belongsToMany', [
                'class' => static::class,
                'estimation_column' => $estimationColumnName,
                'product_service_column' => $productServiceColumnName
            ]);

            return $this->belongsToMany(ProductService::class, $junctionTable, $estimationColumnName, $productServiceColumnName)
                ->withPivot('id', 'price', 'quantity', 'description');
        } catch (\Throwable $e) {
            Log::error('Estimation::productServices - Failed to determine junction columns', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage()
            ]);

            return $this->belongsToMany(ProductService::class, 'estimation_products', BC::COL_EST_ID, BC::COL_PRD_SV_ID)
                ->withPivot('id', 'price', 'quantity', 'description');
        }
    }

    /**
     * Get products through junction table
     * 
     * @return BelongsToMany
     */
    public function productProducts(): BelongsToMany
    {
        try {
            $junctionTable = 'estimation_products';
            $estimationColumnName = null;
            $productColumnName = null;

            if (Schema::hasColumn($junctionTable, BC::COL_EST_ID))
                $estimationColumnName = BC::COL_EST_ID;
            elseif (Schema::hasColumn($junctionTable, 'estimation'))
                $estimationColumnName = 'estimation';

            if (Schema::hasColumn($junctionTable, BC::COL_PRD_ID))
                $productColumnName = BC::COL_PRD_ID;
            elseif (Schema::hasColumn($junctionTable, 'product'))
                $productColumnName = 'product';

            if (!$estimationColumnName || !$productColumnName) {
                Log::error('Estimation::productProducts - Missing required columns in junction table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $junctionTable,
                    'estimation_column' => $estimationColumnName,
                    'product_column' => $productColumnName
                ]);
                return $this->belongsToMany(Product::class, $junctionTable, BC::COL_EST_ID, BC::COL_PRD_ID)
                    ->withPivot('id', 'price', 'quantity', 'description');
            }

            Log::debug('Estimation::productProducts - Using columns for belongsToMany', [
                'class' => static::class,
                'estimation_column' => $estimationColumnName,
                'product_column' => $productColumnName
            ]);

            return $this->belongsToMany(Product::class, $junctionTable, $estimationColumnName, $productColumnName)
                ->withPivot('id', 'price', 'quantity', 'description');
        } catch (\Throwable $e) {
            Log::error('Estimation::productProducts - Failed to determine junction columns', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage()
            ]);

            return $this->belongsToMany(Product::class, 'estimation_products', BC::COL_EST_ID, BC::COL_PRD_ID)
                ->withPivot('id', 'price', 'quantity', 'description');
        }
    }

    /**
     * Get all products (merged from both Product and ProductService via UNION)
     * 
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        try {
            $junctionTable = 'estimation_products';
            $productServicesTable = (new ProductService)->getTable();
            $productsTable = (new Product)->getTable();

            $estimationForeignKeyAlias = BC::COL_EST_ID;

            $estimationForeignKeyInJunction = Schema::hasColumn($junctionTable, BC::COL_EST_ID)
                ? BC::COL_EST_ID
                : (Schema::hasColumn($junctionTable, 'estimation') ? 'estimation' : null);

            $productServiceForeignKeyInJunction = Schema::hasColumn($junctionTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($junctionTable, 'product_service') ? 'product_service' : null);

            $productForeignKeyInJunction = Schema::hasColumn($junctionTable, BC::COL_PRD_ID)
                ? BC::COL_PRD_ID
                : (Schema::hasColumn($junctionTable, 'product') ? 'product' : null);

            if (!$estimationForeignKeyInJunction || (!$productServiceForeignKeyInJunction && !$productForeignKeyInJunction)) {
                Log::error('Estimation::products - Missing required FK columns in junction table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'junction_table' => $junctionTable,
                    'estimation_fk' => $estimationForeignKeyInJunction,
                    'product_service_fk' => $productServiceForeignKeyInJunction,
                    'product_fk' => $productForeignKeyInJunction,
                ]);

                return $this->belongsToMany(Product::class, $junctionTable, $estimationForeignKeyAlias, BC::COL_PRD_ID)
                    ->withPivot('id', 'price', 'quantity', 'description');
            }

            $productServicesColumns = Schema::getColumnListing($productServicesTable);
            $productsColumns = Schema::getColumnListing($productsTable);

            $unionColumns = array_values(array_unique(array_merge(
                $productServicesColumns,
                $productsColumns,
                ['_source']
            )));

            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $productServicesSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $productServicesSelect[] = "'product_services' as `_source`";
                    continue;
                }
                $productServicesSelect[] = \in_array($column, $productServicesColumns, true)
                    ? "`{$productServicesTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $productsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $productsSelect[] = "'products' as `_source`";
                    continue;
                }
                $productsSelect[] = \in_array($column, $productsColumns, true)
                    ? "`{$productsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $derivedAlias = 'products_union';

            $unionSql =
                "SELECT " . implode(', ', $productServicesSelect) . " FROM `{$productServicesTable}` " .
                "UNION ALL " .
                "SELECT " . implode(', ', $productsSelect) . " FROM `{$productsTable}`";

            $relation = $this->belongsToMany(Product::class, $junctionTable, $estimationForeignKeyInJunction, 'id', 'id', 'id')
                ->withPivot('id', 'price', 'quantity', 'description');

            $relation->getQuery()
                ->join(DB::raw("({$unionSql}) as `{$derivedAlias}`"), function ($join) use ($junctionTable, $productServiceForeignKeyInJunction, $productForeignKeyInJunction, $derivedAlias) {
                    if ($productServiceForeignKeyInJunction)
                        $join->on("{$junctionTable}.{$productServiceForeignKeyInJunction}", '=', "{$derivedAlias}.id");
                    if ($productForeignKeyInJunction)
                        $join->orOn("{$junctionTable}.{$productForeignKeyInJunction}", '=', "{$derivedAlias}.id");
                });

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Estimation::products - Failed to build union-backed BelongsToMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'estimation_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->belongsToMany(Product::class, 'estimation_products', BC::COL_EST_ID, BC::COL_PRD_ID)
                ->withPivot('id', 'price', 'quantity', 'description');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function getProducts()
    {
        return $this->products();
    }

    public function relatedPayments(): Collection
    {
        $ids = $this->payments ?? [];

        if (!is_array($ids) || !$ids)
            return collect();

        return Payment::query()
            ->whereIn('id', array_map('strval', $ids))
            ->get();
    }

    public function getSubTotal(): float
    {
        $items = $this->getProducts()->get();

        if ($items->isEmpty())
            return (float) ($this->amount ?? 0.0);

        return (float) $items->sum(
            fn($product): float => (float) $product->pivot->price * (float) $product->pivot->quantity // @phpstan-ignore property.notFound, property.notFound
        );
    }

    public function getTax(): float
    {
        $sub      = $this->getSubTotal();
        $discount = (float) ($this->discount ?? 0.0);
        $base     = max(0.0, $sub - $discount);

        if ($base <= 0.0)
            return 0.0;

        $rate = (float) ($this->tax->rate ?? 0.0);

        return ($base * $rate) / 100.0;
    }

    public function getTotal(): float
    {
        $total = $this->getSubTotal() - (float) ($this->discount ?? 0.0) + $this->getTax();

        return $total < 0.0 ? 0.0 : (float) $total;
    }

    public function getDue(): float
    {
        $total = $this->getTotal();
        $paid  = (float) $this->relatedPayments()->sum('amount');
        $due   = $total - $paid;

        return $due < 0.0 ? 0.0 : $due;
    }

    public function isExpired(?Carbon $at = null): bool
    {
        $validTo = $this->getAttribute(BC::COL_VLD_TO) ?? null;
        if (!$validTo)
            return false;
        $at = $at ?? now();
        return $at->greaterThan(Carbon::parse($validTo));
    }

    public function requiresSignature(): bool
    {
        return (bool) $this->{BC::COL_RQ_SIGN};
    }

    public function isSigned(): bool
    {
        return (bool) $this->{BC::COL_IS_SIGN};
    }

    public function hasBeenViewed(): bool
    {
        return !empty($this->{BC::COL_VW_AT});
    }

    public function getSignatureData(): array
    {
        return [
            'is_signed' => $this->isSigned(),
            'signed_at' => $this->{BC::COL_SIGN_AT} ? Carbon::parse($this->{BC::COL_SIGN_AT}) : '#NO_DATE',
            'signed_by' => User::whereKey($this->signer)->first()?->only(['id', 'name', 'email']) ? $this->signer : '#UNDEFINED',
        ];
    }

    public static function status(?string $lang = null): array
    {
        return array_values(FinancialEstimationStatus::labels($lang));
    }

    public static function getEstimationSummary(iterable $estimates): string|RedirectResponse
    {
        return app(BusinessRequestService::class)->getEstimationSummary($estimates);
    }
}
