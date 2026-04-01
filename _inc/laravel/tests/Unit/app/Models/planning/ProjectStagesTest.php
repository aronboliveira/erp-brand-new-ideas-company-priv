<?php

namespace Tests\Unit\Models;

use App\Models\ProjectStage;
use App\Services\ProjectRequestService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class ProjectStagesTest extends TestCase
{
	use SafeAliasMock;

	protected function setUp(): void
	{
		parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

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
		$expectedCollection = new EloquentCollection;

		// Mock ProjectRequestService to return an empty collection
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
		$this->aliasMock(ProjectStage::class)
			->shouldReceive('where')
			->andReturnSelf()
			->getMock()
			->shouldReceive('get')
			->andReturn($fakeStages);

		// Simplify Task::where()*->count() to 0.
		$this->aliasMock('App\Models\Task')
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
