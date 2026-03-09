<?php

namespace Tests\Unit\Enums;

use App\Enums\CaseStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CaseStatus::class)]
#[Group('enums')]
#[Group('case-status')]
class CaseStatusTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_exactly_twenty_five_cases(): void
	{
		$this->assertCount(25, CaseStatus::cases());
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (CaseStatus::cases() as $case) {
			$this->assertSame($case, CaseStatus::normalize($case));
		}
	}

	#[Test]
	public function normalize_returns_null_for_null(): void
	{
		$this->assertNull(CaseStatus::normalize(null));
	}

	#[Test]
	public function normalize_returns_null_for_unknown(): void
	{
		$this->assertNull(CaseStatus::normalize('totally_bogus_xyz'));
	}

	public static function normalizeProvider(): array
	{
		return [
			// Direct values
			['new', CaseStatus::New],
			['open', CaseStatus::Open],
			['inprogress', CaseStatus::InProgress],
			['onhold', CaseStatus::OnHold],

			// Aliases
			['received', CaseStatus::New],
			['created', CaseStatus::New],
			['active', CaseStatus::Open],
			['assigned', CaseStatus::Open],
			['progress', CaseStatus::InProgress],
			['working', CaseStatus::InProgress],
			['hold', CaseStatus::OnHold],
			['paused', CaseStatus::OnHold],

			// Waiting aliases
			['waitingcustomer', CaseStatus::WaitingCustomer],
			['awaitingcustomer', CaseStatus::WaitingCustomer],
			['waitingvendor', CaseStatus::WaitingVendor],
			['waitingthirdparty', CaseStatus::WaitingThirdParty],

			// Resolution aliases
			['escalated', CaseStatus::Escalated],
			['resolved', CaseStatus::Resolved],
			['solved', CaseStatus::Resolved],
			['closed', CaseStatus::Closed],
			['finished', CaseStatus::Closed],
			['reopened', CaseStatus::Reopened],
			['cancelled', CaseStatus::Cancelled],
			['canceled', CaseStatus::Cancelled],
			['duplicate', CaseStatus::Duplicate],
			['archived', CaseStatus::Archived],
			['deleted', CaseStatus::Deleted],

			// Additional aliases
			['underreview', CaseStatus::UnderReview],
			['reviewing', CaseStatus::UnderReview],
			['pendingapproval', CaseStatus::PendingApproval],
			['scheduled', CaseStatus::Scheduled],
			['indevelopment', CaseStatus::InDevelopment],
			['testing', CaseStatus::Testing],
			['deferred', CaseStatus::Deferred],
			['postponed', CaseStatus::Deferred],
			['merged', CaseStatus::Merged],
			['awaitingfeedback', CaseStatus::AwaitingFeedback],
			['failed', CaseStatus::Failed],
			['blocked', CaseStatus::Blocked],
		];
	}

	#[Test]
	#[DataProvider('normalizeProvider')]
	public function normalize_resolves_correctly(string $input, CaseStatus $expected): void
	{
		$this->assertSame($expected, CaseStatus::normalize($input));
	}

	#[Test]
	public function normalize_strips_non_alpha_and_is_case_insensitive(): void
	{
		$this->assertSame(CaseStatus::InProgress, CaseStatus::normalize('IN_PROGRESS'));
		$this->assertSame(CaseStatus::OnHold, CaseStatus::normalize('On-Hold'));
		$this->assertSame(CaseStatus::WaitingCustomer, CaseStatus::normalize('  Waiting Customer  '));
	}

	// ───────── Boolean classification ─────────

	#[Test]
	public function is_active_covers_all_working_statuses(): void
	{
		$active = array_filter(CaseStatus::cases(), fn(CaseStatus $c) => $c->isActive());
		$activeNames = array_map(fn($c) => $c->name, array_values($active));
		$expectedActive = [
			'New',
			'Open',
			'InProgress',
			'OnHold',
			'WaitingCustomer',
			'WaitingVendor',
			'WaitingThirdParty',
			'Escalated',
			'Reopened',
			'UnderReview',
			'PendingApproval',
			'Scheduled',
			'InDevelopment',
			'Testing',
			'AwaitingFeedback',
			'Blocked',
		];
		$this->assertEqualsCanonicalizing($expectedActive, $activeNames);
	}

	#[Test]
	public function is_resolved_covers_resolution_statuses(): void
	{
		$resolved = array_filter(CaseStatus::cases(), fn(CaseStatus $c) => $c->isResolved());
		$resolvedNames = array_map(fn($c) => $c->name, array_values($resolved));
		$this->assertEqualsCanonicalizing(['Resolved', 'Closed', 'Merged'], $resolvedNames);
	}

	#[Test]
	public function is_terminal_covers_final_statuses(): void
	{
		$terminal = array_filter(CaseStatus::cases(), fn(CaseStatus $c) => $c->isTerminal());
		$terminalNames = array_map(fn($c) => $c->name, array_values($terminal));
		$this->assertEqualsCanonicalizing(['Closed', 'Cancelled', 'Duplicate', 'Archived', 'Deleted'], $terminalNames);
	}

	#[Test]
	public function is_waiting_covers_external_input_statuses(): void
	{
		$waiting = array_filter(CaseStatus::cases(), fn(CaseStatus $c) => $c->isWaiting());
		$waitingNames = array_map(fn($c) => $c->name, array_values($waiting));
		$expected = ['OnHold', 'WaitingCustomer', 'WaitingVendor', 'WaitingThirdParty', 'PendingApproval', 'AwaitingFeedback'];
		$this->assertEqualsCanonicalizing($expected, $waitingNames);
	}

	// ───────── canReopen() ─────────

	#[Test]
	public function can_reopen_correct_statuses(): void
	{
		$reopenable = array_filter(CaseStatus::cases(), fn(CaseStatus $c) => $c->canReopen());
		$names = array_map(fn($c) => $c->name, array_values($reopenable));
		$this->assertEqualsCanonicalizing(['Resolved', 'Closed', 'Cancelled', 'Archived'], $names);
	}

	// ───────── getCategory() ─────────

	#[Test]
	public function get_category_returns_valid_categories(): void
	{
		$validCategories = ['initial', 'in_progress', 'waiting', 'planning', 'problem', 'resolved', 'reopened', 'terminal', 'other'];
		foreach (CaseStatus::cases() as $case) {
			$category = $case->getCategory();
			$this->assertContains($category, $validCategories, "Invalid category '{$category}' for {$case->name}");
		}
	}

	#[Test]
	public function category_initial_contains_new_and_open(): void
	{
		$this->assertSame('initial', CaseStatus::New->getCategory());
		$this->assertSame('initial', CaseStatus::Open->getCategory());
	}

	#[Test]
	public function category_in_progress_contains_work_statuses(): void
	{
		$this->assertSame('in_progress', CaseStatus::InProgress->getCategory());
		$this->assertSame('in_progress', CaseStatus::InDevelopment->getCategory());
		$this->assertSame('in_progress', CaseStatus::Testing->getCategory());
	}

	#[Test]
	public function category_problem_contains_escalated_blocked_failed(): void
	{
		$this->assertSame('problem', CaseStatus::Escalated->getCategory());
		$this->assertSame('problem', CaseStatus::Blocked->getCategory());
		$this->assertSame('problem', CaseStatus::Failed->getCategory());
	}

	// ───────── getNextPossibleStatuses() ─────────

	#[Test]
	public function archived_and_deleted_have_no_next_statuses(): void
	{
		$this->assertEmpty(CaseStatus::Archived->getNextPossibleStatuses());
		$this->assertEmpty(CaseStatus::Deleted->getNextPossibleStatuses());
	}

	#[Test]
	public function new_can_move_to_open_inprogress_cancelled(): void
	{
		$next = CaseStatus::New->getNextPossibleStatuses();
		$this->assertContains(CaseStatus::Open, $next);
		$this->assertContains(CaseStatus::InProgress, $next);
		$this->assertContains(CaseStatus::Cancelled, $next);
	}

	#[Test]
	public function resolved_can_move_to_closed_or_reopened(): void
	{
		$next = CaseStatus::Resolved->getNextPossibleStatuses();
		$this->assertContains(CaseStatus::Closed, $next);
		$this->assertContains(CaseStatus::Reopened, $next);
	}

	#[Test]
	public function next_possible_statuses_never_contain_self(): void
	{
		foreach (CaseStatus::cases() as $case) {
			$next = $case->getNextPossibleStatuses();
			$this->assertNotContains($case, $next, "{$case->name} should not transition to itself");
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function normalize_performance(): void
	{
		$inputs = ['new', 'open', 'inprogress', 'resolved', 'blocked', 'garbage_xyz', 'waitingcustomer'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($inputs as $input) {
				CaseStatus::normalize($input);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($inputs));
		$this->assertLessThan(1.0, $perCall);
	}

	#[Test]
	public function classification_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach (CaseStatus::cases() as $case) {
				$case->isActive();
				$case->isResolved();
				$case->isTerminal();
				$case->isWaiting();
				$case->getCategory();
			}
		}
		$perCase = (hrtime(true) - $start) / 1e6 / (1000 * count(CaseStatus::cases()));
		$this->assertLessThan(1.0, $perCase);
	}
}
