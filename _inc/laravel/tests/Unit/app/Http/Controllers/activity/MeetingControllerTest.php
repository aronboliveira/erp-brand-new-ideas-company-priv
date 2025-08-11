<?php

namespace Tests\Feature;

use App\Models\{
	User,
	Branch,
	Department,
	Employee,
	Meeting,
	MeetingEmployee
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class MeetingControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected int $ownerId;

	protected function setUp(): void
	{
		parent::setUp();

		// Create & authenticate a user (non-Employee)
		$this->user = User::factory()->create([
			'type' => 'Admin',
		]);
		$this->actingAs($this->user);
		$this->ownerId = $this->user->creatorId();

		// Grant all relevant permissions
		Gate::define('manage meeting', fn () => true);
		Gate::define('create meeting', fn () => true);
		Gate::define('edit meeting', fn () => true);
		Gate::define('delete meeting', fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Index route should return 403 if user lacks 'manage meeting' permission.
	 **/
	public function test_index_requires_manage_permission()
	{
		Gate::define('manage meeting', fn () => false);

		$this->get(route('meeting.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Index route should display all meetings for the authenticated admin.
	 **/
	public function test_index_shows_all_meetings_for_admin()
	{
		Meeting::factory()->count(3)->create(['created_by' => $this->ownerId]);

		$response = $this->get(route('meeting.index'));

		$response->assertStatus(200)
			->assertViewIs('meeting.index')
			->assertViewHas('meetings', function ($meetings) {
				return $meetings->count() === 3;
			});
	}

	/**
	 ** @test
	 **
	 ** Create route should return 403 without 'create meeting' permission.
	 **/
	public function test_create_requires_create_permission()
	{
		Gate::define('create meeting', fn () => false);

		$this->get(route('meeting.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Create view should receive branches, departments, employees, and settings.
	 **/
	public function test_create_view_receives_branches_departments_employees()
	{
		Branch::factory()->count(2)->create(['created_by' => $this->ownerId]);
		Department::factory()->count(2)->create(['created_by' => $this->ownerId]);
		Employee::factory()->count(2)->create(['created_by' => $this->ownerId]);

		$response = $this->get(route('meeting.create'));

		$response->assertStatus(200)
			->assertViewIs('meeting.create')
			->assertViewHasAll(['branches', 'departments', 'employees', 'settings']);
	}

	/**
	 ** @test
	 **
	 ** Posting invalid data to store should redirect back with error.
	 **/
	public function test_store_validation_failure_redirects_back()
	{
		$this->post(route('meeting.store'), [])
			->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Store should create meeting and MeetingEmployee records, then redirect.
	 **/
	public function test_store_creates_meeting_and_invites()
	{
		$branch = Branch::factory()->create(['created_by' => $this->ownerId]);
		$dept  = Department::factory()->create(['created_by' => $this->ownerId]);
		$emp   = Employee::factory()->create([
			'created_by'    => $this->ownerId,
			'department_id' => $dept->id,
		]);

		$payload = [
			'branch_id'     => $branch->id,
			'department_id' => [$dept->id],
			'employee_id'   => [$emp->id],
			'title'         => 'Standup',
			'date'          => now()->toDateString(),
			'time'          => '09:00',
			'note'          => 'Daily sync',
		];

		$this->post(route('meeting.store'), $payload)
			->assertRedirect(route('meeting.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('meetings', [
			'title'      => 'Standup',
			'created_by' => $this->ownerId,
		]);

		$meeting = Meeting::where('title', 'Standup')->first();

		$this->assertDatabaseHas('meeting_employees', [
			'meeting_id'  => $meeting->id,
			'employee_id' => $emp->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Edit route should enforce 'edit meeting' permission and ownership,
	 ** then render the edit view with meeting and employees.
	 **/
	public function test_edit_requires_permission_and_owner()
	{
		$meeting = Meeting::factory()->create(['created_by' => $this->ownerId]);

		// no 'edit meeting' permission
		Gate::define('edit meeting', fn () => false);
		$this->get(route('meeting.edit', $meeting))->assertStatus(403);

		// wrong owner
		Gate::define('edit meeting', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$this->get(route('meeting.edit', $meeting))->assertStatus(403);

		// correct
		$this->actingAs($this->user);
		$response = $this->get(route('meeting.edit', $meeting));
		$response->assertStatus(200)
			->assertViewIs('meeting.edit')
			->assertViewHasAll(['meeting', 'employees']);
	}

	/**
	 ** @test
	 **
	 ** Update route should validate input; on success update record and redirect.
	 **/
	public function test_update_validation_and_success()
	{
		$meeting = Meeting::factory()->create([
			'title'      => 'Old',
			'date'       => '2025-01-01',
			'time'       => '10:00',
			'created_by' => $this->ownerId,
		]);

		// validation error
		$this->put(route('meeting.update', $meeting), ['title' => ''])
			->assertStatus(302)
			->assertSessionHas('error');

		// successful update
		$data = [
			'title' => 'New',
			'date'  => '2025-01-02',
			'time'  => '11:00',
			'note'  => 'Updated',
		];
		$this->put(route('meeting.update', $meeting), $data)
			->assertRedirect(route('meeting.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('meetings', [
			'id'    => $meeting->id,
			'title' => 'New',
			'note'  => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy route should enforce deletion permission and ownership,
	 ** then delete the record and redirect with success.
	 **/
	public function test_destroy_requires_permission_and_owner()
	{
		$meeting = Meeting::factory()->create(['created_by' => $this->ownerId]);

		// no delete permission
		Gate::define('delete meeting', fn () => false);
		$this->delete(route('meeting.destroy', $meeting))->assertStatus(403);

		// wrong owner
		Gate::define('delete meeting', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$this->delete(route('meeting.destroy', $meeting))->assertStatus(403);

		// proper delete
		$this->actingAs($this->user);
		$this->delete(route('meeting.destroy', $meeting))
			->assertRedirect(route('meeting.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
	}

	/**
	 ** @test
	 **
	 ** getDepartment JSON endpoint should filter by branch_id correctly.
	 **/
	public function test_get_department_json()
	{
		Department::factory()->count(2)->create([
			'created_by' => $this->ownerId,
			'branch_id'  => 1,
		]);
		Department::factory()->create([
			'created_by' => $this->ownerId,
			'branch_id'  => 2,
		]);

		$this->getJson(route('meeting.getDepartment', ['branch_id' => 1]))
			->assertOk()
			->assertJsonCount(2);

		$this->getJson(route('meeting.getDepartment', ['branch_id' => 2]))
			->assertOk()
			->assertJsonCount(1);
	}

	/**
	 ** @test
	 **
	 ** getEmployee JSON endpoint should filter by provided department IDs.
	 **/
	public function test_get_employee_json()
	{
		$dept = Department::factory()->create(['created_by' => $this->ownerId]);
		$e1  = Employee::factory()->create([
			'created_by'    => $this->ownerId,
			'department_id' => $dept->id,
		]);
		Employee::factory()->create([
			'created_by'    => $this->ownerId,
			'department_id' => 999,
		]);

		$this->getJson(route('meeting.getEmployee', ['department_id' => [$dept->id]]))
			->assertOk()
			->assertJsonFragment([$e1->id => $e1->name]);
	}

	/**
	 ** @test
	 **
	 ** Calendar view and getMeetingData endpoint should return meeting data.
	 **/
	public function test_calendar_and_get_meeting_data()
	{
		Meeting::factory()->count(2)->create([
			'created_by' => $this->ownerId,
			'date'       => now()->toDateString(),
			'time'       => '12:00',
		]);

		$this->get(route('meeting.calendar'))
			->assertOk()
			->assertViewIs('meeting.calendar')
			->assertViewHasAll(['arrMeetings', 'transdate', 'meetings']);

		$this->getJson(route('meeting.getMeetingData', ['calendar_type' => 'own']))
			->assertOk()
			->assertJsonCount(2);
	}

	/**
	 ** @test
	 **
	 ** When the user has the 'view meeting' permission,
	 ** the show endpoint returns the meeting view.
	 **/
	public function it_displays_the_meeting_when_authorized()
	{
		$user = User::factory()->create();
		$meeting = Meeting::factory()->create();

		$response = $this->actingAs($user)
			->get(route('meeting.show', $meeting));

		$response->assertStatus(200)
			->assertViewIs('meeting.show')
			->assertViewHas('meeting', fn ($m) => $m->id === $meeting->id);
	}

	/**
	 ** @test
	 **
	 ** When the user lacks the 'view meeting' permission,
	 ** the show endpoint redirects with an error.
	 **/
	public function it_denies_access_if_not_permitted()
	{
		// Deny all permissions
		Gate::before(fn () => false);

		$user = User::factory()->create();
		$meeting = Meeting::factory()->create();

		$response = $this->actingAs($user)
			->get(route('meeting.show', $meeting));

		$response->assertRedirect()
			->assertSessionHas('error');
	}
}
