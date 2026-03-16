<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class ProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Guard the $fillable list.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'product_service_id',
			'name',
			'price',
			'quantity',
			'description',
			'image',
			'type',
		];

		$this->assertSame($expected, (new Product)->getFillable());
	}
}
