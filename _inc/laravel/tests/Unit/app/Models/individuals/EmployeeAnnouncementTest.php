<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmployeeAnnouncement;

class EmployeeAnnouncementTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['announcement_id', 'employee_id', 'created_by'];
		$this->assertEquals($expected, (new EmployeeAnnouncement())->getFillable());
	}
}
