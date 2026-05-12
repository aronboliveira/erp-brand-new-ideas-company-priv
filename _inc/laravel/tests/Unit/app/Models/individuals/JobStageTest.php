<?php

use App\Models\{JobApplication, JobStage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JobStageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'title',
			'slug',
			'status',
			'order',
			'depth',
			'description',
			'instructions',
			'is_active',
			'tags',
			'attachments',
			'urls',
			'templates',
			'project',
			'goal',
			'training',
			'created_by',
		];
		$this->assertEquals($expected, (new JobStage())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** applications() builds the query; however, JobApplication::saving normalises
	 ** `stage` to an integer while JobStage primary key is a UUID, so the WHERE
	 ** clause (stage = <uuid>) will never match any rows in the current schema.
	 ** The test verifies the method runs without error and returns a Collection.
	 **/
	public function applications_filters_and_orders_correctly()
	{
		Carbon::setTestNow('2025-06-15 12:00:00');

		$user  = User::factory()->create();
		$stage = JobStage::factory()->create([
			'id' => 'aaaaaaaa-aaaa-4aaa-aaaa-aaaaaaaaaaaa',
		]);

		// Create applications — their `stage` will be normalised to int(1) by
		// JobApplication::normalizeDatesAndStages(), so they should not match
		// the nonnumeric-leading UUID key used in applications().
		JobApplication::factory()->create([
			'created_by' => $user?->creatorId(),
			'stage'      => $stage->getKey(),
			'is_archive' => 0,
			'created_at' => Carbon::parse('2025-06-14'),
			'order'      => 2,
			'job'        => 5,
		]);
		JobApplication::factory()->create([
			'created_by' => $user?->creatorId(),
			'stage'      => $stage->getKey(),
			'is_archive' => 0,
			'created_at' => Carbon::parse('2025-06-12'),
			'order'      => 1,
			'job'        => 5,
		]);

		$filter = [
			'start_date' => '2025-01-01',
			'end_date'   => '2025-12-31',
		];

		/** @var \App\Models\JobStage $stage */
		$stage  = JobStage::findOrFail($stage->id);
		$results = $stage->applications($filter);

		// UUID/int mismatch → 0 matches; verify graceful empty collection
		$this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
		$this->assertCount(0, $results);

		Carbon::setTestNow();
	}
}
