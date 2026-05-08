<?php

namespace Tests\Unit\Models;

use App\Models\Proposal;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class ProposalTest extends TestCase
{
	use SafeAliasMock;

	/**
	 ** A reusable fake collection of proposal
	 ** items (price × qty minus discount).
	 */
	private Collection $items;

	protected function setUp(): void
	{
		parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

		// Build three stub items
		$this->items = collect([
			(object) ['price' => 50, 'quantity' => 2, 'discount' => 5, 'tax' => 'A'],
			(object) ['price' => 30, 'quantity' => 1, 'discount' => 0, 'tax' => 'B'],
			(object) ['price' => 20, 'quantity' => 3, 'discount' => 2, 'tax' => 'C'],
		]);

		// Prime Utility::$taxRateData cache so totalTaxRate() returns 10
		// for any tax code used by the fake items, instead of aliasMocking
		// the Utility class (fails class-already-loaded).
		\App\Models\Utility::$taxRateData = [
			'A' => 10.0,
			'B' => 10.0,
			'C' => 10.0,
		];
	}

	/**
	 ** @test
	 *
	 ** getSubTotal() must multiply price ×
	 ** quantity for all items.
	 **/
	public function sub_total_is_calculated_correctly(): void
	{
		$p = new Proposal;
		$p->setRelation('items', $this->items);

		$this->assertEquals(50 * 2 + 30 * 1 + 20 * 3, $p->getSubTotal());
	}

	/**
	 ** @test
	 *
	 ** getTotalDiscount() sums the discount
	 ** column across items.
	 **/
	public function total_discount_is_calculated_correctly(): void
	{
		$p = new Proposal;
		$p->setRelation('items', $this->items);

		$this->assertEquals(5 + 0 + 2, $p->getTotalDiscount());
	}

	/**
	 ** @test
	 *
	 ** getTotalTax() applies Utility::totalTaxRate()
	 ** to each item’s net amount.
	 **/
	public function total_tax_is_calculated_correctly(): void
	{
		$p = new Proposal;
		$p->setRelation('items', $this->items);

		// manual expected: 10 % * (price*qty − discount)
		$expected = 0.10 * ((100 - 5) + (30 - 0) + (60 - 2));
		$this->assertSame($expected, $p->getTotalTax());
	}

	/**
	 ** @test
	 *
	 ** getTotal() = subtotal − discount + tax.
	 **/
	public function total_is_calculated_correctly(): void
	{
		$p = new Proposal;
		$p->setRelation('items', $this->items);

		$subtotal = $p->getSubTotal();
		$discount = $p->getTotalDiscount();
		$tax     = $p->getTotalTax();

		$this->assertSame(($subtotal - $discount) + $tax, $p->getTotal());
	}

	/**
	 ** @test
	 *
	 ** changeStatus() must locate a proposal
	 ** and call update() after mutating status.
	 **/
	public function change_status_updates_status_and_saves(): void
	{
		// Build a spy Proposal instance
		$fake = Mockery::mock(Proposal::class)->makePartial();
		$fake->status = 'Draft';

		// Expect update() called once after mutation
		$fake->shouldReceive('update')
			->once()
			->andReturnTrue();

		// Intercept Proposal::find()
		$this->aliasMock(Proposal::class)
			->shouldReceive('find')
			->once()
			->with(7)
			->andReturn($fake);

		Proposal::changeStatus(7, 'Accepted');

		$this->assertSame('Accepted', $fake->status);
	}

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}
}
