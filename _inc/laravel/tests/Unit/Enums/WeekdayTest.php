<?php

namespace Tests\Unit\Enums;

use App\Enums\Weekday;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Weekday::class)]
#[Group('enums')]
#[Group('weekday')]
class WeekdayTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_exactly_seven_cases(): void
	{
		$this->assertCount(7, Weekday::cases());
	}

	#[Test]
	public function each_case_has_expected_string_value(): void
	{
		$expected = [
			'Monday'    => 'monday',
			'Tuesday'   => 'tuesday',
			'Wednesday' => 'wednesday',
			'Thursday'  => 'thursday',
			'Friday'    => 'friday',
			'Saturday'  => 'saturday',
			'Sunday'    => 'sunday',
		];
		foreach (Weekday::cases() as $case) {
			$this->assertSame($expected[$case->name], $case->value, "Case {$case->name} mismatch");
		}
	}

	// ───────── values() ─────────

	#[Test]
	public function values_returns_seven_strings(): void
	{
		$values = Weekday::values();
		$this->assertCount(7, $values);
		$this->assertSame('monday', $values[0]);
		$this->assertSame('sunday', $values[6]);
	}

	// ───────── ordered() ─────────

	#[Test]
	public function ordered_starting_with_monday(): void
	{
		$days = Weekday::ordered(true);
		$this->assertCount(7, $days);
		$this->assertSame(Weekday::Monday, $days[0]);
		$this->assertSame(Weekday::Sunday, $days[6]);
	}

	#[Test]
	public function ordered_starting_with_sunday(): void
	{
		$days = Weekday::ordered(false);
		$this->assertCount(7, $days);
		$this->assertSame(Weekday::Sunday, $days[0]);
		$this->assertSame(Weekday::Saturday, $days[6]);
	}

	#[Test]
	public function ordered_default_starts_with_monday(): void
	{
		$days = Weekday::ordered();
		$this->assertSame(Weekday::Monday, $days[0]);
	}

	#[Test]
	public function both_orderings_contain_all_days(): void
	{
		$mondayStart = Weekday::ordered(true);
		$sundayStart = Weekday::ordered(false);
		$this->assertEqualsCanonicalizing($mondayStart, $sundayStart);
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (Weekday::cases() as $case) {
			$this->assertSame($case, Weekday::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_null_for_null(): void
	{
		$this->assertNull(Weekday::normalize(null));
	}

	#[Test]
	public function normalize_returns_null_for_empty_string(): void
	{
		$this->assertNull(Weekday::normalize(''));
		$this->assertNull(Weekday::normalize('   '));
	}

	#[Test]
	public function normalize_returns_null_for_unknown(): void
	{
		$this->assertNull(Weekday::normalize('garbage_day'));
	}

	public static function normalizeEnglishProvider(): array
	{
		return [
			['monday', Weekday::Monday],
			['tuesday', Weekday::Tuesday],
			['wednesday', Weekday::Wednesday],
			['thursday', Weekday::Thursday],
			['friday', Weekday::Friday],
			['saturday', Weekday::Saturday],
			['sunday', Weekday::Sunday],
			// Abbreviations
			['mon', Weekday::Monday],
			['tue', Weekday::Tuesday],
			['wed', Weekday::Wednesday],
			['thu', Weekday::Thursday],
			['fri', Weekday::Friday],
			['sat', Weekday::Saturday],
			['sun', Weekday::Sunday],
			// Extra abbreviations
			['tues', Weekday::Tuesday],
			['thur', Weekday::Thursday],
			['thurs', Weekday::Thursday],
		];
	}

	#[Test]
	#[DataProvider('normalizeEnglishProvider')]
	public function normalize_english(string $input, Weekday $expected): void
	{
		$this->assertSame($expected, Weekday::normalize($input));
	}

	public static function normalizeNumericProvider(): array
	{
		return [
			['1', Weekday::Monday],
			['2', Weekday::Tuesday],
			['3', Weekday::Wednesday],
			['4', Weekday::Thursday],
			['5', Weekday::Friday],
			['6', Weekday::Saturday],
			['7', Weekday::Sunday],
			['01', Weekday::Monday],
			['07', Weekday::Sunday],
		];
	}

	#[Test]
	#[DataProvider('normalizeNumericProvider')]
	public function normalize_numeric(string $input, Weekday $expected): void
	{
		$this->assertSame($expected, Weekday::normalize($input));
	}

	public static function normalizeMultilingualProvider(): array
	{
		return [
			// Portuguese
			['segunda', Weekday::Monday],
			['segunda-feira', Weekday::Monday],
			['terça', Weekday::Tuesday],
			['terca', Weekday::Tuesday],
			['quarta', Weekday::Wednesday],
			['quinta', Weekday::Thursday],
			['sexta', Weekday::Friday],
			['sábado', Weekday::Saturday],
			['sabado', Weekday::Saturday],
			['domingo', Weekday::Sunday],
			// Spanish
			['lunes', Weekday::Monday],
			['martes', Weekday::Tuesday],
			['miércoles', Weekday::Wednesday],
			['miercoles', Weekday::Wednesday],
			['jueves', Weekday::Thursday],
			['viernes', Weekday::Friday],
			// French
			['lundi', Weekday::Monday],
			['mardi', Weekday::Tuesday],
			['mercredi', Weekday::Wednesday],
			['jeudi', Weekday::Thursday],
			['vendredi', Weekday::Friday],
			['samedi', Weekday::Saturday],
			['dimanche', Weekday::Sunday],
			// German
			['montag', Weekday::Monday],
			['dienstag', Weekday::Tuesday],
			['mittwoch', Weekday::Wednesday],
			['donnerstag', Weekday::Thursday],
			['freitag', Weekday::Friday],
			['samstag', Weekday::Saturday],
			['sonntag', Weekday::Sunday],
		];
	}

	#[Test]
	#[DataProvider('normalizeMultilingualProvider')]
	public function normalize_multilingual(string $input, Weekday $expected): void
	{
		$this->assertSame($expected, Weekday::normalize($input));
	}

	// ───────── isoIndex() ─────────

	#[Test]
	public function iso_index_monday_through_sunday(): void
	{
		$this->assertSame(1, Weekday::Monday->isoIndex());
		$this->assertSame(2, Weekday::Tuesday->isoIndex());
		$this->assertSame(3, Weekday::Wednesday->isoIndex());
		$this->assertSame(4, Weekday::Thursday->isoIndex());
		$this->assertSame(5, Weekday::Friday->isoIndex());
		$this->assertSame(6, Weekday::Saturday->isoIndex());
		$this->assertSame(7, Weekday::Sunday->isoIndex());
	}

	#[Test]
	public function iso_index_unique_per_case(): void
	{
		$indices = array_map(fn(Weekday $d) => $d->isoIndex(), Weekday::cases());
		$this->assertCount(7, array_unique($indices));
	}

	#[Test]
	public function iso_index_range_1_to_7(): void
	{
		foreach (Weekday::cases() as $case) {
			$this->assertGreaterThanOrEqual(1, $case->isoIndex());
			$this->assertLessThanOrEqual(7, $case->isoIndex());
		}
	}

	#[Test]
	public function ordered_monday_iso_indices_are_sequential(): void
	{
		$days = Weekday::ordered(true);
		for ($i = 0; $i < 7; $i++) {
			$this->assertSame($i + 1, $days[$i]->isoIndex());
		}
	}

	// ───────── Numeric normalize ↔ isoIndex round-trip ─────────

	#[Test]
	public function numeric_normalize_iso_index_round_trip(): void
	{
		foreach (Weekday::cases() as $day) {
			$resolved = Weekday::normalize((string) $day->isoIndex());
			$this->assertSame($day, $resolved, "Round-trip failed for {$day->name}");
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance(): void
	{
		$inputs = ['monday', 'tue', '3', 'segunda', 'lundi', 'montag', 'garbage', '', '7'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($inputs as $input) {
				Weekday::normalize($input);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($inputs));
		$this->assertLessThan(1.0, $perCall);
	}
}
