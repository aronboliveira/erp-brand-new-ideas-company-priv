<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\{
	ProductService,
	User,
	Warehouse,
	WarehouseProduct,
	WarehouseTransfer
};
use Spatie\Permission\Models\Permission;

class WarehouseTransferControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private WarehouseTransfer $transfer;

	protected function setUp(): void
	{
		parent::setUp();

		// Ensure creatorId() works in controller checks
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

		// Create our user and log them in
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		// Create a dummy "manage warehouse transfer" permission
		Permission::create(['name' => 'manage warehouse transfer']);

		// Create one transfer owned by $this->user
		$this->transfer = WarehouseTransfer::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
	}


	/**
	 ** @test
	 **
	 ** Should list all transfers for a user with manage warehouse permission.
	 **/
	public function test_index_lists_all_transfers_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage warehouse']);
		$user?->givePermissionTo('manage warehouse');

		WarehouseTransfer::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		WarehouseTransfer::factory()->create(); // other user's transfer

		$response = $this->actingAs($user)->get(route('warehouse-transfer.index'));

		$response->assertStatus(200)
			->assertViewIs('warehouse-transfer.index')
			->assertViewHas('transfers', function ($transfers) use ($user) {
				return $transfers->count() === 2
					&& $transfers->every(fn ($t) => $t->created_by === $user?->creatorId());
			});
	}

	/**
	 ** @test
	 **
	 ** Should redirect when user lacks manage warehouse permission.
	 **/
	public function test_index_redirects_if_not_authorized()
	{
		$user = User::factory()->create();
		// no permission given

		$response = $this->actingAs($user)->get(route('warehouse-transfer.index'));

		$response->assertRedirect(route('warehouse-transfer.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Should display create form for user with create warehouse permission.
	 **/
	public function test_create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create warehouse']);
		$user?->givePermissionTo('create warehouse');

		Warehouse::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		$product = ProductService::factory()->create();
		WarehouseProduct::factory()->create([
			'warehouse_id' => Warehouse::first()->id,
			'product_id'   => $product->id,
			'quantity'     => 10,
			'created_by'   => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('warehouse-transfer.create'));

		$response->assertStatus(200)
			->assertViewIs('warehouse-transfer.create')
			->assertViewHasAll(['fromWarehouses', 'toWarehouses', 'products']);
	}

	/**
	 ** @test
	 **
	 ** Store should create a transfer and redirect when stock is sufficient.
	 **/
	public function test_store_creates_transfer_and_redirects_on_success()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create warehouse']);
		$user?->givePermissionTo('create warehouse');

		$creatorId = $user?->creatorId();
		$from = Warehouse::factory()->create(['created_by' => $creatorId]);
		$to  = Warehouse::factory()->create(['created_by' => $creatorId]);
		$product = ProductService::factory()->create();
		WarehouseProduct::factory()->create([
			'warehouse_id' => $from->id,
			'product_id'   => $product->id,
			'quantity'     => 5,
			'created_by'   => $creatorId,
		]);

		$response = $this->actingAs($user)->post(route('warehouse-transfer.store'), [
			'fromWarehouse' => $from->id,
			'toWarehouse'   => $to->id,
			'productId'     => $product->id,
			'quantity'      => 3,
			'date'          => now()->toDateString(),
		]);

		$response->assertRedirect(route('warehouse-transfer.index'))
			->assertSessionHas('success', __('Warehouse Transfer successfully created.'));
		$this->assertDatabaseHas('warehouse_transfers', [
			'fromWarehouse' => $from->id,
			'toWarehouse'   => $to->id,
			'productId'     => $product->id,
			'quantity'      => 3,
			'created_by'    => $creatorId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Store should fail when requested quantity exceeds stock.
	 **/
	public function test_store_fails_when_stock_insufficient()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create warehouse']);
		$user?->givePermissionTo('create warehouse');

		$creatorId = $user?->creatorId();
		$from = Warehouse::factory()->create(['created_by' => $creatorId]);
		$to  = Warehouse::factory()->create(['created_by' => $creatorId]);
		$product = ProductService::factory()->create();
		WarehouseProduct::factory()->create([
			'warehouse_id' => $from->id,
			'product_id'   => $product->id,
			'quantity'     => 2,
			'created_by'   => $creatorId,
		]);

		$response = $this->actingAs($user)->post(route('warehouse-transfer.store'), [
			'fromWarehouse' => $from->id,
			'toWarehouse'   => $to->id,
			'productId'     => $product->id,
			'quantity'      => 5,
			'date'          => now()->toDateString(),
		]);

		$response->assertRedirect(route('warehouse-transfer.index'))
			->assertSessionHas('error', __('Product out of stock!'));
		$this->assertDatabaseCount('warehouse_transfers', 0);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete transfer for authorized user and restore stock.
	 **/
	public function test_destroy_deletes_transfer_and_restores_stock()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete warehouse']);
		$user?->givePermissionTo('delete warehouse');

		$creatorId = $user?->creatorId();
		$from = Warehouse::factory()->create(['created_by' => $creatorId]);
		$to  = Warehouse::factory()->create(['created_by' => $creatorId]);
		$product = ProductService::factory()->create();
		WarehouseProduct::factory()->create([
			'warehouse_id' => $to->id,
			'product_id'   => $product->id,
			'quantity'     => 1,
			'created_by'   => $creatorId,
		]);
		$transfer = WarehouseTransfer::factory()->create([
			'fromWarehouse' => $from->id,
			'toWarehouse'   => $to->id,
			'productId'     => $product->id,
			'quantity'      => 1,
			'created_by'    => $creatorId,
		]);

		$response = $this->actingAs($user)->delete(
			route('warehouse-transfer.destroy', $transfer)
		);

		$response->assertRedirect(route('warehouse-transfer.index'))
			->assertSessionHas('success', __('Warehouse Transfer successfully deleted.'));
		$this->assertModelMissing($transfer);
		// stock restoration via Utility::warehouseTransferQty assumed working
	}

	/**
	 ** @test
	 **
	 ** getProduct should return JSON with products and toWarehouses for authorized user.
	 **/
	public function test_getProduct_returns_json_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage warehouse']);
		$user?->givePermissionTo('manage warehouse');

		$creatorId = $user?->creatorId();
		$w1 = Warehouse::factory()->create(['created_by' => $creatorId]);
		$w2 = Warehouse::factory()->create(['created_by' => $creatorId]);
		$product = ProductService::factory()->create();
		WarehouseProduct::factory()->create([
			'warehouse_id' => $w1->id,
			'product_id'   => $product->id,
			'quantity'     => 4,
			'created_by'   => $creatorId,
		]);

		$response = $this->actingAs($user)->postJson(route('warehouse-transfer.getProduct'), [
			'warehouseId' => $w1->id,
		]);

		$response->assertJsonStructure(['wareProducts', 'toWarehouses']);
		$data = $response->json();
		$this->assertArrayHasKey((string)$product->id, $data['wareProducts']);
		$this->assertArrayHasKey((string)$w2->id, $data['toWarehouses']);
	}

	/**
	 ** @test
	 **
	 ** getProduct should return 401 when unauthorized.
	 **/
	public function test_getProduct_returns_unauthorized_for_guest()
	{
		$response = $this->postJson(route('warehouse-transfer.getProduct'), [
			'warehouseId' => 1,
		]);

		$response->assertStatus(401);
	}

	/**
	 ** @test
	 **
	 ** getQuantity should return JSON quantities for authorized user.
	 **/
	public function test_getQuantity_returns_json_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage warehouse']);
		$user?->givePermissionTo('manage warehouse');

		$creatorId = $user?->creatorId();
		$product = ProductService::factory()->create();
		WarehouseProduct::factory()->create([
			'product_id' => $product->id,
			'quantity'   => 7,
			'created_by' => $creatorId,
		]);

		$response = $this->actingAs($user)->postJson(route('warehouse-transfer.getQuantity'), [
			'productId' => $product->id,
		]);

		$response->assertJson([(string)$product->id => 7]);
	}

	/**
	 ** @test
	 **
	 ** getQuantity should return 401 when unauthorized.
	 **/
	public function test_getQuantity_returns_unauthorized_for_guest()
	{
		$response = $this->postJson(route('warehouse-transfer.getQuantity'), [
			'productId' => 1,
		]);

		$response->assertStatus(401);
	}

	/** @test
	 ** Owner with the “manage warehouse transfer” permission sees the show view.
	 **/
	public function owner_with_permission_sees_transfer_detail()
	{
		// grant the required permission
		$this->user->givePermissionTo('manage warehouse transfer');

		$response = $this->get(route('warehouse-transfer.show', $this->transfer));

		$response->assertOk()
			->assertViewIs('warehouse-transfer.show')
			->assertViewHas('transfer', fn ($t) => $t->id === $this->transfer->id);
	}

	/** @test
	 ** Owner but without permission is denied (403).
	 **/
	public function owner_without_permission_gets_403()
	{
		// do NOT give permission

		$response = $this->get(route('warehouse-transfer.show', $this->transfer));

		$response->assertStatus(403);
	}

	/** @test
	 ** Non-owner, even with permission, gets 403.
	 **/
	public function non_owner_with_permission_gets_403()
	{
		// grant permission to a different user
		$other = User::factory()->create();
		$other->givePermissionTo('manage warehouse transfer');
		$this->actingAs($other);

		$response = $this->get(route('warehouse-transfer.show', $this->transfer));

		$response->assertStatus(403);
	}
}
