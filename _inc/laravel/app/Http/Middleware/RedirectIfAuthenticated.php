<?php

namespace App\Http\Middleware;

use Closure;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class RedirectIfAuthenticated
{
    use MeasuresPerformance;
    private const REDIRECT_ROUTE = '/login';

    /**
     * Handle an incoming request.
     *
     * Only redirect authenticated users away from the login page on GET.
     * Validate trusted host and referer; on failure, redirect to login.
     *
     * @param  Request   $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $method = __METHOD__;
        return $this->measure($request, function (Request $request) use ($next, $guards, $method) {
            $class  = class_basename(static::class);
            $output = new ConsoleOutput();
            try {
                if (!($request->isMethod('get') && $request->routeIs('login'))) {
                    Log::info("{$class}::{$method} not suitable for the route + method. Skipping login guard checks.", [
                        'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                        'uri' => $request->getRequestUri(),
                        'method' => $request->getMethod(),
                        'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
                        'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                        'next'   => $this->searchForNext($request),
                        'bearer'    => $request->bearerToken(),
                    ]);
                    $output->writeln("[{$class}] Skipping guard checks");
                    return $next($request);
                }
                Log::debug("{$class}::{$method} start", [
                    'ip'     => $request->ip(),
                    'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                    'uri'    => $request->getRequestUri(),
                    'method' => $request->getMethod(),
                    'bearer'    => $request->bearerToken(),
                    'referrer' => $request->header('Referer'),
                ]);
                $output->writeln("[{$class}] Checking host and referer for {$request->getRequestUri()}");
                $appHost       = parse_url(config('app.url'), PHP_URL_HOST);
                $host          = $request->getHost();
                $patterns      = ['127.0.0.1', 'localhost', 'prestech.com.br', 'sistema.prestech.com.br', 'prestek.inf.br'];
                if ($appHost)
                    $patterns[] = '^(.+\.)?' . preg_quote($appHost) . '$';
                $allowed = false;
                foreach ($patterns as $pattern) {
                    if (Str::is($pattern, $host)) {
                        $allowed = true;
                        break;
                    }
                }
                if (!$allowed) {
                    Log::warning("{$class}::{$method} untrusted host detected", ['host' => $host]);
                    $output->writeln("[{$class}] Untrusted host, redirecting to login");
                    return $next($request);
                }
                $referer = $request->header('Referer');
                if ($referer) {
                    $refHost = parse_url($referer, PHP_URL_HOST);
                    $allowed = false;
                    foreach ($patterns as $pattern)
                        if (Str::is($pattern, $refHost)) {
                            $allowed = true;
                            break;
                        }
                    if (!$allowed) {
                        Log::warning("{$class}::{$method} invalid referer detected", ['referer' => $referer]);
                        $output->writeln("[{$class}] Invalid referer, redirecting to login");
                        return $next($request);
                    }
                }
                $guards  = empty($guards) ? [null] : $guards;
                $checked = true;
                foreach ($guards as $guard) {
                    if (Auth::guard($guard)->check())
                        Log::debug("{$class}::{$method} user authenticated by guard", ['guard' => $guard]);
                    else {
                        Log::debug("{$class}::{$method} no user for guard", ['guard' => $guard]);
                        $checked = false;
                        break;
                    }
                }
                if ($checked) {
                    Log::info("{$class}::{$method} redirecting authenticated user", ['uri' => $request->getRequestUri()]);
                    $output->writeln("[{$class}] Redirecting authenticated user");
                    return redirect('/');
                }
                Log::debug("{$class}::{$method} no authenticated user, proceeding", [
                    'uri' => $request->getRequestUri(),
                    'next'   => $this->searchForNext($request)
                ]);
                $output->writeln("[{$class}] Proceeding to login");
                return $next($request);
            } catch (\Throwable $e) {
                Log::error("{$class}::{$method} failed", [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'uri'       => $request->getRequestUri(),
                    'method'    => $request->getMethod(),
                ]);
                $msg = "[{$class}] Error processing request: {$e->getMessage()}";
                app()->runningInConsole() ? $output->writeln($msg) : abort(500, $msg);
                Log::debug("{$class} ingested a throwable. Throwing to upstream...");
                throw $e;
            };
        });
    }
}
