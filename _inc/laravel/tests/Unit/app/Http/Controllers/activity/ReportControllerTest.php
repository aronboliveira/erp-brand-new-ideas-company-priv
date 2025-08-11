<?php

namespace Tests\Feature;

use App\Exports\ProfitLossExport;
use App\Models\{
	BankAccount,
	Bill,
	BillProduct,
	Branch,
	ChartOfAccount,
	ChartOfAccountType,
	ChartOfAccountSubType,
	Customer,
	Department,
	Employee,
	EmployeeAttendance,
	GoalType,
	Payment,
	Payslip,
	Pos,
	ProductServiceCategory,
	Purchase,
	Revenue,
	Invoice,
	InvoiceProduct,
	InvoicePayment,
	Leave,
	LeaveType,
	StockReport,
	Tax,
	User,
	Vendor,
	Warehouse,
	WarehouseProduct
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Maatwebsite\Excel\Facades\Excel;

class ReportControllerTest extends TestCase
{
	use RefreshDatabase;
	private $company;
	private $branch;
	private $employee;
	private $goalType;

	protected function setUp(): void
	{
		parent::setUp();

		// Spy on logging so any DB errors or commits don’t blow up
		Log::spy();

		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = User::factory()->create(['type' => 'Employee']);

		// common lookup data
		$this->branch  = Branch::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->goalType = GoalType::factory()->create(['created_by' => $this->company->creatorId()]);

		// link an Employee record for the employee user
		Employee::factory()->create([
			'user_id'    => $this->employee->id,
			'branch_id'  => $this->branch->id,
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the income summary report.
	 **/
	public function guests_are_redirected_from_income_summary()
	{
		$response = $this->get(route('report.income_summary'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “income report” permission are redirected when accessing income summary.
	 **/
	public function users_without_permission_are_redirected_from_income_summary()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.income_summary'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The income summary view loads correctly for permitted users and includes
	 ** summed revenue and invoice data in the correct monthly slot.
	 **/
	public function income_summary_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income report');

		// Seed one category
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => 1,
		]);

		// One revenue in March
		Revenue::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $category->id,
			'amount'      => 150,
			'date'        => '2023-03-15',
		]);

		// One invoice in March
		Invoice::factory()->create([
			'created_by'  => $user?->creatorId(),
			'status'      => 1,
			'category_id' => $category->id,
			'send_date'   => '2023-03-20',
		]);

		$response = $this->get(route('report.income_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.income_summary')
			->assertViewHasAll([
				'monthList', 'yearList', 'currentYear',
				'incomeArr', 'invoiceArr', 'chartIncome',
				'account', 'customer', 'category',
			]);

		// pull individual view data by key
		$currentYear = $response->viewData('currentYear');
		$chartIncome = $response->viewData('chartIncome');

		// currentYear is passed through
		$this->assertEquals('2023', $currentYear);

		// Our single-March revenue/invoice should show up:
		// chartIncome is zero-based: index 2 = March
		$this->assertGreaterThan(
			0,
			$chartIncome[2],
			'Expected the March column in chartIncome to be > 0'
		);
	}


	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the income vs expense summary report.
	 **/
	public function guests_are_redirected_from_income_vs_expense_summary()
	{
		$response = $this->get(route('report.income_vs_expense_summary'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “income vs expense report” permission are redirected when accessing income vs expense summary.
	 **/
	public function users_without_permission_are_redirected_from_income_vs_expense_summary()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.income_vs_expense_summary'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The income vs expense summary view loads correctly for permitted users and includes
	 ** profit calculated as (revenue + invoice) − (payment + bill) for each month.
	 **/
	public function income_vs_expense_summary_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income vs expense report');

		// Seed category and vendor (for filters)
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => 1,
		]);
		$vendor = Vendor::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		// One revenue and one invoice in March 2023
		Revenue::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $category->id,
			'amount'      => 100,
			'date'        => '2023-03-10',
		]);
		Invoice::factory()->create([
			'created_by'  => $user?->creatorId(),
			'status'      => 1,
			'category_id' => $category->id,
			'send_date'   => '2023-03-12',
		]);

		// One payment and one bill in March 2023
		Payment::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $category->id,
			'vendor_id'   => $vendor->id,
			'amount'      =>  30,
			'date'        => '2023-03-08',
		]);
		Bill::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $category->id,
			'vendor_id'   => $vendor->id,
			'status'      => 1,
			'send_date'   => '2023-03-15',
		]);

		$response = $this->get(route('report.income_vs_expense_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.income_vs_expense_summary')
			->assertViewHasAll([
				'monthList', 'yearList', 'currentYear',
				'paymentTotal', 'billTotal', 'revenueTotal',
				'invoiceTotal', 'profit',
				'account', 'vendor', 'customer', 'category',
			]);

		// Verify currentYear
		$this->assertEquals('2023', $response->viewData('currentYear'));

		// Profit for March: (100 + invoice) - (30 + bill) > 0
		$profit = $response->viewData('profit');
		$this->assertGreaterThan(
			0,
			$profit[3],
			'Expected the March column in profit to be > 0'
		);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the tax summary report.
	 **/
	public function guests_are_redirected_from_tax_summary()
	{
		$response = $this->get(route('report.tax_summary'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “tax report” permission are redirected when accessing tax summary.
	 **/
	public function users_without_permission_are_redirected_from_tax_summary()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.tax_summary'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The tax summary view loads correctly for permitted users and shows aggregated
	 ** income and expense tax amounts per month per tax type.
	 **/
	public function tax_summary_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('tax report');

		// Create a tax entry
		$tax = Tax::factory()->create(['created_by' => $user?->creatorId()]);

		// Create an invoice and attach a product with this tax in May 2023
		$invoice = Invoice::factory()->create([
			'created_by' => $user?->creatorId(),
			'status'     => 1,
			'send_date'  => '2023-05-10',
		]);
		InvoiceProduct::factory()->create([
			'invoice_id' => $invoice->id,
			'product_id' => null,
			'price'      => 200,
			'quantity'   => 1,
			'tax'        => $tax->id, // assume factory casts to CSV internally
			'created_at' => '2023-05-10',
		]);

		// Create a bill and attach a product with this tax in May 2023
		$bill = Bill::factory()->create([
			'created_by' => $user?->creatorId(),
			'status'     => 1,
			'send_date'  => '2023-05-15',
		]);
		BillProduct::factory()->create([
			'bill_id'    => $bill->id,
			'product_id' => null,
			'price'      => 100,
			'quantity'   => 1,
			'tax'        => $tax->id,
			'created_at' => '2023-05-15',
		]);

		$response = $this->get(route('report.tax_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.tax_summary')
			->assertViewHasAll([
				'monthList', 'yearList', 'taxList',
				'incomes', 'expenses', 'filter',
			]);

		// Our Tax->name should appear
		$this->assertArrayHasKey($tax->name, $response->viewData('incomes'));
		$this->assertArrayHasKey($tax->name, $response->viewData('expenses'));

		// May is month 5 → check non-zero for that month
		$incomes  = $response->viewData('incomes')[$tax->name];
		$expenses = $response->viewData('expenses')[$tax->name];
		$this->assertGreaterThan(0, $incomes[5], "Expected income tax for May to be > 0");
		$this->assertGreaterThan(0, $expenses[5], "Expected expense tax for May to be > 0");
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the invoice report.
	 **/
	public function guests_are_redirected_from_invoice_report()
	{
		$response = $this->get(route('report.invoice_report'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “invoice report” permission are redirected when accessing invoice summary.
	 **/
	public function users_without_permission_are_redirected_from_invoice_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.invoice_report'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The invoice summary view loads correctly for permitted users and shows totals.
	 **/
	public function invoice_summary_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('invoice report');

		// Create one invoice with total and due
		$invoice = Invoice::factory()->create([
			'created_by' => $user?->creatorId(),
			'status'     => 1,
			'send_date'  => '2023-06-05',
		]);

		// Assume getTotal() and getDue() yield values; for simplicity no payments => due = total
		$response = $this->get(route('report.invoice_report', [
			'start_month' => '2023-01',
			'end_month'   => '2023-12',
		]));
		$response->assertStatus(200)
			->assertViewIs('report.invoice_report')
			->assertViewHasAll([
				'invoices', 'customer', 'status',
				'totInv', 'totDue', 'paid',
				'invoiceTotal', 'monthList', 'filter',
			]);

		// Totals should reflect at least one invoice
		$this->assertGreaterThan(
			0,
			$response->viewData('totInv'),
			'Expected total invoices sum to be > 0'
		);
	}


	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the bill summary report.
	 **/
	public function guests_are_redirected_from_bill_summary()
	{
		$response = $this->get(route('report.bill_report'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “bill report” permission are redirected when accessing bill summary.
	 **/
	public function users_without_permission_are_redirected_from_bill_summary()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.bill_report'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The bill summary view loads correctly for permitted users and shows totals.
	 **/
	public function bill_summary_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('bill report');

		// create category and vendor
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => 2,
		]);
		$vendor = Vendor::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		// create a bill with one product in July 2023
		$bill = Bill::factory()->create([
			'created_by' => $user?->creatorId(),
			'vendor_id'  => $vendor->id,
			'category_id' => $category->id,
			'status'     => 1,
			'send_date'  => '2023-07-12',
		]);
		BillProduct::factory()->create([
			'bill_id'    => $bill->id,
			'product_id' => null,
			'price'      => 120,
			'quantity'   => 1,
			'tax'        => 0,
			'created_at' => '2023-07-12',
		]);

		$response = $this->get(route('report.bill_report', ['start_month' => '2023-01', 'end_month' => '2023-12']));
		$response->assertStatus(200)
			->assertViewIs('report.bill_report')
			->assertViewHasAll([
				'bills', 'vendor', 'status',
				'tot', 'due', 'paid',
				'billTotal', 'monthList', 'filter',
			]);

		$tot  = $response->viewData('tot');
		$due  = $response->viewData('due');
		$paid = $response->viewData('paid');
		$billTotal = $response->viewData('billTotal');

		$this->assertEquals(120, $tot, 'Total should equal our product total');
		$this->assertEquals(120, $due, 'Due equals total when no payments');
		$this->assertEquals(0,   $paid, 'Paid is zero without payments');
		// July index 7 => zero-based index 6
		$this->assertGreaterThan(0, $billTotal[6], 'Expected a non-zero July entry');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the account statement report.
	 **/
	public function guests_are_redirected_from_account_statement()
	{
		$response = $this->get(route('report.statement_report'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “statement report” permission are redirected when accessing account statement.
	 **/
	public function users_without_permission_are_redirected_from_account_statement()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.statement_report'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The account statement default view loads correctly (revenue) for permitted users and shows revenue entries.
	 **/
	public function account_statement_shows_revenue_data_by_default()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('statement report');

		// create a bank account and a revenue in August 2023
		$account = BankAccount::factory()->create([
			'created_by' => $user?->creatorId(),
		]);
		Revenue::factory()->create([
			'created_by' => $user?->creatorId(),
			'account_id' => $account->id,
			'amount'     => 300,
			'date'       => '2023-08-20',
		]);

		$response = $this->get(route('report.statement_report', [
			'start_month' => '2023-01', 'end_month' => '2023-12'
		]));
		$response->assertStatus(200)
			->assertViewIs('report.statement_report')
			->assertViewHasAll([
				'reportData', 'account', 'types', 'filter'
			]);

		$reportData = $response->viewData('reportData');
		$revenues  = $reportData['revenues'];

		$this->assertNotEmpty($revenues, 'Expected at least one revenue entry');
	}

	/**
	 ** @test
	 **
	 ** The Income vs Expense Summary view shows correct profit calculation per month.
	 **/
	public function income_vs_expense_summary_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income vs expense report');

		// Seed one revenue in May 2023
		Revenue::factory()->create([
			'created_by' => $user?->creatorId(),
			'amount'     => 200,
			'date'       => '2023-05-10',
		]);

		// Seed one payment in May 2023
		Payment::factory()->create([
			'created_by'  => $user?->creatorId(),
			'amount'      => 50,
			'date'        => '2023-05-12',
		]);

		$response = $this->get(route('report.income_vs_expense_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.income_vs_expense_summary')
			->assertViewHasAll([
				'paymentTotal', 'billTotal', 'revenueTotal', 'invoiceTotal', 'profit',
				'account', 'vendor', 'customer', 'category',
			]);

		// Profit for May (month 5) = 200 (revenue) + 0 (invoice) - (50 (payment) + 0 (bill)) = 150
		$profit = $response->viewData('profit');
		$this->assertEquals(
			150,
			$profit[5],
			'Expected profit for May to be 150'
		);
	}

	/**
	 ** @test
	 **
	 ** The Tax Summary view renders with the list of taxes and chart data placeholders.
	 **/
	public function tax_summary_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('tax report');

		// Seed two different taxes
		Tax::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'VAT',
		]);
		Tax::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'GST',
		]);

		$response = $this->get(route('report.tax_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.tax_summary')
			->assertViewHasAll([
				'monthList', 'yearList', 'taxList', 'incomes', 'expenses',
			]);

		$taxList = $response->viewData('taxList')->pluck('name')->all();
		$this->assertContains('VAT', $taxList);
		$this->assertContains('GST', $taxList);
	}


	/**
	 ** @test
	 **
	 ** Guests should be redirected when attempting to view the Invoice Summary.
	 **/
	public function guests_are_redirected_from_invoice_summary()
	{
		$response = $this->get(route('report.invoice_report'));
		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the 'invoice report' permission are denied access.
	 **/
	public function users_without_permission_are_redirected_from_invoice_summary()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.invoice_report'));
		$response->assertRedirect();
	}
	/**
	 ** @test
	 **
	 ** Authenticated users with the 'statement report' permission
	 ** should see the account statement view with filtered data.
	 **/
	public function account_statement_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('statement report');

		// seed one bank account + revenue + payment in August
		$acct = BankAccount::factory()->create([
			'created_by' => $user?->creatorId(),
		]);
		Revenue::factory()->create([
			'created_by' => $user?->creatorId(),
			'account_id' => $acct->id,
			'amount'     => 300,
			'date'       => '2023-08-05',
		]);
		Payment::factory()->create([
			'created_by' => $user?->creatorId(),
			'account_id' => $acct->id,
			'amount'     => 150,
			'date'       => '2023-08-06',
		]);

		$response = $this->get(route('report.statement_report', [
			'type'        => 'revenue',
			'start_month' => '2023-08',
			'end_month'   => '2023-08',
		]));

		$response->assertStatus(200)
			->assertViewIs('report.statement_report')
			->assertViewHasAll(['reportData', 'account', 'types', 'filter']);

		$reportData = $response->viewData('reportData');
		$this->assertNotEmpty($reportData['revenues'], 'Expected at least one revenue record');
	}

	/**
	 ** @test
	 **
	 ** Users with 'bill report' permission should see the balance sheet view
	 ** with a filter and a nested chartAccounts array (default and horizontal).
	 **/
	public function balance_sheet_shows_default_and_horizontal_views()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('bill report');

		// seed one account type, subtype, and account
		$type = ChartOfAccountType::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Assets'
		]);
		$sub = ChartOfAccountSubType::factory()->create(['type' => $type->id]);
		ChartOfAccount::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => $type->id,
			'sub_type'   => $sub->id,
		]);

		// default (vertical) view
		$resp1 = $this->get(route('report.balance_sheet'));
		$resp1->assertStatus(200)
			->assertViewIs('report.balance_sheet')
			->assertViewHasAll(['filter', 'chartAccounts']);

		// horizontal view
		$resp2 = $this->get(route('report.balance_sheet', ['view' => 'horizontal']));
		$resp2->assertStatus(200)
			->assertViewIs('report.balance_sheet_horizontal')
			->assertViewHasAll(['filter', 'chartAccounts']);
	}

	/**
	 ** @test
	 **
	 ** Users with 'ledger report' permission should see the ledger summary
	 ** view with filter, items and accounts lists.
	 **/
	public function ledger_summary_shows_view_with_items_and_accounts()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('ledger report');

		// seed some chart accounts
		$type = ChartOfAccountType::factory()->create(['created_by' => $user?->creatorId()]);
		$sub = ChartOfAccountSubType::factory()->create(['type' => $type->id]);
		$acct = ChartOfAccount::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => $type->id,
			'sub_type'   => $sub->id,
		]);

		$resp = $this->get(route('report.ledger_summary'));
		$resp->assertStatus(200)
			->assertViewIs('report.ledger_summary')
			->assertViewHasAll(['filter', 'items', 'accounts']);
	}

	/**
	 ** @test
	 **
	 ** Users with 'trial balance report' permission should see the trial balance
	 ** view with a filter and totalAccounts array.
	 **/
	public function trial_balance_summary_shows_view_with_totals()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('trial balance report');

		// seed at least one account type
		ChartOfAccountType::factory()->create(['created_by' => $user?->creatorId()]);

		$resp = $this->get(route('report.trial_balance'));
		$resp->assertStatus(200)
			->assertViewIs('report.trial_balance')
			->assertViewHasAll(['filter', 'totalAccounts']);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage report' permission should see the leave report
	 ** with branch/department selects and a leaves array plus totals.
	 **/
	public function leave_report_shows_view_with_leave_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage report');

		// seed branch, department, employee, leave type & a leave record
		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$dept  = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$emp   = Employee::factory()->create([
			'created_by'   => $user?->creatorId(),
			'branch_id'    => $branch->id,
			'department_id' => $dept->id,
		]);
		$lt    = LeaveType::factory()->create(['created_by' => $user?->creatorId()]);
		Leave::factory()->create([
			'employee_id'   => $emp->id,
			'leave_type_id' => $lt->id,
			'status'        => 'Approved',
			'applied_on'    => now()->toDateString(),
		]);

		$resp = $this->get(route('report.leave'));
		$resp->assertStatus(200)
			->assertViewIs('report.leave')
			->assertViewHasAll(['department', 'branch', 'leaves', 'filterYear', 'filter']);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage report' permission should see the detailed leave listing
	 ** for a given employee, status, period type, month/year.
	 **/
	public function employee_leave_report_shows_view_with_specific_leaves()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage report');

		$emp = Employee::factory()->create(['created_by' => $user?->creatorId()]);
		Leave::factory()->count(2)->create([
			'employee_id' => $emp->id,
			'status'      => 'Pending',
			'applied_on'  => '2023-09-10',
		]);

		$resp = $this->get(route('report.employee_leave', [
			'employee_id' => $emp->id,
			'status'      => 'Pending',
			'type'        => 'monthly',
			'month'       => '2023-09',
			'year'        => 2023
		]));
		$resp->assertStatus(200)
			->assertViewIs('report.leaveShow')
			->assertViewHasAll(['leaves', 'leaveData']);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage report' permission should see the monthly attendance
	 ** report with an attendance matrix and summary data.
	 **/
	public function monthly_attendance_shows_view_with_attendance_matrix()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage report');

		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$dept  = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$emp   = Employee::factory()->create([
			'created_by'    => $user?->creatorId(),
			'branch_id'     => $branch->id,
			'department_id' => $dept->id,
		]);

		// mark one day present and one day leave
		EmployeeAttendance::factory()->create([
			'employee_id' => $emp->id,
			'date'        => now()->toDateString(),
			'status'      => 'Present',
			'overtime'    => '00:30:00',
			'early_leaving' => '00:10:00',
			'late'        => '00:05:00',
		]);
		EmployeeAttendance::factory()->create([
			'employee_id' => $emp->id,
			'date'        => now()->subDay()->toDateString(),
			'status'      => 'Leave',
		]);

		$resp = $this->get(route('report.monthly_attendance', ['month' => now()->format('Y-m')]));
		$resp->assertStatus(200)
			->assertViewIs('report.monthlyAttendance')
			->assertViewHasAll(['employeesAttendance', 'branch', 'department', 'dates', 'data']);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage report' permission should see the payroll report
	 ** with payslips, filter data, branch and department lists.
	 **/
	public function payroll_report_shows_view_with_payslips_and_filters()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage report');

		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$dept  = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$emp   = Employee::factory()->create([
			'created_by'    => $user?->creatorId(),
			'branch_id'     => $branch->id,
			'department_id' => $dept->id,
		]);

		Payslip::factory()->create([
			'created_by'   => $user?->creatorId(),
			'employee_id'  => $emp->id,
			'salary_month' => now()->format('Y-m'),
			'basic_salary' => 1000,
			'net_payble'   => 900,
			'allowance'    => json_encode([]),
			'commission'   => json_encode([]),
			'loan'         => json_encode([]),
			'saturation_deduction' => json_encode([]),
			'other_payment' => json_encode([]),
			'overtime'     => json_encode([]),
		]);

		$resp = $this->get(route('report.payroll', ['month' => now()->format('Y-m')]));
		$resp->assertStatus(200)
			->assertViewIs('report.payroll')
			->assertViewHasAll(['payslips', 'filterData', 'branch', 'department', 'filterYear']);
	}

	/**
	 ** @test
	 **
	 ** getPayrollDepartment should require authentication and then
	 ** return a JSON map of department names → IDs filtered by branch_id.
	 **/
	public function get_payroll_department_json_endpoint()
	{
		// as guest → redirect to login
		$resp = $this->getJson(route('report.get_payroll_department', ['branch_id' => 1]));
		$resp->assertRedirect();

		// as user
		$user = User::factory()->create();
		$this->actingAs($user);

		// seed two branches with departments
		$b1 = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$b2 = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$d1 = Department::factory()->create([
			'created_by' => $user?->creatorId(),
			'branch_id'  => $b1->id,
		]);
		$d2 = Department::factory()->create([
			'created_by' => $user?->creatorId(),
			'branch_id'  => $b2->id,
		]);

		// filter by branch 1
		$resp = $this->getJson(route('report.get_payroll_department', ['branch_id' => $b1->id]));
		$resp->assertOk()
			->assertJson([
				$d1->id => $d1->name,
			])
			->assertJsonMissing([
				$d2->id => $d2->name,
			]);

		// branch_id = 0 → all departments
		$respAll = $this->getJson(route('report.get_payroll_department', ['branch_id' => 0]));
		$respAll->assertOk()
			->assertJsonFragment([$d1->id => $d1->name])
			->assertJsonFragment([$d2->id => $d2->name]);
	}

	/**
	 ** @test
	 **
	 ** getPayrollEmployee should require authentication and then
	 ** return a JSON map of employee names → IDs filtered by department_id.
	 **/
	public function get_payroll_employee_json_endpoint()
	{
		$resp = $this->getJson(route('report.get_payroll_employee', ['department_id' => 1]));
		$resp->assertRedirect();

		$user = User::factory()->create();
		$this->actingAs($user);

		$deptA = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$deptB = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$e1 = Employee::factory()->create([
			'created_by'    => $user?->creatorId(),
			'department_id' => $deptA->id,
		]);
		$e2 = Employee::factory()->create([
			'created_by'    => $user?->creatorId(),
			'department_id' => $deptB->id,
		]);

		// filter by deptA
		$resp = $this->getJson(route('report.get_payroll_employee', ['department_id' => $deptA->id]));
		$resp->assertOk()
			->assertJson([
				$e1->id => $e1->name,
			])
			->assertJsonMissing([
				$e2->id => $e2->name,
			]);

		// no department_id → all
		$respAll = $this->getJson(route('report.get_payroll_employee'));
		$respAll->assertOk()
			->assertJsonFragment([$e1->id => $e1->name])
			->assertJsonFragment([$e2->id => $e2->name]);
	}

	/**
	 ** @test
	 **
	 ** getDepartment should validate branch_id, then return JSON departments
	 ** belonging to that branch.
	 **/
	public function get_department_json_endpoint()
	{
		$resp = $this->getJson(route('report.get_department'));
		$resp->assertStatus(422)
			->assertJsonValidationErrors('branch_id');

		$user = User::factory()->create();
		$this->actingAs($user);

		$bX = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$dep1 = Department::factory()->create([
			'created_by' => $user?->creatorId(),
			'branch_id'  => $bX->id,
		]);
		$dep2 = Department::factory()->create([
			'created_by' => $user?->creatorId(),
			'branch_id'  => $bX->id,
		]);

		$resp = $this->getJson(route('report.get_department', ['branch_id' => $bX->id]));
		$resp->assertOk()
			->assertJson([
				$dep1->id => $dep1->name,
				$dep2->id => $dep2->name,
			]);
	}

	/**
	 ** @test
	 **
	 ** getEmployee should accept an optional department_id and then
	 ** return all matching employees in JSON.
	 **/
	public function get_employee_json_endpoint()
	{
		$resp = $this->getJson(route('report.get_employee'));
		$resp->assertStatus(422) // because department_id must be integer if present
			->assertJsonValidationErrors('department_id');

		$user = User::factory()->create();
		$this->actingAs($user);

		$deptA = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$deptB = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$empA1 = Employee::factory()->create([
			'created_by'    => $user?->creatorId(),
			'department_id' => $deptA->id,
		]);
		$empB1 = Employee::factory()->create([
			'created_by'    => $user?->creatorId(),
			'department_id' => $deptB->id,
		]);

		// no filter → both
		$respAll = $this->getJson(route('report.get_employee', ['department_id' => 0]));
		$respAll->assertOk()
			->assertJsonFragment([$empA1->id => $empA1->name])
			->assertJsonFragment([$empB1->id => $empB1->name]);

		// filter by deptA
		$resp = $this->getJson(route('report.get_employee', ['department_id' => $deptA->id]));
		$resp->assertOk()
			->assertJson([
				$empA1->id => $empA1->name,
			])
			->assertJsonMissing([
				$empB1->id => $empB1->name,
			]);
	}

	/**
	 ** @test
	 **
	 ** exportCsv should stream a CSV file of attendance for the given
	 ** month, branch and department, with proper headers and employee rows.
	 **/
	public function export_csv_streams_attendance_csv()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$dept  = Department::factory()->create(['created_by' => $user?->creatorId()]);
		$emp   = Employee::factory()->create([
			'created_by'    => $user?->creatorId(),
			'branch_id'     => $branch->id,
			'department_id' => $dept->id,
		]);

		// mark two days: one present, one leave
		EmployeeAttendance::factory()->create([
			'employee_id' => $emp->id,
			'date'        => now()->toDateString(),
			'status'      => 'Present',
		]);
		EmployeeAttendance::factory()->create([
			'employee_id' => $emp->id,
			'date'        => now()->subDay()->toDateString(),
			'status'      => 'Leave',
		]);

		$month = now()->format('Y-m');
		$url = route('report.export_csv', [$month, $branch->id, $dept->id]);
		$resp = $this->get($url);

		$resp->assertStatus(200)
			->assertHeader('Content-type', 'text/csv')
			->assertHeaderContains('Content-Disposition', 'attachment; filename=');

		$csv = $resp->getContent();
		// header row should start with "employee"
		$this->assertStringStartsWith("employee", trim(explode("\n", $csv)[0]));
		// and our employee name appears somewhere
		$this->assertStringContainsString($emp->name, $csv);
	}

	/**
	 ** @test
	 **
	 ** incomeVsExpenseSummary should render its view with combined
	 ** income and expense arrays, grouped by month and including profit.
	 **/
	public function income_vs_expense_summary_shows_view_with_expected_data()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('income vs expense report');
		$this->actingAs($user);

		$cat = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => 1,
		]);
		$ven = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => 2,
		]);

		// One revenue and one invoice in June 2023
		Revenue::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $cat->id,
			'amount'      => 500,
			'date'        => '2023-06-10',
		]);
		Invoice::factory()->create([
			'created_by'  => $user?->creatorId(),
			'status'      => 1,
			'category_id' => $cat->id,
			'send_date'   => '2023-06-15',
		]);

		// One payment and one bill in June 2023
		Payment::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $ven->id,
			'vendor_id'   => $ven->created_by,
			'amount'      => 300,
			'date'        => '2023-06-05',
		]);
		Bill::factory()->create([
			'created_by'  => $user?->creatorId(),
			'status'      => 1,
			'category_id' => $ven->id,
			'send_date'   => '2023-06-20',
		]);

		$response = $this->get(route('report.income_vs_expense_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.income_vs_expense_summary')
			->assertViewHasAll([
				'paymentTotal',
				'billTotal',
				'revenueTotal',
				'invoiceTotal',
				'profit',
				'account',
				'vendor',
				'customer',
				'category',
			]);

		$profit = $response->viewData('profit');
		// June is month 6 → key 6 in profit array
		$this->assertGreaterThan(0, $profit[6], 'Expected positive profit for June');
	}

	/**
	 ** @test
	 **
	 ** taxSummary should render its view with monthList, yearList,
	 ** taxList, incomes and expenses arrays.
	 **/
	public function tax_summary_shows_view_with_expected_data()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('tax report');
		$this->actingAs($user);

		// create a tax
		$tax = \App\Models\Tax::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->get(route('report.tax_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.tax_summary')
			->assertViewHasAll(['monthList', 'yearList', 'taxList', 'incomes', 'expenses']);

		$taxList = $response->viewData('taxList');
		$this->assertTrue($taxList->contains('id', $tax->id));
	}

	/**
	 ** @test
	 **
	 ** invoiceSummary should render its view with filtered invoices,
	 ** totals, paid amounts, and monthly totals.
	 **/
	public function invoice_summary_shows_view_and_filters()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('invoice report');
		$this->actingAs($user);

		$cust = \App\Models\Customer::factory()->create(['created_by' => $user?->creatorId()]);

		// one paid invoice in Feb 2023
		Invoice::factory()->create([
			'created_by' => $user?->creatorId(),
			'status'     => 1,
			'customer_id' => $cust->id,
			'issue_date' => '2023-02-01',
			'send_date'  => '2023-02-01',
		]);

		$response = $this->get(route('report.invoice_report', [
			'start_month' => '2023-02',
			'end_month'   => '2023-02',
			'customer'    => $cust->id,
		]));
		$response->assertStatus(200)
			->assertViewIs('report.invoice_report')
			->assertViewHasAll([
				'invoices', 'customer', 'status',
				'totInv', 'totDue', 'paid',
				'invoiceTotal', 'monthList', 'filter',
			]);

		$filter = $response->viewData('filter');
		$this->assertStringContainsString('Feb-2023', $filter['startDateRange']);
	}

	/**
	 ** @test
	 **
	 ** billSummary should render its view with filtered bills,
	 ** totals, paid amounts, and monthly totals.
	 **/
	public function bill_summary_shows_view_and_filters()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('bill report');
		$this->actingAs($user);

		$ven = Vendor::factory()->create(['created_by' => $user?->creatorId()]);

		Bill::factory()->create([
			'created_by' => $user?->creatorId(),
			'status'     => 1,
			'vendor_id'  => $ven->id,
			'bill_date'  => '2023-03-01',
			'send_date'  => '2023-03-01',
		]);

		$response = $this->get(route('report.bill_report', [
			'start_month' => '2023-03',
			'end_month'   => '2023-03',
			'vendor'      => $ven->id,
		]));
		$response->assertStatus(200)
			->assertViewIs('report.bill_report')
			->assertViewHasAll([
				'bills', 'vendor', 'status',
				'tot', 'due', 'paid',
				'billTotal', 'monthList', 'filter',
			]);

		$filter = $response->viewData('filter');
		$this->assertStringContainsString('Mar-2023', $filter['startDateRange']);
	}

	/**
	 ** @test
	 **
	 ** accountStatement should render its view defaulting to revenue,
	 ** and switch to payment when requested, including correct filter.
	 **/
	public function account_statement_shows_revenue_and_can_switch_to_payment()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('statement report');
		$this->actingAs($user);

		$acct = \App\Models\BankAccount::factory()->create(['created_by' => $user?->creatorId()]);

		// one revenue and one payment
		Revenue::factory()->create([
			'created_by' => $user?->creatorId(),
			'account_id' => $acct->id,
			'amount'     => 100,
			'date'       => now()->toDateString(),
		]);
		Payment::factory()->create([
			'created_by' => $user?->creatorId(),
			'account_id' => $acct->id,
			'amount'     => 50,
			'date'       => now()->toDateString(),
		]);

		// default (revenue)
		$respRev = $this->get(route('report.statement_report'));
		$respRev->assertStatus(200)
			->assertViewHas('reportData', function ($d) {
				return isset($d['revenues']);
			});

		// switch to payment
		$respPay = $this->get(route('report.statement_report', [
			'type'    => 'payment',
			'account' => $acct->id,
		]));
		$respPay->assertStatus(200)
			->assertViewHas('reportData', function ($d) {
				return isset($d['payments']);
			});
	}

	/**
	 ** @test
	 **
	 ** productStock should render its view with all StockReport records.
	 **/
	public function product_stock_shows_view_with_reports()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('stock report');
		$this->actingAs($user);

		$rep1 = StockReport::factory()->create(['created_by' => $user?->creatorId()]);
		$rep2 = StockReport::factory()->create(['created_by' => $user?->creatorId()]);

		$resp = $this->get(route('report.stock_report'));
		$resp->assertStatus(200)
			->assertViewIs('report.product_stock_report')
			->assertViewHas('stocks', function ($s) use ($rep1, $rep2) {
				return $s->pluck('id')->contains($rep1->id)
					&& $s->pluck('id')->contains($rep2->id);
			});
	}

	/**
	 ** @test
	 **
	 ** warehouseReport should render its view with total counts and
	 ** per-warehouse product counts.
	 **/
	public function warehouse_report_shows_totals_and_counts()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage pos');
		$this->actingAs($user);

		$w1 = Warehouse::factory()->create(['created_by' => $user?->creatorId()]);
		$w2 = Warehouse::factory()->create(['created_by' => $user?->creatorId()]);
		WarehouseProduct::factory()->count(2)->create([
			'created_by'   => $user?->creatorId(),
			'warehouse_id' => $w1->id,
		]);
		WarehouseProduct::factory()->count(3)->create([
			'created_by'   => $user?->creatorId(),
			'warehouse_id' => $w2->id,
		]);

		$resp = $this->get(route('report.warehouse'));
		$resp->assertStatus(200)
			->assertViewIs('report.warehouse')
			->assertViewHasAll([
				'warehouse',
				'totalWarehouse',
				'totalProduct',
				'warehousename',
				'warehouseProductData',
			]);

		$this->assertEquals(2, $resp->viewData('totalWarehouse'));
		$this->assertEquals(5, $resp->viewData('totalProduct'));
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the expense summary report.
	 **/
	public function guests_are_redirected_from_expense_summary()
	{
		$response = $this->get(route('report.expense_summary'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “expense report” permission
	 ** are redirected when accessing expense summary.
	 **/
	public function users_without_permission_are_redirected_from_expense_summary()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.expense_summary'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The expense summary view loads correctly for permitted users and includes
	 ** summed payment and bill data in the correct monthly slot.
	 **/
	public function expense_summary_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('expense report');

		// Seed one expense‐category and one vendor
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => 2,
		]);
		$vendor = Vendor::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		// One payment in April 2023
		Payment::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $category->id,
			'vendor_id'   => $vendor->id,
			'amount'      => 200,
			'date'        => '2023-04-05',
		]);

		// One bill in April 2023 (status != 0)
		Bill::factory()->create([
			'created_by'  => $user?->creatorId(),
			'category_id' => $category->id,
			'vendor_id'   => $vendor->id,
			'status'      => 1,
			'send_date'   => '2023-04-10',
		]);

		$response = $this->get(route('report.expense_summary', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.expense_summary')
			->assertViewHasAll([
				'monthList', 'yearList', 'currentYear',
				'expenseArr', 'billArr', 'chartExpense',
				'account', 'vendor', 'category',
			]);

		$currentYear  = $response->viewData('currentYear');
		$chartExpense = $response->viewData('chartExpense');

		// currentYear is passed through
		$this->assertEquals('2023', $currentYear);

		// April is month #4 → index 3 in zero-based chartExpense
		$this->assertGreaterThan(
			0,
			$chartExpense[3],
			'Expected the April column in chartExpense to be > 0'
		);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the daily purchase report.
	 **/
	public function guests_are_redirected_from_purchase_daily_report()
	{
		$response = $this->get(route('report.purchase_daily'));
		$response->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “manage pos” permission
	 ** are redirected when accessing the daily purchase report.
	 **/
	public function users_without_permission_are_redirected_from_purchase_daily_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('report.purchase_daily'));
		$response->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The daily purchase report view loads correctly for permitted users
	 ** and shows the sum of purchases grouped by date.
	 **/
	public function purchase_daily_report_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		// Seed two purchases on different dates
		Purchase::factory()->create([
			'created_by'   => $user?->creatorId(),
			'purchase_date' => '2023-05-01',
			// assume getTotal() > 0 by default
		]);
		Purchase::factory()->create([
			'created_by'   => $user?->creatorId(),
			'purchase_date' => '2023-05-02',
		]);

		$response = $this->get(route('report.purchase_daily', [
			'start_date' => '2023-05-01',
			'end_date'   => '2023-05-02',
		]));

		$response->assertStatus(200)
			->assertViewIs('report.daily_purchase')
			->assertViewHasAll([
				'warehouses', 'vendors',
				'arrDuration', 'data', 'filter',
			]);

		$data = $response->viewData('data');
		// we created two purchases, so the sum across those days must be > 0
		$this->assertGreaterThan(0, array_sum($data));
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the monthly purchase report.
	 **/
	public function guests_are_redirected_from_purchase_monthly_report()
	{
		$resp = $this->get(route('report.purchase_monthly'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “manage pos” permission
	 ** are redirected when accessing the monthly purchase report.
	 **/
	public function users_without_permission_are_redirected_from_purchase_monthly_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.purchase_monthly'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The monthly purchase report view loads correctly and shows
	 ** totals per month for the given year.
	 **/
	public function purchase_monthly_report_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		// One purchase in June 2023
		Purchase::factory()->create([
			'created_by'    => $user?->creatorId(),
			'purchase_date' => '2023-06-15',
		]);

		$response = $this->get(route('report.purchase_monthly', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.monthly_purchase')
			->assertViewHasAll([
				'monthList', 'yearList',
				'warehouses', 'vendors',
				'arrDuration', 'data', 'filter',
			]);

		$data = $response->viewData('data');
		// June is the 6th month → index 5 in zero-based $data
		$this->assertGreaterThan(
			0,
			$data[5],
			'Expected June total purchases to be > 0'
		);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the daily POS report.
	 **/
	public function guests_are_redirected_from_pos_daily_report()
	{
		$resp = $this->get(route('report.pos_daily'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “manage pos” permission
	 ** are redirected when accessing the daily POS report.
	 **/
	public function users_without_permission_are_redirected_from_pos_daily_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.pos_daily'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The daily POS report view loads correctly and shows summed POS totals per day.
	 **/
	public function pos_daily_report_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		// Two POS sales on different dates
		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_date'   => '2023-07-01',
		]);
		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_date'   => '2023-07-02',
		]);

		$response = $this->get(route('report.pos_daily', [
			'start_date' => '2023-07-01',
			'end_date'   => '2023-07-02',
		]));

		$response->assertStatus(200)
			->assertViewIs('report.daily_pos')
			->assertViewHasAll([
				'warehouses', 'customers',
				'arrDuration', 'data', 'filter',
			]);

		$data = $response->viewData('data');
		$this->assertGreaterThan(0, array_sum($data));
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the monthly POS report.
	 **/
	public function guests_are_redirected_from_pos_monthly_report()
	{
		$resp = $this->get(route('report.pos_monthly'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “manage pos” permission
	 ** are redirected when accessing the monthly POS report.
	 **/
	public function users_without_permission_are_redirected_from_pos_monthly_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.pos_monthly'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The monthly POS report view loads correctly and shows
	 ** totals per month for the given year.
	 **/
	public function pos_monthly_report_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		// One POS sale in August 2023
		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_date'   => '2023-08-10',
		]);

		$response = $this->get(route('report.pos_monthly', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.monthly_pos')
			->assertViewHasAll([
				'monthList', 'yearList',
				'warehouses', 'customers',
				'arrDuration', 'data', 'filter',
			]);

		$data = $response->viewData('data');
		// August is month 8 → index 7
		$this->assertGreaterThan(
			0,
			$data[7],
			'Expected August total POS to be > 0'
		);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the POS vs Purchase summary.
	 **/
	public function guests_are_redirected_from_pos_vs_purchase_report()
	{
		$resp = $this->get(route('report.pos_vs_purchase'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “manage pos” permission
	 ** are redirected when accessing the POS vs Purchase summary.
	 **/
	public function users_without_permission_are_redirected_from_pos_vs_purchase_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.pos_vs_purchase'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The POS vs Purchase summary view loads correctly and calculates
	 ** profit = POS total − Purchase total for each month.
	 **/
	public function pos_vs_purchase_report_shows_correct_profit()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		// One POS sale and one purchase in June 2023
		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_date'   => '2023-06-10',
		]);
		Purchase::factory()->create([
			'created_by'    => $user?->creatorId(),
			'purchase_date' => '2023-06-05',
		]);

		$response = $this->get(route('report.pos_vs_purchase', ['year' => '2023']));
		$response->assertStatus(200)
			->assertViewIs('report.pos_vs_purchase')
			->assertViewHasAll([
				'posTotal', 'purchaseTotal', 'profits', 'filter',
			]);

		$profits = $response->viewData('profits');
		// June is the 6th month → index 5
		$this->assertEquals(
			number_format(
				$response->viewData('posTotal')[5]
					- $response->viewData('purchaseTotal')[5],
				2
			),
			$profits[5]
		);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Profit & Loss report.
	 **/
	public function guests_are_redirected_from_profit_loss_report()
	{
		$resp = $this->get(route('report.profit_loss'));
		$resp->assertRedirect(); // to login
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “income vs expense report” permission
	 ** are redirected when accessing the Profit & Loss report.
	 **/
	public function users_without_permission_are_redirected_from_profit_loss_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.profit_loss'));
		$resp->assertRedirect(); // guard denies
	}

	/**
	 ** @test
	 **
	 ** The Profit & Loss report view loads correctly for permitted users
	 ** and provides a chartAccounts array.
	 **/
	public function profit_loss_report_shows_view_with_chart_accounts()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income vs expense report');

		$resp = $this->get(route('report.profit_loss'));
		$resp->assertStatus(200)
			->assertViewIs('report.profit_loss')
			->assertViewHas('chartAccounts');
	}

	/**
	 ** @test
	 **
	 ** The Profit & Loss report can render the horizontal layout.
	 **/
	public function profit_loss_report_shows_horizontal_view()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income vs expense report');

		$resp = $this->get(route('report.profit_loss', ['view' => 'horizontal']));
		$resp->assertStatus(200)
			->assertViewIs('report.profit_loss_horizontal');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected from the Monthly Cashflow report.
	 **/
	public function guests_are_redirected_from_monthly_cashflow_report()
	{
		$resp = $this->get(route('report.monthly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “loss & profit report” permission
	 ** are redirected when accessing the Monthly Cashflow report.
	 **/
	public function users_without_permission_are_redirected_from_monthly_cashflow_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.monthly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Monthly Cashflow report view loads correctly for permitted users
	 ** and calculates income, expense, and net arrays per month.
	 **/
	public function monthly_cashflow_report_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('loss & profit report');

		// One revenue and one payment in March 2023
		Revenue::factory()->create([
			'created_by' => $user?->creatorId(),
			'amount'     => 200,
			'date'       => '2023-03-10',
		]);
		Payment::factory()->create([
			'created_by' => $user?->creatorId(),
			'amount'     => 50,
			'date'       => '2023-03-12',
		]);

		$resp = $this->get(route('report.monthly_cashflow', ['year' => '2023']));
		$resp->assertStatus(200)
			->assertViewIs('report.monthly_cashflow')
			->assertViewHasAll([
				'chartIncomeArr', 'chartExpenseArr', 'netProfitArray', 'filter',
			]);

		$income = $resp->viewData('chartIncomeArr');
		$expense = $resp->viewData('chartExpenseArr');
		$net    = $resp->viewData('netProfitArray');

		// March is the 3rd month → zero-based index 2
		$this->assertEquals(200,  $income[2],  'Income for March should be 200');
		$this->assertEquals(50,   $expense[2], 'Expense for March should be 50');
		$this->assertEquals(150,  $net[2],     'Net profit for March should be 150');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected from the Quarterly Cashflow report.
	 **/
	public function guests_are_redirected_from_quarterly_cashflow_report()
	{
		$resp = $this->get(route('report.quarterly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “loss & profit report” permission
	 ** are redirected when accessing the Quarterly Cashflow report.
	 **/
	public function users_without_permission_are_redirected_from_quarterly_cashflow_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.quarterly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Quarterly Cashflow report view loads correctly for permitted users
	 ** and provides all key arrays (including netProfitArray).
	 **/
	public function quarterly_cashflow_report_shows_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('loss & profit report');

		// One revenue in February, one payment in February 2023
		Revenue::factory()->create([
			'created_by' => $user?->creatorId(),
			'amount'     => 120,
			'date'       => '2023-02-05',
		]);
		Payment::factory()->create([
			'created_by' => $user?->creatorId(),
			'amount'     => 20,
			'date'       => '2023-02-07',
		]);

		$resp = $this->get(route('report.quarterly_cashflow', ['year' => '2023']));
		$resp->assertStatus(200)
			->assertViewIs('report.quarterly_cashflow')
			->assertViewHas('netProfitArray');

		$quarters = $resp->viewData('netProfitArray');
		// Feb falls in Q1 (Jan-Mar), which is index 0
		$this->assertEquals(
			100,
			$quarters[0],
			'Expected net for Q1 to be revenue 120 − expense 20 = 100'
		);
	}

	/**
	 ** @test
	 **
	 ** The POS vs Purchase report view loads correctly for permitted users
	 ** and shows profit per month.
	 **/
	public function pos_vs_purchase_report_shows_correct_profit_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		// one POS and one Purchase in August 2023
		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_date'   => '2023-08-05',
		]);
		Purchase::factory()->create([
			'created_by'    => $user?->creatorId(),
			'purchase_date' => '2023-08-05',
		]);

		$resp = $this->get(route('report.pos_vs_purchase', ['year' => '2023']));
		$resp->assertStatus(200)
			->assertViewIs('report.pos_vs_purchase')
			->assertViewHasAll([
				'posTotal', 'purchaseTotal', 'profits', 'filter',
			]);

		$profits = $resp->viewData('profits');
		// August is month 8 → zero-based index 7
		$this->assertEquals(
			$resp->viewData('posTotal')[7] - $resp->viewData('purchaseTotal')[7],
			$profits[7]
		);
	}

	/**
	 ** @test
	 **
	 ** The Lead report view loads correctly and returns JSON when
	 ** start_month is provided.
	 **/
	public function lead_report_shows_view_and_json_endpoint()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('lead report');

		// view
		$resp = $this->get(route('report.lead'));
		$resp->assertStatus(200)
			->assertViewIs('report.lead');

		// JSON data when start_month given
		$respJson = $this->getJson(route('report.lead', ['start_month' => '2023-01']));
		$respJson->assertOk()
			->assertJsonStructure(['data', 'name']);
	}

	/**
	 ** @test
	 **
	 ** The Deal report view loads correctly and returns JSON when
	 ** start_month is provided.
	 **/
	public function deal_report_shows_view_and_json_endpoint()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('deal report');

		// view
		$resp = $this->get(route('report.deal'));
		$resp->assertStatus(200)
			->assertViewIs('report.deal');

		// JSON data when start_month given
		$respJson = $this->getJson(route('report.deal', ['start_month' => '2023-01']));
		$respJson->assertOk()
			->assertJsonStructure(['data', 'name']);
	}
	/**
	 ** @test
	 **
	 ** The Profit & Loss report view loads in both vertical and horizontal layouts
	 ** for permitted users, with filter and chartAccounts data.
	 **/
	public function profit_loss_report_shows_default_and_horizontal_views()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income vs expense report');

		// seed one account type, subtype and account so chartAccounts is non-empty
		$type = ChartOfAccountType::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Income'
		]);
		$sub = ChartOfAccountSubType::factory()->create(['type' => $type->id]);
		ChartOfAccount::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => $type->id,
			'sub_type'   => $sub->id,
		]);

		// default (vertical)
		$resp1 = $this->get(route('report.profit_loss'));
		$resp1->assertStatus(200)
			->assertViewIs('report.profit_loss')
			->assertViewHasAll(['filter', 'chartAccounts']);

		// horizontal
		$resp2 = $this->get(route('report.profit_loss', ['view' => 'horizontal']));
		$resp2->assertStatus(200)
			->assertViewIs('report.profit_loss_horizontal')
			->assertViewHasAll(['filter', 'chartAccounts']);
	}
	/**
	 ** @test
	 **
	 ** Guests should be redirected when attempting to stream the attendance CSV export.
	 **/
	public function guests_are_redirected_from_export_csv()
	{
		$month = now()->format('Y-m');
		$url  = route('report.export_csv', [$month, 1, 1]);
		$resp = $this->get($url);

		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Account Statement Excel export.
	 **/
	public function guests_are_redirected_from_account_statement_export()
	{
		$resp = $this->get(route('report.export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the “statement report” permission are redirected
	 ** from the Account Statement Excel export.
	 **/
	public function users_without_permission_are_redirected_from_account_statement_export()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Account Statement Excel export returns a download response
	 ** with correct spreadsheet headers for permitted users.
	 **/
	public function account_statement_export_downloads_xlsx()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('statement report');

		$resp = $this->get(route('report.export'));

		$resp->assertOk()
			->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
			->assertHeaderContains('Content-Disposition', 'attachment; filename=');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Product Stock Excel export.
	 **/
	public function guests_are_redirected_from_stock_export()
	{
		$resp = $this->get(route('report.stock_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the “stock report” permission are redirected
	 ** from the Product Stock Excel export.
	 **/
	public function users_without_permission_are_redirected_from_stock_export()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.stock_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Product Stock Excel export returns a download response
	 ** with correct headers for permitted users.
	 **/
	public function stock_export_downloads_xlsx()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('stock report');

		$resp = $this->get(route('report.stock_export'));

		$resp->assertOk()
			->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
			->assertHeaderContains('Content-Disposition', 'attachment; filename=');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Payroll Excel export.
	 **/
	public function guests_are_redirected_from_payroll_export()
	{
		$resp = $this->get(route('report.payroll_report_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the “manage report” permission are redirected
	 ** from the Payroll Excel export.
	 **/
	public function users_without_permission_are_redirected_from_payroll_export()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.payroll_report_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Payroll Excel export returns a download response
	 ** with correct headers for permitted users.
	 **/
	public function payroll_export_downloads_xlsx()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage report');

		$resp = $this->get(route('report.payroll_report_export'));

		$resp->assertOk()
			->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
			->assertHeaderContains('Content-Disposition', 'attachment; filename=');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Leave Report Excel export.
	 **/
	public function guests_are_redirected_from_leave_export()
	{
		$resp = $this->get(route('report.leave_report_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the “manage report” permission are redirected
	 ** from the Leave Report Excel export.
	 **/
	public function users_without_permission_are_redirected_from_leave_export()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.leave_report_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Leave Report Excel export returns a download response
	 ** with correct headers for permitted users.
	 **/
	public function leave_export_downloads_xlsx()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage report');

		$resp = $this->get(route('report.leave_report_export'));

		$resp->assertOk()
			->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
			->assertHeaderContains('Content-Disposition', 'attachment; filename=');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Trial Balance Excel export.
	 **/
	public function guests_are_redirected_from_trial_balance_export()
	{
		$resp = $this->get(route('report.trial_balance_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the “trial balance report” permission are redirected
	 ** from the Trial Balance Excel export.
	 **/
	public function users_without_permission_are_redirected_from_trial_balance_export()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.trial_balance_export'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Trial Balance Excel export returns a download response
	 ** with correct headers for permitted users.
	 **/
	public function trial_balance_export_downloads_xlsx()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('trial balance report');

		$resp = $this->get(route('report.trial_balance_export'));

		$resp->assertOk()
			->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
			->assertHeaderContains('Content-Disposition', 'attachment; filename=');
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Balance Sheet print view.
	 **/
	public function guests_are_redirected_from_balance_sheet_print()
	{
		$resp = $this->get(route('report.balance_sheet_print'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the “balance sheet report” permission are redirected
	 ** from the Balance Sheet print view.
	 **/
	public function users_without_permission_are_redirected_from_balance_sheet_print()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.balance_sheet_print'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Balance Sheet print view renders in both vertical and horizontal layouts
	 ** for permitted users.
	 **/
	public function balance_sheet_print_shows_default_and_horizontal_views()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('balance sheet report');

		// seed one type/subtype/account so there's data
		$type = ChartOfAccountType::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Assets'
		]);
		$sub = ChartOfAccountSubType::factory()->create(['type' => $type->id]);
		ChartOfAccount::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => $type->id,
			'sub_type'   => $sub->id,
		]);

		// default
		$resp1 = $this->get(route('report.balance_sheet_print'));
		$resp1->assertStatus(200)
			->assertViewIs('report.balance_sheet_receipt')
			->assertViewHasAll(['filter', 'chartAccounts']);

		// horizontal
		$resp2 = $this->get(route('report.balance_sheet_print', ['view' => 'horizontal']));
		$resp2->assertStatus(200)
			->assertViewIs('report.balance_sheet_receipt_horizontal')
			->assertViewHasAll(['filter', 'chartAccounts']);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected when accessing the Trial Balance print view.
	 **/
	public function guests_are_redirected_from_trial_balance_print()
	{
		$resp = $this->get(route('report.trial_balance_print'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Users without the “trial balance report” permission are redirected
	 ** from the Trial Balance print view.
	 **/
	public function users_without_permission_are_redirected_from_trial_balance_print()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.trial_balance_print'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The Trial Balance print view renders in both vertical and horizontal layouts
	 ** for permitted users.
	 **/
	public function trial_balance_print_shows_default_and_horizontal_views()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('trial balance report');

		// seed one type & account
		$type = ChartOfAccountType::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Equity'
		]);
		$sub = ChartOfAccountSubType::factory()->create(['type' => $type->id]);
		ChartOfAccount::factory()->create([
			'created_by' => $user?->creatorId(),
			'type'       => $type->id,
			'sub_type'   => $sub->id,
		]);

		// default
		$resp1 = $this->get(route('report.trial_balance_print'));
		$resp1->assertStatus(200)
			->assertViewIs('report.trial_balance_receipt')
			->assertViewHasAll(['filter', 'totalAccounts']);

		// horizontal
		$resp2 = $this->get(route('report.trial_balance_print', ['view' => 'horizontal']));
		$resp2->assertStatus(200)
			->assertViewIs('report.trial_balance_receipt_horizontal')
			->assertViewHasAll(['filter', 'totalAccounts']);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the profit & loss report.
	 **/
	public function guests_are_redirected_from_profit_loss()
	{
		$response = $this->get(route('report.profit_loss'));
		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “income vs expense report” permission are redirected from profit & loss.
	 **/
	public function users_without_permission_are_redirected_from_profit_loss()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.profit_loss'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The profit & loss view loads correctly for permitted users and includes filter and chartAccounts.
	 **/
	public function profit_loss_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income vs expense report');

		$resp = $this->get(route('report.profit_loss'));
		$resp->assertStatus(200)
			->assertViewIs('report.profit_loss')
			->assertViewHasAll(['filter', 'chartAccounts']);
	}

	/**
	 ** @test
	 **
	 ** Profit & loss export endpoint triggers an Excel download.
	 **/
	public function profit_loss_export_downloads_excel()
	{
		Excel::fake();

		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('income vs expense report');

		$resp = $this->get(route('report.profit_loss_export'));
		$resp->assertStatus(200);

		Excel::assertDownloaded(ProfitLossExport::class);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the monthly cashflow report.
	 **/
	public function guests_are_redirected_from_monthly_cashflow()
	{
		$resp = $this->get(route('report.monthly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “loss & profit report” permission are redirected from monthly cashflow.
	 **/
	public function users_without_permission_are_redirected_from_monthly_cashflow()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.monthly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The monthly cashflow view loads correctly for permitted users and includes chart arrays.
	 **/
	public function monthly_cashflow_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('loss & profit report');

		Revenue::factory()->create([
			'created_by' => $user?->creatorId(),
			'amount'     => 100,
			'date'       => '2023-01-15',
		]);
		Payment::factory()->create([
			'created_by' => $user?->creatorId(),
			'amount'     =>  50,
			'date'       => '2023-01-10',
		]);

		$resp = $this->get(route('report.monthly_cashflow'));
		$resp->assertStatus(200)
			->assertViewIs('report.monthly_cashflow')
			->assertViewHasAll([
				'chartIncomeArr',
				'chartExpenseArr',
				'netProfitArray',
				'filter',
			]);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the quarterly cashflow report.
	 **/
	public function guests_are_redirected_from_quarterly_cashflow()
	{
		$resp = $this->get(route('report.quarterly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without the “loss & profit report” permission are redirected from quarterly cashflow.
	 **/
	public function users_without_permission_are_redirected_from_quarterly_cashflow()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.quarterly_cashflow'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** The quarterly cashflow view loads correctly for permitted users and includes all arrays.
	 **/
	public function quarterly_cashflow_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('loss & profit report');

		$resp = $this->get(route('report.quarterly_cashflow'));
		$resp->assertStatus(200)
			->assertViewIs('report.quarterly_cashflow')
			->assertViewHasAll([
				'month',
				'revenueIncomeArray',
				'invoiceIncomeArray',
				'expenseArray',
				'billExpenseArray',
				'netProfitArray',
				'monthList',
				'yearList',
				'currentYear',
				'filter',
			]);
	}

	/**
	 ** @test
	 **
	 ** The purchase daily report view loads correctly for permitted users and includes duration and data arrays.
	 **/
	public function purchase_daily_report_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		$vendor   = Vendor::factory()->create(['created_by' => $user?->creatorId()]);
		$warehouse = Warehouse::factory()->create(['created_by' => $user?->creatorId()]);
		Purchase::factory()->create([
			'created_by'   => $user?->creatorId(),
			'vendor_id'    => $vendor->id,
			'warehouse_id' => $warehouse->id,
			'purchase_date' => '2023-01-01',
		]);

		$resp = $this->get(route('report.purchase_daily', [
			'start_date' => '2023-01-01',
			'end_date'   => '2023-01-01',
		]));
		$resp->assertStatus(200)
			->assertViewIs('report.daily_purchase')
			->assertViewHasAll(['warehouses', 'vendors', 'arrDuration', 'data', 'filter']);
	}

	/**
	 ** @test
	 **
	 ** The purchase monthly report view loads correctly for permitted users and includes monthList, yearList, arrDuration and data.
	 **/
	public function purchase_monthly_report_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		Purchase::factory()->create([
			'created_by'   => $user?->creatorId(),
			'purchase_date' => '2023-02-15',
		]);

		$resp = $this->get(route('report.purchase_monthly', ['year' => '2023']));
		$resp->assertStatus(200)
			->assertViewIs('report.monthly_purchase')
			->assertViewHasAll([
				'monthList', 'yearList', 'warehouses', 'vendors', 'arrDuration', 'data', 'filter'
			]);
	}

	/**
	 ** @test
	 **
	 ** The POS daily report view loads correctly for permitted users and includes arrDuration, data and filter.
	 **/
	public function pos_daily_report_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		$customer = Customer::factory()->create(['created_by' => $user?->creatorId()]);
		warehouse::factory()->create(['created_by' => $user?->creatorId()]);
		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'customer_id' => $customer->id,
			'pos_date'   => '2023-03-01',
		]);

		$resp = $this->get(route('report.pos_daily', [
			'start_date' => '2023-03-01', 'end_date' => '2023-03-01',
		]));
		$resp->assertStatus(200)
			->assertViewIs('report.daily_pos')
			->assertViewHasAll(['warehouses', 'customers', 'arrDuration', 'data', 'filter']);
	}

	/**
	 ** @test
	 **
	 ** The POS monthly report view loads correctly for permitted users and includes monthList, yearList, arrDuration and data.
	 **/
	public function pos_monthly_report_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_date'  => '2023-04-10',
		]);

		$resp = $this->get(route('report.pos_monthly', ['year' => '2023']));
		$resp->assertStatus(200)
			->assertViewIs('report.monthly_pos')
			->assertViewHasAll(['monthList', 'yearList', 'warehouses', 'customers', 'arrDuration', 'data', 'filter']);
	}

	/**
	 ** @test
	 **
	 ** The POS vs Purchase report view loads correctly for permitted users and includes profit data.
	 **/
	public function pos_vs_purchase_report_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage pos');

		Pos::factory()->create([
			'created_by' => $user?->creatorId(),
			'pos_date'  => '2023-05-05',
		]);
		Purchase::factory()->create([
			'created_by'   => $user?->creatorId(),
			'purchase_date' => '2023-05-05',
		]);

		$resp = $this->get(route('report.pos_vs_purchase', ['year' => 2023]));
		$resp->assertStatus(200)
			->assertViewIs('report.pos_vs_purchase')
			->assertViewHasAll(['filter', 'posTotal', 'purchaseTotal', 'profits']);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the lead report.
	 **/
	public function guests_are_redirected_from_lead_report()
	{
		$resp = $this->get(route('report.lead'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without “lead report” permission are redirected from lead report.
	 **/
	public function users_without_permission_are_redirected_from_lead_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.lead'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** leadReport should return JSON when start_month is provided.
	 **/
	public function lead_report_returns_json_when_requested()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('lead report');

		$resp = $this->getJson(route('report.lead', [
			'start_month' => '2023-01', 'end_month' => '2023-12'
		]));
		$resp->assertOk()
			->assertJsonStructure(['data', 'name']);
	}

	/**
	 ** @test
	 **
	 ** leadReport should show the HTML view with chart data when no JSON params are provided.
	 **/
	public function lead_report_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('lead report');

		$resp = $this->get(route('report.lead'));
		$resp->assertStatus(200)
			->assertViewIs('report.lead')
			->assertViewHasAll([
				'deviceLabels', 'deviceData', 'srcLabels', 'srcData',
				'labels', 'data', 'filter', 'monthList',
				'userCounts', 'pipeLabels', 'pipeData'
			]);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login when accessing the deal report.
	 **/
	public function guests_are_redirected_from_deal_report()
	{
		$resp = $this->get(route('report.deal'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users without “deal report” permission are redirected from deal report.
	 **/
	public function users_without_permission_are_redirected_from_deal_report()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$resp = $this->get(route('report.deal'));
		$resp->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** dealReport should return JSON when start_month is provided.
	 **/
	public function deal_report_returns_json_when_requested()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('deal report');

		$resp = $this->getJson(route('report.deal', [
			'start_month' => '2023-01', 'end_month' => '2023-12'
		]));
		$resp->assertOk()
			->assertJsonStructure(['data', 'name']);
	}

	/**
	 ** @test
	 **
	 ** dealReport should show the HTML view with chart data when no JSON params are provided.
	 **/
	public function deal_report_shows_view_with_correct_data()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('deal report');

		$resp = $this->get(route('report.deal'));
		$resp->assertStatus(200)
			->assertViewIs('report.deal')
			->assertViewHasAll([
				'deviceLabels', 'deviceData', 'srcLabels', 'srcData',
				'userData', 'clientData', 'labels', 'data', 'filter', 'monthList'
			]);
	}
}
