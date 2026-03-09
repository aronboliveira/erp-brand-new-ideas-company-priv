<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Abstracts\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

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
          return redirect(RouteServiceProvider::HOME . '?verified=1');
        $this->logExecutionTime($stepStart, 'checkAlreadyVerified', 'completed');
        $stepStart = microtime(true);
        if ($request->user()->markEmailAsVerified()) event(new Verified($request->user()));
        $this->logExecutionTime($stepStart, 'markEmailVerified', 'completed');
        return redirect(RouteServiceProvider::HOME . '?verified=1');
      } catch (\Throwable $e) {
        Log::error($method . ' - email verification failed', [
            'file' => __FILE__,
            'class' => __CLASS__,
            'error_class' => get_class($e),
            'message' => $e->getMessage(),
            'user_id' => $request->user()->id,
            'id' => $id
        ]);
        return defaultUndefinedException($request, $e, $method);
      }
    }, ['user_id' => $request->user()->id, 'id' => $id]);
  }
}
