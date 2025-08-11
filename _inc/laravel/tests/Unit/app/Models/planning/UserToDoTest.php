<?php

namespace Tests\Unit\Models;

use App\Models\UserToDo;
use Tests\TestCase;

class UserToDoTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure mass-assignment whitelist is correct.
	 **/
	public function fillable_array_is_correct(): void
	{
		$ref     = new \ReflectionClass(UserToDo::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new UserToDo)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() relation must be HasOne.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new UserToDo)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
	}
}
