<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\DepartmentController;

class DepartmentControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		// create a test user
		$this->user = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login and users without
	 ** the 'manage department' permission should be redirected to '/'.
	 ** Once granted, the index action lists departments the user created.
	 **/
	public function index_redirects_guests_and_requires_manage_permission()
	{
		// guest is redirected to login
		$resp = $this->get(action([DepartmentController::class, 'index']));
		$resp->assertRedirect();

		// authenticated without permission redirects to '/'
		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'index']));
		$resp->assertRedirect('/');

		// grant permission and seed a department
		$this->user->givePermissionTo('manage department');
		Department::create([
			'branch_id'  => Branch::factory()->create([
				'created_by' => $this->user->creatorId()
			])->id,
			'name'       => 'HR',
			'created_by' => $this->user->creatorId(),
		]);

		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'index']));

		$resp->assertOk()
			->assertViewIs('department.index')
			->assertViewHas('departments', function ($deps) {
				return $deps->contains('name', 'HR');
			});
	}

	/**
	 ** @test
	 **
	 ** The create form requires 'create department' permission
	 ** and populates the branch dropdown with branches the user created.
	 **/
	public function create_requires_permission_and_displays_branch_list()
	{
		// without permission → '/'
		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'create']));
		$resp->assertRedirect('/');

		// grant permission & seed a branch
		$this->user->givePermissionTo('create department');
		$branch = Branch::create([
			'name'       => 'Main Office',
			'created_by' => $this->user->creatorId(),
		]);

		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'create']));

		$resp->assertOk()
			->assertViewIs('department.create')
			->assertViewHas('branch', function ($b) use ($branch) {
				return isset($b[$branch->id]) && $b[$branch->id] === 'Main Office';
			});
	}

	/**
	 ** @test
	 **
	 ** Posting to store without required fields returns with errors.
	 ** A valid payload creates a new department and redirects with success.
	 **/
	public function store_validates_and_creates_department()
	{
		$this->user->givePermissionTo('create department');
		$branch = Branch::create([
			'name'       => 'HQ',
			'created_by' => $this->user->creatorId(),
		]);

		// missing fields → validation error
		$resp = $this->actingAs($this->user)
			->post(action([DepartmentController::class, 'store']), []);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// valid payload → created
		$resp = $this->actingAs($this->user)
			->post(action([DepartmentController::class, 'store']), [
				'branch_id' => $branch->id,
				'name'      => 'Finance',
			]);

		$resp->assertRedirect(route('department.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('departments', [
			'name'      => 'Finance',
			'branch_id' => $branch->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** The edit form requires 'edit department' permission and ownership.
	 ** Unauthorized or non-owner requests redirect or error.
	 ** A valid owner sees the edit form with department and branch data.
	 **/
	public function edit_requires_permission_and_owner_and_shows_form()
	{
		$branch = Branch::create([
			'name'       => 'Branch A',
			'created_by' => $this->user->creatorId(),
		]);
		$dept = Department::create([
			'branch_id'  => $branch->id,
			'name'       => 'Legal',
			'created_by' => $this->user->creatorId(),
		]);

		// without permission → '/'
		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'edit'], ['department' => $dept->id]));
		$resp->assertRedirect('/');

		// grant permission but wrong owner → permission denial
		$this->user->givePermissionTo('edit department');
		$otherDept = Department::create([
			'branch_id'  => $branch->id,
			'name'       => 'Ops',
			'created_by' => $this->user->creatorId() + 1,
		]);
		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'edit'], ['department' => $otherDept->id]));
		$resp->assertSessionHas('error');

		// correct owner & permission → show form
		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'edit'], ['department' => $dept->id]));
		$resp->assertOk()
			->assertViewIs('department.edit')
			->assertViewHasAll(['department', 'branch']);
	}

	/**
	 ** @test
	 **
	 ** update redirects with error if validation fails.
	 ** A valid update changes the department record and redirects with success.
	 **/
	public function update_validates_and_updates_department()
	{
		$branch = Branch::create([
			'name'       => 'Branch B',
			'created_by' => $this->user->creatorId(),
		]);
		$dept = Department::create([
			'branch_id'  => $branch->id,
			'name'       => 'Support',
			'created_by' => $this->user->creatorId(),
		]);

		$this->user->givePermissionTo('edit department');

		// missing fields → validation error
		$resp = $this->actingAs($this->user)
			->put(action([DepartmentController::class, 'update'], ['department' => $dept->id]), []);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// valid update
		$resp = $this->actingAs($this->user)
			->put(action([DepartmentController::class, 'update'], ['department' => $dept->id]), [
				'branch_id' => $branch->id,
				'name'      => 'Customer Support',
			]);

		$resp->assertRedirect(route('department.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('departments', [
			'id'         => $dept->id,
			'name'       => 'Customer Support',
			'branch_id'  => $branch->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy requires 'delete department' permission and ownership.
	 ** Unauthorized attempts redirect or error.
	 ** A valid delete removes the department and redirects with success.
	 **/
	public function destroy_requires_permission_and_owner_and_deletes_department()
	{
		$branch = Branch::create([
			'name'       => 'Branch C',
			'created_by' => $this->user->creatorId(),
		]);
		$dept = Department::create([
			'branch_id'  => $branch->id,
			'name'       => 'Admin',
			'created_by' => $this->user->creatorId(),
		]);

		// without permission → '/'
		$resp = $this->actingAs($this->user)
			->delete(action([DepartmentController::class, 'destroy'], ['department' => $dept->id]));
		$resp->assertRedirect('/');

		// grant permission but wrong owner → error
		$this->user->givePermissionTo('delete department');
		$otherDept = Department::create([
			'branch_id'  => $branch->id,
			'name'       => 'Temp',
			'created_by' => $this->user->creatorId() + 1,
		]);
		$resp = $this->actingAs($this->user)
			->delete(action([DepartmentController::class, 'destroy'], ['department' => $otherDept->id]));
		$resp->assertSessionHas('error');

		// correct owner & permission → deleted
		$resp = $this->actingAs($this->user)
			->delete(action([DepartmentController::class, 'destroy'], ['department' => $dept->id]));
		$resp->assertRedirect(route('department.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('departments', ['id' => $dept->id]);
	}

	/**
	 ** @test
	 **
	 ** The show route is not used and simply redirects to index.
	 **/
	public function show_redirects_to_index()
	{
		$resp = $this->actingAs($this->user)
			->get(action([DepartmentController::class, 'show']));
		$resp->assertRedirect(route('department.index'));
	}
}
