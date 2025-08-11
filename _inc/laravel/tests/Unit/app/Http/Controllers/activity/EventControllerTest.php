<?php

namespace Tests\Feature\Controllers;

use App\Models\{
	User,
	Employee,
	Branch,
	Department,
	Event,
	EventEmployee
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected int $creatorId;

	protected function setUp(): void
	{
		parent::setUp();

		// Create a user and act as them.
		$this->user = User::factory()->create(['type' => 'Admin']);
		$this->actingAs($this->user);
		$this->creatorId = $this->user->creatorId();

		// Stub out all permissions to true by default
		Gate::define('manage event', fn () => true);
		Gate::define('create event', fn () => true);
		Gate::define('edit event', fn () => true);
		Gate::define('delete event', fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Index should return 403 if the user lacks the 'manage event' permission.
	 **/
	public function test_index_requires_manage_event_permission()
	{
		Gate::define('manage event', fn () => false);
		$response = $this->get(route('event.index'));
		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Index should display the 'event.index' view with the expected data:
	 ** employees, branches, departments, events, currentMonthEvents, transDate.
	 **/
	public function test_index_displays_view_with_expected_data()
	{
		Employee::factory()->count(2)->create(['created_by' => $this->creatorId]);
		Branch::factory()->count(2)->create(['created_by' => $this->creatorId]);
		Department::factory()->count(2)->create(['created_by' => $this->creatorId]);
		Event::factory()->count(3)->create(['created_by' => $this->creatorId]);

		$response = $this->get(route('event.index'));

		$response->assertStatus(200)
			->assertViewIs('event.index')
			->assertViewHasAll([
				'arrEvents',
				'employees',
				'transDate',
				'events',
				'currentMonthEvents',
			]);

		// sanity check: exactly 3 events passed to the view
		$this->assertEquals(3, $response->viewData('events')->count());
	}

	/**
	 ** @test
	 **
	 ** The create route should return 403 without 'create event' permission.
	 **/
	public function test_create_requires_permission()
	{
		Gate::define('create event', fn () => false);
		$response = $this->get(route('event.create'));
		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** The create route should render 'event.create' with employees, branch,
	 ** departments, and settings when permitted.
	 **/
	public function test_create_displays_form_data()
	{
		Branch::factory()->count(2)->create(['created_by' => $this->creatorId]);
		Department::factory()->count(2)->create(['created_by' => $this->creatorId]);
		Employee::factory()->count(2)->create(['created_by' => $this->creatorId]);

		$response = $this->get(route('event.create'));

		$response->assertStatus(200)
			->assertViewIs('event.create')
			->assertViewHasAll(['employees', 'branch', 'departments', 'settings']);
	}

	/**
	 ** @test
	 **
	 ** Posting invalid data to store should redirect back with an error.
	 **/
	public function test_store_validation_errors_redirect_back()
	{
		$response = $this->post(route('event.store'), []); // missing all fields

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Posting valid event data should create an Event and associated
	 ** EventEmployee records, then redirect to index with success.
	 **/
	public function test_store_creates_event_and_event_employees()
	{
		$branch  = Branch::factory()->create(['created_by' => $this->creatorId]);
		$depart  = Department::factory()->create(['created_by' => $this->creatorId]);
		$emps    = Employee::factory()->count(3)->create(['created_by' => $this->creatorId]);

		$payload = [
			'branch_id'        => $branch->id,
			'department_id'    => $depart->id,
			'employee_id'      => $emps->pluck('id')->toArray(),
			'title'            => 'Team Offsite',
			'start_date'       => now()->toDateString(),
			'end_date'         => now()->addDay()->toDateString(),
			'color'            => 'blue',
			'description'      => 'Annual meetup',
			'synchronize_type' => 'none',
		];

		$response = $this->post(route('event.store'), $payload);

		$response->assertRedirect(route('event.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('events', [
			'title'      => 'Team Offsite',
			'created_by' => $this->creatorId,
		]);

		$eventCount = EventEmployee::where('event_id', Event::first()->id)->count();
		$this->assertEquals($emps->count(), $eventCount);
	}

	/**
	 ** @test
	 **
	 ** The show route should always redirect to index for this controller.
	 **/
	public function test_show_redirects_to_index()
	{
		$e = Event::factory()->create(['created_by' => $this->creatorId]);
		$response = $this->get(route('event.show', $e));

		$response->assertRedirect(route('event.index'));
	}

	/**
	 ** @test
	 **
	 ** The edit route should enforce 'edit event' permission and ownership,
	 ** then display the edit form for the owning user.
	 **/
	public function test_edit_requires_permission_and_ownership()
	{
		$e = Event::factory()->create(['created_by' => $this->creatorId]);

		// Lack permission
		Gate::define('edit event', fn () => false);
		$resp = $this->get(route('event.edit', $e));
		$resp->assertStatus(403);

		// Permission but not owner
		Gate::define('edit event', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$resp = $this->get(route('event.edit', $e));
		$resp->assertStatus(403);

		// Permission and owner
		$this->actingAs($this->user);
		$resp = $this->get(route('event.edit', $e));
		$resp->assertStatus(200)
			->assertViewIs('event.edit')
			->assertViewHas('event', $e);
	}

	/**
	 ** @test
	 **
	 ** The update route should validate input and, on success, update the
	 ** Event record and redirect to index with success.
	 **/
	public function test_update_validation_and_success()
	{
		$e = Event::factory()->create(['created_by' => $this->creatorId]);

		$this->actingAs($this->user);
		Gate::define('edit event', fn () => true);

		// Validation fails with empty title
		$resp = $this->put(route('event.update', $e), ['title' => '']);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// Successful update
		$data = [
			'title'       => 'Updated',
			'start_date'  => now()->toDateString(),
			'end_date'    => now()->addDay()->toDateString(),
			'color'       => 'red',
			'description' => 'Desc'
		];
		$resp = $this->put(route('event.update', $e), $data);

		$resp->assertRedirect(route('event.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('events', [
			'id'    => $e->id,
			'title' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** The destroy route should enforce deletion permission and ownership,
	 ** then delete the Event and redirect to index with success.
	 **/
	public function test_destroy_requires_permission_and_ownership()
	{
		$e = Event::factory()->create(['created_by' => $this->creatorId]);

		// No permission
		Gate::define('delete event', fn () => false);
		$r = $this->delete(route('event.destroy', $e));
		$r->assertStatus(403);

		// Permission but not owner
		Gate::define('delete event', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$r = $this->delete(route('event.destroy', $e));
		$r->assertStatus(403);

		// Permission and owner
		$this->actingAs($this->user);
		$r = $this->delete(route('event.destroy', $e));
		$r->assertRedirect(route('event.index'))
			->assertSessionHas('success');
		$this->assertDatabaseMissing('events', ['id' => $e->id]);
	}

	/**
	 ** @test
	 **
	 ** getDepartment should return all departments when branch_id=0,
	 ** otherwise only those matching the given branch_id.
	 **/
	public function test_getDepartment_returns_all_and_filtered()
	{
		$dept1 = Department::factory()->create([
			'created_by' => $this->creatorId,
			'branch_id'  => 1
		]);
		$dept2 = Department::factory()->create([
			'created_by' => $this->creatorId,
			'branch_id'  => 2
		]);

		// All
		$resp = $this->getJson(route('event.getDepartment', ['branch_id' => 0]));
		$resp->assertStatus(200)
			->assertJsonFragment([$dept1->id => $dept1->name, $dept2->id => $dept2->name]);

		// Filtered
		$resp = $this->getJson(route('event.getDepartment', ['branch_id' => 1]));
		$resp->assertStatus(200)
			->assertExactJson([$dept1->id => $dept1->name]);
	}

	/**
	 ** @test
	 **
	 ** getEmployee should return all employees when '0' is included,
	 ** otherwise only those matching provided department_ids.
	 **/
	public function test_getEmployee_returns_all_and_filtered()
	{
		$emp1 = Employee::factory()->create([
			'created_by'    => $this->creatorId,
			'department_id' => 5
		]);
		$emp2 = Employee::factory()->create([
			'created_by'    => $this->creatorId,
			'department_id' => 6
		]);

		// All
		$resp = $this->postJson(route('event.getEmployee'), ['department_id' => ['0']]);
		$resp->assertStatus(200)
			->assertJsonFragment([$emp1->id => $emp1->name, $emp2->id => $emp2->name]);

		// Filtered
		$resp = $this->postJson(route('event.getEmployee'), ['department_id' => [5]]);
		$resp->assertStatus(200)
			->assertExactJson([$emp1->id => $emp1->name]);
	}

	/**
	 ** @test
	 **
	 ** getEventData should return a JSON array
	 ** with the default calendar structure.
	 **/
	public function test_getEventData_returns_default_structure()
	{
		Event::factory()->count(2)->create(['created_by' => $this->creatorId]);

		// Default type
		$resp = $this->getJson(route('event.getEventData', ['calendar_type' => '']));
		$resp->assertStatus(200)
			->assertJsonStructure([[[
				'id', 'title', 'start', 'end', 'className', 'url', 'allDay'
			]]]);
	}
}
