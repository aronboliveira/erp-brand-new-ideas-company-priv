<?php

namespace Tests\Unit\Models;

use App\Models\ProductCategory;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class ProductCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Verify fillable equals class constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'name',
			'description',
			'product_service_category_id',
			'tags',
		];

		$this->assertSame($expected, (new ProductCategory)->getFillable());
	}
}
