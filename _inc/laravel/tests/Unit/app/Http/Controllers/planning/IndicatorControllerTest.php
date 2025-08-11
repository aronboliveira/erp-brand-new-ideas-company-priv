<?php

namespace Tests\Feature;

use App\Models\{Branch, Department, Employee, Indicator, PerformanceType, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class IndicatorControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employee;
	private Branch $branch;
	private Department $department;
	private PerformanceType $perfType;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permission checks by default
		Gate::before(fn () => true);

		// let creatorId() return own id
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

		$this->company   = User::factory()->create(['type' => 'company']);
		$this->employee  = User::factory()->create(['type' => 'Employee']);
		$this->branch    = Branch::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->department = Department::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->perfType  = PerformanceType::factory()->create(['created_by' => $this->company->creatorId()]);

		Employee::factory()->create([
			'user_id'    => $this->employee->id,
			'branch_id'  => $this->branch->id,
			'department_id' => $this->department->id,
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** index_requires_manage_permission:
	 **   - denies access when user lacks 'manage indicator'
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('indicator.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_shows_all_for_company:
	 **   - company sees all created indicators
	 **/
	public function index_shows_all_for_company()
	{
		Indicator::factory()->count(2)->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'     => json_encode(['A', 'B']),
		]);

		$resp = $this->actingAs($this->company)
			->get(route('indicator.index'));

		$resp->assertOk()
			->assertViewIs('indicator.index')
			->assertViewHas('indicators', fn ($c) => $c->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** index_filters_for_employee:
	 **   - employee only sees indicators matching their branch & department
	 **/
	public function index_filters_for_employee()
	{
		// one matching, one not
		Indicator::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'     => json_encode([]),
		]);
		Indicator::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => Branch::factory()->create(['created_by' => $this->company->creatorId()])->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'     => json_encode([]),
		]);

		$resp = $this->actingAs($this->employee)
			->get(route('indicator.index'));

		$resp->assertOk()
			->assertViewHas('indicators', fn ($c) => $c->count() === 1);
	}

	/**
	 ** @test
	 **
	 ** create_requires_permission:
	 **   - denies when lacking 'create indicator'
	 **/
	public function create_requires_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('indicator.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form:
	 **   - shows branches, departments, and performance types
	 **/
	public function create_displays_form()
	{
		$resp = $this->actingAs($this->company)
			->get(route('indicator.create'));

		$resp->assertOk()
			->assertViewIs('indicator.create')
			->assertViewHasAll(['branches', 'departments', 'performance']);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates:
	 **   - fails when required fields missing
	 **   - succeeds and persists JSON-encoded rating
	 **/
	public function store_validates_and_creates()
	{
		// validation fail
		$this->actingAs($this->company)
			->post(route('indicator.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		$payload = [
			'branch'      => $this->branch->id,
			'department'  => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'      => ['X', 'Y'],
		];
		$this->actingAs($this->company)
			->post(route('indicator.store'), $payload)
			->assertRedirect(route('indicator.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('indicators', [
			'branch'     => $this->branch->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** show_displays_indicator:
	 **   - view with decoded ratings and performance list
	 **/
	public function show_displays_indicator()
	{
		$ind = Indicator::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'     => json_encode(['A']),
		]);

		$resp = $this->actingAs($this->company)
			->get(route('indicator.show', $ind));

		$resp->assertOk()
			->assertViewIs('indicator.show')
			->assertViewHas('ratings', ['A']);
	}

	/**
	 ** @test
	 **
	 ** edit_requires_permission_and_displays:
	 **   - denies without 'edit indicator'
	 **   - then displays edit form with existing data
	 **/
	public function edit_requires_permission_and_displays()
	{
		$ind = Indicator::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'     => json_encode([]),
		]);

		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('indicator.edit', $ind))
			->assertStatus(403);

		Gate::before(fn () => true);
		$resp = $this->actingAs($this->company)
			->get(route('indicator.edit', $ind));

		$resp->assertOk()
			->assertViewIs('indicator.edit')
			->assertViewHasAll(['branches', 'departments', 'performance', 'indicator', 'ratings']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves:
	 **   - fails when missing required fields
	 **   - updates JSON-encoded rating
	 **/
	public function update_validates_and_saves()
	{
		$ind = Indicator::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'     => json_encode([]),
		]);

		// validation fail
		$this->actingAs($this->company)
			->put(route('indicator.update', $ind), ['branch' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		$data = [
			'branch'      => $this->branch->id,
			'department'  => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'      => ['Z'],
		];
		$this->actingAs($this->company)
			->put(route('indicator.update', $ind), $data)
			->assertRedirect(route('indicator.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('indicators', [
			'id'     => $ind->id,
			// ensure rating was saved as JSON
			'rating' => json_encode(['Z']),
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_requires_permission_and_owner:
	 **   - denies without 'delete indicator' or wrong owner
	 **   - deletes when permitted owner
	 **/
	public function destroy_requires_permission_and_owner()
	{
		$ind = Indicator::factory()->create([
			'created_by' => $this->company->creatorId(),
			'branch'     => $this->branch->id,
			'department' => $this->department->id,
			'designation' => $this->perfType->id,
			'rating'     => json_encode([]),
		]);

		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('indicator.destroy', $ind))
			->assertStatus(403);

		Gate::before(fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other)
			->delete(route('indicator.destroy', $ind))
			->assertStatus(403);

		$this->actingAs($this->company)
			->delete(route('indicator.destroy', $ind))
			->assertRedirect(route('indicator.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('indicators', ['id' => $ind->id]);
	}
}
