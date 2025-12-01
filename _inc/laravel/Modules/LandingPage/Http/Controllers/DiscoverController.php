<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants
};
use App\Http\Controllers\Controller as AppController;
use App\Models\User;
use App\Traits\ChecksLogin;
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Modules\LandingPage\{
    Config\Constants\RoutesResourcesConstants as RRC,
    Config\Constants\SettingsConstants as LPC,
    Entities\LandingPageSetting
};

class DiscoverController extends AppController
{
    use ChecksLogin;

    public const ENTITY = RRC::DV;
    private const ROUTE_INDEX = self::ENTITY . '.index';
    private const LP = RRC::LP;

    /**
     * Display discover settings.
     */
    public function index(Request $request): Renderable|RedirectResponse
    {
        $function = __FUNCTION__;
        $action = __METHOD__;
        return $this->measureProfile(__METHOD__, function () use ($request, $function, $action) {
            $userId = '#UNAUTHENTICATED';
            if (($ur = self::_checkLogin(haltRedirect: true)) instanceof User) {
                $user    = $ur;
                $userId  = $user->id;
                Log::debug("{$action} • authenticated user", ['user_id' => $userId]);
            }
            try {
                Log::debug("{$action} • retrieving landing page settings", ['user_id' => $userId]);
                $settings = LandingPageSetting::landingPageSetting();
                $discover_of_features = json_decode(!empty($settings[LPC::DC_OF_FTS_K]) ? $settings[LPC::DC_OF_FTS_K] : (!empty(RRC::DV) ? RRC::DV : '[]'), true);
                if (!empty($discover_of_features) && is_array($discover_of_features)) {
                    if (count($discover_of_features) === 1 && isset($discover_of_features[0]) && is_array($discover_of_features[0])) {
                        $firstElement = $discover_of_features[0];
                        $firstKey = array_key_first($firstElement);
                        if ($firstKey && !is_numeric($firstKey))
                            $discover_of_features = $firstElement;
                    }
                } else $discover_of_features = [];
                $discover_of_features = is_array($discover_of_features) && !empty($discover_of_features) ? collect($discover_of_features)->sortByDesc('created_at')->toArray() : [];
                $view = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning("{$action} • view not found", ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                Log::info("{$action} • rendering view", ['features_count' => count($discover_of_features), 'features' => $discover_of_features, 'user_id' => $userId]);
                return view(
                    $view,
                    [
                        DatabaseConstants::TABLE_SETTINGS => $settings,
                        'discover_of_features' => $discover_of_features,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error("{$action} • failed", [
                    'user_id'   => $userId,
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                ]);
                return defaultUndefinedException(
                    $request,
                    $e,
                    $action,
                    route(self::ROUTE_INDEX)
                );
            }
        });
    }

    /**
     * Display a single discover feature.
     */
    public function show(int|string $id): Renderable|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $id) {
            try {
                $checkStart = microtime(true);
                $ur = self::_checkLogin(haltRedirect: true);
                $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
                if ($ur instanceof User) $user = $ur;
                Log::info("[$action] access attempt", ['key' => $id, 'user_id' => $user?->id ?? '#UNAUTHENTICATED']);
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $decodeStart = microtime(true);
                $features = json_decode($settings[LPC::DC_OF_FTS_K] ?? '[]', true) ?: [];
                $this->logExecutionTime($decodeStart, $action . '::decodeFeatures', 'completed');
                if (!isset($features[$id])) {
                    Log::warning("[$action] invalid key", ['key' => $id]);
                    Log::debug("[$action] available keys", ['keys' => array_keys($features)]);
                    return redirect()->route(self::ROUTE_INDEX)->with('error', __('Feature not found.'));
                }
                $feature = $features[$id];
                $key = $id;
                $view = self::getFirstExistingView(self::ENTITY . '.show');
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::ENTITY . '.show']);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.show');
                }
                Log::info("[$action] rendering feature", ['key' => $key]);
                return view($view, compact('feature', 'key'));
            } catch (\Throwable $e) {
                Log::error("[$action] Error in {$action}", ['error' => $e->getMessage(), 'key' => $id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                throw $e;
            }
        }, ['key' => $id]);
    }

    /**
     * Show create form.
     */
    public function create(Request $request): Renderable|RedirectResponse
    {
        $method = __METHOD__;
        $function = __FUNCTION__;
        Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
        return $this->measureProfile($method, function () use ($request, $method, $function) {
            try {
                $stepStart = microtime(true);
                if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
                $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
                Log::info($method . ' - rendering create view', ['user_id' => $ur?->id]);
                $stepStart = microtime(true);
                $view = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning($method . ' - view not found', ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                $this->logExecutionTime($stepStart, 'renderCreateView', 'completed');
                return view($view);
            } catch (\Throwable $e) {
                Log::error($method . ' - failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception trace', ['trace' => $e->getTraceAsString()]);
                throw $e;
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    /**
     * Store global discover settings.
     */
    public function store(Request $request): RedirectResponse
    {
        $method = __METHOD__;
        return $this->measureProfile(__FUNCTION__, function () use ($request, $method) {
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $payload = $request->validate([
                static::ENTITY . '_heading'     => 'string|nullable',
                static::ENTITY . '_description' => 'string|nullable',
                static::ENTITY . '_live_demo_link' => 'url|nullable',
                static::ENTITY . '_buy_now_link'  => 'url|nullable'
            ]);
            DB::beginTransaction();
            try {
                $update = [
                    static::ENTITY . '_status'      => 'on',
                    static::ENTITY . '_heading'     => $payload[static::ENTITY . '_heading']     ?? '',
                    static::ENTITY . '_description' => $payload[static::ENTITY . '_description'] ?? '',
                    static::ENTITY . '_live_demo_link' => $payload[static::ENTITY . '_live_demo_link'] ?? '',
                    static::ENTITY . '_buy_now_link'  => $payload[static::ENTITY . '_buy_now_link']   ?? ''
                ];
                foreach ($update as $name => $value)
                    LandingPageSetting::updateOrCreate(['name' => $name], ['value' => $value, DatabaseConstants::COL_TABLE_CREATOR => $user?->id]);
                $commitTime = microtime(true);
                DB::commit();
                $this->logExecutionTime($commitTime, explode("::", $method)[1] . '::commit', 'completed');
                return redirect()->route(static::ROUTE_INDEX)->with('success', __('Setting updated successfully'));
            } catch (\Throwable $e) {
                $rollTime = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($rollTime, explode("::", $method)[1] . '::rollback', 'failed');
                Log::error($method . ' failed to ' . explode("::", $method)[1] . ' ' . DatabaseConstants::TABLE_SETTINGS, ['user_id' => $user?->id, 'payload' => $payload, 'error' => $e->getMessage()]);
                Log::debug($method . ' debug', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method, route(static::ROUTE_INDEX));
            }
        }, func_get_args());
    }

    /**
     * Show form to edit a single discover feature.
     *
     * @param  int  $id
     * @return Renderable|RedirectResponse
     */
    public function edit($id): Renderable|RedirectResponse
    {
        $action = __METHOD__;
        return $this->measureProfile(__METHOD__, function () use ($id, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                    Log::notice("{$action} • unauthenticated, redirecting");
                    return $userOrRedirect;
                }
                Log::debug("{$action} • loading settings");
                $settings = LandingPageSetting::settings();
                $features = json_decode($settings[LPC::DC_OF_FTS_K] ?? '[]', true);
                if (!isset($features[$id])) {
                    Log::warning("{$action} • invalid feature key", ['key' => $id]);
                    return redirect()->route(self::ROUTE_INDEX)
                        ->with('error', __('Feature not found.'));
                }
                $feature = $features[$id];
                $key     = $id;
                $view = self::getFirstExistingView(self::ENTITY . '.edit');
                if (!$view) {
                    Log::warning("{$action} • view not found", ['attempted' => self::ENTITY . '.edit']);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.edit');
                }
                Log::info("{$action} • rendering edit view", ['key' => $key]);
                return view(
                    $view,
                    compact('feature', 'key')
                );
            } catch (\Throwable $e) {
                Log::error("{$action} • Error in {$action}", ['error' => $e->getMessage(), 'id' => $id]);
                Log::debug("{$action} • exception trace", ['trace' => $e->getTraceAsString()]);
                throw $e;
            }
        });
    }

    /**
     * Update a discover feature.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return RedirectResponse
     */
    public function update(Request $request, string|int $id): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id) {
            try {
                Log::info("[$action] starting update", ['id' => $id]);
                $start = microtime(true);
                $response = $this->discoverUpdate($request, (int)$id);
                $this->logExecutionTime($start, $action . '::discoverUpdate', 'completed');
                return $response;
            } catch (\Throwable $e) {
                Log::error("[$action] Error in {$action}", ['error' => $e->getMessage(), 'id' => $id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                throw $e;
            }
        }, ['id' => $id]);
    }

    /**
     * Delete a discover feature.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy(string|int $id): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['uri' => request()->getRequestUri(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($id) {
            $stepStart = microtime(true);
            $response = $this->discoverDelete(request(), $id);
            $this->logExecutionTime($stepStart, 'discoverDelete', 'completed');
            return $response;
        }, ['uri' => request()->getRequestUri(), 'id' => $id]);
    }

    /**
     * Show form for editing a single feature.
     */
    public const DCV_EDT = 'discoverEdit';
    public function discoverEdit(Request $request, int|string $key): Renderable|RedirectResponse
    {
        $method = static::class . '::' . __FUNCTION__;
        return $this->measureProfile(__FUNCTION__, function () use ($request, $key, $method) {
            try {
                if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $settings = LandingPageSetting::settings();
                $features = json_decode($settings[static::ENTITY . '_of_features'] ?? '[]', true);
                if (!isset($features[$key])) {
                    $time = microtime(true);
                    Log::warning($method . ' invalid key', ['key' => $key]);
                    $this->logExecutionTime($time, explode("::", $method)[1] . '::invalidKey', 'failed');
                    Log::debug($method . ' debug data', ['settings' => $settings, 'features' => $features]);
                    return redirect()->route(static::ROUTE_INDEX)->with('error', __('Feature not found.'));
                }
                $feature = $features[$key];
                $view = self::getFirstExistingView(static::ENTITY . '.edit');
                if (!$view) {
                    Log::warning($method . ' - view not found', ['attempted' => static::ENTITY . '.edit']);
                    throw new \RuntimeException("View not found: " . static::ENTITY . '.edit');
                }
                return view($view, compact('feature', 'key'));
            } catch (\Throwable $e) {
                Log::error($method . ' • failed', ['error' => $e->getMessage(), 'key' => $key]);
                Log::debug($method . ' • exception trace', ['trace' => $e->getTraceAsString()]);
                throw $e;
            }
        }, func_get_args());
    }

    /**
     * Update a single discover feature.
     */
    public const DCV_UPD = 'discoverUpdate';
    public function discoverUpdate(Request $request, int|string $key): RedirectResponse
    {
        return $this->measureProfile(__METHOD__, function () use ($request, $key) {
            $action = __METHOD__;
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                Log::notice("{$action} • unauthenticated, redirecting", ['uri' => $request->getRequestUri()]);
                return $userOrRedirect;
            }
            $user   = $userOrRedirect;
            $payload = $request->validate([
                self::ENTITY . 'Heading'     => 'string|nullable',
                self::ENTITY . 'Description' => 'string|nullable',
                self::ENTITY . 'Logo'        => 'image|mimes:png,jpg,jpeg,svg,webp|max:' . SettingsConstants::MAX_U_SIZE_DEF . '|nullable',
            ]);
            DB::beginTransaction();
            try {
                $settings = LandingPageSetting::settings();
                $features = json_decode($settings[LPC::DC_OF_FTS_K] ?? '[]', true);
                if (!isset($features[$key])) {
                    DB::rollBack();
                    Log::warning("{$action} • feature not found", ['user_id' => $user->id, 'key' => $key]);
                    return redirect()->route(self::ROUTE_INDEX)->with('error', __('Feature not found.'));
                }
                if ($request->hasFile(self::ENTITY . 'Logo')) {
                    $fileName = time() . '-discover_logo.' . $request->discoverLogo->getClientOriginalExtension();
                    $dir      = 'uploads/landing_page_image';
                    $res      = LandingPageSetting::uploadFile($request, self::ENTITY . 'Logo', $fileName, $dir, []);
                    if ($res['flag'] === 0) {
                        DB::rollBack();
                        Log::error("{$action} • logo upload failed", ['user_id' => $user->id, 'msg' => $res['msg']]);
                        return redirect()->back()->with('error', __($res['msg']));
                    }
                    $features[$key][self::ENTITY . 'Logo'] = $fileName;
                }
                $features[$key][self::ENTITY . 'Heading']     = $payload[self::ENTITY . 'Heading']     ?? $features[$key][self::ENTITY . 'Heading'];
                $features[$key][self::ENTITY . 'Description'] = $payload[self::ENTITY . 'Description'] ?? $features[$key][self::ENTITY . 'Description'];
                LandingPageSetting::updateOrCreate(
                    ['name'   => LPC::DC_OF_FTS_K],
                    ['value'  => json_encode(array_values($features)), DatabaseConstants::COL_TABLE_CREATOR => $user->id]
                );
                DB::commit();
                Log::info("{$action} • feature updated successfully", ['user_id' => $user->id, 'key' => $key]);
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Feature updated successfully'));
            } catch (\Throwable $e) {
                DB::rollBack();
                $errCtx = ['exception' => get_class($e), 'message' => $e->getMessage(), 'user_id' => $user->id, 'key' => $key, 'payload' => $payload];
                Log::critical("{$action} • failed to update feature", $errCtx);
                Log::channel(SettingsConstants::CRT_TRACE)->debug("{$action} • failed to update feature", array_merge($errCtx, ['trace' => $e->getTraceAsString()]));
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }

    /**
     * Delete a single discover feature.
     */
    public const DCV_DEL = 'discoverDelete';
    public function discoverDelete(Request $request, int|string $key): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $key) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            DB::beginTransaction();
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $decodeStart = microtime(true);
                $features = json_decode($settings[LPC::DC_OF_FTS_K] ?? '[]', true) ?: [];
                $this->logExecutionTime($decodeStart, $action . '::decodeFeatures', 'completed');
                if (!isset($features[$key])) {
                    Log::warning("[$action] feature not found", ['user_id' => $user?->id, 'key' => $key]);
                    Log::debug("[$action] available keys", ['keys' => array_keys($features)]);
                    DB::rollBack();
                    return redirect()->route(self::ROUTE_INDEX)->with('error', __('Feature not found.'));
                }
                unset($features[$key]);
                $updateStart = microtime(true);
                LandingPageSetting::updateOrCreate(['name' => LPC::DC_OF_FTS_K], ['value' => json_encode(array_values($features)), DatabaseConstants::COL_TABLE_CREATOR => $user?->id]);
                $this->logExecutionTime($updateStart, $action . '::updateOrCreate', 'completed');
                DB::commit();
                Log::info("[$action] feature deleted", ['user_id' => $user?->id, 'key' => $key]);
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Feature deleted successfully'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[$action] failed to delete feature", ['user_id' => $user?->id, 'key' => $key, 'error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        }, ['key' => $key]);
    }

    /**
     * Show form to add a new discover feature.
     */
    public const DCV_CRT = 'discoverCreate';
    public function discoverCreate(): Renderable|RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['uri' => request()->getRequestUri(), 'ip' => request()->ip()]);
        return $this->measureProfile($method, function () use ($method) {
            try {
                $stepStart = microtime(true);
                if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
                $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
                Log::info($method . ' - rendering discoverCreate view', ['user_id' => $ur?->id]);
                $stepStart = microtime(true);
                $view = self::getFirstExistingView(self::ENTITY . '.create');
                if (!$view) {
                    Log::error($method . ' - view not found', ['view' => self::ENTITY . '.create']);
                    return redirect()->route(static::ROUTE_INDEX)->with('error', __('View not found.'));
                }
                $this->logExecutionTime($stepStart, 'renderDiscoverCreate', 'completed');
                return view($view);
            } catch (\Throwable $e) {
                Log::error($method . ' - unexpected error', [
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                    'uri' => request()->getRequestUri(),
                ]);
                Log::debug($method . ' - exception trace', [
                    'trace' => $e->getTraceAsString(),
                    'request' => [
                        'uri' => request()->getRequestUri(),
                        'ip' => request()->ip()
                    ],
                    'user_id' => request()->user()?->id
                ]);
            }
        }, ['uri' => request()->getRequestUri(), 'ip' => request()->ip()]);
    }

    /**
     * Persist a new discover feature.
     */
    public const DCV_STR = 'discoverStore';
    public function discoverStore(Request $request): RedirectResponse
    {
        $method = static::class . '::' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($method, function () use ($request, $method, $function) {
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $settings = LandingPageSetting::settings();
            $data = json_decode($settings[static::ENTITY . '_of_features'] ?? '[]', true);
            $new = [];
            if ($request->hasFile(static::ENTITY . 'Logo')) {
                $startFile = microtime(true);
                $fileName = time() . '-discover_logo.' . $request->discoverLogo->getClientOriginalExtension();
                $dir = 'uploads/landing_page_image';
                $res = LandingPageSetting::uploadFile($request, static::ENTITY . 'Logo', $fileName, $dir, []);
                $this->logExecutionTime($startFile, $function . '::uploadFile', $res['flag'] === 0 ? 'failed' : 'completed');
                if ($res['flag'] === 0) {
                    Log::error($method . ' file upload failed', ['flag' => $res['flag'], 'msg' => $res['msg']]);
                    Log::debug($method . ' debug upload response', ['response' => $res]);
                    return redirect()->back()->with('error', __($res['msg']));
                }
                $new[static::ENTITY . 'Logo'] = $fileName;
            }
            $new[static::ENTITY . 'Heading'] = $request->input(static::ENTITY . 'Heading', '');
            $new[static::ENTITY . 'Description'] = $request->input(static::ENTITY . 'Description', '');
            $data[] = $new;
            $startUpdate = microtime(true);
            LandingPageSetting::updateOrCreate(
                ['name' => static::ENTITY . '_of_features'],
                ['value' => json_encode(array_values($data)), DatabaseConstants::COL_TABLE_CREATOR => $user->id]
            );
            $this->logExecutionTime($startUpdate, explode("::", $method)[1] . '::updateOrCreate', 'completed');
            return redirect()->back()->with('success', __('Feature added successfully'));
        }, func_get_args());
    }
}
