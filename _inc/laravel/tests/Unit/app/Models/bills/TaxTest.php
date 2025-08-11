<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Tax;

class TaxTest extends TestCase
{
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
			'created_by' => 'user-123',
		];

		$tax = Tax::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $tax->$field);
		}
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
			'created_by' => 'user-456',
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
