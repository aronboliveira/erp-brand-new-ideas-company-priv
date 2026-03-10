<?php

namespace App\Http\Controllers\Auth;

use App\Config\Constants\ViewsConstants as VW;
use App\Http\Controllers\Abstracts\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log};
use Illuminate\Validation\ValidationException;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class ConfirmablePasswordController extends Controller
{
  /** @return \Illuminate\View\View|RedirectResponse */
    public const STR = 'store';
    public const SHW = 'show';

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
        Log::info("{$action} – rendering confirm-password view", ['view' => VW::AUT . '.confirm-password']);
        return view(VW::AUT . '.confirm-password');
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
        $request->session()->put(VW::AUT . '.password_confirmed_at', time());
        $this->logExecutionTime($sessionStart, $action . '::sessionPut', 'completed');
        return redirect()->intended(RouteServiceProvider::HOME);
      } catch (ValidationException $e) {
        Log::warning("[$action] Validation failed", [
            'file' => __FILE__,
            'class' => __CLASS__,
            'error_class' => get_class($e),
            'message' => $e->getMessage()
        ]);
        throw $e;
      } catch (\Throwable $e) {
        Log::error("[$action] Unexpected error", [
            'file' => __FILE__,
            'class' => __CLASS__,
            'error_class' => get_class($e),
            'message' => $e->getMessage()
        ]);
        return defaultUndefinedException($request, $e, $action);
      }
    }, ['email' => $request->user()->email]);
  }
}
