<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\Travel;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class TravelControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index should display travels for users with 'manage travel' permission
	 **/
	public function index_displays_travels_for_authorized_user()
	{
		Permission::create(['name' => 'manage travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage travel');

		// create two employees and travels
		$emp1 = Employee::create([
			'user_id'    => $user?->id,
			'name'       => 'Emp One',
			'created_by' => $user?->creatorId(),
		]);
		$emp2 = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp Two',
			'created_by' => $user?->creatorId(),
		]);

		Travel::create([
			'employee_id'      => $emp1->id,
			'start_date'       => '2025-01-01',
			'end_date'         => '2025-01-02',
			'purpose_of_visit' => 'Visit A',
			'place_of_visit'   => 'Place A',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);
		Travel::create([
			'employee_id'      => $emp2->id,
			'start_date'       => '2025-02-01',
			'end_date'         => '2025-02-02',
			'purpose_of_visit' => 'Visit B',
			'place_of_visit'   => 'Place B',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('travel.index'));

		$response->assertStatus(200);
		$response->assertViewIs('travel.index');
		$response->assertViewHas('travels', function ($travels) {
			return $travels->count() === 2;
		});
	}

	/**
	 ** @test
	 **
	 ** index should filter travels when user type is 'Employee'
	 **/
	public function index_filters_travels_for_employee_user()
	{
		Permission::create(['name' => 'manage travel']);
		$user = User::factory()->create(['type' => 'Employee']);
		$user?->givePermissionTo('manage travel');

		// link an Employee record to this user
		$emp = Employee::create([
			'user_id'    => $user?->id,
			'name'       => 'Linked Emp',
			'created_by' => $user?->creatorId(),
		]);

		// travel for linked employee
		$t1 = Travel::create([
			'employee_id'      => $emp->id,
			'start_date'       => '2025-03-01',
			'end_date'         => '2025-03-02',
			'purpose_of_visit' => 'Visit X',
			'place_of_visit'   => 'Place X',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);
		// travel for another employee
		$otherEmp = Employee::create([
			'user_id'    => null,
			'name'       => 'Other Emp',
			'created_by' => $user?->creatorId(),
		]);
		Travel::create([
			'employee_id'      => $otherEmp->id,
			'start_date'       => '2025-04-01',
			'end_date'         => '2025-04-02',
			'purpose_of_visit' => 'Visit Y',
			'place_of_visit'   => 'Place Y',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('travel.index'));

		$response->assertViewHas('travels', function ($travels) use ($t1) {
			return $travels->count() === 1
				&& $travels->first()->id === $t1->id;
		});
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('travel.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create should display the form for users with 'create travel' permission
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create travel');

		Employee::create([
			'user_id'    => null,
			'name'       => 'Emp List',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('travel.create'));

		$response->assertStatus(200);
		$response->assertViewIs('travel.create');
		$response->assertViewHas('employees');
	}

	/**
	 ** @test
	 **
	 ** store should redirect back with error on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create travel');

		$response = $this->actingAs($user)
			->from(route('travel.create'))
			->post(route('travel.store'), [
				'employee_id'      => '',
				'start_date'       => '',
				'end_date'         => '',
				'purpose_of_visit' => '',
				'place_of_visit'   => '',
			]);

		$response->assertRedirect(route('travel.create'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store should create a travel and redirect to index on success
	 **/
	public function store_creates_travel_and_redirects_on_success()
	{
		Permission::create(['name' => 'create travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create travel');

		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp Store',
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'employee_id'      => $emp->id,
			'start_date'       => '2025-05-01',
			'end_date'         => '2025-05-02',
			'purpose_of_visit' => 'Business',
			'place_of_visit'   => 'City',
			'description'      => 'Details',
		];

		$response = $this->actingAs($user)
			->post(route('travel.store'), $payload);

		$response->assertRedirect(route('travel.index'));
		$this->assertDatabaseHas('travels', [
			'employee_id'      => $emp->id,
			'purpose_of_visit' => 'Business',
			'place_of_visit'   => 'City',
			'created_by'       => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show should redirect to index
	 **/
	public function show_redirects_to_index()
	{
		$user = User::factory()->create();
		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp Show',
			'created_by' => $user?->creatorId(),
		]);
		$travel = Travel::create([
			'employee_id'      => $emp->id,
			'start_date'       => '2025-06-01',
			'end_date'         => '2025-06-02',
			'purpose_of_visit' => 'Test',
			'place_of_visit'   => 'Place',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('travel.show', $travel));

		$response->assertRedirect(route('travel.index'));
	}

	/**
	 ** @test
	 **
	 ** edit should display form for users with 'edit travel' permission
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'edit travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit travel');

		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp Edit',
			'created_by' => $user?->creatorId(),
		]);
		$travel = Travel::create([
			'employee_id'      => $emp->id,
			'start_date'       => '2025-07-01',
			'end_date'         => '2025-07-02',
			'purpose_of_visit' => 'Edit',
			'place_of_visit'   => 'Here',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('travel.edit', $travel));

		$response->assertStatus(200);
		$response->assertViewIs('travel.edit');
		$response->assertViewHasAll(['travel', 'employees']);
	}

	/**
	 ** @test
	 **
	 ** update should redirect back with error on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'edit travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit travel');

		$emp   = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp Up',
			'created_by' => $user?->creatorId(),
		]);
		$travel = Travel::create([
			'employee_id'      => $emp->id,
			'start_date'       => '2025-08-01',
			'end_date'         => '2025-08-02',
			'purpose_of_visit' => 'Up',
			'place_of_visit'   => 'Loc',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->from(route('travel.edit', $travel))
			->put(route('travel.update', $travel), [
				'employee_id'      => '',
				'start_date'       => '',
				'end_date'         => '',
				'purpose_of_visit' => '',
				'place_of_visit'   => '',
			]);

		$response->assertRedirect(route('travel.edit', $travel));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should modify the travel and redirect on success
	 **/
	public function update_modifies_travel_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit travel');

		$emp   = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp Up2',
			'created_by' => $user?->creatorId(),
		]);
		$travel = Travel::create([
			'employee_id'      => $emp->id,
			'start_date'       => '2025-09-01',
			'end_date'         => '2025-09-02',
			'purpose_of_visit' => 'Old',
			'place_of_visit'   => 'OldPlace',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);

		$payload = [
			'employee_id'      => $emp->id,
			'start_date'       => '2025-10-01',
			'end_date'         => '2025-10-05',
			'purpose_of_visit' => 'New',
			'place_of_visit'   => 'NewPlace',
			'description'      => 'Updated',
		];

		$response = $this->actingAs($user)
			->put(route('travel.update', $travel), $payload);

		$response->assertRedirect(route('travel.index'));
		$this->assertDatabaseHas('travels', [
			'id'                => $travel->id,
			'purpose_of_visit'  => 'New',
			'place_of_visit'    => 'NewPlace',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the travel and redirect on success
	 **/
	public function destroy_deletes_travel_and_redirects_on_success()
	{
		Permission::create(['name' => 'delete travel']);
		$user = User::factory()->create();
		$user?->givePermissionTo('delete travel');

		$emp   = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp Del',
			'created_by' => $user?->creatorId(),
		]);
		$travel = Travel::create([
			'employee_id'      => $emp->id,
			'start_date'       => '2025-11-01',
			'end_date'         => '2025-11-02',
			'purpose_of_visit' => 'Del',
			'place_of_visit'   => 'Here',
			'description'      => null,
			'created_by'       => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->delete(route('travel.destroy', $travel));

		$response->assertRedirect(route('travel.index'));
		$this->assertDatabaseMissing('travels', ['id' => $travel->id]);
	}
}
