<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\{TaskStage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class TaskStageControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private TaskStage $stage;

	protected function setUp(): void
	{
		parent::setUp();

		// Allow all permissions by default
		Gate::before(fn () => true);

		// Make creatorId() return the user's own ID
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

		// Create and authenticate a company user
		$this->company = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);

		// Create a TaskStage owned by this user
		$this->stage = TaskStage::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** index method should display the task stages for a user with 'manage project task stage' permission.
	 **/
	public function index_displays_task_stages_for_authorized_user()
	{
		Permission::create(['name' => 'manage project task stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage project task stage');

		TaskStage::create([
			'name'       => 'Stage1',
			'order'      => 0,
			'color'      => '#AABBCC',
			'created_by' => $user?->creatorId()
		]);

		$response = $this->actingAs($user)->get(route('project-task-stages.index'));

		$response->assertStatus(200);
		$response->assertViewIs('task_stage.index');
		$response->assertViewHas('stages', function ($stages) use ($user) {
			return $stages->count() === 1
				&& $stages->first()->created_by === $user?->creatorId();
		});
	}

	/**
	 ** @test
	 **
	 ** index method should redirect to login for guests.
	 **/
	public function index_redirects_to_login_for_guests()
	{
		$response = $this->get(route('project-task-stages.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create method should display the create view for users with 'create project task stage' permission.
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create project task stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create project task stage');

		$response = $this->actingAs($user)->get(route('project-task-stages.create'));

		$response->assertStatus(200);
		$response->assertViewIs('task_stage.create');
	}

	/**
	 ** @test
	 **
	 ** storingValue should redirect back with error on validation failure.
	 **/
	public function storingValue_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create project task stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create project task stage');

		$response = $this->actingAs($user)
			->from(route('project-task-stages.create'))
			->post(route('project-task-stages.storingValue'), [
				'name'  => '',
				'color' => 'ZZZZZZ'
			]);

		$response->assertRedirect(route('project-task-stages.create'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** storingValue should create a new stage and redirect to index on success.
	 **/
	public function storingValue_creates_stage_and_redirects_on_success()
	{
		Permission::create(['name' => 'create project task stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create project task stage');

		$payload = ['name' => 'New Stage', 'color' => '123456'];
		$response = $this->actingAs($user)
			->post(route('project-task-stages.storingValue'), $payload);

		$response->assertRedirect(route('project-task-stages.index'));
		$this->assertDatabaseHas('task_stages', [
			'name'       => 'New Stage',
			'color'      => '#123456',
			'created_by' => $user?->creatorId()
		]);
	}

	/**
	 ** @test
	 **
	 ** order method should update the order of stages and return success JSON.
	 **/
	public function order_updates_stage_order()
	{
		Permission::create(['name' => 'manage project task stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage project task stage');

		$stage1 = TaskStage::create([
			'name'       => 'Stage1',
			'order'      => 0,
			'color'      => '#000000',
			'created_by' => $user?->creatorId()
		]);
		$stage2 = TaskStage::create([
			'name'       => 'Stage2',
			'order'      => 1,
			'color'      => '#000000',
			'created_by' => $user?->creatorId()
		]);

		$response = $this->actingAs($user)
			->post(route('project-task-stages.order'), [
				'order' => [$stage2->id, $stage1->id]
			]);

		$response->assertJson(['success' => true]);
		$this->assertDatabaseHas('task_stages', ['id' => $stage1->id, 'order' => 1]);
		$this->assertDatabaseHas('task_stages', ['id' => $stage2->id, 'order' => 0]);
	}

	/**
	 ** @test
	 **
	 ** Owner can view the stage in HTML.
	 **/
	public function owner_sees_html_view()
	{
		$response = $this->get(route('task-stage.show', $this->stage));

		$response->assertOk()
			->assertViewIs('task_stage.show')
			->assertViewHas('taskStage', fn ($s) => $s->id === $this->stage->id);
	}

	/**
	 ** @test
	 **
	 ** Owner can fetch the stage as JSON when requested.
	 **/
	public function owner_can_fetch_json()
	{
		$response = $this->getJson(route('task-stage.show', $this->stage));

		$response->assertOk()
			->assertExactJson($this->stage->toArray());
	}

	/**
	 ** @test
	 **
	 ** Non-owners get a 403 Forbidden response.
	 **/
	public function non_owner_gets_forbidden()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('task-stage.show', $this->stage));
		$response->assertStatus(403);

		$json = $this->getJson(route('task-stage.show', $this->stage));
		$json->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when attempting HTML.
	 **/
	public function guest_html_redirects_to_login()
	{
		auth()->logout();

		$response = $this->get(route('task-stage.show', $this->stage));
		$response->assertRedirect(); // login page
	}

	/**
	 ** @test
	 **
	 ** Guests receive 401 JSON when requesting JSON.
	 **/
	public function guest_json_gets_unauthorized()
	{
		auth()->logout();

		$response = $this->getJson(route('task-stage.show', $this->stage));
		$response->assertStatus(401);
	}

	/**
	 ** @test
	 **
	 ** Permission denied via Gate returns a redirect back to index.
	 **/
	public function permission_denied_redirects_to_index()
	{
		// Deny all permissions
		Gate::before(fn () => false);

		$response = $this->get(route('task-stage.show', $this->stage));
		$response->assertRedirect(route('task-stage.index'));
	}
}
