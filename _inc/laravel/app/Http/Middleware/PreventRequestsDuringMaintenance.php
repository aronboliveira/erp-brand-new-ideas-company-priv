<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\{
    Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware,
    Http\Request,
    Support\Facades\Log
};
use Symfony\Component\HttpFoundation\Response;

final class PreventRequestsDuringMaintenance extends Middleware
{
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
        try {
            $response = parent::handle($request, $next);
            $status = $response instanceof Response ? $response->getStatusCode() : 500;
            if ($response instanceof Response && $status === Response::HTTP_SERVICE_UNAVAILABLE) {
                Log::warning('PreventRequestsDuringMaintenance: 503', ['uri' => $request->getRequestUri()]);
                return response()->json(
                    ['error' => 'Service temporarily unavailable.'],
                    Response::HTTP_SERVICE_UNAVAILABLE
                );
            }
            return $response;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === Response::HTTP_SERVICE_UNAVAILABLE) {
                Log::warning('PreventRequestsDuringMaintenance: 503 (exception)', ['uri' => $request->getRequestUri()]);
                return response()->json(
                    ['error' => 'Service temporarily unavailable.'],
                    Response::HTTP_SERVICE_UNAVAILABLE
                );
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('PreventRequestsDuringMaintenance: blocked', [
                'message' => $e->getMessage(),
                'uri'     => $request->getRequestUri(),
            ]);
            throw $e;
        }
    }
}
