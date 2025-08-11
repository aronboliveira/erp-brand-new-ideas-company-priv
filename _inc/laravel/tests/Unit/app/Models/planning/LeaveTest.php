<?php

/**
 * Leave model tests
 */

namespace Tests\Unit\Models;

use App\Models\Leave;
use Tests\TestCase;

class LeaveTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Verify that all intended columns are
	 ** mass-assignable and none have gone
	 ** missing or changed order.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Leave::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new Leave)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employees() must be a HasOne relation
	 ** Employee::id ← leaves.employee_id.
	 **/
	public function employees_relation_is_has_one(): void
	{
		$rel = (new Leave)->employees();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',          $rel->getForeignKeyName());
		$this->assertSame('employee_id', $rel->getLocalKeyName());
	}

	/**
	 ** @test
	 *
	 ** leaveType() must be a HasOne relation
	 ** LeaveType::id ← leaves.leave_type_id.
	 **/
	public function leave_type_relation_is_has_one(): void
	{
		$rel = (new Leave)->leaveType();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',           $rel->getForeignKeyName());
		$this->assertSame('leave_type_id', $rel->getLocalKeyName());
	}
}
