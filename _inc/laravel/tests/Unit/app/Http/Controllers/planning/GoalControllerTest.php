<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GoalControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employee;

	protected function setUp(): void
	{
		parent::setUp();

		// define creatorId() on User
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		// by default allow all permissions; tests can override
		Gate::before(fn ($user, $ability) => true);

		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = User::factory()->create(['type' => 'Employee']);
	}

	/**
	 ** @test
	 **
	 ** Users without the 'manage goal' permission receive a 403 when accessing index.
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('goal.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Index should only list goals created by the authenticated user.
	 **/
	public function index_shows_only_user_goals()
	{
		// two goals by company, one by someone else
		Goal::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		Goal::factory()->create(['created_by' => User::factory()->create()->creatorId()]);

		$resp = $this->actingAs($this->company)
			->get(route('goal.index'));

		$resp->assertOk()
			->assertViewIs('goal.index')
			->assertViewHas('goals', function ($goals) {
				return $goals->count() === 2;
			});
	}

	/**
	 ** @test
	 **
	 ** Users without the 'create goal' permission receive a 403 when accessing create page.
	 **/
	public function create_requires_create_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('goal.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Users with 'create goal' permission can view the create form with available types.
	 **/
	public function create_displays_form()
	{
		$this->actingAs($this->company)
			->get(route('goal.create'))
			->assertOk()
			->assertViewIs('goal.create')
			->assertViewHas('types', fn ($t) => is_array($t));
	}

	/**
	 ** @test
	 **
	 ** Store should validate input, redirect back with error on failure,
	 ** and create a new goal for valid data.
	 **/
	public function store_validates_and_creates_goal()
	{
		// missing required fields
		$this->actingAs($this->company)
			->post(route('goal.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// valid data
		$payload = [
			'name'       => 'Reach 100 users',
			'type'       => array_key_first(Goal::$goalType),
			'from'       => now()->toDateString(),
			'to'         => now()->addWeek()->toDateString(),
			'amount'     => 100,
			'is_display' => '1',
		];

		$this->actingAs($this->company)
			->post(route('goal.store'), $payload)
			->assertRedirect(route('goal.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('goals', [
			'name'       => 'Reach 100 users',
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Show endpoint should always redirect back to the index.
	 **/
	public function show_always_redirects_to_index()
	{
		$goal = Goal::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company)
			->get(route('goal.show', $goal))
			->assertRedirect(route('goal.index'));
	}

	/**
	 ** @test
	 **
	 ** Edit page requires 'edit goal' permission and only the owner may access it.
	 **/
	public function edit_requires_edit_permission_and_owner()
	{
		$goal = Goal::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('goal.edit', $goal))
			->assertStatus(403);

		// with permission but wrong owner
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->get(route('goal.edit', $goal))
			->assertStatus(403);

		// owner with permission
		$this->actingAs($this->company)
			->get(route('goal.edit', $goal))
			->assertOk()
			->assertViewIs('goal.edit')
			->assertViewHasAll(['goal', 'types']);
	}

	/**
	 ** @test
	 **
	 ** Update should validate input, redirect back with error on failure,
	 ** and properly update the goal on valid data.
	 **/
	public function update_validates_and_updates_goal()
	{
		$goal = Goal::factory()->create([
			'created_by' => $this->company->creatorId(),
			'name'       => 'Old'
		]);

		// validation fail
		$this->actingAs($this->company)
			->put(route('goal.update', $goal), ['name' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		$data = [
			'name'       => 'New Name',
			'type'       => array_key_first(Goal::$goalType),
			'from'       => now()->toDateString(),
			'to'         => now()->addDay()->toDateString(),
			'amount'     => 200,
			'is_display' => '0',
		];

		$this->actingAs($this->company)
			->put(route('goal.update', $goal), $data)
			->assertRedirect(route('goal.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('goals', [
			'id'   => $goal->id,
			'name' => 'New Name',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy requires 'delete goal' permission and only owner may delete;
	 ** redirects with success and removes the record on success.
	 **/
	public function destroy_requires_delete_permission_and_owner()
	{
		$goal = Goal::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('goal.destroy', $goal))
			->assertStatus(403);

		// wrong owner
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->delete(route('goal.destroy', $goal))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->delete(route('goal.destroy', $goal))
			->assertRedirect(route('goal.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('goals', ['id' => $goal->id]);
	}
}
