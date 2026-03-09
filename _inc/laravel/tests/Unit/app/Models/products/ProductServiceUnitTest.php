<?php

/**
 * tests/Unit/Models/ProductServiceUnitTest.php
 *
 * Unit-tests for App\Models\ProductServiceUnit
 */

namespace Tests\Unit\Models;

use App\Models\ProductServiceUnit;
use Tests\TestCase;

class ProductServiceUnitTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	/**
	 ** @test
	 *
	 ** Verify the fillable property matches the
	 ** constant so accidental edits are caught.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'product_service_id',
			'name',
			'code',
			'status',
			'measurement_unit',
			'purchase_index',
			'base_price',
			'discount',
			'currency_id',
			'attributes',
			'notes',
		];

		$this->assertSame($expected, (new ProductServiceUnit)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() must be a BelongsTo relation mapping
	 ** product_service_units.created_by → users.id.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new ProductServiceUnit)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('created_by', $rel->getForeignKeyName());
		$this->assertSame('id',         $rel->getOwnerKeyName());
	}
}
