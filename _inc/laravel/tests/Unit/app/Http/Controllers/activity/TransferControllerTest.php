<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Branch, Department, Employee, Transfer, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Gate};
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class TransferControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private Transfer $transfer;

	protected function setUp(): void
	{
		parent::setUp();

		// Allow all permissions by default
		Gate::before(fn () => true);

		// Macro so creatorId() returns the user's own ID
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

		// Create & authenticate a company user
		$this->company = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);

		// Create a Transfer owned by this user
		$this->transfer = Transfer::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Index should list all transfers for non-employee users.
	 **/
	public function test_index_lists_all_transfers_for_non_employee_users()
	{
		$user = User::factory()->create(['type' => 'company']);
		Permission::create(['name' => 'manage transfer']);
		$user?->givePermissionTo('manage transfer');

		$creatorId = $user?->creatorId();
		Transfer::factory()->count(2)->create(['created_by' => $creatorId]);
		Transfer::factory()->create(); // other user's transfer

		$response = $this->actingAs($user)->get(route('transfer.index'));

		$response->assertStatus(200)
			->assertViewIs('transfer.index')
			->assertViewHas('transfers', function ($transfers) use ($creatorId) {
				return $transfers->count() === 2
					&& $transfers->every(fn ($t) => $t->created_by === $creatorId);
			});
	}

	/**
	 ** @test
	 **
	 ** Index should filter transfers for users of type Employee.
	 **/
	public function test_index_filters_for_employee_users()
	{
		$user = User::factory()->create(['type' => 'Employee']);
		Permission::create(['name' => 'manage transfer']);
		$user?->givePermissionTo('manage transfer');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);

		Transfer::factory()->create([
			'employee_id' => $emp->id,
			'created_by'  => $creatorId,
		]);
		Transfer::factory()->create([
			'employee_id' => $emp->id + 1,
			'created_by'  => $creatorId,
		]);

		$response = $this->actingAs($user)->get(route('transfer.index'));

		$response->assertStatus(200)
			->assertViewHas('transfers', function ($transfers) {
				return $transfers->count() === 1;
			});
	}

	/**
	 ** @test
	 **
	 ** Create should display form for authorized user.
	 **/
	public function test_create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create transfer']);
		$user?->givePermissionTo('create transfer');

		$creatorId = $user?->creatorId();
		Branch::factory()->create(['created_by' => $creatorId]);
		Department::factory()->create(['created_by' => $creatorId]);
		Employee::factory()->create(['created_by' => $creatorId]);

		$response = $this->actingAs($user)->get(route('transfer.create'));

		$response->assertStatus(200)
			->assertViewIs('transfer.create')
			->assertViewHasAll(['employees', 'departments', 'branches']);
	}

	/**
	 ** @test
	 **
	 ** Store should create a new transfer and redirect.
	 **/
	public function test_store_creates_transfer_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create transfer']);
		$user?->givePermissionTo('create transfer');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create([
			'user_id'    => $user?->id,
			'created_by' => $creatorId,
		]);
		$branch = Branch::factory()->create(['created_by' => $creatorId]);
		$dept  = Department::factory()->create(['created_by' => $creatorId]);

		$data = [
			'employeeId'   => $emp->id,
			'branchId'     => $branch->id,
			'departmentId' => $dept->id,
			'transferDate' => Carbon::today()->toDateString(),
			'description'  => 'Department change',
		];

		$response = $this->actingAs($user)->post(route('transfer.store'), $data);

		$response->assertRedirect(route('transfer.index'))
			->assertSessionHas('success', __('Transfer successfully created.'));
		$this->assertDatabaseHas('transfers', [
			'employee_id'   => $emp->id,
			'branch_id'     => $branch->id,
			'department_id' => $dept->id,
			'created_by'    => $creatorId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Store should fail validation when required fields are missing.
	 **/
	public function test_store_fails_validation_with_missing_fields()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create transfer']);
		$user?->givePermissionTo('create transfer');

		$response = $this->actingAs($user)->post(route('transfer.store'), []);

		$response->assertStatus(302)
			->assertSessionHasErrors(['employeeId', 'branchId', 'departmentId', 'transferDate']);
	}

	/**
	 ** @test
	 **
	 ** Edit should display form for owner with permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit transfer']);
		$user?->givePermissionTo('edit transfer');

		$creatorId = $user?->creatorId();
		$transfer = Transfer::factory()->create(['created_by' => $creatorId]);

		$response = $this->actingAs($user)->get(route('transfer.edit', $transfer));

		$response->assertStatus(200)
			->assertViewIs('transfer.edit')
			->assertViewHasAll(['transfer', 'employees', 'departments', 'branches']);
	}

	/**
	 ** @test
	 **
	 ** Update should modify the transfer and redirect.
	 **/
	public function test_update_modifies_transfer_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit transfer']);
		$user?->givePermissionTo('edit transfer');

		$creatorId = $user?->creatorId();
		$emp = Employee::factory()->create(['created_by' => $creatorId]);
		$branch = Branch::factory()->create(['created_by' => $creatorId]);
		$dept  = Department::factory()->create(['created_by' => $creatorId]);
		$transfer = Transfer::factory()->create(['created_by' => $creatorId]);

		$data = [
			'employeeId'   => $emp->id,
			'branchId'     => $branch->id,
			'departmentId' => $dept->id,
			'transferDate' => Carbon::tomorrow()->toDateString(),
			'description'  => 'Updated reason',
		];

		$response = $this->actingAs($user)->put(route('transfer.update', $transfer), $data);

		$response->assertRedirect(route('transfer.index'))
			->assertSessionHas('success', __('Transfer successfully updated.'));
		$this->assertDatabaseHas('transfers', [
			'id'            => $transfer->id,
			'description'   => 'Updated reason',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete the transfer and redirect.
	 **/
	public function test_destroy_deletes_transfer_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete transfer']);
		$user?->givePermissionTo('delete transfer');

		$creatorId = $user?->creatorId();
		$transfer = Transfer::factory()->create(['created_by' => $creatorId]);

		$response = $this->actingAs($user)->delete(route('transfer.destroy', $transfer));

		$response->assertRedirect(route('transfer.index'))
			->assertSessionHas('success', __('Transfer successfully deleted.'));
		$this->assertModelMissing($transfer);
	}

	/**
	 ** @test
	 **
	 ** Destroy should deny non-owner.
	 **/
	public function test_destroy_denies_non_owner()
	{
		$owner = User::factory()->create();
		$stranger = User::factory()->create();
		Permission::create(['name' => 'delete transfer']);
		$stranger->givePermissionTo('delete transfer');

		$transfer = Transfer::factory()->create(['created_by' => $owner->creatorId()]);

		$response = $this->actingAs($stranger)->delete(route('transfer.destroy', $transfer));

		$response->assertRedirect(route('transfer.index'))
			->assertSessionHas('error');
		$this->assertModelExists($transfer);
	}

	/**
	 ** @test
	 **
	 ** Owner may view the transfer via HTML and sees the correct view.
	 **/
	public function owner_can_view_html_show_page()
	{
		$response = $this->get(route('transfer.show', $this->transfer));

		$response->assertOk()
			->assertViewIs('transfer.show')
			->assertViewHas('transfer', fn ($t) => $t->id === $this->transfer->id);
	}

	/**
	 ** @test
	 **
	 ** Owner may request JSON and receives the transfer as JSON.
	 **/
	public function owner_can_view_json_show_endpoint()
	{
		$response = $this->getJson(route('transfer.show', $this->transfer));

		$response->assertOk()
			->assertExactJson($this->transfer->toArray());
	}

	/**
	 ** @test
	 **
	 ** A non-owner company user receives 403 Forbidden.
	 **/
	public function non_owner_gets_forbidden()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('transfer.show', $this->transfer));
		$response->assertStatus(403);

		// JSON path also forbidden
		$json = $this->getJson(route('transfer.show', $this->transfer));
		$json->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the show endpoint.
	 **/
	public function guest_is_redirected_to_login()
	{
		auth()->logout();

		$html = $this->get(route('transfer.show', $this->transfer));
		$html->assertRedirect(); // to login

		$json = $this->getJson(route('transfer.show', $this->transfer));
		$json->assertStatus(302); // redirect guest
	}

	/**
	 ** @test
	 **
	 ** When Gate denies 'manage transfer', user is sent back to index.
	 **/
	public function permission_denied_redirects_to_index()
	{
		Gate::before(fn () => false);

		$response = $this->actingAs($this->company)
			->get(route('transfer.show', $this->transfer));

		$response->assertRedirect(route('transfer.index'));
	}
}
