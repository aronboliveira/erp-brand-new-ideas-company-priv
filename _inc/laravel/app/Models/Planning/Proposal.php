<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{BillStatus, ProposalStatus, UserType};
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, PlansByHierarchy, StoresManyRefJson, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\{Carbon, Collection, Str};
use Illuminate\Support\Facades\{DB, Log, Schema};

class Proposal extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays, PlansByHierarchy, StoresManyRefJson, FiltersSecureAttachments, DefinesDates;

    protected $table = DC::TABLE_PROPOSALS;

    /** @var array<string,mixed> */
    protected $casts = [
        // JSON / array-like
        'employees'    => 'array',
        'customers'    => 'array',
        'signers'      => 'array',
        'payments'     => 'array',
        'taxes'        => 'array',
        'attachments'  => 'array',
        BC::COL_TC     => 'array',
        BC::COL_RCC_RL => 'array',

        // Datas e datetimes
        BC::COL_VLD_TO => 'datetime',
        BC::COL_SIGN_AT => 'datetime',
        BC::COL_VW_AT  => 'datetime',
        BC::COL_REJ_AT => 'datetime',
        BC::COL_ISS_DT => 'date',
        BC::COL_SD_DT  => 'date',
        PJC::COL_D_DATE => 'date',
        DC::COL_C_AT   => 'datetime',
        DC::COL_U_AT   => 'datetime',

        // Numéricos
        'amount'        => 'float',
        'discount'      => 'float',
        BC::COL_SVC_FEE => 'float',
        BC::COL_TXS_FEE => 'float',
        'status'        => 'integer',
        'version'       => 'integer',
        BC::COL_DSC_APL => 'integer',

        // Booleanos
        BC::COL_AUTORCC => 'boolean',
        BC::COL_RQ_SIGN => 'boolean',
        BC::COL_IS_SIGN => 'boolean',
        BC::COL_IS_CNV  => 'boolean',

        // Status auxiliares
        BC::COL_BILL_STATUS => 'string',
    ];

    protected $fillable = [
        'title',
        BC::COL_PPS_ID,
        BC::COL_CST_ID,
        BC::COL_CUR_ID,
        'amount',
        'discount',
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        'reference',
        'description',
        'notes',
        'attachments',
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
        BC::COL_SD_DT,
        BC::COL_ISS_DT,
        PJC::COL_D_DATE,
        BC::COL_DSC_APL,
        BC::COL_CAT_ID,
        'taxes',
        BC::COL_VLD_TO,
        BC::COL_RQ_SIGN,
        BC::COL_IS_SIGN,
        BC::COL_SIGN_AT,
        BC::COL_SIGN_BY,
        BC::COL_SIGN_BY_NAME,
        BC::COL_VW_AT,
        'payments',
        'status',
        BC::COL_STT_LB,
        BC::COL_BILL_STATUS,
        BC::COL_IS_CNV,
        'version',
        BC::COL_REJ_AT,
        BC::COL_REJ_RS,
        PJC::COL_LD_ID,
        BC::COL_CNV_INV_ID,
        BC::COL_TAX_ID,
        'employees',
        'customers',
        'signers',
        'contract',
        'loan',
        BC::COL_PRD_SV_UNT,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $with = [
        'tax',
        'lead',
        'productServiceUnit',
        'invoice',
    ];

    protected $appends = [
        'rejection',
        'full_identifier',
        'full_status',
    ];

    /** @var array<string,string> */
    public static array $statuses = [
        'draft'    => 'Draft',
        'open'     => 'Open',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
        'close'    => 'Close',
    ];

    public static function booted(): void
    {
        static::saving(function (self $m): void {
            if (empty($m->{BC::COL_PPS_ID}) || is_numeric($m->{BC::COL_PPS_ID})) {
                do $candidateCode = Str::uuid()->toString();
                while (self::query()->where(BC::COL_PPS_ID, $candidateCode)->exists());
                $m->{BC::COL_PPS_ID} = $candidateCode;
            }

            $statusEnum = ProposalStatus::normalize($m->{BC::COL_STT_LB} ?? null);
            $m->{BC::COL_STT_LB} = $statusEnum->value;
            $m->status = self::mapStatusEnumToInt($statusEnum);

            if (!empty($m->{BC::COL_BILL_STATUS}))
                $m->{BC::COL_BILL_STATUS} = BillStatus::normalize($m->{BC::COL_BILL_STATUS})->value;

            $m->amount = (float) ($m->amount ?? 0.0);
            $m->discount = (float) ($m->discount ?? 0.0);
            if ($m->discount < 0.0) $m->discount = 0.0;
            if ($m->discount > $m->amount) $m->discount = $m->amount;

            $m->{BC::COL_DSC_APL} = (int) ($m->{BC::COL_DSC_APL} ?? 0) > 0 ? 1 : 0;
            $m->{BC::COL_IS_CNV} = (int) ($m->{BC::COL_IS_CNV} ?? 0) > 0 ? 1 : 0;
            if (empty($m->version) || $m->version < 1) $m->version = 1;

            $m->attachments = static::normalizeArrayField($m->attachments ?? null);
            $m->taxes = static::normalizeArrayField($m->taxes ?? null);
            $m->employees = static::normalizeArrayField($m->employees ?? null);
            $m->customers = static::normalizeArrayField($m->customers ?? null);
            $m->signers = static::normalizeArrayField($m->signers ?? null);
            $m->payments = static::normalizeUuidPointerField($m->payments ?? null, \App\Models\Payment::class);
            $m->{BC::COL_TC} = static::normalizeArrayField($m->{BC::COL_TC} ?? null);
            $m->{BC::COL_RCC_RL} = static::normalizeArrayField($m->{BC::COL_RCC_RL} ?? null);

            if (!empty($m->{BC::COL_CST_ID})) {
                $customerId = (string) $m->{BC::COL_CST_ID};
                $customers = array_map('strval', $m->customers ?? []);
                if (!in_array($customerId, $customers, true)) $customers[] = $customerId;
                $m->customers = array_values(array_unique($customers));
            }

            if (empty($m->title)) {
                $customerName = null;
                if ($m->relationLoaded('customer') && $m->customer)
                    $customerName = $m->customer->name ?? null;
                elseif (!empty($m->{BC::COL_CST_ID}) && class_exists(\App\Models\Customer::class))
                    $customerName = \App\Models\Customer::query()
                        ->whereKey($m->{BC::COL_CST_ID})
                        ->value('name');

                $stamp = Carbon::now()->format('Ymd_His');
                $safeName = $customerName ? Str::upper(Str::slug($customerName, '_')) : 'ANONYMOUS_CUSTOMER';
                $m->title = "PROPOSAL_{$stamp}_{$safeName}";
            }
        });
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
    }

    public function taxes(): BelongsTo
    {
        return $this->tax();
    }

    public function items(): HasMany
    {
        return $this->products();
    }

    public function proposalProducts(): HasMany
    {
        try {
            $proposalProductsTable = (new ProposalProduct)->getTable();

            $proposalForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($proposalProductsTable, 'proposal') ? 'proposal' : null);

            if (!$proposalForeignKeyInProposalProducts) {
                Log::error('Proposal::proposalProducts - No valid proposal FK column found in proposal_products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $proposalProductsTable,
                    'checked_columns' => [BC::COL_PPS_ID, 'proposal'],
                ]);

                return $this->hasMany(ProposalProduct::class, BC::COL_PPS_ID, $this->getKeyName());
            }

            return $this->hasMany(ProposalProduct::class, $proposalForeignKeyInProposalProducts, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Proposal::proposalProducts - Failed to determine proposal FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(ProposalProduct::class, BC::COL_PPS_ID, $this->getKeyName());
        }
    }

    public function productProducts(): HasMany
    {
        try {
            $productsTable = (new Product)->getTable();

            $proposalForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($productsTable, 'proposal') ? 'proposal' : null);

            if (!$proposalForeignKeyInProducts) {
                Log::error('Proposal::productProducts - No valid proposal FK column found in products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $productsTable,
                    'checked_columns' => [BC::COL_PPS_ID, 'proposal'],
                ]);

                return $this->hasMany(Product::class, BC::COL_PPS_ID, $this->getKeyName());
            }

            return $this->hasMany(Product::class, $proposalForeignKeyInProducts, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Proposal::productProducts - Failed to determine proposal FK column', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Product::class, BC::COL_PPS_ID, $this->getKeyName());
        }
    }

    public function products(): HasMany
    {
        try {
            $proposalProductsTable = (new ProposalProduct)->getTable();
            $productsTable = (new Product)->getTable();

            $proposalForeignKeyAlias = BC::COL_PPS_ID;

            $proposalForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($proposalProductsTable, 'proposal') ? 'proposal' : null);

            $proposalForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($productsTable, 'proposal') ? 'proposal' : null);

            if (!$proposalForeignKeyInProposalProducts || !$proposalForeignKeyInProducts) {
                Log::error('Proposal::products - Missing proposal FK column in one or both source tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'proposal_products_table' => $proposalProductsTable,
                    'products_table' => $productsTable,
                    'proposal_products_fk' => $proposalForeignKeyInProposalProducts,
                    'products_fk' => $proposalForeignKeyInProducts,
                    'checked_columns' => [BC::COL_PPS_ID, 'proposal'],
                ]);

                return $this->hasMany(Product::class, $proposalForeignKeyAlias, $this->getKeyName());
            }

            $proposalProductsColumns = Schema::getColumnListing($proposalProductsTable);
            $productsColumns = Schema::getColumnListing($productsTable);

            $unionColumns = array_values(array_unique(array_merge(
                $proposalProductsColumns,
                $productsColumns,
                [$proposalForeignKeyAlias, '_source']
            )));

            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $proposalProductsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $proposalProductsSelect[] = "'proposal_products' as `_source`";
                    continue;
                }
                if ($column === $proposalForeignKeyAlias) {
                    $proposalProductsSelect[] = "`{$proposalProductsTable}`.`{$proposalForeignKeyInProposalProducts}` as `{$proposalForeignKeyAlias}`";
                    continue;
                }
                $proposalProductsSelect[] = \in_array($column, $proposalProductsColumns, true)
                    ? "`{$proposalProductsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $productsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $productsSelect[] = "'products' as `_source`";
                    continue;
                }
                if ($column === $proposalForeignKeyAlias) {
                    $productsSelect[] = "`{$productsTable}`.`{$proposalForeignKeyInProducts}` as `{$proposalForeignKeyAlias}`";
                    continue;
                }
                $productsSelect[] = \in_array($column, $productsColumns, true)
                    ? "`{$productsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $derivedAlias = 'proposal_products_union';

            $unionSql =
                "SELECT " . implode(', ', $proposalProductsSelect) . " FROM `{$proposalProductsTable}` " .
                "UNION ALL " .
                "SELECT " . implode(', ', $productsSelect) . " FROM `{$productsTable}`";

            $relation = $this->hasMany(Product::class, $proposalForeignKeyAlias, $this->getKeyName());
            $relation->getQuery()->from(DB::raw("({$unionSql}) as `{$derivedAlias}`"));

            Log::debug('Proposal::products - Using union-backed HasMany', [
                'class' => static::class,
                'proposal_products_table' => $proposalProductsTable,
                'products_table' => $productsTable,
                'proposal_fk_alias' => $proposalForeignKeyAlias,
                'proposal_products_fk' => $proposalForeignKeyInProposalProducts,
                'products_fk' => $proposalForeignKeyInProducts,
                'columns' => \count($unionColumns),
                'alias' => $derivedAlias,
            ]);

            return $relation;
        } catch (\Throwable $e) {
            Log::error('Proposal::products - Failed to build union-backed HasMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'proposal_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(Product::class, BC::COL_PPS_ID, $this->getKeyName());
        }
    }

    public function productServices(): HasMany
    {
        try {
            $proposalId = $this->getKey();

            $proposalProductsTable = (new ProposalProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $proposalForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($proposalProductsTable, 'proposal') ? 'proposal' : null);

            $proposalForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($productsTable, 'proposal') ? 'proposal' : null);

            $serviceForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($proposalProductsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($proposalProductsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($proposalProductsTable, 'service') ? 'service' : null)));

            $serviceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($productsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($productsTable, 'service') ? 'service' : null)));

            if ((!$proposalForeignKeyInProposalProducts && !$proposalForeignKeyInProducts) || (!$serviceForeignKeyInProposalProducts && !$serviceForeignKeyInProducts)) {
                Log::debug('Proposal::productServices - No source available for product_service ids', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'proposal_products_fk' => $proposalForeignKeyInProposalProducts,
                    'products_fk' => $proposalForeignKeyInProducts,
                    'proposal_products_service_fk' => $serviceForeignKeyInProposalProducts,
                    'products_service_fk' => $serviceForeignKeyInProducts,
                ]);

                return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');
            }

            $proposalProductsServiceIdsQuery = null;

            if ($proposalForeignKeyInProposalProducts && $serviceForeignKeyInProposalProducts) {
                $proposalProductsServiceIdsQuery = DB::table($proposalProductsTable)
                    ->selectRaw("`{$proposalProductsTable}`.`{$serviceForeignKeyInProposalProducts}` as `service_id`")
                    ->where("{$proposalProductsTable}.{$proposalForeignKeyInProposalProducts}", $proposalId)
                    ->whereNotNull("{$proposalProductsTable}.{$serviceForeignKeyInProposalProducts}");
            }

            $productsServiceIdsQuery = null;

            if ($proposalForeignKeyInProducts && $serviceForeignKeyInProducts) {
                $productsServiceIdsQuery = DB::table($productsTable)
                    ->selectRaw("`{$productsTable}`.`{$serviceForeignKeyInProducts}` as `service_id`")
                    ->where("{$productsTable}.{$proposalForeignKeyInProducts}", $proposalId)
                    ->whereNotNull("{$productsTable}.{$serviceForeignKeyInProducts}");

                if ($proposalProductsServiceIdsQuery && $proposalForeignKeyInProposalProducts && $serviceForeignKeyInProposalProducts) {
                    $proposalProductsServiceIdsSubquery = DB::table($proposalProductsTable)
                        ->selectRaw("`{$proposalProductsTable}`.`{$serviceForeignKeyInProposalProducts}`")
                        ->where("{$proposalProductsTable}.{$proposalForeignKeyInProposalProducts}", $proposalId)
                        ->whereNotNull("{$proposalProductsTable}.{$serviceForeignKeyInProposalProducts}");

                    $productsServiceIdsQuery->whereNotIn("{$productsTable}.{$serviceForeignKeyInProducts}", $proposalProductsServiceIdsSubquery);
                }
            }

            $serviceIdsUnionQuery = $proposalProductsServiceIdsQuery
                ? ($productsServiceIdsQuery ? $proposalProductsServiceIdsQuery->unionAll($productsServiceIdsQuery) : $proposalProductsServiceIdsQuery)
                : $productsServiceIdsQuery;

            if (!$serviceIdsUnionQuery) {
                return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');
            }

            $servicesForProposalQuery = DB::table("{$productServicesTable} as ps")
                ->joinSub($serviceIdsUnionQuery, 'src', 'src.service_id', '=', 'ps.id')
                ->selectRaw("ps.*, ? as `" . BC::COL_PPS_ID . "`", [$proposalId])
                ->distinct();

            $derivedAlias = 'proposal_product_services_union';

            $related = new ProductService();
            $related->setTable($derivedAlias);

            $derivedQuery = $related->newQuery()->fromSub($servicesForProposalQuery, $derivedAlias);

            Log::debug('Proposal::productServices - Using derived HasMany for product services', [
                'class' => static::class,
                'proposal_id' => $proposalId,
                'proposal_products_table' => $proposalProductsTable,
                'products_table' => $productsTable,
                'product_services_table' => $productServicesTable,
                'proposal_products_fk' => $proposalForeignKeyInProposalProducts,
                'products_fk' => $proposalForeignKeyInProducts,
                'proposal_products_service_fk' => $serviceForeignKeyInProposalProducts,
                'products_service_fk' => $serviceForeignKeyInProducts,
                'products_filtered_against_proposal_products' => (bool) $proposalProductsServiceIdsQuery && (bool) $productsServiceIdsQuery,
                'alias' => $derivedAlias,
            ]);

            return $this->newHasMany($derivedQuery, $this, "{$derivedAlias}." . BC::COL_PPS_ID, $this->getKeyName());
        } catch (\Throwable $e) {
            Log::error('Proposal::productServices - Failed to build derived HasMany', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'proposal_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->hasMany(ProductService::class, 'id', $this->getKeyName())->whereRaw('1=0');
        }
    }

    public function proposalProductsRaw(): Collection
    {
        try {
            $proposalProductsTable = (new ProposalProduct)->getTable();

            $proposalForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($proposalProductsTable, 'proposal') ? 'proposal' : null);

            if (!$proposalForeignKeyInProposalProducts) {
                Log::error('Proposal::proposalProductsRaw - No valid proposal FK column found in proposal_products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $proposalProductsTable,
                    'checked_columns' => [BC::COL_PPS_ID, 'proposal'],
                ]);

                return collect([]);
            }

            $sql = "SELECT * FROM `{$proposalProductsTable}` WHERE `{$proposalForeignKeyInProposalProducts}` = ?";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Proposal::proposalProductsRaw - Failed to retrieve proposal products', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'proposal_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function productProductsRaw(): Collection
    {
        try {
            $productsTable = (new Product)->getTable();

            $proposalForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($productsTable, 'proposal') ? 'proposal' : null);

            if (!$proposalForeignKeyInProducts) {
                Log::error('Proposal::productProductsRaw - No valid proposal FK column found in products table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $productsTable,
                    'checked_columns' => [BC::COL_PPS_ID, 'proposal'],
                ]);

                return collect([]);
            }

            $sql = "SELECT * FROM `{$productsTable}` WHERE `{$proposalForeignKeyInProducts}` = ?";
            return collect(DB::select($sql, [$this->getKey()]));
        } catch (\Throwable $e) {
            Log::error('Proposal::productProductsRaw - Failed to retrieve products', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'proposal_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function productsRaw(): Collection
    {
        try {
            $proposalProductsTable = (new ProposalProduct)->getTable();
            $productsTable = (new Product)->getTable();

            $proposalForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($proposalProductsTable, 'proposal') ? 'proposal' : null);

            $proposalForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($productsTable, 'proposal') ? 'proposal' : null);

            if (!$proposalForeignKeyInProposalProducts || !$proposalForeignKeyInProducts) {
                Log::error('Proposal::productsRaw - Missing proposal FK column in one or both source tables', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'proposal_products_table' => $proposalProductsTable,
                    'products_table' => $productsTable,
                    'proposal_products_fk' => $proposalForeignKeyInProposalProducts,
                    'products_fk' => $proposalForeignKeyInProducts,
                    'checked_columns' => [BC::COL_PPS_ID, 'proposal'],
                ]);

                return collect([]);
            }

            $proposalId = $this->getKey();

            $proposalProductsColumns = Schema::getColumnListing($proposalProductsTable);
            $productsColumns = Schema::getColumnListing($productsTable);

            $unionColumns = array_values(array_unique(array_merge(
                $proposalProductsColumns,
                $productsColumns,
                [BC::COL_PPS_ID, '_source']
            )));

            if (!\in_array('id', $unionColumns, true)) $unionColumns[] = 'id';

            $proposalProductsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $proposalProductsSelect[] = "'proposal_products' as `_source`";
                    continue;
                }
                if ($column === BC::COL_PPS_ID) {
                    $proposalProductsSelect[] = "`{$proposalProductsTable}`.`{$proposalForeignKeyInProposalProducts}` as `" . BC::COL_PPS_ID . "`";
                    continue;
                }
                $proposalProductsSelect[] = \in_array($column, $proposalProductsColumns, true)
                    ? "`{$proposalProductsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $productsSelect = [];
            foreach ($unionColumns as $column) {
                if ($column === '_source') {
                    $productsSelect[] = "'products' as `_source`";
                    continue;
                }
                if ($column === BC::COL_PPS_ID) {
                    $productsSelect[] = "`{$productsTable}`.`{$proposalForeignKeyInProducts}` as `" . BC::COL_PPS_ID . "`";
                    continue;
                }
                $productsSelect[] = \in_array($column, $productsColumns, true)
                    ? "`{$productsTable}`.`{$column}` as `{$column}`"
                    : "NULL as `{$column}`";
            }

            $sql =
                "SELECT " . implode(', ', $proposalProductsSelect) . " FROM `{$proposalProductsTable}` WHERE `{$proposalForeignKeyInProposalProducts}` = ? " .
                "UNION ALL " .
                "SELECT " . implode(', ', $productsSelect) . " FROM `{$productsTable}` WHERE `{$proposalForeignKeyInProducts}` = ?";

            return collect(DB::select($sql, [$proposalId, $proposalId]));
        } catch (\Throwable $e) {
            Log::error('Proposal::productsRaw - Failed to retrieve merged products', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'proposal_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function productServicesRaw(): Collection
    {
        try {
            $proposalId = $this->getKey();

            $proposalProductsTable = (new ProposalProduct)->getTable();
            $productsTable = (new Product)->getTable();
            $productServicesTable = (new ProductService)->getTable();

            $proposalForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($proposalProductsTable, 'proposal') ? 'proposal' : null);

            $proposalForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PPS_ID)
                ? BC::COL_PPS_ID
                : (Schema::hasColumn($productsTable, 'proposal') ? 'proposal' : null);

            $serviceForeignKeyInProposalProducts = Schema::hasColumn($proposalProductsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($proposalProductsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($proposalProductsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($proposalProductsTable, 'service') ? 'service' : null)));

            $serviceForeignKeyInProducts = Schema::hasColumn($productsTable, BC::COL_PRD_SV_ID)
                ? BC::COL_PRD_SV_ID
                : (Schema::hasColumn($productsTable, 'product_service_id') ? 'product_service_id' : (Schema::hasColumn($productsTable, 'product_service') ? 'product_service' : (Schema::hasColumn($productsTable, 'service') ? 'service' : null)));

            if ((!$proposalForeignKeyInProposalProducts && !$proposalForeignKeyInProducts) || (!$serviceForeignKeyInProposalProducts && !$serviceForeignKeyInProducts)) {
                return collect([]);
            }

            $selectFromProposalProducts = null;

            if ($proposalForeignKeyInProposalProducts && $serviceForeignKeyInProposalProducts) {
                $selectFromProposalProducts =
                    "SELECT `{$proposalProductsTable}`.`{$serviceForeignKeyInProposalProducts}` as `service_id` " .
                    "FROM `{$proposalProductsTable}` " .
                    "WHERE `{$proposalProductsTable}`.`{$proposalForeignKeyInProposalProducts}` = ? " .
                    "AND `{$proposalProductsTable}`.`{$serviceForeignKeyInProposalProducts}` IS NOT NULL";
            }

            $selectFromProducts = null;

            if ($proposalForeignKeyInProducts && $serviceForeignKeyInProducts) {
                $selectFromProducts =
                    "SELECT `{$productsTable}`.`{$serviceForeignKeyInProducts}` as `service_id` " .
                    "FROM `{$productsTable}` " .
                    "WHERE `{$productsTable}`.`{$proposalForeignKeyInProducts}` = ? " .
                    "AND `{$productsTable}`.`{$serviceForeignKeyInProducts}` IS NOT NULL";

                if ($selectFromProposalProducts) {
                    $selectFromProducts .=
                        " AND `{$productsTable}`.`{$serviceForeignKeyInProducts}` NOT IN (" .
                        "SELECT `{$proposalProductsTable}`.`{$serviceForeignKeyInProposalProducts}` " .
                        "FROM `{$proposalProductsTable}` " .
                        "WHERE `{$proposalProductsTable}`.`{$proposalForeignKeyInProposalProducts}` = ? " .
                        "AND `{$proposalProductsTable}`.`{$serviceForeignKeyInProposalProducts}` IS NOT NULL" .
                        ")";
                }
            }

            if (!$selectFromProposalProducts && !$selectFromProducts) return collect([]);

            $bindings = [];
            $serviceIdsSql = null;

            if ($selectFromProposalProducts && $selectFromProducts) {
                $serviceIdsSql = "({$selectFromProposalProducts}) UNION ALL ({$selectFromProducts})";
                $bindings = $selectFromProposalProducts
                    ? ($selectFromProducts
                        ? [$proposalId, $proposalId, $proposalId]
                        : [$proposalId])
                    : [$proposalId];
            } elseif ($selectFromProposalProducts) {
                $serviceIdsSql = $selectFromProposalProducts;
                $bindings = [$proposalId];
            } else {
                $serviceIdsSql = $selectFromProducts;
                $bindings = $selectFromProposalProducts ? [$proposalId, $proposalId] : [$proposalId];
            }

            $finalSql =
                "SELECT DISTINCT ps.* " .
                "FROM `{$productServicesTable}` ps " .
                "JOIN ({$serviceIdsSql}) src ON src.`service_id` = ps.`id`";

            return collect(DB::select($finalSql, $bindings));
        } catch (\Throwable $e) {
            Log::error('Proposal::productServicesRaw - Failed to retrieve product services', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'proposal_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
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


    public function productServiceUnit(): BelongsTo
    {
        return $this->belongsTo(ProductServiceUnit::class, BC::COL_PRD_SV_UNT, 'id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, PJC::COL_LD_ID, 'id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, BC::COL_CNV_INV_ID, 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->convertedInvoice();
    }

    public function statusEnum(): ProposalStatus
    {
        return ProposalStatus::normalize($this->{BC::COL_STT_LB} ?? null);
    }

    public function getFullStatusAttribute(): string
    {
        return $this->{BC::COL_STT_LB} . ' (' . $this->status . ')';
    }

    public function getFullIdentifierAttribute(): string
    {
        return 'PPS-' . $this->{BC::COL_PPS_ID}
            . ' — ' . ($this->title ?: 'UNTITLED')
            . ' — ' . ($this->version ? 'v' . $this->version : 'v#UNDEFINED');
    }

    public function getRejectionAttribute(): ?array
    {
        if (empty($this->{BC::COL_REJ_AT})) return null;
        return [
            'at'     => $this->{BC::COL_REJ_AT},
            'reason' => $this->{BC::COL_REJ_RS},
        ];
    }

    public function getSubTotal(): float
    {
        return $this->items->sum(
            fn($p): float => (float) $p->price * (float) $p->quantity
        );
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum(
            fn($p): float => (float) $p->discount
        );
    }

    public function getTotalTax(): float
    {
        return $this->items->sum(
            fn($p): float => (float) \Utility::totalTaxRate($p->tax) / 100.0
                * ((float) $p->price * (float) $p->quantity - (float) $p->discount)
        );
    }

    public function getTotal(): float
    {
        return ($this->getSubTotal() - $this->getTotalDiscount())
            + $this->getTotalTax();
    }

    public function getDue(): float
    {
        $paid = 0.0;
        $paymentIds = is_array($this->payments) ? $this->payments : [];
        if (class_exists(\App\Models\Payment::class) && $paymentIds) {
            $sum = \App\Models\Payment::query()
                ->whereIn('id', $paymentIds)
                ->sum('amount');
            $paid = (float) $sum;
        }
        $due = $this->getTotal() - $paid - $this->invoiceTotalCreditNote();
        return $due > 0.0 ? $due : 0.0;
    }

    public function invoiceTotalCreditNote(): float
    {
        if (empty($this->{BC::COL_CNV_INV_ID})) return 0.0;
        if (!class_exists(\App\Models\CreditNote::class)) return 0.0;

        try {
            $total = \App\Models\CreditNote::query()
                ->where(BC::COL_INV_ID, $this->{BC::COL_CNV_INV_ID})
                ->sum('amount');
            return (float) $total;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to compute invoiceTotalCreditNote', [
                'error'      => $e->getMessage(),
                'invoice_id' => $this->{BC::COL_CNV_INV_ID},
            ]);
            return 0.0;
        }
    }

    public function scopeIssuedBetween(Builder $query, ?\DateTimeInterface $from, ?\DateTimeInterface $to): Builder
    {
        if ($from) $query->whereDate(BC::COL_ISS_DT, '>=', $from->format('Y-m-d'));
        if ($to) $query->whereDate(BC::COL_ISS_DT, '<=', $to->format('Y-m-d'));
        return $query;
    }

    public function scopeForCustomer(Builder $query, string $customerId): Builder
    {
        return $query->where(BC::COL_CST_ID, $customerId);
    }

    public static function aggregateByStatus(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
        ?string $customerId = null
    ): array {
        $query = static::query();
        if ($from || $to) $query->issuedBetween($from, $to);
        if ($customerId) $query->forCustomer($customerId);

        $rows = $query
            ->selectRaw(BC::COL_STT_LB . ' as status_label, COUNT(*) as aggregate_count, SUM(amount) as aggregate_amount')
            ->groupBy(BC::COL_STT_LB)
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->status_label] = [
                'count'  => (int) $row->aggregate_count,
                'amount' => (float) $row->aggregate_amount,
            ];
        }

        return $result;
    }

    public static function totalAmountBetween(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
        ?string $customerId = null
    ): float {
        $query = static::query();
        if ($from || $to) $query->issuedBetween($from, $to);
        if ($customerId) $query->forCustomer($customerId);
        return (float) $query->sum('amount');
    }

    public static function changeStatus($proposalId, string|ProposalStatus|null $status): void
    {
        $proposal = static::query()->find($proposalId);
        if (!$proposal) return;

        $enum = ProposalStatus::normalize($status);
        $proposal->{BC::COL_STT_LB} = $enum->value;
        $proposal->status = self::mapStatusEnumToInt($enum);
        $proposal->save();
    }

    protected static function mapStatusEnumToInt(ProposalStatus $status): int
    {
        return match ($status) {
            ProposalStatus::Draft    => 0,
            ProposalStatus::Open     => 1,
            ProposalStatus::Accepted => 2,
            ProposalStatus::Declined => 3,
            ProposalStatus::Close    => 4,
        };
    }

    protected static function normalizeArrayField(mixed $value): array
    {
        if ($value === null) return [];
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') return [];
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $value = $decoded;
            else $value = preg_split('/\s*,\s*/', $trimmed) ?: [];
        }
        if (!is_array($value)) return [];

        $normalized = [];
        foreach ($value as $item) {
            if ($item === null) continue;
            if (is_string($item)) {
                $t = trim($item);
                if ($t === '') continue;
                $normalized[] = $t;
                continue;
            }
            $normalized[] = $item;
        }

        return array_values($normalized);
    }

    protected static function normalizeUuidPointerField(mixed $value, string $modelClass): array
    {
        $items = static::normalizeArrayField($value);
        if (!$items) return [];

        $ids = [];
        foreach ($items as $item) {
            if (is_string($item) && \Utility::looksLikeUuid($item)) $ids[] = $item;
            elseif (is_array($item) && isset($item['id']) && \Utility::looksLikeUuid((string) $item['id']))
                $ids[] = (string) $item['id'];
        }

        $ids = array_values(array_unique($ids));
        if (!$ids || !class_exists($modelClass) || !method_exists($modelClass, 'query')) return $ids;

        try {
            $valid = $modelClass::query()
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();
            return array_values(array_map('strval', $valid));
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to normalize uuid pointer field', [
                'model' => $modelClass,
                'error' => $e->getMessage(),
            ]);
            return $ids;
        }
    }
}
