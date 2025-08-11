<?php

namespace Tests\Unit\Models;

use App\Models\InterviewSchedule;
use Tests\TestCase;

class InterviewScheduleTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Confirm the date/time casts are defined
	 ** exactly as expected for “date” and “time”.
	 **/
	public function casts_array_is_correct(): void
	{
		$expected = ['date' => 'date', 'time' => 'datetime:H:i:s'];

		$this->assertSame($expected, (new InterviewSchedule)->getCasts());
	}

	/**
	 ** @test
	 *
	 ** applications() must be HasOne mapping
	 ** JobApplication::id ← interview_schedules.candidate.
	 **/
	public function applications_relation_is_has_one(): void
	{
		$rel = (new InterviewSchedule)->applications();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',        $rel->getForeignKeyName());
		$this->assertSame('candidate', $rel->getLocalKeyName());
	}

	/**
	 ** @test
	 *
	 ** users() must be HasOne mapping
	 ** User::id ← interview_schedules.employee.
	 **/
	public function users_relation_is_has_one(): void
	{
		$rel = (new InterviewSchedule)->users();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',       $rel->getForeignKeyName());
		$this->assertSame('employee', $rel->getLocalKeyName());
	}
}
