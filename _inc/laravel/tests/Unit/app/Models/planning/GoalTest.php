<?php

/**
 * Goal model tests
 */

namespace Tests\Unit\Models;

use App\Models\Goal;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class GoalTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
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
		// target() delegates to GoalRequestService::calculateTarget(),
		// which does its own auth check. Login a real user with `lang`
		// set so the XSS middleware doesn't redirect.
		$user = \App\Models\User::factory()->create(['type' => 'company', 'lang' => 'en']);
		\Illuminate\Support\Facades\Auth::login($user);

		$goal = new Goal;
		$result = $goal->target('Unknown', '2024-01', '2024-12', 1000);

		$this->assertEquals(['percentage' => 0, 'total' => 0], $result);
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
