<?php

namespace Tests\Feature;

use App\Models\{User, Employee, DeductionOption, SaturationDeduction};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SaturationDeductionControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private User $companyUser;
	private User $otherUser;

	protected function setUp(): void
	{
		parent::setUp();

		// authenticate and bypass permission checks
		$this->user = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->user);
		Gate::before(fn () => true);

		// ensure creatorId() returns the user’s own id
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
		$this->companyUser = \App\Models\User::factory()->create(['type' => 'company']);
		$this->otherUser  = \App\Models\User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** create should display the form with employee, options, and types
	 **/
	public function test_create_displays_form(): void
	{
		$employee = Employee::factory()->create(['created_by' => $this->user->creatorId()]);
		DeductionOption::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('saturation-deduction.create', $employee->id));

		$response->assertOk()
			->assertViewIs('saturationdeduction.create')
			->assertViewHasAll(['employee', 'options', 'types']);
	}

	/**
	 ** @test
	 **
	 ** store should persist a valid saturation deduction and redirect
	 **/
	public function test_store_creates_and_redirects(): void
	{
		$employee = Employee::factory()->create(['created_by' => $this->user->creatorId()]);
		$option  = DeductionOption::factory()->create(['created_by' => $this->user->creatorId()]);

		$payload = [
			'employee_id'      => $employee->id,
			'deduction_option' => $option->id,
			'title'            => 'Test Deduction',
			'type'             => array_key_first(SaturationDeduction::$saturationDeductionType),
			'amount'           => 123.45,
		];

		$response = $this->post(route('saturation-deduction.store'), $payload);

		$response->assertRedirect(route('saturation-deduction.index'));
		$this->assertDatabaseHas('saturation_deductions', [
			'employee_id'      => $employee->id,
			'deduction_option' => $option->id,
			'title'            => 'Test Deduction',
			'amount'           => 123.45,
			'created_by'       => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** store should fail validation when required fields are missing
	 **/
	public function test_store_validation_fails(): void
	{
		$response = $this->post(route('saturation-deduction.store'), []);

		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** edit should display the form for an existing deduction
	 **/
	public function test_edit_displays_form(): void
	{
		$deduction = SaturationDeduction::factory()->create(['created_by' => $this->user->creatorId()]);
		DeductionOption::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('saturation-deduction.edit', $deduction->id));

		$response->assertOk()
			->assertViewIs('saturationdeduction.edit')
			->assertViewHasAll(['deduction', 'options', 'types']);
	}

	/**
	 ** @test
	 **
	 ** edit should deny access when not the owner
	 **/
	public function test_edit_permission_denied_for_non_owner(): void
	{
		$otherUser = User::factory()->create();
		$deduction = SaturationDeduction::factory()->create(['created_by' => $otherUser->creatorId()]);

		$response = $this->get(route('saturation-deduction.edit', $deduction->id));

		$response->assertRedirect(route('saturation-deduction.index'));
	}

	/**
	 ** @test
	 **
	 ** update should apply valid changes and redirect
	 **/
	public function test_update_changes_record(): void
	{
		$employee = Employee::factory()->create(['created_by' => $this->user->creatorId()]);
		$option  = DeductionOption::factory()->create(['created_by' => $this->user->creatorId()]);

		$deduction = SaturationDeduction::factory()->create([
			'created_by'       => $this->user->creatorId(),
			'deduction_option' => $option->id,
			'title'            => 'Old Title',
			'type'             => array_key_first(SaturationDeduction::$saturationDeductionType),
			'amount'           => 50,
		]);

		$payload = [
			'deduction_option' => $option->id,
			'title'            => 'New Title',
			'type'             => array_key_first(SaturationDeduction::$saturationDeductionType),
			'amount'           => 75,
		];

		$response = $this->put(route('saturation-deduction.update', $deduction->id), $payload);

		$response->assertRedirect(route('saturation-deduction.index'));
		$this->assertDatabaseHas('saturation_deductions', [
			'id'                => $deduction->id,
			'title'             => 'New Title',
			'amount'            => 75,
		]);
	}

	/**
	 ** @test
	 **
	 ** update should fail validation on incorrect data
	 **/
	public function test_update_validation_fails(): void
	{
		$deduction = SaturationDeduction::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->put(route('saturation-deduction.update', $deduction->id), [
			'title'  => '',
			'amount' => 'not-numeric',
		]);

		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should deny access when not the owner
	 **/
	public function test_update_permission_denied_for_non_owner(): void
	{
		$otherUser = User::factory()->create();
		$deduction = SaturationDeduction::factory()->create(['created_by' => $otherUser->creatorId()]);

		$response = $this->put(route('saturation-deduction.update', $deduction->id), [
			'deduction_option' => '',
			'title'            => 'Whatever',
			'amount'           => 10,
		]);

		$response->assertRedirect(route('saturation-deduction.index'));
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the deduction and redirect
	 **/
	public function test_destroy_deletes_record(): void
	{
		$deduction = SaturationDeduction::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->delete(route('saturation-deduction.destroy', $deduction->id));

		$response->assertRedirect(route('saturation-deduction.index'));
		$this->assertDatabaseMissing('saturation_deductions', ['id' => $deduction->id]);
	}

	/**
	 ** @test
	 **
	 ** destroy should deny access when not the owner
	 **/
	public function test_destroy_permission_denied_for_non_owner(): void
	{
		$otherUser = User::factory()->create();
		$deduction = SaturationDeduction::factory()->create(['created_by' => $otherUser->creatorId()]);

		$response = $this->delete(route('saturation-deduction.destroy', $deduction->id));

		$response->assertRedirect(route('saturation-deduction.index'));
	}

	/** @test
	 *
	 * Show displays view when deduction belongs to user and permission granted
	 **/
	public function show_displays_view_for_owner()
	{
		$this->actingAs($this->companyUser);

		$deduction = SaturationDeduction::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$response = $this->get(route('saturationdeduction.show', $deduction));

		$response->assertStatus(200)
			->assertViewIs('saturationdeduction.show')
			->assertViewHas('deduction', fn ($d) => $d->id === $deduction->id);
	}

	/** @test
	 *
	 * Show denies access with 403 when permission missing
	 **/
	public function show_denies_if_no_permission()
	{
		Gate::before(fn ($user, $perm) => $perm === 'view saturation deduction' ? false : null);

		$this->actingAs($this->companyUser);

		$deduction = SaturationDeduction::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$response = $this->get(route('saturationdeduction.show', $deduction));

		$response->assertStatus(403);
	}

	/** @test
	 *
	 * Show forbids access when deduction does not belong to user
	 **/
	public function show_forbids_if_not_owner()
	{
		$this->actingAs($this->otherUser);

		$deduction = SaturationDeduction::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$response = $this->get(route('saturationdeduction.show', $deduction));

		$response->assertRedirect(route('saturationdeduction.index'))
			->assertSessionHas('error');
	}

	/** @test
	 *
	 * Show handles missing model by redirecting with error
	 **/
	public function show_handles_missing_model()
	{
		$this->actingAs($this->companyUser);

		$response = $this->get(route('saturationdeduction.show', 999));

		$response->assertRedirect(route('saturationdeduction.index'))
			->assertSessionHas('error');
	}
}
