<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{PayslipType};

use Illuminate\Support\Facades\DB;
class PayslipTypeTest extends TestCase
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
	 ** PayslipType is mass assignable for name and created_by
	 **/
	public function payslip_type_is_fillable()
	{
		$data = [
			'name'       => 'Yearly Summary',
		];

		$type = PayslipType::create($data);

		$this->assertEquals('Yearly Summary', $type->name);
	}

	/**
	 ** @test
	 **
	 ** Primary key is a non-incrementing UUID string
	 **/
	public function primary_key_is_uuid()
	{
		$type = PayslipType::factory()->create([
			'name'       => 'Monthly',
		]);

		$key = $type->getKey();

		$this->assertIsString($key);
		$this->assertFalse($type->getIncrementing());
		$this->assertSame('string', $type->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
