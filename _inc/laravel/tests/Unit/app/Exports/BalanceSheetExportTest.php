<?php

namespace Tests\Unit\Exports;

use App\Exports\BalanceSheetExport;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Events\{AfterSheet, BeforeWriting};
use Tests\TestCase;

final class BalanceSheetExportTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The constructor should flatten the nested category data into a
	 ** sequential array of rows ready for Excel; it must also calculate
	 ** the grand-total “Total Liabilities & Equity” row (800 in our stub).
	 **/
	public function it_formats_nested_rows_into_excel_rows(): void
	{
		$export = new BalanceSheetExport(
			$this->sampleRows(),
			'2025-04-01',
			'2025-04-30',
			'ACME Inc.'
		);

		$rows = $export->array();

		// Last row should be the grand total for Liabilities & Equity = 500 + 300 = 800
		$last = end($rows);
		$this->assertSame('Total Liabilities & Equity', Arr::get($last, 'Account Name'));
		$this->assertSame(800, Arr::get($last, 'Total'));

		// “Assets” category header must exist somewhere in the flattened rows
		$this->assertTrue(
			(bool) array_filter($rows, fn ($r) => Arr::get($r, 'Account Name') === 'Assets'),
			'Assets category header was not found in flattened data'
		);
	}

	/**
	 ** @test
	 **
	 ** columnWidths() must return the constant mapping so the worksheet
	 ** receives the exact user-defined widths.
	 **/
	public function it_returns_the_expected_column_widths(): void
	{
		$expected = [
			'A' => 30, 'B' => 15, 'C' => 15,
			'D' => 15, 'E' => 15, 'F' => 15,
		];

		$export = new BalanceSheetExport([], '2025-04-01', '2025-04-30', 'ACME Inc.');
		$this->assertSame($expected, $export->columnWidths());
	}

	/**
	 ** @test
	 **
	 ** startCell() should always be **A5**, leaving the first four rows
	 ** available for report metadata (title, print-date, date-range).
	 **/
	public function it_starts_writing_at_cell_a5(): void
	{
		$export = new BalanceSheetExport([], '2025-04-01', '2025-04-30', 'ACME Inc.');
		$this->assertSame('A5', $export->startCell());
	}

	/**
	 ** @test
	 **
	 ** The export headings are fixed: Account | Account No | Total.
	 **/
	public function it_defines_the_correct_headings(): void
	{
		$export = new BalanceSheetExport([], '2025-04-01', '2025-04-30', 'ACME Inc.');
		$this->assertSame(['Account', 'Account No', 'Total'], $export->headings());
	}

	/**
	 ** @test
	 **
	 ** registerEvents() must wire both BeforeWriting and AfterSheet so that
	 ** style and metadata callbacks execute when Laravel-Excel generates the file.
	 **/
	public function it_registers_before_writing_and_after_sheet_events(): void
	{
		$export = new BalanceSheetExport([], '2025-04-01', '2025-04-30', 'ACME Inc.');
		$events = $export->registerEvents();

		$this->assertArrayHasKey(BeforeWriting::class, $events);
		$this->assertArrayHasKey(AfterSheet::class, $events);
	}

	/**
	 * Build the same nested structure that the controller
	 * passes to the export - categories → sub-types → accounts.
	 */
	private function sampleRows(): array
	{
		return [
			'Assets'      => [
				[
					'subType' => 'Current Assets',
					'account' => [
						['account_name' => 'Cash', 'account_no' => '101', 'netAmount' => 1000],
						['account_name' => 'Bank', 'account_no' => '102', 'netAmount' => null],
					],
				],
			],
			'Liabilities' => [
				[
					'subType' => 'Current Liabilities',
					'account' => [
						['account_name' => 'Accounts Payable', 'account_no' => '201', 'netAmount' => 500],
					],
				],
			],
			'Equity'      => [
				[
					'subType' => 'Shareholders Equity',
					'account' => [
						['account_name' => 'Capital', 'account_no' => '301', 'netAmount' => 300],
					],
				],
			],
		];
	}
}
