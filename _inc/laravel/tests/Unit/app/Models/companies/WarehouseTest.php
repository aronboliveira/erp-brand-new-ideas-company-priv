<?php
// tests/Unit/Models/WarehouseTest.php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Warehouse, User};

class WarehouseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Warehouse is mass assignable for name, address, city, city_zip, and created_by
	 **/
	public function warehouse_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'name'       => 'Main Warehouse',
			'address'    => '123 Industrial Ave',
			'city'       => 'Metropolis',
			'zip'        => '12345',
			'created_by' => $user?->id,
		];

		$wh = Warehouse::create($data);

		$this->assertFillableMatches($data, $wh);
	}

	/**
	 ** @test
	 **
	 ** Warehouse uses UUID for primary key
	 **/
	public function warehouse_uses_uuid_for_primary_key()
	{
		$wh = Warehouse::factory()->create();

		$key = $wh->getKey();
		$this->assertIsString($key);
		$this->assertFalse($wh->getIncrementing());
		$this->assertSame('string', $wh->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** creator() relation should point to User via created_by
	 **/
	public function creator_relation_resolves_to_user_model()
	{
		$relation = (new Warehouse)->creator();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
