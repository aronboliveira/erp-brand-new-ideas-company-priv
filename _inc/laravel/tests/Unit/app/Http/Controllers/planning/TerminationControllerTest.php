<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Employee, Termination, TerminationType, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class TerminationControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private Termination $termination;

	protected function setUp(): void
	{
		parent::setUp();

		// Allow all permissions by default
		Gate::before(fn () => true);

		// Macro so creatorId() returns the user's own ID
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
		// Create and authenticate a company user
		$this->company = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);

		// Create a Termination record owned by this user
		$this->termination = Termination::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Index should list all terminations for non-employee users.
	 **/
	public function test_index_lists_all_for_non_employee_users()
	{
		$user = User::factory()->create(['type' => 'company']);
		Permission::create(['name' => 'manage termination']);
		$user?->givePermissionTo('manage termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);

		Termination::factory()->count(2)->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId,
		]);

		Termination::factory()->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId + 1,
		]);

		$response = $this->actingAs($user)->get(route('termination.index'));

		$response->assertStatus(200)
			->assertViewIs('termination.index')
			->assertViewHas('terminations', function ($terms) {
				return $terms->count() === 2;
			});
	}

	/**
	 ** @test
	 **
	 ** Index should filter terminations for users of type Employee.
	 **/
	public function test_index_filters_for_employee_users()
	{
		$user = User::factory()->create(['type' => 'Employee']);
		Permission::create(['name' => 'manage termination']);
		$user?->givePermissionTo('manage termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);

		Termination::factory()->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId,
		]);
		Termination::factory()->create([
			'employee_id'      => $emp->id + 1,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId,
		]);

		$response = $this->actingAs($user)->get(route('termination.index'));

		$response->assertStatus(200)
			->assertViewHas('terminations', function ($terms) {
				return $terms->count() === 1;
			});
	}

	/**
	 ** @test
	 **
	 ** Create should show form for users with create permission.
	 **/
	public function test_create_displays_form_for_authorized_users()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create termination']);
		$user?->givePermissionTo('create termination');

		$response = $this->actingAs($user)->get(route('termination.create'));

		$response->assertStatus(200)
			->assertViewIs('termination.create')
			->assertViewHasAll(['employees', 'terminationTypes']);
	}

	/**
	 ** @test
	 **
	 ** Store should create a new termination and redirect.
	 **/
	public function test_store_creates_termination_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create termination']);
		$user?->givePermissionTo('create termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);

		$data = [
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'description'      => 'Reason for termination',
		];

		$response = $this->actingAs($user)->post(route('termination.store'), $data);

		$response->assertRedirect(route('termination.index'))
			->assertSessionHas('success', __('Termination successfully created.'));
		$this->assertDatabaseHas('terminations', [
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'created_by'       => $creatorId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Store should fail validation with missing fields.
	 **/
	public function test_store_fails_validation_with_missing_fields()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create termination']);
		$user?->givePermissionTo('create termination');

		$response = $this->actingAs($user)->post(route('termination.store'), [
			'employee_id'      => '',
			'termination_type' => '',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseCount('terminations', 0);
	}

	/**
	 ** @test
	 **
	 ** Edit should show form for owner with permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit termination']);
		$user?->givePermissionTo('edit termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);
		$term = Termination::factory()->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId,
		]);

		$response = $this->actingAs($user)->get(route('termination.edit', $term));

		$response->assertStatus(200)
			->assertViewIs('termination.edit')
			->assertViewHasAll(['termination', 'employees', 'terminationTypes']);
	}

	/**
	 ** @test
	 **
	 ** Update should modify the termination and redirect.
	 **/
	public function test_update_modifies_termination_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit termination']);
		$user?->givePermissionTo('edit termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);
		$term = Termination::factory()->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'description'      => 'Old reason',
			'created_by'       => $creatorId,
		]);

		$newDate = Carbon::tomorrow()->toDateString();
		$response = $this->actingAs($user)->put(route('termination.update', $term), [
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => $newDate,
			'termination_date' => $newDate,
			'description'      => 'Updated reason',
		]);

		$response->assertRedirect(route('termination.index'))
			->assertSessionHas('success', __('Termination successfully updated.'));
		$this->assertDatabaseHas('terminations', [
			'id'                => $term->id,
			'notice_date'       => $newDate,
			'termination_date'  => $newDate,
			'description'       => 'Updated reason',
		]);
	}

	/**
	 ** @test
	 **
	 ** Update should fail validation with invalid data.
	 **/
	public function test_update_fails_validation_with_invalid_data()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit termination']);
		$user?->givePermissionTo('edit termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);
		$term = Termination::factory()->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId,
		]);

		$response = $this->actingAs($user)->put(route('termination.update', $term), [
			'employee_id'      => '',
			'termination_type' => '',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseHas('terminations', ['id' => $term->id]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete the termination and redirect.
	 **/
	public function test_destroy_deletes_termination_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete termination']);
		$user?->givePermissionTo('delete termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);
		$term = Termination::factory()->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId,
		]);

		$response = $this->actingAs($user)->delete(route('termination.destroy', $term));

		$response->assertRedirect(route('termination.index'))
			->assertSessionHas('success', __('Termination successfully deleted.'));
		$this->assertModelMissing($term);
	}

	/**
	 ** @test
	 **
	 ** Description should display the termination detail view.
	 **/
	public function test_description_displays_detail_view()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage termination']);
		$user?->givePermissionTo('manage termination');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$tType = TerminationType::factory()->create(['created_by' => $creatorId]);
		$term = Termination::factory()->create([
			'employee_id'      => $emp->id,
			'termination_type' => $tType->id,
			'notice_date'      => Carbon::today()->toDateString(),
			'termination_date' => Carbon::today()->toDateString(),
			'created_by'       => $creatorId,
		]);

		$response = $this->actingAs($user)->get(route('termination.description', $term->id));

		$response->assertStatus(200)
			->assertViewIs('termination.description')
			->assertViewHas('termination', $term);
	}

	/**
	 ** @test
	 **
	 ** Owner can view the termination details page.
	 **/
	public function owner_can_view_termination()
	{
		$response = $this->get(route('termination.show', $this->termination));

		$response->assertOk()
			->assertViewIs('termination.show')
			->assertViewHas('termination', fn ($t) => $t->id === $this->termination->id);
	}

	/**
	 ** @test
	 **
	 ** A non-owner company user receives 403 Forbidden.
	 **/
	public function non_owner_gets_forbidden()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('termination.show', $this->termination));
		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when attempting to view.
	 **/
	public function guest_is_redirected_to_login()
	{
		auth()->logout();

		$response = $this->get(route('termination.show', $this->termination));
		$response->assertRedirect(); // expects login redirect
	}

	/**
	 ** @test
	 **
	 ** If Gate denies the permission, the user is redirected back to index.
	 **/
	public function permission_denied_redirects_to_index()
	{
		Gate::before(fn () => false);

		$response = $this->actingAs($this->company)
			->get(route('termination.show', $this->termination));

		$response->assertRedirect(route('termination.index'));
	}
}
