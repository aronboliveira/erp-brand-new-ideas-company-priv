<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants};
use App\Http\Controllers\Controller as AppController;
use App\Models\User;
use App\Traits\ChecksLogin;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log};
use Modules\LandingPage\{Config\Constants\RoutesResourcesConstants, Entities\LandingPageSetting};
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};


final class ScreenshotsController extends AppController
{
    use ChecksLogin;

    public const ENTITY = 'screenshot';
    private const LP = RoutesResourcesConstants::LP;
    private const PLURAL = self::ENTITY . 's';
    private const VIEW_BASE = self::LP . '::' . self::LP . '.' . self::PLURAL;
    private const DIR      = 'uploads/landing_page_image';
    private const NAME     = RoutesResourcesConstants::SST;

    public function index(Request $request): mixed
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            $userId = '#UNAUTHENTICATED';
            $checkStart = microtime(true);
            $ur = self::_checkLogin(haltRedirect: true);
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof User) $userId = $ur->id;
            Log::info("[$action] rendering view", ['count' => 0, 'user' => $userId]);

            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::landingPageSetting();
                $this->logExecutionTime($settingsStart, $action . '::landingPageSetting', 'completed');
                $screenshotsStart = microtime(true);
                $screenshots = json_decode($settings[self::NAME] ?? '[]', true) ?? [];
                $this->logExecutionTime($screenshotsStart, $action . '::decodeScreenshots', 'completed');
                $view = self::getFirstExistingView(self::PLURAL . '.index');
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::PLURAL . '.index']);
                    throw new \RuntimeException("View not found: " . self::PLURAL . '.index');
                }
                Log::info("[$action] rendering view", ['count' => count($screenshots), 'user' => $userId]);
                return view($view, [
                    DatabaseConstants::TABLE_SETTINGS => $settings,
                    'screenshots' => $screenshots
                ]);
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['user_id' => Auth::id()]);
    }

    public function show(string|int $id): mixed
    {
        $method = __METHOD__;
        $function = __FUNCTION__;
        Log::debug($method . ' - start', ['user_id' => Auth::id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($id, $method, $function) {
            try {
                Log::info($method . ' called', ['user_id' => Auth::id(), 'id' => $id]);
                $view = self::getFirstExistingView(self::PLURAL . '.' . $function);
                if (!$view) {
                    Log::warning("[$method] view not found", ['attempted' => self::PLURAL . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::PLURAL . '.' . $function);
                }
                return view($view);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'id' => $id]);
                throw $e;
            }
        }, ['user_id' => Auth::id(), 'id' => $id]);
    }

    public function create(): mixed
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($function) {
            $method = static::class . '::' . $function;
            $startView = microtime(true);
            Log::info($method . ' called', ['user_id' => Auth::id()]);
            $view = self::getFirstExistingView(self::PLURAL . '.' . $function);
            if (!$view) {
                Log::warning("[$method] view not found", ['attempted' => self::PLURAL . '.' . $function]);
                throw new \RuntimeException("View not found: " . self::PLURAL . '.' . $function);
            }
            $this->logExecutionTime($startView, $function . '::view', 'completed');
            return view($view);
        }, func_get_args());
    }

    public function store(Request $request): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action called", ['user_id' => Auth::id()]);
            $stepStart = microtime(true);
            try {
                $fields = [
                    self::NAME . '_status' => 'on',
                    self::NAME . '_heading' => $request->screenshots_heading,
                    self::NAME . '_description' => $request->screenshots_description
                ];
                foreach ($fields as $key => $value) {
                    LandingPageSetting::updateOrCreate(['name' => $key], ['value' => $value]);
                    Log::info("$action updated setting", ['name' => $key, 'value' => $value]);
                }
                $this->logExecutionTime($stepStart, 'settings update', 'completed');
                Log::info("$action completed successfully");
                return redirect()->back()->with(['success' => 'Setting update successfully']);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function edit(string|int $id): mixed
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $id, $function) {
            try {
                Log::info("[$action] called", ['user_id' => Auth::id(), 'id' => $id]);
                $view = self::getFirstExistingView(self::PLURAL . '.' . $function);
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::PLURAL . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::PLURAL . '.' . $function);
                }
                return view($view);
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                throw $e;
            }
        }, ['id' => $id]);
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => Auth::id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            try {
                $stepStart = microtime(true);
                $setting = LandingPageSetting::findOrFail($id);
                $old = $setting->value;
                $setting->value = $request->value ?? $old;
                $setting->save();
                $this->logExecutionTime($stepStart, 'saveSetting', 'completed');
                Log::info($method . ' updated setting', ['id' => $id, 'old' => $old, 'new' => $setting->value]);
                return redirect()->back()->with(['success' => 'Setting updated successfully']);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'id' => $id]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['user_id' => Auth::id(), 'id' => $id]);
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile(__FUNCTION__, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            Log::info($method . ' called', ['user_id' => Auth::id(), 'id' => $id]);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');

            try {
                $startDelete = microtime(true);
                LandingPageSetting::where('id', $id)->delete();
                $this->logExecutionTime($startDelete, $function . '::delete', 'completed');
                Log::info($method . ' deleted setting', ['id' => $id]);
                return redirect()->back()->with(['success' => 'Setting deleted successfully']);
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public const SST_CRT = 'screenshotsCreate';
    public function screenshotsCreate(): mixed
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($action) {
            Log::info("$action called", ['user_id' => Auth::id()]);
            $stepStart = microtime(true);
            try {
                $this->logExecutionTime($stepStart, 'view render', 'completed');
                $view = self::getFirstExistingView(self::PLURAL . '.create');
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::PLURAL . '.create']);
                    throw new \RuntimeException("View not found: " . self::PLURAL . '.create');
                }
                return view($view);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }


    public const SST_STR = 'screenshotsStore';
    public function screenshotsStore(Request $request): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            Log::info("[$action] called", ['user_id' => Auth::id()]);
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $items = json_decode($settings[self::NAME] ?? '[]', true) ?? [];

                $data = [self::NAME . '_heading' => $request->screenshots_heading];
                if ($request->screenshots) {
                    $file = time() . '-' . self::NAME . '.' . $request->screenshots->getClientOriginalExtension();
                    $uploadStart = microtime(true);
                    $upload = LandingPageSetting::uploadFile($request, self::NAME, $file, self::DIR, []);
                    $this->logExecutionTime($uploadStart, $action . '::uploadFile', 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning("[$action] upload failed", ['message' => $upload['msg']]);
                        Log::debug("[$action] debug upload details", ['upload' => $upload]);
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $data[self::NAME] = $file;
                    Log::info("[$action] file uploaded", ['file' => $file]);
                }
                $items[] = $data;
                $updateStart = microtime(true);
                LandingPageSetting::updateOrCreate(['name' => self::NAME], ['value' => json_encode($items)]);
                $this->logExecutionTime($updateStart, $action . '::updateOrCreate', 'completed');
                Log::info("[$action] added " . self::ENTITY, ['total' => count($items)]);
                return redirect()->back()->with(['success' => 'Screenshots added successfully']);
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, []);
    }

    public const SST_EDT = 'screenshotsEdit';
    public function screenshotsEdit(string|int $key): mixed
    {
        $method = __METHOD__;
        $function = __FUNCTION__;
        Log::debug($method . ' - start', ['user_id' => Auth::id(), 'key' => $key]);
        return $this->measureProfile($method, function () use ($key, $method, $function) {
            try {
                $stepStart = microtime(true);
                Log::info($method . ' called', ['user_id' => Auth::id(), 'key' => $key]);
                $this->logExecutionTime($stepStart, 'logInvocation', 'completed');
                $stepStart = microtime(true);
                $items = json_decode(LandingPageSetting::settings()[self::NAME] ?? '[]', true) ?? [];
                $screenshot = $items[$key] ?? [];
                $this->logExecutionTime($stepStart, 'decodeItems', 'completed');
                $stepStart = microtime(true);
                $view = self::getFirstExistingView(self::PLURAL . '.' . $function);
                if (!$view) {
                    Log::warning("[$method] view not found", ['attempted' => self::PLURAL . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::PLURAL . '.' . $function);
                }
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return view($view, [self::ENTITY => $screenshot, 'key' => $key]);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'key' => $key]);
                throw $e;
            }
        }, ['user_id' => Auth::id(), 'key' => $key]);
    }

    public const SST_UPD = 'screenshotsUpdate';
    public function screenshotsUpdate(Request $request, string|int $key): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $key, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' called', ['user_id' => Auth::id(), 'key' => $key]);
            try {
                $startFetch = microtime(true);
                $items = json_decode(LandingPageSetting::settings()[static::NAME], true);
                $this->logExecutionTime($startFetch, $function . '::fetchItems', 'completed');
                $oldHeading = $items[$key][static::NAME . '_heading'] ?? null;
                $items[$key][static::NAME . '_heading'] = $request->screenshots_heading;
                Log::info($method . ' heading updated', ['old' => $oldHeading, 'new' => $request->screenshots_heading]);
                if ($request->screenshots) {
                    $startUpload = microtime(true);
                    $file = time() . '-' . static::NAME . '.' . $request->screenshots->getClientOriginalExtension();
                    $upload = LandingPageSetting::uploadFile($request, static::NAME, $file, static::DIR, []);
                    $this->logExecutionTime($startUpload, $function . '::uploadFile', $upload['flag'] === 0 ? 'failed' : 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning($method . ' upload failed', ['message' => $upload['msg']]);
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $items[$key][static::NAME] = $file;
                    Log::info($method . ' file updated', ['file' => $file]);
                }
                $startUpdate = microtime(true);
                LandingPageSetting::updateOrCreate(['name' => static::NAME], ['value' => json_encode($items)]);
                $this->logExecutionTime($startUpdate, $function . '::updateOrCreate', 'completed');
                Log::info($method . ' updated ' . static::ENTITY, ['key' => $key]);
                return redirect()->back()->with(['success' => 'Screenshots update successfully']);
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public const SST_DEL = 'screenshotsDelete';
    public function screenshotsDelete(Request $request, string|int $key): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $key, $action) {
            Log::info("$action called", ['user_id' => Auth::id(), 'key' => $key]);
            $stepStart = microtime(true);
            try {
                $items = json_decode(LandingPageSetting::settings()[self::NAME], true);
                unset($items[$key]);
                LandingPageSetting::updateOrCreate(['name' => self::NAME], ['value' => $items]);
                $this->logExecutionTime($stepStart, 'delete setting', 'completed');
                Log::info("$action deleted " . self::ENTITY, ['key' => $key]);
                return redirect()->back()->with(['success' => 'Screenshots delete successfully']);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'key' => $key]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }
}
