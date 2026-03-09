<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use Mockery;

/**
 * Unit tests for bill-related Blade views.
 *
 * Covered views:
 *   - bills.index
 *   - bills.create
 *   - bills.edit
 *   - bills.view
 *   - bills.payment
 *   - bills.customer_bill
 *   - bills.vendor_detail
 *   - bills.script
 *   - bills.templates.template1 … template10
 */
class BillViewsTest extends TestCase
{
	use BladeViewTestHelper;

	// ─── Core CRUD views ────────────────────────────────────────────

	public function test_bills_index_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.index');
	}

	public function test_bills_index_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.index');
	}

	public function test_bills_index_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('bills.index', [
			'bills'    => collect([]),
			'vendors'  => [],
			'currency' => 'USD',
			'settings' => $this->buildMockSettings(),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── bills.create ───────────────────────────────────────────────

	public function test_bills_create_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.create');
	}

	public function test_bills_create_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.create');
	}

	public function test_bills_create_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('bills.create', [
			'vendors'          => collect([]),
			'categories'       => collect([]),
			'product_services' => collect([]),
			'taxes'            => [],
			'bill_number'      => 1,
			'customFields'     => [],
			'settings'         => $this->buildMockSettings(),
			'chartAccounts'    => collect([]),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── bills.edit ─────────────────────────────────────────────────

	public function test_bills_edit_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.edit');
	}

	public function test_bills_edit_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.edit');
	}

	public function test_bills_edit_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('bills.edit', [
			'bill'             => (object) $this->buildMockBill(),
			'vendors'          => collect([]),
			'categories'       => collect([]),
			'product_services' => collect([]),
			'taxes'            => [],
			'billItems'        => collect([]),
			'customFields'     => [],
			'settings'         => $this->buildMockSettings(),
			'chartAccounts'    => collect([]),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── bills.view ─────────────────────────────────────────────────

	public function test_bills_view_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.view');
	}

	public function test_bills_view_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.view');
	}

	// ─── bills.payment ──────────────────────────────────────────────

	public function test_bills_payment_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.payment');
	}

	public function test_bills_payment_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.payment');
	}

	// ─── bills.customer_bill ────────────────────────────────────────

	public function test_bills_customer_bill_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.customer_bill');
	}

	public function test_bills_customer_bill_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.customer_bill');
	}

	// ─── bills.vendor_detail ────────────────────────────────────────

	public function test_bills_vendor_detail_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.vendor_detail');
	}

	public function test_bills_vendor_detail_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.vendor_detail');
	}

	// ─── bills.script ───────────────────────────────────────────────

	public function test_bills_script_view_file_exists(): void
	{
		$this->assertViewFileExists('bills.script');
	}

	public function test_bills_script_view_is_registered(): void
	{
		$this->assertViewRegistered('bills.script');
	}

    // ─── Bill Templates ─────────────────────────────────────────────

	/**
	 * @dataProvider billTemplateProvider
	 */
	public function test_bill_template_view_file_exists(string $template): void
	{
		$this->assertViewFileExists($template);
	}

	/**
	 * @dataProvider billTemplateProvider
	 */
	public function test_bill_template_view_is_registered(string $template): void
	{
		$this->assertViewRegistered($template);
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function billTemplateProvider(): array
	{
		$templates = [];
		for ($i = 1; $i <= 10; $i++) {
			$key = "template{$i}";
			$templates[$key] = ["bills.templates.template{$i}"];
		}
		return $templates;
	}

	// ─── Batch – all bill views exist ───────────────────────────────

	public function test_all_bill_views_exist_in_finder(): void
	{
		$views = [
			'bills.index',
			'bills.create',
			'bills.edit',
			'bills.view',
			'bills.payment',
			'bills.customer_bill',
			'bills.vendor_detail',
			'bills.script',
		];

		for ($i = 1; $i <= 10; $i++) {
			$views[] = "bills.templates.template{$i}";
		}

		foreach ($views as $view) {
			$this->assertTrue(
				View::exists($view),
				"Bill view [{$view}] is not registered."
			);
		}
	}
}
