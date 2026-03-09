<?php

namespace Tests\Unit\Enums;

use App\Enums\MonthName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(MonthName::class)]
#[Group('enums')]
#[Group('monthname')]
class MonthNameTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_exactly_twelve_cases(): void
	{
		$this->assertCount(12, MonthName::cases());
	}

	#[Test]
	public function each_case_has_lowercase_string_value(): void
	{
		foreach (MonthName::cases() as $case) {
			$this->assertSame(strtolower($case->name), $case->value, "Case {$case->name}");
		}
	}

	// ───────── values() ─────────

	#[Test]
	public function values_default_returns_eleven_items(): void
	{
		$v = MonthName::values();
		$this->assertCount(11, $v);
		$this->assertSame('january', $v[0]);
	}

	#[Test]
	public function values_twelve_returns_all(): void
	{
		$v = MonthName::values(12);
		$this->assertCount(12, $v);
		$this->assertSame('december', $v[11]);
	}

	#[Test]
	public function values_six_returns_first_six(): void
	{
		$v = MonthName::values(6);
		$this->assertCount(6, $v);
		$this->assertSame('june', $v[5]);
	}

	#[Test]
	public function values_one_returns_january(): void
	{
		$v = MonthName::values(1);
		$this->assertCount(1, $v);
		$this->assertSame('january', $v[0]);
	}

	// ───────── ordered() ─────────

	#[Test]
	public function ordered_returns_twelve_months_in_calendar_order(): void
	{
		$months = MonthName::ordered();
		$this->assertCount(12, $months);
		$this->assertSame(MonthName::January, $months[0]);
		$this->assertSame(MonthName::February, $months[1]);
		$this->assertSame(MonthName::March, $months[2]);
		$this->assertSame(MonthName::April, $months[3]);
		$this->assertSame(MonthName::May, $months[4]);
		$this->assertSame(MonthName::June, $months[5]);
		$this->assertSame(MonthName::July, $months[6]);
		$this->assertSame(MonthName::August, $months[7]);
		$this->assertSame(MonthName::September, $months[8]);
		$this->assertSame(MonthName::October, $months[9]);
		$this->assertSame(MonthName::November, $months[10]);
		$this->assertSame(MonthName::December, $months[11]);
	}

	#[Test]
	public function ordered_contains_all_cases(): void
	{
		$this->assertEqualsCanonicalizing(MonthName::cases(), MonthName::ordered());
	}

	// ───────── isoIndex() ─────────

	#[Test]
	public function iso_index_january_1_through_december_12(): void
	{
		$ordered = MonthName::ordered();
		for ($i = 0; $i < 12; $i++) {
			$this->assertSame($i + 1, $ordered[$i]->isoIndex(), "{$ordered[$i]->name} index mismatch");
		}
	}

	#[Test]
	public function iso_index_unique_across_all_cases(): void
	{
		$indices = array_map(fn(MonthName $m) => $m->isoIndex(), MonthName::cases());
		$this->assertCount(12, array_unique($indices));
	}

	#[Test]
	public function iso_index_range_1_to_12(): void
	{
		foreach (MonthName::cases() as $case) {
			$this->assertGreaterThanOrEqual(1, $case->isoIndex());
			$this->assertLessThanOrEqual(12, $case->isoIndex());
		}
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (MonthName::cases() as $case) {
			$this->assertSame($case, MonthName::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_null_for_null(): void
	{
		$this->assertNull(MonthName::normalize(null));
	}

	#[Test]
	public function normalize_returns_january_for_empty_string(): void
	{
		$result = MonthName::normalize('');
		$this->assertSame(MonthName::January, $result);
	}

	#[Test]
	public function normalize_returns_january_for_unknown(): void
	{
		$result = MonthName::normalize('notamonth');
		$this->assertSame(MonthName::January, $result);
	}

	public static function normalizeNumericProvider(): array
	{
		return [
			['1',  MonthName::January],
			['2',  MonthName::February],
			['3',  MonthName::March],
			['4',  MonthName::April],
			['5',  MonthName::May],
			['6',  MonthName::June],
			['7',  MonthName::July],
			['8',  MonthName::August],
			['9',  MonthName::September],
			['10', MonthName::October],
			['11', MonthName::November],
			['12', MonthName::December],
			// zero-padded
			['01', MonthName::January],
			['02', MonthName::February],
			['09', MonthName::September],
		];
	}

	#[Test]
	#[DataProvider('normalizeNumericProvider')]
	public function normalize_numeric(string $input, MonthName $expected): void
	{
		$this->assertSame($expected, MonthName::normalize($input));
	}

	public static function normalizeEnglishProvider(): array
	{
		return [
			// full names
			['january',   MonthName::January],
			['february',  MonthName::February],
			['march',     MonthName::March],
			['april',     MonthName::April],
			['may',       MonthName::May],
			['june',      MonthName::June],
			['july',      MonthName::July],
			['august',    MonthName::August],
			['september', MonthName::September],
			['october',   MonthName::October],
			['november',  MonthName::November],
			['december',  MonthName::December],
			// abbreviations
			['jan', MonthName::January],
			['feb', MonthName::February],
			['mar', MonthName::March],
			['apr', MonthName::April],
			['jun', MonthName::June],
			['jul', MonthName::July],
			['aug', MonthName::August],
			['sep', MonthName::September],
			['oct', MonthName::October],
			['nov', MonthName::November],
			['dec', MonthName::December],
		];
	}

	#[Test]
	#[DataProvider('normalizeEnglishProvider')]
	public function normalize_english(string $input, MonthName $expected): void
	{
		$this->assertSame($expected, MonthName::normalize($input));
	}

	public static function normalizePortugueseProvider(): array
	{
		return [
			['janeiro',   MonthName::January],
			['fevereiro', MonthName::February],
			['março',     MonthName::March],
			['marco',     MonthName::March],
			['abril',     MonthName::April],
			['maio',      MonthName::May],
			['junho',     MonthName::June],
			['julho',     MonthName::July],
			['agosto',    MonthName::August],
			['setembro',  MonthName::September],
			['outubro',   MonthName::October],
			['novembro',  MonthName::November],
			['dezembro',  MonthName::December],
		];
	}

	#[Test]
	#[DataProvider('normalizePortugueseProvider')]
	public function normalize_portuguese(string $input, MonthName $expected): void
	{
		$this->assertSame($expected, MonthName::normalize($input));
	}

	#[Test]
	public function normalize_is_case_insensitive(): void
	{
		$this->assertSame(MonthName::January, MonthName::normalize('JANUARY'));
		$this->assertSame(MonthName::March, MonthName::normalize('March'));
		$this->assertSame(MonthName::July, MonthName::normalize('jUlY'));
	}

	#[Test]
	public function normalize_strips_whitespace(): void
	{
		$this->assertSame(MonthName::January, MonthName::normalize('  january  '));
		$this->assertSame(MonthName::May, MonthName::normalize("\tmay\n"));
	}

	// ───────── isoIndex ↔ numeric normalize round-trip ─────────

	#[Test]
	public function numeric_normalize_iso_index_round_trip(): void
	{
		foreach (MonthName::cases() as $month) {
			$resolved = MonthName::normalize((string) $month->isoIndex());
			$this->assertSame($month, $resolved, "Round-trip failed for {$month->name}");
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance(): void
	{
		$inputs = ['january', 'feb', '3', 'março', 'julho', 'NOVEMBER', '', 'garbage', '12', '01'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($inputs as $input) {
				MonthName::normalize($input);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($inputs));
		$this->assertLessThan(1.0, $perCall);
	}
}
