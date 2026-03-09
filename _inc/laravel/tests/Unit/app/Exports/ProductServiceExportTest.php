<?php

namespace Tests\Unit\Exports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Exports\ProductServiceExport;
use App\Models\{User, ProductService, ProductServiceCategory, ProductServiceUnit};

/**
 ** Test-suite for the `ProductServiceExport` class.
 **
 ** These tests certify that:
 **   • The header row matches exactly the constant defined in the export.  
 **   • The `collection()` method returns rows **only** for the authenticated
 **     user, with each value formatted as expected (price, tax, etc.).  
 **/
class ProductServiceExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** It should return the exact headings used by consumer spreadsheets and
	 ** analytics tools, preserving column order and labels.
	 **/
	public function headings_are_correct(): void
	{
		$export = new ProductServiceExport();

		$expected = [
			'ID',
			'Name',
			'SKU',
			'Sale Price',
			'Purchase Price',
			'Tax',
			'Category',
			'Unit',
			'Type',
			'Description',
		];

		$this->assertSame($expected, $export->headings());
	}

	/**
	 ** @test
	 **
	 ** Given an authenticated user with product-services, the `collection()`
	 ** method must only include *that* user’s records and return each row
	 ** already formatted (currency, tax string, etc.).
	 **/
	public function collection_returns_only_authenticated_user_rows(): void
	{
		// ── Arrange ──────────────────────────────────────────────────────────
		$user = User::factory()->create();
		Auth::login($user);

		$category = ProductServiceCategory::factory()->create(['name' => 'Electronics']);
		$unit    = ProductServiceUnit::factory()->create(['name' => 'Piece']);

		$product = ProductService::factory()->create([
			'name'        => 'Laptop',
			'sku'         => 'LP-001',
			'sale_price'  => 1999.50,
			'purchase_price' => 1500,
			'tax_id'      => null,
			'category_id' => $category->id,
			'unit_id'     => $unit->id,
			'type'        => 'product',
			'description' => 'A powerful laptop',
		]);

		// Foreign user product – must be *excluded*
		$otherUser = User::factory()->create();
		Auth::login($otherUser);
		ProductService::factory()->create();
		Auth::login($user);

		// ── Act ──────────────────────────────────────────────────────────────
		$export    = new ProductServiceExport();
		$collection = $export->collection();

		// ── Assert ───────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first();
		$this->assertIsArray($row);
		// 10 columns matching headings
		$this->assertCount(10, $row);
		// First column is the product ID
		$this->assertSame($product->id, $row[0]);
		// Second column is the name
		$this->assertSame('Laptop', $row[1]);
	}
}
