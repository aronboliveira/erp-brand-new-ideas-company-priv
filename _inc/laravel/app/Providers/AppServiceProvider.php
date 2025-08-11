<?php

namespace App\Providers;

use Throwable;
use App\Config\Constants\ViewsConstants;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\{Event, Log, Schema};
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

final class AppServiceProvider extends ServiceProvider
{
    private const DEFAULT_STRING_LENGTH = 191;

    public function register(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' registering...');
    }

    public function boot(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' booting...');
        try {
            Schema::defaultStringLength(self::DEFAULT_STRING_LENGTH);
            Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' set defaultStringLength', [
                'length' => self::DEFAULT_STRING_LENGTH
            ]);
            Fortify::requestPasswordResetLinkView(function () {
                return view(ViewsConstants::AUT . '.forgot_password');
            });
        } catch (Throwable $e) {
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['message' => $e->getMessage()]);
        }
        Event::listen(RouteMatched::class, function (RouteMatched $event) {
            Log::debug('Route Matched', [
                'uri' => $event->request->getUri(),
                'method' => $event->request->getMethod(),
                'route_name' => $event->route->getName(),
                'controller' => $event->route->getControllerClass(),
                'action' => $event->route->getActionMethod(),
                'middleware' => $event->route->gatherMiddleware(),
                'parameters' => $event->route->parameters(),
            ]);
        });
    }
}
