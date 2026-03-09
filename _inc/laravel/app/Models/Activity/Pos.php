<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\{PosStatus, PosType, TransactionType, UserType};
use App\Services\PosRequestService;
use App\Traits\{
    DescribesCompanyBranch,
    HasAuditFields,
    NormalizesAddresses,
    TracksFailures,
    UsesCountryRegions,
    UsesUuids,
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne
};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Schema, Log};
use Illuminate\Http\RedirectResponse;

/**
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Pos extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use NormalizesAddresses;
    use DescribesCompanyBranch;
    use UsesCountryRegions;
    use TracksFailures;

    protected $table = DC::TABLE_POS;
    protected $fillable = [
        BC::COL_POS_ID,
        BC::COL_POS_DT,

        BC::COL_DVC_SR,
        BC::COL_MAC_ADR,
        BC::COL_IP_ADR,
        BC::COL_DVC_MD,
        BC::COL_OPS_SYS,

        CC::COL_CP_ID,
        UC::COL_BRC_ID,
        BC::COL_WRH_ID,
        UC::COL_DEP_ID,
        BC::COL_CST_ID,
        BC::COL_CAT_ID,
        CC::COL_MNF_ID,
        CC::COL_MNF_NM,
        UC::COL_VD_ID,
        CC::COL_VD_NM,

        'type',
        'status',
        BC::COL_SHIP_DSP,
        BC::COL_STT_LB,

        BC::COL_IO,
        BC::COL_ACP_CRD,
        BC::COL_ACP_DBT,
        BC::COL_ACP_PIX,
        BC::COL_ACP_CSH,
        BC::COL_PIX_QR,
        BC::COL_ACP_FLG,

        BC::COL_LST_TRS,
        DC::COL_LA,
        BC::COL_TRS_CNT,
        BC::COL_ACC_TTL,
        BC::COL_SVC_FEE,

        // Billing
        BC::COL_BL_NAME,
        BC::COL_BL_EMAIL,
        BC::COL_BL_TEL,
        BC::COL_BL_ZIP,
        BC::COL_BL_ADR,
        BC::COL_BL_ST,
        BC::COL_BL_CTY,
        BC::COL_BL_CTR,
        BC::COL_BL_DTL,

        // Rastreamento de falhas
        ...self::FAILURE_TRACKING_COLS,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        BC::COL_POS_DT   => 'date',

        'type'           => PosType::class,
        BC::COL_STT_LB   => PosStatus::class,

        BC::COL_IO       => 'boolean',
        BC::COL_ACP_CRD  => 'boolean',
        BC::COL_ACP_DBT  => 'boolean',
        BC::COL_ACP_PIX  => 'boolean',
        BC::COL_ACP_CSH  => 'boolean',

        BC::COL_PIX_QR   => 'array',
        BC::COL_ACP_FLG  => 'array',

        BC::COL_LST_TRS  => 'datetime',
        DC::COL_LA       => 'datetime',

        BC::COL_TRS_CNT  => 'integer',
        BC::COL_ACC_TTL  => 'decimal:2',
        BC::COL_SVC_FEE  => 'decimal:2',

        DC::COL_FL_AT        => 'datetime',
        DC::COL_LST_RTR_AT   => 'datetime',
        DC::COL_ER_LG        => 'array',
    ];

    protected $with = [
        'warehouse',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            $ownerId = $m->getAttribute(DC::COL_TABLE_CREATOR) ?? $m->getAttribute(BC::COL_CST_ID) ?? null;
            $m->setAttribute(BC::COL_BL_EMAIL, static::normalizeEmail(
                $m->getAttribute(BC::COL_BL_EMAIL) ?? null,
                'pos_billing',
                $ownerId
            ));
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            $isNormalizeZipCallable = is_callable([self::class, 'normalizeZip']);
            if ($isNormalizePhoneCallable)
                $m->setAttribute(BC::COL_BL_TEL, static::normalizePhone(
                    $m->getAttribute(BC::COL_BL_TEL) ?? null,
                    'pos_billing',
                    $ownerId
                ));
            if ($isNormalizeZipCallable) {
                $m->setAttribute(BC::COL_BL_ZIP, static::normalizeZip(
                    $m->getAttribute(BC::COL_BL_ZIP) ?? null,
                    $m->getAttribute(BC::COL_BL_CTR) ?? null,
                    'pos_billing',
                    $ownerId
                ));
                $isNormalizeBillingCountryCallable = is_callable([self::class, 'normalizeBillingCountry']);
                if ($isNormalizeBillingCountryCallable)
                    self::normalizeBillingCountry($m);
                foreach ([BC::COL_TRS_CNT, DC::COL_RTR_CT] as $intField) {
                    if ($m->getAttribute($intField) !== null) {
                        $val = (int) $m->getAttribute($intField);
                        if ($val < 0)
                            $val = 0;
                        $m->setAttribute($intField, $val);
                    }
                }
                foreach ([BC::COL_ACC_TTL, BC::COL_SVC_FEE] as $decField) {
                    if ($m->getAttribute($decField) !== null) {
                        $val = (float) $m->getAttribute($decField);
                        if ($val < 0.0)
                            $val = 0.0;
                        $m->setAttribute($decField, $val);
                    }
                }
            }
        });
    }

    public function customer(): ?BelongsTo
    {
        return Utility::getCustomer($this);
    }


    public function warehouse(): ?BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class,
            BC::COL_WRH_ID,
            'id'
        );
    }

    public function company(): ?BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_CP_ID,
            'id'
        )->where('type', UserType::Company->value);
    }

    public function vendor(): ?BelongsTo
    {
        return Utility::getVendor($this);
    }

    public function manufacturer(): ?BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_MNF_ID,
            'id'
        );
    }

    /**
     * Itens da venda associada ao POS.
     *
     * Aqui assumimos que a tabela de itens possui a coluna `pos_id`
     * (BC::COL_POS_ID) referenciando o mesmo campo em `pos`.
     */
    public function items(): HasMany
    {
        return $this->products();
    }

    public function posProducts(): HasMany
    {
        try {
            $posTable = $this->getTable();
            $posProductsTable = (new PosProduct)->getTable();

            $localPosKey = Schema::hasColumn($posTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posTable, 'pos') ? 'pos' : $this->getKeyName());

            $posForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posProductsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInPosProducts) {
                Log::error('Pos::posProducts - No valid pos FK column found in pos_products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'pos_products_table' => $posProductsTable,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return $this->hasMany(PosProduct::class, BC::COL_POS_ID, $localPosKey);
            }

            Log::debug('Pos::posProducts - Using pos FK column in pos_products table', [
                'class' => static::class,
                'pos_fk' => $posForeignKeyInPosProducts,
                'local_key' => $localPosKey,
            ]);

            return $this->hasMany(PosProduct::class, $posForeignKeyInPosProducts, $localPosKey);
        } catch (\Throwable $e) {
            Log::error('Pos::posProducts - Failed to determine pos FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(PosProduct::class, BC::COL_POS_ID, BC::COL_POS_ID);
        }
    }

    public function productProducts(): HasMany
    {
        try {
            $posTable = $this->getTable();
            $productsTable = (new Product)->getTable();

            $localPosKey = Schema::hasColumn($posTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posTable, 'pos') ? 'pos' : $this->getKeyName());

            $posForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($productsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInProducts) {
                Log::error('Pos::productProducts - No valid pos FK column found in products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'products_table' => $productsTable,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return $this->hasMany(Product::class, BC::COL_POS_ID, $localPosKey);
            }

            Log::debug('Pos::productProducts - Using pos FK column in products table', [
                'class' => static::class,
                'pos_fk' => $posForeignKeyInProducts,
                'local_key' => $localPosKey,
            ]);

            return $this->hasMany(Product::class, $posForeignKeyInProducts, $localPosKey);
        } catch (\Throwable $e) {
            Log::error('Pos::productProducts - Failed to determine pos FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Product::class, BC::COL_POS_ID, BC::COL_POS_ID);
        }
    }

    public function products(): HasMany
    {
        try {
            $posTable = $this->getTable();

            $posProductsTable = (new PosProduct)->getTable();
            $productsTable = (new Product)->getTable();

            $localPosKey = Schema::hasColumn($posTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posTable, 'pos') ? 'pos' : $this->getKeyName());

            $posForeignKeyAlias = BC::COL_POS_ID;

            $posForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posProductsTable, 'pos') ? 'pos' : null);

            $posForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($productsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInPosProducts || !$posForeignKeyInProducts) {
                Log::error('Pos::products - Missing pos FK column in one or both tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'pos_products_table' => $posProductsTable,
                    'products_table' => $productsTable,
                    'pos_products_fk' => $posForeignKeyInPosProducts,
                    'products_fk' => $posForeignKeyInProducts,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return $this->hasMany(Product::class, $posForeignKeyAlias, $localPosKey);
            }

            $posProductsColumns = Schema::getColumnListing($posProductsTable);
            $productsColumns = Schema::getColumnListing($productsTable);

            $selectedColumns = array_values(array_unique(array_merge(
                $posProductsColumns,
                $productsColumns,
                [$posForeignKeyAlias, '_source']
            )));

            if (!\in_array('id', $selectedColumns, true)) $selectedColumns[] = 'id';

            $posProductsSelectParts = [];
            foreach ($selectedColumns as $column) {
                if ($column === '_source') {
                    $posProductsSelectParts[] = DB::raw("'pos_products' as `_source`");
                    continue;
                }
                if ($column === $posForeignKeyAlias) {
                    $posProductsSelectParts[] = DB::raw("`{$posProductsTable}`.`{$posForeignKeyInPosProducts}` as `{$posForeignKeyAlias}`");
                    continue;
                }
                $posProductsSelectParts[] = \in_array($column, $posProductsColumns, true)
                    ? DB::raw("`{$posProductsTable}`.`{$column}` as `{$column}`")
                    : DB::raw("NULL as `{$column}`");
            }

            $productsSelectParts = [];
            foreach ($selectedColumns as $column) {
                if ($column === '_source') {
                    $productsSelectParts[] = DB::raw("'products' as `_source`");
                    continue;
                }
                if ($column === $posForeignKeyAlias) {
                    $productsSelectParts[] = DB::raw("`{$productsTable}`.`{$posForeignKeyInProducts}` as `{$posForeignKeyAlias}`");
                    continue;
                }
                $productsSelectParts[] = \in_array($column, $productsColumns, true)
                    ? DB::raw("`{$productsTable}`.`{$column}` as `{$column}`")
                    : DB::raw("NULL as `{$column}`");
            }

            $posProductsQuery = DB::table($posProductsTable)->select($posProductsSelectParts);
            $productsQuery = DB::table($productsTable)->select($productsSelectParts);

            $unionQuery = $posProductsQuery->unionAll($productsQuery);

            $derivedAlias = 'pos_products_union';

            $relation = $this->hasMany(Product::class, $posForeignKeyAlias, $localPosKey);
            $relation->getQuery()->fromSub($unionQuery, $derivedAlias);

            Log::debug('Pos::products - Using union-backed HasMany', [
                'class' => static::class,
                'pos_products_table' => $posProductsTable,
                'products_table' => $productsTable,
                'pos_products_fk' => $posForeignKeyInPosProducts,
                'products_fk' => $posForeignKeyInProducts,
                'local_key' => $localPosKey,
                'alias' => $derivedAlias,
                'columns' => \count($selectedColumns),
            ]);

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Pos::products - Failed to build union relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(PosProduct::class, BC::COL_POS_ID, BC::COL_POS_ID);
        }
    }

    public function productServices(): HasMany
    {
        try {
            $posTable = $this->getTable();

            $posProductsTable = (new PosProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $localPosKey = Schema::hasColumn($posTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posTable, 'pos') ? 'pos' : $this->getKeyName());

            $posValue = $this->getAttribute($localPosKey);

            $posForeignKeyAlias = BC::COL_POS_ID;
            $productServiceForeignKeyAlias = BC::COL_PRD_SV_ID;

            $posForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posProductsTable, 'pos') ? 'pos' : null);

            $posForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($productsTable, 'pos') ? 'pos' : null);

            $productServiceForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($posProductsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($posProductsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($posProductsTable, 'service') ? 'service' : null)));

            $productServiceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($productsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($productsTable, 'service') ? 'service' : null)));

            $posProductsServiceIdsQuery = null;

            if ($posForeignKeyInPosProducts && $productServiceForeignKeyInPosProducts) {
                $posProductsServiceIdsQuery = DB::table($posProductsTable)
                    ->selectRaw(
                        "`{$posProductsTable}`.`{$posForeignKeyInPosProducts}` as `{$posForeignKeyAlias}`, " .
                            "`{$posProductsTable}`.`{$productServiceForeignKeyInPosProducts}` as `{$productServiceForeignKeyAlias}`"
                    )
                    ->where("{$posProductsTable}.{$posForeignKeyInPosProducts}", $posValue)
                    ->whereNotNull("{$posProductsTable}.{$productServiceForeignKeyInPosProducts}");
            }

            $productsServiceIdsQuery = null;

            if ($posForeignKeyInProducts && $productServiceForeignKeyInProducts) {
                $productsServiceIdsQuery = DB::table($productsTable)
                    ->selectRaw(
                        "`{$productsTable}`.`{$posForeignKeyInProducts}` as `{$posForeignKeyAlias}`, " .
                            "`{$productsTable}`.`{$productServiceForeignKeyInProducts}` as `{$productServiceForeignKeyAlias}`"
                    )
                    ->where("{$productsTable}.{$posForeignKeyInProducts}", $posValue)
                    ->whereNotNull("{$productsTable}.{$productServiceForeignKeyInProducts}");

                if ($posProductsServiceIdsQuery) {
                    $posProductsServiceIdsSubquery = DB::table($posProductsTable)
                        ->selectRaw("`{$posProductsTable}`.`{$productServiceForeignKeyInPosProducts}`")
                        ->where("{$posProductsTable}.{$posForeignKeyInPosProducts}", $posValue)
                        ->whereNotNull("{$posProductsTable}.{$productServiceForeignKeyInPosProducts}");

                    $productsServiceIdsQuery->whereNotIn(
                        "{$productsTable}.{$productServiceForeignKeyInProducts}",
                        $posProductsServiceIdsSubquery
                    );
                }
            }

            if (!$posProductsServiceIdsQuery && !$productsServiceIdsQuery) {
                Log::debug('Pos::productServices - No service ids could be sourced from either table', [
                    'class' => static::class,
                    'pos_value' => $posValue,
                    'pos_products_table' => $posProductsTable,
                    'products_table' => $productsTable,
                ]);

                return $this->hasMany(ProductService::class, 'id', $localPosKey)->whereRaw('1=0');
            }

            $serviceIdsUnionQuery = $posProductsServiceIdsQuery
                ? ($productsServiceIdsQuery ? $posProductsServiceIdsQuery->unionAll($productsServiceIdsQuery) : $posProductsServiceIdsQuery)
                : $productsServiceIdsQuery;

            $productServicesForPosQuery = DB::table("{$productServicesTable} as ps")
                ->joinSub($serviceIdsUnionQuery, 'src', "src.{$productServiceForeignKeyAlias}", '=', 'ps.id')
                ->selectRaw("ps.*, src.`{$posForeignKeyAlias}` as `{$posForeignKeyAlias}`")
                ->distinct();

            $derivedAlias = 'pos_product_services_union';

            $related = new ProductService();
            $related->setTable($derivedAlias);

            $derivedQuery = $related->newQuery()->fromSub($productServicesForPosQuery, $derivedAlias);

            return $this->newHasMany($derivedQuery, $this, "{$derivedAlias}.{$posForeignKeyAlias}", $localPosKey);
        } catch (\Throwable $e) {
            Log::error('Pos::productServices - Failed to build derived relation', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');
        }
    }

    public function posProductsRaw(): Collection
    {
        try {
            $posProductsTable = (new PosProduct)->getTable();

            $posForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posProductsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInPosProducts) {
                Log::error('Pos::posProductsRaw - No valid pos FK column found in pos_products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'pos_products_table' => $posProductsTable,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return collect([]);
            }

            $sql = "SELECT * FROM `{$posProductsTable}` WHERE `{$posForeignKeyInPosProducts}` = ?";
            $rows = DB::select($sql, [$this->getAttribute(BC::COL_POS_ID) ?? $this->getAttribute('pos') ?? $this->getKey()]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Pos::posProductsRaw - Failed to retrieve pos products', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function productProductsRaw(): Collection
    {
        try {
            $productsTable = (new Product)->getTable();

            $posForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($productsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInProducts) {
                Log::error('Pos::productProductsRaw - No valid pos FK column found in products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'products_table' => $productsTable,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return collect([]);
            }

            $sql = "SELECT * FROM `{$productsTable}` WHERE `{$posForeignKeyInProducts}` = ?";
            $rows = DB::select($sql, [$this->getAttribute(BC::COL_POS_ID) ?? $this->getAttribute('pos') ?? $this->getKey()]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Pos::productProductsRaw - Failed to retrieve products', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function productsRaw(): Collection
    {
        try {
            $posProductsTable = (new PosProduct)->getTable();
            $productsTable = (new Product)->getTable();

            $posForeignKeyAlias = BC::COL_POS_ID;

            $posForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posProductsTable, 'pos') ? 'pos' : null);

            $posForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($productsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInPosProducts || !$posForeignKeyInProducts) {
                Log::error('Pos::productsRaw - Missing pos FK column in one or both tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'pos_products_table' => $posProductsTable,
                    'products_table' => $productsTable,
                    'pos_products_fk' => $posForeignKeyInPosProducts,
                    'products_fk' => $posForeignKeyInProducts,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return collect([]);
            }

            $posValue = $this->getAttribute(BC::COL_POS_ID) ?? $this->getAttribute('pos') ?? $this->getKey();

            $posProductsColumns = Schema::getColumnListing($posProductsTable);
            $productsColumns = Schema::getColumnListing($productsTable);

            $selectedColumns = array_values(array_unique(array_merge(
                $posProductsColumns,
                $productsColumns,
                [$posForeignKeyAlias, '_source']
            )));

            if (!\in_array('id', $selectedColumns, true)) $selectedColumns[] = 'id';

            $posProductsSelectParts = [];
            foreach ($selectedColumns as $column) {
                if ($column === '_source') {
                    $posProductsSelectParts[] = "'pos_products' as `_source`";
                    continue;
                }
                if ($column === $posForeignKeyAlias) {
                    $posProductsSelectParts[] = "`{$posProductsTable}`.`{$posForeignKeyInPosProducts}` as `{$posForeignKeyAlias}`";
                    continue;
                }
                $posProductsSelectParts[] = \in_array($column, $posProductsColumns, true)
                    ? "`{$posProductsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $productsSelectParts = [];
            foreach ($selectedColumns as $column) {
                if ($column === '_source') {
                    $productsSelectParts[] = "'products' as `_source`";
                    continue;
                }
                if ($column === $posForeignKeyAlias) {
                    $productsSelectParts[] = "`{$productsTable}`.`{$posForeignKeyInProducts}` as `{$posForeignKeyAlias}`";
                    continue;
                }
                $productsSelectParts[] = \in_array($column, $productsColumns, true)
                    ? "`{$productsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $sql =
                "SELECT " . implode(', ', $posProductsSelectParts) . " FROM `{$posProductsTable}` WHERE `{$posForeignKeyInPosProducts}` = ? " .
                "UNION ALL " .
                "SELECT " . implode(', ', $productsSelectParts) . " FROM `{$productsTable}` WHERE `{$posForeignKeyInProducts}` = ?";

            $rows = DB::select($sql, [$posValue, $posValue]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Pos::productsRaw - Failed to retrieve union products', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function productServicesRaw(): Collection
    {
        try {
            $posProductsTable = (new PosProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $posValue = $this->getAttribute(BC::COL_POS_ID) ?? $this->getAttribute('pos') ?? $this->getKey();

            $posForeignKeyAlias = BC::COL_POS_ID;
            $productServiceForeignKeyAlias = BC::COL_PRD_SV_ID;

            $posForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posProductsTable, 'pos') ? 'pos' : null);

            $posForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($productsTable, 'pos') ? 'pos' : null);

            $productServiceForeignKeyInPosProducts = Schema::hasColumn($posProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($posProductsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($posProductsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($posProductsTable, 'service') ? 'service' : null)));

            $productServiceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($productsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($productsTable, 'service') ? 'service' : null)));

            $posProductsServiceIdsQuery = null;

            if ($posForeignKeyInPosProducts && $productServiceForeignKeyInPosProducts) {
                $posProductsServiceIdsQuery = DB::table($posProductsTable)
                    ->selectRaw(
                        "`{$posProductsTable}`.`{$posForeignKeyInPosProducts}` as `{$posForeignKeyAlias}`, " .
                            "`{$posProductsTable}`.`{$productServiceForeignKeyInPosProducts}` as `{$productServiceForeignKeyAlias}`"
                    )
                    ->where("{$posProductsTable}.{$posForeignKeyInPosProducts}", $posValue)
                    ->whereNotNull("{$posProductsTable}.{$productServiceForeignKeyInPosProducts}");
            }

            $productsServiceIdsQuery = null;

            if ($posForeignKeyInProducts && $productServiceForeignKeyInProducts) {
                $productsServiceIdsQuery = DB::table($productsTable)
                    ->selectRaw(
                        "`{$productsTable}`.`{$posForeignKeyInProducts}` as `{$posForeignKeyAlias}`, " .
                            "`{$productsTable}`.`{$productServiceForeignKeyInProducts}` as `{$productServiceForeignKeyAlias}`"
                    )
                    ->where("{$productsTable}.{$posForeignKeyInProducts}", $posValue)
                    ->whereNotNull("{$productsTable}.{$productServiceForeignKeyInProducts}");

                if ($posProductsServiceIdsQuery) {
                    $posProductsServiceIdsSubquery = DB::table($posProductsTable)
                        ->selectRaw("`{$posProductsTable}`.`{$productServiceForeignKeyInPosProducts}`")
                        ->where("{$posProductsTable}.{$posForeignKeyInPosProducts}", $posValue)
                        ->whereNotNull("{$posProductsTable}.{$productServiceForeignKeyInPosProducts}");

                    $productsServiceIdsQuery->whereNotIn(
                        "{$productsTable}.{$productServiceForeignKeyInProducts}",
                        $posProductsServiceIdsSubquery
                    );
                }
            }

            if (!$posProductsServiceIdsQuery && !$productsServiceIdsQuery) return collect([]);

            $serviceIdsUnionQuery = $posProductsServiceIdsQuery
                ? ($productsServiceIdsQuery ? $posProductsServiceIdsQuery->unionAll($productsServiceIdsQuery) : $posProductsServiceIdsQuery)
                : $productsServiceIdsQuery;

            $productServicesForPosQuery = DB::table("{$productServicesTable} as ps")
                ->joinSub($serviceIdsUnionQuery, 'src', "src.{$productServiceForeignKeyAlias}", '=', 'ps.id')
                ->selectRaw("ps.*, src.`{$posForeignKeyAlias}` as `{$posForeignKeyAlias}`")
                ->distinct();

            $rows = DB::select($productServicesForPosQuery->toSql(), $productServicesForPosQuery->getBindings());

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Pos::productServicesRaw - Failed to retrieve product services', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function posPosPayment(): HasOne
    {
        try {
            $posTable = $this->getTable();
            $posPaymentsTable = (new PosPayment)->getTable();

            $localPosKey = Schema::hasColumn($posTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posTable, 'pos') ? 'pos' : $this->getKeyName());

            $posForeignKeyInPosPayments = Schema::hasColumn($posPaymentsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posPaymentsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInPosPayments) {
                Log::error('Pos::posPosPayment - No valid pos FK column found in pos_payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'pos_payments_table' => $posPaymentsTable,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return $this->hasOne(PosPayment::class, BC::COL_POS_ID, $localPosKey);
            }

            return $this->hasOne(PosPayment::class, $posForeignKeyInPosPayments, $localPosKey);
        } catch (\Throwable $e) {
            Log::error('Pos::posPosPayment - Failed to determine pos FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasOne(PosPayment::class, BC::COL_POS_ID, BC::COL_POS_ID);
        }
    }

    public function paymentPayment(): HasOne
    {
        try {
            $posTable = $this->getTable();
            $paymentsTable = (new Payment)->getTable();

            $localPosKey = Schema::hasColumn($posTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($posTable, 'pos') ? 'pos' : $this->getKeyName());

            $posForeignKeyInPayments = Schema::hasColumn($paymentsTable, BC::COL_POS_ID)
                ? BC::COL_POS_ID
                : (Schema::hasColumn($paymentsTable, 'pos') ? 'pos' : null);

            if (!$posForeignKeyInPayments) {
                Log::error('Pos::paymentPayment - No valid pos FK column found in payments table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'payments_table' => $paymentsTable,
                    'checked_columns' => [BC::COL_POS_ID, 'pos'],
                ]);

                return $this->hasOne(Payment::class, BC::COL_POS_ID, $localPosKey);
            }

            return $this->hasOne(Payment::class, $posForeignKeyInPayments, $localPosKey);
        } catch (\Throwable $e) {
            Log::error('Pos::paymentPayment - Failed to determine pos FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasOne(Payment::class, BC::COL_POS_ID, BC::COL_POS_ID);
        }
    }

    public function posPayment(): HasOne
    {
        try {
            $posPaymentsTable = (new PosPayment)->getTable();
            $paymentsTable = (new Payment)->getTable();

            $posFkInPosPayments = Schema::hasColumn($posPaymentsTable, BC::COL_POS_ID) || Schema::hasColumn($posPaymentsTable, 'pos');
            $posFkInPayments = Schema::hasColumn($paymentsTable, BC::COL_POS_ID) || Schema::hasColumn($paymentsTable, 'pos');

            if ($posFkInPosPayments) return $this->posPosPayment();
            if ($posFkInPayments) return $this->paymentPayment();

            Log::error('Pos::posPayment - No valid pos FK column found in either pos_payments or payments table', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'pos_payments_table' => $posPaymentsTable,
                'payments_table' => $paymentsTable,
                'checked_columns' => [BC::COL_POS_ID, 'pos'],
            ]);

            return $this->posPosPayment();
        } catch (\Throwable $e) {
            Log::error('Pos::posPayment - Failed to select pos payment source', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->posPosPayment();
        }
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_id')
            ->where('payment_type', TransactionType::Pos);
    }

    public function getSubTotal(): float
    {
        return (float) $this->items->sum(
            fn($p) => (float) $p->price * (float) $p->quantity // @phpstan-ignore property.notFound, property.notFound
        );
    }

    public function getTotalDiscount(): float
    {
        return (float) $this->items->sum('discount');
    }

    public function getTotalTax(): float
    {
        return (float) $this->items->sum(function ($p) {
            $rate = (float) Utility::totalTaxRate($p->tax); // @phpstan-ignore property.notFound
            $base = (float) $p->price * (float) $p->quantity; // @phpstan-ignore property.notFound, property.notFound

            return ($rate / 100.0) * $base;
        });
    }

    public function getTotal(): float
    {
        return $this->getSubTotal()
            - $this->getTotalDiscount()
            + $this->getTotalTax();
    }

    /**
     * Get total POS amount
     */
    public static function totalPosAmount(bool $month = false): string|RedirectResponse
    {
        return app(PosRequestService::class)->getTotalPosAmount($month);
    }

    /**
     * Get POS report chart data for last 10 days
     */
    public static function getPosReportChart(): array|RedirectResponse
    {
        return app(PosRequestService::class)->getPosReportChart();
    }
}
