<?php

namespace App\Http\Controllers\Auth;

use App\Config\Constants\ViewsConstants;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log};
use Illuminate\Validation\ValidationException;
use function App\Http\Controllers\defaultUndefinedException;

class ConfirmablePasswordController extends Controller
{
  /** @return \Illuminate\View\View|RedirectResponse */
  public function show(Request $request)
  {
    $function = __FUNCTION__;
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($request, $action, $function) {
      try {
        if (!$request->user()) {
          Log::notice("{$action} – guest access, redirecting to login");
          return redirect()->route('login');
        }
        Log::info("{$action} – rendering confirm-password view", ['view' => ViewsConstants::AUT . '.confirm-password']);
        return view(ViewsConstants::AUT . '.confirm-password');
      } catch (\Throwable $e) {
        Log::error("{$action} – exception thrown", ['exception' => get_class($e), 'message' => $e->getMessage()]);
        return defaultUndefinedException($request, $e, static::class . '::' . $function);
      }
    });
  }

  /** @return RedirectResponse|ValidationException */
  public function store(Request $request)
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $request) {
      try {
        Log::info("[$action] Attempting password confirmation", ['email' => $request->user()->email]);
        $validateStart = microtime(true);
        if (!Auth::guard('web')->validate(['email' => $request->user()->email, 'password' => $request->password]))
          throw ValidationException::withMessages(['password' => __('These credentials do not match our records.')]);
        $this->logExecutionTime($validateStart, $action . '::validate', 'completed');
        $sessionStart = microtime(true);
        $request->session()->put(ViewsConstants::AUT . '.password_confirmed_at', time());
        $this->logExecutionTime($sessionStart, $action . '::sessionPut', 'completed');
        return redirect()->intended(RouteServiceProvider::HOME);
      } catch (ValidationException $e) {
        Log::warning("[$action] Validation failed", ['error' => $e->getMessage()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        throw $e;
      } catch (\Throwable $e) {
        Log::error("[$action] Unexpected error", ['error' => $e->getMessage()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        return defaultUndefinedException($request, $e, $action);
      }
    }, ['email' => $request->user()->email]);
  }
}
