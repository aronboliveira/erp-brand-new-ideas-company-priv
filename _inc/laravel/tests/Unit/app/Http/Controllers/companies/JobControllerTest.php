<?php

namespace Tests\Feature;

use App\Models\{
	Branch,
	CustomQuestion,
	Job,
	JobApplication,
	JobApplicationNote,
	JobCategory,
	JobStage,
	User
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{
	DB,
	Gate
};
use Tests\TestCase;

class JobControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Grant all permissions
		Gate::before(fn () => true);

		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 */
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);
	}

	/**
	 ** @test
	 **
	 ** The index action should display counts of active, inactive, and total jobs
	 ** for the authenticated user, and list only that user's jobs.
	 **/
	public function index_displays_counts_and_job_list()
	{
		$user = User::factory()->create();
		Job::factory()->count(2)->create([
			'created_by' => $user?->creatorId(),
			'status'     => 'active',
		]);
		Job::factory()->count(1)->create([
			'created_by' => $user?->creatorId(),
			'status'     => 'in_active',
		]);
		Job::factory()->count(1)->create([
			'created_by' => User::factory()->create()->id,
		]);

		$response = $this->actingAs($user)->get(route('job.index'));

		$response->assertStatus(200)
			->assertViewIs('job.index')
			->assertViewHas('data', function ($data) {
				return $data['active'] === 2
					&& $data['inActive'] === 1
					&& $data['total'] === 3;
			})
			->assertViewHas('jobs', fn ($jobs) => $jobs->count() === 3);
	}

	/**
	 ** @test
	 **
	 ** The create form should display branches, categories,
	 ** custom questions, and status options for job creation.
	 **/
	public function create_form_shows_branches_categories_and_questions()
	{
		$user = User::factory()->create();
		$b = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$c = JobCategory::factory()->create(['created_by' => $user?->creatorId()]);
		$q = CustomQuestion::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('job.create'));

		$response->assertStatus(200)
			->assertViewIs('job.create')
			->assertViewHasAll([
				'branches', 'categories', 'customQuestion', 'status'
			])
			->assertViewHas('branches', fn ($list) => array_key_exists($b->id, $list))
			->assertViewHas('categories', fn ($list) => array_key_exists($c->id, $list->toArray()))
			->assertViewHas('customQuestion', fn ($list) => $list->pluck('id')->contains($q->id));
	}

	/**
	 ** @test
	 **
	 ** Storing a job with valid data should create the job record
	 ** and redirect back to the index.
	 **/
	public function store_validates_and_creates_job()
	{
		$user    = User::factory()->create();
		$branch  = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$category = JobCategory::factory()->create(['created_by' => $user?->creatorId()]);
		$question = CustomQuestion::factory()->create(['created_by' => $user?->creatorId()]);

		$payload = [
			'title'          => 'Dev Position',
			'branch'         => $branch->id,
			'category'       => $category->id,
			'description'    => 'Job desc',
			'startDate'      => now()->toDateString(),
			'endDate'        => now()->addWeek()->toDateString(),
			'position'       => 3,
			'requirement'    => 'Reqs',
			'skill'          => ['PHP', 'Laravel'],
			'visibility'     => ['all'],
			'applicant'      => ['1', '2'],
			'customQuestion' => [$question->id],
			'status'         => 'active',
		];

		$response = $this->actingAs($user)
			->post(route('job.store'), $payload);

		$response->assertRedirect(route('job.index'));
		$this->assertDatabaseHas('jobs', [
			'title'      => 'Dev Position',
			'branch'     => $branch->id,
			'category'   => $category->id,
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** If validation fails on store, the user should
	 ** be redirected back with an error message.
	 **/
	public function store_redirects_back_on_validation_error()
	{
		$user = User::factory()->create();

		// Missing all required fields
		$response = $this->actingAs($user)
			->post(route('job.store'), []);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** The show action should display the job detail view,
	 ** splitting comma-separated fields into arrays.
	 **/
	public function show_displays_job_with_exploded_fields()
	{
		$user = User::factory()->create();
		$job = Job::factory()->create([
			'created_by'      => $user?->creatorId(),
			'applicant'       => 'a,b',
			'custom_question' => 'x,y',
			'skill'           => 's,t',
			'visibility'      => 'v,w',
			'status'          => 'active',
		]);

		$response = $this->actingAs($user)->get(route('job.show', $job));

		$response->assertStatus(200)
			->assertViewIs('job.show')
			->assertViewHas(
				'job',
				fn ($j) =>
				is_array($j->applicant)
					&& is_array($j->customQuestion)
					&& is_array($j->skill)
					&& is_array($j->visibility)
			);
	}

	/**
	 ** @test
	 **
	 ** The edit form should only be accessible by the job owner;
	 ** others should be redirected or denied.
	 **/
	public function edit_displays_form_for_owner_only()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$job1 = Job::factory()->create(['created_by' => $user?->creatorId()]);
		$job2 = Job::factory()->create(['created_by' => $other->id]);

		// Owner may edit
		$ok = $this->actingAs($user)->get(route('job.edit', $job1));
		$ok->assertStatus(200)->assertViewIs('job.edit');

		// Non-owner gets redirected/denied
		$denied = $this->actingAs($user)->get(route('job.edit', $job2));
		$denied->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Updating a job with valid data should persist
	 ** the changes and redirect back to the index.
	 **/
	public function update_validates_and_updates_job()
	{
		$user    = User::factory()->create();
		$branch  = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$category = JobCategory::factory()->create(['created_by' => $user?->creatorId()]);
		$job     = Job::factory()->create([
			'created_by' => $user?->creatorId(),
			'title'      => 'OldTitle',
		]);

		$payload = [
			'title'          => 'NewTitle',
			'branch'         => $branch->id,
			'category'       => $category->id,
			'description'    => 'Desc',
			'startDate'      => now()->toDateString(),
			'endDate'        => now()->toDateString(),
			'position'       => 1,
			'requirement'    => 'Req',
			'skill'          => ['X'],
			'visibility'     => ['Y'],
			'applicant'      => ['Z'],
			'customQuestion' => [],
			'status'         => $job->status,
		];

		$response = $this->actingAs($user)
			->put(route('job.update', $job), $payload);

		$response->assertRedirect(route('job.index'));
		$this->assertDatabaseHas('jobs', [
			'id'    => $job->id,
			'title' => 'NewTitle',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroying a job should remove it and all
	 ** related applications and notes from the database.
	 **/
	public function destroy_deletes_job_and_related_applications()
	{
		$user = User::factory()->create();
		$job = Job::factory()->create(['created_by' => $user?->creatorId()]);
		$app = JobApplication::factory()->create(['job' => $job->id]);
		JobApplicationNote::factory()->create(['application_id' => $app->id]);

		$response = $this->actingAs($user)
			->delete(route('job.destroy', $job));

		$response->assertRedirect(route('job.index'));
		$this->assertDatabaseMissing('jobs', ['id' => $job->id]);
		$this->assertDatabaseMissing('job_applications', ['id' => $app->id]);
		$this->assertDatabaseCount('job_application_notes', 0);
	}

	/**
	 ** @test
	 **
	 ** The career action should show only the public jobs
	 ** for the specified company and language.
	 **/
	public function career_displays_public_job_list()
	{
		$user = User::factory()->create();
		$job1 = Job::factory()->create(['created_by' => $user?->id]);
		Job::factory()->count(2)->create(['created_by' => User::factory()->create()->id]);

		$response = $this->get(route('job.career', ['companyId' => $user?->id, 'lang' => 'en']));

		$response->assertStatus(200)
			->assertViewIs('job.career')
			->assertViewHas('jobs', fn ($jobs) => $jobs->pluck('id')->all() === [$job1->id]);
	}

	/**
	 ** @test
	 **
	 ** The jobRequirement action redirects back with an error
	 ** if the job is inactive.
	 **/
	public function job_requirement_redirects_if_inactive()
	{
		$job = Job::factory()->create(['status' => 'in_active']);
		$response = $this->get(route('job.jobRequirement', ['code' => $job->code, 'lang' => 'en']));

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** The jobRequirement action displays the requirement view
	 ** when the job is active.
	 **/
	public function job_requirement_displays_view_if_active()
	{
		$job = Job::factory()->create(['status' => 'active']);
		$response = $this->get(route('job.jobRequirement', ['code' => $job->code, 'lang' => 'en']));

		$response->assertStatus(200)
			->assertViewIs('job.requirement');
	}

	/**
	 ** @test
	 **
	 ** The jobApply action should render the application form
	 ** with necessary data like questions and settings.
	 **/
	public function job_apply_displays_form()
	{
		$job = Job::factory()->create();
		$response = $this->get(route('job.jobApply', ['code' => $job->code, 'lang' => 'en']));

		$response->assertStatus(200)
			->assertViewIs('job.apply')
			->assertViewHasAll(['job', 'questions', 'languages', 'settings']);
	}

	/**
	 ** @test
	 **
	 ** The jobApplyData action should validate input and
	 ** create a new job application record.
	 **/
	public function job_apply_data_validates_and_creates_application()
	{
		$user = User::factory()->create();
		$job = Job::factory()->create(['created_by' => $user?->creatorId()]);
		JobStage::factory()->create(['created_by' => $job->created_by]);

		$payload = [
			'name'  => 'Applicant',
			'email' => 'app@example.com',
			'phone' => '123456',
		];

		$response = $this->actingAs($user)
			->post(route('job.jobApplyData', ['code' => $job->code]), $payload);

		$response->assertRedirect();
		$this->assertDatabaseHas('job_applications', [
			'email' => 'app@example.com',
			'job'   => $job->id,
		]);
	}
}
