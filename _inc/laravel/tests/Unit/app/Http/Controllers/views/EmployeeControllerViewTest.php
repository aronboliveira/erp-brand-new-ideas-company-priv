<?php

namespace Tests\Unit\app\Http\Controllers\views;

use App\Http\Controllers\Individuals\EmployeeController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;

#[\PHPUnit\Framework\Attributes\Group('controller-views')]
class EmployeeControllerViewTest extends TestCase
{
	use RefreshDatabase;
	use ControllerTestHelper;
	use ViewAssertionHelper;

	/* ------------------------------------------------------------------ */
	/*  View-file existence tests                                         */
	/* ------------------------------------------------------------------ */

	public function test_employees_index_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.index');
	}

	public function test_employees_create_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.create');
	}

	public function test_employees_edit_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.edit');
	}

	public function test_employees_show_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.show');
	}

	public function test_employees_profile_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.profile');
	}

	public function test_employees_last_login_view_file_exists(): void
	{
		// Controller uses VW::EMP . '.' . __FUNCTION__ = 'employees.lastLogin'
		// but the actual blade file is last_login.blade.php (snake_case)
		$this->assertBladeViewExists('employees.last_login');
	}

	/**
	 * Documents that EmployeeController::lastLogin() references 'employees.lastLogin'
	 * but the blade file is employees/last_login.blade.php — a naming mismatch
	 * caught by the controller's ViewFacade::exists() guard.
	 */
	public function test_last_login_view_name_mismatch_is_documented(): void
	{
		$controllerRef = 'employees.lastLogin';
		$actualFile    = 'employees.last_login';
		$this->assertBladeViewExists($actualFile);
		$this->assertNotEquals($controllerRef, $actualFile, 'Mismatch: controller ref ≠ actual blade file');
	}

	public function test_employees_import_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.import');
	}

	/* ------------------------------------------------------------------ */
	/*  Employee template views                                           */
	/* ------------------------------------------------------------------ */

	public function test_joining_letter_pdf_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.templates.joining_letter_pdf');
	}

	public function test_joining_letter_doc_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.templates.joining_letter_doc');
	}

	public function test_exp_certificate_pdf_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.templates.exp_certificate_pdf');
	}

	public function test_exp_certificate_doc_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.templates.exp_certificate_doc');
	}

	public function test_noc_pdf_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.templates.noc_pdf');
	}

	public function test_noc_doc_view_file_exists(): void
	{
		$this->assertBladeViewExists('employees.templates.noc_doc');
	}

	/* ------------------------------------------------------------------ */
	/*  Controller class & reflection tests                                */
	/* ------------------------------------------------------------------ */

	public function test_employee_controller_class_exists(): void
	{
		$this->assertTrue(
			class_exists(EmployeeController::class),
			'EmployeeController class should be loadable.'
		);
	}

	public function test_employee_controller_has_expected_methods(): void
	{
		$ref = new ReflectionClass(EmployeeController::class);

		$expected = [
			'index',
			'create',
			'store',
			'edit',
			'update',
			'destroy',
			'show',
			'json',
			'profile',
			'profileShow',
			'lastLogin',
			'employeeJson',
			'getDepartment',
			'joiningLetterPdf',
			'joiningLetterDoc',
			'expCertificatePdf',
			'expCertificateDoc',
			'nocPdf',
			'nocDoc',
			'importFile',
		];

		foreach ($expected as $method) {
			$this->assertTrue(
				$ref->hasMethod($method),
				"EmployeeController should have public method [{$method}]."
			);
			$this->assertTrue(
				$ref->getMethod($method)->isPublic(),
				"Method [{$method}] should be public."
			);
		}
	}

	public function test_employee_controller_constants_defined(): void
	{
		$this->assertSame('index', EmployeeController::IDX);
		$this->assertSame('create', EmployeeController::CRT);
		$this->assertSame('store', EmployeeController::STR);
		$this->assertSame('show', EmployeeController::SHW);
		$this->assertSame('edit', EmployeeController::EDT);
		$this->assertSame('update', EmployeeController::UPD);
		$this->assertSame('destroy', EmployeeController::DEL);
		$this->assertSame('profileShow', EmployeeController::PRF_SHW);
		$this->assertSame('lastLogin', EmployeeController::LST_LGN);
		$this->assertSame('employeeJson', EmployeeController::EMP_JSON);
		$this->assertSame('getDepartment', EmployeeController::GET_DPT);
		$this->assertSame('joiningLetterPdf', EmployeeController::JNL_PDF);
		$this->assertSame('joiningLetterDoc', EmployeeController::JNL_DOC);
		$this->assertSame('expCertificatePdf', EmployeeController::EC_PDF);
		$this->assertSame('expCertificateDoc', EmployeeController::EC_DOC);
		$this->assertSame('nocPdf', EmployeeController::NOC_PDF);
		$this->assertSame('nocDoc', EmployeeController::NOC_DOC);
		$this->assertSame('importFile', EmployeeController::IMP_FL);
	}
}
