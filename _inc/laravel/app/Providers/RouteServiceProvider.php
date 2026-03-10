<?php

namespace App\Providers;

use App\Config\Constants\{MiddlewaresConstants, RoutesKeysConstants};
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, RateLimiter, Route};
use Illuminate\Support\Str;
use App\Helpers\SafeConsoleOutput;
use Throwable;

final class RouteServiceProvider extends ServiceProvider
{
    public const HOME       = '/';
    public const EMPHOME    = '/hrm-dashboard';

    private const API_PREFIX       = 'api';
    private const API_RATE_LIMITER = RoutesKeysConstants::API_KEY;
    private const API_RATE_LIMIT   = 60;
    private const ROUTES_API_PATH  = 'routes/api.php';
    private const ROUTES_WEB_PATH  = 'routes/web.php';
    private const ROUTES_FORT_PATH = 'routes/fortify.php';

    // Removed: routes use ::class FQCN — no namespace prefix needed.
    // protected $namespace = 'App\\Http\\Controllers';

    public function register(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        $output = SafeConsoleOutput::make();
        $msg = 'Registering main ' . class_basename(RouteServiceProvider::class) . '...';
        app()->runningInConsole()
            ? $output->writeln('<question> ' . $msg . ' </question> ')
            : $output->writeln($msg);
    }

    public function boot(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        $output = SafeConsoleOutput::make();
        $msg = 'Booting main ' . class_basename(RouteServiceProvider::class) . '...';
        app()->runningInConsole()
            ? $output->writeln('<question> ' . $msg . ' </question> ')
            : $output->writeln($msg);
        try {
            $this->configureRateLimiting();
            Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' configured rate limiting');
            parent::boot();
            // Load all routes first, then pluralize segments.
            $this->map();
            /** @var \Illuminate\Routing\RouteCollection $routes */
            $routes = Route::getRoutes();
            /** @phpstan-ignore-next-line */
            foreach ($routes as $route) {
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
            $msg = 'Done configuring the main ' . class_basename(RouteServiceProvider::class);
            app()->runningInConsole()
                ? $output->writeln('<info> ' . $msg . ' </info> ')
                : $output->writeln($msg);
        } catch (Throwable $e) {
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['message' => $e->getMessage()]);
            $msg = 'Failed to boot ' . class_basename(RouteServiceProvider::class);
            app()->runningInConsole()
                ? $output->writeln('<error> ' . $msg . ' </error> ')
                : $output->writeln('## PROVIDER ERROR: ' . $msg);
        }
    }

    public function map(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        $output = SafeConsoleOutput::make();
        $output->writeln('Mapping main application routes');
        try {
            $this->mapApiRoutes();
            $this->mapWebRoutes();
            $this->mapFortifyRoutes();
            Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' completed successfully');
            $output->writeln('Main application routes mapped successfully');
        } catch (Throwable $e) {
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['message' => $e->getMessage()]);
            $output->writeln('Main application route mapping failed: ' . $e->getMessage());
        }
    }

    protected function mapApiRoutes(): void
    {
        $tag = __CLASS__ . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output = SafeConsoleOutput::make();
        try {
            $msg = 'Mapping main API routes...';
            app()->runningInConsole()
                ? $output->writeln('<question> ' . $msg . ' </question> ')
                : $output->writeln($msg);
            if (!file_exists(base_path(self::ROUTES_API_PATH)))
                throw new \Exception('API routes file does not exist: ' . base_path(self::ROUTES_API_PATH));
            if (!is_readable(base_path(self::ROUTES_API_PATH)))
                throw new \Exception('API routes file is not readable: ' . base_path(self::ROUTES_API_PATH));
            Route::prefix(self::API_PREFIX)
                ->middleware(MiddlewaresConstants::API)
                ->group(base_path(self::ROUTES_API_PATH));
            Log::debug("{$tag} completed successfully", [
                'prefix' => self::API_PREFIX,
                'middleware' => MiddlewaresConstants::API,
                'namespace' => $this->namespace,
                'path' => self::ROUTES_API_PATH
            ]);
            $msg = 'Main API routes mapped successfully';
            app()->runningInConsole()
                ? $output->writeln('<info> ' . $msg . ' </info> ')
                : $output->writeln($msg);
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'prefix' => self::API_PREFIX,
                'middleware' => MiddlewaresConstants::API ?? 'UNDEFINED',
                'namespace' => $this->namespace,
                'path' => self::ROUTES_API_PATH,
                'file_exists' => file_exists(base_path(self::ROUTES_API_PATH)),
                'file_readable' => is_readable(base_path(self::ROUTES_API_PATH)),
            ]);
            $msg = 'Failed to map main API routes: ' . $e->getMessage();
            app()->runningInConsole()
                ? $output->writeln('<error> ' . $msg . ' </error> ')
                : $output->writeln('## API ROUTES ERROR: ' . $msg);
        }
    }

    protected function mapWebRoutes(): void
    {
        $tag = __CLASS__ . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output = SafeConsoleOutput::make();
        try {
            $msg = 'Mapping main Web routes...';
            app()->runningInConsole()
                ? $output->writeln('<question> ' . $msg . ' </question> ')
                : $output->writeln($msg);
            if (!file_exists(base_path(self::ROUTES_WEB_PATH)))
                throw new \Exception('Web routes file does not exist: ' . base_path(self::ROUTES_WEB_PATH));
            if (!is_readable(base_path(self::ROUTES_WEB_PATH)))
                throw new \Exception('Web routes file is not readable: ' . base_path(self::ROUTES_WEB_PATH));
            Route::middleware(MiddlewaresConstants::WEB)
                ->group(base_path(self::ROUTES_WEB_PATH));
            Log::debug("{$tag} completed successfully", [
                'middleware' => MiddlewaresConstants::WEB,
                'namespace' => $this->namespace,
                'path' => self::ROUTES_WEB_PATH
            ]);
            $msg = 'Main Web routes mapped successfully';
            app()->runningInConsole()
                ? $output->writeln('<info> ' . $msg . ' </info> ')
                : $output->writeln($msg);
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'middleware' => MiddlewaresConstants::WEB ?? 'UNDEFINED',
                'namespace' => $this->namespace,
                'path' => self::ROUTES_WEB_PATH,
                'file_exists' => file_exists(base_path(self::ROUTES_WEB_PATH)),
                'file_readable' => is_readable(base_path(self::ROUTES_WEB_PATH)),
            ]);
            $msg = 'Failed to map main Web routes: ' . $e->getMessage();
            app()->runningInConsole()
                ? $output->writeln('<error> ' . $msg . ' </error> ')
                : $output->writeln('## WEB ROUTES ERROR: ' . $msg);
        }
    }

    protected function mapFortifyRoutes(): void
    {
        $tag = __CLASS__ . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output = SafeConsoleOutput::make();
        try {
            $msg = 'Mapping main Fortify routes...';
            app()->runningInConsole()
                ? $output->writeln('<question> ' . $msg . ' </question> ')
                : $output->writeln($msg);
            if (!file_exists(base_path(self::ROUTES_FORT_PATH)))
                throw new \Exception('Fortify routes file does not exist: ' . base_path(self::ROUTES_FORT_PATH));
            if (!is_readable(base_path(self::ROUTES_FORT_PATH)))
                throw new \Exception('Fortify routes file is not readable: ' . base_path(self::ROUTES_FORT_PATH));
            Route::middleware(MiddlewaresConstants::WEB)
                ->group(base_path(self::ROUTES_FORT_PATH));
            Log::debug("{$tag} completed successfully", [
                'middleware' => MiddlewaresConstants::WEB,
                'namespace' => $this->namespace,
                'path' => self::ROUTES_FORT_PATH
            ]);
            $msg = 'Main Fortify routes mapped successfully';
            app()->runningInConsole()
                ? $output->writeln('<info> ' . $msg . ' </info> ')
                : $output->writeln($msg);
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'middleware' => MiddlewaresConstants::WEB ?? 'UNDEFINED',
                'namespace' => $this->namespace,
                'path' => self::ROUTES_FORT_PATH,
                'file_exists' => file_exists(base_path(self::ROUTES_FORT_PATH)),
                'file_readable' => is_readable(base_path(self::ROUTES_FORT_PATH)),
            ]);
            $msg = 'Failed to map main Fortify routes: ' . $e->getMessage();
            app()->runningInConsole()
                ? $output->writeln('<error> ' . $msg . ' </error> ')
                : $output->writeln('## FORTIFY ROUTES ERROR: ' . $msg);
        }
    }

    // ── Rate-limit constants ───────────────────────────────────────────────
    /** Login attempts per minute in production */
    private const LOGIN_RATE_LIMIT_PROD  = 5;
    /** Login attempts per minute in dev/local/testing (generous for e2e tests) */
    private const LOGIN_RATE_LIMIT_LOCAL = 60;

    protected function configureRateLimiting(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        $output = SafeConsoleOutput::make();
        $msg = 'Configuring Rate Limit for the main ' . class_basename(RouteServiceProvider::class) . '...';
        app()->runningInConsole()
            ? $output->writeln('<question> ' . $msg . ' </question> ')
            : $output->writeln($msg);
        try {
            // API rate limiter
            RateLimiter::for(
                self::API_RATE_LIMITER,
                fn(Request $request) =>
                Limit::perMinute(self::API_RATE_LIMIT)
                    ->by(optional($request->user())->id ?: $request->ip())
            );
            Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' set limiter', [
                'name'  => self::API_RATE_LIMITER,
                'limit' => self::API_RATE_LIMIT
            ]);
            $msg = 'Done configuring the rate limit for the ' . class_basename(RouteServiceProvider::class);
            app()->runningInConsole()
                ? $output->writeln('<info> ' . $msg . ' </info> ')
                : $output->writeln($msg);
        } catch (Throwable $e) {
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['message' => $e->getMessage()]);
            $msg = 'Failed to configure the rate limit for the main ' . class_basename(RouteServiceProvider::class);
            app()->runningInConsole()
                ? $output->writeln('<error> ' . $msg . ' </error> ')
                : $output->writeln('## PROVIDER ERROR: ' . $msg);
        }
    }
}
