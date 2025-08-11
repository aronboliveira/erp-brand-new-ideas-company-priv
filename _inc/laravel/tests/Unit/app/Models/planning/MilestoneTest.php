<?php

/**
 * Milestone model tests
 */

namespace Tests\Unit\Models;

use App\Models\Milestone;
use Tests\TestCase;

class MilestoneTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure $fillable stays aligned with the
	 ** declared constant list.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Milestone::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new Milestone)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** tasks() must be a HasMany link from
	 ** milestones.id → project_tasks.milestone_id.
	 **/
	public function tasks_relation_is_has_many(): void
	{
		$rel = (new Milestone)->tasks();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasMany::class,
			$rel
		);
		$this->assertSame('milestone_id', $rel->getForeignKeyName());
		$this->assertSame('id',           $rel->getLocalKeyName());
	}
}
