<?php

namespace App\Http\Controllers\Auth;

use App\Config\Constants\{
  DatabaseConstants,
  SettingsConstants,
  ViewsConstants
};
use App\Http\Controllers\Controller;
use App\Models\Utility;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\App;
use Illuminate\View\View;
use function App\Http\Controllers\defaultUndefinedException;

class EmailVerificationPromptController extends Controller
{

  public function __invoke(Request $req): RedirectResponse|View
  {
    try {
      return $req->user()->hasVerifiedEmail()
        ? redirect()->intended(RouteServiceProvider::HOME)
        : view(ViewsConstants::AUT . '.verify');
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public const SH_VR_FM = 'showVerifyForm';
  public function showVerifyForm(string $lang = DatabaseConstants::DEFAULT_LANG): View
  {
    return view(
      ViewsConstants::AUT . '.verify',
      ['lang' => self::_setLocale($lang)]
    );
  }

  private static function _setLocale(string $lang = null): string
  {
    $lang ??= Utility::getValByName(SettingsConstants::DEF_LNG);
    App::setLocale($lang);
    return $lang;
  }
}



// ! ALERT _setLocale() trusts user‑supplied $lang; ensure only supported locales are accepted (e.g., whitelist or in_array) to avoid locale‑based path traversal or SSRF attacks.