<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Allowance, AllowanceOption, Employee, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AllowanceControllerTest extends TestCase
{
	use RefreshDatabase, WithFaker;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
		DB::shouldReceive('transaction')->andReturnUsing(function ($closure) {
			return $closure();
		});
	}

	/**
	 ** @test
	 **
	 ** When a user with permission submits valid allowance data,
	 ** the controller should create a new allowance record
	 ** and redirect back.
	 **/
	public function test_store_creates_allowance_successfully()
	{
		$user    = User::factory()->create(['type' => 'company']);
		$employee = Employee::factory()->create(['created_by' => $user?->id]);
		$option  = AllowanceOption::factory()->create(['created_by' => $user?->id]);

		$this->actingAs($user);

		$response = $this->post(route('allowance.store'), [
			'employee_id'       => $employee->id,
			'allowance_option'  => $option->id,
			'title'             => 'Health Benefit',
			'type'              => 'fixed',
			'amount'            => 300,
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('allowances', [
			'employee_id'      => $employee->id,
			'title'            => 'Health Benefit',
			'amount'           => 300,
			'created_by'       => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** If a user lacks the required permission,
	 ** the store action should deny access and redirect.
	 **/
	public function test_store_fails_without_permission()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->syncPermissions([]); // revoke all

		$response = $this->post(route('allowance.store'), []);

		$response->assertStatus(302); // authorization denial fallback
	}

	/**
	 ** @test
	 **
	 ** When updating an existing allowance with valid data,
	 ** the controller should modify the record and redirect back.
	 **/
	public function test_update_modifies_allowance()
	{
		$user     = User::factory()->create();
		$option   = AllowanceOption::factory()->create(['created_by' => $user?->id]);
		$allowance = Allowance::factory()->create([
			'created_by'       => $user?->id,
			'allowance_option' => $option->id,
		]);

		$this->actingAs($user);

		$response = $this->put(route('allowance.update', $allowance), [
			'title'            => 'Updated Title',
			'type'             => 'fixed',
			'amount'           => 200,
			'allowance_option' => $option->id,
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('allowances', [
			'id'     => $allowance->id,
			'title'  => 'Updated Title',
			'amount' => 200,
		]);
	}

	/**
	 ** @test
	 **
	 ** A user can delete an existing allowance,
	 ** and the record should be removed from the database.
	 **/
	public function test_destroy_deletes_allowance()
	{
		$user     = User::factory()->create();
		$allowance = Allowance::factory()->create(['created_by' => $user?->id]);

		$this->actingAs($user);

		$response = $this->delete(route('allowance.destroy', $allowance));
		$response->assertRedirect();
		$this->assertDatabaseMissing('allowances', ['id' => $allowance->id]);
	}
}
