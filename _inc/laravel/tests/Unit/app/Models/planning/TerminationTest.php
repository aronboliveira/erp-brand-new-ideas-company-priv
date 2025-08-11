<?php

namespace Tests\Unit\Models;

use App\Models\Termination;
use Tests\TestCase;

class TerminationTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Guard the fillable whitelist.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Termination::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new Termination)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employee() and terminationType()
	 ** relations must both be HasOne.
	 **/
	public function relations_are_has_one(): void
	{
		$term = new Termination;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$term->employee()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$term->terminationType()
		);
	}
}
