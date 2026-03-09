<?php

namespace Modules\LandingPage\Providers;

use Illuminate\Support\Facades\{Log, Route, View};
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Output\NullOutput; // TEMP: was ConsoleOutput
use Throwable;

final class AddMenuProvider extends ServiceProvider
{
    private const MODULE      = 'landingpage';
    private const ROUTE_PREFIX = self::MODULE;
    private const STACK_NAME  = 'add_menu';
    private const VIEW_NAME   = self::MODULE . '::menu.' . self::MODULE;
    protected $defer = true;

    /**
     * Boot service: register view composer for named routes.
     *
     * @return void
     */
    public function boot(): void
    {
        $output = new NullOutput(); // TEMP: was ConsoleOutput
        $output->writeln('Booting ' . class_basename(self::class));
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        try {
            $routes = $this->getNamedRoutes(self::ROUTE_PREFIX);
            if (empty($routes)) {
                Log::warning(
                    __CLASS__ . '::' . __FUNCTION__ . ' found no named routes',
                    ['prefix' => self::ROUTE_PREFIX]
                );
                return;
            }
            View::composer(
                $routes,
                fn($view) => $view->getFactory()->startPush(
                    self::STACK_NAME,
                    view(self::VIEW_NAME)
                )
            );
            Log::debug(
                __CLASS__ . '::' . __FUNCTION__ . ' registered composer',
                ['routes_count' => count($routes)]
            );
        } catch (Throwable $e) {
            $output->writeln('Failed to boot ' . class_basename(self::class));
            Log::critical(
                __CLASS__ . '::' . __FUNCTION__ . ' failed',
                ['message' => $e->getMessage()]
            );
        }
    }

    /**
     * Collect and filter named routes by prefix.
     *
     * @param  string  $prefix
     * @return array<int, string>
     */
    private function getNamedRoutes(string $prefix): array
    {
        Log::debug(
            __CLASS__ . '::' . __FUNCTION__ . ' called',
            ['prefix' => $prefix]
        );
        $allNames = collect(Route::getRoutes())
            ->map(fn($route) => $route->getName());
        if ($allNames->isEmpty()) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' found no routes');
            return [];
        }
        // TODO: consider caching route names for performance
        $named = $allNames
            ->filter(fn($name) => is_string($name) &&
                str_starts_with($name, $prefix))
            ->values()
            ->toArray();
        empty($named)
            ? Log::warning(
                __CLASS__ . '::' . __FUNCTION__ . ' no matches',
                ['prefix' => $prefix]
            )
            : Log::debug(
                __CLASS__ . '::' . __FUNCTION__ . ' matched routes',
                ['count' => count($named)]
            );
        return $named;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        // no bindings
    }

    /**
     * Services provided by this provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        return ['landingpage.menu'];
    }
}
