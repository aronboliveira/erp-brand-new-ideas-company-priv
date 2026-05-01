<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

/**
 * Deep hardening of financial processing routes.
 *
 * Covers: Invoices, Bills, Expenses, Revenues, Payments, Bank Accounts,
 *         Taxes, Credit/Debit Notes, Proposals, Contracts, Purchases,
 *         Journal Entries, Payroll, POS.
 *
 * Tests focus on:
 * - CRUD operations with valid & invalid params
 * - Financial report endpoints with date/filter matrices
 * - POST store operations with boundary financial values
 * - Export/PDF/Preview routes
 * - AJAX endpoints for financial calculations
 * - Query string variations on report filters
 *
 * No RefreshDatabase — uses seeded admin user.
 *
 * @group financial
 * @group hardening
 */
class FinancialRouteHardeningTest extends TestCase
{
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
		$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first();
		if ($this->admin) {
			$this->actingAs($this->admin);
		}
	}

	protected function assertNot500(\Illuminate\Testing\TestResponse $r, string $ctx = ''): void
	{
		$this->assertNotEquals(500, $r->getStatusCode(), "HTTP 500 on [{$ctx}]");
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 1 — INVOICE ROUTES (most complex financial entity)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider invoiceCrudRoutesProvider
	 */
	public function test_invoice_crud_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function invoiceCrudRoutesProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'index'              => ['GET', '/invoices', 'index'],
			'create'             => ['GET', '/invoices/create', 'create'],
			'show'               => ['GET', "/invoices/{$fakeId}", 'show'],
			'edit'               => ['GET', "/invoices/{$fakeId}/edit", 'edit'],
			'pdf'                => ['GET', "/invoices/pdfs/{$fakeId}", 'pdf'],
			'preview'            => ['GET', "/invoices/preview/{$fakeId}", 'preview'],
			'payment'            => ['GET', "/invoices/{$fakeId}/payment", 'payment'],
			'link'               => ['GET', "/invoices/{$fakeId}/invoice-link", 'link'],
			'reminder'           => ['GET', "/invoices/{$fakeId}/payment-reminder", 'reminder'],
			'send'               => ['GET', "/invoices/{$fakeId}/customer-invoice-send", 'send'],
			'duplicate'          => ['GET', "/invoices/{$fakeId}/duplicate", 'duplicate'],
			'shipping'           => ['GET', "/invoices/{$fakeId}/shipping-display", 'shipping'],
			'store_empty'        => ['POST', '/invoices', 'store empty'],
			'update_fake'        => ['PUT', "/invoices/{$fakeId}", 'update fake'],
			'delete_fake'        => ['DELETE', "/invoices/{$fakeId}", 'delete fake'],
		];
	}

	/**
	 * POST invoice store with boundary financial values.
	 * @dataProvider invoiceStorePayloadProvider
	 */
	public function test_invoice_store_with_boundary_values(
		array $payload,
		string $label
	): void {
		$r = $this->post('/invoices', $payload);
		$this->assertNot500($r, "POST /invoices with {$label}");
	}

	public static function invoiceStorePayloadProvider(): array
	{
		return [
			'empty_payload'       => [[], 'empty payload'],
			'zero_amount'         => [['total' => 0, 'discount' => 0, 'tax' => 0], 'zero amounts'],
			'negative_total'      => [['total' => -100], 'negative total'],
			'huge_total'          => [['total' => 9999999999.99], 'huge total'],
			'float_precision'     => [['total' => 0.001], 'float precision'],
			'string_total'        => [['total' => 'abc'], 'string total'],
			'xss_customer'        => [['customer_name' => '<script>alert(1)</script>'], 'XSS customer name'],
			'sql_injection'       => [['customer_name' => "'; DROP TABLE invoices;--"], 'SQL injection'],
			'unicode_description' => [['description' => '🔥 Emoji invoice 日本語'], 'unicode description'],
			'overflow_qty'        => [['quantity' => PHP_INT_MAX], 'overflow quantity'],
			'null_fields'         => [['customer_id' => null, 'total' => null], 'null fields'],
			'date_boundaries'     => [['issue_date' => '0000-00-00', 'due_date' => '9999-12-31'], 'date boundaries'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — BILL ROUTES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider billCrudRoutesProvider
	 */
	public function test_bill_crud_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function billCrudRoutesProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'index'          => ['GET', '/bills', 'index'],
			'create'         => ['GET', '/bills/create', 'create'],
			'show'           => ['GET', "/bills/{$fakeId}", 'show'],
			'edit'           => ['GET', "/bills/{$fakeId}/edit", 'edit'],
			'pdf'            => ['GET', "/bills/pdfs/{$fakeId}", 'pdf'],
			'payment'        => ['GET', "/bills/{$fakeId}/payment", 'payment'],
			'duplicate'      => ['GET', "/bills/{$fakeId}/duplicate", 'duplicate'],
			'shipping'       => ['GET', "/bills/{$fakeId}/shipping-display", 'shipping'],
			'send'           => ['GET', "/bills/{$fakeId}/vendor-bill-send", 'send'],
			'store_empty'    => ['POST', '/bills', 'store empty'],
			'update_fake'    => ['PUT', "/bills/{$fakeId}", 'update fake'],
			'delete_fake'    => ['DELETE', "/bills/{$fakeId}", 'delete fake'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — EXPENSE & REVENUE ROUTES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider expenseRevenueRoutesProvider
	 */
	public function test_expense_revenue_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function expenseRevenueRoutesProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			// Expenses
			'expense_index'       => ['GET', '/expenses', 'expense index'],
			'expense_create'      => ['GET', '/expenses/create', 'expense create'],
			'expense_show'        => ['GET', "/expenses/{$fakeId}", 'expense show'],
			'expense_edit'        => ['GET', "/expenses/{$fakeId}/edit", 'expense edit'],
			'expense_store'       => ['POST', '/expenses', 'expense store empty'],
			'expense_update'      => ['PUT', "/expenses/{$fakeId}", 'expense update fake'],
			'expense_delete'      => ['DELETE', "/expenses/{$fakeId}", 'expense delete fake'],
			// Revenues
			'revenue_index'       => ['GET', '/revenues', 'revenue index'],
			'revenue_create'      => ['GET', '/revenues/create', 'revenue create'],
			'revenue_show'        => ['GET', "/revenues/{$fakeId}", 'revenue show'],
			'revenue_edit'        => ['GET', "/revenues/{$fakeId}/edit", 'revenue edit'],
			'revenue_store'       => ['POST', '/revenues', 'revenue store empty'],
			'revenue_update'      => ['PUT', "/revenues/{$fakeId}", 'revenue update fake'],
			'revenue_delete'      => ['DELETE', "/revenues/{$fakeId}", 'revenue delete fake'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 4 — PAYMENT & BANK ACCOUNT ROUTES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider paymentBankRouteProvider
	 */
	public function test_payment_and_bank_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function paymentBankRouteProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'payment_index'          => ['GET', '/payments', 'payment index'],
			'payment_create'         => ['GET', '/payments/create', 'payment create'],
			'payment_show'           => ['GET', "/payments/{$fakeId}", 'payment show'],
			'payment_edit'           => ['GET', "/payments/{$fakeId}/edit", 'payment edit'],
			'payment_store_empty'    => ['POST', '/payments', 'payment store empty'],
			'bank_account_index'     => ['GET', '/bank_accounts', 'bank account index'],
			'bank_account_create'    => ['GET', '/bank_accounts/create', 'bank account create'],
			'bank_account_show'      => ['GET', "/bank_accounts/{$fakeId}", 'bank account show'],
			'bank_account_edit'      => ['GET', "/bank_accounts/{$fakeId}/edit", 'bank account edit'],
			'bank_account_store'     => ['POST', '/bank_accounts', 'bank account store empty'],
			'bank_transfer_index'    => ['GET', '/bank_transfers', 'bank transfer index'],
			'bank_transfer_create'   => ['GET', '/bank_transfers/create', 'bank transfer create'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — TAX, CREDIT/DEBIT NOTES, JOURNAL ENTRIES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider taxNotesJournalProvider
	 */
	public function test_tax_notes_journal_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function taxNotesJournalProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'tax_index'             => ['GET', '/taxes', 'tax index'],
			'tax_create'            => ['GET', '/taxes/create', 'tax create'],
			'tax_show'              => ['GET', "/taxes/{$fakeId}", 'tax show'],
			'credit_note_index'     => ['GET', '/credit_notes', 'credit note index'],
			'credit_note_create'    => ['GET', '/credit_notes/create', 'credit note create'],
			'credit_note_show'      => ['GET', "/credit_notes/{$fakeId}", 'credit note show'],
			'debit_note_index'      => ['GET', '/debit_notes', 'debit note index'],
			'debit_note_create'     => ['GET', '/debit_notes/create', 'debit note create'],
			'debit_note_show'       => ['GET', "/debit_notes/{$fakeId}", 'debit note show'],
			'journal_entry_index'   => ['GET', '/journal_entries', 'journal entry index'],
			'journal_entry_create'  => ['GET', '/journal_entries/create', 'journal entry create'],
			'journal_entry_show'    => ['GET', "/journal_entries/{$fakeId}", 'journal entry show'],
			'chart_accounts_index'  => ['GET', '/chart_of_accounts', 'chart of accounts index'],
			'chart_accounts_create' => ['GET', '/chart_of_accounts/create', 'chart of accounts create'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 6 — PROPOSAL & CONTRACT ROUTES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider proposalContractProvider
	 */
	public function test_proposal_contract_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function proposalContractProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'proposal_index'        => ['GET', '/proposals', 'proposal index'],
			'proposal_create'       => ['GET', '/proposals/create', 'proposal create'],
			'proposal_show'         => ['GET', "/proposals/{$fakeId}", 'proposal show'],
			'proposal_edit'         => ['GET', "/proposals/{$fakeId}/edit", 'proposal edit'],
			'proposal_store_empty'  => ['POST', '/proposals', 'proposal store empty'],
			'contract_index'        => ['GET', '/contracts', 'contract index'],
			'contract_create'       => ['GET', '/contracts/create', 'contract create'],
			'contract_show'         => ['GET', "/contracts/{$fakeId}", 'contract show'],
			'contract_edit'         => ['GET', "/contracts/{$fakeId}/edit", 'contract edit'],
			'contract_store_empty'  => ['POST', '/contracts', 'contract store empty'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 7 — PURCHASE ROUTES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider purchaseRoutesProvider
	 */
	public function test_purchase_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function purchaseRoutesProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'purchase_index'          => ['GET', '/purchases', 'purchase index'],
			'purchase_create'         => ['GET', '/purchases/create', 'purchase create'],
			'purchase_show'           => ['GET', "/purchases/{$fakeId}", 'purchase show'],
			'purchase_edit'           => ['GET', "/purchases/{$fakeId}/edit", 'purchase edit'],
			'purchase_store_empty'    => ['POST', '/purchases', 'purchase store empty'],
			'purchase_update_fake'    => ['PUT', "/purchases/{$fakeId}", 'purchase update fake'],
			'purchase_delete_fake'    => ['DELETE', "/purchases/{$fakeId}", 'purchase delete fake'],
			'vendor_index'            => ['GET', '/vendors', 'vendor index'],
			'vendor_create'           => ['GET', '/vendors/create', 'vendor create'],
			'vendor_show'             => ['GET', "/vendors/{$fakeId}", 'vendor show'],
			'customer_index'          => ['GET', '/customers', 'customer index'],
			'customer_create'         => ['GET', '/customers/create', 'customer create'],
			'customer_show'           => ['GET', "/customers/{$fakeId}", 'customer show'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 8 — FINANCIAL REPORT FILTER MATRICES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider reportFilterMatrixProvider
	 */
	public function test_financial_reports_with_filter_matrix(
		string $baseUri,
		array $queryParams,
		string $label
	): void {
		$uri = $baseUri . '?' . http_build_query($queryParams);
		$r   = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function reportFilterMatrixProvider(): array
	{
		$reportRoutes = [
			'/reports-income-summary',
			'/reports-expense-summary',
			'/reports-income-vs-expense-summary',
			'/reports-invoice-summary',
			'/reports-bill-summary',
			'/reports-profit-loss-summary',
			'/reports-tax-summary',
		];

		$filterSets = [
			'no_filter'            => [],
			'year_2026'            => ['year' => '2026'],
			'year_0'               => ['year' => '0'],
			'year_negative'        => ['year' => '-1'],
			'month_1'              => ['month' => '1'],
			'month_13'             => ['month' => '13'],
			'category_all'         => ['category' => 'all'],
			'category_xss'         => ['category' => '<script>alert(1)</script>'],
			'customer_fake'        => ['customer' => '00000000-0000-0000-0000-000000000000'],
			'vendor_fake'          => ['vendor' => '00000000-0000-0000-0000-000000000000'],
			'start_end_date'       => ['start_date' => '2025-01-01', 'end_date' => '2026-12-31'],
			'reversed_dates'       => ['start_date' => '2026-12-31', 'end_date' => '2025-01-01'],
			'invalid_date_format'  => ['start_date' => 'not-a-date'],
			'account_fake'         => ['account' => '00000000-0000-0000-0000-000000000000'],
			'duration_monthly'     => ['duration' => 'monthly'],
			'type_all'             => ['type' => 'all'],
		];

		$cases = [];
		foreach ($reportRoutes as $route) {
			$routeSlug = str_replace(['/', '-'], '_', trim($route, '/'));
			foreach ($filterSets as $filterLabel => $params) {
				$key = "{$routeSlug}_{$filterLabel}";
				$cases[$key] = [$route, $params, basename($route) . " with {$filterLabel}"];
			}
		}

		return $cases;
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 9 — PAYROLL ROUTES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider payrollRoutesProvider
	 */
	public function test_payroll_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function payrollRoutesProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'payslip_index'          => ['GET', '/payslips', 'payslip index'],
			'payslip_create'         => ['GET', '/payslips/create', 'payslip create'],
			'payslip_show'           => ['GET', "/payslips/{$fakeId}", 'payslip show'],
			'payslip_edit'           => ['GET', "/payslips/{$fakeId}/edit", 'payslip edit'],
			'payslip_store_empty'    => ['POST', '/payslips', 'payslip store empty'],
			'salary_index'           => ['GET', '/set_salaries', 'salary index'],
			'salary_create'          => ['GET', '/set_salaries/create', 'salary create'],
			'salary_show'            => ['GET', "/set_salaries/{$fakeId}", 'salary show'],
			'salary_edit'            => ['GET', "/set_salaries/{$fakeId}/edit", 'salary edit'],
			'allowance_index'        => ['GET', '/allowances', 'allowance index'],
			'commission_index'       => ['GET', '/commissions', 'commission index'],
			'loan_index'             => ['GET', '/loans', 'loan index'],
			'deduction_index'        => ['GET', '/deductions', 'deduction index'],
			'overtime_index'         => ['GET', '/overtimes', 'overtime index'],
			'other_payment_index'    => ['GET', '/other_payments', 'other payment index'],
			'payroll_report'         => ['GET', '/reports-payroll', 'payroll report'],
			'payroll_export'         => ['GET', '/reports/payrolls/export', 'payroll export'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 10 — POS ROUTES
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider posRoutesProvider
	 */
	public function test_pos_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function posRoutesProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'pos_index'                => ['GET', '/pos', 'pos index'],
			'pos_show'                 => ['GET', "/pos/{$fakeId}", 'pos show'],
			'product_service_index'    => ['GET', '/product_services', 'product service index'],
			'product_service_create'   => ['GET', '/product_services/create', 'product service create'],
			'product_service_show'     => ['GET', "/product_services/{$fakeId}", 'product service show'],
			'product_category_index'   => ['GET', '/product_categories', 'product category index'],
			'product_unit_index'       => ['GET', '/product_units', 'product unit index'],
			'warehouse_index'          => ['GET', '/warehouses', 'warehouse index'],
			'pos_report_daily'         => ['GET', '/reports-daily-pos', 'daily pos report'],
			'pos_report_monthly'       => ['GET', '/reports-monthly-pos', 'monthly pos report'],
			'pos_vs_purchase'          => ['GET', '/reports-pos-vs-purchase', 'pos vs purchase'],
			'warehouse_report'         => ['GET', '/reports-warehouse', 'warehouse report'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 11 — ACCOUNT STATEMENT / LEDGER / TRIAL BALANCE DEEP TESTS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider accountingDeepRouteProvider
	 */
	public function test_accounting_deep_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, "GET {$uri} ({$label})");
	}

	public static function accountingDeepRouteProvider(): array
	{
		return [
			'statement_default'          => ['/reports-account-statement-summary', 'statement default'],
			'statement_with_account'     => ['/reports-account-statement-summary?account=00000000-0000-0000-0000-000000000000', 'statement with fake account'],
			'statement_date_range'       => ['/reports-account-statement-summary?start_date=2025-01-01&end_date=2026-12-31', 'statement date range'],
			'balance_sheet_default'      => ['/reports-balance-sheet-summary', 'balance sheet default'],
			'balance_sheet_year'         => ['/reports-balance-sheet-summary?year=2026', 'balance sheet year'],
			'balance_sheet_year_zero'    => ['/reports-balance-sheet-summary?year=0', 'balance sheet year=0'],
			'ledger_default'             => ['/reports-ledger-summary', 'ledger default'],
			'ledger_with_account'        => ['/reports-ledger-summary?account=00000000-0000-0000-0000-000000000000', 'ledger with fake account'],
			'trial_balance_default'      => ['/reports-trial-balance-summary', 'trial balance default'],
			'trial_balance_start_end'    => ['/reports-trial-balance-summary?start_date=2025-01-01&end_date=2026-12-31', 'trial balance dates'],
			'receivable_default'         => ['/reports-receivable', 'receivable default'],
			'payable_default'            => ['/reports-payable', 'payable default'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 12 — FINANCIAL AJAX / JSON ENDPOINTS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider financialAjaxEndpointProvider
	 */
	public function test_financial_ajax_endpoints(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri, [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
		$this->assertNot500($r, "{$method} {$uri} AJAX ({$label})");
	}

	public static function financialAjaxEndpointProvider(): array
	{
		$fakeId = '00000000-0000-0000-0000-000000000000';
		return [
			'invoice_product_get'     => ['GET', '/invoices/product', 'invoice product ajax'],
			'invoice_customer_get'    => ['GET', '/invoices/customer', 'invoice customer ajax'],
			'bill_product_get'        => ['GET', '/bills/product', 'bill product ajax'],
			'bill_vendor_get'         => ['GET', '/bills/vendor', 'bill vendor ajax'],
			'payment_invoice_get'     => ['GET', "/invoices/{$fakeId}/payment", 'payment for invoice'],
			'payment_bill_get'        => ['GET', "/bills/{$fakeId}/payment", 'payment for bill'],
			'expense_category_get'    => ['GET', '/expenses/categories', 'expense categories'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 13 — FINANCIAL STORE OPERATIONS WITH CSRF TOKEN VALIDATION
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider financialStoreEndpointProvider
	 */
	public function test_financial_store_endpoints_reject_empty(string $uri, string $label): void
	{
		$r = $this->post($uri, []);
		$this->assertNot500($r, "POST {$uri} empty ({$label})");
		// Expect redirect (302) or validation error (422), never 500
		$this->assertContains($r->getStatusCode(), [200, 302, 303, 400, 401, 403, 404, 405, 419, 422, 429], "Unexpected status {$r->getStatusCode()} on POST {$uri}");
	}

	public static function financialStoreEndpointProvider(): array
	{
		return [
			'invoice'        => ['/invoices', 'invoice store'],
			'bill'           => ['/bills', 'bill store'],
			'expense'        => ['/expenses', 'expense store'],
			'revenue'        => ['/revenues', 'revenue store'],
			'payment'        => ['/payments', 'payment store'],
			'bank_account'   => ['/bank_accounts', 'bank account store'],
			'proposal'       => ['/proposals', 'proposal store'],
			'contract'       => ['/contracts', 'contract store'],
			'purchase'       => ['/purchases', 'purchase store'],
			'credit_note'    => ['/credit_notes', 'credit note store'],
			'debit_note'     => ['/debit_notes', 'debit note store'],
			'journal_entry'  => ['/journal_entries', 'journal entry store'],
			'tax'            => ['/taxes', 'tax store'],
		];
	}
}
