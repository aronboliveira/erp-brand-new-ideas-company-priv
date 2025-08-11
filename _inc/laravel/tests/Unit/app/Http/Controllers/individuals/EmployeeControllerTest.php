<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\EmployeeDocument;
use App\Models\Utility;
use App\Http\Controllers\EmployeeController;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// create needed permissions
		Permission::create(['name' => 'manage employee']);
		Permission::create(['name' => 'create employee']);
		Permission::create(['name' => 'edit employee']);
		Permission::create(['name' => 'delete employee']);
		Permission::create(['name' => 'view employee']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing index.
	 **/
	public function guest_redirected_from_index()
	{
		$response = $this->get(action([EmployeeController::class, 'index']));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Users without manage permission cannot access index.
	 **/
	public function user_without_manage_permission_cannot_access_index()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$response = $this->get(action([EmployeeController::class, 'index']));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with manage permission can view the index.
	 **/
	public function user_with_manage_permission_can_view_index()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage employee');

		Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->get(action([EmployeeController::class, 'index']));

		$response->assertStatus(200);
		$response->assertViewIs('employee.index');
		$response->assertViewHas('employees');
	}

	/**
	 ** @test
	 **
	 ** Users without create permission cannot access create form.
	 **/
	public function user_without_create_permission_cannot_access_create()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$response = $this->get(action([EmployeeController::class, 'create']));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with create permission can view the create form.
	 **/
	public function user_with_create_permission_can_view_create()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create employee');
		Department::factory()->create(['created_by' => $user?->creatorId()]);
		Designation::factory()->create(['created_by' => $user?->creatorId()]);
		Document::factory()->count(2)->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([EmployeeController::class, 'create']));

		$response->assertStatus(200);
		$response->assertViewIs('employee.create');
		$response->assertViewHasAll(['departments', 'designations', 'documents']);
	}

	/**
	 ** @test
	 **
	 ** Store redirects back on validation error.
	 **/
	public function store_redirects_back_on_validation_error()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create employee');

		$this->actingAs($user);
		$response = $this->post(action([EmployeeController::class, 'store']), [
			// missing required fields
		]);

		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Store creates a new employee and redirects on success.
	 **/
	public function store_creates_employee_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create employee');
		$owner = User::factory()->create(['plan' => -1]);
		$owner->save();
		// assume owner and creatorId alignment
		$this->actingAs($user);

		$response = $this->post(action([EmployeeController::class, 'store']), [
			'name'           => 'John Doe',
			'dob'            => '1990-01-01',
			'phone'          => '1234567890',
			'address'        => '123 Main St',
			'email'          => 'john@example.com',
			'password'       => 'secret123',
			'department_id'  => Department::factory()->create(['created_by' => $user?->creatorId()])->id,
			'designation_id' => Designation::factory()->create(['created_by' => $user?->creatorId()])->id,
		]);

		$response->assertRedirect(route('employee.index'));
		$this->assertDatabaseHas('users', ['email' => 'john@example.com']);
		$this->assertDatabaseHas('employees', ['email' => 'john@example.com']);
	}

	/**
	 ** @test
	 **
	 ** Users with view permission can access show.
	 **/
	public function user_with_view_permission_can_access_show()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('view employee');

		$employee = Employee::factory()->create(['created_by' => $user?->creatorId()]);
		$encId   = encrypt($employee->id);

		$this->actingAs($user);
		$response = $this->get(action([EmployeeController::class, 'show'], ['encId' => $encId]));

		$response->assertStatus(200);
		$response->assertViewIs('employee.show');
	}

	/**
	 ** @test
	 **
	 ** JSON endpoint returns department designations.
	 **/
	public function json_returns_designations_for_department()
	{
		$designation = Designation::factory()->create(['department_id' => 42]);
		$response = $this->getJson(action([EmployeeController::class, 'json']), ['department_id' => 42]);
		$response->assertStatus(200)
			->assertJsonFragment([$designation->id => $designation->name]);
	}

	/**
	 ** @test
	 **
	 ** importFile displays the import view.
	 **/
	public function import_file_displays_view()
	{
		$this->actingAs(User::factory()->create());
		$response = $this->get(action([EmployeeController::class, 'importFile']));
		$response->assertStatus(200);
		$response->assertViewIs('employee.import');
	}

	/**
	 ** @test
	 **
	 ** import redirects back on missing file validation.
	 **/
	public function import_redirects_back_on_missing_file()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create employee');

		$this->actingAs($user);
		$response = $this->post(action([EmployeeController::class, 'import']), [
			// no 'file' key
		]);

		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** export returns a file download response.
	 **/
	public function export_returns_download_response()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage employee');
		Excel::fake(); // prevents real file generation

		$this->actingAs($user);
		$response = $this->get(action([EmployeeController::class, 'export']));

		// Excel::download() returns BinaryFileResponse
		$this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response);
	}
}
