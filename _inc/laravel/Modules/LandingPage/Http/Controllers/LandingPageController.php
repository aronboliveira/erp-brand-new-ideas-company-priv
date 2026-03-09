<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants as DC,
    LandingPageConstants as LPC,
    PermissionsConstants as PMC,
    UsersConstants as UC
};
use App\Http\Controllers\Abstracts\Controller as AppController;
use App\Models\User;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Validation\ValidationException;
use Modules\LandingPage\{Config\Constants\RoutesResourcesConstants as RRC, Entities\LandingPageSetting};
use function App\Http\Controllers\Helpers\{defaultPermissionDenial, defaultUndefinedException};

class LandingPageController extends AppController
{
    use ChecksLogin, ChecksPermissions;
    private const SINGULAR = RRC::LP;
    private const TP = 'topbar';
    private const ROUTE_INDEX = self::SINGULAR . '.index';

    /**
     * Show topbar settings.
     */
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function index(Request $request): Renderable|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            $ur = self::_checkLogin(haltRedirect: true);
            if (!($ur instanceof User) || $ur->type !== 'super admin') {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            try {
                $viewStart = microtime(true);
                $response = view(self::SINGULAR . '::' . self::SINGULAR . '.' . self::TP);
                $this->logExecutionTime($viewStart, $action . '::view', 'completed');
                return $response;
            } catch (\Throwable $e) {
                Log::error("[$action] Error in index", ['error' => $e->getMessage()]);
                Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, []);
    }

    /**
     * Display a single setting in read-only mode.
     */
    public function show(Request $request, int|string $id): Renderable|RedirectResponse|null
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        Log::debug($method . ' - start', ['id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method, $function) {
            $stepStart = microtime(true);
            $userId = '#UNAUTHENTICATED';
            if (($ur = self::_checkLogin(haltRedirect: true)) instanceof User) $userId = $ur->id;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            try {
                $stepStart = microtime(true);
                $setting = LandingPageSetting::where('id', $id)
                    ->where(DC::COL_TABLE_CREATOR, $userId)
                    ->firstOrFail();
                $this->logExecutionTime($stepStart, 'loadSetting', 'completed');
                Log::info($method . ' loaded setting', ['user_id' => $userId, 'setting_id' => $id]);
                $stepStart = microtime(true);
                $view = view(self::SINGULAR . '::' . self::SINGULAR . '.' . $function, ['setting' => $setting]);
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return $view;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::warning($method . ' setting not found', ['user_id' => $userId, 'setting_id' => $id]);
                Log::debug($method . ' - exception details', ['exception' => get_class($e), 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'user_id' => $userId, 'setting_id' => $id]);
                return redirect()->route(self::ROUTE_INDEX)->with('error', __('Setting not found'));
            } catch (\Throwable $e) {
                Log::error($method . ' exception', ['error' => $e->getMessage(), 'setting_id' => $id]);
                Log::debug($method . ' - exception details', ['exception' => get_class($e), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method, route(self::ROUTE_INDEX));
            }
        }, ['id' => $id]);
    }

    /**
     * Persist topbar settings.
     */
    public function store(Request $request): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            try {
                if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                $request->merge([
                    static::TP . '_status' => $request->has(static::TP . '_status')
                        && in_array($request->{static::TP . '_status'}, ['on', 'off', 'checked', 'unchecked', '0', '1', 'true', 'false'], true)
                        ? 'on'
                        : 'off'
                ]);
                $payload = $request->validate([
                    static::TP . '_status' => [
                        'required',
                        'in:on,off,checked,unchecked,0,1,true,false'
                    ],
                    static::TP . '_notification_msg' => 'nullable|string'
                ]);
                DB::beginTransaction();
                try {
                    $statusValue = $payload[static::TP . '_status'];
                    $normalizedStatus = in_array($statusValue, ['on', 'checked', '1', 'true', true, 1], true)
                        ? 'on'
                        : 'off';
                    $settings = [
                        static::TP . '_status'          => $normalizedStatus,
                        static::TP . '_notification_msg' => $payload[static::TP . '_notification_msg' ?? ''] ?? ''
                    ];
                    Log::notice("LandingPageController settings loaded", ["count" => is_array($settings) ? count($settings) : 0]);
                    foreach ($settings as $name => $value) {
                        $startUpdate = microtime(true);
                        $existingSetting = LandingPageSetting::where([
                            LPC::COL_LPS_NM => $name,
                            DC::COL_TABLE_CREATOR => $user?->id
                        ])->first();
                        if ($existingSetting)
                            $existingSetting->update([
                                LPC::COL_LPS_V => $value
                            ]);
                        else
                            LandingPageSetting::create([
                                LPC::COL_LPS_NM => $name,
                                LPC::COL_LPS_V => $value,
                                DC::COL_TABLE_CREATOR => $user?->id
                            ]);
                        $this->logExecutionTime($startUpdate, $function . '::updateOrCreate', 'completed');
                        Log::info(ucfirst(static::TP) . ' settings saved', [
                            UC::COL_USER_ID => $user?->id,
                            'setting' => $name,
                            LPC::COL_LPS_V => $value
                        ]);
                    }
                    DB::commit();
                    $savedSettings = LandingPageSetting::where(DC::COL_TABLE_CREATOR, $user?->id);
                    Log::notice('Verified saved settings from DB', [
                        'saved' => $savedSettings->pluck(LPC::COL_LPS_V, LPC::COL_LPS_NM)->toArray()
                    ]);
                    return redirect()->route(static::ROUTE_INDEX)
                        ->with('success', __(ucfirst(static::TP) . ' settings updated successfully'));
                } catch (\Throwable $e) {
                    $errorTime = microtime(true);
                    DB::rollBack();
                    $this->logExecutionTime($errorTime, $function . '::exception', 'failed');
                    Log::error($method . ' failed to store ' . static::TP . ' settings', [
                        UC::COL_USER_ID => $user?->id,
                        'payload'                   => $payload,
                        'error'                     => $e->getMessage()
                    ]);
                    Log::debug($method . ' debug exception', [
                        'exception' => $e,
                        'trace'     => $e->getTraceAsString()
                    ]);
                    return defaultUndefinedException($request, $e, $method, route(static::ROUTE_INDEX));
                }
            } catch (ValidationException $e) {
                $validationTime = microtime(true);
                $this->logExecutionTime($validationTime, $function . '::validationException', 'failed');

                Log::error($method . ' failed to store ' . static::TP . ' settings due to validation error', [
                    'request' => $request->all(),
                    'errors'  => $e->errors()
                ]);

                return redirect()->route(static::ROUTE_INDEX)
                    ->withErrors($e->errors())
                    ->withInput();
            } catch (\Throwable $e) {
                Log::error($method . ' failed to store ' . static::TP . ' settings', [
                    'error' => $e->getMessage()
                ]);

                // Added missing return statement
                return redirect()->route(static::ROUTE_INDEX)
                    ->with('error', __('An unexpected error occurred'))
                    ->withInput();
            }
        }, func_get_args());
    }

    /**
     * Update a single setting by ID.
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $payload = $request->validate([LPC::COL_LPS_V => 'required|string']);
            DB::beginTransaction();
            $stepStart = microtime(true);
            try {
                $setting = LandingPageSetting::where('id', $id)->where(DC::COL_TABLE_CREATOR, $user?->id)->firstOrFail();
                $old = $setting->value;
                $setting->value = $payload[LPC::COL_LPS_V];
                $setting->save();
                $this->logExecutionTime($stepStart, 'update setting', 'completed');
                Log::info("$action succeeded", [
                    UC::COL_USER_ID => $user?->id,
                    'setting_id' => $id,
                    'old_value' => $old,
                    'new_value' => $setting->value
                ]);
                DB::commit();
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Setting updated successfully'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                DB::rollBack();
                Log::debug("$action model not found", [
                    UC::COL_USER_ID => $user?->id,
                    'setting_id' => $id,
                    'exception' => $e->getMessage()
                ]);
                Log::warning("$action setting not found", [
                    UC::COL_USER_ID => $user?->id,
                    'setting_id' => $id
                ]);
                return redirect()->route(self::ROUTE_INDEX)->with('error', __('Setting not found'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'setting_id' => $id]);
                Log::error("$action failed to update setting", [
                    UC::COL_USER_ID => $user?->id,
                    'setting_id' => $id,
                    'error' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }

    /**
     * Remove a single setting by ID.
     */
    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id) {
            $checkStart = microtime(true);
            $ur = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;
            DB::beginTransaction();
            try {
                $fetchStart = microtime(true);
                $setting = LandingPageSetting::where('id', $id)
                    ->where(DC::COL_TABLE_CREATOR, $user?->id)
                    ->firstOrFail();
                $this->logExecutionTime($fetchStart, $action . '::fetchSetting', 'completed');
                $delStart = microtime(true);
                $setting->delete();
                $this->logExecutionTime($delStart, $action . '::delete', 'completed');
                Log::info("[$action] LandingPageSetting deleted", ['user_id' => $user?->id, 'setting_id' => $id]);
                Log::debug("[$action] debug deletion complete", ['setting' => $setting]);
                DB::commit();
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Setting deleted successfully'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                DB::rollBack();
                Log::warning("[$action] setting not found for delete", ['user_id' => $user?->id, 'setting_id' => $id]);
                Log::debug("[$action] available setting ids", ['query' => ['id' => $id, 'creator' => $user?->id]]);
                return redirect()->route(self::ROUTE_INDEX)->with('error', __('Setting not found'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[$action] failed to delete setting", ['user_id' => $user?->id, 'setting_id' => $id, 'error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        }, ['setting_id' => $id]);
    }

    /**
     * Show the form for creating a new topbar setting.
     */
    public function create(Request $request): Renderable|RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
        return $this->measureProfile($method, function () use ($request, $method) {
            try {
                $stepStart = microtime(true);
                if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
                $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
                $stepStart = microtime(true);
                $user = $ur;
                if (($redirect = self::guard($request, PMC::MNG_LP, self::ROUTE_INDEX)) !== true) return $redirect;
                $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
                Log::info($method . ' - rendering create form', ['user_id' => $user?->id]);
                $stepStart = microtime(true);
                $view = view(self::SINGULAR . '::' . self::SINGULAR . '.create');
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::error($method . ' exception', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception details', ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    /**
     * Show the form for editing a single setting.
     */
    public function edit(Request $request, int|string $id): Renderable|RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            if (($ur = static::_checkLogin()) instanceof RedirectResponse) return $ur;
            $user = $ur;
            if (($redirect = static::guard($request, PMC::MNG_LP, static::ROUTE_INDEX)) !== true) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                return $redirect;
            }
            try {
                $startFetch = microtime(true);
                $setting = LandingPageSetting::where('id', $id)
                    ->where(DC::COL_TABLE_CREATOR, $user?->id)
                    ->firstOrFail();
                $this->logExecutionTime($startFetch, $function . '::fetchSetting', 'completed');
                Log::info($method . ' rendering edit form', [UC::COL_USER_ID => $user?->id, 'setting_id' => $id]);
                $startView = microtime(true);
                $view = view(static::SINGULAR . '::' . static::SINGULAR . '.edit', ['setting' => $setting]);
                $this->logExecutionTime($startView, $function . '::view', 'completed');
                return $view;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $timeNotFound = microtime(true);
                Log::warning($method . ' setting not found for edit', ['user_id' => $user?->id, 'setting_id' => $id]);
                $this->logExecutionTime($timeNotFound, $function . '::notFound', 'failed');
                return redirect()->route(static::ROUTE_INDEX)->with('error', __('Setting not found'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                Log::error($method . ' exception', ['error' => $e->getMessage(), 'setting_id' => $id]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                return defaultUndefinedException($request, $e, $method, route(static::ROUTE_INDEX));
            }
        }, func_get_args());
    }
}
