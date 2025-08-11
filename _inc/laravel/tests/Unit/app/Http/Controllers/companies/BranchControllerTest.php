<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Grant all permissions in these tests
		Gate::before(fn () => true);

		// Ensure creatorId() returns the user’s own ID
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 */
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);
	}

	/**
	 ** @test
	 **
	 ** When visiting the index, only branches created by the current user should be listed.
	 **/
	public function index_shows_only_user_branches()
	{
		$user   = User::factory()->create();
		$other  = User::factory()->create();
		$branch1 = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$branch2 = Branch::factory()->create(['created_by' => $other->creatorId()]);

		$response = $this->actingAs($user)->get(route('branch.index'));

		$response->assertStatus(200)
			->assertViewIs('branch.index')
			->assertViewHas(
				'branches',
				fn ($list) =>
				$list->pluck('id')->all() === [$branch1->id]
			);
	}

	/**
	 ** @test
	 **
	 ** The create page should be accessible to an authenticated user.
	 **/
	public function create_page_is_accessible()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)->get(route('branch.create'));

		$response->assertStatus(200)
			->assertViewIs('branch.create');
	}

	/**
	 ** @test
	 **
	 ** Submitting the store form with valid data should create a new branch and redirect to index.
	 **/
	public function store_creates_branch_and_redirects()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->post(route('branch.store'), ['name' => 'New Branch']);

		$response->assertRedirect(route('branch.index'));
		$this->assertDatabaseHas('branches', [
			'name'       => 'New Branch',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** If validation fails on store (e.g. missing name), the user is redirected back with an error.
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->post(route('branch.store'), []); // missing name

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** The show route should not display a branch and instead redirect back to index.
	 **/
	public function show_redirects_to_index()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->get(route('branch.show', ['branch' => 123]));

		$response->assertRedirect(route('branch.index'));
	}

	/**
	 ** @test
	 **
	 ** The edit page should display the existing branch data for the owner.
	 **/
	public function edit_shows_existing_branch()
	{
		$user  = User::factory()->create();
		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->get(route('branch.edit', $branch));

		$response->assertStatus(200)
			->assertViewIs('branch.edit')
			->assertViewHas('branch', fn ($b) => $b->id === $branch->id);
	}

	/**
	 ** @test
	 **
	 ** Updating a branch with valid data should change its name and redirect to index.
	 **/
	public function update_changes_branch_name()
	{
		$user  = User::factory()->create();
		$branch = Branch::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Old',
		]);

		$response = $this->actingAs($user)
			->put(route('branch.update', $branch), ['name' => 'Updated']);

		$response->assertRedirect(route('branch.index'));
		$this->assertDatabaseHas('branches', [
			'id'   => $branch->id,
			'name' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** If update validation fails (e.g. empty name), the user is redirected back with an error.
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		$user  = User::factory()->create();
		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->put(route('branch.update', $branch), ['name' => '']);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Deleting a branch should remove it from the database and redirect to index.
	 **/
	public function destroy_deletes_branch()
	{
		$user  = User::factory()->create();
		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->delete(route('branch.destroy', $branch));

		$response->assertRedirect(route('branch.index'));
		$this->assertDatabaseMissing('branches', ['id' => $branch->id]);
	}

	/**
	 ** @test
	 **
	 ** getDepartment should return all departments when branch_id is zero.
	 **/
	public function get_department_returns_all_when_branch_id_zero()
	{
		$user = User::factory()->create();
		$d1  = Department::factory()->create(['branch_id' => 1]);
		$d2  = Department::factory()->create(['branch_id' => 2]);

		$response = $this->actingAs($user)
			->getJson(route('branch.getDepartment', ['branch_id' => 0]));

		$response->assertStatus(200)
			->assertJsonCount(2)
			->assertJsonFragment([$d1->id => $d1->name])
			->assertJsonFragment([$d2->id => $d2->name]);
	}

	/**
	 ** @test
	 **
	 ** getDepartment should filter and return only departments matching the given branch_id.
	 **/
	public function get_department_filters_by_branch_id()
	{
		$user = User::factory()->create();
		$d1  = Department::factory()->create(['branch_id' => 1]);
		Department::factory()->create(['branch_id' => 2]);

		$response = $this->actingAs($user)
			->getJson(route('branch.getDepartment', ['branch_id' => 1]));

		$response->assertStatus(200)
			->assertExactJson([
				$d1->id => $d1->name,
			]);
	}

	/**
	 ** @test
	 **
	 ** getEmployee should return all employees if department_id contains zero.
	 **/
	public function get_employee_returns_all_when_dept_contains_zero()
	{
		$user = User::factory()->create();
		$e1  = Employee::factory()->create(['department_id' => 1]);
		$e2  = Employee::factory()->create(['department_id' => 2]);

		$response = $this->actingAs($user)
			->getJson(route('branch.getEmployee', ['department_id' => [0]]));

		$response->assertStatus(200)
			->assertJsonCount(2)
			->assertJsonFragment([$e1->id => $e1->name])
			->assertJsonFragment([$e2->id => $e2->name]);
	}

	/**
	 ** @test
	 **
	 ** getEmployee should filter and return only employees matching the given department_ids.
	 **/
	public function get_employee_filters_by_department_ids()
	{
		$user = User::factory()->create();
		$e1  = Employee::factory()->create(['department_id' => 1]);
		Employee::factory()->create(['department_id' => 2]);

		$response = $this->actingAs($user)
			->getJson(route('branch.getEmployee', ['department_id' => [1]]));

		$response->assertStatus(200)
			->assertExactJson([
				$e1->id => $e1->name,
			]);
	}
}
