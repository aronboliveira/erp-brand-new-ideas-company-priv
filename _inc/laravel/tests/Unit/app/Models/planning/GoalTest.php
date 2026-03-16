<?php

/**
 * Goal model tests
 */

namespace Tests\Unit\Models;

use App\Models\Goal;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

use Illuminate\Support\Facades\DB;
class GoalTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use SafeAliasMock;

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
		$this->aliasMock(Goal::class)
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
		$this->assertSame($expected, $ref->getConstant('LEGACY_GOAL_TYPES'));
	}
}
