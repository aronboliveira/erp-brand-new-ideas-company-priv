<?php

namespace Tests\Unit\Controllers;

use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Gate, Log};
use Spatie\Permission\Models\Permission;
use App\Models\{
	Customer,
	Project,
	ProjectUser,
	User,
	Utility,
	ZoomMeeting
};
use App\Http\Controllers\ZoomMeetingController;

class ZoomMeetingControllerTest extends TestCase
{
	use RefreshDatabase;
	private User $user;
	private ZoomMeeting $meeting;
	private Project $project;
	private Customer $client;

	protected function setUp(): void
	{
		parent::setUp();
		Permission::create(['name' => 'view zoom meeting']);
		Permission::create(['name' => 'create zoom meeting']);
		Permission::create(['name' => 'delete zoom meeting']);
		// allow all gates by default
		Gate::before(fn () => true);

		// let creatorId() just return id
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		// silence logs and DB transactions
		Log::spy();
		DB::shouldReceive('beginTransaction')->andReturnTrue();
		DB::shouldReceive('commit')->andReturnTrue();
		DB::shouldReceive('rollBack')->andReturnTrue();

		// create a company user and act as them
		$this->user = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->user);

		// set up a project and a client for the meeting
		$this->project = Project::factory()->create(['created_by' => $this->user->creatorId()]);
		$this->client = Customer::factory()->create(['created_by' => $this->user->creatorId()]);

		// create a ZoomMeeting owned by this user
		$this->meeting = ZoomMeeting::factory()->create([
			'created_by' => $this->user->creatorId(),
			'project_id' => $this->project->id,
			'client_id'  => $this->client->id,
		]);

		// create the “create time tracker” permission name for guard 
		Permission::create(['name' => 'create zoom meeting']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the index route.
	 **/
	public function guest_redirected_to_login()
	{
		$response = $this->get(action([ZoomMeetingController::class, 'index']));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the view permission are redirected from index.
	 **/
	public function user_without_permission_cannot_access_index()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(action([ZoomMeetingController::class, 'index']));
		$response->assertRedirect(route('zoom-meeting.index'));
	}

	/**
	 ** @test
	 **
	 ** Users with view permission can see the index and their meetings.
	 **/
	public function user_with_view_permission_can_view_index()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('view zoom meeting');

		ZoomMeeting::factory()->create([
			'created_by' => $user?->creatorId(),
			'title'      => 'Test Meeting',
		]);

		$this->actingAs($user);

		$response = $this->get(action([ZoomMeetingController::class, 'index']));
		$response->assertStatus(200);
		$response->assertViewIs('zoom-meeting.index');
		$response->assertViewHas('meetings', function ($meetings) {
			return $meetings->first()->title === 'Test Meeting';
		});
	}

	/**
	 ** @test
	 **
	 ** Users with create permission can access the create form.
	 **/
	public function user_with_create_permission_can_view_create()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create zoom meeting');
		$this->actingAs($user);

		$response = $this->get(action([ZoomMeetingController::class, 'create']));
		$response->assertStatus(200);
		$response->assertViewIs('zoom-meeting.create');
	}

	/**
	 ** @test
	 **
	 ** Users without create permission are redirected from create.
	 **/
	public function user_without_create_permission_cannot_view_create()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(action([ZoomMeetingController::class, 'create']));
		$response->assertRedirect(route('zoom-meeting.index'));
	}

	/**
	 ** @test
	 **
	 ** Owners can view a single meeting via the show action.
	 **/
	public function owner_can_view_show()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('view zoom meeting');

		$meeting = ZoomMeeting::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);

		$response = $this->get(action([ZoomMeetingController::class, 'show'], $meeting));
		$response->assertStatus(200);
		$response->assertViewIs('zoom-meeting.show');
		$response->assertViewHas('zoomMeeting', $meeting);
	}

	/**
	 ** @test
	 **
	 ** Non-owners are redirected when trying to view another user's meeting.
	 **/
	public function non_owner_cannot_view_show()
	{
		$owner  = User::factory()->create();
		$other  = User::factory()->create();
		$other->givePermissionTo('view zoom meeting');

		$meeting = ZoomMeeting::factory()->create([
			'created_by' => $owner->creatorId(),
		]);

		$this->actingAs($other);

		$response = $this->get(action([ZoomMeetingController::class, 'show'], $meeting));
		$response->assertRedirect(route('zoom-meeting.index'));
	}

	/**
	 ** @test
	 **
	 ** Owners can delete their own meetings.
	 **/
	public function owner_can_delete_meeting()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('delete zoom meeting');

		$meeting = ZoomMeeting::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);

		$response = $this->delete(action([ZoomMeetingController::class, 'destroy'], $meeting));
		$response->assertRedirect(route('zoom-meeting.index'));
		$this->assertDatabaseMissing('zoom_meetings', ['id' => $meeting->id]);
	}

	/**
	 ** @test
	 **
	 ** Non-owners cannot delete meetings they do not own.
	 **/
	public function non_owner_cannot_delete_meeting()
	{
		$owner  = User::factory()->create();
		$other  = User::factory()->create();
		$other->givePermissionTo('delete zoom meeting');

		$meeting = ZoomMeeting::factory()->create([
			'created_by' => $owner->creatorId(),
		]);

		$this->actingAs($other);

		$response = $this->delete(action([ZoomMeetingController::class, 'destroy'], $meeting));
		$response->assertRedirect(route('zoom-meeting.index'));
	}

	/**
	 ** @test
	 **
	 ** projectWiseUser returns a JSON array of users for a given project.
	 **/
	public function project_wise_user_returns_users_array()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('view zoom meeting');

		$project = Project::factory()->create([
			'created_by' => $user?->creatorId(),
		]);
		$member = User::factory()->create([
			'created_by' => $user?->creatorId(),
		]);
		ProjectUser::factory()->create([
			'project_id' => $project->id,
			'user_id'    => $member->id,
		]);

		$this->actingAs($user);

		$response = $this->getJson(action([ZoomMeetingController::class, 'projectWiseUser'], ['projectId' => $project->id]));
		$response->assertStatus(200)
			->assertJsonFragment(['id' => $member->id, 'name' => $member->name]);
	}

	/**
	 ** @test
	 **
	 ** statusUpdate returns a success JSON when permitted.
	 **/
	public function status_update_returns_success_json()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('view zoom meeting');

		ZoomMeeting::factory()->create([
			'meeting_id' => 999,
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);

		$response = $this->postJson(action([ZoomMeetingController::class, 'statusUpdate']));
		$response->assertStatus(200)
			->assertJson(['success' => true]);
	}

	/**
	 ** @test
	 **
	 ** getZoomMeetingData returns local meetings JSON.
	 **/
	public function get_zoom_meeting_data_returns_local_json()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('view zoom meeting');

		ZoomMeeting::factory()->create([
			'created_by' => $user?->creatorId(),
			'title'      => 'Local Meeting',
			'start_date' => '2025-05-14',
		]);

		$this->actingAs($user);

		$response = $this->getJson(action([ZoomMeetingController::class, 'getZoomMeetingData']), ['calendarType' => '']);
		$response->assertStatus(200)
			->assertJsonFragment(['title' => 'Local Meeting']);
	}

	/**
	 ** @test
	 **
	 ** calendar returns the calendar view with events for authorized users.
	 **/
	public function calendar_returns_view_with_events()
	{
		$user = User::factory()->create(['type' => 'company']);
		$user?->givePermissionTo('view zoom meeting');

		ZoomMeeting::factory()->create([
			'created_by' => $user?->creatorId(),
			'title'      => 'Event',
			'start_date' => '2025-05-14',
			'end_date'   => '2025-05-14',
		]);

		$this->actingAs($user);

		$response = $this->get(action([ZoomMeetingController::class, 'calendar']));
		$response->assertStatus(200);
		$response->assertViewIs('zoom-meeting.calendar');
		$response->assertViewHasAll(['calandar', 'zoomMeetings', 'transdate']);
	}

	/** @test
	 ** edit(): owner with permission sees the edit form populated.
	 **/
	public function owner_with_permission_can_view_edit_form()
	{
		$this->user->givePermissionTo('create zoom meeting');

		$response = $this->get(route('zoom-meeting.edit', $this->meeting));

		$response->assertOk()
			->assertViewIs('zoom-meeting.edit')
			->assertViewHasAll([
				'zoomMeeting', 'projects', 'users', 'settings'
			])
			->assertViewHas('zoomMeeting', fn ($m) => $m->id === $this->meeting->id);
	}

	/** @test
	 ** edit(): non-owner is denied with 403.
	 **/
	public function non_owner_gets_403_on_edit()
	{
		$other = User::factory()->create();
		$other->givePermissionTo('create zoom meeting');
		$this->actingAs($other);

		$response = $this->get(route('zoom-meeting.edit', $this->meeting));

		$response->assertStatus(403);
	}

	/** @test
	 ** update(): invalid input redirects back with first error.
	 **/
	public function update_validation_failure_redirects_back_with_error()
	{
		$this->user->givePermissionTo('create zoom meeting');

		$response = $this->put(
			route('zoom-meeting.update', $this->meeting),
			['title' => ''] // missing required fields
		);

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test
	 ** update(): missing Zoom API credentials redirects back with error.
	 **/
	public function update_without_api_credentials_shows_error()
	{
		$this->user->givePermissionTo('create zoom meeting');

		// ensure no zoom_* settings exist
		Utility::settingsById($this->user->creatorId()); // loads nothing by default

		$payload = [
			'title'           => 'New Title',
			'startDate'       => now()->toDateString(),
			'duration'        => 30,
			'password'        => 'secret',
			'projectId'       => $this->project->id,
			'userIds'         => [$this->user->id],
			'clientId'        => $this->client->id,
			'synchronizeType' => '',
		];

		$response = $this->put(
			route('zoom-meeting.update', $this->meeting),
			$payload
		);

		$response->assertRedirect()
			->assertSessionHas('error', __('Zoom API credentials not configured.'));
	}

	/** @test
	 ** update(): valid input and credentials calls Zoom API, saves, and redirects success.
	 **/
	public function update_successful_flow_saves_changes_and_redirects()
	{
		$this->user->givePermissionTo('create zoom meeting');

		// seed the three required zoom_* settings
		DB::table('settings')->insert([
			['name' => 'zoom_account_id',    'value' => 'acct', 'created_by' => $this->user->creatorId()],
			['name' => 'zoom_client_id',     'value' => 'cid',  'created_by' => $this->user->creatorId()],
			['name' => 'zoom_client_secret', 'value' => 'csec', 'created_by' => $this->user->creatorId()],
		]);

		$payload = [
			'title'           => 'Updated Meeting',
			'startDate'       => now()->toDateString(),
			'duration'        => 45,
			'password'        => 'p@ss',
			'projectId'       => $this->project->id,
			'userIds'         => [$this->user->id],
			'clientId'        => $this->client->id,
			'synchronizeType' => 'google_calendar',
		];

		// bind a partial mock that fakes the Zoom API call
		$mock = Mockery::mock(ZoomMeetingController::class)
			->makePartial()
			->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive('updateMeeting')
			->once()
			->with($this->meeting->meeting_id, Mockery::type('array'))
			->andReturn([
				'success' => true,
				'data'    => [
					'start_url' => 'https://zoom/start',
					'join_url'  => 'https://zoom/join',
					'status'    => 'started',
				],
			]);
		$this->app->instance(ZoomMeetingController::class, $mock);

		$response = $this->put(
			route('zoom-meeting.update', $this->meeting),
			$payload
		);

		$response->assertRedirect(route('zoom-meeting.index'))
			->assertSessionHas('success', __('Zoom Meeting successfully updated.'));

		$this->assertDatabaseHas('zoom_meetings', [
			'id'         => $this->meeting->id,
			'title'      => 'Updated Meeting',
			'duration'   => 45,
			'password'   => 'p@ss',
			'start_url'  => 'https://zoom/start',
			'join_url'   => 'https://zoom/join',
			'status'     => 'started',
			'project_id' => $this->project->id,
			'client_id'  => $this->client->id,
		]);
	}
}
