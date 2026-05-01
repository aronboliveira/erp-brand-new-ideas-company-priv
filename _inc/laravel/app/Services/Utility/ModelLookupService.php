<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    BillsConstants as BC,
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC,
};
use App\Enums\UserType;
use App\Helpers\ErrorHandler;
use App\Models\{
    Client,
    Customer,
    Employee,
    Product,
    ProductCategory,
    ProductService,
    ProductServiceCategory,
    User,
    Utility,
    Vendor,
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log, Schema};

/**
 * ModelLookupService — extracted from Utility.php
 *
 * Resolves BelongsTo relationships dynamically by checking
 * which foreign key columns exist on a model's table, then
 * looking up the related entity across multiple candidate tables.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class ModelLookupService
{
    // ─────────────────────────────────────────────────────────
    //  Entity Relation Resolvers
    // ─────────────────────────────────────────────────────────

    public static function getClient(Model $model): ?BelongsTo
    {
        $clientColumn = null;
        try {
            $modelTable = $model->getTable();

            if (Schema::hasColumn($modelTable, BC::COL_CLT_ID))
                $clientColumn = BC::COL_CLT_ID;
            elseif (Schema::hasColumn($modelTable, 'client'))
                $clientColumn = 'client';

            if (!$clientColumn) {
                Log::debug('getClient() - Missing client column on model table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $modelTable,
                    'checked_columns' => [BC::COL_CLT_ID, 'client_id', 'client'],
                ]);
                return null;
            }

            $clientId = $model->getAttribute($clientColumn);

            if (!$clientId)
                return $model->belongsTo(Client::class, $clientColumn, 'id');

            $clientsTable = (new Client)->getTable();
            $clientExists = Schema::hasTable($clientsTable) && DB::table($clientsTable)->where('id', $clientId)->exists();

            if ($clientExists)
                return $model->belongsTo(Client::class, $clientColumn, 'id');

            $usersTable = (new User)->getTable();
            $typeColumn = Schema::hasColumn($usersTable, UC::COL_TP) ? UC::COL_TP : 'type';

            Log::debug('getClient() - Client not found in main table; falling back to users.type=client', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'client_id' => $clientId,
                'client_column' => $clientColumn,
                'clients_table' => $clientsTable,
                'users_table' => $usersTable,
                'type_column' => $typeColumn,
                'type_value' => UserType::Client->value,
            ]);

            return $model
                ->belongsTo(User::class, $clientColumn, 'id')
                ->where($typeColumn, UserType::Client->value);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'utility_errors',
                candidate: [
                    'message' => 'getClient() - Failed to resolve client relation with fallback',
                    'context' => [
                        'class' => static::class,
                        'method' => __METHOD__,
                        'line' => __LINE__,
                        'client_id' => $clientColumn ? $model->getAttribute($clientColumn) : null,
                        'client_column' => $clientColumn,
                        'error' => $e->getMessage(),
                    ]
                ],
                mainChannel: 'error'
            );
            return $clientColumn
                ? $model->belongsTo(Client::class, $clientColumn, 'id')
                : null;
        }
    }

    public static function getCustomer(Model $model): ?BelongsTo
    {
        $customerColumn = null;
        try {
            $modelTable = $model->getTable();

            if (Schema::hasColumn($modelTable, BC::COL_CST_ID))
                $customerColumn = BC::COL_CST_ID;
            elseif (Schema::hasColumn($modelTable, 'customer'))
                $customerColumn = 'customer';

            if (!$customerColumn) {
                Log::debug('getCustomer() - Missing customer column on model table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $modelTable,
                    'checked_columns' => [BC::COL_CST_ID, 'customer'],
                ]);
                return null;
            }

            $customerId = $model->getAttribute($customerColumn);

            if (!$customerId)
                return $model->belongsTo(Customer::class, $customerColumn, 'id');

            $customersTable = (new Customer)->getTable();
            $customerExists = Schema::hasTable($customersTable) && DB::table($customersTable)->where('id', $customerId)->exists();

            if ($customerExists)
                return $model->belongsTo(Customer::class, $customerColumn, 'id');

            $usersTable = (new User)->getTable();
            $typeColumn = Schema::hasColumn($usersTable, UC::COL_TP) ? UC::COL_TP : 'type';

            Log::debug('getCustomer() - Customer not found in main table; falling back to users.type=customer', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'customer_id' => $customerId,
                'customer_column' => $customerColumn,
                'customers_table' => $customersTable,
                'users_table' => $usersTable,
                'type_column' => $typeColumn,
                'type_value' => UserType::Customer->value,
            ]);

            return $model
                ->belongsTo(User::class, $customerColumn, 'id')
                ->where($typeColumn, UserType::Customer->value);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'utility_errors',
                candidate: [
                    'message' => 'getCustomer() - Failed to resolve customer relation with fallback',
                    'context' => [
                        'class' => static::class,
                        'method' => __METHOD__,
                        'line' => __LINE__,
                        'customer_id' => $customerColumn ? $model->getAttribute($customerColumn) : null,
                        'customer_column' => $customerColumn,
                        'error' => $e->getMessage(),
                    ]
                ],
                mainChannel: 'error'
            );
            return $customerColumn
                ? $model->belongsTo(Customer::class, $customerColumn, 'id')
                : null;
        }
    }

    public static function getVendor(Model $model): ?BelongsTo
    {
        $vendorColumn = null;
        try {
            $modelTable = $model->getTable();

            if (Schema::hasColumn($modelTable, UC::COL_VD_ID))
                $vendorColumn = UC::COL_VD_ID;
            elseif (Schema::hasColumn($modelTable, 'vendor'))
                $vendorColumn = 'vendor';

            if (!$vendorColumn) {
                Log::debug('getVendor() - Missing vendor column on model table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $modelTable,
                    'checked_columns' => [UC::COL_VD_ID, 'vendor'],
                ]);
                return null;
            }

            $vendorId = $model->getAttribute($vendorColumn);

            if (!$vendorId)
                return $model->belongsTo(Vendor::class, $vendorColumn, 'id');

            $vendorTable = (new Vendor)->getTable();
            $vendorExists = Schema::hasTable($vendorTable) && DB::table($vendorTable)->where('id', $vendorId)->exists();

            if ($vendorExists)
                return $model->belongsTo(Vendor::class, $vendorColumn, 'id');

            $usersTable = (new User)->getTable();
            $typeColumn = Schema::hasColumn($usersTable, UC::COL_TP) ? UC::COL_TP : 'type';

            Log::debug('getVendor() - Vendor not found in main table; falling back to users.type=vendor', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'vendor_id' => $vendorId,
                'vendor_column' => $vendorColumn,
                'vendor_table' => $vendorTable,
                'users_table' => $usersTable,
                'type_column' => $typeColumn,
                'type_value' => UserType::Vendor->value,
            ]);

            return $model
                ->belongsTo(User::class, $vendorColumn, 'id')
                ->where($typeColumn, UserType::Vendor->value);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'utility_errors',
                candidate: [
                    'message' => 'getVendor() - Failed to resolve vendor relation with fallback',
                    'context' => [
                        'class' => static::class,
                        'method' => __METHOD__,
                        'line' => __LINE__,
                        'vendor_id' => $vendorColumn ? $model->getAttribute($vendorColumn) : null,
                        'vendor_column' => $vendorColumn,
                        'error' => $e->getMessage(),
                    ]
                ],
                mainChannel: 'error'
            );
            return $vendorColumn
                ? $model->belongsTo(Vendor::class, $vendorColumn, 'id')
                : null;
        }
    }

    public static function getCompany(Model $model): ?BelongsTo
    {
        $companyColumn = null;
        try {
            $modelTable = $model->getTable();

            if (Schema::hasColumn($modelTable, CPC::COL_CP_ID))
                $companyColumn = CPC::COL_CP_ID;
            elseif (Schema::hasColumn($modelTable, 'company'))
                $companyColumn = 'company';

            if (!$companyColumn) {
                Log::debug('getCompany() - Missing company column on model table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $modelTable,
                    'checked_columns' => [CPC::COL_CP_ID, 'company'],
                ]);
                return null;
            }

            $companyId = $model->getAttribute($companyColumn);

            if (!$companyId)
                return $model->belongsTo(User::class, $companyColumn, 'id')->where('type', UserType::Company->value);

            $usersTable = (new User)->getTable();
            $typeColumn = Schema::hasColumn($usersTable, UC::COL_TP) ? UC::COL_TP : 'type';

            Log::debug('getCompany() - Using users.type=company', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'company_id' => $companyId,
                'company_column' => $companyColumn,
                'users_table' => $usersTable,
                'type_column' => $typeColumn,
                'type_value' => UserType::Company->value,
            ]);

            return $model
                ->belongsTo(User::class, $companyColumn, 'id')
                ->where($typeColumn, UserType::Company->value);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'utility_errors',
                candidate: [
                    'message' => 'getCompany() - Failed to resolve company relation',
                    'context' => [
                        'class' => static::class,
                        'method' => __METHOD__,
                        'line' => __LINE__,
                        'company_id' => $companyColumn ? $model->getAttribute($companyColumn) : null,
                        'company_column' => $companyColumn,
                        'error' => $e->getMessage(),
                    ]
                ],
                mainChannel: 'error'
            );
            return $companyColumn
                ? $model->belongsTo(User::class, $companyColumn, 'id')->where('type', UserType::Company->value)
                : null;
        }
    }

    public static function getCategory(Model $model): ?BelongsTo
    {
        try {
            $modelTable = $model->getTable();

            $foreignKeyCandidates = [
                BC::COL_CAT_ID,
                PJC::COL_PRD_SERV_CAT_ID,
                'product_service_category',
                'category',
                'product_category',
            ];

            $existingForeignKeys = array_values(array_filter(
                $foreignKeyCandidates,
                fn($col) => Schema::hasColumn($modelTable, $col)
            ));

            if (!$existingForeignKeys) {
                Log::debug('getCategory() - No valid category FK column found on model table', [
                    'class' => $model::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $modelTable,
                    'checked_columns' => $foreignKeyCandidates,
                ]);

                return null;
            }

            $foreignKeyOnModel = null;
            foreach ($existingForeignKeys as $col) {
                $v = $model->getAttribute($col);
                if ($v !== null && trim((string) $v) !== '') {
                    $foreignKeyOnModel = $col;
                    break;
                }
            }
            $foreignKeyOnModel ??= $existingForeignKeys[0];

            $categoryId = $model->getAttribute($foreignKeyOnModel);

            $ownerKeyCandidates = [
                'id',
                PJC::COL_PRD_SERV_CAT_ID,
                BC::COL_CAT_ID,
                'product_service_category',
                'product_category',
                'category',
            ];

            $resolveOwnerKey = function (string $table) use ($ownerKeyCandidates): ?string {
                if (!Schema::hasTable($table)) return null;
                foreach ($ownerKeyCandidates as $candidate) {
                    if (Schema::hasColumn($table, $candidate)) return $candidate;
                }
                return null;
            };

            $serviceCategoryTable = (new ProductServiceCategory)->getTable();
            $productCategoryTable = (new ProductCategory)->getTable();

            $serviceOwnerKey = $resolveOwnerKey($serviceCategoryTable);
            $productOwnerKey = $resolveOwnerKey($productCategoryTable);

            if ($categoryId === null || trim((string) $categoryId) === '') {
                return $model->belongsTo(ProductServiceCategory::class, $foreignKeyOnModel, $serviceOwnerKey ?: 'id');
            }

            $foundInServiceCategory = $serviceOwnerKey
                ? DB::table($serviceCategoryTable)->where($serviceOwnerKey, $categoryId)->exists()
                : false;

            if ($foundInServiceCategory) {
                return $model->belongsTo(ProductServiceCategory::class, $foreignKeyOnModel, $serviceOwnerKey);
            }

            $foundInProductCategory = $productOwnerKey
                ? DB::table($productCategoryTable)->where($productOwnerKey, $categoryId)->exists()
                : false;

            if ($foundInProductCategory) {
                Log::debug('getCategory() - Not found in ProductServiceCategory; falling back to ProductCategory', [
                    'class' => $model::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'model_table' => $modelTable,
                    'foreign_key_on_model' => $foreignKeyOnModel,
                    'category_id' => $categoryId,
                    'service_category_table' => $serviceCategoryTable,
                    'service_owner_key' => $serviceOwnerKey,
                    'product_category_table' => $productCategoryTable,
                    'product_owner_key' => $productOwnerKey,
                ]);

                return $model->belongsTo(ProductCategory::class, $foreignKeyOnModel, $productOwnerKey);
            }

            Log::debug('getCategory() - Category id not found in either ProductServiceCategory or ProductCategory; defaulting to ProductServiceCategory relation', [
                'class' => $model::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'model_table' => $modelTable,
                'foreign_key_on_model' => $foreignKeyOnModel,
                'category_id' => $categoryId,
                'service_category_table' => $serviceCategoryTable,
                'service_owner_key' => $serviceOwnerKey,
                'product_category_table' => $productCategoryTable,
                'product_owner_key' => $productOwnerKey,
            ]);

            return $model->belongsTo(ProductServiceCategory::class, $foreignKeyOnModel, $serviceOwnerKey ?: 'id');
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'utility_errors',
                candidate: [
                    'message' => 'getCategory() - Failed to resolve category relation with fallback',
                    'context' => [
                        'class' => $model::class,
                        'method' => __METHOD__,
                        'line' => __LINE__,
                        'model_table' => method_exists($model, 'getTable') ? $model->getTable() : null,
                        'error' => $e->getMessage(),
                    ],
                ],
                mainChannel: 'error'
            );
            return null;
        }
    }

    public static function getProduct(Model $model): ?BelongsTo
    {
        try {
            $modelTable = $model->getTable();

            $foreignKeyCandidates = [
                BC::COL_PRD_SV_ID,
                BC::COL_PRD_ID,
                'product',
                'product_service',
            ];

            $existingForeignKeys = array_values(array_filter(
                $foreignKeyCandidates,
                fn($col) => Schema::hasColumn($modelTable, $col)
            ));

            if (!$existingForeignKeys) {
                Log::debug('getProduct() - No valid product FK column found on model table', [
                    'class' => $model::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $modelTable,
                    'checked_columns' => $foreignKeyCandidates,
                ]);

                return null;
            }

            $nonEmptyForeignKeys = [];
            foreach ($existingForeignKeys as $col) {
                $v = $model->getAttribute($col);
                if ($v !== null && trim((string) $v) !== '') $nonEmptyForeignKeys[$col] = $v;
            }

            $productServiceTable = (new ProductService)->getTable();
            $productTable = (new Product)->getTable();

            $ownerKeyCandidates = [
                'id',
                BC::COL_PRD_SV_ID,
                BC::COL_PRD_ID,
                'product_service_id',
                'product_service',
                'product_id',
                'product',
            ];

            $resolveOwnerKey = function (string $table) use ($ownerKeyCandidates): ?string {
                if (!Schema::hasTable($table)) return null;
                foreach ($ownerKeyCandidates as $candidate) {
                    if (Schema::hasColumn($table, $candidate)) return $candidate;
                }
                return null;
            };

            $productServiceOwnerKey = $resolveOwnerKey($productServiceTable) ?: 'id';
            $productOwnerKey = $resolveOwnerKey($productTable) ?: 'id';

            if (!$nonEmptyForeignKeys) {
                return $model->belongsTo(ProductService::class, $existingForeignKeys[0], $productServiceOwnerKey);
            }

            $servicePreferredOrder = [
                BC::COL_PRD_SV_ID,
                'product_service',
                BC::COL_PRD_ID,
                'product',
            ];

            foreach ($servicePreferredOrder as $fk) {
                if (!array_key_exists($fk, $nonEmptyForeignKeys)) continue;

                $value = $nonEmptyForeignKeys[$fk];

                if (Schema::hasTable($productServiceTable) && Schema::hasColumn($productServiceTable, $productServiceOwnerKey)) {
                    if (DB::table($productServiceTable)->where($productServiceOwnerKey, $value)->exists()) {
                        return $model->belongsTo(ProductService::class, $fk, $productServiceOwnerKey);
                    }
                }
            }

            $productPreferredOrder = [
                BC::COL_PRD_ID,
                'product',
                BC::COL_PRD_SV_ID,
                'product_service',
            ];

            foreach ($productPreferredOrder as $fk) {
                if (!array_key_exists($fk, $nonEmptyForeignKeys)) continue;

                $value = $nonEmptyForeignKeys[$fk];

                if (Schema::hasTable($productTable) && Schema::hasColumn($productTable, $productOwnerKey)) {
                    if (DB::table($productTable)->where($productOwnerKey, $value)->exists()) {
                        Log::debug('getProduct() - Not found in ProductService; falling back to Product', [
                            'class' => $model::class,
                            'method' => __METHOD__,
                            'line' => __LINE__,
                            'model_table' => $modelTable,
                            'foreign_key_on_model' => $fk,
                            'value' => $value,
                            'product_service_table' => $productServiceTable,
                            'product_service_owner_key' => $productServiceOwnerKey,
                            'product_table' => $productTable,
                            'product_owner_key' => $productOwnerKey,
                        ]);

                        return $model->belongsTo(Product::class, $fk, $productOwnerKey);
                    }
                }
            }

            Log::debug('getProduct() - FK value not found in either ProductService or Product; defaulting to ProductService relation', [
                'class' => $model::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'model_table' => $modelTable,
                'non_empty_keys' => array_keys($nonEmptyForeignKeys),
                'product_service_table' => $productServiceTable,
                'product_service_owner_key' => $productServiceOwnerKey,
                'product_table' => $productTable,
                'product_owner_key' => $productOwnerKey,
            ]);

            $defaultFk = array_key_exists(BC::COL_PRD_SV_ID, $nonEmptyForeignKeys)
                ? BC::COL_PRD_SV_ID
                : (array_key_exists('product_service', $nonEmptyForeignKeys) ? 'product_service' : array_key_first($nonEmptyForeignKeys));

            return $model->belongsTo(ProductService::class, $defaultFk, $productServiceOwnerKey);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'utility_errors',
                candidate: [
                    'message' => 'getProduct() - Failed to resolve product relation with fallback',
                    'context' => [
                        'class' => $model::class,
                        'method' => __METHOD__,
                        'line' => __LINE__,
                        'model_table' => method_exists($model, 'getTable') ? $model->getTable() : null,
                        'error' => $e->getMessage(),
                    ]
                ],
                mainChannel: 'error',
            );
            return null;
        }
        return null;
    }

    public static function getEmployee(Model $model): ?BelongsTo
    {
        $employeeColumn = null;
        try {
            $modelTable = $model->getTable();

            if (Schema::hasColumn($modelTable, UC::COL_EMP_ID))
                $employeeColumn = UC::COL_EMP_ID;
            elseif (Schema::hasColumn($modelTable, 'employee'))
                $employeeColumn = 'employee';

            if (!$employeeColumn) {
                Log::debug('getEmployee() - Missing employee column on model table', [
                    'class' => static::class,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'table' => $modelTable,
                    'checked_columns' => [UC::COL_EMP_ID, 'employee'],
                ]);
                return null;
            }

            $employeeId = $model->getAttribute($employeeColumn);

            if (!$employeeId)
                return $model->belongsTo(Employee::class, $employeeColumn, 'id');

            $employeesTable = DC::TABLE_EMPLOYEES;
            $employeeExists = Schema::hasTable($employeesTable) && DB::table($employeesTable)->where('id', $employeeId)->exists();

            if ($employeeExists)
                return $model->belongsTo(Employee::class, $employeeColumn, 'id');

            $usersTable = DC::TABLE_USERS;
            $typeColumn = Schema::hasColumn($usersTable, UC::COL_TP) ? UC::COL_TP : 'type';

            Log::debug('getEmployee() - Employee not found in main table; falling back to users.type IN (hr, admin, super_admin, company)', [
                'class' => static::class,
                'method' => __METHOD__,
                'line' => __LINE__,
                'employee_id' => $employeeId,
                'employee_column' => $employeeColumn,
                'employees_table' => $employeesTable,
                'users_table' => $usersTable,
                'type_column' => $typeColumn,
                'type_values' => [UserType::Hr->value, UserType::Admin->value, UserType::SuperAdmin->value, UserType::Company->value],
            ]);
            return $model
                ->belongsTo(User::class, $employeeColumn, 'id')
                ->whereIn($typeColumn, [UserType::Hr->value, UserType::Admin->value, UserType::SuperAdmin->value, UserType::Company->value]);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'utility_errors',
                candidate: [
                    'message' => 'getEmployee() - Failed to resolve employee relation with fallback',
                    'context' => [
                        'class' => static::class,
                        'method' => __METHOD__,
                        'line' => __LINE__,
                        'employee_id' => $employeeColumn ? $model->getAttribute($employeeColumn) : null,
                        'employee_column' => $employeeColumn,
                        'error' => $e->getMessage(),
                    ]
                ],
                mainChannel: 'error',
            );
            return $employeeColumn
                ? $model->belongsTo(Employee::class, $employeeColumn, 'id')
                : null;
        }
    }

    public static function isEmployee(User $user): bool
    {
        try {
            return in_array(
                strtolower((string) ($user[UC::COL_TP] ?? '')),
                [
                    'employee',
                    UserType::Admin->value,
                    UserType::SuperAdmin->value,
                    UserType::Hr->value,
                    UserType::Company->value
                ],
                true
            ) || DB::table(DC::TABLE_EMPLOYEES)
                ->where('id', $user[UC::COL_EMP_ID] ?? null)
                ->exists();
        } catch (\Throwable $e) {
            Log::notice('isEmployee check failed for user ' . ($user?->id ?? 'null'), [
                'method' => __METHOD__,
                'line' => __LINE__,
                'class' => static::class,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Prepare common view data including settings, files, SEO meta, and layout options.
     *
     * @param  int|string  $creatorId  Company/creator ID, or default UUID for global.
     * @param  string|null $logoPath   Custom logo path, defaults to 'uploads/logo/'.
     * @return array<string, mixed>    Array of all shared view variables.
     */
    public static function prepareCommonViewData(int|string $creatorId = DC::DEFAULT_UUID, ?string $logoPath = null): array
    {
        $settings     = ($creatorId && $creatorId !== DC::DEFAULT_UUID)
            ? Utility::settingsById($creatorId)
            : Utility::settings();
        $colorSettings = Utility::colorset();
        $locale       = app()->getLocale();
        $seo          = Utility::getSeoSetting();
        $company_logo_dk = $settings[SC::CPN_LG_DK]
            ?? $settings[SC::CPN_LG_LT]
            ?? '';
        $company_logo_lt = $settings[SC::CPN_LG_LT]
            ?? $settings[SC::CPN_LG_DK]
            ?? '';
        $company_favicon = $settings[SC::FAV_ICN]
            ?? asset(SC::CPN_FAVICON_DEF);
        $logo = $settings[SC::LOGO]
            ?? asset($logoPath)
            ?? asset(SC::CPN_FAVICON_DEF);
        $secondary_logo = $settings[SC::SC_LOGO]
            ?? asset($logoPath)
            ?? asset(SC::CPN_FAVICON_DEF);
        $color = $settings[SC::THM_CLR]
            ?? SC::THM_CLR_DEF;
        $siteRtl = $settings[SC::RTL]
            ?? 'off';
        if (in_array($locale, ['ar', 'he'], true))
            $siteRtl = 'on';
        $lang = $settings[SC::LCL]
            ?? str_replace('_', '-', $locale)
            ?? DC::DEFAULT_LANG;
        $meta_title = $seo[SC::MT_TTL_K]
            ?? config('app.name', 'ERPNovaBrand New Ideas Company');
        $meta_desc = $seo[SC::MT_DESC_LONG]
            ?? config('app.desc', 'A brand new ERP!');
        $meta_image = $seo[SC::MT_IMG_K]
            ?? $company_logo_lt;
        $meta_logo = $seo[SC::MT_LOGO]
            ?? $seo[SC::MT_IMG_K]
            ?? $company_logo_lt;
        $cookie_setting = $settings[SC::CK_STG]
            ?? 'off';
        $modeLayout = method_exists(Utility::class, SC::MD_LO)
            ? Utility::mode_layout()
            : null;
        if (!is_array($colorSettings))
            $colorSettings = [];
        if (empty($colorSettings[SC::CST_DRK]))
            $colorSettings[SC::CST_DRK] = 'off';
        if (empty($settings[SC::RCPT_MDL]))
            $settings[SC::RCPT_MDL] = 'off';
        return [
            SC::ENTITY       => $settings,
            SC::CLR_STG      => $colorSettings,
            SC::RTL          => $siteRtl,
            SC::LCL          => $lang,
            SC::MT_TTL_K     => $meta_title,
            SC::MT_DESC_LONG => $meta_desc,
            SC::MT_IMG_K     => $meta_image,
            SC::MT_LOGO      => $meta_logo,
            SC::LOGO         => $logo,
            SC::SC_LOGO      => $secondary_logo,
            SC::FAV_ICN      => $company_favicon,
            SC::THM_CLR      => $color,
            SC::CK_STG       => $cookie_setting,
            SC::MD_LO        => $modeLayout,
            SC::CPN_CFG      => $settings,
        ];
    }
}
