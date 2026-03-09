<?php

declare(strict_types=1);

namespace Tests\Unit\app\Models\Traits;

use App\Traits\TracksFailures;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use PHPUnit\Framework\TestCase;

#[Group('models-traits')]
class TracksFailuresTest extends TestCase
{
	private static function host(): object
	{
		return new class {
			use TracksFailures {
				normalizeErrorLog as public;
			}
		};
	}

	/* ══════════════ normalizeErrorLog ══════════════ */

	#[Test]
	public function normalize_error_log_null_returns_null(): void
	{
		$this->assertNull(self::host()::normalizeErrorLog(null));
	}

	#[Test]
	public function normalize_error_log_empty_string_returns_null(): void
	{
		$this->assertNull(self::host()::normalizeErrorLog(''));
	}

	#[Test]
	public function normalize_error_log_whitespace_returns_null(): void
	{
		$this->assertNull(self::host()::normalizeErrorLog('   '));
	}

	#[Test]
	public function normalize_error_log_empty_array_returns_null(): void
	{
		$this->assertNull(self::host()::normalizeErrorLog([]));
	}

	#[Test]
	public function normalize_error_log_populated_array_returns_as_is(): void
	{
		$input = ['error' => 'something', 'code' => 500];
		$this->assertSame($input, self::host()::normalizeErrorLog($input));
	}

	#[Test]
	public function normalize_error_log_valid_json_string(): void
	{
		$this->assertSame(
			['error' => 'oops'],
			self::host()::normalizeErrorLog('{"error":"oops"}')
		);
	}

	#[Test]
	public function normalize_error_log_invalid_json_wraps_in_message(): void
	{
		$result = self::host()::normalizeErrorLog('not json');
		$this->assertSame(['message' => 'not json'], $result);
	}

	#[Test]
	public function normalize_error_log_object_cast_to_array(): void
	{
		$obj = (object)['key' => 'val'];
		$result = self::host()::normalizeErrorLog($obj);
		$this->assertSame(['key' => 'val'], $result);
	}

	#[Test]
	public function normalize_error_log_scalar_wraps_in_message(): void
	{
		$this->assertSame(['message' => '42'], self::host()::normalizeErrorLog(42));
		$this->assertSame(['message' => '1'], self::host()::normalizeErrorLog(true));
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function normalize_error_log_performance(): void
	{
		$host = self::host();
		$start = hrtime(true);
		for ($i = 0; $i < 5000; $i++) {
			$host::normalizeErrorLog('{"error":"test","code":500}');
			$host::normalizeErrorLog(null);
			$host::normalizeErrorLog(['err' => 'x']);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(300, $elapsed, '15000 normalizeErrorLog should be < 300ms');
	}
}
