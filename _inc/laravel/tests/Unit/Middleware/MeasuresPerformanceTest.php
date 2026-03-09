<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\MeasuresPerformance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Concrete class that uses the MeasuresPerformance trait for testing purposes.
 */
class MeasuresPerformanceStub
{
	use MeasuresPerformance;

	public function callMeasure(Request $request, Closure $next, string $key = 'completed'): mixed
	{
		return $this->measure($request, $next, $key);
	}

	public function callLogExecutionTime(float $startTime, string $result): void
	{
		$this->logExecutionTime($startTime, $result);
	}

	public function callSearchForNext(Request $request): string
	{
		return $this->searchForNext($request);
	}

	public function getNoticeThreshold(): int
	{
		return self::NOTICE_THRESHOLD_MS;
	}

	public function getWarningThreshold(): int
	{
		return self::WARNING_THRESHOLD_MS;
	}

	public function getCriticalThreshold(): int
	{
		return self::CRITICAL_THRESHOLD_MS;
	}
}

#[Group('middleware')]
#[Group('measures-performance')]
class MeasuresPerformanceTest extends TestCase
{
	private MeasuresPerformanceStub $stub;

	protected function setUp(): void
	{
		parent::setUp();
		$this->stub = new MeasuresPerformanceStub();
	}

	// ───────── Threshold constants ─────────

	#[Test]
	public function notice_threshold_is_250ms(): void
	{
		$this->assertSame(250, $this->stub->getNoticeThreshold());
	}

	#[Test]
	public function warning_threshold_is_1000ms(): void
	{
		$this->assertSame(1000, $this->stub->getWarningThreshold());
	}

	#[Test]
	public function critical_threshold_is_4000ms(): void
	{
		$this->assertSame(4000, $this->stub->getCriticalThreshold());
	}

	#[Test]
	public function thresholds_are_ordered_ascending(): void
	{
		$this->assertLessThan(
			$this->stub->getWarningThreshold(),
			$this->stub->getNoticeThreshold()
		);
		$this->assertLessThan(
			$this->stub->getCriticalThreshold(),
			$this->stub->getWarningThreshold()
		);
	}

	// ───────── measure() ─────────

	#[Test]
	public function measure_returns_response_from_next(): void
	{
		$request  = Request::create('/test', 'GET');
		$expected = new Response('ok', 200);

		$result = $this->stub->callMeasure($request, fn() => $expected);

		$this->assertSame($expected, $result);
	}

	#[Test]
	public function measure_preserves_response_status(): void
	{
		$request  = Request::create('/test', 'GET');
		$expected = new Response('created', 201);

		$result = $this->stub->callMeasure($request, fn() => $expected, 'test_key');

		$this->assertSame(201, $result->getStatusCode());
	}

	#[Test]
	public function measure_rethrows_downstream_exceptions(): void
	{
		$request = Request::create('/test', 'GET');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Downstream failure');

		$this->stub->callMeasure($request, function () {
			throw new \RuntimeException('Downstream failure');
		});
	}

	#[Test]
	public function measure_accepts_custom_result_key(): void
	{
		$request  = Request::create('/test', 'GET');
		$expected = new Response('ok', 200);

		// Should not throw — key is just a label for logging
		$result = $this->stub->callMeasure($request, fn() => $expected, 'custom_operation');

		$this->assertSame($expected, $result);
	}

	// ───────── logExecutionTime() ─────────

	#[Test]
	public function logExecutionTime_does_not_throw_with_recent_start(): void
	{
		$start = microtime(true);

		// Should complete without exceptions
		$this->stub->callLogExecutionTime($start, 'test_result');

		$this->assertTrue(true, 'logExecutionTime completed without throwing');
	}

	#[Test]
	public function logExecutionTime_handles_zero_start_time(): void
	{
		// Start time of 0 means huge elapsed time → triggers critical log
		$this->stub->callLogExecutionTime(0.0, 'ancient_start');

		$this->assertTrue(true, 'logExecutionTime handled zero start time without throwing');
	}

	// ───────── searchForNext() ─────────

	#[Test]
	public function searchForNext_returns_string_for_routeless_request(): void
	{
		$request = Request::create('/no-route', 'GET');

		$result = $this->stub->callSearchForNext($request);

		$this->assertIsString($result);
		// Without a route, it'll hit the catch and return an error string
		$this->assertNotEmpty($result);
	}

	// ───────── performance ─────────

	#[Test]
	public function measure_overhead_is_minimal(): void
	{
		$request  = Request::create('/perf', 'GET');
		$expected = new Response('ok', 200);

		$iterations = 100;
		$start      = hrtime(true);

		for ($i = 0; $i < $iterations; $i++) {
			$this->stub->callMeasure($request, fn() => $expected);
		}

		$elapsed = (hrtime(true) - $start) / 1e6;
		$avg     = $elapsed / $iterations;

		$this->assertLessThan(10, $avg, "Average measure() overhead should be under 10ms, was {$avg}ms");
	}

	#[Test]
	public function logExecutionTime_performance(): void
	{
		$start = microtime(true);
		$iterations = 500;

		$begin = hrtime(true);
		for ($i = 0; $i < $iterations; $i++) {
			$this->stub->callLogExecutionTime($start, 'perf_test');
		}
		$elapsed = (hrtime(true) - $begin) / 1e6;
		$avg     = $elapsed / $iterations;

		$this->assertLessThan(5, $avg, "Average logExecutionTime() overhead should be under 5ms, was {$avg}ms");
	}
}
