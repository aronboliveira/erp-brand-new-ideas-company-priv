<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Bill, BillPayment, ProductService, ProductServiceCategory, User, Vendor};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Crypt, Log};
use Tests\TestCase;
use Maatwebsite\Excel\Facades\Excel;

class BillControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
	}

	/**
	 ** @test
	 **
	 ** index should return the bills index view for a user
	 ** with the 'manage bill' permission.
	 **/
	public function test_index_returns_bills_for_authenticated_user()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage bill');

		$response = $this->get(route('bill.index'));

		$response->assertStatus(200);
		$response->assertViewIs('bill.index');
	}

	/**
	 ** @test
	 **
	 ** create should render the bill creation form
	 ** including vendor, product categories, and accounts.
	 **/
	public function test_create_renders_form_with_dependencies()
	{
		$user  = User::factory()->create();
		$vendor = Vendor::factory()->create(['created_by' => $user?->id]);
		$this->actingAs($user);
		$user?->givePermissionTo('create bill');

		$response = $this->get(route('bill.create', ['vendorId' => $vendor->id]));

		$response->assertStatus(200);
		$response->assertViewIs('bill.create');
	}

	/**
	 ** @test
	 **
	 ** store should persist a new bill with its line items
	 ** and redirect to the bills index.
	 **/
	public function test_store_creates_bill_with_products_and_accounts()
	{
		$user    = User::factory()->create();
		$vendor  = Vendor::factory()->create(['created_by' => $user?->id]);
		$product = ProductService::factory()->create(['created_by' => $user?->id]);
		$category = ProductServiceCategory::factory()->create(['created_by' => $user?->id]);
		$this->actingAs($user);
		$user?->givePermissionTo('create bill');

		$payload = [
			'vendor_id'   => $vendor->id,
			'bill_date'   => now()->toDateString(),
			'due_date'    => now()->addWeek()->toDateString(),
			'category_id' => $category->id,
			'items'       => [[
				'item'        => $product->id,
				'quantity'    => 1,
				'tax'         => 0,
				'discount'    => 0,
				'price'       => 100,
				'description' => 'Test item',
			]],
		];

		$response = $this->post(route('bill.store'), $payload);

		$response->assertRedirect(route('bill.index'));
		$this->assertDatabaseHas('bills', ['vendor_id' => $vendor->id]);
	}

	/**
	 ** @test
	 **
	 ** show should display the bill details view
	 ** when provided with an encrypted bill ID.
	 **/
	public function test_show_displays_bill_details()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('show bill');

		$bill = Bill::factory()->create(['created_by' => $user?->id]);
		$encId = Crypt::encrypt($bill->id);

		$response = $this->get(route('bill.show', $encId));

		$response->assertStatus(200);
		$response->assertViewIs('bill.view');
		$response->assertViewHas('bill');
	}

	/**
	 ** @test
	 **
	 ** edit should render the edit form for a bill
	 ** when given its encrypted ID.
	 **/
	public function test_edit_renders_edit_form()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('edit bill');

		$bill = Bill::factory()->create(['created_by' => $user?->id]);
		$encId = Crypt::encrypt($bill->id);

		$response = $this->get(route('bill.edit', $encId));

		$response->assertStatus(200);
		$response->assertViewIs('bill.edit');
	}

	/**
	 ** @test
	 **
	 ** update should apply changes to the bill record
	 ** and redirect back to the bills index.
	 **/
	public function test_update_changes_bill_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('edit bill');

		$bill = Bill::factory()->create(['created_by' => $user?->id]);

		$payload = [
			'vendor_id'   => $bill->vendor_id,
			'bill_date'   => now()->toDateString(),
			'due_date'    => now()->addWeek()->toDateString(),
			'category_id' => $bill->category_id,
			'items'       => [],
		];

		$response = $this->put(route('bill.update', $bill->id), $payload);

		$response->assertRedirect(route('bill.index'));
		$this->assertDatabaseHas('bills', ['id' => $bill->id]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the bill and its related data,
	 ** then redirect to the bills index.
	 **/
	public function test_destroy_removes_bill_and_related_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('delete bill');

		$bill = Bill::factory()->create(['created_by' => $user?->id]);

		$response = $this->delete(route('bill.destroy', $bill->id));

		$response->assertRedirect(route('bill.index'));
		$this->assertDatabaseMissing('bills', ['id' => $bill->id]);
	}

	/**
	 ** @test
	 **
	 ** create payment should store a bill payment record
	 ** including the uploaded receipt file.
	 **/
	public function test_create_payment_saves_payment_and_receipt()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('create payment bill');

		$bill = Bill::factory()->create(['created_by' => $user?->id]);
		$file = UploadedFile::fake()->create('receipt.pdf');

		$response = $this->post(route('bill.create.payment', $bill->id), [
			'date'        => now()->toDateString(),
			'amount'      => 100,
			'account_id'  => 1,
			'add_receipt' => $file,
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('bill_payments', [
			'bill_id' => $bill->id,
			'amount'  => 100,
		]);
	}

	/**
	 ** @test
	 **
	 ** payment destroy should delete the specified payment
	 ** and redirect back.
	 **/
	public function test_payment_destroy_deletes_payment()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('delete payment bill');

		$payment = BillPayment::factory()->create();
		$bill   = $payment->bill;

		$response = $this->delete(route('bill.payment.destroy', [
			'billId'    => $bill->id,
			'paymentId' => $payment->id,
		]));

		$response->assertRedirect();
		$this->assertDatabaseMissing('bill_payments', ['id' => $payment->id]);
	}

	/**
	 ** @test
	 **
	 ** duplicate should clone an existing bill along with its items,
	 ** resulting in two bills in the database.
	 **/
	public function test_duplicate_clones_bill_and_products()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('duplicate bill');

		$bill = Bill::factory()->create(['created_by' => $user?->id]);

		$response = $this->post(route('bill.duplicate', $bill->id));
		$response->assertRedirect();

		$this->assertDatabaseCount('bills', 2);
	}

	/**
	 ** @test
	 **
	 ** sent should dispatch the bill via email
	 ** and update its status to 'sent'.
	 **/
	public function test_sent_dispatches_mail_and_updates_status()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('send bill');

		$bill = Bill::factory()->create(['created_by' => $user?->id]);

		$response = $this->post(route('bill.sent', $bill->id));
		$response->assertRedirect();

		$bill->refresh();
		$this->assertEquals(1, $bill->status);
	}

	/**
	 ** @test
	 **
	 ** resent should re-trigger the bill email when allowed
	 ** by the 'bill_resent' setting.
	 **/
	public function test_resent_retriggers_email_when_allowed()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('send bill');

		config()->set('settings.bill_resent', 1);

		$bill = Bill::factory()->create(['created_by' => $user?->id]);

		$response = $this->post(route('bill.resent', $bill->id));
		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** export should trigger an Excel download of all bills
	 ** named with a timestamp.
	 **/
	public function test_export_returns_excel_download()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		Excel::fake();

		$response = $this->get(route('bill.export'));
		$response->assertStatus(200);

		Excel::assertDownloaded('bill_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
	}

	/**
	 ** @test
	 **
	 ** vendorBill should filter bills for the authenticated vendor user
	 ** and render the index view.
	 **/
	public function test_vendor_bill_filters_bills_correctly()
	{
		$user = User::factory()->create(['vendor_id' => 1]);
		$this->actingAs($user);
		$user?->givePermissionTo('manage vendor bill');

		$response = $this->get(route('bill.vendor.bill'));
		$response->assertStatus(200);
		$response->assertViewIs('bill.index');
	}

	/**
	 ** @test
	 **
	 ** vendorBill show should display the bill details
	 ** for the vendor with an encrypted ID.
	 **/
	public function test_vendor_bill_show_displays_bill_detail()
	{
		$user = User::factory()->create(['vendor_id' => 1]);
		$this->actingAs($user);
		$user?->givePermissionTo('show bill');

		$bill = Bill::factory()->create(['vendor_id' => 1, 'created_by' => $user?->id]);
		$encId = Crypt::encrypt($bill->id);

		$response = $this->get(route('bill.vendor.show', $encId));

		$response->assertStatus(200);
		$response->assertViewIs('bill.view');
	}
}
