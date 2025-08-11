<?php

/**
 * GoalTracking model tests
 */

namespace Tests\Unit\Models;

use App\Models\GoalTracking;
use Tests\TestCase;

class GoalTrackingTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure the static $status list stays
	 ** identical to the private constant.
	 **/
	public function status_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(GoalTracking::class);
		$expected = $ref->getConstant('STATUS_LIST');

		$this->assertSame($expected, GoalTracking::$status);
	}

	/**
	 ** @test
	 *
	 ** Guard the mass-assignment list against
	 ** unintended edits by comparing it with
	 ** the private FILLABLE_FIELDS constant.
	 **/
	public function fillable_array_is_as_declared(): void
	{
		$ref     = new \ReflectionClass(GoalTracking::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new GoalTracking)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The goalType() relation must be HasOne
	 ** GoalType::id ← goal_tracking.goal_type.
	 **/
	public function goal_type_relation_is_has_one(): void
	{
		$rel = (new GoalTracking)->goalType();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('goal_type',  $rel->getLocalKeyName());
	}

	/**
	 ** @test
	 *
	 ** branches() must be HasOne mapping
	 ** Branch::id ← goal_tracking.branch.
	 **/
	public function branch_relation_is_has_one(): void
	{
		$rel = (new GoalTracking)->branches();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',      $rel->getForeignKeyName());
		$this->assertSame('branch',  $rel->getLocalKeyName());
	}
}
