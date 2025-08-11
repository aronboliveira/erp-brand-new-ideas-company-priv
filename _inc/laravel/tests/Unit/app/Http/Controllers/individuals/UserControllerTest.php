<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Crypt, Hash};
use App\Models\User;
use App\Models\UserToDo;
use Spatie\Permission\Models\Permission;

class UserControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index should display users for users with 'manage user' permission
	 **/
	public function index_displays_users_for_authorized_user()
	{
		Permission::create(['name' => 'manage user']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage user');

		// create two company users under this creator
		User::factory()->create([
			'type'       => 'company',
			'created_by' => $user?->creatorId(),
		]);
		User::factory()->create([
			'type'       => 'company',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('users.index'));

		$response->assertStatus(200);
		$response->assertViewIs('user.index');
		$response->assertViewHas('users', function ($list) {
			return $list->count() === 2;
		});
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('users.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create should display the form for users with 'create user' permission
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create user']);
		$user = User::factory()->create(['type' => 'super admin']);
		$user?->givePermissionTo('create user');

		$response = $this->actingAs($user)->get(route('users.create'));

		$response->assertStatus(200);
		$response->assertViewIs('user.create');
		$response->assertViewHasAll(['roles', 'customFields']);
	}

	/**
	 ** @test
	 **
	 ** store should redirect back on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create user']);
		$user = User::factory()->create(['type' => 'super admin']);
		$user?->givePermissionTo('create user');

		$response = $this->actingAs($user)
			->from(route('users.create'))
			->post(route('users.store'), [
				// missing name, email, password
			]);

		$response->assertRedirect(route('users.create'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store should create a new super-admin user and redirect on success
	 **/
	public function store_creates_super_admin_user_and_redirects_on_success()
	{
		// seed default language and a plan
		DB::table('settings')->insert([
			['name' => 'default_language', 'value' => 'en', 'created_by' => 1]
		]);
		\App\Models\Plan::factory()->create(); // ensure Plan::first() exists

		Permission::create(['name' => 'create user']);
		$user = User::factory()->create(['type' => 'super admin']);
		$user?->givePermissionTo('create user');

		$payload = [
			'name'     => 'NewCompany',
			'email'    => 'newco@example.com',
			'password' => 'secret123',
		];

		$response = $this->actingAs($user)
			->post(route('users.store'), $payload);

		$response->assertRedirect(route('users.index'));
		$this->assertDatabaseHas('users', [
			'email'      => 'newco@example.com',
			'type'       => 'company',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show should redirect to index
	 **/
	public function show_redirects_to_index()
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)->get(route('users.show', $user?->id));
		$response->assertRedirect(route('users.index'));
	}

	/**
	 ** @test
	 **
	 ** edit should display the edit form for users with 'edit user' permission
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'edit user']);
		$user = User::factory()->create(['type' => 'company']);
		$user?->givePermissionTo('edit user');

		// target user under same creator
		$target = User::factory()->create([
			'type'       => 'company',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('users.edit', $target->id));

		$response->assertStatus(200);
		$response->assertViewIs('user.edit');
		$response->assertViewHasAll(['userDetail', 'roles', 'customFields']);
	}

	/**
	 ** @test
	 **
	 ** update should redirect back on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'edit user']);
		$user = User::factory()->create(['type' => 'super admin']);
		$user?->givePermissionTo('edit user');

		$target = User::factory()->create(['type' => 'company', 'created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->from(route('users.edit', $target->id))
			->put(route('users.update', $target->id), [
				'name'  => '',
				'email' => 'not-an-email',
			]);

		$response->assertRedirect(route('users.edit', $target->id));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should modify the user and redirect on success
	 **/
	public function update_modifies_user_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit user']);
		$user = User::factory()->create(['type' => 'super admin']);
		$user?->givePermissionTo('edit user');

		$target = User::factory()->create(['type' => 'company', 'created_by' => $user?->creatorId()]);

		$payload = [
			'name'  => 'UpdatedName',
			'email' => 'updated@example.com',
		];

		$response = $this->actingAs($user)
			->put(route('users.update', $target->id), $payload);

		$response->assertRedirect(route('users.index'));
		$this->assertDatabaseHas('users', [
			'id'    => $target->id,
			'name'  => 'UpdatedName',
			'email' => 'updated@example.com',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should toggle delete_status for 'super admin' users
	 **/
	public function destroy_toggles_delete_status_for_super_admin()
	{
		Permission::create(['name' => 'delete user']);
		$user = User::factory()->create(['type' => 'super admin']);
		$user?->givePermissionTo('delete user');

		$target = User::factory()->create(['type' => 'company', 'created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->delete(route('users.destroy', $target->id));

		$response->assertRedirect(route('users.index'));
		$this->assertTrue((bool) $target->fresh()->delete_status);
	}

	/**
	 ** @test
	 **
	 ** todoStore should return validation error when title is missing
	 **/
	public function todo_store_returns_error_on_validation_failure()
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->postJson(route('todo.store'), []);

		$response->assertStatus(400)
			->assertJsonStructure(['error']);
	}

	/**
	 ** @test
	 **
	 ** todoStore should create a todo and return JSON on success
	 **/
	public function todo_store_creates_todo_and_returns_json()
	{
		$user = User::factory()->create();
		$payload = ['title' => 'Test ToDo'];

		$response = $this->actingAs($user)
			->postJson(route('todo.store'), $payload);

		$response->assertStatus(201)
			->assertJsonFragment(['title' => 'Test ToDo']);
		$this->assertDatabaseHas('user_to_dos', ['title' => 'Test ToDo']);
	}

	/**
	 ** @test
	 **
	 ** todoUpdate should toggle completion status
	 **/
	public function todo_update_toggles_completion()
	{
		$user = User::factory()->create();
		$todo = UserToDo::create(['title' => 'T', 'user_id' => $user?->id, 'is_complete' => false]);

		$response = $this->actingAs($user)
			->patchJson(route('todo.update', $todo->id));

		$response->assertStatus(200)
			->assertJsonFragment(['is_complete' => true]);
	}

	/**
	 ** @test
	 **
	 ** todoDestroy should delete the todo and return success
	 **/
	public function todo_destroy_deletes_todo_and_returns_success()
	{
		$user = User::factory()->create();
		$todo = UserToDo::create(['title' => 'T2', 'user_id' => $user?->id]);

		$response = $this->actingAs($user)
			->deleteJson(route('todo.destroy', $todo->id));

		$response->assertStatus(200)
			->assertJson(['success' => true]);
		$this->assertDatabaseMissing('user_to_dos', ['id' => $todo->id]);
	}
}
