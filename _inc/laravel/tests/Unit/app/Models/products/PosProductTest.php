<?php

namespace Tests\Unit\Models;

use App\Models\PosProduct;
use Tests\TestCase;

class PosProductTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure mass-assignment whitelist is intact.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'product_id',
			'pos_id',
			'quantity',
			'tax',
			'discount',
			'price',
			'description',
		];

		$this->assertSame($expected, (new PosProduct)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** product() must be a HasOne relation.
	 **/
	public function product_relation_is_has_one(): void
	{
		$rel = (new PosProduct)->product();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('product_id', $rel->getLocalKeyName());
	}
}
