<?php

namespace App\Http\Middleware;

use Closure;
use App\Config\Constants\SettingsConstants;
use Illuminate\Http\{Request, Response, Middleware\TrustHosts as Middleware};
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use App\Helpers\SafeConsoleOutput;

final class TrustHosts extends Middleware
{
    use MeasuresPerformance;
    /**
     * Handle an incoming request and configure trusted hosts.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $method = __FUNCTION__;
        return $this->measure($request, function ($request) use ($next, $method) {
            $class  = class_basename(static::class);
            $output = SafeConsoleOutput::make();
            $ctx    = [
                'ip'        => $request->ip(),
                'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                'method'    => $request->getMethod(),
                'full-path' => $request->fullUrl(),
                'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
                'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                'params'    => $request->route()?->parameters() ?? [],
                'bearer_present' => (bool)$request->bearerToken(),
            ];
            Log::debug("{$class}::{$method} start", $ctx);
            $output->writeln("[{$class}] Configuring trusted hosts for {$request->getRequestUri()}");
            try {
                $hosts = $this->hosts();
                Log::info("{$class}::hosts configured", ['hosts' => $hosts] + $ctx);
                $output->writeln("[{$class}] Allowed hosts: " . implode(', ', $hosts));
                $response = parent::handle($request, $next);
                if ($response->getStatusCode() >= 400) throw new \RuntimeException(
                    "Failed response status: {$response->getStatusCode()}"
                );
                Log::info("{$class}::{$method} succeeded", [
                    'uri' => $request->getRequestUri(),
                ]);
                Log::debug("{$class}::{$method} applied successfully", [
                    'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                    'uri' => $request->getRequestUri(),
                    'status' => $response->getStatusCode() ?? 'n/a',
                    'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
                    'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                    'next'   => $this->searchForNext($request)
                ] + $ctx);
                $output->writeln("[{$class}] Configuration applied");
                return $response;
            } catch (SuspiciousOperationException $e) {
                Log::warning("{$class}::{$method} blocked request", [
                    'host'      => $request->getHost(),
                    'uri'       => $request->getRequestUri(),
                    'ip'        => $request->ip(),
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                ]);
                $output->writeln("[{$class}] Suspicious host detected: {$request->getHost()}, aborting");
                abort(403, 'Host not trusted.');
            } catch (\Throwable $e) {
                $errCtx = ['exception' => get_class($e), 'message' => $e->getMessage(), 'uri' => $request->getRequestUri(), 'ip' => $request->ip()];
                Log::error("{$class}::{$method} unexpected error", $errCtx);
                Log::channel(SettingsConstants::ERR_TRACE)->debug(
                    "{$class}::{$method} unexpected error",
                    array_merge($errCtx, ['trace' => $e->getTraceAsString()])
                );
                $output->writeln("<error>[{$class}] Error: {$e->getMessage()}</error>");
                Log::debug("{$class} ingested a throwable. Aborting.");
                abort(500, 'Hosts verification failed');
            }
        }, $method);
    }

    /**
     * Determine the trusted host patterns.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $patterns = [
            $this->allSubdomainsOfApplicationUrl(),
            '127.0.0.1',
            'localhost',
            'prestech.com.br',
            'sistema.prestech.com.br',
            'prestek.inf.br',
        ];
        Log::debug('TrustHosts hosts()', ['hosts' => $patterns]);
        return $patterns;
    }
}
