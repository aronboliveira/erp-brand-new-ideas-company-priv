<?php

namespace App\Http\Middleware;
use App\Helpers\SafeConsoleOutput;

use Closure;
use App\Config\Constants\SettingsConstants;
use Illuminate\Http\{Middleware\TrustProxies as Middleware, Request};
use Illuminate\Support\Facades\Log;
use Symfony\Component\{
    HttpFoundation\Response,
    HttpFoundation\Exception\SuspiciousOperationException
};

final class TrustProxies extends Middleware
{
    use MeasuresPerformance;
    private const DEFAULT_HEADERS =
    Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /** @var array<int, string>|string|null */
    protected $proxies = [
        '127.0.0.1',
        '4.151.118.125',
        '192.168.0.233',
        '192.168.0.243',
        '192.168.0.254',
    ];

    /** @var int */
    protected $headers = self::DEFAULT_HEADERS;

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $method = __FUNCTION__;
        return $this->measure($request, function (Request $request) use ($next, $method) {
            $class  = class_basename(static::class);
            $output = SafeConsoleOutput::make();
            $ctx    = [
                'ip'        => $request->ip(),
                'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                'full-path' => $request->fullUrl(),
                'uri'       => $request->getRequestUri(),
                'method'    => $request->getMethod(),
                'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
                'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                'proxies'   => $this->proxies,
                'headers'   => $this?->headers,
                'params'    => $request->route()?->parameters() ?? [],
                'bearer_present' => (bool)$request->bearerToken(),
            ];
            Log::debug("{$class}::{$method} start", $ctx);
            $output->writeln("[{$class}] {$method}: Processing {$request->getMethod()} {$request->getRequestUri()}");
            $response = null;
            try {
                try {
                    $response = $next($request);
                } catch (\Throwable $e) {
                    $errCtx = ['exception' => get_class($e), 'message' => $e->getMessage()];
                    Log::error("{$class} encountered downstream error", $errCtx);
                    Log::channel(SettingsConstants::ERR_TRACE)->debug(
                        "{$class} encountered downstream error",
                        array_merge($errCtx, ['trace' => $e->getTraceAsString()])
                    );
                    throw $e;
                }
                Log::info("{$class}::{$method} succeeded", [
                    'uri' => $request->getRequestUri(),
                ]);
                Log::debug("{$class}::{$method} succeeded", [
                    'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                    'uri' => $request->getRequestUri(),
                    'status' => $response instanceof Response ? $response->getStatusCode() : 'n/a',
                    'next'   => $this->searchForNext($request)
                ] + $ctx);
                $output->writeln("[{$class}] {$method}: Proxies trusted successfully");
                return $response;
            } catch (SuspiciousOperationException $e) {
                Log::notice("{$class}::{$method} error", [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'uri' => $request->getRequestUri(),
                    'status' => $response?->getStatusCode() ?? 'n/a', // @phpstan-ignore-line
                    'host'  => $request->getHost()
                ] + $ctx);
                $output->writeln("[{$class}] {$method}: Suspicious host detected {$request->getHost()}, aborting");
                abort(403, 'Host not trusted.');
            } catch (\Throwable $e) {
                Log::error("{$class}::{$method} error", [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'uri' => $request->getRequestUri(),
                    'status' => $response?->getStatusCode() ?? 'n/a', // @phpstan-ignore-line
                    'host'  => $request->getHost()
                ] + $ctx);
                $msg = "[{$class}] {$method}: Error trusting proxies: {$e->getMessage()}";
                app()->runningInConsole() ? $output->writeln("<error> {$msg} </error>") : $output->writeln("## TRUST PROXIES ERROR: {$msg}");
                Log::debug("{$class} ingested a throwable. Aborting.");
                abort(500, 'CSRF verification failed');
            }
        }, $method);
    }
}
