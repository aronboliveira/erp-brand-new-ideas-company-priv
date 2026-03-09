<?php

declare(strict_types=1);

namespace Tests\Unit\app\Models\Traits;

use App\Traits\PlansWithSchedule;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use PHPUnit\Framework\TestCase;

#[Group('models-traits')]
class PlansWithScheduleTest extends TestCase
{
	private static function host(): object
	{
		return new class {
			use PlansWithSchedule {
				tryParseCarbon as public;
				buildTemporalBoundary as public;
				compareStartBoundary as public;
				compareEndBoundary as public;
				isTemporalType as public;
				readRowValue as public;
			}
		};
	}

	/* ══════════════ tryParseCarbon ══════════════ */

	#[Test]
	public function try_parse_carbon_valid_date(): void
	{
		$result = self::host()::tryParseCarbon('2025-06-15');
		$this->assertInstanceOf(Carbon::class, $result);
		$this->assertSame('2025-06-15', $result->toDateString());
	}

	#[Test]
	public function try_parse_carbon_valid_datetime(): void
	{
		$result = self::host()::tryParseCarbon('2025-06-15 14:30:00');
		$this->assertInstanceOf(Carbon::class, $result);
		$this->assertSame(14, $result->hour);
		$this->assertSame(30, $result->minute);
	}

	#[Test]
	public function try_parse_carbon_empty_returns_null(): void
	{
		$this->assertNull(self::host()::tryParseCarbon(''));
	}

	#[Test]
	public function try_parse_carbon_whitespace_returns_null(): void
	{
		$this->assertNull(self::host()::tryParseCarbon('   '));
	}

	#[Test]
	public function try_parse_carbon_invalid_returns_null(): void
	{
		$this->assertNull(self::host()::tryParseCarbon('not-a-date'));
	}

	/* ══════════════ isTemporalType ══════════════ */

	#[Test]
	#[DataProvider('temporalTypeProvider')]
	public function is_temporal_type_returns_expected(?string $type, bool $expected): void
	{
		$this->assertSame($expected, self::host()::isTemporalType($type));
	}

	public static function temporalTypeProvider(): array
	{
		return [
			'date' => ['date', true],
			'datetime' => ['datetime', true],
			'timestamp' => ['timestamp', true],
			'time' => ['time', true],
			'datetimetz' => ['datetimetz', true],
			'timetz' => ['timetz', true],
			'uppercase DATE' => ['DATE', true],
			'string' => ['string', false],
			'integer' => ['integer', false],
			'null' => [null, false],
			'empty' => ['', false],
		];
	}

	/* ══════════════ readRowValue ══════════════ */

	#[Test]
	public function read_row_value_existing_property(): void
	{
		$row = (object)['name' => 'Test'];
		$this->assertSame('Test', self::host()::readRowValue($row, 'name'));
	}

	#[Test]
	public function read_row_value_missing_property(): void
	{
		$row = (object)['name' => 'Test'];
		$this->assertNull(self::host()::readRowValue($row, 'missing'));
	}

	/* ══════════════ buildTemporalBoundary ══════════════ */

	#[Test]
	public function build_temporal_boundary_full_datetime(): void
	{
		$result = self::host()::buildTemporalBoundary(
			'2025-06-15 14:30:00',
			'datetime',
			null,
			null,
			start: true
		);
		$this->assertInstanceOf(Carbon::class, $result['dt']);
		$this->assertInstanceOf(Carbon::class, $result['date']);
		$this->assertIsInt($result['time_sec']);
		$this->assertSame(14 * 3600 + 30 * 60, $result['time_sec']);
	}

	#[Test]
	public function build_temporal_boundary_date_plus_time(): void
	{
		$result = self::host()::buildTemporalBoundary(
			'2025-06-15',
			'date',
			'08:00:00',
			'time',
			start: true
		);
		$this->assertInstanceOf(Carbon::class, $result['dt']);
		$this->assertSame(8, $result['dt']->hour);
		$this->assertSame(0, $result['dt']->minute);
	}

	#[Test]
	public function build_temporal_boundary_date_only(): void
	{
		$result = self::host()::buildTemporalBoundary(
			'2025-06-15',
			'date',
			null,
			null,
			start: true
		);
		$this->assertNull($result['dt']);
		$this->assertInstanceOf(Carbon::class, $result['date']);
		$this->assertNull($result['time_sec']);
	}

	#[Test]
	public function build_temporal_boundary_empty_returns_nulls(): void
	{
		$result = self::host()::buildTemporalBoundary('', null, '', null, start: true);
		$this->assertNull($result['dt']);
		$this->assertNull($result['date']);
		$this->assertNull($result['time_sec']);
	}

	/* ══════════════ compareStartBoundary ══════════════ */

	#[Test]
	public function compare_start_schedule_before_entity_returns_true(): void
	{
		$schedule = ['dt' => Carbon::parse('2025-06-01')];
		$entity = ['dt' => Carbon::parse('2025-06-15')];
		$this->assertTrue(self::host()::compareStartBoundary($schedule, $entity));
	}

	#[Test]
	public function compare_start_schedule_after_entity_returns_false(): void
	{
		$schedule = ['dt' => Carbon::parse('2025-06-15')];
		$entity = ['dt' => Carbon::parse('2025-06-01')];
		$this->assertFalse(self::host()::compareStartBoundary($schedule, $entity));
	}

	#[Test]
	public function compare_start_same_returns_false(): void
	{
		$dt = Carbon::parse('2025-06-15');
		$this->assertFalse(self::host()::compareStartBoundary(['dt' => $dt->copy()], ['dt' => $dt->copy()]));
	}

	#[Test]
	public function compare_start_fallback_to_date(): void
	{
		$schedule = ['dt' => null, 'date' => Carbon::parse('2025-06-01')];
		$entity = ['dt' => null, 'date' => Carbon::parse('2025-06-15')];
		$this->assertTrue(self::host()::compareStartBoundary($schedule, $entity));
	}

	#[Test]
	public function compare_start_fallback_to_time_sec(): void
	{
		$schedule = ['dt' => null, 'date' => null, 'time_sec' => 3600];
		$entity = ['dt' => null, 'date' => null, 'time_sec' => 7200];
		$this->assertTrue(self::host()::compareStartBoundary($schedule, $entity));
	}

	/* ══════════════ compareEndBoundary ══════════════ */

	#[Test]
	public function compare_end_schedule_after_entity_returns_true(): void
	{
		$schedule = ['dt' => Carbon::parse('2025-06-30')];
		$entity = ['dt' => Carbon::parse('2025-06-15')];
		$this->assertTrue(self::host()::compareEndBoundary($schedule, $entity));
	}

	#[Test]
	public function compare_end_schedule_before_entity_returns_false(): void
	{
		$schedule = ['dt' => Carbon::parse('2025-06-01')];
		$entity = ['dt' => Carbon::parse('2025-06-15')];
		$this->assertFalse(self::host()::compareEndBoundary($schedule, $entity));
	}

	#[Test]
	public function compare_end_empty_arrays_returns_false(): void
	{
		$this->assertFalse(self::host()::compareEndBoundary([], []));
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function try_parse_carbon_performance(): void
	{
		$host = self::host();
		$start = hrtime(true);
		for ($i = 0; $i < 5000; $i++) {
			$host::tryParseCarbon('2025-06-15 14:30:00');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(500, $elapsed, '5000 tryParseCarbon should be < 500ms');
	}

	#[Test]
	public function build_temporal_boundary_performance(): void
	{
		$host = self::host();
		$start = hrtime(true);
		for ($i = 0; $i < 2000; $i++) {
			$host::buildTemporalBoundary('2025-06-15', 'date', '14:30:00', 'time', start: true);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(500, $elapsed, '2000 buildTemporalBoundary should be < 500ms');
	}
}
