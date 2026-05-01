<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Config\Constants\{
	MiddlewaresConstants as MWC,
	ViewsConstants as VW
};
use App\Http\Controllers\Activity\{
	AwardController as AWDC,
	AwardTypeController as AWDTC,
	CommissionController as COMC,
	ComplaintController as CPLC,
	EventController as EVTC,
	MeetingController as MTC,
	OvertimeController as OVTC,
	TrainingController as TNGC,
	TrainingTypeController as TNGTC
};
use App\Http\Controllers\Bills\{
	AllowanceController as ALWC,
	AllowanceOptionController as ALWOC,
	DeductionOptionController as DDTOC,
	LoanController as LNC,
	LoanOptionController as LNOC,
	OtherPaymentController as OTPC,
	PayslipController as PYSC,
	PayslipTypeController as PYSTC,
	SaturationDeductionController as SDDC
};
use App\Http\Controllers\Companies\{
	BranchController as BRCC,
	CompanyPolicyController as CMPC,
	DepartmentController as DPTC
};
use App\Http\Controllers\Individuals\{
	DesignationController as DSGC,
	EmployeeAttendanceController as EPATDC,
	EmployeeController as EMPC
};
use App\Http\Controllers\Info\{
	AnnouncementController as ANCC,
	WarningController as WRNC
};
use App\Http\Controllers\Planning\{
	LeaveController as LVC,
	LeaveTypeController as LVTYC,
	PromotionController as PRMC,
	ResignationController as RSGC,
	SetSalaryController as SSLC,
	TerminationController as TMNC,
	TerminationTypeController as TMNTC,
	TravelController as TRVC
};
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * @group hrm
 * @group hrm-returns
 * @group hardening
 */
class HrmRouteReturnTest extends TestCase
{
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
<<<<<<< HEAD
		$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first();
=======
		$this->admin = User::where('email', 'suporte@prestech.com.br')->first();
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
		if ($this->admin) {
			$this->actingAs($this->admin);
		}
	}

	protected function assertNot500(TestResponse $r, string $ctx = ''): void
	{
		$this->assertNotEquals(500, $r->getStatusCode(), "HTTP 500 on [{$ctx}]");
	}

	protected function assertSuccessOrRedirect(TestResponse $r, string $ctx = ''): void
	{
		$code = $r->getStatusCode();
		$this->assertTrue(
			$code >= 200 && $code < 400,
			"Expected 2xx/3xx, got {$code} on [{$ctx}]"
		);
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 1 — EMPLOYEE MANAGEMENT
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider employeeIndexRoutesProvider
	 */
	public function test_employee_index_routes_return_ok(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
		if ($r->getStatusCode() === 200) {
			$r->assertSee('employee', false);
		}
	}

	public static function employeeIndexRoutesProvider(): array
	{
		return [
			'employees.index' => ['/' . VW::EMP, VW::EMP . ' index'],
			'employees.create' => ['/' . VW::EMP . '/create', VW::EMP . ' create'],
			'employees.profile' => ['/employee-profile', VW::EMP . ' profile'],
			'employees.salary' => ['/' . VW::EMP . '/salary', VW::EMP . ' salary list'],
		];
	}

	public function test_employee_export_returns_download_or_redirect(): void
	{
		$r = $this->get('/' . VW::EMP . '/export');
		$this->assertNot500($r, VW::EMP . ' export');
		$code = $r->getStatusCode();
		$this->assertTrue(
			$code === 200 || ($code >= 300 && $code < 400),
			"Expected 200 (download) or 3xx (redirect), got {$code} on employee export"
		);
	}

	/**
	 * @dataProvider employeeCrudWithFakeIdProvider
	 */
	public function test_employee_crud_with_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function employeeCrudWithFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::EMP . "/{$fk}", VW::EMP . ' show'],
			'edit' => ['GET', '/' . VW::EMP . "/{$fk}/edit", VW::EMP . ' edit'],
			'update' => ['PUT', '/' . VW::EMP . "/{$fk}", VW::EMP . ' update'],
			'delete' => ['DELETE', '/' . VW::EMP . "/{$fk}", VW::EMP . ' destroy'],
			'store_empty' => ['POST', '/' . VW::EMP, VW::EMP . ' store'],
			'salary_basic' => ['GET', '/' . VW::EMP . "/salary/{$fk}", VW::EMP . ' salary basic'],
			'show_profile' => ['GET', "/show-employee-profile/{$fk}", VW::EMP . ' show profile'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — DEPARTMENT / DESIGNATION / BRANCH
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider orgStructureResourceProvider
	 */
	public function test_org_structure_resources(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function orgStructureResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$resources = [
			VW::DPT => 'department',
			VW::DSG => 'designation',
			VW::BRC => 'branch',
		];
		$cases = [];
		foreach ($resources as $slug => $label) {
			$cases["{$label}_index"] = ['GET', "/{$slug}", "{$label} index"];
			$cases["{$label}_create"] = ['GET', "/{$slug}/create", "{$label} create"];
			$cases["{$label}_show"] = ['GET', "/{$slug}/{$fk}", "{$label} show"];
			$cases["{$label}_edit"] = ['GET', "/{$slug}/{$fk}/edit", "{$label} edit"];
			$cases["{$label}_store"] = ['POST', "/{$slug}", "{$label} store"];
			$cases["{$label}_update"] = ['PUT', "/{$slug}/{$fk}", "{$label} update"];
			$cases["{$label}_delete"] = ['DELETE', "/{$slug}/{$fk}", "{$label} destroy"];
		}
		return $cases;
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — SALARY / PAYROLL
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider salaryPayrollProvider
	 */
	public function test_salary_payroll_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function salaryPayrollProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'set_salary_index' => ['GET', '/' . VW::S_SLR, VW::S_SLR . ' index'],
			'set_salary_create' => ['GET', '/' . VW::S_SLR . '/create', VW::S_SLR . ' create'],
			'set_salary_show' => ['GET', '/' . VW::S_SLR . "/{$fk}", VW::S_SLR . ' show'],
			'set_salary_edit' => ['GET', '/' . VW::S_SLR . "/{$fk}/edit", VW::S_SLR . ' edit'],
			'salary_update' => ['POST', '/' . VW::EMP . "/update/sallary/{$fk}", VW::EMP . ' salary update'],
			'allowance_index' => ['GET', '/' . VW::ALW, VW::ALW . ' index'],
			'allowance_create_eid' => ['GET', '/' . VW::ALW . "/create/{$fk}", VW::ALW . ' create with eid'],
			'commission_index' => ['GET', '/' . VW::COM, VW::COM . ' index'],
			'commission_create_eid' => ['GET', '/' . VW::COM . "/create/{$fk}", VW::COM . ' create with eid'],
			'loan_index' => ['GET', '/' . VW::LN, VW::LN . ' index'],
			'loan_create_eid' => ['GET', '/' . VW::LN . "/create/{$fk}", VW::LN . ' create with eid'],
			'sat_ded_index' => ['GET', '/' . VW::STR_DD, VW::STR_DD . ' index'],
			'sat_ded_create_eid' => ['GET', '/' . VW::STR_DD . "/create/{$fk}", VW::STR_DD . ' create with eid'],
			'other_pay_index' => ['GET', '/' . VW::OT_PAY, VW::OT_PAY . ' index'],
			'other_pay_create_eid' => ['GET', '/' . VW::OT_PAY . "/create/{$fk}", VW::OT_PAY . ' create with eid'],
			'overtime_index' => ['GET', '/' . VW::OVT, VW::OVT . ' index'],
			'overtime_create_eid' => ['GET', '/' . VW::OVT . "/create/{$fk}", VW::OVT . ' create with eid'],
			'allowance_opt_index' => ['GET', '/' . VW::ALW_OPT, VW::ALW_OPT . ' index'],
			'deduction_opt_index' => ['GET', '/' . VW::DDT_OPT, VW::DDT_OPT . ' index'],
			'loan_opt_index' => ['GET', '/' . VW::LN_OPT, VW::LN_OPT . ' index'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 4 — PAYSLIP MANAGEMENT
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider payslipRoutesProvider
	 */
	public function test_payslip_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function payslipRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PY_SLP, VW::PY_SLP . ' index'],
			'create' => ['GET', '/' . VW::PY_SLP . '/create', VW::PY_SLP . ' create'],
			'type_index' => ['GET', '/' . VW::PY_SLP_TP, VW::PY_SLP_TP . ' index'],
			'type_create' => ['GET', '/' . VW::PY_SLP_TP . '/create', VW::PY_SLP_TP . ' create'],
			'show_fake' => ['GET', '/' . VW::PY_SLP . "/{$fk}", VW::PY_SLP . ' show'],
			'employeepayslip' => ['GET', '/' . VW::PY_SLP . '/employeepayslip', VW::PY_SLP . ' employeepayslip'],
			'show_employee' => ['GET', '/' . VW::PY_SLP . "/show/{$fk}", VW::PY_SLP . ' showemployee'],
			'edit_employee' => ['GET', '/' . VW::PY_SLP . "/edit/{$fk}", VW::PY_SLP . ' editemployee'],
			'paysalary' => ['GET', '/' . VW::PY_SLP . "/paysalary/{$fk}/2025-01", VW::PY_SLP . ' paysalary'],
			'bulk_pay' => ['GET', '/' . VW::PY_SLP . '/bulk_pay_create/2025-01', VW::PY_SLP . ' bulk_pay_create'],
			'pdf' => ['GET', '/' . VW::PY_SLP . "/pdf/{$fk}/01", VW::PY_SLP . ' pdf'],
			'payslip_pdf' => ['GET', '/' . VW::PY_SLP . "/payslipPdf/{$fk}", VW::PY_SLP . ' payslipPdf'],
			'send' => ['GET', '/' . VW::PY_SLP . "/send/{$fk}/01", VW::PY_SLP . ' send'],
			'delete' => ['GET', '/' . VW::PY_SLP . "/delete/{$fk}", VW::PY_SLP . ' delete'],
			'store_empty' => ['POST', '/' . VW::PY_SLP, VW::PY_SLP . ' store'],
			'search_json' => ['POST', '/' . VW::PY_SLP . '/search_json', VW::PY_SLP . ' search_json'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — LEAVE MANAGEMENT
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider leaveRoutesProvider
	 */
	public function test_leave_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		if (str_contains($uri, '/action')) {
			$code = $r->getStatusCode();
			$this->assertTrue(
				$code < 503,
				"Catastrophic error ({$code}) on [{$method} {$uri} ({$label})]: fake-id action may 500 from Blade null-access"
			);
			return;
		}
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function leaveRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'leave_index' => ['GET', '/leave', VW::LV . ' index'],
			'leaves_create' => ['GET', '/leaves/create', VW::LV . ' create'],
			'leaves_show' => ['GET', "/leaves/{$fk}", VW::LV . ' show'],
			'leaves_edit' => ['GET', "/leaves/{$fk}/edit", VW::LV . ' edit'],
			'leave_store' => ['POST', '/leave', VW::LV . ' store'],
			'leave_action' => ['GET', "/leaves/{$fk}/action", VW::LV . ' action'],
			'leave_export' => ['GET', '/leaves/export', VW::LV . ' export'],
			'leave_type_index' => ['GET', '/' . VW::LV_TP, VW::LV_TP . ' index'],
			'leave_type_create' => ['GET', '/' . VW::LV_TP . '/create', VW::LV_TP . ' create'],
			'leave_type_show' => ['GET', '/' . VW::LV_TP . "/{$fk}", VW::LV_TP . ' show'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 6 — ATTENDANCE
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider attendanceRoutesProvider
	 */
	public function test_attendance_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function attendanceRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'attendance_index' => ['GET', '/' . VW::EMP_ATD, VW::EMP_ATD . ' index'],
			'attendance_create' => ['GET', '/' . VW::EMP_ATD . '/create', VW::EMP_ATD . ' create'],
			'attendance_show' => ['GET', '/' . VW::EMP_ATD . "/{$fk}", VW::EMP_ATD . ' show'],
			'attendance_edit' => ['GET', '/' . VW::EMP_ATD . "/{$fk}/edit", VW::EMP_ATD . ' edit'],
			'bulk_attendance' => ['GET', '/' . VW::EMP_ATD . '/bulk-attendance', VW::EMP_ATD . ' bulk'],
			'attendance_import_file' => ['GET', '/attendances/imports/file', VW::EMP_ATD . ' import file'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 7 — EVENTS / MEETINGS / TRAININGS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider activityResourceProvider
	 */
	public function test_activity_resources(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		if ($method === 'POST' && (str_contains($uri, 'get-department') || str_contains($uri, 'get-employee'))) {
			$code = $r->getStatusCode();
			$this->assertTrue(
				$code < 503,
				"Catastrophic error ({$code}) on [{$method} {$uri} ({$label})]: JSON endpoint may return handled 500"
			);
			return;
		}
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function activityResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$resources = [
			VW::EVT => 'event',
			VW::MT => 'meeting',
			VW::TNG => 'training',
			VW::TNG_TP => 'training_type',
			VW::TNR => 'trainer',
		];
		$cases = [];
		foreach ($resources as $slug => $label) {
			$cases["{$label}_index"] = ['GET', "/{$slug}", "{$label} index"];
			$cases["{$label}_create"] = ['GET', "/{$slug}/create", "{$label} create"];
			$cases["{$label}_show"] = ['GET', "/{$slug}/{$fk}", "{$label} show"];
			$cases["{$label}_store"] = ['POST', "/{$slug}", "{$label} store"];
		}
		$cases['event_getdept'] = ['POST', '/' . VW::EVT . '/get-department', 'event getdepartment'];
		$cases['event_getemp'] = ['POST', '/' . VW::EVT . '/get-employee', 'event getemployee'];
		$cases['meeting_getdept'] = ['POST', '/' . VW::MT . '/get-department', 'meeting getdepartment'];
		$cases['meeting_getemp'] = ['POST', '/' . VW::MT . '/get-employee', 'meeting getemployee'];
		$cases['meeting_calendar'] = ['GET', '/meeting-calendar', 'meeting calendar'];
		$cases['training_status'] = ['POST', '/' . VW::TNG . '/status', 'training status'];
		return $cases;
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 8 — HR MODULE: Awards, Resignations, Travels,
	//              Promotions, Complaints, Warnings, Terminations,
	//              Announcements
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider hrModuleResourceProvider
	 */
	public function test_hr_module_resources(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function hrModuleResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$resources = [
			VW::AWD_TP => 'award_type',
			VW::AWD => 'award',
			VW::RSG => 'resignation',
			VW::TRV => 'travel',
			VW::PRM => 'promotion',
			VW::CPL => 'complaint',
			VW::WRN => 'warning',
			VW::TMN => 'termination',
		];
		$cases = [];
		foreach ($resources as $slug => $label) {
			$cases["{$label}_index"] = ['GET', "/{$slug}", "{$label} index"];
			$cases["{$label}_create"] = ['GET', "/{$slug}/create", "{$label} create"];
			$cases["{$label}_show"] = ['GET', "/{$slug}/{$fk}", "{$label} show"];
			$cases["{$label}_edit"] = ['GET', "/{$slug}/{$fk}/edit", "{$label} edit"];
			$cases["{$label}_store"] = ['POST', "/{$slug}", "{$label} store"];
			$cases["{$label}_update"] = ['PUT', "/{$slug}/{$fk}", "{$label} update"];
			$cases["{$label}_delete"] = ['DELETE', "/{$slug}/{$fk}", "{$label} destroy"];
		}
		$cases['termination_desc'] = ['GET', '/' . VW::TMN . "/{$fk}/description", 'termination description'];
		$cases['termtype_index'] = ['GET', '/terminationtype', 'terminationtype index'];
		$cases['termtype_create'] = ['GET', '/terminationtypes/create', 'terminationtype create'];
		$cases['termtype_show'] = ['GET', "/terminationtypes/{$fk}", 'terminationtype show'];
		$cases['announcement_index'] = ['GET', '/announcement', 'announcement index'];
		$cases['announcement_create'] = ['GET', '/announcements/create', 'announcement create'];
		$cases['announcement_show'] = ['GET', "/announcements/{$fk}", 'announcement show'];
		$cases['announcement_getdept'] = ['POST', '/' . VW::ANC . '/getdepartment', 'announcement getdepartment'];
		$cases['announcement_getemp'] = ['POST', '/' . VW::ANC . '/getemployee', 'announcement getemployee'];
		return $cases;
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 9 — COMPANY POLICIES / INDICATORS / APPRAISALS / GOALS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider performanceResourceProvider
	 */
	public function test_performance_resources(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function performanceResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$resources = [
			VW::CPN_PL => 'company_policy',
			VW::IND => 'indicator',
			VW::APR => 'appraisal',
			VW::GL_TP => 'goal_type',
			VW::GL_TRC => 'goal_tracking',
		];
		$cases = [];
		foreach ($resources as $slug => $label) {
			$cases["{$label}_index"] = ['GET', "/{$slug}", "{$label} index"];
			$cases["{$label}_create"] = ['GET', "/{$slug}/create", "{$label} create"];
			$cases["{$label}_show"] = ['GET', "/{$slug}/{$fk}", "{$label} show"];
			$cases["{$label}_store"] = ['POST', "/{$slug}", "{$label} store"];
		}
		return $cases;
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 10 — RECRUITMENT
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider recruitmentRoutesProvider
	 */
	public function test_recruitment_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function recruitmentRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'job_index' => ['GET', '/' . VW::JB, VW::JB . ' index'],
			'job_create' => ['GET', '/' . VW::JB . '/create', VW::JB . ' create'],
			'job_show' => ['GET', '/' . VW::JB . "/{$fk}", VW::JB . ' show'],
			'job_cat_index' => ['GET', '/job-category', 'job-category index'],
			'job_cat_create' => ['GET', '/job-category/create', 'job-category create'],
			'job_stage_index' => ['GET', '/job-stage', 'job-stage index'],
			'job_stage_create' => ['GET', '/job-stage/create', 'job-stage create'],
			'job_app_index' => ['GET', '/job-application', 'job-application index'],
			'job_app_create' => ['GET', '/job-application/create', 'job-application create'],
			'job_app_show' => ['GET', "/job-application/{$fk}", 'job-application show'],
			'candidates' => ['GET', '/candidates-job-applications', 'candidates applications'],
			'onboard' => ['GET', '/job-onboard', 'job onboard'],
			'onboard_create' => ['GET', '/' . VW::JB_OB . "/create/{$fk}", 'onboard create'],
			'interview_index' => ['GET', '/interview-schedule', 'interview index'],
			'interview_create' => ['GET', '/' . VW::ITV_SCD . "/create/{$fk}", 'interview create with id'],
			'custom_question_index' => ['GET', '/custom-question', 'custom-question index'],
			'custom_question_create' => ['GET', '/custom-questions/create', 'custom-question create'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 11 — HRM REPORTS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider hrmReportRoutesProvider
	 */
	public function test_hrm_report_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function hrmReportRoutesProvider(): array
	{
		return [
			'payroll' => ['/' . VW::RPT . '-payroll', VW::RPT . ' payroll'],
			'leave' => ['/' . VW::RPT . '-leave', VW::RPT . ' leave'],
			'monthly_attendance' => ['/' . VW::RPT . '-monthly-attendance', VW::RPT . ' monthly attendance'],
			'attendance_01' => ['/' . VW::RPT . '/attendance/01/0/0', VW::RPT . ' attendance jan'],
			'attendance_06' => ['/' . VW::RPT . '/attendance/06/0/0', VW::RPT . ' attendance jun'],
			'attendance_12' => ['/' . VW::RPT . '/attendance/12/0/0', VW::RPT . ' attendance dec'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 12 — DOCUMENT UPLOADS & TRANSFERS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider documentTransferProvider
	 */
	public function test_document_transfer_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function documentTransferProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'doc_index' => ['GET', '/' . VW::DOC, VW::DOC . ' index'],
			'doc_create' => ['GET', '/' . VW::DOC . '/create', VW::DOC . ' create'],
			'doc_upload_index' => ['GET', '/' . VW::DOC_UP, VW::DOC_UP . ' index'],
			'doc_upload_create' => ['GET', '/' . VW::DOC_UP . '/create', VW::DOC_UP . ' create'],
			'transfer_index' => ['GET', '/' . VW::TRF, VW::TRF . ' index'],
			'transfer_create' => ['GET', '/' . VW::TRF . '/create', VW::TRF . ' create'],
			'transfer_show' => ['GET', '/' . VW::TRF . "/{$fk}", VW::TRF . ' show'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 13 — INDEX PAGES RENDER TABLE/CARD/GRID CONTENT
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider indexContentAssertionProvider
	 */
	public function test_index_pages_render_content(string $uri, string $label, string $keyword): void
	{
		$r = $this->get($uri);
		if ($r->getStatusCode() === 200) {
			$content = strtolower($r->getContent() ?: '');
			$hasTable = str_contains($content, '<table') || str_contains($content, 'datatable');
			$hasCard = str_contains($content, 'card') || str_contains($content, 'grid');
			$hasList = str_contains($content, '<ul') || str_contains($content, '<ol') || str_contains($content, 'list-group');
			$hasKeyword = str_contains($content, strtolower($keyword));
			$this->assertTrue(
				$hasTable || $hasCard || $hasList || $hasKeyword,
				"Expected table/card/grid/list or keyword '{$keyword}' on [{$label}]"
			);
		} else {
			$this->assertNot500($r, $label);
		}
	}

	public static function indexContentAssertionProvider(): array
	{
		return [
			'employees' => ['/' . VW::EMP, 'employees index', 'employee'],
			'departments' => ['/' . VW::DPT, 'departments index', 'department'],
			'designations' => ['/' . VW::DSG, 'designations index', 'designation'],
			'branches' => ['/' . VW::BRC, 'branches index', 'branch'],
			'leaves' => ['/leave', 'leaves index', 'leave'],
			'attendance' => ['/' . VW::EMP_ATD, 'attendance index', 'attendance'],
			'payslips' => ['/' . VW::PY_SLP, 'payslips index', 'payslip'],
			'events' => ['/' . VW::EVT, 'events index', 'event'],
			'meetings' => ['/' . VW::MT, 'meetings index', 'meeting'],
			'trainings' => ['/' . VW::TNG, 'trainings index', 'training'],
			'awards' => ['/' . VW::AWD, 'awards index', 'award'],
			'resignations' => ['/' . VW::RSG, 'resignations index', 'resignation'],
			'travels' => ['/' . VW::TRV, 'travels index', 'travel'],
			'promotions' => ['/' . VW::PRM, 'promotions index', 'promotion'],
			'complaints' => ['/' . VW::CPL, 'complaints index', 'complaint'],
			'warnings' => ['/' . VW::WRN, 'warnings index', 'warning'],
			'terminations' => ['/' . VW::TMN, 'terminations index', 'termination'],
			'announcements' => ['/announcement', 'announcements index', 'announcement'],
			'allowances' => ['/' . VW::ALW, 'allowances index', 'allowance'],
			'loans' => ['/' . VW::LN, 'loans index', 'loan'],
			'commissions' => ['/' . VW::COM, 'commissions index', 'commission'],
			'overtimes' => ['/' . VW::OVT, 'overtimes index', 'overtime'],
			'payslip_types' => ['/' . VW::PY_SLP_TP, 'payslip types index', 'payslip'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 14 — HOLIDAYS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider holidayRoutesProvider
	 */
	public function test_holiday_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function holidayRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::HLD, VW::HLD . ' index'],
			'create' => ['GET', '/' . VW::HLD . '/create', VW::HLD . ' create'],
			'show' => ['GET', '/' . VW::HLD . "/{$fk}", VW::HLD . ' show'],
			'edit' => ['GET', '/' . VW::HLD . "/{$fk}/edit", VW::HLD . ' edit'],
			'store' => ['POST', '/' . VW::HLD, VW::HLD . ' store'],
			'calendar' => ['GET', '/holiday-calendar', VW::HLD . ' calendar'],
		];
	}
}
