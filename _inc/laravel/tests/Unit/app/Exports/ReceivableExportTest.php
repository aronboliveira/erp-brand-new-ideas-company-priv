<?php

namespace Tests\Unit\Exports;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Exports\ReceivableExport;

/**
 ** Test-suite for the `ReceivableExport` class.
 **
 ** Ensures that:
 **   • Headings, start cell, and column widths match the spec.  
 **   • The constructor correctly transforms raw invoice data into the array
 **     used by `array()`, including calculated balances and the total row.  
 **   • An `AfterSheet` listener is always registered so layout & styling are
 **     applied when the sheet renders.  
 **/
class ReceivableExportTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** It must expose the exact column headings required by consumers.
	 **/
	public function headings_are_correct(): void
	{
		$export = new ReceivableExport([], '2025-05-01', '2025-05-31', 'ACME Inc.');

		$this->assertSame(
			['Customer Name', 'Invoice Balance', 'Available Credits', 'Balance'],
			$export->headings()
		);
	}

	/**
	 ** @test
	 **
	 ** The `startCell()` method should lock table headers at `A6` so that the
	 ** preceding cells can hold the report title, print date and range.
	 **/
	public function start_cell_is_a6(): void
	{
		$export = new ReceivableExport([], '2025-05-01', '2025-05-31', 'ACME Inc.');

		$this->assertSame('A6', $export->startCell());
	}

	/**
	 ** @test
	 **
	 ** Column widths must respect the predefined mapping so that text fits
	 ** nicely inside the generated spreadsheet.
	 **/
	public function column_widths_match_spec(): void
	{
		$export = new ReceivableExport([], '2025-05-01', '2025-05-31', 'ACME Inc.');

		$expected = ['A' => 30, 'B' => 20, 'C' => 20, 'D' => 20];

		$this->assertSame($expected, $export->columnWidths());
	}

	/**
	 ** @test
	 **
	 ** Given a list of invoices, the export must calculate the correct
	 ** *Invoice Balance*, subtract available credits to compute *Balance*, and
	 ** append a *Total* row that sums all balances.
	 **/
	public function array_transforms_and_totals_invoice_data(): void
	{
		// ── Arrange raw data ────────────────────────────────────────────────
		$raw = [
			[
				'name'         => 'Alice',
				'total_price'  => 100,
				'pay_price'    => 30,
				'credit_price' => 10,
			],
			[
				'name'         => 'Bob',
				'total_price'  => 200,
				'pay_price'    => 60,
				// credit_price intentionally omitted to test default 0
			],
		];

		// ── Act ─────────────────────────────────────────────────────────────
		$export  = new ReceivableExport($raw, '2025-05-01', '2025-05-31', 'ACME Inc.');
		$payload = $export->array();
		$expected = [
			// Alice
			[
				'Customer Name'     => 'Alice',
				'Invoice Balance'   => 70,  // 100 – 30
				'Available Credits' => 10,
				'Balance'           => 60,  // 70 – 10
			],
			// Bob
			[
				'Customer Name'     => 'Bob',
				'Invoice Balance'   => 140, // 200 – 60
				'Available Credits' => 0,
				'Balance'           => 140,
			],
			// Total row
			[
				'Customer Name'     => 'Total',
				'Invoice Balance'   => '',
				'Available Credits' => '',
				'Balance'           => 200, // 60 + 140
			],
		];

		// ── Assert ──────────────────────────────────────────────────────────
		$this->assertSame($expected, $payload);
	}

	/**
	 ** @test
	 **
	 ** The export must always register an `AfterSheet` event so that formatting
	 ** and borders are applied once data is written to the sheet.
	 **/
	public function register_events_contains_after_sheet_listener(): void
	{
		$export = new ReceivableExport([], '2025-05-01', '2025-05-31', 'ACME Inc.');
		$events = $export->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
