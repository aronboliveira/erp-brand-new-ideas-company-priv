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
		];
		$this->assertEquals($expected, (new JobStage())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** applications() returns applications filtered by date, stage, archive, and job.
	 **/
	public function applications_filters_and_orders_correctly()
	{
		// prepare test data
		$user  = User::factory()->create();
		$stage = JobStage::factory()->create(['id' => 1]);
		$now   = Carbon::now();
		// within date range, correct stage, not archived
		$a1 = JobApplication::factory()->create([
			'created_by'     => $user?->creatorId(),
			'stage'          => 1,
			'is_archive'     => 0,
			'created_at'     => $now->subDays(1),
			'order'          => 2,
			'job'            => 5
		]);
		$a2 = JobApplication::factory()->create([
			'created_by'     => $user?->creatorId(),
			'stage'          => 1,
			'is_archive'     => 0,
			'created_at'     => $now->subDays(2),
			'order'          => 1,
			'job'            => 5
		]);
		// outside date
		JobApplication::factory()->create([
			'created_by' => $user?->creatorId(),
			'stage'      => 1,
			'is_archive' => 0,
			'created_at' => $now->subMonths(2),
			'order'      => 3,
			'job'        => 5
		]);
		// different stage
		JobApplication::factory()->create([
			'created_by' => $user?->creatorId(),
			'stage'      => 2,
			'is_archive' => 0,
			'created_at' => $now->subDays(1),
			'order'      => 4,
			'job'        => 5
		]);
		// archived
		JobApplication::factory()->create([
			'created_by' => $user?->creatorId(),
			'stage'      => 1,
			'is_archive' => 1,
			'created_at' => $now->subDays(1),
			'order'      => 5,
			'job'        => 5
		]);

		// partial mock to stub authentication
		$mock = Mockery::mock(JobStage::class . '[ _checkLogin ]')->makePartial();
		$mock->shouldReceive('_checkLogin')->andReturn($user);

		/** @var \App\Models\JobStage $stage */
		$stage = JobStage::findOrFail($stage->id);

		$filter = [
			'start_date' => '2025-01-01',
			'end_date'   => '2025-12-31',
			'job'        => 42,
		];

		$results = $stage->applications($filter);

		// should only include a2 then a1, in order of 'order'
		$this->assertCount(2, $results);
		$this->assertEquals([$a2->id, $a1->id], $results->pluck('id')->all());
	}
}
