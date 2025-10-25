<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Http\Controllers\Controller as AppController;
use App\Models\User;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\{Support\Str, View\View};
use Modules\LandingPage\{
    Config\Constants\SettingsConstants,
    Config\Constants\RoutesResourcesConstants,
    Entities\LandingPageSetting
};
use Symfony\Component\Console\Output\ConsoleOutput;
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class HomeController extends AppController
{
    use ChecksLogin, ChecksPermissions;

    public const ENTITY = ViewsConstants::HM;
    private const LP = RoutesResourcesConstants::LP;
    private const REDIRECT_INDEX = RoutesResourcesConstants::HM . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            $output = new ConsoleOutput;
            $steps = [
                'start'            => 'Starting home index',
                'loginCheck'       => 'Checking login',
                'permissionCheck'  => 'Checking permissions',
                'loadSettings'     => 'Loading landing page settings',
                'settingsLoaded'   => 'Settings loaded',
                'renderView'       => 'Rendering home view'
            ];
            foreach ([$steps['start'], $steps['loginCheck']] as $msg) {
                app()->runningInConsole()
                    ? $output->writeln("<info> {$msg} </info>")
                    : $output->writeln("## HOME: {$msg}");
            }
            $startLogin = microtime(true);
            if (($ur = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $ur;
            }
            $userId = $ur->id;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            $startGuard = microtime(true);
            // if ($g = static::guard($request, PermissionsConstants::MNG_LP, static::REDIRECT_INDEX)) {
            //     Log::warning($method . ' permission denied', ['user_id' => $userId]);
            //     $this->logExecutionTime($startGuard, $function . '::guard', 'failed');
            //     Log::debug($method . ' debug guard', ['redirect' => $g]);
            //     return $g;
            // }
            // $this->logExecutionTime($startGuard, $function . '::guard', 'completed');
            Log::info($method . ' login and permission checks passed', [UsersConstants::COL_USER_ID => $userId]);
            foreach ([$steps['permissionCheck'], $steps['loadSettings']] as $msg) {
                app()->runningInConsole()
                    ? $output->writeln("<comment> {$msg} </comment>")
                    : $output->writeln("## HOME: {$msg}");
            }
            $startSettings = microtime(true);
            $settings = LandingPageSetting::landingPageSetting();
            $this->logExecutionTime($startSettings, $function . '::loadSettings', 'completed');
            Log::info($method . ' loaded settings', ['user_id' => $userId, 'keys' => array_keys($settings)]);
            foreach ([$steps['settingsLoaded'], $steps['renderView']] as $msg) {
                app()->runningInConsole()
                    ? $output->writeln("<info> {$msg} </info>")
                    : $output->writeln("## HOME: {$msg}");
            }
            $startView = microtime(true);
            $view = view(static::LP . '::' . static::LP . '.' . RoutesResourcesConstants::HM, compact(DatabaseConstants::TABLE_SETTINGS));
            $this->logExecutionTime($startView, $function . '::view', 'completed');
            Log::info($method . ' succeeded', [UsersConstants::COL_USER_ID => $userId]);
            return $view;
        }, func_get_args());
    }

    public function show(Request $request, int $id): View|RedirectResponse|null
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            Log::info("$action invoked", ['id' => $id]);
            $stepStart = microtime(true);
            try {
                if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
                Log::warning("$action not implemented", ['id' => $id]);
                $this->logExecutionTime($stepStart, 'not implemented', 'completed');
                return defaultPermissionDenial($request, new \Exception('Not implemented'), $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'id' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'id' => $id]);
                throw $e;
            }
        });
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $function) {
            $checkStart = microtime(true);
            $ur = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($ur instanceof RedirectResponse) return $ur;
            $user = $ur;
            $guardStart = microtime(true);
            $g = self::guard($request, PermissionsConstants::MNG_LP, self::REDIRECT_INDEX);
            $this->logExecutionTime($guardStart, $action . '::guard', 'completed');
            if ($g !== true) {
                Log::warning("[$action] permission denied", ['user_id' => $user?->id]);
                Log::debug("[$action] lacking MNG_LP permission", ['user_id' => $user?->id]);
                return $g;
            }
            Log::info("[$action] rendering form", ['user_id' => $user?->id]);
            return view(self::LP . '::' . self::LP . '.' . RoutesResourcesConstants::HM . '.' . $function);
        }, ['user_id' => Auth::id()]);
    }

    public function store(Request $request): RedirectResponse|bool
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', ['user_id' => Auth::id()]);
        return $this->measureProfile($method, function () use ($request, $method) {
            $stepStart = microtime(true);
            if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (($g = self::guard($request, PermissionsConstants::MNG_LP, self::REDIRECT_INDEX)) !== true) {
                Log::warning($method . ' permission denied', ['user_id' => Auth::id()]);
                return $g;
            }
            $this->logExecutionTime($stepStart, 'authorizationGuard', 'completed');
            $stepStart = microtime(true);
            Log::info($method . ' started', ['user_id' => Auth::id()]);
            $this->logExecutionTime($stepStart, 'logStart', 'completed');
            $data = [];
            $stepStart = microtime(true);
            if ($request->hasFile(SettingsConstants::HM_BNR_K)) {
                Log::debug($method . ' banner upload detected', ['user_id' => Auth::id()]);
                $file = $request->file(SettingsConstants::HM_BNR_K);
                $name = SettingsConstants::HM_BNR_K . '.' . $file->getClientOriginalExtension();
                $dir = 'uploads/landing_page_image';
                $path = LandingPageSetting::uploadFile($request, SettingsConstants::HM_BNR_K, $name, $dir, []);
                $this->logExecutionTime($stepStart, 'bannerUploadCall', 'completed');
                if ($path['flag'] !== 1) {
                    Log::error($method . ' banner upload failed', ['user_id' => Auth::id(), 'msg' => $path['msg']]);
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $data[self::ENTITY . 'Banner'] = $name;
                Log::info($method . ' banner saved', ['user_id' => Auth::id(), 'file' => $name]);
            } else {
                $this->logExecutionTime($stepStart, 'bannerUploadCheck', 'completed');
                Log::debug($method . ' no banner uploaded', ['user_id' => Auth::id()]);
            }
            $stepStart = microtime(true);
            $existing = explode(',', LandingPageSetting::settings()[SettingsConstants::HM_LGO_K] ?? '');
            $keep = explode(',', $request->input('savedlogo', ''));
            $logos = array_values(array_intersect($existing, $keep));
            Log::debug($method . ' existing logos filtered', ['user_id' => Auth::id(), 'count' => count($logos)]);
            $this->logExecutionTime($stepStart, 'filterExistingLogos', 'completed');
            if ($request->has(SettingsConstants::HM_LGO_K)) {
                foreach ($request->file(SettingsConstants::HM_LGO_K) as $file) {
                    $stepStart = microtime(true);
                    $fname = md5(now()) . '_' . $file->getClientOriginalName();
                    $dir = 'uploads/landing_page_image';
                    $p = LandingPageSetting::keyWiseUpload_file($request, SettingsConstants::HM_LGO_K, $fname, $dir, [], false);
                    $this->logExecutionTime($stepStart, 'logoUploadCall', 'completed');
                    if ($p['flag'] !== 1) {
                        Log::error($method . ' logo upload failed', ['user_id' => Auth::id(), 'msg' => $p['msg']]);
                        return redirect()->back()->with('error', __($p['msg']));
                    }
                    $logos[] = $p['url'];
                    Log::info($method . ' logo saved', ['user_id' => Auth::id(), 'url' => $p['url']]);
                }
            }
            $data[self::ENTITY . 'Logo'] = implode(',', $logos);
            Log::debug($method . ' total logos', ['user_id' => Auth::id(), 'count' => count($logos)]);
            $fields = [
                self::ENTITY . 'Status',
                self::ENTITY . 'OfferText',
                self::ENTITY . 'Title',
                self::ENTITY . 'Heading',
                self::ENTITY . 'Description',
                self::ENTITY . 'TrustedBy',
                self::ENTITY . 'LiveDemoLink',
                self::ENTITY . 'BuyNowLink'
            ];
            foreach ($fields as $field) {
                $stepStart = microtime(true);
                $snake = Str::snake($field);
                $data[$field] = $request->input($snake, '');
                $this->logExecutionTime($stepStart, 'field' . $field, 'completed');
                Log::debug($method . " field {$field}", ['value' => $data[$field]]);
            }
            try {
                $stepStart = microtime(true);
                DB::transaction(fn() => collect($data)->each(fn($v, $k) => LandingPageSetting::updateOrCreate(
                    ['name' => Str::snake($k)],
                    ['value' => $v]
                )));
                $this->logExecutionTime($stepStart, 'dbTransaction', 'completed');
                Log::info($method . ' settings saved', ['user_id' => Auth::id()]);
                return redirect()->back()->with('success', __('Settings updated successfully'));
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'user_id' => Auth::id()]);
                Log::error($method . ' persistence error', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $method, route(self::REDIRECT_INDEX));
            }
        }, ['user_id' => Auth::id()]);
    }

    public function edit(Request $request, int $id): View|RedirectResponse|null
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($ur = static::_checkLogin()) instanceof RedirectResponse) return $this->logExecutionTime($startLogin, $function . '::login', 'failed') ?: $ur;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            Log::warning($method . ' not implemented', ['id' => $id]);
            Log::debug($method . ' debug', ['request' => $request->all()]);
            return defaultPermissionDenial($request, new \Exception('Not implemented'), $method, route(static::REDIRECT_INDEX));
        }, func_get_args());
    }

    public function update(Request $request, int $id): RedirectResponse|null
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            Log::info("$action invoked", ['id' => $id]);
            $stepStart = microtime(true);
            try {
                Log::warning("$action not implemented", ['id' => $id]);
                $this->logExecutionTime($stepStart, 'not implemented', 'completed');
                return defaultPermissionDenial($request, new \Exception('Not implemented'), $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'id' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'id' => $id]);
                throw $e;
            }
        });
    }

    public function destroy(Request $request, int $id): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id) {
            Log::warning("[$action] not implemented", ['id' => $id]);
            Log::debug("[$action] debug not implemented", []);
            return defaultPermissionDenial($request, new \Exception('Not implemented'), $action, route(self::REDIRECT_INDEX));
        }, ['id' => $id]);
    }
}
