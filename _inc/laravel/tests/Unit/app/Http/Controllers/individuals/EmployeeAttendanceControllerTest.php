<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\IpRestrict;
use App\Models\Utility;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\EmployeeAttendanceController;

class EmployeeAttendanceControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index should display attendances for users with 'manage attendance' permission
	 **/
	public function index_displays_attendances_for_authorized_user()
	{
		Permission::create(['name' => 'manage attendance']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage attendance');

		// seed branches and departments
		Branch::create(['name' => 'B1', 'created_by' => $user?->creatorId()]);
		Department::create(['name' => 'D1', 'created_by' => $user?->creatorId()]);

		// create an employee and attendance
		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'Emp',
			'created_by' => $user?->creatorId(),
		]);
		EmployeeAttendance::create([
			'employee_id'   => $emp->id,
			'date'          => '2025-05-01',
			'status'        => 'Present',
			'clock_in'      => '09:00:00',
			'clock_out'     => '17:00:00',
			'late'          => '00:00:00',
			'early_leaving' => '00:00:00',
			'overtime'      => '00:00:00',
			'total_rest'    => '00:00:00',
			'created_by'    => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('attendance.index'));

		$response->assertStatus(200);
		$response->assertViewIs('attendance.index');
		$response->assertViewHas('attendances', function ($atts) {
			return $atts->count() === 1;
		});
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('attendance.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create should display form for users with 'create attendance' permission
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create attendance']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create attendance');

		$response = $this->actingAs($user)->get(route('attendance.create'));

		$response->assertStatus(200);
		$response->assertViewIs('attendance.create');
		$response->assertViewHas('employees');
	}

	/**
	 ** @test
	 **
	 ** store should redirect back on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create attendance']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create attendance');

		$response = $this->actingAs($user)
			->from(route('attendance.create'))
			->post(route('attendance.store'), []);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store should create attendance and redirect on success
	 **/
	public function store_creates_attendance_and_redirects_on_success()
	{
		Permission::create(['name' => 'create attendance']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create attendance');

		// seed company start/end times
		DB::table('settings')->insert([
			['name' => 'company_start_time', 'value' => '09:00:00', 'created_by' => $user?->creatorId()],
			['name' => 'company_end_time',   'value' => '17:00:00', 'created_by' => $user?->creatorId()],
		]);

		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'EmpStore',
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'employee_id' => $emp->id,
			'date'        => '2025-06-01',
			'clock_in'    => '09:15',
			'clock_out'   => '17:30',
		];

		$response = $this->actingAs($user)
			->post(route('attendance.store'), $payload);

		$response->assertRedirect(route('attendance.index'));
		$this->assertDatabaseHas('employee_attendances', [
			'employee_id' => $emp->id,
			'date'        => '2025-06-01',
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
		$response = $this->actingAs($user)->get(route('attendance.show'));

		$response->assertRedirect(route('attendance.index'));
	}

	/**
	 ** @test
	 **
	 ** edit should display form for users with 'edit attendance' permission
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'edit attendance']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit attendance');

		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'EmpEdit',
			'created_by' => $user?->creatorId(),
		]);
		$att = EmployeeAttendance::create([
			'employee_id'   => $emp->id,
			'date'          => '2025-07-01',
			'status'        => 'Present',
			'clock_in'      => '09:00:00',
			'clock_out'     => '17:00:00',
			'late'          => '00:00:00',
			'early_leaving' => '00:00:00',
			'overtime'      => '00:00:00',
			'total_rest'    => '00:00:00',
			'created_by'    => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('attendance.edit', $att->id));

		$response->assertStatus(200);
		$response->assertViewIs('attendance.edit');
		$response->assertViewHasAll(['attendance', 'employees']);
	}

	/**
	 ** @test
	 **
	 ** update should modify attendance and redirect on success
	 **/
	public function update_modifies_attendance_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit attendance']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit attendance');

		// seed company times
		DB::table('settings')->insert([
			['name' => 'company_start_time', 'value' => '09:00:00', 'created_by' => $user?->creatorId()],
			['name' => 'company_end_time',   'value' => '17:00:00', 'created_by' => $user?->creatorId()],
		]);

		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'EmpUp',
			'created_by' => $user?->creatorId(),
		]);
		$att = EmployeeAttendance::create([
			'employee_id'   => $emp->id,
			'date'          => '2025-08-01',
			'status'        => 'Present',
			'clock_in'      => '09:00:00',
			'clock_out'     => '17:00:00',
			'late'          => '00:00:00',
			'early_leaving' => '00:00:00',
			'overtime'      => '00:00:00',
			'total_rest'    => '00:00:00',
			'created_by'    => $user?->creatorId(),
		]);

		$payload = ['clock_in' => '10:00', 'clock_out' => '18:00'];

		$response = $this->actingAs($user)
			->put(route('attendance.update', $att->id), $payload);

		$response->assertRedirect(route('attendance.index'));
		$this->assertDatabaseHas('employee_attendances', [
			'id'        => $att->id,
			'clock_in'  => '10:00:00',
			'clock_out' => '18:00:00',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete attendance and redirect on success
	 **/
	public function destroy_deletes_attendance_and_redirects_on_success()
	{
		Permission::create(['name' => 'delete attendance']);
		$user = User::factory()->create();
		$user?->givePermissionTo('delete attendance');

		$emp = Employee::create([
			'user_id'    => null,
			'name'       => 'EmpDel',
			'created_by' => $user?->creatorId(),
		]);
		$att = EmployeeAttendance::create([
			'employee_id'   => $emp->id,
			'date'          => '2025-09-01',
			'status'        => 'Present',
			'clock_in'      => '09:00:00',
			'clock_out'     => '17:00:00',
			'late'          => '00:00:00',
			'early_leaving' => '00:00:00',
			'overtime'      => '00:00:00',
			'total_rest'    => '00:00:00',
			'created_by'    => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->delete(route('attendance.destroy', $att->id));

		$response->assertRedirect(route('attendance.index'));
		$this->assertDatabaseMissing('employee_attendances', ['id' => $att->id]);
	}

	/**
	 ** @test
	 **
	 ** computeDurations should return correct late, earlyLeaving and overtime
	 **/
	public function computeDurations_calculates_correct_values()
	{
		$attendance = new EmployeeAttendanceController();
		$result = $attendance->computeDurations(
			'10:00:00',
			'18:30:00',
			'2025-01-01',
			'09:00:00',
			'17:00:00'
		);
		$this->assertEquals('01:00:00', $result['late']);
		$this->assertEquals('00:00:00', $result['earlyLeaving']);
		$this->assertEquals('01:30:00', $result['overtime']);
	}
}
