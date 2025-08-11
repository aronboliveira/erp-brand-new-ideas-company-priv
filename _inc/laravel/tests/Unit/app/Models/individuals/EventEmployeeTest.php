<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EventEmployee;

class EventEmployeeTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['created_by', 'employee_id', 'event_id'];
		$this->assertEquals($expected, (new EventEmployee())->getFillable());
	}
}
