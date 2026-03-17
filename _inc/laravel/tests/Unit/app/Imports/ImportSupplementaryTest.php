<?php

namespace Tests\Unit\Imports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{Auth, Log};
use App\Imports\{
	AttendanceImport,
	CustomerImport,
	EmployeesImport,
	ProductServiceImport,
	VendorImport
};
use App\Models\{Employee, EmployeeAttendance, User};
use ReflectionProperty;

/**
 * Supplementary test-suite covering edge-cases, performance / resource
 * limits, Python-delegation presence, and empty-input handling across
 * all 5 Import classes.
 *
 * Complements the per-class test files in the same directory.
 */
class ImportSupplementaryTest extends TestCase
{
	use DatabaseTransactions;

	private const TIME_LIMIT  = 5.0;
	private const MEM_LIMIT_MB = 50;

	// ─── helper ──────────────────────────────────────────────────────

	private function assertWithinLimits(callable $fn, string $label): mixed
	{
		$memBefore = memory_get_usage(true);
		$start     = microtime(true);
		$result    = $fn();
		$elapsed   = microtime(true) - $start;
		$memMb     = (memory_get_usage(true) - $memBefore) / 1048576;
		$this->assertLessThan(self::TIME_LIMIT, $elapsed, "{$label}: took {$elapsed}s");
		$this->assertLessThan(self::MEM_LIMIT_MB, $memMb, "{$label}: used {$memMb}MB");
		return $result;
	}

	// ═════════════════════════════════════════════════════════════════
	// 1. PYTHON-DELEGATION PRESENCE
	// ═════════════════════════════════════════════════════════════════

	/** @test */
	public function all_importers_expose_import_via_python(): void
	{
		$classes = [
			AttendanceImport::class,
			CustomerImport::class,
			EmployeesImport::class,
			ProductServiceImport::class,
			VendorImport::class,
		];
		foreach ($classes as $cls) {
			$this->assertTrue(
				method_exists($cls, 'importViaPython'),
				"{$cls} must have importViaPython() via DelegatesPythonImport trait."
			);
		}
	}

	/** @test */
	public function import_via_python_returns_array(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$import = new VendorImport();
		$result = $import->importViaPython([]);

		$this->assertIsArray($result, 'importViaPython() must always return an array.');
	}

	// ═════════════════════════════════════════════════════════════════
	// 2. EMPTY-INPUT EDGE CASES
	// ═════════════════════════════════════════════════════════════════

	/** @test */
	public function attendance_import_handles_all_null_row(): void
	{
		$import = new AttendanceImport();
		$result = $import->model([null, null, null, null]);
		$this->assertNull($result, 'All-null row should return null (header not found).');
	}

	/** @test */
	public function customer_import_handles_empty_row_unauthenticated(): void
	{
		$import = new CustomerImport();
		$result = $import->model([]);
		$this->assertNull(
			$result,
			'Empty row with no auth should return null (redirect is suppressed).'
		);
	}

	/** @test */
	public function customer_import_handles_empty_row_authenticated(): void
	{
		Auth::login(User::factory()->create());
		Log::spy();
		$import = new CustomerImport();
		$result = $import->model([]);
		$this->assertNull($result, 'Empty row should return null (header not found).');
		Log::shouldHaveReceived('error')->atLeast()->once();
	}

	/** @test */
	public function employees_import_handles_empty_row(): void
	{
		Auth::login(User::factory()->create());
		Log::spy();
		$import = new EmployeesImport();
		$result = $import->model([]);
		$this->assertNull($result, 'Empty row should return null due to missing required name.');
		Log::shouldHaveReceived('warning')->atLeast()->once();
	}

	/** @test */
	public function product_service_import_handles_empty_row(): void
	{
		Auth::login(User::factory()->create());
		Log::spy();
		$import = new ProductServiceImport();
		$result = $import->model([]);
		$this->assertNull($result, 'Empty row should return null (header not found).');
	}

	/** @test */
	public function vendor_import_handles_all_empty_row(): void
	{
		Log::spy();
		$import = new VendorImport();
		$result = $import->model(['', '', '']);
		$this->assertNull($result, 'All-empty row should return null (header not found).');
		Log::shouldHaveReceived('error')->atLeast()->once();
	}

	// ═════════════════════════════════════════════════════════════════
	// 3. PERFORMANCE / RESOURCE-LIMIT TESTS
	//    Simulate processing N data rows through the importer pipeline.
	// ═════════════════════════════════════════════════════════════════

	/** @test */
	public function attendance_import_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		$emp = Employee::factory()->create(['created_by' => $user->id]);

		$import = new AttendanceImport();
		// header row
		$import->model([
			'employee_id',
			'date',
			'status',
			'clock_in',
			'clock_out',
			'late',
			'early_leaving',
			'overtime',
			'total_rest',
		]);

		$this->assertWithinLimits(function () use ($import, $emp) {
			for ($i = 0; $i < 15; ++$i) {
				$day = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
				$import->model([
					$emp->id,
					"2025-06-{$day}",
					'present',
					'08:00',
					'17:00',
					'00:00:00',
					'00:00:00',
					'00:00:00',
					'00:00:00',
				]);
			}
		}, 'AttendanceImport×15');
	}

	/** @test */
	public function customer_import_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$fillable = (new \App\Models\Customer())->getFillable();
		$keys     = array_slice($fillable, 0, 2); // e.g. ['name', 'email']

		$import = new CustomerImport();
		$import->model([$keys[0], $keys[1]]);

		$this->assertWithinLimits(function () use ($import, $keys) {
			for ($i = 0; $i < 15; ++$i) {
				$import->model(["Customer {$i}", "cust{$i}@test.com"]);
			}
		}, 'CustomerImport×15');
	}

	/** @test */
	public function employees_import_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$import = new EmployeesImport();

		$this->assertWithinLimits(function () use ($import) {
			for ($i = 0; $i < 15; ++$i) {
				$import->model([
					'name'        => "Employee {$i}",
					'email'       => "emp{$i}@test.com",
					'employee_id' => "EMP-PERF-{$i}",
				]);
			}
		}, 'EmployeesImport×15');
	}

	/** @test */
	public function product_service_import_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$fillable = (new \App\Models\ProductService())->getFillable();
		$keys     = array_slice($fillable, 0, 2);

		$import = new ProductServiceImport();
		$import->model([$keys[0], $keys[1]]);

		$this->assertWithinLimits(function () use ($import) {
			for ($i = 0; $i < 15; ++$i) {
				$import->model(["Val-A-{$i}", "Val-B-{$i}", '', '']);
			}
		}, 'ProductServiceImport×15');
	}

	/** @test */
	public function vendor_import_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$import = new VendorImport();
		// header
		$import->model([
			'vendor_id',
			'name',
			'email',
			'password',
			'contact',
			'avatar',
			'is_active',
			'created_by',
			'email_verified_at',
			'billing_name',
			'billing_country',
			'billing_state',
			'billing_city',
			'billing_phone',
			'billing_zip',
			'billing_address',
			'shipping_name',
			'shipping_country',
			'shipping_state',
			'shipping_city',
			'shipping_phone',
			'shipping_zip',
			'shipping_address',
		]);

		$this->assertWithinLimits(function () use ($import, $user) {
			for ($i = 0; $i < 15; ++$i) {
				$import->model([
					$user->id,
					"Vendor {$i}",
					"vendor{$i}@test.com",
					'password123',
					"555000{$i}",
					null,
					1,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
				]);
			}
		}, 'VendorImport×15');
	}
}
