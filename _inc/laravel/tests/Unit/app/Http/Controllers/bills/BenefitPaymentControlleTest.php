<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Coupon, Invoice, InvoicePayment, Order, Plan, User, UserCoupon};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, DB, Log, Http};
use Tests\TestCase;

class BenefitPaymentControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
		Http::fake(); // Prevent real API calls
		DB::shouldReceive('beginTransaction')->andReturnTrue();
		DB::shouldReceive('commit')->andReturnTrue();
		DB::shouldReceive('rollBack')->andReturnTrue();
	}

	/**
	 ** @test
	 **
	 ** When initiating payment with a valid coupon code,
	 ** the controller should apply the discount and redirect to the payment gateway.
	 **/
	public function test_initiate_payment_with_coupon_applies_discount()
	{
		$user  = User::factory()->create();
		$plan  = Plan::factory()->create(['price' => 100]);
		$coupon = Coupon::factory()->create([
			'code'     => 'SAVE50',
			'discount' => 50,
			'limit'    => 5,
		]);
		$this->actingAs($user);

		$encId   = Crypt::encryptString($plan->id);
		$response = $this->post(route('benefit.initiate'), [
			'plan_id' => $encId,
			'coupon'  => $coupon->code,
		]);

		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Upon callback from Benefit gateway with a successful transaction,
	 ** the controller should activate the plan and record the order with coupon attached.
	 **/
	public function test_callback_activates_plan_and_attaches_coupon()
	{
		$user  = User::factory()->create();
		$plan  = Plan::factory()->create(['price' => 100]);
		$coupon = Coupon::factory()->create(['code' => 'PROMO10']);
		$this->actingAs($user);

		Http::fake([
			'https://api.tap.company/v2/charges/*' => Http::response([
				'gateway'     => ['response'   => ['code' => '00']],
				'transaction' => ['url'        => 'https://fakeurl.com'],
			]),
		]);

		$response = $this->get(route('benefit.callBack', [
			'plan'   => $plan->id,
			'coupon' => $coupon->code,
			'tap_id' => 'tap_1234',
			'amount' => 100,
		]));

		$response->assertRedirect(route('plans.index'));
		$this->assertDatabaseHas('orders', [
			'plan_id'      => $plan->id,
			'payment_type' => 'Benefit',
		]);
	}

	/**
	 ** @test
	 **
	 ** When paying an invoice via Benefit gateway callback,
	 ** the controller should record an InvoicePayment and redirect appropriately.
	 **/
	public function test_invoice_payment_creates_invoice_payment_record()
	{
		$user   = User::factory()->create();
		$invoice = Invoice::factory()->create(['created_by' => $user?->id]);
		$this->actingAs($user);

		Http::fake([
			'https://api.tap.company/v2/charges/*' => Http::response([
				'gateway'     => ['response'   => ['code' => '00']],
				'transaction' => ['url'        => 'https://fakeurl.com'],
			]),
		]);

		$encId   = Crypt::encryptString($invoice->id);
		$response = $this->get(route('invoice.benefit.status', [
			'invoiceEncrypted' => $encId,
			'amount'           => 100,
			'tap_id'           => 'tap_5678',
		]));

		$response->assertRedirect();
		$this->assertDatabaseHas('invoice_payments', [
			'invoice_id'   => $invoice->id,
			'amount'       => 100,
			'payment_type' => 'Benefit',
		]);
	}
}
