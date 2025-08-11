<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\Log;
use function App\Http\Controllers\defaultUndefinedException;

class EmailVerificationNotificationController extends Controller
{
  public function store(Request $req): RedirectResponse|JsonResponse|null
  {
    $method = __METHOD__;
    Log::debug($method . ' - start', ['user_id' => $req->user()?->id]);
    return $this->measureProfile($method, function () use ($req, $method) {
      $stepStart = microtime(true);
      if ($req->user()?->hasVerifiedEmail())
        return redirect()->intended(RouteServiceProvider::HOME);
      $this->logExecutionTime($stepStart, 'checkEmailVerified', 'completed');
      try {
        $stepStart = microtime(true);
        $req->user()?->sendEmailVerificationNotification();
        $this->logExecutionTime($stepStart, 'sendEmailVerificationNotification', 'completed');
        return back()->with('status', 'verification-link-sent');
      } catch (\Throwable $e) {
        Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
        Log::error($method . ' - error sending verification link', ['user_id' => $req->user()?->id]);
        return self::_catch($req, $e);
      }
    }, ['user_id' => $req->user()?->id]);
  }


  private static function _catch(
    Request   $req,
    \Throwable $e
  ): RedirectResponse|JsonResponse|null {
    return defaultUndefinedException(
      $req,
      $e,
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
    );
  }
}
