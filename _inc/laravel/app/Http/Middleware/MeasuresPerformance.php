<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;
use App\Config\Constants\SettingsConstants;
use App\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\{Collection, Str};

trait MeasuresPerformance
{
	protected const NOTICE_THRESHOLD_MS   = 250;
	protected const WARNING_THRESHOLD_MS  = 1000;
	protected const CRITICAL_THRESHOLD_MS = 4000;

	protected function measure(Request $request, Closure $next, string $resultKey = 'completed'): mixed
	{
		try {
			$start   = microtime(true);
			try {
				$response = $next($request);
			} catch (\Throwable $e) {
				$errCtx = [
					'exception' => get_class($e),
					'message'   => $e->getMessage(),
				];
				Log::error(get_class($this) . " encountered downstream error", $errCtx);
				Log::channel(SettingsConstants::ERR_TRACE)->debug(
					get_class($this) . " encountered downstream error",
					array_merge($errCtx, ['trace' => $e->getTraceAsString()])
				);
				throw $e;
			}
			$this->logExecutionTime($start, $resultKey);
			return $response;
		} catch (Throwable $e) {
			$errCtx = [
				'exception' => get_class($e),
				'message'   => $e->getMessage(),
			];
			Log::error("Failed to measure performance for " . get_class($this), $errCtx);
			Log::channel(SettingsConstants::ERR_TRACE)->debug(
				"Failed to measure performance for " . get_class($this),
				array_merge($errCtx, ['trace' => $e->getTraceAsString()])
			);
			throw $e;
		}
	}

	protected function logExecutionTime(float $startTime, string $result): void
	{
		try {
			$class         = get_class($this);
			$executionTime = round((microtime(true) - $startTime) * 1000, 2);
			$memoryNow     = memory_get_usage();
			$memoryPeak    = memory_get_peak_usage();
			$context = [
				'execution_time_ms'       => $executionTime,
				'memory_usage_bytes'      => $memoryNow,
				'memory_peak_usage_bytes' => $memoryPeak,
				'result'                  => $result,
				'thresholds_ms'           => [
					'notice'   => self::NOTICE_THRESHOLD_MS,
					'warning'  => self::WARNING_THRESHOLD_MS,
					'critical' => self::CRITICAL_THRESHOLD_MS,
				],
			];
			Log::debug("{$class} middleware executed", $context);
			if ($executionTime <= self::NOTICE_THRESHOLD_MS) return;
			if ($executionTime <= self::WARNING_THRESHOLD_MS) {
				Log::info("{$class} execution above expected (> {$context['thresholds_ms']['notice']} ms)", $context);
				return;
			}
			if ($executionTime <= self::CRITICAL_THRESHOLD_MS) {
				Log::notice("{$class} execution slow (> {$context['thresholds_ms']['warning']} ms)", $context);
				return;
			}
			Log::warning("{$class} execution extremely slow (> {$context['thresholds_ms']['critical']} ms) — immediate action recommended", $context);
		} catch (Throwable $e) {
			$errCtx = [
				'exception' => get_class($e),
				'message'   => $e->getMessage(),
			];
			Log::notice("Failed to log performance for {$class}", $errCtx);
			Log::channel(SettingsConstants::ERR_TRACE)->debug("Failed to log performance for {$class}", array_merge(
				$errCtx,
				['trace' => $e->getTraceAsString()]
			));
		}
	}

	protected function searchForNext(Request $request): string
	{
		try {
			$kernel = app(HttpKernel::class);
			$globalStack = $kernel->getMiddleware();
			$groupMap = $kernel->getMiddlewareGroups();
			$routeMw = $kernel->getRouteMiddleware();
			$stack = Collection::make($request->route()->action['middleware'] ?? [])
				?->flatMap(function (string $mw) use ($groupMap, $routeMw) {
					[$name] = explode(':', $mw, 2);
					if (isset($groupMap[$name])) return $groupMap[$name];
					if (isset($routeMw[$name])) return $routeMw[$name];
					return [$name];
				})->all() ?? [];
			$query = array_search(get_class($this), array_merge($globalStack, $stack), true);
			return match (true) {
				is_int($query) && isset($stack[$query + 1]) => $stack[$query + 1],
				is_int($query)                     => '#NO_FOLLOWER_IN_STACK',
				default                            => '#NOT_FOUND_IN_STACK',
			};
		} catch (Throwable $e) {
			Log::notice("Failed to search for next middleware in stack for " . get_class($this), [
				'class'	=> get_class($e),
				'error' => $e->getMessage(),
			]);
			return '#ERROR_SEARCHING_FOR_NEXT';
		}
	}
}
