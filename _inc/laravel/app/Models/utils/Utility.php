<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    ChartsConstants as CTC,
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    EmailsConstants as EC,
    FormsConstants as FC,
    LangsConstants as LC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Enums\{BrazilState, UserType};
use App\Helpers\ErrorHandler;
use App\Mail\CommonEmailTemplate;
use App\Models\{
    Branch,
    Client,
    Department,
    Designation,
    Employee,
    Permission,
    Role,
    Tax,
    User
};
use App\Traits\ChecksLogin;
use Carbon\{Carbon, CarbonPeriod};
use Faker\Factory as Faker;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\{Model, ModelNotFoundException};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\{Collection, Str};
use Illuminate\Support\Facades\{
    App,
    Artisan,
    Auth,
    Cache,
    Config,
    DB,
    File,
    Http,
    Mail,
    Lang,
    Log,
    Route,
    Schema,
    Storage,
    Validator
};
use Spatie\GoogleCalendar\Event as GoogleEvent;
use App\Helpers\SafeConsoleOutput;
use App\Services\Utility\AccountingService;
use App\Services\Utility\CalendarService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Filesystem\FilesystemAdapter;

// Same-namespace explicit imports (silences PHP Namespace Resolver)
use App\Models\BankAccount;
use App\Models\BillAccount;
use App\Models\BillPayment;
use App\Models\BillProduct;
use App\Models\Budget;
use App\Models\BugStatus;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\Indicator;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\JobStage;
use App\Models\JournalItem;
use App\Models\Label;
use App\Models\Language;
use App\Models\LeadStage;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateLang;
use App\Models\Payment;
use App\Models\Payslip;
use App\Models\PayslipType;
use App\Models\Pipeline;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Project;
use App\Models\Revenue;
use App\Models\Source;
use App\Models\Stage;
use App\Models\StockReport;
use App\Models\TaskStage;
use App\Models\UserEmailTemplate;
use App\Models\Vendor;
use App\Models\WarehouseProduct;
use App\Models\WebhookSettings;

class Utility extends Model
{
    use ChecksLogin;

    private static $getSettings    = null;
    private static $getSettingsId  = [];
    private static $taxsData       = [];
    private static $taxRateData    = [];
    private static $taxData        = null;
    private static $taxes          = [];
    private static $languageSetting = null;
    private static $getRatingData  = null;
    public static array $DEFAULT_SETTINGS = SC::DFT_SETTINGS;
    public static $colorCode = SC::CLR_CD;
    public static $chartOfAccountType = CTC::COA_TPS;
    public static $chartOfAccountSubType = CTC::COA_SBTPS;
    public static $emailStatus = EC::STATUS_MAP;
    private const DEFAULT_SETTINGS = SC::DFT_SETTINGS;
    private const DEFAULT_SETTINGS_BY_ID = SC::DFT_SETTINGS_IDF;
    public static $chartOfAccount = CTC::COA_SETTINGS_0;
    public static $chartOfAccount1 = CTC::COA_SETTINGS_1;
    private const ARR_PERMISSIONS = FC::PERMISSIONS;
    private const COMPANY_DATA_PERMISSIONS = FC::PERMISSIONS;
    private const FST_DSK = 'filesystems.disks';
    private const FST_DSK_WSB = self::FST_DSK . '.wasabi.';
    private const FST_DSK_S3 = self::FST_DSK . '.s3.';
    private const FST_DSK_WSB_K = self::FST_DSK_WSB . 'key';
    private const FST_DSK_WSB_SC = self::FST_DSK_WSB . 'secret';
    private const FST_DSK_WSB_RG = self::FST_DSK_WSB . 'region';
    private const FST_DSK_WSB_BK = self::FST_DSK_WSB . 'bucket';
    private const FST_DSK_WSB_EP = self::FST_DSK_WSB . 'endpoint';
    private const FST_DSK_S3_K = self::FST_DSK_S3 . 'key';
    private const FST_DSK_S3_SC = self::FST_DSK_S3 . 'secret';
    private const FST_DSK_S3_RG = self::FST_DSK_S3 . 'region';
    private const FST_DSK_S3_BK = self::FST_DSK_S3 . 'bucket';
    private const FST_DSK_S3_EP = self::FST_DSK_S3 . 'use_path_style_endpoint';
    /** @var string[] Already-used UUIDs in this PHP process */
    protected static array $uuids = [];
    protected static array $utilityErrors = [];

    /**
     * Reset all static settings/tax caches.
     * Useful in tests to ensure a clean state between test methods.
     */
    public static function resetSettingsCache(): void
    {
        self::$getSettings    = null;
        self::$getSettingsId  = [];
        self::$taxsData       = null;
        self::$taxRateData    = null;
        self::$taxData        = null;
        self::$taxes          = null;
        self::$languageSetting = null;
        self::$getRatingData  = null;
    }

    public static function generateUuid(): string
    {
        do $uuid = Str::uuid()->toString();
        while (in_array($uuid, self::$uuids, true));
        self::$uuids[] = $uuid;
        return $uuid;
    }

    public static function looksLikeUuid(string $s): bool
    {
        if (!$s || !is_string($s)) return false;
        $s = trim($s);
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $s
        );
    }

    public static function generateRandomBase64Path(): string
    {
        return '/' . str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode(Faker::create()->text(20))
        );
    }

    public static function getReferrer(Request $request): string
    {
        return $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous();
    }

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

            $clientsTable = (new \App\Models\Client)->getTable();
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

    public static function isValidRouteUrl(string $url): bool
    {
        try {
            $route = Route::getRoutes()->match(Request::create($url));
            if (!$route) {
                Log::notice("Invalid route URL: {$url}");
                return false;
            }
            return Route::has($route->getName());
        } catch (MethodNotAllowedHttpException $e) {
            Log::warning("Method not allowed for route URL: {$url}", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attempted_url' => is_string($url) ? $url : '# UNDEFINED URL',
            ]);
            return false;
        } catch (NotFoundHttpException $e) {
            Log::warning("Route URL not found: {$url}", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attempted_url' => is_string($url) ? $url : '# UNDEFINED URL',
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error("Error checking route URL: {$url}", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attempted_url' => is_string($url) ? $url : '# UNDEFINED URL',
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
            ? self::settingsById($creatorId)
            : self::settings();
        $colorSettings = self::colorset();
        $locale       = app()->getLocale();
        $seo          = self::getSeoSetting();
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
            ?? config('app.name', 'ERPNovaPrestech');
        $meta_desc = $seo[SC::MT_DESC_LONG]
            ?? config('app.desc', 'A brand new ERP!');
        $meta_image = $seo[SC::MT_IMG_K]
            ?? $company_logo_lt;
        $meta_logo = $seo[SC::MT_LOGO]
            ?? $seo[SC::MT_IMG_K]
            ?? $company_logo_lt;
        $cookie_setting = $settings[SC::CK_STG]
            ?? 'off';
        $modeLayout = method_exists(self::class, SC::MD_LO)
            ? self::mode_layout()
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

    public static function isFilled(mixed $list = null): bool
    {
        return isset($list) && !empty($list) && (is_array($list) ? count($list) : ($list instanceof Collection ? $list->isNotEmpty() : false));
    }

    public static function settings(): array
    {
        $output = SafeConsoleOutput::make();
        $class  = class_basename(self::class);
        $method = __FUNCTION__;
        $tag    = "{$class}::{$method}";
        Log::debug("{$tag} start");
        $output->writeln("## {$tag} -- Retrieving settings…");
        try {
            $userOrRedirect = self::_checkLogin(haltRedirect: true);
            if ($userOrRedirect instanceof User) {
                /** @var User $user */
                $user = $userOrRedirect;
                Log::debug("{$tag} authenticated user", [UC::COL_USER_ID => $user?->id]);
                $output->writeln("## {$tag} -- User ID: {$user?->id}");
                $userId = $user?->creatorId();
                Log::debug("{$tag} fetching settings by user ID", [DC::COL_TABLE_CREATOR => $userId]);
                $data = self::getSettingsById($userId);
                if (empty($data)) {
                    Log::info("{$tag} No user settings found. Loading global defaults...");
                    $output->writeln("## {$tag} -- No user-specific settings, falling back to defaults");
                    $data = self::getSettings();
                }
            } else {
                Log::debug("{$tag} -- No user instance. Loading global settings...");
                $output->writeln("## {$tag} Loading global settings");
                $data = self::getSettings();
            }
            $settings = self::DEFAULT_SETTINGS;
            foreach ($data as $name => $value)
                $settings[$name] = $value;
            Log::debug("{$tag} applying config", [
                SC::G_RCPT_K => $settings[SC::G_RCPT_K] ?? '',
            ]);
            config([
                'captcha.secret'  => $settings[SC::G_RCPT_SC] ?? '',
                'captcha.sitekey' => $settings[SC::G_RCPT_K]    ?? '',
                'options'         => ['timeout' => 30],
            ]);
            Log::debug("{$tag} complete");
            $output->writeln("## {$tag} -- Settings loaded successfully");
            return $settings;
        } catch (\Throwable $e) {
            Log::error("{$tag} exception", [
                'message' => $e->getMessage(),
            ]);
            Log::channel(SC::ERR_TRACE)->debug("{$tag} exception", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $output->writeln("## {$tag} -- Error loading settings: {$e->getMessage()}");
            return self::DEFAULT_SETTINGS;
        }
    }

    public static function getSettings(): array
    {
        $output = SafeConsoleOutput::make();
        $class  = class_basename(self::class);
        $method = __FUNCTION__;
        $tag    = "{$class}::{$method}";
        Log::debug("{$tag} called");
        $output->writeln("## [{$tag}] Getting global settings");
        if (!self::$getSettings) {
            try {
                $data = DB::table(DC::TABLE_SETTINGS)
                    ->where(DC::COL_TABLE_CREATOR, DC::DEFAULT_UUID)
                    ->pluck('value', 'name')
                    ->toArray();
                if (empty($data)) {
                    Log::warning("{$tag} no default settings found; using system default");
                    $data = SC::DFT_SETTINGS;
                }
                self::$getSettings = $data;
                Log::info("{$tag} default settings retrieved", ['count' => count($data)]);
                $output->writeln("## [{$tag}] Retrieved " . count($data) . " default setting(s)");
            } catch (QueryException $e) {
                Log::error("{$tag} QueryException", ['message' => $e->getMessage()]);
                $output->writeln("## [{$tag}] DB error: {$e->getMessage()}");
                self::$getSettings = SC::DFT_SETTINGS;
            } catch (\Throwable $e) {
                Log::error("{$tag} unexpected exception", [
                    'message' => $e->getMessage(),
                ]);
                Log::channel(SC::ERR_TRACE)->debug("{$tag} unexpected exception", [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                $output->writeln("## [{$tag}] Error loading default settings: {$e->getMessage()}");
                self::$getSettings = SC::DFT_SETTINGS;
            }
        } else {
            Log::debug("{$tag} returning cached default settings", ['count' => count(self::$getSettings)]);
            $output->writeln("## [{$tag}] Returning cached default settings (" . count(self::$getSettings) . ")");
        }
        return self::$getSettings;
    }

    public static function settingsById(string|int $userId): array
    {
        $output = SafeConsoleOutput::make();
        $class  = class_basename(self::class);
        $method = __FUNCTION__;
        $tag    = "{$class}::{$method}";
        Log::debug("{$tag} start", [UC::COL_USER_ID => $userId]);
        $output->writeln("## {$tag} -- Loading settings for user ID {$userId}");
        try {
            $data = self::getSettingsById($userId);
            $count = is_array($data) ? count($data) : 0;
            Log::debug("{$tag} fetched settings", ['count' => $count]);
            $output->writeln("## {$tag} -- Retrieved {$count} setting(s)");
            $settings = self::DEFAULT_SETTINGS_BY_ID;
            foreach ($data as $name => $value)
                $settings[$name] = $value;
            Log::debug("{$tag} settings assembled", [
                UC::COL_USER_ID => $userId,
                'settings'                 => $settings,
            ]);
            $output->writeln("## {$tag} -- Settings assembled successfully");
            return $settings;
        } catch (\Throwable $e) {
            Log::error("{$tag} exception", [
                UC::COL_USER_ID => $userId,
                'message'                  => $e->getMessage(),
                'file'                     => $e->getFile(),
                'line'                     => $e->getLine(),
            ]);
            $output->writeln("## {$tag} -- Error: {$e->getMessage()} – falling back to defaults");
            return self::DEFAULT_SETTINGS_BY_ID;
        }
    }

    public static function getSettingsById(string|int $id): array
    {
        $output = SafeConsoleOutput::make();
        $class = class_basename(self::class);
        $method = __FUNCTION__;
        $tag   = "{$class}::{$method}";
        Log::debug("{$tag} called", [UC::COL_USER_ID => $id]);
        $output->writeln("## [{$tag}] Fetching settings for user ID {$id}");
        if (!isset(self::$getSettingsId[$id])) {
            try {
                $data = DB::table(DC::TABLE_SETTINGS)
                    ->where(DC::COL_TABLE_CREATOR, $id)
                    ->pluck('value', 'name')
                    ->toArray();
                if (empty($data)) {
                    Log::info("{$tag} no settings for user {$id}, falling back to user defaults");
                    $data = DB::table(DC::TABLE_SETTINGS)
                        ->where(DC::COL_TABLE_CREATOR, DC::DEFAULT_UUID)
                        ->pluck('value', 'name')
                        ->toArray();
                }
                if (empty($data)) {
                    Log::notice("{$tag} no default settings found; using system default");
                    $data = SC::DFT_SETTINGS;
                }
                self::$getSettingsId[$id] = $data;
                Log::debug("{$tag} found settings", ['count' => count($data)]);
                $output->writeln("## [{$tag}] Retrieved " . count($data) . " rows");
            } catch (QueryException $qe) {
                Log::error("{$tag} QueryException", ['message' => $qe->getMessage()]);
                $output->writeln("## [{$tag}] DB error: {$qe->getMessage()}");
                self::$getSettingsId[$id] = SC::DFT_SETTINGS;
            } catch (\Throwable $e) {
                Log::error("{$tag} unexpected exception", [
                    UC::COL_USER_ID => $id,
                    'message'                  => $e->getMessage(),
                ]);
                Log::channel(SC::ERR_TRACE)->debug("{$tag} unexpected exception", [
                    UC::COL_USER_ID => $id,
                    'message'                  => $e->getMessage(),
                    'trace'                    => $e->getTraceAsString(),
                ]);
                $output->writeln("## [{$tag}] Error fetching settings: {$e->getMessage()}");
                self::$getSettingsId[$id] = SC::DFT_SETTINGS;
            }
        } else {
            Log::debug("{$tag} returning cached settings", ['count' => count(self::$getSettingsId[$id])]);
            $output->writeln("## [{$tag}] Returning cached settings (" . count(self::$getSettingsId[$id]) . ")");
        }
        return self::$getSettingsId[$id];
    }

    /**
     * Alias for getSettings() — called by tests as getSetting().
     */
    public static function getSetting(...$args): array
    {
        return static::getSettings(...$args);
    }

    /**
     * Alias for getSettingsById() — called by tests as getSettingById().
     */
    public static function getSettingById(...$args): array
    {
        return static::getSettingsById(...$args);
    }

    public static function fallbackSettings(mixed $data): mixed
    {
        try {
            $setting = Utility::settings() ?: [];
            foreach ($setting as $key => $value) {
                if (empty($data[$key]) && !empty($setting[$key]))
                    $data[$key] = $value;
            }
        } catch (\Error $e) {
            Log::error(
                'Error fetching theme settings',
                [
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            );
        } catch (\Exception $e) {
            Log::error(
                'Exception fetching theme settings',
                [
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            );
        } catch (\Throwable $e) {
            Log::error(
                'Throwable fetching theme settings',
                [
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            );
        }
        return $data;
    }

    public static function getCompanyLogo(): string
    {
        $settings = self::settings();
        $company_favicon = $settings[SC::FAV_ICN] ?? '';
        $logo = $settings[SC::LOGO] ?? '';
        $candidates = [];
        if (!empty($company_favicon)) {
            if (preg_match('/\.(ico|svg|png)$/i', $company_favicon))
                $candidates[] = $company_favicon;
            else
                foreach (['ico', 'svg', 'png'] as $ext)
                    $candidates[] = "{$company_favicon}.{$ext}";
        }
        if (!empty($logo)) {
            if (preg_match('/\.(ico|svg|png)$/i', $logo))
                $candidates[] = $logo;
            else
                foreach (['ico', 'svg', 'png'] as $ext)
                    $candidates[] = "{$logo}.{$ext}";
        }
        $faviconUrl = SC::CPN_FAVICON_DEF;
        foreach ($candidates as $file) {
            if (file_exists(public_path($file))) {
                $faviconUrl = asset($file);
                break;
            }
        }
        return $faviconUrl;
    }

    public static function languages(): Collection
    {
        $output = SafeConsoleOutput::make();
        $tag   = class_basename(self::class) . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output->writeln("## [{$tag}] Loading languages…");
        try {
            if (self::$languageSetting === null) {
                Log::debug("{$tag} no cache, building language list");
                $output->writeln("## [{$tag}] Generating language list");
                if (Schema::hasTable(DC::TABLE_LANGS)) {
                    Log::debug("{$tag} languages table exists");
                    $output->writeln("## [{$tag}] Querying DB for languages");
                    $settings = self::settings();
                    $disabled = $settings[SC::DSB_LNG] ?? '';
                    if (!empty($disabled)) {
                        $codes = array_filter(explode(',', $disabled));
                        Log::debug("{$tag} excluding codes", ['disabled' => $codes]);
                        $languages = Language::whereNotIn('code', $codes)
                            ->pluck('full_name', 'code');
                    } else {
                        Log::debug("{$tag} no disabled languages, loading all");
                        $languages = Language::pluck('full_name', 'code');
                    }
                    Log::debug("{$tag} DB languages loaded", ['count' => $languages->count()]);
                    $collection = $languages;
                } else {
                    Log::warning("{$tag} languages table missing, using default list");
                    $output->writeln("## [{$tag}] Using default map");
                    $default = self::langList();
                    $collection = collect($default);
                }
                self::$languageSetting = $collection;
            } else {
                $count = self::$languageSetting instanceof Collection
                    ? self::$languageSetting->count()
                    : count(self::$languageSetting);
                Log::debug("{$tag} cache hit", ['count' => $count]);
                $output->writeln("## [{$tag}] Cache hit ({$count} entries)");
                if (!self::$languageSetting instanceof Collection)
                    self::$languageSetting = collect(self::$languageSetting);
            }
            if (self::$languageSetting->isEmpty()) {
                Log::warning("{$tag} languages list empty, falling back to DEFAULT_LANG");
                $output->writeln("## [{$tag}] Empty list, using DEFAULT_LANG");
                self::$languageSetting = collect([
                    DC::DEFAULT_LANG => DC::DEFAULT_LANG_LONG
                ]);
            }
            return self::$languageSetting;
        } catch (QueryException $qe) {
            Log::error("{$tag} QueryException", ['message' => $qe->getMessage()]);
            $output->writeln("## [{$tag}] DB error: {$qe->getMessage()}");
            return collect([DC::DEFAULT_LANG => DC::DEFAULT_LANG_LONG]);
        } catch (\Throwable $e) {
            Log::error("{$tag} unexpected error", [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            $output->writeln("## [{$tag}] Exception: {$e->getMessage()}");
            return collect([DC::DEFAULT_LANG => DC::DEFAULT_LANG_LONG]);
        }
    }

    public static function getValByName(string $key): string
    {
        return self::settings()[$key] ?? '';
    }

    public static function setEnvironmentValue(array $values): bool
    {
        $envFile = app()->environmentFilePath();
        try {
            $str = file_get_contents($envFile);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed reading env file: {$e->getMessage()}");
            return false;
        }
        if (!empty($values)) {
            foreach ($values as $envKey => $envValue) {
                $pair       = "{$envKey}='{$envValue}'";
                $pattern    = "/^{$envKey}=.*/m";
                if (preg_match($pattern, $str))
                    $str = preg_replace($pattern, $pair, $str);
                else
                    $str .= "\n{$pair}";
            }
        }
        $str = rtrim($str, "\n") . "\n";
        if (file_put_contents($envFile, $str) === false) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed writing env file");
            return false;
        }
        return true;
    }

    public static function templateData(): array
    {
        $colors = [
            '003580',
            '666666',
            '6676ef',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000',
        ];
        $templates = [
            'template1' => 'New York',
            'template2' => 'Toronto',
            'template3' => 'Rio',
            'template4' => 'London',
            'template5' => 'Istanbul',
            'template6' => 'Mumbai',
            'template7' => 'Hong Kong',
            'template8' => 'Tokyo',
            'template9' => 'Sydney',
            'template10' => 'Paris',
        ];
        return ['colors' => $colors, 'templates' => $templates];
    }

    public static function priceFormat(array $settings, float|int $price): string
    {
        $symbol  = $settings[SC::CR_SB] ?? '';
        $position = $settings[SC::CR_SB_P] ?? 'pre';
        $decimals = (int) ($settings['decimal_number'] ?? 2);
        $formatted = number_format($price, $decimals);
        return ($position === 'pre' ? $symbol : '')
            . $formatted
            . ($position === 'post' ? $symbol : '');
    }

    public static function currencySymbol(array $settings): string
    {
        return $settings[SC::CR_SB] ?? '';
    }

    public static function dateFormat(array $settings, string $date): string
    {
        return date($settings['site_date_format'] ?? 'Y-m-d', strtotime($date));
    }

    public static function timeFormat(array $settings, string $time): string
    {
        return date($settings['site_time_format'] ?? 'H:i:s', strtotime($time));
    }

    public static function purchaseNumberFormat(int|string $number): string
    {
        return self::formatNumber(SC::PRC_PFX, $number);
    }

    public static function posNumberFormat(int|string $number): string
    {
        return self::formatNumber(SC::POS_PFX, $number);
    }

    public static function contractNumberFormat(int|string $number): string
    {
        return self::formatNumber(SC::CTC_PFX, $number);
    }

    public static function invoiceNumberFormat(array $settings, int|string $number): string
    {
        $prefix = $settings[SC::INV_PFX] ?? '';
        return $prefix . sprintf('%05d', (int) $number);
    }

    public static function proposalNumberFormat(array $settings, int|string $number): string
    {
        $prefix = $settings[SC::PPS_PFX] ?? '';
        return $prefix . sprintf('%05d', (int) $number);
    }

    public static function customerProposalNumberFormat(int|string $number): string
    {
        return self::formatNumber(SC::PPS_PFX, $number);
    }

    public static function customerInvoiceNumberFormat(int|string $number): string
    {
        return self::formatNumber(SC::INV_PFX, $number);
    }

    public static function customerPosNumberFormat(int|string $number): string
    {
        return self::formatNumber(SC::POS_PFX, $number);
    }

    public static function billNumberFormat(array $settings, int|string $number): string
    {
        $prefix = $settings[SC::BL_PFX] ?? '';
        return $prefix . sprintf('%05d', (int) $number);
    }

    public static function vendorBillNumberFormat(int|string $number): string
    {
        return self::formatNumber(SC::BL_PFX, $number);
    }

    /**
     * Compute aggregated item stats for a bill's PDF / template view.
     *
     * Returns a list: [$items, $taxesData, $totalTaxPrice, $totalQuantity, $totalRate, $totalDiscount]
     *
     * @param  Bill               $bill     Bill with items eagerly loaded.
     * @param  array<string,mixed> $settings Creator-level settings.
     * @return array{0: list<object>, 1: array<string,float>, 2: float, 3: float, 4: float, 5: float}
     */
    public static function billItemStats(Bill $bill, array $settings): array
    {
        $totalTaxPrice = 0.0;
        $totalQuantity = 0.0;
        $totalRate     = 0.0;
        $totalDiscount = 0.0;
        $taxesData     = [];
        $items         = [];

        foreach ($bill->items as $it) {
            $name     = $it->productService?->name ?? '';
            $qty      = (float) ($it->quantity ?? 0);
            $price    = (float) ($it->price ?? 0);
            $discount = (float) ($it->discount ?? 0);
            $taxRate  = (string) ($it->tax ?? '');

            $totalQuantity += $qty;
            $totalRate     += $price;
            $totalDiscount += $discount;

            $itemTaxes = [];
            if ($taxRate !== '') {
                foreach (self::tax($taxRate) as $tax) {
                    $taxPrice       = self::taxRate((float) ($tax->rate ?? 0), $price, $qty, $discount);
                    $totalTaxPrice += $taxPrice;
                    $itemTaxes[]    = [
                        'name'      => $tax->name ?? '',
                        'rate'      => ($tax->rate ?? 0) . '%',
                        'price'     => self::priceFormat($settings, $taxPrice),
                        'tax_price' => $taxPrice,
                    ];
                    $taxesData[$tax->name ?? ''] = ($taxesData[$tax->name ?? ''] ?? 0) + $taxPrice;
                }
            }

            $items[] = (object) [
                'name'        => $name,
                'quantity'    => $qty,
                'tax'         => $taxRate,
                'discount'    => $discount,
                'price'       => $price,
                'unit'        => $it->productService?->unit_id ?? 0,
                'description' => $it->description ?? '',
                'itemTax'     => $itemTaxes,
            ];
        }

        return [$items, $taxesData, $totalTaxPrice, $totalQuantity, $totalRate, $totalDiscount];
    }

    public static function getTax(string|int $taxId): ?Tax
    {
        if (!isset(self::$taxes[$taxId])) {
            try {
                self::$taxes[$taxId] = Tax::find($taxId);
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed fetching Tax[{$taxId}]: {$e->getMessage()}");
                return null;
            }
        }
        return self::$taxes[$taxId] ?? null;
    }

    public static function tax(string $taxesCsv): array
    {
        $cacheKey = $taxesCsv;
        if (!isset(self::$taxsData[$cacheKey])) {
            $taxIds = array_filter(explode(',', $taxesCsv));
            $results = [];
            foreach ($taxIds as $id) {
                $taxModel = self::getTax(trim($id));
                if ($taxModel !== null)
                    $results[] = $taxModel;
            }
            self::$taxsData[$cacheKey] = $results;
        }
        return self::$taxsData[$cacheKey];
    }

    public static function taxRate(float $taxRate, float $price, float $quantity, float $discount = 0): float
    {
        $base = ($price * $quantity) - $discount;
        return $base * ($taxRate * 0.01);
    }

    public static function totalTaxRate(string $taxesCsv): float
    {
        $cacheKey = $taxesCsv;
        if (!isset(self::$taxRateData[$cacheKey])) {
            $taxIds = array_filter(explode(',', $taxesCsv));
            $rateSum = 0.0;
            foreach ($taxIds as $id) {
                $taxModel = self::getTax(trim($id));
                $rateSum += $taxModel->rate ?? 0;
            }
            self::$taxRateData[$cacheKey] = $rateSum;
        }
        return self::$taxRateData[$cacheKey];
    }

    public static function userBalance(string $userType, string|int $id, float $amount, string $type): void
    {
        $modelClass = $userType === 'customer' ? Customer::class : Vendor::class;
        $user = $modelClass::find($id);
        if (!$user) return;
        try {
            DB::transaction(function () use ($user, $amount, $type) {
                $multiplier = $type === 'credit' ? 1 : -1;
                $newBalance = ($user?->balance ?? 0) + ($amount * $multiplier);
                $user->balance = $newBalance;
                $user?->save();
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating {$userType}Balance[{$id}]: {$e->getMessage()}");
        }
    }

    public static function updateUserBalance(string $userType, string|int $id, float $amount, string $type): void
    {
        $modelClass = $userType === PMC::CT ? Customer::class : Vendor::class;
        $user = $modelClass::find($id);
        if (!$user) return;
        try {
            DB::transaction(function () use ($user, $amount, $type) {
                $multiplier = $type === 'credit' ? -1 : 1;
                $newBalance = ($user?->balance ?? 0) + ($amount * $multiplier);
                $user->balance = $newBalance;
                $user?->save();
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updatingUser {$userType}Balance[{$id}]: {$e->getMessage()}");
        }
    }

    public static function bankAccountBalance(string|int $id, float $amount, string $type): void
    {
        $account = BankAccount::find($id);
        if (!$account) return;
        try {
            DB::transaction(function () use ($account, $amount, $type) {
                $multiplier = $type === 'credit' ? 1 : -1;
                $newBalance = ($account->opening_balance ?? 0) + ($amount * $multiplier);
                $account->opening_balance = $newBalance;
                $account->save();
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating BankAccount[{$id}]: {$e->getMessage()}");
        }
    }

    public static function hex2rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $chars = str_split($hex);
            $hex = implode('', array_map(fn($c) => str_repeat($c, 2), $chars));
        }
        $pairs = str_split($hex, 2);
        return array_map(fn($pair) => hexdec($pair), $pairs);
    }

    public static function getFontColor(string $colorCode): string
    {
        $rgb = self::hex2rgb($colorCode);
        $norm = array_map(fn($v) => $v / 255, $rgb);
        $lin = array_map(
            fn($c) => $c <= 0.03928
                ? $c / 12.92
                : pow(($c + 0.055) / 1.055, 2.4),
            $norm
        );
        $L = 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
        return $L > 0.179 ? 'black' : 'white';
    }

    public static function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item)
            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        return rmdir($dir);
    }

    public const COA_TP_DT = 'chartOfAccountTypeData';
    /** @see AccountingService::seedAccountTypes() */
    public static function chartOfAccountTypeData(string $companyId): void
    {
        AccountingService::seedAccountTypes($companyId);
    }

    public const COA_DATA = 'chartOfAccountData';
    /** @see AccountingService::seedAccounts() */
    public static function chartOfAccountData(object $user): void
    {
        AccountingService::seedAccounts($user, self::$chartOfAccount);
    }

    public const COA_DATA1 = 'chartOfAccountData1';
    /** @see AccountingService::seedAccountsByName() */
    public static function chartOfAccountData1(string|int $userId): void
    {
        AccountingService::seedAccountsByName($userId, self::$chartOfAccount1);
    }

    public static function sendEmailTemplate(string $emailTemplate, array $mailTo, array $obj): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        /** @var User $user */
        $user = $userOrRedirect;
        $mailTo = array_values($mailTo);
        if ($user->type != PMC::SA) {
            $template = EmailTemplate::where('slug', 'LIKE', $emailTemplate)->first();
            if (!$template) {
                return ['is_success' => false, 'error' => __('Mail not send, email not found')];
            }
            $isActiveRecord = $user->type != PMC::SA
                ? UserEmailTemplate::where('template_id', $template->id)
                ->where(UC::COL_USER_ID, $user?->creatorId())->first()
                : (object)['is_active' => 1];
            if (!$isActiveRecord || $isActiveRecord->is_active != 1) {
                return ['is_success' => true, 'error' => false];
            }
            $settings = self::settingsById($user?->id);
            $content = EmailTemplateLang::where('parent_id', $template->id)
                ->where('lang', 'LIKE', $user?->lang)->first();
            $content->from = $template->from;
            if (empty($content->content)) {
                return ['is_success' => false, 'error' => __('Mail not send, email is empty')];
            }
            $content->content = self::replaceVariable($content->content, $obj);
            try {
                config([
                    'mail.driver'       => $settings['mail_driver'],
                    'mail.host'         => $settings['mail_host'],
                    'mail.port'         => $settings['mail_port'],
                    'mail.encryption'   => $settings['mail_encryption'],
                    'mail.username'     => $settings['mail_username'],
                    'mail.password'     => $settings['mail_password'],
                    'mail.from.address' => $settings['mail_from_address'],
                    'mail.from.name'    => $settings['mail_from_name'],
                ]);
                Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
                return ['is_success' => true, 'error' => false];
            } catch (\Throwable $e) {
                $err = $e->getMessage();
                return ['is_success' => false, 'error' => $err];
            }
        }
        return [];
    }

    public static function sendUserEmailTemplate(string $emailTemplate, array $mailTo, array $obj): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        /** @var User $user */
        $user = $userOrRedirect;
        $mailTo = array_values($mailTo);
        $template = EmailTemplate::where('slug', 'LIKE', $emailTemplate)->first();
        if (!$template) {
            return ['is_success' => false, 'error' => __('Mail not send, email not found')];
        }
        $isActiveRecord = UserEmailTemplate::where('template_id', $template->id)
            ->where(UC::COL_USER_ID, $user?->creatorId())->first();
        if (!$isActiveRecord || $isActiveRecord->is_active != 1) {
            return ['is_success' => true, 'error' => false];
        }
        $settings = self::settingsById(1);
        $content = EmailTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'LIKE', $user?->lang)->first();
        $content->from = $template->from;
        if (empty($content->content)) {
            return ['is_success' => false, 'error' => __('Mail not send, email is empty')];
        }
        $content->content = self::replaceVariable($content->content, $obj);
        try {
            config([
                'mail.driver'       => $settings['mail_driver'],
                'mail.host'         => $settings['mail_host'],
                'mail.port'         => $settings['mail_port'],
                'mail.encryption'   => $settings['mail_encryption'],
                'mail.username'     => $settings['mail_username'],
                'mail.password'     => $settings['mail_password'],
                'mail.from.address' => $settings['mail_from_address'],
                'mail.from.name'    => $settings['mail_from_name'],
            ]);
            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
            return ['is_success' => true, 'error' => false];
        } catch (\Throwable $e) {
            $err = $e->getMessage();
            return ['is_success' => false, 'error' => $err];
        }
    }

    public static function replaceVariable($content, $obj): array|string
    {
        $arrVariable = [
            '{app_name}',
            '{company_name}',
            '{app_url}',
            '{email}',
            '{password}',
            '{client_name}',
            '{client_email}',
            '{client_password}',
            '{support_name}',
            '{support_title}',
            '{support_priority}',
            '{support_end_date}',
            '{support_description}',
            '{lead_name}',
            '{lead_email}',
            '{lead_subject}',
            '{lead_pipeline}',
            '{lead_stage}',
            '{deal_name}',
            '{deal_pipeline}',
            '{deal_stage}',
            '{deal_status}',
            '{deal_price}',
            '{award_name}',
            '{award_email}',
            '{customer_name}',
            '{customer_email}',
            '{invoice_name}',
            '{invoice_number}',
            '{invoice_url}',
            '{invoice_payment_name}',
            '{invoice_payment_amount}',
            '{invoice_payment_date}',
            '{payment_dueAmount}',
            '{payment_reminder_name}',
            '{invoice_payment_number}',
            '{invoice_payment_dueAmount}',
            '{payment_reminder_date}',
            '{payment_name}',
            '{payment_bill}',
            '{payment_amount}',
            '{payment_date}',
            '{payment_method}',
            '{vendor_name}',
            '{vendor_email}',
            '{bill_name}',
            '{bill_id}',
            '{bill_url}',
            '{proposal_name}',
            '{proposal_number}',
            '{proposal_url}',
            '{complaint_name}',
            '{complaint_title}',
            '{complaint_against}',
            '{complaint_date}',
            '{complaint_description}',
            '{leave_name}',
            '{leave_status}',
            '{leave_reason}',
            '{leave_start_date}',
            '{leave_end_date}',
            '{total_leave_days}',
            '{employee_name}',
            '{employee_email}',
            '{payslip_name}',
            '{payslip_salary_month}',
            '{payslip_url}',
            '{promotion_designation}',
            '{promotion_title}',
            '{promotion_date}',
            '{resignation_email}',
            '{assign_user}',
            '{resignation_date}',
            '{notice_date}',
            '{termination_name}',
            '{termination_email}',
            '{termination_date}',
            '{termination_type}',
            '{transfer_name}',
            '{transfer_email}',
            '{transfer_date}',
            '{transfer_department}',
            '{transfer_branch}',
            '{transfer_description}',
            '{trip_name}',
            '{purpose_of_visit}',
            '{start_date}',
            '{end_date}',
            '{place_of_visit}',
            '{trip_description}',
            '{vendor_bill_name}',
            '{vendor_bill_id}',
            '{vendor_bill_url}',
            '{employee_warning_name}',
            '{warning_subject}',
            '{warning_description}',
            '{contract_client}',
            '{contract_subject}',
            '{contract_start_date}',
            '{contract_end_date}',
            '{user_name}',
            '{lead_user_name}',
            '{project_name}',
            '{payment_price}',
            '{invoice_payment_type}',
            '{task_name}',
            '{old_stage_name}',
            '{new_stage_name}',
            '{year}',
            '{announcement_title}',
            '{branch_name}',
            '{support_user_name}',
            '{meeting_title}',
            '{meeting_date}',
            '{meeting_time}',
            '{award_date}',
            '{holiday_title}',
            '{holiday_date}',
            '{event_title}',
            '{event_start_date}',
            '{event_end_date}',
            '{company_policy_name}',
            '{budget_period}',
            '{budget_year}',
            '{budget_name}',
            '{revenue_amount}',
            '{vendor_name}',
            '{payment_type}',
            '{bill_due_date}',
            '{bill_date}',




        ];
        $arrValue   = [
            'app_name' => '-',
            'company_name' => '-',
            'app_url' => '-',
            'email' => '-',
            'password' => '-',
            'client_name' => '-',
            'client_email' => '-',
            'client_password' => '-',
            'support_name' => '-',
            'support_title' => '-',
            'support_priority' => '-',
            'support_end_date' => '-',
            'support_description' => '-',
            'lead_name' => '-',
            'lead_email' => '-',
            'lead_subject' => '-',
            'lead_pipeline' => '-',
            'lead_stage' => '-',
            'deal_name' => '-',
            'deal_pipeline' => '-',
            'deal_stage' => '-',
            'deal_status' => '-',
            'deal_price' => '-',
            'award_name' => '-',
            'award_email' => '-',
            'customer_name' => '-',
            'customer_email' => '-',
            'invoice_name' => '-',
            'invoice_number' => '-',
            'invoice_url' => '-',
            'invoice_payment_name' => '-',
            'invoice_payment_amount' => '-',
            'invoice_payment_date' => '-',
            'payment_dueAmount' => '-',
            'payment_reminder_name' => '-',
            'invoice_payment_number' => '-',
            'invoice_payment_dueAmount' => '-',
            'payment_reminder_date' => '-',
            'payment_name' => '-',
            'payment_bill' => '-',
            'payment_amount' => '-',
            'payment_date' => '-',
            'payment_method' => '-',
            'vendor_name' => '-',
            'vendor_email' => '-',
            'bill_name' => '-',
            'bill_id' => '-',
            'bill_url' => '-',
            'proposal_name' => '-',
            'proposal_number' => '-',
            'proposal_url' => '-',
            'complaint_name' => '-',
            'complaint_title' => '-',
            'complaint_against' => '-',
            'complaint_date' => '-',
            'complaint_description' => '-',
            'leave_name' => '-',
            'leave_status' => '-',
            'leave_reason' => '-',
            'leave_start_date' => '-',
            'leave_end_date' => '-',
            'total_leave_days' => '-',
            'employee_name' => '-',
            'employee_email' => '-',
            'payslip_name' => '-',
            'payslip_salary_month' => '-',
            'payslip_url' => '-',
            'promotion_designation' => '-',
            'promotion_title' => '-',
            'promotion_date' => '-',
            'resignation_email' => '-',
            'assign_user' => '-',
            'resignation_date' => '-',
            'notice_date' => '-',
            'termination_name' => '-',
            'termination_email' => '-',
            'termination_date' => '-',
            'termination_type' => '-',
            'transfer_name' => '-',
            'transfer_email' => '-',
            'transfer_date' => '-',
            'transfer_department' => '-',
            'transfer_branch' => '-',
            'transfer_description' => '-',
            'trip_name' => '-',
            'purpose_of_visit' => '-',
            'start_date' => '-',
            'end_date' => '-',
            'place_of_visit' => '-',
            'trip_description' => '-',
            'vendor_bill_name' => '-',
            'vendor_bill_id' => '-',
            'vendor_bill_url' => '-',
            'employee_warning_name' => '-',
            'warning_subject' => '-',
            'warning_description' => '-',
            'contract_client' => '-',
            'contract_subject' => '-',
            'contract_start_date' => '-',
            'contract_end_date' => '-',
            'user_name' => '-',
            'lead_user_name' => '-',
            'project_name' => '-',
            'payment_price' => '-',
            'invoice_payment_type' => '-',
            'task_name' => '-',
            'old_stage_name' => '-',
            'new_stage_name' => '-',
            'year' => '-',
            'announcement_title' => '-',
            'branch_name' => '-',
            'support_user_name' => '-',
            'meeting_title' => '-',
            'meeting_date' => '-',
            'meeting_time' => '-',
            'award_date' => '-',
            'holiday_title' => '-',
            'holiday_date' => '-',
            'event_title' => '-',
            'event_start_date' => '-',
            'event_end_date' => '-',
            'company_policy_name' => '-',
            'budget_period' => '-',
            'budget_year' => '-',
            'budget_name' => '-',
            'revenue_amount' => '-',
            'vendor_bill_payment_name' => '-',
            'payment_type' => '-',
            'bill_due_date' => '-',
            'bill_date' => '-',
        ];

        foreach ($obj as $key => $val)
            $arrValue[$key] = $val;
        $settings = Utility::settings();
        $company_name = $settings['company_name'];
        $arrValue['app_name']    =  !empty($company_name) ? $company_name : env('APP_NAME');
        $arrValue['company_name'] = self::settings()['mail_from_name'];
        $arrValue['app_url']     = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';

        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public const PPL_LD_DL_STG = 'pipelineLeadDealStage';
    public static function pipelineLeadDealStage(string|int $createdId): void
    {
        try {
            DB::transaction(function () use ($createdId) {
                $pipeline = Pipeline::create([
                    PJC::COL_PPL_NM         => 'Sales',
                    DC::COL_TABLE_CREATOR      => $createdId,
                ]);
                $stages = ['Draft', 'Sent', 'Open', 'Revised', 'Declined'];
                foreach ($stages as $order => $stageName) {
                    LeadStage::create([
                        PJC::COL_STG_NM         => $stageName,
                        PJC::COL_PPL_ID         => $pipeline->id,
                        AC::COL_OD           => $order,
                        DC::COL_TABLE_CREATOR      => $createdId,
                    ]);
                    Stage::create([
                        PJC::COL_STG_NM         => $stageName,
                        PJC::COL_PPL_ID         => $pipeline->id,
                        AC::COL_OD           => $order,
                        DC::COL_TABLE_CREATOR      => $createdId,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating " . __FUNCTION__ . " for [{$createdId}]: {$e->getMessage()}");
        }
    }

    public const PRJ_TSK_STGS = 'projectTaskStages';
    public static function projectTaskStages(string $projectId, string $createdBy): void
    {
        $projectStages = ['To Do', 'In Progress', 'Review', 'Done'];
        try {
            DB::transaction(function () use ($projectStages, $projectId, $createdBy) {
                foreach ($projectStages as $order => $stageName) {
                    TaskStage::create([
                        AC::COL_PJ       => $projectId,
                        PJC::COL_STG_NM     => $stageName,
                        AC::COL_OD       => $order,
                        DC::COL_TABLE_CREATOR  => $createdBy,
                    ]);
                }
            });
        } catch (ModelNotFoundException $e) {
            Log::warning(
                __CLASS__ . '::' . __FUNCTION__
                    . " – project not found [{$projectId}]: {$e->getMessage()}",
                ['exception' => $e]
            );
        } catch (QueryException $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__
                    . " – database error creating TaskStages for project [{$projectId}]: {$e->getMessage()}",
                [
                    'sql'       => $e->getSql(),
                    'bindings'  => $e->getBindings(),
                    'exception' => $e,
                ]
            );
        } catch (\RuntimeException $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__
                    . " – runtime error for project [{$projectId}]: {$e->getMessage()}",
                ['exception' => $e]
            );
        } catch (\Throwable $e) {
            Log::critical(
                __CLASS__ . '::' . __FUNCTION__
                    . " – unexpected error for project [{$projectId}]: {$e->getMessage()}",
                ['exception' => $e]
            );
        }
    }

    public const JB_STG = 'jobStage';
    public static function jobStage(string|int $creatorId): void
    {
        $stages = ['Applied', 'Phone Screen', 'Interview', 'Hired', 'Rejected'];
        try {
            DB::transaction(function () use ($stages, $creatorId) {
                foreach ($stages as $order => $title)
                    JobStage::create([
                        AC::COL_TT        => $title,
                        AC::COL_OD        => $order,
                        DC::COL_TABLE_CREATOR   => $creatorId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating JobStage entries for [{$creatorId}]: {$e->getMessage()}");
        }
    }

    public static function labels(string|int $creatorId): void
    {
        $pipeline = null;
        try {
            DB::transaction(function () use ($creatorId, &$pipeline) {
                $pipeline = Pipeline::create([
                    PJC::COL_PPL_NM      => 'Default Pipeline',
                    DC::COL_TABLE_CREATOR  => $creatorId,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating Pipeline: {$e->getMessage()}");
            return;
        }
        $labelData = [
            [PJC::COL_LB_NM => 'On Hold',  PJC::COL_CL => 'primary'],
            [PJC::COL_LB_NM => 'New',      PJC::COL_CL => PJC::STT_INF],
            [PJC::COL_LB_NM => 'Pending',  PJC::COL_CL => PJC::STT_WRN],
            [PJC::COL_LB_NM => 'Loss',     PJC::COL_CL => PJC::STT_DGR],
            [PJC::COL_LB_NM => 'Win',      PJC::COL_CL => 'success'],
        ];
        try {
            DB::transaction(function () use ($labelData, $creatorId, $pipeline) {
                foreach ($labelData as $item)
                    Label::create([
                        PJC::COL_LB_NM      => $item[PJC::COL_LB_NM],
                        PJC::COL_CL         => $item[PJC::COL_CL],
                        PJC::COL_PPL_ID     => $pipeline?->id,
                        DC::COL_TABLE_CREATOR  => $creatorId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating Label entries: {$e->getMessage()}");
        }
        $bugStatusData = ['Confirmed', 'Resolved', 'Unconfirmed', 'In Progress', 'Verified'];
        try {
            DB::transaction(function () use ($bugStatusData, $creatorId) {
                foreach ($bugStatusData as $order => $status)
                    BugStatus::create([
                        AC::COL_TT        => $status,
                        AC::COL_OD        => $order,
                        DC::COL_TABLE_CREATOR   => $creatorId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating BugStatus entries: {$e->getMessage()}");
        }
    }

    public static function sources(string|int $createdId): void
    {
        $sourceNames = ['Websites', 'Facebook', 'Naukari.com', 'Phone', 'LinkedIn'];
        try {
            DB::transaction(function () use ($sourceNames, $createdId) {
                foreach ($sourceNames as $name)
                    Source::create([
                        'name'                             => $name,
                        DC::COL_TABLE_CREATOR   => $createdId,
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating Source entries: {$e->getMessage()}");
        }
    }
    public static function employeeNumber($userId): string|int
    {
        if (is_string($userId)) return (string) Str::uuid();
        $employee = Employee::where(UC::COL_USER_ID, $userId)->latest()->first();
        return $employee?->id ?? (string) Str::uuid();
    }

    public const EMP_DTLS = 'employeeDetails';
    public static function employeeDetails(string|int $userId, string|int $createdBy): void
    {
        $user = User::find($userId);
        if (!$user) return;
        if (Employee::where(UC::COL_USER_ID, $user->id)->exists()) return;
        $faker = Faker::create('pt_BR');
        try {
            DB::transaction(function () use ($user, $createdBy, $faker) {
                $branchBudget   = $faker->randomFloat(2, 100_000, 1_000_000);
                $branchExpenses = $faker->randomFloat(2, 0, $branchBudget);
                $branchProfit   = $branchBudget - $branchExpenses;
                $branch = Branch::create([
                    CPC::COL_BRC_NM       => $faker->company . ' ' . Str::upper(Str::random(4)), // unique
                    'address'             => $faker->streetAddress(),
                    'phone'               => $faker->phoneNumber(),
                    CPC::COL_FND          => (string) $createdBy,
                    CPC::COL_MNG          => $createdBy,
                    CPC::COL_ADM          => $createdBy,
                    'description'         => $faker->sentence(),
                    'departments'         => '[]',
                    'budget'              => $branchBudget,
                    'expenses'            => $branchExpenses,
                    'profit'              => $branchProfit,
                    // audit
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);

                $deptBudget   = $faker->randomFloat(2, 10_000, 200_000);
                $deptExpenses = $faker->randomFloat(2, 0, $deptBudget);
                $deptProfit   = $deptBudget - $deptExpenses;
                $department = Department::create([
                    CPC::COL_DEP_NM       => ucfirst($faker->unique()->word()), // per-branch uniqueness handled by unique([branch_id, name])
                    CPC::COL_BRC_ID       => $branch->id,
                    'description'         => $faker->sentence(),
                    'phone'               => $faker->phoneNumber(),
                    'email'               => $faker->companyEmail(),
                    CPC::COL_MNG          => $createdBy,
                    'budget'              => $deptBudget,
                    'expenses'            => $deptExpenses,
                    'profit'              => $deptProfit,
                    // audit
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);

                $dsgBudget = $faker->randomFloat(2, 5_000, 100_000);
                $validFrom = $faker->dateTimeBetween('-1 year', 'now');
                $validTo   = $faker->dateTimeBetween('now', '+10 years');

                $designation = Designation::create([
                    UC::COL_DSG_NM        => $faker->jobTitle(),
                    CPC::COL_DEP_ID       => $department->id,
                    CPC::COL_EBDG         => $dsgBudget,
                    'description'         => $faker->sentence(),
                    'notes'               => $faker->sentence(),
                    CPC::COL_VFROM        => $validFrom->format('Y-m-d'),
                    CPC::COL_VTO          => $validTo->format('Y-m-d'),
                    // audit
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);

                $taxName = 'Tax ' . $faker->unique()->randomNumber(3);
                $taxRate = $faker->randomFloat(2, 0, 30); // up to 30%

                $tax = Tax::create([
                    BC::COL_TAX_NM       => $taxName,
                    BC::COL_TAX_RT       => $taxRate,
                    // audit
                    DC::COL_TABLE_CREATOR            => $createdBy,
                    DC::COL_TABLE_UPDATER            => $createdBy,
                ]);

                $payslipTypeName = $faker->randomElement(['Monthly', 'Hourly', 'Daily']);

                $code = (string) Str::uuid();
                while (PayslipType::where('code', $code)->exists())
                    $code = (string) Str::uuid();

                $minAmount = $faker->randomFloat(2, 0, 1_000);
                $maxAmount = $minAmount + $faker->randomFloat(2, 0, 10_000);

                $rolesApplies = json_encode(
                    $faker->randomElement([
                        ['all'],
                        ['employee', 'manager'],
                        ['contractor'],
                    ])
                );

                $payslipType = PayslipType::create([
                    'code'                   => $code,
                    BC::COL_PAY_SLP_NM => $payslipTypeName, // maps to 'name'
                    'description'            => $faker->sentence(),
                    BC::COL_MIN_AMT    => $minAmount,
                    BC::COL_MAX_AMT    => $maxAmount,
                    BC::COL_RL_APL     => $rolesApplies,
                    // audit
                    DC::COL_TABLE_CREATOR    => $createdBy,
                    DC::COL_TABLE_UPDATER    => $createdBy,
                ]);

                $phone = $faker->phoneNumber();
                while (Employee::where('phone', $phone)->exists()) {
                    $phone = $faker->phoneNumber();
                }

                $email = $user[UC::COL_EM] ?? $user->email ?? $faker->unique()->safeEmail();
                if (Employee::where('email', $email)->exists()) {
                    $email = $faker->unique()->safeEmail();
                }

                $accountName = $faker->bothify('ACC-####-' . substr((string) $user->id, 0, 4));
                while (Employee::where(UC::COL_ACC_NM, $accountName)->exists()) {
                    $accountName = $faker->bothify('ACC-####-' . substr((string) Str::uuid(), 0, 4));
                }

                $employeeNumber = self::employeeNumber($createdBy);

                $documents = json_encode([
                    'id_card'  => (string) Str::uuid(),
                    'contract' => (string) Str::uuid(),
                ]);

                $dob = $faker->optional()->dateTimeBetween('-60 years', '-18 years');

                Employee::create([
                    UC::COL_EMP_ID        => $employeeNumber,
                    UC::COL_USER_ID       => $user->id,
                    'name'                => $user[UC::COL_NM] ?? $user->name,
                    'phone'               => $phone,
                    'email'               => $email,
                    'gender'              => $faker->randomElement(['male', 'female', 'other']),
                    'notes'               => $faker->sentence(),
                    'password'            => $user[UC::COL_PW] ?? $user->password,
                    'address'             => $faker->address(),
                    'dob'                 => $dob ? $dob->format('Y-m-d') : null,
                    CPC::COL_BRC_ID       => $branch->id,
                    CPC::COL_BRC_LC       => $branch->address,
                    CPC::COL_DEP_ID       => $department->id,
                    UC::COL_DSG_ID        => $designation->id,
                    CPC::COL_DOJ          => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                    'documents'           => $documents,
                    UC::COL_ACC_HD        => $user[UC::COL_NM] ?? $user->name,
                    UC::COL_ACC_NM        => $accountName,
                    UC::COL_BANK_NM       => $faker->company() . ' Bank',
                    UC::COL_BANK_IC       => strtoupper($faker->bothify('BR##-####')),
                    UC::COL_TAX_ID        => $tax->id,
                    'salary'              => $faker->randomFloat(2, 30_000, 100_000),
                    UC::COL_SLR_TP        => $payslipType->id,
                    UC::COL_IA            => 1,
                    // audit
                    DC::COL_TABLE_CREATOR => $createdBy,
                    DC::COL_TABLE_UPDATER => $createdBy,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__
                    . " failed creating Employee for user[{$userId}]: {$e->getMessage()}"
            );
        }
    }


    public static function employeeDetailsUpdate(string|int $userId, string|int $createdBy): void
    {
        $user = User::find($userId);
        if (!$user) return;
        try {
            DB::transaction(function () use ($user) {
                Employee::where(UC::COL_USER_ID, $user?->id)->update([
                    UC::COL_NM  => $user[UC::COL_NM],
                    UC::COL_EM => $user[UC::COL_EM],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating Employee for user[{$userId}]: {$e->getMessage()}");
        }
    }

    public static function errorFormat(\Illuminate\Support\MessageBag $errors): string
    {
        return implode('<br>', $errors->all());
    }

    public static function getDateFormated(?string $date): string
    {
        if ($date && $date !== '0000-00-00')
            return date('d M Y', strtotime($date));
        return '';
    }

    public static function getProgressColor(float|int $percentage): string
    {
        return match (true) {
            $percentage <= 20  => PJC::STT_DGR,
            $percentage <= 40  => PJC::STT_WRN,
            $percentage <= 60  => PJC::STT_INF,
            $percentage <= 80  => 'secondary',
            default            => 'primary',
        };
    }

    public static function getPercentage(float|int $val1 = 0, float|int $val2 = 0): int
    {
        return ($val1 > 0 && $val2 > 0) ? intval(($val1 / $val2) * 100) : 0;
    }

    public static function getCrmPercentage(float|int $val1 = 0, float|int $val2 = 0): string
    {
        if ($val1 > 0 && $val2 > 0) {
            $perc = ($val1 / $val2) * 100;
            return number_format($perc, (int) self::getValByName('decimal_number'));
        }
        return '0';
    }

    public static function timeToHr(array $times): string
    {
        $total = self::calculateTimesheetHours($times);
        [$hr, $min] = explode(':', $total);
        if ((int) $min <= 30) $total = $hr;
        return $total !== '00' ? $total : '0';
    }

    public static function calculateTimesheetHours(array $times): string
    {
        $minutes = array_reduce($times, function ($carry, $time) {
            [$h, $m] = explode(':', $time);
            return $carry + ((int)$h * 60) + (int)$m;
        }, 0);
        $hours = floor($minutes / 60);
        $minutes -= $hours * 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public static function getLastSevenDays(): array
    {
        $result = [];
        $date = strtotime('-1 week +1 day');
        for ($i = 0; $i < 7; $i++) {
            $key = date('Y-m-d', $date);
            $result[$key] = date('D', $date);
            $date = strtotime($key . ' +1 day');
        }
        return $result;
    }

    public static function checkFileExistsAndDelete(array $files): bool
    {
        foreach ($files as $file)
            if (Storage::exists($file) && !Storage::delete($file))
                return false;
        return true;
    }

    public static function projectCurrencyFormat(string|int $projectId, float|int $amount, bool $decimal = false): ?string
    {
        $project = Project::find($projectId);
        if (!$project) {
            $settings = self::settings();
            $symbol = $settings[SC::CR_SB] ?? '';
            $position = $settings[SC::CR_SB_P] ?? 'pre';
            $dec = $decimal ? (int) Utility::getValByName('decimal_number') : (int) Utility::getValByName('decimal_number');
            $formatted = number_format($amount, $dec);
            return ($position === 'pre' ? $symbol : '') . $formatted . ($position === 'post' ? $symbol : '');
        }
        return null;
    }

    public static function getFirstSeventhWeekDay(?int $week = null): array
    {
        $first = $seventh = null;
        if (isset($week)) {
            $first = Carbon::now()->addWeeks($week)->startOfWeek();
            $seventh = Carbon::now()->addWeeks($week)->endOfWeek();
        }
        $dates = [];
        if ($first && $seventh) {
            $period = CarbonPeriod::create($first, $seventh);
            foreach ($period as $date)
                $dates[(string)$date->format('Y-m-d')] = $date;
        }
        return ['first_day' => $first, 'seventh_day' => $seventh, 'datePeriod' => $dates];
    }

    public static function employeePayslipDetail(string|int $employeeId, string $month): array
    {
        $payslips = Payslip::where('employee_id', $employeeId)
            ->where('salary_month', $month)
            ->get();
        $totalAllowance   = 0;
        $totalCommission  = 0;
        $totalOtherPayment = 0;
        $totalOvertime    = 0;
        $totalLoan        = 0;
        $totalDeduction   = 0;

        foreach ($payslips as $p) {
            $basic = $p->gross_salary;
            $allowances = json_decode($p->allowance, true) ?: [];
            foreach ($allowances as $a) {
                $amount = $a['type'] === 'percentage'
                    ? ($a['amount'] * $basic / 100)
                    : $a['amount'];
                $totalAllowance += $amount;
            }
            $commissions = json_decode($p->commission, true) ?: [];
            foreach ($commissions as $c) {
                $amount = $c['type'] === 'percentage'
                    ? ($c['amount'] * $basic / 100)
                    : $c['amount'];
                $totalCommission += $amount;
            }
            $otherPays = json_decode($p->other_payment, true) ?: [];
            foreach ($otherPays as $o) {
                $amount = $o['type'] === 'percentage'
                    ? ($o['amount'] * $basic / 100)
                    : $o['amount'];
                $totalOtherPayment += $amount;
            }
            $overtimes = json_decode($p->overtime, true) ?: [];
            foreach ($overtimes as $o) {
                $totalOvertime += ($o['number_of_days'] * $o['hours'] * $o['rate']);
            }
            $loans = json_decode($p->loan, true) ?: [];
            foreach ($loans as $l) {
                $amount = $l['type'] === 'percentage'
                    ? ($l['amount'] * $basic / 100)
                    : $l['amount'];
                $totalLoan += $amount;
            }
            $deductions = json_decode($p->saturation_deduction, true) ?: [];
            foreach ($deductions as $d) {
                $amount = $d['type'] === 'percentage'
                    ? ($d['amount'] * $basic / 100)
                    : $d['amount'];
                $totalDeduction += $amount;
            }
        }

        return [
            'earning'        => [
                'allowance'   => $payslips,
                'commission'  => $payslips,
                'otherPayment' => $payslips,
                'overTime'    => $payslips,
            ],
            'totalEarning'   => $totalAllowance + $totalCommission + $totalOtherPayment + $totalOvertime,
            'deduction'      => [
                'loan'      => $payslips,
                'deduction' => $payslips,
            ],
            'totalDeduction' => $totalLoan + $totalDeduction,
        ];
    }

    public static function companyData(string|int $companyId, string $key): string
    {
        $row = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, $companyId)
            ->where('name', $key)
            ->first();
        return $row->value ?? '';
    }

    public static function addNewData(): void
    {
        Artisan::call('cache:forget spatie.permission.cache');
        Artisan::call('cache:clear');
        $allPermissions     = self::ARR_PERMISSIONS;
        $companyDataPerms   = self::COMPANY_DATA_PERMISSIONS;
        $companyRole = Role::where('name', 'LIKE', 'company')->first();
        if (!$companyRole) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " company role not found");
            return;
        }
        $existingPermissions = $companyRole->getPermissionNames()->toArray();
        try {
            DB::transaction(function () use ($allPermissions, $companyDataPerms, $companyRole, $existingPermissions) {
                foreach ($allPermissions as $permName)
                    Permission::firstOrCreate(['name' => $permName]);
                foreach ($companyDataPerms as $permName) {
                    if (!in_array($permName, $existingPermissions, true)) {
                        $permission = Permission::findByName($permName);
                        $companyRole->givePermissionTo($permission);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed addNewData transaction: {$e->getMessage()}");
        }
    }

    public static function getAdminPaymentSetting(): array
    {
        try {
            $query   = DB::table('admin_payment_settings');
            $user = Auth::user();
            if (Auth::check())
                $query->where(DC::COL_TABLE_CREATOR, $user?->{UC::COL_TP} === PMC::SA ? $user->id : DC::DEFAULT_UUID);
            $rows    = $query->get();
            $settings = [];
            foreach ($rows as $row)
                $settings[$row->name] = $row->value;
            return $settings;
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed fetching admin payment settings: {$e->getMessage()}");
            return [];
        }
    }

    public static function getCompanyPaymentSetting(string|int $userId): array
    {
        $rows    = DB::table('company_payment_settings')
            ->where(DC::COL_TABLE_CREATOR, $userId)
            ->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }

    public static function getCompanyPayment(): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $query   = DB::table('company_payment_settings');
        if (Auth::check())
            $query->where(DC::COL_TABLE_CREATOR, $user?->creatorId());
        $rows    = $query->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }

    public static function errorRes(string $msg = "", array $args = []): array
    {
        $id  = 'error.' . ($msg === '' ? 'error' : $msg);
        $text = Lang::get($id, $args);
        $msg = $text === $id ? ($msg === '' ? 'error' : $msg) : $text;
        return ['flag' => 0, 'msg' => $msg];
    }

    public static function successRes(string $msg = "", array $args = []): array
    {
        $id  = 'success.' . ($msg === '' ? 'success' : $msg);
        $text = Lang::get($id, $args);
        $msg = $text === $id ? ($msg === '' ? 'success' : $msg) : $text;
        return ['flag' => 1, 'msg' => $msg];
    }

    public static function getMessengerPackagesMigration(): int
    {
        $path = base_path() . '/vendor/munafio/chatify/database/migrations' . DIRECTORY_SEPARATOR . '*.php';
        $files = glob($path);
        return $files ? count($files) : 0;
    }

    public static function getSelectedThemeColor(): string
    {
        $color = env('THEME_COLOR');
        return $color === '' || $color === null ? 'blue' : $color;
    }

    public static function getAllThemeColors(): array
    {
        $colors = [
            'blue',
            'denim',
            'sapphire',
            'olympic',
            'violet',
            'black',
            'cyan',
            'dark-blue-natural',
            'gray-dark',
            'light-blue',
            'light-purple',
            'magenta',
            'orange-mute',
            'pale-green',
            'rich-magenta',
            'rich-red',
            'sky-gray',
        ];

        return $colors;
    }

    public static function differenceToTime(string $start, string $end): int
    {
        $s = new Carbon($start);
        $e = new Carbon($end);
        return $s->diffInSeconds($e);
    }

    public static function secondToTime(int $seconds = 0): string
    {
        $H = floor($seconds / 3600);
        $M = floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d:%02d', $H, $M, $seconds % 60);
    }

    public static function sendSlackMsg(string $slug, array $obj, string|int|null $userId = null): void
    {
        $template = NotificationTemplate::where('slug', $slug)->first();
        if (!$template || empty($obj)) return;
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return;
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(DC::COL_TABLE_CREATOR, $user?->id)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'en')
            ->first();
        if (!$notiLang || empty($notiLang->content)) return;
        $msg = self::replaceVariable($notiLang->content, $obj);
        $settings = self::settingsById($user?->id);
        $webhook = $settings['slack_webhook'] ?? '';
        if (!$webhook) return;
        try {
            Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($webhook, ['text' => $msg]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " Slack send failed: {$e->getMessage()}");
        }
    }

    public static function sendTelegramMsg(string $slug, array $obj, string|int|null $userId = null): void
    {
        $template = NotificationTemplate::where('slug', $slug)->first();
        if (!$template || empty($obj)) {
            return;
        }
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) {
            return;
        }
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(DC::COL_TABLE_CREATOR, $user?->id)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'en')
            ->first();
        if (!$notiLang || empty($notiLang->content))
            return;
        $msg = self::replaceVariable($notiLang->content, $obj);
        $settings = self::settingsById($user?->id);
        $bot  = $settings['telegram_accesstoken'] ?? '';
        $chat = $settings['telegram_chatid'] ?? '';
        if (!$bot || !$chat)
            return;
        try {
            $url = "https://api.telegram.org/bot{$bot}/sendMessage";
            Http::asForm()->post($url, [
                'chat_id' => $chat,
                'text'    => $msg,
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " Telegram send failed: {$e->getMessage()}");
        }
    }

    public static function sendTwilioMsg(string $to, string $slug, array $obj, string|int|null $userId = null): void
    {
        $template = NotificationTemplate::where('slug', $slug)->first();
        if (!$template || empty($obj)) return;
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return;
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(DC::COL_TABLE_CREATOR, $user?->id)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'en')
            ->first();
        if (!$notiLang || empty($notiLang->content)) return;
        $msg = self::replaceVariable($notiLang->content, $obj);
        $settings  = self::settingsById($user?->id);
        $sid       = $settings['twilio_sid'] ?? '';
        $token     = $settings['twilio_token'] ?? '';
        $fromNumber = $settings['twilio_from'] ?? '';
        if (!$sid || !$token || !$fromNumber) return;
        try {
            $client = new TwilioClient($sid, $token);
            $client->messages->create($to, [
                'from' => $fromNumber,
                'body' => $msg,
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " Twilio send failed: {$e->getMessage()}");
        }
    }

    public static function totalQuantity(string $type, int $quantity, string|int $productId): void
    {
        $product = ProductService::find($productId);
        if (!$product || $product->type !== 'product') return;
        DB::transaction(function () use ($product, $type, $quantity) {
            $newQty = $type === 'minus'
                ? max(0, $product->quantity - $quantity)
                : $product->quantity + $quantity;
            $product->quantity = $newQty;
            $product->save();
        });
    }

    public static function warehouseQuantity(string $type, int $quantity, string|int $productId, string|int $warehouseId): void
    {
        $record = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
        if (!$record) return;
        DB::transaction(function () use ($record, $type, $quantity) {
            $currentQty = $record->quantity ?? 0;
            $newQty = $type === 'minus'
                ? max(0, $currentQty - $quantity)
                : $currentQty + $quantity;
            $record->quantity = $newQty;
            $record->save();
        });
    }

    public static function warehouseTransferQty(string|int $fromWarehouse, string|int $toWarehouse, string|int $productId, int $quantity, ?string $delete = null): void
    {
        DB::transaction(function () use ($fromWarehouse, $toWarehouse, $productId, $quantity, $delete) {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            $toRecord = WarehouseProduct::firstOrNew([
                'warehouse_id' => $toWarehouse,
                'product_id'   => $productId,
            ]);
            if (!$toRecord->exists && $delete !== 'delete') {
                $toRecord->quantity  = $quantity;
                $toRecord[DC::COL_TABLE_CREATOR] = $user?->creatorId();
                $toRecord->save();
            } elseif ($toRecord->exists) {
                $toRecord->quantity += $quantity;
                $toRecord->save();
            }
            $fromRecord = WarehouseProduct::where('warehouse_id', $fromWarehouse)
                ->where('product_id', $productId)
                ->first();
            if ($fromRecord) {
                $newQty = $fromRecord->quantity - $quantity;
                if ($newQty <= 0)
                    $fromRecord->delete();
                else {
                    $fromRecord->quantity = $newQty;
                    $fromRecord->save();
                }
            }
        });
    }

    public static function addProductStock(string|int $productId, int $quantity, string $type, string $description, string|int $typeId): void
    {
        DB::transaction(function () use ($productId, $quantity, $type, $description, $typeId) {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            StockReport::create([
                'product_id' => $productId,
                'quantity'   => $quantity,
                'type'       => $type,
                'type_id'    => $typeId,
                'description' => $description,
                'title'      => ucfirst($type) . ' Stock',
                DC::COL_TABLE_CREATOR => $user?->creatorId(),
            ]);
        });
    }

    public static function g(): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $query = DB::table(DC::TABLE_SETTINGS);
        if (Auth::check()) {
            $query = $query->where(UC::COL_USER_ID, $user?->creatorId());
            $rows = $query->get();
            if ($rows->isEmpty())
                $rows = DB::table(DC::TABLE_SETTINGS)->where(UC::COL_USER_ID, DC::DEFAULT_UUID)->get();
        } else
            $rows = $query->where(UC::COL_USER_ID, DC::DEFAULT_UUID)->get();
        $defaults = [
            SC::CST_DRK => 'off',
            SC::CST_BG   => 'on',
            SC::CLR           => '',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    public static function colorset(): array
    {
        $default = [
            SC::CST_DRK => 'off',
        ];
        $userOrRedirect = self::_checkLogin(haltRedirect: true);
        if (!$userOrRedirect instanceof User)
            return $default;
        $user  = $userOrRedirect;
        $role  = Auth::user()[UC::COL_TP];
        $userId = $user?->id;
        $creator = $user?->creatorId();
        $qb = DB::table(DC::TABLE_SETTINGS)
            ->select('name', 'value');
        if (in_array($role, [
            PMC::SA,
            PMC::ADM,
            PMC::CPN,
        ], true))
            $rows = $qb
                ->where('user_id', $userId)
                ->orWhere(DC::COL_TABLE_CREATOR, $creator)
                ->get();
        else
            $rows = $qb
                ->where('user_id', $userId)
                ->get();
        $fetched = $rows->pluck('value', 'name')->toArray();
        $colorSettings = $default + $fetched;
        if (empty($colorSettings[SC::CST_DRK]))
            $colorSettings[SC::CST_DRK] = 'off';
        return $colorSettings;
    }

    public static function getSeoSetting(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)
            ->whereIn('name', [
                SC::MT_TTL_K,
                SC::MT_DSC_K,
                SC::MT_IMG_K
            ])
            ->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }

    public static function getSuperadminLogo(): string
    {
        $settings = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, Auth::user()->id)
            ->pluck('value', 'name')
            ->toArray();
        $mode = $settings[SC::CST_DRK] ?? 'off';
        if ($mode === 'on')
            return SC::CPN_LG_LT_DEF;
        return SC::CPN_LG_DK_DEF;
    }

    public static function getLogo(string $settingKey = '', string $fallbackKey = '', int|string|null $creatorId = null): string
    {
        $isDark = (self::getValByName(SC::CST_DRK) === 'on');
        if (Auth::user() && Auth::user()[UC::COL_TP] !== PMC::SA) {
            return $isDark
                ? self::getValByName(SC::CPN_LG_LT)
                : self::getValByName(SC::CPN_LG_DK);
        }
        return $isDark
            ? self::getValByName('light_logo')
            : self::getValByName('dark_logo');
    }

    public static function getGdpr(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, DC::DEFAULT_UUID)
            ->get();
        $defaults = [
            'gdpr_cookie' => '',
            'cookie_text' => '',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    public static function getValByName1(string $key): string
    {
        return self::getGdpr()[$key] ?? '';
    }

    public static function addWarehouseStock(string|int $productId, int $quantity, string|int $warehouseId): void
    {
        try {
            DB::transaction(function () use ($productId, $quantity, $warehouseId) {
                $record = WarehouseProduct::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                $newQty = $quantity;
                if ($record)
                    $newQty = $record->quantity + $quantity;
                WarehouseProduct::updateOrCreate(
                    [
                        'warehouse_id' => $warehouseId,
                        'product_id' => $productId,
                    ],
                    ['quantity' => $newQty]
                );
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating warehouse stock: {$e->getMessage()}");
        }
    }

    public static function startingNumber(int $id, string $type): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creator = $user?->creatorId();
        $mapping = [
            'invoice'  => 'invoice_starting_number',
            'proposal' => 'proposal_starting_number',
            'bill'     => 'bill_starting_number',
        ];
        if (!isset($mapping[$type])) return 0;
        return DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, $creator)
            ->where('name', $mapping[$type])
            ->update(['value' => $id]);
    }

    public static function uploadFile($request, string $keyName, string $name, string $path, array $customValidation = []): array
    {
        try {
            $settings = Utility::getStorageSetting();
            if (empty($settings[SC::STR_STT]))
                return ['flag' => 0, 'msg' => __('Please set proper configuration for storage.')];
            $settingType = $settings[SC::STR_STT] ?? SC::LC;
            $diskConfig = [];
            if ($settingType === SC::WSB) {
                $diskConfig = [
                    self::FST_DSK_WSB_K  => $settings[SC::WSB_K]    ?? '',
                    self::FST_DSK_WSB_SC => $settings[SC::WSB_SC]   ?? '',
                    self::FST_DSK_WSB_RG => $settings[SC::WSB_RG]   ?? '',
                    self::FST_DSK_WSB_BK => $settings[SC::WSB_BK]   ?? '',
                    self::FST_DSK_WSB_EP => 'https://s3.' . ($settings[SC::WSB_RG] ?? '') . '.wasabisys.com',
                ];
                $maxSize = $settings[SC::WSB_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::WSB_STG_VL] ?? '';
            } elseif ($settingType === SC::S3) {
                $diskConfig = [
                    self::FST_DSK_S3_K   => $settings[SC::S3_K]    ?? '',
                    self::FST_DSK_S3_SC  => $settings[SC::S3_SC]   ?? '',
                    self::FST_DSK_S3_RG  => $settings[SC::S3_RG]   ?? '',
                    self::FST_DSK_S3_BK  => $settings[SC::S3_BK]   ?? '',
                    self::FST_DSK_S3_EP  => false,
                ];
                $maxSize = $settings[SC::S3_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::S3_STG_VL] ?? '';
            } else {
                $maxSize = $settings[SC::LC_ST_M_UP] ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::LC_ST_VL]   ?? '';
            }
            if (!empty($diskConfig))
                config($diskConfig);
            if (!$request->hasFile($keyName))
                return ['flag' => 0, 'msg' => __('No file provided.')];
            $file = $request->file($keyName);
            $rules = count($customValidation) > 0
                ? $customValidation
                : ['mimes:' . $mimes, 'max:' . $maxSize];
            $validator = Validator::make($request->all(), [$keyName => $rules]);
            if ($validator->fails())
                return ['flag' => 0, 'msg' => $validator->messages()->first()];
            if ($settingType === 'local') {
                $file->move(storage_path($path), $name);
                $resultPath = $path . $name;
            } else {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk      = Storage::disk($settingType);
                $resultPath = $disk->putFileAs($path, $file, $name);
            }
            return ['flag' => 1, 'msg' => 'success', 'url' => $resultPath];
        } catch (\Throwable $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function uploadCustomFile($request, string $keyName, string $name, string $path, string $dataKey, array $customValidation = []): array
    {
        try {
            $settings = Utility::getStorageSetting();
            if (empty($settings[SC::STR_STT]))
                return ['flag' => 0, 'msg' => __('Please set proper configuration for storage.')];
            $settingType = $settings[SC::STR_STT] ?? SC::LC;
            $diskConfig = [];
            if ($settingType === SC::WSB) {
                $diskConfig = [
                    self::FST_DSK_WSB_K  => $settings[SC::WSB_K]    ?? '',
                    self::FST_DSK_WSB_SC => $settings[SC::WSB_SC]   ?? '',
                    self::FST_DSK_WSB_RG => $settings[SC::WSB_RG]   ?? '',
                    self::FST_DSK_WSB_BK => $settings[SC::WSB_BK]   ?? '',
                    self::FST_DSK_WSB_EP => 'https://s3.' . ($settings[SC::WSB_RG] ?? '') . '.wasabisys.com',
                ];
                $maxSize = $settings[SC::WSB_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::WSB_STG_VL] ?? '';
            } elseif ($settingType === 's3') {
                $diskConfig = [
                    self::FST_DSK_S3_K   => $settings[SC::S3_K]    ?? '',
                    self::FST_DSK_S3_SC  => $settings[SC::S3_SC]   ?? '',
                    self::FST_DSK_S3_RG  => $settings[SC::S3_RG]   ?? '',
                    self::FST_DSK_S3_BK  => $settings[SC::S3_BK]   ?? '',
                    self::FST_DSK_S3_EP => false,
                ];
                $maxSize = $settings[SC::S3_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::S3_STG_VL] ?? '';
            } else {
                $maxSize = $settings[SC::LC_ST_M_UP] ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::LC_ST_VL]   ?? '';
            }
            if (!empty($diskConfig))
                config($diskConfig);
            if (!$request->hasFile($keyName) || !isset($request->file($keyName)[$dataKey]))
                return ['flag' => 0, 'msg' => __('No file provided.')];
            $file = $request->file($keyName)[$dataKey];
            $rules = count($customValidation) > 0
                ? $customValidation
                : ['mimes:' . $mimes, 'max:' . $maxSize];
            $validator = Validator::make($request->all(), [$dataKey => $rules]);
            if ($validator->fails())
                return ['flag' => 0, 'msg' => $validator->messages()->first()];
            if ($settingType === 'local') {
                $file->move(storage_path($path), $name);
                $resultPath = $path . $name;
            } else {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk      = Storage::disk($settingType);
                $resultPath = $disk->putFileAs($path, $file, $name);
            }
            return ['flag' => 1, 'msg' => 'success', 'url' => $resultPath];
        } catch (\Throwable $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function getFile(string $path = 'uploads/logo', mixed $settings = null): string
    {
        $output = SafeConsoleOutput::make();
        $class  = class_basename(self::class);
        $method = __FUNCTION__;
        $tag    = "{$class}::{$method}";
        Log::debug("{$tag} called", ['path' => $path]);
        $output->writeln("## [{$tag}] Retrieving file URL for path: {$path}");
        try {
            if (!$settings) {
                Log::debug("{$tag} no settings provided, loading via settings()");
                $output->writeln("## [{$tag}] Loading settings…");
                $settings = self::settings();
                Log::debug("{$tag} settings loaded", ['keys' => array_keys($settings)]);
            }
            $storageType = $settings[SC::STR_STT] ?? SC::LC;
            Log::debug("{$tag} storage type determined", ['storageType' => $storageType]);
            $output->writeln("## [{$tag}] Using disk: {$storageType}");
            if ($storageType === SC::WSB) {
                Log::debug("{$tag} configuring Wasabi disk", [
                    'region' => $settings[SC::WSB_RG] ?? null,
                    'bucket' => $settings[SC::WSB_BK] ?? null,
                ]);
                config([
                    self::FST_DSK_WSB_K  => $settings[SC::WSB_K]  ?? '',
                    self::FST_DSK_WSB_SC => $settings[SC::WSB_SC] ?? '',
                    self::FST_DSK_WSB_RG => $settings[SC::WSB_RG] ?? '',
                    self::FST_DSK_WSB_BK => $settings[SC::WSB_BK] ?? '',
                    self::FST_DSK_WSB_EP => 'https://s3.' . ($settings[SC::WSB_RG] ?? '') . '.wasabisys.com',
                ]);
            } elseif ($storageType === SC::S3) {
                Log::debug("{$tag} configuring S3 disk", [
                    'region' => $settings[SC::S3_RG] ?? null,
                    'bucket' => $settings[SC::S3_BK] ?? null,
                ]);
                config([
                    self::FST_DSK_S3_K  => $settings[SC::S3_K]  ?? '',
                    self::FST_DSK_S3_SC => $settings[SC::S3_SC] ?? '',
                    self::FST_DSK_S3_RG => $settings[SC::S3_RG] ?? '',
                    self::FST_DSK_S3_BK => $settings[SC::S3_BK] ?? '',
                    self::FST_DSK_S3_EP => false,
                ]);
            }
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk($storageType);
            Log::debug("{$tag} obtained disk", [
                'disk'    => $storageType,
                'adapter' => get_class($disk),
            ]);
            $output->writeln("## [{$tag}] Using disk adapter " . get_class($disk));
            $url = $disk->url($path);
            Log::debug("{$tag} URL generated", ['url' => $url]);
            $output->writeln("## [{$tag}] URL: {$url}");
            return $url;
        } catch (QueryException $qe) {
            Log::error("{$tag} QueryException", [
                'message' => $qe->getMessage(),
                'file'    => $qe->getFile(),
                'line'    => $qe->getLine(),
            ]);
            $output->writeln("## [{$tag}] DB error: " . $qe->getMessage());
            return '';
        } catch (\Throwable $e) {
            Log::error("{$tag} unexpected error", [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            $output->writeln("## [{$tag}] Exception: " . $e->getMessage());
            return '';
        }
    }

    public static function getStorageSetting(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)->where(UC::COL_USER_ID, DC::DEFAULT_UUID)->get();
        $defaults = [
            SC::STR_STT          => SC::LC,
            SC::LC_ST_VL         => SC::FMTS_UP_DEF,
            SC::LC_ST_M_UP       => SC::MAX_U_SIZE_DEF,
            SC::S3_K             => '',
            SC::S3_SC            => '',
            SC::S3_RG            => '',
            SC::S3_BK            => '',
            SC::S3_URL           => '',
            SC::S3_EP            => '',
            SC::S3_M_UP          => '',
            SC::S3_STG_VL        => '',
            SC::WSB_K            => '',
            SC::WSB_SC           => '',
            SC::WSB_RG           => '',
            SC::WSB_BK           => '',
            SC::WSB_URL          => '',
            SC::WSB_RT           => '',
            SC::WSB_M_UP         => '',
            SC::WSB_STG_VL       => '',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    public static function getTargetRating(int $designationId, int $competencyCount): float
    {
        $indicator = Indicator::where('designation', $designationId)->first();
        if ($indicator && !empty($indicator->rating) && $competencyCount > 0) {
            $ratingArray = json_decode($indicator->rating, true) ?: [];
            $starSum = array_sum($ratingArray);
            return $starSum / $competencyCount;
        }
        return 0.0;
    }

    public static function colorCodeData(string $type): int
    {
        return match ($type) {
            'event'             => 1,
            'zoom_meeting'      => 2,
            'task', 'rotas'     => 3,
            'appointment'       => 11,
            'holiday'           => 4,
            'call'              => 10,
            'meeting'           => 5,
            'leave'             => 6,
            'work_order', 'lead' => 7,
            'deal'              => 8,
            'interview_schedule' => 9,
            default             => 11,
        };
    }

    /** @see CalendarService::configure() */
    public static function googleCalendarConfig(): void
    {
        CalendarService::configure();
    }

    /** @see CalendarService::addEvent() */
    public static function addCalendarData(object $request, string $type): void
    {
        CalendarService::addEvent($request, $type);
    }

    /** @see CalendarService::getEvents() */
    public static function getCalendarData(string $type): array
    {
        return CalendarService::getEvents($type);
    }

    public static function getStartEndMonthDates(): array
    {
        $month_start = Carbon::now()->startOfMonth();
        return [
            'start_date' => $month_start->toDateString(),
            'end_date' => $month_start->addMonth()->toDateString()
        ];
    }

    public static function webhookSetting(string $module, string|int|null $userId = null): array|bool
    {
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return false;
        $webhook = WebhookSettings::where('module', $module)
            ->where('created_by', $user?->id)
            ->first();
        if (!$webhook) return false;
        $reference = sprintf('https://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
        return [
            'method'        => $webhook->method,
            'reference_url' => $reference,
            'url'           => $webhook->url,
        ];
    }

    public static function webhookCall(?string $url, $parameter = null, string $method = 'POST'): bool
    {
        if (empty($url) || empty($parameter)) return false;
        try {
            $response = Http::withOptions(['verify' => false])
                ->send(strtoupper($method), $url, ['form_params' => $parameter]);
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getCookieSetting(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)
            ->whereIn('name', [
                'enable_cookie',
                'cookie_logging',
                'cookie_title',
                'cookie_description',
                'necessary_cookies',
                'strictly_cookie_title',
                'strictly_cookie_description',
                'more_information_description',
                'contactus_url'
            ])->get();
        $defaults = [
            'enable_cookie'              => 'off',
            'necessary_cookies'          => 'on',
            'cookie_logging'             => 'on',
            'cookie_title'               => '',
            'cookie_description'         => '',
            'strictly_cookie_title'      => '',
            'strictly_cookie_description' => '',
            'more_information_description' => '',
            'contactus_url'              => '#',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    public static function getDeviceType(string $userAgent): string
    {
        $mobilePattern = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot).+?mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tabletPattern = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?!.+?mobile))/i';
        if (preg_match($mobilePattern, $userAgent))
            return 'mobile';
        if (preg_match($tabletPattern, $userAgent))
            return 'tablet';
        return 'desktop';
    }

    public static function generateBrazilianPhone($mobile = true, $formatted = true): string
    {
        $areaCodes = BrazilState::DDD;
        $areaCode = $areaCodes[array_rand($areaCodes)];
        if ($mobile) {
            // Mobile numbers start with 9 and have 9 digits total
            $firstDigit = 9;
            $secondDigit = rand(6, 9); // Usually 9, but can be 6-9
            $remaining = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $number = $firstDigit . $secondDigit . $remaining;

            if ($formatted) {
                return sprintf(
                    '+55 (%02d) %d%d%d%d%d-%d%d%d%d',
                    $areaCode,
                    $firstDigit,
                    $secondDigit,
                    (int)$remaining[0],
                    (int)$remaining[1],
                    (int)$remaining[2],
                    (int)$remaining[3],
                    (int)$remaining[4],
                    (int)$remaining[5],
                    (int)$remaining[6]
                );
            } else {
                return '55' . $areaCode . $number;
            }
        } else {
            // Landline numbers have 8 digits and start with 2-5
            $firstDigit = rand(2, 5);
            $remaining = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $number = $firstDigit . $remaining;

            if ($formatted) {
                return sprintf(
                    '+55 (%02d) %d%d%d%d-%d%d%d%d',
                    $areaCode,
                    $firstDigit,
                    (int)$remaining[0],
                    (int)$remaining[1],
                    (int)$remaining[2],
                    (int)$remaining[3],
                    (int)$remaining[4],
                    (int)$remaining[5],
                    (int)$remaining[6]
                );
            } else {
                return '55' . $areaCode . $number;
            }
        }
        return '+55 00000-0000';
    }

    public static function generateRandomCpf(bool $formatted = true): string
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++)
            $cpf .= random_int(0, 9);
        $sum = 0;
        $weight = 10;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * $weight;
            $weight--;
        }
        $remainder = $sum % 11;
        $cpf .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        $sum = 0;
        $weight = 11;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * $weight;
            $weight--;
        }
        $remainder = $sum % 11;
        $cpf .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        if ($formatted)
            return sprintf(
                '%s.%s.%s-%s',
                substr($cpf, 0, 3),
                substr($cpf, 3, 3),
                substr($cpf, 6, 3),
                substr($cpf, 9, 2)
            );
        return $cpf;
    }

    public static function generateRandomCnpj(bool $formatted = true): string
    {
        $cnpj = '';
        for ($i = 0; $i < 8; $i++)
            $cnpj .= random_int(0, 9);
        $cnpj .= '0001';
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++)
            $sum += (int) $cnpj[$i] * $weights1[$i];
        $remainder = $sum % 11;
        $cnpj .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++)
            $sum += (int) $cnpj[$i] * $weights2[$i];
        $remainder = $sum % 11;
        $cnpj .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        if ($formatted)
            return sprintf(
                '%s.%s.%s/%s-%s',
                substr($cnpj, 0, 2),
                substr($cnpj, 2, 3),
                substr($cnpj, 5, 3),
                substr($cnpj, 8, 4),
                substr($cnpj, 12, 2)
            );
        return $cnpj;
    }

    public static function isValidCpf(?string $cpf): bool
    {
        if (!$cpf) return false;
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (!preg_match('/^[0-9]{11}$/', $cpf))
            return false;
        if (preg_match('/^(\d)\1{10}$/', $cpf))
            return false;
        $sum = 0;
        for ($i = 0; $i < 9; $i++)
            $sum += (int) $cpf[$i] * (10 - $i);
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit1 !== (int) $cpf[9])
            return false;
        $sum = 0;
        for ($i = 0; $i < 10; $i++)
            $sum += (int) $cpf[$i] * (11 - $i);
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit2 !== (int) $cpf[10])
            return false;
        return true;
    }

    public static function isValidCnpj(?string $cnpj): bool
    {
        if (!$cnpj) return false;
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (!preg_match('/^[0-9]{14}$/', $cnpj))
            return false;
        if (preg_match('/^(\d)\1{13}$/', $cnpj))
            return false;
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++)
            $sum += (int) $cnpj[$i] * $weights1[$i];
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit1 !== (int) $cnpj[12])
            return false;
        $sum = 0;
        for ($i = 0; $i < 13; $i++)
            $sum += (int) $cnpj[$i] * $weights2[$i];
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit2 !== (int) $cnpj[13])
            return false;
        return true;
    }


    public static function updateStorageLimit(string|int $companyId, float $imageSize): string|int
    {
        try {
            return DB::transaction(function () use ($companyId, $imageSize) {
                $user = User::find($companyId);
                if (!$user) return __('User not found.');
                $plan = Plan::find($user?->plan);
                if (!$plan) return __('Plan not found.');
                $newTotal = $user?->storage_limit + ($imageSize / 1048576);
                if ($plan->storage_limit != -1 && $newTotal > $plan->storage_limit)
                    return __('Plan storage limit is over so please upgrade the plan.');
                $user->storage_limit = $newTotal;
                $user?->save();
                return 1;
            });
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public static function changeStorageLimit(string|int $companyId, string $filePath): bool
    {
        try {
            return DB::transaction(function () use ($companyId, $filePath) {
                $files = File::glob(storage_path($filePath));
                $totalSize = array_reduce($files, function ($carry, $file) {
                    return $carry + (File::exists($file) ? File::size($file) : 0);
                }, 0);
                $user = User::find($companyId);
                if (!$user) return false;
                $plan = Plan::find($user?->plan);
                if (!$plan) return false;
                $user->storage_limit - ($totalSize / 1048576);
                $user?->save();
                foreach ($files as $file)
                    if (File::exists($file)) File::delete($file);
                return true;
            });
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function flagOfCountry(): array
    {
        return [
            'ar'    => '🇦🇪 ar',
            'zh'    => '🇨🇳 zh',
            'da'    => '🇩🇰 da',
            'de'    => '🇩🇪 de',
            'es'    => '🇪🇸 es',
            'fr'    => '🇫🇷 fr',
            'he'    => '🇮🇱 he',
            'it'    => '🇮🇹 it',
            'ja'    => '🇯🇵 ja',
            'nl'    => '🇳🇱 nl',
            'pl'    => '🇵🇱 pl',
            'ru'    => '🇷🇺 ru',
            'pt'    => '🇵🇹 pt',
            'en'    => '🇮🇳 en',
            'tr'    => '🇹🇷 tr',
            'pt-br' => '🇵🇹 pt-br',
        ];
    }

    public static function langList(): array
    {
        return [
            'ar'    => 'Arabic',
            'zh'    => 'Chinese',
            'da'    => 'Danish',
            'de'    => 'German',
            'en'    => 'English',
            'es'    => 'Spanish',
            'fr'    => 'French',
            'he'    => 'Hebrew',
            'it'    => 'Italian',
            'ja'    => 'Japanese',
            'nl'    => 'Dutch',
            'pl'    => 'Polish',
            'pt'    => 'Portuguese',
            'ru'    => 'Russian',
            'tr'    => 'Turkish',
            'pt-br' => 'Portuguese (Brazil)',
        ];
    }

    public static function fetchUserLang(?User $user = null, ?Request $req = null): ?string
    {
        $lang = DC::DEFAULT_LANG;
        try {
            $locale = $req?->cookie('LANGUAGE');
            $id = $user?->id ?? $req?->user()?->id ?? null;
            if ($id && (!$locale || !array_key_exists($locale, Utility::langList())))
                $locale = Cache::get("user_{$id}_lang");
            if (!$locale || !array_key_exists($locale, Utility::langList())) {
                $user ??= $req?->user() ?? null;
                $locale = $user?->{UC::COL_LG} ?? $locale;
            }
            if (!$locale || !array_key_exists($locale, Utility::langList()))
                config('app.locale');
            if (!$locale || !array_key_exists($locale, Utility::langList()))
                $locale = DC::DEFAULT_LANG;
            $lang = $locale;
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to retrieve user language', [
                'exception'    => get_class($e),
                'message'      => $e->getMessage(),
                'default_lang' => $lang,
            ]);
            $lang = DC::DEFAULT_LANG;
        }
        if (!array_key_exists($lang, Utility::langList()))
            $lang = DC::DEFAULT_LANG;
        return $lang;
    }

    public static function fetchLinkMessage(string $lang = DC::DEFAULT_LANG, ?string $set = 'generics', string $key = '', bool $isFailure = true, bool $shouldFallback = true): ?string
    {
        $startMsg = 'Undefined server message. This could mean either a failure or a success. Check with your support team about your request.';
        $resultMsg = $startMsg;
        try {
            $msgs = LC::LINK_MESSAGES;
            $canDefault = $isFailure && is_array(LC::DEFAULT_CLIENT_MESSAGES) && !empty(LC::DEFAULT_CLIENT_MESSAGES['link_not_found']);
            if (!is_array($msgs)) {
                if ($isFailure && $canDefault)
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Link messages are not defined properly.');
            }
            if (!array_key_exists($lang, Utility::langList()))
                $lang = self::fetchUserLang();
            if (!array_key_exists($set, $msgs)) {
                if ($isFailure && $canDefault)
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Set not found in link messages.');
            }
            if (!array_key_exists($lang, $msgs)) {
                if ($isFailure && $canDefault)
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Language not found in link messages.');
            }
            $langMsg = $msgs[$lang] ?? [];
            if (!array_key_exists($key, $langMsg)) {
                if ($isFailure && is_array(LC::DEFAULT_CLIENT_MESSAGES) && !empty(LC::DEFAULT_CLIENT_MESSAGES['link_not_found']))
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Key not found in link messages for the specified language.');
            }
            $resultMsg = $langMsg[$key] ?? ($shouldFallback ? null : $startMsg);
            return $resultMsg;
        } catch (\Throwable) {
            if ($shouldFallback) return null;
            return !$isFailure && !isset($resultMsg) ? $startMsg : 'Something went wrong! Try again later.'; // @phpstan-ignore isset.variable
        }
    }

    public static function displayErrorMessage($msg = null, $lang = DC::DEFAULT_LANG): string
    {
        if (!$msg)
            $msg = Utility::fetchLinkMessage($lang, 'generics', 'route_unavailable') ?? 'Request unavailable';
        $escapedMsg = addslashes(__($msg));
        $uuid = Str::uuid();
        $snippet = <<<HTML
        <script id="{$uuid}-route-alert">
            (function() {
                const message = '{$escapedMsg}';
                if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Toast) {
                    const toast = document.getElementById('loginToast') || document.querySelector('.toast');
                    if (toast) {
                        const body = toast.querySelector('.toast-body');
                        if (body) {
                            const delay = 5000;
                            const bs = new window.bootstrap.Toast(toast, { delay });
                            body.textContent = message;
                            toast.style.display = 'block';
                            bs.show();
                            const handleHidden = function() {
                                body.textContent = '';
                                toast.style.display = 'none';
                                toast.removeEventListener('hidden.bs.toast', handleHidden);
                            };
                            toast.addEventListener('hidden.bs.toast', handleHidden);
                            setTimeout(function() {
                                document.getElementById('{$uuid}')?.remove();
                            }, delay * 1.25);
                            return;
                        }
                    }
                }
                if (typeof window.LaravelToast !== 'undefined' || typeof window.toastr !== 'undefined') {
                    if (window.toastr) {
                        window.toastr.error(message);
                    } else if (window.LaravelToast) {
                        window.LaravelToast.error(message);
                    }
                    setTimeout(function() {
                        document.getElementById('{$uuid}')?.remove();
                    }, 5000);
                    return;
                }
                alert(message);
                document.getElementById('{$uuid}')?.remove();
            })();
        </script>
        HTML;
        return $snippet;
    }

    public static function languageCreate(?string $createdBy = DC::DEFAULT_UUID): void
    {
        foreach (self::langList() as $code => $fullName) {
            $output = SafeConsoleOutput::make();
            $output->writeln("Creating or finding language: {$code} - {$fullName}");
            try {
                Language::firstOrCreate(
                    ['code'      => $code],
                    [
                        'full_name'         => $fullName,
                        DC::COL_TABLE_CREATOR => $createdBy,
                    ]
                );
                Log::debug(
                    __CLASS__ . '::' . __FUNCTION__
                        . ": language [{$code}] newly created or already existed."
                );
            } catch (QueryException $e) {
                Log::error(
                    __CLASS__ . '::' . __FUNCTION__
                        . " DB error creating language [{$code}]: {$e->getMessage()}",
                    ['sql' => $e->getSql(), 'bindings' => $e->getBindings()]
                );
            } catch (\Throwable $e) {
                Log::critical(
                    __CLASS__ . '::' . __FUNCTION__
                        . " unexpected error for language [{$code}]: {$e->getMessage()}"
                );
            }
        }
    }

    public static function langSetting(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)->where(UC::COL_USER_ID, DC::DEFAULT_UUID)->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }

    public static function getChatGPTSettings(): Plan|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = User::find($userOrRedirect->creatorId());
        if (!$user)
            return null;
        return Plan::find($user?->plan);
    }

    public static function getAccountBalance(string|int $accountId, ?string $startDate = null, ?string $endDate = null): float|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $start = $startDate ?: date('Y-01-01');
        $end  = $endDate   ?: date('Y-m-d', strtotime('+1 day'));
        $invoiceProductIds = ProductService::where('sale_chart_account_id', $accountId)->pluck('id');
        $invoiceAmount = InvoiceProduct::whereIn('product_id', $invoiceProductIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum(DB::raw('price * quantity'));
        $accountIds = BankAccount::where('chart_account_id', $accountId)
            ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
            ->pluck('id');
        $invoicePaymentAmount = InvoicePayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        $revenueAmount = Revenue::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        $billProductIds = ProductService::where('expense_chart_account_id', $accountId)->pluck('id');
        $billProductAmount = BillProduct::whereIn('product_id', $billProductIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum('total');
        $billAmount = BillAccount::where('chart_account_id', $accountId)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum('price');
        $billPaymentAmount = BillPayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        $paymentAmount = Payment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        $journalCredit = JournalItem::join(DC::TABLE_JOURNAL_ENTRIES, DC::TABLE_JOURNAL_ENTRIES . '.id', 'journal_items.journal')
            ->where(DC::TABLE_JOURNAL_ENTRIES . '.' . DC::COL_TABLE_CREATOR, $user?->creatorId())
            ->where('journal_items.account', $accountId)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('journal_items.created_at', [$start, $end]))
            ->sum('credit');
        $journalDebit = JournalItem::join(DC::TABLE_JOURNAL_ENTRIES, DC::TABLE_JOURNAL_ENTRIES . '.id', 'journal_items.journal')
            ->where(DC::TABLE_JOURNAL_ENTRIES . '.' . DC::COL_TABLE_CREATOR, $user?->creatorId())
            ->where('journal_items.account', $accountId)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('journal_items.created_at', [$start, $end]))
            ->sum('debit');
        return ($invoiceAmount + $invoicePaymentAmount + $revenueAmount + $journalCredit)
            - ($journalDebit + $billProductAmount + $billAmount + $billPaymentAmount + $paymentAmount);
    }

    public static function getAccountData(string|int $accountId, ?string $startDate = null, ?string $endDate = null): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $start = $startDate ?: date('Y-01-01');
        $end  = $endDate   ?: date('Y-m-d', strtotime('+1 day'));
        $invoiceProducts = ProductService::where('sale_chart_account_id', $accountId)->pluck('id');
        $invoice = InvoiceProduct::whereIn('product_id', $invoiceProducts)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->get();
        $accountIds = BankAccount::where('chart_account_id', $accountId)
            ->pluck('id');
        $invoicePayment = InvoicePayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->get();
        $revenue = Revenue::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->get();
        $billProducts = ProductService::where('expense_chart_account_id', $accountId)->pluck('id');
        $bill = BillProduct::whereIn('product_id', $billProducts)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->get();
        $billData = BillAccount::where('chart_account_id', $accountId)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->get();
        $billPayment = BillPayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->get();
        $payment = Payment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->get();
        $journalItems = JournalItem::select(DC::TABLE_JOURNAL_ENTRIES . '.journal_id', DC::TABLE_JOURNAL_ENTRIES . '.date as transaction_date', 'journal_items.*')
            ->join(DC::TABLE_JOURNAL_ENTRIES, DC::TABLE_JOURNAL_ENTRIES . '.id', 'journal_items.journal')
            ->where(DC::TABLE_JOURNAL_ENTRIES . '.' . DC::COL_TABLE_CREATOR, $user?->creatorId())
            ->where('journal_items.account', $accountId)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('journal_items.created_at', [$start, $end]))
            ->get();
        return [
            'invoice'        => $invoice,
            'invoicepayment' => $invoicePayment,
            'revenue'        => $revenue,
            'bill'           => $bill,
            'billdata'       => $billData,
            'billpayment'    => $billPayment,
            'payment'        => $payment,
            'journalItem'    => $journalItems,
        ];
    }

    /** @see AccountingService::getBalanceSheetCredit() */
    public static function getBalanceSheetCredit(string|int $accountId, ?string $startDate = null, ?string $endDate = null): float
    {
        return AccountingService::getBalanceSheetCredit($accountId, $startDate, $endDate);
    }

    /** @see AccountingService::getBalanceSheetDebit() */
    public static function getBalanceSheetDebit(string|int $accountId, ?string $startDate = null, ?string $endDate = null): float
    {
        return AccountingService::getBalanceSheetDebit($accountId, $startDate, $endDate);
    }

    /** @see AccountingService::trialBalance() */
    public static function trialBalance(string|int $accountType, string $start, string $end): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        /** @var User $userOrRedirect */
        return AccountingService::trialBalance($accountType, $start, $end, $userOrRedirect);
    }

    public static function smtpDetail(string|int $userId): array
    {
        $settings = self::settingsById($userId);
        $smtpConfig = [
            'mail.driver'       => $settings['mail_driver']       ?? '',
            'mail.host'         => $settings['mail_host']         ?? '',
            'mail.port'         => $settings['mail_port']         ?? '',
            'mail.encryption'   => $settings['mail_encryption']   ?? '',
            'mail.username'     => $settings['mail_username']     ?? '',
            'mail.password'     => $settings['mail_password']     ?? '',
            'mail.from.address' => $settings['mail_from_address'] ?? '',
            'mail.from.name'    => $settings['mail_from_name']    ?? '',
        ];
        Config::set($smtpConfig);
        return $smtpConfig;
    }

    public static function getPusherSetting(): array
    {
        $settings = self::settingsById(DC::DEFAULT_UUID);
        if (empty($settings['pusher_app_key'])) {
            return [];
        }
        $pusherConfig = [
            'chatify.pusher.key'            => $settings['pusher_app_key']      ?? '',
            'chatify.pusher.secret'         => $settings['pusher_app_secret']   ?? '',
            'chatify.pusher.app_id'         => $settings['pusher_app_id']       ?? '',
            'chatify.pusher.options.cluster' => $settings['pusher_app_cluster']  ?? '',
        ];
        Config::set($pusherConfig);
        return $settings;
    }

    private static function formatNumber(string $prefixKey, int|string $number): string
    {
        $settings = self::settings();
        $prefix  = $settings[$prefixKey] ?? '';
        return $prefix . sprintf('%05d', (int) $number);
    }

    /**
     * Delete a file from the configured storage driver.
     *
     * @param  string $path  Relative storage path (e.g. 'uploads/documentUpload/file.pdf')
     * @return array{flag: int, msg: string}
     */
    public static function deleteFile(string $path): array
    {
        try {
            $settings   = self::getStorageSetting();
            $driverType = $settings[SC::STR_STT] ?? SC::LC;

            if ($driverType === 'local') {
                $fullPath = storage_path($path);
                if (!file_exists($fullPath)) {
                    return ['flag' => 1, 'msg' => 'File does not exist, nothing to delete.'];
                }
                if (!unlink($fullPath)) {
                    return ['flag' => 0, 'msg' => 'Failed to delete local file.'];
                }
            } else {
                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk($driverType);
                if (!$disk->exists($path)) {
                    return ['flag' => 1, 'msg' => 'File does not exist, nothing to delete.'];
                }
                if (!$disk->delete($path)) {
                    return ['flag' => 0, 'msg' => "Failed to delete file from {$driverType}."];
                }
            }

            return ['flag' => 1, 'msg' => 'success'];
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['path' => $path, 'error' => $e->getMessage()]);
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Store an UploadedFile using the configured storage driver,
     * returning the final stored filename (with extension).
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory   e.g. 'uploads/document'
     * @param  string  $baseName    Desired name without extension
     * @return string  The stored filename
     */
    public static function uploadFileGeneric(
        \Illuminate\Http\UploadedFile $file,
        string $directory,
        string $baseName
    ): string {
        $extension = $file->getClientOriginalExtension();
        $fileName  = $baseName . ($extension ? ".{$extension}" : '');

        $settings   = self::getStorageSetting();
        $driverType = $settings[SC::STR_STT] ?? SC::LC;

        if ($driverType === 'local') {
            $file->move(storage_path($directory), $fileName);
        } else {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk($driverType);
            $disk->putFileAs($directory, $file, $fileName);
        }

        return $fileName;
    }

    /**
     * Send notifications for a newly created Budget.
     *
     * @param  Budget  $budget
     * @return void
     */
    public static function notifyNewBudget(Budget $budget): void
    {
        try {
            $creator = User::find($budget->{DC::COL_TABLE_CREATOR}); // @phpstan-ignore property.notFound
            if (!$creator) return;

            $emailObj = [
                'budget_name'   => $budget->name ?? '',
                'budget_period' => $budget->period ?? '',
                'budget_year'   => $budget->from ?? '',
            ];

            $adminUsers = User::where('type', PMC::CPN)
                ->where(DC::COL_TABLE_CREATOR, $creator->creatorId())
                ->get();

            foreach ($adminUsers as $admin) {
                if (!empty($admin->email)) {
                    self::sendEmailTemplate('new_budget', [$admin->email], $emailObj);
                }
            }

            $webhook = self::webhookSetting('new_budget', $creator->id);
            if (is_array($webhook) && !empty($webhook['url'])) {
                self::webhookCall($webhook['url'], $budget->toArray(), $webhook['method'] ?? 'POST');
            }
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['budget_id' => $budget->id ?? null, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Convert a collection of ProjectTask models into a calendar-compatible array.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectTask>  $tasks
     * @return array<int, array{id: mixed, title: string, start: string, end: string, className: string, allDay: bool}>
     */
    public static function getTaskCalendarArray($tasks): array
    {
        $result = [];

        foreach ($tasks as $task) {
            $result[] = [
                'id'        => $task->id,
                'title'     => $task->title ?? $task->name ?? '',
                'start'     => $task->start_date ?? '',
                'end'       => $task->end_date ?? $task->due_date ?? '',
                'className' => self::$colorCode[$task->status ?? 0] ?? '',
                'allDay'    => true,
            ];
        }

        return $result;
    }
}

    //    public static function employeePayslipDetail($employeeId)
    //    {
    ////        dd($employeeId);
    //        $earning['allowance']        = Allowance::where('employee_id', $employeeId)->get();
    ////        dd($earning['allowance']);
    //        $employeesSalary = Employee::find($employeeId);
    //
    //        $totalAllowance = 0 ;
    //        foreach($earning['allowance'] as $allowance)
    //        {
    //            if($allowance->type == 'fixed')
    //            {
    //                $totalAllowances = $allowance->amount;
    //            }
    //            else
    //            {
    //                $totalAllowances = $allowance->amount * $employeesSalary->salary / 100;
    //            }
    //            $totalAllowance += $totalAllowances ;
    //        }
    //
    //
    ////        $earning['totalAllowance']   = Allowance::where('employee_id', $employeeId)->where('type', 'fixed')->get()->sum('amount');
    //        $earning['commission']       = Commission::where('employee_id', $employeeId)->get();
    //        $totalCommisions = 0 ;
    //        foreach($earning['commission'] as $commission)
    //        {
    //            if($commission->type == 'fixed')
    //            {
    //                $totalCom = $commission->amount;
    //            }
    //            else
    //            {
    //                $totalCom = $commission->amount * $employeesSalary->salary / 100;
    //            }
    //            $totalCommisions += $totalCom ;
    //        }
    ////        $earning['totalCommission']  = Commission::where('employee_id', $employeeId)->where('type', 'fixed')->get()->sum('amount');
    //        $earning['otherPayment']     = OtherPayment::where('employee_id', $employeeId)->get();
    //        $totalOtherPayment = 0 ;
    //        foreach($earning['otherPayment'] as $otherPayment)
    //        {
    //            if($otherPayment->type == 'fixed')
    //            {
    //                $totalother = $otherPayment->amount;
    //            }
    //            else
    //            {
    //                $totalother = $otherPayment->amount * $employeesSalary->salary / 100;
    //            }
    //            $totalOtherPayment += $totalother ;
    //        }
    ////        $earning['totalOtherPayment'] = OtherPayment::where('employee_id', $employeeId)->where('type', 'fixed')->get()->sum('amount');
    //        $earning['overTime']         = Overtime::select('id', 'title')->selectRaw('number_of_days * hours* rate as amount')->where('employee_id', $employeeId)->get();
    //        $earning['totalOverTime']    = Overtime::selectRaw('number_of_days * hours* rate as total')->where('employee_id', $employeeId)->get()->sum('total');
    //
    //        $deduction['loan']          = Loan::where('employee_id', $employeeId)->get();
    //        $totalLoan = 0 ;
    //        foreach($deduction['loan'] as $loan)
    //        {
    //            if($loan->type == 'fixed')
    //            {
    //                $totalloan = $loan->amount;
    //            }
    //            else
    //            {
    //                $totalloan = $loan->amount * $employeesSalary->salary / 100;
    //            }
    //            $totalLoan += $totalloan ;
    //        }
    ////        $deduction['totalLoan']     = Loan::where('employee_id', $employeeId)->where('type', 'fixed')->get()->sum('amount');
    //        $deduction['deduction']     = SaturationDeduction::where('employee_id', $employeeId)->get();
    //        $totalDeduction = 0 ;
    //        foreach($deduction['deduction'] as $deductions)
    //        {
    //            if($deductions->type == 'fixed')
    //            {
    //                $totaldeduction = $deductions->amount;
    //            }
    //            else
    //            {
    //                $totaldeduction = $deductions->amount * $employeesSalary->salary / 100;
    //            }
    //            $totalDeduction += $totaldeduction ;
    //        }
    ////        $deduction['totalDeduction'] = SaturationDeduction::where('employee_id', $employeeId)->where('type', 'fixed')->get()->sum('amount');
    //
    //        $payslip['earning']       = $earning;
    //        $payslip['totalEarning']  = $totalAllowance + $totalCommisions + $totalOtherPayment + $earning['totalOverTime'];
    //        $payslip['deduction']     = $deduction;
    //        $payslip['totalDeduction'] = $totalLoan + $totalDeduction;
    //
    //        return $payslip;
    //    }