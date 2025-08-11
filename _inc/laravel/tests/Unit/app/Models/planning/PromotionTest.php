<?php

namespace Tests\Unit\Models;

use App\Models\Promotion;
use Tests\TestCase;

class PromotionTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Guard the $fillable array against drift.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Promotion::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new Promotion)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** designation() must be HasOne via
	 ** designations.id ← promotions.designation_id.
	 **/
	public function designation_relation_is_has_one(): void
	{
		$rel = (new Promotion)->designation();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',              $rel->getForeignKeyName());
		$this->assertSame('designation_id',  $rel->getLocalKeyName());
	}
}
