<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Guard the $fillable list.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'name', 'price', 'description', 'image', 'type', 'created_by',
		];

		$this->assertSame($expected, (new Product)->getFillable());
	}
}
