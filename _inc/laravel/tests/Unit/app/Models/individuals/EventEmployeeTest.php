<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EventEmployee;

class EventEmployeeTest extends TestCase
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
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'event_id',
			'employee_id',
			'role',
			'metadata',
		];
		$this->assertEquals($expected, (new EventEmployee())->getFillable());
	}
}
