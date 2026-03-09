<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use App\Models\Pos;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\PosProduct;
use App\Models\Product;
use App\Models\PosPayment;
use App\Models\Tax;

class PosTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Pos is mass assignable for all fillable fields
	 **/
	public function pos_is_fillable()
	{
		$customer = Customer::factory()->create();
		$warehouse = Warehouse::factory()->create();

		$data = [
			'pos_id'           => 'POS-1001',
			'customer_id'      => $customer->id,
			'warehouse_id'     => $warehouse->id,
			'pos_date'         => '2025-05-24',
			'category_id'      => 'cat-1',
			'status'           => 'open',
			'shipping_display' => 'Standard',
			'created_by'       => 'user123',
			'tax'              => Tax::factory()->create()->id,
		];

		$pos = Pos::create($data);

		$this->assertFillableMatches($data, $pos);
	}

	/**
	 ** @test
	 **
	 ** Pos uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function pos_uses_uuid_for_primary_key()
	{
		$pos = Pos::factory()->create();

		$key = $pos->getKey();

		$this->assertIsString($key);
		$this->assertFalse($pos->getIncrementing());
		$this->assertSame('string', $pos->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** customer() relation should point to App\Models\Customer
	 **/
	public function customer_relation_resolves_to_customer_model()
	{
		$relation = (new Pos)->customer();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Customer::class,        get_class($relation->getRelated()));
		$this->assertSame('customer_id',          $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** items() relation should point to App\Models\PosProduct
	 **/
	public function items_relation_resolves_to_pos_product_model()
	{
		$relation = (new Pos)->items();

		$this->assertInstanceOf(HasMany::class,   $relation);
		$this->assertSame(Product::class,         get_class($relation->getRelated()));
		$this->assertSame('pos_id',               $relation->getForeignKeyName());
		$this->assertSame('pos_id',               $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** posPayment() relation should point to App\Models\PosPayment
	 **/
	public function pos_payment_relation_resolves_to_pos_payment_model()
	{
		$relation = (new Pos)->posPayment();

		$this->assertInstanceOf(HasOne::class,    $relation);
		$this->assertSame(PosPayment::class,      get_class($relation->getRelated()));
		$this->assertSame('pos_id',               $relation->getForeignKeyName());
		$this->assertSame('pos_id',               $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** taxes() relation should point to App\Models\Tax
	 **/
	public function taxes_relation_resolves_to_tax_model()
	{
		$relation = (new Pos)->taxes();

		$this->assertInstanceOf(BelongsTo::class,    $relation);
		$this->assertSame(Tax::class,             get_class($relation->getRelated()));
		$this->assertSame('tax',                   $relation->getForeignKeyName());
		$this->assertSame('id',                  $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** warehouse() relation should point to App\Models\Warehouse
	 **/
	public function warehouse_relation_resolves_to_warehouse_model()
	{
		$relation = (new Pos)->warehouse();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Warehouse::class,       get_class($relation->getRelated()));
		$this->assertSame('warehouse_id',         $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** getSubTotal returns 0 when there are no items
	 **/
	public function get_subtotal_returns_zero_when_no_items()
	{
		$pos = Pos::factory()->create();
		$this->assertSame(0.0, $pos->getSubTotal());
	}

	/**
	 ** @test
	 **
	 ** getSubTotal correctly sums price × quantity
	 **/
	public function get_subtotal_correctly_sums_price_times_quantity()
	{
		$pos = Pos::factory()->create();
		PosProduct::factory()->create([
			'pos_id'   => $pos->pos_id,
			'price'    => 10.00,
			'quantity' => 3,
			'discount' => 0,
			'tax'      => 0,
		]);
		PosProduct::factory()->create([
			'pos_id'   => $pos->pos_id,
			'price'    => 5.00,
			'quantity' => 2,
			'discount' => 0,
			'tax'      => 0,
		]);

		$pos->load('items');
		$this->assertEquals(10 * 3 + 5 * 2, $pos->getSubTotal());
	}

	/**
	 ** @test
	 **
	 ** getTotalDiscount returns 0 when there are no items
	 **/
	public function get_total_discount_returns_zero_when_no_items()
	{
		$pos = Pos::factory()->create();
		$this->assertSame(0.0, $pos->getTotalDiscount());
	}

	/**
	 ** @test
	 **
	 ** getTotalDiscount correctly sums discount
	 **/
	public function get_total_discount_correctly_sums_discount()
	{
		$pos = Pos::factory()->create();
		PosProduct::factory()->create([
			'pos_id'   => $pos->pos_id,
			'price'    => 0,
			'quantity' => 1,
			'discount' => 4.50,
			'tax'      => 0,
		]);
		PosProduct::factory()->create([
			'pos_id'   => $pos->pos_id,
			'price'    => 0,
			'quantity' => 1,
			'discount' => 2.25,
			'tax'      => 0,
		]);

		$pos->load('items');
		$this->assertEquals(4.50 + 2.25, $pos->getTotalDiscount());
	}

	/**
	 ** @test
	 **
	 ** getTotalTax returns 0 when all item taxes are 0
	 **/
	public function get_total_tax_returns_zero_when_zero_rates()
	{
		$pos = Pos::factory()->create();
		PosProduct::factory()->create([
			'pos_id'   => $pos->id,
			'price'    => 20.00,
			'quantity' => 1,
			'discount' => 0,
			'tax'      => 0,
		]);

		$this->assertSame(0.0, $pos->getTotalTax());
	}

	/**
	 ** @test
	 **
	 ** getTotal returns subtotal – discount + tax
	 **/
	public function get_total_calculation_is_correct()
	{
		$pos = Pos::factory()->create();
		// Subtotal = 10×2 = 20; discount = 3; tax = 0
		PosProduct::factory()->create([
			'pos_id'   => $pos->pos_id,
			'price'    => 10.00,
			'quantity' => 2,
			'discount' => 3.00,
			'tax'      => 0,
		]);

		$pos->load('items');
		$this->assertEquals(20 - 3 + 0, $pos->getTotal());
	}
}
