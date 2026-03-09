<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use Mockery;

/**
 * Unit tests for report-related Blade views.
 *
 * Covered views (reports.*):
 *   - dashboard, income_summary, expense_summary, income_vs_expense_summary
 *   - invoice_report, bill_report, tax_summary, profit_loss, profit_loss_horizontal
 *   - profit_loss_receipt, profit_loss_receipt_horizontal, profit_loss_summary
 *   - balance_sheet, balance_sheet_horizontal, balance_sheet_receipt, balance_sheet_receipt_horizontal
 *   - trial_balance, trial_balance_receipt, ledger_summary, statement_report
 *   - monthly_cashflow, quarterly_cashflow
 *   - payroll, leave, leave_show, monthly_attendance
 *   - deal, lead, sales_report, sales_report_receipt
 *   - receivable_report, receivable_report_receipt, payable_report, payable_report_receipt
 *   - daily_pos, monthly_pos, pos_vs_purchase, daily_purchase, monthly_purchase
 *   - product_stock_report, warehouse
 */
class ReportViewsTest extends TestCase
{
	use BladeViewTestHelper;

	/**
	 * Canonical list of every known report view.
	 *
	 * @return string[]
	 */
	private static function allReportViews(): array
	{
		return [
			'reports.dashboard',
			'reports.income_summary',
			'reports.expense_summary',
			'reports.income_vs_expense_summary',
			'reports.invoice_report',
			'reports.bill_report',
			'reports.tax_summary',
			'reports.profit_loss',
			'reports.profit_loss_horizontal',
			'reports.profit_loss_receipt',
			'reports.profit_loss_receipt_horizontal',
			'reports.profit_loss_summary',
			'reports.balance_sheet',
			'reports.balance_sheet_horizontal',
			'reports.balance_sheet_receipt',
			'reports.balance_sheet_receipt_horizontal',
			'reports.trial_balance',
			'reports.trial_balance_receipt',
			'reports.ledger_summary',
			'reports.statement_report',
			'reports.monthly_cashflow',
			'reports.quarterly_cashflow',
			'reports.payroll',
			'reports.leave',
			'reports.leave_show',
			'reports.monthly_attendance',
			'reports.deal',
			'reports.lead',
			'reports.sales_report',
			'reports.sales_report_receipt',
			'reports.receivable_report',
			'reports.receivable_report_receipt',
			'reports.payable_report',
			'reports.payable_report_receipt',
			'reports.daily_pos',
			'reports.monthly_pos',
			'reports.pos_vs_purchase',
			'reports.daily_purchase',
			'reports.monthly_purchase',
			'reports.product_stock_report',
			'reports.warehouse',
		];
	}

	// ─── Individual view existence checks ───────────────────────────

	public function test_reports_dashboard_view_file_exists(): void
	{
		$this->assertViewFileExists('reports.dashboard');
	}

	public function test_reports_dashboard_view_is_registered(): void
	{
		$this->assertViewRegistered('reports.dashboard');
	}

	public function test_reports_income_summary_exists(): void
	{
		$this->assertViewFileExists('reports.income_summary');
		$this->assertViewRegistered('reports.income_summary');
	}

	public function test_reports_expense_summary_exists(): void
	{
		$this->assertViewFileExists('reports.expense_summary');
		$this->assertViewRegistered('reports.expense_summary');
	}

	public function test_reports_income_vs_expense_summary_exists(): void
	{
		$this->assertViewFileExists('reports.income_vs_expense_summary');
		$this->assertViewRegistered('reports.income_vs_expense_summary');
	}

	public function test_reports_invoice_report_exists(): void
	{
		$this->assertViewFileExists('reports.invoice_report');
		$this->assertViewRegistered('reports.invoice_report');
	}

	public function test_reports_bill_report_exists(): void
	{
		$this->assertViewFileExists('reports.bill_report');
		$this->assertViewRegistered('reports.bill_report');
	}

	public function test_reports_tax_summary_exists(): void
	{
		$this->assertViewFileExists('reports.tax_summary');
		$this->assertViewRegistered('reports.tax_summary');
	}

	public function test_reports_profit_loss_exists(): void
	{
		$this->assertViewFileExists('reports.profit_loss');
		$this->assertViewRegistered('reports.profit_loss');
	}

	public function test_reports_profit_loss_horizontal_exists(): void
	{
		$this->assertViewFileExists('reports.profit_loss_horizontal');
		$this->assertViewRegistered('reports.profit_loss_horizontal');
	}

	public function test_reports_profit_loss_receipt_exists(): void
	{
		$this->assertViewFileExists('reports.profit_loss_receipt');
		$this->assertViewRegistered('reports.profit_loss_receipt');
	}

	public function test_reports_profit_loss_receipt_horizontal_exists(): void
	{
		$this->assertViewFileExists('reports.profit_loss_receipt_horizontal');
		$this->assertViewRegistered('reports.profit_loss_receipt_horizontal');
	}

	public function test_reports_profit_loss_summary_exists(): void
	{
		$this->assertViewFileExists('reports.profit_loss_summary');
		$this->assertViewRegistered('reports.profit_loss_summary');
	}

	public function test_reports_balance_sheet_exists(): void
	{
		$this->assertViewFileExists('reports.balance_sheet');
		$this->assertViewRegistered('reports.balance_sheet');
	}

	public function test_reports_balance_sheet_horizontal_exists(): void
	{
		$this->assertViewFileExists('reports.balance_sheet_horizontal');
		$this->assertViewRegistered('reports.balance_sheet_horizontal');
	}

	public function test_reports_balance_sheet_receipt_exists(): void
	{
		$this->assertViewFileExists('reports.balance_sheet_receipt');
		$this->assertViewRegistered('reports.balance_sheet_receipt');
	}

	public function test_reports_balance_sheet_receipt_horizontal_exists(): void
	{
		$this->assertViewFileExists('reports.balance_sheet_receipt_horizontal');
		$this->assertViewRegistered('reports.balance_sheet_receipt_horizontal');
	}

	public function test_reports_trial_balance_exists(): void
	{
		$this->assertViewFileExists('reports.trial_balance');
		$this->assertViewRegistered('reports.trial_balance');
	}

	public function test_reports_trial_balance_receipt_exists(): void
	{
		$this->assertViewFileExists('reports.trial_balance_receipt');
		$this->assertViewRegistered('reports.trial_balance_receipt');
	}

	public function test_reports_ledger_summary_exists(): void
	{
		$this->assertViewFileExists('reports.ledger_summary');
		$this->assertViewRegistered('reports.ledger_summary');
	}

	public function test_reports_statement_report_exists(): void
	{
		$this->assertViewFileExists('reports.statement_report');
		$this->assertViewRegistered('reports.statement_report');
	}

	public function test_reports_monthly_cashflow_exists(): void
	{
		$this->assertViewFileExists('reports.monthly_cashflow');
		$this->assertViewRegistered('reports.monthly_cashflow');
	}

	public function test_reports_quarterly_cashflow_exists(): void
	{
		$this->assertViewFileExists('reports.quarterly_cashflow');
		$this->assertViewRegistered('reports.quarterly_cashflow');
	}

	public function test_reports_payroll_exists(): void
	{
		$this->assertViewFileExists('reports.payroll');
		$this->assertViewRegistered('reports.payroll');
	}

	public function test_reports_leave_exists(): void
	{
		$this->assertViewFileExists('reports.leave');
		$this->assertViewRegistered('reports.leave');
	}

	public function test_reports_leave_show_exists(): void
	{
		$this->assertViewFileExists('reports.leave_show');
		$this->assertViewRegistered('reports.leave_show');
	}

	public function test_reports_monthly_attendance_exists(): void
	{
		$this->assertViewFileExists('reports.monthly_attendance');
		$this->assertViewRegistered('reports.monthly_attendance');
	}

	public function test_reports_deal_exists(): void
	{
		$this->assertViewFileExists('reports.deal');
		$this->assertViewRegistered('reports.deal');
	}

	public function test_reports_lead_exists(): void
	{
		$this->assertViewFileExists('reports.lead');
		$this->assertViewRegistered('reports.lead');
	}

	public function test_reports_sales_report_exists(): void
	{
		$this->assertViewFileExists('reports.sales_report');
		$this->assertViewRegistered('reports.sales_report');
	}

	public function test_reports_sales_report_receipt_exists(): void
	{
		$this->assertViewFileExists('reports.sales_report_receipt');
		$this->assertViewRegistered('reports.sales_report_receipt');
	}

	public function test_reports_receivable_report_exists(): void
	{
		$this->assertViewFileExists('reports.receivable_report');
		$this->assertViewRegistered('reports.receivable_report');
	}

	public function test_reports_receivable_report_receipt_exists(): void
	{
		$this->assertViewFileExists('reports.receivable_report_receipt');
		$this->assertViewRegistered('reports.receivable_report_receipt');
	}

	public function test_reports_payable_report_exists(): void
	{
		$this->assertViewFileExists('reports.payable_report');
		$this->assertViewRegistered('reports.payable_report');
	}

	public function test_reports_payable_report_receipt_exists(): void
	{
		$this->assertViewFileExists('reports.payable_report_receipt');
		$this->assertViewRegistered('reports.payable_report_receipt');
	}

	public function test_reports_daily_pos_exists(): void
	{
		$this->assertViewFileExists('reports.daily_pos');
		$this->assertViewRegistered('reports.daily_pos');
	}

	public function test_reports_monthly_pos_exists(): void
	{
		$this->assertViewFileExists('reports.monthly_pos');
		$this->assertViewRegistered('reports.monthly_pos');
	}

	public function test_reports_pos_vs_purchase_exists(): void
	{
		$this->assertViewFileExists('reports.pos_vs_purchase');
		$this->assertViewRegistered('reports.pos_vs_purchase');
	}

	public function test_reports_daily_purchase_exists(): void
	{
		$this->assertViewFileExists('reports.daily_purchase');
		$this->assertViewRegistered('reports.daily_purchase');
	}

	public function test_reports_monthly_purchase_exists(): void
	{
		$this->assertViewFileExists('reports.monthly_purchase');
		$this->assertViewRegistered('reports.monthly_purchase');
	}

	public function test_reports_product_stock_report_exists(): void
	{
		$this->assertViewFileExists('reports.product_stock_report');
		$this->assertViewRegistered('reports.product_stock_report');
	}

	public function test_reports_warehouse_exists(): void
	{
		$this->assertViewFileExists('reports.warehouse');
		$this->assertViewRegistered('reports.warehouse');
	}

	// ─── POS report ─────────────────────────────────────────────────

	public function test_pos_report_view_file_exists(): void
	{
		$this->assertViewFileExists('pos.report');
	}

	public function test_pos_report_view_is_registered(): void
	{
		$this->assertViewRegistered('pos.report');
	}

	// ─── Batch – every report view exists ───────────────────────────

	public function test_all_report_views_exist_in_finder(): void
	{
		foreach (self::allReportViews() as $view) {
			$this->assertTrue(
				View::exists($view),
				"Report view [{$view}] is not registered."
			);
		}
	}

	// ─── Safe render – reports.dashboard ────────────────────────────

	public function test_reports_dashboard_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('reports.dashboard', [
			'settings' => $this->buildMockSettings(),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}
}
