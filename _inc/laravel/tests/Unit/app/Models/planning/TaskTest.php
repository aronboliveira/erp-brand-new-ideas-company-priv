<?php

namespace Tests\Unit\Models;

use App\Models\Task;
use Tests\TestCase;

class TaskTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Guard the fillable whitelist against drift.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Task::class);
		$expected = $ref->getConstant('FILLABLE');

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
		$task = new Task;

		$task->setRelation('taskCheckList', collect([
			(object)['status' => 1], (object)['status' => 0], (object)['status' => 1],
		]));

		$this->assertSame(2, $task->taskCompleteCheckListCount());
	}

	/**
	 ** @test
	 *
	 ** taskTotalCheckListCount() counts all items.
	 **/
	public function total_checklist_counter_works(): void
	{
		$task = new Task;

		$task->setRelation('taskCheckList', collect([
			(object)['status' => 1], (object)['status' => 0],
		]));

		$this->assertSame(2, $task->taskTotalCheckListCount());
	}
}
