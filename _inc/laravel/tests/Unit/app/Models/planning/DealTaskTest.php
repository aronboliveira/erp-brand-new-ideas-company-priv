<?php

/**
 * DealTask model tests
 */

namespace Tests\Unit\Models;

use App\Models\DealTask;
use Tests\TestCase;

class DealTaskTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	/**
	 ** @test
	 *
	 ** Verify the priorities map contains
	 ** exactly Low / Medium / High.
	 **/
	public function priorities_array_is_correct(): void
	{
		$this->assertSame(
			[
				0 => 'None',
				1 => 'Low',
				2 => 'Medium',
				3 => 'High',
				4 => 'Critical',
				5 => 'Urgent',
				6 => 'Blocker',
				7 => 'Immediate',
			],
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
			[
				0  => 'In Progress',
				1  => 'Completed',
				2  => 'Active',
				3  => 'Suspended',
				4  => 'Pending',
				5  => 'Draft',
				6  => 'Cancelled',
				7  => 'Expired',
				8  => 'Archived',
				9  => 'Undefined',
				10 => 'Accept',
				11 => 'Decline',
				12 => 'Not Started',
			],
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
			'deal_id',
			'name',
			'description',
			'date',
			'time',
			'priority',
			'status',
			'task_id',
			'attachments',
			'tags',
		];

		$this->assertSame($expected, (new DealTask)->getFillable());
	}
}
