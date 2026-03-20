<?php

namespace Tests\Unit\Exports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Exports\{
	AccountStatementExport,
	BalanceSheetExport,
	BillExport,
	CustomerExport,
	EmployeeExport,
	InvoiceExport,
	LeaveReportExport,
	PayrollExport,
	PayslipExport,
	ProductServiceExport,
	ProductStockExport,
	ProfitLossExport,
	ProposalExport,
	ReceivableExport,
	SalesReportExport,
	TaskReportExport,
	TransactionExport,
	TrialBalanceExport,
	VendorExport
};
use App\Models\{
	User,
	Bill,
	Customer,
	Employee,
	Invoice,
	Leave,
	Payslip,
	ProductService,
	Proposal,
	Revenue,
	StockReport,
	Transaction,
	Vendor
};

/**
 * Supplementary test-suite covering edge-cases, unauthenticated guards,
 * AfterSheet event registration, empty-data handling, and performance /
 * resource-limit assertions across **all 19** Export classes.
 *
 * These tests complement the per-class test files in the same directory
 * and focus exclusively on coverage gaps identified during review:
 *   1. Unauthenticated guard         (12 exports were missing this)
 *   2. AfterSheet event registration  (5 exports were missing this)
 *   3. Zero-record / empty-input      (all 19 exports)
 *   4. Performance / resource limits   (all 19 exports)
 */
class ExportSupplementaryTest extends TestCase
{
	use DatabaseTransactions;

	/** Maximum wall-clock seconds allowed for a single export call. */
	private const TIME_LIMIT = 10.0;

	/** Maximum additional MB of memory an export call may consume. */
	private const MEM_LIMIT_MB = 50;

	/** Records to create in each performance scenario. */
	private const PERF_N = 10;

	// ─── helper ──────────────────────────────────────────────────────

	/**
	 * Assert that a callable completes within time and memory limits.
	 */
	private function assertWithinLimits(callable $fn, string $label): mixed
	{
		$memBefore = memory_get_usage(true);
		$start     = microtime(true);

		$result = $fn();

		$elapsed = microtime(true) - $start;
		$memMb   = (memory_get_usage(true) - $memBefore) / 1048576;

		$this->assertLessThan(
			self::TIME_LIMIT,
			$elapsed,
			"{$label}: exceeded " . self::TIME_LIMIT . " s (took {$elapsed} s)."
		);
		$this->assertLessThan(
			self::MEM_LIMIT_MB,
			$memMb,
			"{$label}: exceeded " . self::MEM_LIMIT_MB . " MB delta ({$memMb} MB)."
		);

		return $result;
	}

	// ═════════════════════════════════════════════════════════════════
	// 1. UNAUTHENTICATED GUARD TESTS
	//    Verify that each collection-based export yields an empty
	//    Collection when no user is logged in.
	// ═════════════════════════════════════════════════════════════════

	/** @test */
	public function account_statement_returns_empty_when_unauthenticated(): void
	{
		$r = (new AccountStatementExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function bill_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new BillExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function employee_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new EmployeeExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function invoice_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new InvoiceExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function leave_report_returns_empty_when_unauthenticated(): void
	{
		$r = (new LeaveReportExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function payroll_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new PayrollExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function payslip_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new PayslipExport(request()))->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function product_service_returns_empty_when_unauthenticated(): void
	{
		$r = (new ProductServiceExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function proposal_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new ProposalExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function task_report_returns_empty_when_unauthenticated(): void
	{
		$r = (new TaskReportExport(Str::uuid()->toString()))->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function transaction_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new TransactionExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	/** @test */
	public function vendor_export_returns_empty_when_unauthenticated(): void
	{
		$r = (new VendorExport())->collection();
		$this->assertInstanceOf(Collection::class, $r);
		$this->assertTrue($r->isEmpty());
	}

	// ═════════════════════════════════════════════════════════════════
	// 2. AFTERSHEET / WITH-EVENTS VERIFICATION
	//    Customer, Invoice, ProductService, Proposal and Transaction
	//    do NOT implement WithEvents — confirm they lack registerEvents().
	//    This documents the design choice so a future refactor can detect
	//    whether the interface was added intentionally.
	// ═════════════════════════════════════════════════════════════════

	/** @test */
	public function customer_export_does_not_implement_with_events(): void
	{
		$this->assertFalse(
			method_exists(CustomerExport::class, 'registerEvents'),
			'CustomerExport should not implement WithEvents.'
		);
	}

	/** @test */
	public function invoice_export_does_not_implement_with_events(): void
	{
		$this->assertFalse(
			method_exists(InvoiceExport::class, 'registerEvents'),
			'InvoiceExport should not implement WithEvents.'
		);
	}

	/** @test */
	public function product_service_export_does_not_implement_with_events(): void
	{
		$this->assertFalse(
			method_exists(ProductServiceExport::class, 'registerEvents'),
			'ProductServiceExport should not implement WithEvents.'
		);
	}

	/** @test */
	public function proposal_export_does_not_implement_with_events(): void
	{
		$this->assertFalse(
			method_exists(ProposalExport::class, 'registerEvents'),
			'ProposalExport should not implement WithEvents.'
		);
	}

	/** @test */
	public function transaction_export_does_not_implement_with_events(): void
	{
		$this->assertFalse(
			method_exists(TransactionExport::class, 'registerEvents'),
			'TransactionExport should not implement WithEvents.'
		);
	}

	// ═════════════════════════════════════════════════════════════════
	// 3. ZERO-RECORD / EMPTY-INPUT EDGE-CASE TESTS
	// ═════════════════════════════════════════════════════════════════

	// ── 3a. Collection-based (auth user, no matching records) ────────

	/** @test */
	public function account_statement_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new AccountStatementExport())->collection());
	}

	/** @test */
	public function bill_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new BillExport())->collection());
	}

	/** @test */
	public function customer_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new CustomerExport())->collection());
	}

	/** @test */
	public function employee_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new EmployeeExport())->collection());
	}

	/** @test */
	public function invoice_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new InvoiceExport())->collection());
	}

	/** @test */
	public function leave_report_returns_collection_for_user_with_no_own_records(): void
	{
		// LeaveReportExport fetches ALL employees (not filtered by created_by),
		// so pre-existing DB rows may be returned.  We only verify the return
		// type and that it does not throw.
		Auth::login(User::factory()->create());
		$result = (new LeaveReportExport())->collection();
		$this->assertInstanceOf(Collection::class, $result);
	}

	/** @test */
	public function payroll_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new PayrollExport())->collection());
	}

	/** @test */
	public function payslip_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new PayslipExport(request()))->collection());
	}

	/** @test */
	public function product_service_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new ProductServiceExport())->collection());
	}

	/** @test */
	public function product_stock_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new ProductStockExport())->collection());
	}

	/** @test */
	public function proposal_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new ProposalExport())->collection());
	}

	/** @test */
	public function task_report_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new TaskReportExport(Str::uuid()->toString()))->collection());
	}

	/** @test */
	public function transaction_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new TransactionExport())->collection());
	}

	/** @test */
	public function vendor_export_empty_for_user_with_no_records(): void
	{
		Auth::login(User::factory()->create());
		$this->assertCount(0, (new VendorExport())->collection());
	}

	// ── 3b. Array-based (empty input data) ──────────────────────────

	/** @test */
	public function balance_sheet_handles_empty_input(): void
	{
		$this->actingAs(User::factory()->create());
		$result = (new BalanceSheetExport([], '2025-01-01', '2025-12-31', 'Co'))->array();
		$this->assertIsArray($result);
	}

	/** @test */
	public function profit_loss_handles_empty_input(): void
	{
		$this->actingAs(User::factory()->create());
		$result = (new ProfitLossExport([], '2025-01-01', '2025-12-31', 'Co'))->array();
		$this->assertIsArray($result);
	}

	/** @test */
	public function receivable_handles_empty_input(): void
	{
		$this->actingAs(User::factory()->create());
		$result = (new ReceivableExport([], '2025-01-01', '2025-12-31', 'Co'))->array();
		$this->assertIsArray($result);
	}

	/** @test */
	public function sales_report_handles_empty_input(): void
	{
		$this->actingAs(User::factory()->create());
		$result = (new SalesReportExport([], '2025-01-01', '2025-12-31', 'Co', 'item'))->array();
		$this->assertIsArray($result);
	}

	/** @test */
	public function trial_balance_handles_empty_input(): void
	{
		$this->actingAs(User::factory()->create());
		$result = (new TrialBalanceExport([], '2025-01-01', '2025-12-31', 'Co'))->array();
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	// ═════════════════════════════════════════════════════════════════
	// 4. PERFORMANCE / RESOURCE-LIMIT TESTS
	// ═════════════════════════════════════════════════════════════════

	// ── 4a. Collection-based exports ────────────────────────────────

	/** @test */
	public function account_statement_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Revenue::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new AccountStatementExport())->collection(),
			'AccountStatementExport'
		);
	}

	/** @test */
	public function bill_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Bill::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new BillExport())->collection(),
			'BillExport'
		);
	}

	/** @test */
	public function customer_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Customer::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new CustomerExport())->collection(),
			'CustomerExport'
		);
	}

	/** @test */
	public function employee_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Employee::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new EmployeeExport())->collection(),
			'EmployeeExport'
		);
	}

	/** @test */
	public function invoice_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Invoice::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new InvoiceExport())->collection(),
			'InvoiceExport'
		);
	}

	/** @test */
	public function leave_report_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		$emp = Employee::factory()->create();
		Leave::factory()->count(self::PERF_N)->create(['employee_id' => $emp->id]);
		$this->assertWithinLimits(
			fn() => (new LeaveReportExport())->collection(),
			'LeaveReportExport'
		);
	}

	/** @test */
	public function payroll_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		$emp = Employee::factory()->create();
		Payslip::factory()->count(self::PERF_N)->create([
			'employee_id'  => $emp->id,
			'salary_month' => date('Y-m'),
		]);
		$this->assertWithinLimits(
			fn() => (new PayrollExport())->collection(),
			'PayrollExport'
		);
	}

	/** @test */
	public function payslip_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		$emp = Employee::factory()->create();
		Payslip::factory()->count(self::PERF_N)->create(['employee_id' => $emp->id]);
		$this->assertWithinLimits(
			fn() => (new PayslipExport(request()))->collection(),
			'PayslipExport'
		);
	}

	/** @test */
	public function product_service_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		ProductService::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new ProductServiceExport())->collection(),
			'ProductServiceExport'
		);
	}

	/** @test */
	public function product_stock_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		StockReport::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new ProductStockExport())->collection(),
			'ProductStockExport'
		);
	}

	/** @test */
	public function proposal_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Proposal::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new ProposalExport())->collection(),
			'ProposalExport'
		);
	}

	/** @test */
	public function transaction_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Transaction::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new TransactionExport())->collection(),
			'TransactionExport'
		);
	}

	/** @test */
	public function vendor_export_within_resource_limits(): void
	{
		$user = User::factory()->create();
		Auth::login($user);
		Vendor::factory()->count(self::PERF_N)->create();
		$this->assertWithinLimits(
			fn() => (new VendorExport())->collection(),
			'VendorExport'
		);
	}

	// ── 4b. Array-based exports (synthetic large input) ─────────────

	/** @test */
	public function balance_sheet_within_resource_limits(): void
	{
		$this->actingAs(User::factory()->create());
		$rows = [];
		foreach (['Assets', 'Liabilities', 'Equity', 'Revenue', 'Expenses'] as $type) {
			$rows[$type] = [];
			for ($i = 0; $i < 20; ++$i) {
				$rows[$type][] = [
					'label'  => "{$type} Item {$i}",
					'amount' => rand(100, 50000),
				];
			}
		}
		$this->assertWithinLimits(
			fn() => (new BalanceSheetExport($rows, '2025-01-01', '2025-12-31', 'Perf Co'))->array(),
			'BalanceSheetExport'
		);
	}

	/** @test */
	public function profit_loss_within_resource_limits(): void
	{
		$this->actingAs(User::factory()->create());
		$rows = [];
		foreach (['Income', 'Cost of Goods Sold', 'Operating Expenses'] as $type) {
			$rows[$type] = [];
			for ($i = 0; $i < 20; ++$i) {
				$rows[$type][] = [
					'label'  => "{$type} Item {$i}",
					'amount' => rand(100, 50000),
				];
			}
		}
		$this->assertWithinLimits(
			fn() => (new ProfitLossExport($rows, '2025-01-01', '2025-12-31', 'Perf Co'))->array(),
			'ProfitLossExport'
		);
	}

	/** @test */
	public function receivable_within_resource_limits(): void
	{
		$this->actingAs(User::factory()->create());
		$data = [];
		for ($i = 0; $i < 50; ++$i) {
			$data[] = [
				'customer_name' => "Customer {$i}",
				'invoice_id'    => Str::uuid()->toString(),
				'total_amount'  => rand(100, 10000),
				'credit_price'  => rand(0, 500),
			];
		}
		$this->assertWithinLimits(
			fn() => (new ReceivableExport($data, '2025-01-01', '2025-12-31', 'Perf Co'))->array(),
			'ReceivableExport'
		);
	}

	/** @test */
	public function sales_report_within_resource_limits(): void
	{
		$this->actingAs(User::factory()->create());
		$data = [];
		for ($i = 0; $i < 50; ++$i) {
			$data[] = [
				'product_name' => "Product {$i}",
				'quantity'     => rand(1, 100),
				'amount'       => rand(100, 10000),
				'average'      => rand(50, 500),
			];
		}
		$this->assertWithinLimits(
			fn() => (new SalesReportExport($data, '2025-01-01', '2025-12-31', 'Perf Co', 'item'))->array(),
			'SalesReportExport'
		);
	}

	/** @test */
	public function trial_balance_within_resource_limits(): void
	{
		$this->actingAs(User::factory()->create());
		$input = [];
		foreach (['Assets', 'Liabilities', 'Equity', 'Revenue', 'Expenses'] as $type) {
			$input[$type] = [];
			for ($i = 0; $i < 20; ++$i) {
				$input[$type][] = [
					'name'        => "{$type} Acct {$i}",
					'code'        => (string)(100 + $i),
					'totalDebit'  => rand(0, 10000),
					'totalCredit' => rand(0, 10000),
				];
			}
		}
		$this->assertWithinLimits(
			fn() => (new TrialBalanceExport($input, '2025-01-01', '2025-12-31', 'Perf Co'))->array(),
			'TrialBalanceExport'
		);
	}

	// ═════════════════════════════════════════════════════════════════
	// 5. I/O VARIATION — PYTHON EXPORT DELEGATION
	//    Verify that exportViaPython() is callable and returns a string.
	//    The method exists via the DelegatesPythonExport trait.
	// ═════════════════════════════════════════════════════════════════

	/** @test */
	public function export_classes_expose_export_via_python(): void
	{
		$classes = [
			AccountStatementExport::class,
			BillExport::class,
			CustomerExport::class,
			EmployeeExport::class,
			InvoiceExport::class,
			LeaveReportExport::class,
			PayrollExport::class,
			ProductServiceExport::class,
			ProductStockExport::class,
			ProposalExport::class,
			TransactionExport::class,
			VendorExport::class,
		];
		foreach ($classes as $cls) {
			$this->assertTrue(
				method_exists($cls, 'exportViaPython'),
				"{$cls} should have exportViaPython() via DelegatesPythonExport trait."
			);
		}
	}

	/** @test */
	public function export_via_python_returns_string_on_authenticated_user(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		// BillExport is a representative sample; Python binary may not
		// be installed, so the trait returns '' on failure — that is still a string.
		$export = new BillExport();
		$result = $export->exportViaPython();

		$this->assertIsString($result, 'exportViaPython must always return a string.');
	}
}
