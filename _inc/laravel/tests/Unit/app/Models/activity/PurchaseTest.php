<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany};
use App\Models\{
	Purchase,
	Vendor,
	Tax,
	ProductServiceCategory,
	PurchaseProduct,
	PurchasePayment
};

class PurchaseTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Purchase is mass assignable for all fillable fields
	 **/
	public function purchase_is_fillable()
	{
		$vendor  = Vendor::factory()->create();
		$tax     = Tax::factory()->create();
		$category = ProductServiceCategory::factory()->create();

		$data = [
			'purchase_id'     => 'PUR-1001',
			'vendor_id'       => $vendor->id,
			'warehouse_id'    => 'wh-1',
			'purchase_date'   => '2025-05-24',
			'purchase_number' => 'PN-123',
			'discount_apply'  => 'yes',
			'category_id'     => $category->id,
			'created_by'      => 'user_1',
			'status'          => 'Draft',
			'shipping_display' => 'Standard',
			'send_date'       => '2025-05-25',
			'tax_id'          => $tax->id,
		];

		$purchase = Purchase::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $purchase->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Purchase uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function purchase_uses_uuid_for_primary_key()
	{
		$purchase = Purchase::factory()->create();

		$key = $purchase->getKey();

		$this->assertIsString($key);
		$this->assertFalse($purchase->getIncrementing());
		$this->assertSame('string', $purchase->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** vendor() relation should point to Vendor model
	 **/
	public function vendor_relation_resolves_to_vendor_model()
	{
		$relation = (new Purchase)->vendor();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Vendor::class,       get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('vendor_id',         $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** tax() relation should point to Tax model
	 **/
	public function tax_relation_resolves_to_tax_model()
	{
		$relation = (new Purchase)->tax();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Tax::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('tax_id',            $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** category() relation should point to ProductServiceCategory model
	 **/
	public function category_relation_resolves_to_category_model()
	{
		$relation = (new Purchase)->category();

		$this->assertInstanceOf(HasOne::class,               $relation);
		$this->assertSame(ProductServiceCategory::class,    get_class($relation->getRelated()));
		$this->assertSame('id',                              $relation->getForeignKeyName());
		$this->assertSame('category_id',                     $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** items() relation should point to PurchaseProduct model
	 **/
	public function items_relation_resolves_to_purchase_product_model()
	{
		$relation = (new Purchase)->items();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(PurchaseProduct::class,    get_class($relation->getRelated()));
		$this->assertSame('purchase_id',             $relation->getForeignKeyName());
		$this->assertSame('id',                      $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** payments() relation should point to PurchasePayment model
	 **/
	public function payments_relation_resolves_to_purchase_payment_model()
	{
		$relation = (new Purchase)->payments();

		$this->assertInstanceOf(HasMany::class,       $relation);
		$this->assertSame(PurchasePayment::class,     get_class($relation->getRelated()));
		$this->assertSame('purchase_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                       $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** lastPayments() relation should point to PurchasePayment model via id = purchase_id
	 **/
	public function last_payments_relation_resolves_correctly()
	{
		$relation = (new Purchase)->lastPayments();

		$this->assertInstanceOf(HasOne::class,       $relation);
		$this->assertSame(PurchasePayment::class,    get_class($relation->getRelated()));
		$this->assertSame('id',                      $relation->getForeignKeyName());
		$this->assertSame('purchase_id',             $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** getSubTotal returns sum of price × quantity
	 **/
	public function get_subtotal_calculation_is_correct()
	{
		$purchase = Purchase::factory()->create();
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'price'       => 10.00,
			'quantity'    => 2,
			'discount'    => 0,
			'tax'         => 0,
		]);
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'price'       => 5.00,
			'quantity'    => 3,
			'discount'    => 0,
			'tax'         => 0,
		]);

		$this->assertEquals(10 * 2 + 5 * 3, $purchase->getSubTotal());
	}

	/**
	 ** @test
	 **
	 ** getTotalDiscount returns sum of discount
	 **/
	public function get_total_discount_calculation_is_correct()
	{
		$purchase = Purchase::factory()->create();
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'price'       => 0,
			'quantity'    => 1,
			'discount'    => 4.00,
			'tax'         => 0,
		]);
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'price'       => 0,
			'quantity'    => 1,
			'discount'    => 2.00,
			'tax'         => 0,
		]);

		$this->assertEquals(6.00, $purchase->getTotalDiscount());
	}

	/**
	 ** @test
	 **
	 ** getTotalTax returns correct sum using Utility::totalTaxRate
	 **/
	public function get_total_tax_calculation_is_correct()
	{
		$purchase = Purchase::factory()->create();
		// assume Utility::totalTaxRate(10) returns 10
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'price'       => 20.00,
			'quantity'    => 1,
			'discount'    => 2.00,
			'tax'         => 10,
		]);

		$expectedTax = (10 / 100) * (20 * 1 - 2);
		$this->assertEquals($expectedTax, $purchase->getTotalTax());
	}

	/**
	 ** @test
	 **
	 ** getTotal returns subtotal – discount + tax
	 **/
	public function get_total_calculation_is_correct()
	{
		$purchase = Purchase::factory()->create();
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'price'       => 15.00,
			'quantity'    => 2,
			'discount'    => 5.00,
			'tax'         => 0,
		]);

		$subtotal = 15 * 2;
		$discount = 5.00;
		$tax     = 0.00;
		$this->assertEquals($subtotal - $discount + $tax, $purchase->getTotal());
	}

	/**
	 ** @test
	 **
	 ** getDue returns total minus sum of payments
	 **/
	public function get_due_calculation_is_correct()
	{
		$purchase = Purchase::factory()->create();
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'price'       => 30.00,
			'quantity'    => 1,
			'discount'    => 0,
			'tax'         => 0,
		]);
		PurchasePayment::factory()->create([
			'purchase_id' => $purchase->id,
			'amount'      => 10.00,
		]);
		PurchasePayment::factory()->create([
			'purchase_id' => $purchase->id,
			'amount'      => 5.00,
		]);

		$total = $purchase->getTotal(); // 30
		$paid = 15;
		$this->assertEquals($total - $paid, $purchase->getDue());
	}
}
