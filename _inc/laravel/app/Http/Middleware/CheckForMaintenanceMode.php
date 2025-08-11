<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\{
    Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware,
    Http\Request,
    Support\Facades\Log
};
use Symfony\Component\{HttpFoundation\Response, Console\Output\ConsoleOutput};

final class CheckForMaintenanceMode extends Middleware
{
    use MeasuresPerformance;
    private const EXEMPT_ROUTES = [];
    /**
     * URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = self::EXEMPT_ROUTES;
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        $output = new ConsoleOutput();
        $base = class_basename(static::class);
        Log::debug("{$base}::" . __FUNCTION__ . " start", [
            'ip'     => $request->ip(),
            'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
            'method' => $request->getMethod(),
            'uri'    => $request->getRequestUri(),
            'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
            'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
        ]);
        $output->writeln("[{$base}] Checking maintenance mode for {$request->getMethod()} {$request->getRequestUri()}");
        try {
            $response = parent::handle($request, $next);
            $status = $response instanceof Response ? $response->getStatusCode() : 500;
            if ($response instanceof Response && $status === Response::HTTP_SERVICE_UNAVAILABLE) {
                Log::warning("{$base}::" . __FUNCTION__ . " maintenance_mode", [
                    'uri'    => $request->getRequestUri(),
                    'method' => $request->getMethod(),
                    'status' => $response->getStatusCode(),
                ]);
                $output->writeln("[{$base}] Service unavailable (503)");
                throw new \RuntimeException(
                    "App in Maintenace: {$response->getStatusCode()}"
                );
            } else if ($status >= 400) throw new \RuntimeException(
                "Failed response status: {$response->getStatusCode()}"
            );
            else {
                Log::info("{$base}::" . __FUNCTION__ . " check_passed", [
                    'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                    'uri'    => $request->getRequestUri(),
                    'method' => $request->getMethod(),
                    'status' => $response instanceof Response ? $response->getStatusCode() : 'n/a',
                    'next'   => $this->searchForNext($request)
                ]);
                $output->writeln("[{$base}] Maintenance check passed");
            }
            $this->logExecutionTime($start, __METHOD__ . '::success');
            return $response;
        } catch (\Throwable $e) {
            Log::error("{$base}::" . __FUNCTION__ . " failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'uri'       => $request->getRequestUri(),
                'status'    => Response::HTTP_SERVICE_UNAVAILABLE,
            ]);
            $output->writeln(app()->runningInConsole() ? "<error>[{$base}] Unexpected error: {$e->getMessage()}</error>" : "## MAINTENANCE ERROR: {$e->getMessage()}");
            if ($response?->getStatusCode() === Response::HTTP_SERVICE_UNAVAILABLE) {
                $response = response()->json(
                    ['error' => 'Application is under maintenance. Try again later.'],
                    Response::HTTP_SERVICE_UNAVAILABLE
                );
                $this->logExecutionTime($start, 'maintenance_check::blocked');
                return $response;
            }
            Log::debug("{$base} ingested a throwable. Throwing to upstream...");
            throw $e;
        }
    }
}
