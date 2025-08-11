<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\{
    Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware,
    Http\Request,
    Support\Facades\Log
};
use Symfony\Component\{HttpFoundation\Response, Console\Output\ConsoleOutput};

final class PreventRequestsDuringMaintenance extends Middleware
{
    use MeasuresPerformance;
    private const EXEMPT_URIS = [];

    /**
     * URIs that should remain accessible while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = self::EXEMPT_URIS;

    /**
     * Handle an incoming request while the app is in maintenance mode.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        $output = new ConsoleOutput();
        $class = class_basename(static::class);
        $method = __METHOD__;
        $ctx = [
            'ip'     => $request->ip(),
            'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
            'uri'    => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
            'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
        ];
        Log::debug("{$class}::{$method} start", $ctx);
        $output->writeln("[$class] Incoming {$ctx['method']} {$ctx['uri']}");
        try {
            $response = parent::handle($request, $next);
            $status = $response instanceof Response ? $response->getStatusCode() : 500;
            if ($response instanceof Response && $status === Response::HTTP_SERVICE_UNAVAILABLE) {
                Log::warning("{$class}::" . __FUNCTION__ . " maintenance_mode", [
                    'uri'    => $request->getRequestUri(),
                    'method' => $request->getMethod(),
                    'status' => $response->getStatusCode(),
                ]);
                $output->writeln("[{$class}] Service unavailable (503)");
                throw new \RuntimeException(
                    "App in Maintenace: {$response->getStatusCode()}"
                );
            }
            Log::info("{$class}::{$method} passed maintenance check", [
                'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                'uri'    => $ctx['uri'],
                'status' => $response instanceof Response ? $response->getStatusCode() : 'n/a',
                'next'   => $this->searchForNext($request)
            ]);
            $output->writeln("[$class] Request allowed");
            $this->logExecutionTime($start, 'maintenance_check::success');
            return $response;
        } catch (\Throwable $e) {
            Log::warning("{$class}::{$method} blocked request", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'uri'       => $ctx['uri'],
                'status'    => Response::HTTP_SERVICE_UNAVAILABLE,
            ]);
            $msg = "[$class] Blocked: {$e->getMessage()}";
            app()->runningInConsole()
                ? $output->writeln("<error> {$msg} </error>")
                : $output->writeln("## MAINTENANCE BLOCKED: {$msg}");
            if ($response?->getStatusCode() === Response::HTTP_SERVICE_UNAVAILABLE) {
                $response = response()->json(
                    ['error' => 'Application is under maintenance. Try again later.'],
                    Response::HTTP_SERVICE_UNAVAILABLE
                );
                $this->logExecutionTime($start, 'maintenance_check::blocked');
                return $response;
            }
            Log::debug("{$class} ingested a throwable. Throwing to upstream...");
            throw $e;
        }
    }
}
