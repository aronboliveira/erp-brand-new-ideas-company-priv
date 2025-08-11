<?php

namespace Tests\Unit\Models;

use App\Models\ProductCategory;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Verify fillable equals class constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(ProductCategory::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new ProductCategory)->getFillable());
	}
}
