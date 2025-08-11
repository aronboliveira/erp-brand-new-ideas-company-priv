<?php

namespace Tests\Unit\Models;

use App\Models\Timesheet;
use Tests\TestCase;

class TimesheetTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The $fillable array must match the private constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'project_id',
			'task_id',
			'date',
			'time',
			'description',
			'created_by',
		];

		$this->assertSame($expected, (new Timesheet)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** project() and task() must be HasOne relations.
	 **/
	public function relations_are_has_one(): void
	{
		$ts = new Timesheet;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$ts->project()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$ts->task()
		);
	}
}
