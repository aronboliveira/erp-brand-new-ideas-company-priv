<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{ProductService, User, Warehouse, WarehouseTransfer};

class WarehouseTransferTest extends TestCase
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
	 ** WarehouseTransfer is mass assignable for from_warehouse, to_warehouse, product_id, quantity, date, and created_by
	 **/
	public function warehouse_transfer_is_fillable()
	{
		$product      = ProductService::factory()->create();
		$warehouseFrom = Warehouse::factory()->create();
		$warehouseTo  = Warehouse::factory()->create();
		$user         = User::factory()->create();

		$data = [
			'from_warehouse' => $warehouseFrom->id,
			'to_warehouse'   => $warehouseTo->id,
			'product_id'     => $product->id,
			'quantity'       => 10,
			'date'           => '2025-05-22',
			'created_by'     => $user?->id,
		];

		$transfer = WarehouseTransfer::create($data);

		$this->assertFillableMatches($data, $transfer);
	}

	/**
	 ** @test
	 **
	 ** WarehouseTransfer uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function warehouse_transfer_uses_uuid_for_primary_key()
	{
		$transfer = WarehouseTransfer::factory()->create();

		$key = $transfer->getKey();

		$this->assertIsString($key);
		$this->assertFalse($transfer->getIncrementing());
		$this->assertSame('string', $transfer->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** product() relation should point to App\Models\ProductService via product_id
	 **/
	public function product_relation_resolves_to_product_service_model()
	{
		$relation = (new WarehouseTransfer)->product();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(ProductService::class,  get_class($relation->getRelated()));
		$this->assertSame('product_id',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** fromWarehouse() relation should point to App\Models\Warehouse via from_warehouse
	 **/
	public function from_warehouse_relation_resolves_to_warehouse_model()
	{
		$relation = (new WarehouseTransfer)->fromWarehouse();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Warehouse::class,       get_class($relation->getRelated()));
		$this->assertSame('from_warehouse',       $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** toWarehouse() relation should point to App\Models\Warehouse via to_warehouse
	 **/
	public function to_warehouse_relation_resolves_to_warehouse_model()
	{
		$relation = (new WarehouseTransfer)->toWarehouse();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Warehouse::class,       get_class($relation->getRelated()));
		$this->assertSame('to_warehouse',         $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to App\Models\User via created_by
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new WarehouseTransfer)->createdBy();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
