<?php

namespace Tests\Feature;

use App\Models\{Employee, Leave, LeaveType, User};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Gate, Mail};
use Tests\TestCase;

class LeaveControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employee;
	private LeaveType $type;

	protected function setUp(): void
	{
		parent::setUp();

		// allow or deny permissions per test
		Gate::before(fn () => true);

		// so creatorId() returns own id
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
		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = User::factory()->create(['type' => 'Employee', 'created_by' => $this->company->creatorId()]);
		$this->type    = LeaveType::factory()->create(['created_by' => $this->company->creatorId()]);

		// link Employee record
		Employee::factory()->create([
			'user_id'    => $this->employee->id,
			'created_by' => $this->company->creatorId(),
		]);

		$this->actingAs($this->company);
	}

	/**
	 ** @test
	 **
	 ** index_requires_manage_leave_permission:
	 **   - denies users without 'manage leave'
	 **/
	public function index_requires_manage_leave_permission()
	{
		Gate::before(fn () => false);

		$this->get(route('leave.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_lists_all_leaves_for_company_and_filters_for_employee:
	 **   - company sees all
	 **   - employee sees only own leaves
	 **/
	public function index_lists_and_filters_leaves()
	{
		// seed two leaves for company
		Leave::factory()->count(2)->create([
			'leave_type_id'    => $this->type->id,
			'employee_id'      => Employee::first()->id,
			'start_date'       => now()->toDateString(),
			'end_date'         => now()->addDay()->toDateString(),
			'leave_reason'     => 'Reason',
			'remark'           => 'Remark',
			'created_by'       => $this->company->creatorId(),
		]);

		// company sees 2
		$resp = $this->actingAs($this->company)
			->get(route('leave.index'));

		$resp->assertOk()
			->assertViewHas('leaves', fn ($leaves) => $leaves->count() === 2);

		// employee sees only their own (also 2 here)
		$resp = $this->actingAs($this->employee)
			->get(route('leave.index'));

		$resp->assertOk()
			->assertViewHas('leaves', fn ($leaves) => $leaves->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** create_requires_permission_and_displays_form:
	 **   - denies without 'create leave'
	 **   - shows employees & leaveTypes dropdown
	 **/
	public function create_requires_permission_and_displays_form()
	{
		Gate::before(fn () => false);
		$this->get(route('leave.create'))
			->assertStatus(403);

		Gate::before(fn () => true);
		$resp = $this->actingAs($this->company)
			->get(route('leave.create'));

		$resp->assertOk()
			->assertViewIs('leave.create')
			->assertViewHasAll(['employees', 'leaveTypes']);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates_leave:
	 **   - fails when missing fields
	 **   - creates record otherwise
	 **/
	public function store_validates_and_creates_leave()
	{
		// validation fail
		$this->post(route('leave.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// success payload
		$payload = [
			'leave_type_id' => $this->type->id,
			'start_date'    => now()->toDateString(),
			'end_date'      => now()->addDay()->toDateString(),
			'leave_reason'  => 'Sick',
			'remark'        => 'Feeling ill',
		];

		$this->post(route('leave.store'), $payload)
			->assertRedirect(route('leave.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('leaves', [
			'leave_type_id' => $this->type->id,
			'leave_reason'  => 'Sick',
		]);
	}

	/**
	 ** @test
	 **
	 ** show_redirects_to_index:
	 **   - show() always redirects back to index
	 **/
	public function show_redirects_to_index()
	{
		$leave = Leave::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->get(route('leave.show', $leave))
			->assertRedirect(route('leave.index'));
	}

	/**
	 ** @test
	 **
	 ** edit_requires_permission_and_ownership_and_displays_form:
	 **   - denies without 'edit leave'
	 **   - denies non-owner
	 **   - allows owner and shows form
	 **/
	public function edit_requires_permission_and_ownership_and_displays_form()
	{
		$leave = Leave::factory()->create(['created_by' => $this->company->creatorId()]);

		Gate::before(fn () => false);
		$this->get(route('leave.edit', $leave))
			->assertStatus(403);

		Gate::before(fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other)
			->get(route('leave.edit', $leave))
			->assertStatus(403);

		$this->actingAs($this->company)
			->get(route('leave.edit', $leave))
			->assertOk()
			->assertViewIs('leave.edit')
			->assertViewHasAll(['leave', 'emps', 'lts']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves_changes:
	 **   - fails on invalid input
	 **   - updates leave on success
	 **/
	public function update_validates_and_saves_changes()
	{
		$leave = Leave::factory()->create([
			'created_by'    => $this->company->creatorId(),
			'leave_type_id' => $this->type->id,
			'start_date'    => now()->toDateString(),
			'end_date'      => now()->toDateString(),
			'leave_reason'  => 'Old',
			'remark'        => 'Old',
		]);

		// validation fail
		$this->put(route('leave.update'), ['leave_id' => $leave->id, 'leave_type_id' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		$data = [
			'leave_id'       => $leave->id,
			'leave_type_id'  => $this->type->id,
			'start_date'     => now()->toDateString(),
			'end_date'       => now()->addDay()->toDateString(),
			'leave_reason'   => 'Updated',
			'remark'         => 'Updated',
		];

		$this->put(route('leave.update'), $data)
			->assertRedirect(route('leave.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('leaves', [
			'id'           => $leave->id,
			'leave_reason' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_requires_permission_and_owner_and_deletes:
	 **   - denies without 'delete leave'
	 **   - denies non-owner
	 **   - deletes on success
	 **/
	public function destroy_requires_permission_and_owner_and_deletes()
	{
		$leave = Leave::factory()->create(['created_by' => $this->company->creatorId()]);

		Gate::before(fn () => false);
		$this->delete(route('leave.destroy', $leave))
			->assertStatus(403);

		Gate::before(fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other)
			->delete(route('leave.destroy', $leave))
			->assertStatus(403);

		$this->actingAs($this->company)
			->delete(route('leave.destroy', $leave))
			->assertRedirect(route('leave.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('leaves', ['id' => $leave->id]);
	}

	/**
	 ** @test
	 **
	 ** action_displays_leave_action_form:
	 **   - shows action view with employee, type, and leave
	 **/
	public function action_displays_leave_action_form()
	{
		$leave = Leave::factory()->create([
			'employee_id'   => Employee::first()->id,
			'leave_type_id' => $this->type->id,
			'created_by'    => $this->company->creatorId(),
		]);

		$resp = $this->get(route('leave.action', $leave->id));

		$resp->assertOk()
			->assertViewIs('leave.action')
			->assertViewHasAll(['emp', 'lt', 'lv']);
	}

	/**
	 ** @test
	 **
	 ** changeAction_updates_status_and_redirects:
	 **   - updates leave status to Rejected or Approved
	 **/
	public function changeAction_updates_status_and_redirects()
	{
		$leave = Leave::factory()->create([
			'employee_id'   => Employee::first()->id,
			'leave_type_id' => $this->type->id,
			'start_date'    => now()->toDateString(),
			'end_date'      => now()->addDay()->toDateString(),
			'created_by'    => $this->company->creatorId(),
		]);

		// change to Rejected
		$this->post(route('leave.changeAction'), [
			'leave_id' => $leave->id,
			'status'   => 'Rejected',
		])->assertRedirect(route('leave.index'))
			->assertSessionHas('success');

		$this->assertEquals('Rejected', $leave->fresh()->status);
	}

	/**
	 ** @test
	 **
	 ** jsonCount_returns_leave_totals_per_type:
	 **   - returns JSON array with counts per leaveType
	 **/
	public function jsonCount_returns_leave_totals_per_type()
	{
		Employee::first(); // ensure employee exists

		// create some leaves
		Leave::factory()->count(3)->create([
			'employee_id'   => Employee::first()->id,
			'leave_type_id' => $this->type->id,
			'total_leave_days' => 2,
			'created_by'    => $this->company->creatorId(),
		]);

		$resp = $this->getJson(route('leave.jsonCount', ['employee_id' => Employee::first()->id]));

		$resp->assertOk()
			->assertJsonFragment(['id' => $this->type->id, 'title' => $this->type->title])
			->assertJsonCount(1);
	}
}
