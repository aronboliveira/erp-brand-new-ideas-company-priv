<?php

namespace App\Http\Middleware;

use Closure;
use App\Config\Constants\{DatabaseConstants, LangsConstants, SettingsConstants};
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class RevalidateBackHistory
{
    use MeasuresPerformance;

    /**
     * Apply CORS headers (or on error, redirect back safely).
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $class  = class_basename(static::class);
        $method = __FUNCTION__;
        $output = new ConsoleOutput();

        Log::debug("{$class}::{$method} start", [
            'ip'       => $request->ip(),
            'referrer' => $request->headers->get('referer', '#UNIDENTIFIED'),
            'uri'      => $request->getRequestUri(),
            'method'   => $request->getMethod(),
            'bearer'   => $request->bearerToken() ?? '#NO_TOKEN',
            'next'     => $this->searchForNext($request),
        ]);

        $output->writeln("[{$class}] Applying CORS headers to {$request->getRequestUri()}");
        try {
            $lang = Utility::fetchUserLang();
        } catch (\Throwable $e) {
            Log::warning("{$class}::{$method} failed to fetch user language", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $lang = DatabaseConstants::DEFAULT_LANG;
        }

        $msgs = !empty(LangsConstants::ERROR_MESSAGES[$lang]) ?
            LangsConstants::ERROR_MESSAGES[$lang] :
            LangsConstants::DEFAULT_CLIENT_MESSAGES;
        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            Log::error("{$class} downstream error in {$method}", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('error', !empty($msgs['internal_error']) ? $msgs['internal_error'] : 'An error occurred while processing your request.');
        }

        $headers = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With, Application',
        ];

        foreach ($headers as $key => $value) {
            try {
                $response->headers->set($key, $value);
            } catch (\Throwable $e) {
                Log::warning("{$class}::{$method} failed to set header", [
                    'header'    => $key,
                    'value'     => $value,
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                ]);
            }
        }

        Log::info("{$class}::{$method} CORS headers applied", [
            'uri'     => $request->getRequestUri(),
            'headers' => array_keys($headers),
            'status'  => $response->getStatusCode(),
            'next'    => $this->searchForNext($request),
        ]);

        $output->writeln("[{$class}] CORS headers set successfully");

        return $response;
    }
}
