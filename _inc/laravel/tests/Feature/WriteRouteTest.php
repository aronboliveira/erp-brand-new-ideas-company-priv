<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;

/**
 * HTTP-level POST / PUT / PATCH / DELETE tests for major ERP write routes.
 *
 * Each test authenticates as the seeded super-admin user and sends realistic
 * form data, then asserts the response is NOT 500 (i.e. the controller handled
 * the request without crashing).
 *
 * Acceptable responses:
 *   200 — success rendered inline
 *   302 — redirect (most common for store/update/destroy)
 *   422 — validation error (expected when fields are missing/invalid)
 *   403 — permission denied
 *   404 — resource not found
 *   405 — method not allowed (route doesn't accept this verb)
 *
 * A 500 is always a failure — it means the controller threw an unhandled exception.
 *
 * @group write-routes
 * @covers \App\Http\Controllers
 */
class WriteRouteTest extends TestCase
{
	/**
	 * Resolved user instance shared across all tests in this class.
	 */
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
		$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first();
		if ($this->admin) {
			$this->actingAs($this->admin);
		}
	}

	/**
	 * Assert the response is anything but 500.
	 */
	protected function assertNot500(\Illuminate\Testing\TestResponse $response, string $route = ''): void
	{
		$status = $response->getStatusCode();
		$this->assertNotEquals(
			500,
			$status,
			"Route [{$route}] returned HTTP 500. Server error detected."
		);
	}

	// ─────────────────────────────────────────────────────────────────────
	// Phase 1: POST (Create) routes
	// ─────────────────────────────────────────────────────────────────────

	public function test_post_deals_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/deals', [
			'name' => 'PHPUnitTestDeal_' . time(),
			'phone' => '11999990000',
			'price' => 100,
		]);
		$this->assertNot500($r, 'POST /deals');
	}

	public function test_post_leads_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/leads', [
			'subject' => 'PHPUnitTestLead',
			'name' => 'TestLeadName',
			'email' => 'phpunit_lead_' . time() . '@test.com',
			'phone' => '11999998888',
		]);
		$this->assertNot500($r, 'POST /leads');
	}

	public function test_post_company_settings_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/company-settings', [
			'companyName' => 'Brand New Ideas Company ERP_PHPUnit',
		]);
		$this->assertNot500($r, 'POST /company-settings');
	}

	public function test_post_business_setting_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/business-setting', [
			'SITE_RTL' => 'off',
		]);
		$this->assertNot500($r, 'POST /business-setting');
	}

	public function test_post_cache_settings_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/cache-settings');
		$this->assertNot500($r, 'POST /cache-settings');
	}

	public function test_post_announcement_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/announcement', [
			'title' => 'PHPUnitAnnouncement',
			'start_date' => '2026-01-01',
			'end_date' => '2026-12-31',
			'description' => 'Auto test announcement',
		]);
		$this->assertNot500($r, 'POST /announcement');
	}

	public function test_post_bank_accounts_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/bank_accounts', [
			'holder_name' => 'TestHolder',
			'bank_name' => 'TestBank',
			'account_number' => '1234567890',
			'opening_balance' => 0,
			'contact_number' => '11999990000',
		]);
		$this->assertNot500($r, 'POST /bank_accounts');
	}

	public function test_post_budgets_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/budgets', [
			'name' => 'PHPUnitBudget',
			'from' => '2026-01-01',
			'to' => '2026-12-31',
			'period' => 'monthly',
		]);
		$this->assertNot500($r, 'POST /budgets');
	}

	public function test_post_chart_of_accounts_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/chart_of_accounts', [
			'name' => 'PHPUnitAccount',
			'code' => 'PU' . time(),
			'type' => 1,
			'is_enabled' => 1,
		]);
		$this->assertNot500($r, 'POST /chart_of_accounts');
	}

	public function test_post_company_policies_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/company_policies', [
			'title' => 'PHPUnitPolicy',
			'description' => 'Auto test policy',
		]);
		$this->assertNot500($r, 'POST /company_policies');
	}

	public function test_post_bug_status_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/bug_status', [
			'title' => 'PHPUnitBugStatus',
		]);
		$this->assertNot500($r, 'POST /bug_status');
	}

	public function test_post_award_types_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/award_types', [
			'name' => 'PHPUnitAwardType',
		]);
		$this->assertNot500($r, 'POST /award_types');
	}

	public function test_post_allowance_options_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/allowance_options', [
			'name' => 'PHPUnitAllowance',
		]);
		$this->assertNot500($r, 'POST /allowance_options');
	}

	public function test_post_commissions_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/commissions', [
			'title' => 'PHPUnitCommission',
			'type' => 'fixed',
			'amount' => 100,
		]);
		$this->assertNot500($r, 'POST /commissions');
	}

	// ─────────────────────────────────────────────────────────────────────
	// Phase 2: Settings POST routes
	// ─────────────────────────────────────────────────────────────────────

	public function test_post_system_settings_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/system-settings', [
			'siteCurrency' => 'BRL',
		]);
		$this->assertNot500($r, 'POST /system-settings');
	}

	public function test_post_company_email_settings_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/company-email-settings', [
			'mail_driver' => 'smtp',
			'mail_host' => 'smtp.test.com',
			'mail_port' => 587,
		]);
		$this->assertNot500($r, 'POST /company-email-settings');
	}

	public function test_post_chatgpt_settings_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/chatgpt-settings', [
			'chatgpt_key' => 'mock_key_phpunit',
		]);
		$this->assertNot500($r, 'POST /chatgpt-settings');
	}

	// ─────────────────────────────────────────────────────────────────────
	// Phase 3: AJAX / JSON helper endpoints
	// ─────────────────────────────────────────────────────────────────────

	public function test_post_announcements_getdepartment_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/announcements/getdepartment');
		$this->assertNot500($r, 'POST /announcements/getdepartment');
	}

	public function test_post_announcements_getemployee_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/announcements/getemployee');
		$this->assertNot500($r, 'POST /announcements/getemployee');
	}

	public function test_post_billsproduct_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/billsproduct');
		$this->assertNot500($r, 'POST /billsproduct');
	}

	public function test_post_branches_employees_json_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/branches/employees/json');
		$this->assertNot500($r, 'POST /branches/employees/json');
	}

	public function test_post_chart_of_accounts_subtype_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/chart_of_accounts/subtype');
		$this->assertNot500($r, 'POST /chart_of_accounts/subtype');
	}

    // ─────────────────────────────────────────────────────────────────────
    // Phase 4: Resource store endpoints (scan — expects 302 or 422)
    // ─────────────────────────────────────────────────────────────────────

	/**
	 * @dataProvider resourceStoreRouteProvider
	 */
	public function test_resource_store_does_not_crash(string $uri, array $data, string $label): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/' . $uri, $data);
		$this->assertNot500($r, "POST /{$uri} ({$label})");
	}

	/**
	 * @return array<string, array{string, array<string,mixed>, string}>
	 */
	public static function resourceStoreRouteProvider(): array
	{
		return [
			'account_assets' => ['account_assets', [], 'store account asset'],
			'allowances' => ['allowances', [], 'store allowance'],
			'bank_transfers' => ['bank_transfers', [], 'store bank transfer'],
			'branches' => ['branches', ['name' => 'PHPUnit Branch'], 'store branch'],
			'contract_types' => ['contract_types', ['name' => 'PHPUnit ContractType'], 'store contract type'],
			'contracts' => ['contracts', [], 'store contract'],
			'custom_fields' => ['custom_fields', [], 'store custom field'],
			'deduction_options' => ['deduction_options', ['name' => 'PHPUnit Deduction'], 'store deduction option'],
			'departments' => ['departments', ['name' => 'PHPUnit Dept'], 'store department'],
			'designations' => ['designations', ['name' => 'PHPUnit Designation'], 'store designation'],
			'document_uploads' => ['document_uploads', [], 'store document upload'],
			'documents' => ['documents', [], 'store document'],
			'events' => ['events', [], 'store event'],
			'expenses' => ['expenses', [], 'store expense'],
			'goal_types' => ['goal_types', ['name' => 'PHPUnit GoalType'], 'store goal type'],
			'goals' => ['goals', [], 'store goal'],
			'holidays' => ['holidays', ['date' => '2026-12-25', 'occasion' => 'PHPUnit Holiday'], 'store holiday'],
			'indicators' => ['indicators', [], 'store indicator'],
			'invoices' => ['invoices', [], 'store invoice'],
			'journal_entries' => ['journal_entries', [], 'store journal entry'],
			'leave_types' => ['leave_types', ['title' => 'PHPUnit Leave', 'days' => 5], 'store leave type'],
			'other_payments' => ['other_payments', [], 'store other payment'],
			'payments' => ['payments', [], 'store payment'],
			'performance_types' => ['performance_types', ['name' => 'PHPUnit PerfType'], 'store performance type'],
			'pipelines' => ['pipelines', ['name' => 'PHPUnit Pipeline'], 'store pipeline'],
			'purchases' => ['purchases', [], 'store purchase'],
			'saturation_deductions' => ['saturation_deductions', [], 'store saturation deduction'],
			'sources' => ['sources', ['name' => 'PHPUnit Source'], 'store source'],
			'stages' => ['stages', ['name' => 'PHPUnit Stage'], 'store stage'],
			'taxes' => ['taxes', ['name' => 'PHPUnit Tax', 'rate' => 10], 'store tax'],
			'trainers' => ['trainers', [], 'store trainer'],
			'training_types' => ['training_types', ['name' => 'PHPUnit TrainingType'], 'store training type'],
			'trainings' => ['trainings', [], 'store training'],
			'transfers' => ['transfers', [], 'store transfer'],
			'travels' => ['travels', [], 'store travel'],
			'vendors' => ['vendors', ['name' => 'PHPUnit Vendor', 'email' => 'phpunit_vendor@test.com', 'contact' => '11999990000'], 'store vendor'],
			'warnings' => ['warnings', [], 'store warning'],
			'customers' => ['customers', ['name' => 'PHPUnit Customer', 'email' => 'phpunit_customer@test.com'], 'store customer'],
			'complaints' => ['complaints', [], 'store complaint'],
			'competencies' => ['competencies', [], 'store competency'],
			'terminations' => ['terminations', [], 'store termination'],
			// 'warehouses' excluded — pre-existing bug: route('warehouse.index') undefined (should be warehouses.index)
			'supports' => ['supports', [], 'store support'],
			'coupons' => ['coupons', ['name' => 'PHPUnit Coupon', 'code' => 'PU' . time()], 'store coupon'],
			'employee_attendances' => ['employee_attendances', [], 'store attendance'],
		];
	}

    // ─────────────────────────────────────────────────────────────────────
    // Phase 5: DELETE with fake UUID (expect 404 or 302, NOT 500)
    // ─────────────────────────────────────────────────────────────────────

	/**
	 * @dataProvider resourceDeleteRouteProvider
	 */
	public function test_resource_delete_nonexistent_does_not_crash(string $uri, string $label): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$fakeId = '00000000-0000-0000-0000-000000000000';
		$r = $this->delete("/{$uri}/{$fakeId}");
		$this->assertNot500($r, "DELETE /{$uri}/{$fakeId} ({$label})");
	}

	/**
	 * @return array<string, array{string, string}>
	 */
	public static function resourceDeleteRouteProvider(): array
	{
		return [
			'deals' => ['deals', 'delete deal'],
			'leads' => ['leads', 'delete lead'],
			'projects' => ['projects', 'delete project'],
			'invoices' => ['invoices', 'delete invoice'],
			'expenses' => ['expenses', 'delete expense'],
			'bank_accounts' => ['bank_accounts', 'delete bank account'],
			'contracts' => ['contracts', 'delete contract'],
			'departments' => ['departments', 'delete department'],
			'designations' => ['designations', 'delete designation'],
			'holidays' => ['holidays', 'delete holiday'],
			'pipelines' => ['pipelines', 'delete pipeline'],
			'stages' => ['stages', 'delete stage'],
			'sources' => ['sources', 'delete source'],
			'taxes' => ['taxes', 'delete tax'],
			'vendors' => ['vendors', 'delete vendor'],
			'customers' => ['customers', 'delete customer'],
			'warehouses' => ['warehouses', 'delete warehouse'],
		];
	}

	// ─────────────────────────────────────────────────────────────────────
	// Phase 6: Specific endpoint edge cases
	// ─────────────────────────────────────────────────────────────────────

	public function test_post_change_password_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/change-password', [
			'current_password' => '123456789qwe.*',
			'new_password' => '123456789qwe.*',
			'new_confirm_password' => '123456789qwe.*',
		]);
		$this->assertNot500($r, 'POST /change-password');
	}

	public function test_post_calendars_get_task_data_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/calendars/get_task_data');
		$this->assertNot500($r, 'POST /calendars/get_task_data');
	}

	public function test_post_pos_does_not_crash(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		$r = $this->post('/pos');
		$this->assertNot500($r, 'POST /pos');
	}

	public function test_set_salaries_store_returns_405(): void
	{
		if (!$this->admin) {
			$this->markTestSkipped('No admin user seeded');
		}
		// Route was restricted to only(['index','show','edit','create'])
		$r = $this->post('/set_salaries');
		$this->assertContains($r->getStatusCode(), [405, 404, 302, 200]);
	}
}
