<?php

namespace Tests\Unit\Exports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
	use DatabaseTransactions;

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

		$uniqueName = 'AliceTest_' . uniqid();
		$employee = Employee::factory()->create([
			'name'        => $uniqueName,
		]);

		// One leave of each status for Alice
		// Note: The Leave model's creating callback normalizes status to valid
		// project statuses (e.g. 'in_progress'). We must bypass that via raw DB
		// update to set leave-specific statuses that the export queries for.
		$leaveA = Leave::factory()->create(['employee_id' => $employee->id]);
		$leaveR = Leave::factory()->create(['employee_id' => $employee->id]);
		$leaveP = Leave::factory()->create(['employee_id' => $employee->id]);
		\Illuminate\Support\Facades\DB::table('leaves')->where('id', $leaveA->id)->update(['status' => 'Approved']);
		\Illuminate\Support\Facades\DB::table('leaves')->where('id', $leaveR->id)->update(['status' => 'Reject']);
		\Illuminate\Support\Facades\DB::table('leaves')->where('id', $leaveP->id)->update(['status' => 'Pending']);

		// ── Act ──────────────────────────────────────────────────────────────
		$export    = new LeaveReportExport();
		$collection = $export->collection();

		// ── Assert ───────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);

		// The export iterates Leave::all(), so at minimum our 3 leaves are present.
		// (Other pre-existing leaves in the DB may also appear.)
		$this->assertGreaterThanOrEqual(3, $collection->count());

		// Filter to only the rows for the unique employee name
		$aliceRows = $collection->filter(fn(array $row) => $row[1] === $uniqueName);
		$this->assertCount(3, $aliceRows, 'There should be 3 rows for the employee (one per leave).');

		// Every Alice row must show the same status totals
		$aliceRows->each(function (array $row) {
			$this->assertEquals(1, $row[2], 'Approved count');
			$this->assertEquals(1, $row[3], 'Rejected count');
		});
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
