<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Tax;

use Illuminate\Support\Facades\DB;
class TaxTest extends TestCase
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
	 ** Tax is mass assignable for name, rate, and created_by
	 **/
	public function tax_is_fillable()
	{
		$data = [
			'name'       => 'VAT',
			'rate'       => 12.5,
		];

		$tax = Tax::create($data);

		$this->assertFillableMatches($data, $tax);
	}

	/**
	 ** @test
	 **
	 ** Tax uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function tax_uses_uuid_for_primary_key()
	{
		$tax = Tax::create([
			'name'       => 'Service Tax',
			'rate'       => 5.0,
		]);

		$key = $tax->getKey();

		$this->assertIsString($key);
		$this->assertFalse($tax->getIncrementing());
		$this->assertSame('string', $tax->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
