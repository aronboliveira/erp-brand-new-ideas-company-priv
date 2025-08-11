<?php

namespace Tests\Feature;

use App\Models\ProductService;
use App\Models\ProductServiceUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\TestCase;

class ProductServiceUnitControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// create and authenticate a user
		$this->user = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->user);

		// bypass all permission checks
		Gate::before(fn () => true);

		// ensure creatorId() returns this user's id
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
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
	 ** index redirects guests and requires manage permission
	 **/
	public function test_index_requires_manage_permission()
	{
		// logout to simulate guest
		auth()->logout();
		$resp = $this->get(route('product-unit.index'));
		$resp->assertRedirect(route('login'));

		// login but without permission
		$this->actingAs($this->user);
		Gate::before(fn () => false);
		$resp = $this->get(route('product-unit.index'));
		$resp->assertRedirect('/');

		// grant permission and create sample units
		Gate::before(fn () => true);
		$this->user->givePermissionTo('manage constant unit');
		ProductServiceUnit::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);

		$resp = $this->get(route('product-unit.index'));
		$resp->assertOk()
			->assertViewIs('productServiceUnit.index')
			->assertViewHas('units', fn ($units) => $units->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** create requires permission and shows form
	 **/
	public function test_create_requires_permission_and_shows_form()
	{
		Gate::before(fn () => false);
		$resp = $this->get(route('product-unit.create'));
		$resp->assertRedirect('/');

		Gate::before(fn () => true);
		$this->user->givePermissionTo('create constant unit');
		$resp = $this->get(route('product-unit.create'));
		$resp->assertOk()
			->assertViewIs('productServiceUnit.create');
	}

	/**
	 ** @test
	 **
	 ** store validates and creates a unit
	 **/
	public function test_store_validates_and_creates_unit()
	{
		$this->user->givePermissionTo('create constant unit');

		// validation error
		$resp = $this->post(route('product-unit.store'), []);
		$resp->assertSessionHas('error');

		// valid data
		$resp = $this->post(route('product-unit.store'), ['name' => 'Box']);
		$resp->assertRedirect(route('product-unit.index'));
		$this->assertDatabaseHas('product_service_units', [
			'name'       => 'Box',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show and edit require appropriate permissions
	 **/
	public function test_show_and_edit_permissions_and_views()
	{
		$unit = ProductServiceUnit::factory()->create(['created_by' => $this->user->creatorId()]);

		Gate::before(fn () => false);
		$resp = $this->get(route('product-unit.show', $unit));
		$resp->assertRedirect('/');

		$resp = $this->get(route('product-unit.edit', $unit));
		$resp->assertRedirect('/');

		Gate::before(fn () => true);
		$this->user->givePermissionTo('manage constant unit');
		$resp = $this->get(route('product-unit.show', $unit));
		$resp->assertOk()->assertViewIs('productServiceUnit.show');

		$this->user->givePermissionTo('edit constant unit');
		$resp = $this->get(route('product-unit.edit', $unit));
		$resp->assertOk()->assertViewIs('productServiceUnit.edit');
	}

	/**
	 ** @test
	 **
	 ** update validates and updates a unit
	 **/
	public function test_update_validates_and_updates_unit()
	{
		$unit = ProductServiceUnit::factory()->create([
			'name'       => 'Old',
			'created_by' => $this->user->creatorId(),
		]);

		// no permission
		Gate::before(fn () => false);
		$resp = $this->put(route('product-unit.update', $unit), ['name' => 'New']);
		$resp->assertRedirect('/');

		Gate::before(fn () => true);
		$this->user->givePermissionTo('edit constant unit');

		// validation fail
		$resp = $this->put(route('product-unit.update', $unit), ['name' => str_repeat('A', 21)]);
		$resp->assertSessionHas('error');

		// valid update
		$resp = $this->put(route('product-unit.update', $unit), ['name' => 'New']);
		$resp->assertRedirect(route('product-unit.index'));
		$this->assertDatabaseHas('product_service_units', [
			'id'   => $unit->id,
			'name' => 'New',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy requires permission, ownership, and handles in-use units
	 **/
	public function test_destroy_with_permission_and_usage()
	{
		$unit = ProductServiceUnit::factory()->create(['created_by' => $this->user->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$resp = $this->delete(route('product-unit.destroy', $unit));
		$resp->assertRedirect('/');

		// not owner
		Gate::before(fn () => true);
		$this->user->givePermissionTo('delete constant unit');
		$other = ProductServiceUnit::factory()->create();
		$resp = $this->delete(route('product-unit.destroy', $other));
		$resp->assertRedirect('/');

		// in-use
		ProductService::factory()->create(['unit_id' => $unit->id]);
		$resp = $this->delete(route('product-unit.destroy', $unit));
		$resp->assertSessionHas('error');
		$this->assertDatabaseHas('product_service_units', ['id' => $unit->id]);

		// successful delete
		ProductService::query()->delete();
		$resp = $this->delete(route('product-unit.destroy', $unit));
		$resp->assertRedirect(route('product-unit.index'))
			->assertSessionHas('success');
		$this->assertDatabaseMissing('product_service_units', ['id' => $unit->id]);
	}
}
