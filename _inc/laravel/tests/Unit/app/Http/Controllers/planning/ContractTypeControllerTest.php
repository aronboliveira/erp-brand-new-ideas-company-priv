<?php

namespace Tests\Feature;

use App\Http\Controllers\ContractTypeController;
use App\Models\{Contract, ContractType, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Gate, Request};
use Illuminate\View\View;
use Tests\TestCase;

class ContractTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employee;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permission checks
		Gate::before(fn () => true);

		// make creatorId() return the user's own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = User::factory()->create(['type' => 'Employee']);
	}

	/**
	 ** @test
	 **
	 ** index_denies_non_company_users:
	 **   - ensures Employee users are forbidden from viewing the contract type index
	 **/
	public function index_denies_non_company_users()
	{
		$this->actingAs($this->employee)
			->get(route('contractType.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_shows_list_for_company:
	 **   - seeds two contract types for the company
	 **   - asserts the index view renders with the correct count
	 **/
	public function index_shows_list_for_company()
	{
		ContractType::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);

		$resp = $this->actingAs($this->company)
			->get(route('contractType.index'));

		$resp->assertOk()
			->assertViewIs('contractType.index')
			->assertViewHas('types', function ($types) {
				return $types->count() === 2;
			});
	}

	/**
	 ** @test
	 **
	 ** create_denies_non_company:
	 **   - verifies Employees cannot access the create form
	 **/
	public function create_denies_non_company()
	{
		$this->actingAs($this->employee)
			->get(route('contractType.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form_for_company:
	 **   - ensures company users can view the create contract type form
	 **/
	public function create_displays_form_for_company()
	{
		$this->actingAs($this->company)
			->get(route('contractType.create'))
			->assertOk()
			->assertViewIs('contractType.create');
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates:
	 **   - submits empty payload and expects validation error
	 **   - submits valid name and asserts redirect and database entry
	 **/
	public function store_validates_and_creates()
	{
		$this->actingAs($this->company)
			->post(route('contractType.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		$this->actingAs($this->company)
			->post(route('contractType.store'), ['name' => 'New Type'])
			->assertRedirect(route('contractType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('contract_types', [
			'name'       => 'New Type',
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit_and_update_enforce_ownership_and_validation:
	 **   - another company cannot edit a type they did not create
	 **   - owner sees edit form
	 **   - update fails validation on empty name
	 **   - update succeeds with valid name and persists change
	 **/
	public function edit_and_update_enforce_ownership_and_validation()
	{
		$type = ContractType::factory()->create(['created_by' => $this->company->creatorId()]);

		// another company user cannot edit
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->get(route('contractType.edit', $type))
			->assertStatus(403);

		// owner sees form
		$this->actingAs($this->company)
			->get(route('contractType.edit', $type))
			->assertOk()
			->assertViewIs('contractType.edit')
			->assertViewHas('contractType', $type);

		// update validation
		$this->actingAs($this->company)
			->put(route('contractType.update', $type), ['name' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// successful update
		$this->actingAs($this->company)
			->put(route('contractType.update', $type), ['name' => 'Updated'])
			->assertRedirect(route('contractType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('contract_types', [
			'id'   => $type->id,
			'name' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_prevents_deletion_when_in_use_and_allows_when_free:
	 **   - when a ContractType is referenced by a Contract, deletion is blocked
	 **   - after deleting related Contracts, deletion of the type is allowed
	 **/
	public function destroy_prevents_deletion_when_in_use_and_allows_when_free()
	{
		$type = ContractType::factory()->create(['created_by' => $this->company->creatorId()]);

		// in use by a Contract?
		Contract::factory()->create(['type' => $type->id, 'created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company)
			->delete(route('contractType.destroy', $type))
			->assertRedirect()
			->assertSessionHas('error');

		// detach all contracts
		Contract::query()->delete();

		$this->actingAs($this->company)
			->delete(route('contractType.destroy', $type))
			->assertRedirect(route('contractType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('contract_types', ['id' => $type->id]);
	}


	/**
	 ** @test
	 **
	 ** show() redirects guests to login.
	 **/
	public function show_redirects_guests_to_login()
	{
		$contractType = ContractType::factory()->create();
		$controller  = new ContractTypeController();
		$request     = Request::create("/contract-type/{$contractType->id}", 'GET');
		// ensure no user is authenticated
		auth()->logout();
		$request->setUserResolver(fn () => null);

		$response = $controller->show($request, $contractType);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertStringContainsString(route('login'), $response->headers->get('Location'));
	}

	/**
	 ** @test
	 **
	 ** show() denies users without the permission.
	 **/
	public function show_denies_user_without_permission()
	{
		$user        = User::factory()->create();
		$contractType = ContractType::factory()->create(['created_by' => $user?->creatorId()]);
		$controller  = new ContractTypeController();
		$request     = Request::create("/contract-type/{$contractType->id}", 'GET');
		$this->actingAs($user);
		$request->setUserResolver(fn () => $user);

		$response = $controller->show($request, $contractType);

		$this->assertInstanceOf(RedirectResponse::class, $response);
	}

	/**
	 ** @test
	 **
	 ** show() denies users who do not own the contract type, even if they have permission.
	 **/
	public function show_denies_non_owner_even_with_permission()
	{
		$owner       = User::factory()->create();
		$other       = User::factory()->create();
		$other->givePermissionTo('manage contract type');

		$contractType = ContractType::factory()->create(['created_by' => $owner->creatorId()]);
		$controller  = new ContractTypeController();
		$request     = Request::create("/contract-type/{$contractType->id}", 'GET');
		$this->actingAs($other);
		$request->setUserResolver(fn () => $other);

		$response = $controller->show($request, $contractType);

		$this->assertInstanceOf(RedirectResponse::class, $response);
	}

	/**
	 ** @test
	 **
	 ** show() displays the view for an owner with the correct permission.
	 **/
	public function show_displays_view_for_owner_with_permission()
	{
		$user        = User::factory()->create();
		$user?->givePermissionTo('manage contract type');

		$contractType = ContractType::factory()->create(['created_by' => $user?->creatorId()]);

		$controller = new ContractTypeController();
		$request   = Request::create("/contract-type/{$contractType->id}", 'GET');
		$this->actingAs($user);
		$request->setUserResolver(fn () => $user);

		$response = $controller->show($request, $contractType);

		$this->assertInstanceOf(View::class, $response);
		$this->assertEquals('contractType.show', $response->getName());
		$this->assertSame($contractType->id, $response->getData()['contractType']->id);
	}
}
