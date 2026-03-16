<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Designation;

use Illuminate\Support\Facades\DB;
class DesignationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
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
			'name',
			'department_id',
			'expected_budget',
			'valid_from',
			'valid_to',
			'description',
			'notes',
		];
		$this->assertEquals($expected, (new Designation())->getFillable());
	}
}
