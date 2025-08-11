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
	/**
	 ** @test
	 *
	 ** Verify the fillable property matches the
	 ** constant so accidental edits are caught.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(ProductServiceUnit::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new ProductServiceUnit)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() must be a HasOne relation mapping
	 ** users.id ← product_service_units.created_by.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new ProductServiceUnit)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('created_by', $rel->getLocalKeyName());
	}
}
