<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Announcement;

class AnnouncementTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The Announcement model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'title', 'start_date', 'end_date', 'branch_id', 'department_id',
			'employee_id', 'description', 'created_by'
		];
		$this->assertEquals($expected, (new Announcement())->getFillable());
	}
}
