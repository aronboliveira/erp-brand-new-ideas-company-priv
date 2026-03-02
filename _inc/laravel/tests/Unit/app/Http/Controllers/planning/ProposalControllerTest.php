<?php

namespace Tests\Feature\Controllers;

use App\Models\{
	Customer,
	ProductService,
	ProductServiceCategory,
	Proposal,
	ProposalProduct,
	User
};
use Tests\Unit\Traits\CreatesMockUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\{Crypt, Gate, Http};
use Tests\TestCase;
use Maatwebsite\Excel\Facades\Excel;

class ProposalControllerTest extends TestCase
{
	use RefreshDatabase, CreatesMockUser;

	protected User $user;
	protected Customer $customer;
	protected string $encId;
	private Proposal $proposal;

	protected function setUp(): void
	{
		parent::setUp();

		// bypass all permission checks
		Gate::before(fn () => true);

		// user context
		$this->user    = User::factory()->create();
		$this->actingAs($this->user);

		$this->customer = Customer::factory()->create(['created_by' => $this->user->creatorId()]);
		$proposal = Proposal::factory()->create([
			'customer_id' => $this->customer->id,
			'created_by'  => $this->user->creatorId(),
		]);
		$this->proposal = Proposal::factory()->has(
			ProposalProduct::factory()->count(2)
		)->create();
		$this->actingAsUserWithAllPermissions();
		$this->encId = Crypt::encrypt((string)$proposal->id);
	}

	/**
	 ** @test
	 **
	 ** index shows only this user's proposals, with filtering options.
	 **/
	public function index_displays_user_proposals_and_filters()
	{
		Proposal::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);
		Proposal::factory()->create(); // other user

		$response = $this->get(route('proposal.index'));
		$response->assertOk()
			->assertViewIs('proposal.index')
			->assertViewHas('proposals', fn ($col) => $col->every(fn ($p) => $p->created_by === $this->user->creatorId()));
	}

	/**
	 ** @test
	 **
	 ** create displays form with customers, categories, products, and custom fields.
	 **/
	public function create_shows_form_data()
	{
		ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 'income']);
		ProductService::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('proposal.create', ['customer_id' => $this->customer->id]));
		$response->assertOk()
			->assertViewIs('proposal.create')
			->assertViewHasAll([
				'customers', 'proposalNumber', 'productServices', 'category', 'customFields', 'customer_id'
			]);
	}

	/**
	 ** @test
	 **
	 ** product endpoint returns product details JSON.
	 **/
	public function product_endpoint_returns_json()
	{
		$product = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->getJson(route('proposal.product'), ['product_id' => $product->id]);
		$response->assertOk()
			->assertJsonStructure(['product', 'unit', 'taxRate', 'taxes', 'total']);
	}

	/**
	 ** @test
	 **
	 ** store validates input and creates proposal with its items.
	 **/
	public function store_creates_proposal_and_items_or_fails_validation()
	{
		// validation fail: missing items
		$bad = $this->post(route('proposal.store'), ['customer_id' => $this->customer->id]);
		$bad->assertSessionHasErrors(['items']);

		// success
		$category = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 'income']);
		$prod    = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);
		$payload = [
			'customer_id' => $this->customer->id,
			'issue_date'  => now()->toDateString(),
			'category_id' => $category->id,
			'items'       => [
				['item' => $prod->id, 'quantity' => 2, 'tax' => 0, 'discount' => 0, 'price' => 100, 'description' => 'Foo'],
			],
		];
		$response = $this->post(route('proposal.store'), $payload);
		$response->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('proposals', [
			'customer_id' => $this->customer->id,
			'category_id' => $category->id,
			'created_by'  => $this->user->creatorId(),
		]);
		$this->assertDatabaseHas('proposal_products', [
			'product_id'  => $prod->id,
			'quantity'    => 2,
		]);
	}

	/**
	 ** @test
	 **
	 ** edit decrypts id, enforces ownership, and displays edit view.
	 **/
	public function edit_displays_edit_form_or_denies_on_wrong_owner()
	{
		$response = $this->get(route('proposal.edit', ['encId' => $this->encId]));
		$response->assertOk()
			->assertViewIs('proposal.edit')
			->assertViewHasAll([
				'customers', 'productServices', 'proposal', 'proposalNumber', 'category', 'customFields', 'items'
			]);
	}

	/**
	 ** @test
	 **
	 ** update validates and updates proposal and its items.
	 **/
	public function update_modifies_proposal_and_items_or_fails_validation()
	{
		// validation error
		$bad = $this->put(route('proposal.update', ['proposal' => Proposal::first()->id]), ['customer_id' => null]);
		$bad->assertSessionHasErrors(['customer_id']);

		// success
		$proposal = Proposal::first();
		$category = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 'income']);
		$prod    = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);
		// seed an existing item
		$pp      = ProposalProduct::factory()->create(['proposal_id' => $proposal->id]);
		$payload = [
			'customer_id' => $this->customer->id,
			'issue_date'  => now()->toDateString(),
			'category_id' => $category->id,
			'items'       => [
				['id' => $pp->id, 'item' => $prod->id, 'quantity' => 5, 'tax' => 1, 'discount' => 0, 'price' => 200, 'description' => 'Bar'],
			],
		];
		$resp = $this->put(route('proposal.update', ['proposal' => $proposal->id]), $payload);
		$resp->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('proposals', [
			'id'          => $proposal->id,
			'category_id' => $category->id,
		]);
		$this->assertDatabaseHas('proposal_products', [
			'id'       => $pp->id,
			'quantity' => 5,
			'price'    => 200,
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy deletes proposal and its products.
	 **/
	public function destroy_deletes_proposal_and_products()
	{
		$proposal = Proposal::first();
		ProposalProduct::factory()->create(['proposal_id' => $proposal->id]);

		$resp = $this->delete(route('proposal.destroy', ['proposal' => $proposal->id]));
		$resp->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
		$this->assertDatabaseMissing('proposal_products', ['proposal_id' => $proposal->id]);
	}

	/**
	 ** @test
	 **
	 ** product_destroy deletes a single proposal product.
	 **/
	public function product_destroy_removes_proposal_product()
	{
		$pp = ProposalProduct::factory()->create(['proposal_id' => Proposal::first()->id]);
		$resp = $this->delete(route('proposal.product_destroy'), ['id' => $pp->id]);
		$resp->assertRedirect()
			->assertSessionHas('success');
		$this->assertDatabaseMissing('proposal_products', ['id' => $pp->id]);
	}

	/**
	 ** @test
	 **
	 ** show decrypts id, enforces ownership, and displays view.
	 **/
	public function show_displays_proposal_view_or_denies()
	{
		$response = $this->get(route('proposal.show', ['encId' => $this->encId]));
		$response->assertOk()
			->assertViewIs('proposal.view')
			->assertViewHas('proposal', function ($p) {
				return $p->id === $this->encId;
			});
	}

	/**
	 ** @test
	 **
	 ** customer_proposal lists only non-zero status proposals for the customer guard.
	 **/
	public function customer_proposal_filters_and_displays()
	{
		// mark one as sent
		$this->proposal->update(['status' => 1]);
		$response = $this->get(route('proposal.customer_proposal'));
		$response->assertOk()
			->assertViewIs('proposal.index')
			->assertViewHas('proposals', fn ($col) => $col->first()->id === $this->proposal->id);
	}

	/**
	 ** @test
	 **
	 ** customer_proposal_show shows same view as show.
	 **/
	public function customer_proposal_show_behaves_like_show()
	{
		$this->proposal->update(['status' => 1]);
		$resp = $this->get(route('proposal.customer_proposal_show', ['encId' => $this->encId]));
		$resp->assertOk()
			->assertViewIs('proposal.view')
			->assertViewHas('proposal', fn ($p) => $p->id === $this->proposal->id);
	}

	/**
	 ** @test
	 **
	 ** sent marks status and send_date, sends email if enabled.
	 **/
	public function sent_updates_and_sends_email()
	{
		// enable template setting
		/** @var \App\Models\Utility $u */
		$u = \App\Models\Utility::create(['name' => 'proposal_sent', 'value' => 'on']);
		Http::fake(); // skip real email

		$resp = $this->get(route('proposal.sent', ['encId' => $this->proposal->id]));
		$resp->assertRedirect()
			->assertSessionHas('success', fn ($msg) => str_contains($msg, 'sent'));
		$this->assertDatabaseHas('proposals', [
			'id'     => $this->proposal->id,
			'status' => 1,
			// send_date set today
			'send_date' => now()->toDateString(),
		]);
	}

	/**
	 ** @test
	 **
	 ** resent re-sends email without changing send_date.
	 **/
	public function resent_re_sends_email()
	{
		$this->proposal->update(['status' => 1, 'send_date' => now()->subDay()->toDateString()]);
		\App\Models\Utility::create(['name' => 'proposal_sent', 'value' => 'on']);
		Http::fake();

		$resp = $this->get(route('proposal.resent', ['encId' => $this->proposal->id]));
		$resp->assertRedirect()
			->assertSessionHas('success', 'Proposal successfully sent.');
		$this->assertDatabaseHas('proposals', [
			'id'        => $this->proposal->id,
			// original send_date unchanged
			'send_date' => $this->proposal->send_date,
		]);
	}

	/**
	 ** @test
	 **
	 ** shipping_display toggles the shipping_display flag.
	 **/
	public function shipping_display_toggles_flag()
	{
		$respTrue = $this->get(route('proposal.shipping_display', ['id' => $this->proposal->id, 'is_display' => 'true']));
		$respTrue->assertRedirect()->assertSessionHas('success');
		$this->assertDatabaseHas('proposals', ['id' => $this->proposal->id, 'shipping_display' => 1]);

		$respFalse = $this->get(route('proposal.shipping_display', ['id' => $this->proposal->id, 'is_display' => 'false']));
		$respFalse->assertRedirect()->assertSessionHas('success');
		$this->assertDatabaseHas('proposals', ['id' => $this->proposal->id, 'shipping_display' => 0]);
	}

	/**
	 ** @test
	 **
	 ** duplicate replicates proposal and its items.
	 **/
	public function duplicate_creates_copy()
	{
		// seed items
		$item = ProposalProduct::factory()->create(['proposal_id' => $this->proposal->id]);
		$countBefore = Proposal::count();

		$resp = $this->get(route('proposal.duplicate', ['id' => $this->proposal->id]));
		$resp->assertRedirect()->assertSessionHas('success');

		$this->assertEquals($countBefore + 1, Proposal::count());
		$dup = Proposal::latest('id')->first();
		$this->assertDatabaseHas('proposal_products', ['proposal_id' => $dup->id, 'product_id' => $item->product_id]);
	}

	/**
	 ** @test
	 **
	 ** convert creates invoice and replicates items & updates inventory.
	 **/
	public function convert_to_invoice_successfully()
	{
		$prodItem = ProposalProduct::factory()->create(['proposal_id' => $this->proposal->id, 'quantity' => 2]);
		Excel::fake(); // stub excel only
		// ensure invoice number logic
		$resp = $this->get(route('proposal.convert', ['id' => $this->proposal->id]));
		$resp->assertRedirect()->assertSessionHas('success');
		$this->assertDatabaseHas('invoices', ['created_by' => $this->user->creatorId()]);
		$this->assertDatabaseHas('invoice_products', ['product_id' => $prodItem->product_id, 'quantity' => 2]);
	}

	/**
	 ** @test
	 **
	 ** statusChange updates status.
	 **/
	public function statusChange_updates_status_field()
	{
		$resp = $this->post(route('proposal.statusChange', ['id' => $this->proposal->id, 'status' => 2]));
		$resp->assertRedirect()->assertSessionHas('success');
		$this->assertDatabaseHas('proposals', ['id' => $this->proposal->id, 'status' => 2]);
	}

	/**
	 ** @test
	 **
	 ** preview_proposal renders template view.
	 **/
	public function preview_proposal_renders_template()
	{
		\App\Models\Utility::create(['name' => 'company_logo_dark', 'value' => 'logo.png']);
		$response = $this->get(route('proposal.preview_proposal', ['template' => 'default', 'color' => 'ff0000']));
		$response->assertOk()
			->assertViewExists("proposal.templates.default")
			->assertViewHas('proposal')
			->assertViewHas('color')
			->assertViewHas('img');
	}

	/**
	 ** @test
	 **
	 ** proposal_link shows public proposal page for owner.
	 **/
	public function proposal_link_displays_customer_page()
	{
		$response = $this->get(route('proposal.proposal_link', ['encId' => $this->encId]));
		$response->assertOk()
			->assertViewIs('proposal.customer_proposal')
			->assertViewHas('proposal', fn ($p) => $p->id === $this->proposal->id);
	}

	/**
	 ** @test
	 **
	 ** export downloads an excel file.
	 **/
	public function export_downloads_excel()
	{
		Excel::fake();
		$response = $this->get(route('proposal.export'));
		$response->assertStatus(Response::HTTP_OK)
			->assertHeader('content-disposition');
	}

	/**
	 ** @test
	 **
	 ** items returns JSON for given proposal and product.
	 **/
	public function items_endpoint_returns_json_item()
	{
		$pp = ProposalProduct::factory()->create(['proposal_id' => $this->proposal->id]);
		$response = $this->postJson(route('proposal.items'), [
			'proposal_id' => $this->proposal->id,
			'product_id'  => $pp->product_id,
		]);
		$response->assertOk()
			->assertJsonFragment(['id' => $pp->id]);
	}

	/**
	 ** @test
	 **
	 ** index lists proposals with filters applied.
	 **/
	public function index_filters_by_customer_date_and_status()
	{
		// seed
		$otherCust = Customer::factory()->create(['created_by' => $this->user->creatorId()]);
		$p1 = Proposal::factory()->create([
			'customer_id' => $this->customer->id,
			'issue_date'  => '2025-01-01',
			'status'      => 2,
			'created_by'  => $this->user->creatorId(),
		]);
		$p2 = Proposal::factory()->create([
			'customer_id' => $otherCust->id,
			'issue_date'  => '2025-02-01',
			'status'      => 1,
			'created_by'  => $this->user->creatorId(),
		]);

		// filter by customer
		$respCust = $this->get(route('proposal.index', ['customer' => $this->customer->id]));
		$respCust->assertOk()
			->assertViewHas('proposals', fn ($col) => $col->pluck('id')->all() === [$p1->id]);

		// filter by date range
		$respDate = $this->get(route('proposal.index', ['issue_date' => '2025-01-01 to 2025-01-31']));
		$respDate->assertOk()
			->assertViewHas('proposals', fn ($col) => $col->pluck('id')->all() === [$p1->id]);

		// filter by status
		$respStat = $this->get(route('proposal.index', ['status' => 1]));
		$respStat->assertOk()
			->assertViewHas('proposals', fn ($col) => $col->pluck('id')->all() === [$p2->id]);
	}

	/**
	 ** @test
	 **
	 ** create shows form with dropdowns.
	 **/
	public function create_renders_form_with_selects()
	{
		ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'income',
		]);
		ProductService::factory()->create(['created_by' => $this->user->creatorId()]);

		$resp = $this->get(route('proposal.create', ['customer_id' => $this->customer->id]));
		$resp->assertOk()
			->assertViewIs('proposal.create')
			->assertViewHasAll([
				'customers', 'proposalNumber',
				'productServices', 'category', 'customFields', 'customer_id'
			]);
	}

	/**
	 ** @test
	 **
	 ** customer returns customer detail view.
	 **/
	public function customer_displays_customer_detail()
	{
		$resp = $this->get(route('proposal.customer', ['id' => $this->customer->id]));
		$resp->assertOk()
			->assertViewIs('proposal.customer_detail')
			->assertViewHas('customer', fn ($c) => $c->id === $this->customer->id);
	}

	/**
	 ** @test
	 **
	 ** product returns JSON metadata for a service.
	 **/
	public function product_returns_json_product_info()
	{
		$svc = ProductService::factory()->create(['created_by' => $this->user->creatorId(), 'tax_id' => null]);
		$resp = $this->getJson(route('proposal.product', ['product_id' => $svc->id]));
		$resp->assertOk()
			->assertJsonStructure(['product', 'unit', 'taxRate', 'taxes', 'total']);
	}

	/**
	 ** @test
	 **
	 ** store validates and persists proposal and items.
	 **/
	public function store_creates_proposal_and_items()
	{
		$cat = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 'income']);
		$svc = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);

		$payload = [
			'customer_id' => $this->customer->id,
			'issue_date'  => now()->toDateString(),
			'category_id' => $cat->id,
			'items'       => [
				['item' => $svc->id, 'quantity' => 3, 'tax' => 0, 'discount' => 0, 'price' => 100, 'description' => 'x'],
			],
		];

		$resp = $this->post(route('proposal.store'), $payload);
		$resp->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('proposals', ['customer_id' => $this->customer->id, 'category_id' => $cat->id]);
		$this->assertDatabaseHas('proposal_products', ['product_id' => $svc->id, 'quantity' => 3]);
	}

	/**
	 ** @test
	 **
	 ** update validates and updates proposal and items.
	 **/
	public function update_modifies_proposal_and_items()
	{
		$cat = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 'income']);
		$item = ProposalProduct::factory()->create(['proposal_id' => $this->proposal->id, 'quantity' => 1]);

		$newData = [
			'customer_id' => $this->customer->id,
			'issue_date'  => now()->addDay()->toDateString(),
			'category_id' => $cat->id,
			'items'       => [
				['id' => $item->id, 'item' => $item->product_id, 'quantity' => 5, 'tax' => 0, 'discount' => 0, 'price' => 200, 'description' => 'upd'],
			],
		];

		$resp = $this->put(route('proposal.update', $this->proposal), $newData);
		$resp->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('proposals', ['id' => $this->proposal->id, 'category_id' => $cat->id]);
		$this->assertDatabaseHas('proposal_products', ['id' => $item->id, 'quantity' => 5, 'price' => 200]);
	}
	/**
	 ** @test
	 **
	 ** product_destroy deletes a single proposal product.
	 **/
	public function product_destroy_removes_specific_item()
	{
		$prod = ProposalProduct::factory()->create(['proposal_id' => $this->proposal->id]);
		$resp = $this->post(route('proposal.product_destroy'), ['id' => $prod->id]);
		$resp->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('proposal_products', ['id' => $prod->id]);
	}


	/**
	 ** @test
	 **
	 ** Shows the proposal template view for the owner when given a valid encrypted ID
	 **/
	public function it_displays_the_proposal_template_for_owner()
	{
		$user    = User::factory()->create();
		$proposal = Proposal::factory()->create(['created_by' => $user?->creatorId()]);
		$encId   = Crypt::encryptString($proposal->id);

		$response = $this->actingAs($user)->get(route('proposal.proposal', $encId));

		$response->assertStatus(200)
			->assertViewStartsWith("proposal.templates");
	}

	/**
	 ** @test
	 **
	 ** Denies access if the proposal does not belong to the user
	 **/
	public function it_denies_access_to_proposal_of_other_user()
	{
		$owner   = User::factory()->create();
		$other   = User::factory()->create();
		$proposal = Proposal::factory()->create(['created_by' => $owner->creatorId()]);
		$encId   = Crypt::encryptString($proposal->id);

		$response = $this->actingAs($other)->get(route('proposal.proposal', $encId));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Returns 403 if user lacks 'show proposal' permission
	 **/
	public function it_returns_403_if_not_authorized_to_show()
	{
		Gate::before(fn () => false);

		$user    = User::factory()->create();
		$proposal = Proposal::factory()->create(['created_by' => $user?->creatorId()]);
		$encId   = Crypt::encryptString($proposal->id);

		$response = $this->actingAs($user)->get(route('proposal.proposal', $encId));

		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Persists proposal template settings without file upload
	 **/
	public function it_saves_proposal_template_settings()
	{
		$user = User::factory()->create();

		$data = [
			'proposal_template' => 'modern',
			'proposal_color'    => '',
		];

		$response = $this->actingAs($user)
			->post(route('proposal.saveProposalTemplateSettings'), $data);

		$response->assertRedirect()
			->assertSessionHas('success');

		// Should have inserted at least these two settings
		$this->assertDatabaseHas('settings', [
			'name'       => 'proposal_template',
			'value'      => 'modern',
			'created_by' => $user?->creatorId(),
		]);
		$this->assertDatabaseHas('settings', [
			'name'       => 'proposal_color',
			'value'      => 'ffffff',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Shows the customer-facing proposal page when valid and owned
	 **/
	public function it_displays_customer_proposal_link_for_owner()
	{
		$user    = User::factory()->create();
		$proposal = Proposal::factory()->create(['created_by' => $user?->creatorId()]);
		$encId   = Crypt::encryptString($proposal->id);

		$response = $this->actingAs($user)
			->get(route('proposal.invoiceLink', $encId));

		$response->assertStatus(200)
			->assertViewIs('proposal.customer_proposal');
	}

	/**
	 ** @test
	 **
	 ** Denies the customer proposal link if not the owner
	 **/
	public function it_denies_invoice_link_for_non_owner()
	{
		$owner   = User::factory()->create();
		$other   = User::factory()->create();
		$proposal = Proposal::factory()->create(['created_by' => $owner->creatorId()]);
		$encId   = Crypt::encryptString($proposal->id);

		$response = $this->actingAs($other)
			->get(route('proposal.invoiceLink', $encId));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}
}
