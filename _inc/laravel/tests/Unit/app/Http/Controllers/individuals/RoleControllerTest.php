<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// authenticate and bypass permission checks
		$this->user = User::factory()->create();
		$this->actingAs($this->user);
		Gate::before(fn () => true);

		// ensure creatorId() returns the user’s own id
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
	}

	/**
	 ** @test
	 **
	 ** index should display only roles created by the authenticated user
	 **/
	public function index_displays_only_user_roles(): void
	{
		$own  = Role::create(['name' => 'OwnRole', 'guard_name' => 'web', 'created_by' => $this->user->creatorId()]);
		$other = Role::create(['name' => 'OtherRole', 'guard_name' => 'web', 'created_by' => $this->user->creatorId() + 1]);

		$response = $this->get(route('roles.index'));

		$response->assertOk()
			->assertViewIs('role.index')
			->assertViewHas(
				'roles',
				fn ($roles) =>
				$roles->pluck('id')->all() === [$own->id]
			);
	}

	/**
	 ** @test
	 **
	 ** create should render form with available permissions
	 **/
	public function create_shows_permissions_list(): void
	{
		// seed some permissions
		Permission::create(['name' => 'perm_a', 'guard_name' => 'web']);
		Permission::create(['name' => 'perm_b', 'guard_name' => 'web']);

		$response = $this->get(route('roles.create'));

		$response->assertOk()
			->assertViewIs('role.create')
			->assertViewHas('permissions', function ($perms) {
				$names = array_values($perms);
				return in_array('perm_a', $names) && in_array('perm_b', $names);
			});
	}

	/**
	 ** @test
	 **
	 ** store should validate and create a new role with assigned permissions
	 **/
	public function store_creates_role_and_assigns_permissions(): void
	{
		$perm = Permission::create(['name' => 'perm_store', 'guard_name' => 'web']);

		$payload = [
			'name'        => 'NewRole',
			'permissions' => [$perm->id],
		];

		$response = $this->post(route('roles.store'), $payload);

		$response->assertRedirect(route('roles.index'))
			->assertSessionHas('success', 'Role successfully created.');

		$this->assertDatabaseHas('roles', [
			'name'       => 'NewRole',
			'created_by' => $this->user->creatorId(),
		]);

		$role = Role::where('name', 'NewRole')->first();
		$this->assertTrue($role->hasPermissionTo('perm_store'));
	}

	/**
	 ** @test
	 **
	 ** store should fail validation when missing fields
	 **/
	public function store_validation_fails(): void
	{
		$response = $this->post(route('roles.store'), []);

		$response->assertSessionHasErrors('name');
	}

	/**
	 ** @test
	 **
	 ** edit should render form with role and permissions
	 **/
	public function edit_shows_form_with_role_and_permissions(): void
	{
		$role = Role::create(['name' => 'EditRole', 'guard_name' => 'web', 'created_by' => $this->user->creatorId()]);
		Permission::create(['name' => 'perm_edit', 'guard_name' => 'web']);

		$response = $this->get(route('roles.edit', $role->id));

		$response->assertOk()
			->assertViewIs('role.edit')
			->assertViewHasAll(['role', 'permissions']);
	}

	/**
	 ** @test
	 **
	 ** update should validate and modify the role and its permissions
	 **/
	public function update_changes_role_and_permissions(): void
	{
		$role = Role::create(['name' => 'OldRole', 'guard_name' => 'web', 'created_by' => $this->user->creatorId()]);
		$permOld = Permission::create(['name' => 'perm_old', 'guard_name' => 'web']);
		$permNew = Permission::create(['name' => 'perm_new', 'guard_name' => 'web']);
		$role->givePermissionTo($permOld);

		$payload = [
			'name'        => 'UpdatedRole',
			'permissions' => [$permNew->id],
		];

		$response = $this->put(route('roles.update', $role->id), $payload);

		$response->assertRedirect(route('roles.index'))
			->assertSessionHas('success', 'Role successfully updated.');

		$this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'UpdatedRole']);
		$this->assertFalse($role->fresh()->hasPermissionTo('perm_old'));
		$this->assertTrue($role->fresh()->hasPermissionTo('perm_new'));
	}

	/**
	 ** @test
	 **
	 ** update should fail validation when name is missing
	 **/
	public function update_validation_fails(): void
	{
		$role = Role::create(['name' => 'RoleToUpdate', 'guard_name' => 'web', 'created_by' => $this->user->creatorId()]);
		Permission::create(['name' => 'perm_x', 'guard_name' => 'web']);

		$response = $this->put(route('roles.update', $role->id), [
			'name'        => '',
			'permissions' => [],
		]);

		$response->assertSessionHasErrors('name');
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the role
	 **/
	public function destroy_deletes_role(): void
	{
		$role = Role::create(['name' => 'DelRole', 'guard_name' => 'web', 'created_by' => $this->user->creatorId()]);

		$response = $this->delete(route('roles.destroy', $role->id));

		$response->assertRedirect(route('roles.index'))
			->assertSessionHas('success', 'Role successfully deleted.');

		$this->assertDatabaseMissing('roles', ['id' => $role->id]);
	}
}
