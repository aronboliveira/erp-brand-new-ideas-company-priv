<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\TimesheetController;
use App\Models\{Project, ProjectTask, Timesheet, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, Gate};
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class TimesheetControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private Project $project;
	private ProjectTask $task;
	private Timesheet $timesheet;

	protected function setUp(): void
	{
		parent::setUp();

		// Allow all permissions by default
		Gate::before(fn () => true);

		// creatorId() returns own id
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

		// create & authenticate a company user
		$this->company = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);

		// set up a project, a task and a timesheet entry
		$this->project = Project::factory()->create([
			'created_by' => $this->company->creatorId(),
			'client_id'  => null,
		]);

		$this->task = ProjectTask::factory()->create([
			'project_id' => $this->project->id,
			'created_by' => $this->company->creatorId(),
		]);

		$this->timesheet = Timesheet::factory()->create([
			'project_id' => $this->project->id,
			'task_id'    => $this->task->id,
			'date'       => now()->toDateString(),
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** View should display the timesheet page for a project when user has permission.
	 **/
	public function test_view_displays_timesheet_page_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage timesheet']);
		$user?->givePermissionTo('manage timesheet');

		$project = Project::factory()->create([
			'created_by' => $user?->id,
			'project_name' => 'Test Project',
		]);

		$url = action([TimesheetController::class, 'view'], ['projectId' => $project->id]);
		$response = $this->actingAs($user)->get($url);

		$response->assertStatus(200)
			->assertViewIs('projects.timesheets.index')
			->assertViewHas('project', $project);
	}

	/**
	 ** @test
	 **
	 ** View should deny access to a project the user does not belong to.
	 **/
	public function test_view_denies_unauthorized_project_access()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage timesheet']);
		$user?->givePermissionTo('manage timesheet');

		$other = User::factory()->create();
		$project = Project::factory()->create(['created_by' => $other->id]);

		$url = action([TimesheetController::class, 'view'], ['projectId' => $project->id]);
		$response = $this->actingAs($user)->get($url);

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** appendTaskHtml should return an HTML snippet with inputs for each date.
	 **/
	public function test_appendTaskHtml_returns_html_snippet_for_task_and_dates()
	{
		$task = ProjectTask::factory()->create();
		$dates = '2025-05-01 - 2025-05-03';

		$response = $this->postJson(
			action([TimesheetController::class, 'appendTaskHtml']),
			[
				'project_id' => $task->project_id ?? null,
				'task_id'    => $task->id,
				'selected_dates' => $dates,
			]
		);

		$response->assertJson(['success' => true]);
		$html = $response->json('html');
		// Expect 3 inputs for dates 2025-05-01,05-02,05-03
		$this->assertEquals(3, substr_count($html, 'class="task-time"'));
		$this->assertStringContainsString('total-task-time', $html);
	}

	/**
	 ** @test
	 **
	 ** Create should display the timesheet form when user has permission.
	 **/
	public function test_create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create timesheet']);
		$user?->givePermissionTo('create timesheet');

		$project = Project::factory()->create([
			'created_by' => $user?->id,
			'project_name' => 'Test Project',
		]);
		$task = ProjectTask::factory()->create(['project_id' => $project->id]);
		$date = '2025-05-10';

		$url = action([TimesheetController::class, 'create'], ['projectId' => $project->id])
			. "?task_id={$task->id}&date={$date}";
		$response = $this->actingAs($user)->get($url);

		$response->assertStatus(200)
			->assertViewIs('projects.timesheets.create')
			->assertViewHas('project', $project)
			->assertViewHas('parseArray', function ($array) use ($task, $date, $project) {
				return $array['project_id'] === $project->id
					&& $array['task_id'] === $task->id
					&& $array['date'] === $date;
			});
	}

	/**
	 ** @test
	 **
	 ** Store should create a new timesheet and redirect back with success.
	 **/
	public function test_store_creates_timesheet_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create timesheet']);
		$user?->givePermissionTo('create timesheet');

		$project = Project::factory()->create(['created_by' => $user?->id]);
		$task = ProjectTask::factory()->create(['project_id' => $project->id]);
		$date = Carbon::today()->toDateString();

		$response = $this->actingAs($user)->post(
			action([TimesheetController::class, 'store']),
			[
				'project_id'   => $project->id,
				'task_id'      => $task->id,
				'date'         => $date,
				'time_hour'    => 2,
				'time_minute'  => 30,
				'description'  => 'Worked on feature X',
			]
		);

		$response->assertRedirect()
			->assertSessionHas('success', __('Timesheet Created Successfully!'));
		$this->assertDatabaseHas('timesheets', [
			'project_id'  => $project->id,
			'task_id'     => $task->id,
			'date'        => $date,
			'time'        => '02:30',
			'created_by'  => $user?->id,
			'description' => 'Worked on feature X',
		]);
	}

	/**
	 ** @test
	 **
	 ** Store should fail validation when required fields are missing.
	 **/
	public function test_store_fails_validation_with_missing_fields()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create timesheet']);
		$user?->givePermissionTo('create timesheet');

		$response = $this->actingAs($user)->post(
			action([TimesheetController::class, 'store']),
			[
				'project_id'  => '',
				'task_id'     => '',
				'date'        => '',
				'time_hour'   => '',
				'time_minute' => '',
			]
		);

		$response->assertStatus(302)
			->assertSessionHasErrors(['project_id', 'task_id', 'date', 'time_hour', 'time_minute']);
	}

	/**
	 ** @test
	 **
	 ** Edit should display the edit form for a timesheet when user has permission.
	 **/
	public function test_edit_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit timesheet']);
		$user?->givePermissionTo('edit timesheet');

		$project = Project::factory()->create(['created_by' => $user?->id]);
		$task = ProjectTask::factory()->create(['project_id' => $project->id]);
		$timesheet = Timesheet::factory()->create([
			'project_id' => $project->id,
			'task_id'    => $task->id,
			'time'       => '01:15',
			'created_by' => $user?->id,
		]);

		$url = action(
			[TimesheetController::class, 'edit'],
			['projectId' => $project->id, 'timesheetId' => $timesheet->id]
		);
		$response = $this->actingAs($user)->get($url);

		$response->assertStatus(200)
			->assertViewIs('projects.timesheets.edit')
			->assertViewHas('timesheet', $timesheet)
			->assertViewHas('parseArray');
	}

	/**
	 ** @test
	 **
	 ** Update should modify the timesheet and redirect back with success.
	 **/
	public function test_update_modifies_timesheet_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit timesheet']);
		$user?->givePermissionTo('edit timesheet');

		$project = Project::factory()->create(['created_by' => $user?->id]);
		$task = ProjectTask::factory()->create(['project_id' => $project->id]);
		$timesheet = Timesheet::factory()->create([
			'project_id'  => $project->id,
			'task_id'     => $task->id,
			'time'        => '00:45',
			'created_by'  => $user?->id,
		]);

		$response = $this->actingAs($user)->put(
			action([TimesheetController::class, 'update'], ['timesheetId' => $timesheet->id]),
			[
				'date'        => Carbon::today()->toDateString(),
				'time_hour'   => 3,
				'time_minute' => 0,
				'description' => 'Updated work',
			]
		);

		$response->assertRedirect()
			->assertSessionHas('success', __('Timesheet Updated Successfully!'));
		$this->assertDatabaseHas('timesheets', [
			'id'          => $timesheet->id,
			'time'        => '03:00',
			'description' => 'Updated work',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete the timesheet and redirect back with success.
	 **/
	public function test_destroy_deletes_timesheet_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete timesheet']);
		$user?->givePermissionTo('delete timesheet');

		$project = Project::factory()->create(['created_by' => $user?->id]);
		$task = ProjectTask::factory()->create(['project_id' => $project->id]);
		$timesheet = Timesheet::factory()->create([
			'project_id' => $project->id,
			'task_id'    => $task->id,
			'created_by' => $user?->id,
		]);

		$response = $this->actingAs($user)->delete(
			action([TimesheetController::class, 'destroy'], ['timesheetId' => $timesheet->id])
		);

		$response->assertRedirect()
			->assertSessionHas('success', __('Timesheet deleted Successfully!'));
		$this->assertModelMissing($timesheet);
	}

	/** @test
	 ** filterTimesheetTableView:
	 ** - missing week or project_id should redirect back with error
	 **/
	public function filter_missing_parameters_redirect_back_with_error()
	{
		$response = $this->postJson(route('timesheet.filter'));
		$response->assertStatus(302); // missing params triggers redirect via defaultUndefinedException
	}

	/** @test
	 ** filterTimesheetTableView:
	 ** - valid week & project_id=0 returns JSON with success, html, totalrecords, sectiontasks, selectedDate, onewWeekDate
	 **/
	public function filter_with_valid_params_returns_expected_json()
	{
		$response = $this->postJson(route('timesheet.filter'), [
			'week'       => 0,
			'project_id' => '0',
		]);

		$response->assertOk()
			->assertJsonStructure([
				'success',
				'totalrecords',
				'selectedDate',
				'sectiontasks',
				'onewWeekDate',
				'html',
			])
			->assertJson(['success' => true])
			->assertJsonFragment(['totalrecords' => 1]);
	}

	/** @test
	 ** timesheetList:
	 ** - renders the timesheet_list view for authenticated company user
	 **/
	public function timesheet_list_displays_view_for_company()
	{
		$response = $this->get(route('timesheet.list'));
		$response->assertOk()
			->assertViewIs('projects.timesheet_list');
	}

	/** @test
	 ** timesheetListGet:
	 ** - missing parameters should redirect back with error
	 **/
	public function list_get_missing_parameters_redirect_back()
	{
		$response = $this->getJson(route('timesheet.list.get'));
		$response->assertStatus(302);
	}

	/** @test
	 ** timesheetListGet:
	 ** - valid week & project_id=0 returns JSON with expected keys
	 **/
	public function list_get_with_valid_params_returns_expected_json()
	{
		// user must be member of project => attach via pivot
		$this->company->projects()->attach($this->project->id);

		$response = $this->getJson(route('timesheet.list.get', [
			'week'       => 0,
			'project_id' => '0',
		]));

		$response->assertOk()
			->assertJsonStructure([
				'success',
				'totalrecords',
				'selectedDate',
				'sectiontasks',
				'onewWeekDate',
				'html',
			])
			->assertJson(['success' => true])
			->assertJsonFragment(['totalrecords' => 1]);
	}

	/** @test
	 ** Guest is redirected to login for both endpoints
	 **/
	public function guest_redirected_to_login()
	{
		auth()->logout();

		$html = $this->post(route('timesheet.filter'), [
			'week'       => 0,
			'project_id' => '0',
		]);
		$html->assertRedirect();

		$list = $this->get(route('timesheet.list'));
		$list->assertRedirect();
	}

	/** @test
	 ** Permission denied at Gate => redirect to index
	 **/
	public function permission_denied_redirects_to_index()
	{
		Gate::before(fn () => false);

		$response = $this->postJson(route('timesheet.filter'), [
			'week'       => 0,
			'project_id' => '0',
		]);

		$response->assertStatus(302)
			->assertRedirect(route('timesheet.index'));
	}
}
