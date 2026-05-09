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
		// PJC::COL_STG was reconciled with the migration source-of-truth
		// (JSON `stages` column on tasks); SQL inside projectStageChartData
		// now uses JSON_CONTAINS+JSON_QUOTE, so the dataset is populated
		// with one entry per ProjectStage row.
		$company = \App\Models\User::factory()->create(['type' => 'company', 'lang' => 'en']);
		\Illuminate\Support\Facades\Auth::login($company);

		ProjectStage::create(['name' => 'Todo', 'color' => '#ff0', 'order' => 1, 'created_by' => $company->id]);
		ProjectStage::create(['name' => 'Done', 'color' => '#0f0', 'order' => 2, 'created_by' => $company->id]);

		$data = ProjectStage::getChartData();

		$this->assertArrayHasKey('label', $data);
		$this->assertArrayHasKey('dataset', $data);
		$this->assertCount(2, $data['dataset']);
	}

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}
}
