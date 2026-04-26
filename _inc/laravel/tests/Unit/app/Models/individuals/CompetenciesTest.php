<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Models\{Competencies, PerformanceType};

class CompetenciesTest extends TestCase
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
		$expected = ['name', 'type', 'code', 'created_by'];
		$this->assertEquals($expected, (new Competencies())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** This function ensures performance() returns the associated PerformanceType model.
	 **/
	public function it_resolves_performance_relationship()
	{
		$performanceType = PerformanceType::factory()->create();
		$competency = Competencies::create([
			'name'       => 'Quality',
			'type'       => $performanceType->id,
			'created_by' => (string) Str::uuid(),
		]);

		$this->assertInstanceOf(PerformanceType::class, $competency->performance);
		$this->assertEquals($performanceType->id, $competency->performance->id);
	}
}
