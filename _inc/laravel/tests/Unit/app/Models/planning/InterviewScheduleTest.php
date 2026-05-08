<?php

namespace Tests\Unit\app\Models\planning;

use App\Models\InterviewSchedule;
use Tests\TestCase;

class InterviewScheduleTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Confirm the date/time casts are defined
	 ** exactly as expected for "date" and "time".
	 **/
	public function casts_array_is_correct(): void
	{
		$casts = (new InterviewSchedule)->getCasts();

		$this->assertSame('date:Y-m-d', $casts['date']);
		$this->assertSame('string',     $casts['time']);
		$this->assertSame('array',      $casts['steps']);
		$this->assertSame('array',      $casts['results']);
		$this->assertSame('array',      $casts['attachments']);
		$this->assertSame('array',      $casts['involved']);
	}

	/**
	 ** @test
	 *
	 ** applications() must be a BelongsTo mapping
	 ** interview_schedules.candidate → JobApplication::id.
	 **/
	public function applications_relation_is_belongs_to(): void
	{
		$rel = (new InterviewSchedule)->applications();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('candidate', $rel->getForeignKeyName());
		$this->assertSame('id',        $rel->getOwnerKeyName());
	}

}
