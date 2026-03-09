<?php

namespace Tests\Unit\Exports;

use App\Exports\ProductStockExport;
use App\Models\StockReport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

class ProductStockExportTest extends TestCase
{
	use DatabaseTransactions;

	/** Local copy of the expected column headers */
	private const EXPECTED_HEADINGS = [
		'Stock Id',
		'Product Name',
		'Quantity',
		'Type',
		'Description',
		'Date'
	];

	/**
	 ** @test
	 **
	 ** If **no** user is logged-in the exporter must short-circuit and
	 ** return an **empty** `Collection`.
	 **/
	public function collection_returns_redirect_when_user_not_authenticated(): void
	{
		$export = new ProductStockExport();
		$result = $export->collection();

		$this->assertInstanceOf(
			Collection::class,
			$result,
			'Unauthenticated requests should return an empty Collection.'
		);
		$this->assertTrue($result->isEmpty(), 'Collection should be empty when unauthenticated.');
	}

	/**
	 ** @test
	 **
	 ** When a user **is** authenticated the exporter must gather their
	 ** `StockReport` rows, strip the _unset_ fields, transform
	 ** `product_id` into a product **name**, and yield the data as a
	 ** `Collection`.
	 **
	 ** We verify:
	 **  • correct row-count  
	 **  • the absence of a stripped field (`type_id`)  
	 **  • the presence of the transformed `product_id` key  
	 **/
	public function collection_returns_populated_collection_for_authenticated_user(): void
	{
		// Arrange – a creator with two stock reports
		$user = User::factory()->create();
		$this->actingAs($user);

		StockReport::factory()->count(2)->create([
			'quantity'   => 5,
			'type'       => 'inventory',
		]);

		$export    = new ProductStockExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);

		/** @var \Illuminate\Support\Collection $collection */ // <-- static-analysis hint
		$this->assertCount(2, $collection, 'Row count mismatch.');

		/** @var array|null $firstRow */                       // <-- static-analysis hint
		$firstRow = $collection->first();

		// Null-safety (should never happen with >0 rows, but keeps tools happy)
		$this->assertNotNull($firstRow, 'First row unexpectedly null.');
		$this->assertIsArray($firstRow,  'First row should be an array.');

		// Assert – field mapping
		$this->assertArrayNotHasKey(
			'type_id',
			$firstRow,
			'`type_id` should have been removed by UNSET_FIELDS.'
		);

		$this->assertArrayHasKey(
			'product_id',
			$firstRow,
			'`product_id` key (now product name) should be present.'
		);
	}

	/**
	 ** @test
	 **
	 ** The `headings()` method must return the exact titles used by the
	 ** spreadsheet header row so that exported columns line up.
	 **/
	public function headings_match_expected_definition(): void
	{
		$export = new ProductStockExport();

		$this->assertSame(
			self::EXPECTED_HEADINGS,
			$export->headings(),
			'Headings array does not match the expected definition.'
		);
	}

	/**
	 ** @test
	 **
	 ** `registerEvents()` must register an **AfterSheet** callback so
	 ** the exporter can style the worksheet once data is written.
	 **/
	public function register_events_contains_aftersheet_handler(): void
	{
		$export = new ProductStockExport();
		$events = $export->registerEvents();

		$this->assertArrayHasKey(
			AfterSheet::class,
			$events,
			'AfterSheet event is not registered.'
		);

		$this->assertIsCallable(
			$events[AfterSheet::class],
			'AfterSheet handler is not callable.'
		);
	}
}
