<?php

namespace Tests\Feature\Controllers;

use App\Models\{User, Note, Task, Email, ActivityLog, Schedule};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ActivityControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		// Disable actual authorization checks
		Gate::shouldReceive('allows')->andReturnTrue();
	}

	private function loginUserWithPermission(string $permission): User
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		Gate::define($permission, fn () => true);
		return $user;
	}

	/**
	 ** @test
	 **
	 ** Ensure that accessing the index endpoint without the
	 ** 'view CRM activity' permission returns a 403 Forbidden response.
	 **/
	public function test_index_requires_permission(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		Gate::define('view CRM activity', fn () => false);
		$response = $this->get(route('activity.index'));
		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** When the user has 'view CRM activity' permission and there are
	 ** notes, tasks, emails, activity logs, and schedules in the system,
	 ** the index endpoint should render the CRM activity view.
	 **/
	public function test_index_returns_view(): void
	{
		$user = $this->loginUserWithPermission('view CRM activity');

		Note::factory()->create(['created_by' => $user?->creatorId()]);
		Task::factory()->create(['created_by' => $user?->creatorId()]);
		Email::factory()->create(['created_by' => $user?->creatorId()]);
		ActivityLog::factory()->create(['created_by' => $user?->creatorId()]);
		Schedule::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->get(route('activity.index'));
		$response->assertStatus(200);
		$response->assertViewIs('crm.activity.view');
	}

	/**
	 ** @test
	 **
	 ** The notes endpoint should return a JSON array of the user's notes,
	 ** each containing 'note' and 'createdAt' fields.
	 **/
	public function test_notes_returns_json(): void
	{
		$user = $this->loginUserWithPermission('view CRM activity');

		Note::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		$response = $this->getJson(route('activity.notes'));
		$response->assertOk()->assertJsonStructure([['note', 'createdAt']]);
	}

	/**
	 ** @test
	 **
	 ** The tasks endpoint should return a JSON array of the user's tasks,
	 ** each containing 'note', 'agentOrManager', and 'createdAt' fields.
	 **/
	public function test_tasks_returns_json(): void
	{
		$user = $this->loginUserWithPermission('view CRM activity');

		Task::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		$response = $this->getJson(route('activity.tasks'));
		$response->assertOk()->assertJsonStructure([['note', 'agentOrManager', 'createdAt']]);
	}

	/**
	 ** @test
	 **
	 ** The emails endpoint should return a JSON array of the user's emails,
	 ** each containing 'note', 'email', and 'createdAt' fields.
	 **/
	public function test_emails_returns_json(): void
	{
		$user = $this->loginUserWithPermission('view CRM activity');

		Email::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		$response = $this->getJson(route('activity.emails'));
		$response->assertOk()->assertJsonStructure([['note', 'email', 'createdAt']]);
	}

	/**
	 ** @test
	 **
	 ** The logActivities endpoint should return a JSON array of the user's
	 ** activity logs, each containing 'note', 'type', 'startDate', and 'createdAt'.
	 **/
	public function test_log_activities_returns_json(): void
	{
		$user = $this->loginUserWithPermission('view CRM activity');

		ActivityLog::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		$response = $this->getJson(route('activity.logActivities'));
		$response->assertOk()->assertJsonStructure([['note', 'type', 'startDate', 'createdAt']]);
	}

	/**
	 ** @test
	 **
	 ** The schedules endpoint should return a JSON array of the user's schedules,
	 ** each containing 'note', 'type', 'startDate', and 'createdAt' fields.
	 **/
	public function test_schedules_returns_json(): void
	{
		$user = $this->loginUserWithPermission('view CRM activity');

		Schedule::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		$response = $this->getJson(route('activity.schedules'));
		$response->assertOk()->assertJsonStructure([['note', 'type', 'startDate', 'createdAt']]);
	}
}
