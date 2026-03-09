<?php

namespace App\Http\Controllers\Auth;

use App\Config\Constants\{SettingsConstants as SC, ViewsConstants as VW};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Utility;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request,
    Response
};
use Illuminate\Support\Facades\{
    Log,
    Password,
    Validator
};
use Illuminate\View\View;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

class PasswordResetLinkController extends Controller
{
    public const CRT = 'create';
    public const STR = 'store';


  public function create(Request $req): View|RedirectResponse|JsonResponse|null
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($req, $action) {
      Log::info("{$action} – rendering email form view", ['view' => VW::PWD . 'email']);
      try {
        return view(VW::PWD . 'email');
      } catch (\Throwable $e) {
        Log::error("{$action} – exception thrown", [
            'file' => __FILE__,
            'class' => __CLASS__,
            'error_class' => get_class($e),
            'message' => $e->getMessage()
        ]);
        return self::_catch($req, $e);
      }
    });
  }

  public function store(Request $req): RedirectResponse|JsonResponse|null
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $req) {
      $valStart = microtime(true);
      if ($r = self::_validate($req)) return $r;
      $this->logExecutionTime($valStart, $action . '::_validate', 'completed');
      try {
        Log::info("[$action] Applying SMTP configuration", ['email' => $req->input('email')]);
        $smtpStart = microtime(true);
        Utility::smtpDetail(1);
        $this->logExecutionTime($smtpStart, $action . '::smtpDetail', 'completed');
        Log::info("[$action] Sending password reset link", ['email' => $req->input('email')]);
        $sendStart = microtime(true);
        $status = Password::sendResetLink($req->only('email'));
        $this->logExecutionTime($sendStart, $action . '::sendResetLink', 'completed');
        return $status === Password::RESET_LINK_SENT
          ? back()->with('status', __($status))
          : back()->withInput($req->only('email'))->withErrors(['email' => __($status)]);
      } catch (\Throwable $e) {
        Log::error("[$action] Error sending reset link", [
            'file' => __FILE__,
            'class' => __CLASS__,
            'error_class' => get_class($e),
            'message' => $e->getMessage()
        ]);
        return redirect()->back()->withErrors('E-Mail has not been sent due to SMTP configuration');
      }
    }, ['email' => $req->input('email')]);
  }

  private static function _catch(
    Request    $req,
    \Throwable $e
  ): RedirectResponse|JsonResponse|null {
    return defaultUndefinedException(
      $req,
      $e,
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
    );
  }

  private static function _validate(
    Request $req
  ): RedirectResponse|JsonResponse|null {
    $rules = ['email' => 'required|email'];
    if (env('RECAPTCHA_MODULE') === 'on')
      $rules[SC::G_RCPT_RES] = 'required|captcha';
    $v = Validator::make($req->all(), $rules);
    return $v->fails()
      ? redirect()->back()->withErrors($v)->withInput()
      : null;
  }
}
