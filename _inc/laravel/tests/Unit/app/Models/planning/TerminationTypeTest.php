<?php

namespace Tests\Unit\Models;

use App\Models\TerminationType;
use Tests\TestCase;

class TerminationTypeTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Verify $fillable array stays intact.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['name', 'created_by'];

		$this->assertSame($expected, (new TerminationType)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** createdBy() relation must be HasOne.
	 **/
	public function created_by_relation_is_has_one(): void
	{
		$rel = (new TerminationType)->createdBy();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
	}
}
