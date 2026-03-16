<?php

namespace Tests\Unit\Models;

use App\Models\ProposalProduct;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class ProposalProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Fillable list must match constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'proposal_id',
			'product_id',
			'quantity',
			'tax',
			'discount',
			'price',
			'description',
		];

		$this->assertSame($expected, (new ProposalProduct)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** product() relation is HasOne.
	 **/
	public function product_relation_is_has_one(): void
	{
		$rel = (new ProposalProduct)->product();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('product_id',         $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}
}
