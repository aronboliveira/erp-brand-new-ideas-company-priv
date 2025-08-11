<?php

namespace Tests\Feature\Controllers\InvoiceController;

use App\Mail\CustomerInvoiceSend;
use App\Models\{
	BankAccount,
	Customer,
	Invoice,
	InvoicePayment,
	Product,
	ProductService,
	ProductServiceCategory,
	InvoiceProduct,
	User,
};
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, Mail, Storage};
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected User $admin;
	private Invoice $invoice;
	private string $encId;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create(['type' => 'company']);
		$this->admin = User::factory()->create(['type' => 'super admin']);
		$this->actingAs($this->user);
		$this->user = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->user);
		$customer = Customer::factory()->create(['created_by' => $this->user->creatorId()]);
		$this->invoice = Invoice::factory()->create([
			'customer_id' => $customer->id,
			'created_by'  => $this->user->creatorId(),
		]);
		$product = Product::factory()->create(['name' => 'Test Product']);
		Invoice::factory()->create([
			'invoice_id' => $this->invoice->id,
			'product_id' => $product->id,
			'quantity'   => 2,
			'price'      => 100,
			'tax'        => 5,
			'discount'   => 10,
		]);

		$this->encId = Crypt::encryptString((string)$this->invoice->id);
	}

	/**
	 ** @test
	 **
	 ** List all invoices for the company user,
	 ** rendering the index view with invoices data.
	 **/
	public function test_can_list_invoices()
	{
		Invoice::factory()->count(3)->create(['created_by' => $this->user->creatorId()]);
		$res = $this->get(route('invoice.index'));

		$res->assertStatus(200)
			->assertViewIs('invoice.index')
			->assertViewHas('invoices');
	}

	/**
	 ** @test
	 **
	 ** Display the invoice creation form with
	 ** customers, categories, products, and invoice number.
	 **/
	public function test_can_view_invoice_create_form()
	{
		$res = $this->get(route('invoice.create'));

		$res->assertStatus(200)
			->assertViewIs('invoice.create')
			->assertViewHasAll(['customers', 'category', 'product_services', 'invoice_number']);
	}

	/**
	 ** @test
	 **
	 ** Store a new invoice with line items and
	 ** redirect to the index, persisting in DB.
	 **/
	public function test_can_store_invoice()
	{
		$customer = Customer::factory()->create(['created_by' => $this->user->creatorId()]);
		$category = ProductServiceCategory::factory()->create(['type' => 'income', 'created_by' => $this->user->creatorId()]);
		$product = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);

		$payload = [
			'customer_id' => $customer->id,
			'issue_date'  => now()->toDateString(),
			'due_date'    => now()->addWeek()->toDateString(),
			'category_id' => $category->id,
			'items' => [[
				'item'        => $product->id,
				'quantity'    => 2,
				'tax'         => 0,
				'discount'    => 0,
				'price'       => 100,
				'description' => 'Test item',
			]],
		];

		$res = $this->post(route('invoice.store'), $payload);

		$res->assertRedirect(route('invoice.index'));
		$this->assertDatabaseHas('invoices', ['customer_id' => $customer->id]);
	}

	/**
	 ** @test
	 **
	 ** Show the invoice edit form for an existing invoice
	 ** identified by its encrypted ID.
	 **/
	public function test_can_view_invoice_edit_form()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->user->creatorId()]);
		$encId   = encrypt($invoice->id);

		$res = $this->get(route('invoice.edit', $encId));

		$res->assertStatus(200)
			->assertViewIs('invoice.edit')
			->assertViewHas('invoice');
	}

	/**
	 ** @test
	 **
	 ** Update an existing invoice with new data
	 ** and redirect back to index, persisting changes.
	 **/
	public function test_can_update_invoice()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->user->creatorId()]);
		$customer = Customer::factory()->create(['created_by' => $this->user->creatorId()]);
		$category = ProductServiceCategory::factory()->create(['type' => 'income', 'created_by' => $this->user->creatorId()]);
		$product = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);

		$payload = [
			'customer_id' => $customer->id,
			'issue_date'  => now()->toDateString(),
			'due_date'    => now()->addWeek()->toDateString(),
			'category_id' => $category->id,
			'items' => [[
				'item'        => $product->id,
				'quantity'    => 1,
				'tax'         => 0,
				'discount'    => 0,
				'price'       => 200,
				'description' => 'Updated product',
			]],
		];

		$res = $this->put(route('invoice.update', $invoice->id), $payload);

		$res->assertRedirect(route('invoice.index'));
		$this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'customer_id' => $customer->id]);
	}

	/**
	 ** @test
	 **
	 ** Soft delete an invoice and redirect to index.
	 **/
	public function test_can_delete_invoice()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->user->creatorId()]);

		$res = $this->delete(route('invoice.destroy', $invoice->id));

		$res->assertRedirect(route('invoice.index'));
		$this->assertSoftDeleted($invoice);
	}

	/**
	 ** @test
	 **
	 ** Render the invoice creation page when passing a customer ID.
	 **/
	public function test_can_render_create_invoice_page(): void
	{
		$customer = Customer::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('invoice.create', $customer->id));

		$response->assertStatus(200)
			->assertViewIs('invoice.create');
	}

	/**
	 ** @test
	 **
	 ** Render the invoice edit page using encrypted ID.
	 **/
	public function test_can_render_edit_invoice_page(): void
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->user->creatorId()]);
		$encId   = Crypt::encryptString($invoice->id);

		$response = $this->get(route('invoice.edit', $encId));

		$response->assertStatus(200)
			->assertViewIs('invoice.edit');
	}

	/**
	 ** @test
	 **
	 ** Display invoice details view for super admin user.
	 **/
	public function it_shows_invoice_details()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->admin->creatorId()]);
		$encId   = Crypt::encryptString($invoice->id);

		$response = $this->actingAs($this->admin)
			->get(route('invoice.show', $encId));

		$response->assertOk()
			->assertViewIs('invoice.view')
			->assertViewHas('invoice');
	}

	/**
	 ** @test
	 **
	 ** Delete a specific invoice product via AJAX and
	 ** ensure it is removed from the database.
	 **/
	public function it_deletes_invoice_product()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->admin->creatorId()]);
		$product = InvoiceProduct::factory()->create(['invoice_id' => $invoice->id]);

		$response = $this->actingAs($this->admin)
			->deleteJson(route('invoice.productDestroy'), [
				'id'     => $product->id,
				'amount' => 10,
			]);

		$response->assertRedirect();
		$this->assertDatabaseMissing('invoice_products', ['id' => $product->id]);
	}

	/**
	 ** @test
	 **
	 ** List invoices belonging to a customer user,
	 ** rendering the customer index view.
	 **/
	public function it_lists_customer_invoices()
	{
		$customer = Customer::factory()->create(['created_by' => $this->admin->creatorId()]);
		Invoice::factory()->count(2)->create([
			'customer_id' => $customer->id,
			'created_by'  => $this->admin->creatorId(),
			'status'      => 1,
		]);

		$response = $this->actingAs($customer->user)
			->get(route('invoice.customer.index'));

		$response->assertOk()
			->assertViewIs('invoice.index')
			->assertViewHas('invoices');
	}

	/**
	 ** @test
	 **
	 ** Display a single customer invoice view.
	 **/
	public function it_displays_customer_invoice_show()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->admin->creatorId()]);
		$customer = $invoice->customer;

		$response = $this->actingAs($customer->user)
			->get(route('invoice.customer.show', $invoice->id));

		$response->assertOk()
			->assertViewIs('invoice.customer_invoice');
	}

	/**
	 ** @test
	 **
	 ** Send an invoice and update its status to sent.
	 **/
	public function it_sends_invoice_and_updates_status()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->admin->creatorId(), 'status' => 0]);

		$response = $this->actingAs($this->admin)
			->post(route('invoice.sent', $invoice->id));

		$response->assertRedirect();
		$this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 1]);
	}

	/**
	 ** @test
	 **
	 ** Resend an existing invoice to the customer, flashing success.
	 **/
	public function it_resent_invoice_to_customer()
	{
		$invoice = Invoice::factory()->create(['created_by' => $this->admin->creatorId()]);

		$response = $this->actingAs($this->admin)
			->post(route('invoice.resent', $invoice->id));

		$response->assertRedirect()
			->assertSessionHas('success');
	}

	/**
	 ** @test
	 **
	 ** Render the invoice payment form for authorized users.
	 **/
	protected function actingAsUserWithPermission(string $permission)
	{
		$user = User::factory()->create();
		$user?->givePermissionTo($permission);

		return $user;
	}

	/**
	 ** @test
	 **
	 ** Display the form to record a payment against an invoice.
	 **/
	public function test_can_render_invoice_payment_form(): void
	{
		$user   = $this->actingAsUserWithPermission('create payment invoice');
		$invoice = Invoice::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->get(route('invoice.payment', $invoice->id));

		$response->assertOk()
			->assertViewIs('invoice.payment')
			->assertViewHas('invoice', $invoice);
	}

	/**
	 ** @test
	 **
	 ** Create a new invoice payment record and persist to the database.
	 **/
	public function test_can_create_invoice_payment(): void
	{
		$user   = $this->actingAsUserWithPermission('create payment invoice');
		$invoice = Invoice::factory()->create(['created_by' => $user?->creatorId()]);
		$account = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);

		$payload = [
			'date'        => now()->toDateString(),
			'amount'      => 100,
			'account_id'  => $account->id,
			'reference'   => 'Ref#001',
			'description' => 'Partial payment',
		];

		$response = $this->actingAs($user)
			->post(route('invoice.payment.create', $invoice->id), $payload);

		$response->assertRedirect();
		$this->assertDatabaseHas('invoice_payments', [
			'invoice_id' => $invoice->id,
			'amount'     => 100,
		]);
	}

	/**
	 ** @test
	 **
	 ** Delete an invoice payment and ensure it is removed.
	 **/
	public function test_can_delete_invoice_payment(): void
	{
		$user   = $this->actingAsUserWithPermission('delete payment invoice');
		$invoice = Invoice::factory()->create(['created_by' => $user?->creatorId()]);
		$payment = InvoicePayment::factory()->create(['invoice_id' => $invoice->id]);

		$response = $this->actingAs($user)
			->delete(route('invoice.payment.destroy', [$invoice->id, $payment->id]));

		$response->assertRedirect();
		$this->assertDatabaseMissing('invoice_payments', ['id' => $payment->id]);
	}

	/**
	 ** @test
	 **
	 ** Send an invoice link and set the send_date timestamp.
	 **/
	public function test_can_send_invoice(): void
	{
		$user   = $this->actingAsUserWithPermission('send invoice');
		$invoice = Invoice::factory()->create(['created_by' => $user?->creatorId()]);
		Customer::factory()->create(['id' => $invoice->customer_id]);

		$response = $this->actingAs($user)
			->get(route('invoice.sent', $invoice->id));

		$response->assertRedirect();
		$this->assertNotNull(Invoice::find($invoice->id)->send_date);
	}

	/**
	 ** @test
	 **
	 ** Resend the invoice link to the customer again.
	 **/
	public function test_can_resent_invoice(): void
	{
		$user   = $this->actingAsUserWithPermission('send invoice');
		$invoice = Invoice::factory()->create(['created_by' => $user?->creatorId()]);
		Customer::factory()->create(['id' => $invoice->customer_id]);

		$response = $this->actingAs($user)
			->get(route('invoice.resent', $invoice->id));

		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Access an invoice via a copied link and render the customer view.
	 **/
	public function test_invoice_link_returns_invoice_view(): void
	{
		$admin    = $this->actingAsUserWithPermission('manage invoice');
		$invoice  = Invoice::factory()->create(['created_by' => $admin->creatorId()]);
		$encrypted = Crypt::encrypt($invoice->id);

		$response = $this->actingAs($admin)
			->get(route('invoice.link.copy', $encrypted));

		$response->assertStatus(200)
			->assertViewIs('invoice.customer_invoice')
			->assertViewHas('invoice', fn ($inv) => $inv->id === $invoice->id);
	}

	/**
	 ** @test
	 **
	 ** Handle invalid encryption on invoice link gracefully,
	 ** redirecting back with an error message.
	 **/
	public function test_invoice_link_with_invalid_encryption_fails_gracefully(): void
	{
		$admin   = $this->actingAsUserWithPermission('manage invoice');
		$response = $this->actingAs($admin)
			->get(route('invoice.link.copy', 'invalid-encryption'));

		$response->assertRedirect()
			->assertSessionHas('error', 'Invoice Not Found.');
	}

	/**
	 ** @test
	 **
	 ** Save invoice template settings including logo upload,
	 ** persisting the logo file under the user’s directory.
	 **/
	public function test_save_template_settings_with_logo(): void
	{
		Storage::fake('local');
		$admin = $this->actingAsUserWithPermission('manage invoice');

		$file = UploadedFile::fake()->image('invoice_logo.png');

		$response = $this->actingAs($admin)
			->post(route('invoice.template.save'), [
				'invoice_template' => 'template1',
				'invoice_color'    => '00ff00',
				'invoice_logo'     => $file,
			]);

		$response->assertRedirect()
			->assertSessionHas('success', 'Invoice setting updated successfully');

		$disk = Storage::disk('local');
		assert($disk instanceof FilesystemAdapter);
		$disk->assertExists('invoice_logo/' . $admin->id . '_invoice_logo.png');
	}

	/**
	 ** @test
	 **
	 ** Save invoice template settings without a logo,
	 ** updating only the template and color.
	 **/
	public function test_save_template_settings_without_logo(): void
	{
		$admin = $this->actingAsUserWithPermission('manage invoice');

		$response = $this->actingAs($admin)
			->post(route('invoice.template.save'), [
				'invoice_template' => 'template1',
				'invoice_color'    => 'ff00ff',
			]);

		$response->assertRedirect()
			->assertSessionHas('success', 'Invoice setting updated successfully');
	}

	/**
	 ** @test
	 **
	 ** Visiting the invoice endpoint with a valid encrypted ID should
	 ** decrypt it, verify ownership, and render the configured invoice template.
	 **/
	public function invoice_displays_the_invoice_template_for_owner()
	{
		$response = $this->get(route('invoice.invoice', ['encId' => $this->encId]));

		$response->assertOk()
			->assertViewStartsWith("invoice.templates."); // any template path
	}

	/**
	 ** @test
	 **
	 ** Visiting the invoice endpoint as a non-owner should trigger
	 ** a permission denial and redirect back to the index.
	 **/
	public function invoice_denies_access_for_non_owner()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('invoice.invoice', ['encId' => $this->encId]));

		$response->assertRedirect(route('invoice.index'));
	}

	/**
	 ** @test
	 **
	 ** The customerInvoiceSend endpoint displays a simple form
	 ** to enter the recipient address.
	 **/
	public function customer_invoice_send_displays_form()
	{
		$response = $this->get(route('invoice.customerInvoiceSend', ['encId' => $this->encId]));

		$response->assertOk()
			->assertViewIs('customer.invoice_send')
			->assertViewHas('encId', $this->encId);
	}

	/**
	 ** @test
	 **
	 ** Posting invalid data to customerInvoiceSendMail should
	 ** redirect back with validation errors; valid data sends mail
	 ** and redirects with success.
	 **/
	public function customer_invoice_send_mail_validates_and_sends_mail()
	{
		Mail::fake();

		// missing email → validation
		$this->post(route('invoice.customerInvoiceSendMail', ['encId' => $this->encId]), [])
			->assertRedirect()
			->assertSessionHas('error');

		// valid request → mail sent
		$response = $this->post(route('invoice.customerInvoiceSendMail', ['encId' => $this->encId]), [
			'email' => 'test@example.com',
		]);

		$response->assertRedirect()
			->assertSessionHas('success');

		Mail::assertSent(CustomerInvoiceSend::class, function ($mail) {
			return $mail->hasTo('test@example.com');
		});
	}

	/**
	 ** @test
	 **
	 ** The previewInvoice endpoint renders a preview of the template
	 ** even without an actual invoice record.
	 **/
	public function preview_invoice_renders_preview_template()
	{
		$template = 'default';
		$colorHex = 'ff0000';

		$response = $this->get(route('invoice.previewInvoice', [
			'template' => $template,
			'colorHex' => $colorHex,
		]));

		$response->assertOk()
			->assertViewStartsWith("invoice.templates.{$template}");
	}

	/**
	 ** @test
	 **
	 ** The show endpoint decrypts the ID, checks ownership, then
	 ** renders the invoice view with payments and related models.
	 **/
	public function show_displays_the_invoice_view_for_owner()
	{
		$response = $this->get(route('invoice.show', ['encId' => $this->encId]));

		$response->assertOk()
			->assertViewIs('invoice.view')
			->assertViewHasAll(['invoice', 'customer', 'iteams', 'invoicePayment']);
	}

	/**
	 ** @test
	 **
	 ** The show endpoint for a non-owner should be denied.
	 **/
	public function show_denies_non_owner()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('invoice.show', ['encId' => $this->encId]));

		$response->assertStatus(403);
	}
}
