<?php

namespace Tests\Unit;

use App\Models\{JobStage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class JobStageControllerTest extends TestCase
{
	use RefreshDatabase;
	private User $user;
	private JobStage $stage;

	protected function setUp(): void
	{
		parent::setUp();

		// allow or deny per test
		Gate::before(fn () => true);

		// define creatorId macro
		User::macro(
			'creatorId',
			/**
			 ** @this \App\Models\User
			 ** @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		// create and authenticate a user
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		// create a JobStage owned by this user
		$this->stage = JobStage::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
	}
	/**
	 ** @test
	 **
	 ** index should display stages for users with 'manage job stage' permission
	 **/
	public function index_displays_stages_for_authorized_user()
	{
		Permission::create(['name' => 'manage job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage job stage');

		JobStage::create([
			'title'      => 'Stage A',
			'created_by' => $user?->creatorId(),
		]);
		JobStage::create([
			'title'      => 'Stage B',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('jobStage.index'));

		$response->assertStatus(200);
		$response->assertViewIs('jobStage.index');
		$response->assertViewHas('stages', function ($stages) {
			return $stages->count() === 2;
		});
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('jobStage.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create should display the form for users with 'create job stage' permission
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create job stage');

		$response = $this->actingAs($user)->get(route('jobStage.create'));

		$response->assertStatus(200);
		$response->assertViewIs('jobStage.create');
	}

	/**
	 ** @test
	 **
	 ** store should redirect back on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create job stage');

		$response = $this->actingAs($user)
			->from(route('jobStage.create'))
			->post(route('jobStage.store'), []);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store should create a new stage and redirect back on success
	 **/
	public function store_creates_stage_and_redirects_on_success()
	{
		Permission::create(['name' => 'create job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create job stage');

		$payload = ['title' => 'New Stage'];

		$response = $this->actingAs($user)
			->post(route('jobStage.store'), $payload);

		$response->assertRedirect();
		$this->assertDatabaseHas('job_stages', [
			'title'      => 'New Stage',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit should display the edit form for users with 'edit job stage' permission
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'edit job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit job stage');

		$stage = JobStage::create([
			'title'      => 'EditMe',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('jobStage.edit', $stage));

		$response->assertStatus(200);
		$response->assertViewIs('jobStage.edit');
		$response->assertViewHas('jobStage', $stage);
	}

	/**
	 ** @test
	 **
	 ** update should redirect back on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'edit job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit job stage');

		$stage = JobStage::create([
			'title'      => 'Old',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->from(route('jobStage.edit', $stage))
			->put(route('jobStage.update', $stage), ['title' => '']);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should modify the stage and redirect back on success
	 **/
	public function update_modifies_stage_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit job stage');

		$stage = JobStage::create([
			'title'      => 'OldTitle',
			'created_by' => $user?->creatorId(),
		]);

		$payload = ['title' => 'UpdatedTitle'];

		$response = $this->actingAs($user)
			->put(route('jobStage.update', $stage), $payload);

		$response->assertRedirect();
		$this->assertDatabaseHas('job_stages', [
			'id'    => $stage->id,
			'title' => 'UpdatedTitle',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the stage and redirect back on success
	 **/
	public function destroy_deletes_stage_and_redirects_on_success()
	{
		Permission::create(['name' => 'delete job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('delete job stage');

		$stage = JobStage::create([
			'title'      => 'ToDelete',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->delete(route('jobStage.destroy', $stage));

		$response->assertRedirect();
		$this->assertDatabaseMissing('job_stages', ['id' => $stage->id]);
	}

	/**
	 ** @test
	 **
	 ** order should update the order of stages
	 **/
	public function order_updates_stage_order()
	{
		Permission::create(['name' => 'edit job stage']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit job stage');

		$s1 = JobStage::create(['title' => 'A', 'created_by' => $user?->creatorId()]);
		$s2 = JobStage::create(['title' => 'B', 'created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->post(route('jobStage.order'), ['order' => [$s2->id, $s1->id]]);

		$this->assertDatabaseHas('job_stages', ['id' => $s1->id, 'order' => 1]);
		$this->assertDatabaseHas('job_stages', ['id' => $s2->id, 'order' => 0]);
	}

	/**
	 ** @test
	 **
	 ** Guests accessing the show route are redirected to login.
	 **/
	public function show_redirects_guests_to_login()
	{
		auth()->logout();

		$response = $this->get(route('jobStage.show', $this->stage->id));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** It denies access when the user lacks manage permission.
	 **/
	public function show_denies_without_permission()
	{
		Gate::before(fn () => false);

		$response = $this->get(route('jobStage.show', $this->stage->id));

		$response->assertRedirect(route('job-stage.index'));
	}

	/**
	 ** @test
	 **
	 ** It denies access if the stage was created by another user.
	 **/
	public function show_denies_non_owner()
	{
		Gate::before(fn () => true);

		$other = User::factory()->create();
		$this->actingAs($other);

		$response = $this->get(route('jobStage.show', $this->stage->id));

		$response->assertRedirect(route('job-stage.index'));
	}

	/**
	 ** @test
	 **
	 ** It displays the detail view for the owner with permission.
	 **/
	public function show_displays_view_for_owner()
	{
		Gate::before(fn () => true);

		$response = $this->get(route('jobStage.show', $this->stage->id));

		$response->assertOk()
			->assertViewIs('jobStage.show')
			->assertViewHas('jobStage', fn ($s) => $s->id === $this->stage->id);
	}
}
