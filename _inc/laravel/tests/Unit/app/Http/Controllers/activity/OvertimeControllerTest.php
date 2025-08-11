<?php

namespace Tests\Feature;

use App\Models\{User, Employee, Overtime};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class OvertimeControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected int $ownerId;

	public function setUp(): void
	{
		parent::setUp();

		// Create & authenticate a non-employee user
		$this->user = User::factory()->create([
			'type' => 'Admin',
		]);
		$this->actingAs($this->user);
		$this->ownerId = $this->user->creatorId();

		// Grant all relevant permissions
		Gate::define('manage overtime', fn () => true);
		Gate::define('create overtime', fn () => true);
		Gate::define('edit overtime', fn () => true);
		Gate::define('delete overtime', fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Ensure that accessing the index endpoint without the
	 ** 'manage overtime' permission returns a 403 Forbidden response.
	 **/
	public function test_index_requires_manage_permission()
	{
		Gate::define('manage overtime', fn () => false);

		$this->get(route('overtime.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** The index endpoint should display all overtime records
	 ** when the user has the 'manage overtime' permission.
	 **/
	public function test_index_shows_all_overtimes()
	{
		Overtime::factory()->count(2)->create(['created_by' => $this->ownerId]);

		$response = $this->get(route('overtime.index'));

		$response->assertStatus(200)
			->assertViewIs('overtime.index')
			->assertViewHas('overtimes', function ($overtimes) {
				return $overtimes->count() === 2;
			});
	}

	/**
	 ** @test
	 **
	 ** The create form should display the correct employee
	 ** when provided a valid employee ID.
	 **/
	public function test_overtimeCreate_displays_form_with_employee()
	{
		$emp = Employee::factory()->create(['created_by' => $this->ownerId]);
		$response = $this->get(route('overtime.create', ['id' => $emp->id]));

		$response->assertStatus(200)
			->assertViewIs('overtime.create')
			->assertViewHas('employee', function ($e) use ($emp) {
				return $e->id === $emp->id;
			});
	}

	/**
	 ** @test
	 **
	 ** Submitting the store endpoint without required data
	 ** should redirect back with an error message.
	 **/
	public function test_store_validation_failure()
	{
		$this->post(route('overtime.store'), [])
			->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** A valid store request should create a new overtime record
	 ** and redirect back with a success message.
	 **/
	public function test_store_creates_overtime()
	{
		$emp = Employee::factory()->create(['created_by' => $this->ownerId]);

		$payload = [
			'employee_id'    => $emp->id,
			'title'          => 'Overnight',
			'number_of_days' => 1,
			'hours'          => 5,
			'rate'           => 100,
		];

		$this->post(route('overtime.store'), $payload)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('overtimes', [
			'employee_id' => $emp->id,
			'title'       => 'Overnight',
			'created_by'  => $this->ownerId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Editing requires both the 'edit overtime' permission
	 ** and ownership; unauthorized attempts return 401.
	 **/
	public function test_edit_requires_edit_permission_and_owner()
	{
		$ot = Overtime::factory()->create(['created_by' => $this->ownerId]);

		// no edit permission
		Gate::define('edit overtime', fn () => false);
		$this->get(route('overtime.edit', ['overtime' => $ot->id]))
			->assertStatus(401);

		// wrong owner
		Gate::define('edit overtime', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$this->get(route('overtime.edit', ['overtime' => $ot->id]))
			->assertStatus(401);

		// correct
		$this->actingAs($this->user);
		$response = $this->get(route('overtime.edit', ['overtime' => $ot->id]));
		$response->assertStatus(200)
			->assertViewIs('overtime.edit')
			->assertViewHas('overtime', fn ($v) => $v->id === $ot->id);
	}

	/**
	 ** @test
	 **
	 ** The update endpoint should validate input and
	 ** apply changes for valid data, returning a success redirect.
	 **/
	public function test_update_validation_and_success()
	{
		$ot = Overtime::factory()->create([
			'title'          => 'Old',
			'number_of_days' => 2,
			'hours'          => 8,
			'rate'           => 50,
			'created_by'     => $this->ownerId,
		]);

		// validation error
		$this->put(route('overtime.update', ['overtime' => $ot->id]), ['title' => ''])
			->assertStatus(302)
			->assertSessionHas('error');

		// successful update
		$data = [
			'title'          => 'New Title',
			'number_of_days' => 3,
			'hours'          => 6,
			'rate'           => 75,
		];
		$this->put(route('overtime.update', ['overtime' => $ot->id]), $data)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('overtimes', [
			'id'             => $ot->id,
			'title'          => 'New Title',
			'number_of_days' => 3,
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroying an overtime record requires both the
	 ** 'delete overtime' permission and ownership; failures return errors.
	 **/
	public function test_destroy_requires_delete_permission_and_owner()
	{
		$ot = Overtime::factory()->create(['created_by' => $this->ownerId]);

		// no delete permission
		Gate::define('delete overtime', fn () => false);
		$this->delete(route('overtime.destroy', ['overtime' => $ot->id]))
			->assertRedirect()
			->assertSessionHas('error');

		// wrong owner
		Gate::define('delete overtime', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$this->delete(route('overtime.destroy', ['overtime' => $ot->id]))
			->assertRedirect()
			->assertSessionHas('error');

		// proper delete
		$this->actingAs($this->user);
		$this->delete(route('overtime.destroy', ['overtime' => $ot->id]))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('overtimes', ['id' => $ot->id]);
	}
}
