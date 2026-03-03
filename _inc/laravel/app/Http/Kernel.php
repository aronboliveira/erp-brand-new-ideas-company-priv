<?php

namespace App\Http;

use Throwable;
use Carbon\Carbon;
use App\Config\Constants\{MiddlewaresConstants, RoutesKeysConstants};
use App\Http\Middleware\{
    Authenticate,
    CheckMount,
    DebugRouteToConsole,
    EncryptCookies,
    PreventRequestsDuringMaintenance,
    PusherConfig,
    RecordLanding,
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
            DebugRouteToConsole::class,
            RecordLanding::class,
            CheckMount::class,
            // StripHtmlComments::class
        ],
        MiddlewaresConstants::API => [
            MiddlewaresConstants::TRT . ':' . RoutesKeysConstants::API_KEY,
            SubstituteBindings::class,
        ],
        MiddlewaresConstants::SET => [
            RecordLanding::class,
            CheckMount::class,
        ]
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
        'check.mount'                         => CheckMount::class,
    ];
    public function handle($request)
    {
        $class  = class_basename(self::class);
        $method = __FUNCTION__;
        $tag    = "{$class}::{$method}";
        error_log("{$tag} start " . json_encode([
            'uri'    => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'ip'     => $request->ip(),
        ]));
        $this->requestStartedAt = Carbon::now();
        try {
            $request->enableHttpMethodParameterOverride();
            $response = $this->sendRequestThroughRouter($request);
            $status  = $response->getStatusCode();
            error_log("{$tag} response generated status={$status}");
        } catch (Throwable $e) {
            error_log("{$tag} exception " . json_encode([
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]));
            $this->reportException($e);
            $response = $this->renderException($request, $e);
        }
        $this->app['events']->dispatch(new RequestHandled($request, $response));
        $duration = Carbon::now()->diffInMilliseconds($this->requestStartedAt);
        error_log("{$tag} finished duration_ms={$duration}");
        return $response;
    }
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}
