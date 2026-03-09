<?php

namespace Tests\Unit\Enums;

use App\Enums\FinancialEstimationStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FinancialEstimationStatus::class)]
#[Group('enums')]
#[Group('financial-estimation-status')]
class FinancialEstimationStatusTest extends TestCase
{
	// ───────── Cases & Backing Values ─────────

	#[Test]
	public function it_has_exactly_five_cases(): void
	{
		$this->assertCount(6, FinancialEstimationStatus::cases());
	}

	#[Test]
	public function each_case_has_expected_string_value(): void
	{
		$expected = [
			'Draft'          => 'draft',
			'Open'           => 'open',
			'NotPaid'        => 'not_paid',
			'PartiallyPaid'  => 'partially_paid',
			'Paid'           => 'paid',
			'Cancelled'      => 'cancelled',
		];

		foreach (FinancialEstimationStatus::cases() as $case) {
			$this->assertSame($expected[$case->name], $case->value, "Case {$case->name} mismatch");
		}
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (FinancialEstimationStatus::cases() as $case) {
			$this->assertSame($case, FinancialEstimationStatus::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_open_for_null(): void
	{
		$this->assertSame(FinancialEstimationStatus::Open, FinancialEstimationStatus::normalize(null));
	}

	public static function normalizeDirectProvider(): array
	{
		return [
			['draft', FinancialEstimationStatus::Draft],
			['open', FinancialEstimationStatus::Open],
			['not_paid', FinancialEstimationStatus::NotPaid],
			['partially_paid', FinancialEstimationStatus::PartiallyPaid],
			['paid', FinancialEstimationStatus::Paid],
			['cancelled', FinancialEstimationStatus::Cancelled],
		];
	}

	#[Test]
	#[DataProvider('normalizeDirectProvider')]
	public function normalize_resolves_direct_values(string $input, FinancialEstimationStatus $expected): void
	{
		$this->assertSame($expected, FinancialEstimationStatus::normalize($input));
	}

	public static function normalizeAliasProvider(): array
	{
		return [
			// Open aliases
			['pending', FinancialEstimationStatus::Open],
			['in_progress', FinancialEstimationStatus::Open],
			['in-progress', FinancialEstimationStatus::Open],
			['aberto', FinancialEstimationStatus::Open],
			['abierto', FinancialEstimationStatus::Open],
			// Not Paid aliases
			['not paid', FinancialEstimationStatus::NotPaid],
			['not-paid', FinancialEstimationStatus::NotPaid],
			['não pago', FinancialEstimationStatus::NotPaid],
			['impayé', FinancialEstimationStatus::NotPaid],
			['nicht bezahlt', FinancialEstimationStatus::NotPaid],
			// Partially Paid aliases
			['partially paid', FinancialEstimationStatus::PartiallyPaid],
			['partial', FinancialEstimationStatus::PartiallyPaid],
			['parcialmente pago', FinancialEstimationStatus::PartiallyPaid],
			// Paid aliases
			['completed', FinancialEstimationStatus::Paid],
			['settled', FinancialEstimationStatus::Paid],
			['pago', FinancialEstimationStatus::Paid],
			['bezahlt', FinancialEstimationStatus::Paid],
			// Cancelled aliases
			['canceled', FinancialEstimationStatus::Cancelled],
			['void', FinancialEstimationStatus::Cancelled],
			['voided', FinancialEstimationStatus::Cancelled],
			['rejected', FinancialEstimationStatus::Cancelled],
			['cancelado', FinancialEstimationStatus::Cancelled],
			['annulé', FinancialEstimationStatus::Cancelled],
		];
	}

	#[Test]
	#[DataProvider('normalizeAliasProvider')]
	public function normalize_resolves_aliases(string $input, FinancialEstimationStatus $expected): void
	{
		$this->assertSame($expected, FinancialEstimationStatus::normalize($input));
	}

	#[Test]
	public function normalize_defaults_to_open_for_unknown(): void
	{
		$this->assertSame(FinancialEstimationStatus::Open, FinancialEstimationStatus::normalize('totally_unknown'));
	}

	// ───────── isValidValue() ─────────

	#[Test]
	public function is_valid_value_returns_true_for_known_values(): void
	{
		foreach (FinancialEstimationStatus::cases() as $case) {
			$this->assertTrue(FinancialEstimationStatus::isValidValue($case->value));
		}
	}

	#[Test]
	public function is_valid_value_returns_false_for_unknown(): void
	{
		$this->assertFalse(FinancialEstimationStatus::isValidValue('nonexistent'));
	}

	// ───────── values() / names() ─────────

	#[Test]
	public function values_returns_all_backing_strings(): void
	{
		$values = FinancialEstimationStatus::values();
		$this->assertCount(6, $values);
		$this->assertContains('open', $values);
		$this->assertContains('draft', $values);
		$this->assertContains('paid', $values);
		$this->assertContains('cancelled', $values);
	}

	#[Test]
	public function names_returns_all_case_names(): void
	{
		$names = FinancialEstimationStatus::names();
		$this->assertCount(6, $names);
		$this->assertContains('Open', $names);
		$this->assertContains('Draft', $names);
		$this->assertContains('Paid', $names);
	}

	// ───────── Boolean classification ─────────

	public static function booleanProvider(): array
	{
		return [
			// [case, isOpen, isClosed, isPendingPayment, isPaid, isCancelled, isPartiallyPaid, isNotPaid, allowsEditing, allowsPayment]
			[FinancialEstimationStatus::Open, true, false, true, false, false, false, false, true, true],
			[FinancialEstimationStatus::NotPaid, true, false, true, false, false, false, true, true, true],
			[FinancialEstimationStatus::PartiallyPaid, true, false, true, false, false, true, false, true, true],
			[FinancialEstimationStatus::Paid, false, true, false, true, false, false, false, false, false],
			[FinancialEstimationStatus::Cancelled, false, true, false, false, true, false, false, false, false],
		];
	}

	#[Test]
	#[DataProvider('booleanProvider')]
	public function boolean_classification_methods(
		FinancialEstimationStatus $case,
		bool $isOpen,
		bool $isClosed,
		bool $isPendingPayment,
		bool $isPaid,
		bool $isCancelled,
		bool $isPartiallyPaid,
		bool $isNotPaid,
		bool $allowsEditing,
		bool $allowsPayment,
	): void {
		$this->assertSame($isOpen, $case->isOpen(), "{$case->name}->isOpen()");
		$this->assertSame($isClosed, $case->isClosed(), "{$case->name}->isClosed()");
		$this->assertSame($isPendingPayment, $case->isPendingPayment(), "{$case->name}->isPendingPayment()");
		$this->assertSame($isPaid, $case->isPaid(), "{$case->name}->isPaid()");
		$this->assertSame($isCancelled, $case->isCancelled(), "{$case->name}->isCancelled()");
		$this->assertSame($isPartiallyPaid, $case->isPartiallyPaid(), "{$case->name}->isPartiallyPaid()");
		$this->assertSame($isNotPaid, $case->isNotPaid(), "{$case->name}->isNotPaid()");
		$this->assertSame($allowsEditing, $case->allowsEditing(), "{$case->name}->allowsEditing()");
		$this->assertSame($allowsPayment, $case->allowsPayment(), "{$case->name}->allowsPayment()");
	}

	// ───────── getWeight() / fromWeight() ─────────

	#[Test]
	public function get_weight_returns_int_for_every_case(): void
	{
		foreach (FinancialEstimationStatus::cases() as $case) {
			$this->assertIsInt($case->getWeight());
		}
	}

	#[Test]
	public function from_weight_round_trips_all_cases(): void
	{
		foreach (FinancialEstimationStatus::cases() as $case) {
			$this->assertSame(
				$case,
				FinancialEstimationStatus::fromWeight($case->getWeight()),
				"fromWeight round-trip failed for {$case->name}"
			);
		}
	}

	#[Test]
	public function from_weight_defaults_to_open_for_unknown(): void
	{
		$this->assertSame(FinancialEstimationStatus::Open, FinancialEstimationStatus::fromWeight(999));
		$this->assertSame(FinancialEstimationStatus::Open, FinancialEstimationStatus::fromWeight(-50));
	}

	// ───────── State Machine: nextStatus / previousStatus ─────────

	#[Test]
	public function next_status_follows_payment_lifecycle(): void
	{
		$this->assertSame(FinancialEstimationStatus::NotPaid, FinancialEstimationStatus::Open->nextStatus());
		$this->assertSame(FinancialEstimationStatus::PartiallyPaid, FinancialEstimationStatus::NotPaid->nextStatus());
		$this->assertSame(FinancialEstimationStatus::Paid, FinancialEstimationStatus::PartiallyPaid->nextStatus());
		$this->assertNull(FinancialEstimationStatus::Paid->nextStatus());
		$this->assertNull(FinancialEstimationStatus::Cancelled->nextStatus());
	}

	#[Test]
	public function previous_status_reverses_payment_lifecycle(): void
	{
		$this->assertNull(FinancialEstimationStatus::Open->previousStatus());
		$this->assertSame(FinancialEstimationStatus::Open, FinancialEstimationStatus::NotPaid->previousStatus());
		$this->assertSame(FinancialEstimationStatus::NotPaid, FinancialEstimationStatus::PartiallyPaid->previousStatus());
		$this->assertSame(FinancialEstimationStatus::PartiallyPaid, FinancialEstimationStatus::Paid->previousStatus());
		$this->assertNull(FinancialEstimationStatus::Cancelled->previousStatus());
	}

	#[Test]
	public function full_forward_chain_open_to_paid(): void
	{
		$chain = [];
		$current = FinancialEstimationStatus::Open;
		while ($current !== null) {
			$chain[] = $current;
			$current = $current->nextStatus();
		}
		$this->assertSame(
			[
				FinancialEstimationStatus::Open,
				FinancialEstimationStatus::NotPaid,
				FinancialEstimationStatus::PartiallyPaid,
				FinancialEstimationStatus::Paid,
			],
			$chain,
		);
	}

	// ───────── Mutual exclusiveness ─────────

	#[Test]
	public function open_and_closed_are_mutually_exclusive(): void
	{
		foreach (FinancialEstimationStatus::cases() as $case) {
			$this->assertFalse(
				$case->isOpen() && $case->isClosed(),
				"{$case->name} cannot be both open and closed"
			);
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance(): void
	{
		$inputs = ['open', 'not_paid', 'partial', 'completed', 'void', 'aberto', 'pago', 'garbage'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($inputs as $input) {
				FinancialEstimationStatus::normalize($input);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($inputs));
		$this->assertLessThan(1.0, $perCall, "normalize() averaged {$perCall}ms per call");
	}
}
