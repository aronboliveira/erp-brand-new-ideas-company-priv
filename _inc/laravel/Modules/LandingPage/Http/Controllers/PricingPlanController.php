<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    SettingsConstants as SC
};
use App\Http\Controllers\Abstracts\Controller as AppController;
use App\Models\User;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Log};
use Modules\LandingPage\{
    Config\Constants\RoutesResourcesConstants as RRC,
    Entities\LandingPageSetting
};
use Modules\LandingPage\Config\Constants\SettingsConstants as LPSC;
use function App\Http\Controllers\Helpers\{defaultUndefinedException};

class PricingPlanController extends AppController
{
    use ChecksLogin, ChecksPermissions;

    public const CRT = 'create';
    public const IDX = 'index';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    private const LP = RRC::LP;
    private const REDIRECT_INDEX = RRC::PRC_PLN . '.index';

    /**
     * Display the pricing plan settings.
     */
    public function index(Request $request): Renderable|RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            $userId = '#UNAUTHENTICATED';
            $checkStart = microtime(true);
            $ur = self::_checkLogin(haltRedirect: true);
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof User) $userId = $ur->id;
            if (!($ur instanceof User) || $ur->type !== 'super admin') {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            Log::info("[$action] start", ['user_id' => $userId]);
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                Log::info("[$action] success: fetched pricing settings", ['count' => count($settings)]);
                return view(self::LP . '::' . self::LP . '.pricing_plan', compact(DC::TABLE_SETTINGS));
            } catch (\Throwable $e) {
                Log::error("[$action] exception", ['error' => $e->getMessage()]);
                Log::channel(SC::ERR_TRACE)->debug("[$action] exception", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['user_id' => Auth::id()]);
    }

    /**
     * Display a single pricing‐plan setting in read-only mode.
     */
    public function show(Request $request, string $key): Renderable|RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            $stepStart = microtime(true);
            $userId = '#UNAUTHENTICATED';
            if (($ur = self::_checkLogin(haltRedirect: true)) instanceof User) $userId = $ur->id;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            Log::info($method . " called by {$userId}");
            try {
                $stepStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($stepStart, 'loadSettings', 'completed');
                if (!isset($settings[$key])) {
                    Log::warning($method . " missing setting", ['key' => $key]);
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', "Setting '{$key}' not found");
                }
                $stepStart = microtime(true);
                Log::info($method . ' displaying setting', ['key' => $key, 'value' => $settings[$key]]);
                $this->logExecutionTime($stepStart, 'logDisplay', 'completed');
                $stepStart = microtime(true);
                $view = view(self::LP . '::' . self::LP . '.pricing_plan_show', [
                    'key' => $key,
                    'value' => $settings[$key],
                ]);
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error($method . " exception", ['error' => $e->getMessage()]);
                Log::channel(SC::ERR_TRACE)->debug($method . " exception", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['key' => $key]);
    }

    /**
     * Show form to create/update pricing plan.
     */
    public function create(Request $request): Renderable|RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            try {
                $method = static::class . '::' . $function;
                $startLogin = microtime(true);
                if (($user = static::_checkLogin()) instanceof RedirectResponse) {
                    $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                    return $user;
                }
                $this->logExecutionTime($startLogin, $function . '::login', 'completed');
                $startGuard = microtime(true);
                if (($redirect = static::guard($request, 'manage pricing plan', static::REDIRECT_INDEX)) !== true) {
                    Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                    $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                    return $redirect;
                }
                $this->logExecutionTime($startGuard, $function . '::guard', 'completed');
                $startView = microtime(true);
                $view = view(static::LP . '::' . static::LP . '.pricing_plan_form');
                $this->logExecutionTime($startView, $function . '::view', 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error(static::class . '::' . $function . ' exception', ['error' => $e->getMessage()]);
                Log::debug(static::class . '::' . $function . ' exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, static::class . '::' . $function, route(static::REDIRECT_INDEX));
            }
        }, func_get_args());
    }

    /**
     * Persist new pricing plan settings.
     */
    public function store(Request $request): RedirectResponse|null
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            return $this->saveSettings($request, 'create');
        });
    }

    /**
     * Show form to edit existing pricing plan.
     */
    public function edit(Request $request, string $key): Renderable|RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $key) {
            try {
                $checkStart = microtime(true);
                $user = self::_checkLogin();
                $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
                if ($user instanceof RedirectResponse) return $user;
                $guardStart = microtime(true);
                $redirect = self::guard($request, 'manage pricing plan', self::REDIRECT_INDEX);
                $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
                if ($redirect instanceof RedirectResponse) {
                    Log::warning("[$action] permission denied", ['user_id' => $user?->id, 'key' => $key]);
                    Log::debug("[$action] lacks manage pricing plan permission", ['user_id' => $user?->id, 'key' => $key]);
                    return $redirect;
                }
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                if (!isset($settings[$key])) {
                    Log::warning("[$action] missing key", ['key' => $key]);
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', "Setting '{$key}' not found");
                }
                Log::info("[$action] rendering form", ['key' => $key]);
                return view(self::LP . '::' . self::LP . '.pricing_plan_form', [
                    'key' => $key,
                    'value' => $settings[$key],
                ]);
            } catch (\Throwable $e) {
                Log::error("[$action] exception", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['key' => $key]);
    }

    /**
     * Update existing pricing plan setting.
     */
    public function update(Request $request, string $key): RedirectResponse|null
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        Log::debug($method . ' - start', ['key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $function) {
            return $this->saveSettings($request, $function, $key);
        }, ['key' => $key]);
    }

    /**
     * Delete a specific pricing plan setting.
     */
    public function destroy(Request $request, string $key): RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $key, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($user = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $user;
            }
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            $startGuard = microtime(true);
            if (($redirect = static::guard($request, 'manage pricing plan', static::REDIRECT_INDEX)) !== true) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                return $redirect;
            }
            $this->logExecutionTime($startGuard, $function . '::guard', 'completed');

            try {
                $startTransaction = microtime(true);
                DB::beginTransaction();
                $deleted = LandingPageSetting::where('name', $key)->delete();
                $this->logExecutionTime($startTransaction, $function . '::transaction', 'completed');

                if (!$deleted) {
                    $timeNotDeleted = microtime(true);
                    Log::warning($method . ' nothing deleted', ['key' => $key]);
                    $this->logExecutionTime($timeNotDeleted, $function . '::notDeleted', 'failed');
                    DB::rollBack();
                    return redirect()->route(static::REDIRECT_INDEX)
                        ->with('error', "No setting found for '{$key}'");
                }

                $startCommit = microtime(true);
                DB::commit();
                $this->logExecutionTime($startCommit, $function . '::commit', 'completed');

                Log::info($method . ' deleted setting', ['key' => $key]);
                return redirect()->route(static::REDIRECT_INDEX)
                    ->with('success', "Setting '{$key}' deleted");
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' exception', ['error' => $e->getMessage()]);
                Log::channel(SC::ERR_TRACE)->debug($method . ' exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $method, route(static::REDIRECT_INDEX));
            }
        }, func_get_args());
    }

    /**
     * Handles both create and update in a transaction.
     */
    protected function saveSettings(Request $request, string $mode, string $key = null): RedirectResponse|null
    {
        $action = __METHOD__ . "[$mode]";
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'manage pricing plan', self::REDIRECT_INDEX)) !== true) return $redirect;
        $rules = [
            'planTitle'       => 'required|string',
            'planHeading'     => 'required|string',
            'planDescription' => 'nullable|string',
            'planStatus'      => 'nullable|in:on,off',
        ];
        $validated = $request->validate($rules);
        try {
            DB::beginTransaction();
            $data = [
                LPSC::PN_STT_K      => $validated['planStatus'] ?? 'off',
                LPSC::PN_TTL_K       => $validated['planTitle'] ?? LPSC::PN_TTL_DEF,
                LPSC::PN_HDG_K     => $validated['planHeading'] ?? LPSC::PN_HDG_DEF,
                LPSC::PN_DESC_K => $validated['planDescription'] ?? '',
            ];
            // if updating single key
            if ($mode === 'update' && $key) {
                if (!array_key_exists($key, $data)) {
                    Log::warning("$action invalid key", ['key' => $key]);
                    DB::rollBack();
                    return redirect()->route(self::REDIRECT_INDEX)
                        ->with('error', "Invalid setting '{$key}'");
                }
                LandingPageSetting::updateOrCreate(
                    ['name' => $key],
                    ['value' => $data[$key]]
                );
                Log::info("$action updated one", ['key' => $key, 'value' => $data[$key]]);
            } else {
                foreach ($data as $name => $value) {
                    LandingPageSetting::updateOrCreate(
                        ['name'  => $name],
                        ['value' => $value]
                    );
                    Log::info("$action upserted setting", ['name' => $name, 'value' => $value]);
                }
            }
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', ucfirst($mode) . ' successful');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action exception", ['error' => $e->getMessage(), 'mode' => $mode, 'input' => $request->all()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
