<?php
// tests/Unit/Exports/SalesReportExportTest.php

namespace Tests\Unit\Exports;

use App\Exports\SalesReportExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

class SalesReportExportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** For **Item** reports the constructor must transform the raw
	 ** rows into the expected keyed structure:
	 **   • Item Name
	 **   • Quantity Sold
	 **   • Amount
	 **   • Average Amount
	 **/
	public function item_report_formats_rows_correctly(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$raw = [
			['name' => 'Widget', 'invoice_count' => 3, 'price' => 150, 'avg_price' => 50],
			['name' => 'Gadget', 'invoice_count' => 2, 'price' => 80,  'avg_price' => 40],
		];

		$export = new SalesReportExport(
			$raw,
			'2025-05-01',
			'2025-05-31',
			'Demo Inc.',
			'Item'
		);

		$rows = $export->array();

		$this->assertCount(2, $rows);
		$this->assertSame([
			'Item Name'      => 'Widget',
			'Quantity Sold'  => 3,
			'Amount'         => 150,
			'Average Amount' => 50,
		], $rows[0]);
	}

	/**
	 ** @test
	 **
	 ** For **Customer** reports the constructor must transform the
	 ** data using:
	 **   • Customer Name
	 **   • Invoice Count
	 **   • Sales
	 **   • Sales With Tax  (price + total_tax)
	 **/
	public function customer_report_formats_rows_correctly(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$raw = [
			['name' => 'ACME Corp.', 'invoice_count' => 4, 'price' => 400, 'total_tax' => 40],
		];

		$export = new SalesReportExport(
			$raw,
			'2025-05-01',
			'2025-05-31',
			'Demo Inc.',
			'Customer'
		);

		$rows = $export->array();

		$this->assertSame([
			'Customer Name'  => 'ACME Corp.',
			'Invoice Count'  => 4,
			'Sales'          => 400,
			'Sales With Tax' => 440,
		], $rows[0]);
	}

	/**
	 ** @test
	 **
	 ** `headings()` must return the correct labels based on the
	 ** `reportName` constructor argument.
	 **/
	public function headings_change_according_to_report_type(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$itemExport = new SalesReportExport([], '2025-05-01', '2025-05-31', 'Demo', 'Item');
		$custExport = new SalesReportExport([], '2025-05-01', '2025-05-31', 'Demo', 'Customer');

		$this->assertSame(
			['Item Name', 'Quantity Sold', 'Amount', 'Average Amount'],
			$itemExport->headings()
		);

		$this->assertSame(
			['Customer Name', 'Invoice Count', 'Sales', 'Sales With Tax'],
			$custExport->headings()
		);
	}

	/**
	 ** @test
	 **
	 ** Helper methods **must** expose the class constants unchanged.
	 **/
	public function helper_returns_expected_values(): void
	{
		$export = new SalesReportExport([], '2025-05-01', '2025-05-31', 'Demo', 'Item');

		$this->assertSame('A6', $export->startCell());
		$this->assertSame(
			['A' => 30, 'B' => 15, 'C' => 15, 'D' => 20],
			$export->columnWidths()
		);
	}

	/**
	 ** @test
	 **
	 ** `registerEvents()` should register a single **AfterSheet**
	 ** listener that is a valid callable.
	 **/
	public function register_events_contains_callable_aftersheet(): void
	{
		$export = new SalesReportExport([], '2025-05-01', '2025-05-31', 'Demo', 'Item');
		$events = $export->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
