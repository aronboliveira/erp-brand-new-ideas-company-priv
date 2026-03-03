<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    SettingsConstants
};
use App\Models\{Customer, Language, User, Utility, Vendor};
use App\Traits\ChecksPermissions;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Cache, DB, File, Log, Redirect, View as ViewFacade};
use Illuminate\View\View;
use function App\Http\Controllers\defaultUndefinedException;

class LanguageController extends Controller
{
    use ChecksPermissions;

    private const ROUTE_INDEX = 'languages.manage';

    public function changeLanquage(Request $request, string $lang): RedirectResponse
    {
        // typo version kept for compatibility
        return $this->changeLanguage($request, $lang);
    }

    public const CHG_LNG = 'changeLanguage';
    public function changeLanguage(Request $request, string $lang): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $lang, $action, $method) {
            $startOverall = microtime(true);
            try {
                $startGuard = microtime(true);
                if (($r = self::guard($request, 'change language', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                $user = $request->user();
                Log::info("[$action] called", ['user_id' => $user?->id, 'lang' => $lang]);
                $startUpdate = microtime(true);
                $user->update(['lang' > $lang]);
                $this->logExecutionTime($startUpdate, "{$action} updateLang", 'completed');
                $rtlValue = in_array($lang, ['ar', 'he']) ? 'on' : 'off';
                $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
                $startDB = microtime(true);
                DB::transaction(fn() => DB::insert(
                    'insert into settings (`value`,`name`,`' . $creatorCol . '`) values (?,?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [$rtlValue, 'SITE_RTL', $user?->creatorId()]
                ));
                $this->logExecutionTime($startDB, "{$action} dbTransaction", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                $id = $user?->id;
                $cookie = null;
                if ($id) {
                    Cache::put("user_{$id}_lang", $lang, 3600);
                    $cookie = cookie()->forever('LANGUAGE', $lang);
                }
                return $cookie ? Redirect::back()->withCookie($cookie)->with('success', __('Language changed successfully.')) : Redirect::back()->with('success', __('Language changed successfully.'));
            } catch (\Throwable $e) {
                Log::error("[$action] error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_INDEX));
            }
        }, ['req' => $request]);
    }

    public const MNG_LNG = 'manageLanguage';
    public function manageLanguage(Request $request, string $lang): View|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $lang, $action) {
            $startOverall = microtime(true);
            $startGuard = microtime(true);
            if (($r = self::guard($request, 'manage language', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
            $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
            $currentLang = $lang ?: DatabaseConstants::DEFAULT_LANG;
            Log::warning($currentLang);
            try {
                $startFetchLangs = microtime(true);
                $languages = Language::pluck('full_name', 'code');
                $this->logExecutionTime($startFetchLangs, "{$action} fetchLanguages", 'completed');
            } catch (\Throwable $e) {
                Log::error("[$action] failed fetch languages", ['error' => $e->getMessage()]);
                $languages = collect();
            }
            try {
                $startLoadSettings = microtime(true);
                $settings = Utility::settings() ?: [];
                $this->logExecutionTime($startLoadSettings, "{$action} loadSettings", 'completed');
            } catch (\Throwable $e) {
                Log::error("[$action] failed load settings", ['error' => $e->getMessage()]);
                $disabled = [];
            }
            if (!empty($settings[SettingsConstants::DSB_LNG] ?? null)) try {
                $disabled = explode(',', $settings[SettingsConstants::DSB_LNG]);
            } catch (\Throwable $e) {
                Log::error("[$action] failed parse disabled langs", ['error' => $e->getMessage()]);
            };
            try {
                $startLoadLabel = microtime(true);
                $baseDir = base_path("resources/lang/{$currentLang}");
                $labelFilePath = is_dir($baseDir) ? "{$baseDir}.json" : base_path('resources/lang/en.json');
                Log::warning($labelFilePath);
                $raw = file_get_contents($labelFilePath) ?: '{}';
                $labelFile = json_decode($raw, true) ?: [];
                $this->logExecutionTime($startLoadLabel, "{$action} loadLabelFile", 'completed');
            } catch (\Throwable $e) {
                Log::error("[$action] failed load label file", ['path' => $labelFilePath, 'error' => $e->getMessage()]);
                $labelFile = [];
            }
            try {
                $startLoadMsgs = microtime(true);
                $messages = [];
                if (is_dir($baseDir)) {
                    foreach (array_diff(scandir($baseDir), ['.', '..']) as $file) if (str_ends_with($file, '.php')) {
                        $key = basename($file, '.php');
                        $data = include "{$baseDir}/{$file}";
                        if (is_array($data)) $messages[$key] = $data;
                    }
                }
                $id = auth()->id();
                $cookie = null;
                if ($id) {
                    Cache::put("user_{$id}_lang", $currentLang, 3600);
                    $cookie = cookie()->forever('LANGUAGE', $currentLang);
                }
                Log::info("[$action] rendering view");
                $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                $data = [
                    DatabaseConstants::TABLE_LANGS => $languages,
                    'currentLang' => $currentLang,
                    'labelFile' => $labelFile,
                    'messages' => $messages,
                    'disabledLangs' => $disabled,
                    'settings' => $settings
                ];
                Log::warning("[$action] rendering view", ['view' => $viewName]);
                return $cookie ? response()->view($viewName, $data)->withCookie($cookie) : view($viewName, $data);
            } catch (\Throwable $e) {
                Log::error("[$action] failed load message files", ['error' => $e->getMessage()]);
                $viewName = 'lang.index';
                if (!ViewFacade::exists($viewName)) return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
            }
        }, ['req' => $request]);
    }

    public const STR_LNG_DT = 'storeLanguageData';
    public function storeLanguageData(Request $request, string $currentLang): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $currentLang, $action, $method) {
            $startOverall = microtime(true);
            try {
                $startGuard = microtime(true);
                if (($r = self::guard($request, 'manage language', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                Log::info("[$action] start", ['lang' => $currentLang, 'input' => $request->all()]);
                $startDir = microtime(true);
                $baseDir = base_path('resources/lang');
                !is_dir($baseDir) && mkdir($baseDir, 0755, true);
                $this->logExecutionTime($startDir, "{$action} ensureBaseDir", 'completed');
                $safeLang = preg_replace('/[^a-zA-Z0-9_-]/', '', $currentLang);
                $jsonPath = "{$baseDir}/{$safeLang}.json";
                if (!empty($request->label)) file_put_contents($jsonPath, json_encode($request->label));
                $startJson = microtime(true);
                $this->logExecutionTime($startJson, "{$action} writeJson", 'completed');
                $langDir = "{$baseDir}/{$safeLang}";
                $startLangDir = microtime(true);
                !is_dir($langDir) && mkdir($langDir, 0755, true);
                $this->logExecutionTime($startLangDir, "{$action} ensureLangDir", 'completed');
                if (!empty($request->message)) {
                    foreach ($request->message as $fileName => $fileData) {
                        $startFile = microtime(true);
                        $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', basename($fileName));
                        if ($safeFileName === '') continue;
                        $content = "<?php return [" . $this->buildArray($fileData) . "];";
                        file_put_contents("{$langDir}/{$safeFileName}.php", $content);
                        $this->logExecutionTime($startFile, "{$action} writeMessageFile", 'completed');
                    }
                }
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return Redirect::route(self::ROUTE_INDEX, [$currentLang])->with('success', __('Language saved successfully.'));
            } catch (\Throwable $e) {
                Log::error("[$action] error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_INDEX));
            }
        }, ['req' => $request]);
    }

    public const BD_ARR = 'buildArray';
    public function buildArray(array $data): string
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($data, $action) {
            $startOverall = microtime(true);
            try {
                $content = '';
                foreach ($data as $label => $value) {
                    $startLoop = microtime(true);
                    if (is_array($value))
                        $content .= "'{$label}'=>[" . $this->buildArray($value) . "],";
                    else {
                        $escaped = addslashes((string)$value);
                        $content .= "'{$label}'=>'{$escaped}',";
                    }
                    $this->logExecutionTime($startLoop, "{$action} processItem", 'completed');
                }
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return $content;
            } catch (\Throwable $e) {
                Log::error("[$action] error building array", ['error' => $e->getMessage()]);
                return '';
            }
        }, []);
    }

    public const CR_LNG = 'createLanguage';
    public function createLanguage(): View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($action, $method) {
            $startOverall = microtime(true);
            try {
                $viewName = 'lang.create';
                if (!ViewFacade::exists($viewName))
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewName} not found!");
                Log::info("[$action] rendering create language view");
                $this->logExecutionTime($startOverall, "{$action} renderView", 'completed');
                return view($viewName);
            } catch (\Throwable $e) {
                Log::error("[$action] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, "{$method}");
            }
        }, []);
    }

    public const STR_LNG = 'storeLanguage';
    public function storeLanguage(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $startOverall = microtime(true);
            try {
                $startGuard = microtime(true);
                if (($r = self::guard($request, 'create language', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                Log::info("[{$action}] start", ['input' => $request->all()]);
                $startProcess = microtime(true);
                $code = preg_replace('/[^a-z0-9_-]/', '', strtolower($request->input('code')));
                if ($code === '') return Redirect::route(self::ROUTE_INDEX)->with('error', __('Invalid language code.'));
                $fullName = $request->input('full_name');
                $baseDir = base_path('resources/lang');
                !is_dir($baseDir) && mkdir($baseDir, 0755, true);
                File::copy("{$baseDir}/en.json", "{$baseDir}/{$code}.json");
                $langDir = "{$baseDir}/{$code}";
                !is_dir($langDir) && mkdir($langDir, 0755, true);
                if (!Language::where('code', $code)->orWhere('full_name', $fullName)->exists()) Language::create(['code' => $code, 'full_name' => $fullName, DatabaseConstants::COL_TABLE_CREATOR => $request->user()?->creatorId()]);
                File::copyDirectory("{$baseDir}/en", $langDir);
                $this->logExecutionTime($startProcess, "{$action} processFiles", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return Redirect::route(self::ROUTE_INDEX, [$code])->with('success', __('Language successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$action}] error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_INDEX));
            }
        }, ['req' => $request]);
    }

    public const DEL_LNG = 'destroyLang';
    public function destroyLang(Request $request, string $lang): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $lang, $action, $method) {
            $startOverall = microtime(true);
            try {
                $startGuard = microtime(true);
                if (($r = self::guard($request, 'delete language', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                Log::info("[{$action}] start", ['lang' => $lang]);
                $default = env(SettingsConstants::DEF_LNG, DatabaseConstants::DEFAULT_LANG);
                $baseDir = base_path('resources/lang');
                $dir = "{$baseDir}/{$lang}";
                if (is_dir($dir)) Utility::deleteDirectory($dir);
                File::delete("{$baseDir}/{$lang}.json");
                User::where('lang', $lang)->update(['lang' => $default]);
                Customer::where('lang', $lang)->update(['lang' => $default]);
                Vendor::where('lang', $lang)->update(['lang' => $default]);
                Language::where('code', $lang)->delete();
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return Redirect::route(self::ROUTE_INDEX, [$default])->with('success', __('Language deleted successfully.'));
            } catch (\Throwable $e) {
                Log::error("[{$action}] error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "{$method}", route(self::ROUTE_INDEX));
            }
        }, ['req' => $request]);
    }

    public const DSB_LNG = 'disableLang';
    public function disableLang(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            $startOverall = microtime(true);
            try {
                $startGuard = microtime(true);
                if (($r = self::guard($request, 'manage language', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['status' => 'error', 'message' => __('Permission denied.')], 401);
                $this->logExecutionTime($startGuard, "{$action} guardCheck", 'completed');
                Log::info("[{$action}] start", ['input' => $request->all()]);
                $startProcess = microtime(true);
                $settings = Utility::settings();
                $disabled = empty($settings[SettingsConstants::DSB_LNG] ?? null) ? [] : explode(',', $settings[SettingsConstants::DSB_LNG]);
                $langToToggle = $request->input('lang');
                if ($request->input('mode') === 'off') {
                    $disabled[] = $langToToggle;
                    $message = __('Language disabled successfully');
                } else {
                    $disabled = array_values(array_diff($disabled, [$langToToggle]));
                    $message = __('Language enabled successfully');
                }
                $value = implode(',', $disabled);
                $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
                DB::insert('insert into settings (`value`,`name`,`' . $creatorCol . '`) values (?,?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$value, SettingsConstants::DSB_LNG, $request->user()->creatorId()]);
                $this->logExecutionTime($startProcess, "{$action} processSettings", 'completed');
                $this->logExecutionTime($startOverall, "{$action} completed", 'completed');
                return response()->json(['status' => 'success', 'message' => $message], 200);
            } catch (\Throwable $e) {
                Log::error("[{$action}] failed", ['err' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => __('An unexpected error occurred.')], 500);
            }
        }, ['req' => $request]);
    }
}
