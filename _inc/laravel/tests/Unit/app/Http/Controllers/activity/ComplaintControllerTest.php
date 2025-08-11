<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\ComplaintController;
use App\Models\{User, Employee, Complaint};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ComplaintControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $admin;
	protected Employee $employee;

	protected function setUp(): void
	{
		parent::setUp();

		// Create an admin user and stub out all required permissions
		$this->admin = User::factory()->create(['type' => 'Admin']);
		$this->actingAs($this->admin);

		Gate::define('manage complaint', fn () => true);
		Gate::define('create complaint', fn () => true);
		Gate::define('edit complaint', fn () => true);
		Gate::define('delete complaint', fn () => true);

		// Create an Employee record linked to our admin
		$this->employee = Employee::factory()->create([
			'user_id'    => $this->admin->id,
			'created_by' => $this->admin->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Ensure index is forbidden when 'manage complaint' permission is denied.
	 **/
	public function test_index_requires_permission(): void
	{
		Gate::define('manage complaint', fn () => false);
		$response = $this->get(route('complaint.index'));
		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** As an employee user, index shows only complaints created by them.
	 **/
	public function test_index_as_employee_shows_only_their_complaints(): void
	{
		// Create two complaints: one from this employee, one from someone else
		Complaint::factory()->create([
			'complaint_from' => $this->employee->id,
			'created_by'     => $this->admin->creatorId(),
		]);
		Complaint::factory()->create([
			'complaint_from' => Employee::factory()->create([
				'created_by' => $this->admin->creatorId()
			])->id,
			'created_by'     => $this->admin->creatorId(),
		]);

		// Force user->type to Employee
		$this->admin->type = 'Employee';
		$this->admin->save();

		$response = $this->get(route('complaint.index'));
		$response->assertStatus(200);
		$response->assertViewHas('complaints', function ($complaints) {
			return $complaints->count() === 1
				&& $complaints->first()->complaint_from === $this->employee->id;
		});
	}

	/**
	 ** @test
	 **
	 ** As an Admin user, index shows all complaints they created.
	 **/
	public function test_index_as_admin_shows_all_created(): void
	{
		Complaint::factory()->count(3)->create([
			'created_by' => $this->admin->creatorId(),
		]);

		$response = $this->get(route('complaint.index'));
		$response->assertStatus(200);
		$response->assertViewHas('complaints', fn ($c) => $c->count() === 3);
	}

	/**
	 ** @test
	 **
	 ** Ensure create is denied with no 'create complaint' permission.
	 **/
	public function test_create_requires_permission(): void
	{
		Gate::define('create complaint', fn () => false);
		$response = $this->get(route('complaint.create'));
		$response->assertStatus(401)
			->assertJson(['error' => __('Permission denied.')]);
	}

	/**
	 ** @test
	 **
	 ** Create form is accessible to both Employee and Admin, showing employees list.
	 **/
	public function test_create_as_employee_and_admin(): void
	{
		// Employee case
		$this->admin->type = 'Employee';
		$this->admin->save();
		$response = $this->get(route('complaint.create'));
		$response->assertStatus(200)
			->assertViewHasAll(['employees', 'currentEmployee']);

		// Admin case
		$this->admin->type = 'Admin';
		$this->admin->save();
		$response = $this->get(route('complaint.create'));
		$response->assertStatus(200)
			->assertViewHasAll(['employees', 'currentEmployee']);
	}

	/**
	 ** @test
	 **
	 ** Store action works for both Employee (auto sets complaint_from) and Admin (explicit).
	 **/
	public function test_store_as_employee_and_admin(): void
	{
		$payload = [
			'complaint_against' => $this->employee->id,
			'title'             => 'Test Complaint',
			'complaint_date'    => now()->toDateString(),
			'description'       => 'Some details',
		];

		// Employee: complaint_from is taken from Employee::where('user_id')
		$this->admin->type = 'Employee';
		$this->admin->save();

		$response = $this->post(route('complaint.store'), $payload);
		$response->assertRedirect(route('complaint.index'));
		$this->assertDatabaseHas('complaints', [
			'title'          => 'Test Complaint',
			'complaint_from' => $this->employee->id,
			'created_by'     => $this->admin->creatorId(),
		]);

		// Admin: must supply complaint_from
		$otherEmp = Employee::factory()->create(['created_by' => $this->admin->creatorId()]);
		$payload['complaint_from'] = $otherEmp->id;
		$this->admin->type = 'Admin';
		$this->admin->save();

		$response = $this->post(route('complaint.store'), $payload);
		$response->assertRedirect(route('complaint.index'));
		$this->assertDatabaseHas('complaints', [
			'complaint_from' => $otherEmp->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Edit, update, and destroy enforce permission and ownership correctly.
	 **/
	public function test_edit_update_destroy_authorization_and_ownership(): void
	{
		$complaint = Complaint::factory()->create([
			'complaint_from' => $this->employee->id,
			'created_by'     => $this->admin->creatorId(),
		]);

		// edit: unauthorized if missing permission
		Gate::define('edit complaint', fn () => false);
		$resp = $this->get(route('complaint.edit', $complaint));
		$resp->assertStatus(401);

		// restore permission
		Gate::define('edit complaint', fn () => true);

		// ownership: another user
		$other = User::factory()->create(['type' => 'Admin']);
		$this->actingAs($other);
		Gate::define('edit complaint', fn () => true);
		$resp = $this->get(route('complaint.edit', $complaint));
		$resp->assertStatus(401);

		// as owner
		$this->actingAs($this->admin);
		$resp = $this->get(route('complaint.edit', $complaint));
		$resp->assertStatus(200)
			->assertViewHas('complaint', $complaint);

		// update
		$data = ['complaint_against' => $this->employee->id, 'title' => 'New', 'complaint_date' => now()->toDateString()];
		$resp = $this->put(route('complaint.update', $complaint), $data);
		$resp->assertRedirect(route('complaint.index'));
		$this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'title' => 'New']);

		// destroy
		$resp = $this->delete(route('complaint.destroy', $complaint));
		$resp->assertRedirect(route('complaint.index'));
		$this->assertDatabaseMissing('complaints', ['id' => $complaint->id]);
	}

	/**
	 ** @test
	 **
	 ** Show displays the complaint details view correctly.
	 **/
	public function test_show_displays_view(): void
	{
		$complaint = Complaint::factory()->create(['created_by' => $this->admin->creatorId()]);
		$resp = $this->get(route('complaint.show', $complaint));
		$resp->assertStatus(200)
			->assertViewIs('complaint.show')
			->assertViewHas('complaint', $complaint);
	}

	/**
	 ** @test
	 **
	 ** getEmployee returns JSON array of employees for given branch.
	 **/
	public function test_getEmployee_returns_json(): void
	{
		$branchId = 123;
		Employee::factory()->count(2)->create(['branch_id' => $branchId]);
		$resp = $this->getJson(action([ComplaintController::class, 'getEmployee'], ['branch_id' => $branchId]));
		$resp->assertStatus(200)
			->assertJsonCount(2, 'employee');
	}
}
