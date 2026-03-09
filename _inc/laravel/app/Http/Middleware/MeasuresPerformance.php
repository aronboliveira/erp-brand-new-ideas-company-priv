<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;
use App\Config\Constants\SettingsConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight performance-measurement trait for middleware.
 *
 * By default every method is a **no-op pass-through** to avoid runtime
 * overhead.  Middleware that genuinely needs profiling (e.g. Authenticate,
 * XSS) should define  `protected const PERF_ENABLED = true;`  in the
 * consuming class so that the full measurement logic is activated.
 */
trait MeasuresPerformance
{
	protected const NOTICE_THRESHOLD_MS   = 250;
	protected const WARNING_THRESHOLD_MS  = 1000;
	protected const CRITICAL_THRESHOLD_MS = 4000;

	/* ------------------------------------------------------------------
	 |  measure()  — wraps $next($request) with optional timing
	 | ----------------------------------------------------------------*/
	protected function measure(Request $request, Closure $next, string $resultKey = 'completed'): mixed
	{
		if (!$this->perfEnabled()) {
			return $next($request);
		}
		$start = microtime(true);
		try {
			$response = $next($request);
		} catch (Throwable $e) {
			Log::error(static::class . " encountered downstream error", [
				'exception' => get_class($e),
				'message'   => $e->getMessage(),
			]);
			throw $e;
		}
		$this->logExecutionTime($start, $resultKey);
		return $response;
	}

	/* ------------------------------------------------------------------
	 |  logExecutionTime()  — threshold-based log emission
	 | ----------------------------------------------------------------*/
	protected function logExecutionTime(float $startTime, string $result): void
	{
		if (!$this->perfEnabled()) {
			return;
		}
		$class         = static::class;
		$executionTime = round((microtime(true) - $startTime) * 1000, 2);
		$context = [
			'execution_time_ms' => $executionTime,
			'result'            => $result,
		];
		if ($executionTime > self::CRITICAL_THRESHOLD_MS) {
			Log::warning("{$class} extremely slow (>{$executionTime} ms)", $context);
		} elseif ($executionTime > self::WARNING_THRESHOLD_MS) {
			Log::notice("{$class} slow (>{$executionTime} ms)", $context);
		} elseif ($executionTime > self::NOTICE_THRESHOLD_MS) {
			Log::info("{$class} above expected (>{$executionTime} ms)", $context);
		}
	}

	/* ------------------------------------------------------------------
	 |  searchForNext()  — lightweight stub (removed heavy Kernel introspection)
	 | ----------------------------------------------------------------*/
	protected function searchForNext(Request $request): string
	{
		return '#DISABLED';
	}

	/* ------------------------------------------------------------------
	 |  perfEnabled()  — opt-in gate
	 | ----------------------------------------------------------------*/
	private function perfEnabled(): bool
	{
		/** @phpstan-ignore-next-line */
		return defined('static::PERF_ENABLED') && static::PERF_ENABLED === true;
	}
}
