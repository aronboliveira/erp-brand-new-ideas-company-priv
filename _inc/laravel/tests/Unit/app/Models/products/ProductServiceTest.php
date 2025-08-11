<?php

/**
 * tests/Unit/Models/ProductServiceTest.php
 *
 * Unit-tests for App\Models\ProductService
 */

namespace Tests\Unit\Models;

use App\Models\ProductService;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The mass-assignment whitelist must stay
	 ** identical to the private constant list.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(ProductService::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new ProductService)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** taxes(), unit() and category() relations
	 ** must be HasOne relations with correct keys.
	 **/
	public function relations_are_has_one(): void
	{
		$ps = new ProductService;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$ps->taxes()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$ps->unit()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$ps->category()
		);
	}

	/**
	 ** @test
	 *
	 ** tax(), taxRate() and taxData() helpers
	 ** must correctly transform the comma-separated
	 ** tax id list into objects, summed rates and
	 ** comma-concatenated names.
	 **/
	public function tax_helpers_work_as_expected(): void
	{
		// Stub Tax::find for ids 1 and 2.
		Mockery::mock('alias:App\Models\Tax')
			->shouldReceive('find')
			->withArgs([1])
			->andReturn((object)['id' => 1, 'rate' => 5, 'name' => 'VAT'])
			->getMock()
			->shouldReceive('find')
			->withArgs([2])
			->andReturn((object)['id' => 2, 'rate' => 10, 'name' => 'GST']);

		$ps = new ProductService;

		$taxObjs = $ps->tax('1,2');
		$this->assertCount(2, $taxObjs);

		$rate = $ps->taxRate('1,2');
		$this->assertSame(15.0, $rate);

		$names = ProductService::taxData('1,2');
		$this->assertSame('VAT,GST', $names);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
