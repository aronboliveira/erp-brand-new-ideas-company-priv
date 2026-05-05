<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Config\Constants\ViewsConstants as VW;
use App\Http\Controllers\Activity\TaskStageController as TSSTC;
use App\Http\Controllers\Bugs\BugStatusController as BGSTC;
use App\Http\Controllers\Planning\{
	ContractController as CTCC,
	ContractTypeController as CTCTC,
	ProjectController as PRJC,
	ProjectReportController as PRPC,
	ProjectTaskController as PRJTC,
	ProposalController as PPSC,
	TimeTrackerController as TMTC
};
use App\Http\Controllers\Shapes\TimesheetController as TMSC;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * @group pm
 * @group pm-returns
 * @group hardening
 */
class PmRouteReturnTest extends TestCase
{
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
		$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first();
		if (!$this->admin) {
			$this->markTestSkipped('SA user not seeded');
		}
		$this->actingAs($this->admin);
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
	//  SECTION 1 — PROJECT DASHBOARD & CORE
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider projectDashboardProvider
	 */
	public function test_project_dashboard_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function projectDashboardProvider(): array
	{
		return [
			'dashboard' => ['/project-dashboard', 'project dashboard'],
			'projects_index' => ['/' . VW::PRJ, VW::PRJ . ' index'],
			'projects_create' => ['/' . VW::PRJ . '/create', VW::PRJ . ' create'],
			'projects_view' => ['/projects-view', 'projects view'],
			'projects_users' => ['/projects-users', 'projects users'],
		];
	}

	/**
	 * @dataProvider projectCrudFakeIdProvider
	 */
	public function test_project_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function projectCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::PRJ . "/{$fk}", VW::PRJ . ' show'],
			'edit' => ['GET', '/' . VW::PRJ . "/{$fk}/edit", VW::PRJ . ' edit'],
			'update' => ['PUT', '/' . VW::PRJ . "/{$fk}", VW::PRJ . ' update'],
			'delete' => ['DELETE', '/' . VW::PRJ . "/{$fk}", VW::PRJ . ' destroy'],
			'store_empty' => ['POST', '/' . VW::PRJ, VW::PRJ . ' store'],
			'gantt' => ['GET', '/' . VW::PRJ . "/{$fk}/gantts", VW::PRJ . ' gantt'],
			'milestones' => ['GET', '/' . VW::PRJ . "/{$fk}/milestones", VW::PRJ . ' milestones'],
			'tasks' => ['GET', '/' . VW::PRJ . "/{$fk}/tasks", VW::PRJ . ' tasks'],
			'bugs' => ['GET', '/' . VW::PRJ . "/{$fk}/bugs", VW::PRJ . ' bugs'],
			'expenses' => ['GET', '/' . VW::PRJ . "/{$fk}/expenses", VW::PRJ . ' expenses'],
			'copy_link' => ['GET', '/' . VW::PRJ . "/copy-links/{$fk}", VW::PRJ . ' copy_link'],
			'invite_members' => ['GET', "/invite-project-members/{$fk}", VW::PRJ . ' invite members'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — PROJECT TASK STAGES (CRUD resource)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider taskStageResourceProvider
	 */
	public function test_task_stage_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function taskStageResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PRJ_TSK_STG, VW::PRJ_TSK_STG . ' index'],
			'create' => ['GET', '/' . VW::PRJ_TSK_STG . '/create', VW::PRJ_TSK_STG . ' create'],
			'show' => ['GET', '/' . VW::PRJ_TSK_STG . "/{$fk}", VW::PRJ_TSK_STG . ' show'],
			'edit' => ['GET', '/' . VW::PRJ_TSK_STG . "/{$fk}/edit", VW::PRJ_TSK_STG . ' edit'],
			'store' => ['POST', '/' . VW::PRJ_TSK_STG, VW::PRJ_TSK_STG . ' store'],
			'update' => ['PUT', '/' . VW::PRJ_TSK_STG . "/{$fk}", VW::PRJ_TSK_STG . ' update'],
			'delete' => ['DELETE', '/' . VW::PRJ_TSK_STG . "/{$fk}", VW::PRJ_TSK_STG . ' destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — PROJECT STAGES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider projectStageResourceProvider
	 */
	public function test_project_stage_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function projectStageResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PRJ_STG, VW::PRJ_STG . ' index'],
			'create' => ['GET', '/' . VW::PRJ_STG . '/create', VW::PRJ_STG . ' create'],
			'show' => ['GET', '/' . VW::PRJ_STG . "/{$fk}", VW::PRJ_STG . ' show'],
			'edit' => ['GET', '/' . VW::PRJ_STG . "/{$fk}/edit", VW::PRJ_STG . ' edit'],
			'store' => ['POST', '/' . VW::PRJ_STG, VW::PRJ_STG . ' store'],
			'update' => ['PUT', '/' . VW::PRJ_STG . "/{$fk}", VW::PRJ_STG . ' update'],
			'delete' => ['DELETE', '/' . VW::PRJ_STG . "/{$fk}", VW::PRJ_STG . ' destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 4 — BUG STATUS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider bugStatusResourceProvider
	 */
	public function test_bug_status_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function bugStatusResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/bug_status', 'bug_status index'],
			'create' => ['GET', '/bug_statuses/create', 'bug_status create'],
			'show' => ['GET', "/bug_statuses/{$fk}", 'bug_status show'],
			'edit' => ['GET', "/bug_statuses/{$fk}/edit", 'bug_status edit'],
			'store' => ['POST', '/bug_status', 'bug_status store'],
			'update' => ['PUT', "/bug_statuses/{$fk}", 'bug_status update'],
			'delete' => ['DELETE', "/bug_statuses/{$fk}", 'bug_status destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — CONTRACT TYPES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider contractTypeResourceProvider
	 */
	public function test_contract_type_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function contractTypeResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::CTC_TP, VW::CTC_TP . ' index'],
			'create' => ['GET', '/' . VW::CTC_TP . '/create', VW::CTC_TP . ' create'],
			'show' => ['GET', '/' . VW::CTC_TP . "/{$fk}", VW::CTC_TP . ' show'],
			'edit' => ['GET', '/' . VW::CTC_TP . "/{$fk}/edit", VW::CTC_TP . ' edit'],
			'store' => ['POST', '/' . VW::CTC_TP, VW::CTC_TP . ' store'],
			'update' => ['PUT', '/' . VW::CTC_TP . "/{$fk}", VW::CTC_TP . ' update'],
			'delete' => ['DELETE', '/' . VW::CTC_TP . "/{$fk}", VW::CTC_TP . ' destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 6 — CONTRACTS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider contractRoutesProvider
	 */
	public function test_contract_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function contractRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::CTC, VW::CTC . ' index'],
			'create' => ['GET', '/' . VW::CTC . '/create', VW::CTC . ' create'],
			'grid' => ['GET', '/' . VW::CTC . '/grid', VW::CTC . ' grid'],
			'show' => ['GET', '/' . VW::CTC . "/{$fk}", VW::CTC . ' show'],
			'edit' => ['GET', '/' . VW::CTC . "/{$fk}/edit", VW::CTC . ' edit'],
			'store' => ['POST', '/' . VW::CTC, VW::CTC . ' store'],
			'update' => ['PUT', '/' . VW::CTC . "/{$fk}", VW::CTC . ' update'],
			'delete' => ['DELETE', '/' . VW::CTC . "/{$fk}", VW::CTC . ' destroy'],
			'description' => ['GET', '/' . VW::CTC . "/{$fk}/description", VW::CTC . ' description'],
			'get_contract' => ['GET', '/' . VW::CTC . "/{$fk}/get_contract", VW::CTC . ' get'],
			'pdf' => ['GET', '/' . VW::CTC . "/pdfs/{$fk}", VW::CTC . ' pdf'],
			'copy' => ['GET', '/' . VW::CTC . "/copies/{$fk}", VW::CTC . ' copy'],
			'mail' => ['GET', '/' . VW::CTC . "/{$fk}/mail", VW::CTC . ' mail'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 7 — PROPOSALS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider proposalRoutesProvider
	 */
	public function test_proposal_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function proposalRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/proposal', 'proposal index'],
			'create' => ['GET', '/proposals/create', 'proposal create'],
			'show' => ['GET', "/proposals/{$fk}", 'proposal show'],
			'edit' => ['GET', "/proposals/{$fk}/edit", 'proposal edit'],
			'store' => ['POST', '/proposal', 'proposal store'],
			'update' => ['PUT', "/proposals/{$fk}", 'proposal update'],
			'delete' => ['DELETE', "/proposals/{$fk}", 'proposal destroy'],
			'items' => ['GET', '/proposals/items', 'proposal items'],
			'export' => ['GET', '/proposals/export', 'proposals export'],
			'preview' => ['GET', '/proposals/previews/template1/ffffff', 'proposal preview'],
			'convert' => ['GET', "/proposals/{$fk}/convert", 'proposal convert'],
			'duplicate' => ['GET', "/proposals/{$fk}/duplicate", 'proposal duplicate'],
			'sent' => ['GET', "/proposals/{$fk}/sent", 'proposal sent'],
			'resent' => ['GET', "/proposals/{$fk}/resent", 'proposal resent'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 8 — TIME TRACKERS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider timeTrackerRoutesProvider
	 */
	public function test_time_tracker_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function timeTrackerRoutesProvider(): array
	{
		return [
			'index' => ['GET', '/' . VW::TMT, VW::TMT . ' index'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 9 — PROJECT REPORTS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider projectReportRoutesProvider
	 */
	public function test_project_report_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function projectReportRoutesProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PRJ_RPT, VW::PRJ_RPT . ' index'],
			'create' => ['GET', '/' . VW::PRJ_RPT . '/create', VW::PRJ_RPT . ' create'],
			'show' => ['GET', '/' . VW::PRJ_RPT . "/{$fk}", VW::PRJ_RPT . ' show'],
			'edit' => ['GET', '/' . VW::PRJ_RPT . "/{$fk}/edit", VW::PRJ_RPT . ' edit'],
			'store' => ['POST', '/' . VW::PRJ_RPT, VW::PRJ_RPT . ' store'],
			'update' => ['PUT', '/' . VW::PRJ_RPT . "/{$fk}", VW::PRJ_RPT . ' update'],
			'delete' => ['DELETE', '/' . VW::PRJ_RPT . "/{$fk}", VW::PRJ_RPT . ' destroy'],
			'export' => ['GET', '/' . VW::PRJ_RPT . "/exports/{$fk}", VW::PRJ_RPT . ' export'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 10 — TASK BOARD & CALENDAR
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider taskBoardCalendarProvider
	 */
	public function test_task_board_and_calendar_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function taskBoardCalendarProvider(): array
	{
		return [
			'taskboard_view' => ['/task-board-view', 'task board view'],
			'taskboards' => ['/task-boards', 'task boards'],
			'bugs_reports' => ['/bugs_reports', 'bugs reports'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 11 — TIMESHEETS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider timesheetRoutesProvider
	 */
	public function test_timesheet_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function timesheetRoutesProvider(): array
	{
		return [
			'list' => ['GET', '/projects.timesheets/list', 'timesheet list'],
			'list_get' => ['GET', '/projects.timesheets/list-get', 'timesheet list get'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 12 — CROSS-CUTTING: index pages contain expected content
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider indexContentKeywordsProvider
	 */
	public function test_index_pages_contain_expected_keywords(string $uri, string $keyword, string $label): void
	{
		$r = $this->get($uri);
		if ($r->getStatusCode() === 200) {
			$r->assertSee($keyword, false);
		} else {
			$this->assertNot500($r, $label);
		}
	}

	public static function indexContentKeywordsProvider(): array
	{
		return [
			'projects_has_project' => ['/' . VW::PRJ, 'project', 'projects index keyword'],
			'project_dashboard_has_project' => ['/project-dashboard', 'project', 'dashboard keyword'],
			'contracts_has_contract' => ['/' . VW::CTC, 'contract', 'contracts index keyword'],
			'contract_types_has_type' => ['/' . VW::CTC_TP, 'type', 'contract types keyword'],
			'proposals_has_proposal' => ['/proposal', 'proposal', 'proposals index keyword'],
			'time_trackers_has_tracker' => ['/' . VW::TMT, 'track', 'time trackers keyword'],
			'project_reports_has_report' => ['/' . VW::PRJ_RPT, 'report', 'project reports keyword'],
			'task_stages_has_stage' => ['/' . VW::PRJ_TSK_STG, 'stage', 'task stages keyword'],
			'project_stages_has_stage' => ['/' . VW::PRJ_STG, 'stage', 'project stages keyword'],
			'bug_status_has_bug' => ['/bug_status', 'bug', 'bug status keyword'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 13 — EXPORT ROUTES (non-500)
	// ══════════════════════════════════════════════════════════════════════

	public function test_proposals_export_no_500(): void
	{
		$r = $this->get('/proposals/export');
		$this->assertNot500($r, 'proposals export');
		$code = $r->getStatusCode();
		$this->assertTrue(
			$code === 200 || ($code >= 300 && $code < 400),
			"Expected 200 (download) or 3xx, got {$code} on proposals export"
		);
	}

	public function test_project_report_export_fake_id_no_500(): void
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$r = $this->get('/' . VW::PRJ_RPT . "/exports/{$fk}");
		$this->assertNot500($r, 'project report export');
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 14 — TODO ROUTES
	// ══════════════════════════════════════════════════════════════════════

	public function test_todo_store_no_500(): void
	{
		$r = $this->post('/todos/create', ['title' => 'PHPUnit test todo']);
		$this->assertNot500($r, 'todo store');
	}

	public function test_todo_update_fake_id_no_500(): void
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$r = $this->post("/todos/{$fk}/update", ['title' => 'updated']);
		$this->assertNot500($r, 'todo update');
	}

	public function test_todo_delete_fake_id_no_500(): void
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$r = $this->delete("/todos/{$fk}/delete");
		$this->assertNot500($r, 'todo destroy');
	}
}
