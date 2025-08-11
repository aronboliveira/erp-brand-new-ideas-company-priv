<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseControllerTest extends TestCase
{
	use RefreshDatabase;
	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// Create permissions used by the controller
		Permission::create(['name' => 'manage warehouse']);
		Permission::create(['name' => 'create warehouse']);
		Permission::create(['name' => 'show warehouse']);
		Permission::create(['name' => 'edit warehouse']);
		Permission::create(['name' => 'delete warehouse']);

		// Create a user with all warehouse permissions
		$this->user = User::factory()->create();
		$this->user->givePermissionTo([
			'manage warehouse',
			'create warehouse',
			'show warehouse',
			'edit warehouse',
			'delete warehouse',
		]);
	}

	/**
	 ** @test
	 **
	 ** Authenticated users with 'manage warehouse' permission can view the warehouse index.
	 **/
	public function authenticated_users_with_manage_permission_can_view_index()
	{
		$response = $this->actingAs($this->user)
			->get(route('warehouse.index'));

		$response->assertStatus(200)
			->assertViewIs('warehouse.index')
			->assertViewHas('warehouses');
	}

	/**
	 ** @test
	 **
	 ** Authenticated users with 'create warehouse' permission can view the create form.
	 **/
	public function authenticated_users_with_create_permission_can_view_create_form()
	{
		$response = $this->actingAs($this->user)
			->get(route('warehouse.create'));

		$response->assertStatus(200)
			->assertViewIs('warehouse.create');
	}

	/**
	 ** @test
	 **
	 ** Authenticated users with 'create warehouse' permission can store a new warehouse.
	 **/
	public function authenticated_users_with_create_permission_can_store_warehouse()
	{
		$data = [
			'name'     => 'Main Warehouse',
			'address'  => '123 Main St',
			'city'     => 'Metropolis',
			'city_zip' => '12345',
		];

		$response = $this->actingAs($this->user)
			->post(route('warehouse.store'), $data);

		$response->assertRedirect(route('warehouse.index'))
			->assertSessionHas('success', __('Warehouse successfully created.'));

		$this->assertDatabaseHas('warehouses', [
			'name'       => 'Main Warehouse',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Store fails validation and redirects back with an error message when 'name' is missing.
	 **/
	public function store_fails_validation_without_name()
	{
		$response = $this->actingAs($this->user)
			->post(route('warehouse.store'), []);

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Authenticated users with 'show warehouse' permission can view warehouse products.
	 **/
	public function authenticated_users_with_show_permission_can_view_warehouse_products()
	{
		$warehouse = Warehouse::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		WarehouseProduct::factory()->count(2)->create([
			'warehouse_id' => $warehouse->id,
			'created_by'   => $this->user->creatorId(),
		]);

		$response = $this->actingAs($this->user)
			->get(route('warehouse.show', $warehouse));

		$response->assertStatus(200)
			->assertViewIs('warehouse.show')
			->assertViewHas('warehouse');
	}

	/**
	 ** @test
	 **
	 ** Authenticated users with 'edit warehouse' permission and ownership can view the edit form.
	 **/
	public function authenticated_users_with_edit_permission_and_ownership_can_view_edit_form()
	{
		$warehouse = Warehouse::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->actingAs($this->user)
			->get(route('warehouse.edit', $warehouse));

		$response->assertStatus(200)
			->assertViewIs('warehouse.edit')
			->assertViewHas('warehouse', $warehouse);
	}

	/**
	 ** @test
	 **
	 ** Authenticated users with 'edit warehouse' permission can update a warehouse.
	 **/
	public function authenticated_users_with_edit_permission_can_update_warehouse()
	{
		$warehouse = Warehouse::factory()->create([
			'created_by' => $this->user->creatorId(),
			'name'       => 'Old Name',
		]);

		$data = [
			'name'     => 'Updated Name',
			'address'  => '456 Elm St',
			'city'     => 'Gotham',
			'city_zip' => '67890',
		];

		$response = $this->actingAs($this->user)
			->put(route('warehouse.update', $warehouse), $data);

		$response->assertRedirect(route('warehouse.index'))
			->assertSessionHas('success', __('Warehouse successfully updated.'));

		$this->assertDatabaseHas('warehouses', [
			'id'   => $warehouse->id,
			'name' => 'Updated Name',
		]);
	}

	/**
	 ** @test
	 **
	 ** Authenticated users with 'delete warehouse' permission can delete a warehouse.
	 **/
	public function authenticated_users_with_delete_permission_can_delete_warehouse()
	{
		$warehouse = Warehouse::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->actingAs($this->user)
			->delete(route('warehouse.destroy', $warehouse));

		$response->assertRedirect(route('warehouse.index'))
			->assertSessionHas('success', __('Warehouse successfully deleted.'));

		$this->assertDatabaseMissing('warehouses', [
			'id' => $warehouse->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Users without ownership cannot edit a warehouse they do not own.
	 **/
	public function users_cannot_edit_warehouse_they_do_not_own()
	{
		$otherUser = User::factory()->create();
		$warehouse = Warehouse::factory()->create([
			'created_by' => $otherUser->creatorId(),
		]);

		$response = $this->actingAs($this->user)
			->get(route('warehouse.edit', $warehouse));

		$response->assertRedirect(route('warehouse.index'));
	}
}
