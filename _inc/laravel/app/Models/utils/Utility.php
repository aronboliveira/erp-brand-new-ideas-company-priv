<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    BillsConstants,
    ChartsConstants as CTC,
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    EmailsConstants as EC,
    FormsConstants as FC,
    LangsConstants,
    PermissionsConstants as PMC,
    ProjectsConstants,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Mail\CommonEmailTemplate;
use App\Models\{
    Branch,
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
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Twilio\Rest\Client;

class Utility extends Model
{

    use ChecksLogin;

    private static $getSettings    = null;
    private static $getSettingsId  = null;
    private static $taxsData       = null;
    private static $taxRateData    = null;
    private static $taxData        = null;
    private static $taxes          = null;
    private static $languageSetting = null;
    private static $getRatingData  = null;
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

    public static function generateUuid(): string
    {
        do $uuid = Str::uuid()->toString();
        while (in_array($uuid, self::$uuids, true));
        self::$uuids[] = $uuid;
        return $uuid;
    }

    public static function looksLikeUuid(string $s): bool
    {
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
     * @param  int|null    $creatorId  Company/creator ID, or null for global.
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
            ?? $company_logo_lt
            ?? $company_logo_dk
            ?? '';
        $meta_logo = $seo[SC::MT_LOGO]
            ?? $seo[SC::MT_IMG_K]
            ?? $company_logo_lt
            ?? $company_logo_dk
            ?? '';
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
        $output = new ConsoleOutput();
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
        $output = new ConsoleOutput();
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
        $output = new ConsoleOutput();
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
        $output = new ConsoleOutput();
        $class = class_basename(self::class);
        $method = __FUNCTION__;
        $tag   = "{$class}::{$method}";
        Log::debug("{$tag} called", [UC::COL_USER_ID => $id]);
        $output->writeln("## [{$tag}] Fetching settings for user ID {$id}");
        if (!self::$getSettingsId) {
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
                self::$getSettingsId = $data;
                Log::debug("{$tag} found settings", ['count' => count($data)]);
                $output->writeln("## [{$tag}] Retrieved " . count($data) . " rows");
            } catch (QueryException $qe) {
                Log::error("{$tag} QueryException", ['message' => $qe->getMessage()]);
                $output->writeln("## [{$tag}] DB error: {$qe->getMessage()}");
                self::$getSettingsId = SC::DFT_SETTINGS;
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
                self::$getSettingsId = SC::DFT_SETTINGS;
            }
        } else {
            Log::debug("{$tag} returning cached settings", ['count' => count(self::$getSettingsId)]);
            $output->writeln("## [{$tag}] Returning cached settings (" . count(self::$getSettingsId) . ")");
        }
        return self::$getSettingsId;
    }

    public static function fallbackSettings(mixed $data): mixed
    {
        try {
            $setting ??= Utility::settings() ?: [];
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
        return $faviconUrl ?? '';
    }

    public static function languages(): Collection
    {
        $output = new ConsoleOutput();
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

    public static function getTax(string|int $taxId): ?Tax
    {
        if (self::$taxes === null) {
            try {
                self::$taxes = Tax::find($taxId);
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed fetching Tax[{$taxId}]: {$e->getMessage()}");
                return null;
            }
        }
        return self::$taxes;
    }

    public static function tax(string $taxesCsv): array
    {
        if (self::$taxsData === null) {
            $taxIds = array_filter(explode(',', $taxesCsv));
            $results = [];
            foreach ($taxIds as $id) {
                $taxModel = self::getTax($id);
                if ($taxModel !== null)
                    $results[] = $taxModel;
            }
            self::$taxsData = $results;
        }
        return self::$taxsData;
    }

    public static function taxRate(float $taxRate, float $price, float $quantity, float $discount = 0): float
    {
        $base = ($price * $quantity) - $discount;
        return $base * ($taxRate * 0.01);
    }

    public static function totalTaxRate(string $taxesCsv): float
    {
        if (self::$taxRateData === null) {
            $taxIds = array_filter(explode(',', $taxesCsv));
            $rateSum = 0.0;
            foreach ($taxIds as $id) {
                $taxModel = self::getTax($id);
                $rateSum += $taxModel->rate ?? 0;
            }
            self::$taxRateData = $rateSum;
        }
        return self::$taxRateData;
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
    public static function chartOfAccountTypeData(string $companyId): void
    {
        $typeNames = [
            CTC::TP_ASSETS         => CTC::COA_TPS[CTC::AST],
            CTC::TP_LIABILITIES    => CTC::COA_TPS[CTC::LBL],
            CTC::TP_EQUITY         => CTC::COA_TPS[CTC::EQT],
            CTC::TP_INCOME         => CTC::COA_TPS[CTC::ICM],
            CTC::TP_COGS           => CTC::COA_TPS[CTC::CGS],
            CTC::TP_EXPENSES       => CTC::COA_TPS[CTC::EXP],
        ];
        foreach (CTC::COA_SBTPS as $typeId => $subtypes) {
            ChartOfAccountType::updateOrCreate(
                ['id' => $typeId],
                [
                    'name'       => $typeNames[$typeId]  ?? 'Undefined',
                    DC::COL_TABLE_CREATOR => $companyId,
                ]
            );
            foreach ($subtypes as $subTypeId => $subName)
                ChartOfAccountSubType::updateOrCreate(
                    ['id' => $subTypeId],
                    [
                        'name'       => $subName,
                        'type'       => $typeId,
                        'type_name'  => $typeNames[$typeId]  ?? 'Undefined',
                        DC::COL_TABLE_CREATOR => $companyId,
                    ]
                );
        }
    }

    public const COA_DATA = 'chartOfAccountData';
    public static function chartOfAccountData(object $user): void
    {
        foreach (self::$chartOfAccount as $acct) {
            try {
                ChartOfAccount::create([
                    CTC::COL_CD          => $acct[CTC::COL_CD],
                    CTC::COL_NM          => $acct[CTC::COL_NM],
                    CTC::COL_TP          => $acct[CTC::COL_TP],
                    CTC::COL_SUBTP       => $acct[CTC::COL_SUBTP],
                    CTC::COL_ENB         => 1,
                    DC::COL_TABLE_CREATOR => $user?->id,
                ]);
            } catch (\Throwable $e) {
                Log::error(
                    __CLASS__ . '::' . __FUNCTION__
                        . " failed creating COA[{$acct[CTC::COL_CD]}]: {$e->getMessage()}"
                );
            }
        }
    }

    public const COA_DATA1 = 'chartOfAccountData1';
    public static function chartOfAccountData1(string|int $userId): void
    {
        $chartData = self::$chartOfAccount1;
        foreach ($chartData as $acct) {
            try {
                DB::transaction(function () use ($acct, $userId) {
                    $type = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $userId)
                        ->where(CTC::COL_NM, $acct[CTC::COL_TP])
                        ->firstOrFail();
                    $sub = ChartOfAccountSubType::where(CTC::COL_TP, $type->id)
                        ->where(CTC::COL_NM, $acct[CTC::COL_SUBTP])
                        ->firstOrFail();
                    ChartOfAccount::create([
                        CTC::COL_CD          => $acct[CTC::COL_CD],
                        CTC::COL_NM          => $acct[CTC::COL_NM],
                        CTC::COL_TP          => $type->id,
                        CTC::COL_SUBTP       => $sub->id,
                        CTC::COL_ENB         => 1,
                        DC::COL_TABLE_CREATOR => $userId,
                    ]);
                });
            } catch (\Throwable $e) {
                Log::error(
                    __CLASS__ . '::' . __FUNCTION__
                        . " failed creating COA[{$acct[CTC::COL_CD]}]: {$e->getMessage()}"
                );
            }
        }
    }

    public static function sendEmailTemplate(string $emailTemplate, array $mailTo, array $obj): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $mailTo = array_values($mailTo);
        if ($user->type != PMC::SA) {
            $template = EmailTemplate::where('name', 'LIKE', $emailTemplate)->first();
            if (!$template) {
                return ['is_success' => false, 'error' => __('Mail not send, email not found')];
            }
            $isActiveRecord = $user->type != PMC::SA
                ? UserEmailTemplate::where('template_id', $template->id)
                ->where(UC::COL_USER_ID, $user?->creatorId())->first()
                : (object)['is_active' => 1];
            if ($isActiveRecord->is_active != 1) {
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
        $user = $userOrRedirect;
        $mailTo = array_values($mailTo);
        $template = EmailTemplate::where('name', 'LIKE', $emailTemplate)->first();
        if (!$template) {
            return ['is_success' => false, 'error' => __('Mail not send, email not found')];
        }
        $isActiveRecord = UserEmailTemplate::where('template_id', $template->id)
            ->where(UC::COL_USER_ID, $user?->creatorId())->first();
        if ($isActiveRecord->is_active != 1) {
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
            'vendor_name' => '-',
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
                    ProjectsConstants::COL_PPL_NM         => 'Sales',
                    DC::COL_TABLE_CREATOR      => $createdId,
                ]);
                $stages = ['Draft', 'Sent', 'Open', 'Revised', 'Declined'];
                foreach ($stages as $order => $stageName) {
                    LeadStage::create([
                        ProjectsConstants::COL_STG_NM         => $stageName,
                        ProjectsConstants::COL_PPL_ID         => $pipeline->id,
                        ActivitiesConstants::COL_OD           => $order,
                        DC::COL_TABLE_CREATOR      => $createdId,
                    ]);
                    Stage::create([
                        ProjectsConstants::COL_STG_NM         => $stageName,
                        ProjectsConstants::COL_PPL_ID         => $pipeline->id,
                        ActivitiesConstants::COL_OD           => $order,
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
                        ActivitiesConstants::COL_PJ       => $projectId,
                        ProjectsConstants::COL_STG_NM     => $stageName,
                        ActivitiesConstants::COL_OD       => $order,
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
                        ActivitiesConstants::COL_TT        => $title,
                        ActivitiesConstants::COL_OD        => $order,
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
        try {
            DB::transaction(function () use ($creatorId, &$pipeline) {
                $pipeline = Pipeline::create([
                    ProjectsConstants::COL_PPL_NM      => 'Default Pipeline',
                    DC::COL_TABLE_CREATOR  => $creatorId,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__
                . " failed creating Pipeline: {$e->getMessage()}");
            return;
        }
        $labelData = [
            [ProjectsConstants::COL_LB_NM => 'On Hold',  ProjectsConstants::COL_CL => 'primary'],
            [ProjectsConstants::COL_LB_NM => 'New',      ProjectsConstants::COL_CL => ProjectsConstants::STT_INF],
            [ProjectsConstants::COL_LB_NM => 'Pending',  ProjectsConstants::COL_CL => ProjectsConstants::STT_WRN],
            [ProjectsConstants::COL_LB_NM => 'Loss',     ProjectsConstants::COL_CL => ProjectsConstants::STT_DGR],
            [ProjectsConstants::COL_LB_NM => 'Win',      ProjectsConstants::COL_CL => 'success'],
        ];
        try {
            DB::transaction(function () use ($labelData, $creatorId, $pipeline) {
                foreach ($labelData as $item)
                    Label::create([
                        ProjectsConstants::COL_LB_NM      => $item[ProjectsConstants::COL_LB_NM],
                        ProjectsConstants::COL_CL         => $item[ProjectsConstants::COL_CL],
                        ProjectsConstants::COL_PPL_ID     => $pipeline->id,
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
                        ActivitiesConstants::COL_TT        => $status,
                        ActivitiesConstants::COL_OD        => $order,
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
        return Employee::where(UC::COL_USER_ID, $userId)->latest()->first();
    }

    public const EMP_DTLS = 'employeeDetails';
    public static function employeeDetails(string|int $userId, string|int $createdBy): void
    {
        $user = User::find($userId);
        if (!$user) return;
        $faker = Faker::create();
        try {
            DB::transaction(function () use ($user, $createdBy, $faker) {
                $branch = Branch::create([
                    CPC::COL_BRC_NM       => $faker->company,
                    DC::COL_TABLE_CREATOR => $createdBy,
                ]);
                $department = Department::create([
                    CPC::COL_DEP_NM       => $faker->word,
                    CPC::COL_BRC_ID  => $branch->id,
                    DC::COL_TABLE_CREATOR => $createdBy,
                ]);
                $designation = Designation::create([
                    UC::COL_DSG_NM           => $faker->jobTitle,
                    CPC::COL_DEP_ID  => $department->id,
                    DC::COL_TABLE_CREATOR     => $createdBy,
                ]);
                $tax = Tax::create([
                    BillsConstants::COL_TAX_NM       => 'Tax ' . $faker->randomNumber(2),
                    BillsConstants::COL_TAX_RT       => $faker->randomFloat(2, 0, 1),
                    DC::COL_TABLE_CREATOR => $createdBy,
                ]);
                $payslipType = PayslipType::create([
                    BillsConstants::COL_PAY_SLP_NM       => $faker->randomElement(['Monthly', 'Hourly', 'Daily']),
                    DC::COL_TABLE_CREATOR => $createdBy,
                ]);
                Employee::create([
                    UC::COL_USER_ID     => $user?->id,
                    UC::COL_NM        => $user[UC::COL_NM],
                    UC::COL_EM       => $user[UC::COL_EM],
                    UC::COL_PW    => $user[UC::COL_PW],
                    UC::COL_EMP_ID => self::employeeNumber($createdBy),
                    UC::COL_BRC_ID       => $branch->id,
                    UC::COL_DEP_ID   => $department->id,
                    UC::COL_DSG_ID  => $designation->id,
                    UC::COL_TAX_ID    => $tax->id,
                    UC::COL_SLR_TP     => $payslipType->id,
                    UC::COL_SLR          => $faker->numberBetween(30000, 100000),
                    DC::COL_TABLE_CREATOR => $createdBy,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ .
                " failed creating Employee for user[{$userId}]: {$e->getMessage()}");
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
            $percentage <= 20  => ProjectsConstants::STT_DGR,
            $percentage <= 40  => ProjectsConstants::STT_WRN,
            $percentage <= 60  => ProjectsConstants::STT_INF,
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
        $period = CarbonPeriod::create($first, $seventh);
        $dates = [];
        foreach ($period as $date)
            $dates[(string)$date->format('Y-m-d')] = $date;
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
            $query->where(UC::COL_USER_ID, $user?->creatorId());
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
        return sprintf('%02d:%02d:%02d', $H, floor($H / 60), $seconds % 60);
    }

    public static function sendSlackMsg(string $slug, array $obj, ?int $userId = null): void
    {
        $template = NotificationTemplates::where('slug', $slug)->first();
        if (!$template || empty($obj)) return;
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return;
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLangs::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(UC::COL_USER_ID, $user?->id)
            ->first()
            ?: NotificationTemplateLangs::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLangs::where('parent_id', $template->id)
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

    public static function sendTelegramMsg(string $slug, array $obj, ?int $userId = null): void
    {
        $template = NotificationTemplates::where('slug', $slug)->first();
        if (!$template || empty($obj)) {
            return;
        }
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) {
            return;
        }
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLangs::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(UC::COL_USER_ID, $user?->id)
            ->first()
            ?: NotificationTemplateLangs::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLangs::where('parent_id', $template->id)
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

    public static function sendTwilioMsg(string $to, string $slug, array $obj, ?int $userId = null): void
    {
        $template = NotificationTemplates::where('slug', $slug)->first();
        if (!$template || empty($obj)) return;
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return;
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLangs::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(UC::COL_USER_ID, $user?->id)
            ->first()
            ?: NotificationTemplateLangs::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLangs::where('parent_id', $template->id)
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
            $client = new Client($sid, $token);
            $client->messages->create($to, [
                'from' => $fromNumber,
                'body' => $msg,
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " Twilio send failed: {$e->getMessage()}");
        }
    }

    public static function totalQuantity(string $type, int $quantity, int $productId): void
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

    public static function warehouseQuantity(string $type, int $quantity, int $productId, int $warehouseId): void
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

    public static function warehouseTransferQty(int $fromWarehouse, int $toWarehouse, int $productId, int $quantity, ?string $delete = null): void
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

    public static function addProductStock(int $productId, int $quantity, string $type, string $description, int $typeId): void
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
        $mode = $settings[SC::CLR_STG][SC::CST_DRK] ?? 'off';
        if ($mode === 'on')
            return SC::CPN_LG_LT_DEF;
        return SC::CPN_LG_DK_DEF;
    }

    public static function getLogo(): string
    {
        $isDark = self::getValByName(SC::CLR_STG)[SC::CST_DRK] === 'on';
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

    public static function addWarehouseStock(int $productId, int $quantity, int $warehouseId): void
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
                        UC::COL_USER_ID => Auth::id()
                    ],
                    ['quantity' => $newQty, UC::COL_USER_ID => Auth::id()]
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
        $output = new ConsoleOutput();
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
        if (self::$getRatingData === null) {
            $indicator = Indicator::where('designation', $designationId)->first();
            if ($indicator && !empty($indicator->rating) && $competencyCount > 0) {
                $ratingArray = json_decode($indicator->rating, true) ?: [];
                $starSum = array_sum($ratingArray);
                $overall = $starSum / $competencyCount;
            } else
                $overall = 0.0;
            self::$getRatingData = $overall;
        }
        return self::$getRatingData;
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

    public static function googleCalendarConfig(): void
    {
        $settings = self::settings();
        $path = storage_path($settings['google_calendar_json_file'] ?? '');
        if (!file_exists($path)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . " credentials file not found at {$path}");
            return;
        }
        config([
            'google-calendar.default_auth_profile'                      => 'service_account',
            'google-calendar.auth_profiles.service_account.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.credentials_json'      => $path,
            'google-calendar.auth_profiles.oauth.token_json'            => $path,
            'google-calendar.calendar_id'                               => $settings['google_clender_id'] ?? '',
            'google-calendar.user_to_impersonate'                       => '',
        ]);
    }

    public static function addCalendarData(object $request, string $type): void
    {
        self::googleCalendarConfig();
        try {
            DB::transaction(function () use ($request, $type) {
                $event = new GoogleEvent();
                $event->name         = $request->title;
                $event->startDateTime = Carbon::parse($request->start_date);
                $event->endDateTime  = Carbon::parse($request->end_date);
                $event->colorId      = self::colorCodeData($type);
                $event->save();
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed adding calendar event: {$e->getMessage()}");
        }
    }

    public static function getCalendarData(string $type): array
    {
        self::googleCalendarConfig();
        $events = GoogleEvent::get();
        $ColorId = (string) self::colorCodeData($type);
        $result = [];
        foreach ($events as $ev) {
            $endDate = date_create($ev->endDateTime);
            date_add($endDate, date_interval_create_from_date_string('1 days'));
            if ($ev->colorId === $ColorId)
                $result[] = [
                    'id'        => $ev->id,
                    'title'     => $ev->summary,
                    'start'     => $ev->startDateTime,
                    'end'       => date_format($endDate, 'Y-m-d H:i:s'),
                    'className' => self::$colorCode[(int)$ColorId] ?? '',
                    'allDay'    => true,
                ];
        }
        return $result;
    }

    public static function getStartEndMonthDates(): array
    {
        $month_start = Carbon::now()->startOfMonth();
        return [
            'start_date' => $month_start->toDateString(),
            'end_date' => $month_start->addMonth()->toDateString()
        ];
    }

    public static function webhookSetting(string $module, ?int $userId = null): array|bool
    {
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return false;
        $webhook = WebhookSettings::where('module', $module)
            ->where(UC::COL_USER_ID, $user?->id)
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

    public static function updateStorageLimit(int $companyId, float $imageSize): string|int
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

    public static function changeStorageLimit(int $companyId, string $filePath): bool
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

    public static function fetchLinkMessage(string $lang = DC::DEFAULT_LANG, ?string $set = 'generics', string $key, bool $isFailure = true, bool $shouldFallback = true): ?string
    {
        $startMsg = 'Undefined server message. This could mean either a failure or a success. Check with your support team about your request.';
        $resultMsg = $startMsg;
        try {
            $msgs = LangsConstants::LINK_MESSAGES;
            $canDefault = $isFailure && is_array(LangsConstants::DEFAULT_CLIENT_MESSAGES) && !empty(LangsConstants::DEFAULT_CLIENT_MESSAGES['link_not_found']);
            if (!is_array($msgs)) {
                if ($isFailure && $canDefault)
                    return LangsConstants::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Link messages are not defined properly.');
            }
            if (!array_key_exists($lang, Utility::langList()))
                $lang = self::fetchUserLang();
            if (!array_key_exists($set, $msgs)) {
                if ($isFailure && $canDefault)
                    return LangsConstants::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Set not found in link messages.');
            }
            if (!array_key_exists($lang, $msgs)) {
                if ($isFailure && $canDefault)
                    return LangsConstants::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Language not found in link messages.');
            }
            $langMsg = $msgs[$lang] ?? [];
            if (!array_key_exists($key, $langMsg)) {
                if ($isFailure && is_array(LangsConstants::DEFAULT_CLIENT_MESSAGES) && !empty(LangsConstants::DEFAULT_CLIENT_MESSAGES['link_not_found']))
                    return LangsConstants::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Key not found in link messages for the specified language.');
            }
            $resultMsg = $langMsg[$key] ?? ($shouldFallback ? null : $startMsg);
            return $resultMsg;
        } catch (\Throwable) {
            if ($shouldFallback) return null;
            return !$isFailure && !(is_string($resultMsg) && !empty($resultMsg)) ? $startMsg : 'Something went wrong! Try again later.';
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

    public static function languageCreate(?string $createdBy = DB::DEFAULT_UUID): void
    {
        foreach (self::langList() as $code => $fullName) {
            try {
                Language::firstOrCreate(
                    ['code'      => $code],
                    [
                        'full_name'         => $fullName,
                        DC::COL_TABLE_CREATOR => $createdBy,
                    ]
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

    public static function getAccountBalance(int $accountId, ?string $startDate = null, ?string $endDate = null): float|RedirectResponse
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
            ->where(UC::COL_USER_ID, $user?->creatorId())
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
            ->sum(DB::raw('price * quantity'));
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

    public static function getAccountData(int $accountId, ?string $startDate = null, ?string $endDate = null): array|RedirectResponse
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

    public static function getBalanceSheetCredit(int $accountId, ?string $startDate = null, ?string $endDate = null): float
    {
        $start = $startDate ?: date('Y-m-01');
        $end  = $endDate   ?: date('Y-m-t');
        $invoiceProducts = ProductService::where('sale_chart_account_id', $accountId)->pluck('id');
        $invoiceAmount = InvoiceProduct::whereIn('product_id', $invoiceProducts)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum(DB::raw('price * quantity'));
        $accountIds = BankAccount::where('chart_account_id', $accountId)->pluck('id');
        $invoicePaymentAmount = InvoicePayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        $revenueAmount = Revenue::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        return $invoiceAmount + $invoicePaymentAmount + $revenueAmount;
    }

    public static function getBalanceSheetDebit(int $accountId, ?string $startDate = null, ?string $endDate = null): float
    {
        $start = $startDate ?: date('Y-m-01');
        $end  = $endDate   ?: date('Y-m-t');
        $billProducts = ProductService::where('expense_chart_account_id', $accountId)->pluck('id');
        $billProductAmount = BillProduct::whereIn('product_id', $billProducts)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum(DB::raw('price * quantity'));
        $billAmount = BillAccount::where('chart_account_id', $accountId)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum('price');
        $accountIds = BankAccount::where('chart_account_id', $accountId)->pluck('id');
        $billPaymentAmount = BillPayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        $paymentAmount = Payment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');
        return $billProductAmount + $billAmount + $billPaymentAmount + $paymentAmount;
    }

    public static function trialBalance(int $accountType, string $start, string $end): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creatorId = $user?->creatorId();
        $journalItem = JournalItem::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('sum(debit) as totalDebit'),
            DB::raw('sum(credit) as totalCredit')
        )
            ->join(DC::TABLE_JOURNAL_ENTRIES, DC::TABLE_JOURNAL_ENTRIES . '.id', 'journal_items.journal')
            ->join(DC::TABLE_COAS, 'journal_items.account', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('journal_items.created_at', [$start, $end])
            ->groupBy('account')
            ->get()->toArray();
        $invoice = InvoiceProduct::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('0 as totalDebit'),
            DB::raw('sum(price * invoice_products.quantity) as totalCredit')
        )
            ->join(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS . '.id', 'invoice_products.product_id')
            ->join(DC::TABLE_COAS, DC::TABLE_PROD_SERVS . '.sale_chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('invoice_products.created_at', [$start, $end])
            ->groupBy(DC::TABLE_PROD_SERVS . '.sale_chart_account_id')
            ->get()->toArray();
        $invoicePayment = InvoicePayment::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('sum(amount) as totalDebit'),
            DB::raw('0 as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'invoice_payments.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('invoice_payments.created_at', [$start, $end])
            ->groupBy('account_id')
            ->get()->toArray();
        $revenue = Revenue::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('0 as totalDebit'),
            DB::raw('sum(amount) as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'revenues.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('revenues.created_at', [$start, $end])
            ->groupBy('chart_account_id')
            ->get()->toArray();
        $bill = BillProduct::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('sum(price * bill_products.quantity) as totalDebit'),
            DB::raw('0 as totalCredit')
        )
            ->join(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS . '.id', 'bill_products.product_id')
            ->join(DC::TABLE_COAS, DC::TABLE_PROD_SERVS . '.expense_chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('bill_products.created_at', [$start, $end])
            ->groupBy(DC::TABLE_PROD_SERVS . '.expense_chart_account_id')
            ->get()->toArray();
        $billAccount = BillAccount::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('sum(price) as totalDebit'),
            DB::raw('0 as totalCredit')
        )
            ->join(DC::TABLE_COAS, 'bill_accounts.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('bill_accounts.created_at', [$start, $end])
            ->groupBy('chart_account_id')
            ->get()->toArray();
        $billPayment = BillPayment::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('sum(amount) as totalDebit'),
            DB::raw('0 as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'bill_payments.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('bill_payments.created_at', [$start, $end])
            ->groupBy('account_id')
            ->get()->toArray();
        $payments = Payment::select(
            DC::TABLE_COAS . '.id',
            DC::TABLE_COAS . '.code',
            DC::TABLE_COAS . '.name',
            DB::raw('sum(amount) as totalDebit'),
            DB::raw('0 as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'payments.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('payments.created_at', [$start, $end])
            ->groupBy('account_id')
            ->get()->toArray();
        if (!empty($billPayment) && !empty($invoicePayment))
            for ($i = 0; $i < count($invoicePayment); $i++)
                $invoicePayment[$i]['totalDebit'] -= $billPayment[$i]['totalDebit'] ?? 0;
        return array_merge($invoice, $journalItem, $revenue, $bill, $billAccount, $payments, $invoicePayment);
    }

    public static function smtpDetail(int $userId): array
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
        $settings = self::settingsById(1);
        if (empty($settings)) {
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