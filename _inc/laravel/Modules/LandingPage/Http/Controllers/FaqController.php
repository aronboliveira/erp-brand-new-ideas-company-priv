<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants
};
use App\Http\Controllers\Controller as AppController;
use App\Models\User;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Collection;
use Modules\LandingPage\Config\Constants\{
    RoutesResourcesConstants as RRC,
    SettingsConstants as LandingPageSettingsConstants
};
use Modules\LandingPage\Entities\LandingPageSetting;
use function App\Http\Controllers\{
    defaultPermissionDenial,
    defaultUndefinedException
};

class FaqController extends AppController
{
    use ChecksLogin, ChecksPermissions;

    public const ENTITY = RRC::FQ;
    private const SINGULAR = 'faq';
    private const LP = RRC::LP;
    private const REDIRECT_INDEX = self::ENTITY . '.index';

    public function index(Request $request): Renderable|RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $function) {
            $userId = '#UNAUTHENTICATED';
            $ur = self::_checkLogin(haltRedirect: true);
            if ($ur instanceof User) $user = $ur;
            if (isset($user)) $userId = $user->id;
            Log::debug("[$action] start", ['user_id' => $userId]);
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::landingPageSetting();
                $this->logExecutionTime($settingsStart, $action . '::landingPageSetting', 'completed');
                $decodeStart = microtime(true);
                $faqs = json_decode($settings[self::ENTITY] ?? '[]', true) ?: [];
                $faqs = is_array($faqs) ? collect($faqs)->sortByDesc('created_at')->toArray() : ($faqs instanceof Collection ? $faqs->sortByDesc('created_at')->toArray() : $faqs);
                $this->logExecutionTime($decodeStart, $action . '::decodeFAQs', 'completed');
                Log::debug("[$action] loaded FAQs", ['count' => count($faqs)]);
                $view = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                Log::debug("[$action] rendering view", ['user_id' => $userId]);
                return view($view, compact(DatabaseConstants::TABLE_SETTINGS, self::ENTITY));
            } catch (\Throwable $e) {
                $this->logExecutionTime(isset($settingsStart) ? $settingsStart : microtime(true), $action . '::exception', 'error');
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, []);
    }

    public function show(Request $request, int $key): Renderable|RedirectResponse|null
    {
        $method = __METHOD__;
        $function = __FUNCTION__;
        Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $method, $function) {
            try {
                $stepStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($stepStart, 'loadSettings', 'completed');
                $stepStart = microtime(true);
                $faqs = json_decode($settings[self::ENTITY] ?? '[]', true);
                $this->logExecutionTime($stepStart, 'decodeFaqs', 'completed');
                if (!isset($faqs[$key])) {
                    Log::warning($method . ' - FAQ not found', ['key' => $key]);
                    Log::debug($method . ' - available FAQ keys', ['keys' => array_keys($faqs)]);
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', __('FAQ not found'));
                }
                $stepStart = microtime(true);
                Log::info($method . ' - showing FAQ', ['key' => $key]);
                $this->logExecutionTime($stepStart, 'logShowing', 'completed');
                $stepStart = microtime(true);
                $view = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning($method . ' - view not found', ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return view($view, [self::ENTITY => $faqs[$key], 'key' => $key]);
            } catch (\Throwable $e) {
                Log::error($method . ' - failed', ['error' => $e->getMessage(), 'key' => $key]);
                Log::debug($method . ' - exception trace', ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'key' => $key]);
    }

    public function create(Request $request): Renderable|RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile(__FUNCTION__, function () use ($request, $function) {
            try {
            } catch (\Throwable $e) {
                Log::error(static::class . '::' . $function . ' failed', ['error' => $e->getMessage()]);
                Log::debug(static::class . '::' . $function . ' exception trace', ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, static::class . '::' . $function, route(static::REDIRECT_INDEX));
            }
            $method = static::class . '::' . $function;
            if (($user = static::_checkLogin()) instanceof RedirectResponse) return $user;
            $startGuard = microtime(true);
            if (($redirect = static::guard($request, 'manage faq', static::REDIRECT_INDEX)) !== true) {
                Log::warning($method . ' unauthorized access', ['action' => 'manage faq', 'user_id' => $user?->id]);
                $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                Log::debug($method . ' debug guard', ['redirect' => $redirect]);
                return $redirect;
            }
            $view = self::getFirstExistingView(static::ENTITY . '.' . $function);
            if (!$view) {
                Log::warning($method . ' - view not found', ['attempted' => static::ENTITY . '.' . $function]);
                throw new \RuntimeException("View not found: " . static::ENTITY . '.' . $function);
            }
            $this->logExecutionTime($startGuard, $function . '::guard', 'completed');
            Log::debug($method . ' - rendering create view', ['user_id' => $user?->id]);
            return view($view, compact(DatabaseConstants::TABLE_SETTINGS));
        }, func_get_args());
    }

    public function store(Request $request): RedirectResponse|null
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($redirect = self::guard($request, 'manage faq', self::REDIRECT_INDEX)) !== true) return $redirect;
            $data = $request->validate([
                self::SINGULAR . '_status' => 'nullable|in:on,off',
                self::SINGULAR . '_title' => 'required|string',
                self::SINGULAR . '_heading' => 'required|string',
                self::SINGULAR . '_description' => 'nullable|string'
            ]);
            DB::beginTransaction();
            $stepStart = microtime(true);
            try {
                $payload = [
                    LandingPageSettingsConstants::FAQ_STT_K => $data[self::SINGULAR . '_status'] ?? 'off',
                    LandingPageSettingsConstants::FAQ_TTL_K => $data[self::SINGULAR . '_title'],
                    LandingPageSettingsConstants::FAQ_HDG_K => $data[self::SINGULAR . '_heading'],
                    LandingPageSettingsConstants::FAQ_DESC_K => $data[self::SINGULAR . '_description'] ?? ''
                ];
                foreach ($payload as $name => $value) LandingPageSetting::updateOrCreate(['name' => $name], ['value' => $value]);
                DB::commit();
                $this->logExecutionTime($stepStart, 'DB transaction', 'completed');
                Log::info($action . ' saved FAQ settings', $payload);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('FAQ settings updated successfully'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::debug($action . ' exception trace', ['exception' => $e, 'request' => $request->all()]);
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, int $key): Renderable|RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $key, $function) {
            $checkStart = microtime(true);
            $ur = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;
            $guardStart = microtime(true);
            $g = self::guard($request, 'manage faq', self::REDIRECT_INDEX);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($g instanceof RedirectResponse) {
                Log::warning("[$action] permission denied", ['key' => $key, 'user_id' => $user?->id]);
                Log::debug("[$action] lacks manage faq permission", ['key' => $key]);
                return $g;
            }
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $decodeStart = microtime(true);
                $faqs = json_decode($settings[self::ENTITY] ?? '[]', true) ?: [];
                $this->logExecutionTime($decodeStart, $action . '::decodeFAQs', 'completed');
                if (!isset($faqs[$key])) {
                    Log::warning("[$action] FAQ not found", ['key' => $key]);
                    Log::debug("[$action] available keys", ['keys' => array_keys($faqs)]);
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', __('FAQ not found'));
                }
                $view = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                Log::debug("[$action] loaded edit form", ['key' => $key]);
                return view($view, [self::ENTITY => $faqs[$key], 'key' => $key]);
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'key' => $key]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['key' => $key]);
    }

    public function update(Request $request, int $key): RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'ip' => $request->ip(), 'key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (($redirect = self::guard($request, 'manage faq', self::REDIRECT_INDEX)) !== true) return $redirect;
            $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
            $stepStart = microtime(true);
            $data = $request->validate([
                self::SINGULAR . '_questions' => 'required|string',
                self::SINGULAR . '_answer' => 'required|string',
            ]);
            $this->logExecutionTime($stepStart, 'validation', 'completed');
            $stepStart = microtime(true);
            $settings = LandingPageSetting::settings();
            $this->logExecutionTime($stepStart, 'loadSettings', 'completed');
            $stepStart = microtime(true);
            $faqs = json_decode($settings[self::ENTITY] ?? '[]', true);
            $this->logExecutionTime($stepStart, 'decodeFaqs', 'completed');
            if (!isset($faqs[$key])) {
                Log::warning($method . ' - FAQ not found', ['key' => $key]);
                Log::debug($method . ' - available FAQ keys', ['keys' => array_keys($faqs)]);
                return redirect()->route(self::REDIRECT_INDEX)->with('error', __('FAQ not found'));
            }
            $stepStart = microtime(true);
            $faqs[$key] = [
                self::SINGULAR . '_questions' => $data[self::SINGULAR . '_questions'],
                self::SINGULAR . '_answer' => $data[self::SINGULAR . '_answer'],
            ];
            $this->logExecutionTime($stepStart, 'buildFaqItem', 'completed');
            try {
                $stepStart = microtime(true);
                LandingPageSetting::updateOrCreate(
                    ['name' => self::ENTITY],
                    ['value' => json_encode($faqs)]
                );
                $this->logExecutionTime($stepStart, 'persistFaqs', 'completed');
                Log::info($method . ' - updated FAQ', ['key' => $key]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('FAQ updated successfully'));
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'key' => $key]);
                Log::error($method . ' - update failed', ['key' => $key]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip(), 'key' => $key]);
    }

    public function destroy(Request $request, int $key): RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $key, $function) {
            $method = static::class . '::' . $function;
            if (($user = static::_checkLogin()) instanceof RedirectResponse) return $user;
            $startGuard = microtime(true);
            if (($redirect = static::guard($request, 'manage faq', static::REDIRECT_INDEX)) !== true) {
                Log::warning($method . ' unauthorized access', ['action' => 'manage faq', 'user_id' => $user?->id]);
                $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                Log::debug($method . ' debug guard', ['redirect' => $redirect]);
                return $redirect;
            }
            try {
                $startSettings = microtime(true);
                $settings = LandingPageSetting::settings();
                $faqs = json_decode($settings[static::ENTITY . 's'] ?? '[]', true);
                $this->logExecutionTime($startSettings, $function . '::settingsFetch', 'completed');
                if (!isset($faqs[$key])) {
                    $timeNotFound = microtime(true);
                    Log::warning($method . ' FAQ not found', ['key' => $key]);
                    $this->logExecutionTime($timeNotFound, $function . '::notFound', 'failed');
                    Log::debug($method . ' debug FAQ list', ['faqs' => $faqs]);
                    return redirect()->route(static::REDIRECT_INDEX)->with('error', __('FAQ not found'));
                }
                unset($faqs[$key]);
                $startUpdate = microtime(true);
                LandingPageSetting::updateOrCreate(
                    ['name' => static::ENTITY . 's'],
                    ['value' => json_encode(array_values($faqs))]
                );
                $this->logExecutionTime($startUpdate, $function . '::updateOrCreate', 'completed');
                Log::info($method . ' deleted FAQ', ['key' => $key]);
                return redirect()->route(static::REDIRECT_INDEX)->with('success', __('FAQ deleted successfully'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                return defaultUndefinedException($request, $e, $method, route(static::REDIRECT_INDEX));
            }
        }, func_get_args());
    }

    public const FQ_CRT = 'faqCreate';
    public function faqCreate(Request $request): Renderable|RedirectResponse|null
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action invoked");
            $stepStart = microtime(true);
            try {
                $response = $this->create($request);
                $this->logExecutionTime($stepStart, 'create', 'completed');
                Log::info("$action completed");
                return $response;
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                throw $e;
            }
        });
    }

    public const FQ_STR = 'faqStore';
    public function faqStore(Request $request): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            Log::info("[$action] invoked", ['input' => $request->all()]);
            $start = microtime(true);
            $response = $this->store($request);
            $this->logExecutionTime($start, $action . '::store', 'completed');
            Log::info("[$action] completed");
            return $response;
        }, ['input' => $request->all()]);
    }

    public const FQ_EDT = 'faqEdit';
    public function faqEdit(Request $request, string|int $key): Renderable|RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            try {
                $stepStart = microtime(true);
                Log::info($method . ' invoked', ['key' => $key]);
                $this->logExecutionTime($stepStart, 'invokeEdit', 'completed');
                $stepStart = microtime(true);
                $response = $this->edit($request, $key);
                $this->logExecutionTime($stepStart, 'edit', 'completed');
                Log::info($method . ' completed', ['key' => $key]);
                return $response;
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'key' => $key]);
                Log::error($method . ' - invocation failed', ['key' => $key, 'error' => $e->getMessage()]);
                throw $e;
            }
        }, ['key' => $key]);
    }

    public const FQ_UPD = 'faqUpdate';
    public function faqUpdate(Request $request, string|int $key): RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $key, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' invoked', ['key' => $key, 'input' => $request->all()]);
            try {
                $startUpdate = microtime(true);
                $response = $this->update($request, $key);
                $this->logExecutionTime($startUpdate, $function . '::update', 'completed');
            } catch (\Throwable $e) {
                $this->logExecutionTime($startUpdate, $function . '::update', 'failed');
                Log::error($method . ' update failed', ['key' => $key, 'error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                throw $e;
            }
            Log::info($method . ' completed', ['key' => $key]);
            return $response;
        }, func_get_args());
    }

    public const FQ_DEL = 'faqDelete';
    public function faqDelete(Request $request, int $key): RedirectResponse|null
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $key, $action) {
            Log::info("$action invoked", ['key' => $key]);
            $stepStart = microtime(true);
            try {
                $response = $this->destroy($request, $key);
                $this->logExecutionTime($stepStart, 'destroy', 'completed');
                Log::info("$action completed", ['key' => $key]);
                return $response;
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", [
                    'exception' => $e,
                    'request' => $request->all(),
                    'key' => $key
                ]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'key' => $key]);
                throw $e;
            }
        });
    }
}
