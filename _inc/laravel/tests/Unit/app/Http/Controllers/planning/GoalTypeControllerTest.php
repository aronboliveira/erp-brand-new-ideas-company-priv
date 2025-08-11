<?php

namespace Tests\Feature;

use App\Models\GoalType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GoalTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// By default grant all permissions
		Gate::before(fn () => true);

		// Macro so creatorId() returns the user's own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->company = User::factory()->create(['type' => 'company']);
		$this->other  = User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** Index requires manage permission and returns 403 when unauthorized.
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('goaltype.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Index lists only the goal types created by the authenticated user.
	 **/
	public function index_lists_only_user_goal_types()
	{
		GoalType::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		GoalType::factory()->create(['created_by' => $this->other->creatorId()]);

		$resp = $this->actingAs($this->company)
			->get(route('goaltype.index'));

		$resp->assertOk()
			->assertViewIs('goaltype.index')
			->assertViewHas('goalTypes', function ($list) {
				return $list->count() === 2;
			});
	}

	/**
	 ** @test
	 **
	 ** Create page requires permission and returns 403 when unauthorized.
	 **/
	public function create_requires_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('goaltype.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Create page displays the form when authorized.
	 **/
	public function create_displays_form()
	{
		$this->actingAs($this->company)
			->get(route('goaltype.create'))
			->assertOk()
			->assertViewIs('goaltype.create');
	}

	/**
	 ** @test
	 **
	 ** Store validates input and creates a new goal type on success.
	 **/
	public function store_validates_and_creates_goal_type()
	{
		// Validation should fail on missing name
		$this->actingAs($this->company)
			->post(route('goaltype.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// Valid submission creates a record
		$this->actingAs($this->company)
			->post(route('goaltype.store'), ['name' => 'New Type'])
			->assertRedirect(route('goaltype.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('goal_types', [
			'name'       => 'New Type',
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Show requires manage permission and displays the specified goal type.
	 **/
	public function show_requires_manage_permission_and_displays_goal_type()
	{
		$gt = GoalType::factory()->create(['created_by' => $this->company->creatorId()]);

		// Without permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('goaltype.show', $gt))
			->assertStatus(403);

		// With permission
		Gate::before(fn () => true);
		$this->actingAs($this->company)
			->get(route('goaltype.show', $gt))
			->assertOk()
			->assertViewIs('goaltype.show')
			->assertViewHas('goalType', fn ($v) => $v->id === $gt->id);
	}

	/**
	 ** @test
	 **
	 ** Edit requires permission and owner check, then displays the form.
	 **/
	public function edit_requires_permission_and_displays_form()
	{
		$gt = GoalType::factory()->create(['created_by' => $this->company->creatorId()]);

		// Without permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('goaltype.edit', $gt->id))
			->assertStatus(403);

		// With permission
		Gate::before(fn () => true);
		$this->actingAs($this->company)
			->get(route('goaltype.edit', $gt->id))
			->assertOk()
			->assertViewIs('goaltype.edit')
			->assertViewHas('goalType', fn ($v) => $v->id === $gt->id);
	}

	/**
	 ** @test
	 **
	 ** Update validates input and updates the goal type on success.
	 **/
	public function update_validates_and_updates_goal_type()
	{
		$gt = GoalType::factory()->create([
			'name'       => 'Old',
			'created_by' => $this->company->creatorId(),
		]);

		// Validation failure
		$this->actingAs($this->company)
			->put(route('goaltype.update', $gt->id), ['name' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// Successful update
		$this->actingAs($this->company)
			->put(route('goaltype.update', $gt->id), ['name' => 'Updated'])
			->assertRedirect(route('goaltype.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('goal_types', [
			'id'   => $gt->id,
			'name' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy requires permission and owner check, then deletes the goal type.
	 **/
	public function destroy_requires_permission_and_owner_and_deletes()
	{
		$gt = GoalType::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);

		// Without permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('goaltype.destroy', $gt->id))
			->assertStatus(403);

		// Wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->delete(route('goaltype.destroy', $gt->id))
			->assertRedirect(route('goaltype.index'))
			->assertSessionHas('error');

		// Successful deletion
		$this->actingAs($this->company)
			->delete(route('goaltype.destroy', $gt->id))
			->assertRedirect(route('goaltype.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('goal_types', ['id' => $gt->id]);
	}
}
