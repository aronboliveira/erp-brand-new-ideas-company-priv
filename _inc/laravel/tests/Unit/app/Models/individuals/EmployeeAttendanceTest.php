<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmployeeAttendance;
use App\Models\Employee;

class EmployeeAttendanceTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'employee_id', 'date', 'status', 'clock_in', 'clock_out',
			'late', 'early_leaving', 'overtime', 'total_rest', 'created_by'
		];
		$this->assertEquals($expected, (new EmployeeAttendance())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** employees() relation returns the Employee matched by user_id.
	 **/
	public function employees_relation_returns_employee_by_user_id()
	{
		$emp = Employee::factory()->create(['user_id' => 123]);
		$att = EmployeeAttendance::factory()->create(['employee_id' => 123]);

		$this->assertInstanceOf(Employee::class, $att->employees);
		$this->assertEquals($emp->id, $att->employees->id);
	}

	/**
	 ** @test
	 **
	 ** employee() relation returns the Employee matched by id.
	 **/
	public function employee_relation_returns_employee_by_id()
	{
		$emp = Employee::factory()->create();
		$att = EmployeeAttendance::factory()->create(['employee_id' => $emp->id]);

		$this->assertInstanceOf(Employee::class, $att->employee);
		$this->assertEquals($emp->id, $att->employee->id);
	}
}
