<?php

namespace App\Http\Middleware;

use Closure;
use App\Config\Constants\SettingsConstants;
use App\Models\Utility;
use Illuminate\{Http\Request, Support\Facades\Log};
use Symfony\Component\HttpFoundation\Response;

final class PusherConfig
{
    /**
     * Apply dynamic Pusher configuration based on stored settings.
     *
     * @param  Request   $request
     * @param  \Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $settings = Utility::settingsById(1);
            if (is_array($settings) && $settings) {
                foreach (
                    [
                        'chatify.pusher.key'             => 'pusher_app_key',
                        'chatify.pusher.secret'          => 'pusher_app_secret',
                        'chatify.pusher.app_id'          => 'pusher_app_id',
                        'chatify.pusher.options.cluster' => 'pusher_app_cluster',
                    ] as $configKey => $settingKey
                ) {
                    config([$configKey => $settings[$settingKey] ?? null]);
                }
            }
            return $next($request);
        } catch (\Throwable $e) {
            Log::warning('PusherConfig: failed to load settings', [
                'message' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug('PusherConfig trace', [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
