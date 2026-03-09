<?php

namespace Tests\Unit\app\Http\Controllers\views;

use App\Http\Controllers\Bills\BillController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;

#[\PHPUnit\Framework\Attributes\Group('controller-views')]
class BillControllerViewTest extends TestCase
{
	use RefreshDatabase;
	use ControllerTestHelper;
	use ViewAssertionHelper;

	/* ------------------------------------------------------------------ */
	/*  View-file existence tests                                         */
	/* ------------------------------------------------------------------ */

	public function test_bills_index_view_file_exists(): void
	{
		$this->assertBladeViewExists('bills.index');
	}

	public function test_bills_create_view_file_exists(): void
	{
		$this->assertBladeViewExists('bills.create');
	}

	public function test_bills_view_file_exists(): void
	{
		$this->assertBladeViewExists('bills.view');
	}

	public function test_bills_edit_view_file_exists(): void
	{
		$this->assertBladeViewExists('bills.edit');
	}

	public function test_bills_payment_view_file_exists(): void
	{
		$this->assertBladeViewExists('bills.payment');
	}

	public function test_bills_vendor_detail_view_file_exists(): void
	{
		$this->assertBladeViewExists('bills.vendor_detail');
	}

	public function test_vendors_bill_send_view_file_exists(): void
	{
		$this->assertBladeViewExists('vendors.bill_send');
	}

	public function test_bills_customer_bill_view_file_exists(): void
	{
		$this->assertBladeViewExists('bills.customer_bill');
	}

	/* ------------------------------------------------------------------ */
	/*  Bill template views (template1 .. template10)                     */
	/* ------------------------------------------------------------------ */

	public static function billTemplateProvider(): array
	{
		$data = [];
		for ($i = 1; $i <= 10; $i++) {
			$data["template{$i}"] = ["bills.templates.template{$i}"];
		}
		return $data;
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('billTemplateProvider')]
	public function test_bill_template_view_file_exists(string $dotPath): void
	{
		$this->assertBladeViewExists($dotPath);
	}

	/* ------------------------------------------------------------------ */
	/*  Controller class & reflection tests                                */
	/* ------------------------------------------------------------------ */

	public function test_bill_controller_class_exists(): void
	{
		$this->assertTrue(
			class_exists(BillController::class),
			'BillController class should be loadable.'
		);
	}

	public function test_bill_controller_has_expected_methods(): void
	{
		$ref = new ReflectionClass(BillController::class);

		$expected = [
			'index',
			'create',
			'store',
			'show',
			'edit',
			'update',
			'destroy',
			'createPayment',
			'paymentDestroy',
			'vendorBill',
			'vendorBillShow',
			'vendorBillSend',
			'vendorBillSendMail',
			'previewBill',
			'saveBillTemplateSettings',
		];

		foreach ($expected as $method) {
			$this->assertTrue(
				$ref->hasMethod($method),
				"BillController should have public method [{$method}]."
			);
			$this->assertTrue(
				$ref->getMethod($method)->isPublic(),
				"Method [{$method}] should be public."
			);
		}
	}

	public function test_bill_controller_constants_defined(): void
	{
		$this->assertSame('index', BillController::IDX);
		$this->assertSame('create', BillController::CRT);
		$this->assertSame('store', BillController::STR);
		$this->assertSame('show', BillController::SHW);
		$this->assertSame('edit', BillController::EDT);
		$this->assertSame('update', BillController::UPD);
		$this->assertSame('destroy', BillController::DEL);
		$this->assertSame('productDestroy', BillController::PRD_DST);
		$this->assertSame('billNumber', BillController::BIL_N);
		$this->assertSame('createPayment', BillController::PAY_CRT);
		$this->assertSame('paymentDestroy', BillController::PAY_DST);
		$this->assertSame('vendorBill', BillController::VD_BIL);
		$this->assertSame('vendorBillShow', BillController::VD_BIL_SHW);
		$this->assertSame('vendorBillSend', BillController::VD_BIL_SND);
		$this->assertSame('vendorBillSendMail', BillController::VD_BIL_SND_M);
		$this->assertSame('shippingDisplay', BillController::SHP_DSP);
		$this->assertSame('previewBill', BillController::PV_BIL);
		$this->assertSame('saveBillTemplateSettings', BillController::SV_BIL_TMP);
		$this->assertSame('invoiceLink', BillController::IV_LK);
	}
}
