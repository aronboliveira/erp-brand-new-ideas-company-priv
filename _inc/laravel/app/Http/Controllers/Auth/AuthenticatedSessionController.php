<?php

namespace App\Http\Controllers\Auth;

use App\Config\Constants\{
  DatabaseConstants,
  LangsConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  SettingsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\{
  Customer,
  LoginDetail,
  Plan,
  User,
  Utility,
  Vendor
};
use App\Providers\RouteServiceProvider;
use App\Traits\ChecksLogin;
use Carbon\Carbon;
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request,
  Response
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
  App,
  Auth,
  DB,
  Hash,
  Log,
  Mail,
  Route,
  View as ViewFacade
};
use Illuminate\{
  Database\QueryException,
  Validation\ValidationException,
  View\View
};
use App\Helpers\SafeConsoleOutput;
use function App\Http\Controllers\{
  defaultPermissionDenial,
  defaultUndefinedException
};

class AuthenticatedSessionController extends Controller
{
  use ChecksLogin;
  private const SINGULAR = 'auth';

  public function __construct()
  {
    $msg = 'Constructing ' . __CLASS__ . '...';
    app()->runningInConsole() ?
      SafeConsoleOutput::make()->writeln('<info> ' . $msg . ' </info>') : SafeConsoleOutput::make()->writeln("## CONTROLLER: {$msg}");
    Log::debug($msg);
  }

  public const SHW_LG_FM = 'showLoginForm';
  public function showLoginForm(string $lang = DatabaseConstants::DEFAULT_LANG): View|JsonResponse
  {
    $action = __FUNCTION__;
    return $this->measureProfile($action, function () use ($lang, $action) {
      $output = SafeConsoleOutput::make();
      $base   = class_basename(static::class);
      $msg    = 'Starting login form';
      app()->runningInConsole()
        ? $output->writeln("<info>{$msg}</info>")
        : $output->writeln("## {$base}: {$msg}");
      Log::info("[{$base}::{$action}] starting", ['lang_param' => $lang]);
      try {
        $langMsg = 'Processing language';
        app()->runningInConsole()
          ? $output->writeln("<comment>{$langMsg}</comment>")
          : $output->writeln("## {$base}: {$langMsg}");
        Log::info("[{$base}::{$action}] processing language parameter", ['original_lang' => $lang]);
        $lang = self::_setLocale($lang);
        $localeMsg = 'Locale set';
        app()->runningInConsole()
          ? $output->writeln("<info>{$localeMsg}</info>")
          : $output->writeln("## {$base}: {$localeMsg}");
        Log::info("[{$base}::{$action}] locale configuration completed", ['final_lang' => $lang]);
        $settingsMsg = 'Loading settings';
        app()->runningInConsole()
          ? $output->writeln("<comment>{$settingsMsg}</comment>")
          : $output->writeln("## {$base}: {$settingsMsg}");
        Log::info("[{$base}::{$action}] retrieving application settings");
        $settings = Utility::settings();
        $viewMsg = 'Rendering view';
        app()->runningInConsole()
          ? $output->writeln("<info>{$viewMsg}</info>")
          : $output->writeln("## {$base}: {$viewMsg}");
        $viewPath = self::SINGULAR . '.login';
        Log::info("[{$base}::{$action}] rendering login view", [
          'view_path'    => $viewPath,
          'compact_vars' => [DatabaseConstants::DEFAULT_LANG, DatabaseConstants::TABLE_SETTINGS]
        ]);
        $successMsg = "Login form ready for {$viewPath}";
        app()->runningInConsole()
          ? $output->writeln("<info>{$successMsg}</info>")
          : $output->writeln("## {$base}: {$successMsg}");
        if (!ViewFacade::exists($viewPath)) {
          $route = Route::getCurrentRoute()?->getName() ?? 'UNDEFINED ROUTE';
          $err   = "Tried to render non-existent view [{$viewPath}] for {$route}";
          Log::error("[{$base}::{$action}] {$err}", [DatabaseConstants::DEFAULT_LANG => $lang]);
          $output->writeln($err);
          return response()->json([
            'error'   => "View [{$viewPath}] does not exist",
            'message' => 'Not found',
            'code'    => '404'
          ], 404);
        }
        Log::info("[{$base}::{$action}] login form view created successfully");
        return view($viewPath, [
          DatabaseConstants::DEFAULT_LANG   => $lang,
          DatabaseConstants::TABLE_SETTINGS => $settings,
        ]);
      } catch (\Exception $e) {
        $errorMsg = 'Login form failed';
        app()->runningInConsole()
          ? $output->writeln("<error>{$errorMsg}: {$e->getMessage()}</error>")
          : $output->writeln("## {$base} ERROR: {$errorMsg}");
        Log::error("[{$base}::{$action}] error in showLoginForm", [
          'error'      => $e->getMessage(),
          'file'       => $e->getFile(),
          'line'       => $e->getLine(),
          'lang_param' => $lang
        ]);
        return response()->json([
          'error'   => "View [{$viewPath}] failed to load",
          'message' => 'Server unexpected error: ' . $e->getMessage(),
          'code'    => '500'
        ], 500);
      }
    }, ['lang' => $lang]);
  }

  public function store(LoginRequest $req): RedirectResponse|JsonResponse|Response|null
  {
    $action = __FUNCTION__;
    Log::debug('Registering auth store callback');
    return $this->measureProfile($action, function () use ($req, $action) {
      $base = class_basename(static::class);
      $ctx  = [
        'class'       => $base,
        'method'      => $action,
        'ip'          => $req->ip(),
        'user_agent'  => $req->userAgent(),
        'email_input' => $req->input('email', 'n/a'),
      ];
      Log::info("{$base}::{$action} - login attempt", $ctx);
      $rules = env('RECAPTCHA_MODULE') === 'on'
        ? [SettingsConstants::G_RCPT_RES => 'required|captcha']
        : [];
      if ($rules) {
        Log::debug("{$base}::{$action} applying ReCAPTCHA rules", array_merge($ctx, ['rules' => $rules]));
        try {
          $this->validate($req, $rules);
        } catch (ValidationException $ve) {
          Log::warning("{$base}::{$action} ReCAPTCHA validation failed", array_merge($ctx, [
            'errors' => $ve->validator->errors()->all(),
          ]));
          return $req->wantsJson()
            ? response()->json($ve->validator->errors(), 422)
            : redirect()->back()->withErrors($ve->validator);
        }
      }
      try {
        Log::debug("{$base}::{$action} checking credentials in database", $ctx);
        try {
          $user = User::where(UsersConstants::COL_EM, $req->input('email'))
            ->orWhere(UsersConstants::COL_NM, $req->input('email'))->first();
        } catch (QueryException $qe) {
          Log::critical("{$base}::{$action} database query error", array_merge($ctx, [
            'message'     => $qe->getMessage(),
            'file'        => $qe->getFile(),
            'line'        => $qe->getLine(),
            'code'        => $qe->getCode() ?? 'n/a',
          ]));
          Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . "::{$action} error", [
            'message'     => $qe->getMessage(),
            'file'        => $qe->getFile(),
            'line'        => $qe->getLine(),
            'code'        => $qe->getCode() ?? 'n/a',
            'trace'       => $qe->getTraceAsString(),
          ]);
          $msg = addslashes(__('There was an error checking for your user. Try again later.'));
          $uuid = Str::uuid();
          $script = <<<HTML
          <script id="{$uuid}">
            (function() {
                const toast = document.getElementById('loginToast');
                if (!toast) {
                    console.warn('Toast element not found');
                    return;
                }
                const body = toast.querySelector('.toast-body');
                if (!body) {
                    console.warn('Toast body element not found');
                    return;
                }
                const delay = 5000;
                const bs = new bootstrap.Toast(toast, { delay });
                body.textContent = {$msg};
                toast.style.display = 'block';
                bs.show();
                const handleHidden = function() {
                    body.textContent = '';
                    toast.style.display = 'none';
                    toast.removeEventListener('hidden.bs.toast', handleHidden);
                };
                toast.addEventListener('hidden.bs.toast', handleHidden);
                setTimeout(function() {
                    document.getElementById('{$uuid}')?.remove();
                }, delay * 1.25);
            })();
          </script>
          HTML;
          return response()->json([
            'error'   => "View failed to load to an error querying the database.",
            'snippet' => $script,
            'status'  => 500,
          ]);
        }
        if (!$user) {
          Log::warning("{$base}::{$action} user not found", $ctx);
          $msg = addslashes(__('User not found. Please check your email or username.'));
          $uuid = Str::uuid();
          $script = <<<HTML
          <script id="{$uuid}">
            (function() {
                const toast = document.getElementById('loginToast');
                if (!toast) {
                    console.warn('Toast element not found');
                    return;
                }
                const body = toast.querySelector('.toast-body');
                if (!body) {
                    console.warn('Toast body element not found');
                    return;
                }
                const delay = 5000;
                const bs = new bootstrap.Toast(toast, { delay });
                body.textContent = {$msg};
                toast.style.display = 'block';
                bs.show();
                const handleHidden = function() {
                    body.textContent = '';
                    toast.style.display = 'none';
                    toast.removeEventListener('hidden.bs.toast', handleHidden);
                };
                toast.addEventListener('hidden.bs.toast', handleHidden);
                setTimeout(function() {
                    document.getElementById('{$uuid}')?.remove();
                }, delay * 1.25);
            })();
          </script>
          HTML;
          return response()->json([
            'error'   => "View failed to locate an user and aborted.",
            'snippet' => $script,
            'status'  => 404,
          ]);
        }
        Log::info("{$base}::{$action} user record found", array_merge($ctx, [
          'user_id' => $user->id,
        ]));
        $req->authenticate(meta: ['caller' => 'Default Login call'], user: $user);
        Log::info("{$base}::{$action} user authenticated, regenerating session", ['user_id' => $user->id]);
        $req->session()->regenerate();
        Log::debug("{$base}::{$action} checking for inactive user", ['user_id' => $user->id]);
        self::_logoutIfInactive($user);
        if ($user[UsersConstants::COL_TP] === PermissionsConstants::CPN) {
          $plan = Plan::find($user[UsersConstants::COL_PL]);
          Log::debug("{$base}::{$action} company user, checking plan", ['user_id' => $user->id, 'plan' => optional($plan)->toArray()]);
          if ($plan && $plan->duration !== 'lifetime') {
            $days = (new \DateTime())->diff(new \DateTime($user[UsersConstants::COL_PED]))->format('%r%a');
            Log::debug("{$base}::{$action} plan days remaining", ['user_id' => $user->id, 'days' => $days]);
            if ($days <= 0) {
              $user->assignPlan(1);
              Log::warning("{$base}::{$action} plan expired, reassigned to free", ['user_id' => $user->id]);
              return redirect()
                ->intended(RouteServiceProvider::HOME)
                ->with('error', 'Your Plan is expired.');
            }
          }
        }
        Log::debug("{$base}::{$action} updating last login timestamp", ['user_id' => $user->id]);
        Log::info("{$base}::{$action} user record found", array_merge($ctx, [
          'user_id' => $user->id,
        ]));
        Log::info("{$base}::{$action} authenticated, regenerating session", ['user_id' => $user->id]);
        $req->session()->regenerate();
        self::_logoutIfInactive($user);
        if ($user[UsersConstants::COL_TP] === PermissionsConstants::CPN) {
          $plan = Plan::find($user[UsersConstants::COL_PL]);
          Log::debug("{$base}::{$action} company user, checking plan", [
            'user_id' => $user->id,
            'plan'    => optional($plan)->toArray(),
          ]);
          if ($plan && $plan->duration !== 'lifetime') {
            $days = (new \DateTime())->diff(new \DateTime($user[UsersConstants::COL_PED]))->format('%r%a');
            Log::debug("{$base}::{$action} plan days remaining", ['user_id' => $user->id, 'days' => $days]);
            if ($days <= 0) {
              $user->assignPlan(1);
              Log::warning("{$base}::{$action} plan expired, reassigned to free", ['user_id' => $user->id]);
              return redirect()
                ->intended(RouteServiceProvider::HOME)
                ->with('error', 'Your plan has expired.');
            }
          }
        }
        Log::debug("{$base}::{$action} updating last login timestamp", ['user_id' => $user->id]);
        self::_updateLastLogin($user);
        if (!in_array($user[UsersConstants::COL_TP], [
          PermissionsConstants::CPN,
          PermissionsConstants::SA,
          PermissionsConstants::CL,
        ], true)) {
          Log::debug("{$base}::{$action} recording user activity log", ['user_id' => $user->id]);
          $this->_logUser($req, $user);
        }
        $home = in_array($user[UsersConstants::COL_TP], [
          PermissionsConstants::CPN,
          PermissionsConstants::SA,
          PermissionsConstants::CL,
        ], true)
          ? RouteServiceProvider::HOME
          : RouteServiceProvider::EMPHOME;
        Log::info("{$base}::{$action} redirecting to intended route", [
          'route'   => $home,
          'user_id' => $user->id,
        ]);
        return redirect($home);
      } catch (ValidationException $e) {
        $code = $e->getCode() ?? 401;
        Log::notice("{$base}::{$action} " . get_class($e), array_merge($ctx, [
          'exception' => $e->getMessage(),
          'code'      => $code,
          'file'      => $e->getFile(),
          'line'      => $e->getLine(),
          'email'     => $req->input('email', 'n/a'),
        ]));
        $msg = addslashes(__('Something went wrong validating your credentials. Try again later.'));
        $uuid = Str::uuid();
        $script = <<<HTML
          <script id="{$uuid}">
            (function() {
                const toast = document.getElementById('loginToast');
                if (!toast) {
                    console.warn('Toast element not found');
                    return;
                }
                const body = toast.querySelector('.toast-body');
                if (!body) {
                    console.warn('Toast body element not found');
                    return;
                }
                const delay = 5000;
                const bs = new bootstrap.Toast(toast, { delay });
                body.textContent = {$msg};
                toast.style.display = 'block';
                bs.show();
                const handleHidden = function() {
                    body.textContent = '';
                    toast.style.display = 'none';
                    toast.removeEventListener('hidden.bs.toast', handleHidden);
                };
                toast.addEventListener('hidden.bs.toast', handleHidden);
                setTimeout(function() {
                    document.getElementById('{$uuid}')?.remove();
                }, delay * 1.25);
            })();
          </script>
          HTML;
        $lang = Utility::fetchUserLang();
        $msgs = !empty(LangsConstants::ERROR_MESSAGES[$lang]) ? LangsConstants::ERROR_MESSAGES[$lang] : LangsConstants::DEFAULT_CLIENT_MESSAGES;
        $notFoundMsg = !empty($msgs['invalid_user']) ? $msgs['invalid_user'] : 'User not found.';
        return response()->view('errors.login_error', [
          'message' => $notFoundMsg,
          'title' => 'Authentication Error'
        ], 401);
      } catch (\Throwable $e) {
        Log::error("{$base}::{$action} unexpected exception", array_merge($ctx, [
          'exception' => $e->getMessage(),
          'file'      => $e->getFile(),
          'line'      => $e->getLine(),
          'email'     => $req->input('email', 'n/a'),
        ]));
        $msg = addslashes(__('Something unexpected went wrong. Try again later.'));
        $uuid = Str::uuid();
        $script = <<<HTML
				<script id="{$uuid}">
					(function() {
							const toast = document.getElementById('loginToast');
							if (!toast) {
									console.warn('Toast element not found');
									return;
							}
							const body = toast.querySelector('.toast-body');
							if (!body) {
									console.warn('Toast body element not found');
									return;
							}
							const delay = 5000;
							const bs = new bootstrap.Toast(toast, { delay });
							body.textContent = {$msg};
							toast.style.display = 'block';
							bs.show();
							const handleHidden = function() {
									body.textContent = '';
									toast.style.display = 'none';
									toast.removeEventListener('hidden.bs.toast', handleHidden);
							};
							toast.addEventListener('hidden.bs.toast', handleHidden);
							setTimeout(function() {
									document.getElementById('{$uuid}')?.remove();
							}, delay * 1.25);
					})();
				</script>
				HTML;
        return response()->json([
          'error'   => "View failed to load due to an unexpected exception",
          'snippet' => $script,
          'status'  => 500
        ]);
      }
    }, [
      'email' => $req->input('email', 'n/a'),
      'ip'          => $req->ip(),
      'user_agent'  => $req->userAgent(),
    ]);
  }

  public function destroy(Request $req): RedirectResponse
  {
    $action = __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $action) {
      $base = class_basename(static::class);
      $ctx  = ['ip' => $req->ip(), 'user_id' => Auth::id()];
      Log::info("{$base}::{$action} - logout initiated", $ctx);
      Auth::guard(MiddlewaresConstants::WEB)->logout();
      Log::debug("{$base}::{$action} - session invalidation", $ctx);
      $req->session()->invalidate();
      $req->session()->regenerateToken();
      Log::info("{$base}::{$action} - logout completed", $ctx);
      return redirect('/');
    }, [
      'ip'     => $req->ip(),
      'user_id' => Auth::id(),
    ]);
  }

  public const SHW_LG_RQ = 'showLoginRequestForm';
  public function showLoginRequestForm(string $lang = DatabaseConstants::DEFAULT_LANG): View|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    $function = __FUNCTION__;
    return $this->measureProfile($action, function () use ($lang, $action, $function) {
      $output = SafeConsoleOutput::make();
      $start  = 'Starting link request form';
      app()->runningInConsole()
        ? $output->writeln("<info> {$action}: {$start} </info>")
        : $output->writeln("## {$action}: {$start}");
      Log::info("{$action} • Starting " . $function, ['lang_param' => $lang]);
      try {
        $langMsg = 'Processing language';
        app()->runningInConsole()
          ? $output->writeln("<comment> {$action}: {$langMsg} </comment>")
          : $output->writeln("## {$action}: {$langMsg}");
        Log::info("{$action} • Processing language parameter", ['original_lang' => $lang]);
        $lang = self::_setLocale($lang);
        $localeMsg = "Locale set to {$lang}";
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$localeMsg} </info>")
          : $output->writeln("## {$action}: {$localeMsg}");
        Log::info("{$action} • Locale configuration completed", ['final_lang' => $lang]);
        $viewMsg = 'Rendering forgot password view';
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$viewMsg} </info>")
          : $output->writeln("## {$action}: {$viewMsg}");
        $viewPath = ViewsConstants::AUT . '.forgot_password';
        Log::info("{$action} • View path determined", ['view_path' => $viewPath]);
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'UNDEFINED_ROUTE';
          $errMsg  = "{$action} • View not found: {$viewPath} • route={$current}";
          Log::error($errMsg, [DatabaseConstants::DEFAULT_LANG => $lang]);
          $output->writeln($errMsg);
          return response()->json([
            'error'   => "View [{$viewPath}] does not exist",
            'message' => 'Not found',
            'code'    => '404',
          ], 404);
        }
        Log::info("{$action} • Rendering view {$viewPath}", [DatabaseConstants::DEFAULT_LANG => $lang]);
        return view($viewPath, [DatabaseConstants::DEFAULT_LANG => $lang]);
      } catch (\Exception $e) {
        $errorMsg = 'Link request form failed';
        app()->runningInConsole()
          ? $output->writeln("<error> {$action}: {$errorMsg} – {$e->getMessage()} </error>")
          : $output->writeln("## {$action} ERROR: {$errorMsg}");
        Log::error("{$action} • Exception in " . $function, [
          'error'      => $e->getMessage(),
          'file'       => $e->getFile(),
          'line'       => $e->getLine(),
          'lang_param' => $lang,
        ]);
        return response()->json([
          'error'   => "View [{$viewPath}] failed to load",
          'message' => 'Server unexpected error: ' . $e->getMessage(),
          'code'    => '500',
        ], 500);
      }
    });
  }

  public const SHW_CTM_LG_FM = 'showCustomerLoginForm';
  public function showCustomerLoginForm(string $lang = DatabaseConstants::DEFAULT_LANG): View|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($lang, $action) {
      $output   = SafeConsoleOutput::make();
      $startMsg = 'Starting customer login form';
      app()->runningInConsole()
        ? $output->writeln("<info> {$action}: {$startMsg} </info>")
        : $output->writeln("## {$action}: {$startMsg}");
      Log::info("{$action} • Starting showCustomerLoginForm", ['lang_param' => $lang]);
      try {
        $langMsg = 'Processing language parameter';
        app()->runningInConsole()
          ? $output->writeln("<comment> {$action}: {$langMsg} </comment>")
          : $output->writeln("## {$action}: {$langMsg}");
        Log::info("{$action} • Processing language parameter", ['original_lang' => $lang]);
        $lang = self::_setLocale($lang);
        $localeMsg = 'Locale set';
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$localeMsg} </info>")
          : $output->writeln("## {$action}: {$localeMsg}");
        Log::info("{$action} • Locale configuration completed", ['final_lang' => $lang]);
        $viewMsg = 'Rendering customer login view';
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$viewMsg} </info>")
          : $output->writeln("## {$action}: {$viewMsg}");
        $viewPath = ViewsConstants::AUT . '.customer_login';
        Log::info("{$action} • View path determined", ['view_path' => $viewPath]);
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'undefined';
          $errMsg  = "{$action} • View not found: {$viewPath} • route={$current}";
          Log::error($errMsg, [DatabaseConstants::DEFAULT_LANG => $lang]);
          $output->writeln($errMsg);
          return response()->json([
            'error'   => "View [{$viewPath}] does not exist",
            'message' => 'Not found',
            'code'    => '404'
          ], 404);
        }
        Log::info("{$action} • Rendering view {$viewPath}", [DatabaseConstants::DEFAULT_LANG => $lang]);
        return view($viewPath, [DatabaseConstants::DEFAULT_LANG => $lang]);
      } catch (\Exception $e) {
        $errorMsg = 'Customer login form failed';
        app()->runningInConsole()
          ? $output->writeln("<error> {$action}: {$errorMsg} – {$e->getMessage()} </error>")
          : $output->writeln("## {$action} ERROR: {$errorMsg}");
        Log::error("{$action} • Error in showCustomerLoginForm", [
          'error'      => $e->getMessage(),
          'file'       => $e->getFile(),
          'line'       => $e->getLine(),
          'lang_param' => $lang
        ]);
        return response()->json([
          'error'   => "View [{$viewPath}] failed to load",
          'message' => 'Server unexpected error: ' . $e->getMessage(),
          'code'    => '500'
        ], 500);
      }
    });
  }

  public const SHW_VD_LG_FM = 'showVendorLoginForm';
  public function showVendorLoginForm(string $lang = DatabaseConstants::DEFAULT_LANG): View|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $lang) {
      $output = SafeConsoleOutput::make();
      $startMsg = 'Starting vendor login form';
      app()->runningInConsole()
        ? $output->writeln('<info> ' . $startMsg . ' </info>')
        : $output->writeln("## {$action}: {$startMsg}");
      Log::info("[$action] Starting {$action}", ['lang_param' => $lang]);
      $localeStart = microtime(true);
      try {
        $langMsg = 'Processing language parameter';
        app()->runningInConsole()
          ? $output->writeln('<comment> ' . $langMsg . ' </comment>')
          : $output->writeln("## {$action}: {$langMsg}");
        Log::info("[$action] Processing language parameter", ['original_lang' => $lang]);
        $lang = static::_setLocale($lang);
        $this->logExecutionTime($localeStart, $action . '::setLocale', 'completed');
        $viewStart = microtime(true);
        $viewMsg = 'Rendering vendor login view';
        app()->runningInConsole()
          ? $output->writeln('<info> ' . $viewMsg . ' </info>')
          : $output->writeln("## {$action}: {$viewMsg}");
        $viewPath = ViewsConstants::AUT . '.vendor_login';
        Log::info("[$action] View path determined", ['view_path' => $viewPath]);
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'undefined';
          $errMsg = "$action • View not found: $viewPath • route=$current";
          Log::error($errMsg, [DatabaseConstants::DEFAULT_LANG => $lang]);
          Log::debug("[$action] debug view missing details", ['view' => $viewPath, 'route' => $current]);
          $this->logExecutionTime($viewStart, $action . '::viewNotFound', 'error');
          $output->writeln($errMsg);
          return response()->json(['error' => "View [$viewPath] does not exist", 'message' => 'Not found', 'code' => '404'], 404);
        }
        $this->logExecutionTime($viewStart, $action . '::viewExists', 'completed');
        Log::info("[$action] Rendering view $viewPath", [DatabaseConstants::DEFAULT_LANG => $lang]);
        return view($viewPath, [DatabaseConstants::DEFAULT_LANG => $lang]);
      } catch (\Exception $e) {
        $this->logExecutionTime($localeStart, $action . '::exception', 'error');
        Log::error("[$action] Error in {$action}", ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'lang_param' => $lang]);
        Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
        return response()->json(['error' => "View [$viewPath] failed to load", 'message' => 'Server unexpected error: ' . $e->getMessage(), 'code' => '500'], 500);
      }
    }, ['lang_param' => $lang]);
  }

  public const SHW_CTM_LR_FM = 'showCustomerLinkRequestForm';
  public function showCustomerLinkRequestForm(string $lang = DatabaseConstants::DEFAULT_LANG): View|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($lang, $action) {
      $output   = SafeConsoleOutput::make();
      $startMsg = 'Starting customer link request form';
      app()->runningInConsole()
        ? $output->writeln("<info> {$action}: {$startMsg} </info>")
        : $output->writeln("## {$action}: {$startMsg}");
      Log::info("{$action} • Starting showCustomerLinkRequestForm", ['lang_param' => $lang]);
      try {
        $langMsg = 'Processing language parameter';
        app()->runningInConsole()
          ? $output->writeln("<comment> {$action}: {$langMsg} </comment>")
          : $output->writeln("## {$action}: {$langMsg}");
        Log::info("{$action} • Processing language parameter", ['original_lang' => $lang]);
        $lang = self::_setLocale($lang);
        $localeMsg = 'Locale set';
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$localeMsg} </info>")
          : $output->writeln("## {$action}: {$localeMsg}");
        Log::info("{$action} • Locale configuration completed", ['final_lang' => $lang]);
        $viewMsg = 'Rendering customer email form';
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$viewMsg} </info>")
          : $output->writeln("## {$action}: {$viewMsg}");
        $viewPath = ViewsConstants::PWD . 'customer_email';
        Log::info("{$action} • View path determined", ['view_path' => $viewPath]);
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'undefined';
          $errMsg = "{$action} • View not found: {$viewPath} • route={$current}";
          Log::error($errMsg, [DatabaseConstants::DEFAULT_LANG => $lang]);
          $output->writeln($errMsg);
          return response()->json([
            'error'   => "View [{$viewPath}] does not exist",
            'message' => 'Not found',
            'code'    => '404'
          ], 404);
        }
        Log::info("{$action} • Rendering view {$viewPath}", [DatabaseConstants::DEFAULT_LANG => $lang]);
        return view($viewPath, [DatabaseConstants::DEFAULT_LANG => $lang]);
      } catch (\Exception $e) {
        $errorMsg = 'Customer link request form failed';
        app()->runningInConsole()
          ? $output->writeln("<error> {$action}: {$errorMsg} – {$e->getMessage()} </error>")
          : $output->writeln("## {$action} ERROR: {$errorMsg}");
        Log::error("{$action} • Error in showCustomerLinkRequestForm", [
          'error'      => $e->getMessage(),
          'file'       => $e->getFile(),
          'line'       => $e->getLine(),
          'lang_param' => $lang
        ]);
        return response()->json([
          'error'   => "View [{$viewPath}] failed to load",
          'message' => 'Server unexpected error: ' . $e->getMessage(),
          'code'    => '500'
        ], 500);
      }
    });
  }

  public const SHW_VD_LR_FM = 'showVendorLinkRequestForm';
  public function showVendorLinkRequestForm(string $lang = DatabaseConstants::DEFAULT_LANG): View|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($lang, $action) {
      $output   = SafeConsoleOutput::make();
      $startMsg = 'Starting vendor link request form';
      app()->runningInConsole()
        ? $output->writeln("<info> {$action}: {$startMsg} </info>")
        : $output->writeln("## {$action}: {$startMsg}");
      Log::info("{$action} • Starting showVendorLinkRequestForm", ['lang_param' => $lang]);
      try {
        $langMsg = 'Processing language parameter';
        app()->runningInConsole()
          ? $output->writeln("<comment> {$action}: {$langMsg} </comment>")
          : $output->writeln("## {$action}: {$langMsg}");
        Log::info("{$action} • Processing language parameter", ['original_lang' => $lang]);
        $lang = self::_setLocale($lang);
        $localeMsg = 'Locale set';
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$localeMsg} </info>")
          : $output->writeln("## {$action}: {$localeMsg}");
        Log::info("{$action} • Locale configuration completed", ['final_lang' => $lang]);
        $viewMsg = 'Rendering vendor email form';
        app()->runningInConsole()
          ? $output->writeln("<info> {$action}: {$viewMsg} </info>")
          : $output->writeln("## {$action}: {$viewMsg}");
        $viewPath = ViewsConstants::PWD . 'vendor_email';
        Log::info("{$action} • View path determined", ['view_path' => $viewPath]);
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'undefined';
          $errMsg = "{$action} • View not found: {$viewPath} • route={$current}";
          Log::error($errMsg, [DatabaseConstants::DEFAULT_LANG => $lang]);
          $output->writeln($errMsg);
          return response()->json([
            'error'   => "View [{$viewPath}] does not exist",
            'message' => 'Not found',
            'code'    => '404'
          ], 404);
        }
        Log::info("{$action} • Rendering view {$viewPath}", [DatabaseConstants::DEFAULT_LANG => $lang]);
        return view($viewPath, [DatabaseConstants::DEFAULT_LANG => $lang]);
      } catch (\Exception $e) {
        $errorMsg = 'Vendor link request form failed';
        app()->runningInConsole()
          ? $output->writeln("<error> {$action}: {$errorMsg} – {$e->getMessage()} </error>")
          : $output->writeln("## {$action} ERROR: {$errorMsg}");
        Log::error("{$action} • Error in showVendorLinkRequestForm", [
          'error'      => $e->getMessage(),
          'file'       => $e->getFile(),
          'line'       => $e->getLine(),
          'lang_param' => $lang
        ]);
        return response()->json([
          'error'   => "View [{$viewPath}] failed to load",
          'message' => 'Server unexpected error: ' . $e->getMessage(),
          'code'    => '500'
        ], 500);
      }
    });
  }

  public const CTM_LG = 'customerLogin';
  public function customerLogin(Request $req): RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $req) {
      try {
        Log::info("[$action] Invoking customer login", ['email' => $req->input('email')]);
        $creds = $req->only('email', 'password');
        $valStart = microtime(true);
        $req->validate(['email' => 'required|email', 'password' => 'required|min:6']);
        $this->logExecutionTime($valStart, $action . '::validate', 'completed');
        $guardStart = microtime(true);
        $response = $this->_guardLogin($req, PermissionsConstants::CT, $creds, PermissionsConstants::CT . '.dashboard');
        $this->logExecutionTime($guardStart, $action . '::_guardLogin', 'completed');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[$action] Error in {$action}", ['error' => $e->getMessage()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        throw $e;
      }
    }, ['email' => $req->input('email')]);
  }

  public const VD_LG = 'vendorLogin';
  public function vendorLogin(Request $req): RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $action) {
      $creds = $req->only('email', 'password');
      Log::debug("{$action} • Credentials extracted", ['creds_keys' => array_keys($creds)]);
      $req->validate(['email' => 'required|email', 'password' => 'required|min:6']);
      Log::info("{$action} • Validation passed for vendor login", ['email' => $creds['email']]);
      return $this->_guardLogin(
        $req,
        PermissionsConstants::VD,
        $creds,
        PermissionsConstants::VD . '.dashboard'
      );
    });
  }

  public const PST_CTM_EM = 'postCustomerEmail';
  public function postCustomerEmail(Request $req): RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $req) {
      try {
        Log::info("[$action] Sending password reset email", ['email' => $req->input('email')]);
        $sendStart = microtime(true);
        $response = $this->_sendReset($req, DatabaseConstants::TABLE_CUSTOMERS, self::SINGULAR . '.customerVerify');
        $this->logExecutionTime($sendStart, $action . '::_sendReset', 'completed');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[$action] Error in {$action}", ['error' => $e->getMessage()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        throw $e;
      }
    }, ['email' => $req->input('email')]);
  }

  public const PST_VD_EM = 'postVendorEmail';
  public function postVendorEmail(Request $req): RedirectResponse|JsonResponse
  {
    $method = __METHOD__;
    Log::debug($method . ' - start', ['input' => $req->all()]);
    return $this->measureProfile($method, fn() => $this->_sendReset($req, DatabaseConstants::TABLE_VENDORS, self::SINGULAR . '.vendorVerify'), ['req' => $req]);
  }

  public const SHW_RST_FM = 'showResetForm';
  public function showResetForm(Request $req, ?string $token = null): View|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $token, $action) {
      $output = SafeConsoleOutput::make();
      $tag = $action;
      $startMsg = 'Starting reset form';
      app()->runningInConsole() ? $output->writeln("<info> {$tag}: {$startMsg} </info>") : $output->writeln("## {$tag}: {$startMsg}");
      Log::info("[{$tag}] Starting showResetForm", ['token' => $token]);
      try {
        $lang = DB::table(DatabaseConstants::TABLE_SETTINGS)->value('value') ?? DatabaseConstants::DEFAULT_LANG;
        App::setLocale($lang);
        $viewMsg = 'Rendering reset view';
        app()->runningInConsole() ? $output->writeln("<info> {$tag}: {$viewMsg} </info>") : $output->writeln("## {$tag}: {$viewMsg}");
        $viewPath = ViewsConstants::PWD . 'reset';
        Log::info("[{$tag}] View path determined", ['view_path' => $viewPath]);
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'undefined';
          $errMsg = "{$tag} • View not found: {$viewPath} • route={$current}";
          Log::error($errMsg, ['lang' => $lang, 'token' => $token, 'email' => $req->email]);
          $output->writeln($errMsg);
          return response()->json(['error' => "View [{$viewPath}] does not exist", 'message' => 'Not found', 'code' => '404'], 404);
        }
        Log::info("[{$tag}] Rendering view {$viewPath}", ['lang' => $lang, 'token' => $token]);
        return view($viewPath, ['token' => $token, 'email' => $req->email, DatabaseConstants::DEFAULT_LANG => $lang]);
      } catch (\Exception $e) {
        $errorMsg = 'Reset form failed';
        app()->runningInConsole() ? $output->writeln("<error> {$tag}: {$errorMsg} – {$e->getMessage()} </error>") : $output->writeln("## {$tag} ERROR: {$errorMsg}");
        Log::error("[{$tag}] Error in showResetForm", ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'token' => $token, 'email' => $req->email]);
        return response()->json(['error' => "View [{$viewPath}] failed to load", 'message' => 'Server unexpected error: ' . $e->getMessage(), 'code' => '500'], 500);
      }
    });
  }

  public const GET_CTM_PW = 'getCustomerPassword';
  public function getCustomerPassword(string $token): View|JsonResponse
  {
    $function = __FUNCTION__;
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $token, $function) {
      $output = SafeConsoleOutput::make();
      $startMsg = 'Starting ' . $function;
      app()->runningInConsole()
        ? $output->writeln('<info> ' . $startMsg . ' </info>')
        : $output->writeln("## {$action}: {$startMsg}");
      Log::info("[$action] Starting " . $function, ['token' => $token]);
      try {
        $viewStart = microtime(true);
        $viewMsg = 'Rendering customer reset view';
        app()->runningInConsole()
          ? $output->writeln('<info> ' . $viewMsg . ' </info>')
          : $output->writeln("## {$action}: {$viewMsg}");
        $viewPath = ViewsConstants::PWD . 'customer_reset';
        Log::info("[$action] View path determined", ['view_path' => $viewPath]);
        $this->logExecutionTime($viewStart, $action . '::viewPath', 'completed');
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'undefined';
          $errMsg = "$action • View not found: $viewPath • route=$current";
          Log::error($errMsg, ['token' => $token]);
          Log::debug("[$action] debug view missing details", ['view' => $viewPath, 'route' => $current]);
          $output->writeln($errMsg);
          $this->logExecutionTime($viewStart, $action . '::viewNotFound', 'error');
          return response()->json(['error' => "View [$viewPath] does not exist", 'message' => 'Not found', 'code' => '404'], 404);
        }
        $this->logExecutionTime($viewStart, $action . '::viewExists', 'completed');
        Log::info("[$action] Rendering view $viewPath", ['token' => $token]);
        return view($viewPath, ['token' => $token]);
      } catch (\Throwable $e) {
        Log::error("[$action] Error in " . $function, ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'token' => $token]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        $this->logExecutionTime($viewStart, $action . '::exception', 'error');
        return response()->json(['error' => "View [$viewPath] failed to load", 'message' => 'Server unexpected error: ' . $e->getMessage(), 'code' => '500'], 500);
      }
    }, ['token' => $token]);
  }


  public const GET_VD_PW = 'getVendorPassword';
  public function getVendorPassword(string $token): View|JsonResponse
  {
    $function = __FUNCTION__;
    $method = __METHOD__;
    $tag = class_basename(static::class) . '@' . __FUNCTION__;
    Log::debug($method . ' - start', ['token' => $token]);
    return $this->measureProfile($method, function () use ($token, $tag, $method, $function) {
      $output = SafeConsoleOutput::make();
      $startMsg = 'Starting ' . $function;
      app()->runningInConsole()
        ? $output->writeln('<info> ' . $startMsg . ' </info>')
        : $output->writeln("## {$tag}: {$startMsg}");
      Log::info("[{$tag}] {$startMsg}", ['token' => $token]);
      try {
        $viewMsg = 'Rendering vendor reset view';
        app()->runningInConsole()
          ? $output->writeln('<info> ' . $viewMsg . ' </info>')
          : $output->writeln("## {$tag}: {$viewMsg}");
        $stepStart = microtime(true);
        $viewPath = ViewsConstants::PWD . 'vendor_reset';
        Log::info("[{$tag}] View path determined", ['view_path' => $viewPath]);
        $this->logExecutionTime($stepStart, 'viewPathDetermination', 'completed');
        if (!ViewFacade::exists($viewPath)) {
          $current = Route::currentRouteName() ?? 'undefined';
          Log::debug($method . ' - view not found', ['view_path' => $viewPath, 'route' => $current, 'token' => $token]);
          $errMsg = "{$tag} • View not found: {$viewPath} • route={$current}";
          Log::error($errMsg, ['token' => $token]);
          $output->writeln($errMsg);
          return response()->json(['error' => "View [{$viewPath}] does not exist", 'message' => 'Not found', 'code' => '404'], 404);
        }
        $stepStart = microtime(true);
        Log::info("[{$tag}] Rendering view {$viewPath}", ['token' => $token]);
        $this->logExecutionTime($stepStart, 'renderVendorView', 'completed');
        return view($viewPath, ['token' => $token]);
      } catch (\Exception $e) {
        Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'token' => $token]);
        $errorMsg = 'Vendor reset form failed';
        app()->runningInConsole()
          ? $output->writeln('<error> ' . $errorMsg . ': ' . $e->getMessage() . ' </error>')
          : $output->writeln("## {$tag} ERROR: {$errorMsg}");
        Log::error("[{$tag}] Error in " . $function, ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'token' => $token]);
        return response()->json(['error' => "View [{$viewPath}] failed to load", 'message' => 'Server unexpected error: ' . $e->getMessage(), 'code' => '500'], 500);
      }
    }, ['token' => $token]);
  }


  public const UPD_CTM_PW = 'updateCustomerPassword';
  public function updateCustomerPassword(Request $req): RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req) {
      $output = SafeConsoleOutput::make();
      $tag = class_basename(static::class) . '@' . __FUNCTION__;
      $startMsg = 'Starting ' . __FUNCTION__;
      app()->runningInConsole() ? $output->writeln("<info> {$tag}: {$startMsg} </info>") : $output->writeln("## {$tag}: {$startMsg}");
      Log::info("[{$tag}] Starting " . __FUNCTION__);
      try {
        return $this->_updatePassword($req, DatabaseConstants::TABLE_CUSTOMERS, Customer::class);
      } catch (\Exception $e) {
        $errorMsg = 'Customer password update failed';
        app()->runningInConsole() ? $output->writeln("<error> {$tag}: {$errorMsg}: {$e->getMessage()} </error>") : $output->writeln("## {$tag} ERROR: {$errorMsg}");
        Log::error("[{$tag}] Error in " . __FUNCTION__, ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        return response()->json(['error' => $errorMsg, 'message' => 'Server unexpected error: ' . $e->getMessage(), 'code' => '500'], 500);
      }
    });
  }

  public const UPD_VD_PW = 'updateVendorPassword';
  public function updateVendorPassword(Request $req): RedirectResponse|JsonResponse
  {
    $function = __FUNCTION__;
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $req, $function) {
      $output = SafeConsoleOutput::make();
      $startMsg = 'Starting ' . $function;
      app()->runningInConsole()
        ? $output->writeln('<info> ' . $startMsg . ' </info>')
        : $output->writeln("## {$action}: {$startMsg}");
      Log::info("[$action] Starting " . $function);
      try {
        $updateStart = microtime(true);
        $response = $this->_updatePassword($req, DatabaseConstants::TABLE_VENDORS, Vendor::class);
        $this->logExecutionTime($updateStart, $action . '::_updatePassword', 'completed');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[$action] Error in " . $function, ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Vendor password update failed', 'message' => 'Server unexpected error: ' . $e->getMessage(), 'code' => '500'], 500);
      }
    }, []);
  }

  private function _updatePassword(Request $req, string $table, string $model): RedirectResponse
  {
    $method = __METHOD__;
    Log::debug($method . ' - start', ['email' => $req->email]);
    return $this->measureProfile($method, function () use ($req, $table, $model, $method) {
      $stepStart = microtime(true);
      $validated = $req->validate([
        'email' => "required|email|exists:{$table}",
        'password' => 'required|string|min:6|confirmed',
        'password_confirmation' => 'required',
      ]);
      $this->logExecutionTime($stepStart, 'validation', 'completed');
      $stepStart = microtime(true);
      $reset = DB::table('password_resets')->where([
        'email' => $req->email,
        'token' => $req->token,
      ])->first();
      $this->logExecutionTime($stepStart, 'fetchPasswordReset', 'completed');
      if (!$reset) {
        Log::debug($method . ' - invalid token', ['email' => $req->email, 'token' => $req->token]);
        return back()->withInput()->with('error', 'Invalid token!');
      }
      $stepStart = microtime(true);
      $model::where('email', $req->email)->update(['password' => Hash::make($req->password)]);
      $this->logExecutionTime($stepStart, 'updatePassword', 'completed');
      $stepStart = microtime(true);
      DB::table('password_resets')->where('email', $req->email)->delete();
      $this->logExecutionTime($stepStart, 'cleanupReset', 'completed');
      return redirect('/login')->with('message', 'Your password has been changed.');
    }, ['email' => $req->email, 'token' => $req->token]);
  }

  private function _logUser(Request $req, object $user): void
  {
    $method = __METHOD__;
    Log::debug($method . ' - start', ['user_id' => $user?->id]);
    $this->measureProfile($method, function () use ($req, $user, $method) {
      $stepStart = microtime(true);
      $ip = $req->server('REMOTE_ADDR');
      $this->logExecutionTime($stepStart, 'getClientIp', 'completed');
      $stepStart = microtime(true);
      $query = @json_decode((string) file_get_contents("https://ip-api.com/json/{$ip}"), true) ?: [];
      $this->logExecutionTime($stepStart, 'fetchGeoIp', 'completed');
      $stepStart = microtime(true);
      $ua = $req->server('HTTP_USER_AGENT');
      $wb = new \WhichBrowser\Parser($ua);
      $this->logExecutionTime($stepStart, 'parseUserAgent', 'completed');
      if ($wb->device->type === 'bot') return;
      $stepStart = microtime(true);
      $ref = parse_url($req->server('HTTP_REFERER') ?? '');
      $details = json_encode([
        ...($query ?? []),
        'browser_name' => $wb->browser->name ?? null,
        'os_name' => $wb->os->name ?? null,
        'browser_language' => mb_substr($req->server('HTTP_ACCEPT_LANGUAGE') ?? '', 0, 2),
        'device_type' => Utility::getDeviceType($ua),
        'referrer_host' => $ref['host'] ?? null,
        'referrer_path' => $ref['path'] ?? null,
      ]);
      $this->logExecutionTime($stepStart, 'assembleDetails', 'completed');
      $stepStart = microtime(true);
      LoginDetail::create([
        UsersConstants::COL_USER_ID => $user?->id,
        'ip' => $ip,
        'date' => now(),
        'Details' => $details,
        DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
      ]);
      $this->logExecutionTime($stepStart, 'persistLoginDetail', 'completed');
    }, ['user_id' => $user?->id, 'ip' => $req->server('REMOTE_ADDR')]);
  }

  private static function _setLocale(?string $lang = null): string
  {
    $lang ??= Utility::getValByName(SettingsConstants::DEF_LNG);
    App::setLocale($lang);
    return $lang;
  }

  private static function _logoutIfInactive(object $user): void
  {
    if (!$user?->is_active || !$user?->delete_status) auth()->logout();
  }

  private static function _updateLastLogin(object $user): void
  {
    $user->update(['last_login_at' > Carbon::now()->toDateTimeString()]);
  }

  private static function _sendReset(
    Request $req,
    string  $table,
    string  $view
  ): RedirectResponse|JsonResponse {
    $req->validate(['email' => "required|email|exists:$table"]);
    $token = Str::random(60);
    DB::table('password_resets')->insert([
      'email' => $req->email,
      'token' => $token,
      'created_at' => Carbon::now(),
    ]);
    try {
      Mail::send(
        $view,
        ['token' => $token],
        static function ($m) use ($req) {
          $m->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
          $m->to($req->email);
          $m->subject('Reset Password Notification');
        }
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    return back()->with('status', 'We have e‑mailed your password reset link!');
  }

  private function _guardLogin(
    Request $req,
    string  $guard,
    array   $creds,
    string  $route
  ): RedirectResponse|JsonResponse {
    if (!Auth::guard($guard)->attempt(
      $creds,
      $req->boolean('remember')
    )) {
      return $this->sendFailedLoginResponse($req);
    }
    $user = Auth::guard($guard)->user();
    if (!$user?->is_active) {
      Auth::guard($guard)->logout();
      return defaultPermissionDenial(
        $req,
        new \Exception('inactive'),
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    self::_updateLastLogin($user);
    return redirect()->route($route);
  }
}

/*! ALERT ip-api.com & unserialize can expose the server to remote‑code or timing attacks */
