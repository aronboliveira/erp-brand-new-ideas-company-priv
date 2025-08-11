<?php

namespace Tests\Feature\Controllers;

use App\Models\{Employee, Resignation, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ResignationControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $companyUser;
	private User $otherUser;

	protected function setUp(): void
	{
		parent::setUp();

		// Bypass login check and permission guard
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
		$this->companyUser = \App\Models\User::factory()->create(['type' => 'company']);
		$this->otherUser  = \App\Models\User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** index() should show all resignations for company users and only own for employees.
	 **/
	public function index_filters_by_user_type()
	{
		// Company user
		$company = User::factory()->create(['type' => 'company']);
		$emp1   = Employee::factory()->create(['created_by' => $company->creatorId()]);
		$emp2   = Employee::factory()->create(['created_by' => $company->creatorId()]);
		Resignation::factory()->create([
			'created_by'     => $company->creatorId(),
			'employee_id'    => $emp1->id,
		]);
		Resignation::factory()->create([
			'created_by'     => $company->creatorId(),
			'employee_id'    => $emp2->id,
		]);

		// Employee user
		$employeeUser = User::factory()->create(['type' => 'Employee']);
		$empRecord   = Employee::factory()->create([
			'created_by' => $company->creatorId(),
			'user_id'    => $employeeUser->id,
		]);
		Resignation::factory()->create([
			'created_by'     => $company->creatorId(),
			'employee_id'    => $empRecord->id,
		]);
		Resignation::factory()->create([
			'created_by'     => $company->creatorId(),
			'employee_id'    => $emp1->id,
		]);

		// Company sees both
		$response = $this->actingAs($company)
			->get(route('resignation.index'));
		$response->assertOk();
		$this->assertCount(2, $response->viewData('resignations'));

		// Employee sees only own
		$response = $this->actingAs($employeeUser)
			->get(route('resignation.index'));
		$response->assertOk();
		$this->assertCount(1, $response->viewData('resignations'));
	}

	/**
	 ** @test
	 **
	 ** create() should list employees based on user type.
	 **/
	public function create_lists_employees_correctly()
	{
		$company = User::factory()->create(['type' => 'company']);
		$emp1   = Employee::factory()->create(['created_by' => $company->creatorId()]);
		$emp2   = Employee::factory()->create(['created_by' => $company->creatorId()]);

		// Company sees all
		$response = $this->actingAs($company)
			->get(route('resignation.create'));
		$response->assertOk()
			->assertViewHas(
				'employees',
				fn ($list) =>
				$list->pluck('id')->sort()->values()->all() === [$emp1->id, $emp2->id]
			);

		// Employee sees only self
		$employeeUser = User::factory()->create(['type' => 'Employee']);
		$empRec      = Employee::factory()->create([
			'created_by' => $company->creatorId(),
			'user_id'    => $employeeUser->id,
		]);
		$response = $this->actingAs($employeeUser)
			->get(route('resignation.create'));
		$response->assertOk()
			->assertViewHas(
				'employees',
				fn ($list) =>
				$list->pluck('id')->all() === [$empRec->id]
			);
	}

	/**
	 ** @test
	 **
	 ** store() should validate input, create a resignation, and redirect.
	 **/
	public function store_creates_resignation()
	{
		$company = User::factory()->create(['type' => 'company']);
		$employee = Employee::factory()->create(['created_by' => $company->creatorId()]);

		$payload = [
			'noticeDate'      => now()->toDateString(),
			'resignationDate' => now()->addDays(7)->toDateString(),
			'description'     => 'Leaving for personal reasons',
			'employeeId'      => $employee->id,
		];

		$response = $this->actingAs($company)
			->post(route('resignation.store'), $payload);

		$response->assertRedirect(route('resignation.index'));
		$this->assertDatabaseHas('resignations', [
			'employee_id'      => $employee->id,
			'description'      => 'Leaving for personal reasons',
			'created_by'       => $company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit() should only allow owner to view and list employees.
	 **/
	public function edit_checks_owner_and_lists_employees()
	{
		$company = User::factory()->create(['type' => 'company']);
		$other  = User::factory()->create(['type' => 'company']);
		$employee = Employee::factory()->create(['created_by' => $company->creatorId()]);
		$resig  = Resignation::factory()->create([
			'created_by'  => $company->creatorId(),
			'employee_id' => $employee->id,
		]);

		// Other user denied
		$response = $this->actingAs($other)
			->get(route('resignation.edit', $resig->id));
		$response->assertRedirect(route('resignation.index'));

		// Owner allowed
		$response = $this->actingAs($company)
			->get(route('resignation.edit', $resig->id));
		$response->assertOk()
			->assertViewHasAll(['resignation', 'employees']);
	}

	/**
	 ** @test
	 **
	 ** update() should validate, apply changes, and redirect.
	 **/
	public function update_modifies_resignation()
	{
		$company = User::factory()->create(['type' => 'company']);
		$employee = Employee::factory()->create(['created_by' => $company->creatorId()]);
		$resig  = Resignation::factory()->create([
			'created_by'  => $company->creatorId(),
			'employee_id' => $employee->id,
			'description' => 'Old desc',
		]);

		$payload = [
			'noticeDate'      => now()->subDays(1)->toDateString(),
			'resignationDate' => now()->toDateString(),
			'description'     => 'Updated desc',
			'employeeId'      => $employee->id,
		];

		$response = $this->actingAs($company)
			->put(route('resignation.update', $resig->id), $payload);

		$response->assertRedirect(route('resignation.index'));
		$this->assertDatabaseHas('resignations', [
			'id'           => $resig->id,
			'description'  => 'Updated desc',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy() should delete the resignation and redirect.
	 **/
	public function destroy_deletes_resignation()
	{
		$company = User::factory()->create(['type' => 'company']);
		$resig  = Resignation::factory()->create([
			'created_by'  => $company->creatorId(),
		]);

		$response = $this->actingAs($company)
			->delete(route('resignation.destroy', $resig->id));

		$response->assertRedirect(route('resignation.index'));
		$this->assertDatabaseMissing('resignations', ['id' => $resig->id]);
	}


	/** @test
	 *
	 * Show displays view when resignation belongs to user and permission granted
	 **/
	public function show_displays_view_for_owner()
	{
		$this->actingAs($this->companyUser);

		$resig = Resignation::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$response = $this->get(route('resignation.show', $resig));

		$response->assertStatus(200)
			->assertViewIs('resignation.show')
			->assertViewHas('resignation', fn ($r) => $r->id === $resig->id);
	}

	/** @test
	 *
	 * Show denies access with 403 when permission missing
	 **/
	public function show_denies_if_no_permission()
	{
		// revoke the 'view resignation' permission
		Gate::before(fn ($user, $perm) => $perm === 'view resignation' ? false : null);

		$this->actingAs($this->companyUser);

		$resig = Resignation::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$response = $this->get(route('resignation.show', $resig));

		$response->assertStatus(403);
	}

	/** @test
	 *
	 * Show denies access when resignation does not belong to user
	 **/
	public function show_forbids_if_not_owner()
	{
		$this->actingAs($this->otherUser);

		$resig = Resignation::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$response = $this->get(route('resignation.show', $resig));

		$response->assertStatus(302)
			->assertRedirect(route('resignation.index'))
			->assertSessionHas('error');
	}

	/** @test
	 *
	 * Show handles exceptions gracefully
	 **/
	public function show_handles_exceptions()
	{
		$this->actingAs($this->companyUser);

		// simulate a missing model by passing invalid ID
		$response = $this->get(route('resignation.show', 999));

		// should be caught by defaultUndefinedException, redirect to index
		$response->assertRedirect(route('resignation.index'))
			->assertSessionHas('error');
	}
}
