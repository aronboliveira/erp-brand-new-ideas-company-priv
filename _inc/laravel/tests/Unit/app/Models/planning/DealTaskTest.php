<?php

/**
 * DealTask model tests
 */

namespace Tests\Unit\Models;

use App\Models\DealTask;
use Tests\TestCase;

class DealTaskTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Verify the priorities map contains
	 ** exactly Low / Medium / High.
	 **/
	public function priorities_array_is_correct(): void
	{
		$this->assertSame(
			[1 => 'Low', 2 => 'Medium', 3 => 'High'],
			DealTask::$priorities
		);
	}

	/**
	 ** @test
	 *
	 ** The status array must stay “On Going /
	 ** Completed” with numeric keys 0 and 1.
	 **/
	public function status_array_is_correct(): void
	{
		$this->assertSame(
			[0 => 'On Going', 1 => 'Completed'],
			DealTask::$status
		);
	}

	/**
	 ** @test
	 *
	 ** Guard the fillable list against drift
	 ** by comparing with the reflection of
	 ** class constants.
	 **/
	public function fillable_fields_match_column_constants(): void
	{
		$expected = [
			'deal_id', 'name', 'date', 'time', 'priority', 'status',
		];

		$this->assertSame($expected, (new DealTask)->getFillable());
	}
}
