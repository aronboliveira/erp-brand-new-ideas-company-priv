<?php

namespace Tests\Unit\Models;

use App\Models\PurchaseProduct;
use Tests\TestCase;

class PurchaseProductTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Protect $fillable against drift.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'product_id', 'purchase_id', 'quantity', 'tax', 'discount', 'total',
		];

		$this->assertSame($expected, (new PurchaseProduct)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** product() must return a HasOne relation.
	 **/
	public function product_relation_is_has_one(): void
	{
		$rel = (new PurchaseProduct)->product();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('product_id', $rel->getLocalKeyName());
	}
}
