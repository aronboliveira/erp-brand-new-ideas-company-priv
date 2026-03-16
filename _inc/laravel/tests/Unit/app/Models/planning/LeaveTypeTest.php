<?php

/**
 * LeaveType model tests
 */

namespace Tests\Unit\Models;

use App\Models\LeaveType;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class LeaveTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The model should expose only title,
	 ** days, and created_by for
	 ** mass-assignment.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'title',
			'days',
			'extensible_days',
			'paid',
			'is_health_related',
			'salary_minimum_deduction_percent',
			'salary_maximum_deduction_percent',
			'description',
			'categories',
			'conditions',
			'attachments',
		];

		$this->assertSame($expected, (new LeaveType)->getFillable());
	}
}
