<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Http\Controllers\Controller as AppController;
use App\Models\User;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\LandingPage\Config\Constants\{RoutesResourcesConstants, SettingsConstants as LandingPageSettingsConstants};
use Modules\LandingPage\Entities\{JoinUs, LandingPageSetting};
use function App\Http\Controllers\{defaultUndefinedException};

class JoinUsController extends AppController
{
    use ChecksLogin, ChecksPermissions;

    private const LP = RoutesResourcesConstants::LP;
    private const JU = RoutesResourcesConstants::JU;
    private const REDIRECT_INDEX = self::JU . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            $userId = '#UNAUTHENTICATED';
            $checkStart = microtime(true);
            $ur = self::_checkLogin(haltRedirect: true);
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof User) $userId = $ur->id;
            Log::info("[$action] started", ['user_id' => $userId]);
            try {
                $fetchStart = microtime(true);
                $entries = JoinUs::all();
                $this->logExecutionTime($fetchStart, $action . '::fetchEntries', 'completed');
                Log::info("[$action] succeeded", ['user_id' => $userId, 'count' => count($entries)]);
                return view(self::LP . '::' . self::LP . '.' . self::JU, compact('entries'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['user_id' => $userId, 'error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['user_id' => Auth::id()]);
    }

    public function show(Request $request, int $id): View|RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            $userId = '#UNAUTHENTICATED';
            if (($ur = self::_checkLogin(haltRedirect: true)) instanceof User) $userId = $ur->id;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            Log::info($method . ' - start', ['user_id' => $userId, 'id' => $id]);
            try {
                $stepStart = microtime(true);
                $entry = JoinUs::findOrFail($id);
                $this->logExecutionTime($stepStart, 'findEntry', 'completed');
                Log::info($method . ' - succeeded', ['user_id' => $userId, 'id' => $id]);
                $stepStart = microtime(true);
                $view = view(self::LP . '::' . self::LP . '.' . self::JU . '.' . __FUNCTION__, compact('entry'));
                $this->logExecutionTime($stepStart, 'renderView', 'completed');
                return $view;
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'user_id' => $userId, 'id' => $id]);
                Log::error($method . ' - failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['id' => $id]);
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($ur = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $ur;
            }
            $user = $ur;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            $startGuard = microtime(true);
            if ($g = static::guard($request, PermissionsConstants::MNG_LP, static::REDIRECT_INDEX)) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                Log::debug($method . ' debug guard', ['redirect' => $g]);
                return $g;
            }
            $this->logExecutionTime($startGuard, $function . '::guard', 'completed');
            Log::info($method . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $startSettings = microtime(true);
            $settings = LandingPageSetting::settings();
            $this->logExecutionTime($startSettings, $function . '::settingsFetch', 'completed');
            Log::info($method . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            $startView = microtime(true);
            $view = view(static::LP . '::' . static::LP . '.' . static::JU . '.' . DatabaseConstants::TABLE_SETTINGS, compact(DatabaseConstants::TABLE_SETTINGS));
            $this->logExecutionTime($startView, $function . '::view', 'completed');
            return $view;
        }, func_get_args());
    }

    public function store(Request $request): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
            $user = $ur;
            if ($g = self::guard($request, PermissionsConstants::MNG_LP, self::REDIRECT_INDEX)) return $g;
            Log::info("$action started", [UsersConstants::COL_USER_ID => $user?->id]);
            $data = $request->validate([
                LandingPageSettingsConstants::JU_STT_K => 'nullable',
                LandingPageSettingsConstants::JU_HDG_K => 'nullable|string',
                LandingPageSettingsConstants::JU_DESC_K => 'nullable|string'
            ]);
            $settings = [
                self::JU . 'Status' => $request->has(LandingPageSettingsConstants::JU_STT_K) ? 'on' : 'off',
                self::JU . 'Heading' => $data[LandingPageSettingsConstants::JU_HDG_K] ?? '',
                self::JU . 'Description' => $data[LandingPageSettingsConstants::JU_DESC_K] ?? ''
            ];
            DB::beginTransaction();
            $stepStart = microtime(true);
            try {
                collect($settings)->each(fn ($v, $k) => LandingPageSetting::updateOrCreate(['name' => Str::snake($k)], ['value' => $v]));
                DB::commit();
                $this->logExecutionTime($stepStart, 'settings update', 'completed');
                Log::info("$action succeeded", [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Settings updated successfully'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage(), UsersConstants::COL_USER_ID => $user?->id]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, int $id): View|RedirectResponse|null
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id, $function) {
            $checkStart = microtime(true);
            $ur = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;
            $guardStart = microtime(true);
            $g = self::guard($request, PermissionsConstants::MNG_LP, self::REDIRECT_INDEX);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($g instanceof RedirectResponse) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id, 'id' => $id]);
                Log::debug("[$action] lacks MNG_LP permission", ['user_id' => $user?->id]);
                return $g;
            }
            Log::info("[$action] started", ['user_id' => $user?->id, 'id' => $id]);
            try {
                $findStart = microtime(true);
                $entry = JoinUs::findOrFail($id);
                $this->logExecutionTime($findStart, $action . '::findOrFail', 'completed');
                Log::info("[$action] succeeded", ['user_id' => $user?->id, 'id' => $id]);
                return view(self::LP . '::' . self::LP . '.' . self::JU . '.' . $function, compact('entry'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::warning("[$action] entry not found", ['id' => $id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return redirect()->route(self::REDIRECT_INDEX)->with('error', __('Entry not found'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'id' => $id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['id' => $id]);
    }

    public function update(Request $request, int $id): RedirectResponse|null
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => Auth::id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
            $user = $ur;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if ($g = self::guard($request, PermissionsConstants::MNG_LP, self::REDIRECT_INDEX)) return $g;
            $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
            Log::info($method . ' - started', ['user_id' => $user?->id, 'id' => $id]);
            $stepStart = microtime(true);
            $data = $request->validate([
                'email' => 'required|email|unique:join_us,email,' . $id
            ]);
            $this->logExecutionTime($stepStart, 'validation', 'completed');
            try {
                $stepStart = microtime(true);
                JoinUs::whereKey($id)->update(['email' => $data['email']]);
                $this->logExecutionTime($stepStart, 'updateEmail', 'completed');
                Log::info($method . ' - succeeded', ['user_id' => $user?->id, 'id' => $id]);
                return redirect()->back()->with('success', __('Entry updated successfully'));
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'user_id' => $user?->id, 'id' => $id]);
                Log::error($method . ' - failed', ['user_id' => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['user_id' => Auth::id(), 'id' => $id]);
    }

    public function destroy(Request $request, int $id): RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($ur = static::_checkLogin()) instanceof RedirectResponse) return $this->logExecutionTime($startLogin, $function . '::login', 'failed') ?: $ur;
            $user = $ur;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            $startGuard = microtime(true);
            if ($g = static::guard($request, PermissionsConstants::MNG_LP, static::REDIRECT_INDEX)) {
                Log::warning($method . ' permission denied', ['user_id' => $user?->id]);
                $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
                Log::debug($method . ' debug guard', ['redirect' => $g]);
                return $g;
            }
            $this->logExecutionTime($startGuard, $function . '::guard', 'completed');
            Log::info($method . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
            try {
                $startDestroy = microtime(true);
                JoinUs::destroy($id);
                $this->logExecutionTime($startDestroy, $function . '::destroy', 'completed');
                Log::info($method . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
                return redirect()->back()->with('success', __('Entry deleted successfully'));
            } catch (\Throwable $e) {
                $errorTime = microtime(true);
                $this->logExecutionTime($errorTime, $function . '::exception', 'failed');
                Log::error($method . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method, route(static::REDIRECT_INDEX));
            }
        }, func_get_args());
    }

    public const JU_U_ST = 'joinUsUserStore';
    public function joinUsUserStore(Request $request): JsonResponse|RedirectResponse
    {
        $class  = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($ur = self::_checkLogin()) instanceof RedirectResponse)
                return $ur;
            Log::info("$action started", [
                'ip'        => $request->ip(),
                'referer'   => $request->headers->get('referer'),
                'uri'       => $request->getRequestUri(),
                'method'    => $request->method(),
                'user_id'   => optional($request->user())->getKey(),
                'action'    => $action,
            ]);
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:join_us',
            ]);
            if ($validator->fails()) {
                Log::warning("$action validation failed", [
                    'errors' => $validator->errors()->all(),
                    'input'  => $request->all(),
                ]);
                $errorHtml = view('partials.validation.admin_error', [
                    'errors' => $validator->errors(),
                ])->render();
                if ($request->expectsJson())
                    return response()->json([
                        'status' => 'validation_error',
                        'html'   => $errorHtml,
                    ], 422);
                return redirect()->back()
                    ->withInput()
                    ->with('error_html', $errorHtml);
            }
            $data      = $validator->validated();
            $stepStart = microtime(true);
            try {
                $model = JoinUs::create($data);
                $this->logExecutionTime($stepStart, 'create', 'completed');
                Log::info("$action succeeded", [
                    'user_id'  => optional($request->user())->getKey(),
                    'email'    => $data['email'],
                    'model_id' => $model->getKey(),
                ]);
                if ($request->expectsJson())
                    return response()->json([
                        'status'  => 'success',
                        'message' => __('You have joined our community'),
                    ]);
                return redirect()->back()
                    ->with('success', __('You have joined our community'));
            } catch (\Throwable $e) {
                Log::error("$action exception", [
                    'exception' => $e,
                    'input'     => $request->all(),
                ]);
                if ($request->expectsJson())
                    return response()->json([
                        'status'  => 'error',
                        'message' => $e->getMessage(),
                    ], 500);
                return redirect()->back()
                    ->with('error', $e->getMessage());
            }
        });
    }
}
