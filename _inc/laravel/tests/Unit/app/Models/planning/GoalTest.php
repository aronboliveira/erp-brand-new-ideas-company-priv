<?php

/**
 * Goal model tests
 */

namespace Tests\Unit\Models;

use App\Models\Goal;
use Mockery;
use Tests\TestCase;

class GoalTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** When an unsupported goal “type” is
	 ** supplied, target() must return totals
	 ** of zero to avoid DB calls.
	 **/
	public function target_returns_zero_for_unknown_type(): void
	{
		// Fake user login.
		$user = new class
		{
			public function creatorId()
			{
				return 1;
			}
			public function priceFormat($v)
			{
				return $v;
			}
		};
		Mockery::mock('alias:' . Goal::class)
			->shouldReceive('_checkLogin')
			->once()
			->andReturn($user);

		$goal = new Goal;

		$result = $goal->target('Unknown', '2024-01', '2024-12', 1000);

		$this->assertSame(['percentage' => 0, 'total' => 0], $result);
	}

	/**
	 ** @test
	 *
	 ** Protect the GOAL_TYPES constant by
	 ** checking expected options and order.
	 **/
	public function goal_types_constant_is_intact(): void
	{
		$expected = ['Invoice', 'Bill', 'Revenue', 'Payment'];

		$ref = new \ReflectionClass(Goal::class);
		$this->assertSame($expected, $ref->getConstant('GOAL_TYPES'));
	}
}
