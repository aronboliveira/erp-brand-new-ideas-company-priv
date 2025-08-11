<?php

namespace Tests\Unit\Models;

use App\Models\Holiday;
use Tests\TestCase;

class HolidayTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure the $fillable list mirrors the
	 ** private constant so refactors won’t
	 ** silently break mass-assignment rules.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Holiday::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new Holiday)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The user() relation must be HasOne from
	 ** holidays.created_by → users.id.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new Holiday)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',          $rel->getForeignKeyName());
		$this->assertSame('created_by',  $rel->getLocalKeyName());
	}
}
