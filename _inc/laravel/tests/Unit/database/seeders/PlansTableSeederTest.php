<?php

namespace Tests\Unit\Seeders;

use Tests\TestCase;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Carbon, Str};
use Database\Seeders\PlansTableSeeder;

class PlansTableSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
        \DB::table('plans')->truncate();
    }
	use RefreshDatabase;

	/**
	 ** @test
	 *
	 ** Seeds a single "Free Plan" record with the expected attributes,
	 ** generates a valid UUID and sets the timestamps to now.
	 **/
	public function it_seeds_a_free_plan_with_valid_uuid_and_timestamps()
	{
		// Freeze time for predictable timestamps
		$now = Carbon::create(2025, 6, 10, 12, 0, 0);
		Carbon::setTestNow($now);

		// Ensure no plans exist before running
		$this->assertDatabaseCount('plans', 0);

		// Run the seeder
		(new PlansTableSeeder())->run();

		// The free plan should exist among the seeded plans
		$plan = Plan::where('name', 'Free')->first();
		$this->assertNotNull($plan, 'Free plan should exist after seeding');

		// ID should be a valid UUID
		$this->assertTrue(Str::isUuid($plan->id), "Plan ID {$plan->id} is not a valid UUID");

		// Core attributes
		$this->assertEquals('Free', $plan->name);
		$this->assertEquals(0, $plan->price);
		$this->assertEquals('lifetime', $plan->duration);
		$this->assertEquals(5, $plan->max_users);
		$this->assertEquals(5, $plan->max_customers);
		$this->assertEquals(5, $plan->max_vendors);
		$this->assertEquals(5, $plan->max_clients);
		$this->assertEquals(1024, $plan->storage_limit);
		$this->assertTrue((bool) $plan->crm);
		$this->assertTrue((bool) $plan->hrm);
		$this->assertTrue((bool) $plan->account);
		$this->assertTrue((bool) $plan->project);
		$this->assertTrue((bool) $plan->pos);
		$this->assertTrue((bool) $plan->chatgpt);
		$this->assertEquals('plans/free_plan.png', $plan->image);

		// Timestamps should match the frozen "now"
		$this->assertEquals($now->toDateTimeString(), $plan->created_at->toDateTimeString());
		$this->assertEquals($now->toDateTimeString(), $plan->updated_at->toDateTimeString());
	}

	/**
	 ** @test
	 *
	 ** Marks incomplete: cannot simulate exceeding the UUID retry limit in unit tests.
	 **/
	public function it_marks_uuid_retry_limit_as_incomplete()
	{
		$this->markTestIncomplete('Cannot simulate over 100,000 duplicate UUID attempts in a reasonable unit test.');
	}
}
