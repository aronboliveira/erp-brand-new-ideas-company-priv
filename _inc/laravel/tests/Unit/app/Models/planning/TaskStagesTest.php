<?php

namespace Tests\Unit\Models;

use App\Models\TaskStage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class TaskStageTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Static $stages array must mirror constant.
	 **/
	public function stages_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(TaskStage::class);
		$expected = $ref->getConstant('STAGES_LIST');

		$this->assertSame($expected, TaskStage::$stages);
	}

	/**
	 ** @test
	 *
	 ** getChartData() returns label+dataset keys.
	 *  We stub DB queries so no database is hit.
	 **/
	public function get_chart_data_structure(): void
	{
		// Freeze today
		Carbon::setTestNow(Carbon::parse('2025-05-30'));

		// Fake logged-in company user.
		$user = (object)['id' => 1, 'type' => 'company', 'creatorId' => fn () => 1];
		Auth::shouldReceive('user')->andReturn($user);

		// Stub TaskStage::where()->get()
		$fakeStages = collect([
			(object)['id' => 10, 'name' => 'Todo', 'color' => '#f00'],
			(object)['id' => 11, 'name' => 'Done', 'color' => '#0f0'],
		]);
		Mockery::mock('alias:' . TaskStage::class)
			->shouldReceive('where')->andReturnSelf()
			->getMock()->shouldReceive('get')->andReturn($fakeStages);

		// Simplify ProjectTask::where* chain to 0 counts.
		Mockery::mock('alias:App\Models\ProjectTask')
			->shouldReceive('where')->andReturnSelf()
			->getMock()->shouldReceive('whereDate')->andReturnSelf()
			->getMock()->shouldReceive('join')->andReturnSelf()
			->getMock()->shouldReceive('count')->andReturn(0);

		$data = TaskStage::getChartData();

		$this->assertArrayHasKey('label',   $data);
		$this->assertArrayHasKey('dataset', $data);
		$this->assertCount(2, $data['dataset']); // two fake stages
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
