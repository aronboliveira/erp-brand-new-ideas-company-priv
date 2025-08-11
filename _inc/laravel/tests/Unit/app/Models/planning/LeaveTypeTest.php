<?php

/**
 * LeaveType model tests
 */

namespace Tests\Unit\Models;

use App\Models\LeaveType;
use Tests\TestCase;

class LeaveTypeTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The model should expose only title,
	 ** days, and created_by for
	 ** mass-assignment.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['title', 'days', 'created_by'];

		$this->assertSame($expected, (new LeaveType)->getFillable());
	}
}
