<?php

namespace Tests\Unit\Models;

use App\Models\Task;
use Mockery;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class TaskTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	/**
	 ** @test
	 *
	 ** Guard the fillable whitelist against drift.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'title',
			'agent_or_manager',
			'agent_or_manager_id',
			'date',
			'time',
			'description',
			'module_type',
			'module_id',
			'assign_to',
			'project_id',
			'milestone_id',
			'stages',
			'attachments',
			'involved',
			'tags',
			'metadata',
			'updated_by',
		];

		$this->assertSame($expected, (new Task)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** taskCompleteCheckListCount() must count
	 ** only checklist items whose status == 1.
	 **/
	public function complete_checklist_counter_works(): void
	{
		$mockRelation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasMany::class);
		$mockRelation->shouldReceive('where')->with('status', '1')->andReturnSelf();
		$mockRelation->shouldReceive('count')->andReturn(2);

		$task = Mockery::mock(Task::class)->makePartial();
		$task->shouldReceive('taskCheckList')->andReturn($mockRelation);

		$this->assertSame(2, $task->taskCompleteCheckListCount());
	}

	/**
	 ** @test
	 *
	 ** taskTotalCheckListCount() counts all items.
	 **/
	public function total_checklist_counter_works(): void
	{
		$mockRelation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasMany::class);
		$mockRelation->shouldReceive('count')->andReturn(2);

		$task = Mockery::mock(Task::class)->makePartial();
		$task->shouldReceive('taskCheckList')->andReturn($mockRelation);

		$this->assertSame(2, $task->taskTotalCheckListCount());
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
