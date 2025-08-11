<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\JobStage;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobApplicationNote;
use App\Models\JobOnBoard;
use App\Models\CustomQuestion;
use App\Models\GeneratedOfferLetter;
use App\Models\Designation;
use App\Models\Department;

class JobApplicationControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// create all needed permissions
		foreach ([
			'manage job application',
			'create job application',
			'show job application',
			'delete job application',
			'move job application',
			'add job application skill',
			'add job application note',
			'delete job application note',
			'edit job application',
			'archive job application',
			'manage job onBoard'
		] as $perm) {
			Permission::create(['name' => $perm]);
		}
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing index.
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'index']));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Authorized users with manage permission see the index view.
	 **/
	public function index_shows_view_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage job application');

		JobStage::factory()->create(['created_by' => $user?->creatorId()]);
		Job::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'index']));
		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.index');
		$response->assertViewHasAll(['stages', 'jobs', 'filter']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from create form.
	 **/
	public function create_redirects_guests_to_login()
	{
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'create']));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Authorized users with create permission see the create form.
	 **/
	public function create_shows_form_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create job application');

		Job::factory()->create(['created_by' => $user?->creatorId()]);
		CustomQuestion::factory()->count(2)->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'create']));
		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.create');
		$response->assertViewHasAll(['jobs', 'questions']);
	}

	/**
	 ** @test
	 **
	 ** Store creates a new application and redirects.
	 **/
	public function store_creates_application_and_redirects()
	{
		Storage::fake('uploads');
		$user = User::factory()->create();
		$user?->givePermissionTo('create job application');

		JobStage::factory()->create(['created_by' => $user?->creatorId()]);
		$job = Job::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\JobApplicationController::class, 'store']), [
			'job'   => $job->id,
			'name'  => 'Alice',
			'email' => 'alice@example.com',
			'phone' => '123456',
			// files are optional
		]);

		$response->assertRedirect(route('job-application.index'));
		$this->assertDatabaseHas('job_applications', [
			'name'       => 'Alice',
			'job'        => $job->id,
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Show displays the application details for authorized user.
	 **/
	public function show_displays_application_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('show job application');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);
		$enc = Crypt::encrypt($app->id);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'show'], ['encId' => $enc]));
		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.show');
		$response->assertViewHasAll(['jobApplication', 'notes', 'stages']);
	}

	/**
	 ** @test
	 **
	 ** Destroy deletes the application and redirects.
	 **/
	public function destroy_deletes_application_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('delete job application');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->delete(action([\App\Http\Controllers\JobApplicationController::class, 'destroy'], $app));
		$response->assertRedirect(route('job-application.index'));
		$this->assertDatabaseMissing('job_applications', ['id' => $app->id]);
	}

	/**
	 ** @test
	 **
	 ** Order updates application order and redirects back.
	 **/
	public function order_updates_order_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('move job application');

		$apps = JobApplication::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		$order = $apps->pluck('id')->reverse()->all();

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\JobApplicationController::class, 'order']), [
			'order'    => $order,
			'stage_id' => 1
		]);

		$response->assertRedirect();
		$this->assertEquals(0, JobApplication::find($order[0])->order);
	}

	/**
	 ** @test
	 **
	 ** addSkill updates the skill and redirects back.
	 **/
	public function add_skill_updates_skill_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('add job application skill');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\JobApplicationController::class, 'addSkill'], ['id' => $app->id]), [
			'skill' => 'Laravel'
		]);

		$response->assertRedirect();
		$this->assertEquals('Laravel', JobApplication::find($app->id)->skill);
	}

	/**
	 ** @test
	 **
	 ** addNote creates a note and redirects back.
	 **/
	public function add_note_creates_note_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('add job application note');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\JobApplicationController::class, 'addNote'], ['id' => $app->id]), [
			'note' => 'First note'
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('job_application_notes', [
			'application_id' => $app->id,
			'note'           => 'First note',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroyNote deletes a note and redirects back.
	 **/
	public function destroy_note_deletes_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('delete job application note');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);
		$note = JobApplicationNote::factory()->create(['application_id' => $app->id]);

		$this->actingAs($user);
		$response = $this->delete(action([\App\Http\Controllers\JobApplicationController::class, 'destroyNote'], ['id' => $note->id]));

		$response->assertRedirect();
		$this->assertDatabaseMissing('job_application_notes', ['id' => $note->id]);
	}

	/**
	 ** @test
	 **
	 ** rating updates JSON response with success.
	 **/
	public function rating_returns_success_json()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit job application');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->postJson(action([\App\Http\Controllers\JobApplicationController::class, 'rating'], ['id' => $app->id]), [
			'rating' => 5
		]);

		$response->assertJson(['success' => true]);
		$this->assertEquals(5, JobApplication::find($app->id)->rating);
	}

	/**
	 ** @test
	 **
	 ** archive toggles is_archive and redirects appropriately.
	 **/
	public function archive_toggles_archive_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('archive job application');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId(), 'is_archive' => 0]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'archive'], ['id' => $app->id]));

		$response->assertRedirect();
		$this->assertEquals(1, JobApplication::find($app->id)->is_archive);
	}

	/**
	 ** @test
	 **
	 ** candidate view shows archived applications.
	 **/
	public function candidate_shows_archived_applications()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage job onBoard');

		JobApplication::factory()->create(['created_by' => $user?->creatorId(), 'is_archive' => 1]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'candidate']));

		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.candidate');
		$response->assertViewHas('archived');
	}

	/**
	 ** @test
	 **
	 ** jobBoardCreate shows the onboard creation form.
	 **/
	public function job_board_create_shows_form()
	{
		$user = User::factory()->create();

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'jobBoardCreate'], ['id' => 1]));

		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.onboardCreate');
	}

	/**
	 ** @test
	 **
	 ** jobOnBoard shows the onboard list.
	 **/
	public function job_on_board_shows_list()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage job onBoard');

		JobOnBoard::factory()->count(2)->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'jobOnBoard']));

		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.onboard');
		$response->assertViewHas('boards');
	}

	/**
	 ** @test
	 **
	 ** jobBoardStore creates a board record and redirects.
	 **/
	public function job_board_store_creates_record_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage job onBoard');

		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\JobApplicationController::class, 'jobBoardStore'], ['id' => $app->id]), [
			'joining_date'    => now()->toDateString(),
			'job_type'        => 'Full-Time',
			'days_of_week'    => 5,
			'salary'          => 5000,
			'salary_type'     => 1,
			'salary_duration' => 'Monthly',
			'status'          => 'Active'
		]);

		$response->assertRedirect(route('job.on.board'));
		$this->assertDatabaseHas('job_on_boards', ['application' => $app->id]);
	}

	/**
	 ** @test
	 **
	 ** jobBoardUpdate updates a board record and redirects.
	 **/
	public function job_board_update_modifies_record_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage job onBoard');

		$board = JobOnBoard::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->put(action([\App\Http\Controllers\JobApplicationController::class, 'jobBoardUpdate'], ['id' => $board->id]), [
			'joining_date'    => now()->toDateString(),
			'job_type'        => 'Part-Time',
			'days_of_week'    => 3,
			'salary'          => 3000,
			'salary_type'     => 1,
			'salary_duration' => 'Weekly',
			'status'          => 'Active'
		]);

		$response->assertRedirect(route('job.on.board'));
		$this->assertDatabaseHas('job_on_boards', ['id' => $board->id, 'job_type' => 'Part-Time']);
	}

	/**
	 ** @test
	 **
	 ** jobBoardEdit shows the onboard edit form.
	 **/
	public function job_board_edit_shows_form()
	{
		$user = User::factory()->create();

		$board = JobOnBoard::factory()->create();

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'jobBoardEdit'], ['id' => $board->id]));

		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.onboardEdit');
	}

	/**
	 ** @test
	 **
	 ** jobBoardDelete deletes a board record and redirects back.
	 **/
	public function job_board_delete_removes_record_and_redirects()
	{
		$board = JobOnBoard::factory()->create();

		$response = $this->delete(action([\App\Http\Controllers\JobApplicationController::class, 'jobBoardDelete'], ['id' => $board->id]));

		$response->assertRedirect();
		$this->assertDatabaseMissing('job_on_boards', ['id' => $board->id]);
	}

	/**
	 ** @test
	 **
	 ** jobBoardConvert shows the convert form.
	 **/
	public function job_board_convert_shows_form()
	{
		$user = User::factory()->create();

		$board = JobOnBoard::factory()->create();

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'jobBoardConvert'], ['id' => $board->id]));

		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.convert');
	}

	/**
	 ** @test
	 **
	 ** jobBoardConvertData converts application and redirects.
	 **/
	public function job_board_convert_data_converts_and_redirects()
	{
		Storage::fake('uploads');
		$user = User::factory()->create();
		$user?->givePermissionTo('manage job onBoard');

		$board = JobOnBoard::factory()->create();
		$appId = $board->application;

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\JobApplicationController::class, 'jobBoardConvertData'], ['id' => $board->id]), [
			'name'           => 'Bob',
			'dob'            => '1995-05-05',
			'gender'         => 'M',
			'phone'          => '987654',
			'address'        => '45 Side St',
			'email'          => 'bob@example.com',
			'password'       => 'secret',
			'department_id'  => Department::factory()->create(['created_by' => $user?->creatorId()])->id,
			'designation_id' => Designation::factory()->create(['created_by' => $user?->creatorId()])->id,
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('employees', ['email' => 'bob@example.com']);
	}

	/**
	 ** @test
	 **
	 ** getByJob returns job JSON with exploded fields.
	 **/
	public function get_by_job_returns_job_json()
	{
		$job = Job::factory()->create(['applicant' => '1,2', 'visibility' => 'A,B', 'custom_question' => 'X,Y']);
		$response = $this->getJson(action([\App\Http\Controllers\JobApplicationController::class, 'getByJob']), ['id' => $job->id]);
		$response->assertJsonStructure(['id', 'title', 'applicant', 'visibility', 'custom_question']);
	}

	/**
	 ** @test
	 **
	 ** stageChange updates the stage and returns success JSON.
	 **/
	public function stage_change_updates_stage_and_returns_json()
	{
		$app = JobApplication::factory()->create();
		$response = $this->postJson(action([\App\Http\Controllers\JobApplicationController::class, 'stageChange']), [
			'schedule_id' => $app->id,
			'stage'       => 2
		]);
		$response->assertJson(['success' => __('Stage changed.')]);
		$this->assertEquals(2, JobApplication::find($app->id)->stage);
	}

	/**
	 ** @test
	 **
	 ** offerletterPdf renders the PDF view.
	 **/
	public function offerletter_pdf_renders_view()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('show job application');

		$tpl = GeneratedOfferLetter::factory()->create(['lang' => $user?->currentLanguage(), 'created_by' => $user?->creatorId()]);
		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'offerletterPdf'], ['id' => $app->id]));
		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.template.offerletterpdf');
	}

	/**
	 ** @test
	 **
	 ** offerletterDoc renders the DOCX view.
	 **/
	public function offerletter_doc_renders_view()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('show job application');

		$tpl = GeneratedOfferLetter::factory()->create(['lang' => $user?->currentLanguage(), 'created_by' => $user?->creatorId()]);
		$app = JobApplication::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\JobApplicationController::class, 'offerletterDoc'], ['id' => $app->id]));
		$response->assertStatus(200);
		$response->assertViewIs('jobApplication.template.offerletterdocx');
	}
}
