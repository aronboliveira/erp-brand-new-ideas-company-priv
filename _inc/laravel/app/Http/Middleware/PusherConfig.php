<?php

namespace App\Http\Middleware;

use Closure;
use App\Config\Constants\SettingsConstants;
use App\Models\Utility;
use Illuminate\{Http\Request, Support\Facades\Log};
use Symfony\Component\{HttpFoundation\Response, Console\Output\ConsoleOutput};

final class PusherConfig
{
    use MeasuresPerformance;
    /**
     * Apply dynamic Pusher configuration based on stored settings.
     *
     * @param  Request   $request
     * @param  \Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $output = new ConsoleOutput();
        $class = class_basename(static::class);
        $method = __METHOD__;
        $ctx = [
            'ip'        => $request->ip(),
            'method'    => $request->getMethod(),
            'full-path' => $request->fullUrl(),
            'params'    => $request->route()?->parameters() ?? [],
            'referrer'  => $request->header('Referer') ?? '# UNIDENTIFIED',
            'bearer'    => $request->bearerToken() ?? '# NO TOKEN',
            'next'   => $this->searchForNext($request)
        ];
        Log::debug("{$class}::{$method} start", $ctx);
        $output->writeln("[$class] Loading Pusher settings for {$request->getRequestUri()}");
        try {
            $settings = Utility::settingsById(1);
            if (is_array($settings) && $settings) {
                foreach ([
                    'chatify.pusher.key'             => 'pusher_app_key',
                    'chatify.pusher.secret'          => 'pusher_app_secret',
                    'chatify.pusher.app_id'          => 'pusher_app_id',
                    'chatify.pusher.options.cluster' => 'pusher_app_cluster',
                ] as $configKey => $settingKey) {
                    $value = $settings[$settingKey] ?? null;
                    config([$configKey => $value]);
                    Log::debug("{$class}::{$method} set config", [
                        'config_key' => $configKey,
                        'value'      => $value,
                    ]);
                }
                Log::info("{$class}::{$method} settings applied", [
                    'uri'      => $request->getRequestUri(),
                    'settings' => $settings,
                ]);
                $output->writeln("[$class] Pusher settings applied successfully");
            } else {
                Log::info("{$class}::{$method} no settings found", [
                    'uri' => $request->getRequestUri(),
                ]);
                $output->writeln("[$class] No Pusher settings to apply");
            }
            try {
                $response = $next($request);
            } catch (\Throwable $e) {
                $errCtx = [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'uri'       => $request->getRequestUri(),
                ];
                Log::error(get_class($this) . " encountered downstream error", $errCtx);
                Log::channel(SettingsConstants::ERR_TRACE)->debug(
                    get_class($this) . " encountered downstream error",
                    array_merge($errCtx, ['trace' => $e->getTraceAsString()])
                );
                throw $e;
            }
            $this->logExecutionTime($start, "{$class}::{$method}");
            return $response;
        } catch (\Throwable $e) {
            $errCtx = [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'uri'       => $request->getRequestUri(),
            ];
            Log::warning("{$class}::{$method} failed to load settings", $errCtx);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(
                "{$class}::{$method} failed to load settings",
                array_merge($errCtx, ['trace' => $e->getTraceAsString()])
            );
            $msg = "[$class] Error loading settings: {$e->getMessage()}";
            app()->runningInConsole() ? $output->writeln("<error> {$msg} </error>") : $output->writeln("## PUSHER ERROR: {$msg}");
            Log::debug("{$class} ingested a throwable. Throwing to upstream...");
            throw $e;
        }
    }
}
