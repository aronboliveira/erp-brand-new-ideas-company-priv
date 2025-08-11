<?php

namespace Tests\Feature\Controllers;

use App\Models\{BankAccount, Customer, ProductServiceCategory, Revenue, User};
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, File, Gate, Storage};
use Tests\TestCase;

class RevenueControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// Bypass authentication and permissions
		Gate::before(fn () => true);

		// Ensure creatorId() returns the user's own ID
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
		Storage::fake('uploads');
		$this->company = \App\Models\User::factory()->create(['type' => 'company']);
		$this->other  = \App\Models\User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** index() should filter revenues by provided inputs and display view.
	 **/
	public function index_filters_and_displays_revenues()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$custA = Customer::factory()->create(['created_by' => $user?->creatorId()]);
		$custB = Customer::factory()->create(['created_by' => $user?->creatorId()]);
		$acct1 = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);
		$cat1 = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(), 'type' => 'income'
		]);

		// revenues
		Revenue::factory()->create([
			'created_by'  => $user?->creatorId(),
			'customer_id' => $custA->id,
			'account_id'  => $acct1->id,
			'category_id' => $cat1->id,
			'date'        => '2025-01-01',
		]);
		Revenue::factory()->create([
			'created_by'  => $user?->creatorId(),
			'customer_id' => $custB->id,
			'account_id'  => $acct1->id,
			'category_id' => $cat1->id,
			'date'        => '2025-02-01',
		]);

		// filter by customer A and date range
		$response = $this->get(route('revenue.index', [
			'customer_id' => $custA->id,
			'date'       => '2025-01-01 to 2025-01-31',
		]));

		$response->assertOk()
			->assertViewIs('revenue.index')
			->assertViewHas('revenues', function ($list) use ($custA) {
				return $list->count() === 1
					&& $list->first()->customer_id === $custA->id;
			});
	}

	/**
	 ** @test
	 **
	 ** create() should list customers, categories, and accounts.
	 **/
	public function create_displays_form_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		Customer::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(), 'type' => 'income'
		]);
		BankAccount::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->get(route('revenue.create'));

		$response->assertOk()
			->assertViewIs('revenue.create')
			->assertViewHasAll(['customers', 'categories', 'accounts']);
	}

	/**
	 ** @test
	 **
	 ** store() should validate input, save receipt file, create revenue and redirect.
	 **/
	public function store_creates_revenue_with_receipt()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$customer = Customer::factory()->create(['created_by' => $user?->creatorId()]);
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(), 'type' => 'income'
		]);
		$account = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);

		$file = UploadedFile::fake()->create('receipt.pdf', 100);

		$payload = [
			'date'           => now()->toDateString(),
			'amount'         => 123.45,
			'customer_id'     => $customer->id,
			'categoryId'     => $category->id,
			'accountId'      => $account->id,
			'reference'      => 'REF123',
			'description'    => 'Test revenue',
			'add_receipt'    => $file,
		];

		$response = $this->post(route('revenue.store'), $payload);

		$response->assertRedirect(route('revenue.index'));
		$this->assertDatabaseHas('revenues', [
			'customer_id' => $customer->id,
			'category_id' => $category->id,
			'account_id'  => $account->id,
			'description' => 'Test revenue',
		]);

		$revenue = Revenue::first();
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter::disk('uploads')->assertExists("revenue/{$revenue->add_receipt}");
	}

	/**
	 ** @test
	 **
	 ** store() should redirect back on validation failure.
	 **/
	public function store_validation_fails()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->post(route('revenue.store'), []);
		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseCount('revenues', 0);
	}

	/**
	 ** @test
	 **
	 ** edit() should display existing revenue data for editing.
	 **/
	public function edit_displays_existing_revenue()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$revenue = Revenue::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->get(route('revenue.edit', $revenue));

		$response->assertOk()
			->assertViewIs('revenue.edit')
			->assertViewHas('revenue', $revenue);
	}

	/**
	 ** @test
	 **
	 ** update() should apply changes and redirect.
	 **/
	public function update_modifies_revenue()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$customer = Customer::factory()->create(['created_by' => $user?->creatorId()]);
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(), 'type' => 'income'
		]);
		$account = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);
		$revenue = Revenue::factory()->create([
			'created_by'  => $user?->creatorId(),
			'customer_id' => $customer->id,
			'category_id' => $category->id,
			'account_id'  => $account->id,
			'amount'      => 50,
		]);

		$payload = [
			'date'           => now()->toDateString(),
			'amount'         => 75.00,
			'customer_id'     => $customer->id,
			'categoryId'     => $category->id,
			'accountId'      => $account->id,
			'reference'      => 'NEWREF',
			'description'    => 'Updated',
		];

		$response = $this->put(route('revenue.update', $revenue), $payload);

		$response->assertRedirect(route('revenue.index'));
		$this->assertDatabaseHas('revenues', [
			'id'          => $revenue->id,
			'amount'      => 75.00,
			'description' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy() should delete the revenue and its receipt file and redirect.
	 **/
	public function destroy_deletes_revenue_and_file()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		Storage::disk('uploads')->put('revenue/foo.pdf', 'contents');

		$revenue = Revenue::factory()->create([
			'created_by'  => $user?->creatorId(),
			'add_receipt' => 'foo.pdf',
		]);

		$response = $this->delete(route('revenue.destroy', $revenue));

		$response->assertRedirect(route('revenue.index'));
		$this->assertDatabaseMissing('revenues', ['id' => $revenue->id]);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter::disk('uploads')->assertMissing('revenue/foo.pdf');
	}


	/** @test
	 **
	 ** index shows list of revenues and filter dropdowns
	 **/
	public function index_shows_revenues_and_filters()
	{
		$this->actingAs($this->company);

		$cust = Customer::factory()->create(['created_by' => $this->company->creatorId()]);
		$acct = BankAccount::factory()->create(['created_by' => $this->company->creatorId()]);
		$cat = ProductServiceCategory::factory()->create([
			'created_by' => $this->company->creatorId(),
			'type'       => 'income'
		]);
		Revenue::factory()->create([
			'created_by'  => $this->company->creatorId(),
			'customer_id' => $cust->id,
			'account_id'  => $acct->id,
			'category_id' => $cat->id,
			'date'        => now()->toDateString()
		]);

		$resp = $this->get(route('revenue.index'));
		$resp->assertOk()
			->assertViewIs('revenue.index')
			->assertViewHasAll(['revenues', 'customer', 'account', 'category'])
			->assertSeeText($cust->name);
	}

	/** @test
	 **
	 ** create displays the revenue creation form
	 **/
	public function create_displays_form()
	{
		$this->actingAs($this->company);

		$resp = $this->get(route('revenue.create'));
		$resp->assertOk()
			->assertViewIs('revenue.create')
			->assertViewHasAll(['customers', 'categories', 'accounts']);
	}

	/** @test
	 **
	 ** store validates input and creates revenue
	 **/
	public function store_validates_and_creates()
	{
		$this->actingAs($this->company);

		// missing required fields yields validation error
		$this->post(route('revenue.store'), [])
			->assertSessionHasErrors(['date', 'amount', 'account_id', 'category_id']);

		$cust = Customer::factory()->create(['created_by' => $this->company->creatorId()]);
		$acct = BankAccount::factory()->create(['created_by' => $this->company->creatorId()]);
		$cat = ProductServiceCategory::factory()->create([
			'created_by' => $this->company->creatorId(),
			'type'       => 'income'
		]);

		$payload = [
			'date'        => now()->toDateString(),
			'amount'      => 123.45,
			'account_id'  => $acct->id,
			'category_id' => $cat->id,
			'customer_id' => $cust->id,
		];

		$resp = $this->post(route('revenue.store'), $payload);
		$resp->assertRedirect(route('revenue.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('revenues', [
			'created_by'  => $this->company->creatorId(),
			'amount'      => 123.45,
			'account_id'  => $acct->id,
			'category_id' => $cat->id,
		]);
	}

	/** @test
	 **
	 ** show redirects back to index
	 **/
	public function show_redirects_to_index()
	{
		$this->actingAs($this->company);

		$rev = Revenue::factory()->create(['created_by' => $this->company->creatorId()]);

		$resp = $this->get(route('revenue.show', $rev));
		$resp->assertRedirect(route('revenue.index'));
	}

	/** @test
	 **
	 ** edit displays the revenue edit form for owner
	 **/
	public function edit_displays_form_for_owner()
	{
		$this->actingAs($this->company);

		$cust = Customer::factory()->create(['created_by' => $this->company->creatorId()]);
		$acct = BankAccount::factory()->create(['created_by' => $this->company->creatorId()]);
		$cat = ProductServiceCategory::factory()->create([
			'created_by' => $this->company->creatorId(),
			'type'       => 'income'
		]);
		$rev = Revenue::factory()->create([
			'created_by'  => $this->company->creatorId(),
			'customer_id' => $cust->id,
			'account_id'  => $acct->id,
			'category_id' => $cat->id,
		]);

		$resp = $this->get(route('revenue.edit', $rev));
		$resp->assertOk()
			->assertViewIs('revenue.edit')
			->assertViewHas('revenue', fn ($r) => $r->id === $rev->id);
	}

	/** @test
	 **
	 ** update validates and saves changes
	 **/
	public function update_validates_and_saves()
	{
		$this->actingAs($this->company);

		$cust = Customer::factory()->create(['created_by' => $this->company->creatorId()]);
		$acct = BankAccount::factory()->create(['created_by' => $this->company->creatorId()]);
		$cat = ProductServiceCategory::factory()->create([
			'created_by' => $this->company->creatorId(),
			'type'       => 'income'
		]);
		$rev = Revenue::factory()->create([
			'created_by'  => $this->company->creatorId(),
			'customer_id' => $cust->id,
			'account_id'  => $acct->id,
			'category_id' => $cat->id,
			'amount'      => 50,
		]);

		// missing fields
		$this->put(route('revenue.update', $rev), [])
			->assertSessionHasErrors(['date', 'amount', 'account_id', 'category_id']);

		$newAcct = BankAccount::factory()->create(['created_by' => $this->company->creatorId()]);
		$newCat = ProductServiceCategory::factory()->create([
			'created_by' => $this->company->creatorId(),
			'type'       => 'income'
		]);

		$data = [
			'date'        => now()->addDay()->toDateString(),
			'amount'      => 99.99,
			'account_id'  => $newAcct->id,
			'category_id' => $newCat->id,
		];

		$resp = $this->put(route('revenue.update', $rev), $data);
		$resp->assertRedirect(route('revenue.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('revenues', [
			'id'          => $rev->id,
			'amount'      => 99.99,
			'account_id'  => $newAcct->id,
			'category_id' => $newCat->id,
		]);
	}

	/** @test
	 **
	 ** destroy deletes the revenue and redirects
	 **/
	public function destroy_deletes_and_redirects()
	{
		$this->actingAs($this->company);

		$rev = Revenue::factory()->create(['created_by' => $this->company->creatorId()]);

		$resp = $this->delete(route('revenue.destroy', $rev));
		$resp->assertRedirect(route('revenue.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('revenues', ['id' => $rev->id]);
	}
}
