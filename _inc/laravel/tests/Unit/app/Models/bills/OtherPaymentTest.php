<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{OtherPayment, Employee};

class OtherPaymentTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** OtherPayment is mass assignable for employee_id, title, amount, and created_by
	 **/
	public function other_payment_is_fillable()
	{
		$employee = Employee::factory()->create();

		$data = [
			'employee_id' => $employee->id,
			'title'       => 'Bonus',
			'amount'      => 250.00,
			'created_by'  => 'admin_user',
		];

		$op = OtherPayment::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $op->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Primary key is an auto-incrementing integer
	 **/
	public function primary_key_is_incrementing_integer()
	{
		$op = OtherPayment::factory()->create();

		$this->assertTrue($op->getIncrementing());
		$this->assertSame('int', $op->getKeyType());
		$this->assertIsInt($op->getKey());
	}

	/**
	 ** @test
	 **
	 ** static $otherPaymentType property contains expected keys and labels
	 **/
	public function other_payment_type_static_property_is_correct()
	{
		$expected = [
			'fixed'      => 'Fixed',
			'percentage' => 'Percentage',
		];
		$this->assertSame($expected, OtherPayment::$otherPaymentType);
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee via employee_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new OtherPayment)->employee();

		$this->assertInstanceOf(HasOne::class,      $relation);
		$this->assertSame(Employee::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                     $relation->getForeignKeyName());
		$this->assertSame('employee_id',            $relation->getLocalKeyName());
	}
}
