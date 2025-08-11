<?php

namespace Tests\Unit\Models;

use App\Models\GoalType;
use Tests\TestCase;

class GoalTypeTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The $fillable property must stay exactly
	 ** “name” + “created_by” to guard against
	 ** unintended mass-assignment changes.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['name', 'created_by'];

		$this->assertSame($expected, (new GoalType)->getFillable());
	}
}
