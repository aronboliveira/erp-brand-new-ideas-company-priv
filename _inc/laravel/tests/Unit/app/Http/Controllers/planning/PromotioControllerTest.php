<?php

namespace Tests\Feature;

use App\Models\{Designation, Employee, Promotion, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PromotionControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employee;

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

		// default: allow all permission checks
		Gate::before(fn () => true);

		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = User::factory()->create(['type' => 'Employee']);
	}

	/**
	 ** @test
	 **
	 ** index_denies_without_permission
	 **
	 ** Users without 'manage promotion' permission receive 403.
	 **/
	public function index_denies_without_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('promotion.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_lists_promotions_for_company
	 **
	 ** Company users see only their own promotions.
	 **/
	public function index_lists_promotions_for_company()
	{
		Promotion::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		Promotion::factory()->create(['created_by' => $this->employee->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('promotion.index'));

		$response->assertOk()
			->assertViewIs('promotion.index')
			->assertViewHas('promotions', fn ($list) => $list->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** create_denies_without_permission
	 **
	 ** Users without 'create promotion' cannot access form.
	 **/
	public function create_denies_without_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('promotion.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form
	 **
	 ** Authorized users see create form with designations and employees.
	 **/
	public function create_displays_form()
	{
		Designation::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		Employee::factory()->count(3)->create(['created_by' => $this->company->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('promotion.create'));

		$response->assertOk()
			->assertViewIs('promotion.create')
			->assertViewHasAll(['designations', 'employees']);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates
	 **
	 ** Empty payload redirects with error; valid input creates promotion.
	 **/
	public function store_validates_and_creates()
	{
		// validation failure
		$this->actingAs($this->company)
			->post(route('promotion.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		$desig = Designation::factory()->create(['created_by' => $this->company->creatorId()]);
		$emp  = Employee::factory()->create(['created_by' => $this->company->creatorId()]);

		$payload = [
			'employee_id'     => $emp->id,
			'designation_id'  => $desig->id,
			'promotion_title' => 'Title',
			'promotion_date'  => now()->toDateString(),
			'description'     => 'Desc',
		];

		$this->actingAs($this->company)
			->post(route('promotion.store'), $payload)
			->assertRedirect(route('promotion.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('promotions', [
			'employee_id'    => $emp->id,
			'designation_id' => $desig->id,
			'promotion_title' => 'Title',
			'created_by'     => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show_redirects_to_index
	 **
	 ** The show endpoint always redirects back to index.
	 **/
	public function show_redirects_to_index()
	{
		$promo = Promotion::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company)
			->get(route('promotion.show', $promo))
			->assertRedirect(route('promotion.index'));
	}

	/**
	 ** @test
	 **
	 ** edit_denies_without_permission_and_non_owner
	 **
	 ** Without 'edit promotion' or as non-owner returns unauthorized.
	 **/
	public function edit_denies_without_permission_and_non_owner()
	{
		$promo = Promotion::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('promotion.edit', $promo))
			->assertStatus(403);

		// restore permission, wrong owner => JSON 401
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->get(route('promotion.edit', $promo))
			->assertStatus(401);
	}

	/**
	 ** @test
	 **
	 ** edit_displays_form_for_owner
	 **
	 ** Owner with permission sees edit form.
	 **/
	public function edit_displays_form_for_owner()
	{
		$desig = Designation::factory()->create(['created_by' => $this->company->creatorId()]);
		$emp  = Employee::factory()->create(['created_by' => $this->company->creatorId()]);
		$promo = Promotion::factory()->create([
			'employee_id'    => $emp->id,
			'designation_id' => $desig->id,
			'created_by'     => $this->company->creatorId(),
		]);

		$response = $this->actingAs($this->company)
			->get(route('promotion.edit', $promo));

		$response->assertOk()
			->assertViewIs('promotion.edit')
			->assertViewHasAll(['promotion', 'designations', 'employees']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves
	 **
	 ** Empty update fails; valid update persists changes.
	 **/
	public function update_validates_and_saves()
	{
		$desig1 = Designation::factory()->create(['created_by' => $this->company->creatorId()]);
		$desig2 = Designation::factory()->create(['created_by' => $this->company->creatorId()]);
		$emp   = Employee::factory()->create(['created_by' => $this->company->creatorId()]);
		$promo = Promotion::factory()->create([
			'employee_id'    => $emp->id,
			'designation_id' => $desig1->id,
			'promotion_title' => 'Old',
			'promotion_date' => now()->toDateString(),
			'created_by'     => $this->company->creatorId(),
		]);

		// validation fail
		$this->actingAs($this->company)
			->put(route('promotion.update', $promo), [])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		$payload = [
			'employee_id'     => $emp->id,
			'designation_id'  => $desig2->id,
			'promotion_title' => 'New Title',
			'promotion_date'  => now()->addDay()->toDateString(),
			'description'     => 'Updated',
		];

		$this->actingAs($this->company)
			->put(route('promotion.update', $promo), $payload)
			->assertRedirect(route('promotion.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('promotions', [
			'id'              => $promo->id,
			'designation_id'  => $desig2->id,
			'promotion_title' => 'New Title',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_denies_without_permission_and_non_owner
	 **
	 ** Without 'delete promotion' or as non-owner cannot delete.
	 **/
	public function destroy_denies_without_permission_and_non_owner()
	{
		$promo = Promotion::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('promotion.destroy', $promo))
			->assertStatus(403);

		// restore permission, wrong owner => redirect index + error
		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->delete(route('promotion.destroy', $promo))
			->assertRedirect(route('promotion.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** destroy_deletes_for_owner
	 **
	 ** Owner with permission can delete promotion.
	 **/
	public function destroy_deletes_for_owner()
	{
		$promo = Promotion::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company)
			->delete(route('promotion.destroy', $promo))
			->assertRedirect(route('promotion.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
	}
}
