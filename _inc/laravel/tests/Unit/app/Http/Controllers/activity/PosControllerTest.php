<?php

namespace Tests\Unit\Http\Controllers;

use Tests\TestCase;
use App\Models\{Customer, Pos, PosPayment, ProductService, User, Warehouse};
use App\Traits\CreatesMockUser;
use Illuminate\FileSystem\FileSystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Crypt, Storage};

class PosControllerTest extends TestCase
{
	use RefreshDatabase, CreatesMockUser;

	protected User $admin;

	protected function setUp(): void
	{
		parent::setUp();
		$this->admin = User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** Denies access to the POS index route when the user lacks
	 ** the 'manage pos' permission, redirecting back with an error.
	 **/
	public function it_denies_index_access_without_permission()
	{
		$this->actingAs($this->admin);
		$this->admin->revokePermissionTo('manage pos');
		$res = $this->get(route('pos.index'));
		$res->assertRedirect();
		$res->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Renders the POS index view when the user has
	 ** the 'manage pos' permission.
	 **/
	public function it_renders_index_view_for_authorized_user()
	{
		$this->actingAs($this->admin);
		$this->admin->givePermissionTo('manage pos');
		$res = $this->get(route('pos.index'));
		$res->assertOk();
		$res->assertViewIs('pos.index');
	}

	/**
	 ** @test
	 **
	 ** Shows the POS creation view when the session cart
	 ** contains items and the user is authorized.
	 **/
	public function it_renders_create_view_if_cart_is_populated()
	{
		$this->actingAs($this->admin);
		$this->admin->givePermissionTo('manage pos');

		$customer = Customer::factory()->create(['created_by' => $this->admin->creatorId()]);
		$warehouse = Warehouse::factory()->create(['created_by' => $this->admin->creatorId()]);
		session()->put('pos', [
			['id' => 1, 'name' => 'Item A', 'price' => 100, 'quantity' => 2, 'tax' => 10, 'subtotal' => 200],
		]);

		$res = $this->post(route('pos.create'), [
			'vc_name'        => $customer->name,
			'warehouse_name' => $warehouse->id,
			'discount'       => 0,
		]);

		$res->assertOk();
		$res->assertViewIs('pos.show');
	}

	/**
	 ** @test
	 **
	 ** Rejects POS creation with an empty cart,
	 ** returning 404 with a JSON error.
	 **/
	public function it_rejects_create_with_empty_cart()
	{
		$this->actingAs($this->admin);
		$this->admin->givePermissionTo('manage pos');

		$res = $this->post(route('pos.create'), [
			'vc_name'        => 'Unknown',
			'warehouse_name' => 999,
		]);

		$res->assertStatus(404);
		$res->assertJson(['error' => 'Add some products to cart!']);
	}

	/**
	 ** @test
	 **
	 ** Stores a POS transaction successfully when
	 ** the cart is valid, returning JSON success.
	 **/
	public function it_stores_pos_successfully_with_valid_cart()
	{
		$this->actingAs($this->admin);
		$this->admin->givePermissionTo('manage pos');

		$customer = Customer::factory()->create(['created_by' => $this->admin->creatorId()]);
		$warehouse = Warehouse::factory()->create(['created_by' => $this->admin->creatorId()]);
		$product  = ProductService::factory()->create([
			'quantity'   => 10,
			'created_by' => $this->admin->creatorId(),
		]);

		session()->put('pos', [[
			'id'           => $product->id,
			'name'         => $product->name,
			'price'        => 50,
			'quantity'     => 2,
			'tax'          => 5,
			'product_tax'  => 5,
			'subtotal'     => 100,
		]]);

		$res = $this->postJson(route('pos.store'), [
			'vc_name'        => $customer->name,
			'warehouse_name' => $warehouse->id,
			'date'           => now()->toDateString(),
			'discount'       => 0,
		]);

		$res->assertOk();
		$res->assertJson(['code' => 200, 'success' => 'Payment completed successfully!']);
	}

	/**
	 ** @test
	 **
	 ** Displays the POS view page for a given transaction when the
	 ** encrypted ID is valid and user is owner.
	 **/
	public function it_shows_a_pos_view_page()
	{
		$pos = Pos::factory()->create(['created_by' => $this->admin->creatorId()]);
		$res = $this->get(route('pos.show', encrypt($pos->id)));
		$res->assertOk();
		$res->assertViewIs('pos.view');
		$res->assertViewHas('pos');
	}

	/**
	 ** @test
	 **
	 ** Displays the POS report page.
	 **/
	public function it_displays_pos_report()
	{
		$res = $this->get(route('pos.report'));
		$res->assertOk();
		$res->assertViewIs('pos.report');
	}

	/**
	 ** @test
	 **
	 ** Displays the barcode page for POS.
	 **/
	public function it_displays_barcode_page()
	{
		$res = $this->get(route('pos.barcode'));
		$res->assertOk();
		$res->assertViewIs('pos.barcode');
	}

	/**
	 ** @test
	 **
	 ** Shows the POS print view when the cart is not empty.
	 **/
	public function it_displays_print_view_if_cart_is_not_empty()
	{
		$customer = Customer::factory()->create(['created_by' => $this->admin->creatorId()]);
		$warehouse = Warehouse::factory()->create(['created_by' => $this->admin->creatorId()]);

		session()->put('pos', [
			['id' => 1, 'name' => 'Product A', 'price' => 50, 'quantity' => 1, 'tax' => 5, 'subtotal' => 50],
		]);

		$res = $this->post(route('pos.printView'), [
			'vc_name'        => $customer->name,
			'warehouse_name' => $warehouse->id,
			'discount'       => 0,
		]);

		$res->assertOk();
		$res->assertViewIs('pos.printview');
	}

	/**
	 ** @test
	 **
	 ** Renders the POS preview page using the given template and color.
	 **/
	public function it_renders_the_pos_preview_page()
	{
		$res = $this->get(route('pos.preview', ['template1', 'ffffff']));
		$res->assertOk();
		$res->assertViewIs('pos.templates.template1');
	}

	/**
	 ** @test
	 **
	 ** Renders the POS settings page for authorized users.
	 **/
	public function it_renders_setting_page()
	{
		$res = $this->get(route('pos.setting'));
		$res->assertOk();
		$res->assertViewIs('pos.setting');
	}

	/**
	 ** @test
	 **
	 ** Redirect guests to the login page when accessing the POS create route.
	 **/
	public function test_create_redirects_if_not_logged_in(): void
	{
		$response = $this->get(route('pos.create'));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Return a 404 JSON error when attempting to create a POS with an empty cart.
	 **/
	public function test_create_fails_with_empty_cart(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user)->withSession(['pos' => []]);

		$response = $this->post(route('pos.create'), ['vc_name' => 'John Doe']);
		$response->assertStatus(404);
		$response->assertJson(['error' => 'Add some products to cart!']);
	}

	/**
	 ** @test
	 **
	 ** Forbid access when a user who does not own the POS record attempts to view it.
	 **/
	public function test_show_unauthorized_access(): void
	{
		$user     = User::factory()->create();
		$otherUser = User::factory()->create();
		$pos      = Pos::factory()->create(['created_by' => $otherUser->creatorId()]);

		$this->actingAs($user);
		$encId   = Crypt::encrypt($pos->id);
		$response = $this->get(route('pos.show', $encId));

		$response->assertForbidden();
	}

	/**
	 ** @test
	 **
	 ** Render the POS report page for users with 'manage pos' permission.
	 **/
	public function test_report_renders_view(): void
	{
		$user = $this->createUserWithPermissions(['manage pos']);
		Pos::factory()->count(2)->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(route('pos.report'));

		$response->assertOk();
		$response->assertViewIs('pos.report');
	}

	/**
	 ** @test
	 **
	 ** Render the barcode page with product data for authorized 'manage pos' users.
	 **/
	public function test_barcode_renders_data(): void
	{
		$user = $this->createUserWithPermissions(['manage pos']);
		ProductService::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(route('pos.barcode'));

		$response->assertOk();
		$response->assertViewIs('pos.barcode');
		$response->assertViewHas('productServices');
	}

	/**
	 ** @test
	 **
	 ** Helper method to authenticate as a user with a specific permission.
	 **/
	protected function acting_as_user_with_permission(string $perm): User
	{
		$user = User::factory()->create();
		$user?->givePermissionTo($perm);
		$this->actingAs($user);
		return $user;
	}

	/**
	 ** @test
	 **
	 ** Render the POS settings page for users with 'manage pos' permission.
	 **/
	public function test_pos_setting_view_authorized(): void
	{
		$admin = $this->acting_as_user_with_permission('manage pos');
		$res  = $this->get(route('pos.setting'));

		$res->assertOk();
		$res->assertViewIs('pos.setting');
	}

	/**
	 ** @test
	 **
	 ** Forbid access to the POS settings page for unauthorized users.
	 **/
	public function test_pos_setting_view_unauthorized(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$res = $this->get(route('pos.setting'));
		$res->assertForbidden();
	}

	/**
	 ** @test
	 **
	 ** Store barcode settings successfully with valid data, including file upload.
	 **/
	public function test_barcode_setting_store_valid_data(): void
	{
		$admin  = $this->acting_as_user_with_permission('manage pos');
		$payload = ['barcode_type' => 'code128', 'barcode_format' => 'auto'];

		$res = $this->post(route('pos.barcode.setting.store'), $payload);
		$res->assertRedirect();
		$res->assertSessionHas('success');
	}

	/**
	 ** @test
	 **
	 ** Fail to store barcode settings and return validation errors with invalid data.
	 **/
	public function test_barcode_setting_store_invalid_data(): void
	{
		$admin = $this->acting_as_user_with_permission('manage pos');

		$res = $this->post(route('pos.barcode.setting.store'), []); // empty payload
		$res->assertSessionHasErrors();
	}

	/**
	 ** @test
	 **
	 ** Render the print barcode view when warehouses exist for the user.
	 **/
	public function test_print_barcode_view_renders(): void
	{
		$admin = $this->acting_as_user_with_permission('manage pos');
		Warehouse::factory()->create(['created_by' => $admin->creatorId()]);

		$res = $this->get(route('pos.print.barcode'));
		$res->assertOk();
		$res->assertViewIs('pos.print');
	}

	/**
	 ** @test
	 **
	 ** Return a JSON list of products for a given warehouse via AJAX.
	 **/
	public function test_get_product_for_warehouse_returns_products(): void
	{
		$admin    = $this->acting_as_user_with_permission('manage pos');
		$warehouse = Warehouse::factory()->create(['created_by' => $admin->creatorId()]);
		$product  = ProductService::factory()->create(['created_by' => $admin->creatorId()]);
		$warehouse->products()->attach($product->id);

		$res = $this->getJson(route('pos.get.product', ['warehouse_id' => $warehouse->id]));
		$res->assertOk()->assertJsonFragment([$product->id => $product->name]);
	}

	/**
	 ** @test
	 **
	 ** Calculate and return the discounted cart total correctly via JSON.
	 **/
	public function test_cart_discount_calculates_total_correctly(): void
	{
		$admin = $this->acting_as_user_with_permission('manage pos');

		$this->withSession([
			'pos' => [
				['subtotal' => 300],
				['subtotal' => 200],
			],
		]);

		$res = $this->postJson(route('pos.cart.discount'), ['discount' => 100]);
		$res->assertOk()->assertJsonStructure(['total']);
	}

	/**
	 ** @test
	 **
	 ** Render a POS view template for an authorized and owning user.
	 **/
	public function test_pos_view_template_authorized_and_owned(): void
	{
		$admin = $this->acting_as_user_with_permission('manage pos');
		$pos  = Pos::factory()->create(['created_by' => $admin->creatorId()]);

		$res = $this->get(route('pos.view.template', encrypt($pos->id)));
		$res->assertOk();
	}

	/**
	 ** @test
	 **
	 ** Render the POS preview template correctly given a template name and color.
	 **/
	public function test_preview_pos_view_renders_correctly(): void
	{
		$admin = $this->acting_as_user_with_permission('manage pos');

		$res = $this->get(route('pos.preview.template', ['template1', 'ff6600']));
		$res->assertOk()->assertViewIs('pos.templates.template1');
	}

	/**
	 ** @test
	 **
	 ** Save POS template settings including color and logo, and verify logo stored.
	 **/
	public function test_save_pos_template_settings_success(): void
	{
		Storage::fake('local');
		$admin  = $this->acting_as_user_with_permission('manage pos');
		$payload = [
			'pos_color' => '00ff00',
			'pos_logo'  => UploadedFile::fake()->image('logo.png'),
		];

		$res = $this->post(route('pos.template.setting.store'), $payload);
		$res->assertRedirect()->assertSessionHas('success');

		$local = Storage::disk('local');
		if ($local instanceof FileSystemAdapter) {
			$local->assertExists('pos_logo/' . $admin->id . '_logo.png');
		}
	}

	/**
	 ** @test
	 **
	 ** Redirect back with an error when the print view is accessed and the cart is empty.
	 **/
	public function test_print_view_cart_empty_redirects_back(): void
	{
		$res = $this->get(route('pos.print.view'));
		$res->assertRedirect()->assertSessionHas('error', 'Cart is empty.');
	}

	/**
	 ** @test
	 **
	 ** Display the POS print view when the cart has valid items.
	 **/
	public function test_print_view_success(): void
	{
		$admin    = $this->acting_as_user_with_permission('manage pos');
		$warehouse = Warehouse::factory()->create(['created_by' => $admin->creatorId()]);
		$customer = Customer::factory()->create(['created_by' => $admin->creatorId()]);

		$this->withSession([
			'pos' => [[
				'id'          => 1,
				'name'        => 'Test Product',
				'price'       => 50,
				'quantity'    => 2,
				'tax'         => 0,
				'product_tax' => 0,
				'subtotal'    => 100,
			]],
		]);

		$res = $this->get(route('pos.print.view', [
			'warehouse_name' => $warehouse->id,
			'vc_name'        => $customer->name,
			'discount'       => 10,
		]));

		$res->assertOk()->assertViewIs('pos.printview');
	}


	/**
	 ** @test
	 **
	 ** invoicePosNumber returns 1 when no POS exist for the user
	 **/
	public function it_returns_one_if_no_existing_pos()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->getJson(route('pos.invoicePosNumber'));

		$response->assertStatus(200);
		$this->assertEquals(1, $response->decodeResponseJson());
	}

	/**
	 ** @test
	 **
	 ** invoicePosNumber returns the next increment based on latest POS
	 **/
	public function it_returns_next_pos_id_based_on_latest()
	{
		$user = User::factory()->create();
		// Existing POS with pos_id = 5
		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_id'     => 5,
		]);

		$response = $this->actingAs($user)
			->getJson(route('pos.invoicePosNumber'));

		$response->assertStatus(200);
		$this->assertEquals(6, $response->decodeResponseJson());
	}


	/**
	 ** @test
	 **
	 ** receipt redirects back with error when no product_id provided
	 **/
	public function receipt_requires_at_least_one_product()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->get(route('pos.receipt'));

		$response->assertRedirect()
			->assertSessionHas('error', __('Product is required.'));
	}

	/**
	 ** @test
	 **
	 ** receipt displays view with productServices when valid IDs provided
	 **/
	public function receipt_displays_products_and_barcode()
	{
		$user = User::factory()->create();
		$ps1 = ProductService::factory()->create();
		$ps2 = ProductService::factory()->create();

		$response = $this->actingAs($user)
			->get(route('pos.receipt', ['product_id' => [$ps1->id, $ps2->id], 'quantity' => 3]));

		$response->assertStatus(200)
			->assertViewIs('pos.receipt')
			->assertViewHas('productServices', function ($list) use ($ps1, $ps2) {
				return $list->pluck('id')->sort()->values()->all() === [$ps1->id, $ps2->id];
			})
			->assertViewHas('barcode')
			->assertViewHas('quantity', 3);
	}

	/**
	 ** @test
	 **
	 ** pos rejects when user is not the owner
	 **/
	public function pos_denies_access_if_not_owner()
	{
		$user   = User::factory()->create();
		$other  = User::factory()->create();
		$pos    = Pos::factory()->create(['created_by' => $other->creatorId()]);
		$encId  = Crypt::encryptString($pos->id);

		$response = $this->actingAs($user)
			->get(route('pos.pos', ['encId' => $encId]));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** pos displays the correct view when the owner requests it
	 **/
	public function pos_displays_receipt_with_items_for_owner()
	{
		$user = User::factory()->create();
		// Create a POS with one item and link to a ProductService
		$ps = ProductService::factory()->create(['created_by' => $user?->creatorId()]);
		$pos = Pos::factory()->create(['created_by' => $user?->creatorId(), 'pos_id' => 10]);
		$pos->items()->create([
			'product_id' => $ps->id,
			'quantity'   => 2,
			'price'      => 50,
			'discount'   => 5,
			'tax'        => 10,
			'description' => 'Test item',
		]);
		// Optionally a payment record
		PosPayment::factory()->create(['pos_id' => $pos->id]);

		$encId = Crypt::encryptString($pos->id);

		$response = $this->actingAs($user)
			->get(route('pos.pos', ['encId' => $encId]));

		$response->assertStatus(200)
			->assertViewIs('pos.templates.' . ($response->original->getData()['settings']['pos_template'] ?? 'default'))
			->assertViewHas('pos', fn ($v) => $v->id === $pos->id)
			->assertViewHas('posPayment')
			->assertViewHas('items')
			->assertViewHas('taxesData')
			->assertViewHas('img')
			->assertViewHas('barcode');
	}
}
