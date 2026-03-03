<?php

namespace Tests\Unit\Models;

use App\Models\ProjectStage;
use App\Services\ProjectRequestService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class ProjectStagesTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Default fake "employee" user; individual
		// tests may override → Auth::shouldReceive('user')->andReturn(...)
		$user = (object) ['id' => 9, 'type' => 'employee', 'creatorId' => fn () => 1];
		Auth::shouldReceive('user')->andReturn($user);
	}

	/**
	 ** @test
	 *
	 ** tasksForProject() must return a collection
	 ** by delegating to ProjectRequestService.
	 **/
	public function tasks_method_filters_for_employee(): void
	{
		// Intercept Task::where() chain
		Mockery::mock('alias:App\Models\Task')
			->shouldReceive('where')
			->once()
			->with('stage', 1)  // first call
			->andReturnSelf()
			->getMock()
			->shouldReceive('where')
			->once()
			->with('project_id', 42)
			->andReturnSelf()
			->getMock()
			->shouldReceive('where')
			->once()
			->with('assign_to', 9) // employee filter
			->andReturnSelf()
			->getMock()
			->shouldReceive('orderBy')
			->once()
			->with('order')
			->andReturnSelf()
			->getMock()
			->shouldReceive('get')
			->once()
			->andReturn(new Collection);

		// Mock ProjectRequestService to return an empty collection
		$expectedCollection = new EloquentCollection;
		$serviceMock = Mockery::mock(ProjectRequestService::class);
		$serviceMock->shouldReceive('stageTasksForProject')
			->once()
			->andReturn($expectedCollection);
		$this->app->instance(ProjectRequestService::class, $serviceMock);

		$stage     = new ProjectStage;
		$stage->id = 1;

		$result = $stage->tasksForProject(42);

		$this->assertInstanceOf(EloquentCollection::class, $result);
	}

	/**
	 ** @test
	 *
	 ** getChartData() should return an array
	 ** with keys “label” (days) and “dataset”.
	 **/
	public function get_chart_data_returns_expected_structure(): void
	{
		// Fake company user
		$company = (object) ['id' => 1, 'type' => 'company', 'creatorId' => fn () => 1];
		Auth::shouldReceive('user')->andReturn($company);

		// Stub ProjectStages::where()->get() to
		// supply two mock stages.
		$fakeStages = collect([
			(object) ['id' => 1, 'name' => 'Todo', 'color' => '#ff0'],
			(object) ['id' => 2, 'name' => 'Done', 'color' => '#0f0'],
		]);
		Mockery::mock('alias:' . ProjectStage::class)
			->shouldReceive('where')
			->andReturnSelf()
			->getMock()
			->shouldReceive('get')
			->andReturn($fakeStages);

		// Simplify Task::where()*->count() to 0.
		Mockery::mock('alias:App\Models\Task')
			->shouldReceive('where')->andReturnSelf()
			->getMock()->shouldReceive('whereDate')->andReturnSelf()
			->getMock()->shouldReceive('join')->andReturnSelf()
			->getMock()->shouldReceive('count')->andReturn(0);

		$data = ProjectStage::getChartData();

		$this->assertArrayHasKey('label', $data);
		$this->assertArrayHasKey('dataset', $data);
		$this->assertCount(2, $data['dataset']);   // two stages
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
