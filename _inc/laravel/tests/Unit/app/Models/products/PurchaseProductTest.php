<?php

namespace Tests\Unit\Models;

use App\Models\PurchaseProduct;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class PurchaseProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('product_id',         $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}
}
