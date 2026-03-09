<?php

namespace Tests\Unit\Enums;

use App\Enums\ParticipationStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ParticipationStatus::class)]
#[Group('enums')]
#[Group('participation-status')]
class ParticipationStatusTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_exactly_fifteen_cases(): void
	{
		$this->assertCount(15, ParticipationStatus::cases());
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (ParticipationStatus::cases() as $case) {
			$this->assertSame($case, ParticipationStatus::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_null_for_null(): void
	{
		$this->assertNull(ParticipationStatus::normalize(null));
	}

	#[Test]
	public function normalize_returns_null_for_unknown(): void
	{
		$this->assertNull(ParticipationStatus::normalize('xyzzy_unknown_status'));
	}

	public static function normalizeEnglishProvider(): array
	{
		return [
			['invited', ParticipationStatus::Invited],
			['pending', ParticipationStatus::Pending],
			['accepted', ParticipationStatus::Accepted],
			['declined', ParticipationStatus::Declined],
			['tentative', ParticipationStatus::Tentative],
			['attended', ParticipationStatus::Attended],
			['noshow', ParticipationStatus::NoShow],
			['cancelled', ParticipationStatus::Cancelled],
			['canceled', ParticipationStatus::Cancelled],
			['expired', ParticipationStatus::Expired],
			['maybe', ParticipationStatus::Maybe],
			['awaiting', ParticipationStatus::Awaiting],
			['confirmed', ParticipationStatus::Confirmed],
			['unconfirmed', ParticipationStatus::Unconfirmed],
			['withdrawn', ParticipationStatus::Withdrawn],
			['blocked', ParticipationStatus::Blocked],
		];
	}

	#[Test]
	#[DataProvider('normalizeEnglishProvider')]
	public function normalize_resolves_english_values(string $input, ParticipationStatus $expected): void
	{
		$this->assertSame($expected, ParticipationStatus::normalize($input));
	}

	public static function normalizeMultilingualProvider(): array
	{
		return [
			['convidado', ParticipationStatus::Invited],
			['pendente', ParticipationStatus::Pending],
			['aceito', ParticipationStatus::Accepted],
			['recusado', ParticipationStatus::Declined],
			['compareceu', ParticipationStatus::Attended],
			['ausente', ParticipationStatus::NoShow],
			['cancelado', ParticipationStatus::Cancelled],
			['expirado', ParticipationStatus::Expired],
			['talvez', ParticipationStatus::Maybe],
			['aguardando', ParticipationStatus::Awaiting],
			['confirmado', ParticipationStatus::Confirmed],
			['retirado', ParticipationStatus::Withdrawn],
			['bloqueado', ParticipationStatus::Blocked],
		];
	}

	#[Test]
	#[DataProvider('normalizeMultilingualProvider')]
	public function normalize_resolves_multilingual(string $input, ParticipationStatus $expected): void
	{
		$this->assertSame($expected, ParticipationStatus::normalize($input));
	}

	#[Test]
	public function normalize_is_case_insensitive_and_trims(): void
	{
		$this->assertSame(ParticipationStatus::Accepted, ParticipationStatus::normalize('  ACCEPTED  '));
		$this->assertSame(ParticipationStatus::Blocked, ParticipationStatus::normalize('BLocKeD'));
	}

	// ───────── Boolean classification ─────────

	#[Test]
	public function positive_statuses_exactly_three(): void
	{
		$positive = array_filter(
			ParticipationStatus::cases(),
			fn(ParticipationStatus $s) => $s->isPositive()
		);
		$this->assertCount(3, $positive);
		$names = array_map(fn($s) => $s->name, array_values($positive));
		$this->assertEqualsCanonicalizing(['Accepted', 'Attended', 'Confirmed'], $names);
	}

	#[Test]
	public function negative_statuses_exactly_six(): void
	{
		$negative = array_filter(
			ParticipationStatus::cases(),
			fn(ParticipationStatus $s) => $s->isNegative()
		);
		$this->assertCount(6, $negative);
	}

	#[Test]
	public function pending_statuses_exactly_six(): void
	{
		$pending = array_filter(
			ParticipationStatus::cases(),
			fn(ParticipationStatus $s) => $s->isPending()
		);
		$this->assertCount(6, $pending);
	}

	#[Test]
	public function final_statuses_exactly_nine(): void
	{
		$final = array_filter(
			ParticipationStatus::cases(),
			fn(ParticipationStatus $s) => $s->isFinal()
		);
		$this->assertCount(9, $final);
	}

	#[Test]
	public function positive_negative_pending_are_mutually_exclusive(): void
	{
		foreach (ParticipationStatus::cases() as $case) {
			$flags = ($case->isPositive() ? 1 : 0) + ($case->isNegative() ? 1 : 0) + ($case->isPending() ? 1 : 0);
			$this->assertLessThanOrEqual(1, $flags, "{$case->name} has overlapping classification");
		}
	}

	// ───────── Transition Machine: getPossibleTransitions / canTransitionTo ─────────

	#[Test]
	public function terminal_statuses_have_no_transitions(): void
	{
		$terminals = [
			ParticipationStatus::Attended,
			ParticipationStatus::NoShow,
			ParticipationStatus::Declined,
			ParticipationStatus::Cancelled,
			ParticipationStatus::Expired,
			ParticipationStatus::Withdrawn,
			ParticipationStatus::Blocked,
		];

		foreach ($terminals as $status) {
			$this->assertEmpty(
				$status->getPossibleTransitions(),
				"{$status->name} should have no transitions"
			);
		}
	}

	#[Test]
	public function invited_can_transition_to_expected_statuses(): void
	{
		$transitions = ParticipationStatus::Invited->getPossibleTransitions();
		$this->assertContains(ParticipationStatus::Accepted, $transitions);
		$this->assertContains(ParticipationStatus::Declined, $transitions);
		$this->assertContains(ParticipationStatus::Tentative, $transitions);
		$this->assertContains(ParticipationStatus::Maybe, $transitions);
		$this->assertContains(ParticipationStatus::Cancelled, $transitions);
	}

	#[Test]
	public function accepted_can_transition_to_attendance_outcomes(): void
	{
		$transitions = ParticipationStatus::Accepted->getPossibleTransitions();
		$this->assertContains(ParticipationStatus::Attended, $transitions);
		$this->assertContains(ParticipationStatus::NoShow, $transitions);
		$this->assertContains(ParticipationStatus::Withdrawn, $transitions);
		$this->assertContains(ParticipationStatus::Cancelled, $transitions);
	}

	#[Test]
	public function can_transition_to_is_consistent_with_get_possible_transitions(): void
	{
		foreach (ParticipationStatus::cases() as $from) {
			$allowed = $from->getPossibleTransitions();
			foreach (ParticipationStatus::cases() as $to) {
				$this->assertSame(
					in_array($to, $allowed, true),
					$from->canTransitionTo($to),
					"canTransitionTo inconsistency: {$from->name} -> {$to->name}"
				);
			}
		}
	}

	#[Test]
	public function no_status_can_transition_to_itself(): void
	{
		foreach (ParticipationStatus::cases() as $case) {
			$this->assertFalse(
				$case->canTransitionTo($case),
				"{$case->name} should not transition to itself"
			);
		}
	}

	// ───────── getOrder() ─────────

	#[Test]
	public function get_order_produces_unique_values(): void
	{
		$orders = array_map(fn(ParticipationStatus $c) => $c->getOrder(), ParticipationStatus::cases());
		$this->assertCount(count($orders), array_unique($orders));
	}

	#[Test]
	public function get_order_range_is_1_to_15(): void
	{
		$orders = array_map(fn(ParticipationStatus $c) => $c->getOrder(), ParticipationStatus::cases());
		$this->assertSame(1, min($orders));
		$this->assertSame(15, max($orders));
	}

	// ───────── getActiveStatuses / getInactiveStatuses ─────────

	#[Test]
	public function active_and_inactive_cover_all_cases(): void
	{
		$active = ParticipationStatus::getActiveStatuses();
		$inactive = ParticipationStatus::getInactiveStatuses();
		$all = array_merge($active, $inactive);
		$this->assertCount(15, $all);
		foreach (ParticipationStatus::cases() as $case) {
			$this->assertTrue(in_array($case, $all, true), "{$case->name} not in active or inactive");
		}
	}

	#[Test]
	public function active_and_inactive_do_not_overlap(): void
	{
		$active = ParticipationStatus::getActiveStatuses();
		$inactive = ParticipationStatus::getInactiveStatuses();
		$overlap = array_filter($active, fn($s) => in_array($s, $inactive, true));
		$this->assertEmpty($overlap, 'Active and inactive lists overlap');
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance(): void
	{
		$inputs = ['invited', 'accepted', 'convidado', 'noshow', 'BLOCKED', '  maybe  ', 'garbage_xyz'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($inputs as $input) {
				ParticipationStatus::normalize($input);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($inputs));
		$this->assertLessThan(1.0, $perCall);
	}

	#[Test]
	public function transition_lookup_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach (ParticipationStatus::cases() as $from) {
				$from->getPossibleTransitions();
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count(ParticipationStatus::cases()));
		$this->assertLessThan(1.0, $perCall);
	}
}
