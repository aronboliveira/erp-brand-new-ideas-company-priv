<?php
// tests/Unit/Imports/EmployeesImportTest.php

namespace Tests\Unit\Imports;

use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Auth, Log};
use Mockery;
use Tests\TestCase;

class EmployeesImportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** It logs a warning and returns null when a required field is missing.
	 **/
	public function model_logs_warning_and_returns_null_when_required_field_missing(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		Log::spy();

		$import = new EmployeesImport();
		// 'name' is required but omitted
		$row   = ['email' => 'test@example.com', 'employee_id' => 'E123'];

		$result = $import->model($row);

		$this->assertNull($result);
		Log::shouldHaveReceived('warning')
			->with('App\\Imports\\EmployeesImport::model missing required field: name')
			->once();
	}

	/**
	 ** @test
	 **
	 ** It returns an Employee instance with only valid, non-empty,
	 ** non-NaN values, ignores unknown columns, and sets `created_by`
	 ** to the authenticated user ID.
	 **/
	public function model_returns_employee_with_mapped_and_cleaned_attributes(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		// prepare a row: empty email should become null
		$row = [
			'name'        => 'Alice',
			'email'       => '',            // becomes null
			'employee_id' => 'EMP001',
			'foo'         => 'bar',         // not fillable, ignored
		];

		$import = new EmployeesImport();
		$result = $import->model($row);

		$this->assertInstanceOf(Employee::class, $result);
		$this->assertSame('Alice',      $result->name);
		$this->assertNull($result->email);
		$this->assertSame('EMP001',     $result->employee_id);
		$this->assertSame($user?->id,     $result->created_by);
	}

	/**
	 ** @test
	 **
	 ** If an exception occurs during mapping (e.g., `getFillable()` throws),
	 ** it logs an error with the exception message and returns null.
	 **/
	public function model_logs_error_and_returns_null_on_exception(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		// Alias-mock Employee so getFillable() throws
		Mockery::mock('alias:App\Models\Employee')
			->shouldReceive('getFillable')
			->andThrow(new \Exception('fail-fillable'));

		Log::spy();

		$import = new EmployeesImport();
		$row   = ['name' => 'Bob', 'email' => 'b@example.com', 'employee_id' => 'E002'];

		$result = $import->model($row);

		$this->assertNull($result);
		Log::shouldHaveReceived('error')
			->with('App\\Imports\\EmployeesImport::model failed: fail-fillable')
			->once();
	}
}
