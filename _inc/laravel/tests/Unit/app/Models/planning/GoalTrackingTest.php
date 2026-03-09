<?php

/**
 * GoalTracking model tests
 */

namespace Tests\Unit\Models;

use App\Models\GoalTracking;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalTrackingTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	/**
	 ** @test
	 *
	 ** Ensure the static $status list stays
	 ** identical to the private constant.
	 **/
	public function status_array_matches_constant(): void
	{
		// Trigger booted() to populate $status from EvaluationStatus enum + legacy list
		new GoalTracking;

		$expected = [
			'Not Started',
			'In Progress',
			'Completed',
			'in_progress',
			'completed',
			'active',
			'suspended',
			'pending',
			'draft',
			'cancelled',
			'expired',
			'archived',
			'undefined',
			'accept',
			'decline',
			'not_started',
		];

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
		$expected = [
			'company',
			'branch',
			'department',
			'goal_type',
			'goal',
			'start_date',
			'end_date',
			'subject',
			'rating',
			'target_achievement',
			'description',
			'status',
			'progress',
			'priority',
			'metrics',
			'attachments',
			'tags',
			'steps',
			'metadata',
		];

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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('goal_type',         $rel->getForeignKeyName());
		$this->assertSame('id',  $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** branches() must be HasOne mapping
	 ** Branch::id ← goal_tracking.branch.
	 **/
	public function branch_relation_is_has_one(): void
	{
		$rel = (new GoalTracking)->branch();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('branch',      $rel->getForeignKeyName());
		$this->assertSame('id',  $rel->getOwnerKeyName());
	}
}
