<?php

/**
 * Estimation model tests
 */

namespace Tests\Unit\Models;

use App\Models\Estimation;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class EstimationTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** status() must return the original
	 ** list of options in the declared order.
	 **/
	public function status_method_returns_expected_options(): void
	{
		$expected = ['Open', 'Not Paid', 'Partially Paid', 'Paid', 'Cancelled'];

		$this->assertSame($expected, Estimation::status());
	}

	/**
	 ** @test
	 *
	 ** getSubTotal() should multiply price × qty
	 ** for every pivot row in the cached
	 ** “getProducts” relation.
	 **/
	public function it_calculates_subtotal_correctly(): void
	{
		$estimation = new Estimation;

		$products = new Collection([
			(object) ['pivot' => (object) ['price' => 10.0, 'quantity' => 2]],
			(object) ['pivot' => (object) ['price' =>  5.5, 'quantity' => 4]],
		]);

		// Pretend the relation is already loaded.
		$estimation->setRelation('getProducts', $products);

		$this->assertSame(10.0 * 2 + 5.5 * 4, $estimation->getSubTotal());
	}

	/**
	 ** @test
	 *
	 ** getTax() must apply the tax rate to
	 ** (sub-total − discount).
	 **/
	public function it_calculates_tax_correctly(): void
	{
		$estimation = new Estimation;
		$estimation->discount = 5.0;

		// Sub-total 100
		$estimation->setRelation('getProducts', collect([
			(object) ['pivot' => (object) ['price' => 20, 'quantity' => 5]],
		]));

		// Fake tax relation with 10 % rate
		$estimation->setRelation('tax', (object) ['rate' => 10]);

		$this->assertSame((100 - 5) * 0.10, $estimation->getTax());
	}

	/**
	 ** @test
	 *
	 ** getTotal() = sub-total − discount + tax.
	 **/
	public function it_calculates_total_correctly(): void
	{
		$estimation = new Estimation;
		$estimation->discount = 5;

		$estimation->setRelation('getProducts', collect([
			(object) ['pivot' => (object) ['price' => 25, 'quantity' => 2]], // 50
		]));
		$estimation->setRelation('tax', (object) ['rate' => 10]); // (50-5)*0.10 = 4.5

		$this->assertSame(50 - 5 + 4.5, $estimation->getTotal());
	}

	/**
	 ** @test
	 *
	 ** getEstimationSummary() must aggregate
	 ** totals across many estimation objects
	 ** and format via User::priceFormat().
	 **/
	public function it_computes_estimation_summary(): void
	{
		// Fake logged-in user with priceFormat().
		$user = new class
		{
			public function priceFormat($v)
			{
				return number_format($v, 2);
			}
			public function creatorId()
			{
				return 1;
			}
		};

		Mockery::mock('alias:' . Estimation::class)
			->shouldReceive('_checkLogin')
			->once()
			->andReturn($user);

		$a = Mockery::mock(Estimation::class)->shouldReceive('getTotal')->andReturn(40)->getMock();
		$b = Mockery::mock(Estimation::class)->shouldReceive('getTotal')->andReturn(60)->getMock();

		$summary = Estimation::getEstimationSummary([$a, $b]);

		$this->assertSame('100.00', $summary);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
