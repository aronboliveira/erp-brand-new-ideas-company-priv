<?php

namespace Tests\Feature;

use App\Models\ProductService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProductStockControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// create & authenticate user
		$this->user = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->user);

		// bypass permission by default
		Gate::before(fn () => true);

		// ensure creatorId returns user id
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
	 ** index_requires_manage_permission_and_displays_products
	 **/
	public function test_index_requires_manage_permission_and_displays_products()
	{
		// guest redirected
		auth()->logout();
		$resp = $this->get(route('productstock.index'));
		$resp->assertRedirect(route('login'));

		// login without permission
		$this->actingAs($this->user);
		Gate::before(fn () => false);
		$resp = $this->get(route('productstock.index'));
		$resp->assertRedirect(route('productstock.index'));

		// grant permission and seed data
		Gate::before(fn () => true);
		$this->user->givePermissionTo('manage product & service');
		ProductService::factory()->create([
			'type'       => 'product',
			'created_by' => $this->user->creatorId(),
		]);
		ProductService::factory()->create([
			'type'       => 'service',
			'created_by' => $this->user->creatorId(),
		]);

		$resp = $this->get(route('productstock.index'));
		$resp->assertOk()
			->assertViewIs('productstock.index')
			->assertViewHas(
				'productServices',
				fn ($list) =>
				$list->count() === 1 &&
					$list->first()->type === 'product'
			);
	}

	/**
	 ** @test
	 **
	 ** create_requires_edit_permission_and_shows_form
	 **/
	public function test_create_requires_edit_permission_and_shows_form()
	{
		// without permission
		Gate::before(fn () => false);
		$resp = $this->get(route('productstock.create'));
		$resp->assertRedirect(route('productstock.index'));

		// with permission
		Gate::before(fn () => true);
		$this->user->givePermissionTo('edit product & service');
		$product = ProductService::factory()->create([
			'type'       => 'product',
			'created_by' => $this->user->creatorId(),
		]);

		$resp = $this->get(route('productstock.create'));
		$resp->assertOk()
			->assertViewIs('productstock.create')
			->assertViewHas(
				'products',
				fn ($pluck) =>
				$pluck->has($product->id)
			);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_increments_quantity
	 **/
	public function test_store_validates_and_increments_quantity()
	{
		$this->user->givePermissionTo('edit product & service');
		Gate::before(fn () => true);

		$p = ProductService::factory()->create([
			'type'       => 'product',
			'quantity'   => 5,
			'created_by' => $this->user->creatorId(),
		]);

		// missing data
		$resp = $this->post(route('productstock.store'), []);
		$resp->assertSessionHas('error');

		// invalid product_id
		$resp = $this->post(route('productstock.store'), [
			'product_id' => 999,
			'quantity'   => 3,
		]);
		$resp->assertSessionHas('error');

		// valid request
		$resp = $this->post(route('productstock.store'), [
			'product_id' => $p->id,
			'quantity'   => 2,
		]);
		$resp->assertRedirect(route('productstock.index'))
			->assertSessionHas('success');
		$this->assertDatabaseHas('product_services', [
			'id'       => $p->id,
			'quantity' => 7,
		]);
	}

	/**
	 ** @test
	 **
	 ** edit_requires_permission_and_shows_edit_form
	 **/
	public function test_edit_requires_permission_and_shows_edit_form()
	{
		$p = ProductService::factory()->create([
			'type'       => 'product',
			'created_by' => $this->user->creatorId(),
		]);

		// no permission
		Gate::before(fn () => false);
		$resp = $this->get(route('productstock.edit', $p->id));
		$resp->assertRedirect(route('productstock.index'));

		// with permission
		Gate::before(fn () => true);
		$this->user->givePermissionTo('edit product & service');

		$resp = $this->get(route('productstock.edit', $p->id));
		$resp->assertOk()
			->assertViewIs('productstock.edit')
			->assertViewHas('productService', fn ($ps) => $ps->id === $p->id);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_increments_quantity
	 **/
	public function test_update_validates_and_increments_quantity()
	{
		$this->user->givePermissionTo('edit product & service');
		Gate::before(fn () => true);

		$p = ProductService::factory()->create([
			'type'       => 'product',
			'quantity'   => 10,
			'created_by' => $this->user->creatorId(),
		]);

		// invalid quantity
		$resp = $this->put(route('productstock.update', $p->id), ['quantity' => 0]);
		$resp->assertSessionHas('error');

		// valid update
		$resp = $this->put(route('productstock.update', $p->id), ['quantity' => 5]);
		$resp->assertRedirect(route('productstock.index'))
			->assertSessionHas('success');
		$this->assertDatabaseHas('product_services', [
			'id'       => $p->id,
			'quantity' => 15,
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_requires_permission_and_deletes_product
	 **/
	public function test_destroy_requires_permission_and_deletes_product()
	{
		$p = ProductService::factory()->create([
			'type'       => 'product',
			'created_by' => $this->user->creatorId(),
		]);

		// no permission
		Gate::before(fn () => false);
		$resp = $this->delete(route('productstock.destroy', $p->id));
		$resp->assertRedirect(route('productstock.index'));
		$this->assertDatabaseHas('product_services', ['id' => $p->id]);

		// with permission
		Gate::before(fn () => true);
		$this->user->givePermissionTo('delete product & service');

		$resp = $this->delete(route('productstock.destroy', $p->id));
		$resp->assertRedirect()
			->assertSessionHas('success');
		$this->assertDatabaseMissing('product_services', ['id' => $p->id]);
	}

	/**
	 ** @test
	 **
	 ** show_redirects_to_index
	 **/
	public function test_show_redirects_to_index()
	{
		$this->user->givePermissionTo('manage product & service');
		Gate::before(fn () => true);

		$resp = $this->get(route('productstock.show', ['id' => 1]));
		$resp->assertRedirect(route('productstock.index'));
	}
}
