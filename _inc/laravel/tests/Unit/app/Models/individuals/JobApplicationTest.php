<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Job, JobApplication};

class JobApplicationTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'job', 'name', 'email', 'phone', 'profile', 'resume',
			'cover_letter', 'dob', 'gender', 'country', 'state',
			'city', 'stage', 'order', 'skill', 'rating', 'is_archive',
			'custom_question', 'created_by'
		];
		$this->assertEquals($expected, (new JobApplication())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** jobs() relation returns the associated Job instance.
	 **/
	public function jobs_relation_returns_associated_job()
	{
		$job = Job::factory()->create();
		$app = JobApplication::factory()->create(['job' => $job->id]);

		$this->assertInstanceOf(Job::class, $app->jobs);
		$this->assertEquals($job->id, $app->jobs->id);
	}
}
