<?php

/**
 * Unit-tests for App\Models\Plan
 */

namespace Tests\Unit\Models;

use App\Models\Plan;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

use Illuminate\Support\Facades\DB;
class PlanTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}

	use SafeAliasMock;

	/**
	 ** @test
	 *
	 ** durations() must return the exact
	 ** constant map.
	 **/
	public function durations_method_returns_expected_options(): void
	{
		$expected = [
			'lifetime'    => 'Lifetime',
			'month'       => 'Per Month',
			'semimonthly' => 'Semi Monthly',
			'quarterly'   => 'Quarterly',
			'semiannual'  => 'Semi Annual',
			'year'        => 'Per Year',
		];

		$this->assertSame($expected, Plan::durations());
	}

	/**
	 ** @test
	 *
	 ** status() should expose the translated
	 ** labels (in the test environment __()
	 ** echoes the same string).
	 **/
	public function status_method_returns_translated_labels(): void
	{
		$plan  = new Plan;
		$labels = array_values(Plan::durations());

		$this->assertSame($labels, $plan->status());
	}

	/**
	 ** @test
	 *
	 ** totalPlan() must proxy to the static
	 ** count() method.
	 **/
	public function total_plan_proxies_to_count(): void
	{
		$this->aliasMock(Plan::class)
			->shouldReceive('count')
			->once()
			->andReturn(42);

		$this->assertSame(42, Plan::totalPlan());
	}

	/**
	 ** @test
	 *
	 ** getPlan() should cache the result so
	 ** that subsequent calls avoid hitting
	 ** the DB (Plan::find called only once).
	 **/
	public function get_plan_caches_result(): void
	{
		$fake = new Plan;
		$this->aliasMock(Plan::class)
			->makePartial()
			->shouldReceive('find')
			->once()
			->with('abc')
			->andReturn($fake);

		$first = Plan::getPlan('abc');
		$second = Plan::getPlan('abc');

		$this->assertSame($fake, $first);
		$this->assertSame($first, $second);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
