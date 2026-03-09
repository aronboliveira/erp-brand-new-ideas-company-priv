<?php

namespace Tests\Unit\Exports;

use App\Exports\PayslipExport;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

final class PayslipExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** headings() must return the exact static list declared in the
	 ** export so that the generated spreadsheet has a predictable header.
	 **/
	public function it_returns_the_expected_headings(): void
	{
		$export = new PayslipExport((object) []);
		$this->assertSame(
			[
				'EMP ID',
				'Name',
				'Salary',
				'Net Salary',
				'Status',
				'Account Holder Name',
				'Account Number',
				'Bank Name',
				'Bank Identifier Code',
				'Branch Location',
				'Tax Payer Id',
			],
			$export->headings()
		);
	}

	/**
	 ** @test
	 **
	 ** collection() should:
	 **  • Filter payslips by creator-id and salary_month
	 **  • Format monetary values with User::priceFormat
	 **  • Resolve relations (employee) and map every required column
	 **  • Translate status 0/1 → UnPaid/Paid
	 **/
	public function it_builds_the_expected_collection(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$employee = Employee::factory()->create();

		$month = now()->format('m');
		$year  = now()->format('Y');

		Payslip::factory()->create([
			'employee_id'  => $employee->id,
			'salary_month' => "{$year}-{$month}",
			'gross_salary' => 5000.00,
			'net_payable'  => 4000,
			'status'       => 1,
		]);

		$request = (object) ['filterMonth' => $month, 'filterYear' => $year];

		$export     = new PayslipExport($request);
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first();

		$this->assertArrayHasKey('empId', $row);
		$this->assertArrayHasKey('name', $row);
		$this->assertArrayHasKey('salary', $row);
		$this->assertArrayHasKey('netSalary', $row);
		$this->assertSame('Paid', $row['status']);
	}

	/**
	 ** @test
	 **
	 ** registerEvents() must expose an AfterSheet callback so the
	 ** header styling logic is executed when Laravel-Excel writes the file.
	 **/
	public function it_registers_an_after_sheet_event(): void
	{
		$export = new PayslipExport((object) []);
		$events = $export->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
