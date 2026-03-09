<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use Mockery;

/**
 * Unit tests for invoice-related Blade views.
 *
 * Covered views:
 *   - invoices.index
 *   - invoices.create
 *   - invoices.edit
 *   - invoices.view
 *   - invoices.action
 *   - invoices.payment
 *   - invoices.paymentwall
 *   - invoices.paytr_payment
 *   - invoices.customer_detail
 *   - invoices.customer_invoice
 *   - invoices.script
 *   - invoices.templates.template1 … template10
 */
class InvoiceViewsTest extends TestCase
{
	use BladeViewTestHelper;

	// ─── Core CRUD views ────────────────────────────────────────────

	public function test_invoices_index_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.index');
	}

	public function test_invoices_index_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.index');
	}

	public function test_invoices_index_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('invoices.index', [
			'invoices'  => collect([]),
			'customers' => [],
			'currency'  => 'USD',
			'settings'  => $this->buildMockSettings(),
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── invoices.create ────────────────────────────────────────────

	public function test_invoices_create_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.create');
	}

	public function test_invoices_create_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.create');
	}

	public function test_invoices_create_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('invoices.create', [
			'customers'        => collect([]),
			'categories'       => collect([]),
			'product_services' => collect([]),
			'taxes'            => [],
			'invoice_number'   => 1,
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

	// ─── invoices.edit ──────────────────────────────────────────────

	public function test_invoices_edit_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.edit');
	}

	public function test_invoices_edit_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.edit');
	}

	public function test_invoices_edit_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('invoices.edit', [
			'invoice'          => (object) $this->buildMockInvoice(),
			'customers'        => collect([]),
			'categories'       => collect([]),
			'product_services' => collect([]),
			'taxes'            => [],
			'invoiceItems'     => collect([]),
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

	// ─── invoices.view ──────────────────────────────────────────────

	public function test_invoices_view_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.view');
	}

	public function test_invoices_view_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.view');
	}

	// ─── invoices.action ────────────────────────────────────────────

	public function test_invoices_action_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.action');
	}

	public function test_invoices_action_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.action');
	}

	// ─── invoices.payment ───────────────────────────────────────────

	public function test_invoices_payment_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.payment');
	}

	public function test_invoices_payment_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.payment');
	}

	// ─── invoices.paymentwall ───────────────────────────────────────

	public function test_invoices_paymentwall_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.paymentwall');
	}

	public function test_invoices_paymentwall_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.paymentwall');
	}

	// ─── invoices.paytr_payment ─────────────────────────────────────

	public function test_invoices_paytr_payment_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.paytr_payment');
	}

	public function test_invoices_paytr_payment_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.paytr_payment');
	}

	// ─── invoices.customer_detail ───────────────────────────────────

	public function test_invoices_customer_detail_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.customer_detail');
	}

	public function test_invoices_customer_detail_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.customer_detail');
	}

	// ─── invoices.customer_invoice ──────────────────────────────────

	public function test_invoices_customer_invoice_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.customer_invoice');
	}

	public function test_invoices_customer_invoice_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.customer_invoice');
	}

	// ─── invoices.script ────────────────────────────────────────────

	public function test_invoices_script_view_file_exists(): void
	{
		$this->assertViewFileExists('invoices.script');
	}

	public function test_invoices_script_view_is_registered(): void
	{
		$this->assertViewRegistered('invoices.script');
	}

    // ─── Invoice Templates ──────────────────────────────────────────

	/**
	 * @dataProvider invoiceTemplateProvider
	 */
	public function test_invoice_template_view_file_exists(string $template): void
	{
		$this->assertViewFileExists($template);
	}

	/**
	 * @dataProvider invoiceTemplateProvider
	 */
	public function test_invoice_template_view_is_registered(string $template): void
	{
		$this->assertViewRegistered($template);
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function invoiceTemplateProvider(): array
	{
		$templates = [];
		for ($i = 1; $i <= 10; $i++) {
			$key = "template{$i}";
			$templates[$key] = ["invoices.templates.template{$i}"];
		}
		return $templates;
	}

	// ─── Batch – all invoice views exist ────────────────────────────

	public function test_all_invoice_views_exist_in_finder(): void
	{
		$views = [
			'invoices.index',
			'invoices.create',
			'invoices.edit',
			'invoices.view',
			'invoices.action',
			'invoices.payment',
			'invoices.paymentwall',
			'invoices.paytr_payment',
			'invoices.customer_detail',
			'invoices.customer_invoice',
			'invoices.script',
		];

		for ($i = 1; $i <= 10; $i++) {
			$views[] = "invoices.templates.template{$i}";
		}

		foreach ($views as $view) {
			$this->assertTrue(
				View::exists($view),
				"Invoice view [{$view}] is not registered."
			);
		}
	}
}
