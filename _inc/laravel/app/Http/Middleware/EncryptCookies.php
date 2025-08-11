<?php

namespace App\Http\Middleware;

use App\Config\Constants\SettingsConstants;
use Illuminate\{
    Cookie\Middleware\EncryptCookies as Middleware,
    Http\Request,
    Support\Facades\Log
};
use Symfony\Component\{HttpFoundation\Response, Console\Output\ConsoleOutput};

final class EncryptCookies extends Middleware
{
    use MeasuresPerformance;
    private const EXEMPT_COOKIES = [];

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = self::EXEMPT_COOKIES;

    /**
     * Encrypt cookies unless they are in the exempt list.
     *
     * @param  Request   $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $start = microtime(true);
        $output = new ConsoleOutput();
        $class = class_basename(static::class);
        $method = __METHOD__;
        Log::debug("{$class}::{$method} start", [
            'ip'        => $request->ip(),
            'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
            'uri'       => $request->getRequestUri(),
            'method'    => $request->getMethod(),
            'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
            'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
            'full_url'  => $request->fullUrl(),
            'params'    => $request->route()?->parameters() ?? [],
            'bearer'    => $request->bearerToken() ?? '# NO TOKEN',
        ]);
        $output->writeln("[{$class}] Processing cookies for {$request->getRequestUri()}");
        try {
            $response = parent::handle($request, $next);
            $headers = $response->headers->all();
            Log::info("{$class}::{$method} - Cookies encrypted successfully", [
                'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                'user'   => $request->user()?->id ?? 'guest',
                'email'  => $request->user()?->email ?? 'n/a',
                'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
                'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                'status' => $response instanceof Response ? $response->getStatusCode() : 'n/a',
                'common_headers' => array_filter(
                    $headers,
                    fn ($_, $key) => str_replace("_", "-", strtolower($key)) !== 'set-cookie',
                    ARRAY_FILTER_USE_BOTH
                ),
                'cookies_headers' => $response->headers->getCookies(),
                'next'   => $this->searchForNext($request)
            ]);
            $output->writeln("[{$class}] Cookies encrypted successfully");
            $this->logExecutionTime($start, "{$method}::success");
            return $response;
        } catch (\Throwable $e) {
            $errCtx = [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'uri'       => $request->getRequestUri(),
                'status'    => Response::HTTP_INTERNAL_SERVER_ERROR,
            ];
            Log::error("{$class}::{$method} failed", $errCtx);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("{$class}::{$method} failed", array_merge(
                $errCtx,
                ['trace' => $e->getTraceAsString()]
            ));
            $msg = "[{$class}] Encryption error: {$e->getMessage()}";
            app()->runningInConsole()
                ? $output->writeln("<error>{$msg}</error>")
                : $output->writeln("## ENCRYPTION ERROR: {$msg}");
            $this->logExecutionTime($start, "{$method}::exception");
            if ($request->expectsJson()) return response()->json(
                ['error' => 'Cookie encryption failed.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
            Log::debug("{$class} ingested a throwable. Throwing to upstream...");
            throw $e;
        }
    }
}
