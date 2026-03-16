<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{LoanOption};

use Illuminate\Support\Facades\DB;
class LoanOptionTest extends TestCase
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
	 ** LoanOption is mass assignable for name and created_by
	 **/
	public function loan_option_is_fillable()
	{
		$data = [
			'name'       => 'Pension Plan',
		];

		$option = LoanOption::create($data);

		$this->assertEquals('Pension Plan', $option->name);
	}

	/**
	 ** @test
	 **
	 ** Primary key is a non-incrementing UUID string
	 **/
	public function primary_key_is_uuid()
	{
		$option = LoanOption::create([
			'name'       => 'Education Loan',
		]);

		$key = $option->getKey();

		$this->assertIsString($key);
		$this->assertFalse($option->getIncrementing());
		$this->assertSame('string', $option->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
