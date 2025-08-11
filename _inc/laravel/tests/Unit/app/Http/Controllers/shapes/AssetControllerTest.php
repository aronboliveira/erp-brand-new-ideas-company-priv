<?php

namespace Tests\Feature;

use App\Models\{Asset, Employee, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AssetControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private Employee $employee;

	protected function setUp(): void
	{
		parent::setUp();

		// allow creatorId() to return own ID
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

		// default: allow all permissions
		Gate::before(fn () => true);

		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = Employee::factory()->create(['created_by' => $this->company->creatorId()]);
	}

	/**
	 ** @test
	 **
	 ** index_denies_without_permission
	 **
	 ** Users lacking 'manage assets' get 403.
	 **/
	public function index_denies_without_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('account-assets.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_lists_assets_for_creator
	 **
	 ** Company sees only their own assets.
	 **/
	public function index_lists_assets_for_creator()
	{
		Asset::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		Asset::factory()->create(['created_by' => $this->employee->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('account-assets.index'));

		$response->assertOk()
			->assertViewIs('assets.index')
			->assertViewHas('assets', fn ($list) => $list->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** create_denies_without_permission
	 **
	 ** Users lacking 'create assets' cannot access form.
	 **/
	public function create_denies_without_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('account-assets.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form
	 **
	 ** Authorized users see create form with employee list.
	 **/
	public function create_displays_form()
	{
		Employee::factory()->count(3)->create(['created_by' => $this->company->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('account-assets.create'));

		$response->assertOk()
			->assertViewIs('assets.create')
			->assertViewHas('employeeList', fn ($list) => $list->count() === 3);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates_asset
	 **
	 ** Empty payload errors; valid input persists asset.
	 **/
	public function store_validates_and_creates_asset()
	{
		// validation failure
		$this->actingAs($this->company)
			->post(route('account-assets.store'), [])
			->assertSessionHasErrors();

		// success
		$payload = [
			'name'           => 'Laptop',
			'purchase_date'  => now()->toDateString(),
			'supported_date' => now()->addYear()->toDateString(),
			'amount'         => 1500,
			'employee_id'    => [$this->employee->id],
			'description'    => 'Work laptop'
		];

		$this->actingAs($this->company)
			->post(route('account-assets.store'), $payload)
			->assertRedirect(route('account-assets.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('assets', [
			'name'       => 'Laptop',
			'amount'     => 1500,
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show_denies_and_displays
	 **
	 ** Unauthorized or non-owner sees 403; owner sees asset.
	 **/
	public function show_denies_and_displays()
	{
		$asset = Asset::factory()->create(['created_by' => $this->company->creatorId()]);

		// wrong user
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->get(route('account-assets.show', $asset))
			->assertStatus(403);

		// owner
		$this->actingAs($this->company)
			->get(route('account-assets.show', $asset))
			->assertOk()
			->assertViewIs('assets.show')
			->assertViewHas('asset', fn ($a) => $a->id === $asset->id);
	}

	/**
	 ** @test
	 **
	 ** edit_denies_and_displays
	 **
	 ** Missing 'edit assets' or non-owner get 403; owner sees edit form.
	 **/
	public function edit_denies_and_displays()
	{
		$asset = Asset::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('account-assets.edit', $asset->id))
			->assertStatus(403);

		// restore, wrong owner
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->get(route('account-assets.edit', $asset->id))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->get(route('account-assets.edit', $asset->id))
			->assertOk()
			->assertViewIs('assets.edit')
			->assertViewHasAll(['asset', 'employeeList']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves
	 **
	 ** Invalid data fails; valid updates asset.
	 **/
	public function update_validates_and_saves()
	{
		$asset = Asset::factory()->create([
			'created_by'  => $this->company->creatorId(),
			'name'        => 'Old',
			'employee_id' => (string)$this->employee->id,
		]);

		// validation fail
		$this->actingAs($this->company)
			->put(route('account-assets.update', $asset->id), ['name' => ''])
			->assertSessionHasErrors();

		// success
		$payload = [
			'name'           => 'New Name',
			'purchase_date'  => now()->toDateString(),
			'supported_date' => now()->addYear()->toDateString(),
			'amount'         => 2000,
			'employee_id'    => [$this->employee->id],
			'description'    => 'Updated',
		];

		$this->actingAs($this->company)
			->put(route('account-assets.update', $asset->id), $payload)
			->assertRedirect(route('account-assets.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('assets', [
			'id'          => $asset->id,
			'name'        => 'New Name',
			'amount'      => 2000,
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_denies_and_deletes
	 **
	 ** Without 'delete assets' or non-owner get 403; owner deletes asset.
	 **/
	public function destroy_denies_and_deletes()
	{
		$asset = Asset::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('account-assets.destroy', $asset->id))
			->assertStatus(403);

		// restore, wrong owner
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->delete(route('account-assets.destroy', $asset->id))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->delete(route('account-assets.destroy', $asset->id))
			->assertRedirect(route('account-assets.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('assets', ['id' => $asset->id]);
	}
}
