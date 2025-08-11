<?php

namespace Tests\Unit\Models;

use App\Models\Travel;
use Tests\TestCase;

class TravelTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Fillable list must match constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Travel::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new Travel)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employee() is a HasOne relation.
	 **/
	public function employee_relation_is_has_one(): void
	{
		$rel = (new Travel)->employee();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
	}
}
