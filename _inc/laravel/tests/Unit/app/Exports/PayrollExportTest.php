<?php

namespace Tests\Unit\Exports;

use App\Exports\PayrollExport;
use App\Models\{Employee, Payslip, User};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

class PayrollExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** It **must** return only the payslips that:
	 **   • were created by the currently-authenticated user **and**
	 **   • belong to the current salary month (YYYY-MM).
	 **
	 ** The mapped result **must** contain the formatted / derived
	 ** columns:
	 **   • employeeId
	 **   • status
	 **   • employeeName
	 **   • salary
	 **   • netSalary
	 **   • month
	 **/
	public function collection_returns_current_users_current_month_rows(): void
	{
		// The export uses PHP's native `date('Y-m')`, so use the real current month
		$currentMonth = date('Y-m');
		$otherMonth   = date('Y-m', strtotime('-1 month'));

		$user   = User::factory()->create();
		$other  = User::factory()->create();

		Auth::login($user);

		$employee = Employee::factory()->create();

		// 2 payslips for $user in the current month
		Payslip::factory()->count(2)->create([
			'employee_id'    => $employee->id,
			'salary_month'   => $currentMonth,
			'status'         => 1,
		]);

		// 1 payslip for $user in a **different** month (should be ignored)
		Payslip::factory()->create([
			'employee_id'    => $employee->id,
			'salary_month'   => $otherMonth,
		]);

		// 1 payslip for a **different** user in the current month
		Auth::login($other);
		Payslip::factory()->create([
			'employee_id'    => $employee->id,
			'salary_month'   => $currentMonth,
		]);

		Auth::login($user);

		$export    = new PayrollExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(2, $collection); // only 2 valid rows

		$collection->each(function (array $row) use ($currentMonth) {
			// Required derived keys must exist
			foreach (
				[
					'employeeId',
					'status',
					'employeeName',
					'salary',
					'netSalary',
					'month'
				] as $key
			) {
				$this->assertArrayHasKey($key, $row, "Missing `{$key}` column.");
			}

			// Must be current month
			$this->assertSame($currentMonth, $row['month']);
		});
	}

	/**
	 ** @test
	 **
	 ** The `headings()` method must return the **exact** column
	 ** headings used by the Excel template (order is important).
	 **/
	public function headings_match_expected_definition(): void
	{
		$export  = new PayrollExport();
		$expected = [
			'Employee Id',
			'Status',
			'Employee Name',
			'Salary',
			'Net Salary',
			'Month',
		];

		$this->assertSame($expected, $export->headings());
	}

	/**
	 ** @test
	 **
	 ** `registerEvents()` must register a single **AfterSheet**
	 ** callback, and that handler must be a valid callable.
	 **/
	public function register_events_contains_aftersheet_callable(): void
	{
		$events = (new PayrollExport())->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
