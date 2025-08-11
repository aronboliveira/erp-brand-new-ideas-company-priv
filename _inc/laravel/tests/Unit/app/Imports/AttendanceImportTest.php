<?php

namespace Tests\Unit\Imports;

use App\Imports\AttendanceImport;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Auth, Log};
use Mockery;
use Tests\TestCase;

class AttendanceImportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** If two consecutive empty cells appear before any non-empty cell,
	 ** the header row is considered “not found”, the method logs an error,
	 ** and returns null.
	 **/
	public function model_logs_error_and_returns_null_when_header_not_found(): void
	{
		Log::spy();

		$import = new AttendanceImport();
		$row   = ['', '', '', '', ''];

		$result = $import->model($row);

		$this->assertNull($result);
		Log::shouldHaveReceived('error')
			->with('App\\Imports\\AttendanceImport::model header row not found')
			->once();
	}

	/**
	 ** @test
	 **
	 ** A row containing the first non-empty cell marks the header start,
	 ** sets `headerFound` to true, records `headerStartIndex`, and returns null.
	 **/
	public function model_detects_header_row_and_sets_properties(): void
	{
		$import   = new AttendanceImport();
		$headerRow = ['', 'employee_id', 'date', 'status'];

		$result = $import->model($headerRow);
		$this->assertNull($result);

		$ref = new \ReflectionClass($import);

		$foundProp = $ref->getProperty('headerFound');
		$foundProp->setAccessible(true);
		$this->assertTrue($foundProp->getValue($import));

		$indexProp = $ref->getProperty('headerStartIndex');
		$indexProp->setAccessible(true);
		$this->assertSame(1, $indexProp->getValue($import));
	}

	/**
	 ** @test
	 **
	 ** After header detection, the next data row is mapped to fields,
	 ** an EmployeeAttendance is created, and returned.
	 **/
	public function model_creates_record_with_mapped_fields_after_header(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$import = new AttendanceImport();
		// detect header
		$import->model(['', 'employee_id', 'date', 'status', 'clock_in', 'clock_out', 'late', 'early_leaving', 'overtime', 'total_rest']);

		$dataRow = [
			null,
			'42',
			'2025-05-15',
			'Present',
			'08:00',
			'17:00',
			'0',
			'0',
			'1.5',
			'0.5',
		];

		$result = $import->model($dataRow);

		$this->assertInstanceOf(EmployeeAttendance::class, $result);
		$this->assertDatabaseHas('employee_attendances', [
			'employee_id'    => '42',
			'date'           => '2025-05-15',
			'status'         => 'Present',
			'clock_in'       => '08:00',
			'clock_out'      => '17:00',
			'late'           => '0',
			'early_leaving'  => '0',
			'overtime'       => '1.5',
			'total_rest'     => '0.5',
			'created_by'     => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** If `EmployeeAttendance::create()` throws an exception during import,
	 ** the method logs an error with that message and returns null.
	 **/
	public function model_logs_error_and_returns_null_on_create_exception(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$import = new AttendanceImport();
		// detect header
		$import->model(['', 'employee_id', 'date', 'status', 'clock_in', 'clock_out', 'late', 'early_leaving', 'overtime', 'total_rest']);

		// mock the model create to throw
		Mockery::mock('alias:' . EmployeeAttendance::class)
			->shouldReceive('create')
			->andThrow(new \Exception('fail-create'));

		Log::spy();

		$dataRow = [
			null,
			'42', '2025-05-15', 'Present',
			'08:00', '17:00', '0', '0', '1.5', '0.5',
		];
		$result = $import->model($dataRow);

		$this->assertNull($result);
		Log::shouldHaveReceived('error')
			->with('App\\Imports\\AttendanceImport::model failed importing row: fail-create')
			->once();
	}
}
