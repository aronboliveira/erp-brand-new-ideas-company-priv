<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{PaymentStatus, PurchaseStatus};
use App\Services\PurchaseRequestService;
use App\Traits\{
    FiltersSecureAttachments,
    HasAuditFields,
    HasProductSecurityCoverage,
    NormalizesArrays,
    StoresManyRefJson,
    TracksFailures,
    UsesCountryRegions,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\{Collection, Str};

class Purchase extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays, UsesCountryRegions, FiltersSecureAttachments, StoresManyRefJson, HasProductSecurityCoverage, TracksFailures, SoftDeletes;

    protected $table = DC::TABLE_PURCHASES;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        BC::COL_PRC_ID,
        BC::COL_SVC_FEE,
        BC::COL_PRC_NB,
        'source',
        'notes',

        UC::COL_VD_ID,
        BC::COL_CST_ID,
        BC::COL_CAT_ID,
        BC::COL_PRD_SV_ID,
        BC::COL_WRH_ID,
        BC::COL_OD_ID,
        BC::COL_BL_ID,
        BC::COL_INV_ID,
        BC::COL_TAX_ID,

        BC::COL_STT_LB,
        'status',

        BC::COL_PRC_DT,
        BC::COL_SD_DT,

        BC::COL_DSC_APL,
        BC::COL_SHIP_DSP,

        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,

        BC::COL_SHIP_NAME,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_ZIP,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_EMAIL,
        BC::COL_SHIP_DTL,

        'metadata',
        'delivery',
        'attachments',
        'taxes',

        ...self::PRODUCT_SECURITY_COLUMNS,
        ...self::FAILURE_TRACKING_COLS,
    ];

    protected $casts = [
        BC::COL_PRC_DT => 'datetime',
        BC::COL_SD_DT  => 'datetime',

        BC::COL_SVC_FEE => 'float',
        BC::COL_PRC_NB  => 'integer',

        BC::COL_DSC_APL  => 'boolean',
        BC::COL_SHIP_DSP => 'boolean',

        BC::COL_NFE_AUTH_AT => 'datetime',

        'status' => 'integer',

        'metadata'     => 'array',
        'delivery'     => 'array',
        'attachments'  => 'array',
        'taxes'        => 'array',

        DC::COL_LST_RTR_AT => 'datetime',
        DC::COL_FL_AT  => 'datetime',
        DC::COL_RTR_CT => 'integer',
        DC::COL_ER_LG  => 'array'
    ];

    protected $attributes = [
        'status' => 0,
        BC::COL_SVC_FEE => 0.0,
        DC::COL_RTR_CT => 0,
    ];

    protected $with = [
        'warehouse',
        'tax',
    ];

    protected $appends = [
        'status_enum',
        'subtotal',
        'total_tax',
        'total_discount',
        'total',
        'due',
    ];

    public static array $statuses = [
        'Draft',
        'Pending',
        'Confirmed',
        'Processing',
        'Partially Paid',
        'Paid',
        'Shipped',
        'Partially Shipped',
        'Delivered',
        'Completed',
        'On Hold',
        'Cancelled',
        'Refunded',
        'Partially Refunded',
        'Returned',
        'Failed',
        'Backordered',
        'Pre Order',
        'Awaiting Payment',
        'Awaiting Fulfillment',
        'Awaiting Shipment',
        'Awaiting Pickup',
        'Undefined',
    ];

    private static bool $purchaseIdCachePrimed = false;
    /**
     * Set of already existing purchase_ids (primed once in seed mode).
     * Keys are purchase_id strings; values are true.
     */
    private static array $purchaseIdExistingSet = [];
    /**
     * Set of purchase_ids reserved in the current PHP process (even if not persisted yet).
     */
    private static array $purchaseIdReservedSet = [];
    private static array $aggCache = [];
    private static array $purchaseSchemaTableCache = [];
    private static array $purchaseSchemaColumnCache = [];

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            $m->ensurePurchaseId();
            $m->ensurePurchaseNumber();
            $m->ensurePurchaseDate();
        });
        static::saving(function (self $m): void {
            $m->enforceSendDateGtPurchaseDate();
            $m->normalizePseudoBooleans();
            $m->overrideCategoryFromProductService();
            $m->mirrorShippingToLinkedDocuments();
            $m->mergeTaxesFromLinkedDocuments();
            $m->refreshStatusFields(); // ! STUCKING
            $m->ensureJsonAttributesAreEncoded(['metadata', 'delivery', 'attachments', 'taxes']);
        });
    }

    public function getRouteKeyName(): string
    {
        return BC::COL_PRC_ID;
    }

    public function vendorVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, UC::COL_VD_ID, 'id');
    }

    public function vendorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_VD_ID, 'id');
    }

    public function vendor(): ?BelongsTo
    {
        return Utility::getVendor($this);
    }


    public function vender(): ?BelongsTo
    {
        return $this->vendor();
    }

    public function customer(): ?BelongsTo
    {
        return Utility::getCustomer($this);
    }

    public function productServiceCategory(): ?BelongsTo
    {
        return $this->belongsTo(ProductServiceCategory::class, BC::COL_CAT_ID, 'id');
    }

    public function productCategory(): ?BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, BC::COL_CAT_ID, 'id');
    }

    public function category(): ?BelongsTo
    {
        return Utility::getCategory($this);
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, BC::COL_PRD_SV_ID, 'id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, BC::COL_WRH_ID, 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, BC::COL_OD_ID, 'id');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, BC::COL_BL_ID, 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, BC::COL_INV_ID, 'id');
    }

    public function taxRef(): BelongsTo
    {
        return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
    }

    public function items(): HasMany
    {
        return $this->products();
    }

    public function purchaseProducts(): HasMany
    {
        try {
            $purchaseProductsTable = (new PurchaseProduct)->getTable();
            $purchaseForeignKeyInPurchaseProducts = null;

            if (Schema::hasColumn($purchaseProductsTable, BC::COL_PRC_ID)) $purchaseForeignKeyInPurchaseProducts = BC::COL_PRC_ID;
            elseif (Schema::hasColumn($purchaseProductsTable, 'purchase')) $purchaseForeignKeyInPurchaseProducts = 'purchase';

            if (!$purchaseForeignKeyInPurchaseProducts) {
                Log::error('Purchase::purchaseProducts - No valid purchase FK column found in purchase_products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $purchaseProductsTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(PurchaseProduct::class, BC::COL_PRC_ID, $this->getKeyName());
            }

            return $this->hasMany(PurchaseProduct::class, $purchaseForeignKeyInPurchaseProducts, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Purchase::purchaseProducts - Failed to determine purchase FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(PurchaseProduct::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function productProducts(): HasMany
    {
        try {
            $productsTable = (new Product)->getTable();
            $purchaseForeignKeyInProducts = null;

            if (Schema::hasColumn($productsTable, BC::COL_PRC_ID)) $purchaseForeignKeyInProducts = BC::COL_PRC_ID;
            elseif (Schema::hasColumn($productsTable, 'purchase')) $purchaseForeignKeyInProducts = 'purchase';

            if (!$purchaseForeignKeyInProducts) {
                Log::error('Purchase::productProducts - No valid purchase FK column found in products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $productsTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(Product::class, BC::COL_PRC_ID, $this->getKeyName());
            }

            return $this->hasMany(Product::class, $purchaseForeignKeyInProducts, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Purchase::productProducts - Failed to determine purchase FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Product::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function products(): HasMany
    {
        try {
            $purchaseProductsTable = (new PurchaseProduct)->getTable();
            $productsTable = (new Product)->getTable();

            $purchaseForeignKeyAlias = BC::COL_PRC_ID;

            $purchaseForeignKeyInPurchaseProducts = Schema::hasColumn($purchaseProductsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchaseProductsTable, 'purchase') ? 'purchase' : null);

            $purchaseForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($productsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKeyInPurchaseProducts || !$purchaseForeignKeyInProducts) {
                Log::error('Purchase::products - Missing purchase FK column in one or both source tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'purchase_products_table' => $purchaseProductsTable,
                    'products_table' => $productsTable,
                    'purchase_products_fk' => $purchaseForeignKeyInPurchaseProducts,
                    'products_fk' => $purchaseForeignKeyInProducts,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(Product::class, $purchaseForeignKeyAlias, $this->getKeyName());
            }

            $purchaseProductsColumns = Schema::getColumnListing($purchaseProductsTable);
            $productsColumns = Schema::getColumnListing($productsTable);

            $unionColumns = array_values(array_unique(array_merge(
                $purchaseProductsColumns,
                $productsColumns,
                [$purchaseForeignKeyAlias, '_source']
            )));

            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $purchaseProductsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $purchaseProductsSelect[] = "'purchase_products' as `_source`";
                    continue;
                }
                if ($column === $purchaseForeignKeyAlias) {
                    $purchaseProductsSelect[] = "`{$purchaseProductsTable}`.`{$purchaseForeignKeyInPurchaseProducts}` as `{$purchaseForeignKeyAlias}`";
                    continue;
                }
                $purchaseProductsSelect[] = \in_array($column, $purchaseProductsColumns, true)
                    ? "`{$purchaseProductsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $productsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $productsSelect[] = "'products' as `_source`";
                    continue;
                }
                if ($column === $purchaseForeignKeyAlias) {
                    $productsSelect[] = "`{$productsTable}`.`{$purchaseForeignKeyInProducts}` as `{$purchaseForeignKeyAlias}`";
                    continue;
                }
                $productsSelect[] = \in_array($column, $productsColumns, true)
                    ? "`{$productsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $derivedAlias = 'purchase_products_union';

            $unionSql =
                "SELECT " . implode(', ', $purchaseProductsSelect) . " FROM `{$purchaseProductsTable}` " .
                "UNION ALL " .
                "SELECT " . implode(', ', $productsSelect) . " FROM `{$productsTable}`";

            $relation = $this->hasMany(Product::class, $purchaseForeignKeyAlias, $this->getKeyName());
            $relation->getQuery()->from(DB::raw("({$unionSql}) as `{$derivedAlias}`"));

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Purchase::products - Failed to build union-backed HasMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Product::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function productServices(): HasMany
    {
        try {
            $purchaseProductsTable = (new PurchaseProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $purchaseId = $this->getKey();

            $purchaseForeignKeyInPurchaseProducts = Schema::hasColumn($purchaseProductsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchaseProductsTable, 'purchase') ? 'purchase' : null);

            $purchaseForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($productsTable, 'purchase') ? 'purchase' : null);

            $productServiceForeignKeyInPurchaseProducts = Schema::hasColumn($purchaseProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($purchaseProductsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($purchaseProductsTable, 'product_service') ? 'product_service' : null));

            $productServiceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($productsTable, 'product_service') ? 'product_service' : null));

            if ((!$purchaseForeignKeyInPurchaseProducts && !$purchaseForeignKeyInProducts) || (!$productServiceForeignKeyInPurchaseProducts && !$productServiceForeignKeyInProducts)) {
                Log::error('Purchase::productServices - Unable to source product_service ids from either table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'purchase_products_fk' => $purchaseForeignKeyInPurchaseProducts,
                    'products_fk' => $purchaseForeignKeyInProducts,
                    'purchase_products_service_fk' => $productServiceForeignKeyInPurchaseProducts,
                    'products_service_fk' => $productServiceForeignKeyInProducts,
                ]);

                return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');
            }

            $purchaseProductsServiceIdsQuery = null;

            if ($purchaseForeignKeyInPurchaseProducts && $productServiceForeignKeyInPurchaseProducts) {
                $purchaseProductsServiceIdsQuery = DB::table($purchaseProductsTable)
                    ->selectRaw("`{$purchaseProductsTable}`.`{$productServiceForeignKeyInPurchaseProducts}` as `service_id`")
                    ->where("{$purchaseProductsTable}.{$purchaseForeignKeyInPurchaseProducts}", $purchaseId)
                    ->whereNotNull("{$purchaseProductsTable}.{$productServiceForeignKeyInPurchaseProducts}");
            }

            $productsServiceIdsQuery = null;

            if ($purchaseForeignKeyInProducts && $productServiceForeignKeyInProducts) {
                $productsServiceIdsQuery = DB::table($productsTable)
                    ->selectRaw("`{$productsTable}`.`{$productServiceForeignKeyInProducts}` as `service_id`")
                    ->where("{$productsTable}.{$purchaseForeignKeyInProducts}", $purchaseId)
                    ->whereNotNull("{$productsTable}.{$productServiceForeignKeyInProducts}");

                if ($purchaseProductsServiceIdsQuery) {
                    $purchaseProductsServiceIdsSubquery = DB::table($purchaseProductsTable)
                        ->selectRaw("`{$purchaseProductsTable}`.`{$productServiceForeignKeyInPurchaseProducts}`")
                        ->where("{$purchaseProductsTable}.{$purchaseForeignKeyInPurchaseProducts}", $purchaseId)
                        ->whereNotNull("{$purchaseProductsTable}.{$productServiceForeignKeyInPurchaseProducts}");

                    $productsServiceIdsQuery->whereNotIn("{$productsTable}.{$productServiceForeignKeyInProducts}", $purchaseProductsServiceIdsSubquery);
                }
            }

            $serviceIdsUnionQuery = $purchaseProductsServiceIdsQuery
                ? ($productsServiceIdsQuery ? $purchaseProductsServiceIdsQuery->unionAll($productsServiceIdsQuery) : $purchaseProductsServiceIdsQuery)
                : $productsServiceIdsQuery;

            if (!$serviceIdsUnionQuery) return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');

            $servicesQuery = DB::table("{$productServicesTable} as ps")
                ->joinSub($serviceIdsUnionQuery, 'src', 'src.service_id', '=', 'ps.id')
                ->selectRaw("ps.*, ? as `" . BC::COL_PRC_ID . "`", [$purchaseId])
                ->distinct();

            $derivedAlias = 'purchase_product_services_union';

            $related = new ProductService();
            $related->setTable($derivedAlias);

            $derivedQuery = $related->newQuery()->fromSub($servicesQuery, $derivedAlias);

            return $this->newHasMany($derivedQuery, $this, "{$derivedAlias}." . BC::COL_PRC_ID, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Purchase::productServices - Failed to build derived relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');
        }
    }

    public function purchasePayments(): HasMany
    {
        try {
            $purchasePaymentsTable = (new PurchasePayment)->getTable();
            $purchaseForeignKeyInPurchasePayments = null;

            if (Schema::hasColumn($purchasePaymentsTable, BC::COL_PRC_ID)) $purchaseForeignKeyInPurchasePayments = BC::COL_PRC_ID;
            elseif (Schema::hasColumn($purchasePaymentsTable, 'purchase')) $purchaseForeignKeyInPurchasePayments = 'purchase';

            if (!$purchaseForeignKeyInPurchasePayments) {
                Log::error('Purchase::purchasePayments - No valid purchase FK column found in purchase_payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $purchasePaymentsTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(PurchasePayment::class, BC::COL_PRC_ID, $this->getKeyName());
            }

            return $this->hasMany(PurchasePayment::class, $purchaseForeignKeyInPurchasePayments, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Purchase::purchasePayments - Failed to determine purchase FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(PurchasePayment::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function paymentPayments(): HasMany
    {
        try {
            $paymentsTable = (new Payment)->getTable();
            $purchaseForeignKeyInPayments = null;

            if (Schema::hasColumn($paymentsTable, BC::COL_PRC_ID)) $purchaseForeignKeyInPayments = BC::COL_PRC_ID;
            elseif (Schema::hasColumn($paymentsTable, 'purchase')) $purchaseForeignKeyInPayments = 'purchase';

            if (!$purchaseForeignKeyInPayments) {
                Log::error('Purchase::paymentPayments - No valid purchase FK column found in payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $paymentsTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(Payment::class, BC::COL_PRC_ID, $this->getKeyName());
            }

            return $this->hasMany(Payment::class, $purchaseForeignKeyInPayments, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Purchase::paymentPayments - Failed to determine purchase FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Payment::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function payments(): HasMany
    {
        try {
            $purchasePaymentsTable = (new PurchasePayment)->getTable();
            $paymentsTable = (new Payment)->getTable();

            $purchaseForeignKeyAlias = BC::COL_PRC_ID;

            $purchaseForeignKeyInPurchasePayments = Schema::hasColumn($purchasePaymentsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchasePaymentsTable, 'purchase') ? 'purchase' : null);

            $purchaseForeignKeyInPayments = Schema::hasColumn($paymentsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($paymentsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKeyInPurchasePayments || !$purchaseForeignKeyInPayments) {
                Log::error('Purchase::payments - Missing purchase FK column in one or both source tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'purchase_payments_table' => $purchasePaymentsTable,
                    'payments_table' => $paymentsTable,
                    'purchase_payments_fk' => $purchaseForeignKeyInPurchasePayments,
                    'payments_fk' => $purchaseForeignKeyInPayments,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(Payment::class, $purchaseForeignKeyAlias, $this->getKeyName());
            }

            $purchasePaymentsColumns = Schema::getColumnListing($purchasePaymentsTable);
            $paymentsColumns = Schema::getColumnListing($paymentsTable);

            $unionColumns = array_values(array_unique(array_merge(
                $purchasePaymentsColumns,
                $paymentsColumns,
                [$purchaseForeignKeyAlias, '_source']
            )));

            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $purchasePaymentsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $purchasePaymentsSelect[] = "'purchase_payments' as `_source`";
                    continue;
                }
                if ($column === $purchaseForeignKeyAlias) {
                    $purchasePaymentsSelect[] = "`{$purchasePaymentsTable}`.`{$purchaseForeignKeyInPurchasePayments}` as `{$purchaseForeignKeyAlias}`";
                    continue;
                }
                $purchasePaymentsSelect[] = \in_array($column, $purchasePaymentsColumns, true)
                    ? "`{$purchasePaymentsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $paymentsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $paymentsSelect[] = "'payments' as `_source`";
                    continue;
                }
                if ($column === $purchaseForeignKeyAlias) {
                    $paymentsSelect[] = "`{$paymentsTable}`.`{$purchaseForeignKeyInPayments}` as `{$purchaseForeignKeyAlias}`";
                    continue;
                }
                $paymentsSelect[] = \in_array($column, $paymentsColumns, true)
                    ? "`{$paymentsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $derivedAlias = 'purchase_payments_union';

            $unionSql =
                "SELECT " . implode(', ', $purchasePaymentsSelect) . " FROM `{$purchasePaymentsTable}` " .
                "UNION ALL " .
                "SELECT " . implode(', ', $paymentsSelect) . " FROM `{$paymentsTable}`";

            $relation = $this->hasMany(Payment::class, $purchaseForeignKeyAlias, $this->getKeyName());
            $relation->getQuery()->from(DB::raw("({$unionSql}) as `{$derivedAlias}`"));

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Purchase::payments - Failed to build union-backed HasMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Payment::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function lastPurchasePayment(): ?HasOne
    {
        try {
            $purchasePaymentsTable = (new PurchasePayment)->getTable();

            $purchaseForeignKeyInPurchasePayments = Schema::hasColumn($purchasePaymentsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchasePaymentsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKeyInPurchasePayments) {
                Log::error('Purchase::lastPurchasePayment - No valid purchase FK column found in purchase_payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $purchasePaymentsTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasOne(PurchasePayment::class, BC::COL_PRC_ID, $this->getKeyName())->latestOfMany();
            }

            return $this->hasOne(PurchasePayment::class, $purchaseForeignKeyInPurchasePayments, $this->getKeyName())->latestOfMany();
        } catch (\Throwable $e) {
            Log::error('Purchase::lastPurchasePayment - Failed to build relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasOne(PurchasePayment::class, BC::COL_PRC_ID, $this->getKeyName())->latestOfMany();
        }
    }

    public function lastPaymentPayment(): ?HasOne
    {
        try {
            $paymentsTable = (new Payment)->getTable();

            $purchaseForeignKeyInPayments = Schema::hasColumn($paymentsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($paymentsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKeyInPayments) {
                Log::error('Purchase::lastPaymentPayment - No valid purchase FK column found in payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $paymentsTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasOne(Payment::class, BC::COL_PRC_ID, $this->getKeyName())->latestOfMany();
            }

            return $this->hasOne(Payment::class, $purchaseForeignKeyInPayments, $this->getKeyName())->latestOfMany();
        } catch (\Throwable $e) {
            Log::error('Purchase::lastPaymentPayment - Failed to build relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasOne(Payment::class, BC::COL_PRC_ID, $this->getKeyName())->latestOfMany();
        }
    }

    public function lastPayment(): ?HasOne
    {
        try {
            $lastPurchasePaymentRow = $this->lastPurchasePayment()?->first();
            $lastPaymentRow = $this->lastPaymentPayment()?->first();

            if (!$lastPurchasePaymentRow && !$lastPaymentRow) return null;
            if (!$lastPurchasePaymentRow) return $this->lastPaymentPayment();
            if (!$lastPaymentRow) return $this->lastPurchasePayment();

            $purchasePaymentTimestamp = $lastPurchasePaymentRow->created_at ?? $lastPurchasePaymentRow->updated_at ?? null;
            $paymentTimestamp = $lastPaymentRow->created_at ?? $lastPaymentRow->updated_at ?? null;

            if (!$purchasePaymentTimestamp) return $this->lastPaymentPayment();
            if (!$paymentTimestamp) return $this->lastPurchasePayment();

            return $purchasePaymentTimestamp > $paymentTimestamp
                ? $this->lastPurchasePayment()
                : $this->lastPaymentPayment();
        } catch (\Throwable $e) {
            Log::error('Purchase::lastPayment - Failed to resolve last payment', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function lastPayments(): ?HasOne
    {
        return $this->lastPayment();
    }

    public function bankTransfers(): HasMany
    {
        try {
            $bankTransfersTable = (new BankTransfer)->getTable();

            $purchaseForeignKeyInBankTransfers = Schema::hasColumn($bankTransfersTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($bankTransfersTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKeyInBankTransfers) {
                Log::error('Purchase::bankTransfers - No valid purchase FK column found in bank_transfers table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $bankTransfersTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(BankTransfer::class, BC::COL_PRC_ID, $this->getKeyName());
            }

            return $this->hasMany(BankTransfer::class, $purchaseForeignKeyInBankTransfers, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Purchase::bankTransfers - Failed to build relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(BankTransfer::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function invoiceBankTransfers(): HasMany
    {
        try {
            $purchasesTable = $this->getTable();
            $invoicesTable = (new Invoice)->getTable();
            $invoiceBankTransfersTable = (new InvoiceBankTransfer)->getTable();

            $invoiceForeignKeyInPurchase = Schema::hasColumn($purchasesTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($purchasesTable, 'invoice') ? 'invoice' : null);

            $invoiceForeignKeyInInvoiceBankTransfers = Schema::hasColumn($invoiceBankTransfersTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($invoiceBankTransfersTable, 'invoice') ? 'invoice' : null);

            $invoiceId = $invoiceForeignKeyInPurchase ? $this->getAttribute($invoiceForeignKeyInPurchase) : null;

            $relation = $this->hasMany(InvoiceBankTransfer::class, $invoiceForeignKeyInInvoiceBankTransfers ?: BC::COL_INV_ID, $invoiceForeignKeyInPurchase ?: $this->getKeyName());

            if (
                !$invoiceForeignKeyInPurchase ||
                !$invoiceForeignKeyInInvoiceBankTransfers ||
                !$invoiceId ||
                !Schema::hasTable($invoicesTable) ||
                !DB::table($invoicesTable)->where('id', $invoiceId)->exists()
            ) {
                return $relation->whereRaw('1=0');
            }

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Purchase::invoiceBankTransfers - Failed to build relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(InvoiceBankTransfer::class, BC::COL_INV_ID, BC::COL_INV_ID)->whereRaw('1=0');
        }
    }

    public function allBankTransfers(): HasMany
    {
        try {
            $bankTransfersTable = (new BankTransfer)->getTable();
            $invoiceBankTransfersTable = (new InvoiceBankTransfer)->getTable();
            $purchasesTable = $this->getTable();

            $purchaseId = $this->getKey();

            $purchaseForeignKeyInBankTransfers = Schema::hasColumn($bankTransfersTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($bankTransfersTable, 'purchase') ? 'purchase' : null);

            $invoiceForeignKeyInPurchase = Schema::hasColumn($purchasesTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($purchasesTable, 'invoice') ? 'invoice' : null);

            $invoiceForeignKeyInInvoiceBankTransfers = Schema::hasColumn($invoiceBankTransfersTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($invoiceBankTransfersTable, 'invoice') ? 'invoice' : null);

            $bankTransferForeignKeyInInvoiceBankTransfers = Schema::hasColumn($invoiceBankTransfersTable, BC::COL_BNK_TRF_ID)
                ? BC::COL_BNK_TRF_ID
                : (Schema::hasColumn($invoiceBankTransfersTable, 'bank_transfer') ? 'bank_transfer' : (Schema::hasColumn($invoiceBankTransfersTable, 'bank_transfer_id') ? 'bank_transfer_id' : null));

            if (!$purchaseForeignKeyInBankTransfers) {
                Log::error('Purchase::allBankTransfers - No valid purchase FK column found in bank_transfers table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'bank_transfers_table' => $bankTransfersTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasMany(BankTransfer::class, BC::COL_PRC_ID, $this->getKeyName());
            }

            $bankTransfersColumns = Schema::getColumnListing($bankTransfersTable);
            $unionColumns = array_values(array_unique(array_merge($bankTransfersColumns, [$purchaseForeignKeyInBankTransfers, '_source'])));
            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $directSelect = [];
            foreach ($unionColumns as $col) {
                if ($col === '_source') {
                    $directSelect[] = "'bank_transfers' as `_source`";
                    continue;
                }
                $directSelect[] = \in_array($col, $bankTransfersColumns, true)
                    ? "`{$bankTransfersTable}`.`{$col}` as `{$col}`"
                    : "NULL as `{$col}`";
            }

            $directSql = "SELECT " . implode(', ', $directSelect) .
                " FROM `{$bankTransfersTable}` WHERE `{$purchaseForeignKeyInBankTransfers}` = " . DB::getPdo()->quote((string) $purchaseId);

            $invoiceSql = null;

            if ($invoiceForeignKeyInPurchase && $invoiceForeignKeyInInvoiceBankTransfers && $bankTransferForeignKeyInInvoiceBankTransfers) {
                $invoiceId = $this->getAttribute($invoiceForeignKeyInPurchase);

                if ($invoiceId) {
                    $invoiceSelect = [];
                    foreach ($unionColumns as $col) {
                        if ($col === '_source') {
                            $invoiceSelect[] = "'invoice_bank_transfers' as `_source`";
                            continue;
                        }
                        $invoiceSelect[] = \in_array($col, $bankTransfersColumns, true)
                            ? "`bt`.`{$col}` as `{$col}`"
                            : "NULL as `{$col}`";
                    }

                    $invoiceSql =
                        "SELECT " . implode(', ', $invoiceSelect) .
                        " FROM `{$invoiceBankTransfersTable}` ibt" .
                        " JOIN `{$bankTransfersTable}` bt ON bt.`id` = ibt.`{$bankTransferForeignKeyInInvoiceBankTransfers}`" .
                        " WHERE ibt.`{$invoiceForeignKeyInInvoiceBankTransfers}` = " . DB::getPdo()->quote((string) $invoiceId) .
                        " AND ibt.`{$bankTransferForeignKeyInInvoiceBankTransfers}` IS NOT NULL" .
                        " AND bt.`id` NOT IN (" .
                        "SELECT `{$bankTransfersTable}`.`id` FROM `{$bankTransfersTable}` WHERE `{$purchaseForeignKeyInBankTransfers}` = " . DB::getPdo()->quote((string) $purchaseId) .
                        ")";
                }
            }

            $unionSql = $invoiceSql ? "({$directSql}) UNION ALL ({$invoiceSql})" : "({$directSql})";

            $derivedAlias = 'purchase_all_bank_transfers_union';

            $relation = $this->hasMany(BankTransfer::class, $purchaseForeignKeyInBankTransfers, $this->getKeyName());
            $relation->getQuery()->from(DB::raw("({$unionSql}) as `{$derivedAlias}`"));

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Purchase::allBankTransfers - Failed to build union-backed HasMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(BankTransfer::class, BC::COL_PRC_ID, $this->getKeyName());
        }
    }

    public function lastBankTransfer(): ?HasOne
    {
        try {
            $bankTransfersTable = (new BankTransfer)->getTable();

            $purchaseForeignKeyInBankTransfers = Schema::hasColumn($bankTransfersTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($bankTransfersTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKeyInBankTransfers) {
                Log::error('Purchase::lastBankTransfer - No valid purchase FK column found in bank_transfers table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $bankTransfersTable,
                    'checked_columns' => [BC::COL_PRC_ID, 'purchase'],
                ]);

                return $this->hasOne(BankTransfer::class, BC::COL_PRC_ID, $this->getKeyName())->latestOfMany();
            }

            return $this->hasOne(BankTransfer::class, $purchaseForeignKeyInBankTransfers, $this->getKeyName())->latestOfMany();
        } catch (\Throwable $e) {
            Log::error('Purchase::lastBankTransfer - Failed to build relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasOne(BankTransfer::class, BC::COL_PRC_ID, $this->getKeyName())->latestOfMany();
        }
    }

    public function lastInvoiceBankTransfer(): ?HasOne
    {
        try {
            $purchasesTable = $this->getTable();
            $invoiceBankTransfersTable = (new InvoiceBankTransfer)->getTable();

            $invoiceForeignKeyInPurchase = Schema::hasColumn($purchasesTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($purchasesTable, 'invoice') ? 'invoice' : null);

            $invoiceForeignKeyInInvoiceBankTransfers = Schema::hasColumn($invoiceBankTransfersTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($invoiceBankTransfersTable, 'invoice') ? 'invoice' : null);

            if (!$invoiceForeignKeyInPurchase || !$invoiceForeignKeyInInvoiceBankTransfers) return $this->hasOne(InvoiceBankTransfer::class, BC::COL_INV_ID, BC::COL_INV_ID)->whereRaw('1=0');

            $invoiceId = $this->getAttribute($invoiceForeignKeyInPurchase);
            if (!$invoiceId) return $this->hasOne(InvoiceBankTransfer::class, $invoiceForeignKeyInInvoiceBankTransfers, $invoiceForeignKeyInPurchase)->whereRaw('1=0');

            return $this->hasOne(InvoiceBankTransfer::class, $invoiceForeignKeyInInvoiceBankTransfers, $invoiceForeignKeyInPurchase)->latestOfMany();
        } catch (\Throwable $e) {
            Log::error('Purchase::lastInvoiceBankTransfer - Failed to build relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasOne(InvoiceBankTransfer::class, BC::COL_INV_ID, BC::COL_INV_ID)->whereRaw('1=0');
        }
    }

    public function lastAnyBankTransfer(): ?HasOne
    {
        try {
            $lastDirect = $this->lastBankTransfer()?->first();
            $lastInvoice = $this->lastInvoiceBankTransfer()?->first();

            if (!$lastDirect && !$lastInvoice) return null;
            if (!$lastDirect) return $this->lastInvoiceBankTransfer();
            if (!$lastInvoice) return $this->lastBankTransfer();

            $directTs = $lastDirect->created_at ?? $lastDirect->updated_at ?? null;
            $invoiceTs = $lastInvoice->created_at ?? $lastInvoice->updated_at ?? null;

            if (!$directTs) return $this->lastInvoiceBankTransfer();
            if (!$invoiceTs) return $this->lastBankTransfer();

            return $directTs > $invoiceTs ? $this->lastBankTransfer() : $this->lastInvoiceBankTransfer();
        } catch (\Throwable $e) {
            Log::error('Purchase::lastAnyBankTransfer - Failed to resolve last bank transfer', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'purchase_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function setMetadataAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute('metadata', $value);
    }

    public function setDeliveryAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute('delivery', $value);
    }

    public function setAttachmentsAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute('attachments', $value);
    }

    public function setTaxesAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute('taxes', $value);
    }

    public function purchaseProductsRaw(): Collection
    {
        try {
            $purchaseProductsTable = (new PurchaseProduct)->getTable();

            $purchaseForeignKey = Schema::hasColumn($purchaseProductsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchaseProductsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKey) return collect([]);

            $sql = "SELECT * FROM `{$purchaseProductsTable}` WHERE `{$purchaseForeignKey}` = ?";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::purchaseProductsRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function productProductsRaw(): Collection
    {
        try {
            $productsTable = (new Product)->getTable();

            $purchaseForeignKey = Schema::hasColumn($productsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($productsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKey) return collect([]);

            $sql = "SELECT * FROM `{$productsTable}` WHERE `{$purchaseForeignKey}` = ?";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::productProductsRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function productsRaw(): Collection
    {
        try {
            $purchaseProductsTable = (new PurchaseProduct)->getTable();
            $productsTable = (new Product)->getTable();

            $purchaseForeignKeyInPurchaseProducts = Schema::hasColumn($purchaseProductsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchaseProductsTable, 'purchase') ? 'purchase' : null);

            $purchaseForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($productsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKeyInPurchaseProducts || !$purchaseForeignKeyInProducts) return collect([]);

            $purchaseId = $this->getKey();

            $purchaseProductsColumns = Schema::getColumnListing($purchaseProductsTable);
            $productsColumns = Schema::getColumnListing($productsTable);

            $unionColumns = array_values(array_unique(array_merge($purchaseProductsColumns, $productsColumns, [BC::COL_PRC_ID, '_source'])));
            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $selectA = [];
            foreach ($unionColumns as $c) {
                if ($c === '_source') {
                    $selectA[] = "'purchase_products' as `_source`";
                    continue;
                }
                if ($c === BC::COL_PRC_ID) {
                    $selectA[] = "`{$purchaseProductsTable}`.`{$purchaseForeignKeyInPurchaseProducts}` as `" . BC::COL_PRC_ID . "`";
                    continue;
                }
                $selectA[] = \in_array($c, $purchaseProductsColumns, true) ? "`{$purchaseProductsTable}`.`{$c}` as `{$c}`" : "NULL as `{$c}`";
            }

            $selectB = [];
            foreach ($unionColumns as $c) {
                if ($c === '_source') {
                    $selectB[] = "'products' as `_source`";
                    continue;
                }
                if ($c === BC::COL_PRC_ID) {
                    $selectB[] = "`{$productsTable}`.`{$purchaseForeignKeyInProducts}` as `" . BC::COL_PRC_ID . "`";
                    continue;
                }
                $selectB[] = \in_array($c, $productsColumns, true) ? "`{$productsTable}`.`{$c}` as `{$c}`" : "NULL as `{$c}`";
            }

            $sql =
                "SELECT " . implode(', ', $selectA) . " FROM `{$purchaseProductsTable}` WHERE `{$purchaseForeignKeyInPurchaseProducts}` = ? " .
                "UNION ALL " .
                "SELECT " . implode(', ', $selectB) . " FROM `{$productsTable}` WHERE `{$purchaseForeignKeyInProducts}` = ?";

            return collect(DB::select($sql, [$purchaseId, $purchaseId]));
        } catch (\Throwable $e) {
            Log::error('Purchase::productsRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function productServicesRaw(): Collection
    {
        try {
            $purchaseProductsTable = (new PurchaseProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $purchaseId = $this->getKey();

            $purchaseForeignKeyInPurchaseProducts = Schema::hasColumn($purchaseProductsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchaseProductsTable, 'purchase') ? 'purchase' : null);

            $purchaseForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($productsTable, 'purchase') ? 'purchase' : null);

            $serviceFkInPurchaseProducts = Schema::hasColumn($purchaseProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($purchaseProductsTable, 'product_service_id') ? 'product_service_id' : null);

            $serviceFkInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service_id') ? 'product_service_id' : null);

            if ((!$purchaseForeignKeyInPurchaseProducts && !$purchaseForeignKeyInProducts) || (!$serviceFkInPurchaseProducts && !$serviceFkInProducts)) return collect([]);

            $a = null;
            if ($purchaseForeignKeyInPurchaseProducts && $serviceFkInPurchaseProducts) {
                $a = DB::table($purchaseProductsTable)
                    ->selectRaw("`{$purchaseProductsTable}`.`{$serviceFkInPurchaseProducts}` as service_id")
                    ->where("{$purchaseProductsTable}.{$purchaseForeignKeyInPurchaseProducts}", $purchaseId)
                    ->whereNotNull("{$purchaseProductsTable}.{$serviceFkInPurchaseProducts}");
            }

            $b = null;
            if ($purchaseForeignKeyInProducts && $serviceFkInProducts) {
                $b = DB::table($productsTable)
                    ->selectRaw("`{$productsTable}`.`{$serviceFkInProducts}` as service_id")
                    ->where("{$productsTable}.{$purchaseForeignKeyInProducts}", $purchaseId)
                    ->whereNotNull("{$productsTable}.{$serviceFkInProducts}");

                if ($a) {
                    $sub = DB::table($purchaseProductsTable)
                        ->selectRaw("`{$purchaseProductsTable}`.`{$serviceFkInPurchaseProducts}`")
                        ->where("{$purchaseProductsTable}.{$purchaseForeignKeyInPurchaseProducts}", $purchaseId)
                        ->whereNotNull("{$purchaseProductsTable}.{$serviceFkInPurchaseProducts}");

                    $b->whereNotIn("{$productsTable}.{$serviceFkInProducts}", $sub);
                }
            }

            $union = $a ? ($b ? $a->unionAll($b) : $a) : $b;
            if (!$union) return collect([]);

            $q = DB::table("{$productServicesTable} as ps")
                ->joinSub($union, 'src', 'src.service_id', '=', 'ps.id')
                ->selectRaw("ps.*")
                ->distinct();

            return collect(DB::select($q->toSql(), $q->getBindings()));
        } catch (\Throwable $e) {
            Log::error('Purchase::productServicesRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function purchasePaymentsRaw(): Collection
    {
        try {
            $purchasePaymentsTable = (new PurchasePayment)->getTable();

            $purchaseForeignKey = Schema::hasColumn($purchasePaymentsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($purchasePaymentsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKey) return collect([]);

            $sql = "SELECT * FROM `{$purchasePaymentsTable}` WHERE `{$purchaseForeignKey}` = ?";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::purchasePaymentsRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function paymentPaymentsRaw(): Collection
    {
        try {
            $paymentsTable = (new Payment)->getTable();

            $purchaseForeignKey = Schema::hasColumn($paymentsTable, BC::COL_PRC_ID)
                ? BC::COL_PRC_ID
                : (Schema::hasColumn($paymentsTable, 'purchase') ? 'purchase' : null);

            if (!$purchaseForeignKey) return collect([]);

            $sql = "SELECT * FROM `{$paymentsTable}` WHERE `{$purchaseForeignKey}` = ?";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::paymentPaymentsRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function paymentsRaw(): Collection
    {
        try {
            $purchasePaymentsTable = (new PurchasePayment)->getTable();
            $paymentsTable = (new Payment)->getTable();

            $fkA = Schema::hasColumn($purchasePaymentsTable, BC::COL_PRC_ID) ? BC::COL_PRC_ID : (Schema::hasColumn($purchasePaymentsTable, 'purchase') ? 'purchase' : null);
            $fkB = Schema::hasColumn($paymentsTable, BC::COL_PRC_ID) ? BC::COL_PRC_ID : (Schema::hasColumn($paymentsTable, 'purchase') ? 'purchase' : null);
            if (!$fkA || !$fkB) return collect([]);

            $purchaseId = $this->getKey();

            $colsA = Schema::getColumnListing($purchasePaymentsTable);
            $colsB = Schema::getColumnListing($paymentsTable);

            $unionCols = array_values(array_unique(array_merge($colsA, $colsB, [BC::COL_PRC_ID, '_source'])));
            if (!\in_array('id', $unionCols, true)) $unionCols[] = 'id';

            $selA = [];
            foreach ($unionCols as $c) {
                if ($c === '_source') {
                    $selA[] = "'purchase_payments' as `_source`";
                    continue;
                }
                if ($c === BC::COL_PRC_ID) {
                    $selA[] = "`{$purchasePaymentsTable}`.`{$fkA}` as `" . BC::COL_PRC_ID . "`";
                    continue;
                }
                $selA[] = \in_array($c, $colsA, true) ? "`{$purchasePaymentsTable}`.`{$c}` as `{$c}`" : "NULL as `{$c}`";
            }

            $selB = [];
            foreach ($unionCols as $c) {
                if ($c === '_source') {
                    $selB[] = "'payments' as `_source`";
                    continue;
                }
                if ($c === BC::COL_PRC_ID) {
                    $selB[] = "`{$paymentsTable}`.`{$fkB}` as `" . BC::COL_PRC_ID . "`";
                    continue;
                }
                $selB[] = \in_array($c, $colsB, true) ? "`{$paymentsTable}`.`{$c}` as `{$c}`" : "NULL as `{$c}`";
            }

            $sql =
                "SELECT " . implode(', ', $selA) . " FROM `{$purchasePaymentsTable}` WHERE `{$fkA}` = ? " .
                "UNION ALL " .
                "SELECT " . implode(', ', $selB) . " FROM `{$paymentsTable}` WHERE `{$fkB}` = ?";

            return collect(DB::select($sql, [$purchaseId, $purchaseId]));
        } catch (\Throwable $e) {
            Log::error('Purchase::paymentsRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function lastPurchasePaymentRaw(): Collection
    {
        try {
            $table = (new PurchasePayment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_PRC_ID) ? BC::COL_PRC_ID : (Schema::hasColumn($table, 'purchase') ? 'purchase' : null);
            if (!$fk) return collect([]);

            $sql = "SELECT * FROM `{$table}` WHERE `{$fk}` = ? ORDER BY `created_at` DESC, `id` DESC LIMIT 1";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::lastPurchasePaymentRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function lastPaymentPaymentRaw(): Collection
    {
        try {
            $table = (new Payment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_PRC_ID) ? BC::COL_PRC_ID : (Schema::hasColumn($table, 'purchase') ? 'purchase' : null);
            if (!$fk) return collect([]);

            $sql = "SELECT * FROM `{$table}` WHERE `{$fk}` = ? ORDER BY `created_at` DESC, `id` DESC LIMIT 1";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::lastPaymentPaymentRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function lastPaymentRaw(): Collection
    {
        try {
            $a = $this->lastPurchasePaymentRaw()->first();
            $b = $this->lastPaymentPaymentRaw()->first();

            if (!$a && !$b) return collect([]);
            if (!$a) return collect([$b]);
            if (!$b) return collect([$a]);

            $aTs = $a->created_at ?? $a->updated_at ?? null;
            $bTs = $b->created_at ?? $b->updated_at ?? null;

            if (!$aTs) return collect([$b]);
            if (!$bTs) return collect([$a]);

            return $aTs > $bTs ? collect([$a]) : collect([$b]);
        } catch (\Throwable $e) {
            Log::error('Purchase::lastPaymentRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function bankTransfersRaw(): Collection
    {
        try {
            $table = (new BankTransfer)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_PRC_ID) ? BC::COL_PRC_ID : (Schema::hasColumn($table, 'purchase') ? 'purchase' : null);
            if (!$fk) return collect([]);

            $sql = "SELECT * FROM `{$table}` WHERE `{$fk}` = ?";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::bankTransfersRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function invoiceBankTransfersRaw(): Collection
    {
        try {
            $purchasesTable = $this->getTable();
            $ibtTable = (new InvoiceBankTransfer)->getTable();

            $purchaseInvoiceFk = Schema::hasColumn($purchasesTable, BC::COL_INV_ID) ? BC::COL_INV_ID : (Schema::hasColumn($purchasesTable, 'invoice') ? 'invoice' : null);
            $ibtInvoiceFk = Schema::hasColumn($ibtTable, BC::COL_INV_ID) ? BC::COL_INV_ID : (Schema::hasColumn($ibtTable, 'invoice') ? 'invoice' : null);

            if (!$purchaseInvoiceFk || !$ibtInvoiceFk) return collect([]);

            $invoiceId = $this->getAttribute($purchaseInvoiceFk);
            if (!$invoiceId) return collect([]);

            $sql = "SELECT * FROM `{$ibtTable}` WHERE `{$ibtInvoiceFk}` = ?";
            return collect(DB::select($sql, [$invoiceId]));
        } catch (\Throwable $e) {
            Log::error('Purchase::invoiceBankTransfersRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function allBankTransfersRaw(): Collection
    {
        try {
            $bankTransfersTable = (new BankTransfer)->getTable();
            $ibtTable = (new InvoiceBankTransfer)->getTable();
            $purchasesTable = $this->getTable();

            $purchaseId = $this->getKey();

            $purchaseFkInBankTransfers = Schema::hasColumn($bankTransfersTable, BC::COL_PRC_ID) ? BC::COL_PRC_ID : (Schema::hasColumn($bankTransfersTable, 'purchase') ? 'purchase' : null);
            if (!$purchaseFkInBankTransfers) return collect([]);

            $purchaseInvoiceFk = Schema::hasColumn($purchasesTable, BC::COL_INV_ID) ? BC::COL_INV_ID : (Schema::hasColumn($purchasesTable, 'invoice') ? 'invoice' : null);
            $ibtInvoiceFk = Schema::hasColumn($ibtTable, BC::COL_INV_ID) ? BC::COL_INV_ID : (Schema::hasColumn($ibtTable, 'invoice') ? 'invoice' : null);
            $ibtBankTransferFk = Schema::hasColumn($ibtTable, BC::COL_BNK_TRF_ID) ? BC::COL_BNK_TRF_ID : (Schema::hasColumn($ibtTable, 'bank_transfer_id') ? 'bank_transfer_id' : (Schema::hasColumn($ibtTable, 'bank_transfer') ? 'bank_transfer' : null));

            $sqlDirect = "SELECT bt.* FROM `{$bankTransfersTable}` bt WHERE bt.`{$purchaseFkInBankTransfers}` = ?";
            $bindings = [$purchaseId];

            if ($purchaseInvoiceFk && $ibtInvoiceFk && $ibtBankTransferFk) {
                $invoiceId = $this->getAttribute($purchaseInvoiceFk);

                if ($invoiceId) {
                    $sqlInvoice =
                        "SELECT bt.* FROM `{$ibtTable}` ibt " .
                        "JOIN `{$bankTransfersTable}` bt ON bt.`id` = ibt.`{$ibtBankTransferFk}` " .
                        "WHERE ibt.`{$ibtInvoiceFk}` = ? " .
                        "AND ibt.`{$ibtBankTransferFk}` IS NOT NULL " .
                        "AND bt.`id` NOT IN (SELECT `id` FROM `{$bankTransfersTable}` WHERE `{$purchaseFkInBankTransfers}` = ?)";

                    $sql = "{$sqlDirect} UNION ALL {$sqlInvoice}";
                    $bindings = [$purchaseId, $invoiceId, $purchaseId];
                    return collect(DB::select($sql, $bindings));
                }
            }

            return collect(DB::select($sqlDirect, $bindings));
        } catch (\Throwable $e) {
            Log::error('Purchase::allBankTransfersRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function lastBankTransferRaw(): Collection
    {
        try {
            $table = (new BankTransfer)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_PRC_ID) ? BC::COL_PRC_ID : (Schema::hasColumn($table, 'purchase') ? 'purchase' : null);
            if (!$fk) return collect([]);

            $sql = "SELECT * FROM `{$table}` WHERE `{$fk}` = ? ORDER BY `created_at` DESC, `id` DESC LIMIT 1";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Purchase::lastBankTransferRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function lastInvoiceBankTransferRaw(): Collection
    {
        try {
            $purchasesTable = $this->getTable();
            $ibtTable = (new InvoiceBankTransfer)->getTable();

            $purchaseInvoiceFk = Schema::hasColumn($purchasesTable, BC::COL_INV_ID) ? BC::COL_INV_ID : (Schema::hasColumn($purchasesTable, 'invoice') ? 'invoice' : null);
            $ibtInvoiceFk = Schema::hasColumn($ibtTable, BC::COL_INV_ID) ? BC::COL_INV_ID : (Schema::hasColumn($ibtTable, 'invoice') ? 'invoice' : null);

            if (!$purchaseInvoiceFk || !$ibtInvoiceFk) return collect([]);

            $invoiceId = $this->getAttribute($purchaseInvoiceFk);
            if (!$invoiceId) return collect([]);

            $sql = "SELECT * FROM `{$ibtTable}` WHERE `{$ibtInvoiceFk}` = ? ORDER BY `created_at` DESC, `id` DESC LIMIT 1";
            return collect(DB::select($sql, [$invoiceId]));
        } catch (\Throwable $e) {
            Log::error('Purchase::lastInvoiceBankTransferRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function lastAnyBankTransferRaw(): Collection
    {
        try {
            $a = $this->lastBankTransferRaw()->first();
            $b = $this->lastInvoiceBankTransferRaw()->first();

            if (!$a && !$b) return collect([]);
            if (!$a) return collect([$b]);
            if (!$b) return collect([$a]);

            $aTs = $a->created_at ?? $a->updated_at ?? null;
            $bTs = $b->created_at ?? $b->updated_at ?? null;

            if (!$aTs) return collect([$b]);
            if (!$bTs) return collect([$a]);

            return $aTs > $bTs ? collect([$a]) : collect([$b]);
        } catch (\Throwable $e) {
            Log::error('Purchase::lastAnyBankTransferRaw - Failed', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function getStatusEnumAttribute(): PurchaseStatus
    {
        return PurchaseStatus::normalize($this->getAttribute(BC::COL_STT_LB));
    }

    public function getStatusLabelAttribute(): string
    {
        $case = $this->getStatusEnumAttribute();
        $labels = method_exists(PurchaseStatus::class, 'labels')
            ? PurchaseStatus::labels(DC::DEFAULT_LANG ?? DC::DEFAULT_LANG_LONG ?? null)
            : [];
        $label = $labels[$case->value] ?? null;

        return is_string($label) && trim($label) !== ''
            ? $label
            : ucwords(str_replace('_', ' ', $case->value));
    }

    public function getSubtotalAttribute(): float
    {
        return $this->getSubTotal();
    }

    public function getTotalTaxAttribute(): float
    {
        return $this->getTotalTax();
    }

    public function getTotalDiscountAttribute(): float
    {
        return $this->getTotalDiscount();
    }

    public function getTotalAttribute(): float
    {
        return $this->getTotal();
    }

    public function getDueAttribute(): float
    {
        return $this->getDue();
    }

    public function getSubTotal(): float
    {
        $id = (string) ($this->getAttribute('id') ?? '');
        if ($id === '') return 0.0;

        $cacheKey = 'subtotal:' . $id;
        if (array_key_exists($cacheKey, self::$aggCache)) return (float) self::$aggCache[$cacheKey];

        try {
            if ($this->relationLoaded('items'))
                return self::$aggCache[$cacheKey] = (float) $this->items->sum(fn($p) => (float) ($p->price ?? 0) * (float) ($p->quantity ?? 0));

            if (class_exists(PurchaseProduct::class)) {
                $t = (new PurchaseProduct())->getTable();
                $row = DB::selectOne(
                    "select coalesce(sum(coalesce(price,0) * coalesce(quantity,0)),0) as v from {$t} where purchase_id = ?",
                    [$id]
                );
                return self::$aggCache[$cacheKey] = (float) ($row->v ?? 0);
            }
        } catch (\Throwable $e) {
            Log::warning(self::class . ' subtotal aggregation failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id'   => $id,
            ]);
        }

        return 0.0;
    }

    public function getTotalTax(): float
    {
        $id = (string) ($this->getAttribute('id') ?? '');
        if ($id === '') return 0.0;

        $cacheKey = 'tax:' . $id;
        if (array_key_exists($cacheKey, self::$aggCache)) return (float) self::$aggCache[$cacheKey];

        try {
            if (!$this->relationLoaded('items')) $this->loadMissing('items.tax');

            $sum = (float) $this->items->sum(function ($p): float {
                $price = (float) ($p->price ?? 0);
                $qty   = (float) ($p->quantity ?? 0);
                $disc  = (float) ($p->discount ?? 0);
                $rate  = method_exists(Utility::class, 'totalTaxRate') ? (float) (Utility::totalTaxRate($p->tax) ?? 0) : 0.0; // @phpstan-ignore property.notFound
                return ($rate / 100.0) * max(0.0, ($price * $qty) - $disc);
            });

            return self::$aggCache[$cacheKey] = $sum;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' tax aggregation failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id'   => $id,
            ]);
        }

        return 0.0;
    }

    public function getTotalDiscount(): float
    {
        $id = (string) ($this->getAttribute('id') ?? '');
        if ($id === '') return 0.0;

        $cacheKey = 'discount:' . $id;
        if (array_key_exists($cacheKey, self::$aggCache)) return (float) self::$aggCache[$cacheKey];

        try {
            if ($this->relationLoaded('items'))
                return self::$aggCache[$cacheKey] = (float) $this->items->sum('discount');

            if (class_exists(PurchaseProduct::class)) {
                $t = (new PurchaseProduct())->getTable();
                $row = DB::selectOne(
                    "select coalesce(sum(coalesce(discount,0)),0) as v from {$t} where purchase_id = ?",
                    [$id]
                );
                return self::$aggCache[$cacheKey] = (float) ($row->v ?? 0);
            }
        } catch (\Throwable $e) {
            Log::warning(self::class . ' discount aggregation failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id'   => $id,
            ]);
        }

        return 0.0;
    }

    public function getTotal(): float
    {
        $svcFee = (float) ($this->getAttribute(BC::COL_SVC_FEE) ?? 0.0);
        return $this->getSubTotal() - $this->getTotalDiscount() + $this->getTotalTax() + max(0.0, $svcFee);
    }

    public function getDue(): float
    {
        try {
            if (!$this->relationLoaded('payments')) $this->loadMissing('payments');
            return max(0.0, $this->getTotal() - (float) $this->payments->sum('amount'));
        } catch (\Throwable $e) {
            Log::warning(self::class . ' due computation failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id'   => (string) ($this->getAttribute('id') ?? ''),
            ]);
        }

        return $this->getTotal();
    }

    /**
     * Get total purchase amount
     * Pure alias to PurchaseRequestService - auth check happens in service
     */
    public static function totalPurchaseAmount(bool $month = false): string|RedirectResponse
    {
        return app(PurchaseRequestService::class)->getTotalPurchaseAmount($month);
    }

    /**
     * Get purchase report chart data for last 10 days
     * Pure alias to PurchaseRequestService - auth check happens in service
     */
    public static function getPurchaseReportChart(): array|RedirectResponse
    {
        return app(PurchaseRequestService::class)->getPurchaseReportChart();
    }

    public static function primePurchaseIdCache(int $chunkSize = 5000, int $maxRows = 200000): void
    {
        if (self::$purchaseIdCachePrimed) return;
        $table = (new self())->getTable();
        $loaded = 0;
        DB::table($table)
            ->select([BC::COL_PRC_ID, 'id'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) use (&$loaded, $maxRows) {
                foreach ($rows as $r) {
                    if ($loaded >= $maxRows) break;

                    $v = trim((string) ($r->{BC::COL_PRC_ID} ?? ''));
                    if ($v !== '') self::$purchaseIdExistingSet[$v] = true;
                    $loaded++;
                }
            }, 'id');

        self::$purchaseIdCachePrimed = true;
    }

    private static function reservePurchaseId(string $id): void
    {
        $id = trim($id);
        if ($id === '') return;
        self::$purchaseIdReservedSet[$id] = true;
        if (self::$purchaseIdCachePrimed)
            self::$purchaseIdExistingSet[$id] = true;
    }

    private static function tableExistsCached(string $table): bool
    {
        return self::$purchaseSchemaTableCache[$table] ??= Schema::hasTable($table);
    }

    private static function columnExistsCached(string $table, string $column): bool
    {
        return self::$purchaseSchemaColumnCache[$table][$column]
            ??= (self::tableExistsCached($table) && Schema::hasColumn($table, $column));
    }

    private function purchaseIdExists(string $candidate): bool
    {
        $candidate = trim($candidate);
        if ($candidate === '') return true;
        if (isset(self::$purchaseIdReservedSet[$candidate])) return true;
        if (self::$purchaseIdCachePrimed)
            return isset(self::$purchaseIdExistingSet[$candidate]);
        try {
            $table = $this->getTable();
            $sql = "select 1 from `{$table}` where `" . BC::COL_PRC_ID . "` = ? limit 1";
            return DB::selectOne($sql, [$candidate]) !== null;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' purchaseIdExists failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return true;
        }
    }

    private function ensurePurchaseDate(): void
    {
        if ($this->getAttribute(BC::COL_PRC_DT) !== null) return;
        $this->setAttribute(BC::COL_PRC_DT, Carbon::now());
    }

    private function ensurePurchaseId(): void
    {
        $cur = trim((string) ($this->getAttribute(BC::COL_PRC_ID) ?? ''));
        if ($cur !== '') {
            self::reservePurchaseId($cur);
            return;
        }

        $ts = Carbon::now()->format('YmdHisv');
        $maxAttempts = 8;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $uuid = (string) Str::uuid();
            $suffix = $i === 0 ? '' : '-' . Str::lower(Str::random(6));
            $candidate = "PRCH-{$uuid}-{$ts}{$suffix}";
            if (isset(self::$purchaseIdReservedSet[$candidate]))
                continue;
            $this->setAttribute(BC::COL_PRC_ID, $candidate);
            self::reservePurchaseId($candidate);
            return;
        }
        $fallback = 'PRCH-' . (string) Str::uuid() . '-' . Carbon::now()->format('YmdHisv');
        $this->setAttribute(BC::COL_PRC_ID, $fallback);
        self::reservePurchaseId($fallback);
        Log::warning(self::class . ' ensurePurchaseId exhausted attempts; used fallback', [
            'fallback' => $fallback,
            'attempts' => $maxAttempts,
        ]);
    }


    private function ensurePurchaseNumber(): void
    {
        $cur = $this->getAttribute(BC::COL_PRC_NB);
        if (is_numeric($cur) && (int) $cur > 0) return;

        $creator = (string) ($this->getAttribute(DC::COL_TABLE_CREATOR) ?? '');
        if (trim($creator) === '') return;

        try {
            $row = DB::selectOne(
                "select coalesce(max(" . BC::COL_PRC_NB . "),0) as mx from " . $this->getTable() . " where " . DC::COL_TABLE_CREATOR . " = ?",
                [$creator]
            );
            $this->setAttribute(BC::COL_PRC_NB, (int) ($row->mx ?? 0) + 1);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' ensurePurchaseNumber failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    private function enforceSendDateGtPurchaseDate(): void
    {
        $sd = $this->getAttribute(BC::COL_SD_DT);
        $pd = $this->getAttribute(BC::COL_PRC_DT);
        if ($sd === null || $pd === null) return;

        try {
            $send = Carbon::parse($sd);
            $pur  = Carbon::parse($pd);

            if ($send->lessThanOrEqualTo($pur)) {
                Log::warning(self::class . ' send_date <= purchase_date, nullifying send_date', [
                    'purchase_id' => (string) ($this->getAttribute(BC::COL_PRC_ID) ?? ''),
                ]);
                $this->setAttribute(BC::COL_SD_DT, null);
            }
        } catch (\Throwable $e) {
            Log::warning(self::class . ' enforceSendDateGtPurchaseDate failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    private function normalizePseudoBooleans(): void
    {
        $this->setAttribute(BC::COL_DSC_APL, $this->clampOddEvenToBool($this->getAttribute(BC::COL_DSC_APL)));
        $this->setAttribute(BC::COL_SHIP_DSP, $this->clampOddEvenToBool($this->getAttribute(BC::COL_SHIP_DSP)));
    }

    private function clampOddEvenToBool(mixed $v): int
    {
        if ($v === null) return 0;
        if (is_bool($v)) return $v ? 1 : 0;
        if (!is_numeric($v)) return 0;
        return ((int) $v % 2) === 1 ? 1 : 0;
    }

    private function overrideCategoryFromProductService(): void
    {
        $prd = (string) ($this->getAttribute(BC::COL_PRD_SV_ID) ?? '');
        if (trim($prd) === '') return;

        $catCol = BC::COL_CAT_ID;
        $cat = null;

        try {
            foreach ([DC::TABLE_PROD_SERVS, DC::TABLE_PRODUCTS] as $t) {
                if (!is_string($t) || trim($t) === '') continue;
                if (!Schema::hasTable($t)) continue;
                if (!Schema::hasColumn($t, 'id')) continue;
                if (!Schema::hasColumn($t, $catCol)) continue;

                $row = DB::selectOne("select {$catCol} as cid from {$t} where id = ? limit 1", [$prd]);
                $cid = (string) ($row->cid ?? '');
                if (trim($cid) !== '') {
                    $cat = $cid;
                    break;
                }
            }

            if (is_string($cat) && trim($cat) !== '') $this->setAttribute($catCol, $cat);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' overrideCategoryFromProductService failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                BC::COL_PRD_SV_ID => $prd,
            ]);
        }
    }

    private function mirrorShippingToLinkedDocuments(): void
    {
        $shipping = $this->currentShippingPayload();
        if (!$shipping) return;

        try {
            foreach (
                [
                    [DC::TABLE_BILLS, BC::COL_BL_ID],
                    [DC::TABLE_INVS,  BC::COL_INV_ID],
                    [DC::TABLE_ORDERS, BC::COL_OD_ID],
                ] as [$table, $fkCol]
            ) {
                $id = (string) ($this->getAttribute($fkCol) ?? '');
                if (trim($id) === '') continue;
                if (!Schema::hasTable($table)) continue;

                $update = [];
                foreach ($shipping as $k => $v) {
                    if (!Schema::hasColumn($table, $k)) continue;
                    $update[$k] = $v;
                }
                if (!$update) continue;

                DB::table($table)->where('id', $id)->update($update);
            }
        } catch (\Throwable $e) {
            Log::warning(self::class . ' mirrorShippingToLinkedDocuments failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'purchase_id' => (string) ($this->getAttribute(BC::COL_PRC_ID) ?? ''),
            ]);
        }
    }

    private function currentShippingPayload(): array
    {
        $cols = [
            BC::COL_SHIP_NAME,
            BC::COL_SHIP_CTR,
            BC::COL_SHIP_ZIP,
            BC::COL_SHIP_ADR,
            BC::COL_SHIP_ST,
            BC::COL_SHIP_CTY,
            BC::COL_SHIP_TEL,
            BC::COL_SHIP_EMAIL,
            BC::COL_SHIP_DTL,
        ];

        $out = [];
        foreach ($cols as $c) {
            $v = $this->getAttribute($c);
            if ($v === null) continue;
            $s = is_string($v) ? trim($v) : $v;
            if ($s === '' || $s === []) continue;
            $out[$c] = $v;
        }

        return $out;
    }

    private function mergeTaxesFromLinkedDocuments(): void
    {
        if (!$this->isDirty(['taxes', BC::COL_TAX_ID, BC::COL_INV_ID, BC::COL_OD_ID, BC::COL_BL_ID])) {
            return;
        }

        $taxTable = DC::TABLE_TAXES;
        if (!is_string($taxTable) || !self::tableExistsCached($taxTable)) return;

        $ids = $this->normalizeIdList($this->getAttribute('taxes'));

        $directTaxId = trim((string) ($this->getAttribute(BC::COL_TAX_ID) ?? ''));
        if ($directTaxId !== '') $ids[] = $directTaxId;

        try {
            foreach ([[DC::TABLE_INVS, BC::COL_INV_ID], [DC::TABLE_ORDERS, BC::COL_OD_ID]] as [$table, $fkCol]) {
                $id = trim((string) ($this->getAttribute($fkCol) ?? ''));
                if ($id === '') continue;

                if (!self::tableExistsCached($table)) continue;
                if (!self::columnExistsCached($table, BC::COL_TAX_ID)) continue;

                $tid = trim((string) (DB::table($table)->where('id', $id)->value(BC::COL_TAX_ID) ?? ''));
                if ($tid !== '') $ids[] = $tid;
            }

            $billId = trim((string) ($this->getAttribute(BC::COL_BL_ID) ?? ''));
            if ($billId !== '' && self::tableExistsCached(DC::TABLE_BILLS) && self::columnExistsCached(DC::TABLE_BILLS, 'taxes')) {
                $raw = DB::table(DC::TABLE_BILLS)->where('id', $billId)->value('taxes');
                $ids = array_merge($ids, $this->normalizeIdList($raw));
            }

            $ids = array_values(array_unique(array_filter(array_map(
                fn($v) => trim((string) $v),
                $ids
            ), fn($v) => $v !== '')));

            if (!$ids) {
                $this->setAttribute('taxes', null);
                return;
            }

            $valid = DB::table($taxTable)->whereIn('id', $ids)->pluck('id')->all();
            $valid = array_values(array_unique(array_map('strval', $valid)));

            $this->setAttribute('taxes', $valid ?: null);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' mergeTaxesFromLinkedDocuments failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'purchase_id' => (string) ($this->getAttribute(BC::COL_PRC_ID) ?? ''),
            ]);
        }
    }


    private function normalizeIdList(mixed $raw): array
    {
        if ($raw === null || $raw === '') return [];
        if (is_array($raw)) return array_values(array_filter(array_map('strval', $raw), fn($v) => trim($v) !== ''));

        if (is_string($raw)) {
            $s = trim($raw);
            if ($s === '') return [];
            $decoded = json_decode($s, true);
            if (is_array($decoded)) return array_values(array_filter(array_map('strval', $decoded), fn($v) => trim($v) !== ''));
            return [];
        }

        return [];
    }

    private function refreshStatusFields(): void
    {
        if (!$this->isDirty([BC::COL_STT_LB, BC::COL_OD_ID, BC::COL_BL_ID, BC::COL_INV_ID, BC::COL_PRC_DT, BC::COL_SD_DT])) {
            return;
        }
        $desired = $this->resolveDesiredStatus();
        $this->setAttribute(BC::COL_STT_LB, $desired->value);
        $this->setAttribute('status', PurchaseStatus::getIndex($desired->value));
    }

    private function resolveDesiredStatus(): PurchaseStatus
    {
        $raw = trim((string) (
            $this->getRawOriginal(BC::COL_STT_LB)
            ?? ($this->attributes[BC::COL_STT_LB] ?? '')
        ));
        $explicit = PurchaseStatus::normalize($raw);
        $authorized = $this->resolveAuthorizedPaymentValues();
        if ($this->anyLinkedPaymentStatusIn($authorized)) return PurchaseStatus::Paid;
        if ($this->hasAnyFinancialLink()) return PurchaseStatus::PartiallyPaid;
        $purchaseDate = $this->getAttribute(BC::COL_PRC_DT);
        $sendDate     = $this->getAttribute(BC::COL_SD_DT);
        if ($sendDate !== null && $purchaseDate !== null) {
            try {
                if (Carbon::now()->lessThan(Carbon::parse($sendDate)))
                    return PurchaseStatus::Draft;
                else return PurchaseStatus::Undefined; // todo mock only
            } catch (\Throwable) {
                return PurchaseStatus::Undefined;
            }
        }

        return $explicit ?: PurchaseStatus::Unpaid;
    }

    private function resolveAuthorizedPaymentValues(): array
    {
        $values = ['authorized', 'completed'];

        if (class_exists(PaymentStatus::class)) {
            try {
                $values = [
                    PaymentStatus::Authorized->value,
                    PaymentStatus::Completed->value,
                ];
            } catch (\Throwable) {
            }
        }

        return array_values(array_unique(array_filter($values, fn($v) => is_string($v) && trim($v) !== '')));
    }

    private function hasAnyFinancialLink(): bool
    {
        foreach ([BC::COL_OD_ID, BC::COL_BL_ID, BC::COL_INV_ID] as $col) {
            $v = (string) ($this->getAttribute($col) ?? '');
            if (trim($v) !== '') return true;
        }
        return false;
    }

    private function anyLinkedPaymentStatusIn(array $allowed): bool
    {
        $allowed = array_values(array_unique(array_filter($allowed, fn($v) => is_string($v) && trim($v) !== '')));
        if (!$allowed) return false;

        $paySttCol = defined(BC::class . '::COL_PAY_STT') ? BC::COL_PAY_STT : 'pay_status';

        try {
            foreach ([[DC::TABLE_ORDERS, BC::COL_OD_ID], [DC::TABLE_BILLS, BC::COL_BL_ID], [DC::TABLE_INVS, BC::COL_INV_ID]] as [$table, $fkCol]) {
                $id = trim((string) ($this->getAttribute($fkCol) ?? ''));
                if ($id === '') continue;

                if (!self::tableExistsCached($table)) continue;
                if (!self::columnExistsCached($table, $paySttCol)) continue;

                $stt = strtolower(trim((string) (DB::table($table)->where('id', $id)->value($paySttCol) ?? '')));
                if ($stt !== '' && in_array($stt, $allowed, true)) return true;
            }
        } catch (\Throwable $e) {
            Log::warning(self::class . ' anyLinkedPaymentStatusIn failed: ' . $e->getMessage(), [
                'purchase_id' => (string) ($this->getAttribute(BC::COL_PRC_ID) ?? ''),
            ]);
        }

        return false;
    }
}
