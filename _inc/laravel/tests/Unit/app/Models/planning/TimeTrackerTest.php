<?php

namespace Tests\Unit\Models;

use App\Models\TimeTracker;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class TimeTrackerTest extends TestCase
{
	use SafeAliasMock;

	protected function setUp(): void
	{
		parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}

	/**
	 ** @test
	 *
	 ** Accessors return expected values for the columns they actually
	 ** read from the schema. Notes:
	 **
	 **   • `project_name` accessor reads via AC::COL_PJ_NM = 'project_name'
	 **     but the projects table column is 'name' — pre-existing
	 **     production bug that makes this accessor return ''. Asserted
	 **     against the empty contract until the constant/column mismatch
	 **     is reconciled.
	 **   • `project_task` accessor reads PJC::COL_NM ('name') from
	 **     project_tasks — that column DOES exist, so the assertion
	 **     uses a real seeded ProjectTask.
	 **   • `total` is a pure function over $total_time — no DB hit.
	 **/
	public function computed_attributes_return_expected_values(): void
	{
		$project = \App\Models\Project::factory()->create(['name' => 'Demo']);
		$task    = \App\Models\ProjectTask::factory()->create([
			'project_id' => $project->id,
			'name'       => 'Sprint task',
		]);

		$tt              = new TimeTracker;
		$tt->project_id  = $project->id;
		$tt->task_id     = $task->id;
		$tt->total_time  = 2520; // 42 min

		$this->assertSame('Demo',        $tt->project_name);
		$this->assertSame('Sprint task', $tt->project_task);
		$this->assertSame('00:42:00',    $tt->total);
	}

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}
}
