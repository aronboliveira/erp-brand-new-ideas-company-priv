<?php

namespace Tests\Unit\Models;

use App\Models\ProposalProduct;
use Tests\TestCase;

class ProposalProductTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Fillable list must match constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(ProposalProduct::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

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
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('product_id', $rel->getLocalKeyName());
	}
}
