<?php
// tests/Unit/Models/JobOnBoardTest.php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Support\Carbon
};
use App\Models\JobOnBoard;

class JobOnBoardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** JobOnBoard is mass assignable for all fillable fields
	 **/
	public function job_on_board_is_fillable()
	{
		$data = [
			'application'         => 5,
			'joining_date'        => '2025-07-15',
			'status'              => 'pending',
			'convert_to_employee' => 1,
			'job_type'            => 'full_time',
			'days_of_week'        => 5,
			'salary'              => 60000,
			'salary_type'         => 'annual',
			'salary_duration'     => 'yearly',
			'created_by'          => 'admin123',
		];

		$jobBoard = JobOnBoard::create($data);

		$this->assertFillableMatches($data, $jobBoard);
	}

	/**
	 ** @test
	 **
	 ** uses UUID for primary key and casts joining_date and convert_to_employee
	 **/
	public function primary_key_uuid_and_casts_work()
	{
		$jobBoard = JobOnBoard::create([
			'application'         => 10,
			'joining_date'        => '2025-08-01',
			'status'              => 'approved',
			'convert_to_employee' => 0,
			'job_type'            => 'part_time',
			'days_of_week'        => 3,
			'salary'              => 30000,
			'salary_type'         => 'monthly',
			'salary_duration'     => 'monthly',
			'created_by'          => 'user999',
		]);

		// UUID
		$key = $jobBoard->getKey();
		$this->assertIsString($key);
		$this->assertFalse($jobBoard->getIncrementing());
		$this->assertSame('string', $jobBoard->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);

		// Casts
		$this->assertInstanceOf(Carbon::class, $jobBoard->joining_date);
		$this->assertFalse((bool)$jobBoard->convert_to_employee);
	}
}
