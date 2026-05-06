<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

/**
 * View rendering hardening test.
 *
 * Verifies that all major views (tables, grids, cards) across every module
 * render without HTTP 500 errors. Extends coverage from the existing 6
 * view controller tests (Bill, Contract, Dashboard, Employee, Invoice, Project)
 * to ALL modules: CRM, POS, Accounting, Settings, Admin, Reports.
 *
 * Also tests view-mode switching where routes accept list/grid/card params.
 *
 * No RefreshDatabase — uses seeded admin user.
 *
 * @group views
 * @group hardening
 */
class ViewRenderingHardeningTest extends TestCase
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
		$this->assertNotEquals(500, $r->getStatusCode(), "HTTP 500 on [{$ctx}]");
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 1 — DASHBOARD & HOME VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider dashboardViewProvider
	 */
	public function test_dashboard_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function dashboardViewProvider(): array
	{
		return [
			'home'              => ['/home', 'home dashboard'],
			'dashboard'         => ['/dashboard', 'dashboard'],
			'dashboard_account' => ['/account-dashboard-widget', 'account widget'],
			'dashboard_hrm'     => ['/hrm-dashboard-widget', 'hrm widget'],
			'dashboard_project' => ['/project-dashboard-widget', 'project widget'],
			'dashboard_crm'     => ['/crm-dashboard-widget', 'crm widget'],
			'dashboard_pos'     => ['/pos-dashboard-widget', 'pos widget'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — FINANCIAL MODULE INDEX VIEWS (tables/grids)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider financialIndexViewProvider
	 */
	public function test_financial_index_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function financialIndexViewProvider(): array
	{
		return [
			'invoices'          => ['/invoices', 'invoices'],
			'bills'             => ['/bills', 'bills'],
			'expenses'          => ['/expenses', 'expenses'],
			'revenues'          => ['/revenues', 'revenues'],
			'payments'          => ['/payments', 'payments'],
			'bank_accounts'     => ['/bank_accounts', 'bank accounts'],
			'bank_transfers'    => ['/bank_transfers', 'bank transfers'],
			'taxes'             => ['/taxes', 'taxes'],
			'credit_notes'      => ['/credit_notes', 'credit notes'],
			'debit_notes'       => ['/debit_notes', 'debit notes'],
			'journal_entries'   => ['/journal_entries', 'journal entries'],
			'chart_of_accounts' => ['/chart_of_accounts', 'chart of accounts'],
			'proposals'         => ['/proposals', 'proposals'],
			'contracts'         => ['/contracts', 'contracts'],
			'purchases'         => ['/purchases', 'purchases'],
			'vendors'           => ['/vendors', 'vendors'],
			'customers'         => ['/customers', 'customers'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — FINANCIAL CREATE FORM VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider financialCreateViewProvider
	 */
	public function test_financial_create_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function financialCreateViewProvider(): array
	{
		return [
			'invoice_create'        => ['/invoices/create', 'invoice create'],
			'bill_create'           => ['/bills/create', 'bill create'],
			'expense_create'        => ['/expenses/create', 'expense create'],
			'revenue_create'        => ['/revenues/create', 'revenue create'],
			'payment_create'        => ['/payments/create', 'payment create'],
			'bank_account_create'   => ['/bank_accounts/create', 'bank account create'],
			'bank_transfer_create'  => ['/bank_transfers/create', 'bank transfer create'],
			'tax_create'            => ['/taxes/create', 'tax create'],
			'credit_note_create'    => ['/credit_notes/create', 'credit note create'],
			'debit_note_create'     => ['/debit_notes/create', 'debit note create'],
			'journal_entry_create'  => ['/journal_entries/create', 'journal entry create'],
			'chart_account_create'  => ['/chart_of_accounts/create', 'chart of accounts create'],
			'proposal_create'       => ['/proposals/create', 'proposal create'],
			'contract_create'       => ['/contracts/create', 'contract create'],
			'purchase_create'       => ['/purchases/create', 'purchase create'],
			'vendor_create'         => ['/vendors/create', 'vendor create'],
			'customer_create'       => ['/customers/create', 'customer create'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 4 — HRM MODULE VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider hrmIndexViewProvider
	 */
	public function test_hrm_index_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function hrmIndexViewProvider(): array
	{
		return [
			'employees'         => ['/employees', 'employees'],
			'leaves'            => ['/leaves', 'leaves'],
			'leave_types'       => ['/leave_types', 'leave types'],
			'attendances'       => ['/employee_attendances', 'attendances'],
			'mark_attendances'  => ['/mark_attendances', 'mark attendances'],
			'bulk_attendances'  => ['/bulk_attendances', 'bulk attendances'],
			'payslips'          => ['/payslips', 'payslips'],
			'set_salaries'      => ['/set_salaries', 'salaries'],
			'departments'       => ['/departments', 'departments'],
			'designations'      => ['/designations', 'designations'],
			'branches'          => ['/branches', 'branches'],
			'awards'            => ['/awards', 'awards'],
			'trips'             => ['/trips', 'trips'],
			'transfers'         => ['/transfers', 'transfers'],
			'resignations'      => ['/resignations', 'resignations'],
			'terminations'      => ['/terminations', 'terminations'],
			'warnings'          => ['/warnings', 'warnings'],
			'complaints'        => ['/complaints', 'complaints'],
			'holidays'          => ['/holidays', 'holidays'],
			'allowances'        => ['/allowances', 'allowances'],
			'commissions'       => ['/commissions', 'commissions'],
			'loans'             => ['/loans', 'loans'],
			'deductions'        => ['/deductions', 'deductions'],
			'overtimes'         => ['/overtimes', 'overtimes'],
			'other_payments'    => ['/other_payments', 'other payments'],
			'events'            => ['/events', 'events'],
			'meetings'          => ['/meetings', 'meetings'],
			'trainings'         => ['/trainings', 'trainings'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — PROJECT MANAGEMENT VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider projectViewProvider
	 */
	public function test_project_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function projectViewProvider(): array
	{
		return [
			'projects'           => ['/projects', 'projects'],
			'project_reports'    => ['/project_reports', 'project reports'],
			'tasks'              => ['/tasks', 'tasks'],
			'milestones'         => ['/milestones', 'milestones'],
			'timesheets'         => ['/timesheets', 'timesheets'],
			'bugs_default'       => ['/bugs_reports', 'bugs default'],
			'bugs_list'          => ['/bugs_reports/list', 'bugs list'],
			'bugs_grid'          => ['/bugs_reports/grid', 'bugs grid'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 6 — CRM MODULE VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider crmViewProvider
	 */
	public function test_crm_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function crmViewProvider(): array
	{
		return [
			'deals'              => ['/deals', 'deals'],
			'leads'              => ['/leads', 'leads'],
			'pipelines'          => ['/pipelines', 'pipelines'],
			'lead_stages'        => ['/lead_stages', 'lead stages'],
			'deal_stages'        => ['/deal_stages', 'deal stages'],
			'sources'            => ['/sources', 'sources'],
			'labels'             => ['/labels', 'labels'],
			'supports'           => ['/supports', 'supports'],
			'contracts'          => ['/contracts', 'contracts'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 7 — POS MODULE VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider posViewProvider
	 */
	public function test_pos_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function posViewProvider(): array
	{
		return [
			'pos_index'              => ['/pos', 'pos index'],
			'product_services'       => ['/product_services', 'product services'],
			'product_categories'     => ['/product_categories', 'product categories'],
			'product_units'          => ['/product_units', 'product units'],
			'warehouses'             => ['/warehouses', 'warehouses'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 8 — REPORT VIEWS (tables/charts)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider reportViewProvider
	 */
	public function test_report_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function reportViewProvider(): array
	{
		return [
			// Financial reports
			'income_summary'         => ['/reports-income-summary', 'income summary'],
			'expense_summary'        => ['/reports-expense-summary', 'expense summary'],
			'income_vs_expense'      => ['/reports-income-vs-expense-summary', 'income vs expense'],
			'tax_summary'            => ['/reports-tax-summary', 'tax summary'],
			'invoice_summary'        => ['/reports-invoice-summary', 'invoice summary'],
			'bill_summary'           => ['/reports-bill-summary', 'bill summary'],
			'profit_loss'            => ['/reports-profit-loss-summary', 'profit loss'],
			'account_statement'      => ['/reports-account-statement-summary', 'account statement'],
			'balance_sheet'          => ['/reports-balance-sheet-summary', 'balance sheet'],
			'ledger_summary'         => ['/reports-ledger-summary', 'ledger'],
			'trial_balance'          => ['/reports-trial-balance-summary', 'trial balance'],
			'receivable'             => ['/reports-receivable', 'receivable'],
			'payable'                => ['/reports-payable', 'payable'],

			// HRM reports
			'payroll_report'         => ['/reports-payroll', 'payroll'],
			'leave_report'           => ['/reports-leave', 'leave'],
			'monthly_attendance'     => ['/reports-monthly-attendance', 'monthly attendance'],

			// CRM reports
			'deal_report'            => ['/reports-deal', 'deal'],
			'lead_report'            => ['/reports-lead', 'lead'],

			// POS reports
			'daily_pos'              => ['/reports-daily-pos', 'daily pos'],
			'monthly_pos'            => ['/reports-monthly-pos', 'monthly pos'],
			'pos_vs_purchase'        => ['/reports-pos-vs-purchase', 'pos vs purchase'],
			'warehouse_report'       => ['/reports-warehouse', 'warehouse'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 9 — SETTINGS & ADMIN VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider settingsViewProvider
	 */
	public function test_settings_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function settingsViewProvider(): array
	{
		return [
			'settings'               => ['/settings', 'settings'],
			'company_settings'       => ['/company-settings', 'company settings'],
			'business_settings'      => ['/business-settings', 'business settings'],
			'email_settings'         => ['/email-settings', 'email settings'],
			'payment_settings'       => ['/payment-settings', 'payment settings'],
			'system_settings'        => ['/system-settings', 'system settings'],
			'users'                  => ['/users', 'users'],
			'roles'                  => ['/roles', 'roles'],
			'permissions'            => ['/permissions', 'permissions'],
			'email_templates'        => ['/email_templates', 'email templates'],
			'email_notification'     => ['/email-notification-settings', 'email notification settings'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 10 — DOCUMENT/NOTE/GOAL VIEWS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider miscModuleViewProvider
	 */
	public function test_misc_module_views(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function miscModuleViewProvider(): array
	{
		return [
			'documents'          => ['/documents', 'documents'],
			'notes'              => ['/notes', 'notes'],
			'goals'              => ['/goals', 'goals'],
			'goal_types'         => ['/goal_types', 'goal types'],
			'indicators'         => ['/indicators', 'indicators'],
			'assets'             => ['/assets', 'assets'],
			'custom_fields'      => ['/custom_fields', 'custom fields'],
			'coupons'            => ['/coupons', 'coupons'],
			'plans'              => ['/plans', 'plans'],
		];
	}
}
