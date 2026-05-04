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
use App\Helpers\SafeConsoleOutput;
use App\Services\Utility\AccountingService;
use App\Services\Utility\CalendarService;
use App\Services\Utility\FileStorageService;
use App\Services\Utility\FinanceBillingService;
use App\Services\Utility\LocalizationService;
use App\Services\Utility\ModelLookupService;
use App\Services\Utility\NotificationService;
use App\Services\Utility\TenantSetupService;
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

    /** @internal Used by SettingsService — public for cross-service cache sharing */
    public static $getSettings    = null;
    /** @internal Used by SettingsService — public for cross-service cache sharing */
    public static $getSettingsId  = [];
    /** @internal Used by FinanceBillingService — public for cross-service cache sharing */
    public static $taxsData       = [];
    /** @internal Used by FinanceBillingService — public for cross-service cache sharing */
    public static $taxRateData    = [];
    /** @internal */
    public static $taxData        = null;
    /** @internal Used by FinanceBillingService — public for cross-service cache sharing */
    public static $taxes          = [];
    /** @internal Used by LocalizationService — public for cross-service cache sharing */
    public static $languageSetting = null;
    /** @internal */
    public static $getRatingData  = null;
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

    /** @see \App\Services\Utility\ModelLookupService::getClient() */
    public static function getClient(Model $model): ?BelongsTo
    {
        return ModelLookupService::getClient($model);
    }

    /** @see \App\Services\Utility\ModelLookupService::getCustomer() */
    public static function getCustomer(Model $model): ?BelongsTo
    {
        return ModelLookupService::getCustomer($model);
    }

    /** @see \App\Services\Utility\ModelLookupService::getVendor() */
    public static function getVendor(Model $model): ?BelongsTo
    {
        return ModelLookupService::getVendor($model);
    }

    /** @see \App\Services\Utility\ModelLookupService::getCompany() */
    public static function getCompany(Model $model): ?BelongsTo
    {
        return ModelLookupService::getCompany($model);
    }

    /** @see \App\Services\Utility\ModelLookupService::getCategory() */
    public static function getCategory(Model $model): ?BelongsTo
    {
        return ModelLookupService::getCategory($model);
    }

    /** @see \App\Services\Utility\ModelLookupService::getProduct() */
    public static function getProduct(Model $model): ?BelongsTo
    {
        return ModelLookupService::getProduct($model);
    }

    /** @see \App\Services\Utility\ModelLookupService::getEmployee() */
    public static function getEmployee(Model $model): ?BelongsTo
    {
        return ModelLookupService::getEmployee($model);
    }

    /** @see \App\Services\Utility\ModelLookupService::isEmployee() */
    public static function isEmployee(User $user): bool
    {
        return ModelLookupService::isEmployee($user);
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
     * @see \App\Services\Utility\ModelLookupService::prepareCommonViewData()
     */
    public static function prepareCommonViewData(int|string $creatorId = DC::DEFAULT_UUID, ?string $logoPath = null): array
    {
        return ModelLookupService::prepareCommonViewData($creatorId, $logoPath);
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

    /** @see \App\Services\Utility\LocalizationService::languages() */
    public static function languages(): Collection
    {
        return LocalizationService::languages();
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

    /** @see \App\Services\Utility\FinanceBillingService::priceFormat() */
    public static function priceFormat(array $settings, float|int $price): string
    {
        return FinanceBillingService::priceFormat($settings, $price);
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
     * @see \App\Services\Utility\FinanceBillingService::billItemStats()
     */
    public static function billItemStats(Bill $bill, array $settings): array
    {
        return FinanceBillingService::billItemStats($bill, $settings);
    }

    /** @see \App\Services\Utility\FinanceBillingService::getTax() */
    public static function getTax(string|int $taxId): ?Tax
    {
        return FinanceBillingService::getTax($taxId);
    }

    /** @see \App\Services\Utility\FinanceBillingService::tax() */
    public static function tax(string $taxesCsv): array
    {
        return FinanceBillingService::tax($taxesCsv);
    }

    /** @see \App\Services\Utility\FinanceBillingService::taxRate() */
    public static function taxRate(float $taxRate, float $price, float $quantity, float $discount = 0): float
    {
        return FinanceBillingService::taxRate($taxRate, $price, $quantity, $discount);
    }

    /** @see \App\Services\Utility\FinanceBillingService::totalTaxRate() */
    public static function totalTaxRate(string $taxesCsv): float
    {
        return FinanceBillingService::totalTaxRate($taxesCsv);
    }

    /** @see \App\Services\Utility\FinanceBillingService::userBalance() */
    public static function userBalance(string $userType, string|int $id, float $amount, string $type): void
    {
        FinanceBillingService::userBalance($userType, $id, $amount, $type);
    }

    /** @see \App\Services\Utility\FinanceBillingService::updateUserBalance() */
    public static function updateUserBalance(string $userType, string|int $id, float $amount, string $type): void
    {
        FinanceBillingService::updateUserBalance($userType, $id, $amount, $type);
    }

    /** @see \App\Services\Utility\FinanceBillingService::bankAccountBalance() */
    public static function bankAccountBalance(string|int $id, float $amount, string $type): void
    {
        FinanceBillingService::bankAccountBalance($id, $amount, $type);
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

    /** @see \App\Services\Utility\FileStorageService::deleteDirectory() */
    public static function deleteDirectory(string $dir): bool
    {
        return FileStorageService::deleteDirectory($dir);
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

    /** @see \App\Services\Utility\NotificationService::sendEmailTemplate() */
    public static function sendEmailTemplate(string $emailTemplate, array $mailTo, array $obj): array|RedirectResponse
    {
        return NotificationService::sendEmailTemplate($emailTemplate, $mailTo, $obj);
    }

    /** @see \App\Services\Utility\NotificationService::sendUserEmailTemplate() */
    public static function sendUserEmailTemplate(string $emailTemplate, array $mailTo, array $obj): array|RedirectResponse
    {
        return NotificationService::sendUserEmailTemplate($emailTemplate, $mailTo, $obj);
    }

    /** @see \App\Services\Utility\NotificationService::replaceVariable() */
    public static function replaceVariable($content, $obj): array|string
    {
        return NotificationService::replaceVariable($content, $obj);
    }

    public const PPL_LD_DL_STG = 'pipelineLeadDealStage';
    /** @see \App\Services\Utility\TenantSetupService::pipelineLeadDealStage() */
    public static function pipelineLeadDealStage(string|int $createdId): void
    {
        TenantSetupService::pipelineLeadDealStage($createdId);
    }

    public const PRJ_TSK_STGS = 'projectTaskStages';
    /** @see \App\Services\Utility\TenantSetupService::projectTaskStages() */
    public static function projectTaskStages(string $projectId, string $createdBy): void
    {
        TenantSetupService::projectTaskStages($projectId, $createdBy);
    }

    public const JB_STG = 'jobStage';
    /** @see \App\Services\Utility\TenantSetupService::jobStage() */
    public static function jobStage(string|int $creatorId): void
    {
        TenantSetupService::jobStage($creatorId);
    }

    /** @see \App\Services\Utility\TenantSetupService::labels() */
    public static function labels(string|int $creatorId): void
    {
        TenantSetupService::labels($creatorId);
    }

    /** @see \App\Services\Utility\TenantSetupService::sources() */
    public static function sources(string|int $createdId): void
    {
        TenantSetupService::sources($createdId);
    }
    /** @see \App\Services\Utility\TenantSetupService::employeeNumber() */
    public static function employeeNumber($userId): string|int
    {
        return TenantSetupService::employeeNumber($userId);
    }

    public const EMP_DTLS = 'employeeDetails';
    /** @see \App\Services\Utility\TenantSetupService::employeeDetails() */
    public static function employeeDetails(string|int $userId, string|int $createdBy): void
    {
        TenantSetupService::employeeDetails($userId, $createdBy);
    }


    /** @see \App\Services\Utility\TenantSetupService::employeeDetailsUpdate() */
    public static function employeeDetailsUpdate(string|int $userId, string|int $createdBy): void
    {
        TenantSetupService::employeeDetailsUpdate($userId, $createdBy);
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

    /** @see \App\Services\Utility\FileStorageService::checkFileExistsAndDelete() */
    public static function checkFileExistsAndDelete(array $files): bool
    {
        return FileStorageService::checkFileExistsAndDelete($files);
    }

    /** @see \App\Services\Utility\FinanceBillingService::projectCurrencyFormat() */
    public static function projectCurrencyFormat(string|int $projectId, float|int $amount, bool $decimal = false): ?string
    {
        return FinanceBillingService::projectCurrencyFormat($projectId, $amount, $decimal);
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

    /** @see \App\Services\Utility\TenantSetupService::employeePayslipDetail() */
    public static function employeePayslipDetail(string|int $employeeId, string $month): array
    {
        return TenantSetupService::employeePayslipDetail($employeeId, $month);
    }

    public static function companyData(string|int $companyId, string $key): string
    {
        $row = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, $companyId)
            ->where('name', $key)
            ->first();
        return $row->value ?? '';
    }

    /** @see \App\Services\Utility\TenantSetupService::addNewData() */
    public static function addNewData(): void
    {
        TenantSetupService::addNewData();
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

    /** @see \App\Services\Utility\NotificationService::sendSlackMsg() */
    public static function sendSlackMsg(string $slug, array $obj, string|int|null $userId = null): void
    {
        NotificationService::sendSlackMsg($slug, $obj, $userId);
    }

    /** @see \App\Services\Utility\NotificationService::sendTelegramMsg() */
    public static function sendTelegramMsg(string $slug, array $obj, string|int|null $userId = null): void
    {
        NotificationService::sendTelegramMsg($slug, $obj, $userId);
    }

    /** @see \App\Services\Utility\NotificationService::sendTwilioMsg() */
    public static function sendTwilioMsg(string $to, string $slug, array $obj, string|int|null $userId = null): void
    {
        NotificationService::sendTwilioMsg($to, $slug, $obj, $userId);
    }

    /** @see \App\Services\Utility\FinanceBillingService::totalQuantity() */
    public static function totalQuantity(string $type, int $quantity, string|int $productId): void
    {
        FinanceBillingService::totalQuantity($type, $quantity, $productId);
    }

    /** @see \App\Services\Utility\FinanceBillingService::warehouseQuantity() */
    public static function warehouseQuantity(string $type, int $quantity, string|int $productId, string|int $warehouseId): void
    {
        FinanceBillingService::warehouseQuantity($type, $quantity, $productId, $warehouseId);
    }

    /** @see \App\Services\Utility\FinanceBillingService::warehouseTransferQty() */
    public static function warehouseTransferQty(string|int $fromWarehouse, string|int $toWarehouse, string|int $productId, int $quantity, ?string $delete = null): void
    {
        FinanceBillingService::warehouseTransferQty($fromWarehouse, $toWarehouse, $productId, $quantity, $delete);
    }

    /** @see \App\Services\Utility\FinanceBillingService::addProductStock() */
    public static function addProductStock(string|int $productId, int $quantity, string $type, string $description, string|int $typeId): void
    {
        FinanceBillingService::addProductStock($productId, $quantity, $type, $description, $typeId);
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

    /** @see \App\Services\Utility\FinanceBillingService::addWarehouseStock() */
    public static function addWarehouseStock(string|int $productId, int $quantity, string|int $warehouseId): void
    {
        FinanceBillingService::addWarehouseStock($productId, $quantity, $warehouseId);
    }

    /** @see \App\Services\Utility\TenantSetupService::startingNumber() */
    public static function startingNumber(int $id, string $type): int|RedirectResponse
    {
        return TenantSetupService::startingNumber($id, $type);
    }

    /** @see \App\Services\Utility\FileStorageService::uploadFile() */
    public static function uploadFile($request, string $keyName, string $name, string $path, array $customValidation = []): array
    {
        return FileStorageService::uploadFile($request, $keyName, $name, $path, $customValidation);
    }

    /** @see \App\Services\Utility\FileStorageService::uploadCustomFile() */
    public static function uploadCustomFile($request, string $keyName, string $name, string $path, string $dataKey, array $customValidation = []): array
    {
        return FileStorageService::uploadCustomFile($request, $keyName, $name, $path, $dataKey, $customValidation);
    }

    /** @see \App\Services\Utility\FileStorageService::getFile() */
    public static function getFile(string $path = 'uploads/logo', mixed $settings = null): string
    {
        return FileStorageService::getFile($path, $settings);
    }

    /** @see \App\Services\Utility\FileStorageService::getStorageSetting() */
    public static function getStorageSetting(): array
    {
        return FileStorageService::getStorageSetting();
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

    /** @see \App\Services\Utility\NotificationService::webhookSetting() */
    public static function webhookSetting(string $module, string|int|null $userId = null): array|bool
    {
        return NotificationService::webhookSetting($module, $userId);
    }

    /** @see \App\Services\Utility\NotificationService::webhookCall() */
    public static function webhookCall(?string $url, $parameter = null, string $method = 'POST'): bool
    {
        return NotificationService::webhookCall($url, $parameter, $method);
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

    /** @see \App\Services\Utility\LocalizationService::generateBrazilianPhone() */
    public static function generateBrazilianPhone($mobile = true, $formatted = true): string
    {
        return LocalizationService::generateBrazilianPhone($mobile, $formatted);
    }

    /** @see \App\Services\Utility\LocalizationService::generateRandomCpf() */
    public static function generateRandomCpf(bool $formatted = true): string
    {
        return LocalizationService::generateRandomCpf($formatted);
    }

    /** @see \App\Services\Utility\LocalizationService::generateRandomCnpj() */
    public static function generateRandomCnpj(bool $formatted = true): string
    {
        return LocalizationService::generateRandomCnpj($formatted);
    }

    /** @see \App\Services\Utility\LocalizationService::isValidCpf() */
    public static function isValidCpf(?string $cpf): bool
    {
        return LocalizationService::isValidCpf($cpf);
    }

    /** @see \App\Services\Utility\LocalizationService::isValidCnpj() */
    public static function isValidCnpj(?string $cnpj): bool
    {
        return LocalizationService::isValidCnpj($cnpj);
    }


    /** @see \App\Services\Utility\FileStorageService::updateStorageLimit() */
    public static function updateStorageLimit(string|int $companyId, float $imageSize): string|int
    {
        return FileStorageService::updateStorageLimit($companyId, $imageSize);
    }

    /** @see \App\Services\Utility\FileStorageService::changeStorageLimit() */
    public static function changeStorageLimit(string|int $companyId, string $filePath): bool
    {
        return FileStorageService::changeStorageLimit($companyId, $filePath);
    }

    /** @see \App\Services\Utility\LocalizationService::flagOfCountry() */
    public static function flagOfCountry(): array
    {
        return LocalizationService::flagOfCountry();
    }

    /** @see \App\Services\Utility\LocalizationService::langList() */
    public static function langList(): array
    {
        return LocalizationService::langList();
    }

    /** @see \App\Services\Utility\LocalizationService::fetchUserLang() */
    public static function fetchUserLang(?User $user = null, ?Request $req = null): ?string
    {
        return LocalizationService::fetchUserLang($user, $req);
    }

    /** @see \App\Services\Utility\LocalizationService::fetchLinkMessage() */
    public static function fetchLinkMessage(string $lang = DC::DEFAULT_LANG, ?string $set = 'generics', string $key = '', bool $isFailure = true, bool $shouldFallback = true): ?string
    {
        return LocalizationService::fetchLinkMessage($lang, $set, $key, $isFailure, $shouldFallback);
    }

    /** @see \App\Services\Utility\LocalizationService::displayErrorMessage() */
    public static function displayErrorMessage($msg = null, $lang = DC::DEFAULT_LANG): string
    {
        return LocalizationService::displayErrorMessage($msg, $lang);
    }

    /** @see \App\Services\Utility\LocalizationService::languageCreate() */
    public static function languageCreate(?string $createdBy = DC::DEFAULT_UUID): void
    {
        LocalizationService::languageCreate($createdBy);
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
            ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
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

    /** @see \App\Services\Utility\NotificationService::smtpDetail() */
    public static function smtpDetail(string|int $userId): array
    {
        return NotificationService::smtpDetail($userId);
    }

    /** @see \App\Services\Utility\NotificationService::getPusherSetting() */
    public static function getPusherSetting(): array
    {
        return NotificationService::getPusherSetting();
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
     * @see \App\Services\Utility\FileStorageService::deleteFile()
     */
    public static function deleteFile(string $path): array
    {
        return FileStorageService::deleteFile($path);
    }

    /**
     * Store an UploadedFile using the configured storage driver,
     * returning the final stored filename (with extension).
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory   e.g. 'uploads/document'
     * @param  string  $baseName    Desired name without extension
     * @return string  The stored filename
     * @see \App\Services\Utility\FileStorageService::uploadFileGeneric()
     */
    public static function uploadFileGeneric(
        \Illuminate\Http\UploadedFile $file,
        string $directory,
        string $baseName
    ): string {
        return FileStorageService::uploadFileGeneric($file, $directory, $baseName);
    }

    /**
     * Send notifications for a newly created Budget.
     *
     * @param  Budget  $budget
     * @return void
     * @see \App\Services\Utility\NotificationService::notifyNewBudget()
     */
    public static function notifyNewBudget(Budget $budget): void
    {
        NotificationService::notifyNewBudget($budget);
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