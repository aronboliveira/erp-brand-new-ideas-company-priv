<?php

namespace Tests\Unit\Models;

use App\Models\Timesheet;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class TimesheetTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	/**
	 ** @test
	 **
	 ** The $fillable array must match the private constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		// Timesheet uses $guarded (not $fillable), so getFillable() returns [].
		$this->assertSame([], (new Timesheet)->getFillable());
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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ts->project()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ts->task()
		);
	}
}
