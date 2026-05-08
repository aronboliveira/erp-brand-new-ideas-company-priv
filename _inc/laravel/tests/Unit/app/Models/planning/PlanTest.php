<?php

/**
 * Unit-tests for App\Models\Plan
 */

namespace Tests\Unit\Models;

use App\Models\Plan;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class PlanTest extends TestCase
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
		// Plan::totalPlan() simply proxies to self::count() — seed real
		// rows and assert the count instead of aliasMocking the class.
		$baseline = Plan::count();
		Plan::factory()->count(3)->create();
		$this->assertSame($baseline + 3, Plan::totalPlan());
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
		// Plan::getPlan() caches in self::$cachedPlan; seed a real plan,
		// reset the cache, and verify both calls return the same instance.
		$ref = new \ReflectionClass(Plan::class);
		if ($ref->hasProperty('cachedPlan')) {
			$prop = $ref->getProperty('cachedPlan');
			$prop->setAccessible(true);
			$prop->setValue(null, null);
		}
		$plan = Plan::factory()->create();

		$first  = Plan::getPlan($plan->id);
		$second = Plan::getPlan($plan->id);

		$this->assertNotNull($first);
		$this->assertSame($plan->id, $first->id);
		$this->assertSame($first, $second, 'Second call should return cached instance');
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
