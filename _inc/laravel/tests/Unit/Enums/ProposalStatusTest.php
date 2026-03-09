<?php

namespace Tests\Unit\Enums;

use App\Enums\ProposalStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProposalStatus::class)]
#[Group('enums')]
#[Group('proposal-status')]
class ProposalStatusTest extends TestCase
{
	// ───────── Cases & Backing Values ─────────

	#[Test]
	public function it_has_exactly_five_cases(): void
	{
		$this->assertCount(5, ProposalStatus::cases());
	}

	#[Test]
	public function each_case_has_expected_string_value(): void
	{
		$expected = [
			'Draft'    => 'draft',
			'Open'     => 'open',
			'Accepted' => 'accepted',
			'Declined' => 'declined',
			'Close'    => 'close',
		];

		foreach (ProposalStatus::cases() as $case) {
			$this->assertSame($expected[$case->name], $case->value, "Case {$case->name} value mismatch");
		}
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_when_given_instance(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$this->assertSame($case, ProposalStatus::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_draft_for_null(): void
	{
		$this->assertSame(ProposalStatus::Draft, ProposalStatus::normalize(null));
	}

	#[Test]
	public function normalize_returns_draft_for_unknown_string(): void
	{
		$this->assertSame(ProposalStatus::Draft, ProposalStatus::normalize('totally_random_garbage'));
	}

	public static function normalizeEnglishProvider(): array
	{
		return [
			['draft', ProposalStatus::Draft],
			['open', ProposalStatus::Open],
			['accepted', ProposalStatus::Accepted],
			['declined', ProposalStatus::Declined],
			['close', ProposalStatus::Close],
			['closed', ProposalStatus::Close],
		];
	}

	#[Test]
	#[DataProvider('normalizeEnglishProvider')]
	public function normalize_resolves_english_values(string $input, ProposalStatus $expected): void
	{
		$this->assertSame($expected, ProposalStatus::normalize($input));
	}

	public static function normalizeMultilingualProvider(): array
	{
		return [
			// Portuguese
			['rascunho', ProposalStatus::Draft],
			['aberto', ProposalStatus::Open],
			['aceito', ProposalStatus::Accepted],
			['recusado', ProposalStatus::Declined],
			['fechado', ProposalStatus::Close],
			// Spanish
			['borrador', ProposalStatus::Draft],
			['aceptado', ProposalStatus::Accepted],
			['rechazado', ProposalStatus::Declined],
			['cerrado', ProposalStatus::Close],
		];
	}

	#[Test]
	#[DataProvider('normalizeMultilingualProvider')]
	public function normalize_resolves_multilingual_aliases(string $input, ProposalStatus $expected): void
	{
		$this->assertSame($expected, ProposalStatus::normalize($input));
	}

	#[Test]
	public function normalize_is_case_insensitive(): void
	{
		$this->assertSame(ProposalStatus::Draft, ProposalStatus::normalize('DRAFT'));
		$this->assertSame(ProposalStatus::Open, ProposalStatus::normalize('OpEn'));
		$this->assertSame(ProposalStatus::Accepted, ProposalStatus::normalize('ACCEPTED'));
	}

	#[Test]
	public function normalize_trims_whitespace(): void
	{
		$this->assertSame(ProposalStatus::Draft, ProposalStatus::normalize('  draft  '));
	}

	// ───────── Boolean classification ─────────

	public static function classificationProvider(): array
	{
		return [
			// [case, isActive, isFinalized, isPositive, isNegative, isPending, isEditable, isSendable, isRespondable]
			[ProposalStatus::Draft, true, false, false, false, true, true, true, false],
			[ProposalStatus::Open, true, false, false, false, true, false, true, true],
			[ProposalStatus::Accepted, false, true, true, false, false, false, false, false],
			[ProposalStatus::Declined, false, true, false, true, false, false, false, false],
			[ProposalStatus::Close, false, true, false, true, false, false, false, false],
		];
	}

	#[Test]
	#[DataProvider('classificationProvider')]
	public function boolean_classification_methods_are_correct(
		ProposalStatus $case,
		bool $active,
		bool $finalized,
		bool $positive,
		bool $negative,
		bool $pending,
		bool $editable,
		bool $sendable,
		bool $respondable,
	): void {
		$this->assertSame($active, $case->isActive(), "{$case->name}->isActive()");
		$this->assertSame($finalized, $case->isFinalized(), "{$case->name}->isFinalized()");
		$this->assertSame($positive, $case->isPositive(), "{$case->name}->isPositive()");
		$this->assertSame($negative, $case->isNegative(), "{$case->name}->isNegative()");
		$this->assertSame($pending, $case->isPending(), "{$case->name}->isPending()");
		$this->assertSame($editable, $case->isEditable(), "{$case->name}->isEditable()");
		$this->assertSame($sendable, $case->isSendable(), "{$case->name}->isSendable()");
		$this->assertSame($respondable, $case->isRespondable(), "{$case->name}->isRespondable()");
	}

	// ───────── getWeight() / fromWeight() round-trip ─────────

	#[Test]
	public function get_weight_returns_int_for_every_case(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$this->assertIsInt($case->getWeight());
		}
	}

	#[Test]
	public function get_weight_produces_unique_values(): void
	{
		$weights = array_map(fn(ProposalStatus $c) => $c->getWeight(), ProposalStatus::cases());
		$this->assertCount(count($weights), array_unique($weights));
	}

	#[Test]
	public function from_weight_round_trips_for_every_case(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$this->assertSame(
				$case,
				ProposalStatus::fromWeight($case->getWeight()),
				"fromWeight round-trip failed for {$case->name}"
			);
		}
	}

	#[Test]
	public function from_weight_returns_draft_for_unknown_weight(): void
	{
		$this->assertSame(ProposalStatus::Draft, ProposalStatus::fromWeight(999));
		$this->assertSame(ProposalStatus::Draft, ProposalStatus::fromWeight(-1));
	}

	// ───────── State Machine: nextStatus / previousStatus ─────────

	#[Test]
	public function next_status_chain_follows_lifecycle(): void
	{
		$this->assertSame(ProposalStatus::Open, ProposalStatus::Draft->nextStatus());
		$this->assertSame(ProposalStatus::Accepted, ProposalStatus::Open->nextStatus());
		$this->assertSame(ProposalStatus::Close, ProposalStatus::Accepted->nextStatus());
		$this->assertNull(ProposalStatus::Close->nextStatus());
		$this->assertSame(ProposalStatus::Close, ProposalStatus::Declined->nextStatus());
	}

	#[Test]
	public function previous_status_reverses_lifecycle(): void
	{
		$this->assertNull(ProposalStatus::Draft->previousStatus());
		$this->assertSame(ProposalStatus::Draft, ProposalStatus::Open->previousStatus());
		$this->assertSame(ProposalStatus::Open, ProposalStatus::Accepted->previousStatus());
		$this->assertSame(ProposalStatus::Open, ProposalStatus::Declined->previousStatus());
		$this->assertSame(ProposalStatus::Accepted, ProposalStatus::Close->previousStatus());
	}

	#[Test]
	public function full_forward_chain_draft_to_close(): void
	{
		$chain = [];
		$current = ProposalStatus::Draft;
		while ($current !== null) {
			$chain[] = $current;
			$current = $current->nextStatus();
		}
		$this->assertSame(
			[ProposalStatus::Draft, ProposalStatus::Open, ProposalStatus::Accepted, ProposalStatus::Close],
			$chain,
		);
	}

	// ───────── canBeConvertedToInvoice() ─────────

	#[Test]
	public function only_accepted_can_be_converted_to_invoice(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			if ($case === ProposalStatus::Accepted) {
				$this->assertTrue($case->canBeConvertedToInvoice(), 'Accepted should convert');
			} else {
				$this->assertFalse($case->canBeConvertedToInvoice(), "{$case->name} should NOT convert");
			}
		}
	}

	// ───────── getLifecycleStage() ─────────

	#[Test]
	public function lifecycle_stage_returns_non_empty_string(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$stage = $case->getLifecycleStage();
			$this->assertIsString($stage);
			$this->assertNotEmpty($stage, "Lifecycle stage empty for {$case->name}");
		}
	}

	// ───────── allowsRevision() ─────────

	#[Test]
	public function allows_revision_true_only_for_editable(): void
	{
		$this->assertTrue(ProposalStatus::Draft->allowsRevision());
		$this->assertFalse(ProposalStatus::Accepted->allowsRevision());
		$this->assertFalse(ProposalStatus::Close->allowsRevision());
	}

	// ───────── getActionRequired() ─────────

	#[Test]
	public function get_action_required_returns_non_empty_string(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$action = $case->getActionRequired();
			$this->assertIsString($action);
			$this->assertNotEmpty($action, "Action required empty for {$case->name}");
		}
	}

	// ───────── getPriority() ─────────

	#[Test]
	public function get_priority_returns_int(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$this->assertIsInt($case->getPriority());
		}
	}

	// ───────── Mutual exclusiveness ─────────

	#[Test]
	public function positive_and_negative_are_mutually_exclusive(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$this->assertFalse(
				$case->isPositive() && $case->isNegative(),
				"{$case->name} cannot be both positive and negative"
			);
		}
	}

	#[Test]
	public function active_and_finalized_are_mutually_exclusive(): void
	{
		foreach (ProposalStatus::cases() as $case) {
			$this->assertFalse(
				$case->isActive() && $case->isFinalized(),
				"{$case->name} cannot be both active and finalized"
			);
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance_under_1ms_per_call(): void
	{
		$inputs = ['draft', 'open', 'accepted', 'rascunho', 'borrador', 'DECLINED', '  close  ', 'unknown_xyz'];
		$start = hrtime(true);
		$iterations = 1000;
		for ($i = 0; $i < $iterations; $i++) {
			foreach ($inputs as $input) {
				ProposalStatus::normalize($input);
			}
		}
		$elapsed = (hrtime(true) - $start) / 1e6; // ms
		$perCall = $elapsed / ($iterations * count($inputs));
		$this->assertLessThan(1.0, $perCall, "normalize() averaged {$perCall}ms per call");
	}
}
