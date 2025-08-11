<?php

namespace Tests\Unit\Models;

use App\Models\TimeTracker;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class TimeTrackerTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Stub Project::select()->where(...)->first()
		Mockery::mock('alias:App\Models\Project')
			->shouldReceive('select')->andReturnSelf()
			->getMock()->shouldReceive('where')->andReturnSelf()
			->getMock()->shouldReceive('first')
			->andReturn((object)['project_name' => 'Demo']);

		// Stub ProjectTask::select()->where(...)->first()
		Mockery::mock('alias:App\Models\ProjectTask')
			->shouldReceive('select')->andReturnSelf()
			->getMock()->shouldReceive('where')->andReturnSelf()
			->getMock()->shouldReceive('first')
			->andReturn((object)['name' => 'Sprint task']);

		// Stub Utility::secondToTime()
		Mockery::mock('alias:App\Models\Utility')
			->shouldReceive('secondToTime')
			->andReturn('00:42:00');
	}

	/**
	 ** @test
	 *
	 ** Accessors should return expected values
	 ** when underlying queries supply data.
	 **/
	public function computed_attributes_return_expected_values(): void
	{
		$tt            = new TimeTracker;
		$tt->project_id = 1;
		$tt->task_id   = 2;
		$tt->total_time = 2520; // 42 min

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
