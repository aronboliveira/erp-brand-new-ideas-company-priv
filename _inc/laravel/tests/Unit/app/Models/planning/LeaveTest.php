<?php

/**
 * Leave model tests
 */

namespace Tests\Unit\Models;

use App\Models\Leave;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class LeaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Verify that all intended columns are
	 ** mass-assignable and none have gone
	 ** missing or changed order.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'employee_id',
			'leave_type_id',
			'applied_on',
			'start_date',
			'end_date',
			'total_leave_days',
			'leave_reason',
			'remark',
			'status',
			'discount',
			'attachments',
			'conditions',
		];

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
		$rel = (new Leave)->employee();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('employee_id',          $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('leave_type_id',           $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}
}
