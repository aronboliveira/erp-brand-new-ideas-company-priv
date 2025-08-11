<?php

namespace Tests\Unit\Exports;

use App\Exports\EmployeeExport;
use App\Models\{Branch, Department, Designation, Employee, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

class EmployeeExportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The `collection()` method **must** return only the `Employee`
	 ** rows created by the currently-authenticated user, strip every
	 ** attribute listed in `REMOVED_ATTRIBUTES`, **and** append the
	 ** extra fields produced by the private `enrich()` helper:
	 **   • branch
	 **   • department
	 **   • designation
	 **   • salary
	 **/
	public function collection_returns_enriched_and_sanitized_rows(): void
	{
		$user  = User::factory()->create();
		$other = User::factory()->create();

		$branch     = Branch::factory()->create();
		$department = Department::factory()->create();
		$designation = Designation::factory()->create();

		// Two employees owned by $user, one by $other
		Employee::factory()->count(2)->create([
			'created_by'     => $user?->creatorId(),
			'branch_id'      => $branch->id,
			'department_id'  => $department->id,
			'designation_id' => $designation->id,
		]);

		Employee::factory()->create([
			'created_by'     => $other->creatorId(),
			'branch_id'      => $branch->id,
			'department_id'  => $department->id,
			'designation_id' => $designation->id,
		]);

		Auth::login($user);

		$export    = new EmployeeExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(2, $collection); // belongs only to $user

		$collection->each(function ($emp) {
			// Removed attributes should be absent
			foreach ([
				'id', 'password', 'userId', 'employeeId', 'documents',
				'salary_type', 'taxPayerId', 'isActive', 'createdBy',
				'createdAt', 'updatedAt'
			] as $attr) {
				$this->assertFalse(isset($emp->{$attr}), "Attribute {$attr} still present.");
			}

			// Enriched keys **must** exist
			foreach (['branch', 'department', 'designation', 'salary'] as $key) {
				$this->assertTrue(isset($emp[$key]), "{$key} field missing.");
			}
		});
	}

	/**
	 ** @test
	 **
	 ** The `headings()` method should yield the exact column names
	 ** expected by the import template (order is **significant**).
	 **/
	public function headings_return_expected_labels(): void
	{
		$export  = new EmployeeExport();
		$expected = [
			'Name', 'Date of Birth', 'Gender', 'Phone Number', 'Address',
			'Email ID', 'Branch', 'Department', 'Designation', 'Date of Join',
			'Account Holder Name', 'Account Number', 'Bank Name',
			'Bank Identifier Code', 'Branch Location', 'Salary'
		];

		$this->assertSame($expected, $export->headings());
	}

	/**
	 ** @test
	 **
	 ** The `registerEvents()` implementation must register exactly one
	 ** **AfterSheet** listener mapped to a **callable** handler.
	 **/
	public function register_events_maps_aftersheet_callable(): void
	{
		$export = new EmployeeExport();
		$events = $export->registerEvents();

		$this->assertCount(1, $events);
		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
