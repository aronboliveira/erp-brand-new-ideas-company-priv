<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\PermissionController;

class PermissionControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// Create a test user
		$this->user = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login, and authenticated users without
	 ** the “manage permissions” ability should be denied; with the ability,
	 ** the index view should list all permissions.
	 **/
	public function index_redirects_guests_and_requires_manage_permission()
	{
		// Guest → redirect
		$response = $this->get(action([PermissionController::class, 'index']));
		$response->assertRedirect();

		// Authenticated without 'manage permissions' → redirect
		$response = $this->actingAs($this->user)
			->get(action([PermissionController::class, 'index']));
		$response->assertRedirect();

		// Give permission and create some permissions
		Permission::create(['name' => 'manage permissions', 'guard_name' => 'web']);
		$this->user->givePermissionTo('manage permissions');
		$perm1 = Permission::create(['name' => 'perm_one', 'guard_name' => 'web']);
		$perm2 = Permission::create(['name' => 'perm_two', 'guard_name' => 'web']);

		// Now should see the list
		$response = $this->actingAs($this->user)
			->get(action([PermissionController::class, 'index']));

		$response->assertOk()
			->assertViewIs('permission.index')
			->assertViewHas('permissions', function ($perms) use ($perm1, $perm2) {
				$names = $perms->pluck('name')->all();
				return in_array($perm1->name, $names)
					&& in_array($perm2->name, $names);
			});
	}

	/**
	 ** @test
	 **
	 ** The create action should be denied without “create permissions,”
	 ** and with it should render a form listing all roles.
	 **/
	public function create_requires_permission_and_displays_roles()
	{
		// Without create permission → redirect
		$response = $this->actingAs($this->user)
			->get(action([PermissionController::class, 'create']));
		$response->assertRedirect();

		// Grant create permission and seed roles
		Permission::create(['name' => 'create permissions', 'guard_name' => 'web']);
		$this->user->givePermissionTo('create permissions');

		$roleA = Role::create([
			'name'       => 'RoleA',
			'guard_name' => 'web',
			'created_by' => $this->user->creatorId(),
		]);
		$roleB = Role::create([
			'name'       => 'RoleB',
			'guard_name' => 'web',
			'created_by' => $this->user->creatorId(),
		]);

		// Now should see the create form with roles
		$response = $this->actingAs($this->user)
			->get(action([PermissionController::class, 'create']));

		$response->assertOk()
			->assertViewIs('permission.create')
			->assertViewHas('roles', function ($roles) use ($roleA, $roleB) {
				return $roles->pluck('name')->contains($roleA->name)
					&& $roles->pluck('name')->contains($roleB->name);
			});
	}

	/**
	 ** @test
	 **
	 ** The store action should validate input, create a permission by name,
	 ** and assign it to the selected roles.
	 **/
	public function store_validates_and_creates_permission_and_assigns_to_roles()
	{
		// Prepare create permission and one role
		Permission::create(['name' => 'create permissions', 'guard_name' => 'web']);
		$this->user->givePermissionTo('create permissions');

		$role = Role::create([
			'name'       => 'HR',
			'guard_name' => 'web',
			'created_by' => $this->user->creatorId(),
		]);

		// Missing name → validation error
		$response = $this->actingAs($this->user)
			->post(action([PermissionController::class, 'store']), []);
		$response->assertRedirect();
		$response->assertSessionHasErrors('name');

		// Valid request → permission created and assigned
		$response = $this->actingAs($this->user)
			->post(action([PermissionController::class, 'store']), [
				'name'  => 'view_reports',
				'roles' => [$role->id],
			]);

		$response->assertRedirect(route('permissions.index'))
			->assertSessionHas('success', 'Permission view_reports added!');

		$this->assertDatabaseHas('permissions', ['name' => 'view_reports']);
		$this->assertTrue($role->fresh()->hasPermissionTo('view_reports'));
	}

	/**
	 ** @test
	 **
	 ** The edit action should be denied without “edit permissions,”
	 ** and with it should render the edit form including all roles.
	 **/
	public function edit_requires_permission_and_displays_form_with_roles()
	{
		$permission = Permission::create(['name' => 'edit permissions', 'guard_name' => 'web']);

		// Without edit permission → redirect
		$response = $this->actingAs($this->user)
			->get(action([PermissionController::class, 'edit'], ['permission' => $permission->id]));
		$response->assertRedirect();

		// Grant edit permission and seed a role
		$this->user->givePermissionTo('edit permissions');
		$role = Role::create([
			'name'       => 'Manager',
			'guard_name' => 'web',
			'created_by' => $this->user->creatorId(),
		]);

		// Now should show edit form
		$response = $this->actingAs($this->user)
			->get(action([PermissionController::class, 'edit'], ['permission' => $permission->id]));

		$response->assertOk()
			->assertViewIs('permission.edit')
			->assertViewHasAll([
				'permission',
				'roles'
			]);
	}

	/**
	 ** @test
	 **
	 ** The update action should validate the name and update the permission,
	 ** returning success on valid input.
	 **/
	public function update_validates_and_updates_permission_name()
	{
		$permission = Permission::create(['name' => 'old_name', 'guard_name' => 'web']);

		// Without edit permission → redirect
		$response = $this->actingAs($this->user)
			->put(action([PermissionController::class, 'update'], ['permission' => $permission->id]), []);
		$response->assertRedirect();

		// Grant edit permission
		$this->user->givePermissionTo('edit permissions');

		// Missing name → validation error
		$response = $this->actingAs($this->user)
			->put(action([PermissionController::class, 'update'], ['permission' => $permission->id]), []);
		$response->assertRedirect();
		$response->assertSessionHasErrors('name');

		// Valid update → name changed
		$response = $this->actingAs($this->user)
			->put(action([PermissionController::class, 'update'], ['permission' => $permission->id]), [
				'name' => 'new_name',
			]);

		$response->assertRedirect(route('permissions.index'))
			->assertSessionHas('success', 'Permission new_name updated!');

		$this->assertDatabaseHas('permissions', ['id' => $permission->id, 'name' => 'new_name']);
	}

	/**
	 ** @test
	 **
	 ** The destroy action should be denied without “delete permissions,”
	 ** and with it should delete the permission and return success.
	 **/
	public function destroy_requires_permission_and_deletes_permission()
	{
		$permission = Permission::create(['name' => 'delete permissions', 'guard_name' => 'web']);

		// Without delete permission → redirect
		$response = $this->actingAs($this->user)
			->delete(action([PermissionController::class, 'destroy'], ['id' => $permission->id]));
		$response->assertRedirect();

		// Grant delete permission
		$this->user->givePermissionTo('delete permissions');

		// Now delete succeeds
		$response = $this->actingAs($this->user)
			->delete(action([PermissionController::class, 'destroy'], ['id' => $permission->id]));

		$response->assertRedirect(route('permissions.index'))
			->assertSessionHas('success', 'Permission successfully deleted.');

		$this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
	}
}
