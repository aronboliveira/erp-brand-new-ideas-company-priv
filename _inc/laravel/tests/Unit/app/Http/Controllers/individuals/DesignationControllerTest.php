<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;

class DesignationControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		Permission::create(['name' => 'manage designation']);
		Permission::create(['name' => 'create designation']);
		Permission::create(['name' => 'edit designation']);
		Permission::create(['name' => 'delete designation']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing index.
	 **/
	public function guest_redirected_to_login_on_index()
	{
		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'index']));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Users without manage permission are redirected from index.
	 **/
	public function user_without_manage_permission_cannot_access_index()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'index']));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with manage permission see the index view with designations.
	 **/
	public function user_with_manage_permission_can_view_index()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage designation');

		Designation::create([
			'department_id' => 1,
			'name'          => 'Test Designation',
			'created_by'    => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'index']));

		$response->assertStatus(200);
		$response->assertViewIs('designation.index');
		$response->assertViewHas('designations', function ($list) {
			return $list->first()->name === 'Test Designation';
		});
	}

	/**
	 ** @test
	 **
	 ** Users without create permission are redirected from create.
	 **/
	public function user_without_create_permission_cannot_access_create()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'create']));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with create permission see the create form.
	 **/
	public function user_with_create_permission_can_view_create()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create designation');

		Department::create([
			'name'       => 'HR',
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'create']));

		$response->assertStatus(200);
		$response->assertViewIs('designation.create');
		$response->assertViewHas('departmentList');
	}

	/**
	 ** @test
	 **
	 ** Store redirects back on validation error.
	 **/
	public function store_redirects_back_on_validation_error()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create designation');

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\DesignationController::class, 'store']), [
			// missing departmentId and name
		]);

		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Store creates designation and redirects on success.
	 **/
	public function store_creates_designation_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create designation');

		$dept = Department::create([
			'name'       => 'Finance',
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\DesignationController::class, 'store']), [
			'departmentId' => $dept->id,
			'name'         => 'Analyst',
		]);

		$response->assertRedirect(route('designation.index'));
		$this->assertDatabaseHas('designations', [
			'department_id' => $dept->id,
			'name'          => 'Analyst',
			'created_by'    => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Show always redirects to the index route.
	 **/
	public function show_redirects_to_index()
	{
		$designation = Designation::factory()->create();
		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'show'], $designation));
		$response->assertRedirect(route('designation.index'));
	}

	/**
	 ** @test
	 **
	 ** Users without edit permission are redirected from edit.
	 **/
	public function user_without_edit_permission_cannot_access_edit()
	{
		$user = User::factory()->create();
		$designation = Designation::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'edit'], $designation));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Non-owners are denied access to edit.
	 **/
	public function non_owner_cannot_access_edit()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$other->givePermissionTo('edit designation');

		$designation = Designation::factory()->create(['created_by' => $owner->creatorId()]);

		$this->actingAs($other);
		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'edit'], $designation));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Owners with edit permission see the edit form.
	 **/
	public function owner_with_edit_permission_can_view_edit()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit designation');

		$dept = Department::create([
			'name'       => 'IT',
			'created_by' => $user?->creatorId(),
		]);

		$designation = Designation::create([
			'department_id' => $dept->id,
			'name'          => 'Developer',
			'created_by'    => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\DesignationController::class, 'edit'], $designation));

		$response->assertStatus(200);
		$response->assertViewIs('designation.edit');
		$response->assertViewHasAll(['designation', 'departmentList']);
	}

	/**
	 ** @test
	 **
	 ** Update redirects back on validation error.
	 **/
	public function update_redirects_back_on_validation_error()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit designation');

		$designation = Designation::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->put(action([\App\Http\Controllers\DesignationController::class, 'update'], $designation), [
			// missing fields
		]);

		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Update modifies designation and redirects on success.
	 **/
	public function update_modifies_designation_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit designation');

		$dept = Department::create([
			'name'       => 'Sales',
			'created_by' => $user?->creatorId(),
		]);

		$designation = Designation::create([
			'department_id' => $dept->id,
			'name'          => 'Rep',
			'created_by'    => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->put(action([\App\Http\Controllers\DesignationController::class, 'update'], $designation), [
			'departmentId' => $dept->id,
			'name'         => 'Senior Rep',
		]);

		$response->assertRedirect(route('designation.index'));
		$this->assertDatabaseHas('designations', [
			'id'   => $designation->id,
			'name' => 'Senior Rep',
		]);
	}

	/**
	 ** @test
	 **
	 ** Users without delete permission are redirected from destroy.
	 **/
	public function user_without_delete_permission_cannot_destroy()
	{
		$user = User::factory()->create();
		$designation = Designation::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->delete(action([\App\Http\Controllers\DesignationController::class, 'destroy'], $designation));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Non-owners are denied deletion.
	 **/
	public function non_owner_cannot_destroy()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$other->givePermissionTo('delete designation');

		$designation = Designation::factory()->create(['created_by' => $owner->creatorId()]);

		$this->actingAs($other);
		$response = $this->delete(action([\App\Http\Controllers\DesignationController::class, 'destroy'], $designation));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Owners with delete permission can destroy and are redirected.
	 **/
	public function owner_with_delete_permission_can_destroy()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('delete designation');

		$designation = Designation::create([
			'department_id' => 1,
			'name'          => 'Temp',
			'created_by'    => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->delete(action([\App\Http\Controllers\DesignationController::class, 'destroy'], $designation));

		$response->assertRedirect(route('designation.index'));
		$this->assertDatabaseMissing('designations', ['id' => $designation->id]);
	}
}
