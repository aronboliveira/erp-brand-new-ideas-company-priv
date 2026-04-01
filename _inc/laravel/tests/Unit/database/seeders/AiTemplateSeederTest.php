<?php

namespace Tests\Unit\Seeders;

use Tests\TestCase;
use App\{Config\Constants\SeedersTemplating, Models\Template};
use Database\Seeders\AiTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Carbon, Str};

class AiTemplateSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 *
	 ** Seeds the correct number of AI templates into the database,
	 ** each with a valid UUID and matching created_at/updated_at timestamps.
	 **/
	public function it_seeds_default_ai_templates()
	{
		// Freeze time so timestamps are consistent
		$now = Carbon::create(2025, 6, 10, 12, 0, 0);
		Carbon::setTestNow($now);

		// Run the seeder
		(new AiTemplateSeeder())->run();

		// Get the expected templates data
		$defaults = SeedersTemplating::aiDefaultTemplate();
		$expectedCount = count($defaults);

		// Assert the correct number of records were inserted
		$this->assertDatabaseCount('templates', $expectedCount);

		// Fetch all inserted templates
		$records = Template::all();

		// Collect all IDs and ensure no duplicates
		$ids = $records->pluck('id')->toArray();
		$this->assertCount($expectedCount, array_unique($ids));

		foreach ($records as $record) {
			// IDs should be valid UUIDs
			$this->assertTrue(Str::isUuid($record->id), "Record ID {$record->id} is not a valid UUID");

			// created_at and updated_at should equal our frozen “now”
			$this->assertEquals($now->toDateTimeString(), $record->created_at->toDateTimeString());
			$this->assertEquals($now->toDateTimeString(), $record->updated_at->toDateTimeString());
		}
	}

	/**
	 ** @test
	 *
	 ** Marks incomplete: cannot reliably trigger the UUID-retry limit in unit tests.
	 **/
	public function it_marks_uuid_generation_limit_as_incomplete()
	{
		$this->markTestIncomplete('Cannot simulate >100,000 duplicate UUID attempts in a reasonable unit test.');
	}
}
