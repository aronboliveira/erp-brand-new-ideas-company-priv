<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{InvoiceProduct, Invoice, ProductService};

class InvoiceProductTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** InvoiceProduct is mass assignable for product_id, invoice_id, quantity, tax, discount, price, and description
	 **/
	public function invoice_product_is_fillable()
	{
		$invoice = Invoice::factory()->create();
		$product = ProductService::factory()->create();

		$data = [
			'product_id'  => $product->id,
			'invoice_id'  => $invoice->id,
			'quantity'    => 3,
			'tax'         => 5.5,
			'discount'    => 1.25,
			'price'       => 100.00,
			'description' => 'Test line item',
		];

		$line = InvoiceProduct::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $line->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Primary key uses UUID: string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$line = InvoiceProduct::factory()->create();
		$key = $line->getKey();

		$this->assertIsString($key);
		$this->assertFalse($line->getIncrementing());
		$this->assertSame('string', $line->getKeyType());
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
		$relation = (new InvoiceProduct)->product();

		$this->assertInstanceOf(HasOne::class,       $relation);
		$this->assertSame(ProductService::class,     get_class($relation->getRelated()));
		$this->assertSame('id',                      $relation->getForeignKeyName());
		$this->assertSame('product_id',              $relation->getLocalKeyName());
	}
}
