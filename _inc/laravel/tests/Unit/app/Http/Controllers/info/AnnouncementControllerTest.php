<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Announcement;
use App\Models\EmployeeAnnouncement;
use Spatie\Permission\Models\Permission;

class AnnouncementControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Index should list only the creator's announcements for non-employee users.
	 **/
	public function test_index_lists_creator_announcements_for_non_employee()
	{
		$user = User::factory()->create(['type' => 'company']);
		Permission::create(['name' => 'manage announcement']);
		$user?->givePermissionTo('manage announcement');

		Announcement::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		Announcement::factory()->create(); // other user's announcement

		$response = $this->actingAs($user)->get(route('announcement.index'));

		$response->assertStatus(200)
			->assertViewIs('announcement.index')
			->assertViewHas('announcements', function ($announcements) use ($user) {
				return $announcements->count() === 2
					&& $announcements->every(fn ($a) => $a->created_by === $user?->creatorId());
			})
			->assertViewHas('currentEmployee');
	}

	/**
	 ** @test
	 **
	 ** Index should list employee-specific and global announcements for employee users.
	 **/
	public function test_index_lists_employee_and_global_announcements_for_employee()
	{
		$user = User::factory()->create(['type' => 'Employee']);
		Permission::create(['name' => 'manage announcement']);
		$user?->givePermissionTo('manage announcement');

		$creatorId = $user?->creatorId();
		$employee = Employee::factory()->create(['user_id' => $user?->id, 'created_by' => $creatorId]);

		// Employee-specific announcement
		$ann = Announcement::factory()->create(['created_by' => $creatorId]);
		EmployeeAnnouncement::factory()->create([
			'announcement_id' => $ann->id,
			'employee_id'     => $employee->id,
			'created_by'      => $creatorId,
		]);

		// Global announcement (department_id and employee_id = ["0"])
		$global = Announcement::factory()->create([
			'created_by'    => $creatorId,
			'department_id' => json_encode([0]),
			'employee_id'   => json_encode([0]),
		]);

		$response = $this->actingAs($user)->get(route('announcement.index'));

		$response->assertStatus(200)
			->assertViewHas('announcements', function ($announcements) use ($ann, $global) {
				$ids = $announcements->pluck('id')->all();
				return in_array($global->id, $ids)
					&& in_array($ann->id, $ids);
			});
	}

	/**
	 ** @test
	 **
	 ** Create should display form when user has create permission.
	 **/
	public function test_create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create announcement']);
		$user?->givePermissionTo('create announcement');

		Branch::factory()->create(['created_by' => $user?->creatorId()]);
		Department::factory()->create(['created_by' => $user?->creatorId()]);
		Employee::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('announcement.create'));

		$response->assertStatus(200)
			->assertViewIs('announcement.create')
			->assertViewHasAll(['employees', 'branch', 'departments']);
	}

	/**
	 ** @test
	 **
	 ** Store should persist a new announcement and related employee announcements.
	 **/
	public function test_store_persists_announcement_and_employee_announcements()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create announcement']);
		$user?->givePermissionTo('create announcement');

		$creatorId = $user?->creatorId();
		$branch    = Branch::factory()->create(['created_by' => $creatorId]);
		$department = Department::factory()->create(['created_by' => $creatorId]);
		$employee  = Employee::factory()->create(['created_by' => $creatorId]);

		$data = [
			'title'        => 'New Policy',
			'startDate'    => now()->toDateString(),
			'endDate'      => now()->addDay()->toDateString(),
			'branchId'     => $branch->id,
			'departmentId' => [$department->id],
			'employeeId'   => [$employee->id],
			'description'  => 'Details here.',
		];

		$response = $this->actingAs($user)->post(route('announcement.store'), $data);

		$response->assertRedirect(route('announcement.index'))
			->assertSessionHas('success', __('Announcement successfully created.'));
		$this->assertDatabaseHas('announcements', [
			'title'      => 'New Policy',
			'branch_id'  => $branch->id,
			'created_by' => $creatorId,
		]);
		$this->assertDatabaseHas('employee_announcements', [
			'employee_id'     => $employee->id,
			'announcement_id' => Announcement::first()->id,
			'created_by'      => $creatorId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Show should display the announcement when user is owner with view permission.
	 **/
	public function test_show_displays_announcement_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'view announcement']);
		$user?->givePermissionTo('view announcement');

		$announcement = Announcement::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('announcement.show', $announcement));

		$response->assertStatus(200)
			->assertViewIs('announcement.show')
			->assertViewHas('announcement', $announcement);
	}

	/**
	 ** @test
	 **
	 ** Edit should display form for owner with edit permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit announcement']);
		$user?->givePermissionTo('edit announcement');

		$announcement = Announcement::factory()->create(['created_by' => $user?->creatorId()]);
		Branch::factory()->create(['created_by' => $user?->creatorId()]);
		Department::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('announcement.edit', $announcement));

		$response->assertStatus(200)
			->assertViewIs('announcement.edit')
			->assertViewHasAll(['announcement', 'branch', 'departments']);
	}

	/**
	 ** @test
	 **
	 ** Update should change announcement details and redirect.
	 **/
	public function test_update_modifies_announcement_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit announcement']);
		$user?->givePermissionTo('edit announcement');

		$announcement = Announcement::factory()->create([
			'title'      => 'Old Title',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->put(route('announcement.update', $announcement), [
			'title'       => 'Updated Title',
			'startDate'   => now()->toDateString(),
			'endDate'     => now()->addDay()->toDateString(),
			'branchId'    => $announcement->branch_id,
			'departmentId' => json_decode($announcement->department_id, true),
		]);

		$response->assertRedirect(route('announcement.index'))
			->assertSessionHas('success', __('Announcement successfully updated.'));
		$this->assertDatabaseHas('announcements', [
			'id'    => $announcement->id,
			'title' => 'Updated Title',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete the announcement and redirect for owner with delete permission.
	 **/
	public function test_destroy_deletes_announcement_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete announcement']);
		$user?->givePermissionTo('delete announcement');

		$announcement = Announcement::factory()->create(['created_by' => $user?->creatorId()]);
		EmployeeAnnouncement::factory()->create([
			'announcement_id' => $announcement->id,
			'employee_id'     => Employee::factory()->create(['created_by' => $user?->creatorId()])->id,
			'created_by'      => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->delete(route('announcement.destroy', $announcement));

		$response->assertRedirect(route('announcement.index'))
			->assertSessionHas('success', __('Announcement successfully deleted.'));
		$this->assertModelMissing($announcement);
		$this->assertDatabaseCount('employee_announcements', 0);
	}

	/**
	 ** @test
	 **
	 ** getDepartment should return JSON list of departments for the given branch.
	 **/
	public function test_getDepartment_returns_departments_for_branch()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'manage announcement']));

		Department::factory()->count(2)->create(['created_by' => $user?->creatorId(), 'branch_id' => 1]);
		Department::factory()->create(['created_by' => $user?->creatorId(), 'branch_id' => 2]);

		$response = $this->actingAs($user)->postJson(
			route('announcement.getDepartment'),
			['branchId' => 1]
		);

		$response->assertJsonCount(2)
			->assertJsonStructure([
				'*' => ['1', '2'] // keys are department IDs
			]);
	}

	/**
	 ** @test
	 **
	 ** getEmployee should return JSON list of employees for the given department.
	 **/
	public function test_getEmployee_returns_employees_for_department()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'manage announcement']));

		Employee::factory()->count(3)->create(['created_by' => $user?->creatorId(), 'department_id' => 5]);
		Employee::factory()->create(['created_by' => $user?->creatorId(), 'department_id' => 6]);

		$response = $this->actingAs($user)->postJson(
			route('announcement.getEmployee'),
			['departmentId' => 5]
		);

		$response->assertJsonCount(3)
			->assertJsonStructure([
				'*' => ['5']
			]);
	}
}
