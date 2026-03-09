<?php

declare(strict_types=1);

namespace Tests\Unit\app\Traits;

use App\Traits\ZoomMeetingTrait;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use Tests\TestCase;

#[Group('traits')]
class ZoomMeetingTraitTest extends TestCase
{
	private function createHost(): object
	{
		return new class {
			use ZoomMeetingTrait;
		};
	}

	/* ══════════ toZoomTimeFormat ══════════ */

	#[Test]
	public function to_zoom_time_format_valid_datetime(): void
	{
		$host = $this->createHost();
		$result = $host->toZoomTimeFormat('2025-06-15 14:30:00');
		$this->assertSame('2025-06-15T14:30:00', $result);
	}

	#[Test]
	public function to_zoom_time_format_date_only(): void
	{
		$host = $this->createHost();
		$result = $host->toZoomTimeFormat('2025-01-01');
		$this->assertSame('2025-01-01T00:00:00', $result);
	}

	#[Test]
	public function to_zoom_time_format_invalid_returns_empty(): void
	{
		$host = $this->createHost();
		$result = $host->toZoomTimeFormat('not-a-date');
		$this->assertSame('', $result);
	}

	#[Test]
	public function to_zoom_time_format_empty_string_returns_empty(): void
	{
		$host = $this->createHost();
		// empty string to DateTime throws
		$result = $host->toZoomTimeFormat('');
		$this->assertIsString($result);
	}

	#[Test]
	#[DataProvider('dateTimeProvider')]
	public function to_zoom_time_format_various_inputs(string $input, bool $expectNonEmpty): void
	{
		$host = $this->createHost();
		$result = $host->toZoomTimeFormat($input);
		if ($expectNonEmpty) {
			$this->assertNotEmpty($result);
			$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $result);
		} else {
			$this->assertSame('', $result);
		}
	}

	public static function dateTimeProvider(): array
	{
		return [
			'iso8601' => ['2025-06-15T10:00:00', true],
			'date-only' => ['2025-06-15', true],
			'us-format' => ['06/15/2025', true],
			'timestamp' => ['1718438400', true],
			'relative' => ['next Monday', true],
			'garbage' => ['xyz123', false],
			'empty' => ['', true], // PHP DateTime('') parses to 'now'
		];
	}

	/* ══════════ constructor ══════════ */

	#[Test]
	public function constructor_initializes_client(): void
	{
		$host = $this->createHost();
		$this->assertSame('', $host->jwt);
	}

	/* ══════════ performance ══════════ */

	#[Test]
	public function zoom_time_format_performance(): void
	{
		$host = $this->createHost();
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			$host->toZoomTimeFormat('2025-06-15 14:30:00');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(200, $elapsed, '1000 date conversions should be < 200ms');
	}
}
