<?php

namespace Tests\Unit\Models;

use App\Models\PosProduct;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class PosProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('product_id',         $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}
}
