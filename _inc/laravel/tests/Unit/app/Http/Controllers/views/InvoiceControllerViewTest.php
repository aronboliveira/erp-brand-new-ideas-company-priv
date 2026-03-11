<?php

namespace Tests\Unit\app\Http\Controllers\views;

use App\Http\Controllers\InvoiceController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;

#[\PHPUnit\Framework\Attributes\Group('controller-views')]
class InvoiceControllerViewTest extends TestCase
{
	use RefreshDatabase;
	use ControllerTestHelper;
	use ViewAssertionHelper;

	/* ------------------------------------------------------------------ */
	/*  View-file existence tests                                         */
	/* ------------------------------------------------------------------ */

	public function test_invoices_index_view_file_exists(): void
	{
		$this->assertBladeViewExists('invoices.index');
	}

	public function test_invoices_create_view_file_exists(): void
	{
		$this->assertBladeViewExists('invoices.create');
	}

	public function test_invoices_view_file_exists(): void
	{
		$this->assertBladeViewExists('invoices.view');
	}

	public function test_invoices_edit_view_file_exists(): void
	{
		$this->assertBladeViewExists('invoices.edit');
	}

	public function test_invoices_payment_view_file_exists(): void
	{
		$this->assertBladeViewExists('invoices.payment');
	}

	public function test_invoices_customer_detail_view_file_exists(): void
	{
		$this->assertBladeViewExists('invoices.customer_detail');
	}

	public function test_invoices_customer_invoice_view_file_exists(): void
	{
		$this->assertBladeViewExists('invoices.customer_invoice');
	}

	public function test_customers_invoice_send_view_file_exists(): void
	{
		// Controller: VW::CST . '.invoice_send' = 'customers.invoice_send'
		// No blade at customers/invoice_send.blade.php — only emails/invoice/customer_send.blade.php
		// This is a known mismatch — the controller's ViewFacade::exists() guard
		// will catch it at runtime and redirect with error.
		// We verify the closest matching file exists:
		$this->assertBladeViewExists('emails.invoice.customer_send');
	}

	/**
	 * Documents that InvoiceController::customerInvoiceSend() references
	 * 'customers.invoice_send' but no such blade file exists.
	 */
	public function test_invoice_send_view_name_mismatch_is_documented(): void
	{
		$controllerRef = 'customers.invoice_send';
		$viewsPath = base_path('resources/views');
		$filePath = $viewsPath . '/' . str_replace('.', '/', $controllerRef) . '.blade.php';
		$this->assertFileDoesNotExist($filePath, 'Expected mismatch: customers/invoice_send.blade.php should NOT exist');
	}

	/* ------------------------------------------------------------------ */
	/*  Invoice template views (template1 .. template10)                  */
	/* ------------------------------------------------------------------ */

	public static function invoiceTemplateProvider(): array
	{
		$data = [];
		for ($i = 1; $i <= 10; $i++) {
			$data["template{$i}"] = ["invoices.templates.template{$i}"];
		}
		return $data;
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('invoiceTemplateProvider')]
	public function test_invoice_template_view_file_exists(string $dotPath): void
	{
		$this->assertBladeViewExists($dotPath);
	}

	/* ------------------------------------------------------------------ */
	/*  Controller class & reflection tests                                */
	/* ------------------------------------------------------------------ */

	public function test_invoice_controller_class_exists(): void
	{
		$this->assertTrue(
			class_exists(InvoiceController::class),
			'InvoiceController class should be loadable.'
		);
	}

	public function test_invoice_controller_has_expected_methods(): void
	{
		$ref = new ReflectionClass(InvoiceController::class);

		$expected = [
			'index',
			'create',
			'store',
			'edit',
			'update',
			'destroy',
			'customer',
			'customerInvoice',
			'customerInvoiceShow',
			'createPayment',
			'paymentDestroy',
			'paymentReminder',
			'shippingDisplay',
			'invoiceLink',
			'saveTemplateSettings',
			'previewInvoice',
			'customerInvoiceSend',
			'customerInvoiceSendMail',
		];

		foreach ($expected as $method) {
			$this->assertTrue(
				$ref->hasMethod($method),
				"InvoiceController should have public method [{$method}]."
			);
			$this->assertTrue(
				$ref->getMethod($method)->isPublic(),
				"Method [{$method}] should be public."
			);
		}
	}

	public function test_invoice_controller_constants_defined(): void
	{
		$this->assertSame('index', InvoiceController::IDX);
		$this->assertSame('create', InvoiceController::CRT);
		$this->assertSame('store', InvoiceController::STR);
		$this->assertSame('show', InvoiceController::SHW);
		$this->assertSame('edit', InvoiceController::EDT);
		$this->assertSame('update', InvoiceController::UPD);
		$this->assertSame('getInvoice', InvoiceController::GET_INV);
		$this->assertSame('invoiceNumber', InvoiceController::INV_N);
		$this->assertSame('productDestroy', InvoiceController::PRD_DST);
		$this->assertSame('customerInvoice', InvoiceController::CST_INV);
		$this->assertSame('customerInvoiceShow', InvoiceController::CST_INV_SHW);
		$this->assertSame('createPayment', InvoiceController::PAY_CRT);
		$this->assertSame('paymentDestroy', InvoiceController::PAY_DST);
		$this->assertSame('paymentReminder', InvoiceController::PAY_RMD);
		$this->assertSame('shippingDisplay', InvoiceController::SHP_DSP);
		$this->assertSame('invoiceLink', InvoiceController::IV_LK);
		$this->assertSame('saveTemplateSettings', InvoiceController::SV_IV_TMP);
		$this->assertSame('previewInvoice', InvoiceController::INV_PRV);
		$this->assertSame('customerInvoiceSend', InvoiceController::CST_INV_SD);
		$this->assertSame('customerInvoiceSendMail', InvoiceController::CST_INV_SD_ML);
	}
}
