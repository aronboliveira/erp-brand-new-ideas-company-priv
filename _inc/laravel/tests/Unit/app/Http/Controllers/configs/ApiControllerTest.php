<?php

namespace Tests\Feature;

use App\Models\{
	Project,
	ProjectTask,
	ProjectUser,
	TimeTracker,
	User
};
use Illuminate\Support\{Facades\Hash, Str};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** When valid credentials are provided, login should return a JSON
	 ** payload containing a token, the user ID, and settings.
	 **/
	public function login_returns_token_and_user_id_on_valid_credentials()
	{
		$user = User::factory()->create([
			'email'    => 'test@example.com',
			'password' => Hash::make('secret'),
		]);

		$response = $this->postJson('/api/login', [
			'email'    => 'test@example.com',
			'password' => 'secret',
		]);

		$response->assertStatus(200)
			->assertJsonStructure([
				'data' => ['token', 'userId', 'settings'],
				'message'
			])
			->assertJsonPath('data.userId', $user?->id);
	}

	/**
	 ** @test
	 **
	 ** If credentials don’t match, login should return a 401 and an
	 ** appropriate error message.
	 **/
	public function login_returns_401_on_invalid_credentials()
	{
		User::factory()->create([
			'email'    => 'user@example.com',
			'password' => Hash::make('password'),
		]);

		$response = $this->postJson('/api/login', [
			'email'    => 'user@example.com',
			'password' => 'wrong',
		]);

		$response->assertStatus(401)
			->assertJson([
				'error' => 'Credentials do not match'
			]);
	}

	/**
	 ** @test
	 **
	 ** Calling logout should revoke all of the user’s tokens and return
	 ** a success message.
	 **/
	public function logout_revokes_all_tokens()
	{
		$user = User::factory()->create();
		$token = $user?->createToken('API Token')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer {$token}")
			->postJson('/api/logout');

		$response->assertStatus(200)
			->assertJson(['message' => 'Tokens revoked']);

		$this->assertCount(0, $user?->tokens);
	}

	/**
	 ** @test
	 **
	 ** A “company” user should see only the projects they created.
	 **/
	public function get_projects_for_company_returns_owned_projects()
	{
		$user = User::factory()->create(['type' => 'company']);
		Project::factory()->count(2)->create(['created_by' => $user?->id]);
		Project::factory()->count(1)->create(); // another company’s project

		$token = $user?->createToken('t')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer {$token}")
			->getJson('/api/projects');

		$response->assertStatus(200)
			->assertJsonCount(2, 'data.projects');
	}

	/**
	 ** @test
	 **
	 ** A non-company user should see only projects they are assigned to.
	 **/
	public function get_projects_for_non_company_returns_assigned_projects()
	{
		$user = User::factory()->create(['type' => 'employee']);
		$proj = Project::factory()->create();
		ProjectUser::factory()->create([
			'user_id'    => $user?->id,
			'project_id' => $proj->id,
		]);
		Project::factory()->create(); // unassigned

		$token = $user?->createToken('t')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer {$token}")
			->getJson('/api/projects');

		$response->assertStatus(200)
			->assertJsonCount(1, 'data.projects')
			->assertJsonPath('data.projects.0.id', $proj->id);
	}

	/**
	 ** @test
	 **
	 ** Posting { action: 'start', taskId, … } should create a new active
	 ** time tracker and return its details.
	 **/
	public function add_tracker_start_creates_and_returns_tracker()
	{
		$user = User::factory()->create();
		$task = ProjectTask::factory()->create([
			'project_id' => Project::factory()->create()->id,
		]);

		$token = $user?->createToken('t')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer {$token}")
			->postJson('/api/tracker', [
				'action'     => 'start',
				'taskId'     => $task->id,
				'workOn'     => 'Coding',
				'isBillable' => 1,
			]);

		$response->assertStatus(200)
			->assertJsonPath('data.action', 'start')
			->assertJsonPath('data.task_id', $task->id);

		$this->assertDatabaseHas('time_trackers', [
			'task_id'    => $task->id,
			'created_by' => $user?->id,
			'is_active'  => 1,
		]);
	}

	/**
	 ** @test
	 **
	 ** Posting { action: 'stop', trackerId } should mark the tracker inactive,
	 ** update its record, and return the updated tracker.
	 **/
	public function add_tracker_stop_updates_and_returns_tracker()
	{
		$user = User::factory()->create();
		$tracker = TimeTracker::factory()->create([
			'created_by' => $user?->id,
			'is_active'  => 1,
			'start_time' => now()->subHour(),
		]);

		$token = $user?->createToken('t')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer {$token}")
			->postJson('/api/tracker', [
				'action'    => 'stop',
				'trackerId' => $tracker->id,
			]);

		$response->assertStatus(200)
			->assertJsonPath('data.is_active', 0)
			->assertJsonPath('data.id', $tracker->id);

		$this->assertDatabaseHas('time_trackers', [
			'id'        => $tracker->id,
			'is_active' => 0,
		]);
	}

	/**
	 ** @test
	 **
	 ** Uploading an image (base64 + metadata) should store the file under
	 ** uploads/trackerImages/{trackerId}/ and record its path in track_photos.
	 **/
	public function upload_image_saves_file_and_records_photo()
	{
		$user     = User::factory()->create();
		$trackerId = (string) Str::uuid();
		$token    = $user?->createToken('t')->plainTextToken;
		$contents = base64_encode('dummy-image-data');

		$response = $this->withHeader('Authorization', "Bearer {$token}")
			->postJson('/api/upload-image', [
				'img'       => $contents,
				'imgName'   => 'pic.png',
				'trackerId' => $trackerId,
				'time'      => now()->toDateTimeString(),
			]);

		$response->assertStatus(200)
			->assertJsonStructure(['data' => ['id', 'img_path'], 'message']);

		// verify database record
		$this->assertDatabaseHas('track_photos', [
			'track_id' => $trackerId,
			'user_id'  => $user?->id,
			'img_path' => "uploads/trackerImages/{$trackerId}/pic.png",
		]);

		// verify file exists
		$this->assertFileExists(
			storage_path("uploads/trackerImages/{$trackerId}/pic.png")
		);
	}
}
