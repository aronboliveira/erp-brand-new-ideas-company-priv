<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use function App\Http\Controllers\defaultUndefinedException;

class VerifyEmailController extends Controller
{
  /** @return RedirectResponse */
  public function __invoke(EmailVerificationRequest $request, string|int $id)
  {
    $method = __METHOD__;
    Log::debug($method . ' - start', ['user_id' => $request->user()->id, 'id' => $id]);
    return $this->measureProfile($method, function () use ($request, $id, $method) {
      try {
        $stepStart = microtime(true);
        if ($request->user()->hasVerifiedEmail())
          return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
        $this->logExecutionTime($stepStart, 'checkAlreadyVerified', 'completed');
        $stepStart = microtime(true);
        if ($request->user()->markEmailAsVerified()) event(new Verified($request->user()));
        $this->logExecutionTime($stepStart, 'markEmailVerified', 'completed');
        return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
      } catch (\Throwable $e) {
        Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'user_id' => $request->user()->id, 'id' => $id]);
        Log::error($method . ' - email verification failed', ['user_id' => $request->user()->id, 'id' => $id]);
        return defaultUndefinedException($request, $e, $method);
      }
    }, ['user_id' => $request->user()->id, 'id' => $id]);
  }
}
