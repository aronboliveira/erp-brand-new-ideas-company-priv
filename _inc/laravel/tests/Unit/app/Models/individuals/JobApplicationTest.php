<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Job, JobApplication};

class JobApplicationTest extends TestCase
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
			'job',
			'applicant_id',
			'name',
			'email',
			'phone',
			'source',
			'announcement',
			'country',
			'state',
			'city',
			'address',
			'zip',
			'ip',
			'dei_category',
			'applied_at',
			'last_reviewed_at',
			'referrer_id',
			'referrer_name',
			'referrer_email',
			'expected_salary',
			'expected_salary_currency',
			'current_employer',
			'current_position',
			'current_salary',
			'current_salary_currency',
			'work_authorization',
			'work_authorization_approved',
			'notice_period',
			'next_interview_at',
			'interview_notes',
			'interview_note_id',
			'interview_scores',
			'profile',
			'profile_document',
			'portfolio',
			'website',
			'resume',
			'resume_document',
			'cover_letter',
			'cover_letter_document',
			'dob',
			'gender',
			'experience',
			'experience_document',
			'education',
			'education_document',
			'stage',
			'order',
			'skill',
			'skill_document',
			'rating',
			'rejection_reason',
			'rejected_at',
			'feedback',
			'feedback_document',
			'is_archive',
			'custom_question',
			'custom_question_id',
			'terms_accepted',
			'certifications',
			'awards',
			'publications',
			'projects',
			'languages',
			'references',
			'diversity',
			'disabilities',
			'social_media',
			'questions',
			'tests',
			'notes',
			'attachments',
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
