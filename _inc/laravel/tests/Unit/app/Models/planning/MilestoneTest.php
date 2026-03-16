<?php

/**
 * Milestone model tests
 */

namespace Tests\Unit\Models;

use App\Models\Milestone;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class MilestoneTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Ensure $fillable stays aligned with the
	 ** declared constant list.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'project_id',
			'title',
			'description',
			'priority',
			'status',
			'progress',
			'cost',
			'start_date',
			'due_date',
			'metadata',
			'tags',
			'involved',
			'updated_by',
		];

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
