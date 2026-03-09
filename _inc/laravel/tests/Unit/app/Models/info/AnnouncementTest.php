<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Announcement;

class AnnouncementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The Announcement model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'title',
			'start_date',
			'end_date',
			'branch_id',
			'department_id',
			'employee_id',
			'recruiter',
			'description',
			'is_active',
			'is_ready',
			'planned_start',
			'requirements',
			'tags',
			'steps',
		];
		$this->assertEquals($expected, (new Announcement())->getFillable());
	}
}
