<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmployeeAttendance;
use App\Models\Employee;

use Illuminate\Support\Facades\DB;
class EmployeeAttendanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'employee_id',
			'date',
			'status',
			'clock_in',
			'clock_out',
			'early_arrival',
			'early_arrival_count',
			'late',
			'late_count',
			'early_leaving',
			'early_leaving_count',
			'overtime',
			'overtime_count',
			'overtime_id',
			'total_rest',
			'total_work',
		];
		$this->assertEquals($expected, (new EmployeeAttendance())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** employees() relation returns the Employee matched by employee_id (PK).
	 **/
	public function employees_relation_returns_employee_by_user_id()
	{
		$emp = Employee::factory()->create();
		$att = EmployeeAttendance::factory()->create(['employee_id' => $emp->id]);

		$this->assertInstanceOf(Employee::class, $att->employee);
		$this->assertEquals($emp->id, $att->employee->id);
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
