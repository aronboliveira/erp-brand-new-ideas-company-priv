<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{
    BillStatus,
    PaymentStatus,
    TransactionType,
    UserType
};
use App\Traits\{
    DefinesDates,
    HasAuditFields,
    NormalizesAddresses,
    StoresManyRefJson,
    UsesCountryRegions,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne,
    Relations\HasManyThrough
};
use Illuminate\Support\Facades\{
    DB,
    Log,
    Schema
};
use Illuminate\Support\Collection;

class Invoice extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use StoresManyRefJson;
    use NormalizesAddresses;
    use UsesCountryRegions;
    use DefinesDates;

    protected $table = DC::TABLE_INVS;
    protected $fillable = [
        // identificadores
        BC::COL_INV_ID,
        BC::COL_CST_ID,
        BC::COL_BL_ID,
        BC::COL_TAX_ID,

        // emissão / valores
        BC::COL_CUR_ID,
        'amount',
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        BC::COL_PPS_CD,
        'reference',
        BC::COL_REF_N,
        'description',
        'notes',
        'attachments',
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
        'contract',
        'loan',
        BC::COL_PRD_SV_UNT,

        // datas / categoria / status
        BC::COL_SD_DT,
        BC::COL_ISS_DT,
        PJC::COL_D_DATE,
        BC::COL_CAT_ID,
        PJC::COL_STATUS,
        BC::COL_STT_LB,
        BC::COL_PAY_STT,

        // frete / desconto
        BC::COL_SHIP_DSP,
        BC::COL_DSC_APL,
        'discount',
        'taxes',

        // endereço de envio
        BC::COL_SHIP_NAME,
        BC::COL_SHIP_EMAIL,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_ZIP,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_DTL,

        // endereço de cobrança
        BC::COL_BL_NAME,
        BC::COL_BL_EMAIL,
        BC::COL_BL_TEL,
        BC::COL_BL_ZIP,
        BC::COL_BL_ADR,
        BC::COL_BL_ST,
        BC::COL_BL_CTY,
        BC::COL_BL_CTR,
        BC::COL_BL_DTL,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'discount'         => 'decimal:2',
        BC::COL_SVC_FEE    => 'decimal:2',
        BC::COL_TXS_FEE    => 'decimal:2',
        BC::COL_AUTORCC    => 'boolean',
        BC::COL_DSC_APL    => 'boolean',
        BC::COL_SD_DT      => 'date',
        BC::COL_ISS_DT     => 'date',
        PJC::COL_D_DATE    => 'date',
        'attachments'      => 'array',
        'taxes'            => 'array',
        BC::COL_TC         => 'array',
        BC::COL_RCC_RL     => 'array',
    ];

    /**
     * Map de status legados (campo numérico `status` -> rótulo).
     */
    public static array $statuses = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partially Paid',
        'Paid',
    ];

    protected static function booted(): void
    {
        parent::booted();
        // todo too heavy for mocking, use only for production
        // static::saving(function (self $m): void {
        //     if (empty($m->getAttribute(BC::COL_STT_LB)))
        //         $m->setAttribute(BC::COL_STT_LB, BillStatus::Draft->value);
        //     if (empty($m->getAttribute(BC::COL_PAY_STT)))
        //         $m->setAttribute(BC::COL_PAY_STT, PaymentStatus::Processing->value);
        //     $amount   = (float) ($m->getAttribute('amount') ?? 0.0);
        //     $discount = (float) ($m->getAttribute('discount') ?? 0.0);
        //     if ($discount < 0.0)
        //         $discount = 0.0;
        //     elseif ($discount > $amount)
        //         $discount = $amount;
        //     $m->setAttribute('amount', $amount);
        //     $m->setAttribute('discount', $discount);
        //     $isNormalizeCountryCallable = is_callable([self::class, 'normalizeBillingCountry']);
        //     if ($isNormalizeCountryCallable)
        //         self::normalizeBillingCountry($m);
        //     $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
        //     if (!empty($m->getAttribute(BC::COL_BL_EMAIL)) && $isNormalizeEmailCallable)
        //         $m->setAttribute(BC::COL_BL_EMAIL, self::normalizeEmail($m->getAttribute(BC::COL_BL_EMAIL), $m->getAttribute(BC::COL_BL_NAME) ?? null, $m->getAttribute('id') ?? null));
        //     if (!empty($m->getAttribute(BC::COL_SHIP_EMAIL)) && $isNormalizeEmailCallable)
        //         $m->setAttribute(BC::COL_SHIP_EMAIL, self::normalizeEmail($m->getAttribute(BC::COL_SHIP_EMAIL), $m->getAttribute(BC::COL_SHIP_NAME) ?? null, $m->getAttribute('id') ?? null));
        //     $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
        //     if (!empty($m->getAttribute(BC::COL_BL_TEL)) && $isNormalizePhoneCallable)
        //         $m->setAttribute(BC::COL_BL_TEL, self::normalizePhone($m->getAttribute(BC::COL_BL_TEL), $m->getAttribute(BC::COL_BL_NAME) ?? null, $m->getAttribute('id') ?? null));
        //     if (!empty($m->getAttribute(BC::COL_SHIP_TEL)) && $isNormalizePhoneCallable)
        //         $m->setAttribute(BC::COL_SHIP_TEL, self::normalizePhone($m->getAttribute(BC::COL_SHIP_TEL), $m->getAttribute(BC::COL_SHIP_NAME) ?? null, $m->getAttribute('id') ?? null));
        //     $isNormalizeZipCallable = is_callable([self::class, 'normalizeZip']);
        //     if (!empty($m->getAttribute(BC::COL_BL_ZIP)) && !empty($m->getAttribute(BC::COL_BL_CTR)) && $isNormalizeZipCallable)
        //         $m->setAttribute(BC::COL_BL_ZIP, self::normalizeZip($m->getAttribute(BC::COL_BL_ZIP), $m->getAttribute(BC::COL_BL_CTR), $m->getAttribute(BC::COL_BL_CTR) ?? null, $m->getAttribute('id') ?? null));
        //     if (!empty($m->getAttribute(BC::COL_SHIP_ZIP)) && !empty($m->getAttribute(BC::COL_SHIP_CTR)) && $isNormalizeZipCallable)
        //         $m->setAttribute(BC::COL_SHIP_ZIP, self::normalizeZip($m->getAttribute(BC::COL_SHIP_ZIP), $m->getAttribute(BC::COL_SHIP_CTR), $m->getAttribute(BC::COL_SHIP_NAME) ?? null, $m->getAttribute('id') ?? null));
        //     $m->setAttribute('attachments', static::normalizeArrayField($m->getAttribute('attachments') ?? null));
        //     $m->setAttribute('taxes', static::normalizeArrayField($m->getAttribute('taxes') ?? null));
        //     $m->setAttribute(BC::COL_TC, static::normalizeArrayField($m->getAttribute(BC::COL_TC) ?? null));
        //     $m->setAttribute(BC::COL_RCC_RL, static::normalizeArrayField($m->getAttribute(BC::COL_RCC_RL) ?? null));
        // });
    }

    public function getStatusAttribute($value): string
    {
        $index = (int) $value;

        return static::$statuses[$index] ?? static::$statuses[0];
    }

    public function setStatusAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes[PJC::COL_STATUS] = (int) $value;
            return;
        }

        $normalized = strtolower((string) $value);
        $map        = [];

        foreach (static::$statuses as $i => $label) {
            $map[strtolower($label)] = $i;
        }

        $index = $map[$normalized] ?? 0;
        $this->attributes[PJC::COL_STATUS] = $index;
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

    public function tax(): ?BelongsTo
    {
        return $this->belongsTo(
            Tax::class,
            BC::COL_TAX_ID,
            'id'
        );
    }

    public function taxes(): ?BelongsTo
    {
        return $this->tax();
    }

    public function items(): HasMany
    {
        return $this->products();
    }

    public function invoiceProducts(): HasMany
    {
        try {
            $table = (new InvoiceProduct)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::invoiceProducts - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return $this->hasMany(InvoiceProduct::class, BC::COL_INV_ID, 'id');
            }

            Log::debug('Invoice::invoiceProducts - Using invoice column', ['column' => $fk]);
            return $this->hasMany(InvoiceProduct::class, $fk, 'id');
        } catch (\Throwable $e) {
            Log::error('Invoice::invoiceProducts - Failed', ['error' => $e->getMessage()]);
            return $this->hasMany(InvoiceProduct::class, BC::COL_INV_ID, 'id');
        }
    }

    public function productProducts(): HasMany
    {
        try {
            $table = (new Product)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::productProducts - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return $this->hasMany(Product::class, BC::COL_INV_ID, 'id');
            }

            Log::debug('Invoice::productProducts - Using invoice column', ['column' => $fk]);
            return $this->hasMany(Product::class, $fk, 'id');
        } catch (\Throwable $e) {
            Log::error('Invoice::productProducts - Failed', ['error' => $e->getMessage()]);
            return $this->hasMany(Product::class, BC::COL_INV_ID, 'id');
        }
    }

    public function products(): Collection
    {
        try {
            $fromInvoiceProducts = $this->invoiceProducts;
            $fromProducts = $this->productProducts;

            Log::debug('Invoice::products - Retrieved from both sources', [
                'invoice_id' => $this->id,
                'invoice_products' => $fromInvoiceProducts->count(),
                'products' => $fromProducts->count(),
            ]);

            return $fromInvoiceProducts->merge($fromProducts);
        } catch (\Throwable $e) {
            Log::error('Invoice::products - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    public function productServices(): HasMany
    {
        try {
            $invoiceProductsTable = (new InvoiceProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $invoiceIdAlias = BC::COL_INV_ID;
            $productServiceIdAlias = BC::COL_PRD_SV_ID;

            $invoiceId = $this->getKey();
            $invoiceKeyName = $this->getKeyName();

            $invoiceForeignKeyInInvoiceProducts = Schema::hasColumn($invoiceProductsTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($invoiceProductsTable, 'invoice') ? 'invoice' : null);

            $invoiceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($productsTable, 'invoice') ? 'invoice' : null);

            $productServiceForeignKeyInInvoiceProducts = Schema::hasColumn($invoiceProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($invoiceProductsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($invoiceProductsTable, 'service') ? 'service' : null));

            $productServiceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($productsTable, 'service') ? 'service' : null));

            if (!$invoiceForeignKeyInInvoiceProducts && !$invoiceForeignKeyInProducts) {
                Log::error('Invoice::productServices - No valid invoice FK column found in either source table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'invoice_products_table' => $invoiceProductsTable,
                    'products_table' => $productsTable,
                    'checked_columns' => [BC::COL_INV_ID, 'invoice'],
                ]);

                return $this->hasMany(ProductService::class, 'id', $invoiceKeyName)->whereRaw('1=0');
            }

            $invoiceProductsServiceIdsQuery = null;

            if ($invoiceForeignKeyInInvoiceProducts && $productServiceForeignKeyInInvoiceProducts) {
                $invoiceProductsServiceIdsQuery = DB::table($invoiceProductsTable)
                    ->selectRaw(
                        "`{$invoiceProductsTable}`.`{$invoiceForeignKeyInInvoiceProducts}` as `{$invoiceIdAlias}`, " .
                            "`{$invoiceProductsTable}`.`{$productServiceForeignKeyInInvoiceProducts}` as `{$productServiceIdAlias}`"
                    )
                    ->where("{$invoiceProductsTable}.{$invoiceForeignKeyInInvoiceProducts}", $invoiceId)
                    ->whereNotNull("{$invoiceProductsTable}.{$productServiceForeignKeyInInvoiceProducts}");
            }

            $productsServiceIdsQuery = null;

            if ($invoiceForeignKeyInProducts && $productServiceForeignKeyInProducts) {
                $productsServiceIdsQuery = DB::table($productsTable)
                    ->selectRaw(
                        "`{$productsTable}`.`{$invoiceForeignKeyInProducts}` as `{$invoiceIdAlias}`, " .
                            "`{$productsTable}`.`{$productServiceForeignKeyInProducts}` as `{$productServiceIdAlias}`"
                    )
                    ->where("{$productsTable}.{$invoiceForeignKeyInProducts}", $invoiceId)
                    ->whereNotNull("{$productsTable}.{$productServiceForeignKeyInProducts}");

                if ($invoiceProductsServiceIdsQuery) {
                    $invoiceProductsServiceIdSubquery = DB::table($invoiceProductsTable)
                        ->selectRaw("`{$invoiceProductsTable}`.`{$productServiceForeignKeyInInvoiceProducts}`")
                        ->where("{$invoiceProductsTable}.{$invoiceForeignKeyInInvoiceProducts}", $invoiceId)
                        ->whereNotNull("{$invoiceProductsTable}.{$productServiceForeignKeyInInvoiceProducts}");

                    $productsServiceIdsQuery->whereNotIn("{$productsTable}.{$productServiceForeignKeyInProducts}", $invoiceProductsServiceIdSubquery);
                }
            }

            if (!$invoiceProductsServiceIdsQuery && !$productsServiceIdsQuery) {
                Log::debug('Invoice::productServices - No valid product_service FK column found in either source table', [
                    'class' => static::class,
                    'invoice_id' => $invoiceId,
                    'invoice_products_table' => $invoiceProductsTable,
                    'products_table' => $productsTable,
                    'checked_service_columns' => [BC::COL_PRD_SV_ID, 'product_service', 'service'],
                ]);

                return $this->hasMany(ProductService::class, 'id', $invoiceKeyName)->whereRaw('1=0');
            }

            $serviceIdsUnionQuery = $invoiceProductsServiceIdsQuery
                ? ($productsServiceIdsQuery ? $invoiceProductsServiceIdsQuery->unionAll($productsServiceIdsQuery) : $invoiceProductsServiceIdsQuery)
                : $productsServiceIdsQuery;

            $productServicesForInvoiceQuery = DB::table("{$productServicesTable} as ps")
                ->joinSub($serviceIdsUnionQuery, 'src', "src.{$productServiceIdAlias}", '=', 'ps.id')
                ->selectRaw("ps.*, ? as `{$invoiceIdAlias}`", [$invoiceId])
                ->distinct();

            $derivedAlias = 'invoice_product_services_union';

            $related = new ProductService();
            $related->setTable($derivedAlias);

            $derivedQuery = $related->newQuery()->fromSub($productServicesForInvoiceQuery, $derivedAlias);

            Log::debug('Invoice::productServices - Built union-backed HasMany (deduped)', [
                'class' => static::class,
                'invoice_id' => $invoiceId,
                'invoice_products_table' => $invoiceProductsTable,
                'products_table' => $productsTable,
                'product_services_table' => $productServicesTable,
                'invoice_products_invoice_fk' => $invoiceForeignKeyInInvoiceProducts,
                'products_invoice_fk' => $invoiceForeignKeyInProducts,
                'invoice_products_service_fk' => $productServiceForeignKeyInInvoiceProducts,
                'products_service_fk' => $productServiceForeignKeyInProducts,
                'derived_alias' => $derivedAlias,
                'products_filtered_against_invoice_products' => (bool) $invoiceProductsServiceIdsQuery && (bool) $productsServiceIdsQuery,
            ]);

            return $this->newHasMany($derivedQuery, $this, "{$derivedAlias}.{$invoiceIdAlias}", $invoiceKeyName);
        } catch (\Throwable $e) {
            Log::error('Invoice::productServices - Failed to build union-backed HasMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');
        }
    }

    public function invoiceProductsRaw(): Collection
    {
        try {
            $table = (new InvoiceProduct)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::invoiceProductsRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return collect([]);
            }

            $rows = DB::select("SELECT * FROM {$table} WHERE {$fk} = ?", [$this->id]);

            Log::debug('Invoice::invoiceProductsRaw - Retrieved rows', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'count' => \count($rows),
            ]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Invoice::invoiceProductsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    public function productProductsRaw(): Collection
    {
        try {
            $table = (new Product)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::productProductsRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return collect([]);
            }

            $rows = DB::select("SELECT * FROM {$table} WHERE {$fk} = ?", [$this->id]);

            Log::debug('Invoice::productProductsRaw - Retrieved rows', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'count' => \count($rows),
            ]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Invoice::productProductsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    public function productsRaw(): Collection
    {
        try {
            $fromInvoiceProducts = $this->invoiceProductsRaw();
            $fromProducts = $this->productProductsRaw();

            Log::debug('Invoice::productsRaw - Retrieved from both sources', [
                'invoice_id' => $this->id,
                'invoice_products' => $fromInvoiceProducts->count(),
                'products' => $fromProducts->count(),
            ]);

            return $fromInvoiceProducts->merge($fromProducts);
        } catch (\Throwable $e) {
            Log::error('Invoice::productsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    public function productServicesRaw(): Collection
    {
        try {
            $invoiceProductsTable = (new InvoiceProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $invoiceIdAlias = BC::COL_INV_ID;
            $productServiceIdAlias = BC::COL_PRD_SV_ID;

            $invoiceId = $this->getKey();

            $invoiceForeignKeyInInvoiceProducts = Schema::hasColumn($invoiceProductsTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($invoiceProductsTable, 'invoice') ? 'invoice' : null);

            $invoiceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_INV_ID)
                ? BC::COL_INV_ID
                : (Schema::hasColumn($productsTable, 'invoice') ? 'invoice' : null);

            $productServiceForeignKeyInInvoiceProducts = Schema::hasColumn($invoiceProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($invoiceProductsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($invoiceProductsTable, 'service') ? 'service' : null));

            $productServiceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($productsTable, 'service') ? 'service' : null));

            $invoiceProductsServiceIdsQuery = null;

            if ($invoiceForeignKeyInInvoiceProducts && $productServiceForeignKeyInInvoiceProducts) {
                $invoiceProductsServiceIdsQuery = DB::table($invoiceProductsTable)
                    ->selectRaw(
                        "`{$invoiceProductsTable}`.`{$invoiceForeignKeyInInvoiceProducts}` as `{$invoiceIdAlias}`, " .
                            "`{$invoiceProductsTable}`.`{$productServiceForeignKeyInInvoiceProducts}` as `{$productServiceIdAlias}`"
                    )
                    ->where("{$invoiceProductsTable}.{$invoiceForeignKeyInInvoiceProducts}", $invoiceId)
                    ->whereNotNull("{$invoiceProductsTable}.{$productServiceForeignKeyInInvoiceProducts}");
            }

            $productsServiceIdsQuery = null;

            if ($invoiceForeignKeyInProducts && $productServiceForeignKeyInProducts) {
                $productsServiceIdsQuery = DB::table($productsTable)
                    ->selectRaw(
                        "`{$productsTable}`.`{$invoiceForeignKeyInProducts}` as `{$invoiceIdAlias}`, " .
                            "`{$productsTable}`.`{$productServiceForeignKeyInProducts}` as `{$productServiceIdAlias}`"
                    )
                    ->where("{$productsTable}.{$invoiceForeignKeyInProducts}", $invoiceId)
                    ->whereNotNull("{$productsTable}.{$productServiceForeignKeyInProducts}");

                if ($invoiceProductsServiceIdsQuery) {
                    $invoiceProductsServiceIdSubquery = DB::table($invoiceProductsTable)
                        ->selectRaw("`{$invoiceProductsTable}`.`{$productServiceForeignKeyInInvoiceProducts}`")
                        ->where("{$invoiceProductsTable}.{$invoiceForeignKeyInInvoiceProducts}", $invoiceId)
                        ->whereNotNull("{$invoiceProductsTable}.{$productServiceForeignKeyInInvoiceProducts}");

                    $productsServiceIdsQuery->whereNotIn("{$productsTable}.{$productServiceForeignKeyInProducts}", $invoiceProductsServiceIdSubquery);
                }
            }

            if (!$invoiceProductsServiceIdsQuery && !$productsServiceIdsQuery) {
                Log::debug('Invoice::productServicesRaw - No service ids could be sourced', [
                    'class' => static::class,
                    'invoice_id' => $invoiceId,
                    'invoice_products_table' => $invoiceProductsTable,
                    'products_table' => $productsTable,
                ]);

                return collect([]);
            }

            $serviceIdsUnionQuery = $invoiceProductsServiceIdsQuery
                ? ($productsServiceIdsQuery ? $invoiceProductsServiceIdsQuery->unionAll($productsServiceIdsQuery) : $invoiceProductsServiceIdsQuery)
                : $productsServiceIdsQuery;

            $productServicesForInvoiceQuery = DB::table("{$productServicesTable} as ps")
                ->joinSub($serviceIdsUnionQuery, 'src', "src.{$productServiceIdAlias}", '=', 'ps.id')
                ->selectRaw("ps.*, ? as `{$invoiceIdAlias}`", [$invoiceId])
                ->distinct();

            $sql = $productServicesForInvoiceQuery->toSql();
            $bindings = $productServicesForInvoiceQuery->getBindings();

            $rows = DB::select($sql, $bindings);

            Log::debug('Invoice::productServicesRaw - Retrieved product services (deduped)', [
                'class' => static::class,
                'invoice_id' => $invoiceId,
                'count' => \count($rows),
            ]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Invoice::productServicesRaw - Failed to retrieve product services', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    /**
     * Get invoice payments (junction table records)
     */
    public function invoicePayments(): HasMany
    {
        try {
            $table = (new InvoicePayment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::invoicePayments - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return $this->hasMany(InvoicePayment::class, BC::COL_INV_ID, 'id');
            }

            Log::debug('Invoice::invoicePayments - Using invoice column', ['column' => $fk]);
            return $this->hasMany(InvoicePayment::class, $fk, 'id');
        } catch (\Throwable $e) {
            Log::error('Invoice::invoicePayments - Failed', ['error' => $e->getMessage()]);
            return $this->hasMany(InvoicePayment::class, BC::COL_INV_ID, 'id');
        }
    }

    /**
     * Get payments directly from Payment table
     */
    public function paymentPayments(): HasMany
    {
        try {
            $table = (new Payment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::paymentPayments - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return $this->hasMany(Payment::class, BC::COL_INV_ID, 'id');
            }

            Log::debug('Invoice::paymentPayments - Using invoice column', ['column' => $fk]);
            return $this->hasMany(Payment::class, $fk, 'id');
        } catch (\Throwable $e) {
            Log::error('Invoice::paymentPayments - Failed', ['error' => $e->getMessage()]);
            return $this->hasMany(Payment::class, BC::COL_INV_ID, 'id');
        }
    }

    /**
     * Get all payments (merged from both sources)
     */
    public function payments(): HasMany
    {
        try {
            $junctionTable = (new InvoicePayment)->getTable();
            $directTable = (new Payment)->getTable();
            $fkAlias = BC::COL_INV_ID;

            $junctionFk = Schema::hasColumn($junctionTable, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($junctionTable, 'invoice') ? 'invoice' : null);
            $directFk = Schema::hasColumn($directTable, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($directTable, 'invoice') ? 'invoice' : null);

            if (!$junctionFk || !$directFk) {
                Log::error('Invoice::payments - Missing invoice FK in one or both tables', [
                    'junction_table' => $junctionTable,
                    'direct_table' => $directTable,
                    'junction_fk' => $junctionFk,
                    'direct_fk' => $directFk,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return $this->hasMany(Payment::class, $fkAlias, 'id');
            }

            $junctionCols = Schema::getColumnListing($junctionTable);
            $directCols = Schema::getColumnListing($directTable);
            $allCols = array_values(array_unique(array_merge($junctionCols, $directCols, [$fkAlias, '_source'])));
            if (!\in_array('id', $allCols, true)) $allCols[] = 'id';

            $junctionSelect = [];
            foreach ($allCols as $col) {
                if ($col === '_source') {
                    $junctionSelect[] = DB::raw("'invoice_payments' as `_source`");
                    continue;
                }
                if ($col === $fkAlias) {
                    $junctionSelect[] = DB::raw("`{$junctionTable}`.`{$junctionFk}` as `{$fkAlias}`");
                    continue;
                }
                $junctionSelect[] = \in_array($col, $junctionCols, true)
                    ? DB::raw("`{$junctionTable}`.`{$col}` as `{$col}`")
                    : DB::raw("NULL as `{$col}`");
            }

            $directSelect = [];
            foreach ($allCols as $col) {
                if ($col === '_source') {
                    $directSelect[] = DB::raw("'payments' as `_source`");
                    continue;
                }
                if ($col === $fkAlias) {
                    $directSelect[] = DB::raw("`{$directTable}`.`{$directFk}` as `{$fkAlias}`");
                    continue;
                }
                $directSelect[] = \in_array($col, $directCols, true)
                    ? DB::raw("`{$directTable}`.`{$col}` as `{$col}`")
                    : DB::raw("NULL as `{$col}`");
            }

            $junction = DB::table($junctionTable)->select($junctionSelect);
            $direct = DB::table($directTable)->select($directSelect);
            $union = $junction->unionAll($direct);

            $rel = $this->hasMany(Payment::class, $fkAlias, 'id');
            $rel->getQuery()->fromSub($union, 'payments_union');

            Log::debug('Invoice::payments - Using union relation', [
                'junction_table' => $junctionTable,
                'direct_table' => $directTable,
                'fk_alias' => $fkAlias,
                'junction_fk' => $junctionFk,
                'direct_fk' => $directFk,
                'columns' => \count($allCols),
            ]);

            return $rel;
        } catch (\Throwable $e) {
            Log::error('Invoice::payments - Failed to build union relation', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return $this->hasMany(Payment::class, BC::COL_INV_ID, 'id');
        }
    }

    /**
     * Get invoice payments using raw SQL query
     */
    public function invoicePaymentsRaw(): Collection
    {
        try {
            $table = (new InvoicePayment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::invoicePaymentsRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return collect([]);
            }

            $rows = DB::select("SELECT * FROM {$table} WHERE {$fk} = ?", [$this->id]);

            Log::debug('Invoice::invoicePaymentsRaw - Retrieved rows', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'count' => \count($rows)
            ]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Invoice::invoicePaymentsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return collect([]);
        }
    }

    /**
     * Get payments directly from Payment table using raw SQL query
     */
    public function paymentPaymentsRaw(): Collection
    {
        try {
            $table = (new Payment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::paymentPaymentsRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return collect([]);
            }

            $rows = DB::select("SELECT * FROM {$table} WHERE {$fk} = ?", [$this->id]);

            Log::debug('Invoice::paymentPaymentsRaw - Retrieved rows', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'count' => \count($rows)
            ]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Invoice::paymentPaymentsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return collect([]);
        }
    }

    /**
     * Get all payments using raw SQL (merged from both sources)
     */
    public function paymentsRaw(): Collection
    {
        try {
            $fromInvoicePayments = $this->invoicePaymentsRaw();
            $fromPayments = $this->paymentPaymentsRaw();

            Log::debug('Invoice::paymentsRaw - Retrieved from both sources', [
                'invoice_id' => $this->id,
                'invoice_payments' => $fromInvoicePayments->count(),
                'payments' => $fromPayments->count()
            ]);

            return $fromInvoicePayments->merge($fromPayments);
        } catch (\Throwable $e) {
            Log::error('Invoice::paymentsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return collect([]);
        }
    }

    /**
     * Get last invoice payment (from InvoicePayment table)
     */
    public function lastInvoicePayment(): ?HasOne
    {
        try {
            $table = (new InvoicePayment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::lastInvoicePayment - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return $this->hasOne(InvoicePayment::class, BC::COL_INV_ID, 'id')->latestOfMany();
            }

            Log::debug('Invoice::lastInvoicePayment - Using invoice column', ['column' => $fk]);
            return $this->hasOne(InvoicePayment::class, $fk, 'id')->latestOfMany();
        } catch (\Throwable $e) {
            Log::error('Invoice::lastInvoicePayment - Failed', ['error' => $e->getMessage()]);
            return $this->hasOne(InvoicePayment::class, BC::COL_INV_ID, 'id')->latestOfMany();
        }
    }

    /**
     * Get last payment (from Payment table)
     */
    public function lastPaymentPayment(): ?HasOne
    {
        try {
            $table = (new Payment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::lastPaymentPayment - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return $this->hasOne(Payment::class, BC::COL_INV_ID, 'id')->latestOfMany();
            }

            Log::debug('Invoice::lastPaymentPayment - Using invoice column', ['column' => $fk]);
            return $this->hasOne(Payment::class, $fk, 'id')->latestOfMany();
        } catch (\Throwable $e) {
            Log::error('Invoice::lastPaymentPayment - Failed', ['error' => $e->getMessage()]);
            return $this->hasOne(Payment::class, BC::COL_INV_ID, 'id')->latestOfMany();
        }
    }

    /**
     * Get last payment from both sources
     */
    public function lastPayment(): ?HasOne
    {
        try {
            $fromInvoicePayments = $this->lastInvoicePayment()->first();
            $fromPayments = $this->lastPaymentPayment()->first();

            if (!$fromInvoicePayments && !$fromPayments) {
                Log::debug('Invoice::lastPayment - No payments found', ['invoice_id' => $this->id]);
                return null;
            }

            if (!$fromInvoicePayments) return $this->lastPaymentPayment();
            if (!$fromPayments) return $this->lastInvoicePayment();

            $invoicePaymentDate = $fromInvoicePayments->created_at ?? $fromInvoicePayments->updated_at ?? null;
            $paymentDate = $fromPayments->created_at ?? $fromPayments->updated_at ?? null;

            if (!$invoicePaymentDate) return $this->lastPaymentPayment();
            if (!$paymentDate) return $this->lastInvoicePayment();

            $selected = $invoicePaymentDate > $paymentDate ? $this->lastInvoicePayment() : $this->lastPaymentPayment();

            Log::debug('Invoice::lastPayment - Retrieved from both sources', [
                'invoice_id' => $this->id,
                'selected_from' => $invoicePaymentDate > $paymentDate ? 'InvoicePayment' : 'Payment'
            ]);

            return $selected;
        } catch (\Throwable $e) {
            Log::error('Invoice::lastPayment - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get last invoice payment using raw SQL query
     */
    public function lastInvoicePaymentRaw(): ?object
    {
        try {
            $table = (new InvoicePayment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::lastInvoicePaymentRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return null;
            }

            $rows = DB::select(
                "SELECT * FROM {$table} WHERE {$fk} = ? ORDER BY created_at DESC, id DESC LIMIT 1",
                [$this->id]
            );

            $row = $rows[0] ?? null;

            Log::debug('Invoice::lastInvoicePaymentRaw - Retrieved row', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'found' => $row !== null
            ]);

            return $row;
        } catch (\Throwable $e) {
            Log::error('Invoice::lastInvoicePaymentRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get last payment from Payment table using raw SQL query
     */
    public function lastPaymentPaymentRaw(): ?object
    {
        try {
            $table = (new Payment)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::lastPaymentPaymentRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice']
                ]);
                return null;
            }

            $rows = DB::select(
                "SELECT * FROM {$table} WHERE {$fk} = ? ORDER BY created_at DESC, id DESC LIMIT 1",
                [$this->id]
            );

            $row = $rows[0] ?? null;

            Log::debug('Invoice::lastPaymentPaymentRaw - Retrieved row', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'found' => $row !== null
            ]);

            return $row;
        } catch (\Throwable $e) {
            Log::error('Invoice::lastPaymentPaymentRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get last payment from both sources using raw SQL
     */
    public function lastPaymentRaw(): ?object
    {
        try {
            $fromInvoicePayments = $this->lastInvoicePaymentRaw();
            $fromPayments = $this->lastPaymentPaymentRaw();

            if (!$fromInvoicePayments && !$fromPayments) {
                Log::debug('Invoice::lastPaymentRaw - No payments found', ['invoice_id' => $this->id]);
                return null;
            }

            if (!$fromInvoicePayments) return $fromPayments;
            if (!$fromPayments) return $fromInvoicePayments;

            $invoicePaymentDate = $fromInvoicePayments->created_at ?? $fromInvoicePayments->updated_at ?? null;
            $paymentDate = $fromPayments->created_at ?? $fromPayments->updated_at ?? null;

            if (!$invoicePaymentDate) return $fromPayments;
            if (!$paymentDate) return $fromInvoicePayments;

            $selected = $invoicePaymentDate > $paymentDate ? $fromInvoicePayments : $fromPayments;

            Log::debug('Invoice::lastPaymentRaw - Retrieved from both sources', [
                'invoice_id' => $this->id,
                'selected_from' => $invoicePaymentDate > $paymentDate ? 'InvoicePayment' : 'Payment'
            ]);

            return $selected;
        } catch (\Throwable $e) {
            Log::error('Invoice::lastPaymentRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function lastPayments(): ?HasOne
    {
        return $this->lastPayment();
    }

    public function bankPayments(): HasMany
    {
        try {
            $junctionTable = (new InvoiceBankTransfer)->getTable();
            $directTable = (new BankTransfer)->getTable();
            $fkAlias = BC::COL_INV_ID;

            $junctionFk = Schema::hasColumn($junctionTable, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($junctionTable, 'invoice') ? 'invoice' : null);
            $directFk = Schema::hasColumn($directTable, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($directTable, 'invoice') ? 'invoice' : null);

            if (!$junctionFk || !$directFk) {
                Log::error('Invoice::bankPayments - Missing invoice FK in one or both tables', [
                    'junction_table' => $junctionTable,
                    'direct_table' => $directTable,
                    'junction_fk' => $junctionFk,
                    'direct_fk' => $directFk,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return $this->hasMany(BankTransfer::class, $fkAlias, 'id')->where('status', '!=', 'Approved');
            }

            $junctionCols = Schema::getColumnListing($junctionTable);
            $directCols = Schema::getColumnListing($directTable);
            $allCols = array_values(array_unique(array_merge($junctionCols, $directCols, [$fkAlias, '_source'])));
            if (!\in_array('id', $allCols, true)) $allCols[] = 'id';

            $hasStatusJunction = Schema::hasColumn($junctionTable, 'status');
            $hasStatusDirect = Schema::hasColumn($directTable, 'status');

            $junctionSelect = [];
            foreach ($allCols as $col) {
                if ($col === '_source') {
                    $junctionSelect[] = DB::raw("'invoice_bank_transfers' as `_source`");
                    continue;
                }
                if ($col === $fkAlias) {
                    $junctionSelect[] = DB::raw("`{$junctionTable}`.`{$junctionFk}` as `{$fkAlias}`");
                    continue;
                }
                $junctionSelect[] = \in_array($col, $junctionCols, true)
                    ? DB::raw("`{$junctionTable}`.`{$col}` as `{$col}`")
                    : DB::raw("NULL as `{$col}`");
            }

            $directSelect = [];
            foreach ($allCols as $col) {
                if ($col === '_source') {
                    $directSelect[] = DB::raw("'bank_transfers' as `_source`");
                    continue;
                }
                if ($col === $fkAlias) {
                    $directSelect[] = DB::raw("`{$directTable}`.`{$directFk}` as `{$fkAlias}`");
                    continue;
                }
                $directSelect[] = \in_array($col, $directCols, true)
                    ? DB::raw("`{$directTable}`.`{$col}` as `{$col}`")
                    : DB::raw("NULL as `{$col}`");
            }

            $junction = DB::table($junctionTable)->select($junctionSelect);
            if ($hasStatusJunction) $junction->where('status', '!=', 'Approved');

            $direct = DB::table($directTable)->select($directSelect);
            if ($hasStatusDirect) $direct->where('status', '!=', 'Approved');

            $union = $junction->unionAll($direct);

            $rel = $this->hasMany(BankTransfer::class, $fkAlias, 'id');
            $rel->getQuery()->fromSub($union, 'bank_payments_union');

            Log::debug('Invoice::bankPayments - Using union relation', [
                'junction_table' => $junctionTable,
                'direct_table' => $directTable,
                'fk_alias' => $fkAlias,
                'junction_fk' => $junctionFk,
                'direct_fk' => $directFk,
                'status_filtered_junction' => $hasStatusJunction,
                'status_filtered_direct' => $hasStatusDirect,
                'columns' => \count($allCols),
            ]);

            return $rel;
        } catch (\Throwable $e) {
            Log::error('Invoice::bankPayments - Failed to build union relation', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return $this->hasMany(BankTransfer::class, BC::COL_INV_ID, 'id')->where('status', '!=', 'Approved');
        }
    }

    public function invoiceBankPayments(): HasMany
    {
        try {
            $table = (new InvoiceBankTransfer)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::invoiceBankPayments - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return $this->hasMany(InvoiceBankTransfer::class, BC::COL_INV_ID, 'id')->where('status', '!=', 'Approved');
            }

            Log::debug('Invoice::invoiceBankPayments - Using invoice column', ['column' => $fk]);
            return $this->hasMany(InvoiceBankTransfer::class, $fk, 'id')->where('status', '!=', 'Approved');
        } catch (\Throwable $e) {
            Log::error('Invoice::invoiceBankPayments - Failed', ['error' => $e->getMessage()]);
            return $this->hasMany(InvoiceBankTransfer::class, BC::COL_INV_ID, 'id')->where('status', '!=', 'Approved');
        }
    }

    public function bankTransferBankPayments(): HasMany
    {
        try {
            $table = (new BankTransfer)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::bankTransferBankPayments - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return $this->hasMany(BankTransfer::class, BC::COL_INV_ID, 'id')->where('status', '!=', 'Approved');
            }

            Log::debug('Invoice::bankTransferBankPayments - Using invoice column', ['column' => $fk]);
            return $this->hasMany(BankTransfer::class, $fk, 'id')->where('status', '!=', 'Approved');
        } catch (\Throwable $e) {
            Log::error('Invoice::bankTransferBankPayments - Failed', ['error' => $e->getMessage()]);
            return $this->hasMany(BankTransfer::class, BC::COL_INV_ID, 'id')->where('status', '!=', 'Approved');
        }
    }

    public function invoiceBankPaymentsRaw(): Collection
    {
        try {
            $table = (new InvoiceBankTransfer)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::invoiceBankPaymentsRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return collect([]);
            }

            $hasStatus = Schema::hasColumn($table, 'status');
            $sql = $hasStatus
                ? "SELECT * FROM {$table} WHERE {$fk} = ? AND status <> ?"
                : "SELECT * FROM {$table} WHERE {$fk} = ?";
            $bindings = $hasStatus ? [$this->id, 'Approved'] : [$this->id];
            $rows = DB::select($sql, $bindings);

            Log::debug('Invoice::invoiceBankPaymentsRaw - Retrieved rows', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'status_filtered' => $hasStatus,
                'count' => \count($rows),
            ]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Invoice::invoiceBankPaymentsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    public function bankTransferBankPaymentsRaw(): Collection
    {
        try {
            $table = (new BankTransfer)->getTable();
            $fk = Schema::hasColumn($table, BC::COL_INV_ID) ? BC::COL_INV_ID
                : (Schema::hasColumn($table, 'invoice') ? 'invoice' : null);

            if (!$fk) {
                Log::error('Invoice::bankTransferBankPaymentsRaw - No valid invoice column found', [
                    'table' => $table,
                    'checked' => [BC::COL_INV_ID, 'invoice'],
                ]);
                return collect([]);
            }

            $hasStatus = Schema::hasColumn($table, 'status');
            $sql = $hasStatus
                ? "SELECT * FROM {$table} WHERE {$fk} = ? AND status <> ?"
                : "SELECT * FROM {$table} WHERE {$fk} = ?";
            $bindings = $hasStatus ? [$this->id, 'Approved'] : [$this->id];
            $rows = DB::select($sql, $bindings);

            Log::debug('Invoice::bankTransferBankPaymentsRaw - Retrieved rows', [
                'invoice_id' => $this->id,
                'column' => $fk,
                'status_filtered' => $hasStatus,
                'count' => \count($rows),
            ]);

            return collect($rows);
        } catch (\Throwable $e) {
            Log::error('Invoice::bankTransferBankPaymentsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    public function bankPaymentsRaw(): Collection
    {
        try {
            $fromInvoiceBankTransfers = $this->invoiceBankPaymentsRaw();
            $fromBankTransfers = $this->bankTransferBankPaymentsRaw();

            Log::debug('Invoice::bankPaymentsRaw - Retrieved from both sources', [
                'invoice_id' => $this->id,
                'invoice_bank_transfers' => $fromInvoiceBankTransfers->count(),
                'bank_transfers' => $fromBankTransfers->count(),
            ]);

            return $fromInvoiceBankTransfers->merge($fromBankTransfers);
        } catch (\Throwable $e) {
            Log::error('Invoice::bankPaymentsRaw - Failed', [
                'invoice_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    public function creditNote(): HasMany
    {
        return $this->hasMany(
            CreditNote::class,
            'invoice',
            'id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_id')
            ->where('payment_type', TransactionType::Invoice);
    }

    public function invoiceTotalCreditNote(): float
    {
        return $this->creditNote->sum('amount');
    }

    public function getSubTotal(): float
    {
        return $this->items->sum(fn($p) => $p->price * $p->quantity);
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum(fn($p) => $p->discount);
    }

    public function getTotalTax(): float
    {
        return $this->items->sum(
            fn($p) => (Utility::totalTaxRate($p->tax) / 100)
                * ($p->price * $p->quantity - $p->discount)
        );
    }

    public function getTotal(): float
    {
        return ($this->getSubTotal() - $this->getTotalDiscount())
            + $this->getTotalTax();
    }

    public function getDue(): float
    {
        $paid = $this->payments->sum('amount');

        return ($this->getTotal() - $paid)
            - $this->invoiceTotalCreditNote();
    }

    public static function changeStatus(string|int $invoiceId, string|int $status): void
    {
        $invoice = static::find($invoiceId);
        if (!$invoice)
            return;
        $invoice->setAttribute('status', $status);
        $invoice->save();
    }
}
