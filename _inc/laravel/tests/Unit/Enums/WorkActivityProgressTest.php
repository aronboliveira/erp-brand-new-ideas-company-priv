<?php

namespace Tests\Unit\Enums;

use App\Enums\WorkActivityProgress;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(WorkActivityProgress::class)]
#[Group('enums')]
#[Group('work-activity-progress')]
class WorkActivityProgressTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_exactly_four_cases(): void
	{
		$this->assertCount(4, WorkActivityProgress::cases());
	}

	#[Test]
	public function each_case_has_expected_value(): void
	{
		$expected = [
			'Pending'    => 'pending',
			'Started'    => 'started',
			'Completed'  => 'completed',
			'Terminated' => 'terminated',
		];
		foreach (WorkActivityProgress::cases() as $case) {
			$this->assertSame($expected[$case->name], $case->value);
		}
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (WorkActivityProgress::cases() as $case) {
			$this->assertSame($case, WorkActivityProgress::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_pending_for_null(): void
	{
		$this->assertSame(WorkActivityProgress::Pending, WorkActivityProgress::normalize(null));
	}

	public static function normalizeProvider(): array
	{
		return [
			['pending', WorkActivityProgress::Pending],
			['started', WorkActivityProgress::Started],
			['completed', WorkActivityProgress::Completed],
			['terminated', WorkActivityProgress::Terminated],
			// Portuguese
			['pendente', WorkActivityProgress::Pending],
			['iniciado', WorkActivityProgress::Started],
			['concluido', WorkActivityProgress::Completed],
			['terminado', WorkActivityProgress::Terminated],
			// Spanish
			['pendiente', WorkActivityProgress::Pending],
			['iniciada', WorkActivityProgress::Started],
			['concluida', WorkActivityProgress::Completed],
			['terminada', WorkActivityProgress::Terminated],
			// French
			['enattente', WorkActivityProgress::Pending],
			['commence', WorkActivityProgress::Started],
			['termine', WorkActivityProgress::Completed],
			['interrompu', WorkActivityProgress::Terminated],
			// German
			['wartend', WorkActivityProgress::Pending],
			['begonnen', WorkActivityProgress::Started],
			['abgeschlossen', WorkActivityProgress::Completed],
			['beendet', WorkActivityProgress::Terminated],
		];
	}

	#[Test]
	#[DataProvider('normalizeProvider')]
	public function normalize_resolves_multilingual(string $input, WorkActivityProgress $expected): void
	{
		$this->assertSame($expected, WorkActivityProgress::normalize($input));
	}

	#[Test]
	public function normalize_defaults_to_pending_for_unknown(): void
	{
		$this->assertSame(WorkActivityProgress::Pending, WorkActivityProgress::normalize('garbage_xyz'));
	}

	// ───────── values() ─────────

	#[Test]
	public function values_returns_all_backing_strings(): void
	{
		$values = WorkActivityProgress::values();
		$this->assertCount(4, $values);
		$this->assertEqualsCanonicalizing(['pending', 'started', 'completed', 'terminated'], $values);
	}

	// ───────── Boolean classification ─────────

	public static function booleanProvider(): array
	{
		return [
			// [case, isActive, isCompleted, isPending, isTerminated, isFinished, canBeStarted, canBeCompleted, canBeTerminated]
			[WorkActivityProgress::Pending, false, false, true, false, false, true, false, true],
			[WorkActivityProgress::Started, true, false, false, false, false, false, true, true],
			[WorkActivityProgress::Completed, false, true, false, false, true, false, false, false],
			[WorkActivityProgress::Terminated, false, false, false, true, true, false, false, false],
		];
	}

	#[Test]
	#[DataProvider('booleanProvider')]
	public function boolean_classification_methods(
		WorkActivityProgress $case,
		bool $isActive,
		bool $isCompleted,
		bool $isPending,
		bool $isTerminated,
		bool $isFinished,
		bool $canBeStarted,
		bool $canBeCompleted,
		bool $canBeTerminated,
	): void {
		$this->assertSame($isActive, $case->isActive(), "{$case->name}->isActive()");
		$this->assertSame($isCompleted, $case->isCompleted(), "{$case->name}->isCompleted()");
		$this->assertSame($isPending, $case->isPending(), "{$case->name}->isPending()");
		$this->assertSame($isTerminated, $case->isTerminated(), "{$case->name}->isTerminated()");
		$this->assertSame($isFinished, $case->isFinished(), "{$case->name}->isFinished()");
		$this->assertSame($canBeStarted, $case->canBeStarted(), "{$case->name}->canBeStarted()");
		$this->assertSame($canBeCompleted, $case->canBeCompleted(), "{$case->name}->canBeCompleted()");
		$this->assertSame($canBeTerminated, $case->canBeTerminated(), "{$case->name}->canBeTerminated()");
	}

	// ───────── State Machine: getNextStatus / getPreviousStatus ─────────

	#[Test]
	public function next_status_chain(): void
	{
		$this->assertSame(WorkActivityProgress::Started, WorkActivityProgress::Pending->getNextStatus());
		$this->assertSame(WorkActivityProgress::Completed, WorkActivityProgress::Started->getNextStatus());
		$this->assertNull(WorkActivityProgress::Completed->getNextStatus());
		$this->assertNull(WorkActivityProgress::Terminated->getNextStatus());
	}

	#[Test]
	public function previous_status_chain(): void
	{
		$this->assertNull(WorkActivityProgress::Pending->getPreviousStatus());
		$this->assertSame(WorkActivityProgress::Pending, WorkActivityProgress::Started->getPreviousStatus());
		$this->assertSame(WorkActivityProgress::Started, WorkActivityProgress::Completed->getPreviousStatus());
		$this->assertSame(WorkActivityProgress::Started, WorkActivityProgress::Terminated->getPreviousStatus());
	}

	#[Test]
	public function full_forward_chain_pending_to_completed(): void
	{
		$chain = [];
		$current = WorkActivityProgress::Pending;
		while ($current !== null) {
			$chain[] = $current;
			$current = $current->getNextStatus();
		}
		$this->assertSame(
			[WorkActivityProgress::Pending, WorkActivityProgress::Started, WorkActivityProgress::Completed],
			$chain,
		);
	}

	// ───────── getAllowedTransitions / canTransitionTo ─────────

	#[Test]
	public function pending_transitions_to_started_or_terminated(): void
	{
		$transitions = WorkActivityProgress::Pending->getAllowedTransitions();
		$this->assertCount(2, $transitions);
		$this->assertContains(WorkActivityProgress::Started, $transitions);
		$this->assertContains(WorkActivityProgress::Terminated, $transitions);
	}

	#[Test]
	public function started_transitions_to_completed_or_terminated(): void
	{
		$transitions = WorkActivityProgress::Started->getAllowedTransitions();
		$this->assertCount(2, $transitions);
		$this->assertContains(WorkActivityProgress::Completed, $transitions);
		$this->assertContains(WorkActivityProgress::Terminated, $transitions);
	}

	#[Test]
	public function final_statuses_have_no_transitions(): void
	{
		$this->assertEmpty(WorkActivityProgress::Completed->getAllowedTransitions());
		$this->assertEmpty(WorkActivityProgress::Terminated->getAllowedTransitions());
	}

	#[Test]
	public function can_transition_to_is_consistent(): void
	{
		foreach (WorkActivityProgress::cases() as $from) {
			$allowed = $from->getAllowedTransitions();
			foreach (WorkActivityProgress::cases() as $to) {
				$this->assertSame(
					in_array($to, $allowed, true),
					$from->canTransitionTo($to),
					"{$from->name} -> {$to->name}"
				);
			}
		}
	}

	// ───────── getCompletionPercentage() ─────────

	#[Test]
	public function completion_percentage_values(): void
	{
		$this->assertSame(0, WorkActivityProgress::Pending->getCompletionPercentage());
		$this->assertSame(50, WorkActivityProgress::Started->getCompletionPercentage());
		$this->assertSame(100, WorkActivityProgress::Completed->getCompletionPercentage());
		$this->assertSame(100, WorkActivityProgress::Terminated->getCompletionPercentage());
	}

	#[Test]
	public function completion_percentage_range_0_to_100(): void
	{
		foreach (WorkActivityProgress::cases() as $case) {
			$pct = $case->getCompletionPercentage();
			$this->assertGreaterThanOrEqual(0, $pct);
			$this->assertLessThanOrEqual(100, $pct);
		}
	}

	// ───────── Auxiliary methods ─────────

	#[Test]
	public function get_action_verb_returns_non_empty_string(): void
	{
		foreach (WorkActivityProgress::cases() as $case) {
			$this->assertIsString($case->getActionVerb());
			$this->assertNotEmpty($case->getActionVerb());
		}
	}

	#[Test]
	public function get_timeline_estimate_returns_non_empty_string(): void
	{
		foreach (WorkActivityProgress::cases() as $case) {
			$this->assertIsString($case->getTimelineEstimate());
			$this->assertNotEmpty($case->getTimelineEstimate());
		}
	}

	#[Test]
	public function get_notification_type_returns_non_empty_string(): void
	{
		foreach (WorkActivityProgress::cases() as $case) {
			$this->assertIsString($case->getNotificationType());
			$this->assertNotEmpty($case->getNotificationType());
		}
	}

	#[Test]
	public function get_sort_order_unique_across_cases(): void
	{
		$orders = array_map(fn(WorkActivityProgress $c) => $c->getSortOrder(), WorkActivityProgress::cases());
		$this->assertCount(count($orders), array_unique($orders));
	}

	// ───────── Static status groups ─────────

	#[Test]
	public function in_progress_statuses(): void
	{
		$this->assertSame([WorkActivityProgress::Started], WorkActivityProgress::getInProgressStatuses());
	}

	#[Test]
	public function not_started_statuses(): void
	{
		$this->assertSame([WorkActivityProgress::Pending], WorkActivityProgress::getNotStartedStatuses());
	}

	#[Test]
	public function final_statuses(): void
	{
		$final = WorkActivityProgress::getFinalStatuses();
		$this->assertCount(2, $final);
		$this->assertContains(WorkActivityProgress::Completed, $final);
		$this->assertContains(WorkActivityProgress::Terminated, $final);
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance(): void
	{
		$inputs = ['pending', 'started', 'pendente', 'begonnen', 'garbage'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($inputs as $input) {
				WorkActivityProgress::normalize($input);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($inputs));
		$this->assertLessThan(1.0, $perCall);
	}
}
