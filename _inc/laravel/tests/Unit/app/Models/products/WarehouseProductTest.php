<?php

/**
 * tests/Unit/Models/WarehouseProductTest.php
 */

namespace Tests\Unit\Models;

use App\Models\WarehouseProduct;
use Tests\TestCase;

class WarehouseProductTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure mass-assignment fields remain unchanged.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['warehouse_id', 'product_id', 'quantity', 'created_by'];

		$this->assertSame($expected, (new WarehouseProduct)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** product() and warehouse() relations must be HasOne.
	 **/
	public function relations_are_has_one(): void
	{
		$wp = new WarehouseProduct;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$wp->product()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$wp->warehouse()
		);
	}
}
