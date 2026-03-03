<?php

namespace App\Http\Middleware;
use App\Helpers\SafeConsoleOutput;


use Closure;
use App\Config\Constants\SettingsConstants;
use Illuminate\{
    Foundation\Http\Middleware\TrimStrings as Middleware,
    Support\Facades\Log
};
use Symfony\Component\HttpFoundation\Response;

final class TrimStrings extends Middleware
{
    use MeasuresPerformance;
    private const EXEMPT_FIELDS = [
        'current_password',
        'password',
        'password_confirmation',
    ];
    protected $except = self::EXEMPT_FIELDS;

    /**
     * Handle an incoming request and trim string inputs,
     * except for fields defined in the exception list.
     *
     * @param  mixed    $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next): Response
    {
        $method = __FUNCTION__;
        return $this->measure($request, function ($request) use ($next, $method) {
            $class  = class_basename(static::class);
            $output = SafeConsoleOutput::make();
            Log::debug("{$class}::{$method} - Processing inputs", [
                'ip'        => $request->ip(),
                'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                'uri'       => $request->getRequestUri(),
                'method'    => $request->getMethod(),
                'route'     => $request->route()?->getName() ?? '# UNIDENTIFIED',
                'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                'full_url'  => $request->fullUrl(),
                'params'    => $request->route()?->parameters() ?? [],
                'bearer_present' => (bool)$request->bearerToken(),
            ]);
            $output->writeln("[{$class}] Trimming inputs for {$request->getRequestUri()}");
            try {
                $response = parent::handle($request, $next);
                Log::info("{$class}::{$method} succeeded", [
                    'uri' => $request->getRequestUri(),
                ]);
                Log::debug("{$class}::{$method} succeeded", [
                    'uri' => $request->getRequestUri(),
                    'method' => $request->getMethod(),
                    'status' => $response->getStatusCode() ?? 'n/a',
                    'next'   => $this->searchForNext($request)
                ]);
                $output->writeln("[{$class}] Input trimming complete");
                return $response;
            } catch (\Throwable $e) {
                $errCtx = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'uri' => $request->getRequestUri(),
                    'method' => $request->getMethod()
                ];
                Log::error("{$class}::{$method} failed", $errCtx);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("{$class}::{$method} failed", array_merge(
                    $errCtx,
                    ['trace' => $e->getTraceAsString()]
                ));
                $msg = "[{$class}] Error trimming inputs: {$e->getMessage()}";
                app()->runningInConsole()
                    ? $output->writeln("<error> {$msg} </error>")
                    : $output->writeln("## TRIM ERROR: {$msg}");
                if ($request->expectsJson()) return response()->json(
                    ['error' => 'Input processing failed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
                Log::debug("{$class} ingested a throwable. Throwing to upstream...");
                throw $e;
            }
        }, $method);
    }
}
