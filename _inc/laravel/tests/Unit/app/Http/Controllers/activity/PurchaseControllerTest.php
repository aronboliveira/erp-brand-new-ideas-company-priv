<?php

namespace Tests\Feature;

use App\Models\{
	BankAccount,
	CustomField,
	Purchase,
	PurchasePayment,
	PurchaseProduct,
	ProductService,
	ProductServiceCategory,
	Vendor,
	Warehouse,
	WarehouseProduct
};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{
	Crypt,
	DB,
	Gate,
	Storage
};
use Tests\TestCase;

class PurchaseControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// Bypass all permission and login checks
		Gate::before(fn () => true);

		// Make creatorId() return the user's own ID
		User::macro('creatorId', function () {
			/** @var \App\Models\User $this */
			return $this->id;
		});

		$this->user = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Index should display a list of purchases belonging to the authenticated user
	 **/
	public function index_displays_purchase_list()
	{
		// Seed two vendors and purchases for this user
		$vendor1 = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$vendor2 = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$purchase1 = Purchase::factory()->create([
			'vendor_id'  => $vendor1->id,
			'created_by' => $this->user->creatorId(),
		]);
		$purchase2 = Purchase::factory()->create([
			'vendor_id'  => $vendor2->id,
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->actingAs($this->user)->get(route('purchase.index'));

		$response->assertStatus(200)
			->assertViewIs('purchase.index')
			->assertViewHas('purchases', function ($purchases) use ($purchase1, $purchase2) {
				$ids = $purchases->pluck('id')->all();
				return in_array($purchase1->id, $ids) && in_array($purchase2->id, $ids);
			});
	}

	/**
	 ** @test
	 **
	 ** Create should show the purchase form with all required lookup data
	 **/
	public function create_shows_form_data()
	{
		// Seed lookup data
		$category   = ProductServiceCategory::factory()->create(['type' => 'expense', 'created_by' => $this->user->creatorId()]);
		$vendor     = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$warehouse  = Warehouse::factory()->create(['created_by' => $this->user->creatorId()]);
		$product    = ProductService::factory()->create([
			'type'       => 'product',
			'created_by' => $this->user->creatorId(),
		]);
		CustomField::factory()->create(['created_by' => $this->user->creatorId(), 'module' => 'purchase']);

		$response = $this->actingAs($this->user)
			->get(route('purchase.create', ['vendorId' => $vendor->id]));

		$response->assertStatus(200)
			->assertViewIs('purchase.create')
			->assertViewHasAll([
				'vendors', 'purchaseNumber', 'productServices',
				'category', 'customFields', 'vendorId', 'warehouse'
			]);
		$this->assertEquals($vendor->id, $response->viewData('vendorId'));
	}

	/**
	 ** @test
	 **
	 ** Store should validate inputs and create a purchase with its items
	 **/
	public function store_validates_and_creates_purchase()
	{
		$category = ProductServiceCategory::factory()->create(['type' => 'expense', 'created_by' => $this->user->creatorId()]);
		$vendor   = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$warehouse = Warehouse::factory()->create(['created_by' => $this->user->creatorId()]);
		$product  = ProductService::factory()->create([
			'type'       => 'product',
			'created_by' => $this->user->creatorId(),
		]);

		// 1) Missing payload triggers validation error
		$response = $this->actingAs($this->user)
			->post(route('purchase.store'), []);
		$response->assertSessionHas('error');

		// 2) Valid payload creates purchase and items
		$payload = [
			'vendor_id'     => $vendor->id,
			'warehouse_id'  => $warehouse->id,
			'purchase_date' => now()->toDateString(),
			'category_id'   => $category->id,
			'items'         => [
				[
					'item'        => $product->id,
					'quantity'    => 2,
					'tax'         => 5,
					'discount'    => 0,
					'price'       => 100,
					'description' => 'Test item',
				],
			],
		];

		$response = $this->actingAs($this->user)
			->post(route('purchase.store'), $payload);

		// It should redirect to the show page of the newly created purchase
		$purchase = Purchase::where('created_by', $this->user->creatorId())->latest()->first();
		$response->assertRedirect(route('purchase.show', ['purchase' => $purchase->id]))
			->assertSessionHas('success');

		// Database assertions
		$this->assertDatabaseHas('purchases', [
			'id'         => $purchase->id,
			'vendor_id'  => $vendor->id,
			'warehouse_id' => $warehouse->id,
			'category_id' => $category->id,
			'created_by' => $this->user->creatorId(),
		]);
		$this->assertDatabaseHas('purchase_products', [
			'purchase_id' => $purchase->id,
			'product_id' => $product->id,
			'quantity'   => 2,
		]);
	}

	/**
	 ** @test
	 **
	 ** show should display a purchase when decrypted ID belongs to user
	 **/
	public function show_displays_purchase_details()
	{
		$vendor    = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$purchase  = Purchase::factory()->create([
			'vendor_id'  => $vendor->id,
			'created_by' => $this->user->creatorId(),
		]);
		PurchaseProduct::factory()->count(2)->create([
			'purchase_id' => $purchase->id,
		]);

		$encrypted = Crypt::encrypt((string)$purchase->id);

		$response = $this->actingAs($this->user)
			->get(route('purchase.show', ['ids' => $encrypted]));

		$response->assertStatus(200)
			->assertViewIs('purchase.show')
			->assertViewHasAll(['purchase', 'vendor', 'items', 'purchasePayment']);
	}

	/**
	 ** @test
	 **
	 ** edit should show form only if user owns purchase
	 **/
	public function edit_displays_form_for_owner_only()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$encrypted = Crypt::encrypt((string)$purchase->id);

		$response = $this->actingAs($this->user)
			->get(route('purchase.edit', ['ids' => $encrypted]));

		$response->assertStatus(200)
			->assertViewIs('purchase.edit')
			->assertViewHasAll(['purchase', 'vendors', 'productServices', 'warehouse', 'category', 'purchaseNumber']);
	}

	/**
	 ** @test
	 **
	 ** update should validate and update purchase basic fields
	 **/
	public function update_validates_and_updates_purchase_items()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$item    = PurchaseProduct::factory()->create(['purchase_id' => $purchase->id, 'quantity' => 1]);
		$newVendor = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);

		// Missing items key triggers redirect with error
		$response = $this->actingAs($this->user)
			->put(route('purchase.update', ['purchase' => $purchase->id]), [
				'vendor_id'     => $newVendor->id,
				'purchase_date' => now()->toDateString(),
			]);
		$response->assertRedirect(route('purchase.index'))
			->assertSessionHas('error');

		// Valid payload updates purchase vendor
		$response = $this->actingAs($this->user)
			->put(route('purchase.update', ['purchase' => $purchase->id]), [
				'vendor_id'     => $newVendor->id,
				'purchase_date' => now()->toDateString(),
				'items'         => [
					['id' => $item->id, 'item' => $item->product_id, 'quantity' => 2, 'tax' => 0, 'discount' => 0, 'price' => $item->price, 'description' => $item->description],
				],
			]);

		$response->assertRedirect(route('purchase.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('purchases', [
			'id'        => $purchase->id,
			'vendor_id' => $newVendor->id,
		]);
		$this->assertDatabaseHas('purchase_products', [
			'id'       => $item->id,
			'quantity' => 2,
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete purchase and cascade its items and payments
	 **/
	public function destroy_deletes_purchase_and_related_records()
	{
		Storage::fake('local');

		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$item    = PurchaseProduct::factory()->create(['purchase_id' => $purchase->id]);
		$payment = PurchasePayment::factory()->create(['purchase_id' => $purchase->id]);

		$response = $this->actingAs($this->user)
			->delete(route('purchase.destroy', ['purchase' => $purchase]));

		$response->assertRedirect(route('purchase.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
		$this->assertDatabaseMissing('purchase_products', ['id' => $item->id]);
		$this->assertDatabaseMissing('purchase_payments', ['id' => $payment->id]);
	}

	/**
	 ** @test
	 **
	 ** items returns the matching PurchaseProduct as JSON
	 **/
	public function items_returns_purchase_product_json()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$product = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);
		$pp      = PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'product_id'  => $product->id,
		]);

		$response = $this->actingAs($this->user)
			->get(route('purchase.items'), [
				'purchase_id' => $purchase->id,
				'product_id'  => $product->id,
			]);

		$response->assertStatus(200)
			->assertJsonFragment(['id' => $pp->id]);
	}
	/**
	 ** @test
	 **
	 ** product returns product data JSON for a purchase context
	 **/
	public function product_returns_product_data_json()
	{
		$product = ProductService::factory()->create(['created_by' => $this->user->creatorId(), 'purchase_price' => 50, 'tax_id' => null]);

		$response = $this->actingAs($this->user)
			->json('GET', route('purchase.product'), ['product_id' => $product->id]);

		$response->assertStatus(200)
			->assertJsonFragment(['product_id' => $product->id])
			->assertJsonStructure(['product', 'unit', 'taxRate', 'taxes', 'totalAmount']);
	}

	/**
	 ** @test
	 **
	 ** productDestroy should remove a PurchaseProduct and adjust warehouse stock
	 **/
	public function product_destroy_deletes_purchase_product()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId(), 'warehouse_id' => 1]);
		$pp = PurchaseProduct::factory()->create(['purchase_id' => $purchase->id, 'product_id' => 5, 'quantity' => 3]);
		\App\Models\WarehouseProduct::factory()->create([
			'warehouse_id' => $purchase->warehouse_id,
			'product_id'   => $pp->product_id,
			'quantity'     => 3
		]);

		$response = $this->actingAs($this->user)
			->post(route('purchase.productDestroy'), ['id' => $pp->id]);

		$response->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('purchase_products', ['id' => $pp->id]);
		$this->assertDatabaseMissing('warehouse_products', [
			'warehouse_id' => $purchase->warehouse_id,
			'product_id'   => $pp->product_id
		]);
	}

	/**
	 ** @test
	 **
	 ** index should list purchases and vendor options
	 **/
	public function index_displays_purchases_and_vendors()
	{
		Vendor::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);
		Purchase::factory()->count(3)->create(['created_by' => $this->user->creatorId()]);

		$response = $this->actingAs($this->user)
			->get(route('purchase.index'));

		$response->assertStatus(200)
			->assertViewIs('purchase.index')
			->assertViewHasAll(['purchases', 'vendors', 'status']);
	}

	/**
	 ** @test
	 **
	 ** create shows form with required collections
	 **/
	public function create_shows_form_with_collections()
	{
		$vendorId = Vendor::factory()->create(['created_by' => $this->user->creatorId()])->id;
		ProductServiceCategory::factory()->count(2)->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'expense',
		]);
		Warehouse::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);
		ProductService::factory()->count(2)->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'product',
		]);
		DB::table('settings')->insert([
			['name' => 'some_setting', 'value' => 'val', 'created_by' => $this->user->creatorId()],
		]);

		$response = $this->actingAs($this->user)
			->get(route('purchase.create', ['vendorId' => $vendorId]));

		$response->assertStatus(200)
			->assertViewIs('purchase.create')
			->assertViewHasAll([
				'vendors', 'purchaseNumber', 'productServices',
				'category', 'customFields', 'vendorId', 'warehouse'
			]);
	}

	/**
	 ** @test
	 **
	 ** store validates inputs and creates purchase with items
	 **/
	public function store_validates_and_creates_purchase_and_items()
	{
		$vendor   = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$warehouse = Warehouse::factory()->create(['created_by' => $this->user->creatorId()]);
		$prod     = ProductService::factory()->create([
			'created_by' => $this->user->creatorId(), 'type' => 'product'
		]);

		// Missing required fields
		$resp = $this->actingAs($this->user)
			->post(route('purchase.store'), []);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// Valid payload
		$payload = [
			'vendor_id'      => $vendor->id,
			'warehouse_id'   => $warehouse->id,
			'purchase_date'  => now()->toDateString(),
			'category_id'    => ProductServiceCategory::factory()->create([
				'created_by' => $this->user->creatorId(), 'type' => 'expense'
			])->id,
			'items'          => [[
				'item'        => $prod->id,
				'quantity'    => 5,
				'tax'         => 0,
				'discount'    => 0,
				'price'       => 100,
				'description' => 'Test'
			]]
		];

		$resp = $this->actingAs($this->user)
			->post(route('purchase.store'), $payload);

		$resp->assertRedirect();
		$purchase = Purchase::where('created_by', $this->user->creatorId())->first();
		$this->assertNotNull($purchase);
		$this->assertDatabaseHas('purchase_products', [
			'purchase_id' => $purchase->id,
			'product_id'  => $prod->id,
			'quantity'    => 5,
		]);
	}

	/**
	 ** @test
	 **
	 ** sent marks purchase as sent and credits vendor
	 **/
	public function sent_marks_purchase_sent_and_credits_vendor_balance()
	{
		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId(), 'email' => 'v@e.com']);
		$purchase = Purchase::factory()->create([
			'vendor_id'  => $vendor->id,
			'created_by' => $this->user->creatorId(),
			'status'     => 0
		]);
		// set total to 200 via two purchase_products
		PurchaseProduct::factory()->create([
			'purchase_id' => $purchase->id,
			'quantity'   => 2,
			'price'      => 100,
			'tax'        => 0,
			'discount'   => 0,
		]);

		// insert settings to allow email
		DB::table('settings')->insert([
			['name' => 'vendor_bill_sent', 'value' => 1, 'created_by' => $this->user->creatorId()]
		]);

		$resp = $this->actingAs($this->user)
			->post(route('purchase.sent', ['id' => $purchase->id]));

		$resp->assertRedirect()
			->assertSessionHas('success');

		$purchase->refresh();
		$this->assertEquals(1, $purchase->status);
		$this->assertNotNull($purchase->send_date);
	}

	/**
	 ** @test
	 **
	 ** resent re-sends email and returns success
	 **/
	public function resent_re_sends_email_and_redirects_back()
	{
		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId(), 'email' => 'v@e.com']);
		$purchase = Purchase::factory()->create([
			'vendor_id'  => $vendor->id,
			'created_by' => $this->user->creatorId(),
		]);
		// allow email sending
		DB::table('settings')->insert([
			['name' => 'vendor_bill_sent', 'value' => 1, 'created_by' => $this->user->creatorId()]
		]);

		$resp = $this->actingAs($this->user)
			->post(route('purchase.resent', ['id' => $purchase->id]));

		$resp->assertRedirect()
			->assertSessionHas('success');
	}

	/**
	 ** @test
	 **
	 ** purchaseLink displays the customer-facing bill
	 **/
	public function purchase_link_displays_customer_view()
	{
		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$purchase = Purchase::factory()->create([
			'vendor_id'  => $vendor->id,
			'created_by' => $this->user->creatorId(),
		]);
		$encrypted = Crypt::encrypt($purchase->id);

		$resp = $this->actingAs($this->user)
			->get(route('purchase.purchaseLink', ['encryptedId' => $encrypted]));

		$resp->assertStatus(200)
			->assertViewIs('purchase.customer_bill')
			->assertViewHasAll(['purchase', 'vendor', 'items', 'purchasePayment', 'user']);
	}

	/**
	 ** @test
	 **
	 ** show displays purchase when owner, denies otherwise
	 **/
	public function show_displays_and_denies_properly()
	{
		$purchase = Purchase::factory()->create([
			'created_by' => $this->user->creatorId()
		]);
		// add an item so view has something
		PurchaseProduct::factory()->create(['purchase_id' => $purchase->id]);

		$enc = Crypt::encrypt($purchase->id);

		// As owner
		$resp = $this->actingAs($this->user)
			->get(route('purchase.show', ['ids' => $enc]));
		$resp->assertStatus(200)
			->assertViewIs('purchase.show')
			->assertViewHasAll(['purchase', 'vendor', 'items', 'purchasePayment']);

		// As different user
		$other = User::factory()->create();
		$resp2 = $this->actingAs($other)
			->get(route('purchase.show', ['ids' => $enc]));
		$resp2->assertRedirect(route('purchase.index'));
	}

	/**
	 ** @test
	 **
	 ** edit displays form when owner, denies otherwise
	 **/
	public function edit_displays_and_denies_properly()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$enc = Crypt::encrypt($purchase->id);

		$resp = $this->actingAs($this->user)
			->get(route('purchase.edit', ['ids' => $enc]));
		$resp->assertStatus(200)
			->assertViewIs('purchase.edit')
			->assertViewHasAll(['purchase', 'vendors', 'productServices', 'warehouse', 'category', 'purchaseNumber']);

		$other = User::factory()->create();
		$resp2 = $this->actingAs($other)
			->get(route('purchase.edit', ['ids' => $enc]));
		$resp2->assertRedirect(route('purchase.index'));
	}

	/**
	 ** @test
	 **
	 ** update validates and updates purchase items
	 **/
	public function update_validates_and_updates()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$pp      = PurchaseProduct::factory()->create(['purchase_id' => $purchase->id, 'quantity' => 1]);
		$newItem = ProductService::factory()->create([
			'created_by' => $this->user->creatorId(), 'type' => 'product'
		]);

		// Missing items
		$resp = $this->actingAs($this->user)
			->put(route('purchase.update', ['purchase' => $purchase]), []);
		$resp->assertRedirect(route('purchase.index'))
			->assertSessionHas('error');

		// Valid update
		$payload = [
			'vendor_id'    => $purchase->vendor_id,
			'purchase_date' => now()->toDateString(),
			'items'        => [[
				'id'          => $pp->id,
				'item'        => $newItem->id,
				'quantity'    => 3,
				'tax'         => 0,
				'discount'    => 0,
				'price'       => 50,
				'description' => 'Upd'
			]]
		];
		$resp2 = $this->actingAs($this->user)
			->put(route('purchase.update', ['purchase' => $purchase]), $payload);
		$resp2->assertRedirect(route('purchase.index'))
			->assertSessionHas('success');
		$this->assertDatabaseHas('purchase_products', [
			'id'       => $pp->id,
			'product_id' => $newItem->id,
			'quantity' => 3
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy removes purchase and related items/payments
	 **/
	public function destroy_deletes_purchase_cascades()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$item    = PurchaseProduct::factory()->create(['purchase_id' => $purchase->id]);
		$payment = PurchasePayment::factory()->create(['purchase_id' => $purchase->id]);

		$resp = $this->actingAs($this->user)
			->delete(route('purchase.destroy', ['purchase' => $purchase]));
		$resp->assertRedirect(route('purchase.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
		$this->assertDatabaseMissing('purchase_products', ['id' => $item->id]);
		$this->assertDatabaseMissing('purchase_payments', ['id' => $payment->id]);
	}

	/**
	 ** @test
	 **
	 ** previewPurchase returns template view
	 **/
	public function preview_purchase_displays_template()
	{
		// Insert settings
		DB::table('settings')->insert([
			['name' => 'purchase_template', 'value' => 'default', 'created_by' => $this->user->creatorId()],
			['name' => 'purchase_color', 'value' => 'abcdef', 'created_by' => $this->user->creatorId()],
		]);
		$resp = $this->actingAs($this->user)
			->get(route('purchase.preview', ['template' => 'default', 'color' => 'abcdef']));
		$resp->assertStatus(200);
	}

	/**
	 ** @test
	 **
	 ** items returns JSON for a PurchaseProduct
	 **/
	public function items_returns_json_or_empty()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$prod    = ProductService::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 'product']);
		$pp      = PurchaseProduct::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $prod->id]);

		$resp = $this->actingAs($this->user)
			->get(route('purchase.items'), ['purchase_id' => $purchase->id, 'product_id' => $prod->id]);
		$resp->assertSee('"id":' . $pp->id);

		$resp2 = $this->actingAs($this->user)
			->get(route('purchase.items'), ['purchase_id' => 999, 'product_id' => 999]);
		$resp2->assertSee('{}');
	}

	/**
	 ** @test
	 **
	 ** vendor shows vendor detail view
	 **/
	public function vendor_displays_vendor_detail()
	{
		$vendor = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$resp = $this->actingAs($this->user)
			->get(route('purchase.vendor', ['id' => $vendor->id]));
		$resp->assertStatus(200)
			->assertViewIs('purchase.vendor_detail')
			->assertViewHas('vendor');
	}

	/**
	 ** @test
	 **
	 ** product returns correct JSON data
	 **/
	public function product_returns_product_json()
	{
		$prod = ProductService::factory()->create([
			'created_by' => $this->user->creatorId(),
			'purchase_price' => 100,
			'tax_id' => ''
		]);
		$resp = $this->actingAs($this->user)
			->get(route('purchase.product'), ['product_id' => $prod->id]);
		$resp->assertSee('"totalAmount":100');
	}

	/**
	 ** @test
	 **
	 ** payment shows form and createPayment persists payment
	 **/
	public function payment_and_create_payment_work()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$account = BankAccount::factory()->create(['created_by' => $this->user->creatorId()]);

		// show form
		$resp = $this->actingAs($this->user)
			->get(route('purchase.payment', ['purchaseId' => $purchase->id]));
		$resp->assertStatus(200)
			->assertViewIs('purchase.payment');

		// create payment missing fields
		$resp2 = $this->actingAs($this->user)
			->post(route('purchase.createPayment', ['purchaseId' => $purchase->id]), []);
		$resp2->assertRedirect()
			->assertSessionHas('error');

		// valid payment
		$payload = ['date' => now()->toDateString(), 'amount' => 50, 'account_id' => $account->id];
		$resp3 = $this->actingAs($this->user)
			->post(route('purchase.createPayment', ['purchaseId' => $purchase->id]), $payload);
		$resp3->assertRedirect()
			->assertSessionHas('success');
		$this->assertDatabaseHas('purchase_payments', ['purchase_id' => $purchase->id, 'amount' => 50]);
	}

	/**
	 ** @test
	 **
	 ** paymentDestroy deletes payment and updates purchase status
	 **/
	public function payment_destroy_deletes_and_updates()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$pp      = PurchasePayment::factory()->create(['purchase_id' => $purchase->id, 'account_id' => 1, 'amount' => 30]);
		$resp    = $this->actingAs($this->user)
			->delete(route('purchase.paymentDestroy', ['purchaseId' => $purchase->id, 'paymentId' => $pp->id]));
		$resp->assertRedirect()
			->assertSessionHas('success');
		$this->assertDatabaseMissing('purchase_payments', ['id' => $pp->id]);
	}

	/**
	 ** @test
	 **
	 ** savePurchaseTemplateSettings persists settings
	 **/
	public function save_purchase_template_settings_saves_entries()
	{
		Storage::fake('local');
		$resp = $this->actingAs($this->user)
			->post(route('purchase.saveSettings'), [
				'purchase_template' => 'tpl', 'purchase_color' => '112233'
			]);
		$resp->assertRedirect();
		$this->assertDatabaseHas('settings', [
			'name' => 'purchase_template', 'value' => 'tpl', 'created_by' => $this->user->creatorId()
		]);
	}

	/**
	 ** @test
	 **
	 ** productDestroy removes a purchase product and adjusts warehouse
	 **/
	public function product_destroy_removes_item_and_adjusts_warehouse()
	{
		$purchase = Purchase::factory()->create(['created_by' => $this->user->creatorId()]);
		$prod    = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);
		$pp      = PurchaseProduct::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $prod->id, 'quantity' => 2]);
		// ensure a WarehouseProduct exists
		WarehouseProduct::factory()->create([
			'warehouse_id' => $purchase->warehouse_id, 'product_id' => $prod->id, 'quantity' => 2
		]);

		$resp = $this->actingAs($this->user)
			->delete(route('purchase.productDestroy'), ['id' => $pp->id]);
		$resp->assertRedirect()
			->assertSessionHas('success');
		$this->assertDatabaseMissing('purchase_products', ['id' => $pp->id]);
	}
	/**
	 ** @test
	 **
	 ** index shows list of purchases with vendors/status
	 **/
	public function index_shows_purchases_list()
	{
		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		Purchase::factory()->count(2)->create(['vendor_id' => $vendor->id, 'created_by' => $this->user->creatorId()]);

		$resp = $this->actingAs($this->user)
			->get(route('purchase.index'));

		$resp->assertStatus(200)
			->assertViewIs('purchase.index')
			->assertViewHasAll(['purchases', 'status', 'vendors']);
	}

	/**
	 ** @test
	 **
	 ** create displays the purchase creation form
	 **/
	public function create_displays_form()
	{
		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$resp = $this->actingAs($this->user)
			->get(route('purchase.create', ['vendorId' => $vendor->id]));

		$resp->assertStatus(200)
			->assertViewIs('purchase.create')
			->assertViewHasAll([
				'vendors', 'purchaseNumber', 'productServices',
				'category', 'customFields', 'vendorId', 'warehouse'
			]);
	}
	/**
	 ** @test
	 **
	 ** sent marks purchase sent, credits vendor, and emails template
	 **/
	public function sent_marks_sent_and_sends_email()
	{
		Storage::fake('local');
		// make sure settings say to send
		DB::table('settings')->insert([
			['name' => 'vendor_bill_sent', 'value' => '1', 'created_by' => $this->user->creatorId()]
		]);

		$vendor = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$purchase = Purchase::factory()->create([
			'vendor_id' => $vendor->id, 'created_by' => $this->user->creatorId()
		]);

		$resp = $this->actingAs($this->user)
			->post(route('purchase.sent', ['id' => $purchase->id]));

		$resp->assertRedirect()
			->assertSessionHas('success');
		$this->assertDatabaseHas('purchases', ['id' => $purchase->id, 'status' => 1]);
	}

	/**
	 ** @test
	 **
	 ** resent re-sends the email template without changing status
	 **/
	public function resent_resends_email()
	{
		Storage::fake('local');
		DB::table('settings')->insert([
			['name' => 'vendor_bill_sent', 'value' => '1', 'created_by' => $this->user->creatorId()]
		]);

		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$purchase = Purchase::factory()->create([
			'vendor_id' => $vendor->id, 'created_by' => $this->user->creatorId(), 'status' => 1
		]);

		$resp = $this->actingAs($this->user)
			->post(route('purchase.resent', ['id' => $purchase->id]));

		$resp->assertRedirect()
			->assertSessionHas('success');
	}

	/**
	 ** @test
	 **
	 ** purchase renders the chosen template view
	 **/
	public function purchase_renders_template_view()
	{
		// set template
		DB::table('settings')->insert([
			['name' => 'purchase_template', 'value' => 'default', 'created_by' => $this->user->creatorId()],
			['name' => 'company_logo_dark', 'value' => '', 'created_by' => $this->user->creatorId()]
		]);

		$vendor  = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$purchase = Purchase::factory()->create([
			'vendor_id' => $vendor->id, 'created_by' => $this->user->creatorId()
		]);

		$enc = Crypt::encrypt($purchase->id);
		$resp = $this->actingAs($this->user)
			->get(route('purchase.purchase', ['purchaseId' => $enc]));

		$resp->assertStatus(200);
	}
}
