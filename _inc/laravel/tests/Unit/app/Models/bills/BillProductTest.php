<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{BillProduct, Bill, ProductService, ChartOfAccount};

class BillProductTest extends TestCase
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
	 ** BillProduct is mass assignable for product_id, bill_id, chart_account_id, quantity, tax, discount, total, and description
	 **/
	public function bill_product_is_fillable()
	{
		$bill   = Bill::factory()->create();
		$product = ProductService::factory()->create();
		$chart  = ChartOfAccount::factory()->create();

		$data = [
			'product_id'       => $product->id,
			'bill_id'          => $bill->id,
			'chart_account_id' => $chart->id,
			'quantity'         => 5,
			'tax'              => 10.5,
			'discount'         => 2.25,
			'total'            => 48.75,
			'description'      => 'Sample line item',
		];

		$line = BillProduct::create($data);

		$this->assertFillableMatches($data, $line);
	}

	/**
	 ** @test
	 **
	 ** BillProduct uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function bill_product_uses_uuid_for_primary_key()
	{
		$line = BillProduct::factory()->create();

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
	 ** bill() relation should point to App\Models\Bill via bill_id
	 **/
	public function bill_relation_resolves_to_bill_model()
	{
		$relation = (new BillProduct)->bill();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Bill::class,            get_class($relation->getRelated()));
		$this->assertSame('bill_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** product() relation should point to App\Models\ProductService via product_id
	 **/
	public function product_relation_resolves_to_product_service_model()
	{
		$relation = (new BillProduct)->product();

		$this->assertInstanceOf(BelongsTo::class,     $relation);
		$this->assertSame(ProductService::class,      get_class($relation->getRelated()));
		$this->assertSame('product_id',               $relation->getForeignKeyName());
		$this->assertSame('id',                       $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** chartAccount() relation should point to App\Models\ChartOfAccount via chart_account_id
	 **/
	public function chart_account_relation_resolves_to_chart_of_account_model()
	{
		$relation = (new BillProduct)->chartAccount();

		$this->assertInstanceOf(BelongsTo::class,       $relation);
		$this->assertSame(ChartOfAccount::class,        get_class($relation->getRelated()));
		$this->assertSame('chart_account_id',           $relation->getForeignKeyName());
		$this->assertSame('id',                         $relation->getOwnerKeyName());
	}
}
