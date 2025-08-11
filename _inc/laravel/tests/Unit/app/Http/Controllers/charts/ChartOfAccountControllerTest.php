<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChartOfAccountType;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Http\Controllers\ChartOfAccountController;

class ChartOfAccountControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// Create a test user
		$this->user = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Index should require login and "manage" permission,
	 ** then display the account list with default date filters.
	 **/
	public function index_requires_authentication_and_permission()
	{
		// unauthenticated → redirect to login
		$resp = $this->get(action([ChartOfAccountController::class, 'index']));
		$resp->assertRedirect();

		// authenticated but no permission → redirect back to index
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'index']));
		$resp->assertRedirect(route('chartOfAccount.index'));

		// grant permission and seed a type
		$this->user->givePermissionTo('manage chart of account');
		ChartOfAccountType::create([
			'name'       => 'Assets',
			'created_by' => $this->user->creatorId(),
		]);

		// now should render index view with data
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'index']));

		$resp->assertOk()
			->assertViewIs('chartOfAccount.index')
			->assertViewHasAll(['chartAccounts', 'types', 'filter']);

		// verify default filter dates
		$filter = $resp->viewData('filter');
		$this->assertEquals(
			Carbon::now()->startOfYear()->format('Y-m-d'),
			$filter['startDateRange']
		);
		$this->assertEquals(
			Carbon::now()->addDay()->format('Y-m-d'),
			$filter['endDateRange']
		);
	}

	/**
	 ** @test
	 **
	 ** Create action should require "create" permission
	 ** and then display the form with account types.
	 **/
	public function create_requires_permission_and_shows_form()
	{
		// no permission
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'create']));
		$resp->assertRedirect(route('chartOfAccount.index'));

		// grant permission & seed type
		$this->user->givePermissionTo('create chart of account');
		ChartOfAccountType::create([
			'name'       => 'Liabilities',
			'created_by' => $this->user->creatorId(),
		]);

		// now should render create form
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'create']));

		$resp->assertOk()
			->assertViewIs('chartOfAccount.create')
			->assertViewHas('types');
	}

	/**
	 ** @test
	 **
	 ** Store should validate input and then persist a new account record.
	 **/
	public function store_validates_and_creates_record()
	{
		$this->user->givePermissionTo('create chart of account');

		// missing fields → error
		$resp = $this->actingAs($this->user)
			->post(action([ChartOfAccountController::class, 'store']), []);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// valid payload
		$type = ChartOfAccountType::create([
			'name'       => 'Equity',
			'created_by' => $this->user->creatorId(),
		]);

		$payload = [
			'name'        => 'Capital',
			'code'        => 'EQT-001',
			'type'        => $type->id,
			'sub_type'    => null,
			'description' => 'Owner’s Capital',
			'is_enabled'  => 'on',
		];

		$resp = $this->actingAs($this->user)
			->post(action([ChartOfAccountController::class, 'store']), $payload);

		$resp->assertRedirect(route('chartOfAccount.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('chart_of_accounts', [
			'name'       => 'Capital',
			'type'       => $type->id,
			'code'       => 'EQT-001',
			'is_enabled' => 1,
		]);
	}

	/**
	 ** @test
	 **
	 ** Show should require "ledger report" permission,
	 ** then render the ledger with zero balances if no entries.
	 **/
	public function show_requires_permission_and_renders_ledger()
	{
		// seed type & account
		$type = ChartOfAccountType::create([
			'name'       => 'Expenses',
			'created_by' => $this->user->creatorId(),
		]);
		$acct = ChartOfAccount::create([
			'name'        => 'Rent',
			'code'        => 'EXP-001',
			'type'        => $type->id,
			'sub_type'    => null,
			'description' => 'Monthly rent',
			'is_enabled'  => 1,
			'created_by'  => $this->user->creatorId(),
		]);

		// no permission → redirect
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'show'], ['chartOfAccount' => $acct->id]));
		$resp->assertRedirect(route('chartOfAccount.index'));

		// grant permission
		$this->user->givePermissionTo('ledger report');

		// now should render show
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'show'], ['chartOfAccount' => $acct->id]));

		$resp->assertOk()
			->assertViewIs('chartOfAccount.show')
			->assertViewHasAll([
				'account', 'accounts', 'journalItems',
				'debit', 'credit', 'balance', 'filter'
			]);

		// verify zero balances
		$this->assertEquals(0, $resp->viewData('debit'));
		$this->assertEquals(0, $resp->viewData('credit'));
		$this->assertEquals(0, $resp->viewData('balance'));
	}

	/**
	 ** @test
	 **
	 ** Full edit→update→destroy flow should enforce permissions
	 ** and correctly persist or remove the account.
	 **/
	public function edit_update_and_destroy_flow()
	{
		$type = ChartOfAccountType::create([
			'name'       => 'Income',
			'created_by' => $this->user->creatorId(),
		]);
		$acct = ChartOfAccount::create([
			'name'        => 'Sales',
			'code'        => 'INC-001',
			'type'        => $type->id,
			'sub_type'    => null,
			'description' => '',
			'is_enabled'  => 1,
			'created_by'  => $this->user->creatorId(),
		]);

		// EDIT: no permission → redirect
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'edit'], ['chartOfAccount' => $acct->id]));
		$resp->assertRedirect(route('chartOfAccount.index'));

		// grant edit permission
		$this->user->givePermissionTo('edit chart of account');

		// now render edit form
		$resp = $this->actingAs($this->user)
			->get(action([ChartOfAccountController::class, 'edit'], ['chartOfAccount' => $acct->id]));
		$resp->assertOk()
			->assertViewIs('chartOfAccount.edit')
			->assertViewHasAll(['chartOfAccount', 'types']);

		// UPDATE: missing name → error
		$resp = $this->actingAs($this->user)
			->put(action([ChartOfAccountController::class, 'update'], ['chartOfAccount' => $acct->id]), []);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// valid update
		$resp = $this->actingAs($this->user)
			->put(action([ChartOfAccountController::class, 'update'], ['chartOfAccount' => $acct->id]), [
				'name' => 'Updated Sales',
			]);
		$resp->assertRedirect(route('chartOfAccount.index'))
			->assertSessionHas('success');
		$this->assertDatabaseHas('chart_of_accounts', [
			'id'   => $acct->id,
			'name' => 'Updated Sales',
		]);

		// DESTROY: no permission → redirect
		$resp = $this->actingAs($this->user)
			->delete(action([ChartOfAccountController::class, 'destroy'], ['chartOfAccount' => $acct->id]));
		$resp->assertRedirect(route('chartOfAccount.index'));

		// grant delete permission
		$this->user->givePermissionTo('delete chart of account');

		// now can destroy
		$resp = $this->actingAs($this->user)
			->delete(action([ChartOfAccountController::class, 'destroy'], ['chartOfAccount' => $acct->id]));
		$resp->assertRedirect(route('chartOfAccount.index'))
			->assertSessionHas('success');
		$this->assertDatabaseMissing('chart_of_accounts', ['id' => $acct->id]);
	}

	/**
	 ** @test
	 **
	 ** getSubType should return JSON mapping of sub-types for a given type.
	 **/
	public function get_sub_type_returns_json_list()
	{
		// seed two sub-types under type=42
		$st1 = ChartOfAccountSubType::create(['name' => 'Payroll',  'type' => 42]);
		$st2 = ChartOfAccountSubType::create(['name' => 'Benefits', 'type' => 42]);
		// one under another type
		ChartOfAccountSubType::create(['name' => 'Other',    'type' => 99]);

		$resp = $this->actingAs($this->user)
			->getJson(action([ChartOfAccountController::class, 'getSubType']), ['type' => 42]);

		$resp->assertOk()
			->assertExactJson([
				(string)$st1->id => 'Payroll',
				(string)$st2->id => 'Benefits',
			]);
	}
}
