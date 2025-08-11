<?php

namespace App\Http;

use Throwable;
use Carbon\Carbon;
use App\Config\Constants\{MiddlewaresConstants, RoutesKeysConstants};
use App\Http\Middleware\{
    Authenticate,
    EncryptCookies,
    PreventRequestsDuringMaintenance,
    PusherConfig,
    RedirectIfAuthenticated,
    RevalidateBackHistory,
    SecureHeaders,
    TrimStrings,
    TrustProxies,
    VerifyCsrfToken,
    XSS,
    StripHtmlComments
};
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Auth\Middleware\{
    AuthenticateWithBasicAuth,
    Authorize,
    EnsureEmailIsVerified,
    RequirePassword,
};
use Illuminate\Foundation\Http\{
    Kernel as HttpKernel,
    Middleware\ConvertEmptyStringsToNull,
    Middleware\ValidatePostSize,
    Events\RequestHandled
};
use Illuminate\Http\Middleware\{HandleCors, SetCacheHeaders};
use Illuminate\Routing\Middleware\{
    SubstituteBindings,
    ThrottleRequests,
    ValidateSignature
};
use Illuminate\Session\Middleware\{
    AuthenticateSession,
    StartSession
};
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\Console\Output\ConsoleOutput;

class Kernel extends HttpKernel
{
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];
    protected $middlewareGroups = [
        MiddlewaresConstants::WEB => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            SecureHeaders::class,
            \App\Http\Middleware\DebugRouteToConsole::class,
            \App\Http\Middleware\RecordLanding::class,
            // StripHtmlComments::class
        ],
        MiddlewaresConstants::API => [
            MiddlewaresConstants::TRT . ':' . RoutesKeysConstants::API_KEY,
            SubstituteBindings::class,
        ],
    ];
    protected $routeMiddleware = [
        MiddlewaresConstants::TRT               => ThrottleRequests::class,
        MiddlewaresConstants::AUTH              => Authenticate::class,
        MiddlewaresConstants::AUTH . '.basic'   => AuthenticateWithBasicAuth::class,
        MiddlewaresConstants::AUTH . '.session' => AuthenticateSession::class,
        MiddlewaresConstants::XSS               => XSS::class,
        MiddlewaresConstants::GT                => RedirectIfAuthenticated::class,
        'can'                                   => Authorize::class,
        MiddlewaresConstants::VF                => EnsureEmailIsVerified::class,
        MiddlewaresConstants::PSR               => PusherConfig::class,
        MiddlewaresConstants::REV               => RevalidateBackHistory::class,
        'password.confirm'                      => RequirePassword::class,
        'cache.headers'                         => SetCacheHeaders::class,
        MiddlewaresConstants::SGN               => ValidateSignature::class,
    ];
    public function handle($request)
    {
        $class  = class_basename(self::class);
        $method = __FUNCTION__;
        $tag    = "{$class}::{$method}";
        $output = new ConsoleOutput();
        error_log("{$tag} start " . json_encode([
            'uri'    => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'ip'     => $request->ip(),
        ]));
        $output->writeln("[{$tag}] Request started: {$request->getMethod()} {$request->getRequestUri()}");
        $this->requestStartedAt = Carbon::now();
        try {
            $request->enableHttpMethodParameterOverride();
            $response = $this->sendRequestThroughRouter($request);
            $status  = $response->getStatusCode();
            error_log("{$tag} response generated status={$status}");
            $output->writeln("[{$tag}] Response status: {$status}");
        } catch (Throwable $e) {
            error_log("{$tag} exception " . json_encode([
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]));
            $output->writeln("[{$tag}] Exception: {$e->getMessage()}");
            $this->reportException($e);
            $response = $this->renderException($request, $e);
        }
        $this->app['events']->dispatch(new RequestHandled($request, $response));
        $duration = Carbon::now()->diffInMilliseconds($this->requestStartedAt);
        error_log("{$tag} finished duration_ms={$duration}");
        $output->writeln("[{$tag}] Finished in {$duration} ms");
        return $response;
    }
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}
