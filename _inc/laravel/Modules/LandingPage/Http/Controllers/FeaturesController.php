<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{PermissionsConstants, UsersConstants};
use App\Http\Controllers\Controller as AppController;
use App\Models\User;
use App\Traits\ChecksLogin;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Log, View};
use Illuminate\Support\Collection;
use Modules\LandingPage\{
    Config\Constants\RoutesResourcesConstants,
    Entities\LandingPageSetting
};
use Modules\LandingPage\Config\Constants\SettingsConstants as LPC;
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

final class FeaturesController extends AppController
{
    use ChecksLogin;

    public const ENTITY = RoutesResourcesConstants::FT;
    private const LP = RoutesResourcesConstants::LP;
    private const VIEW_BASE   = self::LP . '::' . self::LP . '.' . self::ENTITY;
    private const DIR         = 'uploads/landing_page_image';
    private const FEATURE_NAME = 'feature_of_features';
    private const OTHER_NAME  = 'other_features';

    public function index(Request $request): mixed
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            $userId = '#UNAUTHENTICATED';
            $checkStart = microtime(true);
            $ur = self::_checkLogin(haltRedirect: true);
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof User) $user = $ur;
            $userId = $user->id ?? $userId;
            Log::info("[$action] start", ['user_id' => $userId, 'uri' => $request->getRequestUri()]);
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::landingPageSetting();
                $this->logExecutionTime($settingsStart, $action . '::landingPageSetting', 'completed');
                $decodeStart = microtime(true);
                $features = json_decode($settings[self::FEATURE_NAME] ?? '[]', true) ?: [];
                $this->logExecutionTime($decodeStart, $action . '::decodeFeatures', 'completed');
                $decodeOthersStart = microtime(true);
                $others = json_decode($settings[self::OTHER_NAME] ?? '[]', true) ?: [];
                // $others = is_array($others) ? collect($others)->sortByDesc('created_at')->toArray() : ($others instanceof Collection ? $others->sortByDesc('created_at')->toArray() : $others);
                $this->logExecutionTime($decodeOthersStart, $action . '::decodeOthers', 'completed');
                Log::info("[$action] retrieved counts", ['features' => count($features), 'others' => count($others)]);
                $baseView = self::getFirstExistingView(self::ENTITY . '.index');
                if (!$baseView) {
                    Log::warning("[$action] view not found", ['view' => $baseView]);
                    throw new \RuntimeException("View not found: $baseView");
                }
                return view($baseView, ['settings' => $settings, 'feature_of_features' => $features, 'other_features' => $others]);
            } catch (\Throwable $e) {
                Log::error("[$action] error", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    /**
     * GET /landingpage/features/{id}
     */
    public function show(Request $request, string|int $id): mixed
    {
        $method = __METHOD__;
        $function = __FUNCTION__;
        Log::debug($method . ' - start', ['id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method, $function) {
            $stepStart = microtime(true);
            $userId = '#UNAUTHENTICATED';
            if (($ur = self::_checkLogin(haltRedirect: true)) instanceof User) $user = $ur;
            $userId = $user->id;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            Log::info($method, ['user_id' => $userId]);
            try {
                $stepStart = microtime(true);
                $setting = LandingPageSetting::findOrFail($id);
                $this->logExecutionTime($stepStart, 'findSetting', 'completed');
                Log::info($method . ' loaded setting', ['id' => $id, 'name' => $setting->name]);
                $stepStart = microtime(true);
                $baseView = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$baseView) {
                    Log::warning("[$function] view not found", ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                Log::debug("[$function] resolved view", ['view' => $baseView]);
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return view($baseView, compact('setting'));
            } catch (ModelNotFoundException $e) {
                Log::warning($method . ' setting not found', ['id' => $id]);
                Log::debug($method . ' - exception details', ['exception' => get_class($e), 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'id' => $id]);
                return redirect()->back()->with('error', __('Setting not found'));
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception details', ['exception' => get_class($e), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['id' => $id]);
    }

    public function create(): mixed
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($function) {
            try {
                $method = static::class . '::' . $function;
                Log::info($method, [UsersConstants::COL_USER_ID => Auth::id()]);
                $startView = microtime(true);
                $view = self::getFirstExistingView(static::VIEW_BASE . '.create');
                if (!$view) {
                    Log::warning("[create] view not found", ['attempted' => static::VIEW_BASE . '.create']);
                    throw new \RuntimeException("View not found: " . static::VIEW_BASE . '.create');
                }
                Log::debug("[create] resolved view", ['view' => $view]);
                $this->logExecutionTime($startView, $function . '::view', 'completed');
                return view($view);
            } catch (\Throwable $e) {
                Log::error("[$function] failed", ['error' => $e->getMessage()]);
                Log::debug("[$function] exception trace", ['trace' => $e->getTraceAsString()]);
                throw $e;
            }
        }, func_get_args());
    }

    public function store(Request $request): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action invoked", [UsersConstants::COL_USER_ID => Auth::id()]);
            DB::beginTransaction();
            $stepStart = microtime(true);
            try {
                $fields = [
                    LPC::FT_STT_K => 'on' ?? LPC::FT_STT_DEF,
                    LPC::FT_TTL_K => $request[LPC::FT_TTL_K] ?? LPC::FT_TTL_DEF,
                    LPC::FT_HDG_K => $request[LPC::FT_HDG_K] ?? LPC::FT_HDG_DEF,
                    LPC::FT_DESC_K => $request[LPC::FT_DESC_K] ?? LPC::FT_DESC_DEF,
                    LPC::FT_BUY_LNK_K => $request[LPC::FT_BUY_LNK_K] ?? LPC::FT_BUY_LNK_DEF
                ];
                foreach ($fields as $name => $value) {
                    LandingPageSetting::updateOrCreate(['name' => $name], ['value' => $value]);
                    Log::info("$action saved field", ['field' => $name]);
                }
                DB::commit();
                $this->logExecutionTime($stepStart, 'DB transaction', 'completed');
                Log::info("$action completed");
                return redirect()->back()->with(['success' => 'Settings updated']);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    /**
     * GET /landingpage/features/{id}/edit
     */
    public function edit(Request $request, string|int $id): mixed
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id) {
            Log::info("[$action] invoked", ['user_id' => Auth::id(), 'id' => $id]);
            $checkStart = microtime(true);
            $ur = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;
            $permStart = microtime(true);
            if ($user[UsersConstants::COL_TP] !== PermissionsConstants::SA) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id]);
                Log::debug("[$action] user type '{$user[UsersConstants::COL_TP]}' lacks SA");
                $this->logExecutionTime($permStart, $action . '::permission', 'error');
                return defaultPermissionDenial($request, null, $action);
            }
            $this->logExecutionTime($permStart, $action . '::permission', 'completed');
            try {
                $findStart = microtime(true);
                $setting = LandingPageSetting::queryByKey($id);
                $this->logExecutionTime($findStart, $action . '::findOrFail', 'completed');
                Log::info("[$action] loaded setting for edit", ['id' => $id, 'name' => $setting->name]);
                $view = self::getFirstExistingView(self::ENTITY . '.edit');
                if (!$view) {
                    Log::warning("[edit] view not found", ['attempted' => self::ENTITY . '.edit']);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.edit');
                }
                Log::debug("[edit] resolved view", ['view' => $view]);
                return view($view, compact('setting'));
            } catch (ModelNotFoundException $e) {
                Log::warning("[$action] setting not found", ['id' => $id, 'error' => ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'type' => get_class($e)], 'uri' => $request->getRequestUri()]);
                Log::debug("[$action] exception trace", ['id' => $id, 'request' => ['method' => $request->getMethod(), 'uri' => $request->getRequestUri(), 'headers' => $request->headers->all()], 'error' => ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'type' => get_class($e)], 'trace' => $e->getTraceAsString()]);
                return redirect()->back()->with('error', __('Setting not found'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['id' => $id, 'user_id' => Auth::id()]);
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => Auth::id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            DB::beginTransaction();
            $this->logExecutionTime($stepStart, 'beginTransaction', 'completed');
            try {
                $stepStart = microtime(true);
                $setting = LandingPageSetting::findOrFail($id);
                $this->logExecutionTime($stepStart, 'findSetting', 'completed');
                $stepStart = microtime(true);
                $setting->value = $request->value ?? $setting->value;
                $setting->save();
                $this->logExecutionTime($stepStart, 'saveSetting', 'completed');
                $stepStart = microtime(true);
                DB::commit();
                $this->logExecutionTime($stepStart, 'commitTransaction', 'completed');
                Log::info($method . ' - succeeded', ['user_id' => Auth::id(), 'id' => $id]);
                return redirect()->back()->with(['success' => 'Setting updated']);
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'id' => $id]);
                Log::error($method . ' - failed to update setting', ['user_id' => Auth::id(), 'id' => $id]);
                DB::rollBack();
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['user_id' => Auth::id(), 'id' => $id]);
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' invoked', [UsersConstants::COL_USER_ID => Auth::id(), 'id' => $id]);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            DB::beginTransaction();
            try {
                $startDelete = microtime(true);
                LandingPageSetting::where('id', $id)->delete();
                DB::commit();
                $this->logExecutionTime($startDelete, $function . '::delete', 'completed');
                return redirect()->back()->with(['success' => 'Setting deleted']);
            } catch (\Throwable $e) {
                $startError = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($startError, $function . '::rollback', 'failed');
                Log::error($method . ' failed to delete setting', ['id' => $id, 'error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public const FTR_CRT = 'featureCreate';
    public function featureCreate(): mixed
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($action) {
            Log::info("$action invoked", [UsersConstants::COL_USER_ID => Auth::id()]);
            $stepStart = microtime(true);
            try {
                LandingPageSetting::settings();
                $this->logExecutionTime($stepStart, 'settings', 'completed');
                Log::info("$action completed");
                $view = self::getFirstExistingView(self::ENTITY . '.create');
                if (!$view) {
                    Log::warning("[create] view not found", ['attempted' => self::ENTITY . '.create']);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.create');
                }
                Log::debug("[create] resolved view", ['view' => $view]);
                return view($view);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'An error occurred while loading the feature creation form.');
            }
        });
    }

    public const FTR_STR = 'featureStore';
    public function featureStore(Request $request): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            Log::info("[$action] invoked", ['user_id' => Auth::id()]);
            $txStart = microtime(true);
            DB::beginTransaction();
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $list = json_decode($settings[self::FEATURE_NAME] ?? '[]', true) ?: [];
                $item = ['feature_heading' => $request->feature_heading, 'feature_description' => $request->feature_description];
                if ($request->feature_logo) {
                    $file = time() . '-feature_logo.' . $request->feature_logo->getClientOriginalExtension();
                    $uploadStart = microtime(true);
                    $upload = LandingPageSetting::uploadFile($request, 'feature_logo', $file, self::DIR, []);
                    $this->logExecutionTime($uploadStart, $action . '::uploadFile', 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning("[$action] upload failed", ['msg' => $upload['msg']]);
                        Log::debug("[$action] debug upload details", ['upload' => $upload]);
                        DB::rollBack();
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $item['feature_logo'] = $file;
                    Log::info("[$action] uploaded logo", ['file' => $file]);
                }
                $list[] = $item;
                $updateStart = microtime(true);
                LandingPageSetting::updateOrCreate(['name' => self::FEATURE_NAME], ['value' => json_encode($list)]);
                $this->logExecutionTime($updateStart, $action . '::updateOrCreate', 'completed');
                DB::commit();
                $this->logExecutionTime($txStart, $action . '::transaction', 'completed');
                Log::info("[$action] added feature", ['total' => count($list)]);
                return redirect()->back()->with(['success' => 'Feature added']);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, []);
    }

    public const FTR_EDT = 'featureEdit';
    public function featureEdit(string|int $key): mixed
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => Auth::id(), 'key' => $key]);
        return $this->measureProfile($method, function () use ($key, $method) {
            try {
                $stepStart = microtime(true);
                Log::info($method, ['user_id' => Auth::id(), 'key' => $key]);
                $this->logExecutionTime($stepStart, 'logInvocation', 'completed');
                $stepStart = microtime(true);
                $list = json_decode(LandingPageSetting::settings()[self::FEATURE_NAME] ?? '[]', true) ?: [];
                $this->logExecutionTime($stepStart, 'decodeFeatureList', 'completed');
                Log::debug($method . ' - feature list loaded', ['count' => count($list)]);
                $stepStart = microtime(true);
                $view = self::getFirstExistingView(self::ENTITY . '.edit');
                if (!$view) {
                    Log::warning("[edit] view not found", ['attempted' => self::ENTITY . '.edit']);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.edit');
                }
                Log::debug("[edit] resolved view", ['view' => $view]);
                $this->logExecutionTime($stepStart, 'renderEditView', 'completed');
                return view($view, ['feature' => $list[$key] ?? null, 'key' => $key]);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' exception trace', ['trace' => $e->getTraceAsString()]);
                return redirect()->back()->with('error', 'An error occurred while loading the feature edit form.');
            }
        }, ['user_id' => Auth::id(), 'key' => $key]);
    }

    public const FTR_UPD = 'featureUpdate';
    public function featureUpdate(Request $request, string|int $key): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $key, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' invoked', [UsersConstants::COL_USER_ID => Auth::id(), 'key' => $key]);
            DB::beginTransaction();
            try {
                $startFetch = microtime(true);
                $list = json_decode(LandingPageSetting::settings()[static::FEATURE_NAME] ?? '[]', true);
                $this->logExecutionTime($startFetch, $function . '::settingsFetch', 'completed');
                foreach (['feature_heading', 'feature_description'] as $f) {
                    $fieldStart = microtime(true);
                    $list[$key][$f] = $request->$f;
                    Log::info($method . ' field updated', ['field' => $f, 'key' => $key]);
                    $this->logExecutionTime($fieldStart, $function . '::fieldUpdate', 'completed');
                }
                if ($request->feature_logo) {
                    $fileStart = microtime(true);
                    $file = time() . '-feature_logo.' . $request->feature_logo->getClientOriginalExtension();
                    $upload = LandingPageSetting::uploadFile($request, 'feature_logo', $file, static::DIR, []);
                    $this->logExecutionTime($fileStart, $function . '::uploadFile', $upload['flag'] === 0 ? 'failed' : 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning($method . ' upload failed', ['msg' => $upload['msg']]);
                        Log::debug($method . ' debug upload', ['response' => $upload]);
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $list[$key]['feature_logo'] = $file;
                    Log::info($method . ' logo updated', ['file' => $file, 'key' => $key]);
                }
                $updateStart = microtime(true);
                LandingPageSetting::updateOrCreate(['name' => static::FEATURE_NAME], ['value' => json_encode($list)]);
                $this->logExecutionTime($updateStart, $function . '::updateOrCreate', 'completed');
                DB::commit();
                Log::info($method . ' completed');
                return redirect()->back()->with(['success' => __('Feature updated')]);
            } catch (\Throwable $e) {
                $errorTime = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($errorTime, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public const FTR_DEL = 'featureDelete';
    public function featureDelete(Request $request, string|int $key): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $key, $action) {
            Log::info("$action invoked", [UsersConstants::COL_USER_ID => Auth::id(), 'key' => $key]);
            DB::beginTransaction();
            $stepStart = microtime(true);
            try {
                $list = json_decode(LandingPageSetting::settings()[self::FEATURE_NAME], true);
                unset($list[$key]);
                LandingPageSetting::updateOrCreate(['name' => self::FEATURE_NAME], ['value' => $list]);
                DB::commit();
                $this->logExecutionTime($stepStart, 'DB transaction', 'completed');
                Log::info("$action removed feature", ['remaining' => count($list)]);
                return redirect()->back()->with(['success' => 'Feature deleted']);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'key' => $key]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const FTR_HGL = 'featureHighlightCreate';
    public function featureHighlightCreate(Request $request): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            Log::info("[$action] invoked", ['user_id' => Auth::id()]);
            $txStart = microtime(true);
            DB::beginTransaction();
            try {
                $fields = ['highlight_feature_heading', 'highlight_feature_description'];
                $data = [];
                foreach ($fields as $f) {
                    $data[$f] = $request->$f;
                    Log::info("[$action] field set", ['field' => $f]);
                }
                if ($request->highlight_feature_image) {
                    $file = 'highlight_feature_image.' . $request->highlight_feature_image->getClientOriginalExtension();
                    $uploadStart = microtime(true);
                    $upload = LandingPageSetting::uploadFile($request, 'highlight_feature_image', $file, self::DIR, []);
                    $this->logExecutionTime($uploadStart, $action . '::uploadFile', 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning("[$action] upload failed", ['msg' => $upload['msg']]);
                        Log::debug("[$action] debug upload details", ['upload' => $upload]);
                        DB::rollBack();
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $data['highlight_feature_image'] = $file;
                    Log::info("[$action] image uploaded", ['file' => $file]);
                }
                $saveStart = microtime(true);
                foreach ($data as $name => $value) {
                    LandingPageSetting::updateOrCreate(['name' => $name], ['value' => $value]);
                    Log::info("[$action] saved", ['name' => $name]);
                }
                $this->logExecutionTime($saveStart, $action . '::saveSettings', 'completed');
                DB::commit();
                $this->logExecutionTime($txStart, $action . '::transaction', 'completed');
                Log::info("[$action] completed");
                return redirect()->back()->with(['success' => 'Highlight updated']);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, []);
    }

    public const FTRS_CRT = 'featuresCreate';
    public function featuresCreate(): mixed
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => Auth::id()]);
        return $this->measureProfile($method, function () use ($method) {
            try {
                $stepStart = microtime(true);
                Log::info($method, ['user_id' => Auth::id()]);
                $this->logExecutionTime($stepStart, 'logInvocation', 'completed');
                $stepStart = microtime(true);
                LandingPageSetting::settings();
                $this->logExecutionTime($stepStart, 'loadSettings', 'completed');
                $stepStart = microtime(true);
                $view = self::getFirstExistingView(self::ENTITY . '.features_create');
                if (!$view) {
                    Log::warning("[features_create] view not found", ['attempted' => self::ENTITY . '.features_create']);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.features_create');
                }
                Log::debug("[features_create] resolved view", ['view' => $view]);
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return view($view);
            } catch (\Throwable $e) {
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' exception trace', ['trace' => $e->getTraceAsString()]);
                return redirect()->back()->with('error', 'An error occurred while loading the features creation form.');
            }
        }, ['user_id' => Auth::id()]);
    }

    public const FTRS_STR = 'featuresStore';
    public function featuresStore(Request $request): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' invoked', [UsersConstants::COL_USER_ID => Auth::id()]);
            DB::beginTransaction();
            try {
                $startSettings = microtime(true);
                $settings = LandingPageSetting::settings();
                $list = json_decode($settings[static::OTHER_NAME] ?? '[]', true) ?? [];
                $this->logExecutionTime($startSettings, $function . '::settingsFetch', 'completed');
                $item = [
                    'other_features_heading'     => $request->other_features_heading,
                    'other_featured_description' => $request->other_featured_description,
                    'other_feature_buy_now_link' => $request->other_feature_buy_now_link
                ];
                if ($request->other_features_image) {
                    $startUpload = microtime(true);
                    $file = time() . '-other_features_image.' . $request->other_features_image->getClientOriginalExtension();
                    $upload = LandingPageSetting::uploadFile($request, 'other_features_image', $file, static::DIR, []);
                    $this->logExecutionTime($startUpload, $function . '::uploadFile', $upload['flag'] === 0 ? 'failed' : 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning($method . ' upload failed', ['msg' => $upload['msg']]);
                        Log::debug($method . ' debug upload', ['response' => $upload]);
                        DB::rollBack();
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $item['other_features_image'] = $file;
                    Log::info($method . ' image set', ['file' => $file]);
                }
                $list[] = $item;
                $startUpdate = microtime(true);
                LandingPageSetting::updateOrCreate(
                    ['name' => static::OTHER_NAME],
                    ['value' => json_encode($list)]
                );
                $this->logExecutionTime($startUpdate, $function . '::updateOrCreate', 'completed');
                DB::commit();
                Log::info($method . ' completed');
                return redirect()->back()->with(['success' => __('Other feature added')]);
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public const FTRS_EDT = 'featuresEdit';
    public function featuresEdit(string|int $key): mixed
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($key, $action) {
            Log::info("$action invoked", [UsersConstants::COL_USER_ID => Auth::id(), 'key' => $key]);
            $stepStart = microtime(true);
            try {
                $list = json_decode(LandingPageSetting::settings()[self::OTHER_NAME], true) ?? [];
                $this->logExecutionTime($stepStart, 'settings', 'completed');
                Log::info("$action completed", ['key' => $key]);
                $view = self::getFirstExistingView(self::ENTITY . '.features_edit');
                if (!$view) {
                    Log::warning("[features_edit] view not found", ['attempted' => self::ENTITY . '.features_edit']);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.features_edit');
                }
                Log::debug("[features_edit] resolved view", ['view' => $view]);
                return view($view, ['other_features' => $list[$key], 'key' => $key]);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'key' => $key]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'key' => $key]);
                return redirect()->back()->with('error', 'An error occurred while loading the other feature edit form.');
            }
        });
    }

    public const FTRS_UPD = 'featuresUpdate';
    public function featuresUpdate(Request $request, string|int $key): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $key) {
            Log::info("[$action] invoked", ['user_id' => Auth::id(), 'key' => $key]);
            $txStart = microtime(true);
            DB::beginTransaction();
            try {
                $settingsStart = microtime(true);
                $list = json_decode(LandingPageSetting::settings()[self::OTHER_NAME] ?? '[]', true) ?: [];
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                foreach (['other_features_heading', 'other_featured_description', 'other_feature_buy_now_link'] as $f)
                    $list[$key][$f] = $request->$f;
                if ($request->other_features_image) {
                    $file = time() . '-other_features_image.' . $request->other_features_image->getClientOriginalExtension();
                    $uploadStart = microtime(true);
                    $upload = LandingPageSetting::uploadFile($request, 'other_features_image', $file, self::DIR, []);
                    $this->logExecutionTime($uploadStart, $action . '::uploadFile', 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning("[$action] upload failed", ['msg' => $upload['msg']]);
                        Log::debug("[$action] debug upload details", ['upload' => $upload]);
                        DB::rollBack();
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $list[$key]['other_features_image'] = $file;
                    Log::info("[$action] image uploaded", ['file' => $file]);
                }
                $updateStart = microtime(true);
                LandingPageSetting::updateOrCreate(['name' => self::OTHER_NAME], ['value' => json_encode($list)]);
                $this->logExecutionTime($updateStart, $action . '::updateOrCreate', 'completed');
                DB::commit();
                $this->logExecutionTime($txStart, $action . '::transaction', 'completed');
                Log::info("[$action] completed", ['key' => $key]);
                return redirect()->back()->with(['success' => 'Other feature updated']);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'key' => $key]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['key' => $key]);
    }

    public const FTRS_DEL = 'featuresDelete';
    public function featuresDelete(Request $request, string|int $key): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => Auth::id(), 'key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            $stepStart = microtime(true);
            DB::beginTransaction();
            $this->logExecutionTime($stepStart, 'beginTransaction', 'completed');
            try {
                $stepStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($stepStart, 'loadSettings', 'completed');
                $stepStart = microtime(true);
                $list = json_decode($settings[self::OTHER_NAME] ?? '[]', true);
                $this->logExecutionTime($stepStart, 'decodeList', 'completed');
                $stepStart = microtime(true);
                unset($list[$key]);
                $this->logExecutionTime($stepStart, 'unsetItem', 'completed');
                $stepStart = microtime(true);
                LandingPageSetting::updateOrCreate(['name' => self::OTHER_NAME], ['value' => $list]);
                $this->logExecutionTime($stepStart, 'persistSettings', 'completed');
                $stepStart = microtime(true);
                DB::commit();
                $this->logExecutionTime($stepStart, 'commitTransaction', 'completed');
                return redirect()->back()->with(['success' => 'Other feature deleted']);
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'user_id' => Auth::id(), 'key' => $key]);
                Log::error($method . ' - failed to delete feature', ['user_id' => Auth::id(), 'key' => $key]);
                DB::rollBack();
                return defaultUndefinedException($request, $e, $method);
            }
        }, ['user_id' => Auth::id(), 'key' => $key]);
    }
}
