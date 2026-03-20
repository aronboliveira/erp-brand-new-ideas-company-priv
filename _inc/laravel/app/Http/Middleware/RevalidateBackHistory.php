<?php

namespace App\Http\Middleware;

use Closure;
use App\Config\Constants\{DatabaseConstants, LangsConstants};
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class RevalidateBackHistory
{
    /**
     * Apply CORS headers (or on error, redirect back safely).
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        try {
            $lang = Utility::fetchUserLang();
        } catch (\Throwable $e) {
            $lang = DatabaseConstants::DEFAULT_LANG;
        }

        $msgs = !empty(LangsConstants::ERROR_MESSAGES[$lang]) ?
            LangsConstants::ERROR_MESSAGES[$lang] :
            LangsConstants::DEFAULT_CLIENT_MESSAGES;
        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            Log::error('RevalidateBackHistory: downstream error', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $referer = $request->headers->get('referer', '');
            $current = $request->fullUrl();
            $wouldLoop = empty($referer) || rtrim(strtok($referer, '?'), '/') === rtrim(strtok($current, '?'), '/');
            $target = $wouldLoop ? redirect('/') : redirect()->back();
            return $target->with('error', !empty($msgs['internal_error']) ? $msgs['internal_error'] : 'An error occurred while processing your request.');
        }

        $headers = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With, Application',
            'Cache-Control'                => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                       => 'no-cache',
            'Expires'                      => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ];

        foreach ($headers as $key => $value) {
            try {
                $response->headers->set($key, $value);
            } catch (\Throwable $e) {
                Log::warning('RevalidateBackHistory: failed to set header', [
                    'header'  => $key,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $response;
    }
}
