<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Session\TokenMismatchException;
use App\Helpers\SafeConsoleOutput;
use Symfony\Component\HttpFoundation\Response;

final class VerifyCsrfToken extends Middleware
{
    use MeasuresPerformance;
    private const EXEMPT_URIS = [
        'plan-pay-with-paymentwall/*',
        'invoice-pay-with-paymentwall/*',
    ];

    protected $except = self::EXEMPT_URIS;

    public function handle($request, Closure $next)
    {
        $output = SafeConsoleOutput::make();
        Log::debug('VerifyCsrfToken start', [
            'ip'           => $request->ip(),
            'method'       => $request->getMethod(),
            'uri'          => $request->getRequestUri(),
            'route'        => $request->route()?->getName() ?? '# UNIDENTIFIED',
            'action_method'=> $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
        ]);
        $output->writeln("[VerifyCsrfToken] Checking CSRF for {$request->getMethod()} {$request->getRequestUri()}");
        $response = null;
        try {
            $response = parent::handle($request, $next);
            $headers = $response->headers->all();
            Log::info('CSRF token validated successfully', [
                'uri' => $request->getRequestUri(),
            ]);
            Log::debug('CSRF token validated', [
                'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
                'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                'exempts' => json_encode(self::EXEMPT_URIS),
                'status' => '100',
                'next'   => $this->searchForNext($request),
            ]);
            $output->writeln('[VerifyCsrfToken] CSRF token valid');
            return $response;
        } catch (TokenMismatchException $e) {
            $headers = $response?->headers?->all() ?? ['FAILED' => 'Failed to parse cookies']; // @phpstan-ignore-line
            Log::warning('CSRF token mismatch', [
                'uri'    => $request->getRequestUri(),
                'method' => $request->getMethod(),
                'info'   => '419',
            ]);
            $output->writeln('[VerifyCsrfToken] CSRF token mismatch');
            if ($request->expectsJson())
                return response()->json(
                    ['error' => 'CSRF token mismatch'],
                    Response::HTTP_FORBIDDEN
                );
            abort(419, 'CSRF token mismatch');
        }
        # PULL REQUEST START — Remoção do catch genérico \Throwable que mascarava exceções downstream como falsos erros CSRF 500
        // catch (\Throwable $e) {
        //     $headers = $response?->headers?->all() ?? ['FAILED' => 'Failed to parse cookies'];
        //     Log::error('VerifyCsrfToken error', [
        //         'exception' => get_class($e),
        //         'message'   => $e->getMessage(),
        //         'uri'       => $request->getRequestUri(),
        //         'status'    => '403',
        //     ]);
        //     $msg = "[VerifyCsrfToken] Error: {$e->getMessage()}";
        //     app()->runningInConsole() ?
        //         $output->writeln('<error> ' . $msg . ' </error>') :
        //         $output->writeln("## CSRF ERROR: {$msg}");
        //     if ($request->expectsJson())
        //         return response()->json(
        //             ['error' => 'CSRF verification failed'],
        //             Response::HTTP_INTERNAL_SERVER_ERROR
        //         );
        //     Log::debug(get_class($this) . " ingested a throwable. Aborting.");
        //     abort(500, 'CSRF verification failed');
        // }
        # PULL REQUEST END
    }

    protected function tokensMatch($request): bool
    {
        $output = SafeConsoleOutput::make();
        Log::debug('VerifyCsrfToken::tokensMatch start', [
            'uri'    => $request->getRequestUri(),
            'method' => $request->getMethod(),
        ]);
        try {
            $matched = parent::tokensMatch($request);
            if ($matched) {
                Log::info('CSRF tokens match', ['uri' => $request->getRequestUri()]);
                $output->writeln('[VerifyCsrfToken] Tokens match');
            } else {
                Log::warning('CSRF tokens do not match', ['uri' => $request->getRequestUri()]);
                $output->writeln('[VerifyCsrfToken] Tokens do not match');
            }
            return $matched;
        } catch (\Throwable $e) {
            Log::error('VerifyCsrfToken::tokensMatch failed', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'uri'       => $request->getRequestUri(),
            ]);
            $msg = "[VerifyCsrfToken] Token check error: {$e->getMessage()}";
            app()->runningInConsole() ?
                $output->writeln('<error> ' . $msg . ' </error>') :
                $output->writeln("## CSRF TOKEN ERROR: {$msg}");
            return false;
        }
    }

    protected function parseCookies(?Response $response): array
    {
        try {
            if (!($response instanceof Response)) throw new \RuntimeException("Inadequate type for response: "
                . gettype($response));
            $cookies = $response->headers->getCookies();
            $cookieData = [];
            foreach ($cookies as $c)
                $cookieData[$c->getName()] = [
                    'value' => $c->getValue(),
                    'domain' => $c->getDomain(),
                    'path' => $c->getPath(),
                    'secure' => $c->isSecure(),
                    'httpOnly' => $c->isHttpOnly(),
                    'expires' => $c->getExpiresTime() ? date('Y-m-d H:i:s', $c->getExpiresTime()) : null,
                    'sameSite' => (method_exists($c, 'getSameSite') && $c->getSameSite())
                        ? $c->getSameSite() : null,
                ];
            return $cookieData;
        } catch (\Throwable) {
            return ['FAILED' => 'Failed to parse cookies'];
        }
    }
}
