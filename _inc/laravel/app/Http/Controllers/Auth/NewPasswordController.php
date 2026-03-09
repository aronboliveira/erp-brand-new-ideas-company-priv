<?php

namespace App\Http\Controllers\Auth;

use App\Config\Constants\ViewsConstants;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\{
  RedirectResponse,
  Request
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
  Hash,
  Log,
  Password
};
use Illuminate\Validation\Rules\Password as PasswordRule;
use function App\Http\Controllers\defaultUndefinedException;

class NewPasswordController extends Controller
{
  /** @return \Illuminate\View\View|RedirectResponse */
  public function create(Request $request)
  {
    $function = __FUNCTION__;
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $request, $function) {
      try {
        $viewStart = microtime(true);
        $response = view(ViewsConstants::PWD . 'reset', ['request' => $request]);
        $this->logExecutionTime($viewStart, $action . '::view', 'completed');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[$action] Error in " . $function, ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        return defaultUndefinedException($request, $e, $action);
      }
    }, []);
  }

  /** @return RedirectResponse */
  public function store(Request $request)
  {
    $function = __FUNCTION__;
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($request, $action, $function) {
      Log::info("{$action} – validating input", ['inputs' => array_keys($request->all())]);
      $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => ['required', 'confirmed', PasswordRule::defaults()],
      ]);
      try {
        Log::debug("{$action} – attempting password reset", ['email' => $request->email]);
        $status = Password::reset(
          $request->only('email', 'password', 'password_confirmation', 'token'),
          fn ($user) => tap($user)->forceFill([ /** @phpstan-ignore method.notFound */
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
          ])->save() && event(new PasswordReset($user))
        );
        if ($status === Password::PASSWORD_RESET) {
          Log::info("{$action} – password reset successful", ['status' => $status]);
          return redirect()->route('login')->with('status', __($status));
        }
        Log::warning("{$action} – password reset failed", ['status' => $status]);
        return back()
          ->withInput($request->only('email'))
          ->withErrors(['email' => __($status)]);
      } catch (\Throwable $e) {
        Log::error("{$action} – exception thrown", [
          'exception' => get_class($e),
          'message'   => $e->getMessage(),
          'trace'     => $e->getTraceAsString(),
        ]);
        return defaultUndefinedException($request, $e, static::class . '::' . $function);
      }
    });
  }
}
