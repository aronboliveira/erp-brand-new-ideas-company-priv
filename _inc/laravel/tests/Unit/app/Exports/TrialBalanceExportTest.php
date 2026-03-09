<?php

namespace Tests\Unit\Exports;

use App\Exports\TrialBalanceExport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use ReflectionMethod;
use Tests\TestCase;

class TrialBalanceExportTest extends TestCase
{
	use DatabaseTransactions;

	/** Local copy of the headings so we do not reach into internals */
	private const EXPECTED_HEADINGS = ['Account Name', 'Account No', 'Debit', 'Credit'];

	/** Local copy of the column-width map */
	private const EXPECTED_COLUMN_WIDTHS = ['A' => 30, 'B' => 15, 'C' => 15, 'D' => 15];

	/**
	 ** @test
	 **
	 ** Without an authenticated user the exporter **must** short-circuit and
	 ** return an **empty** array, because `_checkLogin()` yields a redirect.
	 **/
	public function array_returns_empty_when_user_not_authenticated(): void
	{
		$export = new TrialBalanceExport([], '2025-01-01', '2025-01-31', 'ACME Inc.');
		$data  = $export->array();

		$this->assertIsArray($data, 'Export should always return an array.');
		$this->assertEmpty($data,  'Unauthenticated export should be empty.');
	}

	/**
	 ** @test
	 **
	 ** With an authenticated user the exporter **must** build a formatted
	 ** array that:
	 **  • inserts a blank line and a **type header** per account type  
	 **  • transforms each account to the correct column order  
	 **  • appends a **Total** row with the aggregated debit/credit values  
	 **/
	public function array_returns_well_formed_data_for_authenticated_user(): void
	{
		// Arrange – sample trial-balance data
		$user = User::factory()->create();
		$this->actingAs($user);

		$input = [
			'Assets' => [
				['name' => 'Cash', 'code' => '100', 'totalDebit' => 1_000, 'totalCredit' => 0],
				['name' => 'Bank', 'code' => '101', 'totalDebit' => 500,  'totalCredit' => 0],
			],
			'Liabilities' => [
				['name' => 'A/P',  'code' => '200', 'totalDebit' => 0,     'totalCredit' => 1_300],
			],
		];

		$export = new TrialBalanceExport($input, '2025-01-01', '2025-01-31', 'ACME Inc.');

		// Act
		$rows = $export->array();

		// Assert – overall structure
		$this->assertIsArray($rows);
		$this->assertNotEmpty($rows);

		// Each account-type contributes: 1 blank + 1 header + n accounts
		$expectedRows = 1                          // first blank
			+ 1 + 2                      // Assets header + 2 accounts
			+ 1                          // blank before Liabilities
			+ 1 + 1                      // Liabilities header + 1 account
			+ 1;                         // Total row
		$this->assertCount($expectedRows, $rows, 'Unexpected number of rows.');

		// Assert – first header lines
		$this->assertSame('', $rows[0]['Account Name'], 'Row 0 should be blank.');
		$this->assertSame('Assets', $rows[1]['Account Name']);

		// Assert – last row is “Total” with correct sums
		$last = end($rows);
		/** @var array $last */
		$this->assertSame('Total',  $last['Account Name']);
		$this->assertSame(1_500,    $last['Debit']);
		$this->assertSame(1_300,    $last['Credit']);
	}

	/**
	 ** @test
	 **
	 ** `headings()` must return the four spreadsheet column titles in the
	 ** expected order.
	 **/
	public function headings_match_expected_definition(): void
	{
		$export = new TrialBalanceExport([], '2025-01-01', '2025-01-31', 'ACME Inc.');

		$this->assertSame(
			self::EXPECTED_HEADINGS,
			$export->headings(),
			'Headings array does not match the expected values.'
		);
	}

	/**
	 ** @test
	 **
	 ** `startCell()` must always be the constant **A6** to keep headers
	 ** and metadata rows properly aligned.
	 **/
	public function start_cell_is_a6(): void
	{
		$export = new TrialBalanceExport([], '2025-01-01', '2025-01-31', 'ACME Inc.');
		$this->assertSame('A6', $export->startCell());
	}

	/**
	 ** @test
	 **
	 ** The exporter must expose **column-widths** so the sheet is readable.
	 **/
	public function column_widths_match_expected_definition(): void
	{
		$export = new TrialBalanceExport([], '2025-01-01', '2025-01-31', 'ACME Inc.');

		$this->assertSame(
			self::EXPECTED_COLUMN_WIDTHS,
			$export->columnWidths()
		);
	}

	/**
	 ** @test
	 **
	 ** `styles()` should bold the header row (row **6**, i.e. the first
	 ** data row).  We verify it runs without error and modifies the font
	 ** style.  A real worksheet instance is used to avoid mocks.
	 **/
	public function styles_bold_first_data_row(): void
	{
		$export      = new TrialBalanceExport([], '2025-01-01', '2025-01-31', 'ACME Inc.');
		$spreadsheet = new Spreadsheet();
		$sheet       = $spreadsheet->getActiveSheet();

		// Precondition – font is not bold
		$this->assertFalse($sheet->getStyle('A6')->getFont()->getBold());

		// Act
		$export->styles($sheet);

		// Assert
		$this->assertTrue($sheet->getStyle('A6')->getFont()->getBold());
	}

	/**
	 ** @test
	 **
	 ** The exporter **must** register an **AfterSheet** callback that will
	 ** apply final styling once the data is on the worksheet.
	 **/
	public function register_events_contains_aftersheet(): void
	{
		$export = new TrialBalanceExport([], '2025-01-01', '2025-01-31', 'ACME Inc.');
		$events = $export->registerEvents();

		$this->assertArrayHasKey(
			AfterSheet::class,
			$events,
			'AfterSheet event not registered.'
		);
		$this->assertIsCallable($events[AfterSheet::class]);
	}

	/**
	 ** @test
	 **
	 ** `columnLetter()` is an internal helper that must strip the numeric
	 ** row suffix from a cell reference and return only the column part.
	 ** We reach it via reflection so we do not alter the visibility.
	 **/
	public function column_letter_helper_extracts_letters(): void
	{
		$ref = new ReflectionMethod(TrialBalanceExport::class, 'columnLetter');
		$ref->setAccessible(true);

		$this->assertSame('A', $ref->invoke(null, 'A6'));
		$this->assertSame('BC', $ref->invoke(null, 'BC12'));
	}
}
