<?php

namespace Tests\Feature;

use App\Http\Controllers\BankAccountController;
use App\Models\{BankAccount, ChartOfAccount, CustomField, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, DB, Gate, Request};
use Tests\TestCase;

class BankAccountControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Grant all permissions in these tests
		Gate::before(fn () => true);

		// Ensure creatorId() returns the user's own ID
		User::macro(
			'creatorId',
			/**
			 ** @this \App\Models\User
			 ** @return int|string
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
	 ** index should list only the BankAccount records
	 ** belonging to the authenticated user.
	 **/
	public function index_shows_only_the_users_bank_accounts()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$acct1 = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);
		$acct2 = BankAccount::factory()->create(['created_by' => $other->creatorId()]);

		$response = $this->actingAs($user)->get(route('bank-account.index'));

		$response->assertStatus(200)
			->assertViewIs('bankAccount.index')
			->assertViewHas(
				'accounts',
				fn ($accounts) =>
				$accounts->pluck('id')->all() === [$acct1->id]
			);
	}

	/**
	 ** @test
	 **
	 ** create should render the form with the user's ChartOfAccount
	 ** list and associated CustomField definitions.
	 **/
	public function create_displays_chart_accounts_and_custom_fields()
	{
		$user = User::factory()->create();

		$ca1 = ChartOfAccount::factory()->create(['created_by' => $user?->creatorId()]);
		$cf1 = CustomField::factory()->create([
			'created_by' => $user?->creatorId(),
			'module'     => 'account',
		]);

		$response = $this->actingAs($user)->get(route('bank-account.create'));

		$response->assertStatus(200)
			->assertViewIs('bankAccount.create')
			->assertViewHas(
				'chartAccounts',
				fn ($list) =>
				$list->contains($ca1->id)
			)
			->assertViewHas(
				'customFields',
				fn ($list) =>
				$list->pluck('id')->contains($cf1->id)
			);
	}

	/**
	 ** @test
	 **
	 ** store should validate input, create a new BankAccount record,
	 ** and redirect back to the index on success.
	 **/
	public function store_validates_and_creates_new_account()
	{
		$user = User::factory()->create();

		$payload = [
			'holderName'     => 'John Doe',
			'bankName'       => 'Acme Bank',
			'accountNumber'  => 'ACC123',
			'openingBalance' => 500.75,
			'contactNumber'  => '+1 (555) 123-4567',
			'bankAddress'    => '123 Main St',
		];

		$response = $this->actingAs($user)
			->post(route('bank-account.store'), $payload);

		$response->assertRedirect(route('bank-account.index'));

		$this->assertDatabaseHas('bank_accounts', [
			'holder_name'    => 'John Doe',
			'bank_name'      => 'Acme Bank',
			'account_number' => 'ACC123',
			'created_by'     => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** store should redirect with an error message when validation fails.
	 **/
	public function store_redirects_with_error_on_validation_failure()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->post(route('bank-account.store'), [
				'bankName' => 'No Holder',
			]);

		$response->assertRedirect(route('bank-account.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** edit should show the existing BankAccount data along with
	 ** ChartOfAccount and CustomField lists.
	 **/
	public function edit_displays_existing_account_and_related_data()
	{
		$user   = User::factory()->create();
		$account = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);
		$ca     = ChartOfAccount::factory()->create(['created_by' => $user?->creatorId()]);
		$cf     = CustomField::factory()->create([
			'created_by' => $user?->creatorId(),
			'module'     => 'account',
		]);

		$response = $this->actingAs($user)
			->get(route('bank-account.edit', $account));

		$response->assertStatus(200)
			->assertViewIs('bankAccount.edit')
			->assertViewHasAll(['bankAccount', 'chartAccounts', 'customFields']);
	}

	/**
	 ** @test
	 **
	 ** update should validate and apply changes to the BankAccount record,
	 ** then redirect back to the index.
	 **/
	public function update_validates_and_updates_account()
	{
		$user   = User::factory()->create();
		$account = BankAccount::factory()->create([
			'created_by'     => $user?->creatorId(),
			'account_number' => 'OLD123',
		]);

		$payload = [
			'holderName'     => 'Jane Smith',
			'bankName'       => 'New Bank',
			'accountNumber'  => 'NEW456',
			'openingBalance' => 1000,
			'contactNumber'  => '555-0000',
			'bankAddress'    => '456 Elm St',
		];

		$response = $this->actingAs($user)
			->put(route('bank-account.update', $account), $payload);

		$response->assertRedirect(route('bank-account.index'));

		$this->assertDatabaseHas('bank_accounts', [
			'id'             => $account->id,
			'holder_name'    => 'Jane Smith',
			'account_number' => 'NEW456',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the BankAccount if no related records exist,
	 ** otherwise it should redirect back with an error.
	 **/
	public function destroy_deletes_when_no_relations_exist()
	{
		$user   = User::factory()->create();
		$account = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->delete(route('bank-account.destroy', $account));

		$response->assertRedirect(route('bank-account.index'));
		$this->assertDatabaseMissing('bank_accounts', ['id' => $account->id]);
	}

	/**
	 ** @test
	 **
	 ** destroy should reject deletion and set an error if related
	 ** records (e.g., revenues) exist for the BankAccount.
	 **/
	public function destroy_rejects_when_related_records_exist()
	{
		$user   = User::factory()->create();
		$account = BankAccount::factory()->create(['created_by' => $user?->creatorId()]);

		DB::table('revenues')->insert([
			'account_id' => $account->id,
			'created_at' => now(),
			'updated_at' => now(),
		]);

		$response = $this->actingAs($user)
			->delete(route('bank-account.destroy', $account));

		$response->assertRedirect(route('bank-account.index'))
			->assertSessionHas('error');

		$this->assertDatabaseHas('bank_accounts', ['id' => $account->id]);
	}

	/**
	 ** @test
	 **
	 ** show() redirects guests to login.
	 **/
	public function show_redirects_guests_to_login()
	{
		$bank      = BankAccount::factory()->create();
		$controller = new BankAccountController();
		$request   = Request::create("/bank-account/{$bank->id}", 'GET');
		// no authenticated user
		$request->setUserResolver(fn () => null);

		$response = $controller->show($request, $bank);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertEquals(route('login'), $response->headers->get('Location'));
	}

	/**
	 ** @test
	 **
	 ** show() redirects an authorized owner to the index without error flash.
	 **/
	public function show_redirects_owner_to_index()
	{
		$user      = User::factory()->create();
		$user?->givePermissionTo('view bank account');

		$bank = BankAccount::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$controller = new BankAccountController();
		$request   = Request::create("/bank-account/{$bank->id}", 'GET');
		$request->setUserResolver(fn () => $user);
		Auth::login($user);

		$response = $controller->show($request, $bank);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertEquals(route('bank-account.index'), $response->headers->get('Location'));
		$this->assertFalse(session()->has('error'));
	}

	/**
	 ** @test
	 **
	 ** show() denies access to a non-owner (even with permission) and flashes an error.
	 **/
	public function show_denies_non_owner_and_flashes_error()
	{
		$owner = User::factory()->create();
		$owner->givePermissionTo('view bank account');

		$other = User::factory()->create();
		$other->givePermissionTo('view bank account');

		$bank = BankAccount::factory()->create([
			'created_by' => $owner->creatorId(),
		]);

		$controller = new BankAccountController();
		$request   = Request::create("/bank-account/{$bank->id}", 'GET');
		$request->setUserResolver(fn () => $other);
		Auth::login($other);
		// attach session to request so flashing works
		$request->setLaravelSession(session());

		$response = $controller->show($request, $bank);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertEquals(route('bank-account.index'), $response->headers->get('Location'));
		$this->assertTrue(session()->has('error'));
	}
}
