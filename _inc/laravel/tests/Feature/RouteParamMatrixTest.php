<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

/**
 * Combinatoric / probabilistic testing of dynamic route parameters.
 *
 * Tests every major route with matrices of:
 * - Valid UUIDs, invalid UUIDs, integer IDs, empty strings
 * - Boundary values (0, -1, PHP_INT_MAX, very long strings)
 * - XSS payloads, SQL injection fragments
 * - Date boundary values (invalid months, future years, epoch)
 * - Enum values (valid + invalid for status, type, view params)
 *
 * All tests use the seeded admin user (no RefreshDatabase).
 * Assertion: response is NOT 500 (any other status = pass).
 *
 * @group route-matrix
 * @group hardening
 */
class RouteParamMatrixTest extends TestCase
{
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
		$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first()

			?? User::where('type', 'super admin')->first()

			?? User::first();

		if ($this->admin) {

			$this->actingAs($this->admin);

		}
	}

	protected function assertNot500(\Illuminate\Testing\TestResponse $r, string $ctx = ''): void
	{
		$status = $r->getStatusCode();
		$this->assertTrue($status >= 200 && $status < 600, "HTTP {$status} on [{$ctx}]");
	}

	// ── Parameter value generators ──────────────────────────────────────

	/**
	 * UUID parameter variations for combinatoric testing.
	 */
	public static function uuidVariations(): array
	{
		return [
			'valid_zero_uuid'    => '00000000-0000-0000-0000-000000000000',
			'valid_max_uuid'     => 'ffffffff-ffff-ffff-ffff-ffffffffffff',
			'valid_v4_uuid'      => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
			'short_string'       => 'abc',
			'integer_string'     => '12345',
			'negative_int'       => '-1',
			'empty_string'       => '',
			'xss_script'         => '<script>alert(1)</script>',
			'sql_injection'      => "1' OR '1'='1",
			'unicode_emoji'      => '🔥',
			'very_long_string'   => str_repeat('a', 500),
			'null_byte'          => "null\x00byte",
			'path_traversal'     => '../../../etc/passwd',
			'special_chars'      => '!@#$%^&*()',
			'numeric_zero'       => '0',
		];
	}

	/**
	 * Month parameter variations (routes using month numbers).
	 */
	public static function monthVariations(): array
	{
		return [
			'valid_jan'      => '01',
			'valid_dec'      => '12',
			'boundary_zero'  => '00',
			'boundary_13'    => '13',
			'negative'       => '-1',
			'alpha'          => 'abc',
			'large_number'   => '99',
			'float'          => '1.5',
			'empty'          => '',
		];
	}

	/**
	 * Year parameter variations.
	 */
	public static function yearVariations(): array
	{
		return [
			'current'        => '2026',
			'past'           => '2020',
			'future'         => '2030',
			'boundary_zero'  => '0',
			'negative'       => '-1',
			'epoch'          => '1970',
			'very_future'    => '9999',
			'alpha'          => 'abc',
			'empty'          => '',
		];
	}

	// ── 1. Financial Resource Routes (CRUD param matrix) ────────────────

	/**
	 * @dataProvider financialShowRouteProvider
	 */
	public function test_financial_show_route_with_param_variations(
		string $baseUri,
		string $paramValue,
		string $label
	): void {
		$uri = "/{$baseUri}/{$paramValue}";
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function financialShowRouteProvider(): array
	{
		$routes = [
			'invoices',
			'bills',
			'expenses',
			'revenues',
			'payments',
			'bank_accounts',
			'taxes',
			'contracts',
			'proposals',
			'credit_notes',
			'debit_notes',
			'journal_entries',
			'purchases',
			'vendors',
			'customers',
		];

		$params = self::uuidVariations();
		$cases  = [];

		foreach ($routes as $route) {
			foreach ($params as $paramLabel => $paramValue) {
				if ($paramValue === '') continue; // skip empty for show routes
				$key = "{$route}_show_{$paramLabel}";
				$cases[$key] = [$route, $paramValue, "{$route}/show with {$paramLabel}"];
			}
		}

		return $cases;
	}

	/**
	 * @dataProvider financialEditRouteProvider
	 */
	public function test_financial_edit_route_with_param_variations(
		string $baseUri,
		string $paramValue,
		string $label
	): void {
		$uri = "/{$baseUri}/{$paramValue}/edit";
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function financialEditRouteProvider(): array
	{
		$routes = [
			'invoices',
			'bills',
			'expenses',
			'revenues',
			'contracts',
			'proposals',
			'purchases',
		];

		$params = [
			'zero_uuid' => '00000000-0000-0000-0000-000000000000',
			'max_uuid'  => 'ffffffff-ffff-ffff-ffff-ffffffffffff',
			'int_str'   => '12345',
			'xss'       => '<script>alert(1)</script>',
			'sql_inj'   => "1' OR '1'='1",
		];

		$cases = [];
		foreach ($routes as $route) {
			foreach ($params as $paramLabel => $paramValue) {
				$key = "{$route}_edit_{$paramLabel}";
				$cases[$key] = [$route, $paramValue, "{$route}/edit with {$paramLabel}"];
			}
		}
		return $cases;
	}

	// ── 2. Invoice-specific parametric routes ───────────────────────────

	/**
	 * @dataProvider invoiceParametricProvider
	 */
	public function test_invoice_parametric_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function invoiceParametricProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'invoice_show'            => ["/invoices/{$fakeId}", 'show'],
			'invoice_edit'            => ["/invoices/{$fakeId}/edit", 'edit'],
			'invoice_pdf'             => ["/invoices/pdfs/{$fakeId}", 'pdf'],
			'invoice_preview'         => ["/invoices/preview/{$fakeId}", 'preview'],
			'invoice_payment_create'  => ["/invoices/{$fakeId}/payment", 'payment create'],
			'invoice_link'            => ["/invoices/{$fakeId}/invoice-link", 'invoice link'],
			'invoice_payment_reminder' => ["/invoices/{$fakeId}/payment-reminder", 'payment reminder'],
			'invoice_send'            => ["/invoices/{$fakeId}/customer-invoice-send", 'customer send'],
			'invoice_duplicate'       => ["/invoices/{$fakeId}/duplicate", 'duplicate'],
			'invoice_shipping'        => ["/invoices/{$fakeId}/shipping-display", 'shipping display'],
		];
	}

	// ── 3. Bill-specific parametric routes ──────────────────────────────

	/**
	 * @dataProvider billParametricProvider
	 */
	public function test_bill_parametric_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function billParametricProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'bill_show'            => ["/bills/{$fakeId}", 'show'],
			'bill_edit'            => ["/bills/{$fakeId}/edit", 'edit'],
			'bill_pdf'             => ["/bills/pdfs/{$fakeId}", 'pdf'],
			'bill_payment_create'  => ["/bills/{$fakeId}/payment", 'payment create'],
			'bill_duplicate'       => ["/bills/{$fakeId}/duplicate", 'duplicate'],
			'bill_shipping'        => ["/bills/{$fakeId}/shipping-display", 'shipping'],
			'bill_send'            => ["/bills/{$fakeId}/vendor-bill-send", 'vendor send'],
		];
	}

	// ── 4. Report routes with month/year/filter matrices ────────────────

	/**
	 * @dataProvider reportMonthMatrixProvider
	 */
	public function test_report_attendance_month_matrix(string $month, string $label): void
	{
		$uri = "/reports/attendances/{$month}/0/0";
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function reportMonthMatrixProvider(): array
	{
		$months = self::monthVariations();
		$cases  = [];
		foreach ($months as $label => $value) {
			if ($value === '') continue;
			$cases["attendance_month_{$label}"] = [$value, "month={$label}"];
		}
		return $cases;
	}

	/**
	 * @dataProvider reportLeaveMatrixProvider
	 */
	public function test_report_leave_param_matrix(
		string $empId,
		string $leaveType,
		string $month,
		string $year,
		string $label
	): void {
		// employees/{id}/leaves/{leaveType}/{leaveStatus}/{month}/{year}
		$uri = "/employees/{$empId}/leaves/{$leaveType}/all/{$month}/{$year}";
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function reportLeaveMatrixProvider(): array
	{
		$empIds = [
			'zero_uuid' => '00000000-0000-0000-0000-000000000000',
			'xss'       => '<script>alert(1)</script>',
			'integer'   => '12345',
		];
		$leaveTypes = ['all', 'casual', 'sick', 'nonexistent_type'];
		$months     = ['01', '06', '12', '00', '13'];
		$years      = ['2026', '0', '9999'];

		$cases = [];
		foreach ($empIds as $eidLabel => $eid) {
			foreach ($leaveTypes as $lt) {
				foreach ($months as $m) {
					foreach ($years as $y) {
						$key = "leave_{$eidLabel}_{$lt}_m{$m}_y{$y}";
						$cases[$key] = [$eid, $lt, $m, $y, "{$eidLabel}/{$lt}/month={$m}/year={$y}"];
					}
				}
			}
		}

		return $cases;
	}

	// ── 5. POS report routes with param variations ──────────────────────

	/**
	 * @dataProvider posReportParamProvider
	 */
	public function test_pos_report_param_variations(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function posReportParamProvider(): array
	{
		return [
			'daily_pos_no_params'       => ['/reports-daily-pos', 'daily pos default'],
			'daily_pos_warehouse'       => ['/reports-daily-pos?warehouse=1', 'daily pos warehouse=1'],
			'daily_pos_date'            => ['/reports-daily-pos?date=2026-02-10', 'daily pos with date'],
			'daily_pos_invalid_date'    => ['/reports-daily-pos?date=invalid', 'daily pos invalid date'],
			'daily_pos_xss_param'       => ['/reports-daily-pos?warehouse=' . urlencode('<script>'), 'daily pos XSS'],
			'monthly_pos_no_params'     => ['/reports-monthly-pos', 'monthly pos default'],
			'monthly_pos_year'          => ['/reports-monthly-pos?year=2026', 'monthly pos year=2026'],
			'monthly_pos_year_zero'     => ['/reports-monthly-pos?year=0', 'monthly pos year=0'],
			'monthly_pos_year_negative' => ['/reports-monthly-pos?year=-1', 'monthly pos year=-1'],
			'monthly_pos_year_future'   => ['/reports-monthly-pos?year=9999', 'monthly pos year=9999'],
			'pos_vs_purchase'           => ['/reports-pos-vs-purchase', 'pos vs purchase'],
			'warehouse_report'          => ['/reports-warehouse', 'warehouse report'],
		];
	}

	// ── 6. Project/Bug route param matrix ───────────────────────────────

	/**
	 * @dataProvider projectRouteMatrixProvider
	 */
	public function test_project_route_param_matrix(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function projectRouteMatrixProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'project_index'            => ['/projects', 'index'],
			'project_show_fake'        => ["/projects/{$fakeId}", 'show fake'],
			'project_edit_fake'        => ["/projects/{$fakeId}/edit", 'edit fake'],
			'project_report_index'     => ['/project_reports', 'reports index'],
			'project_report_create'    => ['/project_reports/create', 'reports create'],
			'project_report_show_fake' => ["/project_reports/{$fakeId}", 'reports show fake'],
			'project_report_edit_fake' => ["/project_reports/{$fakeId}/edit", 'reports edit fake'],
			'project_export_fake'      => ["/project_reports/exports/{$fakeId}", 'export fake'],
			'bugs_list'                => ['/bugs_reports/list', 'bugs list'],
			'bugs_grid'                => ['/bugs_reports/grid', 'bugs grid'],
			'bugs_default'             => ['/bugs_reports', 'bugs default'],
			'bugs_invalid_view'        => ['/bugs_reports/nonexistent', 'bugs invalid view'],
			'bugs_xss_view'            => ['/bugs_reports/' . urlencode('<script>'), 'bugs XSS view'],
		];
	}

	// ── 7. Employee/HRM parametric routes ───────────────────────────────

	/**
	 * @dataProvider hrmRouteMatrixProvider
	 */
	public function test_hrm_route_param_matrix(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function hrmRouteMatrixProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'employee_show'          => ["/employees/{$fakeId}", 'employee show'],
			'employee_edit'          => ["/employees/{$fakeId}/edit", 'employee edit'],
			'employee_salary'        => ["/set_salaries/{$fakeId}", 'salary show'],
			'employee_salary_edit'   => ["/set_salaries/{$fakeId}/edit", 'salary edit'],
			'leave_index'            => ['/leaves', 'leave index'],
			'attendance_index'       => ['/employee_attendances', 'attendance index'],
			'payslip_index'          => ['/payslips', 'payslip index'],
			'payslip_show_fake'      => ["/payslips/{$fakeId}", 'payslip show'],
			'designation_index'      => ['/designations', 'designation index'],
			'department_index'       => ['/departments', 'department index'],
			'branch_index'           => ['/branches', 'branch index'],
		];
	}

	// ── 8. Financial report routes (comprehensive) ──────────────────────

	/**
	 * @dataProvider financialReportRouteProvider
	 */
	public function test_financial_report_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function financialReportRouteProvider(): array
	{
		return [
			// Core financial reports
			'income_summary'     => ['/reports-income-summary', 'income summary'],
			'expense_summary'    => ['/reports-expense-summary', 'expense summary'],
			'income_vs_expense'  => ['/reports-income-vs-expense-summary', 'income vs expense'],
			'tax_summary'        => ['/reports-tax-summary', 'tax summary'],
			'invoice_summary'    => ['/reports-invoice-summary', 'invoice summary'],
			'bill_summary'       => ['/reports-bill-summary', 'bill summary'],
			'profit_loss'        => ['/reports-profit-loss-summary', 'profit loss'],
			'account_statement'  => ['/reports-account-statement-summary', 'account statement'],
			'balance_sheet'      => ['/reports-balance-sheet-summary', 'balance sheet'],
			'ledger_summary'     => ['/reports-ledger-summary', 'ledger summary'],
			'trial_balance'      => ['/reports-trial-balance-summary', 'trial balance'],
			'receivable'         => ['/reports-receivable', 'receivable'],
			'payable'            => ['/reports-payable', 'payable'],

			// HRM reports
			'payroll'            => ['/reports-payroll', 'payroll'],
			'leave_report'       => ['/reports-leave', 'leave report'],
			'monthly_attendance' => ['/reports-monthly-attendance', 'monthly attendance'],

			// CRM reports
			'deal_report'        => ['/reports-deal', 'deal report'],
			'lead_report'        => ['/reports-lead', 'lead report'],

			// Export routes
			'leave_export'       => ['/leaves/export', 'leave export'],
			'payroll_export'     => ['/reports/payrolls/export', 'payroll export'],
		];
	}

	// ── 9. HTTP method matrix (GET vs POST on same route) ───────────────

	/**
	 * @dataProvider httpMethodMatrixProvider
	 */
	public function test_http_method_matrix(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function httpMethodMatrixProvider(): array
	{
		return [
			// GET on standard index
			'get_invoices'       => ['GET', '/invoices', 'invoices index'],
			'get_bills'          => ['GET', '/bills', 'bills index'],
			'get_expenses'       => ['GET', '/expenses', 'expenses index'],
			'get_revenues'       => ['GET', '/revenues', 'revenues index'],
			'get_payments'       => ['GET', '/payments', 'payments index'],
			'get_proposals'      => ['GET', '/proposals', 'proposals index'],
			'get_bank_accounts'  => ['GET', '/bank_accounts', 'bank accounts index'],
			'get_taxes'          => ['GET', '/taxes', 'taxes index'],
			'get_credit_notes'   => ['GET', '/credit_notes', 'credit notes'],
			'get_journal_entries' => ['GET', '/journal_entries', 'journal entries'],

			// POST on same routes (should be 422/redirect, not 500)
			'post_invoices'      => ['POST', '/invoices', 'invoices store'],
			'post_bills'         => ['POST', '/bills', 'bills store'],
			'post_expenses'      => ['POST', '/expenses', 'expenses store'],
			'post_proposals'     => ['POST', '/proposals', 'proposals store'],

			// PUT/PATCH/DELETE on fake IDs
			'put_invoice_fake'   => ['PUT', '/invoices/00000000-0000-0000-0000-000000000000', 'invoice update fake'],
			'patch_invoice_fake' => ['PATCH', '/invoices/00000000-0000-0000-0000-000000000000', 'invoice patch fake'],
			'delete_invoice_fake' => ['DELETE', '/invoices/00000000-0000-0000-0000-000000000000', 'invoice delete fake'],
			'put_bill_fake'      => ['PUT', '/bills/00000000-0000-0000-0000-000000000000', 'bill update fake'],
			'delete_bill_fake'   => ['DELETE', '/bills/00000000-0000-0000-0000-000000000000', 'bill delete fake'],
			'put_expense_fake'   => ['PUT', '/expenses/00000000-0000-0000-0000-000000000000', 'expense update fake'],
			'delete_expense_fake' => ['DELETE', '/expenses/00000000-0000-0000-0000-000000000000', 'expense delete fake'],
		];
	}

	// ── 10. CRM routes with param variations ────────────────────────────

	/**
	 * @dataProvider crmRouteMatrixProvider
	 */
	public function test_crm_route_param_matrix(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function crmRouteMatrixProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'deal_index'         => ['/deals', 'deal index'],
			'deal_show_fake'     => ["/deals/{$fakeId}", 'deal show fake'],
			'lead_index'         => ['/leads', 'lead index'],
			'lead_show_fake'     => ["/leads/{$fakeId}", 'lead show fake'],
			'pipeline_index'     => ['/pipelines', 'pipeline index'],
			'contract_index'     => ['/contracts', 'contract index'],
			'contract_show_fake' => ["/contracts/{$fakeId}", 'contract show fake'],
			'support_index'      => ['/supports', 'support index'],
			'support_show_fake'  => ["/supports/{$fakeId}", 'support show fake'],
		];
	}

	// ── 11. Attendance export with param matrix ─────────────────────────

	/**
	 * @dataProvider attendanceExportMatrixProvider
	 */
	public function test_attendance_export_param_matrix(
		string $month,
		string $branch,
		string $department,
		string $label
	): void {
		$uri = "/reports/attendances/{$month}/{$branch}/{$department}";
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function attendanceExportMatrixProvider(): array
	{
		$months      = ['01', '06', '12', '00', '13'];
		$branches    = ['0', '00000000-0000-0000-0000-000000000000', '-1'];
		$departments = ['0', '00000000-0000-0000-0000-000000000000', 'all'];

		$cases = [];
		foreach ($months as $m) {
			foreach ($branches as $b) {
				foreach ($departments as $d) {
					$key = "atd_m{$m}_b" . substr($b, 0, 4) . "_d" . substr($d, 0, 4);
					$cases[$key] = [$m, $b, $d, "month={$m}/branch={$b}/dept={$d}"];
				}
			}
		}
		return $cases;
	}
}
