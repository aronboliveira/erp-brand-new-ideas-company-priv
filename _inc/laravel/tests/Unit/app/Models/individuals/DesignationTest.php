<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Designation;

class DesignationTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['created_by', 'department_id', 'name'];
		$this->assertEquals($expected, (new Designation())->getFillable());
	}
}
