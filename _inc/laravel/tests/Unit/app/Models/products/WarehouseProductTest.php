<?php

/**
 * tests/Unit/Models/WarehouseProductTest.php
 */

namespace Tests\Unit\Models;

use App\Models\WarehouseProduct;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class WarehouseProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Ensure mass-assignment fields remain unchanged.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'warehouse_id',
			'product_id',
			'quantity',
		];

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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$wp->product()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$wp->warehouse()
		);
	}
}
