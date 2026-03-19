<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    UsersConstants as UC
};
use App\Http\Controllers\Abstracts\Controller as AppController;
use App\Models\User;
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Collection;
use Modules\LandingPage\{Config\Constants\RoutesResourcesConstants as RRC, Entities\LandingPageSetting};
use function App\Http\Controllers\Helpers\{defaultPermissionDenial, defaultUndefinedException};

final class TestimonialsController extends AppController
{
    use ChecksLogin, ChecksPermissions;

    public const ENTITY  = RRC::TTMN;
    private const LP = RRC::LP;
    private const REDIRECT_INDEX = RRC::TTMN . '.index';
    private const UPLOAD_DIR    = 'uploads/landing_page_image';

    public function index(Request $request): Renderable|RedirectResponse|null
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $function) {
            $ur = self::_checkLogin(haltRedirect: true);
            if (!($ur instanceof User) || $ur->type !== 'super admin') {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::landingPageSetting();
                $this->logExecutionTime($settingsStart, $action . '::landingPageSetting', 'completed');
                $itemsStart = microtime(true);
                $items = json_decode($settings[self::ENTITY] ?? '[]', true) ?? [];
                $items = is_array($items) ? collect($items)->sortByDesc('created_at')->toArray() : ($items instanceof Collection ? $items->sortByDesc('created_at')->toArray() : []);
                $this->logExecutionTime($itemsStart, $action . '::decodeItems', 'completed');
                $view = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                Log::info("[$action] loaded", ['count' => count($items)]);
                return view($view, [
                    DC::TABLE_SETTINGS => $settings,
                    self::ENTITY => $items
                ]);
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, []);
    }

    public function show(Request $request, int $key): RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['key' => $key, 'user_id' => auth()->id()]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            $stepStart = microtime(true);
            Log::info($method . ' called', ['key' => $key, 'user_id' => auth()->id()]);
            $this->logExecutionTime($stepStart, 'logInvocation', 'completed');
            $stepStart = microtime(true);
            if (($redirect = self::guard($request, PMC::MNG_TT, self::REDIRECT_INDEX)) !== true)
                return $redirect;
            $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
            return redirect()->route(self::REDIRECT_INDEX);
        }, ['key' => $key, 'user_id' => auth()->id()]);
    }

    public function create(Request $request): Renderable|RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' start', [UC::COL_USER_ID => auth()->id()]);
            if (($user = static::_checkLogin()) instanceof RedirectResponse) return $user;
            $startGuard = microtime(true);
            if (($redirect = static::guard($request, PMC::MNG_TT, static::REDIRECT_INDEX)) !== true) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                return $redirect;
            }
            $this->logExecutionTime($startGuard, $function . '::guard', 'completed');

            try {
                $startView = microtime(true);
                $view = self::getFirstExistingView(static::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning("[$method] view not found", ['attempted' => static::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . static::ENTITY . '.' . $function);
                }
                $this->logExecutionTime($startView, $function . '::view', 'completed');
                return view($view);
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                return defaultUndefinedException($request, $e, $method, route(static::REDIRECT_INDEX));
            }
        }, func_get_args());
    }

    public function store(Request $request): RedirectResponse|null
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action start", [UC::COL_USER_ID => auth()->id()]);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            if (($redirect = self::guard($request, PMC::MNG_TT, self::REDIRECT_INDEX)) !== true) return $redirect;
            $request->validate([
                self::ENTITY . '_heading' => 'required|string',
                self::ENTITY . '_description' => 'required|string',
                self::ENTITY . '_long_description' => 'nullable|string',
                self::ENTITY . '_user' => 'required|string',
                self::ENTITY . '_designation' => 'required|string',
                self::ENTITY . '_star' => 'required|integer|min:1|max:5',
                self::ENTITY . '_user_avatar' => 'nullable|image',
            ]);
            DB::beginTransaction();
            $stepStart = microtime(true);
            try {
                $settings = LandingPageSetting::settings();
                $list = json_decode($settings[self::ENTITY] ?? '[]', true);
                $item = [
                    self::ENTITY . '_heading' => $request->input(self::ENTITY . '_heading'),
                    self::ENTITY . '_description' => $request->input(self::ENTITY . '_description'),
                    self::ENTITY . '_long_description' => $request->input(self::ENTITY . '_long_description', ''),
                    self::ENTITY . '_user' => $request->input(self::ENTITY . '_user'),
                    self::ENTITY . '_designation' => $request->input(self::ENTITY . '_designation'),
                    self::ENTITY . '_star' => $request->input(self::ENTITY . '_star'),
                ];
                if ($request->hasFile(self::ENTITY . 'UserAvatar')) {
                    $file = time() . '-avatar.' . $request->file(self::ENTITY . 'UserAvatar')->getClientOriginalExtension();
                    $upload = LandingPageSetting::uploadFile($request, self::ENTITY . 'UserAvatar', $file, self::UPLOAD_DIR, []);
                    if ($upload['flag'] === 0) {
                        Log::warning("$action upload failed", ['msg' => $upload['msg']]);
                        DB::rollBack();
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $item[self::ENTITY . 'UserAvatar'] = $file;
                    Log::info("$action avatar uploaded", ['file' => $file]);
                }
                $list[] = $item;
                LandingPageSetting::updateOrCreate(['name' => self::ENTITY], ['value' => json_encode($list)]);
                DB::commit();
                $this->logExecutionTime($stepStart, 'settings update', 'completed');
                Log::info("$action succeeded", ['count' => count($list)]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Testimonial added successfully'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, int $key): Renderable|RedirectResponse|null
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $key, $function) {
            Log::info("[$action] start", ['key' => $key, UC::COL_USER_ID => auth()->id()]);
            $checkStart = microtime(true);
            $user = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($user instanceof RedirectResponse) return $user;
            $guardStart = microtime(true);
            $redirect = self::guard($request, PMC::MNG_TT, self::REDIRECT_INDEX);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($redirect instanceof RedirectResponse) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id, 'key' => $key]);
                Log::debug("[$action] lacks MNG_TT permission", ['user_id' => $user?->id, 'key' => $key]);
                return $redirect;
            }
            try {
                $settingsStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
                $decodeStart = microtime(true);
                $list = json_decode($settings[self::ENTITY] ?? '[]', true);
                $this->logExecutionTime($decodeStart, $action . '::decodeList', 'completed');
                if (!isset($list[$key])) {
                    Log::warning("[$action] not found", ['key' => $key]);
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', __('Testimonial not found'));
                }
                $view = self::getFirstExistingView(self::ENTITY . '.' . $function);
                if (!$view) {
                    Log::warning("[$action] view not found", ['attempted' => self::ENTITY . '.' . $function]);
                    throw new \RuntimeException("View not found: " . self::ENTITY . '.' . $function);
                }
                Log::info("[$action] loaded", ['key' => $key]);
                return view($view, ['testimonial' => $list[$key], 'key' => $key]);
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
        Log::debug($method . ' - start', ['key' => $key, 'user_id' => auth()->id()]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            $stepStart = microtime(true);
            Log::info($method . ' start', ['key' => $key, 'user_id' => auth()->id()]);
            $this->logExecutionTime($stepStart, 'logStart', 'completed');
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $stepStart = microtime(true);
            if (($redirect = self::guard($request, PMC::MNG_TT, self::REDIRECT_INDEX)) !== true) return $redirect;
            $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
            $stepStart = microtime(true);
            $request->validate([
                self::ENTITY . '_heading' => 'required|string',
                self::ENTITY . '_description' => 'required|string',
                self::ENTITY . '_long_description' => 'nullable|string',
                self::ENTITY . '_user' => 'required|string',
                self::ENTITY . '_designation' => 'required|string',
                self::ENTITY . '_star' => 'required|integer|min:1|max:5',
                self::ENTITY . '_user_avatar' => 'nullable|image',
            ]);
            $this->logExecutionTime($stepStart, 'validateRequest', 'completed');
            DB::beginTransaction();
            try {
                $stepStart = microtime(true);
                $settings = LandingPageSetting::settings();
                $list = json_decode($settings[self::ENTITY] ?? '[]', true);
                $this->logExecutionTime($stepStart, 'decodeSettings', 'completed');
                if (!isset($list[$key])) {
                    Log::warning($method . ' not found', ['key' => $key]);
                    DB::rollBack();
                    return redirect()->route(self::REDIRECT_INDEX)->with('error', __('Testimonial not found'));
                }
                foreach (
                    [
                        self::ENTITY . '_heading',
                        self::ENTITY . '_description',
                        self::ENTITY . '_long_description',
                        self::ENTITY . '_user',
                        self::ENTITY . '_designation',
                        self::ENTITY . '_star'
                    ] as $field
                )
                    $list[$key][$field] = $request->input($field);
                $this->logExecutionTime($stepStart, 'updateFields', 'completed');
                if ($request->hasFile(self::ENTITY . '_user_avatar')) {
                    $stepStart = microtime(true);
                    $file = time() . '-avatar.' . $request->file(self::ENTITY . '_user_avatar')->getClientOriginalExtension();
                    $upload = LandingPageSetting::uploadFile(
                        $request,
                        self::ENTITY . '_user_avatar',
                        $file,
                        self::UPLOAD_DIR,
                        []
                    );
                    $this->logExecutionTime($stepStart, 'uploadAvatar', 'completed');
                    if ($upload['flag'] === 0) {
                        Log::warning($method . ' upload failed', ['msg' => $upload['msg']]);
                        DB::rollBack();
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $list[$key][self::ENTITY . 'UserAvatar'] = $file;
                    Log::info($method . ' avatar updated', ['file' => $file, 'key' => $key]);
                }
                $stepStart = microtime(true);
                LandingPageSetting::updateOrCreate(
                    ['name' => self::ENTITY],
                    ['value' => json_encode($list)]
                );
                $this->logExecutionTime($stepStart, 'persistSettings', 'completed');
                DB::commit();
                Log::info($method . ' succeeded', ['key' => $key]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Testimonial updated successfully'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'key' => $key]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['key' => $key]);
    }

    public function destroy(Request $request, int $key): RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $key, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' start', ['key' => $key, UC::COL_USER_ID => auth()->id()]);
            $startLogin = microtime(true);
            if (($user = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $user;
            }
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            $startGuard = microtime(true);
            if (($redirect = static::guard($request, PMC::MNG_TT, static::REDIRECT_INDEX)) !== true) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                return $redirect;
            }
            $this->logExecutionTime($startGuard, $function . '::guard', 'completed');
            DB::beginTransaction();
            try {
                $startFetch = microtime(true);
                $settings = LandingPageSetting::settings();
                $list = json_decode($settings[static::ENTITY] ?? '[]', true);
                $this->logExecutionTime($startFetch, $function . '::fetchSettings', 'completed');

                if (!isset($list[$key])) {
                    $timeNotFound = microtime(true);
                    Log::warning($method . ' not found', ['key' => $key]);
                    $this->logExecutionTime($timeNotFound, $function . '::notFound', 'failed');
                    DB::rollBack();
                    return redirect()->route(static::REDIRECT_INDEX)->with('error', __('Testimonial not found'));
                }

                unset($list[$key]);

                $startUpdate = microtime(true);
                LandingPageSetting::updateOrCreate(
                    ['name' => static::ENTITY],
                    ['value' => json_encode(array_values($list))]
                );
                $this->logExecutionTime($startUpdate, $function . '::updateOrCreate', 'completed');
                DB::commit();
                Log::info($method . ' succeeded', ['key' => $key, 'remaining' => count($list)]);
                return redirect()->route(static::REDIRECT_INDEX)
                    ->with('success', __('Testimonial deleted successfully'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                DB::rollBack();
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method, route(static::REDIRECT_INDEX));
            }
        }, func_get_args());
    }

    public const TTM_CRT = 'testimonialsCreate';
    public function testimonialsCreate(Request $request)
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id()]);
            $stepStart = microtime(true);
            try {
                $response = $this->create($request);
                $this->logExecutionTime($stepStart, 'create action', 'completed');
                return $response;
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    public const TTM_STR = 'testimonialsStore';
    public function testimonialsStore(Request $request)
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            Log::info("[$action] called", ['user_id' => auth()->id()]);
            $start = microtime(true);
            $response = $this->store($request);
            $this->logExecutionTime($start, $action . '::store', 'completed');
            return $response;
        }, []);
    }

    public const TTM_EDT = 'testimonialsEdit';
    public function testimonialsEdit(Request $request, string|int $key)
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => auth()->id(), 'key' => $key]);
        return $this->measureProfile($method, function () use ($request, $key, $method) {
            Log::info($method . ' called', ['user_id' => auth()->id(), 'key' => $key]);
            return $this->edit($request, $key);
        }, ['user_id' => auth()->id(), 'key' => $key]);
    }

    public const TTM_UPD = 'testimonialsUpdate';
    public function testimonialsUpdate(Request $request, string|int $key)
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $key, $function) {
            $method = static::class . '::' . $function;
            Log::info($method . ' called', [UC::COL_USER_ID => auth()->id(), 'key' => $key]);
            try {
                $startUpdate = microtime(true);
                $response = $this->update($request, $key);
                $this->logExecutionTime($startUpdate, $function . '::update', 'completed');
                return $response;
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                throw $e;
            }
        }, func_get_args());
    }

    public const TTM_DEL = 'testimonialsDelete';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function testimonialsDelete(Request $request, string|int $key)
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $key, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), 'key' => $key]);
            $stepStart = microtime(true);
            try {
                $response = $this->destroy($request, $key);
                $this->logExecutionTime($stepStart, 'destroy action', 'completed');
                return $response;
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'key' => $key]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'key' => $key]);
                throw $e;
            }
        });
    }
}
