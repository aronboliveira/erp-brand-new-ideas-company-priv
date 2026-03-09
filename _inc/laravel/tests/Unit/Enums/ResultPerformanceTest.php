<?php

namespace Tests\Unit\Enums;

use App\Enums\ResultPerformance;
use App\Enums\RatingTitle;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ResultPerformance::class)]
#[Group('enums')]
#[Group('result-performance')]
class ResultPerformanceTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_exactly_five_cases(): void
	{
		$this->assertCount(5, ResultPerformance::cases());
	}

	#[Test]
	public function int_backed_values(): void
	{
		$this->assertSame(0, ResultPerformance::NotConcluded->value);
		$this->assertSame(1, ResultPerformance::Satisfactory->value);
		$this->assertSame(2, ResultPerformance::Average->value);
		$this->assertSame(3, ResultPerformance::Poor->value);
		$this->assertSame(4, ResultPerformance::Excellent->value);
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (ResultPerformance::cases() as $case) {
			$this->assertSame($case, ResultPerformance::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_not_concluded_for_null(): void
	{
		$this->assertSame(ResultPerformance::NotConcluded, ResultPerformance::normalize(null));
	}

	public static function normalizeProvider(): array
	{
		return [
			// Numeric strings
			['0', ResultPerformance::NotConcluded],
			['1', ResultPerformance::Satisfactory],
			['2', ResultPerformance::Average],
			['3', ResultPerformance::Poor],
			['4', ResultPerformance::Excellent],
			// English labels
			['notconcluded', ResultPerformance::NotConcluded],
			['satisfactory', ResultPerformance::Satisfactory],
			['average', ResultPerformance::Average],
			['poor', ResultPerformance::Poor],
			['excellent', ResultPerformance::Excellent],
			// Aliases
			['pending', ResultPerformance::NotConcluded],
			['incomplete', ResultPerformance::NotConcluded],
			['good', ResultPerformance::Satisfactory],
			['acceptable', ResultPerformance::Satisfactory],
			['medium', ResultPerformance::Average],
			['moderate', ResultPerformance::Average],
			['bad', ResultPerformance::Poor],
			['weak', ResultPerformance::Poor],
			['outstanding', ResultPerformance::Excellent],
			['superb', ResultPerformance::Excellent],
		];
	}

	#[Test]
	#[DataProvider('normalizeProvider')]
	public function normalize_resolves_correctly(string $input, ResultPerformance $expected): void
	{
		$this->assertSame($expected, ResultPerformance::normalize($input));
	}

	#[Test]
	public function normalize_defaults_to_not_concluded_for_unknown(): void
	{
		$this->assertSame(ResultPerformance::NotConcluded, ResultPerformance::normalize('garbage_xyz'));
	}

	#[Test]
	public function normalize_accepts_int(): void
	{
		$this->assertSame(ResultPerformance::NotConcluded, ResultPerformance::normalize(0));
		$this->assertSame(ResultPerformance::Excellent, ResultPerformance::normalize(4));
	}

	// ───────── getScore() ─────────

	#[Test]
	public function get_score_returns_expected_values(): void
	{
		$this->assertSame(0, ResultPerformance::NotConcluded->getScore());
		$this->assertSame(25, ResultPerformance::Poor->getScore());
		$this->assertSame(50, ResultPerformance::Average->getScore());
		$this->assertSame(75, ResultPerformance::Satisfactory->getScore());
		$this->assertSame(100, ResultPerformance::Excellent->getScore());
	}

	#[Test]
	public function get_score_is_monotonically_increasing_in_quality_order(): void
	{
		$ordered = ResultPerformance::sortedWorstToBest();
		for ($i = 1; $i < count($ordered); $i++) {
			$this->assertGreaterThan(
				$ordered[$i - 1]->getScore(),
				$ordered[$i]->getScore(),
				"{$ordered[$i]->name} score should exceed {$ordered[$i - 1]->name}"
			);
		}
	}

	// ───────── fromScore() ─────────

	public static function fromScoreProvider(): array
	{
		return [
			// Excellent: score >= 90
			[100, ResultPerformance::Excellent],
			[95, ResultPerformance::Excellent],
			[90, ResultPerformance::Excellent],
			// Satisfactory: 70-89
			[89, ResultPerformance::Satisfactory],
			[75, ResultPerformance::Satisfactory],
			[70, ResultPerformance::Satisfactory],
			// Average: 50-69
			[69, ResultPerformance::Average],
			[50, ResultPerformance::Average],
			// Poor: 30-49
			[49, ResultPerformance::Poor],
			[30, ResultPerformance::Poor],
			// NotConcluded: < 30
			[29, ResultPerformance::NotConcluded],
			[0, ResultPerformance::NotConcluded],
			[-10, ResultPerformance::NotConcluded],
		];
	}

	#[Test]
	#[DataProvider('fromScoreProvider')]
	public function from_score_maps_correctly(int $score, ResultPerformance $expected): void
	{
		$this->assertSame($expected, ResultPerformance::fromScore($score));
	}

	#[Test]
	public function from_score_boundary_values(): void
	{
		// Boundary at 90
		$this->assertSame(ResultPerformance::Excellent, ResultPerformance::fromScore(90));
		$this->assertSame(ResultPerformance::Satisfactory, ResultPerformance::fromScore(89));
		// Boundary at 70
		$this->assertSame(ResultPerformance::Satisfactory, ResultPerformance::fromScore(70));
		$this->assertSame(ResultPerformance::Average, ResultPerformance::fromScore(69));
		// Boundary at 50
		$this->assertSame(ResultPerformance::Average, ResultPerformance::fromScore(50));
		$this->assertSame(ResultPerformance::Poor, ResultPerformance::fromScore(49));
		// Boundary at 30
		$this->assertSame(ResultPerformance::Poor, ResultPerformance::fromScore(30));
		$this->assertSame(ResultPerformance::NotConcluded, ResultPerformance::fromScore(29));
	}

	// ───────── Boolean classification ─────────

	#[Test]
	public function is_positive_for_satisfactory_and_excellent(): void
	{
		$this->assertTrue(ResultPerformance::Satisfactory->isPositive());
		$this->assertTrue(ResultPerformance::Excellent->isPositive());
		$this->assertFalse(ResultPerformance::Average->isPositive());
		$this->assertFalse(ResultPerformance::Poor->isPositive());
		$this->assertFalse(ResultPerformance::NotConcluded->isPositive());
	}

	#[Test]
	public function is_negative_for_poor_and_not_concluded(): void
	{
		$this->assertTrue(ResultPerformance::Poor->isNegative());
		$this->assertTrue(ResultPerformance::NotConcluded->isNegative());
		$this->assertFalse(ResultPerformance::Average->isNegative());
		$this->assertFalse(ResultPerformance::Satisfactory->isNegative());
		$this->assertFalse(ResultPerformance::Excellent->isNegative());
	}

	#[Test]
	public function is_neutral_only_for_average(): void
	{
		$this->assertTrue(ResultPerformance::Average->isNeutral());
		foreach (ResultPerformance::cases() as $case) {
			if ($case !== ResultPerformance::Average) {
				$this->assertFalse($case->isNeutral(), "{$case->name} should not be neutral");
			}
		}
	}

	#[Test]
	public function positive_negative_neutral_cover_all_cases(): void
	{
		foreach (ResultPerformance::cases() as $case) {
			$sum = ($case->isPositive() ? 1 : 0) + ($case->isNegative() ? 1 : 0) + ($case->isNeutral() ? 1 : 0);
			$this->assertSame(1, $sum, "{$case->name} should be in exactly one classification");
		}
	}

	// ───────── getNextHigher / getPreviousLower ─────────

	#[Test]
	public function get_next_higher_chain(): void
	{
		$this->assertSame(ResultPerformance::Poor, ResultPerformance::NotConcluded->getNextHigher());
		$this->assertSame(ResultPerformance::Average, ResultPerformance::Poor->getNextHigher());
		$this->assertSame(ResultPerformance::Satisfactory, ResultPerformance::Average->getNextHigher());
		$this->assertSame(ResultPerformance::Excellent, ResultPerformance::Satisfactory->getNextHigher());
		$this->assertNull(ResultPerformance::Excellent->getNextHigher());
	}

	#[Test]
	public function get_previous_lower_chain(): void
	{
		$this->assertNull(ResultPerformance::NotConcluded->getPreviousLower());
		$this->assertSame(ResultPerformance::NotConcluded, ResultPerformance::Poor->getPreviousLower());
		$this->assertSame(ResultPerformance::Poor, ResultPerformance::Average->getPreviousLower());
		$this->assertSame(ResultPerformance::Average, ResultPerformance::Satisfactory->getPreviousLower());
		$this->assertSame(ResultPerformance::Satisfactory, ResultPerformance::Excellent->getPreviousLower());
	}

	#[Test]
	public function full_chain_worst_to_best_via_get_next_higher(): void
	{
		$chain = [];
		$current = ResultPerformance::NotConcluded;
		while ($current !== null) {
			$chain[] = $current;
			$current = $current->getNextHigher();
		}
		$this->assertSame(ResultPerformance::sortedWorstToBest(), $chain);
	}

	// ───────── toRatingTitle() — cross-enum ─────────

	#[Test]
	public function to_rating_title_returns_rating_title_instances(): void
	{
		foreach (ResultPerformance::cases() as $case) {
			$this->assertInstanceOf(RatingTitle::class, $case->toRatingTitle());
		}
	}

	#[Test]
	public function to_rating_title_mapping(): void
	{
		$this->assertSame(RatingTitle::Unsatisfactory, ResultPerformance::NotConcluded->toRatingTitle());
		$this->assertSame(RatingTitle::NeedsImprovement, ResultPerformance::Poor->toRatingTitle());
		$this->assertSame(RatingTitle::Satisfactory, ResultPerformance::Average->toRatingTitle());
		$this->assertSame(RatingTitle::VeryGood, ResultPerformance::Satisfactory->toRatingTitle());
		$this->assertSame(RatingTitle::Excellent, ResultPerformance::Excellent->toRatingTitle());
	}

	// ───────── Sorting methods ─────────

	#[Test]
	public function sorted_worst_to_best_has_5_elements(): void
	{
		$this->assertCount(5, ResultPerformance::sortedWorstToBest());
	}

	#[Test]
	public function sorted_worst_to_best_starts_with_not_concluded(): void
	{
		$sorted = ResultPerformance::sortedWorstToBest();
		$this->assertSame(ResultPerformance::NotConcluded, $sorted[0]);
		$this->assertSame(ResultPerformance::Excellent, end($sorted));
	}

	#[Test]
	public function sorted_best_to_worst_is_reverse_of_worst_to_best(): void
	{
		$this->assertSame(
			array_reverse(ResultPerformance::sortedWorstToBest()),
			ResultPerformance::sortedBestToWorst(),
		);
	}

	// ───────── getOrder() ─────────

	#[Test]
	public function get_order_matches_int_value(): void
	{
		foreach (ResultPerformance::cases() as $case) {
			$this->assertSame($case->value, $case->getOrder());
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance(): void
	{
		$inputs = ['0', '1', 'excellent', 'poor', 'good', 'pending', 'unknown'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($inputs as $input) {
				ResultPerformance::normalize($input);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($inputs));
		$this->assertLessThan(1.0, $perCall);
	}

	#[Test]
	public function from_score_performance(): void
	{
		$scores = [0, 10, 29, 30, 49, 50, 69, 70, 89, 90, 100];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($scores as $score) {
				ResultPerformance::fromScore($score);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($scores));
		$this->assertLessThan(1.0, $perCall);
	}
}
