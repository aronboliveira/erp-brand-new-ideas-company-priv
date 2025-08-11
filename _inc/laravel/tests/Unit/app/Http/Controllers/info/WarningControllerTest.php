<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Employee, User, Warning};
use Spatie\Permission\Models\Permission;

class WarningControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private Warning $warning;

	protected function setUp(): void
	{
		parent::setUp();

		// Make creatorId() return the user's own id
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

		// Create and authenticate a user
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		// Create the “view warning” permission
		Permission::create(['name' => 'view warning']);

		// Create a warning owned by this user
		$this->warning = Warning::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** index should display warnings for users with 'manage warning' permission
	 **/
	public function index_displays_warnings_for_authorized_user()
	{
		Permission::create(['name' => 'manage warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage warning');

		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp A',
			'created_by' => $user?->creatorId(),
		]);

		Warning::create([
			'warning_by'   => $emp->id,
			'warning_to'   => $emp->id,
			'subject'      => 'Subject 1',
			'warning_date' => '2025-01-01',
			'description'  => 'Desc1',
			'created_by'   => $user?->creatorId(),
		]);
		Warning::create([
			'warning_by'   => $emp->id,
			'warning_to'   => $emp->id,
			'subject'      => 'Subject 2',
			'warning_date' => '2025-02-01',
			'description'  => 'Desc2',
			'created_by'   => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('warning.index'));

		$response->assertStatus(200);
		$response->assertViewIs('warning.index');
		$response->assertViewHas('warnings', function ($warnings) use ($user) {
			return $warnings->count() === 2
				&& $warnings->every(fn ($w) => $w->created_by === $user?->creatorId());
		});
	}

	/**
	 ** @test
	 **
	 ** index should filter warnings for employee users
	 **/
	public function index_filters_warnings_for_employee_user()
	{
		Permission::create(['name' => 'manage warning']);
		$user = User::factory()->create(['type' => 'Employee']);
		$user?->givePermissionTo('manage warning');

		$emp = Employee::create([
			'user_id'    => $user?->id,
			'name'       => 'Linked Emp',
			'created_by' => $user?->creatorId(),
		]);
		$otherEmp = Employee::create([
			'user_id'    => null,
			'name'       => 'Other Emp',
			'created_by' => $user?->creatorId(),
		]);

		$w1 = Warning::create([
			'warning_by'   => $emp->id,
			'warning_to'   => $otherEmp->id,
			'subject'      => 'Emp Subject',
			'warning_date' => '2025-03-01',
			'description'  => 'Desc',
			'created_by'   => $user?->creatorId(),
		]);
		Warning::create([
			'warning_by'   => $otherEmp->id,
			'warning_to'   => $emp->id,
			'subject'      => 'Other Subject',
			'warning_date' => '2025-04-01',
			'description'  => 'Desc',
			'created_by'   => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('warning.index'));

		$response->assertStatus(200);
		$response->assertViewHas('warnings', function ($warnings) use ($w1) {
			return $warnings->count() === 1
				&& $warnings->first()->id === $w1->id;
		});
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('warning.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create should display form for users with 'create warning' permission
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create warning');

		$current = Employee::create([
			'user_id'    => $user?->id,
			'name'       => 'Current Emp',
			'created_by' => $user?->creatorId(),
		]);
		Employee::create([
			'user_id'    => null,
			'name'       => 'Other Emp',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('warning.create'));

		$response->assertStatus(200);
		$response->assertViewIs('warning.create');
		$response->assertViewHasAll(['employees', 'currentEmployee']);
	}

	/**
	 ** @test
	 **
	 ** store should redirect back with error on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create warning');

		$response = $this->actingAs($user)
			->from(route('warning.create'))
			->post(route('warning.store'), []);

		$response->assertRedirect(route('warning.create'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store should create warning and redirect on success
	 **/
	public function store_creates_warning_and_redirects_on_success()
	{
		Permission::create(['name' => 'create warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create warning');

		$by = Employee::create([
			'user_id'    => null,
			'name'       => 'By Emp',
			'created_by' => $user?->creatorId(),
		]);
		$to = Employee::create([
			'user_id'    => null,
			'name'       => 'To Emp',
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'warning_by'   => $by->id,
			'warning_to'   => $to->id,
			'subject'      => 'Test Warning',
			'warning_date' => '2025-05-01',
			'description'  => 'Details',
		];

		$response = $this->actingAs($user)
			->post(route('warning.store'), $payload);

		$response->assertRedirect(route('warning.index'));
		$this->assertDatabaseHas('warnings', [
			'warning_by'   => $by->id,
			'warning_to'   => $to->id,
			'subject'      => 'Test Warning',
			'description'  => 'Details',
			'created_by'   => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit should display form for users with 'edit warning' permission
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'edit warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit warning');

		$current = Employee::create([
			'user_id'    => $user?->id,
			'name'       => 'Current Emp',
			'created_by' => $user?->creatorId(),
		]);
		$other = Employee::create([
			'user_id'    => null,
			'name'       => 'Other Emp',
			'created_by' => $user?->creatorId(),
		]);

		$warning = Warning::create([
			'warning_by'   => $current->id,
			'warning_to'   => $other->id,
			'subject'      => 'Edit Subj',
			'warning_date' => '2025-06-01',
			'description'  => '',
			'created_by'   => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('warning.edit', $warning));

		$response->assertStatus(200);
		$response->assertViewIs('warning.edit');
		$response->assertViewHasAll(['warning', 'employees', 'currentEmployee']);
	}

	/**
	 ** @test
	 **
	 ** update should redirect back with error on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'edit warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit warning');

		$current = Employee::create([
			'user_id'    => $user?->id,
			'name'       => 'Current Emp',
			'created_by' => $user?->creatorId(),
		]);
		$other = Employee::create([
			'user_id'    => null,
			'name'       => 'Other Emp',
			'created_by' => $user?->creatorId(),
		]);
		$warning = Warning::create([
			'warning_by'   => $current->id,
			'warning_to'   => $other->id,
			'subject'      => 'Subj',
			'warning_date' => '2025-07-01',
			'description'  => '',
			'created_by'   => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->from(route('warning.edit', $warning))
			->put(route('warning.update', $warning), [
				'warning_by'   => '',
				'warning_to'   => '',
				'subject'      => '',
				'warning_date' => '',
			]);

		$response->assertRedirect(route('warning.edit', $warning));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should modify warning and redirect on success
	 **/
	public function update_modifies_warning_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit warning');

		$current = Employee::create([
			'user_id'    => $user?->id,
			'name'       => 'Current Emp',
			'created_by' => $user?->creatorId(),
		]);
		$other = Employee::create([
			'user_id'    => null,
			'name'       => 'Other Emp',
			'created_by' => $user?->creatorId(),
		]);
		$warning = Warning::create([
			'warning_by'   => $current->id,
			'warning_to'   => $other->id,
			'subject'      => 'Old',
			'warning_date' => '2025-08-01',
			'description'  => 'OldDesc',
			'created_by'   => $user?->creatorId(),
		]);

		$payload = [
			'warning_by'   => $current->id,
			'warning_to'   => $other->id,
			'subject'      => 'New',
			'warning_date' => '2025-09-01',
			'description'  => 'NewDesc',
		];

		$response = $this->actingAs($user)
			->put(route('warning.update', $warning), $payload);

		$response->assertRedirect(route('warning.index'));
		$this->assertDatabaseHas('warnings', [
			'id'           => $warning->id,
			'subject'      => 'New',
			'description'  => 'NewDesc',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete warning and redirect on success
	 **/
	public function destroy_deletes_warning_and_redirects_on_success()
	{
		Permission::create(['name' => 'delete warning']);
		$user = User::factory()->create();
		$user?->givePermissionTo('delete warning');

		$by = Employee::create([
			'user_id'    => null,
			'name'       => 'By Emp',
			'created_by' => $user?->creatorId(),
		]);
		$to = Employee::create([
			'user_id'    => null,
			'name'       => 'To Emp',
			'created_by' => $user?->creatorId(),
		]);
		$warning = Warning::create([
			'warning_by'   => $by->id,
			'warning_to'   => $to->id,
			'subject'      => 'DelTest',
			'warning_date' => '2025-10-01',
			'description'  => '',
			'created_by'   => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->delete(route('warning.destroy', $warning));

		$response->assertRedirect(route('warning.index'));
		$this->assertDatabaseMissing('warnings', ['id' => $warning->id]);
	}

	/** @test
	 ** Owner with permission sees the warning details.
	 **/
	public function owner_with_permission_sees_warning()
	{
		// grant the required permission
		$this->user->givePermissionTo('view warning');

		$response = $this->get(route('warning.show', $this->warning));

		$response->assertOk()
			->assertViewIs('warning.show')
			->assertViewHas('warning', fn ($w) => $w->id === $this->warning->id);
	}

	/** @test
	 ** Owner without permission is denied (403).
	 **/
	public function owner_without_permission_gets_403()
	{
		// do not give permission

		$response = $this->get(route('warning.show', $this->warning));

		$response->assertStatus(403);
	}

	/** @test
	 ** Non-owner with permission is also denied (403).
	 **/
	public function non_owner_with_permission_gets_403()
	{
		// grant permission on a different user
		$other = User::factory()->create();
		$other->givePermissionTo('view warning');

		$this->actingAs($other);

		$response = $this->get(route('warning.show', $this->warning));

		$response->assertStatus(403);
	}
}
