<?php

/**
 * Estimation model tests
 */

namespace Tests\Unit\Models;

use App\Models\Estimation;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class EstimationTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}

	use SafeAliasMock;

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
		$products = new Collection([
			(object) ['pivot' => (object) ['price' => 10.0, 'quantity' => 2]],
			(object) ['pivot' => (object) ['price' =>  5.5, 'quantity' => 4]],
		]);

		$fakeRelation = new class($products) extends Collection {
			private Collection $result;
			public function __construct(Collection $result)
			{
				parent::__construct();
				$this->result = $result;
			}
			public function get($key = null, $default = null): mixed
			{
				return $key === null ? $this->result : parent::get($key, $default);
			}
		};

		$estimation = Mockery::mock(Estimation::class)->makePartial();
		$estimation->shouldReceive('getProducts')->andReturn($fakeRelation);

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
		$products = collect([
			(object) ['pivot' => (object) ['price' => 20, 'quantity' => 5]],
		]);

		$fakeRelation = new class($products) extends Collection {
			private Collection $result;
			public function __construct(Collection $result)
			{
				parent::__construct();
				$this->result = $result;
			}
			public function get($key = null, $default = null): mixed
			{
				return $key === null ? $this->result : parent::get($key, $default);
			}
		};

		$estimation = Mockery::mock(Estimation::class)->makePartial();
		$estimation->shouldReceive('getProducts')->andReturn($fakeRelation);
		$estimation->shouldReceive('getAttribute')->with('discount')->andReturn(5.0);
		$estimation->shouldReceive('getAttribute')->with('tax')->andReturn((object) ['rate' => 10]);

		$this->assertSame((100 - 5) * 0.10, $estimation->getTax());
	}

	/**
	 ** @test
	 *
	 ** getTotal() = sub-total − discount + tax.
	 **/
	public function it_calculates_total_correctly(): void
	{
		$products = collect([
			(object) ['pivot' => (object) ['price' => 25, 'quantity' => 2]], // 50
		]);

		$fakeRelation = new class($products) extends Collection {
			private Collection $result;
			public function __construct(Collection $result)
			{
				parent::__construct();
				$this->result = $result;
			}
			public function get($key = null, $default = null): mixed
			{
				return $key === null ? $this->result : parent::get($key, $default);
			}
		};

		$estimation = Mockery::mock(Estimation::class)->makePartial();
		$estimation->shouldReceive('getProducts')->andReturn($fakeRelation);
		$estimation->shouldReceive('getAttribute')->with('discount')->andReturn(5.0);
		$estimation->shouldReceive('getAttribute')->with('tax')->andReturn((object) ['rate' => 10]);

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
		// Login a real user; the service does its own _checkLogin() and
		// then $user->priceFormat($total). Seed settings so the format
		// produces a deterministic '100' string.
		\DB::table('settings')->updateOrInsert(
			['created_by' => \App\Config\Constants\DatabaseConstants::DEFAULT_UUID, 'name' => 'site_currency_symbol'],
			['user_id' => \App\Config\Constants\DatabaseConstants::DEFAULT_UUID, 'value' => '']
		);
		\DB::table('settings')->updateOrInsert(
			['created_by' => \App\Config\Constants\DatabaseConstants::DEFAULT_UUID, 'name' => 'site_currency_symbol_position'],
			['user_id' => \App\Config\Constants\DatabaseConstants::DEFAULT_UUID, 'value' => 'pre']
		);
		\DB::table('settings')->updateOrInsert(
			['created_by' => \App\Config\Constants\DatabaseConstants::DEFAULT_UUID, 'name' => 'decimal_number'],
			['user_id' => \App\Config\Constants\DatabaseConstants::DEFAULT_UUID, 'value' => '0']
		);
		\App\Models\Utility::resetSettingsCache();

		$user = \App\Models\User::factory()->create(['type' => 'company', 'lang' => 'en']);
		\Illuminate\Support\Facades\Auth::login($user);

		$a = Mockery::mock(Estimation::class)->shouldReceive('getTotal')->andReturn(40)->getMock();
		$b = Mockery::mock(Estimation::class)->shouldReceive('getTotal')->andReturn(60)->getMock();

		$summary = Estimation::getEstimationSummary([$a, $b]);

		$this->assertSame('100', $summary);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
