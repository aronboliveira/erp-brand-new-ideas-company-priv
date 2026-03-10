<?php

namespace Modules\LandingPage\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\{Log, Route};
use Illuminate\Support\Str;
use Modules\LandingPage\Config\Constants\MiddlewaresConstants;
use Symfony\Component\Console\Output\NullOutput; // TEMP: was ConsoleOutput
use Throwable;

final class RouteServiceProvider extends ServiceProvider
{
    private const API_PREFIX          = 'api';
    private const API_ROUTES          = '/Routes/api.php';
    private const MODULE              = 'LandingPage';
    private const WEB_ROUTES          = '/Routes/web.php';
    protected string $moduleNamespace = 'Modules\LandingPage\Http\Controllers';

    public function boot(): void
    {
        $tag   = 'Landing ' . class_basename(self::class) . '::' . __FUNCTION__;
        $output = new NullOutput(); // TEMP: was ConsoleOutput
        $msg = 'Booting Landing Page ' . class_basename(RouteServiceProvider::class) . '...';
        Log::debug("{$tag} called");
        app()->runningInConsole()
            ? $output->writeln('<question> ' . $msg . ' </question> ')
            : $output->writeln($msg);
        parent::boot();
        /** @phpstan-ignore-next-line */
        foreach (Route::getRoutes() as $route) {
            $specialRoutes = ['login', 'password', 'register', 'verification',
                              'verify', 'logout', 'forgot-password', 'reset-password',
                              'confirm-password', 'two-factor-challenge', 'fortify-login'];
            $converted = Str::kebab($route->uri());
            $segments = collect(explode('/', $converted));
            // Skip pluralization if ANY segment (not just last) is a special auth route
            $hasSpecialSegment = $segments->contains(
                fn($seg) => in_array(preg_replace('/\{.*\}/', '', $seg), $specialRoutes)
            );
            if ($hasSpecialSegment) {
                $finalUri = $converted;
            } else {
                $lastSegment = $segments->last();
                $finalUri = $segments->slice(0, -1)
                    ->map(fn($segment) => Str::startsWith($segment, '{')
                        ? $segment
                        : Str::plural($segment))
                    ->push($lastSegment)
                    ->join('/');
            }
            $route->setUri($finalUri);
        }
    }

    public function map(): void
    {
        $tag   = 'Landing ' . class_basename(self::class) . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output = new NullOutput(); // TEMP: was ConsoleOutput
        $output->writeln("[{$tag}] Mapping Landing Page routes");
        try {
            $this->mapWebRoutes();
            $this->mapApiRoutes();
            Log::debug("{$tag} — all Landing Page routes mapped");
            $output->writeln("[{$tag}] Landing Page Routes mapped successfully");
        } catch (Throwable $e) {
            Log::critical("{$tag} failed to map Landing Page routes", ['message' => $e->getMessage()]);
            $output->writeln("[{$tag}] Landing Page Route Map failed: {$e->getMessage()}");
        }
    }

    protected function mapWebRoutes(): void
    {
        $tag   = 'Landing ' . class_basename(self::class) . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output = new NullOutput(); // TEMP: was ConsoleOutput
        $output->writeln("[{$tag}] Registering Landing Page web routes");
        try {
            Route::middleware([
                MiddlewaresConstants::WEB,
            ])->group(module_path(self::MODULE, self::WEB_ROUTES));
            Log::debug("{$tag} web routes loaded", ['path' => self::WEB_ROUTES]);
            $output->writeln("[{$tag}] Landing Page Web routes loaded");
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", ['message' => $e->getMessage()]);
            $output->writeln("[{$tag}] Landing Page mapWebRoutes failed: {$e->getMessage()}");
        }
    }

    protected function mapApiRoutes(): void
    {
        $tag   = 'Landing ' . class_basename(self::class) . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output = new NullOutput(); // TEMP: was ConsoleOutput
        $output->writeln("[{$tag}] Registering Landing Page API routes");
        try {
            Route::prefix(self::API_PREFIX)
                ->middleware(MiddlewaresConstants::API)
                ->group(module_path(self::MODULE, self::API_ROUTES));
            Log::debug("{$tag} Landing Page api routes loaded", [
                'prefix' => self::API_PREFIX,
                'path'   => self::API_ROUTES,
            ]);
            $output->writeln("[{$tag}] Landing Page API routes loaded");
        } catch (Throwable $e) {
            Log::critical("{$tag} Landing Page failed", ['message' => $e->getMessage()]);
            $output->writeln("[{$tag}] Landing Page mapApiRoutes failed: {$e->getMessage()}");
        }
    }
}
