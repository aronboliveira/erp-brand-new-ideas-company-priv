<?php

namespace Tests\Unit\Exports;

use App\Exports\PayslipExport;
use App\Models\Payslip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery as m;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

final class PayslipExportTest extends TestCase
{
	/**
	 ** @test
	 *
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
	 *
	 ** collection() should:
	 **  • Filter payslips by creator-id and salary_month
	 **  • Format monetary values with User::priceFormat
	 **  • Resolve relations (employee) and map every required column
	 **  • Translate status 0/1 → UnPaid/Paid
	 **/
	public function it_builds_the_expected_collection(): void
	{
		// ── Arrange ───────────────────────────────────────────────────────────
		/** Fake authenticated user object */
		$user = new class
		{
			public int $id = 1;
			public function creatorId(): string|int
			{
				return $this->id;
			}
			public function priceFormat(float $v): string
			{
				return number_format($v, 2);
			}
		};

		Auth::shouldReceive('check')->andReturnTrue();
		Auth::shouldReceive('user')->andReturn($user);

		// Fake employee record
		$employee = new class
		{
			public string  $employee_id         = 'E001';
			public string  $name                = 'Jane Doe';
			public string  $account_holder_name = 'Jane Doe';
			public string  $account_number      = '123456';
			public string  $bank_name           = 'Banco do Brasil';
			public string  $bank_identifier_code = 'BRASBRRJ';
			public string  $branch_location     = 'São Paulo';
			public string  $tax_payer_id        = '111222333';
			public function employeeIdFormat($raw): string
			{
				return "EMP-{$raw}";
			}
		};

		// Stub Payslip model chain
		Payslip::shouldReceive('where')
			->once()->with('created_by', $user?->creatorId())->andReturnSelf();
		Payslip::shouldReceive('where')
			->once()->with('salary_month', '2025-04')->andReturnSelf();
		Payslip::shouldReceive('get')
			->once()->andReturn(collect([
				// minimal stdClass is enough for the export logic
				(object) [
					'employees'     => $employee,
					'basic_salary'  => 1000,
					'net_payble'    => 800,
					'status'        => 1,
				],
			]));

		$request = (object) ['filterMonth' => '04', 'filterYear' => '2025'];

		// ── Act ───────────────────────────────────────────────────────────────
		$export    = new PayslipExport($request);
		$collection = $export->collection();

		// ── Assert ────────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first();

		$this->assertSame('EMP-E001', $row['empId']);
		$this->assertSame('Jane Doe', $row['name']);
		$this->assertSame(number_format(1000, 2), $row['salary']);
		$this->assertSame(number_format(800, 2),  $row['netSalary']);
		$this->assertSame('Paid',      $row['status']);
		$this->assertSame('Banco do Brasil', $row['bankName']);
	}

	/**
	 ** @test
	 *
	 ** registerEvents() must expose an AfterSheet callback so the
	 ** header styling logic is executed when Laravel-Excel writes the file.
	 **/
	public function it_registers_an_after_sheet_event(): void
	{
		$export = new PayslipExport((object) []);
		$this->assertArrayHasKey(AfterSheet::class, $export->registerEvents());
	}

	/** Clean up Mockery expectations. */
	protected function tearDown(): void
	{
		m::close();
		parent::tearDown();
	}
}
