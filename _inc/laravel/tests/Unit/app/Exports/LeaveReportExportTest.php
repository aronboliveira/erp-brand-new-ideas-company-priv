<?php

namespace Tests\Unit\Exports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Exports\LeaveReportExport;
use App\Models\{User, Employee, Leave};
use Maatwebsite\Excel\Events\AfterSheet;

/**
 ** Test-suite for the `LeaveReportExport` class.
 **
 ** These tests guarantee that:
 **   • The headings are exactly as defined in the export.  
 **   • The `collection()` method returns correctly formatted rows **only** for
 **     the authenticated user’s employees, with accurate status counts.  
 **   • An `AfterSheet` event listener is always registered to apply styling.  
 **/
class LeaveReportExportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** It should return the precise column headings expected by downstream
	 ** consumers (spreadsheets, BI tools, etc.).
	 **/
	public function headings_are_correct(): void
	{
		$export = new LeaveReportExport();

		$this->assertSame(
			['Employee ID', 'Employee', 'Approved Leaves', 'Rejected Leaves', 'Pending Leaves'],
			$export->headings()
		);
	}

	/**
	 ** @test
	 **
	 ** Given an authenticated user with employees and mixed-status leaves,
	 ** the export must return a `Collection` whose rows show accurate totals
	 ** for each status and employee, and **must not** include data from other
	 ** users.
	 **/
	public function collection_returns_rows_only_for_authenticated_user(): void
	{
		// ── Arrange ──────────────────────────────────────────────────────────
		$user = User::factory()->create();
		Auth::login($user);

		$employee = Employee::factory()->create([
			'employee_id' => 123,
			'name'        => 'Alice',
			'created_by'  => $user?->id,
		]);

		// One leave of each status for Alice
		Leave::factory()->create(['employee_id' => $employee->id, 'status' => 'Approved']);
		Leave::factory()->create(['employee_id' => $employee->id, 'status' => 'Reject']);
		Leave::factory()->create(['employee_id' => $employee->id, 'status' => 'Pending']);

		// A foreign employee + leave that must be ignored
		$foreignEmployee = Employee::factory()->create();
		Leave::factory()->create(['employee_id' => $foreignEmployee->id, 'status' => 'Approved']);

		// ── Act ──────────────────────────────────────────────────────────────
		$export    = new LeaveReportExport();
		$collection = $export->collection();

		// ── Assert ───────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		// There are three leaves for Alice; the export builds one row per leave
		$this->assertCount(3, $collection);

		// Every row for Alice must show the same status totals
		$expected = [
			User::employeeIdFormat($employee->employee_id),
			'Alice',
			'1', // approved
			'1', // rejected
			'1', // pending
		];

		$collection->each(
			fn (array $row) => $this->assertSame($expected, $row)
		);
	}

	/**
	 ** @test
	 **
	 ** The `registerEvents()` method must include an `AfterSheet` listener so
	 ** that styling logic always runs after the sheet is generated.
	 **/
	public function register_events_contains_after_sheet_listener(): void
	{
		$export = new LeaveReportExport();
		$events = $export->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
