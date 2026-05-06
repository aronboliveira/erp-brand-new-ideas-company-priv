<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

/**
 * Comprehensive hardening of HRM and Project Management routes.
 *
 * Covers: Employees, Leaves, Attendance, Payslips, Departments,
 *         Designations, Branches, Awards, Trips, Events, Meetings,
 *         Trainings, Holidays, Projects, Tasks, Bug Reports, Milestones,
 *         Timesheets.
 *
 * Tests focus on:
 * - CRUD routes with fake/boundary UUID params
 * - Employee-specific sub-routes (leave balance, salary, attendance)
 * - Date-scoped report routes with param matrices
 * - View mode variations (list, grid, card)
 *
 * No RefreshDatabase — uses seeded admin user.
 *
 * @group hrm
 * @group project
 * @group hardening
 */
class HrmProjectRouteTest extends TestCase
{
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
		\$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first()

			?? User::where('type', 'super admin')->first()

			?? User::first();

		if (\$this->admin) {

			\$this->actingAs(\$this->admin);

		}
	}

	protected function assertNot500(\Illuminate\Testing\TestResponse $r, string $ctx = ''): void
	{
		$this->assertNotEquals(500, $r->getStatusCode(), "HTTP 500 on [{$ctx}]");
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 1 — EMPLOYEE CRUD
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider employeeCrudProvider
	 */
	public function test_employee_crud(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function employeeCrudProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'index'          => ['GET', '/employees', 'index'],
			'create'         => ['GET', '/employees/create', 'create'],
			'show'           => ['GET', "/employees/{$fakeId}", 'show fake'],
			'edit'           => ['GET', "/employees/{$fakeId}/edit", 'edit fake'],
			'store_empty'    => ['POST', '/employees', 'store empty'],
			'update_fake'    => ['PUT', "/employees/{$fakeId}", 'update fake'],
			'delete_fake'    => ['DELETE', "/employees/{$fakeId}", 'delete fake'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — LEAVE MANAGEMENT
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider leaveCrudProvider
	 */
	public function test_leave_crud(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function leaveCrudProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'index'            => ['GET', '/leaves', 'leave index'],
			'create'           => ['GET', '/leaves/create', 'leave create'],
			'show'             => ['GET', "/leaves/{$fakeId}", 'leave show fake'],
			'edit'             => ['GET', "/leaves/{$fakeId}/edit", 'leave edit fake'],
			'store_empty'      => ['POST', '/leaves', 'leave store empty'],
			'type_index'       => ['GET', '/leave_types', 'leave type index'],
			'type_create'      => ['GET', '/leave_types/create', 'leave type create'],
			'report'           => ['GET', '/reports-leave', 'leave report'],
			'export'           => ['GET', '/leaves/export', 'leave export'],
		];
	}

	/**
	 * @dataProvider leaveCalendarParamProvider
	 */
	public function test_leave_calendar_params(
		string $empId,
		string $leaveType,
		string $month,
		string $year,
		string $label
	): void {
		$uri = "/employees/{$empId}/leaves/{$leaveType}/all/{$month}/{$year}";
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function leaveCalendarParamProvider(): array
	{
		$validEmpId  = '34026c75-ac6b-4fb3-9a88-2071a48701ab'; // known seeded
		$fakeId      = '00000000-0000-0000-0000-000000000000';

		$empIds      = ['valid' => $validEmpId, 'fake' => $fakeId, 'xss' => '<script>'];
		$leaveTypes  = ['all', 'casual', 'sick', 'maternity', 'nonexistent'];
		$months      = ['01', '06', '12', '00', '13', '-1'];
		$years       = ['2026', '2025', '0', '9999'];

		$cases = [];
		// Full combinatorics is huge, do targeted cross-product
		foreach ($empIds as $eidLabel => $eid) {
			foreach (['all', 'casual', 'nonexistent'] as $lt) {
				foreach (['01', '12', '00', '13'] as $m) {
					$key = "lc_{$eidLabel}_{$lt}_m{$m}_y2026";
					$cases[$key] = [$eid, $lt, $m, '2026', "{$eidLabel}/{$lt}/m={$m}/y=2026"];
				}
			}
		}
		// Year boundary tests with valid employee
		foreach ($years as $y) {
			$key = "lc_valid_all_m01_y{$y}";
			$cases[$key] = [$validEmpId, 'all', '01', $y, "valid/all/m=01/y={$y}"];
		}

		return $cases;
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — ATTENDANCE MANAGEMENT
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider attendanceCrudProvider
	 */
	public function test_attendance_crud(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function attendanceCrudProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'index'            => ['GET', '/employee_attendances', 'attendance index'],
			'create'           => ['GET', '/employee_attendances/create', 'attendance create'],
			'mark_index'       => ['GET', '/mark_attendances', 'mark attendance index'],
			'bulk_attendance'  => ['GET', '/bulk_attendances', 'bulk attendance index'],
			'show_fake'        => ['GET', "/employee_attendances/{$fakeId}", 'attendance show fake'],
			'edit_fake'        => ['GET', "/employee_attendances/{$fakeId}/edit", 'attendance edit fake'],
			'store_empty'      => ['POST', '/employee_attendances', 'attendance store empty'],
		];
	}

	/**
	 * @dataProvider attendanceReportParamMatrixProvider
	 */
	public function test_attendance_report_param_matrix(
		string $month,
		string $branch,
		string $department,
		string $label
	): void {
		$uri = "/reports/attendances/{$month}/{$branch}/{$department}";
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function attendanceReportParamMatrixProvider(): array
	{
		$months      = ['01', '06', '12', '00', '13'];
		$branches    = ['0', '00000000-0000-0000-0000-000000000000'];
		$departments = ['0', '00000000-0000-0000-0000-000000000000'];

		$cases = [];
		foreach ($months as $m) {
			foreach ($branches as $b) {
				foreach ($departments as $d) {
					$bShort = substr($b, 0, 5);
					$dShort = substr($d, 0, 5);
					$key = "attn_m{$m}_b{$bShort}_d{$dShort}";
					$cases[$key] = [$m, $b, $d, "month={$m}/branch={$b}/dept={$d}"];
				}
			}
		}

		return $cases;
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 4 — HR CONFIGURATION RESOURCES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider hrConfigResourceProvider
	 */
	public function test_hr_config_resources(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function hrConfigResourceProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			// Departments
			'dept_index'            => ['GET', '/departments', 'department index'],
			'dept_create'           => ['GET', '/departments/create', 'department create'],
			'dept_show'             => ['GET', "/departments/{$fakeId}", 'department show'],
			'dept_edit'             => ['GET', "/departments/{$fakeId}/edit", 'department edit'],
			'dept_store_empty'      => ['POST', '/departments', 'department store empty'],
			// Designations
			'desig_index'           => ['GET', '/designations', 'designation index'],
			'desig_create'          => ['GET', '/designations/create', 'designation create'],
			'desig_show'            => ['GET', "/designations/{$fakeId}", 'designation show'],
			'desig_store_empty'     => ['POST', '/designations', 'designation store empty'],
			// Branches
			'branch_index'          => ['GET', '/branches', 'branch index'],
			'branch_create'         => ['GET', '/branches/create', 'branch create'],
			'branch_show'           => ['GET', "/branches/{$fakeId}", 'branch show'],
			'branch_store_empty'    => ['POST', '/branches', 'branch store empty'],
			// Awards
			'award_index'           => ['GET', '/awards', 'award index'],
			'award_create'          => ['GET', '/awards/create', 'award create'],
			// Trips / Transfers / Resignations
			'trip_index'            => ['GET', '/trips', 'trip index'],
			'trip_create'           => ['GET', '/trips/create', 'trip create'],
			'transfer_index'        => ['GET', '/transfers', 'transfer index'],
			'resignation_index'     => ['GET', '/resignations', 'resignation index'],
			'termination_index'     => ['GET', '/terminations', 'termination index'],
			'warning_index'         => ['GET', '/warnings', 'warning index'],
			'complaint_index'       => ['GET', '/complaints', 'complaint index'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — PROJECT MANAGEMENT CRUD
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider projectCrudProvider
	 */
	public function test_project_crud(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function projectCrudProvider(): array
	{
		$fakeId  = '00000000-0000-0000-0000-000000000000';
		$validId = '712ce293-ad04-470d-8885-baa1ebfcef3a'; // known seeded
		return [
			'index'              => ['GET', '/projects', 'project index'],
			'create'             => ['GET', '/projects/create', 'project create'],
			'show_valid'         => ['GET', "/projects/{$validId}", 'project show valid'],
			'show_fake'          => ['GET', "/projects/{$fakeId}", 'project show fake'],
			'edit_valid'         => ['GET', "/projects/{$validId}/edit", 'project edit valid'],
			'edit_fake'          => ['GET', "/projects/{$fakeId}/edit", 'project edit fake'],
			'store_empty'        => ['POST', '/projects', 'project store empty'],
			'update_fake'        => ['PUT', "/projects/{$fakeId}", 'project update fake'],
			'delete_fake'        => ['DELETE', "/projects/{$fakeId}", 'project delete fake'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 6 — BUG REPORTS & VIEW MODES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider bugReportViewProvider
	 */
	public function test_bug_report_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function bugReportViewProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'bugs_default'       => ['/bugs_reports', 'bugs default'],
			'bugs_list'          => ['/bugs_reports/list', 'bugs list'],
			'bugs_grid'          => ['/bugs_reports/grid', 'bugs grid'],
			'bugs_create'        => ['/bugs_reports/create', 'bugs create'],
			'bugs_show_fake'     => ["/bugs_reports/{$fakeId}", 'bugs show fake'],
			'bugs_edit_fake'     => ["/bugs_reports/{$fakeId}/edit", 'bugs edit fake'],
			'bugs_invalid_view'  => ['/bugs_reports/nonexistent', 'bugs invalid view'],
			'bugs_xss_view'      => ['/bugs_reports/' . urlencode('<script>alert(1)</script>'), 'bugs XSS view'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 7 — PROJECT REPORTS & TASKS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider projectReportTaskProvider
	 */
	public function test_project_report_and_task_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function projectReportTaskProvider(): array
	{
		$fakeId  = '00000000-0000-0000-0000-000000000000';
		$validProjId = '712ce293-ad04-470d-8885-baa1ebfcef3a';
		return [
			// Project reports
			'report_index'          => ['GET', '/project_reports', 'report index'],
			'report_create'         => ['GET', '/project_reports/create', 'report create'],
			'report_show_fake'      => ['GET', "/project_reports/{$fakeId}", 'report show fake'],
			'report_edit_fake'      => ['GET', "/project_reports/{$fakeId}/edit", 'report edit fake'],
			'report_export_fake'    => ['GET', "/project_reports/exports/{$fakeId}", 'report export fake'],
			'report_store_empty'    => ['POST', '/project_reports', 'report store empty'],
			// Tasks
			'task_index'            => ['GET', '/tasks', 'task index'],
			'task_create'           => ['GET', '/tasks/create', 'task create'],
			'task_show_fake'        => ['GET', "/tasks/{$fakeId}", 'task show fake'],
			'task_store_empty'      => ['POST', '/tasks', 'task store empty'],
			// Milestones
			'milestone_index'       => ['GET', '/milestones', 'milestone index'],
			'milestone_create'      => ['GET', '/milestones/create', 'milestone create'],
			// Timesheet
			'timesheet_index'       => ['GET', '/timesheets', 'timesheet index'],
			'timesheet_create'      => ['GET', '/timesheets/create', 'timesheet create'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 8 — EVENTS, MEETINGS, TRAININGS, HOLIDAYS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider eventsMeetingsProvider
	 */
	public function test_events_meetings_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function eventsMeetingsProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'event_index'       => ['GET', '/events', 'event index'],
			'event_create'      => ['GET', '/events/create', 'event create'],
			'event_show'        => ['GET', "/events/{$fakeId}", 'event show'],
			'meeting_index'     => ['GET', '/meetings', 'meeting index'],
			'meeting_create'    => ['GET', '/meetings/create', 'meeting create'],
			'meeting_show'      => ['GET', "/meetings/{$fakeId}", 'meeting show'],
			'training_index'    => ['GET', '/trainings', 'training index'],
			'training_create'   => ['GET', '/trainings/create', 'training create'],
			'training_show'     => ['GET', "/trainings/{$fakeId}", 'training show'],
			'holiday_index'     => ['GET', '/holidays', 'holiday index'],
			'holiday_create'    => ['GET', '/holidays/create', 'holiday create'],
			'holiday_show'      => ['GET', "/holidays/{$fakeId}", 'holiday show'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 9 — HRM REPORTS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider hrmReportProvider
	 */
	public function test_hrm_reports(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function hrmReportProvider(): array
	{
		return [
			'payroll_report'         => ['/reports-payroll', 'payroll report'],
			'leave_report'           => ['/reports-leave', 'leave report'],
			'monthly_attendance'     => ['/reports-monthly-attendance', 'monthly attendance'],
			'payroll_export'         => ['/reports/payrolls/export', 'payroll export'],
			'leave_export'           => ['/leaves/export', 'leave export'],
			'attendance_m01'         => ['/reports/attendances/01/0/0', 'attendance jan'],
			'attendance_m06'         => ['/reports/attendances/06/0/0', 'attendance jun'],
			'attendance_m12'         => ['/reports/attendances/12/0/0', 'attendance dec'],
		];
	}
}
