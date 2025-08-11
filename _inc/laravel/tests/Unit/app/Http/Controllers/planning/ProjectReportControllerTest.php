<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\TaskStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProjectReportControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $client;

	protected function setUp(): void
	{
		parent::setUp();

		// let creatorId() return own id
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		// default: allow all permissions
		Gate::before(fn () => true);

		$this->company = User::factory()->create(['type' => 'company']);
		$this->client = User::factory()->create(['type' => 'client']);
	}

	/**
	 ** @test
	 **
	 ** index_denies_without_permission
	 **
	 ** Users lacking 'view project report' permission get 403.
	 **/
	public function index_denies_without_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('project_report.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_lists_company_projects
	 **
	 ** Company users see only their created projects.
	 **/
	public function index_lists_company_projects()
	{
		Project::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		Project::factory()->create(['created_by' => $this->client->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('project_report.index'));

		$response->assertOk()
			->assertViewIs('project_report.index')
			->assertViewHas('projects', fn ($list) => $list->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** index_filters_client_projects
	 **
	 ** Client users see only projects where they are the client.
	 **/
	public function index_filters_client_projects()
	{
		Project::factory()->create([
			'client_id'  => $this->client->id,
			'created_by' => $this->company->creatorId(),
		]);
		Project::factory()->create([
			'client_id'  => $this->company->id,
			'created_by' => $this->company->creatorId(),
		]);

		$response = $this->actingAs($this->client)
			->get(route('project_report.index'));

		$response->assertOk()
			->assertViewHas('projects', fn ($list) => $list->every(fn ($p) => $p->client_id === $this->client->id));
	}

	/**
	 ** @test
	 **
	 ** show_denies_without_permission
	 **
	 ** Users lacking 'view project report' cannot view details.
	 **/
	public function show_denies_without_permission()
	{
		Gate::before(fn () => false);
		$proj = Project::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company)
			->get(route('project_report.show', $proj->id))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** show_displays_details_for_company
	 **
	 ** Company users can view their project report details.
	 **/
	public function show_displays_details_for_company()
	{
		$proj = Project::factory()->create(['created_by' => $this->company->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('project_report.show', $proj->id));

		$response->assertOk()
			->assertViewIs('project_report.show')
			->assertViewHas('project', fn ($p) => $p->id === $proj->id);
	}

	/**
	 ** @test
	 **
	 ** getProjectChart_returns_correct_structure
	 **
	 ** The helper returns 'labels' and 'datasets' arrays.
	 **/
	public function getProjectChart_returns_correct_structure()
	{
		$ctrl = new \App\Http\Controllers\ProjectReportController;
		// create some stages
		TaskStage::factory()->count(3)->create(['created_by' => $this->company->creatorId()]);

		$chart = $ctrl->getProjectChart([
			'duration'   => 'week',
			'created_by' => $this->company->creatorId(),
		]);

		$this->assertArrayHasKey('labels', $chart);
		$this->assertArrayHasKey('datasets', $chart);
		$this->assertIsArray($chart['labels']);
		$this->assertIsArray($chart['datasets']);
	}

	/**
	 ** @test
	 **
	 ** export_denies_without_permission
	 **
	 ** Users lacking 'export project report' cannot download.
	 **/
	public function export_denies_without_permission()
	{
		Gate::before(fn () => false);
		$proj = Project::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company)
			->get(route('project_report.export', $proj->id))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** export_triggers_excel_download
	 **
	 ** Authorized users receive an Excel download response.
	 **/
	public function export_triggers_excel_download()
	{
		Excel::fake();

		$proj = Project::factory()->create(['created_by' => $this->company->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('project_report.export', $proj->id));

		Excel::assertDownloaded(
			fn ($filename, $export) => fn ($export) => $export instanceof \App\Exports\task_reportExport,
			$response->headers->get('content-disposition')
		);
	}
}
