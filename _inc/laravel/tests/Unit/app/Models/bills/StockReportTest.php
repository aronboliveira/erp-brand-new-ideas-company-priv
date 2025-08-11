<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{StockReport, ProductService};

class StockReportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** StockReport is mass assignable for product_id, quantity, type, type_id, description, and created_by
	 **/
	public function stock_report_is_fillable()
	{
		$product = ProductService::factory()->create();

		$data = [
			'product_id'  => $product->id,
			'quantity'    => 10,
			'type'        => 'adjustment',
			'type_id'     => 'T123',
			'description' => 'Initial stock',
			'created_by'  => 'user_abc',
		];

		$sr = StockReport::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $sr->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** StockReport uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$sr = StockReport::factory()->create();
		$key = $sr->getKey();

		$this->assertIsString($key);
		$this->assertFalse($sr->getIncrementing());
		$this->assertSame('string', $sr->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** product() relation should point to ProductService via product_id
	 **/
	public function product_relation_resolves_to_product_service_model()
	{
		$relation = (new StockReport)->product();

		$this->assertInstanceOf(HasOne::class,         $relation);
		$this->assertSame(ProductService::class,       get_class($relation->getRelated()));
		$this->assertSame('id',                        $relation->getForeignKeyName());
		$this->assertSame('product_id',                $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** static products() returns the name of the last valid product id in CSV, or empty string if none found
	 **/
	public function products_static_method_returns_last_name_or_empty()
	{
		$p1 = ProductService::factory()->create(['name' => 'Widget']);
		$p2 = ProductService::factory()->create(['name' => 'Gadget']);

		// Should return 'Gadget', the name of the last id in the CSV
		$result = StockReport::products("{$p1->id},{$p2->id}");
		$this->assertSame('Gadget', $result);

		// Unknown id yields empty
		$this->assertSame('', StockReport::products('unknown-id'));
	}
}
