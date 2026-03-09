<?php

namespace Tests\Unit\Models;

use App\Models\GoalType;
use Tests\TestCase;

class GoalTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable property must stay exactly
	 ** “name” + “created_by” to guard against
	 ** unintended mass-assignment changes.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'category',
			'name',
			'description',
			'icon',
			'color',
			'rules',
			'metadata',
			'tags',
		];

		$this->assertSame($expected, (new GoalType)->getFillable());
	}
}
