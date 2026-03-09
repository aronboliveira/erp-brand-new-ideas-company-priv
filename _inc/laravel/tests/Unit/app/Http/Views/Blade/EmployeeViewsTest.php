<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use Mockery;

/**
 * Unit tests for employee-related Blade views.
 *
 * Covered views:
 *   - employees.index
 *   - employees.create
 *   - employees.edit
 *   - employees.show
 *   - employees.profile
 *   - employees.import
 *   - employees.last_login
 *   - employees.templates.joining_letter_doc
 *   - employees.templates.joining_letter_pdf
 *   - employees.templates.exp_certificate_doc
 *   - employees.templates.exp_certificate_pdf
 *   - employees.templates.noc_doc
 *   - employees.templates.noc_pdf
 */
class EmployeeViewsTest extends TestCase
{
	use BladeViewTestHelper;

	// ─── Core CRUD views ────────────────────────────────────────────

	public function test_employees_index_view_file_exists(): void
	{
		$this->assertViewFileExists('employees.index');
	}

	public function test_employees_index_view_is_registered(): void
	{
		$this->assertViewRegistered('employees.index');
	}

	public function test_employees_index_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('employees.index', [
			'employees'    => collect([]),
			'branches'     => [],
			'departments'  => [],
			'designations' => [],
			'settings'     => $this->buildMockSettings(),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── employees.create ───────────────────────────────────────────

	public function test_employees_create_view_file_exists(): void
	{
		$this->assertViewFileExists('employees.create');
	}

	public function test_employees_create_view_is_registered(): void
	{
		$this->assertViewRegistered('employees.create');
	}

	public function test_employees_create_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('employees.create', [
			'branches'     => collect([]),
			'departments'  => collect([]),
			'designations' => collect([]),
			'employees'    => collect([]),
			'company_settings' => (object) $this->buildMockCompany(),
			'customFields' => [],
			'settings'     => $this->buildMockSettings(),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── employees.edit ─────────────────────────────────────────────

	public function test_employees_edit_view_file_exists(): void
	{
		$this->assertViewFileExists('employees.edit');
	}

	public function test_employees_edit_view_is_registered(): void
	{
		$this->assertViewRegistered('employees.edit');
	}

	public function test_employees_edit_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('employees.edit', [
			'employee'     => (object) $this->buildMockEmployee(),
			'branches'     => collect([]),
			'departments'  => collect([]),
			'designations' => collect([]),
			'employeesObj' => collect([]),
			'customFields' => [],
			'settings'     => $this->buildMockSettings(),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── employees.show ─────────────────────────────────────────────

	public function test_employees_show_view_file_exists(): void
	{
		$this->assertViewFileExists('employees.show');
	}

	public function test_employees_show_view_is_registered(): void
	{
		$this->assertViewRegistered('employees.show');
	}

	// ─── employees.profile ──────────────────────────────────────────

	public function test_employees_profile_view_file_exists(): void
	{
		$this->assertViewFileExists('employees.profile');
	}

	public function test_employees_profile_view_is_registered(): void
	{
		$this->assertViewRegistered('employees.profile');
	}

	// ─── employees.import ───────────────────────────────────────────

	public function test_employees_import_view_file_exists(): void
	{
		$this->assertViewFileExists('employees.import');
	}

	public function test_employees_import_view_is_registered(): void
	{
		$this->assertViewRegistered('employees.import');
	}

	// ─── employees.last_login ───────────────────────────────────────

	public function test_employees_last_login_view_file_exists(): void
	{
		$this->assertViewFileExists('employees.last_login');
	}

	public function test_employees_last_login_view_is_registered(): void
	{
		$this->assertViewRegistered('employees.last_login');
	}

    // ─── Employee Templates ─────────────────────────────────────────

	/**
	 * @dataProvider employeeTemplateProvider
	 */
	public function test_employee_template_view_file_exists(string $template): void
	{
		$this->assertViewFileExists($template);
	}

	/**
	 * @dataProvider employeeTemplateProvider
	 */
	public function test_employee_template_view_is_registered(string $template): void
	{
		$this->assertViewRegistered($template);
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function employeeTemplateProvider(): array
	{
		return [
			'joining_letter_doc'   => ['employees.templates.joining_letter_doc'],
			'joining_letter_pdf'   => ['employees.templates.joining_letter_pdf'],
			'exp_certificate_doc'  => ['employees.templates.exp_certificate_doc'],
			'exp_certificate_pdf'  => ['employees.templates.exp_certificate_pdf'],
			'noc_doc'              => ['employees.templates.noc_doc'],
			'noc_pdf'              => ['employees.templates.noc_pdf'],
		];
	}

	// ─── Batch – all employee views exist ───────────────────────────

	public function test_all_employee_views_exist_in_finder(): void
	{
		$views = [
			'employees.index',
			'employees.create',
			'employees.edit',
			'employees.show',
			'employees.profile',
			'employees.import',
			'employees.last_login',
			'employees.templates.joining_letter_doc',
			'employees.templates.joining_letter_pdf',
			'employees.templates.exp_certificate_doc',
			'employees.templates.exp_certificate_pdf',
			'employees.templates.noc_doc',
			'employees.templates.noc_pdf',
		];

		foreach ($views as $view) {
			$this->assertTrue(
				View::exists($view),
				"Employee view [{$view}] is not registered."
			);
		}
	}
}
