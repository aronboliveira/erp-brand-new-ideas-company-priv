<?php

namespace App\Http\Controllers\Auth;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  SettingsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Http\Controllers\Controller;
use App\Models\{
  ExperienceCertificate,
  GeneratedOfferLetter,
  JoiningLetter,
  Noc,
  Role,
  User,
  Utility
};
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\{
  RedirectResponse,
  Request
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
  App,
  Auth,
  DB,
  Hash,
  Log
};
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Helpers\SafeConsoleOutput;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\HasCrudConstants;
class RegisteredUserController extends Controller
{
    use HasCrudConstants;

  public function __construct()
  {
    SafeConsoleOutput::make()->writeln('Constructing ' . __CLASS__);
  }

  public function store(Request $req): RedirectResponse|View
  {
    $function = __FUNCTION__;
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $action, $function) {
      $ctx = [
        'ip'           => $req->ip(),
        'user_agent'   => $req->userAgent(),
        'email_input'  => $req->input('email', 'n/a'),
      ];
      Log::info("{$action} – user registration attempt", $ctx);
      try {
        self::_validateRecaptcha($req);
        $req->validate([
          UsersConstants::COL_NM => 'required|string|max:255',
          UsersConstants::COL_EM => 'required|string|email|max:255|unique:' . DatabaseConstants::TABLE_USERS,
          UsersConstants::COL_PW => ['required', 'string', 'min:8', 'confirmed', Rules\Password::defaults()],
        ]);
        $ctx['requester'] = $req->user()?->id ?? Auth::id() ?? DatabaseConstants::DEFAULT_UUID;
        Log::debug("{$action} – validation passed", $ctx);
        DB::beginTransaction();
        $user = User::create([
          UsersConstants::COL_NM => $req->name,
          UsersConstants::COL_EM => $req->email,
          UsersConstants::COL_PW => Hash::make($req->password),
          UsersConstants::COL_TP => PermissionsConstants::CPN,
          UsersConstants::COL_DPL => DatabaseConstants::DEFAULT_PIPELINE,
          UsersConstants::COL_PL => DatabaseConstants::DEFAULT_PLAN,
          UsersConstants::COL_LG => Utility::getValByName(SettingsConstants::DEF_LNG),
          UsersConstants::COL_AV => '',
          DatabaseConstants::COL_TABLE_CREATOR => $ctx['requester'],
        ]);
        Auth::login($user);
        Log::info("{$action} – user created and logged in", ['user_id' => $user->id]);
        $settings = Utility::settings();
        if (($settings['email_verification'] ?? 'off') === 'on') {
          Utility::smtpDetail(1);
          event(new Registered($user));
          self::_assignCompanyRole($user);
          self::_seedDefaults($user->id);
          DB::commit();
          Log::notice("{$action} – verification view", ['user_id' => $user->id]);
          return view(ViewsConstants::AUT . '.verify');
        }
        $user->email_verified_at = Carbon::now(); /** @phpstan-ignore property.notFound */
        $user->save();
        self::_assignCompanyRole($user);
        self::_seedDefaults($user->id);
        Utility::sendUserEmailTemplate(
          'new_user',
          [$user->email],
          [UsersConstants::COL_EM => $user->email, UsersConstants::COL_PW => $req->password]
        );
        DB::commit();
        Log::info("{$action} – registration complete, redirecting home", ['user_id' => $user->id]);
        return redirect(RouteServiceProvider::HOME);
      } catch (\Throwable $e) {
        if (DB::transactionLevel() > 0) {
          DB::rollBack();
        }
        Log::error("{$action} – exception", [
          'exception' => get_class($e),
          'message'   => $e->getMessage(),
          'trace'     => $e->getTraceAsString(),
        ]);
        return defaultUndefinedException($req, $e, __CLASS__ . '::' . $function);
      }
    });
  }

  public const SHW_RG_FM = 'showRegistrationForm';
  public function showRegistrationForm(?string $lang = null): RedirectResponse|\Illuminate\View\View
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $lang) {
      Log::info("[$action] Starting showRegistrationForm", ['lang_param' => $lang]);
      $settingsStart = microtime(true);
      $settings = Utility::settings();
      $this->logExecutionTime($settingsStart, $action . '::settings', 'completed');
      if (($settings[SettingsConstants::ENB_SGU] ?? 'off') !== 'on') {
        Log::warning("[$action] Signup feature disabled", ['settings' => $settings]);
        Log::debug("[$action] Feature flag ENB_SGU not 'on'", ['value' => $settings[SettingsConstants::ENB_SGU] ?? null]);
        $this->logExecutionTime($settingsStart, $action . '::featureCheck', 'completed');
        return redirect('login');
      }
      $localeStart = microtime(true);
      $lang = $lang ?: App::getLocale() ?: Utility::getValByName(SC::DEF_LNG) ?: DC::DEFAULT_LANG;
      if (!in_array($lang, array_keys(Utility::langList()), true)) {
        $lang = DC::DEFAULT_LANG;
      }
      App::setLocale($lang);
      $this->logExecutionTime($localeStart, $action . '::setLocale', 'completed');
      return view(ViewsConstants::AUT . '.register', compact('lang'));
    }, ['lang_param' => $lang]);
  }

  private static function _validateRecaptcha(Request $req): void
  {
    $rules = env('RECAPTCHA_MODULE') === 'on'
      ? [SettingsConstants::G_RCPT_RES => 'required|captcha']
      : [];
    if ($rules) validator($req->all(), $rules)->validate();
  }

  private static function _seedDefaults(int $uid): void
  {
    $utilityCalls = [
      User::USR_DEF_DT_REG,
      User::USR_WA_REG,
      User::USR_DEF_BA,
    ];
    $map = [
      Utility::class => [
        Utility::COA_TP_DT,
        Utility::COA_DATA1,
        Utility::PPL_LD_DL_STG,
        Utility::PRJ_TSK_STGS,
        'labels',
        'sources',
        Utility::JB_STG,
      ],
      GeneratedOfferLetter::class => [GeneratedOfferLetter::DEF_OFL_REG],
      ExperienceCertificate::class => [ExperienceCertificate::DEF_EXP_CRT_REG],
      JoiningLetter::class => [JoiningLetter::DEF_JG_LT_REG],
      Noc::class => [Noc::DEF_NOC_CRT_REG],
    ];
    foreach ($utilityCalls as $m) User::find($uid)->$m($uid);
    foreach ($map as $cls => $methods) {
      foreach ($methods as $m) $cls::$m($uid);
    }
  }

  private static function _assignCompanyRole(User $user): void
  {
    $user?->assignRole(Role::findByName(PermissionsConstants::CPN));
  }
}

// ! ALERT _seedDefaults() runs multiple heavy inserts; wrap costly operations in queued jobs if response time or atomicity become concerns.