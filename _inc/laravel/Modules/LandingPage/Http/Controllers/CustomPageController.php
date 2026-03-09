<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    SettingsConstants as SC,
    UsersConstants as UC,
    ViewsConstants as VW,
};
use App\Http\Controllers\Abstracts\Controller as AppController;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Models\User;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\{Collection, Str};
use Illuminate\View\View;
use Modules\LandingPage\{Config\Constants\RoutesResourcesConstants as RRC, Entities\LandingPageSetting};
use Modules\LandingPage\Config\Constants\SettingsConstants as LPSC;

class CustomPageController extends AppController
{
    use ChecksLogin, ChecksPermissions;

    public const CRT = 'create';
    public const EDT = 'edit';
    public const DEL = 'destroy';
    public const STR = 'store';
    private const LP = RRC::LP;
    private const MB = 'menubar';
    private const REDIRECT_INDEX = RRC::HM . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            // ? instanceof has higher precedence than =, so me must use parentheses
            if (!(($userOrRedirect = self::_checkLogin()) instanceof User)) {
                Log::notice("{$action} • unauthenticated, redirecting");
                return $userOrRedirect instanceof RedirectResponse ? $userOrRedirect : redirect('/login');
            }
            $user = $userOrRedirect;
            if ($user->type !== 'super admin') {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            if (($redirect = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX)) !== true) {
                Log::warning("{$action} • permission denied, redirecting");
                return $redirect;
            }
            Log::info("{$action} • started", [UC::COL_USER_ID => $user->id]);
            try {
                $settings = LandingPageSetting::landingPageSetting();
                $pages    = json_decode($settings[self::MB . '_page'], true);
                // $pages = is_array($pages) ? usort($pages, fn($a, $b) => $b['created_at'] <=> $a['created_at']) : [];
                $view = self::getFirstExistingView(self::MB . '.' . $action);
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::MB . '.' . $action]);
                    throw new \RuntimeException("View not found: " . self::MB . '.' . $action);
                }
                Log::debug("[$action] resolved view", ['view' => $view]);
                return view($view, compact('pages', DC::TABLE_SETTINGS));
            } catch (\Throwable $e) {
                Log::error("{$action} • failed", [
                    UC::COL_USER_ID => $user->id,
                    'error'                    => $e->getMessage()
                ]);
                return defaultUndefinedException(
                    $request,
                    $e,
                    $action,
                    route(self::REDIRECT_INDEX)
                );
            }
        });
    }

    public function show(Request $request, int|string $key): View|RedirectResponse|null
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $key, $function) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse)
                return $userOrRedirect;
            $user = $userOrRedirect;
            $guardStart = microtime(true);
            $redirect = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($redirect instanceof RedirectResponse) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id, 'key' => $key]);
                Log::debug("[$action] lacks MNG_LP permission", ['user_id' => $user?->id, 'key' => $key]);
                return $redirect;
            }
            Log::info("[$action] started", ['user_id' => $user?->id, 'key' => $key]);
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $pagesStart = microtime(true);
                $pages = json_decode($settings[self::MB . '_page'] ?? '[]', true) ?: [];
                $this->logExecutionTime($pagesStart, $action . '::decodePages', 'completed');
                if (!isset($pages[$key])) {
                    Log::warning("[$action] page not found", ['user_id' => $user?->id, 'key' => $key]);
                    Log::debug("[$action] available pages keys", ['pages' => array_keys($pages)]);
                    return redirect()->back()->with('error', __('Page not found'));
                }
                $page = $pages[$key];
                Log::info("[$action] succeeded", ['user_id' => $user?->id, 'key' => $key]);
                return view(self::LP . '::' . self::LP . '.' . $function, compact('page', DC::TABLE_SETTINGS));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['user_id' => $user?->id, 'key' => $key, 'error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['key' => $key]);
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
        return $this->measureProfile($method, function () use ($request, $method) {
            try {
                $stepStart = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return $userOrRedirect;
                $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
                $stepStart = microtime(true);
                $user = $userOrRedirect;
                if (($redirect = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX)) !== true)
                    return $redirect;
                $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
                Log::info($method . ' - initializing create', ['user_id' => $user?->id]);
                Log::debug($method . ' - view params', ['LP' => self::LP, 'MB' => self::MB, 'method' => $method]);
                $stepStart = microtime(true);
                $view = self::getFirstExistingView(self::MB . '.' . explode("::", $method)[1]);
                $this->logExecutionTime($stepStart, 'findView', 'completed');
                if (!$view) {
                    Log::warning($method . ' - view not found', ['attempted' => self::MB . '.' . explode("::", $method)[1]]);
                    throw new \RuntimeException("View not found: " . self::MB . '.' . explode("::", $method)[1]);
                }
                $this->logExecutionTime($stepStart, 'renderCreateView', 'completed');
                Log::info($method . ' - succeeded', ['user_id' => $user?->id]);
                return view($view);
            } catch (\Throwable $e) {
                Log::error($method . ' - failed', [
                    'error' => $e->getMessage(),
                    'uri'   => $request->getRequestUri(),
                    'ip'    => $request->ip()
                ]);
                return defaultUndefinedException(
                    $request,
                    $e,
                    $method,
                    route(self::REDIRECT_INDEX)
                );
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function store(Request $request): RedirectResponse|bool
    {
        $action = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) {
                Log::notice("{$action} • unauthenticated, redirecting");
                return $ur;
            }
            $user = $ur;
            if ($g = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX) !== true) {
                Log::warning("{$action} • access denied, redirecting");
                return $g;
            }
            Log::info("{$action} • started", [UC::COL_USER_ID => $user->id]);
            $settings = LandingPageSetting::settings();
            $pages    = json_decode($settings[self::MB . '_page'], true);
            $v        = $request->validate([
                self::MB . '_page_name'    => 'nullable|string',
                self::MB . '_page_content' => 'nullable|string',
                'template_name'            => 'required|string',
                'page_url'                 => 'nullable|url',
            ]);
            $slug = Str::slug($v[self::MB . '_page_name'], '_');
            $item = [
                self::MB . 'PageName'    => $v[self::MB . '_page_name'],
                self::MB . 'PageContent' => $v['template_name'] === 'page_url' ? '' : $v[self::MB . '_page_content'],
                'pageSlug'               => $slug,
                'templateName'           => $v['template_name'],
                'pageUrl'                => $v['template_name'] === 'page_url' ? $v['page_url'] : '',
            ];
            foreach (['header', 'footer', 'login'] as $flag) {
                $item[$flag] = $request->has($flag) ? 'on' : 'off';
            }
            $pages[] = $item;
            try {
                DB::transaction(fn() => LandingPageSetting::updateOrCreate(
                    ['name'  => self::MB . '_page'],
                    ['value' => json_encode($pages)]
                ));
                Log::info("{$action} • succeeded", [UC::COL_USER_ID => $user->id]);
                return redirect()->back()->with('success', __('Page added successfully'));
            } catch (\Throwable $e) {
                Log::error("{$action} • failed", [
                    UC::COL_USER_ID => $user->id,
                    'error'                    => $e->getMessage(),
                ]);
                return defaultUndefinedException(
                    $request,
                    $e,
                    $action,
                    route(self::REDIRECT_INDEX)
                );
            }
        });
    }

    public function edit(Request $request, int|string $key): View|RedirectResponse|null
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $key, $function) {
            $checkStart = microtime(true);
            $ur = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;
            $guardStart = microtime(true);
            $g = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($g instanceof RedirectResponse) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id, 'key' => $key]);
                Log::debug("[$action] lacks MNG_LP permission", ['user_id' => $user?->id, 'key' => $key]);
                return $g;
            }
            Log::info("[$action] started", ['user_id' => $user?->id, 'key' => $key]);
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $pagesStart = microtime(true);
                $pages = json_decode($settings[self::MB . '_page'] ?? '[]', true) ?: [];
                $this->logExecutionTime($pagesStart, $action . '::decodePages', 'completed');
                if (!isset($pages[$key])) {
                    Log::warning("[$action] page not found", ['user_id' => $user?->id, 'key' => $key]);
                    Log::debug("[$action] available pages keys", ['pages' => array_keys($pages)]);
                    return redirect()->back()->with('error', __('Page not found'));
                }
                $page = $pages[$key];
                $view = self::getFirstExistingView(self::MB . '.' . $function);
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::MB . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::MB . '.' . $function);
                }
                Log::info("[$action] succeeded", ['user_id' => $user?->id, 'key' => $key]);
                return view($view, compact('page', 'key'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['user_id' => $user?->id, 'key' => $key, 'error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['key' => $key]);
    }

    public function update(Request $request, int|string $key): RedirectResponse|bool
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => $request->user()?->id, 'key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            $stepStart = microtime(true);
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            $user = $ur;
            if (($g = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX)) !== true) return $g;
            $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
            Log::info($method . ' - starting update', ['user_id' => $user->id, 'key' => $key]);
            $stepStart = microtime(true);
            $settings = LandingPageSetting::settings();
            $this->logExecutionTime($stepStart, 'fetchSettings', 'completed');
            $stepStart = microtime(true);
            $pages = json_decode($settings[self::MB . '_page'], true);
            $this->logExecutionTime($stepStart, 'decodePages', 'completed');
            $stepStart = microtime(true);
            $v = $request->validate([
                self::MB . '_page_name' => 'required|string',
                self::MB . '_page_content' => 'nullable|string',
                'template_name' => 'required|string',
                'page_url' => 'nullable|url'
            ]);
            $this->logExecutionTime($stepStart, 'validation', 'completed');
            $stepStart = microtime(true);
            $slug = Str::slug($v[self::MB . '_page_name'], '_');
            $item = [
                self::MB . 'PageName' => $v[self::MB . '_page_name'],
                self::MB . 'PageContent' => $v['template_name'] === 'page_url' ? '' : $v[self::MB . '_page_content'],
                'pageSlug' => $slug,
                'templateName' => $v['template_name'],
                'pageUrl' => $v['template_name'] === 'page_url' ? $v['page_url'] : '',
            ];
            foreach (['header', 'footer', 'login'] as $flag) $item[$flag] = $request->has($flag) ? 'on' : 'off';
            $this->logExecutionTime($stepStart, 'buildItem', 'completed');
            $pages[$key] = $item;
            try {
                $stepStart = microtime(true);
                DB::transaction(fn() => LandingPageSetting::updateOrCreate(
                    ['name' => self::MB . '_page'],
                    ['value' => json_encode($pages)]
                ));
                $this->logExecutionTime($stepStart, 'dbTransaction', 'completed');
                Log::info($method . ' - succeeded', ['user_id' => $user->id, 'key' => $key]);
                return redirect()->back()->with('success', __('Page updated successfully'));
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'user_id' => $user->id, 'key' => $key]);
                Log::error($method . ' - update failed', ['user_id' => $user->id, 'key' => $key]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['user_id' => $request->user()?->id, 'key' => $key]);
    }

    public function destroy(Request $request, int|string $key): RedirectResponse|bool
    {
        $action = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $key, $action) {
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) {
                Log::notice("{$action} • unauthenticated access", ['key' => $key]);
                return $ur;
            }
            $user = $ur;
            if (($g = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX)) !== true) {
                Log::warning("{$action} • authorization failed", ['user_id' => $user->id, 'key' => $key]);
                return $g;
            }
            Log::info("{$action} • started", ['user_id' => $user->id, 'key' => $key]);
            $settings = LandingPageSetting::settings();
            $pages    = json_decode($settings[self::MB . '_page'], true);
            if (!array_key_exists($key, $pages)) {
                Log::warning("{$action} • invalid key", ['user_id' => $user->id, 'key' => $key]);
                return redirect()->back()->withErrors(['key' => __('Invalid page key')]);
            }
            unset($pages[$key]);
            try {
                DB::transaction(fn() => LandingPageSetting::updateOrCreate(
                    ['name'  => self::MB . '_page'],
                    ['value' => json_encode($pages)]
                ));
                Log::info("{$action} • succeeded", ['user_id' => $user->id, 'key' => $key]);
                return redirect()->back()->with('success', __('Page deleted successfully'));
            } catch (\Throwable $e) {
                $errCtx = ['exception' => get_class($e), 'message' => $e->getMessage(), 'user_id' => $user->id, 'key' => $key];
                Log::error("{$action} • failed", $errCtx);
                Log::channel(SC::ERR_TRACE)->debug("{$action} • failed", array_merge(
                    $errCtx,
                    ['trace' => $e->getTraceAsString()]
                ));
                return defaultUndefinedException(
                    $request,
                    $e,
                    $action,
                    route(self::REDIRECT_INDEX)
                );
            }
        });
    }

    public const CT_STR = 'customStore';
    public function customStore(Request $request): RedirectResponse|bool
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            $checkStart = microtime(true);
            $ur = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;
            $guardStart = microtime(true);
            $g = self::guard($request, PMC::MNG_LP, self::REDIRECT_INDEX);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($g instanceof RedirectResponse) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id]);
                Log::debug("[$action] lacks MNG_LP permission", ['user_id' => $user?->id]);
                return $g;
            }
            Log::info("[$action] started", ['user_id' => $user?->id]);
            $data = [];
            if ($request->hasFile('site_logo')) {
                $file = $request->file('site_logo');
                $name = 'site_logo.' . $file->getClientOriginalExtension();
                $dir = 'uploads/landing_page_image';
                $fileStart = microtime(true);
                $path = LandingPageSetting::uploadFile($request, 'site_logo', $name, $dir, []);
                $this->logExecutionTime($fileStart, $action . '::uploadFile', 'completed');
                if ($path['flag'] !== 1) {
                    Log::error("[$action] file upload failed", ['flag' => $path['flag'], 'msg' => $path['msg']]);
                    Log::debug("[$action] debug upload details", ['path' => $path]);
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $data['site_logo'] = $name;
            }
            $data[LPSC::SD_K] = $request->input(LPSC::SD_K, '');
            try {
                $dbStart = microtime(true);
                DB::transaction(fn() => collect($data)->each(fn($v, $k) => LandingPageSetting::updateOrCreate(['name' => Str::snake($k)], ['value' => $v])));
                $this->logExecutionTime($dbStart, $action . '::transaction', 'completed');
                Log::info("[$action] succeeded", ['user_id' => $user?->id]);
                Log::debug("[$action] debug saved data", ['data' => $data]);
                return redirect()->back()->with('success', __('Settings saved successfully'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['user_id' => $user?->id, 'error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, []);
    }

    public const CT_PG = 'customPage';
    public const IDX = 'index';
    public const SHW = 'show';
    public const UPD = 'update';

    /**
     * Known static page slugs that have dedicated partial views.
     * When the DB has no menubar_page data, these slugs are rendered
     * from their Blade partials instead of aborting with 404.
     */
    private const STATIC_PAGE_PARTIALS = [
        'about_us'             => 'landingpage::partials.about_us',
        'privacy_policy'       => 'landingpage::partials.privacy_policy',
        'terms_and_conditions' => 'landingpage::partials.terms_and_conditions',
    ];

    public function customPage(Request $request, string $slug): View|RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['path' => $request->path(), 'slug' => $slug, 'query' => $request->query()]);
        return $this->measureProfile($method, function () use ($request, $slug, $method) {
            $stepStart = microtime(true);
            $settings = LandingPageSetting::settings();
            $this->logExecutionTime($stepStart, 'loadSettings', 'completed');
            Log::debug($method . ' - loaded settings', ['settings_keys' => array_keys($settings)]);
            $stepStart = microtime(true);
            $pages = json_decode($settings[self::MB . '_page'] ?? '[]', true);
            $this->logExecutionTime($stepStart, 'decodePages', 'completed');
            if (!is_array($pages)) {
                Log::debug($method . ' - raw pages JSON invalid, treating as empty', ['raw' => $settings[self::MB . '_page'] ?? null]);
                $pages = [];
            }
            $stepStart = microtime(true);
            // Try to find the page in DB-stored menubar_page JSON
            foreach ($pages as $page) {
                try {
                    if (($page[LPSC::PG_SLG] ?? '') === $slug) {
                        Log::info($method . ' - rendering custom page', ['slug' => $slug, 'title' => $page['page_title'] ?? null]);
                        $view = self::getFirstExistingView(VW::SET_LOS . '.' . strtolower(explode('::', $method)[1]));
                        if (!$view) {
                            Log::warning($method . ' - view not found', ['attempted' => VW::SET_LOS . '.' . strtolower(explode('::', $method)[1])]);
                            throw new \RuntimeException("View not found: " . VW::SET_LOS . '.' . strtolower(explode('::', $method)[1]));
                        }
                        $this->logExecutionTime($stepStart, 'findPage', 'completed');
                        return view($view, compact('page', DC::TABLE_SETTINGS));
                    }
                } catch (\Throwable $e) {
                    Log::error($method . ' - exception during page iteration', ['error' => $e->getMessage(), 'page' => $page]);
                    continue;
                }
            }
            $this->logExecutionTime($stepStart, 'findPage', 'completed');
            // Fallback: check if this is a known static page with a dedicated partial view
            if (isset(self::STATIC_PAGE_PARTIALS[$slug])) {
                Log::info($method . ' - rendering static page in layout', ['slug' => $slug]);
                // Build a synthetic $page array so the custompage layout can render header/footer/CSS
                $page = [
                    LPSC::MB_PG_NM => __(ucwords(str_replace('_', ' ', $slug))),
                    LPSC::MB_PG_CT => '', // empty content — layout will fall through to @include partial
                    LPSC::PG_SLG   => $slug,
                    'template_name' => 'page_content',
                    'page_url'     => '',
                    'header'       => 'on',
                    'footer'       => 'on',
                    'login'        => 'on',
                ];
                return view('landingpage::layouts.custompage', compact('page', DC::TABLE_SETTINGS));
            }
            Log::warning($method . ' - page not found', ['slug' => $slug]);
            abort(404, __('Page not found'));
        }, ['path' => $request->path(), 'slug' => $slug]);
    }
}
