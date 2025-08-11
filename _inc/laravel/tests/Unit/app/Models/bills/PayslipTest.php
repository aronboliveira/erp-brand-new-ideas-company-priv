<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{Payslip, Employee};

class PayslipTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Payslip is mass assignable for all fillable fields
	 **/
	public function payslip_is_fillable()
	{
		$employee = Employee::factory()->create();

		$data = [
			'employee_id'         => $employee->id,
			'net_payble'          => 2500.75,
			'basic_salary'        => 2000.00,
			'salary_month'        => '2025-05',
			'status'              => 'Processed',
			'allowance'           => 200.00,
			'commission'          => 150.00,
			'loan'                => 100.00,
			'saturation_deduction' => 50.00,
			'other_payment'       => 75.25,
			'overtime'            => 20.00,
			'created_by'          => 'admin_user',
		];

		$payslip = Payslip::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $payslip->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Payslip uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function payslip_uses_uuid_for_primary_key()
	{
		$p = Payslip::factory()->create();
		$key = $p->getKey();

		$this->assertIsString($key);
		$this->assertFalse($p->getIncrementing());
		$this->assertSame('string', $p->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** static employee() returns the Employee model for a given id or null
	 **/
	public function static_employee_method_returns_employee_or_null()
	{
		$emp = Employee::factory()->create();
		$this->assertInstanceOf(
			Employee::class,
			Payslip::employee($emp->id)
		);

		$this->assertNull(Payslip::employee('non-existent-id'));
	}

	/**
	 ** @test
	 **
	 ** employees() relation should point to Employee via employee_id
	 **/
	public function employees_relation_resolves_to_employee_model()
	{
		$relation = (new Payslip)->employees();

		$this->assertInstanceOf(HasOne::class,      $relation);
		$this->assertSame(Employee::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                     $relation->getForeignKeyName());
		$this->assertSame('employee_id',            $relation->getLocalKeyName());
	}
}
