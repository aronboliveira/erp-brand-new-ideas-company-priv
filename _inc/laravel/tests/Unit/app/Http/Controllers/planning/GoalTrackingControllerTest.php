<?php

namespace Tests\Feature;

use App\Models\{Branch, Employee, GoalTracking, GoalType, User};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GoalTrackingControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employee;
	private Branch $branch;
	private GoalType $goalType;

	protected function setUp(): void
	{
		parent::setUp();

		// allow or deny in each test
		Gate::before(fn () => true);

		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = User::factory()->create(['type' => 'Employee']);

		// common lookup data
		$this->branch  = Branch::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->goalType = GoalType::factory()->create(['created_by' => $this->company->creatorId()]);

		// link an Employee record for the employee user
		Employee::factory()->create([
			'user_id'    => $this->employee->id,
			'branch_id'  => $this->branch->id,
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** index_requires_manage_permission:
	 **   - if the user lacks 'manage goal tracking' permission, returns 403
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('goaltracking.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_shows_all_for_company:
	 **   - seeds two goal trackings under the company’s branch
	 **   - asserts the index view shows both
	 **/
	public function index_shows_all_for_company()
	{
		GoalTracking::factory()->count(2)->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'goal_type'  => $this->goalType->id,
		]);

		$resp = $this->actingAs($this->company)
			->get(route('goaltracking.index'));

		$resp->assertOk()
			->assertViewIs('goaltracking.index')
			->assertViewHas('goalTrackings', fn ($list) => $list->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** index_filters_by_employee_branch:
	 **   - seeds one tracking in the employee’s branch and one in another
	 **   - as that employee, only the same-branch record is returned
	 **/
	public function index_filters_by_employee_branch()
	{
		$otherBranch = Branch::factory()->create(['created_by' => $this->company->creatorId()]);

		GoalTracking::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'goal_type'  => $this->goalType->id,
		]);
		GoalTracking::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $otherBranch->id,
			'goal_type'  => $this->goalType->id,
		]);

		$resp = $this->actingAs($this->employee)
			->get(route('goaltracking.index'));

		$resp->assertOk()
			->assertViewHas('goalTrackings', fn ($list) => $list->count() === 1);
	}

	/**
	 ** @test
	 **
	 ** create_requires_permission:
	 **   - denies access to the creation form when lacking 'create goal tracking'
	 **/
	public function create_requires_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('goaltracking.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form:
	 **   - as a permitted company user, shows the create form with necessary data
	 **/
	public function create_displays_form()
	{
		$resp = $this->actingAs($this->company)
			->get(route('goaltracking.create'));

		$resp->assertOk()
			->assertViewIs('goaltracking.create')
			->assertViewHasAll(['branches', 'goalTypes', 'status']);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates:
	 **   - first submits empty data and expects validation error
	 **   - then submits complete payload and asserts a new record is saved
	 **/
	public function store_validates_and_creates()
	{
		// validation failure
		$this->actingAs($this->company)
			->post(route('goaltracking.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// successful creation
		$payload = [
			'branch'             => $this->branch->id,
			'goal_type'          => $this->goalType->id,
			'start_date'         => now()->toDateString(),
			'end_date'           => now()->addDay()->toDateString(),
			'subject'            => 'Test Subject',
			'target_achievement' => '50%',
			'description'        => 'Details',
		];

		$this->actingAs($this->company)
			->post(route('goaltracking.store'), $payload)
			->assertRedirect(route('goaltracking.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('goal_trackings', [
			'branch'    => $this->branch->id,
			'goal_type' => $this->goalType->id,
			'subject'   => 'Test Subject',
		]);
	}

	/**
	 ** @test
	 **
	 ** edit_requires_permission_and_owner:
	 **   - denies access without 'edit goal tracking' or when not the creator
	 **   - owner with permission can view the edit form
	 **/
	public function edit_requires_permission_and_owner()
	{
		$gt = GoalTracking::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'goal_type'  => $this->goalType->id,
		]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('goaltracking.edit', $gt->id))
			->assertStatus(403);

		// wrong owner
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->get(route('goaltracking.edit', $gt->id))
			->assertStatus(403);

		// success as owner
		$this->actingAs($this->company)
			->get(route('goaltracking.edit', $gt->id))
			->assertOk()
			->assertViewIs('goaltracking.edit')
			->assertViewHasAll(['branches', 'goalTypes', 'goalTracking', 'ratings', 'status']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves_changes:
	 **   - fails on missing required fields
	 **   - then updates the record with new data and persists it
	 **/
	public function update_validates_and_saves_changes()
	{
		$gt = GoalTracking::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'goal_type'  => $this->goalType->id,
			'subject'    => 'Old'
		]);

		// validation failure
		$this->actingAs($this->company)
			->put(route('goaltracking.update', $gt->id), ['branch' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// successful update
		$data = [
			'branch'             => $this->branch->id,
			'goal_type'          => $this->goalType->id,
			'start_date'         => now()->toDateString(),
			'end_date'           => now()->addDay()->toDateString(),
			'subject'            => 'New',
			'target_achievement' => '75%',
			'status'             => GoalTracking::$status[0],
			'progress'           => '10%',
			'description'        => 'Updated',
			'rating'             => ['good', 'better'],
		];

		$this->actingAs($this->company)
			->put(route('goaltracking.update', $gt->id), $data)
			->assertRedirect(route('goaltracking.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('goal_trackings', [
			'id'      => $gt->id,
			'subject' => 'New',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_requires_permission_and_owner:
	 **   - denies deletion without 'delete goal tracking' or when not the creator
	 **   - allows and removes the record for the creator with permission
	 **/
	public function destroy_requires_permission_and_owner()
	{
		$gt = GoalTracking::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'goal_type'  => $this->goalType->id,
		]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('goaltracking.destroy', $gt->id))
			->assertStatus(403);

		// wrong owner
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->delete(route('goaltracking.destroy', $gt->id))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->delete(route('goaltracking.destroy', $gt->id))
			->assertRedirect(route('goaltracking.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('goal_trackings', ['id' => $gt->id]);
	}

	/**
	 ** @test
	 **
	 ** Unauthenticated users are redirected to login when accessing show.
	 **/
	public function show_redirects_guests_to_login()
	{
		auth()->logout();

		$response = $this->get(route('goaltracking.show', $this->goalType->id));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** It should deny access when user lacks 'view goal tracking' permission.
	 **/
	public function show_denies_without_permission()
	{
		Gate::before(fn () => false);

		$response = $this->get(route('goaltracking.show', $this->goalType->id));

		$response->assertRedirect(route('goaltracking.index'));
	}

	/**
	 ** @test
	 **
	 ** It should deny access when the user is not the owner of the goal tracking.
	 **/
	public function show_denies_non_owner()
	{
		// allow permissions
		Gate::before(fn () => true);

		// login as different user
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('goaltracking.show', $this->goalType->id));

		$response->assertRedirect(route('goaltracking.index'));
	}

	/**
	 ** @test
	 **
	 ** It should display the goal tracking view for the owner with permission.
	 **/
	public function show_displays_view_for_owner_with_permission()
	{
		Gate::before(fn () => true);

		$response = $this->get(route('goaltracking.show', $this->goalType->id));

		$response->assertStatus(200)
			->assertViewIs('goaltracking.show')
			->assertViewHas('goalTracking', fn ($g) => $g->id === $this->goalType->id);
	}
}
